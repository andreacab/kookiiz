<?php
    /**********************************************************
    Title: Login
    Authors: Kookiiz Team
    Purpose: Handle login process in hidden iframe
    ***********************************************************/

    /**********************************************************
	SET UP
	***********************************************************/

    //Dependencies
    require_once '../class/dblink.php';
    require_once '../class/password.php';
    require_once '../class/request.php';
    require_once '../class/session.php';

    //Start session
    Session::start();

    //Init handlers
    $DB      = new DBLink('kookiiz');
    $Request = new RequestHandler();

    //Load parameters
    $mode     = $Request->get('mode');
    $email    = $Request->get('email', 'POST');
    $password = $Request->get('password', 'POST');
    $remember = $Request->get('remember');
    $fb_id    = Session::get('fb_id');
    $tw_id    = Session::get('tw_id');

    /**********************************************************
	SCRIPT
	***********************************************************/

    //Check if user is already logged
    if(Session::getStatus() == Session::STATUS_LOGGED)
        $user_id = Session::getID();
    //Login using form information
    else if($email && $password)
    {
        $Password = new PasswordHandler();
        $salt = $Password->salt_from_email($DB, $email);
        $hash = $Password->hash($password, $salt);
        $user_id = Session::login($email, $hash, $remember);
    }
    //Login using Facebook ID
    else if($fb_id)
        $user_id = Session::loginFB($fb_id, $remember);
    //Login using Twitter ID
    else if($tw_id)
        $user_id = Session::loginTW($tw_id, $remember);
?>
<?php if($mode == 'mobile'): ?>
<?php
    header('Location: /m' . ($user_id ? '' : '?error=1'));
    exit();
?>
<?php else: ?>
<html>
    <head>
        <script type="text/javascript" charset="utf-8">
        <?php echo 'window.loginStatus = ', ($user_id ? 1 : 0), ';'; ?>
        </script>
    </head>
    <body></body>
</html>
<?php endif; ?>
