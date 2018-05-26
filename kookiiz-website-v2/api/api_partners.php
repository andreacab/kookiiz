<?php
    /*******************************************************
    Title: API partners
    Authors: Kookiiz Team
    Purpose: Handler API calls related to partners
    ********************************************************/

    //Dependencies
    require_once '../api/api.php';
    require_once '../class/partners_lib.php';
    require_once '../class/password.php';
    require_once '../class/users_lib.php';

    //Represents an API module for partner-related actions
    class PartnersAPI extends KookiizAPIHandler
    {
        /**********************************************************
        PROPERTIES
        ***********************************************************/

        const MODULE = 'partners';

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
                case 'add':
                    $this->add();
                    break;
                case 'delete':
                    $this->delete();
                    break;
                case 'edit':
                    $this->edit();
                    break;
                case 'list':
                    $this->listing();
                    break;
                case 'load':
                    $this->load();
                    break;
            }
        }

        /**********************************************************
        ADD
        ***********************************************************/

        //Request to add a new partner
        //-> (void)
        private function add()
        {
            //Load and store parameters
            $name       = $this->Request->get('name');
            $link       = $this->Request->get('link');
            $pic_link   = $this->Request->get('pic_link');
            $valid      = (int)$this->Request->get('valid');
            $this->responseSetParam('name', $name);
            $this->responseSetParam('link', $link);
            $this->responseSetParam('pic_link', $pic_link);
            $this->responseSetParam('valid', $valid);

            //Create new partner
            $PartnersLib = new PartnersLib($this->DB);
            $partner_id = $PartnersLib->add($name, $link, $pic_link, $valid);
            if($partner_id) 
                $this->responseSetParam('partner_id', $partner_id);
            else            
                throw new KookiizException('admin_partners', 1);
            //Create virtual user
            $PasswordHandler = new PasswordHandler();
            $password = $PasswordHandler->hash($name);
            $UsersLib = new UsersLib($this->DB, $this->User);
            $user_id = $UsersLib->create($name, $lastname = '', $email = '', $password, $pic_id = 0, $lang = '', $virtual = true);
            //Elect virtual user as a partner
            if($user_id)    
                $UsersLib->elect_partner($user_id, $partner_id);
            else            
                throw new KookiizException('admin_partners', 2);
        }

        /**********************************************************
        DELETE
        ***********************************************************/

        //Request to remove a partner from database
        //-> (void)
        private function delete()
        {
            //Load and store parameters
            $partner_id = (int)$this->Request->get('partner_id');
            $this->responseSetParam('partner_id', $partner_id);

            //Delete partner
            $PartnersLib = new PartnersLib($this->DB);
            $PartnersLib->delete($partner_id);
        }

        /**********************************************************
        EDIT
        ***********************************************************/

        //Request to edit an existing parameter
        //-> (void)
        private function edit()
        {
            //Load and store parameters
            $id         = (int)$this->Request->get('id');
            $name       = $this->Request->get('name');
            $link       = $this->Request->get('link');
            $pic_link   = $this->Request->get('pic_link');
            $valid      = (int)$this->Request->get('valid');
            $this->responseSetParam('id', $id);
            $this->responseSetParam('name', $name);
            $this->responseSetParam('link', $link);
            $this->responseSetParam('pic_link', $pic_link);
            $this->responseSetParam('valid', $valid);

            //Edit partner
            $PartnersLib = new PartnersLib($this->DB);
            $PartnersLib->edit($id, $name, $link, $pic_link, $valid);
        }

        /**********************************************************
        LIST
        ***********************************************************/

        //Request to list all existing partners
        //-> (void)
        private function listing()
        {
            //List partners
            $PartnersLib = new PartnersLib($this->DB);
            $this->responseSetContent($PartnersLib->listing());
        }

        /**********************************************************
        LOAD
        ***********************************************************/

        //Request to load data on a specific partner
        //-> (void)
        private function load()
        {
            //Load and store parameters
            $partner_id = (int)$this->Request->get('partner_id');
            $enforce    = (int)$this->Request->get('enforce') == 1;
            $admin      = $this->User->isAdmin();
            $this->responseSetParam('partner_id', $partner_id);

            //Load partner data
            $PartnersLib = new PartnersLib($this->DB);
            $partner = $PartnersLib->load($partner_id, $enforce && $admin);
            if($partner)    
                $this->responseSetContent($partner);
            else if($admin) 
                throw new KookiizException('admin_partners', 4);
        }
    }
?>