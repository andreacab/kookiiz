<?php
	/**********************************************************
    Title: Profile
    Authors: Kookiiz Team
    Purpose: HTML content of the profile tab
	***********************************************************/
?>
<!-- Personal information -->
<div class="nav_section column single">
	<h5>
		<span class="nav_title"><?php $Lang->p('OPTIONS_TITLES', 0); ?></span>
		<button type="button" class="button_80" id="options_profile_delete"><?php $Lang->p('ACTIONS', 23); ?></button>
	</h5>
	<table id="profile_fields_table">
	<tbody>
		<tr>
			<td rowspan="3" class="center">
				<span class="bold"><?php $Lang->p('USER_PROPERTIES', 8); ?><br/></span>
				<span class="tiny">(<?php $Lang->p('OPTIONS_TEXT', 1); ?>)<br/></span>
				<div id="options_avatar_area"></div>
				<button type="button" class="button_80" id="options_avatar_delete" style="display:none"><?php $Lang->p('ACTIONS', 15); ?></button>
			</td>
			<td>
				<p class="left">
					<span class="bold"><?php $Lang->p('USER_PROPERTIES', 1); ?></span>
					<span class="tiny"> (<?php $Lang->p('OPTIONS_TEXT', 0); ?>)</span>
				</p>
				<p class="left">
					<span id="span_profile_name"></span>
				</p>
			</td>
		</tr>
		<tr>
			<td>
				<p class="left">
					<span class="bold"><?php $Lang->p('USER_PROPERTIES', 2); ?></span>
					<span class="tiny"> (<?php $Lang->p('OPTIONS_TEXT', 2); ?>)</span>
				</p>
				<p class="left">
					<span id="span_profile_email"></span>
					<img id="profile_email_edit" class="icon15 click edit" src="<?php C::p('ICON_URL'); ?>" title="<?php $Lang->p('ACTIONS', 25); ?>" alt="<?php $Lang->p('ACTIONS', 25); ?>" />
				</p>
			</td>
		</tr>
		<tr>
			<td>
				<p class="left">
					<span class="bold"><?php $Lang->p('USER_PROPERTIES', 3); ?></span>
					<span class="tiny"> (<?php $Lang->p('OPTIONS_TEXT', 2); ?>)</span>
				</p>
				<p class="left">
					<span>**********</span>
					<img id="profile_password_edit" class="icon15 click edit" src="<?php C::p('ICON_URL'); ?>" title="<?php $Lang->p('ACTIONS', 25); ?>" alt="<?php $Lang->p('ACTIONS', 25); ?>" />
				</p>
			</td>
		</tr>
	</tbody>
	</table>
</div>

<!-- Interface -->
<div class="nav_section column single">
	<h5>
		<span class="nav_title"><?php $Lang->p('OPTIONS_TITLES', 1); ?></span>
		<button type="button" class="options_save button_80"><?php $Lang->p('ACTIONS', 0); ?></button>
	</h5>
	<p><?php $Lang->p('OPTIONS_TEXT', 4); ?></p>
	<table>
	<tbody>
		<tr>
			<td class="left">
				<span class="bold"><?php $Lang->p('USER_PROPERTIES', 5); ?></span>
			</td>
			<td class="left">
				<select id="select_profile_lang" class="medium">
				<?php
                    $langs = C::get('LANGUAGES');
                    $langsNames = C::get('LANGUAGES_NAMES');
					foreach($langs as $lang_id => $lang_key)
					{
						echo '<option value="', $lang_key, '">', $langsNames[$lang_id], '</option>';
					}
				?>
				</select>
			</td>
		</tr>
		<tr>
			<td class="left">
				<span class="bold"><?php $Lang->p('KEYWORDS', 9); ?></span>
			</td>
			<td class="left">
				<select class="user_option medium" name="currency" id="select_currency">
				<?php
                    $currencies = C::get('CURRENCIES');
                    $currenciesVals = C::get('CURRENCIES_VALUES');
					foreach($currencies as $id => $name)
					{
						echo '<option value="', $currenciesVals[$id], '">', $name, '</option>';
					}
				?>
				</select>
			</td>
		</tr>
        <tr>
            <td class="left">
                <span class="bold"><?php $Lang->p('OPTIONS_TEXT', 19); ?></span>
            </td>
            <td class="left">
                <select class="user_option medium" name="units" id="select_units">
                <?php
                    $unitsSys = $Lang->get('UNITS_SYS_NAMES');
                    foreach($unitsSys as $id => $name)
                    {
                        echo '<option value="', $id, '">', $name, '</option>';
                    }
                ?>
                </select>
            </td>
        </tr>
	</tbody>
	</table>
	<p><?php $Lang->p('OPTIONS_TEXT', 5); ?></p>
	<ul>
		<li>
			<label>
                <input type="checkbox" class="user_option" name="fast_mode" id="check_fast_mode" />
                <span class="click"><?php $Lang->p('OPTIONS_TEXT', 6); ?></span>
            </label>
		</li>
		<li class="option_panel">
			<label>
                <input type="checkbox" class="user_option" name="panel_fridge" id="check_panel_fridge" checked="checked" />
                <span class="click"><?php echo $Lang->get('OPTIONS_TEXT', 7), ' "', $Lang->get('PANELS_TITLES', 2), '"'; ?></span>
            </label>
		</li>
		<li class="option_panel">
			<label>
                <input type="checkbox" class="user_option" name="panel_invitations" id="check_panel_invitations" checked="checked" />
                <span class="click"><?php echo $Lang->get('OPTIONS_TEXT', 7), ' "', $Lang->get('PANELS_TITLES', 13), '"'; ?></span>
            </label>
		</li>
		<li class="option_panel">
			<label>
                <input type="checkbox" class="user_option" name="panel_nutrition" id="check_panel_nutrition" checked="checked" />
                <span class="click"><?php echo $Lang->get('OPTIONS_TEXT', 7), ' "', $Lang->get('PANELS_TITLES', 4), '"'; ?></span>
            </label>
		</li>
		<li class="option_panel">
			<label>
                <input type="checkbox" class="user_option" name="panel_recipes" id="check_panel_recipes" checked="checked" />
                <span class="click"><?php echo $Lang->get('OPTIONS_TEXT', 7), ' "', $Lang->get('PANELS_TITLES', 1), '"'; ?></span>
            </label>
		</li>
	</ul>
	<p><?php $Lang->p('OPTIONS_TEXT', 8); ?></p>
	<ul>
		<li>
			<label>
                <input type="checkbox" class="user_option" name="email_friendship" id="check_email_friendship" checked="checked" />
                <span class="click"><?php $Lang->p('OPTIONS_TEXT', 9); ?></span>
            </label>
		</li>
		<li>
			<label>
                <input type="checkbox" class="user_option" name="email_invitation" id="check_email_invitation" checked="checked" />
                <span class="click"><?php $Lang->p('OPTIONS_TEXT', 10); ?></span>
            </label>
		</li>
		<li>
			<label>
                <input type="checkbox" class="user_option" name="email_recipe" id="check_email_recipe" checked="checked" />
                <span class="click"><?php $Lang->p('OPTIONS_TEXT', 11); ?></span>
            </label>
		</li>
	</ul>
