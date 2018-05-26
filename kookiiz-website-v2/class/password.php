<?php
    /*******************************************************
    Title: Password
    Authors: Kookiiz Team
    Purpose: Generate password hashes and manage salts
    ********************************************************/

    //Dependencies
    require_once '../class/dblink.php';
    require_once '../class/globals.php';

    //Represents a handler for password hash and salts management
    class PasswordHandler
    {
        /**********************************************************
        PROPERTIES
        ***********************************************************/

        const SALT_LENGTH = 10;

        /**********************************************************
        CONSTRUCTOR
        ***********************************************************/

        //Class constructor
        //-> (void)
        public function __construct()
        {
        }

        /**********************************************************
        CHECK
        ***********************************************************/

        //Check if password matches provided user ID
        //@DB (object):     open database connection
        //@user_id (int):   unique user ID
        //@hash (string):   hashed password
        //->match (bool): true if password is correct
        public function check(DBLink &$DB, $user_id, $hash)
        {
            $request = 'SELECT 1 FROM users WHERE user_id = ? AND password = ?';
			$stmt = $DB->query($request, array($user_id, $hash));
            $data = $stmt->fetch();
            return $data != false;
        }

        /**********************************************************
        HASH
        ***********************************************************/

        //Generate password hash
        //@password (string):   plain text password
        //@salt (string):       salt of the password (a new one will be generated if not provided)
        //->hash (string): hashed password + salt
        public function hash($password, $salt = null)
        {
            if(is_null($salt))
            {
                $salt = substr(md5(uniqid(rand(), $entropy = true)), $start = 0, self::SALT_LENGTH);
            }
            else
            {
                $salt = substr($salt, 0, self::SALT_LENGTH);
            }

            return $hash = $salt . hash('whirlpool', $salt . $password);
        }

        /**********************************************************
        SALT
        ***********************************************************/

        //Get salt from database for provided email
        //@DB (object):     open database connection
        //@email (string):  user email address
        //->salt (string): password salt (empty string if not found)
        function salt_from_email(DBLink &$DB, $email)
        {
            $request    = 'SELECT password FROM users WHERE email = ?';
            $stmt       = $DB->query($request, array($email));
            $user_data  = $stmt->fetch();
            if($user_data)  return $user_data['password'];
            else            return '';
        }

        //Get salt from database for provided user ID
        //@db (object):     open database connection
        //@user_id (int):   user unique ID
        //->salt (string): password salt (empty string if not found)
        function salt_from_id(DBLink &$DB, $user_id)
        {
            $request    = 'SELECT password FROM users WHERE user_id = ?';
            $stmt       = $DB->query($request, array($user_id));
            $user_data  = $stmt->fetch();
            if($user_data)  return $user_data['password'];
            else            return '';
        }

        /**********************************************************
        TEMPORARY PASSWORD
        ***********************************************************/

        //Generate a temporary password for provided user
        //@DB (object):     open database connection
        //@user_id (int):   user unique ID
        //->password (string): temporary password (before hashing)
        public function tempCreate(DBLink &$DB, $user_id)
        {
            //Generate a new password and corresponding hash
            $password   = substr(md5(uniqid()), 0, C::USER_PASSWORD_MIN);
            $hash       = $this->hash($password);

            //Store it in database
            $request = 'INSERT INTO password_reset (user_id, password) VALUES (?, ?)'
                     . ' ON DUPLICATE KEY UPDATE password = VALUES(password)';
            $DB->query($request, array($user_id, $hash));

            //Return temporary password
            return $password;
        }

        //Get temporary password from user ID
        //@DB (object):     open database connection
        //@user_id (int):   user unique ID
        //->password (string): hashed temporary password or empty sring (if not found)
        public function tempGet(DBLink &$DB, $user_id)
        {
            $request = 'SELECT password FROM password_reset WHERE user_id = ?';
            $stmt = $DB->query($request, array($user_id));
            $data = $stmt->fetch();
            return $data ? $data['password'] : '';
        }

        //Remove temporary password from database
        //@DB (object):     open database connection
        //@user_id (int):   user unique ID
        //-> (void)
        public function tempRemove(DBLink &$DB, $user_id)
        {
            $request = 'DELETE FROM password_reset WHERE user_id = ?';
            $DB->query($request, array($user_id));
        }
    }
?>