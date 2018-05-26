<?php
	/*******************************************************
    Title: Email confirm
    Authors: Kookiiz Team
    Purpose: Deals with user email confirmations
    ********************************************************/
	
	/**********************************************************
	SET UP
	***********************************************************/
	
	//Dependencies
	require_once '../class/dblink.php';
	require_once '../class/email.php';
	require_once '../class/globals.php';
	require_once '../class/lang_db.php';
	require_once '../class/request.php';
	require_once '../class/session.php';
	require_once '../class/user.php';

    //Start session
    Session::start();
	
	//Init handlers
	$DB      = new DBLink('kookiiz');
    $Lang    = LangDB::getHandler(Session::getLang());
    $Request = new RequestHandler();
	
	//Load parameters
	$user_id = (int)$Request->get('user_id');
	$key     = $Request->get('key');
	
	/**********************************************************
	SCRIPT
	***********************************************************/

    $error = false;
    try
    {
        $EmailHandler = new EmailHandler($DB);
        $params = $EmailHandler->confirm($user_id, $key);
        if($params)
        {
            $email = $params['email'];
            $type  = $params['type'];
            switch($type)
            {
                case EmailHandler::CONFIRM_CHANGE:
                    $user_id = Session::loginID($user_id);
                    if($user_id)
                    {
                        $User = new User($DB);
                        $User->email_change($email);
                    }
                    else
                        throw new Exception();
                    break;

                case EmailHandler::CONFIRM_SUBSCRIBE:
                    require_once '../class/users_lib.php';                
                    //Confirm user email
                    $User = new User($DB);
                    $UsersLib = new UsersLib($DB, $User);
                    $UsersLib->confirm($user_id);                    
                    //Log him in
                    Session::loginID($user_id);
                    break;

                default:
                    throw new Exception();
                    break;
            }
        }   
        else
            throw new Exception();
    }
    catch(Exception $e)
    {
        $error = true;
    }
?>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        
        <?php if(!$error): ?>
        <meta http-equiv="refresh" content="5; URL=/" />
        <?php endif; ?>
        
        <!-- Style sheet -->
        <link rel="stylesheet" href="/min/f=/themes/<?php C::p('THEME'); ?>/css/main.css" media="screen" type="text/css" />
    </head>
    <body>
        <div>
            <a href="/" alt="Kookiiz">
                <img id="kookiiz_logo" src="/pictures/logo.png" alt="<?php $Lang->p('MAIN_TEXT', 2); ?>" />
            </a>
        </div>
        <h6>           
            <?php
                if($error)
                    $Lang->p('EMAIL_TEXT', 4); 
                else
                    $Lang->p('EMAIL_TEXT', 3);
            ?>
            <br/><br/>
            <a href="/" alt="Kookiiz"><?php $Lang->p('EMAIL_TEXT', 5); ?></a>
        </h6>
    </body>
</html>