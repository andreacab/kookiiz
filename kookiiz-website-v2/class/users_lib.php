<?php
    /*******************************************************
    Title: Users library
    Authors: Kookiiz Team
    Purpose: Interact with users' database
    ********************************************************/

    //Dependencies
    require_once '../class/dblink.php';
    require_once '../class/email.php';
    require_once '../class/user.php';

    //Represents a library of users
    class UsersLib
    {
        /**********************************************************
        CONSTANTS
        ***********************************************************/

        const CHART_MAX     = 20;   //Max number of users on the chart
        const SEARCH_MAX    = 20;   //Max number of search results

        /**********************************************************
        PROPERTIES
        ***********************************************************/

        private $DB;    //database connection
        private $User;  //user connected to the library

        /**********************************************************
        CONSTRUCTOR
        ***********************************************************/

        //Class constructor
        //@DB (object):     open database connection
        //@User (object):   user connected to the library
        //-> (void)
        public function __construct(DBLink &$DB, User &$User)
        {
            //Set up properties
            $this->DB   = $DB;
            $this->User = $User;
        }

        /**********************************************************
        CHART
        ***********************************************************/

        //Return list of most active users
        //->users (array): list of user objects (most active first)
        public function chart()
        {
            /*
            $request = 'SELECT user_id FROM users'
                    . ' WHERE users.virtual = 0 AND users.deleted = 0 AND admin = 0'
                    . ' ORDER BY user_grade DESC LIMIT ' . self::CHART_MAX;
            $stmt       = $this->DB->query($request);
            $users_ids  = array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN, 0));
            */
            
            //Retrieve user's friends (including himself)
            $users_ids = $this->User->friends_ids_get();
            $users_ids[] = $this->User->getID();
            $users = $this->get($users_ids);

            //Sort users by grade
            usort($users, array('UserPublic', 'sortGrade'));
            return $users;
        }

        /**********************************************************
        CONFIRM
        ***********************************************************/

        //Confirm a user account
        //@user_id (int): unique user ID
        //-> (void)
        public function confirm($user_id)
        {
            $request = 'UPDATE users SET virtual = 0 WHERE user_id = ?';
            $this->DB->query($request, array($user_id));
        }
        
        /**********************************************************
        CREATE
        ***********************************************************/

        //Create a new user
        //@firstname (string):  user's first name
        //@lastname (string):   user's last name
        //@email (string):      user's email
        //@password (string):   hashed password
        //@pic_id (int):        ID of user's picture
        //@lang (string):       language identifier
        //@virtual (bool):      true if it is not a real user and should not be able to login (defaults to false)
        //->user_id (int): ID of the new user (0 in case of failure)
        public function create($firstname, $lastname, $email, $password, $pic_id, $lang, $virtual = false)
        {
            //Format data
            $virtual    = $virtual ? 1 : 0;
            $firstname  = ucfirst(strtolower($firstname));
            $lastname   = ucfirst(strtolower($lastname));

            //Insert new user in database
            $request = 'INSERT INTO users (firstname, lastname, name,'
                        . ' password, email, pic_id, last_visit, lang, virtual)'
                    . ' VALUES (:firstname, :lastname, :name,'
                        . ' :password, :email, :pic_id, NOW(), :lang, :virtual)';
            $params = array(
                ':firstname'    => $firstname,
                ':lastname'     => $lastname,
                ':name'         => "$firstname $lastname",
                ':password'     => $password,
                ':email'        => $email,
                ':pic_id'       => $pic_id,
                ':lang'         => $lang,
                ':virtual'      => $virtual
            );
            $stmt = $this->DB->query($request, $params);
            if($stmt->rowCount())
                return $this->DB->insertID();
            else
                return 0;
        }
        
        /**********************************************************
        ELECT
        ***********************************************************/

        //Elect user as admin
        //@user_id (int): ID of the user to elect
		//-> (void)
		public function elect_admin($user_id)
		{
			//Check if user is already an admin
			$request = 'SELECT admin FROM users WHERE user_id = ?';
			$stmt = $this->DB->query($request, array($user_id));
            $user = $stmt->fetch();
			if($user)
			{
				$admin = (int)$user['admin'];
				if(!$admin)
				{
					//Elect user as admin
					$request = 'UPDATE users SET admin = 1 WHERE user_id = ?';
					$this->DB->query($request, array($user_id));
				}
                //User is already an admin
				else 
                    throw new KookiizException('admin_users', 2);
			}
			//User does not exist
			else 
                throw new KookiizException('admin_users', 1);
		}

        //Elect user as partner
        //@user_id (int):       ID of the user to elect
        //@partner_id (int):    ID of the partner
        //->error (int): error code (0 = no error)
        public function elect_partner($user_id, $partner_id)
        {
            $request = 'UPDATE users SET partner_id = ? WHERE user_id = ?';
            $stmt = $this->DB->query($request, array($partner_id, $user_id));

            //Failed to elect user as a partner
            if(!$stmt->rowCount())
                throw new KookiizException('partners', 2);
        }

        /**********************************************************
        EXISTS
        ***********************************************************/

        //Check if there is a user account with provided email address
        //@email (string): the email address to check for
        //->exists (bool): true if an account exists
        public function existsEmail($email)
        {
            $request = 'SELECT 1 FROM users WHERE email = ?';
            $stmt = $this->DB->query($request, array($email));
            $data = $stmt->fetch();
            if($data)
                return true;
            else
                return false;
        }

        /**********************************************************
        EXPORT
        ***********************************************************/

        //Export public profile information for provided users
        //@users_ids (array): list of user IDs
        //->users (mixed): structure containing several arrays of user information
        public function export($users_ids)
        {
            $users = $this->get($users_ids);

            $users_data = array();
            foreach($users as $user)
            {
                $users_data[] = $user->export();
            }
            return $users_data;
        }

        /**********************************************************
        GET
        ***********************************************************/

        //Get user objects for provided IDs
        //@users_ids (array): list of user IDs
        //->users (array): list of user objects
        public function get($users_ids)
        {
            $users = array();
            $users_ids  = array_map('intval', array_values(array_unique($users_ids)));
            $friends    = $this->User->friends_ids_get();
            if(count($users_ids))
            {
                $request = 'SELECT user_id, facebook_id, IF(twitter_id IS NULL, 0, twitter_id) AS twitter_id,'
                            . ' firstname, lastname, email, user_grade, UNIX_TIMESTAMP(user_date) AS date,'
                            . ' users.pic_id, users.lang, GROUP_CONCAT(recipe_id) AS recipes'
                        . ' FROM users'
                            . ' LEFT JOIN users_twitter USING(user_id)'
                            . ' LEFT JOIN recipes ON users.user_id = recipes.author_id'
                        . ' WHERE user_id IN (' . implode(', ', $users_ids) . ')'
                        . ' GROUP BY users.user_id';
                $stmt = $this->DB->query($request);
                while($user = $stmt->fetch())
                {
                    $id = (int)$user['user_id'];
                    $props = array(
                        'id'        => $id,
                        'fb_id'     => (int)$user['facebook_id'],
                        'tw_id'     => (int)$user['twitter_id'],
                        'firstname' => htmlspecialchars($user['firstname'], ENT_COMPAT, 'UTF-8'),
                        'lastname'  => htmlspecialchars($user['lastname'], ENT_COMPAT, 'UTF-8'),
                        'email'     => in_array($id, $friends) ? $user['email'] : '',
                        'grade'     => (int)$user['user_grade'],
                        'pic_id'    => (int)$user['pic_id'],
                        'date'      => date('Y-m-d', $user['date']),
                        'lang'      => htmlspecialchars($user['lang'], ENT_COMPAT, 'UTF-8'),
                        'recipes'   => $user['recipes'] ? array_map('intval', explode(',', $user['recipes'])) : array()
                    );
                    $users[] = new UserPublic($props);
                }
            }
            return $users;
        }

        //Get a user object from its email
        //@email (string): user email
        //->user (object): user object (null if not found)
        public function getFromEmail($email)
        {
            $request = 'SELECT user_id FROM users WHERE email = ?';
            $stmt = $this->DB->query($request, array($email));
            $data = $stmt->fetch();
            if($data)
            {
                $user_id = (int)$data['user_id'];
                $users   = $this->get(array($user_id));
                return $users[0];
            }
            else
                return null;
        }

        //For each provided user ID, return recipient information (id, email, firstname, lastname)
        //  or false if type is provided and user chose not to receive this type of email
        //@ids (array): ID of the users for which to retrieve email information
        //@type (int):  email pattern type ID (optional)
        //->recipients (array): list of recipient information structures
        public function getRecipients(array $ids, $type = null)
        {
            $option = false;
            switch($type)
            {
                case EmailHandler::TYPE_FRIENDREQUEST:
                    $option = 'email_friendship';
                    break;
                case EmailHandler::TYPE_INVITATION:
                    $option = 'email_invitation';
                    break;
            }

            //Request recipients information
            $recipients = array();
            if(count($ids))
            {
                $ids = array_map('intval', $ids);
                if($option)
                {
                    $request = 'SELECT user_id, email, firstname, lastname,'
                                . " IF($option IS NULL, 1, $option) AS opt"
                            . ' FROM users'
                                . ' LEFT JOIN users_options USING(user_id)'
                            . ' WHERE users.user_id IN (' . implode(', ', $ids) . ')';
                }
                else
                {
                    $request = 'SELECT user_id, email, firstname, lastname,'
                                . ' 1 AS opt'
                            . ' FROM users'
                            . ' WHERE user_id IN (' . implode(', ', $ids) . ')';
                }
                $stmt = $this->DB->query($request);
                while($user = $stmt->fetch())
                {
                    //Store user if option is enabled
                    if((int)$user['opt']) 
                        $recipients[] = $user;
                }

            }
            return $recipients;
        }
        
        /**********************************************************
        GRADE
        ***********************************************************/

        //Add or remove cookies to specified user
        //@user_id (int):       ID of the user
        //@cookies_value (int): number of cookies to add or remove
        //@action (string):     either "add" or "remove"
        //->new_grade (int): updated grade of the user (false in case of failure)
        public function grade_update($user_id, $cookies_value, $action)
        {
            //Retrieve current grade
            $request = 'SELECT user_grade FROM users WHERE user_id = ?';
            $stmt = $this->DB->query($request, array($user_id));
            $data = $stmt->fetch();
            if($data)
            {
                //Modify grade
                $current_grade = $data['user_grade'];
                if($action == 'add')
                    $new_grade = $current_grade + (int)$cookies_value;
                else if($action == 'remove')
                    $new_grade = $current_grade - (int)$cookies_value;
                if($new_grade < 0)
                    $new_grade = 0;

                //Update grade in database
                $request = 'UPDATE users SET user_grade = ? WHERE user_id = ?';
                $stmt = $this->DB->query($request, array($new_grade, $user_id));
                if($stmt->rowCount())
                    return $new_grade;
                else
                    return false;
            }
            else return false;
        }

        /**********************************************************
        SEARCH
        ***********************************************************/

        //Search for users matching provided keyword
        //@keyword (string): string first or last names must match
        //->users (object): arrays of user properties
        public function search($keyword)
        {
            //Search for members names containing this keyword
            //Exclude current user
            $users = array();
            $request = 'SELECT user_id, name'
                    . ' FROM users'
                    . ' WHERE MATCH(name) AGAINST (? IN BOOLEAN MODE)'
                    .    ' AND user_id != ?'
                    .' LIMIT ' . self::SEARCH_MAX;
            $stmt = $this->DB->query($request, array("*$keyword*", $this->User->getID()));
            while($user = $stmt->fetch())
            {
                $users[] = array(
                    'i' => (int)$user['user_id'],
                    'n' => htmlspecialchars($user['name'], ENT_COMPAT, 'UTF-8')
                );
            }
            return $users;
        }
    }
?>
