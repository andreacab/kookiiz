<?php
    /*******************************************************
    Title: API chefs
    Authors: Kookiiz Team
    Purpose: API module for chefs-related actions
    ********************************************************/

    //Dependencies
    require_once '../api/api.php';
    require_once '../class/chefs_lib.php';
    require_once '../class/recipes_lib.php';

    //Represents an API handler for chefs-related actions
    class ChefsAPI extends KookiizAPIHandler
    {
        /**********************************************************
        PROPERTIES
        ***********************************************************/

        const MODULE = 'chefs';

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

        //Request for content on specific chef
        //-> (void)
        private function load()
        {
            //Load and store parameters
            $chef_id    = (int)$this->Request->get('chef_id');
            $full       = (int)$this->Request->get('full');
            $this->responseSetParam('chef_id', $chef_id);
            $this->responseSetParam('full', $full);

            //Load chef data
            $ChefsLib = new ChefsLib($this->DB, $this->User);
            $chef = $ChefsLib->load($chef_id, $full);
            if($chef && $full)
            {
                $RecipesLib = new RecipesLib($this->DB, $this->User);
                $chef['recipes'] = $RecipesLib->chef_best($chef_id);
            }
            $this->responseSetContent($chef);
        }
    }
?>