<?php
	/**********************************************************
    Title: Shopping finish
    Authors: Kookiiz Team
    Purpose: HTML content of the shopping finalization tab
	***********************************************************/
?>
<div class="column medium left_side">
    <div id="shopping_ad" class="center">
        <script type="text/javascript">
        var uri = 'http://impch.tradedoubler.com/imp?type(img)g(16138344)a(1973404)' + new String (Math.random()).substring (2, 11);
        document.write('<a href="http://clk.tradedoubler.com/click?p=31182&a=1973404&g=16138344" target="_BLANK"><img src="'+uri+'" border=0></a>');
        </script>
    </div>
	<div id="shopping_full">
		<ul id="shopping_full_list" class="shopping_list">
		<?php
            $groups      = C::get('ING_GROUPS');
            $groupsNames = $Lang->get('INGREDIENTS_GROUPS_NAMES');
			foreach($groups as $id => $keyword)
			{
				$name = $groupsNames[$id];
				
				echo '<li style="display:none" class="shopping_group" id="shopfullgroup_', $id, '">',
						'<div class="top"></div>',
						'<div class="middle">',
							'<div class="title">',
								'<img src="', C::ICON_URL, '" class="category_icon ', ($keyword ? $keyword : 'none'), '" alt="" />',
								"<p>$name</p>",
							'</div>',
							'<div class="list"></div>',
						'</div>',
						'<div class="bottom"></div>',
					'</li>';
			}
		?>
		</ul>
		<p class="shopping_empty" style="display:none"></p>
	</div>
