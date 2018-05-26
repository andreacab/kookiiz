<?php
    /*******************************************************
    Title: API news
    Authors: Kookiiz Team
    Purpose: API module for news-related actions
    ********************************************************/

    //Dependencies
    require_once '../api/api.php';
    require_once '../class/globals.php';
    require_once '../class/facebook.php';
    require_once '../secure/facebook.php';

    //Represents an API handler for news-related actions
    class NewsAPI extends KookiizAPIHandler
    {
        /**********************************************************
        PROPERTIES
        ***********************************************************/

        const MODULE = 'news';
        
        const LIMIT = 5;    //Default news count

        /**********************************************************
        CONSTRUCTOR
        ***********************************************************/

        //Class constructor
        //-> (void)
        public function __construct()
        {
            global $API_AUTHORIZATIONS;

            //Construct generic API handler
            parent::__construct();

            //Define authorizations for current API module
            $this->AUTH = $API_AUTHORIZATIONS[self::MODULE];
        }

        /**********************************************************
        DESTRUCTOR
        ***********************************************************/

        //Class destructor
        //-> (void)
        public function __destruct()
        {
            parent::__destruct();
        }

        /**********************************************************
        ACTION
        ***********************************************************/

        //Take appropriate action
        //-> (void)
        protected function action()
        {
            switch($this->action)
            {
                case 'load':
                    $this->load();
                    break;
            }
        }

        /**********************************************************
        LOAD
        ***********************************************************/

        //Request to load current news
        //-> (void)
        private function load()
        {
            //Load and store parameters
            $limit = (int)$this->Request->get('limit');
            $limit = $limit ? $limit : self::LIMIT;
            $this->responseSetParam('limit', $limit);
            
            //Try to fetch data from Facebook
            try
            {
                //Init Facebook API
                $Facebook = new Facebook(array
                (
                    'appId'     => C::FACEBOOK_APP_ID,
                    'secret'    => FACEBOOK_SECRET,
                    'cookie'    => true
                ));

                //Fetch feed data
                $response = $Facebook->api('/kookiizapp/feed', 'GET', array(
                    'date_format'   => 'd.m.Y',
                    'fields'        => array('message', 'link', 'created_time', 'from'),
                    'limit'         => $limit
                ));
                //Return fetched data
                if($response && isset($response['data']))
                    $this->responseSetContent($response['data']);
            }
            catch(FacebookApiException $e)
            {
            }
        }
    }
?>