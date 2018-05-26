<?php
	/**********************************************************
    Title: Network connect popup
    Authors: Kookiiz Team
    Purpose: HTML content of the social network connection popup
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
    $network = $Request->get('network');
?>
<div id="network_connect">
	<form class="network_connect" action="/dom/network_connect.php" method="post" target="network_connect">
        <input type="hidden" name="network" value="<?php echo $network; ?>" />
		<div id="network_connect_loader" class="center"></div>
		<div id="network_connect_login" class="login center">
			<p id="network_connect_error" class="error center" style="display:none"></p>
			<table class="login">
				<tr>
					<td>
						<p class="bold left">
                            <?php $Lang->p('USER_PROPERTIES', 2); ?>
                        </p>
					</td>
					<td class="left">
						<span class="input_wrap size_160">
							<input type="text" name="email" />
						</span>
					</td>
				</tr>
				<tr>
					<td>
						<p class="bold left">
                            <?php $Lang->p('USER_PROPERTIES', 3); ?>
                        </p>
					</td>
					<td class="left">
						<span class="input_wrap size_160">
							<input type="password" name="password" maxlength="<?php C::p('USER_PASSWORD_MAX'); ?>" />
						</span>
					</td>
				</tr>
			</table>
            <div class="buttons right">
                <button type="submit" class="button_80 connect"><?php $Lang->p('ACTIONS', 17); ?></button>
                <button type="button" class="button_80 create"><?php $Lang->p('ACTIONS', 18); ?></button>
            </div>
		</div>
	</form>
	<iframe name="network_connect" style="display:none"></iframe>
</div>