</div>
<div class="column medium">
	<div>
		<h5>
			<img class="icon25 list_plus" src="<?php C::p('ICON_URL'); ?>" alt="<?php $Lang->p('KEYWORDS', 3); ?>" />
			<span><?php $Lang->p('SHOPPING_TITLES', 0); ?></span>
		</h5>
		<p class="justify"><?php $Lang->p('SHOPPING_TEXT', 6); ?></p>
		<table>
		<tbody>
			<tr>
				<td>
					<span class="bold"><?php $Lang->p('INGREDIENTS_TEXT', 0); ?></span>
				</td>
				<td>
					<span class="input_wrap size_180">
						<input type="text" id="input_shopping_add" class="focus" value="<?php $Lang->p('INGREDIENTS_TEXT', 4); ?>" title="<?php $Lang->p('INGREDIENTS_TEXT', 4); ?>" maxlength="25" />
					</span>
				</td>
			</tr>
			<tr>
				<td>
					<span class="bold"><?php $Lang->p('INGREDIENTS_TEXT', 1); ?></span>
				</td>
				<td>
					<span class="input_wrap size_60 centered">
						<input type="text" id="input_shopping_quantity" class="focus enter add" maxlength="6" />
					</span>
					<select id="select_shopping_unit">
					<?php
                        //Create an option for each ingredient unit
                        $system = C::get('UNITS_SYSTEMS', $User->options_get('units'));
                        $units  = UnitsLib::getAll($system);
                        foreach($units as $Unit)
                        {
                            $id = $Unit->getID();
                            echo '<option value="', $id, '"', $id == C::ING_UNIT_DEFAULT ? ' selected="selected">' : '>', $Lang->get('UNITS_NAMES', $id), '</option>';
                        }
					?>
					</select>
					<img id="button_shopping_clear" class="button15 cancel" src="<?php C::p('ICON_URL'); ?>" alt="X" />
					<img id="button_shopping_add" class="button15 accept" src="<?php C::p('ICON_URL'); ?>" alt="<?php $Lang->p('ACTIONS', 22); ?>" />
				</td>
			</tr>
			<tr>
				<td>
					<span class="bold"><?php $Lang->p('INGREDIENTS_TEXT', 2); ?></span>
				</td>
				<td>
					<select id="select_shopping_group">
					<?php
						//Create an option for each ingredient group
						//Groups are sorted by alphabetical order
                        $groupsNames = $Lang->get('INGREDIENTS_GROUPS_NAMES');
						asort($groupsNames);
						foreach($groupsNames as $id => $name)
						{
							echo '<option value="', $id,'"', $id == C::ING_GROUP_DEFAULT ? 'selected="selected">' : '>', $name, '</option>';
						}
					?>
					</select>
				</td>
			</tr>
		</tbody>
		</table>
	<hr/>
	</div>
	<!-- Markets -->
	<div <?php if(!$User->isLogged()){echo 'style="display:none"';} ?>>
		<h5>
			<img class="icon25 market" src="<?php C::p('ICON_URL'); ?>" alt="<?php $Lang->p('KEYWORDS', 13); ?>" />
			<span><?php $Lang->p('SHOPPING_TITLES', 1); ?></span>
		</h5>
		<p class="justify"><?php $Lang->p('SHOPPING_TEXT', 7); ?></p>
		<p>
			<select id="select_shopping_market" class="large">
				<option value="-1"><?php $Lang->p('VARIOUS', 1); ?></option>
			</select>
			<img id="shopping_market_delete" class="button15 cancel" src="<?php C::p('ICON_URL'); ?>" title="<?php $Lang->p('SHOPPING_TEXT', 8); ?>" alt="<?php $Lang->p('ACTIONS', 23); ?>" style="display:none" />
			<img id="shopping_market_save" class="icon15 click save" src="<?php C::p('ICON_URL'); ?>" title="<?php $Lang->p('ACTIONS', 0); ?>" alt="<?php $Lang->p('ACTIONS', 0); ?>" style="display:none" />
		</p>
		<p class="center">
			<button type="button" class="button_80" id="shopping_market_new"><?php $Lang->p('ACTIONS', 7); ?></button>
		</p>
	<hr/>
	</div>
	<!-- Actions -->
	<div>
		<h5>
			<img class="icon25 actions" src="<?php C::p('ICON_URL'); ?>" alt="<?php $Lang->p('SHOPPING_TITLES', 2); ?>" />
			<span><?php $Lang->p('SHOPPING_TITLES', 2); ?></span>
		</h5>
		<p class="justify"><?php $Lang->p('SHOPPING_TEXT', 20); ?></p>
		<div id="shopping_full_actions" class="center">
			<button type="button" class="button_80 disabled" id="shopping_share" disabled="disabled"><?php $Lang->p('ACTIONS', 9); ?></button>
			<button type="button" class="button_80" id="shopping_email"><?php $Lang->p('ACTIONS', 6); ?></button>
		</div>
		<div class="center">
			<button type="button" class="button_80" id="shopping_print"><?php $Lang->p('ACTIONS', 1); ?></button>
		</div>
	<hr/>
	</div>
	<!-- Remarks -->
	<div id="shopping_full_notices">
		<h5>
			<img class="icon25 remark" src="<?php C::p('ICON_URL'); ?>" alt="" />
			<span><?php $Lang->p('SHOPPING_TITLES', 3); ?></span>
		</h5>
		<p id="shopping_notice_expired" class="expired" style="display:none">
			<span><?php $Lang->p('SHOPPING_NOTICE', 4); ?></span>
		</p>
		<p id="shopping_notice_modified" style="display:none">
			<span><?php $Lang->p('SHOPPING_NOTICE', 5); ?></span>
		</p>
		<p id="shopping_notice_received" style="display:none">
			<span><?php $Lang->p('SHOPPING_NOTICE', 2); ?> </span>
			<span id="shopping_received_name"></span>
		</p>
		<p id="shopping_notice_shared" style="display:none">
			<span><?php $Lang->p('SHOPPING_NOTICE', 1); ?> </span>
			<span id="shopping_shared_names"></span>
		</p>
		<p id="shopping_notice_stocked" class="stocked" style="display:none">
			<span><?php $Lang->p('SHOPPING_NOTICE', 6); ?></span>
		</p>
	</div>
</div>