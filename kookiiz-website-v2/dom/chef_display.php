<?php
	/**********************************************************
    Title: Chef display
    Authors: Kookiiz Team
    Purpose: HTML content of the chef display tab
	***********************************************************/
?>
<div class="column wide left_side">
	<div>
		<h5>
			<img class="icon25 cook" src="<?php C::p('ICON_URL'); ?>" alt="<?php $Lang->p('KEYWORDS', 1); ?>" />
			<span id="chef_name"></span>
		</h5>
		<p id="chef_bio"></p>
	</div>
</div>
<div class="column narrow">
	<div>
		<div id="chef_picture"></div>
	</div>
	<div>
		<h5>
			<img class="icon25 books" src="<?php C::p('ICON_URL'); ?>" alt="<?php $Lang->p('CHEF_TEXT', 0); ?>" />
			<span></span>
		</h5>
		<div>
			<p id="chef_recipes"><?php $Lang->p('CHEF_TEXT', 0); ?></p>
		</div>
	</div>
</div>