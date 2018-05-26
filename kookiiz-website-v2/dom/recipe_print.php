<?php
	/**********************************************************
    Title: Recipe print
    Authors: Kookiiz Team
    Purpose: HTML content of the recipe print sheet
    ***********************************************************/
	
	/**********************************************************
	SET UP
	***********************************************************/
    
	//Dependencies
    require_once '../class/globals.php';
    require_once '../class/lang_db.php';
    require_once '../class/request.php';
    require_once '../class/session.php';

    //Start session
    Session::start();

    //Init handlers
    $Lang       = LangDB::getHandler(Session::getLang());
    $Request    = new RequestHandler();
	
	//Load parameters
	$recipe_id = (int)$Request->get('recipe_id');
	if(!$recipe_id) header('Location: /');
	
	/**********************************************************
	DOM GENERATION
	***********************************************************/
?>
<html lang="<?php echo Session::getLang(); ?>">
<head>
    <!-- Meta data -->
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">

    <!-- Screen style sheets -->
    <link rel="stylesheet" href="<?php echo '/themes/', C::THEME, '/css/recipe_print.css'; ?>" media="screen" type="text/css" />

    <!-- Print style sheets -->
    <link rel="stylesheet" href="<?php echo '/themes/', C::THEME, '/css/recipe_print.css'; ?>" media="print" type="text/css" />
    <link rel="stylesheet" href="<?php echo '/themes/', C::THEME, '/css/print.css'; ?>" media="print" type="text/css" />

    <!-- JS libraries -->
    <script src="https://www.google.com/jsapi?key=ABQIAAAAoOVfj5wULkABS7jnh59RgBT0weiSytlRPz3LR-PHtvBCoqOslBSmDFLfOeq9QmEwoKRna8fMnyqM3A" type="text/javascript"></script>
	<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/prototype/1.7.0.0/prototype.js" charset="utf-8"></script>
	<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/scriptaculous/1.8.3/scriptaculous.js" charset="utf-8"></script>
    <script type="text/javascript" src="/min/f=/js/libs_fixes.js" charset="utf-8"></script>

    <!-- Globals -->
    <script type="text/javascript" charset="utf-8">
    <!--
    <?php
      echo "var RECIPE_ID = $recipe_id;\n";
    ?>
    -->
   </script>

    <!-- Page title -->
	<title></title>
