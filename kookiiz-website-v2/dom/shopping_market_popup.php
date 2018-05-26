<?php
	/**********************************************************
    Title: Shopping market popup
    Authors: Kookiiz Team
    Purpose: HTML code of the popup to create a new market
    ***********************************************************/

    //Dependencies
	require_once '../class/globals.php';
	require_once '../class/lang_db.php';
	require_once '../class/session.php';

    //Start session
    Session::start();

    //Init handlers
    $Lang = LangDB::getHandler(Session::getLang());
?>
<div class="center">
	<span class="input_wrap size_220">
		<input type="text" id="input_shopping_market" class="focus" maxlength="<?php C::p('MARKET_NAME_MAX'); ?>" value="<?php $Lang->p('SHOPPING_TEXT', 5); ?>" title="<?php $Lang->p('SHOPPING_TEXT', 5); ?>" />
	</span>
</div>