<?php
	/**********************************************************
    Title: Search
    Authors: Kookiiz Team
    Purpose: HTML content of the recipe search result area
	***********************************************************/
?>
<div id="recipes_themes">
	<div id="recipes_themes_display">
		<div>
			<h4 class="title bold"><?php $Lang->p('RECIPES_THEMES_TEXT', 1); ?></h4>
            <p class="bold" id="recipes_themes_refresh">
                <img src="<?php C::p('ICON_URL'); ?>" class="icon15 refresh click" alt="<?php $Lang->p('ACTIONS', 37); ?>" title="<?php $Lang->p('ACTIONS', 37); ?>" />
                <span class="click"><?php $Lang->p('ACTIONS', 37); ?></span>
            </p>
			<div class="display"></div>
		</div>
	</div>
	<div id="recipes_themes_border"></div>
	<div id="recipes_themes_tab" class="selected">
		<h5 class="bold center"><?php $Lang->p('ACTIONS', 16); ?></h5>
	</div>
</div>
<div id="recipes_search_area">
	<div id="recipes_search_index" style="display:none"></div>
    
	<p id="recipes_results_controls" class="right" style="visibility:hidden">
		<span><?php $Lang->p('ACTIONS_LONG', 1); ?></span>
		<select id="recipes_sorting">
			<option value="abc"><?php $Lang->p('RECIPES_SORTING', 0); ?></option>
			<option value="score" selected="selected"><?php $Lang->p('RECIPES_SORTING', 1); ?></option>
			<option value="price"><?php $Lang->p('RECIPES_SORTING', 2); ?></option>
			<option value="rating"><?php $Lang->p('RECIPES_SORTING', 3); ?></option>
		</select>
	</p>
	<div id="recipes_search_results" class="center"></div>
	<div id="recipes_search_loader" class="center" style="display:none"></div>
    <p id="recipes_search_description"></p>
</div>