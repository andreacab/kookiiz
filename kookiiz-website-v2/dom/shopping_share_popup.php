<?php
	/**********************************************************
    Title: Shopping share popup
    Authors: Kookiiz Team
    Purpose: HTML content of the popup to share shopping with a friend
    ***********************************************************/
	
	/**********************************************************
	SET UP
	***********************************************************/
	
	//Dependencies
	require_once '../class/dblink.php';
	require_once '../class/lang_db.php';
	require_once '../class/request.php';
	require_once '../class/session.php';
	require_once '../class/user.php';
	require_once '../class/users_lib.php';

    //Start session
    Session::start();

	//Init handlers
	$DB         = new DBLink('kookiiz');
    $Lang       = LangDB::getHandler(Session::getLang());
    $Request    = new RequestHandler();
    $User       = new User($DB);
	
    //Load parameters
	$shared = json_decode($Request->get('shared'), true);
	
	/**********************************************************
	SCRIPT
	***********************************************************/
	
	//User must be logged to share shopping with friends
	if(!$User->isLogged()) die();

    //Retrieve user's friends
    $UsersLib = new UsersLib($DB, $User);
	$friends    = $User->friends_ids_get();         //Friend IDs
    $friends    = $UsersLib->get($friends);         //User objects

    //Remove users with whom the list is already shared
    //and store their names in dedicated array
    $shared_names = array();
    foreach($friends as $index => $friend)
    {
        if(in_array($friend->getID(), $shared))
        {
            $shared_names[] = $friend->getName();
            unset($friends[$index]);
        }
    }
    $friends = array_values($friends);
?>
<p class="center">
	<select id="shopping_share_friend" class="large">
	<?php
		foreach($friends as $friend)
		{
			echo '<option value="', $friend->getID(), '">', $friend->getName(), '</option>';
		}
		if(!count($friends)) echo '<option value="0">', $Lang->get('FRIENDS_TEXT', 4), '</option>';
	?>
	</select>
</p>
<?php if(count($shared_names)) { ?>
<p class="center">
    <?php echo $Lang->get('SHOPPING_TEXT', 19), ' : ', implode(', ', $shared_names); ?>
</p>
<?php } ?>