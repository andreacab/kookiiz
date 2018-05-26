<?php
	/**********************************************************
    Title: Help
    Authors: Kookiiz Team
    Purpose: HTML content of the help section
	***********************************************************/

    //Dependencies
    require_once '../class/globals.php';
    require_once '../class/lang_db.php';
    require_once '../class/session.php';

    //Start session
    Session::start();

    //Init handlers
    $Lang = LangDB::getHandler(Session::getLang());
?>
<h4 class="tab"><?php $Lang->p('VARIOUS', 18); ?></h4>
<img id="help_logo" src="/pictures/help_logo.png" alt="Logo Kookiiz" title="Kookiiz" />
<ul class="topmenu">
    <li data-theme="menu" class="theme"><h5 class="center"><?php $Lang->p('HELP_THEMES', 0); ?></h5></li>
    <li data-theme="health" class="theme"><h5 class="center"><?php $Lang->p('HELP_THEMES', 1); ?></h5></li>
    <li data-theme="social" class="theme"><h5 class="center"><?php $Lang->p('HELP_THEMES', 2); ?></h5></li>
</ul>
<img id="help_close" class="button15 cancel" src="<?php C::p('ICON_URL'); ?>" alt="X" title="<?php $Lang->p('ACTIONS', 16); ?>" />
<div class="sidebar"></div>
<div class="content center">
    <img src="<?php C::p('ICON_URL'); ?>" alt="" />
    <p class="text justify"></p>
</div>