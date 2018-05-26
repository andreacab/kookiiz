<?php
	/**********************************************************
    Title: Menu print
    Authors: Kookiiz Team
    Purpose: Display printable version of the menu
    ***********************************************************/
	
	//Dependencies
	require_once '../class/globals.php';
	require_once '../class/lang_db.php';
	require_once '../class/session.php';

    //Start session
    Session::start();

    //Init handlers
    $Lang = LangDB::getHandler(Session::getLang());
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html lang="<?php echo Session::getLang(); ?>">
<head>
    <!-- Meta data -->
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">

    <!-- Screen style sheets -->
    <link rel="stylesheet" href="<?php echo '/themes/', C::THEME, '/css/menu_print.css'; ?>" media="screen" type="text/css" />

    <!-- Print style sheets -->
    <link rel="stylesheet" href="<?php echo '/themes/', C::THEME, '/css/menu_print.css'; ?>" media="print" type="text/css" />
    <link rel="stylesheet" href="<?php echo '/themes/', C::THEME, '/css/print.css'; ?>" media="print" type="text/css" />

    <!-- JS libraries -->
	<script type="text/javascript" src="https://www.google.com/jsapi?key=ABQIAAAAoOVfj5wULkABS7jnh59RgBT0weiSytlRPz3LR-PHtvBCoqOslBSmDFLfOeq9QmEwoKRna8fMnyqM3A"></script>
	<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/prototype/1.7.0.0/prototype.js" charset="utf-8"></script>
	<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/scriptaculous/1.8.3/scriptaculous.js" charset="utf-8"></script>
    <script type="text/javascript" src="/min/f=/js/libs_fixes.js" charset="utf-8"></script>

    <!-- Page title -->
    <title><?php $Lang->p('MENU_TEXT', 18); ?></title>
</head>
<body>
<div id="menu_print_main">
	<div id="menu_print_title">
		<img id="print_logo" src="/pictures/logo.png" alt="Kookiiz" />
		<h1><?php $Lang->p('MENU_TEXT', 18); ?></h1>
		<hr />
	</div>
	<div id="menu_print_stats" style="display:none">
		<div class="content"></div>
		<div class="empty"><?php $Lang->p('MENU_TEXT', 21); ?></div>
		<hr />
	</div>
	<div id="menu_print_totalnut" style="display:none">
		<div class="content"></div>
		<hr />
	</div>
	<div id="menu_print_content">
		<div id="div_menu_content">
        <?php
            //Loop through menu days
            $days_names   = $Lang->get('DAYS_NAMES');
            $months_names = $Lang->get('MONTHS_NAMES');
            for($i = -C::MENU_DAYS_PAST, $imax = C::MENU_DAYS_FUTURE; $i < $imax; $i++)
            {
                $current_time   = time() + ($i * 3600 * 24);
                $current_day    = (int)Date('N', $current_time);
                $current_day    = strtoupper($days_names[$current_day - 1]);
                $current_date   = Date('j', $current_time);
                $current_month  = (int)Date('n', $current_time);
                $current_month  = $months_names[$current_month - 1];
        ?>
			<div  id="menu_box_<?php echo $i; ?>" class="menu_box">
				<div  class="content">
					<h5 class="menu_header center">
						<span class="menu_title"><?php echo $current_day; ?></span>
                        <br/>
						<span class="menu_date tiny"><?php echo $current_date, ' ', $current_month; ?></span>
					</h5>
					<?php
						//Loop through menu meals
						for($j = 0; $j < C::MENU_MEALS_COUNT; $j++)
						{
					?>
							<div id="meal_box_<?php echo (3 * $i + $j); ?>" class="meal_box">
								<div class="meal_content small"></div>
								<p class="right">
									<span class="span_guests_count bold">
                                        <?php C::p('MENU_GUESTS_DEFAULT'); ?>
                                    </span>
									<img class="icon_guests guests" src="/pictures/icons/dish_icon15.png" title="<?php $Lang->p('MENU_TEXT', 3); ?>" alt="<?php $Lang->p('MENU_TEXT', 4); ?>" />
								</p>
							</div>
					<?php
						}
					?>
					<div class="menu_shopping center">
						<img class="icon_shopping cart" src="/pictures/icons/cart_icon25.png" title="<?php $Lang->p('MENU_TEXT', 7); ?>" alt="<?php $Lang->p('MENU_TEXT', 8); ?>" />
						<span class="shopping_day"><?php $Lang->p('SHOPPING_STATUS', 0); ?></span>
					</div>
				</div>
			</div>
			<div id="nutrition_box_<?php echo $i; ?>" class="nutrition_box" style="display:none">
				<div class="content">
					<h6><?php $Lang->p('MENU_TEXT', 14); ?></h6>
					<p class="display"></p>
				</div>
			</div>
			<?php
				}
			?>
		</div>
	</div>
	<div id="menu_print_footer">
		<hr />
		<span class="tiny left">
            <?php echo $Lang->get('MENU_TEXT', 22), ', ', $Lang->get('VARIOUS', 3), ' ',
                        date('d.m.y'), ' ',$Lang->get('VARIOUS', 4), ' ', date('H:i'); ?>
        </span>
		<span class="tiny right"><?php echo 'www.kookiiz.com'; ?></span>
	</div>
</div>
<div id="menu_print_options" class="noprint">
    <h5>
        <span><?php $Lang->p('MENU_TEXT', 10); ?></span>
    </h5>
	<ul>
		<li>
			<span><?php $Lang->p('MENU_TEXT', 11); ?></span>
			<select id="menu_print_start"></select>
			<span><?php $Lang->p('MENU_TEXT', 12); ?></span>
			<select id="menu_print_stop"></select>
		</li>
		<li>
			<label>
                <input type="checkbox" id="menu_print_withstats" />
                <span class="click"><?php $Lang->p('MENU_TEXT', 19); ?></span>
            </label>
		</li>
		<li>
			<label>
                <input type="checkbox" id="menu_print_withtotalnut" />
                <span class="click"><?php $Lang->p('MENU_TEXT', 20); ?></span>
            </label>
		</li>
		<li>
			<label>
                <input type="checkbox" id="menu_print_withnut" />
                <span class="click"><?php $Lang->p('MENU_TEXT', 13); ?></span>
            </label>
		</li>
	</ul>
    <p class="right">
        <button type="button" id="button_menu_print" class="button_80"><?php $Lang->p('ACTIONS', 1); ?></button>
    </p>
</div>
</body>

<!-- Kookiiz scripts -->
<script type="text/javascript" src="/js/globals.js.php" charset="utf-8"></script>
<script type="text/javascript" src="/min/f=/js/observable.js" charset="utf-8"></script>
<script type="text/javascript" src="/min/g=menu_print" charset="utf-8"></script>
<script type="text/javascript" src="/min/f=/js/menu_print.js" charset="utf-8"></script>
</html>