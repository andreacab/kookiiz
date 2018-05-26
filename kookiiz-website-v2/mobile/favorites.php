<?php
    /**********************************************************
    Title: Favorites (mobile)
    Authors: Kookiiz Team
    Purpose: Generate clickable list of user's favorite recipes
    ***********************************************************/

    if(!defined('PAGE_NAME') || PAGE_NAME != 'mobile') die();
    
    /**********************************************************
    SET UP
    ***********************************************************/
    
    //Dependencies
    require_once '../class/globals.php';
    require_once '../class/recipesController.php';
    
    //Init handlers
    $RecipesController = new recipesController($DB, $User);
    
    //Load parameters
    $favp = $Request->get('p');
    $sort = $Request->get('sort');
    if(is_null($favp)) $favp = 0;
    if(is_null($sort)) $sort = 'abc';
    
    /**********************************************************
    SCRIPT
    ***********************************************************/
    
    //Load user's favorites
    $RecipesController->loadFav($sort);
?>
<p class="center">
    <select id="favorites_sorting">
        <option value="abc" <?php if($sort === 'abc') echo 'selected="selected"'; ?>><?php $Lang->p('RECIPES_SORTING', 0); ?></option>
        <option value="price" <?php if($sort === 'price') echo 'selected="selected"'; ?>><?php $Lang->p('RECIPES_SORTING', 2); ?></option>
        <option value="rating" <?php if($sort === 'rating') echo 'selected="selected"'; ?>><?php $Lang->p('RECIPES_SORTING', 3); ?></option>
    </select>
</p>
<div id="recipes_index">
    <?php $RecipesController->displayIndex($favp, C::MOBILE_RECIPES_PERPAGE); ?>  
</div>
<div id="recipes_list">
    <?php $RecipesController->displayList($favp, C::MOBILE_RECIPES_PERPAGE); ?>
</div>
