<?php
	/**********************************************************
    Title: Password change popup
    Authors: Kookiiz Team
    Purpose: HTML content of the popup to change user password
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
<div id="password_change">
	<form action="/dom/password_change.php" method="post" target="iframe_passchange">
		<div id="password_change_loader" class="center"></div>
		<div id="password_change_inputs" class="center">
			<p id="password_change_error" class="error center" style="display:none"></p>
			<table class="login">
				<tr>
					<td>
						<p class="bold left"><?php $Lang->p('PASSWORD_TEXT', 8); ?></p>
					</td>
					<td class="left">
						<span class="input_wrap size_160">
							<input type="password" name="password_old" />
						</span>
					</td>
				</tr>
				<tr>
					<td>
						<p class="bold left"><?php $Lang->p('PASSWORD_TEXT', 5); ?></p>
					</td>
					<td class="left">
						<span class="input_wrap size_160">
							<input type="password" id="options_password_new1" name="password_new1" maxlength="<?php C::p('USER_PASSWORD_MAX'); ?>" />
						</span>
					</td>
				</tr>
				<tr>
					<td>
						<p class="bold left"><?php $Lang->p('PASSWORD_TEXT', 6); ?></p>
					</td>
					<td class="left">
						<span class="input_wrap size_160">
							<input type="password" id="options_password_new2" name="password_new2" maxlength="<?php C::p('USER_PASSWORD_MAX'); ?>" />
						</span>
					</td>
				</tr>
			</table>
			<div class="buttons right">
				<button type="submit" class="button_80 change"><?php $Lang->p('ACTIONS', 21); ?></button>
				<button id="password_change_cancel" type="button" class="button_80 cancel"><?php $Lang->p('ACTIONS', 5); ?></button>
			</div>
		</div>
	</form>
	<iframe name="iframe_passchange" style="display:none"></iframe>
</div>