<?php
	/**********************************************************
    Title: Popup
    Authors: Kookiiz Team
    Purpose: HTML code of the custom Kookiiz popup
    ***********************************************************/
?>
<div id="kookiiz_popup" style="display:none">
    <div class="header"></div>
    <div class="main">
        <div class="container">
            <h6 class="title"></h6>
            <img id="popup_cancel" class="button15 cancel" src="<?php C::p('ICON_URL'); ?>" alt="X" title="<?php $Lang->p('ACTIONS', 16); ?>" />
            <div class="loader center"></div>
            <div class="middle">
                <div class="message left"></div>
                <div class="content"></div>
            </div>
            <div class="bottom">
                <button type="button" class="button_80 deny" style="display:none"></button>
                <button type="button" class="button_80 confirm" style="display:none"></button>
            </div>
        </div>
    </div>
    <div class="footer"></div>
</div>
<!-- Gray background -->
<div class="curtain popup" style="display:none"></div>