<?php
    /**********************************************************
    Title: Terms popup
    Authors: Kookiiz Team
    Purpose: HTML content of the terms of use popup
	***********************************************************/
	
	/**********************************************************
	SET UP
	***********************************************************/
	
	//Dependencies
    require_once '../class/session.php';

    //Start session
    Session::start();
	
	//Load parameters
	$lang = Session::getLang();
	
?>
<div id="kookiiz_terms">
    <?php include "../terms/terms_$lang.php"; ?>
</div>