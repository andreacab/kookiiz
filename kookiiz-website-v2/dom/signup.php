<?php
	/**********************************************************
    Title: Sign up
    Authors: Kookiiz Team
    Purpose: Handle data POSTed through the user form
    ***********************************************************/

	/**********************************************************
	SET UP
	***********************************************************/

	//Include external files
	require_once '../class/email.php';
	require_once '../class/globals.php';
	require_once '../class/lang_db.php';
	require_once '../class/password.php';
	require_once '../class/request.php';
	require_once '../class/session.php';
	require_once '../class/users_lib.php';

    //Start session
    Session::start();

	//Init handlers
	$DB      = new DBLink('kookiiz');
    $Request = new RequestHandler();
    $User    = new User($DB);

	//Load parameters
	$terms      = $Request->get('terms');
	$firstname  = $Request->get('firstname');
	$lastname   = $Request->get('lastname');
	$email      = $Request->get('email');
	$password1  = $Request->get('password1');
	$password2  = $Request->get('password2');
	$pic_id     = (int)$Request->get('pic_id');
	$lang       = $Request->get('lang');

	/**********************************************************
	SCRIPT
	***********************************************************/

    try
    {
        //Check that all required fields are provided
        $required = array('firstname', 'lastname', 'email', 'password1', 'password2', 'pic_id', 'lang');
        foreach($required as $field)
        {
            if(is_null($$field))
                throw new KookiizException('subscribe', 1);
        }

        /**********************************************************
        CHECK DATA
        ***********************************************************/

        //TERMS
        if(is_null($terms))
            throw new KookiizException('subscribe', 15);

        //FIRST AND LAST NAME
        $pattern = '/' . C::REGEXP_NAME_PATTERN . '/';
        if(strlen($firstname) < C::USER_FIRSTNAME_MIN)
            throw new KookiizException('subscribe', 2);
        else if(strlen($firstname) > C::USER_FIRSTNAME_MAX)
            throw new KookiizException('subscribe', 3);
        else if(!preg_match($pattern, $firstname))
            throw new KookiizException('subscribe', 4);
        if(strlen($lastname) < C::USER_LASTNAME_MIN)
            throw new KookiizException('subscribe', 11);
        else if(strlen($lastname) > C::USER_LASTNAME_MAX)
            throw new KookiizException('subscribe', 12);
        else if(!preg_match($pattern, $lastname))
            throw new KookiizException('subscribe', 13);

        //PASSWORD
        if($password1 != $password2)
            throw new KookiizException('subscribe', 5);
        else if(strlen($password1) < C::USER_PASSWORD_MIN)
            throw new KookiizException('subscribe', 6);
        else if(strlen($password1) > C::USER_PASSWORD_MAX)
            throw new KookiizException('subscribe', 7);

        //EMAIL
        $EmailHandler   = new EmailHandler($DB);
        $UsersLib       = new UsersLib($DB, $User);
        if(!$EmailHandler->check($email))
            throw new KookiizException('subscribe', 8);
        else if($UsersLib->existsEmail($email))
            throw new KookiizException('subscribe', 14);

        //LANG
        if(!in_array($lang, C::get('LANGUAGES')))
            throw new KookiizException('subscribe', 9);

        /**********************************************************
        CREATE USER ACCOUNT
        ***********************************************************/

        //Create password hash
        $PasswordHandler = new PasswordHandler();
        $hash = $PasswordHandler->hash($password1);

        //Create account
        $user_id = $UsersLib->create($firstname, $lastname, $email, $hash, $pic_id, $lang, $virtual = true);
        if($user_id)
        {
            //Send email for validation process
            $key = $EmailHandler->confirm_getKey($user_id, $email, EmailHandler::CONFIRM_SUBSCRIBE);
            $EmailHandler->pattern(EmailHandler::TYPE_SUBSCRIBE, array(
                'content'   => 'text',
                'recipient' => $email,
                'firstname' => $firstname,
                'user_id'   => $user_id,
                'key'       => $key
            ));
        }
        //Account creation failed
        else
            throw new KookiizException('subscribe', 10);
    }
    catch(KookiizException $e)
    {
        $error = array('code' => $e->getCode(), 'type' => $e->getType());
    }
?>
<html>
    <head>
        <script type="text/javascript" charset="utf-8">
        <?php
            echo "window.MODE = 'standard'\n";  //Sign-up mode
            if(!isset($error)) $error = array('code' => 0, 'type' => '');
            echo 'window.ERROR = ', json_encode($error), ';';
        ?>
        </script>
    </head>
    <body></body>
</html>