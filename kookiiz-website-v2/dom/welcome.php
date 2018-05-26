<?php
    /**********************************************************
    Title: Welcome
    Authors: Kookiiz Team
    Purpose: HTML content of Kookiiz welcome screen
    ***********************************************************/

    /**********************************************************
	SET UP
	***********************************************************/

    //Dependencies
    require_once '../class/dblink.php';
    require_once '../class/globals.php';
    require_once '../class/lang_db.php';
    require_once '../class/session.php';
    require_once '../class/user.php';

    //Start session
    Session::start();

    //Init handlers
    $DB   = new DBLink('kookiiz');
    $Lang = LangDB::getHandler(Session::getLang());
    $User = new User($DB);
    
    /**********************************************************
	DOM GENERATION
	***********************************************************/
?>
<h4 class="title_bar">
    <span><?php $Lang->p('FRONTPAGE_TEXT', 5); ?></span>
    <img class="button15 cancel" src="<?php C::p('ICON_URL'); ?>" alt="X" title="<?php $Lang->p('ACTIONS', 16); ?>" />
</h4>
<div class="header">
    <div class="header_left center">
        <a href="/">
            <img id="welcome_logo" src="/pictures/help_logo.png" alt="Logo Kookiiz" title="Kookiiz" />
        </a>
    </div>
    <div class="header_right">
    <?php if($User->isLogged()): ?>
        <p>
            <span><?php echo $Lang->get('USER_TEXT', 2), ' ', $User->getName(), ' '; ?></span>
            (<a href="/logout"><?php $Lang->p('USER_TEXT', 3); ?></a>)
        </p>
    <?php endif; ?>
        <div id="kookiiz_login">
            <form method="post" action="/dom/login.php" target="iframe_login" autocomplete="off" <?php if($User->isLogged()) echo 'style="display:none;"' ?>>
                <input type="hidden" name="mode" value="full" />
                <table>
                    <tr>
                        <td colspan="4" class="small"><?php $Lang->p('WELCOME_TEXTS', 2); ?></td>
                    </tr>
                    <tr>
                        <td class="left">
                            <span class="input_wrap size_300">
                                <input type="text" name="email" class="focus" value="<?php $Lang->p('USER_PROPERTIES', 2); ?>" title="<?php $Lang->p('USER_PROPERTIES', 2); ?>" />
                            </span>
                        </td>
                        <td class="left">
                            <span class="input_wrap size_160">
                                <input type="password" name="password" class="focus" maxlength="<?php C::p('USER_PASSWORD_MAX'); ?>" value="<?php $Lang->p('USER_PROPERTIES', 3); ?>" title="<?php $Lang->p('USER_PROPERTIES', 3); ?>" />
                            </span>
                        </td>
                        <td colspan="2" class="center">
                            <button type="submit" class="button_80"><?php $Lang->p('ACTIONS', 28); ?></button>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div>
                                <button type="button" class="social facebook_100 text_color0 bold left" data-network="facebook"><?php $Lang->p('ACTIONS', 17); ?></button>
                                <?php if(false) { //Hide Twitter connect button ?>
                                <button type="button" class="social twitter_100 text_color0 bold left" data-network="twitter"><?php $Lang->p('ACTIONS', 17); ?></button>
                                <?php } ?>
                            </div>
                        </td>
                        <td>
                            <span class="passlost click"><?php echo $Lang->get('PASSWORD_TEXT', 9); ?></span>
                        </td>
                        <td>
                            <label>
                                <input type="checkbox" name="remember" />
                                <span class="click"><?php $Lang->p('FRONTPAGE_TEXT', 0); ?></span>
                            </label>
                        </td>
                    </tr>
                </table>
            </form>
            <iframe name="iframe_login" style="display:none"></iframe>
        </div>
    </div>
</div>
<div class="main">
    <div class="demo" style="display:none">
        <ul class="menu">
            <li class="tab click selected" data-tab="0">
                <h5 class="center"><?php $Lang->p('WELCOME_MENU', 0); ?></h5>
            </li>
            <li class="tab click" data-tab="1">
                <h5 class="center"><?php $Lang->p('WELCOME_MENU', 1); ?></h5>
            </li>
            <li class="tab click" data-tab="2">
                <h5 class="center"><?php $Lang->p('WELCOME_MENU', 2); ?></h5>
            </li>
            <li class="tab click" data-tab="3">
                <h5 class="center"><?php $Lang->p('WELCOME_MENU', 3); ?></h5>
            </li>
        </ul>
        <div class="content">
            <h5 class="caption"></h5>
            <div class="frame_wrap">
                <img src="<?php echo C::ICON_URL; ?>" class="frame" alt="" />
            </div>
            <h5 class="caption"></h5>
        </div>
        <div class="videos">
            <h6 class="center text_color2"><?php $Lang->p('WELCOME_TEXTS', 0); ?></h6>
            <div class="list"></div>
        </div>
        <div class="buttons center">
            <button type="button" class="subscribe button_shiny"><?php $Lang->p('FRONTPAGE_TEXT', 1); ?></button>
            <button type="button" class="try button_shiny"><?php $Lang->p('ACTIONS', 31); ?></button>
        </div>
    </div>
    <div class="form" style="display:none">
        <?php include '../dom/user_form.php'; ?>
    </div>
</div>