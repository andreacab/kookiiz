<?php
    /**********************************************************
    Title: Picture upload popup
    Authors: Kookiiz Team
    Purpose: HTML content of the picture upload popup
	***********************************************************/
	
	/**********************************************************
	SET UP
	***********************************************************/
	
	//Dependencies
	require_once '../class/lang_db.php';
	require_once '../class/pictures_lib.php';
	require_once '../class/request.php';
	require_once '../class/session.php';

    //Start session
    Session::start();

    //Init handlers
    $Lang    = LangDB::getHandler(Session::getLang());
    $Request = new RequestHandler();

	//Load parameters
	$type = $Request->get('type');
	
	/**********************************************************
	CONTROLLER
	***********************************************************/
	
	//Retrieve settings for current picture type
    $settings = PicturesLib::getSettings($type);
    if(is_null($settings)) die();
    
    /**********************************************************
	VIEW
	***********************************************************/
?>
<form id="picture_form" method="post" action="/dom/picture_upload.php" target="iframe_picture_upload" enctype="multipart/form-data">
	<p id="picture_upload_loader" class="center" style="display:none"></p>
	<p id="picture_upload_error" class="error" style="display:none"></p>
	<div id="picture_upload_inputs">
		<p>
            <input type="hidden" name="type" value="<?php echo $type; ?>" />
			<input type="hidden" name="MAX_FILE_SIZE" value="<?php echo $settings['size']; ?>" />
			<input type="file" id="input_picture" name="picture" size="30" />
			<input type="submit" class="button_80" value="<?php $Lang->p('ACTIONS', 6); ?>" />
		</p>
		<p><?php $Lang->p('PICTURES_TEXT', 4); ?></p>
		<?php
			echo '<p>', $Lang->get('PICTURES_TEXT', 0), ': ', (int)($settings['size'] / 1000), $Lang->get('PICTURES_TEXT', 1), '</p>',
					'<p>', $Lang->get('PICTURES_TEXT', 2), ': ', $settings['width'], 'x', $settings['height'], ' ', $Lang->get('PICTURES_TEXT', 3), '</p>';
		?>
	</div>
</form>
<iframe name="iframe_picture_upload" id="iframe_picture_upload" style="display:none"></iframe>