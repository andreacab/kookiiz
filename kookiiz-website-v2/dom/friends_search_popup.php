<?php
	/**********************************************************
    Title: Friends search popup
    Authors: Kookiiz Team
    Purpose: HTML code of friend search popup
    ***********************************************************/

	/**********************************************************
	SET UP
	***********************************************************/

    //Dependencies
	require_once '../class/dblink.php';
	require_once '../class/facebook.php';
	require_once '../class/globals.php';
	require_once '../class/lang_db.php';
	require_once '../class/session.php';
	require_once '../class/social.php';
	require_once '../class/user.php';
    require_once '../secure/facebook.php';

    //Start session
    Session::start();

    //Init handlers
    $DB         = new DBLink('kookiiz');
    $Lang       = LangDB::getHandler(Session::getLang());
    $User       = new User($DB);

    /**********************************************************
	SCRIPT
	***********************************************************/

    $fb_friends = array();

    //Connect to Facebook
    $Facebook = new Facebook(array
    (
        'appId'     => C::FACEBOOK_APP_ID,
        'secret'    => FACEBOOK_SECRET,
        'cookie'    => true
    ));

    //Try to fetch a session
    $fb_user = $Facebook->getUser();
    if($fb_user)
    {
        //Retrieve list of Facebook friends
        try
        {
            $fb_friends = $Facebook->api('/me/friends');
        }
        catch(FacebookApiException $e){error_log($e);}

        //Retrieve corresponding Kookiiz users
        $SocialHandler  = new SocialHandler($DB, $User);
        $fb_friends     = $SocialHandler->facebook_friends_find($fb_friends['data']);

        //Remove those that are already among user's friends on Kookiiz
        $kookiiz_friends = $User->friends_ids_get();
        foreach($fb_friends as $index => $friend)
        {
            if(in_array($friend['user_id'], $kookiiz_friends))
            {
                unset($fb_friends[$index]);
            }
        }
        $fb_friends = array_values($fb_friends);
    }
?>
<div class="center">
	<span class="input_wrap size_220 icon">
		<input type="text" id="input_friends_search" class="focus enter search" value="<?php $Lang->p('FRIENDS_TEXT', 2); ?>" title="<?php $Lang->p('FRIENDS_TEXT', 2); ?>" maxlength="30" />
		<img id="icon_friends_search" class="icon15_white click search" src="<?php C::p('ICON_URL'); ?>" title="<?php $Lang->p('ACTIONS', 13); ?>" alt="<?php $Lang->p('ACTIONS', 13); ?>" />
	</span>
</div>
<div id="friends_search_results" class="center"></div>
<div style="<?php echo count($fb_friends) ? '' : 'display:none;'; ?>">
    <p>
        <?php $Lang->p('FRIENDS_TEXT',5); ?>
    </p>
    <p class="center" id="friends_search_fb">
    <?php
        //Display suggestions of Facebook friends
        foreach($fb_friends as $index => $fb_friend)
        {
            if($index >= C::FRIENDS_SEARCH_FBMAX) break;
            
            $fb_id      = $fb_friend['fb_id'];
            $kookiiz_id = (int)$fb_friend['user_id'];
            echo    '<a href="javascript:void(0);" class="fbfriend_search" id="fbfriend_search_', $kookiiz_id, '">',
                        '<fb:profile-pic uid="', $fb_id, '" class="fb_friend avatar click" size="thumb" linked="false" onclick="" facebook-logo="true"></fb:profile-pic>',
                    '</a>';
        }
    ?>
    </p>
</div>