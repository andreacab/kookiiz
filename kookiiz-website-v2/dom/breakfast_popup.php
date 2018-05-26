<?php
	/**********************************************************
    Title: Breakfast popup
    Authors: Kookiiz Team
    Purpose: HTML content of the popup to edit user's breakfast
    ***********************************************************/

	//Dependencies
	require_once '../class/dblink.php';
	require_once '../class/globals.php';
	require_once '../class/lang_db.php';
	require_once '../class/session.php';
	require_once '../class/units_lib.php';
	require_once '../class/user.php';

    //Start session
    Session::start();

    //Init handlers
    $DB   = new DBLink('kookiiz');
    $Lang = LangDB::getHandler(Session::getLang());
    $User = new User($DB);
?>
<p id="breakfast_popup_message" class="center"></p>
<p class="center">
	<span class="input_wrap size_220 centered">
		<input type="text" class="focus" id="input_breakfast_ingredient" title="<?php $Lang->p('INGREDIENTS_TEXT', 3); ?>" value="<?php $Lang->p('INGREDIENTS_TEXT', 3); ?>" />
	</span>
</p>
<p class="center">
	<span class="bold"><?php $Lang->p('INGREDIENTS_TEXT', 1); ?></span>
	<span class="input_wrap size_60 centered">
		<input type="text" id="input_breakfast_quantity" class="focus enter add" maxlength="<?php C::p('ING_QTY_CHARS'); ?>" />
	</span>
	<select id="select_breakfast_unit">
	<?php
		//Create an option for each ingredient unit
        $system = C::get('UNITS_SYSTEMS', $User->options_get('units'));
        $units  = UnitsLib::getAll($system);
		foreach($units as $Unit)
		{
            $id = $Unit->getID();
			echo '<option value="', $id, '"', $id == C::ING_UNIT_DEFAULT ? 'selected="selected">' : '>', $Lang->get('UNITS_NAMES', $id), '</option>';
		}
	?>
	</select>
	<img id="button_breakfast_add" class="button15 accept" src="<?php C::p('ICON_URL'); ?>" alt="<?php $Lang->p('ACTIONS', 22); ?>" />
</p>