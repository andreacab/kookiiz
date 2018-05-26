<?php
	/**********************************************************
    Title: Panels right
    Authors: Kookiiz Team
    Purpose: HTML code of the right-hand side panels
    ***********************************************************/

	//PANEL 3 : SHOPPING
?>
<div id="panel_3" class="kookiiz_panel" style="display:none">
	<div class="panel_handle text_color0 left">
        <div class="handle">
            <img class="icon20_white panel_3" src="<?php C::p('ICON_URL'); ?>" alt="<?php $Lang->p('PANELS_TITLES', 3); ?>" />
            <span class="bold"><?php $Lang->p('PANELS_TITLES', 3); ?></span>
        </div>
		<img class="panel_help icon15 click help" src="<?php C::p('ICON_URL'); ?>" title="<?php $Lang->p('PANELS_TEXT', 1); ?>" alt="<?php $Lang->p('PANELS_TEXT', 1); ?>" />
		<img class="panel_toggle icon15 click arrow_up" src="<?php C::p('ICON_URL'); ?>" title="<?php $Lang->p('PANELS_TEXT', 7); ?>" alt="<?php $Lang->p('PANELS_TEXT', 3); ?>" />
	</div>
    <div class="panel_box">
        <h6 class="panel_header bold center"><?php $Lang->p('PANELS_HEADERS', 3); ?></h6>
        <div class="panel_content">
            <div>
                <p class="center">
                    <select id="select_shopping_day" class="large">
						<!-- Default option ensures proper behavior during loading -->
						<optgroup label="menu" class="menu">
							<option value="0" selected="selected">empty</option>
						</optgroup>
					</select>
                </p>
				<p id="shopping_short_notice_old" class="center" style="display:none">
					<span class="error"><?php $Lang->p('SHOPPING_NOTICE', 0); ?></span>
				</p>
				<p id="shopping_short_notice_shared" class="center" style="display:none">
					<span><?php echo $Lang->get('SHOPPING_NOTICE', 1), ' '; ?></span>
					<span id="shopping_short_shared_names"></span>
				</p>
				<p id="shopping_short_notice_received" class="center" style="display:none">
					<span><?php echo $Lang->get('SHOPPING_NOTICE', 2), ' '; ?></span>
					<span id="shopping_short_received_name"></span>
				</p>
                <div id="shopping_short" class="center"></div>
                <div id="div_shopping_info" style="display:none">
                    <hr />
                    <p class="left">
                        <img class="icon15 price" src="<?php C::p('ICON_URL'); ?>" title="<?php $Lang->p('KEYWORDS', 7); ?>" alt="<?php $Lang->p('KEYWORDS', 7); ?>" />
                        <span class="bold"><?php $Lang->p('SHOPPING_TEXT', 27); ?>: </span>
                        <span id="span_shopping_price"></span>
                    </p>
					<p class="left" style="display:none">
                        <img class="icon15 weight" src="<?php C::p('ICON_URL'); ?>" title="<?php $Lang->p('KEYWORDS', 8); ?>" alt="<?php $Lang->p('KEYWORDS', 8); ?>" />
                        <span class="bold"><?php $Lang->p('SHOPPING_TEXT', 28); ?>: </span>
                        <span id="span_shopping_weight"></span>
                    </p>
                </div>
				<div class="center">
                    <button type="button" id="shopping_short_finalize" class="button_80" style="display:none"><?php $Lang->p('ACTIONS', 27); ?></button>
					<button type="button" id="shopping_short_print" class="button_80" style="display:none"><?php $Lang->p('ACTIONS', 1); ?></button>
					<button type="button" id="shopping_short_share" class="button_80" style="display:none"><?php $Lang->p('ACTIONS', 9); ?></button>
					<button type="button" id="shopping_short_cancel" class="button_80" style="display:none"><?php $Lang->p('ACTIONS', 5); ?></button>
                    <button type="button" id="shopping_short_transfer" class="button_80" style="display:none"><?php $Lang->p('ACTIONS', 10); ?></button>
                </div>
            </div>
        </div>
    </div>
    <div class="panel_footer"></div>
