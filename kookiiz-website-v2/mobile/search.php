<?php
    /**********************************************************
    Title: Search (mobile)
    Authors: Kookiiz Team
    Purpose: Search for recipes
    ***********************************************************/

    if(!defined('PAGE_NAME') || PAGE_NAME != 'mobile') die();
    
    /**********************************************************
    SET UP
    ***********************************************************/
    
    //Dependencies
    require_once $_SERVER['DOCUMENT_ROOT'] . '/class/globals.php';
    require_once $_SERVER['DOCUMENT_ROOT'] . '/class/recipesController.php';
    
    //Init handlers
    $RecipesController = new recipesController($DB, $User);

    //Load parameters
    $srcp = $Request->get('p');
    $sort = $Request->get('sort');
    if(is_null($srcp)) $srcp = 0;
    if(is_null($sort)) $sort = 'score';
    
    /**********************************************************
    SCRIPT
    ***********************************************************/

    //Array of criteria
    $criteria = array(
        'text'      => $Request->get('src_txt'),
        'category'  => $Request->get('src_cat'),
        'origin'    => 0,
        'favorites' => 0,
        'healthy'   => 0,
        'cheap'     => 0,
        'easy'      => 0,
        'quick'     => 0,
        'success'   => 0,
        'veggie'    => 0,
        'chef'      => 0,
        'chef_id'   => 0,
        'fridge'    => 0,
        'season'    => 0,
        'liked'     => 0,
        'disliked'  => 0,
        'allergy'   => 0,
        'random'    => 0
    );

    //Check criteria and set default values
    $src_empty = true;
    foreach($criteria as $name => $value)
    {
        if(is_null($value) || !$value)
        {
            if($name == 'text')
                $criteria[$name] = '';
            else
                $criteria[$name] = 0;
        }
        else
            $src_empty = false;
    }
    if(!$src_empty) $RecipesController->search($criteria, $sort);
    $search_input = $criteria['text'] ? $criteria['text'] : $Lang->get('RECIPES_SEARCH_TEXT', 2);
?>
<div>
    <form action="" method="get">
        <span class="input_wrap size_400 big">
            <input type="text" class="focus" name="src_txt" id="input_search" value="<?php echo $search_input;?>" title="<?php $Lang->p('RECIPES_SEARCH_TEXT', 2); ?>" />
        </span>
        <select name="src_cat" class="xlarge">
        <?php
            //Retrieve list of categories
            $RECIPES_CATEGORIES = $Lang->get('RECIPES_CATEGORIES');
            $categories = array_slice($RECIPES_CATEGORIES, 1); sort($categories);

            //Create an option for each recipe category
            $selected = ($criteria['category'] == 0 ? 'selected="selected"' : '');
            echo '<option value="0" ', $selected, '>', $RECIPES_CATEGORIES[0], '</option>';
            foreach($categories as $index => $name)
            {
                $id = array_search($name, $RECIPES_CATEGORIES);
                $selected = ($criteria['category'] == $id ? 'selected="selected"' : '');
                echo '<option value="', $id,'" ', $selected, '>', $name, '</option>';
            }
        ?>
        </select>
        <input type="submit" value="<?php $Lang->p('ACTIONS', 13); ?>" />
    </form>
</div>
<div id="recipes_list">
    <?php $RecipesController->displayList($srcp, C::MOBILE_RECIPES_PERPAGE); ?>
</div>