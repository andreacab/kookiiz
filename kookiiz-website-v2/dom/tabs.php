<?php
	/**********************************************************
    Title: Tabs
    Authors: Kookiiz Team
    Purpose: HTML content of the main tabs
	***********************************************************/
?>
<?php
    $tabs       = C::get('TABS');
    $tabs_temp  = C::get('TABS_TEMP');
    $tabs_names = $Lang->get('TABS_NAMES');
	foreach($tabs as $index => $tab)
	{
		$tab_name   = $tabs_names[$index];
		$temp       = $tabs_temp[$index];
		
		//Hide admin tabs from regular users
		if(($tab == 'admin' || $tab == 'feedback') && !$User->isAdmin())
            continue;
        //Hide profile tab
        if($tab == 'profile' /*&& !$User->isLogged()*/)
            continue;

        //Display current tab
		echo    
            '<div id="tab_', $tab, '" class="tab', ($temp ? ' temp" style="display:none">' : '">'),
                '<div class="top">',
                    ($temp ? '<img class="button15 cancel" src="' . C::ICON_URL . '" alt="' . $Lang->get('ACTIONS', 16) . '" title="' . $Lang->get('ACTIONS', 16) . '" />' : ''),
                '</div>',
                '<h5 class="content bold center">', $tab_name, '</h5>',
            '</div>';
	}
?>