</div>
<?php
	//PANEL 2 : FRIDGE
	//This panel contains an input field to add ingredients from the database to user's virtual fridge.
?>
<div id="panel_2" class="kookiiz_panel<?php echo $User->isLogged() ? '' : ' disabled'; ?>" style="display:none">
    <div class="panel_handle text_color0 left">
        <div class="handle">
            <img class="icon20_white panel_2" src="<?php C::p('ICON_URL'); ?>" alt="<?php $Lang->p('PANELS_TITLES', 2); ?>" />
            <span class="bold"><?php $Lang->p('PANELS_TITLES', 2); ?></span>
        </div>
		<img class="panel_help icon15 click help" src="<?php C::p('ICON_URL'); ?>" title="<?php $Lang->p('PANELS_TEXT', 1); ?>" alt="<?php $Lang->p('PANELS_TEXT', 1); ?>" />
		<img class="panel_toggle icon15 click arrow_up" src="<?php C::p('ICON_URL'); ?>" title="<?php $Lang->p('PANELS_TEXT', 7); ?>" alt="<?php $Lang->p('PANELS_TEXT', 3); ?>" />
    </div>
    <div class="panel_box">
        <h6 class="panel_header bold center"><?php $Lang->p('PANELS_HEADERS', 2); ?></h6>
        <div class="panel_content">
            <div>
                <div id="fridge">
					<div class="header center">
						<span class="input_wrap size_160 icon">
							<input type="text" id="fridge_input" class="focus" maxlength="25" value="<?php $Lang->p('PANEL_FRIDGE_TEXT', 0); ?>" title="<?php $Lang->p('PANEL_FRIDGE_TEXT', 0); ?>" />
							<img id="fridge_ingredient_add" class="icon15_white click plus" src="<?php C::p('ICON_URL'); ?>" title="<?php $Lang->p('ACTIONS', 14); ?>" alt="<?php $Lang->p('ACTIONS', 14); ?>" />
						</span>
					</div>
					<div class="middle">
						<div id="fridge_content" class="center"></div>
					</div>
					<div class="footer"></div>
                </div>
				<div id="fridge_search" class="center">
					<span class="click"><?php $Lang->p('PANEL_FRIDGE_TEXT', 1); ?></span>
					<img class="icon15 click search" src="<?php C::p('ICON_URL'); ?>" alt="<?php $Lang->p('ACTIONS', 13); ?>" />
				</div>
            </div>
        </div>
    </div>
    <div class="panel_footer"></div>
</div>
<?php
	//PANEL 4 : NUTRITION
?>
<div id="panel_4" class="kookiiz_panel" style="display:none">
    <div class="panel_handle text_color0 left">
        <div class="handle">
            <img class="icon20_white panel_4" src="<?php C::p('ICON_URL'); ?>" alt="<?php $Lang->p('PANELS_TITLES', 4); ?>" />
            <span class="bold"><?php $Lang->p('PANELS_TITLES', 4); ?></span>
        </div>
		<img class="panel_help icon15 click help" src="<?php C::p('ICON_URL'); ?>" title="<?php $Lang->p('PANELS_TEXT', 1); ?>" alt="<?php $Lang->p('PANELS_TEXT', 1); ?>" />
		<img class="panel_toggle icon15 click arrow_up" src="<?php C::p('ICON_URL'); ?>" title="<?php $Lang->p('PANELS_TEXT', 7); ?>" alt="<?php $Lang->p('PANELS_TEXT', 3); ?>" />
    </div>
    <div class="panel_box">
        <h6 class="panel_header bold center"><?php $Lang->p('PANELS_HEADERS', 4); ?></h6>
        <div class="panel_content">
            <div>
				<div>
					<div id="nutrition_category_controls">
						<p class="center">
							<select id="select_nutrition_category">
							<?php
                                $categories = $Lang->get('NUTRITION_CATEGORIES');
								foreach($categories as $cat_id => $cat_name)
								{
									echo '<option value="', $cat_id, '">', $cat_name, '</option>';
								}
							?>
							</select>
						</p>
					</div>
					<p id="nutrition_display" class="center"></p>
				</div>
				<p class="tiny center"><?php $Lang->p('PANEL_NUTRITION_TEXT', 0); ?></p>
            </div>
        </div>
    </div>
    <div class="panel_footer"></div>
