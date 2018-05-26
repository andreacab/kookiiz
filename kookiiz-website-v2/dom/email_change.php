<?php
    /**********************************************************
    Title: Email change
    Authors: Kookiiz Team
    Purpose: Handle form submissions to change user's email
    ***********************************************************/
	
	/**********************************************************
	SET UP
	***********************************************************/
	
	//Dependencies
	require_once '../class/dblink.php';
	require_once '../class/email.php';
	require_once '../class/lang_db.php';
	require_once '../class/password.php';
	require_once '../class/users_lib.php';
	
	//Init handlers
	$DB           = new DBLink('kookiiz');
    $Request      = new RequestHandler();
    $EmailHandler = new EmailHandler($DB);
    $User         = new User($DB);
	
	//Load parameters
	$email_old  = $Request->get('email_old');
	$email_new1 = $Request->get('email_new1');
	$email_new2 = $Request->get('email_new2');
	$password   = $Request->get('password');
	
	/**********************************************************
	SCRIPT
	***********************************************************/
	
	//User must be logged
	if(!$User->isLogged()) die();
	
	$error = 0;
	//Check if emails match
	if($email_new1 != $email_new2)              
        $error = 2;
	//Check if email is different from old one
	else if($email_new1 == $email_old)          
        $error = 4;
	//Check email validity
	else if(!$EmailHandler->check($email_new1)) 
        $error = 1;
	else
	{
		//Check if email already exists
        $UsersLib = new UsersLib($DB, $User);
		if($UsersLib->existsEmail($email_new1)) 
            $error = 5;
		else
		{
			$email = $email_new1;
		
			//Check password
            $PasswordHandler = new PasswordHandler();
            $salt  = $PasswordHandler->salt_from_email($DB, $email);
            $hash  = $PasswordHandler->hash($password, $salt);
            $match = $PasswordHandler->check($DB, $User->getID(), $hash);
			if($match)
			{
                //Send email
                $EmailHandler->pattern(EmailHandler::TYPE_EMAILCHANGE, array(
                    'content'   => 'text', 
                    'recipient' => $email, 
                    'firstname' => $User->getFirstname(),
                    'user_id'   => $User->getID(),
                    'key'       => $EmailHandler->confirm_getKey($User->getID(), $email, EmailHandler::CONFIRM_CHANGE)
                ));
			}
			//Password is wrong
			else 
                $error = 3;
		}
	}
?>
<html>
    <head>
        <script type="text/javascript" charset="utf-8">
        <?php
            echo "window.ERROR = $error;";
        ?>
        </script>
    </head>
    <body></body>
</html>
