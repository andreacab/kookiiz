<?php
	/**********************************************************
    Title: Email change popup
    Authors: Kookiiz Team
    Purpose: HTML content of the popup to change user email
    ***********************************************************/
	
	/**********************************************************
	SET UP
	***********************************************************/
	
	//Dependencies
	require_once '../class/globals.php';
	require_once '../class/lang_db.php';
	require_once '../class/request.php';
	require_once '../class/session.php';

    //Start session
    Session::start();

    //Init handlers
    $Lang    = LangDB::getHandler(Session::getLang());
    $Request = new RequestHandler();

	//Load parameters
	$email = htmlspecialchars($Request->get('email'), ENT_COMPAT, 'UTF-8');
?>
<div id="email_change">
	<form action="/dom/email_change.php" method="post" target="iframe_emailchange">
		<div id="email_change_loader" class="center"></div>
		<div id="email_change_inputs" class="center">
			<p id="email_change_error" class="error center" style="display:none"></p>
			<table class="login">
				<tr>
					<td>
						<p class="bold left"><?php $Lang->p('EMAIL_TEXT', 0); ?></p>
					</td>
					<td class="left">
						<input type="hidden" name="email_old" value="<?php echo $email; ?>" />
						<span><?php echo $email; ?></span>
					</td>
				</tr>
				<tr>
					<td>
						<p class="bold left"><?php $Lang->p('EMAIL_TEXT', 1); ?></p>
					</td>
					<td class="left">
						<span class="input_wrap size_220">
							<input type="text" id="options_email_new1" name="email_new1" />
						</span>
					</td>
				</tr>
				<tr>
					<td>
						<p class="bold left"><?php $Lang->p('EMAIL_TEXT', 2); ?></p>
					</td>
					<td class="left">
						<span class="input_wrap size_220">
							<input type="text" id="options_email_new2" name="email_new2" />
						</span>
					</td>
				</tr>
				<tr>
					<td>
						<p class="bold left"><?php $Lang->p('USER_PROPERTIES', 3); ?></p>
					</td>
					<td class="left">
						<span class="input_wrap size_220">
							<input type="password" name="password" maxlength="<?php C::p('USER_PASSWORD_MAX'); ?>" />
						</span>
					</td>
				</tr>
			</table>
			<div class="buttons right">
				<button type="submit" class="button_80 change"><?php $Lang->p('ACTIONS', 21); ?></button>
				<button id="email_change_cancel" type="button" class="button_80 cancel"><?php $Lang->p('ACTIONS', 5); ?></button>
			</div>
		</div>
	</form>
	<iframe name="iframe_emailchange" style="display:none"></iframe>
</div>