</div>
<?php
	//PANEL 5 : FRIENDS
	//Manage friends and share recipes with them
?>
<div id="panel_5" class="kookiiz_panel<?php echo $User->isLogged() ? '' : ' disabled'; ?>" style="display:none">
    <div class="panel_handle text_color0 left">
        <div class="handle">
            <img class="icon20_white panel_5" src="<?php C::p('ICON_URL'); ?>" alt="<?php $Lang->p('PANELS_TITLES', 5); ?>" />
            <span class="bold"><?php echo $Lang->p('PANELS_TITLES', 5); ?></span>
        </div>
		<img class="panel_help icon15 click help" src="<?php C::p('ICON_URL'); ?>" title="<?php $Lang->p('PANELS_TEXT', 1); ?>" alt="<?php $Lang->p('PANELS_TEXT', 1); ?>" />
		<img class="panel_toggle icon15 click arrow_up" src="<?php C::p('ICON_URL'); ?>" title="<?php $Lang->p('PANELS_TEXT', 7); ?>" alt="<?php $Lang->p('PANELS_TEXT', 3); ?>" />
    </div>
    <div class="panel_box">
        <h6 class="panel_header bold center"><?php $Lang->p('PANELS_HEADERS', 5); ?></h6>
        <div class="panel_content">
            <div>
				<div id="friends_list" class="center" style="display:none"></div>
                <div id="friends_loader" class="center"></div>
				<p class="center">
					<button type="button" class="button_80" id="friends_search_open"><?php $Lang->p('ACTIONS', 14); ?></button>
				</p>
            </div>
        </div>
    </div>
    <div class="panel_footer"></div>
</div>
<?php
	//PANEL 8 : COMMENTS
	//Add private notes and public comments to currently viewed content
    if(false):
?>
<div id="panel_8" class="kookiiz_panel<?php echo $User->isLogged() ? '' : ' disabled'; ?>" style="display:none">
    <div class="panel_handle text_color0 left">
        <div class="handle">
            <img class="icon20_white panel_8" src="<?php C::p('ICON_URL'); ?>" alt="<?php $Lang->p('PANELS_TITLES', 8); ?>" />
            <span class="bold"><?php $Lang->p('PANELS_TITLES', 8); ?></span>
        </div>
        <img class="panel_help icon15 click help" src="<?php C::p('ICON_URL'); ?>" title="<?php $Lang->p('PANELS_TEXT', 1); ?>" alt="<?php $Lang->p('PANELS_TEXT', 1); ?>" />
		<img class="panel_toggle icon15 click arrow_up" src="<?php C::p('ICON_URL'); ?>" title="<?php $Lang->p('PANELS_TEXT', 7); ?>" alt="<?php $Lang->p('PANELS_TEXT', 3); ?>" />
    </div>
    <div class="panel_box">
        <h6 class="panel_header bold center"><?php $Lang->p('PANELS_HEADERS', 8); ?></h6>
        <div class="panel_content">
            <div>
                <p class="center">
                    <textarea id="textarea_comment" cols="40" rows="5"></textarea>
                </p>
                <p class="bold center">
                    <span id="textarea_comment_chars"></span>
                </p>
                <p class="center">
                    <select id="select_comment_type">
						<option value="0"><?php $Lang->p('COMMENTS_TYPES', 0); ?></option>
						<option value="1"><?php $Lang->p('COMMENTS_TYPES', 1); ?></option>
                    </select>
                </p>
                <p class="center">
                    <button type="button" class="button_80" id="button_clear_comment"><?php $Lang->p('ACTIONS', 15); ?></button>
                    <button type="button" class="button_80" id="button_send_comment"><?php $Lang->p('ACTIONS', 6); ?></button>
                </p>
            </div>
        </div>
    </div>
    <div class="panel_footer"></div>
</div>
<?php
    endif;
	//PANEL 12 : ARTICLES
	//Search for articles and display clickable results
    if(false):