</head>
<body>
<div id="recipe_print">
    <div id="recipe_print_header">
        <h4 class="display title float"></h4>
		<div class="display rating left float"></div>
		<div class="display icons center float"></div>
		<hr class="float" />
		<div id="recipe_print_picture" class="picture center float">
			<img class="display picture" alt="<?php $Lang->p('RECIPE_DISPLAY_TEXT', 0); ?>" />
		</div>
		<div class="properties float">
			<ul class="float">
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
			<ul class="float">
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
					<span class="currency_display">CHF</span>
					<span><?php $Lang->p('RECIPE_DISPLAY_TEXT', 9); ?></span>
				</li>
			</ul>
		</div>
    </div>  
    <div class="recipe_print_left">
        <div id="recipe_print_description">
            <h5>
                <img class="icon25 books" src="<?php C::p('ICON_URL'); ?>" alt="<?php $Lang->p('RECIPE_DISPLAY_TITLES', 0); ?>" />
                <span>
                    <?php $Lang->p('RECIPE_DISPLAY_TITLES', 0); ?>
                </span>
            </h5>
            <p class="display description"></p>
        </div>
		<div id="recipe_comments_module">
			<h5>
				<img class="icon25 book" src="<?php C::p('ICON_URL'); ?>" alt="<?php $Lang->p('RECIPE_DISPLAY_TITLES', 1); ?>" />
				<span>
                    <?php $Lang->p('RECIPE_DISPLAY_TITLES', 1); ?>
                </span>
			</h5>
			<p id="recipe_print_comments"></p>
		</div>
    </div>    
    <div class="recipe_print_right">
        <div id="recipe_print_ingredients">
            <h5>
                <img class="icon25 ingredients" src="<?php C::p('ICON_URL'); ?>" alt="<?php $Lang->p('RECIPE_DISPLAY_TITLES', 2); ?>" />
                <span>
                    <?php $Lang->p('RECIPE_DISPLAY_TITLES', 2); ?>
                </span>
            </h5>
            <p class="display ingredients"></p>
        </div>
        <div id="recipe_print_nutrition">
            <h5>
                <img class="icon25 healthy" src="<?php C::p('ICON_URL'); ?>" alt="<?php $Lang->p('RECIPE_DISPLAY_TITLES', 3); ?>" />
                <span>
                    <?php $Lang->p('RECIPE_DISPLAY_TITLES', 3); ?>*
                </span>
            </h5>
            <p class="display nutrition"></p>
            <p class="italic_right">
                *<?php $Lang->p('RECIPE_DISPLAY_TEXT', 10); ?>
            </p>
        </div>
		<div id="recipe_print_wines" style="display:none">
			<h5>
				<img class="icon25 wine" src="<?php C::p('ICON_URL'); ?>" alt="<?php $Lang->p('RECIPE_DISPLAY_TITLES', 4); ?>" />
				<span>
                    <?php $Lang->p('RECIPE_DISPLAY_TITLES', 4); ?>
                </span>
			</h5>
			<p class="italic"><?php $Lang->p('RECIPE_DISPLAY_TEXT', 11); ?><p>
			<p class="display wines"><?php $Lang->p('RECIPE_DISPLAY_TEXT', 13); ?></p>
		</div>
    </div>
	<div id="recipe_print_footer">
		<img src="/pictures/logo.png" class="logo" alt="Kookiiz" />
		<span class="tiny">
            <?php echo $Lang->get('RECIPE_PRINT_TEXT', 0), ' (www.kookiiz.com) ', $Lang->get('VARIOUS', 3); ?>
        </span>
		<span class="tiny">
            <?php echo date('d.m.y'), ' ', $Lang->get('VARIOUS', 4), ' ', date('H:i'); ?>
        </span>
	</div>
</div>
<div id="recipe_print_options" class="noprint">
    <h5><?php $Lang->p('RECIPE_PRINT_TEXT', 1); ?></h5>
    <ul>
        <li class="print_option">
            <label>
                <input type="checkbox" id="check_picture" />
                <span class="click"><?php $Lang->p('RECIPE_PRINT_TEXT', 2); ?></span>
            </label>
        </li>
        <li class="print_option">
            <label>
                <input type="checkbox" id="check_nutrition" />
                <span class="click"><?php $Lang->p('RECIPE_PRINT_TEXT', 3); ?></span>
            </label>
        </li>
		<li class="print_option" style="display:none">
            <label>
                <input type="checkbox" id="check_wines" />
                <span class="click"><?php $Lang->p('RECIPE_PRINT_TEXT', 4); ?></span>
            </label>
        </li>
        <li id="print_option_comments" class="print_option">
            <label>
                <input type="checkbox" id="check_comments" />
                <span class="click"><?php $Lang->p('RECIPE_PRINT_TEXT', 5); ?></span>
            </label>
        </li>
    </ul>
    <p class="right">
		<button type="button" id="print_button" class="button_80"><?php $Lang->p('ACTIONS', 1); ?></button>
    </p>
</div>
</body>

<!-- Kookiiz scripts -->
<script type="text/javascript" src="/js/globals.js.php" charset="utf-8"></script>
<script type="text/javascript" src="/min/f=/js/library.js" charset="utf-8"></script>
<script type="text/javascript" src="/min/f=/js/observable.js" charset="utf-8"></script>
<script type="text/javascript" src="/min/g=recipe_print" charset="utf-8"></script>
<script type="text/javascript" src="/min/f=/js/recipe_print.js" charset="utf-8"></script>
</html>