<?php
    /**********************************************************
    Title: Builder
    Authors: Kookiiz Team
    Purpose: Import data and prepare files to deploy Kookiiz
    ***********************************************************/

    /**********************************************************
	SET UP
	***********************************************************/
	
	//Dependencies
	require_once '../class/dblink.php';
	require_once '../class/globals.php';
	require_once '../class/ingredients_db.php';
	require_once '../class/request.php';
	require_once '../class/user.php';
    
    //Init handlers
	$DB      = new DBLink('kookiiz');
    $Request = new RequestHandler();
    $User    = new User($DB);
    
    //Allow execution from admins only
	if(!$User->isAdmin())
		die('Only admins can execute this script!');
?>