?>
<div id="panel_12" class="kookiiz_panel" style="display:none">
    <div class="panel_handle text_color0 left">
        <div class="handle">
            <img class="icon20_white panel_12" src="<?php C::p('ICON_URL'); ?>" alt="<?php $Lang->p('PANELS_TITLES', 12); ?>" />
            <span class="bold"><?php $Lang->p('PANELS_TITLES', 12); ?></span>
        </div>
		<img class="panel_help icon15 click help" src="<?php C::p('ICON_URL'); ?>" title="<?php $Lang->p('PANELS_TEXT', 1); ?>" alt="<?php $Lang->p('PANELS_TEXT', 1); ?>" />
		<img class="panel_toggle icon15 click arrow_up" src="<?php C::p('ICON_URL'); ?>" title="<?php $Lang->p('PANELS_TEXT', 7); ?>" alt="<?php $Lang->p('PANELS_TEXT', 3); ?>" />
    </div>
    <div class="panel_box">
        <h6 class="panel_header bold center"><?php $Lang->p('PANELS_HEADERS', 12); ?></h6>
        <div class="panel_content">
            <div class="center">
				<span class="input_wrap size_180 icon">
					<input type="text" id="input_articles_search" class="focus enter search" maxlength="30" value="<?php $Lang->p('ACTIONS', 13); ?>" title="<?php $Lang->p('ACTIONS', 13); ?>" />
					<img id="icon_articles_search" class="icon15_white click search" src="<?php C::p('ICON_URL'); ?>" title="<?php $Lang->p('ACTIONS', 13); ?>" alt="<?php $Lang->p('ACTIONS', 13); ?>" />
				</span>
				<div id="articles_search_results"></div>
            </div>
        </div>
    </div>
    <div class="panel_footer"></div>
</div>
<?php
    endif;
	//PANEL 13 : INVITATIONS
	//Get invited and/or make invitations to share meals with friends
?>
<div id="panel_13" class="kookiiz_panel<?php echo $User->isLogged() ? '' : ' disabled'; ?>" style="display:none">
    <div class="panel_handle text_color0 left">
        <div class="handle">
            <img class="icon20_white panel_13" src="<?php C::p('ICON_URL'); ?>" alt="<?php $Lang->p('PANELS_TITLES', 13); ?>" />
            <span class="bold"><?php $Lang->p('PANELS_TITLES', 13); ?></span>
        </div>
		<img class="panel_help icon15 click help" src="<?php C::p('ICON_URL'); ?>" title="<?php $Lang->p('PANELS_TEXT', 1); ?>" alt="<?php $Lang->p('PANELS_TEXT', 1); ?>" />
		<img class="panel_toggle icon15 click arrow_up" src="<?php C::p('ICON_URL'); ?>" title="<?php $Lang->p('PANELS_TEXT', 7); ?>" alt="<?php $Lang->p('PANELS_TEXT', 3); ?>" />
    </div>
    <div class="panel_box">
        <h6 class="panel_header bold center"><?php $Lang->p('PANELS_HEADERS', 13); ?></h6>
        <div class="panel_content">
            <div>
                <div id="invitation_header">
					<p class="center">
						<select id="select_invitation" class="large">
                            <option></option>
                        </select>
					</p>
					<p class="center">
						<button type="button" id="invitation_new" class="button_80"><?php $Lang->p('ACTIONS', 7); ?></button>
						<button type="button" id="invitation_cancel" class="button_80"><?php $Lang->p('ACTIONS', 5); ?></button>
					</p>
				</div>
				<div id="invitation_content" style="display:none">
                    <div class="center">
                        <span id="invitation_title" class="bold"></span>
                        <input type="text" id="invitation_title_input" title="<?php $Lang->p('INVITATIONS_TEXT', 6); ?>" style="display:none" />
                    </div>
                    <p id="invitation_datetime" class="center">
                        <img src="<?php C::p('ICON_URL'); ?>" class="icon15 calendar" alt="<?php $Lang->p('PANEL_INVITATIONS_TEXT', 4); ?>" title="<?php $Lang->p('PANEL_INVITATIONS_TEXT', 4); ?>" />
                        <span id="invitation_date"></span>
                        <img src="<?php C::p('ICON_URL'); ?>" class="icon15 clock" alt="<?php $Lang->p('PANEL_INVITATIONS_TEXT', 5); ?>" title="<?php $Lang->p('PANEL_INVITATIONS_TEXT', 5); ?>" />
                        <span id="invitation_time"></span>
                    </p>
                    <div class="center">
                        <p id="invitation_text"></p>
                        <textarea id="invitation_text_input" cols="20" rows="10" style="display:none" title="<?php $Lang->p('PANEL_INVITATIONS_TEXT', 7); ?>"></textarea>
                    </div>
					<div id="invitation_table">
						<div id="invitation_table_top"></div>
						<div id="invitation_table_middle" style="height:55px"></div>
						<div id="invitation_table_bottom"></div>
						<div id="invitation_table_droppable"></div>
					</div>
					<div id="invitation_menu" class="center" style="display:none">
						<span class="bold"><?php $Lang->p('PANEL_INVITATIONS_TEXT', 8); ?></span>
						<div id="invitation_menu_content"></div>
					</div>
				</div>
                <div id="invitation_loader" class="center" style="display:none"></div>
				<p class="center">
					<button type="button" id="invitation_save" class="button_80"><?php $Lang->p('ACTIONS', 0); ?></button>
					<button type="button" id="invitation_send" class="button_80"><?php $Lang->p('ACTIONS', 6); ?></button>
					<button type="button" id="invitation_giveup" class="button_80" style="display:none"><?php $Lang->p('ACTIONS', 34); ?></button>
				</p>
				<div id="invitation_footer" style="display:none">
					<p class="bold center"><?php $Lang->p('INVITATIONS_TEXT', 7); ?></p>
					<p id="invitation_alerts" class="center"></p>
				</div>
			</div>
        </div>
    </div>
    <div class="panel_footer"></div>
