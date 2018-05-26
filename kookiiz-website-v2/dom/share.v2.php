<?php
	/**********************************************************
    Title: Share
    Authors: Kookiiz Team
    Purpose: HTML content of the sharing tab
	***********************************************************/

    $statuses = $Lang->get('STATUS_NAMES');
?>
<!-- Notice for visitors -->
<?php if(!$User->isLogged()) { ?>
<div class="column single">
	<h5>
		<span><?php $Lang->p('SHARE_TITLES', 3); ?></span>
	</h5>
	<p><?php $Lang->p('VISITOR_NOTICES', 0); ?></p>
    <p class="center">
        <img src="/pictures/sections/preview.png" alt="" />
    </p>
    <p class="center">
        <button type="button" class="button_shiny signUp"><?php $Lang->p('FRONTPAGE_TEXT', 1); ?></button>
    </p>
    <hr/>
</div>
<?php } ?>

<!-- Status update -->
<div class="column single" <?php if(!$User->isLogged()){echo 'style="display:none"';} ?>>
	<h5>
		<span><?php $Lang->p('SHARE_TITLES', 0); ?></span>
	</h5>
	<p class="center" id="status_types_area">
	<?php foreach($statuses as $index => $name): ?>
        <span class="status click" data-id="<?php echo $index; ?>"><?php echo $name; ?></span>
	<?php endforeach; ?>
	</p>
	<p id="status_summary_display" style="display:none"></p>
	<div id="status_wrapper">
		<div id="status_input_area">
			<div class="header"></div>
			<div class="content">
				<textarea id="status_comment_input" class="focus" title="<?php $Lang->p('SHARE_TEXTS', 4); ?>" cols="20" rows="25"><?php $Lang->p('SHARE_TEXTS', 4); ?></textarea>
			</div>
			<div class="footer"></div>
		</div>
		<div id="status_picture_area" class="center" style="display:none">
			<span class="tiny bold"><?php $Lang->p('SHARE_TEXTS', 1); ?></span>
			<img id="status_content_picture" src="" style="display:none" alt="<?php $Lang->p('KEYWORDS', 14); ?>" />
		</div>
	</div>
	<div id="status_actions" class="right" style="display:none">
        <label>
            <input type="checkbox" id="status_share_facebook" class="share_check" />
            <img class="icon25 facebook" src="<?php C::p('ICON_URL'); ?>" alt="facebook" />
        </label>
        <label>
            <input type="checkbox" id="status_share_twitter" class="share_check" />
            <img class="icon25 tweet" src="<?php C::p('ICON_URL'); ?>" alt="tweet" />
        </label>
        <button id="status_cancel_button" class="button_80"><?php $Lang->p('ACTIONS', 5); ?></button>
		<button id="status_share_button" class="button_80"><?php $Lang->p('SHARE_TEXTS', 3); ?></button>
	</div>
<hr/>
</div>

<!-- Events -->
<div class="column single" <?php if(!$User->isLogged()){echo 'style="display:none"';} ?>>
	<h5>
		<span><?php $Lang->p('SHARE_TITLES', 1); ?></span>
	</h5>
    <?php if(false): ?>
    <ul id="events_types">
    <?php
        $events_types = $Lang->get('EVENTS_TYPES_NAMES');
        foreach($events_types as $id => $name)
        {
            echo    '<li class="event_type">',
                        '<label>', 
                            '<input type="checkbox" id="event_type_', $id, '" checked="checked" />',
                            '<span class="click">', $name, '</span>',
                        '</label>',
                    '</li>';
        }
    ?>
    </ul>
    <?php endif; ?>
	<div id="share_events" class="center"></div>
</div>