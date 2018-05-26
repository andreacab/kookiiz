<?php
	/**********************************************************
    Title: Panels left
    Authors: Kookiiz Team
    Purpose: HTML code of the left-hand side panels
    ***********************************************************/

	//PANEL 0 : SEARCH
	//This panel contains search controls to look for recipes in database according to several criteria.
?>
<?php if(KVERSION == 1): ?>
<div id="panel_0" class="kookiiz_panel" style="display:none">
    <div class="panel_handle text_color0 left">
        <div class="handle">
            <img class="icon20_white panel_0" src="<?php C::p('ICON_URL'); ?>" alt="<?php $Lang->p('PANELS_TITLES', 0); ?>" />
            <span class="bold"><?php $Lang->p('PANELS_TITLES', 0); ?></span>
        </div>
		<img class="panel_help icon15 click help" src="<?php C::p('ICON_URL'); ?>" title="<?php $Lang->p('PANELS_TEXT', 1); ?>" alt="<?php $Lang->p('PANELS_TEXT', 1); ?>" />
		<img class="panel_toggle icon15 click arrow_up" src="<?php C::p('ICON_URL'); ?>" title="<?php $Lang->p('PANELS_TEXT', 7); ?>" alt="<?php $Lang->p('PANELS_TEXT', 3); ?>" />
	</div>
    <div class="panel_box">
        <h6 class="panel_header bold center"><?php $Lang->p('PANELS_HEADERS', 0); ?></h6>
        <div class="panel_content">
            <div>
				<div class="center">
					<span class="input_wrap size_180 icon">
						<input type="text" id="input_recipes_search" class="focus enter search" maxlength="25" value="<?php $Lang->p('PANEL_SEARCH_TEXT', 1); ?>" title="<?php $Lang->p('PANEL_SEARCH_TEXT', 1); ?>" />
						<img id="icon_recipe_search" class="icon15_white click search" src="<?php C::p('ICON_URL'); ?>" title="<?php $Lang->p('ACTIONS', 13); ?>" alt="<?php $Lang->p('ACTIONS', 13); ?>" />
					</span>
                </div>
                <div class="right">
					<p id="recipes_search_toggle" class="click"><?php $Lang->p('PANEL_SEARCH_TEXT', 0); ?></p>
                    <button id="recipes_search_button" class="button_80" type="button"><?php $Lang->p('ACTIONS', 39); ?></button>
                </div>
                <div id="search_criteria" style="display:none">
					<div>
						<hr />
						<table class="left">
						<tbody>
							<tr>
								<td><?php $Lang->p('PANEL_SEARCH_TEXT', 5); ?></td>
								<td>
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
								</td>
							</tr>
							<tr>
								<td><?php $Lang->p('PANEL_SEARCH_TEXT', 6); ?></td>
								<td>
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
								</td>
							</tr>
						</tbody>
						</table>
						<ul>
							<li>
								<label>
                                    <input type="checkbox" id="check_search_favorites" class="check_search" />
                                    <img src="<?php C::p('ICON_URL'); ?>" class="icon15 favorite" alt="" />
                                    <span class="click"><?php $Lang->p('PANEL_SEARCH_TEXT', 7); ?></span>
                                </label>
							</li>
							<li>
								<label>
                                    <input type="checkbox" id="check_search_easy" class="check_search" />
                                    <img src="<?php C::p('ICON_URL'); ?>" class="icon15 easy" alt="" />
                                    <span class="click"><?php $Lang->p('PANEL_SEARCH_TEXT', 8); ?></span>
                                </label>
							</li>
							<li>
								<label>
                                    <input type="checkbox" id="check_search_healthy" class="check_search" />
                                    <img src="<?php C::p('ICON_URL'); ?>" class="icon15 healthy" alt="" />
                                    <span class="click"><?php $Lang->p('PANEL_SEARCH_TEXT', 9); ?></span>
                                </label>
							</li>
							<li>
								<label>
                                    <input type="checkbox" id="check_search_cheap" class="check_search" />
                                    <img src="<?php C::p('ICON_URL'); ?>" class="icon15 cheap" alt="" />
                                    <span class="click"><?php $Lang->p('PANEL_SEARCH_TEXT', 10); ?></span>
                                </label>
							</li>
							<li>
								<label>
                                    <input type="checkbox" id="check_search_quick" class="check_search" />
                                    <img src="<?php C::p('ICON_URL'); ?>" class="icon15 quick" alt="" />
                                    <span class="click"><?php $Lang->p('PANEL_SEARCH_TEXT', 11); ?></span>
                                </label>
							</li>
							<li>
								<label>
                                    <input type="checkbox" id="check_search_success" class="check_search" />
                                    <img src="<?php C::p('ICON_URL'); ?>" class="icon15 success" alt="" />
                                    <span class="click"><?php $Lang->p('PANEL_SEARCH_TEXT', 12); ?></span>
                                </label>
							</li>
							<li>
								<label>
                                    <input type="checkbox" id="check_search_veggie" class="check_search" />
                                    <img src="<?php C::p('ICON_URL'); ?>" class="icon15 veggie" alt="" />
                                    <span class="click"><?php $Lang->p('PANEL_SEARCH_TEXT', 19); ?></span>
                                </label>
							</li>
							<li style="display:none">
								<input type="checkbox" id="check_search_chef" class="check_search" />
								<img src="<?php C::p('ICON_URL'); ?>" class="icon15 chef" alt="" />
								<select id="select_search_chef" class="select_search medium">
									<option value="-1"><?php $Lang->p('PANEL_SEARCH_TEXT', 13); ?></option>
									<?php
                                    //Create an option for each Kookiiz chef
                                    /*
										$request = 'SELECT * FROM chefs';
										$stmt = $DB->query($request);
										while($chef = $stmt->fetch())
										{
											echo    '<option value="', $chef['chef_id'],'">',
                                                        htmlspecialchars($chef['chef_name'], ENT_COMPAT, "UTF-8"),
                                                    '</option>';
										}
                                     */
									?>
								</select>
							</li>
							<li>
								<input type="checkbox" id="check_search_fridge" class="check_search" />
								<img src="<?php C::p('ICON_URL'); ?>" class="icon15 fridge" alt="" />
								<select id="select_search_fridge" class="select_search medium">
									<option value="-1"><?php $Lang->p('PANEL_SEARCH_TEXT', 14); ?></option>
									<option value="-1" disabled="disabled"><?php $Lang->p('PANEL_SEARCH_TEXT', 15); ?></option>
								</select>
							</li>
							<li>
								<input type="checkbox" id="check_search_season" class="check_search" />
								<img src="<?php C::p('ICON_URL'); ?>" class="icon15 season" alt="" />
								<select id="select_search_season" class="select_search medium">
									<option value="-1"><?php $Lang->p('PANEL_SEARCH_TEXT', 16); ?></option>
									<option value="-1" disabled="disabled"><?php $Lang->p('PANEL_SEARCH_TEXT', 15); ?></option>
								</select>
							</li>
							<li>
								<label>
                                    <input type="checkbox" id="check_search_liked" class="check_search"/>
                                    <img src="<?php C::p('ICON_URL'); ?>" class="icon15 liked" alt="" />
                                    <span class="click"><?php $Lang->p('PANEL_SEARCH_TEXT', 20); ?></span>
                                </label>
							</li>
							<li>
								<label>
                                    <input type="checkbox" id="check_search_disliked" class="check_search"/>
                                    <img src="<?php C::p('ICON_URL'); ?>" class="icon15 disliked" alt="" />
                                    <span class="click"><?php $Lang->p('PANEL_SEARCH_TEXT', 17); ?></span>
                                </label>
							</li>
							<li style="display:none">
								<label>
                                    <input type="checkbox" id="check_search_allergy" class="check_search" />
                                    <img src="<?php C::p('ICON_URL'); ?>" class="icon15 allergy" alt="" />
                                    <span class="click"><?php $Lang->p('PANEL_SEARCH_TEXT', 18); ?></span>
                                </label>
							</li>
						</ul>
						<p class="center" style="display:none">
							<button type="button" id="menu_express" class="button_100"><?php $Lang->p('MENU_ACTIONS', 0); ?></button>
						</p>
					</div>
                </div>
            </div>
        </div>
    </div>
    <div class="panel_footer"></div>
