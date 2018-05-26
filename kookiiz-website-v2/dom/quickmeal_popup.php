<?php
	/**********************************************************
    Title: Quick meals popup
    Authors: Kookiiz Team
    Purpose: HTML content of the quick meals popup
    ***********************************************************/

    /**********************************************************
    SET-UP
    ***********************************************************/
	
	//Dependencies
	require_once '../class/dblink.php';
	require_once '../class/globals.php';
	require_once '../class/lang_db.php';
	require_once '../class/session.php';
	require_once '../class/units_lib.php';
	require_once '../class/user.php';

    //Start session
    Session::start();

    //Init handlers
    $DB   = new DBLink('kookiiz');
    $Lang = LangDB::getHandler(Session::getLang());
    $User = new User($DB);
    
    /**********************************************************
    VIEW
    ***********************************************************/
?>
<p id="quickmeal_error" class="error center" style="display:none"></p>
<p class="center">
	<span class="input_wrap size_220">
		<input type="text" id="input_quickmeal_name" class="focus" value="<?php $Lang->p('QUICKMEALS_TEXT', 2); ?>" title="<?php $Lang->p('QUICKMEALS_TEXT', 2); ?>" maxlength="<?php C::p('QM_NAME_MAX'); ?>" />
	</span>
</p>
<p class="center">
	<span class="bold"><?php $Lang->p('ACTIONS', 18); ?></span>
	<select id="select_quickmeal_mode">
	<?php
		echo '<option value="ingredients">', $Lang->get('QUICKMEALS_TEXT', 3), '</option>';
		echo '<option value="nutrition">', $Lang->get('QUICKMEALS_TEXT', 4), '</option>';
	?>
	</select>
</p>
<hr/>
<p id="quickmeal_mode_description"><?php $Lang->p('QUICKMEALS_TEXT', 5); ?></p>
<div id="quickmeal_display">
	<div id="quickmeal_controls">
		<div id="quickmeal_mode_ingredients">
			<p class="center">
				<span class="input_wrap size_180 centered">
					<input type="text" class="focus" id="input_quickmeal_ingredient" title="<?php $Lang->p('INGREDIENTS_TEXT', 3); ?>" value="<?php $Lang->p('INGREDIENTS_TEXT', 3); ?>" />
				</span>
			</p>
			<p class="center">
				<span class="bold">
                    <?php $Lang->p('INGREDIENTS_TEXT', 1); ?>
                </span>
				<span class="input_wrap size_60 centered">
					<input type="text" id="input_quickmeal_quantity" class="focus enter add" maxlength="6" />
				</span>
				<select id="select_quickmeal_unit">
				<?php
					//Create an option for each ingredient unit
                    $system = C::get('UNITS_SYSTEMS', $User->options_get('units'));
                    $units  = UnitsLib::getAll($system);
                    foreach($units as $Unit)
                    {
                        $id = $Unit->getID();
                        echo '<option value="', $id, '"', $id == C::ING_UNIT_DEFAULT ? 'selected="selected">' : '>', $Lang->get('UNITS_NAMES', $id), '</option>';
                    }
				?>
				</select>
				<img id="quickmeal_ingredient_add" class="button15 accept" src="<?php C::p('ICON_URL'); ?>" alt="<?php $Lang->p('ACTIONS', 22); ?>" />
			</p>
			<p id="quickmeal_ingredients_display" class="center"></p>
		</div>
		<div id="quickmeal_mode_nutrition" style="display:none">
			<table>
			<tbody>
			<?php
                $nutValues = C::get('MENU_NUTRITION_VALUES');
                $nutUnits  = C::get('NUT_UNITS');
                $valNames  = $Lang->get('NUTRITION_VALUES_NAMES');
				foreach($nutValues as $value)
				{
					echo '<tr id="quickmeal_nutrition_', $value, '">',
							'<td class="value_name bold">', $valNames[$value], '</td>',
							'<td class="Value_input">',
								'<span class="input_wrap size_60">',
									'<input type="text" class="focus" value="0" title="0" />',
								'</span>',
								'<span>', $nutUnits[$value], '</span>',
							'</td>',
						'</tr>';
				}
			?>
			</tbody>
			</table>
		</div>
	</div>
	<div id="quickmeal_nutrition"></div>
</div>
<p class="center">
	<button type="button" class="button_80" id="button_quickmeal_confirm">
        <?php $Lang->p('ACTIONS', 18); ?>
    </button>
	<button type="button" class="button_80" id="button_quickmeal_cancel">
        <?php $Lang->p('ACTIONS', 5); ?>
    </button>
</p>