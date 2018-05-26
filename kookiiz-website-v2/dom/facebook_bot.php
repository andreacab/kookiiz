<?php
    /**********************************************************
    Title: Facebook Bot
    Authors: Kookiiz Team
    Purpose: Landing page for Facebook bot
    ***********************************************************/

    /**********************************************************
    SCRIPT
    ***********************************************************/

    Session::createVisitor();
    $User = new User($DB);

    $query_tab = $Request->get('tab');
    $query_cid = $Request->get('cid');
    
    //Find current tab ID
    $tabID = 0;
    $tabKeys = LangDB::getTranslation('URL_HASH_TABS');
    if($query_tab)
    {
        foreach($tabKeys as $lang => $keys)
        {
            $index = array_search($query_tab, $keys);
            if($index !== false)
            {
                $tabID = $index;
                break;
            }
        }
    }
    
    //Recipe tab
    if($tabID == 4)
    {
        require_once '../class/recipes_lib.php';
        $RecipesLib = new RecipesLib($DB, $User);
        $data = $RecipesLib->load(array((int)$query_cid));
        if(count($data))
            $recipe = $data[0];
        else
            $tabID = 0;
    }
    
    //Page properties
    $title = 'Kookiiz - '; $desc = $Lang->get('MAIN_TEXT', 6); $text = '';
    $imageSRC = 'http://www.kookiiz.com/pictures/logo-square.png';
    switch($tabID)
    {
        case 4:
            $title .= $recipe['name'];
            $text = $recipe['desc'];
            if(strlen($text) > 200)
                $text = substr($text, 0, 200) . '...';
            $imageSRC = "http://www.kookiiz.com/pics/recipes-{$recipe['pic']}";
            break;
            
        default:
            $title .= $LangDB->get('TABS_TITLES', $tabID);
            break;
    }
    
    //Build path
    $path = $query_tab ? "/$query_tab" : '';
    $path = $path && $query_cid ? ($path . '-' . $query_cid) : $path;

    /**********************************************************
    DOM GENERATION
    ***********************************************************/

    //Set header for HTML content
    header('Content-Type:text/html; charset=utf-8');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html 
    lang="<?php echo Session::getLang(); ?>"
    xmlns="http://www.w3.org/1999/xhtml"
    xmlns:og="http://opengraphprotocol.org/schema/">
    <head>
        <!-- Meta data -->
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        
        <!-- FB meta properties -->
        <meta property="og:title" content="<?php echo $title; ?>"/>
        <meta property="og:type" content="website"/>
        <meta property="og:url" content="http://www.kookiiz.com<?php echo $path; ?>"/>
        <meta property="og:image" content="<?php echo $imageSRC; ?>"/>
        <meta property="og:site_name" content="Kookiiz"/>
        <meta property="fb:admins" content="564971984,562237851"/>
        <meta property="fb:app_id" content="<?php C::p('FACEBOOK_APP_ID'); ?>"/>

        <!-- Illustration -->
        <link rel="image_src" type="image/jpeg" href="<?php echo $imageSRC; ?>" title="<?php echo $title; ?>" />

        <!-- Favicon -->
        <link rel="shortcut icon" type="image/x-icon" href="/pictures/favicon.ico" />

        <!-- Page title -->
        <title><?php echo $title; ?></title>
        <!-- Description -->
        <meta name="Description" content="<?php echo $text; ?>" />
    </head>
    <body>
        <img src="<?php echo $imageSRC; ?>" alt="<?php echo $title; ?>" title="<?php echo $title; ?>" />
        <p><?php echo $text; ?></p>
    </body>
</html>