</div>
<?php
	//PANEL 15 : Navigation
	//Links to navigate on some tabs
?>
<div id="panel_15" class="kookiiz_panel" style="display:none">
    <div class="panel_handle text_color0 left">
        <div class="handle">
            <img class="icon20_white panel_15" src="<?php C::p('ICON_URL'); ?>" alt="<?php $Lang->p('PANELS_TITLES', 15); ?>" />
            <span class="bold"><?php $Lang->p('PANELS_TITLES', 15); ?></span>
        </div>
		<img class="panel_help icon15 click help" src="<?php C::p('ICON_URL'); ?>" title="<?php $Lang->p('PANELS_TEXT', 1); ?>" alt="<?php $Lang->p('PANELS_TEXT', 1); ?>" />
		<img class="panel_toggle icon15 click arrow_up" src="<?php C::p('ICON_URL'); ?>" title="<?php $Lang->p('PANELS_TEXT', 7); ?>" alt="<?php $Lang->p('PANELS_TEXT', 3); ?>" />
    </div>
    <div class="panel_box">
        <h6 class="panel_header bold center"><?php $Lang->p('PANELS_HEADERS', 15); ?></h6>
        <div class="panel_content">
            <div>
				<div id="navigation_container"></div>
            </div>
        </div>
    </div>
    <div class="panel_footer"></div>
</div>
<?php
	//PANEL 16 : Feedback
	//Random feedback question
