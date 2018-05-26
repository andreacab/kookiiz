<?php
	/**********************************************************
    Title: Video popup
    Authors: Kookiiz Team
    Purpose: HTML content of the popup to play videos
    ***********************************************************/

	/**********************************************************
	SET UP
	***********************************************************/

    //Dependencies
	require_once '../class/request.php';

    //Init handlers
    $Request = new RequestHandler();

    //Load parameters
    $video = (int)$Request->get('video');
?>
<div class="center">
    <iframe class="center" src="http://player.vimeo.com/video/<?php echo $video; ?>" width="400" height="300" frameborder="0"></iframe>
</div>