<?php
    /**********************************************************
    Title: Ingredients similar (ADMIN)
    Authors: Kookiiz Team
    Purpose: Build ingredients similarities table
    ***********************************************************/

    /**********************************************************
	SET UP
	***********************************************************/
	
	//Dependencies
	require_once '../class/dblink.php';
	require_once '../class/globals.php';
	require_once '../class/ingredients_db.php';
	require_once '../class/user.php';
	
	//Init handlers
	$DB   = new DBLink('kookiiz');
    $User = new User($DB);
	    
    //Allow execution from admins only
	if(!$User->isAdmin())
		die('Only admins can execute this script!');
	
	/**********************************************************
	SCRIPT
	***********************************************************/
    
    $IngredientsDB = new IngredientsDB($DB, 'fr');
    $IngredientsDB->match();
?>
<html lang="en">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <!-- Style sheets -->
        <link rel="stylesheet" href="<?php echo '/themes/', C::THEME, '/css/globals.css'; ?>" media="screen" type="text/css" />
        <!-- Page title -->
        <title>Ingredients match routine</title>
    </head>
    <body>
    <?php $IngredientsDB->matchList(); ?>
    </body>
</html>