<?php
	/**********************************************************
    Title: Shopping send popup
    Authors: Kookiiz Team
    Purpose: HTML content of the popup to send shopping by email
    ***********************************************************/
	
	/**********************************************************
	SET UP
	***********************************************************/
	
	//Dependencies
	require_once '../class/dblink.php';
	require_once '../class/globals.php';
	require_once '../class/lang_db.php';
	require_once '../class/session.php';
	require_once '../class/user.php';
	require_once '../class/users_lib.php';

    //Start session
    Session::start();
	
	//Init handlers
	$DB         = new DBLink('kookiiz');
    $Lang       = LangDB::getHandler(Session::getLang());
    $User       = new User($DB);
	
	/**********************************************************
	SCRIPT
	***********************************************************/
	
	//Retrieve user's friends
    $UsersLib = new UsersLib($DB, $User);
	$friends    = $User->friends_ids_get(); //Friend IDs
    $friends    = $UsersLib->get($friends); //User objects
?>
<table>
<colgroup class="headers"></colgroup>
<colgroup></colgroup>
<tbody>
	<tr>
		<td class="bold"><?php $Lang->p('SHOPPING_TEXT', 11); ?></td>
		<td>
			<select id="shopping_send_mode">
				<option value="<?php C::p('SHOPPING_SEND_EMAIL'); ?>" selected="selected"><?php $Lang->p('SHOPPING_TEXT', 12); ?></option>
				<option value="<?php C::p('SHOPPING_SEND_FRIEND'); ?>"><?php $Lang->p('SHOPPING_TEXT', 13); ?></option>
			</select>
		</td>
	</tr>
	<tr>
		<td class="bold"><?php $Lang->p('SHOPPING_TEXT', 14); ?></td>
		<td>
			<span id="shopping_send_input" class="input_wrap size_220">
				<input type="text" id="shopping_send_email" class="focus enter email" value="<?php $Lang->p('VARIOUS', 2); ?>" title="<?php $Lang->p('VARIOUS', 2); ?>" />
			</span>
			<select id="shopping_send_friend" style="display:none">
			<?php
				foreach($friends as $friend)
				{
					echo '<option value="', $friend->getEmail(), '">', $friend->getName(), '</option>';
				}
				if(!count($friends))
                    echo '<option value="">', $Lang->get('FRIENDS_TEXT', 4), '</option>';
			?>
			</select>
		</td>
	</tr>
</tbody>
</table>