</div>
<?php endif; ?>
<?php
	//PANEL 1 : RECIPES
	//This panel displays a list of favorites/recently viewed/searched/added recipes.
?>
<div id="panel_1" class="kookiiz_panel<?php echo $User->isLogged() ? '' : ' disabled'; ?>" style="display:none">
    <div class="panel_handle text_color0 left">
        <div class="handle">
            <img class="icon20_white panel_1" src="<?php C::p('ICON_URL'); ?>" alt="<?php $Lang->p('PANELS_TITLES', 1); ?>" />
            <span class="bold"><?php $Lang->p('PANELS_TITLES', 1); ?></span>
        </div>
		<img class="panel_help icon15 click help" src="<?php C::p('ICON_URL'); ?>" title="<?php $Lang->p('PANELS_TEXT', 1); ?>" alt="<?php $Lang->p('PANELS_TEXT', 1); ?>" />
		<img class="panel_toggle icon15 click arrow_up" src="<?php C::p('ICON_URL'); ?>" title="<?php $Lang->p('PANELS_TEXT', 7); ?>" alt="<?php $Lang->p('PANELS_TEXT', 3); ?>" />
	</div>
    <div class="panel_box">
        <h6 class="panel_header bold center"><?php $Lang->p('PANELS_HEADERS', 1); ?></h6>
        <div class="panel_content">
            <div>
				<p class="center">
					<select id="recipe_box_sorting">
						<option value="abc" selected="selected"><?php $Lang->p('RECIPES_SORTING', 0); ?></option>
						<option value="price"><?php $Lang->p('RECIPES_SORTING', 2); ?></option>
						<option value="rating"><?php $Lang->p('RECIPES_SORTING', 3); ?></option>
					</select>
				</p>
				<div class="center" id="recipe_box_types">
					<div id="recipe_tab_favorite" class="recipe_tab selected">
						<div class="top"></div>
						<div class="content">
							<img id="recipe_type_favorite" class="icon20 click favorite" src="<?php C::p('ICON_URL'); ?>" alt="<?php $Lang->p('PANEL_RECIPES_TEXT', 0); ?>" />
						</div>
					</div>
					<div id="recipe_tab_menu" class="recipe_tab">
						<div class="top"></div>
						<div class="content">
							<img id="recipe_type_menu" class="icon20 click menu" src="<?php C::p('ICON_URL'); ?>" alt="<?php $Lang->p('PANEL_RECIPES_TEXT', 1); ?>" />
						</div>
					</div>
					<div id="recipe_tab_viewed" class="recipe_tab">
						<div class="top"></div>
						<div class="content">
							<img id="recipe_type_viewed" class="icon20 click viewed" src="<?php C::p('ICON_URL'); ?>" alt="<?php $Lang->p('PANEL_RECIPES_TEXT', 2); ?>" />
						</div>
					</div>
					<div id="recipe_tab_searched" class="recipe_tab">
						<div class="top"></div>
						<div class="content">
							<img id="recipe_type_searched" class="icon20 click searched" src="<?php C::p('ICON_URL'); ?>" alt="<?php $Lang->p('PANEL_RECIPES_TEXT', 3); ?>" />
						</div>
					</div>
				</div>
				<div id="recipe_box">
					<div class="box_top"></div>
					<div class="box_middle center">
						<div id="recipe_box_criteria">
                        <?php
                            $criteria       = C::get('RECIPES_CRITERIA');
                            $criteria_names = $Lang->get('RECIPES_CRITERIA_NAMES');
                            foreach($criteria as $index => $criteria)
                            {
                                $name = $criteria_names[$index];
                                echo '<img src="', C::ICON_URL, '" class="criteria ', $criteria, ' icon15 disabled click" alt="', $name, '" title="', $name, '" />';
                            }
                        ?>
						</div>
						<div id="recipe_box_content" class="center"></div>
						<div id="recipe_box_drop" class="center" style="display:none"><?php $Lang->p('PANEL_RECIPES_TEXT', 5); ?></div>
					</div>
					<div class="box_bottom"></div>
					<div style="clear:both;"></div>
				</div>
				<div id="recipe_box_contribute" class="center">
					<a id="recipeform_link" href="javascript:void(0);" class="bold"><?php $Lang->p('PANEL_RECIPES_TEXT', 4); ?></a>
					<span>(+<?php C::p('COOKIES_VALUE_RECIPE'); ?></span>
					<img class="icon15 cookie1" src="<?php C::p('ICON_URL'); ?>" alt="<?php $Lang->p('KEYWORDS', 4); ?>" />
					<span>)</span>
				</div>
            </div>
        </div>
    </div>
    <div class="panel_footer"></div>