</div>

<!-- Food preferences -->
<div class="nav_section column single">
	<h5>
		<span class="nav_title"><?php $Lang->p('OPTIONS_TITLES', 2); ?></span>
		<button type="button" class="options_save button_80"><?php $Lang->p('ACTIONS', 0); ?></button>
	</h5>
	<div>
		<p class="bold"><?php $Lang->p('OPTIONS_TEXT', 12); ?></p>
		<p><?php $Lang->p('OPTIONS_TEXT', 13); ?></p>
		<p class="center">
			<span class="input_wrap size_180 icon">
				<input type="text" id="input_taste_ingredient" class="focus enter add" maxlength="25" value="<?php $Lang->p('INGREDIENTS_TEXT', 3); ?>" title="<?php $Lang->p('INGREDIENTS_TEXT', 3); ?>" />
				<img id="taste_ingredient_add" class="icon15_white click plus" src="<?php C::p('ICON_URL'); ?>" title="<?php $Lang->p('ACTIONS', 14); ?>" alt="<?php $Lang->p('ACTIONS', 14); ?>" />
			</span>
		</p>
		<div class="center">
			<span><?php $Lang->p('OPTIONS_TEXT', 14); ?></span>
			<select id="select_taste_type">
				<option value="<?php C::p('TASTE_DISLIKE'); ?>"><?php $Lang->p('OPTIONS_TEXT', 15); ?></option>
				<option value="<?php C::p('TASTE_LIKE'); ?>"><?php $Lang->p('OPTIONS_TEXT', 16); ?></option>
			</select>
		</div>
		<div id="tastes_display">
			<div class="like">
				<div class="header"></div>
				<div class="middle">
					<p class="title center">
						<img class="icon20 like" src="<?php C::p('ICON_URL'); ?>" alt="<?php $Lang->p('OPTIONS_TEXT', 16); ?>" />
						<span class="bold"><?php $Lang->p('OPTIONS_TEXT', 16); ?></span>
					</p>
					<div class="list center"></div>
				</div>
				<div class="footer"></div>
			</div>
			<div class="dislike">
				<div class="header"></div>
				<div class="middle">
					<p class="title center">
						<img class="icon20 dislike" src="<?php C::p('ICON_URL'); ?>" alt="<?php $Lang->p('OPTIONS_TEXT', 15); ?>" />
						<span class="bold"><?php $Lang->p('OPTIONS_TEXT', 15); ?></span>
					</p>
					<div class="list center"></div>
				</div>
				<div class="footer"></div>
			</div>
		</div>
	</div>
    <?php if(false): ?>
	<hr/>
	<div>
		<p class="bold"><?php $Lang->p('OPTIONS_TEXT', 17); ?></p>
		<p><?php $Lang->p('OPTIONS_TEXT', 18); ?></p>
		<div>
			<ul id="options_allergies_list">
			<?php
                $allergies = C::get('ALLERGIES');
                $allergiesNames = $Lang->get('ALLERGIES_NAMES');
				foreach($allergies as $id => $allergy)
				{
					echo '<li class="option_allergy">',
							'<label>', 
                                '<input type="checkbox" id="check_allergy_', $allergy, '"/>',
                                '<span class="click">', $allergiesNames[$id], '</span>',
                            '</label>',
						'</li>';
				}
			?>
			</ul>
		</div>
	</div>
    <?php endif; ?>
</div>