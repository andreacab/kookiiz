<?php
    /**********************************************************
    Title: Shopping transfer popup
    Authors: Kookiiz Team
    Purpose: HTML content of the popup to transfer shopping lists
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
	$shopping_days  = json_decode($Request->get('shopping_days'), true);
?>
<p class="center">
	<select id="select_shopping_transfer">
		<?php
            $days_names     = $Lang->get('DAYS_NAMES');
            $months_names   = $Lang->get('MONTHS_NAMES');
			foreach($shopping_days as $day_index)
			{
				$day        = date('N', time() + $day_index * 3600 * 24);
				$month      = date('n', time() + $day_index * 3600 * 24);
				$date       = date('j', time() + $day_index * 3600 * 24);
				$day_name   = $days_names[$day - 1];
				$month_name = $months_names[$month - 1];
                
				echo '<option value="', $day_index, '">', $day_name, ' ', $date, ' ', $month_name, '</option>';
			}
		?>
	</select>
</p>