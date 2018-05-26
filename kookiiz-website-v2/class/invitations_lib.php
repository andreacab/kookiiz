<?php
    /*******************************************************
    Title: Invitations library
    Authors: Kookiiz Team
    Purpose: Manage dinner invitations
    ********************************************************/

    //Dependencies
    require_once '../class/dblink.php';
    require_once '../class/email.php';
    require_once '../class/exception.php';
    require_once '../class/globals.php';
    require_once '../class/invitation.php';
    require_once '../class/user.php';
    require_once '../class/users_lib.php';

    //Represents a library of invitations
    class InvitationsLib
    {
        /**********************************************************
        CONSTANTS
        ***********************************************************/

        const EXPIRY = 14;   //Period (in days) after which an invitation is considered as 'expired'

        /**********************************************************
        PROPERTIES
        ***********************************************************/

        private $DB;
        private $User;

        //Most recent invitation on which an action was performed
        private $Invitation = null; 

        /**********************************************************
        CONSTRUCTOR
        ***********************************************************/

        //Class constructor
        //@DB (object):     open database connection
        //@User (object):   user connected to the library
        //-> (void)
        public function __construct(DBLink &$DB, User &$User)
        {
            $this->DB   = $DB;
            $this->User = $User;
        }
        
        /**********************************************************
        ALERTS
        ***********************************************************/

        //Load invitation alerts (upcoming invitations and requests) for current user
        //->alerts (object): arrays of invitation alerts by categories
        public function alerts()
        {
            $alerts = array(
                'upcoming' => array(),
                'requests' => array()
            );

            //Upcoming invitations
            $invitations_ids = array();
            $request = 'SELECT invitations.invitation_id AS id, invitations.title,'
                        . ' invitations.location, UNIX_TIMESTAMP(invitations.time) AS time,'
                        . ' users.user_id, users.name,'
                        . ' invitations_guests.status, invitations_guests.viewed'
                    . ' FROM invitations'
                        . ' NATURAL JOIN invitations_guests'
                        . ' NATURAL JOIN users'
                    . ' WHERE (users.user_id = :user_id OR (guest_id = :guest_id AND status = :status))'
                    . ' HAVING time - UNIX_TIMESTAMP() > 0';
            $params = array(
                ':user_id'  => $this->User->getID(),
                ':guest_id' => $this->User->getID(),
                ':status'   => C::INV_STATUS_ACCEPT
            );
            $stmt = $this->DB->query($request, $params);
            while($invitation = $stmt->fetch())
            {
                $id = (int)$invitation['id'];
                if(!in_array($id, $invitations_ids))
                {
                    $alerts['upcoming'][] = array(
                        'id'        => $id,
                        'title'     => htmlspecialchars($invitation['title'], ENT_COMPAT, 'UTF-8'),
                        'location'  => htmlspecialchars($invitation['location'], ENT_COMPAT, 'UTF-8'),
                        'date'      => date('d.m.Y', (int)$invitation['time']),
                        'time'      => date('H:i', (int)$invitation['time']),
                        'user_id'   => (int)$invitation['user_id'],
                        'user_name' => htmlspecialchars($invitation['name'], ENT_COMPAT, 'UTF-8'),
                        'status'    => (int)$invitation['status'],
                        'viewed'    => (int)$invitation['viewed']
                    );
                }
            }

            //Requests
            $request = 'SELECT invitations.invitation_id AS id, invitations.title,'
                        . ' invitations.location, UNIX_TIMESTAMP(invitations.time) AS time,'
                        . ' users.user_id, users.name, invitations_guests.status, invitations_guests.viewed'
                    . ' FROM invitations'
                        . ' NATURAL JOIN (invitations_guests, users)'
                    . ' WHERE guest_id = :guest_id AND status != :status';
            $params = array(
                ':guest_id' => $this->User->getID(),
                ':status'   => C::INV_STATUS_NONE
            );
            $stmt = $this->DB->query($request, $params);
            while($invitation = $stmt->fetch())
            {
                $alerts['requests'][] = array(
                        'id'        => (int)$invitation['id'],
                        'title'     => htmlspecialchars($invitation['title'], ENT_COMPAT, 'UTF-8'),
                        'location'  => htmlspecialchars($invitation['location'], ENT_COMPAT, 'UTF-8'),
                        'date'      => date('d.m.Y', (int)$invitation['time']),
                        'time'      => date('H:i', (int)$invitation['time']),
                        'user_id'   => (int)$invitation['user_id'],
                        'user_name' => htmlspecialchars($invitation['name'], ENT_COMPAT, 'UTF-8'),
                        'status'    => (int)$invitation['status'],
                        'viewed'    => (int)$invitation['viewed']
                    );
            }

            //Return alerts data
            return $alerts;
        }
        
        /**********************************************************
        CREATE
        ***********************************************************/

        //Create a new blank invitation
        //->invitation (object): new invitation object
        public function create()
        {
            $Invitation = new Invitation();
            $Invitation->save($this->DB, $this->User);

            //Store pointer
            $this->Invitation = &$Invitation;

            //Return new invitation object
            return $Invitation;
        }

        /**********************************************************
        DELETE
        ***********************************************************/

        //Remove an existing invitation from database
        //@invitation_id (int): ID of the invitation to delete
        //->invitation (object): deleted invitation object
        public function delete($id)
        {
            $Invitation = new Invitation($id);
            $Invitation->load($this->DB);
            $Invitation->delete($this->DB, $this->User);

            //Store pointer
            $this->Invitation = &$Invitation;

            //Return invitation that was deleted
            return $Invitation;
        }

        /**********************************************************
        GET
        ***********************************************************/

        //Return a list of invitation objects
        //@ids (array): list of invitation IDs
        //->invitations (array): list of invitation objects
        public function get($ids)
        {
            return $this->load($ids);
        }

        //Load invitations list from database
        //-> (void)
        public function getList()
        {
            $invitations = array();
            if($this->User->isLogged())
            {
                //Retrieve invitations IDs
                $request = 'SELECT invitation_id'
                        . ' FROM invitations'
                            . ' LEFT JOIN invitations_guests USING (invitation_id)'
                        . ' WHERE (user_id = :user_id OR (guest_id = :guest_id AND status = :status))'
                            . ' AND UNIX_TIMESTAMP() - UNIX_TIMESTAMP(time) < :expiry';
                $params = array(
                    ':user_id'  => $this->User->getID(),
                    ':guest_id' => $this->User->getID(),
                    ':status'   => C::INV_STATUS_ACCEPT,
                    ':expiry'   => self::EXPIRY * 24 * 3600
                );
                $stmt = $this->DB->query($request, $params);
                $invitations = array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN, 0));
            }
            return $invitations;
        }

        /**********************************************************
        LOAD
        ***********************************************************/

        //Load a whole bunch of invitations at once
        //@ids (array): list of invitations ids
        //->invitations (array): list of invitation objects
        private function load($ids)
        {
            $invitations = array();
            $ids = array_map('intval', array_values(array_unique($ids)));
            if(count($ids))
            {
                //Retrieve invitation data
                $request = 'SELECT invitation_id AS id, user_id, title, text, location,'
                            . ' UNIX_TIMESTAMP(invitations.time) AS time,'
                            . ' UNIX_TIMESTAMP(invitations.updated) AS updated,'
                            . ' GROUP_CONCAT(guest_id) as guests,'
                            . ' GROUP_CONCAT(status) as statuses,'
                            . ' GROUP_CONCAT(recipe_id) as recipes'
                        . ' FROM invitations'
                            . ' LEFT JOIN invitations_guests USING (invitation_id)'
                            . ' LEFT JOIN invitations_recipes USING (invitation_id)'
                        . ' WHERE invitation_id IN (' . implode(', ', $ids) . ')'
                        . ' GROUP BY invitation_id';
                $stmt = $this->DB->query($request);

                //Loop through invitations
                while($inv = $stmt->fetch())
                {
                    //Retrieve ID
                    $id = (int)$inv['id'];

                    //Format guests
                    $guests = array();
                    if($inv['guests'])
                    {
                        $guests_ids     = explode(',', $inv['guests']);
                        $guests_status  = explode(',', $inv['statuses']);
                        foreach($guests_ids as $index => $guest_id)
                        {
                            $guests[] = array(
                                'i' => (int)$guest_id,
                                's' => (int)$guests_status[$index]
                            );
                        }
                    }
                    //Format recipes
                    $recipes = array();
                    if($inv['recipes'])
                    {
                        $recipes = explode(',', $inv['recipes']);
                    }

                    //Store current invitation
                    $data = array(
                        'user_id'   => (int)$inv['user_id'],
                        'title'     => $inv['title'],
                        'text'      => $inv['text'],
                        'location'  => $inv['location'],
                        'time'      => (int)$inv['time'],
                        'update'    => (int)$inv['updated'],
                        'guests'    => $guests,
                        'recipes'   => $recipes
                    );
                    $invitations[] = new Invitation($id, $data);
                }
            }
            return $invitations;
        }
        
        /**********************************************************
        RESPOND
        ***********************************************************/

        //Reply to an invitation
        //@id (int):        ID of the invitation
        //@status (int):    new guest status for current user
        //->Invitation (object): invitation object
        public function respond($id, $status)
        {
            $Invitation = new Invitation($id);
            $Invitation->load($this->DB);
            $Invitation->respond($this->DB, $this->User, $status);

            //Store pointer
            $this->Invitation = &$Invitation;

            //Return updated invitation object
            return $Invitation;
        }

        /**********************************************************
        SAVE
        ***********************************************************/
        
        //Save an existing invitation
        //@id (int):        invitation unique ID
        //@data (object):   invitation data
        //->Invitation (object): invitation object
        public function save($id, $data)
        {
            //Save invitation to database
            $Invitation = new Invitation($id);
            $Invitation->load($this->DB);
            $Invitation->save($this->DB, $this->User, $data);

            //Store pointer
            $this->Invitation = &$Invitation;

            //Return updated invitation object
            return $Invitation;
        }
        
        /**********************************************************
        SEND
        ***********************************************************/

        //Send an invitation
        //@id (int): ID of the invitation to send (defaults to current invitation)
        //->Invitation (object): invitation object
        public function send($id = 0)
        {
            if($id)
            {
                //Load invitation content from database
                $Invitation = new Invitation($id);
                $Invitation->load($this->DB);
            }
            //Retrieve most recent invitation object
            else
            {
                $Invitation = &$this->Invitation;
                if(is_null($Invitation)) return;
            }

            //Send invitation and retrieve target guests
            $guests = $Invitation->send($this->DB, $this->User);

            //Retrieve recipient information for corresponding users
            $UsersLib = new UsersLib($this->DB, $this->User);
            $recipients = $UsersLib->getRecipients($guests, EmailHandler::TYPE_INVITATION);

            //Send emails
            $EmailHandler = new EmailHandler($this->DB);
            foreach($recipients as $guest)
            {
                //Send invitation email to guest
                $params = array(
                    'content'       => 'text',
                    'recipient'     => $guest['email'],
                    'guest_name'    => $guest['firstname'],
                    'name'          => $this->User->getName()
                );
                $EmailHandler->pattern(EmailHandler::TYPE_INVITATION, $params);
            }

            //Return updated invitation object
            return $Invitation;
        }

        /**********************************************************
        UPDATED
        ***********************************************************/

        //Check which invitations have been updated in provided list
        //@ids (array):     list of invitation IDs
        //@times (array):   list of invitation times (in the same order)
        //->updated (array): IDs of invitations that have been updated
        public function updatedGet(array $ids, array $times)
        {
            //Look for updated invitations
            $updated = array();
            if(count($ids))
            {
                $request = 'SELECT invitation_id AS id, UNIX_TIMESTAMP(updated) AS time'
                        . ' FROM invitations WHERE invitation_id IN (' . implode(', ', $ids) . ')';
                $stmt = $this->DB->query($request);
                while($invitation = $stmt->fetch())
                {
                    $id     = (int)$invitation['id'];
                    $time   = (int)$invitation['time'];
                    $index  = array_search($id, $ids);
                    if($index !== false)
                    {
                        if($time > $times[$index]) $updated[] = $id;
                    }
                }
            }
            return $updated;
        }
    }
?>
