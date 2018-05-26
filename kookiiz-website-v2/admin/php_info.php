<?php
	/**********************************************************
    Title: PHP info (ADMIN)
    Authors: Kookiiz Team
    Purpose: Display PHP config
	***********************************************************/
	
	/**********************************************************
	SET UP
	***********************************************************/

    //Dependencies
    require_once '../class/dblink.php';
    require_once '../class/session.php';
    require_once '../class/user.php';

    //Start session
    Session::start();

    //Init handlers
    $DB     = new DBLink('kookiiz');
    $User   = new User($DB);
	
	/**********************************************************
	SCRIPT
	***********************************************************/
	
	//Allow execution from admins only
	if(!$User->isAdmin()) die('You need administrator privileges to run this script!');

    //Display PHP configuration
	phpinfo();
?>
