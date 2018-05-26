<?php
    /**********************************************************
    Title: Notifications invitations popup
    Authors: Kookiiz Team
    Purpose: HTML content of invitations alerts notification popup
	***********************************************************/
	
	/**********************************************************
	SET UP
	***********************************************************/
	
	//Dependencies
	require_once '../class/dblink.php';
	require_once '../class/globals.php';
	require_once '../class/invitations_lib.php';
	require_once '../class/lang_db.php';
	require_once '../class/request.php';
	require_once '../class/session.php';
	require_once '../class/user.php';

    //Start session
    Session::start();
	
	//Init handlers
    $DB      = new DBLink('kookiiz');
    $Lang    = LangDB::getHandler(Session::getLang());
    $Request = new RequestHandler();
    $User    = new User($DB);
	
	//Load parameters
	$action = $Request->get('action');
	
	/**********************************************************
	SCRIPT
	***********************************************************/

    //Init invitations library
    $InvitationsLib = new InvitationsLib($DB, $User);
	
	//Take appropriate action
	if(!is_null($action))
	{
		switch($action)
		{
			case 'accept':
                $invitation_id = (int)$Request->get('invitation_id');
                $InvitationsLib->respond($invitation_id, C::INV_STATUS_ACCEPT);
                break;
            
			case 'deny':
                $invitation_id = (int)$Request->get('invitation_id');
                $InvitationsLib->respond($invitation_id, C::INV_STATUS_DENY);
                break;
		}
	}
	
	//Load invitations alerts
	$alerts = $InvitationsLib->alerts();
?>
<p class="bold">
    <?php $Lang->p('NOTIFICATIONS_TEXT', 10); ?>
</p>
<?php if(count($alerts['upcoming'])): ?>
<table id="invitations_upcoming_table">
<thead>
	<tr>
		<th><?php $Lang->p('NOTIFICATIONS_TEXT', 14); ?></th>
		<th><?php $Lang->p('NOTIFICATIONS_TEXT', 15); ?></th>
		<th><?php $Lang->p('NOTIFICATIONS_TEXT', 16); ?></th>
	</tr>
</thead>
<tbody>
<?php
		//Loop through upcoming invitations alerts
		foreach($alerts['upcoming'] as $invitation)
		{
			//Retrieve parameters
			$id             = $invitation['id'];
			$title          = $invitation['title'];
			$location       = $invitation['location'];
			$date           = $invitation['date'];
			$time           = $invitation['time'];
			$author_id      = $invitation['user_id'];
			$author_name    = $invitation['user_name'];
			$status         = $invitation['status'];
			$viewed         = $invitation['viewed'];
			
			//Current user status
			$status_text = '';
			if($author_id == $User->getID())    
                $status_text = $Lang->get('INVITATIONS_TEXT', 10);  //Host
			else                                
                $status_text = $Lang->get('INVITATIONS_TEXT', 11);  //Guest
		
			//Alert row
			echo '<tr id="invitation_upcoming_', $id, '">';
			//Description
			echo '<td>',
					"<p class='invitation_property'>$title</p>",
					'<p class="invitation_property">',
                        '<strong>', $Lang->get('PANEL_INVITATIONS_TEXT', 2), '</strong>: ',
                        $location,
                    '</p>',
					'<p class="invitation_property">',
                        '<strong>', $Lang->get('INVITATIONS_TEXT', 15), '</strong>: ',
                        ($author_id == $User->getID() ? $Lang->get('VARIOUS', 12) : $author_name),
                    '</p>',
				'</td>';
			//Date
			echo "<td>$date<br/>$time</td>";
			//Status
			echo "<td>$status_text</td>";
			echo '</tr>';
		}
?>
</tbody>
</table>
<?php
	else: echo '<p class="center">', $Lang->get('NOTIFICATIONS_TEXT', 12), '</p>'; 
    endif;
?>
<p class="bold"><?php $Lang->p('NOTIFICATIONS_TEXT', 11); ?></p>
<?php if(count($alerts['requests'])): ?>
<table id="invitations_requests_table">
<thead>
	<tr>
		<th><?php $Lang->p('NOTIFICATIONS_TEXT', 14); ?></th>
		<th><?php $Lang->p('NOTIFICATIONS_TEXT', 15); ?></th>
		<th><?php $Lang->p('NOTIFICATIONS_TEXT', 16); ?></th>
		<th></th>
	</tr>
</thead>
<tbody>
<?php
		//Loop through invitation requests
		foreach($alerts['requests'] as $invitation)
		{
			//Retrieve parameters
			$id             = $invitation['id'];
			$title          = $invitation['title'];
			$date           = $invitation['date'];
			$time           = $invitation['time'];
			$author_id      = $invitation['user_id'];
			$author_name    = $invitation['user_name'];
			$status         = $invitation['status'];
			$viewed         = $invitation['viewed'];
			
			//Current user status
			$status_text = '';
			switch($status)
			{
				case C::INV_STATUS_ACCEPT:
                    $status_text = $Lang->get('INVITATIONS_TEXT', 12);
                    break;
				case C::INV_STATUS_DENY:
                    $status_text = $Lang->get('INVITATIONS_TEXT', 13);
                    break;
				case C::INV_STATUS_SENT:
                    $status_text = $Lang->get('INVITATIONS_TEXT', 11);
                    break;
			}
		
			//Request row
			echo '<tr id="invitation_request_', $id, '" class="status_', $status, '">';
			//Description
			echo '<td>',
					"<p class='invitation_property'>$title</p>",
					'<p class="invitation_property">',
                        '<strong>', $Lang->get('INVITATIONS_TEXT', 15), '</strong>: ',
                        $author_name,
                    '</p>',
				'</td>';
			//Date
			echo "<td>$date<br/>$time</td>";
			//Status
			echo "<td>$status_text</td>";
			//Action
			echo '<td>';
			if($status != C::INV_STATUS_ACCEPT)
                echo '<img class="button15 accept" src="', C::ICON_URL, '" alt="', $Lang->get('ACTIONS', 3), '" title="', $Lang->get('ACTIONS', 3), '" />';
			if($status != C::INV_STATUS_DENY)
                echo '<img class="button15 cancel" src="', C::ICON_URL, '" alt="', $Lang->get('ACTIONS', 4), '" title="', $Lang->get('ACTIONS', 4), '" />';
            echo '</td>';
			echo '</tr>';
		}
?>
</tbody>
</table>
<?php
	else: echo '<p class="center">', $Lang->get('NOTIFICATIONS_TEXT', 13), '</p>'; 
    endif;
?>