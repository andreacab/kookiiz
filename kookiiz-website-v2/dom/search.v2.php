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
    <div id="recipes_search_controls" class="center">
        <span class="input_wrap size_400 big icon">
            <input type="text" id="recipes_search_input" class="focus enter search" maxlength="50" value="<?php $Lang->p('RECIPES_SEARCH_TEXT', 2); ?>" title="<?php $Lang->p('RECIPES_SEARCH_TEXT', 2); ?>" />
            <img id="recipes_search_button" class="icon25 click search_white" src="<?php C::p('ICON_URL'); ?>" alt="<?php $Lang->p('ACTIONS', 13); ?>" title="<?php $Lang->p('ACTIONS', 13); ?>" />
        </span>
        <div id="recipes_search_selection" class="left">
            <ul>
                <li>
                    <select id="select_search_category" class="medium">
                    <?php
                        //Create an option for each recipe category
                        echo '<option value="0" selected="selected">', $Lang->get('RECIPES_CATEGORIES', 0), '</option>';
                        $RECIPES_CATEGORIES = $Lang->get('RECIPES_CATEGORIES');
                        $categories = array_slice($RECIPES_CATEGORIES, 1);
                        sort($categories);
                        foreach($categories as $name)
                        {
                            $id = array_search($name, $RECIPES_CATEGORIES);
                            echo '<option value="', $id,'">', $name, '</option>';
                        }
                    ?>
                    </select>
                </li>
                <li>
                    <select id="select_search_origin" class="medium">
                    <?php
                        //Create an option for each recipe origin
                        echo '<option value="0" selected="selected">', $Lang->get('RECIPES_ORIGINS', 0), '</option>';
                        $RECIPES_ORIGINS = $Lang->get('RECIPES_ORIGINS');
                        $origins = array_slice($RECIPES_ORIGINS, 1);
                        sort($origins);
                        foreach($origins as $name)
                        {
                            $id = array_search($name, $RECIPES_ORIGINS);
                            echo '<option value="', $id,'">', $name, '</option>';
                        }
                    ?>
                    </select>
                </li>
                <li>
                    <span class="click" id="recipes_search_toggle"><?php $Lang->p('ACTIONS', 44); ?></span>
                </li>
                <li>
                    <span class="click" id="recipes_search_reset"><?php $Lang->p('ACTIONS', 15); ?></span>
                </li>
            </ul>
        </div>
        <div id="recipes_search_extended" style="display:none">
            <div>
                <div>
                    <p class="criteria_section bold"><?php $Lang->p('RECIPES_SEARCH_TEXT', 4); ?></p>
                    <ul id="recipes_search_criteria">
                        <li>
                            <label>
                                <input type="checkbox" id="check_search_favorites" class="check_search" />
                                <img src="<?php C::p('ICON_URL'); ?>" class="icon15 favorite" alt="" />
                                <span class="click"><?php $Lang->p('RECIPES_SEARCH_CRITERIA', 0); ?></span>
                            </label>
                        </li>
                        <li>
                            <label>
                                <input type="checkbox" id="check_search_easy" class="check_search" />
                                <img src="<?php C::p('ICON_URL'); ?>" class="icon15 easy" alt="" />
                                <span class="click"><?php $Lang->p('RECIPES_SEARCH_CRITERIA', 1); ?></span>
                            </label>
                        </li>
                        <li>
                            <label>
                                <input type="checkbox" id="check_search_healthy" class="check_search" />
                                <img src="<?php C::p('ICON_URL'); ?>" class="icon15 healthy" alt="" />
                                <span class="click"><?php $Lang->p('RECIPES_SEARCH_CRITERIA', 2); ?></span>
                            </label>
                        </li>
                        <li>
                            <label>
                                <input type="checkbox" id="check_search_cheap" class="check_search" />
                                <img src="<?php C::p('ICON_URL'); ?>" class="icon15 cheap" alt="" />
                                <span class="click"><?php $Lang->p('RECIPES_SEARCH_CRITERIA', 3); ?></span>
                            </label>
                        </li>
                        <li>
                            <label>
                                <input type="checkbox" id="check_search_quick" class="check_search" />
                                <img src="<?php C::p('ICON_URL'); ?>" class="icon15 quick" alt="" />
                                <span class="click"><?php $Lang->p('RECIPES_SEARCH_CRITERIA', 4); ?></span>
                            </label>
                        </li>
                        <li>
                            <label>
                                <input type="checkbox" id="check_search_success" class="check_search" />
                                <img src="<?php C::p('ICON_URL'); ?>" class="icon15 success" alt="" />
                                <span class="click"><?php $Lang->p('RECIPES_SEARCH_CRITERIA', 5); ?></span>
                            </label>
                        </li>
                        <li>
                            <label>
                                <input type="checkbox" id="check_search_veggie" class="check_search" />
                                <img src="<?php C::p('ICON_URL'); ?>" class="icon15 veggie" alt="" />
                                <span class="click"><?php $Lang->p('RECIPES_SEARCH_CRITERIA', 6); ?></span>
                            </label>
                        </li>
                        <li>
                            <label>
                                <input type="checkbox" id="check_search_liked" class="check_search"/>
                                <img src="<?php C::p('ICON_URL'); ?>" class="icon15 liked" alt="" />
                                <span class="click"><?php $Lang->p('RECIPES_SEARCH_CRITERIA', 9); ?></span>
                            </label>
                        </li>
                        <li>
                            <label>
                                <input type="checkbox" id="check_search_disliked" class="check_search"/>
                                <img src="<?php C::p('ICON_URL'); ?>" class="icon15 disliked" alt="" />
                                <span class="click"><?php $Lang->p('RECIPES_SEARCH_CRITERIA', 10); ?></span>
                            </label>
                        </li>
                    </ul>
                    <p class="criteria_section bold"><?php $Lang->p('RECIPES_SEARCH_TEXT', 5); ?></p>
                    <ul id="recipes_search_criteria2">
                        <li>
                            <input type="checkbox" id="check_search_fridge" class="check_search" />
                            <img src="<?php C::p('ICON_URL'); ?>" class="icon15 fridge" alt="" />
                            <select id="select_search_fridge" class="select_search medium">
                                <option value="-1"><?php $Lang->p('RECIPES_SEARCH_CRITERIA', 7); ?></option>
                                <option value="-1" disabled="disabled"><?php $Lang->p('RECIPES_SEARCH_TEXT', 3); ?></option>
                            </select>
                        </li>
                        <li>
                            <input type="checkbox" id="check_search_season" class="check_search" />
                            <img src="<?php C::p('ICON_URL'); ?>" class="icon15 season" alt="" />
                            <select id="select_search_season" class="select_search medium">
                                <option value="-1"><?php $Lang->p('RECIPES_SEARCH_CRITERIA', 8); ?></option>
                                <option value="-1" disabled="disabled"><?php $Lang->p('RECIPES_SEARCH_TEXT', 3); ?></option>
                            </select>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    
	<div id="recipes_results_controls" style="visibility:hidden; display:none;">
		<span><?php $Lang->p('ACTIONS_LONG', 1); ?></span>
		<select id="recipes_sorting">
			<option value="abc"><?php $Lang->p('RECIPES_SORTING', 0); ?></option>
			<option value="score" selected="selected"><?php $Lang->p('RECIPES_SORTING', 1); ?></option>
			<option value="price"><?php $Lang->p('RECIPES_SORTING', 2); ?></option>
			<option value="rating"><?php $Lang->p('RECIPES_SORTING', 3); ?></option>
		</select>
	</div>
    <div id="recipes_search_index"></div>
	<div id="recipes_search_results" class="center"></div>
	<div id="recipes_search_loader" class="center" style="display:none"></div>
    <p id="recipes_search_description"></p>
</div>