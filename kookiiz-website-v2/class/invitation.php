<?php
    /*******************************************************
    Title: Invitation
    Authors: Kookiiz Team
    Purpose: Describe an invitation between users
    ********************************************************/

    //Dependencies
    require_once '../class/dblink.php';
    require_once '../class/exception.php';
    require_once '../class/globals.php';
    require_once '../class/user.php';

    //Represents a dinner invitation
    class Invitation
    {
        /**********************************************************
        PROPERTIES
        ***********************************************************/

        private $id;
        private $location;
        private $text;
        private $time;
        private $title;
        private $update;
        private $user_id;

        private $guests     = array();
        private $recipes    = array();

        /**********************************************************
        CONSTRUCTOR
        ***********************************************************/

        //Class constructor
        //@id (int):        invitation unique ID (defaults to 0 = none)
        //@data (object):   invitation data structure (optional)
        //-> (void)
        public function __construct($id = 0, $data = null)
        {
            $this->id = $id;
            if(is_null($data))
            {
                $this->user_id  = 0;
                $this->location = '';
                $this->text     = '';
                $this->title    = '';

                //Set-up time
                $default_date   = date('Y-m-d');
                $default_time   = C::INV_HOUR_DEFAULT . ':' . C::INV_MINUTE_DEFAULT;
                $this->time     = $default_date . ' ' . $default_time;
                
                //Current timestamp
                $this->update   = time();
            }
            else $this->import($data);
        }

        /**********************************************************
        DELETE
        ***********************************************************/

        //Delete invitation from database
        //@DB (object):     database link
        //@User (object):   profile of user performing the deletion
        //-> (void)
        public function delete(DBLink &$DB, User &$User)
        {
            //Abort if invitation has no ID
            if(!$this->id) return;
            //Abort if user is not the owner
            if($this->user_id != $User->getID())
            {
                throw new KookiizException('invitations', 1);
            }

            //Delete invitation, as well as related guests and recipes
            $request = 'DELETE FROM invitations'
                    . ' WHERE invitation_id = ?';
            $DB->query($request, array($this->id));
            $this->delete_guests($DB);
            $this->delete_recipes($DB);
        }

        //Delete invitation guests
        //@DB (object): database link
        //-> (void)
        private function delete_guests(DBLink &$DB)
        {
            $request = 'DELETE FROM invitations_guests'
                    . ' WHERE invitation_id = ?';
            $DB->query($request, array($this->id));
        }

        //Delete invitation recipes
        //@DB (object): database link
        //-> (void)
        private function delete_recipes(DBLink &$DB)
        {
            $request = 'DELETE FROM invitations_recipes'
                    . ' WHERE invitation_id = ?';
            $DB->query($request, array($this->id));
        }

        /**********************************************************
        GET
        ***********************************************************/

        //Return list of guest IDs
        //@status (int): restrict to guests with a specific status (optional)
        //->guests (array): list of user IDs
        public function getGuests($status = null)
        {
            $ids = array();
            foreach($this->guests as $guest)
            {
                if(is_null($status) || $guest['status'] == $status)
                {
                    $ids[] = $guest['id'];
                }
            }
            return $ids;
        }

        //Return invitation ID
        //->id (int): unique invitation ID
        public function getID()
        {
            return $this->id;
        }

        //Return owner ID
        //->user_id (int): unique user ID
        public function getOwner()
        {
            return $this->user_id;
        }

        //Return list of related recipes
        //->recipes (array): list of recipe IDs
        public function getRecipes()
        {
            return $this->recipes;
        }

        //Return current invitation timestamp
        //->update (int): invitation timestamp
        public function getUpdate()
        {
            return $this->update;
        }

        //Return list of related users
        //->users (array): list of user IDs
        public function getUsers()
        {
            $users = array($this->user_id);
            foreach($this->guests as $guest)
            {
                $users[] = $guest['id'];
            }
            return $users;
        }

        /**********************************************************
        EXPORT
        ***********************************************************/

        //Export invitation content
        //->content (object): compact invitation content
        public function export()
        {
            return array(
                'id'        => $this->id,
                'user_id'   => $this->user_id,
                'location'  => htmlspecialchars($this->location, ENT_COMPAT, 'UTF-8'),
                'text'      => htmlspecialchars($this->text, ENT_COMPAT, 'UTF-8'),
                'title'     => htmlspecialchars($this->title, ENT_COMPAT, 'UTF-8'),
                'time'      => strtotime($this->time),
                'update'    => $this->update,
                'guests'    => $this->exportGuests(),
                'recipes'   => $this->exportRecipes()
            );
        }

        //Export invitation guests
        //->guests (array): list of compact guest data structures
        private function exportGuests()
        {
            $guests = array();
            foreach($this->guests as $guest)
            {
                $guests[] = array(
                    'i' => $guest['id'],
                    's' => $guest['status']
                );
            }
            return $guests;
        }

        //Export invitation recipes
        //->recipes (array): list of recipe IDs
        private function exportRecipes()
        {
            return $this->recipes;
        }

        /**********************************************************
        IMPORT
        ***********************************************************/

        //Import invitation data
        //@data (object): invitation data structure with the following parameters:
        //  @user_id (int):     owner ID
        //  @location (string): invitation location
        //  @text (string):     invitation text
        //  @title (string):    invitation title
        //  @time (int):        invitation date and time as a UNIX timestamp
        //  @update (int):      last invitation update timestamp
        //  @guests (array):    list of guest ID/status pairs ("i"/"s")
        //  @recipes (array):   list of recipe IDs
        //-> (void)
        private function import($data)
        {
            //User ID is updated ONLY if there is none yet
            if(!$this->user_id) $this->user_id = (int)$data['user_id'];

            //Import properties
            $this->location = substr($data['location'], 0, C::INV_LOCATION_MAX);
            $this->text     = substr($data['text'], 0, C::INV_TEXT_MAX);
            $this->title    = substr($data['title'], 0, C::INV_TITLE_MAX);
            $this->time     = date('Y-m-d H:i', (int)$data['time']);
            $this->update   = (int)$data['update'];

            //Import guests list
            $this->guests = array();
            foreach($data['guests'] as $guest)
            {
                $this->guests[] = array(
                    'id'        => (int)$guest['i'],
                    'status'    => (int)$guest['s']
                );
            }

            //Import recipes list
            $this->recipes = array_map('intval', $data['recipes']);
        }

        /**********************************************************
        LOAD
        ***********************************************************/

        //Load invitation content from database
        //@DB (object): database link
        //-> (void)
        public function load(DBLink &$DB)
        {
            //Abort if this invitation has no ID
            if(!$this->id) return;

            //Request for content
            $request = 'SELECT user_id, title, text, location,'
                        . ' UNIX_TIMESTAMP(invitations.time) AS time,'
                        . ' UNIX_TIMESTAMP(invitations.updated) AS updated,'
                        . ' GROUP_CONCAT(guest_id) as guests,'
                        . ' GROUP_CONCAT(status) as statuses,'
                        . ' GROUP_CONCAT(recipe_id) as recipes'
                    . ' FROM invitations'
                        . ' LEFT JOIN invitations_guests USING (invitation_id)'
                        . ' LEFT JOIN invitations_recipes USING (invitation_id)'
                    . ' WHERE invitation_id = ?'
                    . ' GROUP BY invitation_id';
            $stmt = $DB->query($request, array($this->id));
            $data = $stmt->fetch();
            if($data)
            {
                //Format guests
                $guests = array();
                if($data['guests'])
                {
                    $guests_ids     = explode(',', $data['guests']);
                    $guests_status  = explode(',', $data['statuses']);
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
                if($data['recipes'])
                {
                    $recipes = explode(',', $data['recipes']);
                }

                //Import data
                $params = array(
                    'user_id'   => (int)$data['user_id'],
                    'title'     => $data['title'],
                    'text'      => $data['text'],
                    'location'  => $data['location'],
                    'time'      => (int)$data['time'],
                    'update'    => (int)$data['updated'],
                    'guests'    => $guests,
                    'recipes'   => $recipes
                );
                $this->import($params);
            }
        }

        /**********************************************************
        RESPOND
        ***********************************************************/

        //Send response to invitation
        //@DB (object):     database link
        //@User (object):   responding user's profile
        //@status (int):    new guest status
        //-> (void)
        public function respond(DBLink &$DB, User &$User, $status)
        {
            $request = 'UPDATE invitations_guests'
                        . ' SET status = :status'
                    . ' WHERE invitation_id = :inv_id'
                        . ' AND guest_id = :guest_id';
            $params = array(
                ':status'   => $status,
                ':inv_id'   => $this->id,
                ':guest_id' => $User->getID()
            );
            $stmt = $DB->query($request, $params);

            //Update invitation timestamp
            if($stmt->rowCount()) $this->update($DB);
            else
            {
                //Failed to update guest status
                throw new KookiizException('invitations', 3);
            }
        }

        /**********************************************************
        SAVE
        ***********************************************************/

        //Save invitation in database
        //@DB (object):     database link
        //@User (object):   profile of user performing the save
        //@data (object):   invitation data to import (optional)
        //-> (void)
        public function save(DBLink &$DB, User &$User, array $data = null)
        {
            //Invitation content update
            if($this->id)
            {
                //Abort if user is not the owner
                if($this->user_id != $User->getID())
                {
                    throw new KookiizException('session', 2);
                }

                //Import updated data
                if(!is_null($data)) $this->import($data);

                //Check ownership
                $request = 'SELECT 1 FROM invitations'
                        . ' WHERE invitation_id = ? AND user_id = ?';
                $stmt = $DB->query($request, array($this->id, $this->user_id));
                $data = $stmt->fetch();
                if(!$data) return;

                //Update invitation data
                $request = 'UPDATE invitations'
                            . ' SET title = :title, text = :text,'
                                . ' location = :location, time = :time,'
                                . ' updated = FROM_UNIXTIME(:update)'
                        . ' WHERE invitation_id = :inv_id';
                $params = array(
                    ':title'        => $this->title,
                    ':text'         => $this->text,
                    ':location'     => $this->location,
                    ':time'         => $this->time,
                    ':update'       => $this->update,
                    ':inv_id'       => $this->id
                );
                $DB->query($request, $params);

                //Save guests and recipes lists
                $this->saveGuests($DB, $User);
                $this->saveRecipes($DB);
            }
            //Invitation is saved for the first time
            else
            {
                //Set current user as the owner
                $this->user_id = $User->getID();

                //Insert new invitation in database
                $request = 'INSERT INTO invitations (user_id, title, text, location, time, updated)'
                            . ' VALUES(:user_id, :title, :text, :location, :time, :update)';
                $params = array(
                    ':user_id'      => $this->user_id,
                    ':title'        => $this->title,
                    ':text'         => $this->text,
                    ':location'     => $this->location,
                    ':time'         => $this->time,
                    ':update'       => $this->update
                );
                $DB->query($request, $params);
                $this->id = $DB->insertID();
            }
        }

        //Save invitation guests list
        //@DB (object):     database link
        //@User (object):   profile of user performing the save
        //-> (void)
        private function saveGuests(DBLink &$DB, User &$User)
        {
            //Check that all guests are among user's friends
            $friends = $User->friends_ids_get();
            foreach($this->guests as &$guest)
            {
                if(!in_array($guest['id'], $friends))
                {
                    $guest = null;
                }
            }
            unset($guest); //Remove reference!!!
            $this->guests = array_values($this->guests);

            //Remove guests that are not anymore on the list
            $guests_ids = $this->getGuests();
            $request = 'DELETE FROM invitations_guests'
                    . ' WHERE invitation_id = ?';
            if(count($guests_ids))
            {
                $request .= ' AND guest_id NOT IN (' . implode(', ', $guests_ids) . ')';
            }
            $DB->query($request, array($this->id));

            //Store current guests
            if(count($this->guests))
            {
                //Insert new guests
                $request = 'INSERT IGNORE INTO invitations_guests (invitation_id, guest_id, status)'
                            . ' VALUES (:inv_id, :guest_id, :status)';
                $params = array();
                foreach($this->guests as $guest)
                {
                    $params[] = array(
                        ':inv_id'   => $this->id,
                        ':guest_id' => $guest['id'],
                        ':status'   => $guest['status']
                    );
                }
                $DB->query($request, $params);
            }
        }

        //Save invitation recipes list
        //@DB (object): database link
        //-> (void)
        private function saveRecipes(DBLink &$DB)
        {
            //Delete existing recipes
            $request = 'DELETE FROM invitations_recipes'
                    . ' WHERE invitation_id = ?';
            $DB->query($request, array($this->id));

            //Save current recipes
            if(count($this->recipes))
            {
                $request = 'INSERT IGNORE INTO invitations_recipes'
                        . ' (invitation_id, recipe_id) VALUES (?, ?)';
                $params = array();
                foreach($this->recipes as $recipe_id)
                {
                    $params[] = array($this->id, $recipe_id);
                }
                $DB->query($request, $params);
            }
        }

        /**********************************************************
        SEND
        ***********************************************************/

        //Send invitation
        //@DB (object):     database link
        //@User (object):   profile of user performing the save
        //->guests (array): ID of users to which the invitation has been sent
        public function send(DBLink &$DB, User &$User)
        {
            //Abort if invitation has no ID
            if(!$this->id) return;
            //Abort if user is not the owner
            if($this->user_id != $User->getID())
            {
                throw new KookiizException('session', 2);
            }

            //Retrieve guests for which no invitation has been sent yet
            $guests = $this->getGuests(C::INV_STATUS_NONE);
            if(count($guests))
            {
                //Set status as "sent"
                $request = 'UPDATE invitations_guests'
                            . ' SET status = ' . C::INV_STATUS_SENT
                        . ' WHERE invitation_id = ?'
                            . ' AND guest_id IN(' . implode(', ', $guests) . ')';
                $DB->query($request, array($this->id));
            }

            //Set invitation as updated
            $this->update($DB);

            //Return guests list
            return $guests;
        }

        /**********************************************************
        UPDATE
        ***********************************************************/

        //Set invitation update timestamp to now
        //@DB (object): database link
        //-> (void)
        private function update(DBLink &$DB)
        {
            $this->update = time();
            if($this->id)
            {
                $request = 'UPDATE invitations'
                            . ' SET updated = FROM_UNIXTIME(?)'
                        . ' WHERE invitation_id = ?';
                $DB->query($request, array($this->update, $this->id));
            }
        }
    }
?>
