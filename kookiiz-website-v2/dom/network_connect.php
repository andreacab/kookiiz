<?php
	/**********************************************************
    Title: Network connect
    Authors: Kookiiz Team
    Purpose: Connect a Kookiiz account with a social network
	***********************************************************/
	
	/**********************************************************
	SET UP
	***********************************************************/

	//Dependencies
	require_once '../class/dblink.php';
	require_once '../class/exception.php';
	require_once '../class/password.php';
	require_once '../class/request.php';
	require_once '../class/session.php';
	require_once '../class/social.php';
	require_once '../class/user.php';

    //Start session
    Session::start();
	
	//Init handlers
	$DB      = new DBLink('kookiiz');
    $Request = new RequestHandler();
	
	//Load parameters
    $network    = $Request->get('network');
    $email      = $Request->get('email');
    $password   = $Request->get('password');
	
	/**********************************************************
	SCRIPT
	***********************************************************/

    //Compute hashed password
    $Password = new PasswordHandler();
    $salt = $Password->salt_from_email($DB, $email);
    $hash = $Password->hash($password, $salt);

    //Try to connect Kookiiz account with social network
    $error = 0;
    try
    {
        //Log user in
        Session::login($email, $hash);
        $User = new User($DB);
        
        //Take appropriate action depending on social network
        $Social = new SocialHandler($DB, $User);
        switch($network)
        {
            case 'facebook':
                $fb_id = Session::get('fb_id');
                if($fb_id)
                    $Social->facebook_connect($fb_id);
                else
                    throw new KookiizException('social', 2);
                break;
                
            case 'twitter':
                $tw_id = Session::get('tw_id');
                if($tw_id)
                {
                    $token  = Session::get('tw_token');
                    $secret = Session::get('tw_secret');
                    $Social->twitter_connect($tw_id, $token, $secret);
                }
                else
                    throw new KookiizException('social', 2);
                break;
        }
    }
    catch(KookiizException $e)
    {
        $error = $e->getCode();
    }
    
?>
<html>
    <head>
        <script type="text/javascript" charset="utf-8">
        <?php
            echo "window.ERROR = ", ($error ? $error : 0), ';';
        ?>
        </script>
    </head>
    <body></body>
</html>