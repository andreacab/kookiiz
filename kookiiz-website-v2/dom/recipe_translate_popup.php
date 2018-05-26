<?php
	/**********************************************************
    Title: Recipe translate popup
    Authors: Kookiiz Team
    Purpose: HTML content of the recipe translation popup
    ***********************************************************/

	/**********************************************************
	SET UP
	***********************************************************/

    //Dependencies
    require_once '../class/dblink.php';
    require_once '../class/globals.php';
    require_once '../class/lang_db.php';
    require_once '../class/recipes_lib.php';
    require_once '../class/request.php';
    require_once '../class/session.php';
    require_once '../class/user.php';

    //Start session
    Session::start();
    
	//Init handlers
	$DB         = new DBLink('kookiiz');
    $Lang       = LangDB::getHandler(Session::getLang());
    $Request    = new RequestHandler();
    $User       = new User($DB);

    //Load parameters
    $recipe_id = (int)$Request->get('recipe_id');

	/**********************************************************
	SCRIPT
	***********************************************************/

    //Retrieve languages for which no translation of the recipe exists
    $RecipesLib = new RecipesLib($DB, $User);
    $languages      = C::get('LANGUAGES');
    $translations   = $RecipesLib->langsGet($recipe_id);
    $langs_missing  = array_diff($languages, $translations);
    
?>
<p class="center">
    <?php if(count($langs_missing)) { ?>
    <select id="recipe_translate_lang">
    <?php
        $languages_names = C::get('LANGUAGES_NAMES');
        foreach($langs_missing as $lang)
        {
            $id     = array_search($lang, $languages);
            $name   = $languages_names[$id];
            echo "<option value='$lang'>$name</option>";
        }
    ?>
    </select>
    <?php } else { ?>
    <span>
        <?php $Lang->p('RECIPES_TRANSLATE_TEXT', 0); ?>
    </span>
    <?php } ?>
</p>