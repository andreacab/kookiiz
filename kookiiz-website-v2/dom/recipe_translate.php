<?php
	/**********************************************************
    Title: Recipe translate
    Authors: Kookiiz Team
    Purpose: HTML code of the tab to translate a recipe
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

    //Check that user is logged
    if(!$User->isLogged()) die();

    //Load original recipe content
    $RecipesLib = new RecipesLib($DB, $User);
    $recipe     = $RecipesLib->load(array($recipe_id));
    $recipe     = isset($recipe[0]) ? $recipe[0] : null;
    if(is_null($recipe)) die();

    //Retrieve recipe language ID
    $languages  = C::get('LANGUAGES');
    $lang_id    = array_search($recipe['lang'], $languages);

    //Format description steps
    $description_steps = array();
    $description_split = explode("\n", $recipe['desc']);
    foreach($description_split as $chunk)
    {
        $chunk = trim($chunk);
        if(strlen($chunk))
        {
            $description_steps[] = preg_replace("/[0-9]+.\s/", '', $chunk, $limit = 1);
        }
    }
?>
<div class="column single">
    <h3>
        <span class="title">
            <?php echo $recipe['name']; ?>
        </span>
    </h3>
    <h3>
        <input id="recipe_translate_title" type="text" maxlength="<?php C::p('RECIPE_TITLE_MAX'); ?>" value="" />
    </h3>
</div>
<div id="recipe_translate_descriptions" class="column single">
    <div class="center">
        <table>
        <thead>
            <tr>
                <th class="center">
                    <?php echo C::get('LANGUAGES_NAMES', $lang_id); ?>
                </th>
                <th class="center" id="recipe_translate_target"></th>
            </tr>
        </thead>
        <tbody>
        <?php
            $RECIPES_TRANSLATE_TEXT = $Lang->get('RECIPES_TRANSLATE_TEXT');
            foreach($description_steps as $index => $step)
            {
                if(!$step) continue;
                echo '<tr>',
                        '<td colspan="2">',
                            '<span class="bold">', $RECIPES_TRANSLATE_TEXT[1], ' ', ($index + 1), '</span>',
                            '<hr/>',
                        '</td>',
                     '<tr>',
                     '<tr>',
                        '<td class="half"><span>', $step, '</span></td>',
                        '<td class="half center"><textarea class="step_input"></textarea></td>',
                     '</tr>';
            }
        ?>
        </tbody>
        </table>
    </div>
    <p class="center">
        <button type="button" id="button_recipe_translate" class="button_80">
            <?php $Lang->p('ACTIONS', 2); ?>
        </button>
    </p>
</div>
