<?php
	/**********************************************************
    Title: Shopping print
    Authors: Kookiiz Team
    Purpose: Display printable version of the shopping list
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
    <link rel="stylesheet" href="/min/f=/themes/<?php echo C::THEME; ?>/css/shop_print.css" media="screen" type="text/css" />

    <!-- Print style sheets -->
    <link rel="stylesheet" href="/min/f=/themes/<?php echo C::THEME; ?>/css/shop_print.css" media="print" type="text/css" />
    <link rel="stylesheet" href="/min/f=/themes/<?php echo C::THEME; ?>/css/print.css" media="print" type="text/css" />

	<!-- JS libraries -->
    <script src="https://www.google.com/jsapi?key=ABQIAAAAoOVfj5wULkABS7jnh59RgBT0weiSytlRPz3LR-PHtvBCoqOslBSmDFLfOeq9QmEwoKRna8fMnyqM3A" type="text/javascript"></script>
	<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/prototype/1.7.0.0/prototype.js" charset="utf-8"></script>
    <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/scriptaculous/1.8.3/scriptaculous.js" charset="utf-8"></script>
    <script type="text/javascript" src="/min/f=/js/libs_fixes.js" charset="utf-8"></script>

    <!-- Page title -->
    <title><?php $Lang->p('SHOPPING_TEXT', 24); ?></title>
</head>
<body>
    <!-- Print sheet -->
    <div id="shopping_print_main">
        <!-- Header -->
        <h5 id="shopping_print_title">
            <img id="print_logo" src="/pictures/logo.png" alt="Kookiiz" />
            <span><?php $Lang->p('SHOPPING_TEXT', 24); ?></span>
            <hr />
        </h5>
        <!-- Shopping list -->
        <div id="div_overflow">
            <div id="shopping_full"></div>
        </div>
        <!-- Info -->
        <div id="shopping_print_info" style="display:none">
            <hr />
            <div class="content"></div>
        </div>
        <!-- Footer -->
        <div id="shopping_print_footer">
            <hr />
            <span class="tiny left">
            <?php
                echo $Lang->get('MENU_TEXT', 22), ', ', $Lang->get('VARIOUS', 3),
                        ' ', date('d.m.y'),' ', $Lang->get('VARIOUS', 4), ' ', date('H:i');
            ?>
            </span>
            <span class="tiny right">www.kookiiz.com</span>
        </div>
    </div>

    <!-- Options box -->
    <div id="div_shoppingprint_options" class="noprint">
        <h5>
            <span><?php $Lang->p('MENU_TEXT', 10); ?></span>
        </h5>
        <ul>
            <li>
                <label>
                    <input type="checkbox" id="shopping_icons_check" />
                    <span class="click"><?php $Lang->p('SHOPPING_TEXT', 25); ?></span>
                </label>
            </li>
            <li>
                <label>
                    <input type="checkbox" id="shopping_info_check" />
                    <span class="click"><?php $Lang->p('SHOPPING_TEXT', 23); ?></span>
                </label>
            </li>
        </ul>
        <p class="right">
            <button type="button" id="button_shopping_print" class="button_80"><?php $Lang->p('ACTIONS', 1); ?></button>
        </p>
    </div>
</body>
<!-- Kookiiz scripts -->
<script type="text/javascript" src="/js/globals.js.php" charset="utf-8"></script>
<script type="text/javascript" src="/min/f=/js/library.js" charset="utf-8"></script>
<script type="text/javascript" src="/min/f=/js/observable.js" charset="utf-8"></script>
<script type="text/javascript" src="/min/g=shop_print" charset="utf-8"></script>
<script type="text/javascript" src="/min/f=/js/shopping_print.js" charset="utf-8"></script>
</html>