<?php
	/**********************************************************
    Title: Sign up social
    Authors: Kookiiz Team
    Purpose: Sign up user from social network account
    ***********************************************************/

	/**********************************************************
	SET UP
	***********************************************************/

	//Include external files
    require_once '../class/facebook.php';
	require_once '../class/globals.php';
	require_once '../class/lang_db.php';
	require_once '../class/password.php';
	require_once '../class/request.php';
	require_once '../class/session.php';
	require_once '../class/social.php';
	require_once '../class/users_lib.php';
    require_once '../secure/facebook.php';

    //Start session
    Session::start();

	//Init handlers
	$DB       = new DBLink('kookiiz');
    $Request  = new RequestHandler();
    $User     = new User($DB);
    $UsersLib = new UsersLib($DB, $User);

	//Load parameters
	$network = $Request->get('network');

	/**********************************************************
	SCRIPT
	***********************************************************/

    try
    {
        //Retrieve session language
        $lang = Session::getLang();

        //Create a random password hash
        $PasswordHandler = new PasswordHandler();
        $hash = $PasswordHandler->hash(md5(uniqid()));

        //Network-dependent
        switch($network)
        {
            case 'facebook':
                $Facebook = new Facebook(array(
                    'appId'     => C::FACEBOOK_APP_ID,
                    'secret'    => FACEBOOK_SECRET
                ));
                $fb_id = $Facebook->getUser();
                if($fb_id)
                {
                    //Retrieve Facebook details
                    $fb_user = $Facebook->api('/me');
                    $firstname = $fb_user['first_name'];
                    $lastname  = $fb_user['last_name'];
                    $email     = $fb_user['email'];
                    if(!$firstname || !$lastname || !$email)
                        throw new KookiizException('social', 2);

                    //Create Kookiiz account
                    $user_id = $UsersLib->create($firstname, $lastname, $email, $hash, $pic_id = 0, $lang);
                    if($user_id)
                    {
                        //Log user in
                        Session::login($email, $hash, $remember = true);
                        $User = new User($DB);

                        //Connect account to Facebook ID
                        $SocialHandler = new SocialHandler($DB, $User);
                        $SocialHandler->facebook_connect($fb_id);
                    }
                    else
                        //Account creation failed
                        throw new KookiizException('subscribe', 10);
                }
                else
                    //Social network connection failed
                    throw new KookiizException('social', 2);
                break;

//            case 'twitter':
//                //Twitter API does not provide user's email address...
//                break;

            default:
                //Social network connection failed
                throw new KookiizException('social', 2);
                break;
        }
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
            echo "window.MODE = 'network'\n";   //Sign-up mode
            if(!isset($error)) $error = array('code' => 0, 'type' => '');
            echo 'window.ERROR = ', json_encode($error), ';';
        ?>
        </script>
    </head>
    <body></body>
</html>