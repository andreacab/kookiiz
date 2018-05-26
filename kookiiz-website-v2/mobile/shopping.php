<?php
    /**********************************************************
    Title: Shopping (mobile)
    Authors: Kookiiz Team
    Purpose: Generate shopping list display
    ***********************************************************/

    if(!defined('PAGE_NAME') || PAGE_NAME != 'mobile') die();
    
    /**********************************************************
    SET UP
    ***********************************************************/
    
    //Dependencies
    require_once '../class/shoppingController.php';
    
    //Init handlers
    $ShopController = new shoppingController($DB, $User);
    
    //Load parameters
    $day = $Request->get('day');
    if(is_null($day)) $day = 0;
    
    /**********************************************************
    VIEW
    ***********************************************************/
?>
<p class="center">
    <select id="shopping_select">
    <?php $ShopController->listOptions($day); ?>
    </select>
</p>
<div id="shopping_wrap">
<?php $ShopController->display($day); ?>
</div>