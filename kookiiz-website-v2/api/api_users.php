<?php
    /*******************************************************
    Title: API users
    Authors: Kookiiz Team
    Purpose: API module for users-related actions
    ********************************************************/

    //Dependencies
    require_once '../api/api.php';
    require_once '../class/facebook.php';
    require_once '../class/globals.php';
    require_once '../class/users_lib.php';
    require_once '../secure/facebook.php';

    //Represents an API handler for user-related actions
    class UsersAPI extends KookiizAPIHandler
    {
        /**********************************************************
        PROPERTIES
        ***********************************************************/

        const MODULE = 'users';

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
                case 'admin_elect':
                    $this->admin_elect();
                    break;
                case 'delete':
                    $this->delete();
                    break;
                case 'network_info':
                    $this->network_info();
                    break;
                case 'pic_save':
                    $this->pic_save();
                    break;
                case 'preview':
                    $this->preview();
                    break;
                case 'search':
                    $this->search();
                    break;
            }
        }

        /**********************************************************
        ADMIN ELECT
        ***********************************************************/

        //Request to elect a user as admin
        //-> (void)
        private function admin_elect()
        {
            //Load and store parameters
            $target_id = (int)$this->Request->get('target_id');
            $this->responseSetParam('target_id', $target_id);

            //Elect target user as admin
            $UsersLib = new UsersLib($this->DB, $this->User);
            $UsersLib->elect_admin($target_id);
        }

        /**********************************************************
        DELETE
        ***********************************************************/

        //Request to delete current user profile
        //-> (void)
        private function delete()
        {
            $this->User->delete();
        }
        
        /**********************************************************
        NETWORK INFO
        ***********************************************************/
        
        //Request for social network information on current user
        //Data is only returned in case of success
        //-> (void)
        private function network_info()
        {
            $network = $this->Request->get('network');
            $this->responseSetParam('network', $network);
            
            $info = array('firstname' => '', 'lastname' => '', 'email' => '', 'lang' => '');        
            switch($network)
            {
                case 'facebook':
                    $Facebook = new Facebook(array(
                        'appId'     => C::FACEBOOK_APP_ID,
                        'secret'    => FACEBOOK_SECRET
                    ));
                    $fb_user = $Facebook->getUser();
                    if($fb_user)
                    {
                        try
                        {
                            $fb_user = $Facebook->api('/me');
                            $info['firstname']  = $fb_user['first_name'];
                            $info['lastname']   = $fb_user['last_name'];
                            $info['email']      = $fb_user['email'];
                            $info['lang']       = explode('_', $fb_user['locale']);
                            $info['lang']       = $info['lang'][0];
                        }
                        catch(FacebookApiException $e){}
                    }
                    break;
            }
            $this->responseSetContent($info);
        }

        /**********************************************************
        PICTURE
        ***********************************************************/

        //Request to update user's picture
        //-> (void)
        private function pic_save()
        {
            //Load and store params
            $pic_id = (int)$this->Request->get('pic_id');
            $this->responseSetParam('pic_id', $pic_id);

            //Update user's picture and store updated timestamp
            $time = $this->User->picture_set($pic_id);
            $this->responseSetParam('time', $time);
        }

        /**********************************************************
        PREVIEW
        ***********************************************************/

        //Request for a preview of a user profile
        //-> (void)
        private function preview()
        {
            //Load and store parameters
            $user_id = (int)$this->Request->get('user_id');
            $this->responseSetParam('user_id', $user_id);

            //Load user profile
            $UsersLib = new UsersLib($this->DB, $this->User);
            $this->responseSetContent($UsersLib->export(array($user_id)));
        }

        /**********************************************************
        SEARCH
        ***********************************************************/

        //Request to search for user accounts matching a keyword
        //-> (void)
        private function search()
        {
            //Load and store parameters
            $keyword = $this->Request->get('keyword');
            $this->responseSetParam('keyword', $keyword);

            //Search for matching users
            $UsersLib = new UsersLib($this->DB, $this->User);
            $this->responseSetContent($UsersLib->search($keyword));
        }
    }
?>