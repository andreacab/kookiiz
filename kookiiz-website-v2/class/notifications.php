<?php
    /*******************************************************
    Title: Notifications
    Authors: Kookiiz Team
    Purpose: Handler user notifications
    ********************************************************/

    //Dependencies
    require_once '../class/user.php';

    //Represents a handler for user notifications
    class NotifHandler
    {
        /**********************************************************
        CONSTANTS
        ***********************************************************/

        const INVITATION_ALERT_DELAY = 5;   //Number of days before the invitation for an alert to be thrown

        /**********************************************************
        PROPERTIES
        ***********************************************************/

        private $DB;
        private $User;

        /**********************************************************
        CONSTRUCTOR
        ***********************************************************/

        //Class constructor
        //@DB (object):     open database connection
        //@User (object):   connected user
        //-> (void)
        public function __construct(DBLink &$DB, User &$User)
        {
            $this->DB   = $DB;
            $this->User = $User;
        }
        
        /**********************************************************
        GET
        ***********************************************************/

        //Count notifications of every type which have not yet been viewed
        //->notifications (object): count of notifications for each type
        public function get()
        {
            $notifications = array(
                'friends'       => 0,
                'invitations'   => 0,
                'shared'        => 0
            );

            $user_id = $this->User->getID();
            if($user_id)
            {
                //Friendship requests
                $request = 'SELECT 1'
                        . ' FROM friends'
                        . ' WHERE user_2 = ?'
                            . ' AND valid = 0 AND blocked = 0';
                $stmt = $this->DB->query($request, array($user_id));
                $notifications['friends'] = count($stmt->fetchAll());

                //Invitations
                //Upcoming
                $request = 'SELECT DISTINCT invitation_id'
                        . ' FROM invitations'
                            . ' NATURAL JOIN invitations_guests'
                        . ' WHERE (user_id = :user_id || (guest_id = :guest_id AND status = :status))'
                            . ' AND time BETWEEN NOW() AND (NOW() + INTERVAL :delay DAY)';
                $params = array(
                    ':user_id'  => $user_id,
                    ':guest_id' => $user_id,
                    ':status'   => C::INV_STATUS_ACCEPT,
                    ':delay'    => self::INVITATION_ALERT_DELAY
                );
                $stmt = $this->DB->query($request, $params);
                $notifications['invitations'] += count($stmt->fetchAll());
                //Received
                $request = 'SELECT 1'
                        . ' FROM invitations_guests'
                        . ' WHERE guest_id = :user_id'
                            . ' AND status = :status'
                            . ' AND viewed = 0';
                $params = array(
                    ':user_id'  => $user_id,
                    ':status'   => C::INV_STATUS_SENT
                );
                $stmt = $this->DB->query($request, $params);
                $notifications['invitations'] += count($stmt->fetchAll());

                //Shared content
                $request = 'SELECT 1 FROM shared_recipes WHERE user_2 = ? AND viewed = 0';
                $stmt = $this->DB->query($request, array($user_id));
                $notifications['shared'] += count($stmt->fetchAll());
                $request = 'SELECT 1 FROM shared_shopping WHERE friend_id = ? AND viewed = 0';
                $stmt = $this->DB->query($request, array($user_id));
                $notifications['shared'] += count($stmt->fetchAll());
            }

            //Return notification counters
            return $notifications;
        }
    }
?>