</div>
<?php
	//PANEL 6 : CHEFS
	//This panel displays a quick preview of a chef, either randomly chosen or according to currently displayed recipe.
	if(false):
?>
<div id="panel_6" class="kookiiz_panel" style="display:none">
    <div class="panel_handle text_color0 left">
        <div class="handle">
            <img class="icon20_white panel_6" src="<?php C::p('ICON_URL'); ?>" alt="<?php $Lang->p('PANELS_TITLES', 6); ?>" />
            <span class="bold"><?php $Lang->p('PANELS_TITLES', 6); ?></span>
        </div>
		<img class="panel_help icon15 click help" src="<?php C::p('ICON_URL'); ?>" title="<?php $Lang->p('PANELS_TEXT', 1); ?>" alt="<?php $Lang->p('PANELS_TEXT', 1); ?>" />
		<img class="panel_toggle icon15 click arrow_up" src="<?php C::p('ICON_URL'); ?>" title="<?php $Lang->p('PANELS_TEXT', 7); ?>" alt="<?php $Lang->p('PANELS_TEXT', 3); ?>" />
	</div>
    <div class="panel_box">
        <h6 class="panel_header bold center"><?php $Lang->p('PANELS_HEADERS', 6); ?></h6>
        <div class="panel_content">
            <div>
				<div id="chef_preview"></div>
            </div>
        </div>
    </div>
    <div class="panel_footer"></div>
