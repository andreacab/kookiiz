<?php
    /*******************************************************
    Title: Health
    Authors: Kookiiz Team
    Purpose: HTML content of the health tab
    ********************************************************/
?>
<div>
	<div class="health_header">
		<div class="header_left">
			<h5>
				<span><?php $Lang->p('HEALTH_TITLES', 0); ?></span>
			</h5>
			<p class="left">
				<span><?php $Lang->p('HEALTH_TEXT', 0); ?></span>
				<select id="nutrition_history_start">
				<?php
					$now = time();
					for($i = -C::MENU_DAYS_PAST, $imax = C::MENU_DAYS_FUTURE; $i < $imax; $i++)
					{
						$current_day    = date('d', time() + $i * 60 * 60 * 24);
						$current_month  = date('m', time() + $i * 60 * 60 * 24);
						
						echo '<option value="', $i, '">', $current_day, '.', $current_month, '</option>';
					}
				?>
				</select>
				<span><?php $Lang->p('HEALTH_TEXT', 1); ?></span>
				<select id="nutrition_history_stop">
                    <option value="<?php echo C::MENU_DAYS_FUTURE - 1; ?>"></option>
                </select>
			</p>
			<p class="left">
				<span><?php $Lang->p('HEALTH_TEXT', 2); ?></span>
				<select id="nutrition_history_value">
				<?php
                    $nutrition_values = $Lang->get('NUTRITION_VALUES_NAMES');
					foreach($nutrition_values as $id => $name)
					{
						echo '<option value="', $id, '">', $name, '</option>';
					}
				?>
				</select>
			</p>
		</div>
		<div class="header_right">
			<ul class="caption">
				<li class="bold"><?php $Lang->p('HEALTH_TEXT', 8); ?></li>
				<li>
					<img class="caption_icon caption_0" src="<?php C::p('ICON_URL'); ?>" alt="<?php $Lang->p('HEALTH_TEXT', 5); ?>" />
					<span><?php echo $Lang->get('HEALTH_TEXT', 5), ' '; ?>(<span id="health_graph_needs"></span>)</span>
				</li>
				<li>
					<img class="caption_icon caption_2" src="<?php C::p('ICON_URL'); ?>" alt="<?php $Lang->p('HEALTH_TEXT', 13); ?>" />
					<span><?php $Lang->p('HEALTH_TEXT', 13); ?></span>
				</li>
				<li>
					<img class="caption_icon caption_3" src="<?php C::p('ICON_URL'); ?>" alt="<?php $Lang->p('HEALTH_TEXT', 6); ?>" />
					<span><?php $Lang->p('HEALTH_TEXT', 6); ?></span>
				</li>
				<li>
					<img class="caption_icon caption_1 " src="<?php C::p('ICON_URL'); ?>" alt="<?php $Lang->p('HEALTH_TEXT', 7); ?>" />
					<span><?php $Lang->p('HEALTH_TEXT', 7); ?></span>
				</li>
			</ul>
		</div>
	</div>
	<div id="health_graph"></div>
	<p id="health_graph_tip" class="justify"></p>
</div>