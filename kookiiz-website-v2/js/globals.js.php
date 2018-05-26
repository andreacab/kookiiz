<?php
	/*******************************************************
	Title: Globals (JS)
	Authors: Kookiiz Team
	Purpose: Generate Javascript globals file
	********************************************************/
	
	//Dependencies
	require_once '../class/articles_lib.php';
    require_once '../class/dblink.php';
	require_once '../class/events_lib.php';
	require_once '../class/exception.php';
	require_once '../class/globals.php';
	require_once '../class/globals_export.php';
    require_once '../class/ingredients_db.php';
	require_once '../class/lang_db.php';
	require_once '../class/session.php';
	require_once '../class/style.php';
	require_once '../class/units_lib.php';
	require_once '../class/user.php';

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

    //JS constants hash
    echo "var C = {};\n";

    //Export constants from several objects
    $Globals = new GlobalsExport();
    $Globals->exportConstants('ArticlesLib', 'ARTICLE');
    $Globals->exportConstants('C', false);
    $Globals->exportConstants('Error');
    $Globals->exportConstants('EventsLib', 'EVENT');
    $Globals->exportConstants('Style', false);

    /*******************************************************
    CONSTANT ARRAYS
    ********************************************************/

    //Various
	$Globals->exportStatic('C', false);

    //Language arrays
	$Lang->exportJSON();
    
    /*******************************************************
    LIBRARIES
    ********************************************************/

    //Set-up ingredients database handler
    $IngredientsDB = new IngredientsDB($DB, Session::getLang());
    //Export database in compact format
    echo 'var INGS_DB = ', json_encode($IngredientsDB->export()), ";\n";

    //Export units library
    echo "var UNITS_LIB = ", json_encode(UnitsLib::exportAll()), ";\n";
?>