<?php
	/**********************************************************
    Title: Members chart
    Authors: Kookiiz Team
    Purpose: Retrieve and returns chart of highest grade members
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
	
	//Retrieve members chart information
	$UsersLib = new UsersLib($DB, $User);
    $chart = $UsersLib->chart();

    /**********************************************************
	DOM GENERATION
	***********************************************************/
	
	header('Content-Type:text/html; charset=utf-8');
?>
<table id="members_chart_table">
<tbody>
	<tr>
		<td>
			<span class="bold"><?php $Lang->p('MEMBERS_CHART_TEXT', 0); ?></span>
		</td>
		<td>
			<span class="bold"><?php $Lang->p('MEMBERS_CHART_TEXT', 1); ?></span>
		</td>
		<td>
			<span class="bold"><?php $Lang->p('MEMBERS_CHART_TEXT', 2); ?></span>
		</td>
		<td>
			<span class="bold"><?php $Lang->p('MEMBERS_CHART_TEXT', 3); ?></span>
		</td>
	</tr>
	<?php
        if(count($chart))
        {           
            foreach($chart as $member)
            {
	?>
			<tr>
				<td>
					<img  class="icon15 cook" src="<?php C::p('ICON_URL'); ?>" alt="<?php $Lang->p('MEMBERS_CHART_TEXT', 4); ?>">
					<span><?php echo $member->getName(); ?></span>
				</td>
				<td>
					<span><?php echo $member->getDate(); ?></span>
				</td>
				<td>
					<?php $member->grade_display($Lang); ?>
				</td>
				<td class="members_chart_recipes center">
					<span><?php echo $member->getRecipesCount(); ?></span>
				</td>
			</tr>
	<?php
            }
        }
        else
        {
    ?>
            <tr>
                <td colspan="4" class="center">
                    <span><?php $Lang->p(); ?></span>
                </td>
            </tr>
    <?php
        }
	?>
</tbody>
</table>