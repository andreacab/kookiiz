<?php
	/**********************************************************
    Title: Clean-up (ADMIN)
    Authors: Kookiiz Team
    Purpose: Clean-up useless files or DB entries
    ***********************************************************/
	
	/**********************************************************
	SET UP
	***********************************************************/
	
	//Dependencies
	require_once '../class/dblink.php';
	require_once '../class/events_lib.php';
	require_once '../class/globals.php';
	require_once '../class/pictures_lib.php';
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
    
    //Orphan events
    $EventsLib = new EventsLib($DB, $User);
    $events = $EventsLib->cleanOrphans();
    
    //Orphan pictures
    $PicturesLib = new PicturesLib($DB, $User);
    $pictures = $PicturesLib->cleanOrphans();
    
    /**********************************************************
	VIEW
	***********************************************************/
?>
<html lang="en">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <!-- Style sheets -->
        <link rel="stylesheet" href="<?php echo '/themes/', C::THEME, '/css/globals.css'; ?>" media="screen" type="text/css" />
        <!-- Page title -->
        <title>Clean-up module</title>
    </head>
    <body>
        <div style="margin:10px;">
            <h4>Clean-up summary</h4>
            <div>
                <h5>Orphan events</h5>
                <p>Found <?php echo count($events); ?> orphan event(s) to delete.</p>
            </div>
            <div>
                <h5>Orphan pictures</h5>
                <p>Following pictures were deleted.</p>
                <ul>
                    <?php foreach($pictures as $type => $paths): ?>
                    <li>
                        <p class="bold"><?php echo $type; ?></p>
                        <?php if(count($paths)): ?>
                            <ul>
                            <?php foreach($paths as $path): ?>
                                <li><?php echo $path; ?></li>
                            <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <p>none</p>
                        <?php endif; ?>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </body>
</html>