</div>
<?php
	endif;
	//PANEL 7 : WEIGHT WATCHER
	//This panel displays the number of points of a recipe according to Weight Watchers' formula.
	if(false):
?>
<div id="panel_7" class="kookiiz_panel" style="display:none">
    <div class="panel_handle text_color0 left">
        <div class="handle">
            <img class="icon20_white panel_7" src="<?php C::p('ICON_URL'); ?>" alt="<?php $Lang->p('PANELS_TITLES', 7); ?>" />
            <span class="bold"><?php $Lang->p('PANELS_TITLES', 7); ?></span>
        </div>
		<img class="panel_help icon15 click help" src="<?php C::p('ICON_URL'); ?>" title="<?php $Lang->p('PANELS_TEXT', 1); ?>" alt="<?php $Lang->p('PANELS_TEXT', 1); ?>" />
		<img class="panel_toggle icon15 click arrow_up" src="<?php C::p('ICON_URL'); ?>" title="<?php $Lang->p('PANELS_TEXT', 7); ?>" alt="<?php $Lang->p('PANELS_TEXT', 3); ?>" />
	</div>
    <div class="panel_box">
        <h6 class="panel_header bold center"><?php echo $PANELS_HEADERS[7]; ?></h6>
        <div class="panel_content">
            <div>
				<div id="ww_points">
					<span id="ww_points_preview"></span>
				</div>
            </div>
        </div>
    </div>
    <div class="panel_footer"></div>
