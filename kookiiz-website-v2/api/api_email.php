<?php
    /*******************************************************
    Title: API email
    Authors: Kookiiz Team
    Purpose: API module for email-related actions
    ********************************************************/

    //Dependencies
    require_once '../api/api.php';
    require_once '../class/email.php';
    require_once '../class/users_lib.php';

    //Represents an API handler for email-related actions
    class EmailAPI extends KookiizAPIHandler
    {
        /**********************************************************
        PROPERTIES
        ***********************************************************/

        const MODULE = 'email';

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
                case 'check':
                    $this->check();
                    break;
            }
        }

        /**********************************************************
        CHECK
        ***********************************************************/

        //Request to check validity of an email address
        //-> (void)
        private function check()
        {
            //Load and store parameters
            $email = $this->Request->get('email');
            $this->responseSetParam('email', $email);

            //Check if email already exists
            $UsersLib = new UsersLib($this->DB, $this->User);
            $exists = $UsersLib->existsEmail($email);
            if($exists)
            {
                $this->responseSetParam('status', C::EMAIL_STATUS_EXISTING);
            }
            //Check if email format is valid
            else
            {
                $EmailHandler = new EmailHandler($this->DB);
                $valid = $EmailHandler->check($email);
                if($valid)
                {
                    $this->responseSetParam('status', C::EMAIL_STATUS_VALID);
                }
                else
                {
                    $this->responseSetParam('status', C::EMAIL_STATUS_NOTVALID);
                }
            }
        }
    }
?>