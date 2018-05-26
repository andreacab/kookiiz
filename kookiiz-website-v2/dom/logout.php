<?php
    /*******************************************************
    Title: Logout
    Authors: Kookiiz Team
    Purpose: Log user out
    ********************************************************/
	
	//Dependencies
	require_once '../class/session.php';

    //Start session
    Session::start();

    //Erase session
    Session::destroy($cookie = true);

    //Redirect user to main page
    header('Location: /');
?>