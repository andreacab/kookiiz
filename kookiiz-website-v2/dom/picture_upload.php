<?php
    /**********************************************************
    Title: Picture upload
    Authors: Kookiiz Team
    Purpose: Upload a picture and returns its unique ID
    ***********************************************************/

	/**********************************************************
	SET UP
	***********************************************************/
    
	//Include external files
	require_once '../class/dblink.php';
	require_once '../class/exception.php';
	require_once '../class/pictures_lib.php';
	require_once '../class/user.php';
	
	//Init handlers
	$DB   = new DBLink('kookiiz');
    $User = new User($DB);
	
	/**********************************************************
	CONTROLLER
	***********************************************************/

    $picID = 0; $error = 0;
    try
    {
        $PicturesLib = new PicturesLib($DB, $User);
        $picID = $PicturesLib->upload();
    }
    catch(KookiizException $e)
    {
        if($e->getType() == 'picture')
            $error = $e->getCode();
        else
            $error = 15;
    }
    catch(Exception $e)
    {
        $error = 15;
    }
?>
<html>
    <head>
        <script type="text/javascript" charset="utf-8">
        <?php
            echo "window.PICID = $picID;\n";
            echo "window.ERROR = $error;\n";
        ?>
        </script>
    </head>
    <body></body>
</html>