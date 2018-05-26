<?php
    /**********************************************************
    Title: OAuth FB
    Authors: Kookiiz Team
    Purpose: Request and store OAuth credentials for Facebook
    ***********************************************************/

	/**********************************************************
	SET UP
	***********************************************************/

    //Dependencies
    require_once '../class/dblink.php';
    require_once '../class/globals.php';
    require_once '../class/facebook.php';
    require_once '../class/request.php';
    require_once '../class/session.php';
    require_once '../class/social.php';
    require_once '../class/user.php';
    require_once '../secure/facebook.php';

    //Start session
    Session::start();

    //Init handlers
    $DB         = new DBLink('kookiiz');
    $Request    = new RequestHandler();
    $User       = new User($DB);

    //Load parameters
    $error = $Request->get('error');

    //Init Facebook API
    $Facebook = new Facebook(array(
        'appId'     => C::FACEBOOK_APP_ID,
        'secret'    => FACEBOOK_SECRET
    ));
    $fb_id = $Facebook->getUser();

    /**********************************************************
	SCRIPT
	***********************************************************/

    //User is logged and its account is already connected to Facebook
    if($User->isLogged() && $User->getFbID() && $fb_id)
        $status = C::NETWORK_STATUS_SUCCESS;
    //User has authorized Kookiiz
    else if($fb_id)
    {
        try
        {
            //Check if user is currently logged on Kookiiz
            if($User->isLogged())
            {
                //Connect Kookiiz account to Facebook ID
                $SocialHandler->facebook_connect($fb_id);
                //Process was successfull
                $status = C::NETWORK_STATUS_SUCCESS;
            }
            else
            {
                //Retrieve Facebook user data
                $fb_user = $Facebook->api('/me');
                
                //Store FB credentials in session for future use
                Session::set('fb_id', $fb_id);
                Session::set('fb_name', $fb_user['name']);
                
                //Check if FB ID is already tied to a Kookiiz account
                $SocialHandler = new SocialHandler($DB, $User);
                $user_id = $SocialHandler->facebook_check($fb_id);
                if($user_id)
                    //FB ID is tied to a Kookiiz account
                    $status = C::NETWORK_STATUS_SUCCESS;
                else
                    //User needs to login or to create a new Kookiiz account to tie it to its FB ID
                    $status = C::NETWORK_STATUS_PENDING;
            }
        }
        catch(FacebookApiException $e)
        {
            $status = C::NETWORK_STATUS_FAILURE;
        }
        catch(KookiizException $e)
        {
            $status = C::NETWORK_STATUS_FAILURE;
        }
    }
    //User is coming back from FB authorization page and an error occurred
    //Could be that user did not allow Kookiiz, or any other processing error
    else if($error)
        $status = C::NETWORK_STATUS_FAILURE;
    //No session or error yet: redirect user for FB authentication
    else
    {
        $url = $Facebook->getLoginUrl(array(
            'display'   => 'popup',
            'scope'     => C::FACEBOOK_PERMS
        ));
        header("Location: $url");
        exit();
    }
?>
<script type="text/javascript" charset="utf-8">
    <?php
        echo "window.opener.Kookiiz.networks.onAuth($status);";
    ?>
    self.close();
</script>