<?php
    /**********************************************************
    Title: Invitation time popup
    Authors: Kookiiz Team
    Purpose: HTML content of the invitation time selector popup
	***********************************************************/
	
	/**********************************************************
	SET UP
	***********************************************************/
	
	//Dependencies
    require_once '../class/lang_db.php';
    require_once '../class/request.php';
    require_once '../class/session.php';

    //Start session
    Session::start();

    //Init handlers
    $Lang       = LangDB::getHandler(Session::getLang());
    $Request    = new RequestHandler();
	
	//Load parameters
	$year   = (int)$Request->get('year');
	$month  = (int)$Request->get('month');
	$day    = (int)$Request->get('day');
	$hour   = (int)$Request->get('hour');
	$minute = (int)$Request->get('minute');
	
	/**********************************************************
	SCRIPT
	***********************************************************/
?>
<table>
<tbody>
	<tr>
		<td class="bold"><?php $Lang->p('KEYWORDS', 11); ?></td>
		<td>
			<select id="invitation_day">
			<?php
				for($i = 1, $imax = 32; $i < $imax; $i++)
				{
					echo '<option ', ($i == $day ? 'selected="selected"' : ''), ' value="', $i, '">', ($i < 10 ? '0' : ''), $i, '</option>';
				}
			?>
			</select>
			<select id="invitation_month">
			<?php
				for($i = 1, $imax = 13; $i < $imax; $i++)
				{
					echo '<option ', ($i == $month ? 'selected="selected"' : ''), ' value="', $i, '">', ($i < 10 ? '0' : ''), $i, '</option>';
				}
			?>
			</select>
			<select id="invitation_year">
			<?php
                $cur_year = date('Y');
				for($i = $cur_year, $imax = $cur_year + 2; $i < $imax; $i++)
				{
					echo '<option ', ($i == $year ? 'selected="selected"' : ''), ' value="', $i, '">', $i, '</option>';
				}
			?>
			</select>
		</td>
	</tr>
	<tr>
		<td class="bold"><?php $Lang->p('KEYWORDS', 12); ?></td>
		<td>
			<select id="invitation_hour">
			<?php
				for($i = 0, $imax = 24; $i < $imax; $i++)
				{
					echo '<option ', ($i == $hour ? 'selected="selected"' : ''), ' value="', $i, '">', ($i < 10 ? '0' : ''), $i, '</option>';
				}
			?>
			</select>
			<select id="invitation_minute">
			<?php					
				for($i = 0, $imax = 60; $i < $imax; $i += 15)
				{
					echo '<option ', ($i == $minute ? 'selected="selected"' : ''), ' value="', $i, '">', ($i < 10 ? '0' : ''), $i, '</option>';
				}
			?>
			</select>
		</td>
	</tr>
</tbody>
</table>