<?php
    /*******************************************************
    Title: API pictures
    Authors: Kookiiz Team
    Purpose: API module for pictures-related actions
    ********************************************************/

    //Dependencies
    require_once '../api/api.php';
    require_once '../class/pictures_lib.php';

    //Represents an API handler for pictures-related actions
    class PicturesAPI extends KookiizAPIHandler
    {
        /**********************************************************
        PROPERTIES
        ***********************************************************/

        const MODULE = 'pictures';

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
                case 'delete':
                    $this->delete();
                    break;
            }
        }

        /**********************************************************
        DELETE
        ***********************************************************/

        //Request for content on specific chef
        //-> (void)
        private function delete()
        {
            //Load and store parameters
            $type   = $this->Request->get('type');
            $pic_id = (int)$this->Request->get('pic_id');
            $this->responseSetParam('type', $type);
            $this->responseSetParam('pic_id', $pic_id);

            //Check user's authorizations
            $PicturesLib = new PicturesLib($this->DB, $this->User);
            if(!$this->User->isAdmin())
            {
                $owner = $PicturesLib->ownerGet($type, $pic_id);
                if($owner == -1 || $owner != $this->User->getID())
                {
                    //User is not authorized to delete this picture
                    throw new KookiizException('picture', 15);
                }
            }

            //Delete picture from library
            $PicturesLib->delete($type, $pic_id);
        }
    }
?>