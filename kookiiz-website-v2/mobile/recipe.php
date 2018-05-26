<?php
    /**********************************************************
    Title: Recipe (mobile)
    Authors: Kookiiz Team
    Purpose: Display recipe in full screen
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
    $recID = $Request->get('cid');
    
    /**********************************************************
    SCRIPT
    ***********************************************************/

    $success = $RecipesController->displayFull($recID);
    if(!$success)
    {
        header('Location: /m');
        die();
    }
?>
