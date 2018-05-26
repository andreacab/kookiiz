<?php
	/**********************************************************
    Title: Password reset
    Authors: Kookiiz Team
    Purpose: Handle ajax calls to reset user password
    ***********************************************************/

	/**********************************************************
	SET UP
	***********************************************************/
	
	//Include external files
	require_once '../class/dblink.php';
	require_once '../class/lang_db.php';
	require_once '../class/password.php';
	require_once '../class/request.php';
	require_once '../class/session.php';
	require_once '../class/user.php';

    //Start session
    Session::start();
	
	//Init handlers
	$DB         = new DBLink('kookiiz');
    $Lang       = LangDB::getHandler(Session::getLang());
    $Request    = new RequestHandler();
	
	//Load parameters
	$user_id    = (int)$Request->get('user_id');
    $password   = $Request->get('pass');
	
	/**********************************************************
	SCRIPT
	***********************************************************/

    $success = false;
	
	//Retrieve password hash (containing salt)
    $PasswordHandler = new PasswordHandler();
    $salt = $PasswordHandler->tempGet($DB, $user_id);
	if($salt)
    {
        //Check provided temporary password
        $hash = $PasswordHandler->hash($password, $salt);
        if($salt === $hash)
        {
            //Create user session
            Session::create($DB, $user_id, Session::getLang(), $remember = false);

            //Update user password, then destroy session to force user to log-in
            $User = new User($DB);
            $User->password_change($hash);
            Session::destroy();

            //Remove temporary password from database
            $PasswordHandler->tempRemove($DB, $user_id);

            //Password has been successfully reset
            $success = true;
        }
    }

    //Redirect to welcome page
    Session::set('pass_confirm', $success ? 1 : 0);
    header('Location: /' . $Lang->get('URL_KEYS', 0));
?>