<?php
    /*******************************************************
    Title: Main (API)
    Authors: Kookiiz Team
    Purpose: Handle API calls
    ********************************************************/

    //Dependencies
    require_once '../api/api.php';
    require_once '../class/request.php';

    //Retrieve current API module
    $Request    = new RequestHandler();
    $module     = $Request->get('module');

    //Handle API call
    $API = KookiizAPIFactory::getHandler($module);
    if($API) $API->handle();
?>