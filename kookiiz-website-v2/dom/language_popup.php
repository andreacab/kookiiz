<?php
	/**********************************************************
    Title: Language popup
    Authors: Kookiiz Team
    Purpose: HTML content of the language change popup
    ***********************************************************/

    /**********************************************************
	SET UP
	***********************************************************/

    //Dependencies
    require_once '../class/globals.php';
    require_once '../class/session.php';

    //Start session
    Session::start();

    //Load parameters
    $current_lang = Session::getLang();

    /**********************************************************
	SCRIPT
	***********************************************************/
?>
<div class="center">
    <ul class="language_list">
    <?php
        //Loop through available languages
        $languages          = C::get('LANGUAGES');
        $languages_names    = C::get('LANGUAGES_NAMES');
        foreach($languages as $index => $lang)
        {
            $name = $languages_names[$index];
            echo '<li class="center">';
            if($lang == $current_lang)  echo '<span class="bold">', $name, '</span>';
            else                        echo '<a href="javascript:Kookiiz.lang.change(\'', $lang, '\');">', $name, '</a>';
            echo '</li>';
        }
    ?>
    </ul>
</div>