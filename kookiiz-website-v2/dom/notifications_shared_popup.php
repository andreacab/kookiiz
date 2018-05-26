<?php
    /**********************************************************
    Title: Notifications shared popup
    Authors: Kookiiz Team
    Purpose: HTML content of shared content notification popup
	***********************************************************/
	
	/**********************************************************
	SET UP
	***********************************************************/
	
	//Include external files
	require_once '../class/dblink.php';
	require_once '../class/globals.php';
	require_once '../class/lang_db.php';
	require_once '../class/social.php';
	require_once '../class/session.php';
	require_once '../class/user.php';
    
    //Start session
    Session::start();
	
	//Init handlers
	$DB   = new DBLink('kookiiz');
    $Lang = LangDB::getHandler(Session::getLang());
    $User = new User($DB);
	
	/**********************************************************
	SCRIPT
	***********************************************************/
	
	//Load shared content
    $SocialHandler = new SocialHandler($DB, $User);
	$shared_recipes  = $SocialHandler->shared_recipes_load();
	//$shared_shopping = $SocialHandler->shared_shopping_load();
?>
<p class="bold"><?php $Lang->p('NOTIFICATIONS_TEXT', 4); ?></p>
<?php
	if(count($shared_recipes))
	{
?>
<table id="shared_recipes_table">
<thead>
	<tr>
		<th colspan="2">
            <?php $Lang->p('NOTIFICATIONS_TEXT', 8); ?>
        </th>
		<th>
            <?php $Lang->p('NOTIFICATIONS_TEXT', 9); ?>
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
		//Loop through shared recipes
		foreach($shared_recipes as $recipe)
		{
			//Retrieve parameters
			$id          = $recipe['recipe_id'];
			$name        = $recipe['recipe_name'];
			$pic_id      = $recipe['recipe_pic'];
			$author_id   = $recipe['author_id'];
			$author_name = $recipe['author_name'];
			$date        = $recipe['date'];
			$time        = $recipe['time'];
			$viewed      = $recipe['viewed'];
		
			//Row
			echo '<tr id="shared_recipe_', $id, '"', ($viewed ? '' : ' class="new"'), '>';
			//Recipe picture
			echo '<td class="recipe_picture">',
					'<img src="/pics/recipes-', $pic_id, '-tb" />',
				'</td>';
			//Recipe name
			echo "<td class='recipe_name'>$name</td>";
			//Shared by
			echo '<td>',
					"<input type='hidden' class='sharer' value='$author_id' />",
					$author_name,
				'</td>';
			//Date
			echo "<td>$date</td>";
			//Actions
			echo '<td class="center">',
					'<img class="button15 cancel" src="', C::ICON_URL, '" alt="', $Lang->get('ACTIONS', 15), '" title="', $Lang->get('ACTIONS', 15), '" />',
					'<img class="button15 accept" src="', C::ICON_URL, '" alt="', $Lang->get('ACTIONS', 32), '" title="', $Lang->get('ACTIONS', 32), '" />',
					'<img class="icon15 save click" src="', C::ICON_URL, '" alt="', $Lang->get('ACTIONS', 0), '" title="', $Lang->get('ACTIONS', 0), '" />',
				'</td>';
			echo '</tr>';
		}
?>
</tbody>
</table>
<?php
	}
	else echo '<p class="center">', $Lang->get('NOTIFICATIONS_TEXT', 5), '</p>';
?>
<!--
<p class="bold">
    <?php /*$Lang->p('NOTIFICATIONS_TEXT', 6); ?>
</p>
<?php
	if(count($shared_shopping))
	{
?>
<table id="shared_shopping_table">
<thead>
	<tr>
		<th>
            <?php $Lang->p('NOTIFICATIONS_TEXT', 17); ?>
        </th>
		<th>
            <?php $Lang->p('NOTIFICATIONS_TEXT', 18); ?>
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
		//Loop through shared shopping lists
		foreach($shared_shopping as $shopping)
		{
			$share_id       = $shopping['share_id'];
			$author_name    = $shopping['author_name'];
			$date           = $shopping['date'];
			$time           = $shopping['time'];
			$shopping_date  = $shopping['shopping_date'];
			$viewed         = $shopping['viewed'];
			
			//Row
			echo '<tr', ($viewed ? '' : ' class="new"'), '>';
			//Shopping list
			echo '<td>',
					"<input type='hidden' class='share_id' value='$share_id' />",
					$shopping_date,
				'</td>';
			//Shared by
			echo '<td>',
					$author_name,
				'</td>';
			//Date
			echo "<td>$date</td>";
			//Actions
			echo '<td class="center">',
					'<img class="button15 accept" src="', C::ICON_URL, '" alt="', $Lang->get('ACTIONS', 3), '" title="', $Lang->get('ACTIONS', 3), '" />',
					'<img class="button15 cancel" src="', C::ICON_URL, '" alt="', $Lang->get('ACTIONS', 4), '" title="', $Lang->get('ACTIONS', 4), '" />',
				'</td>';
			echo '</tr>';
		}
?>
</tbody>
</table>
<?php
	}
	else echo '<p class="center">', $Lang->get('NOTIFICATIONS_TEXT', 7), '</p>';

    */
?>
-->