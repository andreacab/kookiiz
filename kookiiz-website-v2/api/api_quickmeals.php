<?php
    /*******************************************************
    Title: API quick meals
    Authors: Kookiiz Team
    Purpose: API module for quick meals-related actions
    ********************************************************/

    //Dependencies
    require_once '../api/api.php';
    require_once '../class/quickmeals_lib.php';

    //Represents an API handler for quick meals-related actions
    class QuickmealsAPI extends KookiizAPIHandler
    {
        /**********************************************************
        PROPERTIES
        ***********************************************************/

        const MODULE = 'quickmeals';

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
                case 'create':
                    $this->create();
                    break;
                case 'delete':
                    $this->delete();
                    break;
            }
        }

        /**********************************************************
        CREATE
        ***********************************************************/

        //Request to create a new quick meal
        //-> (void)
        private function create()
        {
            //Load and store parameters
            $quickmeal = json_decode($this->Request->get('quickmeal'), true);

            //Create quick meal
            $QuickmealsLib = new QuickmealsLib($this->DB, $this->User);
            $quickmeal_id = $QuickmealsLib->create($quickmeal);
            if($quickmeal_id)   $this->responseSetParam('quickmeal_id', $quickmeal_id);
            else                throw new KookiizException('quickmeals', 4);
        }

        /**********************************************************
        DELETE
        ***********************************************************/

        //Request to delete an existing quick meal
        //-> (void)
        private function delete()
        {
            //Load and store parameters
            $quickmeal_id = (int)$this->Request->get('quickmeal_id');
            $this->responseSetParam('quickmeal_id', $quickmeal_id);

            //Delete quick meal
            $QuickmealsLib = new QuickmealsLib($this->DB, $this->User);
            $QuickmealsLib->delete($quickmeal_id);
        }
    }
?>