<?php
    /*******************************************************
    Title: Sorry
    Authors: Kookiiz Team
    Purpose: Replacement page during maintenance process
    ********************************************************/

    /**********************************************************
	SET UP
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
<html lang="<?php echo Session::getLang(); ?>" xmlns="http://www.w3.org/1999/xhtml">
<head>
    <!-- Meta data -->
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    
    <!-- Screen style sheets -->
	<link rel="stylesheet" href="<?php echo '/themes/', C::THEME, '/css/globals.css'; ?>" media="screen" type="text/css" />

    <!-- Page title -->
    <title><?php $Lang->p('MAIN_TITLE', 0); ?></title>
</head>
<body>
    <div class="center">
        <img id="kookiiz_logo" src="/pictures/logo.png" alt="<?php $Lang->p('MAIN_TEXT', 2); ?>" />
    </div>
    <p class="center">
        <img src="/pictures/sections/workinprogress.png" alt="" />
    </p>
    <h4 class="center"><?php $Lang->p('MAIN_TEXT', 4); ?><br/><?php $Lang->p('MAIN_TEXT', 5); ?></h4>
</body>
</html>