</div>
<?php
	endif;
	//PANEL 9 : PARTNER
	//This panel contains banners of Kookiiz content partners.
?>
<div id="panel_9" class="kookiiz_panel frozen" style="display:none">
	<div class="panel_handle text_color0 left">
        <div class="handle">
            <img class="icon20_white panel_9" src="<?php C::p('ICON_URL'); ?>" alt="<?php $Lang->p('PANELS_TITLES', 9); ?>" />
            <span class="bold"><?php $Lang->p('PANELS_TITLES', 9); ?></span>
        </div>
        <img class="panel_help icon15 click help" src="<?php C::p('ICON_URL'); ?>" title="<?php $Lang->p('PANELS_TEXT', 1); ?>" alt="<?php $Lang->p('PANELS_TEXT', 1); ?>" style="display:none" />
		<img class="panel_toggle icon15 click arrow_up" src="<?php C::p('ICON_URL'); ?>" title="<?php $Lang->p('PANELS_TEXT', 7); ?>" alt="<?php $Lang->p('PANELS_TEXT', 3); ?>" />
	</div>
    <div class="panel_box">
        <h6 class="panel_header bold center"><?php $Lang->p('PANELS_HEADERS', 9); ?></h6>
        <div class="panel_content">
            <div>
				<div id="partner_display" class="center"></div>
            </div>
        </div>
    </div>
    <div class="panel_footer"></div>
</div>
<?php
	//PANEL 10 : GLOSSARY
	//This panel contains a search field to look for keywords in glossary. Definitions are shown directly on the panel and keywords are suggested according to current context.
?>
<div id="panel_10" class="kookiiz_panel" style="display:none">
	<div class="panel_handle text_color0 left">
        <div class="handle">
            <img class="icon20_white panel_10" src="<?php C::p('ICON_URL'); ?>" alt="<?php $Lang->p('PANELS_TITLES', 10); ?>" />
            <span class="bold"><?php $Lang->p('PANELS_TITLES', 10); ?></span>
        </div>
		<img class="panel_help icon15 click help" src="<?php C::p('ICON_URL'); ?>" title="<?php $Lang->p('PANELS_TEXT', 1); ?>" alt="<?php $Lang->p('PANELS_TEXT', 1); ?>" />
		<img class="panel_toggle icon15 click arrow_up" src="<?php C::p('ICON_URL'); ?>" title="<?php $Lang->p('PANELS_TEXT', 7); ?>" alt="<?php $Lang->p('PANELS_TEXT', 3); ?>" />
	</div>
    <div class="panel_box">
        <h6 class="panel_header bold center"><?php $Lang->p('PANELS_HEADERS', 10); ?></h6>
        <div class="panel_content">
            <div class="center">
				<span id="glossary_search_controls" class="input_wrap size_180 icon">
					<input type="text" id="input_glossary_search" class="focus enter search" maxlength="25" value="<?php $Lang->p('ACTIONS', 13); ?>" title="<?php $Lang->p('ACTIONS', 13); ?>" />
					<img id="icon_glossary_search" class="icon15_white click search" src="<?php C::p('ICON_URL'); ?>" title="<?php $Lang->p('ACTIONS', 13); ?>" alt="<?php $Lang->p('ACTIONS', 13); ?>" />
				</span>
				<div id="glossary_results" class="center"></div>
            </div>
        </div>
    </div>
    <div class="panel_footer"></div>
</div>
<?php
	//PANEL 11 : HEALTH PROFILE
	//Here the user can set its nutritionnal habits.
