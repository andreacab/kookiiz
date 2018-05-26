<?php
    /**********************************************************
    Title: Terms of use
    Authors: Kookiiz Team
    Purpose: HTML content of the terms of use
	***********************************************************/
	
	/**********************************************************
	SET UP
	***********************************************************/
	
	//Dependencies
    require_once '../class/globals.php';
	require_once '../class/lang_db.php';
    require_once '../class/session.php';

    //Start session
    Session::start();
	
	//Load parameters
	$lang = Session::getLang();
    $Lang = LangDB::getHandler($lang);
?>
<html lang="<?php echo Session::getLang(); ?>">
<head>
    <!-- Meta data -->
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">

    <!-- Screen style sheets -->
    <link rel="stylesheet" href="/min/f=/themes/<?php C::p('THEME'); ?>/css/main.css" media="screen" type="text/css" />

    <!-- Page title -->
	<title><?php $Lang->p('TERMS_ALERTS', 0); ?></title>
</head>
<body style="background:none; margin:20px;">
    <a href="/">
        <img id="kookiiz_logo" src="/pictures/logo.png" alt="<?php $Lang->p('MAIN_TEXT', 2); ?>" title="<?php $Lang->p('ACTIONS_LONG', 2); ?>" />
    </a>
    <div>
        <?php include "../terms/terms_$lang.php"; ?>
    </div>
</body>