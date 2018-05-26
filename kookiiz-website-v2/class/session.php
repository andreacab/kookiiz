<?php
    /*******************************************************
    Title: Session handler
    Authors: Kookiiz Team
    Purpose: Load and manage user session
    ********************************************************/

    //Dependencies
    require_once '../class/dblink.php';
    require_once '../class/globals.php';

    //Represents a handler to load and manage user session and cookies
    //This is a static class, which cannot be instanciated
    class Session
    {
        /**********************************************************
        CONSTANTS
        ***********************************************************/

        const COOKIE_EXPIRY = 100;  //Number of days after which user has to log-in again
        const STATUS_LOGGED = 2;    //Session statuses
        const STATUS_NONE   = 0;
        const STATUS_VISIT  = 1;
        
        /**********************************************************
        CLEAR
        ***********************************************************/

        /**
         * Unset a given session value
         * @param String $key value label
         */
        public static function clear($key)
        {
            unset($_SESSION[$key]);
        }

        /**********************************************************
        COOKIE
        ***********************************************************/

        /**
         * Read session cookie value
         * @return String session key (empty string if no cookie is available)
         */
        private static function cookieGet()
        {
            return isset($_COOKIE['session_key']) ? $_COOKIE['session_key'] : '';
        }

        /**
         * Create session cookie for provided key
         * IMPORTANT
         * The 4th parameter of "setcookie" is the path.
         * If not set, it defaults to current path (even if it's a virtual path such as '/api/session' !!!)
         * If set to "/", it is available on the entire domain.
         * The VERY SAME path must be provided when deleting the cookie.
         * E.g. setting the path to "/" in cookieUnset will NOT remove a cookie with path = '/api/session/' !!!
         * @param String $key session key
         */
        private static function cookieSet($key)
        {
            setcookie('session_key', $key, (time() + 60 * 60 * 24 * self::COOKIE_EXPIRY), '/');
        }

        /**
         * Destroy session cookie
         */
        private static function cookieUnset()
        {
            setcookie(session_name(), '', time() - 3600, '/');
            setcookie('session_key', '', time() - 3600, '/');
        }

        /**********************************************************
        CREATE
        ***********************************************************/

        /**
         * Create user session
         * @param DBLink $DB database handler
         * @param Int $user_id ID of the user for which to create a session
         * @param String $lang language identifier
         * @param Bool $remember whether to create a cookie
         */
        public static function create(DBLink &$DB, $user_id, $lang, $remember = false)
        {      
            //Fetch session key
            $key = self::keyFetch($DB, $user_id);

            //Store profile in session
            $_SESSION['visitor'] = 0;
            $_SESSION['user_id'] = $user_id;
            $_SESSION['lang']    = $lang;
            $_SESSION['key']     = $key;

            //Set cookie
            if($remember) 
                self::cookieSet($key);
        }

        /**
         * Create a visitor session
         */
        public static function createVisitor()
        {
            $_SESSION['visitor'] = 1;
            $_SESSION['user_id'] = 0;
            $_SESSION['key']     = '';
        }
        
        /**********************************************************
        DESTROY
        ***********************************************************/

        /**
         * Erase session data
         * @param Bool $cookie whether to erase cookie as well
         */
        public static function destroy($cookie = true)
        {
            //Clear global variables (while keeping the language setting)
            $lang = $_SESSION['lang'];
            $_SESSION = array();
            $_SESSION['lang'] = $lang;

            //Erase the session cookie
            if($cookie)
                self::cookieUnset();

            //Erase session data
            session_destroy();

            //Restart session
            self::release();
            self::start();
        }

        /**********************************************************
        GET
        ***********************************************************/

        /**
         * Get session param value
         * @param String $key param label
         * @return Mixed param value
         */
        public static function get($key)
        {
            return isset($_SESSION[$key]) ? $_SESSION[$key] : null;
        }

        /**
         * Get user ID stored in session
         * @return Int unique user ID or 0 (none)
         */
        public static function getID()
        {
            $id = self::get('user_id');
            return is_null($id) ? 0 : $id;
        }
 
        /**
         * Return current session key
         * @return String current session key
         */
        public static function getKey()
        {
            return $_SESSION['key'];
        }

        /**
         * Get current session language
         * @return String language identifier
         */
        public static function getLang()
        {
            return self::get('lang');
        }

        /**
         * Return current session status
         * @return Int session status code
         */
        public static function getStatus()
        {
            if(!isset($_SESSION['user_id']))
                return self::STATUS_NONE;
            else if(!$_SESSION['user_id'])
                return self::STATUS_VISIT;
            else
                return self::STATUS_LOGGED;
        }

        /**********************************************************
        KEY
        ***********************************************************/

        /**
         * Fetch session key from database or insert new one for provided user ID
         * @param DBLink $DB database handler
         * @param Int $user_id unique user ID
         * @return String session key
         */
        private static function keyFetch(DBLink &$DB, $user_id)
        {
            //Delete expired session keys
            $request = 'DELETE FROM sessions_keys'
                    . ' WHERE user_id = ?'
                        . ' AND DATEDIFF(CURDATE(), date) > ?';
            $DB->query($request, array($user_id, self::COOKIE_EXPIRY));

            //Try to retrieve existing key
            $request = 'SELECT session_key'
                    . ' FROM sessions_keys'
                    . ' WHERE user_id = ?';
            $stmt = $DB->query($request, array($user_id));
            $data = $stmt->fetch();
            if($data)
            {
                //Retrieve existing key
                $key = $data['session_key'];

                //Update timestamp in database
                $request = 'UPDATE sessions_keys'
                            . ' SET date = NOW()'
                        . ' WHERE user_id = ?'
                            . ' AND session_key = ?';
                $stmt = $DB->query($request, array($user_id, $key));
            }
            else
            {
                //Create new key
                $key = md5(uniqid());

                //Store key in database
                $request = 'INSERT INTO sessions_keys'
                        . ' (user_id, session_key) VALUES (?, ?)';
                $DB->query($request, array($user_id, $key));
            }

            //Return session key
            return $key;
        }

        /**********************************************************
        LANG
        ***********************************************************/

        /**
         * Return session default language
         * @return String language identifier
         */
        private static function langDefault()
        {
            //Retrieve browser setting
            $lang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
            //$lang = 'fr';   //Default to french!!!
            //Check if selected language is supported or set default
            if(!in_array($lang, C::get('LANGUAGES')))
                $lang = C::LANG_DEFAULT;
            //Return default language
            return $lang;
        }

        /**********************************************************
        LOGIN
        ***********************************************************/

        /**
         * Try to log user in using provided email/password combination
         * @param String $email user email
         * @param String $password hashed password
         * @param Bool $remember if true create cookie
         * @param Bool $virtual force login for virtual user
         * @return Int ID of the logged user (0 if login failed)
         */
        public static function login($email, $password, $remember = false, $virtual = false)
        {
            //Init DB handler
            $DB = new DBLink('kookiiz');

            //Try to retrieve user data
            $request = 'SELECT user_id, lang'
                    . ' FROM users'
                    . ' WHERE email = ? AND password = ? AND deleted = 0';
            if(!$virtual)
                $request .= ' AND virtual = 0';
            $stmt = $DB->query($request, array($email, $password));
            $data = $stmt->fetch();

            //Try to create session
            if($data)
            {
                $user_id = (int)$data['user_id'];
                self::create($DB, $user_id, $data['lang'], $remember);

                //Return user ID
                return $user_id;
            }
            //Wrong email/password combination
            else return 0;
        }

        /**
         * Log user in using a Facebook ID
         * @param Int $fb_id unique Facebook ID
         * @param Bool $remember if true create cookie
         * @return Int ID of the logged user (0 if login failed)
         */
        public static function loginFB($fb_id, $remember = false)
        {
            //Init DB handler
            $DB = new DBLink('kookiiz');

            //Try to retrieve user data
            $request = 'SELECT user_id, lang'
                    . ' FROM users'
                    . ' WHERE facebook_id = ?'
                        . ' AND virtual = 0 AND deleted = 0';
            $stmt = $DB->query($request, array($fb_id));
            $data = $stmt->fetch();

            //Try to create session
            if($data)
            {
                $user_id = (int)$data['user_id'];
                self::create($DB, $user_id, $data['lang'], $remember);

                //Return user ID
                return $user_id;
            }
            //Unknown Facebook ID
            else return 0;
        }
        
        /**
         * Login using user ID
         * @param Int $user_id unique user ID
         * @param Bool $remember if true create cookie
         * @return Int ID of the logged user (0 if login failed)
         */
        public static function loginID($user_id, $remember = false)
        {
            //Init DB handler
            $DB = new DBLink('kookiiz');
            
            //Try to retrieve user data
            $request = 'SELECT user_id, lang'
                    . ' FROM users'
                    . ' WHERE user_id = ?'
                        . ' AND virtual = 0 AND deleted = 0';
            $stmt = $DB->query($request, array($user_id));
            $data = $stmt->fetch();

            //Try to create session
            if($data)
            {
                $user_id = (int)$data['user_id'];
                self::create($DB, $user_id, $data['lang'], $remember);

                //Return user ID
                return $user_id;
            }
            //Unknown Facebook ID
            else return 0;
        }

        /**
         * Login using session key
         * @param String $key session key (defaults to current)
         * @param Bool $remember if true create cookie
         * @return Int ID of the logged user (0 if login failed)
         */
        public static function loginKey($key = '', $remember = false)
        {
            //Init DB handler
            $DB = new DBLink('kookiiz');

            //Key is not specified -> fetch it from cookie
            if(!$key) $key = self::getKey();

            //Retrieve user ID from session key
            $request = 'SELECT user_id, lang'
                    . ' FROM users'
                        .' NATURAL JOIN sessions_keys'
                    . ' WHERE session_key = ?'
                        . ' AND DATEDIFF(CURDATE(), date) <= ?'
                        . ' AND virtual = 0 AND deleted = 0';
            $stmt = $DB->query($request, array($key, self::COOKIE_EXPIRY));
            $data = $stmt->fetch();

            //Try to create session
            if($data)
            {
                $user_id = (int)$data['user_id'];
                self::create($DB, $user_id, $data['lang'], $remember);

                //Return user ID
                return $user_id;
            }
            //No session key
            else return 0;
        }

        /**
         * Login using Twitter ID
         * @param Int $tw_id unique Twitter ID
         * @param Bool $remember if true create cookie 
         * @return Int ID of the logged user (0 if login failed)
         */
        public static function loginTW($tw_id, $remember = false)
        {
            //Init DB handler
            $DB = new DBLink('kookiiz');

            //Try to retrieve user data
            $request = 'SELECT user_id, lang'
                    . ' FROM users'
                        . ' NATURAL JOIN users_twitter'
                    . ' WHERE twitter_id = ?'
                        . ' AND virtual = 0 AND deleted = 0';
            $stmt = $DB->query($request, array($tw_id));
            $data = $stmt->fetch();

            //Try to create session
            if($data)
            {
                $user_id = (int)$data['user_id'];
                self::create($DB, $user_id, $data['lang'], $remember);

                //Return user ID
                return $user_id;
            }
            //Unknown Twitter ID
            else return 0;
        }

        /**********************************************************
        RELEASE
        ***********************************************************/

        /**
         * Release session data
         */
        public static function release()
        {
            session_write_close();
        }

        /**********************************************************
        SET
        ***********************************************************/
 
        /**
         * Set a session value
         * @param String $key value label
         * @param Mixed $value value to set
         */
        public static function set($key, $value)
        {
            $_SESSION[$key] = $value;
        }

        /**********************************************************
        START
        ***********************************************************/

        /**
         * Init session data
         */
        public static function start()
        {
            //Start session (if it's not already)
            if(!session_id())
            {
                session_start();

                //Ensure that session language is defined
                if(!isset($_SESSION['lang']))
                    $_SESSION['lang'] = self::langDefault();

                //Try to retrieve key from cookies
                if(!isset($_SESSION['key']) || !$_SESSION['key'])
                    $_SESSION['key'] = self::cookieGet();

                //Login if key is available and user is not logged
                if(self::getKey() && (self::getStatus() != self::STATUS_LOGGED))
                    self::loginKey();
            }
        }
    }
?>
