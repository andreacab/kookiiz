<?php
	/**********************************************************
    Title: Feedback popup
    Authors: Kookiiz Team
    Purpose: HTML content of the feedback popup
    ***********************************************************/
	
	//Dependencies
	require_once '../class/lang_db.php';
	require_once '../class/session.php';

    //Start session
    Session::start();

    //Init handlers
    $Lang = LangDB::getHandler(Session::getLang());
?>
<p id="feedback_error" class="error" style="display:none"></p>
<p class="left">
	<select id="select_feedback_type">
	<?php
        $FEEDBACK_TYPES = $Lang->get('FEEDBACK_TYPES');
		foreach($FEEDBACK_TYPES as $index => $type)
		{
			echo '<option value="', $index, '">', $type, '</option>';
		}
	?>
	</select>
	<span class="input_wrap size_180">
		<input type="text" id="input_feedback_content" class="focus" value="<?php $Lang->p('FEEDBACK_CONTENTS', 0); ?>" title="<?php $Lang->p('FEEDBACK_CONTENTS', 0); ?>" />
	</span>
</p>
<p class="center">
	<textarea id="input_feedback_text" class="focus" cols="" rows=""></textarea>
</p>