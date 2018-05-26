<?php
	/**********************************************************
    Title: User area
    Authors: Kookiiz Team
    Purpose: HTML content of the user area
    ***********************************************************/
?>
<div id="user_area">
    <!-- Avatar -->
	<div id="user_area_avatar">
        <img id="user_avatar" class="avatar" src="<?php echo '/pictures/users/', C::USER_PIC_DEFAULT; ?>" title="<?php $Lang->p('KEYWORDS', 0); ?>" alt="<?php $Lang->p('KEYWORDS', 1); ?>" />
	</div>

    <!-- Name -->
	<div class="name">
	<?php
		if($User->isLogged())
            echo '<span id="user_area_name" class="click">', $User->getFirstname(), '</span>';
		else
            echo '<span id="user_area_name">Invité</span>';
	?>
	</div>
	<div class="grade">
	<?php
		if($User->isLogged())
            echo '<span id="user_area_grade" class="grade">', $User->grade_display($Lang, $compact = true), '</span>';
	?>
	</div>

    <!-- Notifications -->
	<?php if($User->isLogged()) { ?>
	<div id="user_area_notifications" class="notifications">
		<div class="container friends">
			<img class="icon20 notification friends disabled" src="<?php C::p('ICON_URL'); ?>" alt="<?php $Lang->p('NOTIFICATIONS_TEXT', 19); ?>" title="<?php $Lang->p('NOTIFICATIONS_TEXT', 19); ?>" />
			<div class="friends counter center tiny" style="display:none"></div>
		</div>
		<div class="container invitations">
			<img class="icon20 notification invitations disabled" src="<?php C::p('ICON_URL'); ?>" alt="<?php $Lang->p('NOTIFICATIONS_TEXT', 20); ?>" title="<?php $Lang->p('NOTIFICATIONS_TEXT', 20); ?>" />
			<div class="invitations counter center tiny" style="display:none"></div>
		</div>
		<div class="container shared">
			<img class="icon20 notification shared disabled" src="<?php C::p('ICON_URL'); ?>" alt="<?php $Lang->p('NOTIFICATIONS_TEXT', 21); ?>" title="<?php $Lang->p('NOTIFICATIONS_TEXT', 21); ?>" />
			<div class="shared counter center tiny" style="display:none"></div>
		</div>
	</div>
	<?php } ?>

    <!-- Buttons -->
    <img id="help_button" src="<?php C::p('ICON_URL'); ?>" class="button15 help" alt="<?php $Lang->p('VARIOUS', 18); ?>" title="<?php $Lang->p('VARIOUS', 18); ?>" />
	<?php
		if($User->isLogged())
            echo '<img id="logout_button" src="', C::ICON_URL, '" class="button15 cancel" alt="X" title="', $Lang->get('ACTIONS', 19), '" />';
		else
            echo '<button type="button" class="button_100 signUp">', $Lang->get('ACTIONS', 35), '</button>';
	?>
</div>