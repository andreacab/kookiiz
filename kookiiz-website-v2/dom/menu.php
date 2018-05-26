<div class="content">
	<?php include '../dom/menu_content.php'; ?>
	<div style="width:100%;height:1px;clear:both"></div>
</div>
<div class="buttons">
	<img id="menu_back" class="icon40 click arrow_left" src="<?php C::p('ICON_URL'); ?>" title="<?php $Lang->p('MENU_ACTIONS', 5); ?>" alt="<?php $Lang->p('MENU_ACTIONS', 4); ?>" />
	<button type="button" id="menu_today" class="button_100"><?php $Lang->p('MENU_ACTIONS', 8); ?></button>
	<button type="button" id="menu_save" class="button_80" <?php if(!$User->isLogged()) echo 'style="display:none"'; ?>><?php $Lang->p('ACTIONS', 0); ?></button>
	<button type="button" id="menu_reset" class="button_80"><?php $Lang->p('ACTIONS', 15); ?></button>
	<button type="button" id="menu_print" class="button_80"><?php $Lang->p('ACTIONS', 1); ?></button>
	<img id="menu_forward" class="icon40 click arrow_right" src="<?php C::p('ICON_URL'); ?>" title="<?php $Lang->p('MENU_ACTIONS', 7); ?>" alt="<?php $Lang->p('MENU_ACTIONS', 6); ?>" />
</div>