<?php
    /*******************************************************
    Title: API invitations
    Authors: Kookiiz Team
    Purpose: API module for invitations-related actions
    ********************************************************/

    //Dependencies
    require_once '../api/api.php';
    require_once '../class/invitation.php';
    require_once '../class/invitations_lib.php';

    //Represents an API handler for invitations-related actions
    class InvitationsAPI extends KookiizAPIHandler
    {
        /**********************************************************
        PROPERTIES
        ***********************************************************/

        const MODULE = 'invitations';

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
                case 'respond':
                    $this->respond();
                    break;
                case 'save':
                    $this->save();
                    break;
            }
        }

        /**********************************************************
        CREATE
        ***********************************************************/

        //Request to create a new invitation
        //-> (void)
        private function create()
        {
            //Create invitation object
            $InvitationsLib = new InvitationsLib($this->DB, $this->User);
            $Invitation     = $InvitationsLib->create();

            //Return invitation ID and timestamp
            $this->responseSetParam('invitation_id', $Invitation->getID());
            $this->responseSetParam('time', $Invitation->getUpdate());
        }

        /**********************************************************
        DELETE
        ***********************************************************/

        //Request to delete an existing invitation
        //-> (void)
        private function delete()
        {
            //Load and store parameters
            $invitation_id = (int)$this->Request->get('invitation_id');
            $this->responseSetParam('invitation_id', $invitation_id);

            //Delete invitation from database
            $InvitationsLib = new InvitationsLib($this->DB, $this->User);
            $InvitationsLib->delete($invitation_id);
        }

        /**********************************************************
        RESPOND
        ***********************************************************/

        //Respond to an invitation
        //-> (void)
        private function respond()
        {
            //Load and store parameters
            $invitation_id  = (int)$this->Request->get('invitation_id');
            $status         = (int)$this->Request->get('status');
            $this->responseSetParam('invitation_id', $invitation_id);
            $this->responseSetParam('status', $status);

            //Respond to the invitation
            $InvitationsLib = new InvitationsLib($this->DB, $this->User);
            $InvitationsLib->respond($invitation_id, $status);
        }

        /**********************************************************
        SAVE
        ***********************************************************/

        //Save invitation content
        //-> (void)
        private function save()
        {
            //Load and store parameters
            $invitation_id  = (int)$this->Request->get('invitation_id');
            $invitation     = json_decode($this->Request->get('invitation'), true);
            $send           = (int)$this->Request->get('send');
            $this->responseSetParam('invitation_id', $invitation_id);
            $this->responseSetParam('send', $send);

            //Save invitation in database
            $InvitationsLib = new InvitationsLib($this->DB, $this->User);
            $Invitation = $InvitationsLib->save($invitation_id, $invitation);

            //Send invitation
            if($send) $InvitationsLib->send();

            //Store new invitation timestamp
            $this->responseSetParam('time', $Invitation->getUpdate());
        }
    }
?>