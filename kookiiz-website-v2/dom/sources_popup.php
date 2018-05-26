<?php
	/**********************************************************
    Title: Sources popup
    Authors: Kookiiz Team
    Purpose: HTML content of the sources popup
    ***********************************************************/
	
	/**********************************************************
	SET UP
	***********************************************************/

	//Dependencies
	require_once '../class/globals.php';
	require_once '../class/lang_db.php';
	require_once '../class/session.php';

    //Start session
    Session::start();

    //Init handlers
    $Lang = LangDB::getHandler(Session::getLang());
?>
<ul id="kookiiz_sources">
<?php
    $titles = $Lang->get('SOURCES_TITLES');
    $names  = $Lang->get('SOURCES_NAMES');
    $links  = C::get('SOURCES_LINKS');
	foreach($titles as $id => $title)
	{
		echo	'<li>',
					'<h5>', $title, '</h5>',
					'<a class="text_default" href="', $links[$id], '" target="_blank"><span>', $names[$id], '</span></a>',
				'</li>';
	}
?>
</ul>