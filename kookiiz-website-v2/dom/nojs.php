<?php
    /**********************************************************
    Title: No JS
    Authors: Kookiiz Team
    Purpose: Landing page for users without Javascript enabled
	***********************************************************/

    //Dependencies
    require_once '../class/lang_db.php';
    require_once '../class/session.php';

    //Start session
    Session::start();

    //Init handlers
    $Lang = LangDB::getHandler(Session::getLang());
?>
<html>
    <head>
        <title>
            <?php $Lang->p('MAIN_TITLE', 0); ?>
        </title>
    </head>
    <body>
        <h3>
            <?php $Lang->p('MAIN_TEXT', 3); ?>
        </h3>
    </body>
</html>