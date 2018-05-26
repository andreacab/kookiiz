<?php
	/**********************************************************
    Title: Recipe preview
    Authors: Kookiiz Team
    Purpose: HTML content of the recipe preview window
	***********************************************************/
?>
<div id="recipe_preview_left">
	<div id="recipe_preview_rating" class="preview"></div>
	<div id="recipe_preview_plate"></div>
	<img id="recipe_preview_picture" src="<?php echo '/themes/' . C::THEME . '/pictures/preview/plate_center.png'; ?>" alt="<?php $Lang->p('RECIPE_DISPLAY_TEXT', 0); ?>" />
	<div id="recipe_preview_plate_mask"></div>
	<p id="recipe_preview_icons" class="center"></p>
</div>
<div id="recipe_preview_center">
	<h5 id="recipe_preview_title"></h5>
	<div id="recipe_preview_description"></div>
	<div id="recipe_preview_properties">
		<img src="<?php C::p('ICON_URL'); ?>" class="icon15 preparation" alt="<?php $Lang->p('RECIPE_DISPLAY_TEXT', 5); ?>" />
		<span id="recipe_preview_preptime" class="bold"></span>
		<img src="<?php C::p('ICON_URL'); ?>" class="icon15 cooking" alt="<?php $Lang->p('RECIPE_DISPLAY_TEXT', 6); ?>" />
		<span id="recipe_preview_cooktime" class="bold"></span>
		<img src="<?php C::p('ICON_URL'); ?>" class="icon15 price" alt="<?php $Lang->p('RECIPE_DISPLAY_TEXT', 8); ?>" />
		<span id="recipe_preview_price" class="bold"></span>
		<span class="bold"><?php $Lang->p('RECIPE_DISPLAY_TEXT', 9); ?></span>
	</div>
</div>
<div id="recipe_preview_right">
    <h6>
        <img class="icon25 ingredients" src="<?php C::p('ICON_URL'); ?>" alt="<?php $Lang->p('RECIPE_DISPLAY_TITLES', 2); ?>" />
        <span><?php $Lang->p('RECIPE_DISPLAY_TITLES', 2); ?></span>
    </h6>
    <div id="recipe_preview_ingredients"></div>
</div>