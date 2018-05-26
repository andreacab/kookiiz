<?php
	/**********************************************************
    Title: Recipe display
    Authors: Kookiiz Team
    Purpose: HTML code of the full recipe display tab
	***********************************************************/
?>
<div id="recipe_display">
    <div id="recipe_properties" class="column single">
		<h2 class="display title"></h2>
        <ul id="recipe_display_actions">
            <li class="print">
                <img class="icon15 click print" src="<?php C::p('ICON_URL'); ?>" title="<?php $Lang->p('ACTIONS', 1); ?>" alt="<?php $Lang->p('ACTIONS', 1); ?>" />
                <span class="click"><?php $Lang->p('ACTIONS', 1); ?></span>
            </li>
            <li class="save">
                <img class="icon15 click save" src="<?php C::p('ICON_URL'); ?>" title="<?php $Lang->p('RECIPE_DISPLAY_TEXT', 16); ?>" alt="<?php $Lang->p('KEYWORDS', 6); ?>" />
                <span class="click"><?php $Lang->p('ACTIONS', 0); ?></span>
            </li>
            <li class="translate"<?php if(!$User->isLogged()) echo ' style="display:none;"'; ?>>
                <img class="icon15 click translate" src="<?php C::p('ICON_URL'); ?>" title="<?php $Lang->p('RECIPE_DISPLAY_TEXT', 21); ?>" alt="<?php $Lang->p('ACTIONS', 38); ?>" />
                <span class="click"><?php $Lang->p('ACTIONS', 38); ?></span>
            </li>
            <li class="report"<?php if(!$User->isLogged()) echo ' style="display:none;"'; ?>>
                <img class="icon15 click report" src="<?php C::p('ICON_URL'); ?>" title="<?php $Lang->p('RECIPE_DISPLAY_TEXT', 17); ?>" alt="<?php $Lang->p('ACTIONS', 30); ?>" />
                <span class="click"><?php $Lang->p('ACTIONS', 30); ?></span>
            </li>
            <li class="share"></li>
        </ul>
		<hr />
		<div class="display picture float">
            <p class="caption center bold" style="display:none"><?php $Lang->p('RECIPE_DISPLAY_TEXT', 11); ?></p>
        </div>
		<div class="properties float">
            <div class="icons">
                <div class="display criteria float"></div>
                <div class="display rating float"></div>
            </div>
			<ul class="float list_left">
				<li>
					<img src="<?php C::p('ICON_URL'); ?>" class="icon25 cook" alt="<?php $Lang->p('RECIPE_DISPLAY_TEXT', 1); ?>" title="<?php $Lang->p('RECIPE_DISPLAY_TEXT', 1); ?>" />
					<span class="display author"></span>
				</li>
				<li>
					<img src="<?php C::p('ICON_URL'); ?>" class="icon25 category" title="<?php $Lang->p('RECIPE_DISPLAY_TEXT', 2); ?>" alt="<?php $Lang->p('RECIPE_DISPLAY_TEXT', 2); ?>" />
					<span class="display category"></span>
				</li>
				<li>
					<img src="<?php C::p('ICON_URL'); ?>"class="icon25 origin" title="<?php $Lang->p('RECIPE_DISPLAY_TEXT', 3); ?>" alt="<?php $Lang->p('RECIPE_DISPLAY_TEXT', 3); ?>" />
					<span class="display origin"></span>
				</li>
				<li>
					<img src="<?php C::p('ICON_URL'); ?>" class="icon25 guests" title="<?php $Lang->p('RECIPE_DISPLAY_TEXT', 4); ?>" alt="<?php $Lang->p('RECIPE_DISPLAY_TEXT', 4); ?>" />
					<span class="display guests"></span>
				</li>
			</ul>
			<ul class="float list_right">
				<li>
					<img src="<?php C::p('ICON_URL'); ?>" class="icon25 preparation" title="<?php $Lang->p('RECIPE_DISPLAY_TEXT', 5); ?>" alt="<?php $Lang->p('RECIPE_DISPLAY_TEXT', 5); ?>" />
					<span class="display preparation"></span>
				</li>
				<li>
					<img src="<?php C::p('ICON_URL'); ?>" class="icon25 cooking" title="<?php $Lang->p('RECIPE_DISPLAY_TEXT', 6); ?>" alt="<?php $Lang->p('RECIPE_DISPLAY_TEXT', 6); ?>" />
					<span class="display cooking"></span>
				</li>
				<li>
					<img src="<?php C::p('ICON_URL'); ?>" class="icon25 level" title="<?php $Lang->p('RECIPE_DISPLAY_TEXT', 7); ?>" alt="<?php $Lang->p('RECIPE_DISPLAY_TEXT', 7); ?>" />
					<span class="display level"></span>
				</li>
				<li>
					<img src="<?php C::p('ICON_URL'); ?>" class="icon25 price" title="<?php $Lang->p('RECIPE_DISPLAY_TEXT', 8); ?>" alt="<?php $Lang->p('RECIPE_DISPLAY_TEXT', 8); ?>" />
					<span class="display price"></span>
					<span class="currency_display"></span>
					<span><?php $Lang->p('RECIPE_DISPLAY_TEXT', 9); ?></span>
				</li>
			</ul>
		</div>
	</div>
	<div class="column wide left_side">
		<div id="recipe_description">
			<h5>
				<img class="icon25 books" src="<?php C::p('ICON_URL'); ?>" alt="<?php $Lang->p('RECIPE_DISPLAY_TITLES', 0); ?>" />
				<span><?php $Lang->p('RECIPE_DISPLAY_TITLES', 0); ?></span>
			</h5>
			<p class="display description"></p>
		</div>
	</div>
	<div class="column narrow">
		<div id="recipe_ingredients">
			<h5>
				<img class="icon25 ingredients" src="<?php C::p('ICON_URL'); ?>" alt="<?php $Lang->p('RECIPE_DISPLAY_TITLES', 2); ?>" />
				<span><?php $Lang->p('RECIPE_DISPLAY_TITLES', 2); ?></span>
			</h5>
			<p class="display ingredients"></p>
		</div>
		<div id="recipe_wines" style="display:none">
			<h5>
				<img class="icon25 wine" src="<?php C::p('ICON_URL'); ?>" alt="<?php $Lang->p('RECIPE_DISPLAY_TITLES', 4); ?>" />
				<span><?php $Lang->p('RECIPE_DISPLAY_TITLES', 4); ?></span>
			</h5>
            <p class="display wines"></p>
		</div>		
		<div id="recipe_actions" style="display:none;">
			<h5>
                <img class="icon25 actions" src="<?php C::p('ICON_URL'); ?>" alt="<?php $Lang->p('SHOPPING_TITLES', 2); ?>" />
				<span><?php $Lang->p('RECIPE_DISPLAY_TITLES', 5); ?></span>
			</h5>
			<p class="center">
                <button type="button" class="button_80" id="recipe_edit"><?php $Lang->p('ACTIONS', 25); ?></button>
                <?php if($User->isAdmin()): ?>
				<button type="button" class="button_80" id="admin_recipe_tags"><?php $Lang->p('ACTIONS', 36); ?></button>
				<button type="button" class="button_80" id="admin_recipe_dismiss"><?php $Lang->p('ACTIONS', 41); ?></button>
                <?php endif; ?>
                <?php if($User->isAdminSup()): ?>
                <button type="button" class="button_80" id="admin_recipe_delete"><?php $Lang->p('ACTIONS', 23); ?></button>   
                <?php endif; ?>
			</p>
		</div>
	</div>
	<div class="column single">
		<div id="recipe_comments_module">
			<h5>
				<img class="icon25 book" src="<?php C::p('ICON_URL'); ?>" alt="<?php $Lang->p('RECIPE_DISPLAY_TITLES', 1); ?>" />
				<span><?php $Lang->p('RECIPE_DISPLAY_TITLES', 1); ?></span>
			</h5>
            <?php if($User->isLogged()): ?>
            <div>
                <p class="center">
                    <textarea id="textarea_comment" class="focus" cols="40" rows="5" title="<?php $Lang->p('COMMENTS_TEXT', 4); ?>"><?php $Lang->p('COMMENTS_TEXT', 4); ?></textarea>
                </p>
                <div class="right">
                    <p class="bold small" id="textarea_comment_chars"></p>
                    <span><?php $Lang->p('COMMENTS_TEXT', 12); ?></span>
                    <select id="select_comment_type">
                        <option value="0"><?php $Lang->p('COMMENTS_TYPES', 0); ?></option>
                        <option value="1"><?php $Lang->p('COMMENTS_TYPES', 1); ?></option>
                    </select>
                    <button type="button" class="button_80" id="button_clear_comment"><?php $Lang->p('ACTIONS', 15); ?></button>
                    <button type="button" class="button_80" id="button_send_comment"><?php $Lang->p('ACTIONS', 6); ?></button>
                </div>
            </div>
            <hr/>
            <?php endif; ?>
			<h6><?php $Lang->p('COMMENTS_TEXT', 1); ?></h6>
            <p>
				<select id="recipe_comments_count" style="display:none">
					<option value="5" selected="selected">5</option>
					<option value="10">10</option>
					<option value="15">15</option>
					<option value="20">20</option>
				</select>
				<select id="recipe_comments_type">
					<option value="0" selected="selected"><?php $Lang->p('COMMENTS_TYPES', 0); ?></option>
					<option value="1"><?php $Lang->p('COMMENTS_TYPES', 1); ?></option>
					<option value="-1"><?php $Lang->p('COMMENTS_TEXT', 2); ?></option>
				</select>
				<span id="recipe_comments_perpage"><?php $Lang->p('COMMENTS_TEXT', 3); ?></span>
			</p>
			<p id="recipe_comments_index" class="comments_index right"></p>
			<p id="recipe_comments" class="center"></p>
		</div>
	</div>
</div>