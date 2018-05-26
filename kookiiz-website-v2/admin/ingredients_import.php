<?php
	/**********************************************************
    Title: Ingredients import (ADMIN)
    Authors: Kookiiz Team
    Purpose: Import ingredients from CSV file into database
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
	
	//Load parameters
    $source = $Request->get('source');
    
    //Allow execution from admins only
	if(!$User->isAdmin())
		die('Only admins can execute this script!');
	
	/**********************************************************
	SCRIPT
	***********************************************************/
    
    //Import ingredients data from CSV
    $IngredientsDB = new IngredientsDB($DB, $User->getLang());
    $IngredientsDB->import($source);
?>
<html lang="en">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <!-- Style sheets -->
        <link rel="stylesheet" href="<?php echo '/themes/', C::THEME, '/css/globals.css'; ?>" media="screen" type="text/css" />
        <!-- Page title -->
        <title>Ingredients import process</title>
    </head>
    <body>
    <?php $IngredientsDB->log(); ?>
    </body>
</html>