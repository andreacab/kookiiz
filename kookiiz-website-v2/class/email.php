<?php
    /*******************************************************
    Title: Email manager
    Authors: Kookiiz Team
    Purpose: Send standardized Kookiiz emails
    ********************************************************/

    //Dependencies
    require_once '../class/dblink.php';
    require_once '../class/lang_db.php';
    require_once '../class/session.php';

    //Represents an interface to send standardized emails
    class EmailHandler
    {
        /**********************************************************
        PROPERTIES
        ***********************************************************/

        //Class constants
        const CONFIRM_CHANGE        = 1;    //Types of email confirmations
        const CONFIRM_SUBSCRIBE     = 0;
        const TYPE_EMAILCHANGE      = 6;    //Email types
        const TYPE_FRIENDREQUEST    = 0;
        const TYPE_INVITATION       = 1;
        const TYPE_PASSWORDRESET    = 2;
        const TYPE_RECIPEADDED      = 3;
        const TYPE_SHOPPING         = 4;
        const TYPE_SUBSCRIBE        = 5;

        //Variables
        private $DB;    //Database handler
        private $Lang;  //Language handler

        /**********************************************************
        CONSTRUCTOR
        ***********************************************************/

        /**
         * Class constructor
         * @param DBLink $DB open database connection
         */
        public function __construct(DBLink &$DB)
        {
            Session::start();

            //Set up properties
            $this->DB   = $DB;
            $this->Lang = LangDB::getHandler(Session::getLang());
        }
        
        /**********************************************************
        CHECK
        ***********************************************************/

        /**
         * Validate an email address
         * Found on "http://www.linuxjournal.com/article/9585"
         * @param String $email address to check
         * @return Bool true if the email format is valid and the domain exists
         */
        public function check($email)
        {
            $valid   = true;
            $atIndex = strrpos($email, '@');
            
            if(is_bool($atIndex) && !$atIndex)
                $valid = false;
            else
            {
                $domain     = substr($email, $atIndex + 1);
                $local      = substr($email, 0, $atIndex);
                $localLen   = strlen($local);
                $domainLen  = strlen($domain);
                
                //local part length exceeded
                if($localLen < 1 || $localLen > 64)
                    $valid = false;
                //domain part length exceeded
                else if($domainLen < 1 || $domainLen > 255)
                    $valid = false;
                //local part starts or ends with '.'
                else if($local[0] == '.' || $local[$localLen - 1] == '.')
                    $valid = false;
                //local part has two consecutive dots
                else if(preg_match('/\\.\\./', $local))
                    $valid = false;
                //character not valid in domain part
                else if(!preg_match('/^[A-Za-z0-9\\-\\.]+$/', $domain))
                    $valid = false;
                //domain part has two consecutive dots
                else if(preg_match('/\\.\\./', $domain))                    
                    $valid = false;
                //character not valid in local part unless local part is quoted
                else if(!preg_match('/^(\\\\.|[A-Za-z0-9!#%&`_=\\/$\'*+?^{}|~.-])+$/', str_replace("\\\\", "", $local))
                        && !preg_match('/^"(\\\\"|[^"])+"$/', str_replace("\\\\", "", $local)))
                    $valid = false;
            }
            //domain not found in DNS
            if($valid && !(checkdnsrr($domain, 'MX') || !checkdnsrr($domain, 'A')))
                $valid = false;

            //Return validity status
            return $valid;
        }

        /**********************************************************
        CONFIRMATION PROCESS
        ***********************************************************/

        /**
         * Check if confirmation key is valid and delete it
         * @param Int $user_id ID of the user to which the key belongs
         * @param String $key confirmation key
         * @return Array structure containing email and type (or false if confirmation fails)
         */
        public function confirm($user_id, $key)
        {
            $request = 'SELECT email, type FROM email_confirm'
                    . ' WHERE user_id = ? AND confirm_key = ?';
            $stmt = $this->DB->query($request, array($user_id, $key));
            $data = $stmt->fetch();
            if($data)
            {
                //Delete entry
                $request = 'DELETE FROM email_confirm'
                        . ' WHERE user_id = ? AND confirm_key = ?';
                $this->DB->query($request, array($user_id, $key));

                //Return parameters
                return array(
                    'email' => $data['email'],
                    'type'  => (int)$data['type']
                );
            }
            return false;
        }

        /**
         * Generate database entry for email confirmation process
         * @param Int $user_id ID of the user for which to create a key
         * @param String $email user email address
         * @param Int $type type of email confirmation
         * @return String random key required to confirm email address
         */
        public function confirm_getKey($user_id, $email, $type)
        {
            $key = md5(uniqid());
            $request = 'INSERT INTO email_confirm (user_id, email, confirm_key, type)'
                        . ' VALUES (:user_id, :email, :key, :type)';
            $this->DB->query($request, array(
                ':user_id'  => $user_id,
                ':email'    => $email,
                ':key'      => $key,
                ':type'     => $type
            ));
            return $key;
        }

        /**********************************************************
        CREATE
        ***********************************************************/

        /**
         * Create and send an email from a pattern
         * @param Int $type ID of the email model
         * @param Array $params list of email parameters specific to the model
         */
        public function pattern($type, array $params)
        {
            $TITLES   = $this->Lang->get('EMAILS_TITLES');
            $FOOTERS  = $this->Lang->get('EMAILS_FOOTERS');
            $URL_HASH = $this->Lang->get('URL_HASH_TABS');

            //Recipient & subject
            $to      = $params['recipient'];
            $subject = $TITLES[$type];

            //Message body
            //Note: text parameters must NOT be passed to htmlentities if email content-type is text !!!
            $message = '';
            switch($type)
            {
                case self::TYPE_EMAILCHANGE:
                    $BODY = $this->Lang->get('EMAILS_BODY_EMAILCHANGE');
                    $message = "{$BODY[0]} {$params['firstname']}\n\n"
                                . "{$BODY[1]}: www.kookiiz.com/confirm?user_id={$params['user_id']}&key={$params['key']} {$BODY[2]}\n"
                                . "{$BODY[3]}\n\n"
                                . $FOOTERS[0];
                    break;

                case self::TYPE_FRIENDREQUEST:
                    $BODY = $this->Lang->get('EMAILS_BODY_FRIENDREQUEST');
                    $message = "{$BODY[0]} {$params['friend_name']},\n\n"
                                . "{$params['name']} {$BODY[1]}\n"
                                . "{$BODY[2]}: www.kookiiz.com/?notify=friends {$BODY[3]}\n\n"
                                . "{$FOOTERS[0]}\n\n"
                                . "{$FOOTERS[1]} www.kookiiz.com/{$URL_HASH[3]}";
                    break;

                case self::TYPE_INVITATION:
                    $BODY = $this->Lang->get('EMAILS_BODY_INVITATION');
                    $message = "{$BODY[0]} {$params['guest_name']},\n\n"
                                . "{$params['name']} {$BODY[1]}\n"
                                . "{$BODY[2]}: www.kookiiz.com/?notify=invitations {$BODY[3]}\n\n"
                                . "{$FOOTERS[0]}\n\n"
                                . "{$FOOTERS[1]} www.kookiiz.com/{$URL_HASH[3]}";
                    break;

                case self::TYPE_PASSWORDRESET:
                    $BODY = $this->Lang->get('EMAILS_BODY_PASSWORDRESET');
                    $password = $params['password'];
                    $message = "{$BODY[0]} {$params['firstname']},\n\n"
                                . "{$BODY[1]}\n"
                                . "{$BODY[2]}\n"
                                . "{$BODY[3]}: www.kookiiz.com/pass_reset?user_id={$params['user_id']}&pass=$password\n\n"
                                . "{$BODY[4]} '$password' {$BODY[5]}\n"
                                . "{$BODY[6]}\n\n"
                                . $FOOTERS[0];
                    break;

                case self::TYPE_RECIPEADDED:
                    $BODY = $this->Lang->get('EMAILS_BODY_RECIPEADDED');
                    $message = "{$BODY[0]} {$params['firstname']},\n\n"
                                . "{$BODY[1]} \"{$params['recipe_name']}\" {$BODY[2]}\n"
                                . "{$BODY[3]}: www.kookiiz.com/{$URL_HASH[4]}-{$params['recipe_id']}.\n\n"
                                . ($params['public'] ? ("{$BODY[4]}\n\n") : ("{$BODY[5]}\n\n"))
                                . "{$FOOTERS[0]}\n\n"
                                . "{$FOOTERS[1]} www.kookiiz.com/{$URL_HASH[3]}";
                    break;

                case self::TYPE_SHOPPING:
                    $BODY = $this->Lang->get('EMAILS_BODY_SHOPPING');
                    $name = htmlspecialchars($params['name'], ENT_COMPAT, 'UTF-8');
                    $message = "{$BODY[0]}<br/><br/>"
                                . "$name {$BODY[1]}:<br/><br/>"
                                . "{$params['list']}<br/>"
                                . "{$FOOTERS[2]}<br/><br/>"
                                . $FOOTERS[0];
                    break;

                case self::TYPE_SUBSCRIBE:
                    $BODY = $this->Lang->get('EMAILS_BODY_SUBSCRIBE');
                    $message = "{$BODY[0]} {$params['firstname']},\n\n"
                                . "{$BODY[1]}: www.kookiiz.com/confirm?user_id={$params['user_id']}&key={$params['key']} {$BODY[2]}\n"
                                . "{$BODY[3]}\n\n"
                                . $FOOTERS[0];
                    break;
            }

            //Content type
            $content = $params['content'];
            $this->send($to, $subject, $message, $content);
        }

        /**********************************************************
        SEND
        ***********************************************************/

        /**
         * Send an email
         * @param String $to email address of the recipient
         * @param String $subject text of the subject
         * @param String $message text of the email body
         * @param String $content either "html" or "text"
         * @return Bool success 
         */
        public function send($to, $subject, $message, $content = 'text')
        {
            //Don't try to send emails on the localhost
            $domain = strtolower($_SERVER['HTTP_HOST']);
            $local  = array('localhost', '127.0.0.1', 'kookiiz.local');
            if(in_array($domain, $local)) 
            {
                //Write email in plain text file
                @$log = fopen('../logs/emails.txt', 'wb');
                if($log)
                    fwrite ($log, $message);
                return false;
            }
            
            //Headers
            $headers = "From: Kookiiz Team <noreply@kookiiz.com> " . "\r\n";
            if($content == 'text')      
                $headers .= "Content-Type: text/plain; charset=utf-8" . "\r\n";
            else if($content == 'html') 
                $headers .= "Content-Type: text/html; charset=utf-8" . "\r\n";
            $headers .= "\r\n";

            //Call to mail() function
            mail($to, $subject, $message, $headers);
            
            //Email was sent
            return true;
        }
    }
?>
