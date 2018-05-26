<?php
	/**********************************************************
    Title: OAuth TW
    Authors: Kookiiz Team
    Purpose: Request and store OAuth credentials for Twitter
    ***********************************************************/

	/**********************************************************
	SET UP
	***********************************************************/

    //Dependencies
    require_once '../class/dblink.php';
    require_once '../class/globals.php';
    require_once '../class/oauth.php';
    require_once '../class/request.php';
    require_once '../class/session.php';
    require_once '../class/social.php';
    require_once '../class/twitter_oauth.php';
    require_once '../class/user.php';
    require_once '../secure/twitter.php';

    //Start session
    Session::start();

    //Init handlers
    $DB         = new DBLink('kookiiz');
    $Request    = new RequestHandler();
    $User       = new User($DB);

    //Load parameters
    $verifier = $Request->get('oauth_verifier');

    /**********************************************************
	SCRIPT
	***********************************************************/

    //User is logged and its account is already connected to Twitter
    if($User->isLogged() && $User->getTwID())
        $status = C::NETWORK_STATUS_SUCCESS;
    //User is coming back from Twitter authorization page
    else if($verifier)
    {
        try
        {
            //Request permanent credentials for current user
            $temp_token  = Session::get('oauth_temp_token');
            $temp_secret = Session::get('oauth_temp_secret');
            $Twitter     = new TwitterOAuth(TWITTER_CLIENT, TWITTER_SECRET, $temp_token, $temp_secret);
            $credentials = $Twitter->getAccessToken();

            //Clear temp credentials
            Session::clear('oauth_temp_token');
            Session::clear('oauth_temp_secret');

            //Retrieve user data
            $tw_id   = $credentials['user_id'];
            $tw_name = $credentials['screen_name'];
            $token   = $credentials['oauth_token'];
            $secret  = $credentials['oauth_token_secret'];

            //User is logged
            if($User->isLogged())
            {
                //Store Twitter credentials for current user
                $SocialHandler = new SocialHandler($DB, $User);
                $SocialHandler->twitter_connect($tw_id, $token, $secret);

                //Twitter is successfully connected
                $status = C::NETWORK_STATUS_SUCCESS;
            }
            else
            {
                //Store credentials in session for futur use
                Session::set('tw_id', $tw_id);
                Session::set('tw_name', $tw_name);
                Session::set('tw_token', $token);
                Session::set('tw_secret', $secret);
                
                //Check if Twitter ID is already tied to a Kookiiz account
                $SocialHandler = new SocialHandler($DB, $User);
                $user_id = $SocialHandler->twitter_check($tw_id);
                if($user_id)       
                    //TW ID is tied to a Kookiiz account
                    $status = C::NETWORK_STATUS_SUCCESS;
                else
                    //User needs to login or to create a new Kookiiz account to tie it to its TW ID
                    $status = C::NETWORK_STATUS_PENDING;
            }
        }
        catch(KookiizException $e)
        {
            $status = C::NETWORK_STATUS_FAILURE;
        }
    }
    //User should be redirected to Twitter authorization page
    else
    {
        //Retrieve temporary credentials
        $Twitter = new TwitterOAuth(TWITTER_CLIENT, TWITTER_SECRET);
        $credentials  = $Twitter->getRequestToken();
        $redirect_url = $Twitter->getAuthorizeURL($credentials);

        //Store temporary credentials in session
        Session::set('oauth_temp_token', $credentials['oauth_token']);
        Session::set('oauth_temp_secret', $credentials['oauth_token_secret']);

        //Redirect user for authorization
        header("Location: $redirect_url");
        exit();
    }
?>
<script type="text/javascript" charset="utf-8">
    <?php
        echo "window.opener.Kookiiz.networks.onAuth($status);";
    ?>
    self.close();
</script>