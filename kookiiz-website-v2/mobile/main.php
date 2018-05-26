<?php
    /**********************************************************
    Title: Main (mobile)
    Authors: Kookiiz Team
    Purpose: Homepage for mobile users
    ***********************************************************/

    define('PAGE_NAME', 'mobile');
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
    require_once $_SERVER['DOCUMENT_ROOT'] . '/class/dblink.php';
    require_once $_SERVER['DOCUMENT_ROOT'] . '/class/globals.php';
    require_once $_SERVER['DOCUMENT_ROOT'] . '/class/lang_db.php';
    require_once $_SERVER['DOCUMENT_ROOT'] . '/class/request.php';
    require_once $_SERVER['DOCUMENT_ROOT'] . '/class/session.php';
    require_once $_SERVER['DOCUMENT_ROOT'] . '/class/user.php';

    //Start session
    Session::start();
    
    //Init handlers
    $DB      = new DBLink('kookiiz');
    $Lang    = LangDB::getHandler(Session::getLang());
    $Request = new RequestHandler();
    
    //Load parameters
    $error = $Request->get('error');
    $page  = $Request->get('page');
    if(is_null($page))  $page = '';
    if(is_null($error)) $error = 0;

    /**********************************************************
    SCRIPT
    ***********************************************************/
    
    //Check if page is provided in current language or translate it
    $translation = LangDB::getTranslation('MOBILE_PAGES');
    $pagesNames  = $translation[Session::getLang()];
    $pageID = array_search($page, $pagesNames);
    if($pageID === false)
    {
        foreach($translation as $lang => $pages)
        {
            $pageID = array_search($page, $pages);
            if($pageID !== false)
            {
                $page = $pagesNames[$pageID];
                break;
            }
        }
        if($pageID === false)
            $page = '';
    }
    if(!$page)
    {   
        //Redirect to default page
        $page = $pagesNames[C::MOBILE_PAGE_DEFAULT];
        header("Location: /m/$page" . ($error ? '?error=1' : ''));
    }
    
    //Init user profile
    $User = new User($DB);

    /**********************************************************
    VIEW
    ***********************************************************/

    //Set header for HTML content
    header('Content-Type:text/html; charset=utf-8');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html lang="<?php echo Session::getLang(); ?>" xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <!-- Meta data -->
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <meta name="viewport" content="width=480, initial-scale=1" />

        <!-- Style sheets -->
        <link rel="stylesheet" href="/min/f=/themes/<?php C::p('THEME'); ?>/css/mobile.css" media="screen" type="text/css" />

        <!-- Illustration -->
        <link rel="image_src" type="image/jpeg" href="http://www.kookiiz.com/pictures/logo-square.png" title="<?php $Lang->p('MAIN_TEXT', 6); ?>" />
        <!-- Favicon -->
        <link rel="shortcut icon" type="image/x-icon" href="/pictures/favicon.ico" />
        
        <!-- JS libraries -->
        <script type="text/javascript" src="https://www.google.com/jsapi?key=ABQIAAAAoOVfj5wULkABS7jnh59RgBT0weiSytlRPz3LR-PHtvBCoqOslBSmDFLfOeq9QmEwoKRna8fMnyqM3A"></script>
        <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/prototype/1.7.0.0/prototype.js" charset="utf-8"></script>
        <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/scriptaculous/1.8.3/scriptaculous.js" charset="utf-8"></script>
        <script type="text/javascript" src="http://connect.facebook.net/<?php echo $Lang->getCode(); ?>/all.js#appId=<?php C::p('FACEBOOK_APP_ID'); ?>&amp;xfbml=1"></script>
        <script type="text/javascript" src="/min/f=/js/libs_fixes.js" charset="utf-8"></script>
        
        <!-- PHP generated JS -->
        <script type="text/javascript">
        <!--
        function getPageID()
        {
            return <?php echo $pageID; ?>;
        }
        function session_lang()
        {
            return <?php echo "'", Session::getLang(), "'"; ?>;
        }
        function user_logged()
        {
            return <?php echo $User->getID(); ?>;
        }
        -->
        </script>

        <!-- Page title -->
        <title><?php $Lang->p('MAIN_TITLE', 1); ?> - <?php $Lang->p('MOBILE_TITLES', $pageID); ?></title>
        <!-- Description -->
        <meta name="Description" content="<?php $Lang->p('MAIN_TEXT', 6); ?>" />
    </head>
    <body>
        <!-- Facebook root -->
        <div id="fb-root"></div>
        
        <div id="kookiiz_main" class="center">
            <a href="/m">
                <img id="kookiiz_logo" src="/pictures/logo.png" alt="<?php $Lang->p('MAIN_TEXT', 2); ?>" title="<?php $Lang->p('ACTIONS_LONG', 2); ?>" />
            </a>
            <?php if($User->isLogged()): ?>

            <!-- Content -->
            <div id="kookiiz_content">
                <div <?php if(!C::get('MOBILE_PAGE_LISTED', $pageID)) echo 'style="display:none"'; ?>>
                    <h5 class="center bold">
                        <select id="select_page">
                        <?php foreach($pagesNames as $id => $pageName): ?>
                            <?php if(!C::get('MOBILE_PAGE_LISTED', $id)) continue; ?>
                            <option value="<?php echo $id; ?>" <?php if($id === $pageID) echo 'selected="selected"'; ?>><?php $Lang->p('MOBILE_TITLES', $id); ?></option>
                        <?php endforeach; ?>
                        </select>
                    </h5>
                    <p class="desc"><?php $Lang->p('MOBILE_TEXTS', $pageID); ?></p>
                </div>
                <div>   
                <?php
                    switch($pageID)
                    {
                        case 0:
                            include $_SERVER['DOCUMENT_ROOT'] . '/mobile/shopping.php';
                            break;
                        case 1:
                            include $_SERVER['DOCUMENT_ROOT'] . '/mobile/favorites.php';
                            break;
                        case 2:
                            include $_SERVER['DOCUMENT_ROOT'] . '/mobile/recipe.php';
                            break;
                        case 3:
                            include $_SERVER['DOCUMENT_ROOT'] . '/mobile/search.php';
                            break;
                    }
                ?>
                </div>
            </div>
            
            <?php else: ?>
            
            <!-- Login -->
            <div id="kookiiz_login">
                <?php if($error): ?>
                <p class="error center"><?php $Lang->p('LOGIN_ERRORS', 0); ?></p>
                <?php endif; ?>
                <form method="post" action="/dom/login.php" autocomplete="off">
                    <input type="hidden" name="mode" value="mobile" />
                    <ul class="center">
                        <li style="display:none">
                            <button type="button" class="social facebook_100 text_color0 bold left" data-network="facebook"><?php $Lang->p('ACTIONS', 17); ?></button>
                            <button type="button" class="social twitter_100 text_color0 bold left" data-network="twitter"><?php $Lang->p('ACTIONS', 17); ?></button>
                        </li>
                        <li>
                            <span class="input_wrap size_300">
                                <input type="text" name="email" class="focus" value="<?php $Lang->p('USER_PROPERTIES', 2); ?>" title="<?php $Lang->p('USER_PROPERTIES', 2); ?>" />
                            </span>
                        </li>
                        <li>
                            <span class="input_wrap size_300">
                                <input type="password" name="password" class="focus" maxlength="<?php C::p('USER_PASSWORD_MAX'); ?>" value="<?php $Lang->p('USER_PROPERTIES', 3); ?>" title="<?php $Lang->p('USER_PROPERTIES', 3); ?>" />
                            </span>
                        </li>
                        <li>
                            <span class="passlost click"><?php echo $Lang->get('PASSWORD_TEXT', 9); ?></span>
                        </li>
                        <li>
                            <label>
                                <input type="checkbox" name="remember" />
                                <span class="click"><?php $Lang->p('FRONTPAGE_TEXT', 0); ?></span>
                            </label>
                        </li>
                        <li>
                            <button type="submit" class="button_80"><?php $Lang->p('ACTIONS', 28); ?></button>
                        </li>
                    </ul>
                </form>
            </div>
            
            <?php endif; ?>
            
            <!-- Footer -->
            <div id="kookiiz_footer">
                <?php include $_SERVER['DOCUMENT_ROOT'] . '/dom/footer.php'; ?>
            </div>
        </div>
        
        <!-- Kookiiz popup -->
        <?php include '../dom/popup.php'; ?>
        
        <!-- Kookiiz scripts -->
        <script type="text/javascript" src="/js/globals.js.php" charset="utf-8"></script>
        <!--
        <script type="text/javascript" src="/min/f=/js/library.js" charset="utf-8"></script>
        <script type="text/javascript" src="/min/f=/js/observable.js" charset="utf-8"></script>
        -->
        <script type="text/javascript" src="/min/g=mobile&debug=1"></script>
        <script type="text/javascript" src="/min/f=/js/mobile/main.js"></script>
    </body>
</html>