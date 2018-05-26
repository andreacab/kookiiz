<?php
    /**********************************************************
    Title: Picture get
    Authors: Kookiiz Team
    Purpose: Returns the picture with ID pic_id
    ***********************************************************/
	
	/**********************************************************
	SET UP
	***********************************************************/
    
	//Include external files
    require_once '../class/dblink.php';
    require_once '../class/globals.php';
    require_once '../class/pictures_lib.php';
    require_once '../class/user.php';
	
	//Init handlers
	$DB     = new DBLink('kookiiz');
    $User   = new User($DB);
	
	/**********************************************************
	SCRIPT
	***********************************************************/

    //Request picture from library
	$PicturesLib = new PicturesLib($DB, $User);
    $PicturesLib->display();
?>