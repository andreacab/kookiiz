<?php
    /**********************************************************
    Title: Notifications friends popup
    Authors: Kookiiz Team
    Purpose: HTML content of friendship requests notification popup
	***********************************************************/
	
	/**********************************************************
	SET UP
	***********************************************************/
	
	//Dependencies
	require_once '../class/dblink.php';
	require_once '../class/globals.php';
	require_once '../class/lang_db.php';
	require_once '../class/user.php';
	require_once '../class/session.php';

    //Start session
    Session::start();
	
	//Init handlers
	$DB         = new DBLink('kookiiz');
    $Lang       = LangDB::getHandler(Session::getLang());
    $User       = new User($DB);
	
	/**********************************************************
	SCRIPT
	***********************************************************/
	
	//Load friendship requests
    $requests = $User->friends_requests();
	if(count($requests))
	{
?>
<table id="friends_requests_table">
<thead>
	<tr>
		<th></th>
		<th>
            <?php $Lang->p('NOTIFICATIONS_TEXT', 0); ?>
        </th>
		<th>
            <?php $Lang->p('NOTIFICATIONS_TEXT', 1); ?>
        </th>
		<th class="center">
            <?php $Lang->p('NOTIFICATIONS_TEXT', 2); ?>
        </th>
	</tr>
</thead>
<tbody>
<?php
		//Loop through friendship requests
		foreach($requests as $request)
		{
			//Retrieve parameters
			$id     = $request['user_id'];
			$fb_id  = $request['fb_id'];
			$name   = $request['name'];
			$pic_id = $request['pic_id'];
			$date   = $request['date'];
			$time   = $request['time'];
		
			//Request row
			echo '<tr id="friend_request_', $id, '">';
			//Friend picture
			if($pic_id || !$fb_id)
			echo '<td>',
					'<img class="avatar" src="/pics/users-', $pic_id, '-tb" />',
				'</td>';
			else
			echo '<td>',
					'<fb:profile-pic uid="', $fb_id, '" class="avatar" size="thumb" linked="false" facebook-logo="true">',
					'</fb:profile-pic>',
				'</td>';
			//Friend name
			echo "<td>$name</td>";
			//Date
			echo '<td>', $Lang->get('VARIOUS', 3), " $date ", $Lang->get('VARIOUS', 4), " $time</td>";
			//Actions
			echo '<td class="center">',
					'<img class="button15 cancel" src="', C::ICON_URL, '" alt="', $Lang->get('ACTIONS', 4), '" title="', $Lang->get('ACTIONS', 4), '" />',
					//'<img class="button15 block" src="', C::ICON_URL, '" alt="', $Lang->get('ACTIONS', 33), '" title="', $Lang->get('ACTIONS', 33), '" />',
					'<img class="button15 accept" src="', C::ICON_URL, '" alt="', $Lang->get('ACTIONS', 3), '" title="', $Lang->get('ACTIONS', 3), '" />',
				'</td>';
			echo '</tr>';
		}
?>
</tbody>
</table>
<?php
	}
	else echo '<p class="center">', $Lang->get('NOTIFICATIONS_TEXT', 3), '</p>';
?>