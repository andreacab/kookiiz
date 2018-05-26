<?php
	/*******************************************************
	Title: Globals M (JS)
	Authors: Kookiiz Team
	Purpose: Generate Javascript globals file for mobile
	********************************************************/
	
	//Dependencies
    require_once $_SERVER['DOCUMENT_ROOT'] . '/class/dblink.php';
    require_once $_SERVER['DOCUMENT_ROOT'] . '/class/globals_export.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/class/lang_db.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/class/session.php';
    require_once $_SERVER['DOCUMENT_ROOT'] . '/class/style.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/class/user.php';

    //Start session
    Session::start();

    //Init handlers
    $DB   = new DBLink('kookiiz');
    $User = new User($DB);
    $Lang = LangDB::getHandler(Session::getLang(), 'JS', $User->isAdmin());
	
	//Set appropriate header
	header('content-type: application/x-javascript');
    
    /*******************************************************
    CONSTANTS
    ********************************************************/
    
    //Export constants from several objects
    $Globals = new GlobalsExport();
    $Globals->exportConstants('Style', false);
    
    /*******************************************************
    CONSTANT ARRAYS
    ********************************************************/

    //Language arrays
    $Arrays = array(
        'ACTIONS',
        'LANGUAGES_ALERTS',
        'MOBILE_PAGES',
        'VARIOUS'
    );
    foreach($Arrays as $ArrayName)
    {
        $Array = $Lang->get($ArrayName);
        echo "$ArrayName = ", json_encode($Array), ";\n";
    }
?>