?>
<div id="panel_11" class="kookiiz_panel<?php echo $User->isLogged() ? '' : ' disabled'; ?>" style="display:none">
    <div class="panel_handle text_color0 left">
        <div class="handle">
            <img class="icon20_white panel_11" src="<?php C::p('ICON_URL'); ?>" alt="<?php $Lang->p('PANELS_TITLES', 11); ?>" />
            <span class="bold"><?php echo $Lang->p('PANELS_TITLES', 11); ?></span>
        </div>
		<img class="panel_help icon15 click help" src="<?php C::p('ICON_URL'); ?>" title="<?php $Lang->p('PANELS_TEXT', 1); ?>" alt="<?php $Lang->p('PANELS_TEXT', 1); ?>" />
		<img class="panel_toggle icon15 click arrow_up" src="<?php C::p('ICON_URL'); ?>" title="<?php $Lang->p('PANELS_TEXT', 7); ?>" alt="<?php $Lang->p('PANELS_TEXT', 3); ?>" />
	</div>
    <div class="panel_box">
        <h6 class="panel_header bold center"><?php $Lang->p('PANELS_HEADERS', 11); ?></h6>
        <div class="panel_content">
            <div>
				<p class="center">
					<select id="health_profile_category">
                    <?php
                        $HEALTH_PROFILE_CATEGORIES = $Lang->get('HEALTH_PROFILE_CATEGORIES');
                        foreach($HEALTH_PROFILE_CATEGORIES as $index => $category)
                        {
                            echo '<option value="', $index, '">', $category, '</option>';
                        }
                    ?>
					</select>
				</p>
				<!-- Anatomy -->
				<div id="health_profile_anatomy" class="health_profile_category">
					<table class="left">
					<tbody>
						<tr>
							<td>
								<span><?php $Lang->p('HEALTH_PROFILE_ANATOMY', 0); ?></span>
							</td>
							<td>
								<select id="health_profile_height" class="health_profile_select anatomy">
								<?php
									for($i = User::HEIGHT_MIN; $i <= User::HEIGHT_MAX; $i++)
									{	
										echo '<option value="', $i, '">', $i, '</option>';
									}
								?>
								</select>
							</td>
						</tr>
						<tr>
							<td>
								<span><?php $Lang->p('HEALTH_PROFILE_ANATOMY', 1); ?></span>
							</td>
							<td>
								<select id="health_profile_weight" class="health_profile_select anatomy">
								<?php
									for($i = User::WEIGHT_MIN; $i <= User::WEIGHT_MAX; $i++)
									{	
										echo '<option value="', $i, '">', $i, '</option>';
									}
								?>
								</select>
							</td>
						</tr>
						<tr>
							<td>
								<span><?php $Lang->p('HEALTH_PROFILE_ANATOMY', 2); ?></span>
							</td>
							<td>
								<span id="health_profile_imc" class="bold"></span>
							</td>
						</tr>
						<tr>
							<td>
								<span><?php $Lang->p('HEALTH_PROFILE_ANATOMY', 3); ?></span>
							</td>
							<td>
								<select id="health_profile_gender" class="health_profile_select anatomy">
								<?php
									echo '<option value="', C::USER_GENDER_FEMALE, '">', $Lang->get('HEALTH_PROFILE_ANATOMY', 4), '</option>';
									echo '<option value="', C::USER_GENDER_MALE, '">', $Lang->get('HEALTH_PROFILE_ANATOMY', 5), '</option>';
								?>
								</select>
							</td>
						</tr>
						<tr>
							<td>
								<span><?php $Lang->p('HEALTH_PROFILE_ANATOMY', 6); ?></span>
							</td>
							<td>
								<select id="health_profile_birth" class="health_profile_select anatomy">
								<?php
									$year = date('Y');
									for($i = $year - User::AGE_MAX, $imax = $year - User::AGE_MIN; $i <= $imax; $i++)
									{	
										echo '<option value="', $i, '">', $i, '</option>';
									}
								?>
								</select>
							</td>
						</tr>
					</tbody>
					</table>
				</div>
				<!-- Breakfast -->
				<div id="health_profile_breakfast" class="health_profile_category" style="display:none">
					<p><?php $Lang->p('HEALTH_TEXT', 4); ?></p>
					<div id="health_breakfast_display">
						<div class="top"></div>
						<div class="middle"></div>
						<div class="bottom"></div>
					</div>
				</div>
				<!-- Activity -->
				<div id="health_profile_activity" class="health_profile_category" style="display:none">
					<table>
					<tbody>
						<tr>
							<td colspan="2">
								<span class="bold"><?php $Lang->p('HEALTH_PROFILE_ACTIVITY', 0); ?></span>
							</td>
						</tr>
						<tr>
							<td colspan="2" class="center">
								<select id="health_profile_occupation" class="health_profile_select activity">
								<?php
                                    $occupations = $Lang->get('USER_ACTIVITY_OCCUPATION');
                                    $values      = C::get('USER_ACTIVITY_VALUES', 0);
									foreach($values as $index => $value)
									{
										echo '<option value="', $value, '">', $occupations[$index], '</option>';
									}
								?>
								</select>
							</td>
						</tr>
						<tr>
							<td class="left">
								<span><?php $Lang->p('HEALTH_PROFILE_ACTIVITY', 1); ?></span>
							</td>
							<td class="left">
								<select id="health_profile_occupation_rate" class="health_profile_select activity">
								<?php
                                    $occupations_rates  = $Lang->get('USER_ACTIVITY_OCCUPATIONRATE');
                                    $values             = C::get('USER_ACTIVITY_VALUES', 1);
									foreach($values as $index => $value)
									{
										echo '<option value="', $value, '">', $occupations_rates[$index], '</option>';
									}
								?>
								</select>
							</td>
						</tr>
						<tr>
							<td class="left">
								<span><?php $Lang->p('HEALTH_PROFILE_ACTIVITY', 2); ?></span>
							</td>
							<td class="left">
								<select id="health_profile_transport" class="health_profile_select activity">
								<?php
                                    $transports = $Lang->get('USER_ACTIVITY_TRANSPORT');
                                    $values     = C::get('USER_ACTIVITY_VALUES', 2);
									foreach($values as $index => $value)
									{
										echo '<option value="', $value, '">', $transports[$index], '</option>';
									}
								?>
								</select>
							</td>
						</tr>
						<tr>
							<td colspan="2">
								<span class="bold"><?php $Lang->p('HEALTH_PROFILE_ACTIVITY', 3); ?></span>
							</td>
						</tr>
						<tr>
							<td class="left">
								<span><?php $Lang->p('HEALTH_PROFILE_ACTIVITY', 4); ?></span>
							</td>
							<td class="left">
								<select id="health_profile_lazy" class="health_profile_select activity">
								<?php
                                    $lazy   = $Lang->get('USER_ACTIVITY_LAZY');
                                    $values = C::get('USER_ACTIVITY_VALUES', 4);
									foreach($values as $index => $value)
									{
										echo '<option value="', $value, '">', $lazy[$index], '</option>';
									}
								?>
								</select>
							</td>
						</tr>
						<tr>
							<td class="left">
								<span><?php $Lang->p('HEALTH_PROFILE_ACTIVITY', 5); ?></span>
							</td>
							<td class="left">
								<select id="health_profile_laying" class="health_profile_select activity">
								<?php
                                    $laying = $Lang->get('USER_ACTIVITY_LAYING');
                                    $values = C::get('USER_ACTIVITY_VALUES', 5);
									foreach($values as $index => $value)
									{
										echo '<option value="', $value, '">', $laying[$index], '</option>';
									}
								?>
								</select>
							</td>
						</tr>
						<tr>
							<td class="left">
								<span><?php $Lang->p('HEALTH_PROFILE_ACTIVITY', 8); ?></span>
							</td>
							<td class="left">
								<select id="health_profile_walk" class="health_profile_select activity">
								<?php
                                    $walk   = $Lang->get('USER_ACTIVITY_WALK');
                                    $values = C::get('USER_ACTIVITY_VALUES', 3);
									foreach($values as $index => $value)
									{
										echo '<option value="', $value, '">', $walk[$index], '</option>';
									}
								?>
								</select>
							</td>
						</tr>
					</tbody>
					</table>
					<hr/>
					<p>
						<span class="bold"><?php $Lang->p('HEALTH_PROFILE_ACTIVITY', 6); ?></span>
					</p>
					<div class="sports_controls center">
						<select id="health_profile_sport" class="health_profile_select sport large">
						<?php
                            $sports_names = $Lang->get('SPORTS_NAMES');
							foreach($sports_names as $sport_id => $sport_name)
							{
								echo '<option value="', $sport_id, '">', $sport_name, '</option>';
							}
						?>
						</select>
					</div>
					<div class="sports_controls center">
						<span><?php $Lang->p('HEALTH_PROFILE_ACTIVITY', 7); ?></span>
						<select id="health_profile_sport_frequency" class="health_profile_select sport">
						<?php
                            $sports_frequencies = $Lang->get('SPORTS_FREQUENCIES');
							foreach($sports_frequencies as $freq_id => $freq_name)
							{
								echo '<option value="', $freq_id, '">', $freq_name, '</option>';
							}
						?>
						</select>
						<img class="button15 plus" src="<?php C::p('ICON_URL'); ?>" id="health_sport_add"  alt="+" />
					</div>
					<div id="health_sports_display"></div>
				</div>
				<!-- Quick meals -->
				<div id="health_profile_quickmeal" class="health_profile_category" style="display:none">
					<p><?php $Lang->p('HEALTH_TEXT', 9); ?></p>
					<div id="quick_meals_display"></div>
				</div>
				<div id="health_profile_controls">
					<hr />
					<p class="center">
						<button type="button" id="health_breakfast_add" class="button_80" style="display:none"><?php $Lang->p('ACTIONS', 27); ?></button>
						<button type="button" id="health_profile_save" class="button_80"><?php $Lang->p('ACTIONS', 0); ?></button>
						<button type="button" id="health_quickmeal_create" class="button_80" style="display:none"><?php $Lang->p('ACTIONS', 18); ?></button>
					</p>
				</div>
            </div>
        </div>
    </div>
    <div class="panel_footer"></div>
