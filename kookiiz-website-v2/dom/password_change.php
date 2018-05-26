<?php
    /**********************************************************
    Title: Password change
    Authors: Kookiiz Team
    Purpose: Handle ajax calls to change user's password
    ***********************************************************/
	
	/**********************************************************
	SET UP
	***********************************************************/
	
	//Dependencies
    require_once '../class/dblink.php';
    require_once '../class/globals.php';
    require_once '../class/password.php';
    require_once '../class/request.php';
    require_once '../class/user.php';
	
	//Init handlers
    $DB      = new DBLink('kookiiz');
	$Request = new RequestHandler();
    $User    = new User($DB);
	
	//Load parameters
	$pass_old  = $Request->get('password_old');
	$pass_new1 = $Request->get('password_new1');
	$pass_new2 = $Request->get('password_new2');
	
	/**********************************************************
	SCRIPT
	***********************************************************/
	
	//User must be logged
	if(!$User->isLogged()) die();
	
	$error = 0;
	//Check if new passwords match
	if($pass_new1 != $pass_new2)                
        $error = 3;
	//Check if new password is different from old one
	else if($pass_new1 == $pass_old)            
        $error = 6;
	//Check password length
	else if(strlen($pass_new1) < C::USER_PASSWORD_MIN) 
        $error = 4;
	else if(strlen($pass_new1) > C::USER_PASSWORD_MAX) 
        $error = 5;
	else
	{
        //Generate old and new password hashes
        $PasswordHandler = new PasswordHandler();
        $salt     = $PasswordHandler->salt_from_id($DB, $User->getID());
        $hash_old = $PasswordHandler->hash($pass_old, $salt);
        $hash_new = $PasswordHandler->hash($pass_new1);

        //Check password validity then update user profile
        $valid = $PasswordHandler->check($DB, $User->getID(), $hash_old);
        if($valid)  
            $User->password_change($hash_new);
        else        
            $error = 2;
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