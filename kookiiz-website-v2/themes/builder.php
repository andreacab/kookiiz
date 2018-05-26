<?php
    /**********************************************************
    Title: Builder
    Authors: Kookiiz Team
    Purpose: Handle calls to theme builder
    ***********************************************************/

    /**********************************************************
	SET UP
	***********************************************************/

    //Dependencies
    require_once '../class/dblink.php';
    require_once '../class/globals.php';
    require_once '../class/request.php';
    require_once '../class/session.php';
    require_once '../class/theme_builder.php';
    require_once '../class/user.php';

    //Start session
    Session::start();

    //Init handlers
    $DB      = new DBLink('kookiiz');
    $Request = new RequestHandler();
    $User    = new User($DB);

    //Load parameters
    $theme = $Request->get('theme');

    /**********************************************************
	SCRIPT
	***********************************************************/

    //Allow execution from admins only
    if(!$User->isAdmin())   
        die('Only admins can run this script!');
    //Theme is required
    if(is_null($theme))     
        die('No theme is specified!');

    //Include style definitions for current theme
    require_once '../themes/' . $theme . '/style.php';

    //Run theme builder
    $error = '';
    try
    {       
        $ThemeBuilder = new ThemeBuilder($theme);
        $ThemeBuilder->run();
    }
    catch(Exception $e)
    {
        $error = $e->getMessage();
		ob_end_clean();
    }

    /**********************************************************
	DOM GENERATION
	***********************************************************/
?>
<html lang="en">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <!-- Style sheets -->
        <link rel="stylesheet" href="<?php echo '/themes/', C::THEME, '/css/globals.css'; ?>" media="screen" type="text/css" />
        <!-- Page title -->
        <title>Theme Builder</title>
    </head>
    <body>
    <?php 
        if($error)
            echo $error;
        else
            $ThemeBuilder->summary(); 
    ?>
    </body>
</html>