</div>
<?php
	//PANEL 14 : USER INFO
	//This panel show the users info.
?>
<div id="panel_14" class="kookiiz_panel" style="display:none">
    <div class="panel_handle text_color0 left">
        <div class="handle">
            <img class="icon20_white panel_14" src="<?php C::p('ICON_URL'); ?>" alt="<?php $Lang->p('PANELS_TITLES', 14); ?>" />
            <span class="bold"><?php $Lang->p('PANELS_TITLES', 14); ?></span>
        </div>
		<img class="panel_help icon15 click help" src="<?php C::p('ICON_URL'); ?>" title="<?php $Lang->p('PANELS_TEXT', 1); ?>" alt="<?php $Lang->p('PANELS_TEXT', 1); ?>" />
		<img class="panel_toggle icon15 click arrow_up" src="<?php C::p('ICON_URL'); ?>" title="<?php $Lang->p('PANELS_TEXT', 7); ?>" alt="<?php $Lang->p('PANELS_TEXT', 3); ?>" />
	</div>
    <div class="panel_box">
        <h6 class="panel_header bold center"><?php $Lang->p('PANELS_HEADERS', 14); ?></h6>
        <div class="panel_content">
            <div>
				<div id="user_info"></div>
            </div>
        </div>
    </div>
    <div class="panel_footer"></div>
</div>