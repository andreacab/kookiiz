<?php
	/**********************************************************
    Title: Main
    Authors: Kookiiz Team
    Purpose: Kookiiz homepage
    ***********************************************************/

    /**********************************************************
	CONSTANTS
	***********************************************************/

    define('PAGE_NAME', 'main');
    define('KVERSION', 2);
    if($_SERVER['SERVER_NAME'] == 'kookiiz.local')
        define('DEBUG', true);
    else
        define('DEBUG', false);

    if(DEBUG)
    {
        //Show all errors and notices
        error_reporting(E_ALL);
        ini_set('display_errors','1');
    }
	
	/**********************************************************
	SET UP
	***********************************************************/
	
	//Dependencies
	require_once '../class/dblink.php';
	require_once '../class/facebook.php';
	require_once '../class/globals.php';
	require_once '../class/lang_db.php';
    require_once '../class/mobile_detect.php';
	require_once '../class/request.php';
	require_once '../class/session.php';
	require_once '../class/units_lib.php';
	require_once '../class/user.php';
    require_once '../secure/facebook.php';

    //Start session
    Session::start();

    //Init handlers
    $DB      = new DBLink('kookiiz');
    $Lang    = LangDB::getHandler(Session::getLang());
    $Request = new RequestHandler();

    /**********************************************************
	SCRIPT
	***********************************************************/

    //Redirect phones to mobile website
    $mobdetect = new Mobile_Detect();
    if($mobdetect->isMobile() && !$mobdetect->isTablet()) header('Location: /m');

    //Check if query contains something
    if(!empty($_GET))
    {
        //Store query into session
        $Request->queryToSession(PAGE_NAME);

        //Check if tab and/or content ID are provided
        $query_tab = $Request->get('tab');
        $query_cid = $Request->get('cid');
        $query_txt = $Request->get('txt');

        //Check if tab is provided in current language or translate it
        $translation = LangDB::getTranslation('URL_HASH_TABS');
        $tabID = array_search($translation[Session::getLang()], $query_tab);
        if($tabID === false)
        {
            foreach($translation as $lang => $tabs)
            {
                $tabID = array_search($query_tab, $tabs);
                if($tabID !== false)
                {
                    $query_tab = $translation[Session::getLang()][$tabID];
                    break;
                }
            }
            if($tabID === false)
            {
                $query_tab = '';
                $tabID = 0;
            }
        }

        //Reload page with hash
        if($query_tab)
        {
            //Generate special content for FB bot on recipe tab
            if($tabID === 4 
                && strpos($_SERVER['HTTP_USER_AGENT'], 'facebookexternal') !== false)
            {
                include '../dom/facebook_bot.php';
                exit();
            }

            $hash = $query_tab . ($query_cid ? "-$query_cid" : '') . ($query_txt ? "-$query_txt" : '');
            header("Location: /#/$hash");
        }
        //Reload page with clean URL
        else
            header('Location: /');

        //Exit script (IMPORTANT !)
        exit();
    }
    //Load query string from session
    $Request->queryFromSession(PAGE_NAME);

    //Create visitor if no user session is available
    if(Session::getStatus() == Session::STATUS_NONE)
        Session::createVisitor();
        
    //Init user profile
    $User = new User($DB);
	
	/**********************************************************
	VIEW
	***********************************************************/

    //Set header for HTML content
	header('Content-Type:text/html; charset=utf-8');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html 
    lang="<?php echo Session::getLang(); ?>"
    xmlns="http://www.w3.org/1999/xhtml"
    xmlns:fb="http://www.facebook.com/2008/fbml"
    xmlns:og="http://opengraphprotocol.org/schema/">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

    <!-- FB meta properties -->
	<meta property="og:title" content="<?php $Lang->p('MAIN_TITLE', 0); ?>"/>
	<meta property="og:type" content="website"/>
	<meta property="og:url" content="http://www.kookiiz.com"/>
	<meta property="og:image" content="http://www.kookiiz.com/pictures/logo-square.png"/>
	<meta property="og:site_name" content="Kookiiz"/>
	<meta property="fb:admins" content="564971984,562237851"/>
	<meta property="fb:app_id" content="<?php C::p('FACEBOOK_APP_ID'); ?>"/>

    <!-- Screen style sheets -->
    <?php if(KVERSION == 2): ?>
    <link rel="stylesheet" href="/min/f=/themes/<?php C::p('THEME'); ?>/css/main.v2.css" media="screen" type="text/css" />
    <?php else: ?>
	<link rel="stylesheet" href="/min/f=/themes/<?php C::p('THEME'); ?>/css/main.css" media="screen" type="text/css" />
    <?php endif; ?>

    <!-- IE style fixes -->
    <!--[if lte IE 7]>
    <link rel="stylesheet" href="/css/ie7_fixes.css" media="screen" type="text/css" />
    <![endif]-->
    
    <!-- Illustration -->
    <link rel="image_src" type="image/jpeg" href="http://www.kookiiz.com/pictures/logo-square.png" title="<?php $Lang->p('MAIN_TEXT', 6); ?>" />
    <!-- Favicon -->
	<link rel="shortcut icon" type="image/x-icon" href="/pictures/favicon.ico" />
       
    <!-- Page title -->
    <title><?php $Lang->p('MAIN_TITLE', 0); ?></title>
    <!-- Description -->
    <meta name="Description" content="<?php $Lang->p('MAIN_TEXT', 6); ?>" />

    <!-- Google Analytics -->
    <script type="text/javascript">
        var _gaq = _gaq || [];
        _gaq.push(['_setAccount', 'UA-20236266-1']);
        _gaq.push(['_trackPageview']);

        (function()
        {
            var ga  = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
            ga.src  = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
            var s   = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
        })();
    </script>

    <!-- JS libraries -->
    <script type="text/javascript" src="https://www.google.com/jsapi?key=ABQIAAAAoOVfj5wULkABS7jnh59RgBT0weiSytlRPz3LR-PHtvBCoqOslBSmDFLfOeq9QmEwoKRna8fMnyqM3A"></script>
	<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/prototype/1.7.0.0/prototype.js" charset="utf-8"></script>
	<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/scriptaculous/1.8.3/scriptaculous.js" charset="utf-8"></script>
    <script type="text/javascript" src="http://connect.facebook.net/<?php echo $Lang->getCode(); ?>/all.js#appId=<?php C::p('FACEBOOK_APP_ID'); ?>&amp;xfbml=1"></script>
    <script type="text/javascript" src="/min/f=/js/libs_fixes.js" charset="utf-8"></script>

    <!-- PHP generated JS -->
	<script type="text/javascript">
	<!--
    function session_lang()
    {
        return <?php echo "'", Session::getLang(), "'"; ?>;
    }
    function session_onload()
    {
    <?php
        $notify = $Request->get('notify', 'GET');
        if($User->isLogged() && $notify)
            echo "Kookiiz.notifications.popup('$notify');";
    ?>
    }
    function user_isadmin()
    {
        return <?php echo $User->isAdmin() ? 1 : 0; ?>;
    }
    function user_isadminsup()
    {
        return <?php echo $User->isAdminSup() ? 1 : 0; ?>;
    }
	function user_isnew()
	{
		return <?php echo $User->isNew() ? 1 : 0; ?>;
	}
	function user_logged()
	{
		return <?php echo $User->getID(); ?>;
	}
	-->
	</script>
