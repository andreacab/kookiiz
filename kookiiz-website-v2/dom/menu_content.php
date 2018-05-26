<?php
	/*******************************************************
    Title: Menu content
    Authors: Kookiiz Team
    Purpose: Generate HTML content for the menu
    ********************************************************/

	//Loop through menu days
    $days_names   = $Lang->get('DAYS_NAMES');
    $months_names = $Lang->get('MONTHS_NAMES');
	for($i = 0, $imax = C::MENU_DAYS_COUNT; $i < $imax; $i++)
	{
		//Get current time
		$current_time   = time() + ($i * 3600 * 24);
		$current_day    = (int)Date("N", $current_time);
		$current_day    = strtoupper($days_names[$current_day - 1]);
		$current_date   = Date("j", $current_time);
		$current_month  = (int)Date("n", $current_time);
		$current_month  = $months_names[$current_month - 1];
		
		//Generate menu boxes
?>
<div id="menu_box_<?php echo $i; ?>" class="menu_box">
    <h5 class="menu_header center">
        <span class="menu_title"><?php echo $current_day; ?></span>
        <br/>
        <span class="menu_date tiny"><?php echo $current_date, ' ', $current_month; ?></span>
    </h5>
    <div class="nutrition" style="display:none"></div>
    <div class="quickmeals" style="display:none">
        <p class="text_color0 bold center"><?php $Lang->p('MENU_TEXT', 3); ?></p>
        <div class="list"></div>
    </div>
    <div class="meals">
    <?php
        //Loop through menu meals
        for($j = 0; $j < C::MENU_MEALS_COUNT; $j++)
        {
    ?>
            <div id="meal_box_<?php echo (3 * $i + $j); ?>" class="meal_box">
                <p class="meal_content text_color0 small click"></p>
                <span class="meal_controls small left" style="display:none">
                    <img class="icon15 guests" src="<?php C::p('ICON_URL'); ?>" alt="<?php $Lang->p('MENU_TEXT', 4); ?>" />
                    <span class="guests_count bold"><?php C::p('MENU_GUESTS_DEFAULT'); ?></span>
                    <span class="guests_control minus bold">-</span>
                    <span class="guests_control plus bold">+</span>
                </span>
                <button type="button" class="meal_delete button15 cancel"></button>
            </div>
            <div id="meal_drop_<?php echo (3 * $i + $j); ?>" class="meal_drop center" style="display:none"><?php $Lang->p('MENU_TEXT', 9); ?></div>
    <?php
        }
    ?>
    </div>
    <div class="quickmeal_drop" style="display:none"></div>
    <div class="shopping none center">
        <img class="icon_shopping icon25 cart click" src="<?php C::p('ICON_URL'); ?>" title="<?php $Lang->p('MENU_TEXT', 7); ?>" alt="<?php $Lang->p('MENU_TEXT', 8); ?>" />
        <img class="status morning click" src="<?php C::p('ICON_URL'); ?>" title="<?php echo $Lang->p('MENU_TEXT', 15); ?>" alt="<?php $Lang->p('MENU_TEXT', 8); ?>" />
        <img class="status none click" src="<?php C::p('ICON_URL'); ?>" title="<?php echo $Lang->p('MENU_TEXT', 17); ?>" alt="<?php $Lang->p('MENU_TEXT', 8); ?>" />
        <img class="status evening click" src="<?php C::p('ICON_URL'); ?>" title="<?php echo $Lang->p('MENU_TEXT', 16); ?>" alt="<?php $Lang->p('MENU_TEXT', 8); ?>" />
    </div>
    <div class="nutrition_slate">
        <img class="icon_nutrition icon25 healthy click" src="<?php C::p('ICON_URL'); ?>" title="" alt="" style="display:none" />
    </div>
    <div class="quickmeal_slate">
        <img class="icon_quickmeals icon25 quickmeal click" src="<?php C::p('ICON_URL'); ?>" title="" alt="" style="display:none" />
    </div>
    <div class="shadow"></div>
</div>
<?php
	}
?>