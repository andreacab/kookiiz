<?php
    /*******************************************************
    Title: Events
    Authors: Kookiiz Team
    Purpose: Interface for Kookiiz events system
    ********************************************************/

    //Dependencies
    require_once '../class/dblink.php';
    require_once '../class/exception.php';
    require_once '../class/globals.php';
    require_once '../class/user.php';

    //Represents a library of events
    class EventsLib
    {
        /**********************************************************
        CONSTANTS
        ***********************************************************/

        //Class constants
        const FLUX_MAX              = 20;
        const TYPE_ADDRECIPE        = 1;
        const TYPE_COMMENTARTICLE   = 6;
        const TYPE_COMMENTRECIPE    = 5;
        const TYPE_NEWMEMBER        = 4;
        const TYPE_RATERECIPE       = 3;
        const TYPE_SHARERECIPE      = 0;
        const TYPE_SHARESHOPPING    = 7;
        const TYPE_SHARESTATUS      = 2;

        /**********************************************************
        PROPERTIES
        ***********************************************************/

        /** @var DBLink $DB database connection */
        private $DB;      
        /** @var User currently connected user */
        private $User;

        /**********************************************************
        CONSTRUCTOR
        ***********************************************************/

        /**
         * Class constructor
         * @param DBLink $DB open database connection
         * @param User $User connected user
         */
        public function __construct(DBLink &$DB, User &$User)
        {
            $this->DB   = $DB;
            $this->User = $User;
        }

        /**********************************************************
        CLEAN
        ***********************************************************/

        /**
         * Clean-up events library
         * Delete orphan events which point to deleted content
         * @return Array orphan event IDs
         */
        public function cleanOrphans()
        {
            $events_ids = array();

            //Shared recipes
            $request = 'SELECT event_id'
                    . ' FROM events'
                        . ' LEFT OUTER JOIN shared_recipes ON events.content_id = shared_recipes.share_id'
                    . ' WHERE shared_recipes.share_id IS NULL AND event_type = 0';
            $stmt = $this->DB->query($request);
            $events_ids = array_merge($events_ids, array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN, 0)));

            //New recipe
            $request = 'SELECT event_id'
                    . ' FROM events'
                        . ' LEFT OUTER JOIN recipes ON events.content_id = recipes.recipe_id'
                    . ' WHERE recipes.recipe_id IS NULL AND event_type = 1';
            $stmt = $this->DB->query($request);
            $events_ids = array_merge($events_ids, array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN, 0)));

            //Shared status
            $request = 'SELECT event_id'
                    . ' FROM events'
                        . ' LEFT OUTER JOIN shared_status ON events.content_id = shared_status.share_id'
                    . ' WHERE shared_status.share_id IS NULL AND event_type = 2';
            $stmt = $this->DB->query($request);
            $events_ids = array_merge($events_ids, array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN, 0)));

            //Recipes rated
            $request = 'SELECT event_id'
                    . ' FROM events'
                        . ' LEFT OUTER JOIN recipes_ratings ON events.content_id = recipes_ratings.rating_id'
                    . ' WHERE recipes_ratings.rating_id IS NULL AND event_type = 3';
            $stmt = $this->DB->query($request);
            $events_ids = array_merge($events_ids, array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN, 0)));

            //New member
            $request = 'SELECT event_id'
                    . ' FROM events'
                        . ' LEFT OUTER JOIN users ON events.content_id = users.user_id'
                    . ' WHERE users.user_id IS NULL AND event_type = 4';
            $stmt = $this->DB->query($request);
            $events_ids = array_merge($events_ids, array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN, 0)));

            //Commented recipe
            $request = 'SELECT event_id'
                    . ' FROM events'
                        . ' LEFT OUTER JOIN recipes_comments ON events.content_id = recipes_comments.comment_id'
                    . ' WHERE recipes_comments.comment_id IS NULL AND event_type = 5';
            $stmt = $this->DB->query($request);
            $events_ids = array_merge($events_ids, array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN, 0)));

            //Commented article
            $request = 'SELECT event_id'
                    . ' FROM events'
                        . ' LEFT OUTER JOIN articles_comments ON events.content_id = articles_comments.comment_id'
                    . ' WHERE articles_comments.comment_id IS NULL AND event_type = 6';
            $stmt = $this->DB->query($request);
            $events_ids = array_merge($events_ids, array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN, 0)));

            //Delete orphan events
            if(count($events_ids))
            {
                $request = 'DELETE FROM events WHERE event_id IN (' . implode(', ', $events_ids) . ')';
                $this->DB->query($request);
            }
            
            return $events_ids;
        }
        
        /**********************************************************
        COMPARE
        ***********************************************************/

        /**
         * Sort events by date (using their IDs)
         * @param Array $event_a first event to compare
         * @param Array $event_b second event to compare
         * @return Int sorting as -1 (a before b), 0 (no sorting), 1 (a after b)
         */
        public static function compare(array $event_a, array $event_b)
        {
            return (int)$event_a['id'] > (int)$event_b['id'] ? -1 : 1;
        }
        
        /**********************************************************
        GET
        ***********************************************************/

        /**
         * Get a table with all events grouped by types
         * @param Array $types event type IDs
         * @param Int $start_event search for events with a higher ID
         * @param Int $start_time search for events more recent than this timestamp
         * @return Array list of event IDs and related content IDs grouped by event type
         */
        public function get(array $types, $start_event = 0, $start_time = 0)
        {
            $events = array();
            if(!count($types)) return $events;

            //Retrieve user's friends
            $users_ids   = $this->User->friends_ids_get();
            $users_ids[] = $this->User->getID();	//Add user's own ID

            //Request for event IDs
            //For public events, 'public' field should be equal to 1
            //For private events, 'public' field should be equal to 0 AND event's user ID should be among user's friends or himself
            $request = 'SELECT event_id'
                    . ' FROM events'
                    . ' WHERE ((public = 1 AND lang = ?) || (public = 0 AND user_id IN (' . implode(', ', $users_ids) . ')))';
            $params = array($this->User->getLang());
            //Limit to events more recent than start_event
            if($start_event > 0)
            {
                $request .= ' AND event_id > ?';
                $params[] = $start_event;
            }
            //Limit to events more recent than start_time
            if($start_time > 0)
            {
                $request .= ' AND UNIX_TIMESTAMP(event_date) > ?';
                $params[] = $start_time;
            }
            //Limit to events of specified types
            if(count($types))
                $request .= ' AND event_type IN (' . implode(', ', $types) . ' )';
            //Order by most recent (highest ID) first and limit total
            $request .= ' ORDER BY event_id DESC LIMIT ?';
            $params[] = self::FLUX_MAX;

            //Send request
            $stmt = $this->DB->query($request, $params);
            $events_ids = array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN, 0));

            //Request type and content IDS for pre-selected events
            if(count($events_ids))
            {
                $request = 'SELECT event_type AS type,'
                            . ' GROUP_CONCAT(event_id) AS events_ids,'
                            . ' GROUP_CONCAT(content_id) AS contents_ids'
                        . ' FROM events'
                        . ' WHERE event_id IN (' . implode(', ', $events_ids) . ')'
                        . ' GROUP BY event_type';
                $stmt   = $this->DB->query($request);
                $events = $stmt->fetchAll();
            }

            //Return event objects
            return $this->load($events);
        }

        /**
         * Return IDs of users related to provided event objects
         * @param Array $events list of event objects
         * @return Array list of user IDs
         */
        public function getUsers(array $events)
        {
            $users_ids = array();
            foreach($events as $event)
            {
                //Store related user IDs
                $users_ids[] = (int)$event['user_id'];
                if(isset($event['friend_id'])) $users_ids[] = (int)$event['friend_id'];
            }
            return $users_ids;
        }
        
        /**********************************************************
        LOAD
        ***********************************************************/

        /**
         * Load content of events listed in provided groups
         * @param Array $groups list of events groups
         * @return Array list of loaded events
         */
        private function load(array $groups)
        {
            $events = array();

            //Retrieve content for each type of event
            foreach($groups as $group)
            {
                $type         = (int)$group['type'];
                $events_ids   = explode(',', $group['events_ids']);
                $contents_ids = $group['contents_ids'];
                switch($type)
                {
                    case self::TYPE_ADDRECIPE:
                        $request = 'SELECT IF(partner_id AND virtual, ' . C::PARTNER_DEFAULT . ', user_id) AS user_id,'
                                    . ' recipe_id, recipes.name AS recipe_name, recipes.pic_id AS pic,'
                                    . ' UNIX_TIMESTAMP(date_created) AS time,'
                                    . ' recipe_id AS content_ref'
                                . ' FROM users, recipes'
                                . ' WHERE user_id = author_id'
                                    . " AND recipe_id IN($contents_ids)";
                        $params = array();
                        break;
                    
                    case self::TYPE_COMMENTARTICLE:
                        $request = 'SELECT users.user_id, comment_text,'
                                    . ' article_id, article_title,'
                                    . ' UNIX_TIMESTAMP(comment_date) AS time,'
                                    . ' comment_id AS content_ref'
                                . ' FROM users, articles_comments, articles'
                                . ' WHERE users.user_id = articles_comments.user_id'
                                    . ' AND articles_comments.content_id = articles.article_id'
                                    . " AND comment_id IN($contents_ids)";
                        $params = array();
                        break;

                    case self::TYPE_COMMENTRECIPE:
                        $request = 'SELECT users.user_id, comment_text,'
                                    . ' recipe_id, recipes.name AS recipe_name, recipes.pic_id AS pic,'
                                    . ' UNIX_TIMESTAMP(comment_date) AS time,'
                                    . ' comment_id AS content_ref'
                                . ' FROM users, recipes_comments, recipes'
                                . ' WHERE users.user_id = recipes_comments.user_id'
                                    . ' AND recipes_comments.content_id = recipes.recipe_id'
                                    . " AND comment_id IN($contents_ids)";
                        $params = array();
                        break;

                    case self::TYPE_NEWMEMBER:
                        $request = 'SELECT user_id, pic_id AS pic,'
                                    . ' UNIX_TIMESTAMP(user_date) AS time,'
                                    . ' user_id AS content_ref'
                                . ' FROM users'
                                . " WHERE user_id IN($contents_ids)";
                        $params = array();
                        break;

                    case self::TYPE_RATERECIPE:
                        $request = 'SELECT users.user_id,'
                                    . ' recipes.recipe_id, recipes.name AS recipe_name,'
                                    . ' recipes.rating, recipes.pic_id AS pic,'
                                    . ' UNIX_TIMESTAMP(rating_date) AS time,'
                                    . ' rating_id AS content_ref'
                                . ' FROM users, recipes_ratings, recipes'
                                . ' WHERE users.user_id = recipes_ratings.user_id'
                                    . ' AND recipes_ratings.recipe_id = recipes.recipe_id'
                                    . " AND rating_id IN($contents_ids)";
                        $params = array();
                        break;

                    case self::TYPE_SHARERECIPE:
                        $request = 'SELECT user_1 AS user_id, users.user_id AS friend_id,'
                                    . ' recipes.recipe_id, recipes.name AS recipe_name, recipes.pic_id AS pic,'
                                    . ' UNIX_TIMESTAMP(share_date) AS time,'
                                    . ' share_id AS content_ref'
                                . ' FROM users, recipes, shared_recipes'
                                . ' WHERE IF(user_1 = ?, users.user_id = user_2, users.user_id = user_1)'
                                    . ' AND recipes.recipe_id = shared_recipes.recipe_id'
                                    . " AND share_id IN($contents_ids)";
                        $params = array($this->User->getID());
                        break;

                    case self::TYPE_SHARESHOPPING:
                        $request = 'SELECT 1 FROM shared_shopping WHERE 0';
                        $params = array();
                        break;

                    case self::TYPE_SHARESTATUS:
                        $request = 'SELECT users.user_id, status_type, status_comment,'
                                    . ' content_id, content_title, content_pic AS pic,'
                                    . ' UNIX_TIMESTAMP(status_date) AS time,'
                                    . ' share_id AS content_ref'
                                . ' FROM users, shared_status'
                                . ' WHERE shared_status.user_id = users.user_id'
                                    . " AND share_id IN($contents_ids)";
                        $params = array();
                        break;
                }
                $stmt = $this->DB->query($request, $params);

                //Loop through events data
                $contents_ids = explode(',', $contents_ids);
                while($event = $stmt->fetch(PDO::FETCH_ASSOC))
                {
                    //Assign ID and type to event
                    $content_ref    = (int)$event['content_ref'];
                    $content_index  = array_search($content_ref, $contents_ids);
                    $event['id']    = $events_ids[$content_index];
                    $event['type']  = $type;
                    unset($event['content_ref']);

                    //Store event data
                    $events[] = $event;
                }
            }

            //Sort and return events
            usort($events, array('EventsLib', 'compare'));
            return $events;
        }

        /**********************************************************
        REGISTER
        ***********************************************************/

        /**
         * Register an event in database
         * @param Int $user_id ID of the user that triggered the event
         * @param String $type identifier of the event type
         * @param Bool $public should the event be registered for anyone (true) or only user's friends
         * @param Int $content_id ID of the content related to the event (can be a recipe, article, etc.)
         * @param String $lang language identifier
         */
        public function register($user_id, $type, $public, $content_id, $lang = '')
        {
            //Check parameters
            $public = $public ? 1 : 0;
            if(!$user_id || !$content_id) return;

            //Insert new event
            $request = 'INSERT INTO events (event_type, user_id, content_id, public, lang)'
                    . ' VALUES(:type, :user_id, :content_id, :public, :lang)';
            $this->DB->query($request, array(
                ':type'         => $type,
                ':user_id'      => $user_id,
                ':content_id'   => $content_id,
                ':public'       => $public,
                ':lang'         => $lang
            ));
        }
    }
?>
