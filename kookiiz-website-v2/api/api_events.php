<?php
    /*******************************************************
    Title: API events
    Authors: Kookiiz Team
    Purpose: API module for events-related actions
    ********************************************************/

    //Dependencies
    require_once '../api/api.php';
    require_once '../class/events_lib.php';
    require_once '../class/users_lib.php';

    //Represents an API handler for events-related actions
    class EventsAPI extends KookiizAPIHandler
    {
        /**********************************************************
        PROPERTIES
        ***********************************************************/

        const MODULE = 'events';

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

        //Request for the content of a specific article
        //-> (void)
        private function load()
        {
            //Load and store parameters
            $last_event = (int)$this->Request->get('last_event');
            $types      = json_decode($this->Request->get('types'));
            $this->responseSetParam('types', $types);

            //Load events
            $EventsLib  = new EventsLib($this->DB, $this->User);
            $UsersLib   = new UsersLib($this->DB, $this->User);
            $events     = $EventsLib->get($types, $last_event ? $last_event : 0);
            $users      = $EventsLib->getUsers($events);
            $content = array(
                'events'    => $events,
                'users'     => $UsersLib->export($users)
            );
            $this->responseSetContent($content);
            
            //Return last event parameter
            $this->responseSetParam('last_event', isset($events[0]) ? $events[0]['id'] : $last_event);
        }
    }
?>