?>
<div id="panel_16" class="kookiiz_panel<?php echo $User->isAdmin() ? ' disabled' : ''; ?>" style="display:none">
    <div class="panel_handle text_color0 left">
        <div class="handle">
            <img class="icon20_white panel_16" src="<?php C::p('ICON_URL'); ?>" alt="<?php $Lang->p('PANELS_TITLES', 16); ?>" />
            <span class="bold"><?php $Lang->p('PANELS_TITLES', 16); ?></span>
        </div>
		<img class="panel_help icon15 click help" src="<?php C::p('ICON_URL'); ?>" title="<?php $Lang->p('PANELS_TEXT', 1); ?>" alt="<?php $Lang->p('PANELS_TEXT', 1); ?>" />
		<img class="panel_toggle icon15 click arrow_up" src="<?php C::p('ICON_URL'); ?>" title="<?php $Lang->p('PANELS_TEXT', 7); ?>" alt="<?php $Lang->p('PANELS_TEXT', 3); ?>" />
    </div>
    <div class="panel_box">
        <h6 class="panel_header bold center"><?php $Lang->p('PANELS_HEADERS', 16); ?></h6>
        <div class="panel_content">
            <div>
				<div id="feedback_question">
					<p class="question"></p>
					<p class="controls center" style="display:none">	
						<span class="answer yes click text_color1 bold"><?php $Lang->p('VARIOUS', 13); ?></span>
						<span class="answer no click text_color2 bold"><?php $Lang->p('VARIOUS', 14); ?></span>
                        <span class="skip click"><?php $Lang->p('VARIOUS', 19); ?></span>
					</p>
				</div>
				<div id="feedback_thanks" style="display:none">
					<p class="message center"><?php echo $Lang->get('VARIOUS', 17), ' !'; ?></p>
				</div>
            </div>
        </div>
    </div>
    <div class="panel_footer"></div>
</div>
<?php
    //PANEL 17 : OFFERS
    //Advertisement
?>
<div id="panel_17" class="kookiiz_panel frozen<?php echo $User->isAdmin() ? ' disabled' : ''; ?>" style="display:none">
    <div class="panel_handle text_color0 left">
        <div class="handle">
            <img class="icon20_white panel_17" src="<?php C::p('ICON_URL'); ?>" alt="<?php $Lang->p('PANELS_TITLES', 17); ?>" />
            <span class="bold"><?php $Lang->p('PANELS_TITLES', 17); ?></span>
        </div>
        <img class="panel_help icon15 click help" src="<?php C::p('ICON_URL'); ?>" title="<?php $Lang->p('PANELS_TEXT', 1); ?>" alt="<?php $Lang->p('PANELS_TEXT', 1); ?>" style="display:none" />
		<img class="panel_toggle icon15 click arrow_up" src="<?php C::p('ICON_URL'); ?>" title="<?php $Lang->p('PANELS_TEXT', 7); ?>" alt="<?php $Lang->p('PANELS_TEXT', 3); ?>" />
	</div>
    <div class="panel_box">
        <h6 class="panel_header bold center"><?php $Lang->p('PANELS_HEADERS', 17); ?></h6>
        <div class="panel_content">
            <div>
                <script type="text/javascript">
                <!--
                    google_ad_client    = "ca-pub-3234212648582586";
                    /* Panel Offers */
                    google_ad_slot      = "2937328370";
                    google_ad_width     = 200;
                    google_ad_height    = 200;
                //-->
                </script>
                <script type="text/javascript" src="http://pagead2.googlesyndication.com/pagead/show_ads.js"></script>
            </div>
        </div>
    </div>
    <div class="panel_footer"></div>
</div>
<?php
    //PANEL 18 : FACEBOOK
    //Social plugin
?>
<div id="panel_18" class="kookiiz_panel" style="display:none">
    <div class="panel_handle text_color0 left">
        <div class="handle">
            <img class="icon20_white panel_18" src="<?php C::p('ICON_URL'); ?>" alt="<?php $Lang->p('PANELS_TITLES', 18); ?>" />
            <span class="bold"><?php $Lang->p('PANELS_TITLES', 18); ?></span>
        </div>
        <img class="panel_help icon15 click help" src="<?php C::p('ICON_URL'); ?>" title="<?php $Lang->p('PANELS_TEXT', 1); ?>" alt="<?php $Lang->p('PANELS_TEXT', 1); ?>" style="display:none" />
		<img class="panel_toggle icon15 click arrow_up" src="<?php C::p('ICON_URL'); ?>" title="<?php $Lang->p('PANELS_TEXT', 7); ?>" alt="<?php $Lang->p('PANELS_TEXT', 3); ?>" />
	</div>
    <div class="panel_box">
        <div class="panel_content">
            <div id="kookiiz_facebook">
                <fb:like-box href="http://www.facebook.com/kookiizapp" width="200" height="185" show_faces="true" stream="false" header="false"></fb:like-box>
            </div>
        </div>
    </div>
    <div class="panel_footer"></div>
</div>