</head>
<body>
    <!-- Facebook root -->
    <div id="fb-root"></div>
    <!-- Hash iframe -->
    <iframe id="kookiiz_hash_iframe" style="display:none"></iframe>
    <!-- Main container -->
    <div id="kookiiz_main">

        <!-- Main header -->
        <div id="kookiiz_header">
            <div id="kookiiz_header_left">
                <div>
                    <a href="/">
                        <img id="kookiiz_logo" src="/pictures/logo.png" alt="<?php $Lang->p('MAIN_TEXT', 2); ?>" title="<?php $Lang->p('ACTIONS_LONG', 2); ?>" />
                    </a>
                    <button type="button" id="button_recipeform" class="button_shiny small"><?php $Lang->p('MAIN_TEXT', 7); ?></button>
                </div>
            </div>
            <div id="kookiiz_header_center">
                <div id="kookiiz_news">
                    <h6><?php $Lang->p('NEWS_TEXT', 0); ?></h6>
                    <div class="feed"></div>
                    <p class="date"></p>
                    <p class="numb"></p>
                    <p class="more bold">
                        <a href="http://www.facebook.com/kookiizapp" target="_blank"><?php $Lang->p('VARIOUS', 23); ?></a>
                    </p>
                    <img class="icon15 transfer click" src="<?php C::p('ICON_URL'); ?>" alt="<?php $Lang->p('ACTIONS', 40); ?>" title="<?php $Lang->p('ACTIONS', 40); ?>" />
                </div>
            </div>
            <div id="kookiiz_header_right">

                <!-- Information area -->
                <div id="kookiiz_user">
                    <?php include '../dom/user_area.php'; ?>
                    <button type="button" id="button_feedback" class="button_shiny small"><?php $Lang->p('MAIN_TEXT', 1); ?></button>
                </div>

            </div>
        </div>

        <!-- Left column -->
        <div id="kookiiz_column_left">

            <!-- Panels -->
            <div id="kookiiz_panels_left" class="panels_area">
                <?php include (KVERSION == 2) ? '../dom/panels_left.v2.php' : '../dom/panels_left.php';?>
            </div>

        </div>
        <!-- End of left column -->

        <!-- Center column -->
        <div id="kookiiz_column_center">

            <!-- Main tabs -->
            <div id="tabs_main">
                <?php include '../dom/tabs.php'; ?>
            </div>

            <div id="kookiiz_section">

                <!-- Section header -->
                <div id="kookiiz_section_header"></div>

                <!-- Tab loader -->
                <div id="tab_loader" class="center" style="display:none"></div>

                <!-- Main -->
                <div id="section_main" class="kookiiz_section" style="display:block">
                    <div class="section_content">
                    <?php include (KVERSION == 2) ? '../dom/search.v2.php' : '../dom/search.php'; ?>
                    </div>
                </div>

                <!-- Health -->
                <div id="section_health" class="kookiiz_section" style="display:none">
                    <div class="section_content">
                    <?php include '../dom/health.php'; ?>
                    </div>
                </div>

                <!-- Community -->
                <div id="section_share" class="kookiiz_section" style="display:none">
                    <div class="section_content">
                    <?php include (KVERSION == 2) ? '../dom/share.v2.php' : '../dom/share.php'; ?>
                    </div>
                </div>

                <!-- Options -->
                <!--
                <div id="section_profile" class="kookiiz_section" style="display:none">
                    <div class="section_content">
                    <?php //include (KVERSION == 2) ? '../dom/profile.v2.php' : '../dom/profile.php'; ?>
                    </div>
                </div>
                -->

                <?php if($User->isAdmin()): ?>
                <!-- Admin -->
                <div id="section_admin" class="kookiiz_section" style="display:none">
                    <div class="section_content">
                    <?php include '../dom/admin.php'; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Shopping list finalization -->
                <div id="section_shopping_finish" class="kookiiz_section" style="display:none">
                    <div class="section_content">
                    <?php include '../dom/shopping_finish.php'; ?>
                    </div>
                </div>

                <!-- Recipe form -->
                <div id="section_recipe_form" class="kookiiz_section" style="display:none">
                    <div class="section_content"></div>
                </div>

                <!-- Recipe translation -->
                <div id="section_recipe_translate" class="kookiiz_section" style="display:none">
                    <div class="section_content"></div>
                </div>

                <!-- Recipe full display -->
                <div id="section_recipe_full" class="kookiiz_section" style="display:none">
                    <div class="section_content">
                    <?php include '../dom/recipe_display.php'; ?>
                    </div>
                </div>

                <!-- 404 not found -->
                <div id="section_error_404" class="kookiiz_section" style="display:none">
                    <div class="section_content">
                    <?php include '../dom/section_404.php'; ?>
                    </div>
                </div>

                <!-- Section footer -->
                <div id="kookiiz_section_footer"></div>

            </div>
            <!-- End of section -->

            <!-- Menu -->
            <?php if(KVERSION == 1): ?>
            <div id="kookiiz_menu">
            <?php include '../dom/menu.php'; ?>
            </div>
            <?php include '../dom/footer.php'; ?>
            <?php endif; ?>

            <!-- Recipes hint -->
            <div id="recipes_search_hint" style="display:none"></div>

        </div>
        <!-- End of center column -->

        <!-- Right column -->
        <div id="kookiiz_column_right">

            <!-- Panels area -->
            <div id="kookiiz_panels_right" class="panels_area">
            <?php include '../dom/panels_right.php'; ?>
            </div>

        </div>
        <!-- End of right column -->

        <!-- Kookiiz illustration -->
        <div id="kookiiz_illustration"></div>
    </div>
    <!-- End of main container -->

    <!-- Spacer -->
    <?php if(KVERSION == 2): ?>
    <div id="kookiiz_spacer"></div>
    <?php endif; ?>
    
    <!-- Menu dock -->
    <?php if(KVERSION == 2): ?>
    <div id="kookiiz_dock">
    <?php include '../dom/dock.php'; ?>
    </div>
    <?php endif; ?>

    <!-- Recipe preview -->
    <div id="recipe_preview" style="display:none">
    <?php include '../dom/recipe_preview.php'; ?>
    </div>

    <!-- Panels help -->
    <div id="panels_help" style="display:none">
        <img class="button15 cancel" src="<?php C::p('ICON_URL'); ?>" alt="X" title="<?php $Lang->p('ACTIONS', 16); ?>" />
        <div class="content">
            <p class="text justify"></p>
        </div>
    </div>

    <!-- Kookiiz hover -->
    <?php include '../dom/hover.php'; ?>
    <!-- Kookiiz popup -->
    <?php include '../dom/popup.php'; ?>
    <!-- Kookiiz welcome screen -->
    <div id="kookiiz_welcome" style="display:none"></div>
    <div class="curtain welcome" style="display:none"></div>
    <!-- Kookiiz help -->
    <div id="kookiiz_help" style="display:none"></div>
    <div class="curtain help" style="display:none"></div>

    <!-- Draggable fake list -->
    <ul id="draggable_fake_list">
        <li id="draggable_fake_item"></li>
    </ul>

    <!-- Kookiiz scripts -->
    <?php
        $URLJSgroup = 'g=main' . (KVERSION == 2 ? '.v2' : '') . (DEBUG ? '&debug=1' : '');
        $URLJSmain = 'f=/js/main' . (KVERSION == 2 ? '.v2' : '') . '.js' . (DEBUG ? '&debug=1' : '');
    ?>
    
    <script type="text/javascript" src="/js/globals.js.php" charset="utf-8"></script>
    <script type="text/javascript" src="/min/f=/js/library.js" charset="utf-8"></script>
    <script type="text/javascript" src="/min/f=/js/observable.js" charset="utf-8"></script>
    <script type="text/javascript" src="/min/<?php echo $URLJSgroup; ?>" charset="utf-8"></script>
    <script type="text/javascript" src="/min/<?php echo $URLJSmain ?>" charset="utf-8"></script>

    <?php if($User->isAdmin()): ?>
    <script type="text/javascript" src="/admin/admin.js" charset="utf-8"></script>
    <?php endif; ?>
</body>
</html>