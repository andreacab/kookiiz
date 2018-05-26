<?php
	/**********************************************************
    Title: Recipe form
    Authors: Kookiiz Team
    Purpose: HTML content of the recipe form
    ***********************************************************/
	
	/**********************************************************
	SET UP
	***********************************************************/

    //Dependencies
    require_once '../class/dblink.php';
    require_once '../class/globals.php';
    require_once '../class/lang_db.php';
    require_once '../class/partners_lib.php';
    require_once '../class/session.php';
    require_once '../class/units_lib.php';
    require_once '../class/user.php';

    //Start session
    Session::start();

    //Init handlers
	$DB   = new DBLink('kookiiz');
    $Lang = LangDB::getHandler(Session::getLang());
    $User = new User($DB);
    
    //Languages
    $lang       = $User->getLang();
    $langs      = C::get('LANGUAGES');
    $langsNames = C::get('LANGUAGES_NAMES');
    asort($langs);
?>
<div class="column single">
	<h2 class="title">
		<span id="span_recipeform_title" class="click"><?php $Lang->p('RECIPEFORM_TEXT', 0); ?></span>
		<input type="text" id="input_recipeform_title" class="title" maxlength="<?php C::p('RECIPE_TITLE_MAX'); ?>" title="<?php $Lang->p('RECIPEFORM_TEXT', 0); ?>" style="display:none" autocomplete="off" />
		<img id="img_recipeform_edit" class="icon15 click edit" src="<?php C::p('ICON_URL'); ?>" title="<?php $Lang->p('ACTIONS', 25); ?>" alt="<?php $Lang->p('ACTIONS', 25); ?>" />
	</h2>
	<p id="recipeform_existing_caption" style="display:none">
		<span><?php $Lang->p('RECIPEFORM_TEXT', 9); ?></span>
		<span id="recipeform_existing_link" class="click"><?php $Lang->p('RECIPEFORM_TEXT', 10); ?></span>
	</p>
	<table>
	<tbody>
		<tr>
			<td>
				<img class="icon15 guests" src="<?php C::p('ICON_URL'); ?>" title="<?php $Lang->p('RECIPE_DISPLAY_TEXT', 2); ?>" alt="<?php $Lang->p('RECIPE_DISPLAY_TEXT', 2); ?>" />
				<span><?php $Lang->p('RECIPEFORM_TEXT', 2); ?></span>
				<select id="select_recipeform_guests">
                <?php for($i = C::RECIPE_GUESTS_MIN; $i <= C::RECIPE_GUESTS_MAX; $i++): ?>
                    <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                <?php endfor; ?>
				</select>
				<span><?php $Lang->p('RECIPEFORM_TEXT', 3); ?></span>
			</td>
			<td class="right">
				<span class="bold"><?php $Lang->p('VARIOUS', 5); ?>:</span>
				<select id="select_recipeform_lang">
				<?php foreach($langs as $id => $code): ?>
                    <option value="<?php echo $code; ?>"<?php echo ($code == $lang ? ' selected="selected"' : ''); ?>><?php echo $langsNames[$id]; ?></option>
				<?php endforeach; ?>
				</select>
			</td>
		</tr>
	</tbody>
	</table>
</div>
<hr/>
<div class="column wide left_side">
	<table class="left">
	<tbody>
		<tr>
			<td>
				<img class="icon25 category" src="<?php C::p('ICON_URL'); ?>" title="<?php $Lang->p('RECIPE_DISPLAY_TEXT', 2); ?>" alt="<?php $Lang->p('RECIPE_DISPLAY_TEXT', 2); ?>" />
				<span class="bold"><?php $Lang->p('RECIPE_DISPLAY_TEXT', 2); ?>:</span>
			</td>
			<td>
				<select id="select_recipeform_category" class="medium">
				<?php
					//Create an option for each recipe category
                    $categories = $Lang->get('RECIPES_CATEGORIES');
                    asort($categories);
                    foreach($categories as $id => $name)
                    {
                        //Exclude ID 0
                        if(!$id) continue;
                        echo '<option value="', $id,'"', ($id == C::RECIPE_CATEGORY_DEFAULT ? ' selected="selected">' : '>'), $name, '</option>';
                    }
				?>
				</select>
			</td>
		</tr>
		<tr>
			<td>
				<img class="icon25 origin" src="<?php C::p('ICON_URL'); ?>" title="<?php $Lang->p('RECIPE_DISPLAY_TEXT', 3); ?>" alt="<?php $Lang->p('RECIPE_DISPLAY_TEXT', 3); ?>" />
				<span class="bold"><?php $Lang->p('RECIPE_DISPLAY_TEXT', 3); ?>:</span>
			</td>
			<td>
				<select id="select_recipeform_origin" class="medium">
				<?php
					//Create an option for each recipe origin
                    $origins = $Lang->get('RECIPES_ORIGINS');
                    asort($origins);
					foreach($origins as $id => $name)
					{
                        //Exclude ID 0
                        if(!$id) continue;
                        echo '<option value="', $id,'"', ($id == C::RECIPE_ORIGIN_DEFAULT ? ' selected="selected">' : '>'), $name, '</option>';
					}
				?>
				</select>
			</td>
		</tr>
		<tr>
			<td>
				<img class="icon25 level" src="<?php C::p('ICON_URL'); ?>" title="<?php $Lang->p('RECIPE_DISPLAY_TEXT', 7); ?>" alt="<?php $Lang->p('RECIPE_DISPLAY_TEXT', 7); ?>" />
				<span class="bold"><?php $Lang->p('RECIPE_DISPLAY_TEXT', 7); ?>:</span>
			</td>
			<td>
				<select id="select_recipeform_level" class="medium">
				<?php
					//Create an option for each recipe level
                    $levels = $Lang->get('RECIPES_LEVELS');
					foreach($levels as $id => $level)
					{
						echo '<option value="', $id,'"', ($id == C::RECIPE_LEVEL_DEFAULT ? ' selected="selected">' : '>'), $level, '</option>';
					}
				?>
				</select>
			</td>
		</tr>
		<tr>
			<td>
				<img class="icon25 preparation" src="<?php C::p('ICON_URL'); ?>" title="<?php $Lang->p('RECIPE_DISPLAY_TEXT', 5); ?>" alt="<?php $Lang->p('RECIPE_DISPLAY_TEXT', 5); ?>" />
				<span class="bold"><?php $Lang->p('RECIPE_DISPLAY_TEXT', 5); ?>:</span>
			</td>
			<td>
				<span class="input_wrap size_60">
					<input type="text" id="input_recipeform_prep" maxlength="3" autocomplete="off">
				</span>
				<span><?php $Lang->p('VARIOUS', 6); ?></span>
			</td>
		</tr>
		<tr>
			<td>
				<img class="icon25 cooking" src="<?php C::p('ICON_URL'); ?>" title="<?php $Lang->p('RECIPE_DISPLAY_TEXT', 6); ?>" alt="<?php $Lang->p('RECIPE_DISPLAY_TEXT', 6); ?>" />
				<span class="bold"><?php $Lang->p('RECIPE_DISPLAY_TEXT', 6); ?>:</span>
			</td>
			<td>
				<span class="input_wrap size_60">
					<input type="text" id="input_recipeform_cook" maxlength="3" autocomplete="off">
				</span>
				<span><?php $Lang->p('VARIOUS', 6); ?></span>
			</td>
		</tr>
		<tr>
			<td>
				<img class="icon25 price" src="<?php C::p('ICON_URL'); ?>" title="<?php $Lang->p('RECIPE_DISPLAY_TEXT', 15); ?>" alt="<?php $Lang->p('RECIPE_DISPLAY_TEXT', 14); ?>" />
				<span class="bold"><?php $Lang->p('RECIPE_DISPLAY_TEXT', 15); ?>:</span>
			</td>
			<td>
				<span id="span_recipeform_price">0</span>
				<span><?php $Lang->p('RECIPE_DISPLAY_TEXT', 9); ?></span>
			</td>
		</tr>
	</table>
</div>
<div class="column narrow">
	<div class="center">
		<div id="recipeform_picture" class="nopicture click">
            <p class="caption center bold"><?php $Lang->p('RECIPE_DISPLAY_TEXT', 11); ?></p>
            <img id="recipeform_picture_delete" class="button15 cancel" src="<?php C::p('ICON_URL'); ?>" style="display:none" alt="<?php $Lang->p('ACTIONS', 23); ?>" title="<?php $Lang->p('ACTIONS', 23); ?>" />
        </div>
	</div>
</div>
<hr/>
<div class="column medium left_side">
	<div>
		<h5>
			<img class="icon25 books" src="<?php C::p('ICON_URL'); ?>" alt="<?php $Lang->p('RECIPE_DISPLAY_TITLES', 0); ?>" />
			<span><?php $Lang->p('RECIPE_DISPLAY_TITLES', 0); ?></span>
		</h5>
		<p>
			<span><?php $Lang->p('RECIPEFORM_TEXT', 4); ?></span>
		</p>
		<p class="center">
			<textarea id="recipeform_description_input" cols="50" rows="15"></textarea>
		</p>
        <p>
            <span id="recipeform_description_chars" class="small"></span>
			<button type="button" id="recipeform_add_step" class="button_100"><?php $Lang->p('ACTIONS', 14); ?></button>
        </p>
		<div id="recipeform_description"></div>
	</div>
</div>
<div class="column medium">
    <div>
        <h5>
            <img class="icon25 ingredients" src="<?php C::p('ICON_URL'); ?>" alt="<?php $Lang->p('RECIPE_DISPLAY_TITLES', 2); ?>" />
            <span><?php $Lang->p('RECIPE_DISPLAY_TITLES', 2); ?></span>
        </h5>
        <p>
            <span><?php $Lang->p('RECIPEFORM_TEXT', 5); ?></span>
        </p>
		<p class="center">
			<span class="input_wrap size_220">
				<input type="text" id="input_recipeform_ingredient" class="focus" maxlength="25" title="<?php $Lang->p('INGREDIENTS_TEXT', 0); ?>" value="<?php $Lang->p('INGREDIENTS_TEXT', 0); ?>" />
			</span>
		</p>
		<p class="center">
			<span class="bold"><?php $Lang->p('INGREDIENTS_TEXT', 1); ?></span>
			<span class="input_wrap size_60">
				<input type="text" id="input_recipeform_quantity" class="focus quantity" maxlength="6" autocomplete="off" />
			</span>
			<select id="select_recipeform_unit">
			<?php
                //Create an option for each ingredient unit
                $system = C::get('UNITS_SYSTEMS', $User->options_get('units'));
                $units  = UnitsLib::getAll($system);
                foreach($units as $Unit)
                {
                    $id = $Unit->getID();
                    echo '<option value="', $id, '"', $id == C::ING_UNIT_DEFAULT ? ' selected="selected">' : '>', $Lang->get('UNITS_NAMES', $id), '</option>';
                }
			?>
			</select>
			<img id="recipeform_ingredient_add" class="button15 plus" src="<?php C::p('ICON_URL'); ?>" alt="<?php $Lang->p('ACTIONS', 2); ?>" />
		</p>
        <p id="recipeform_ing_missing" class="right click small"><?php $Lang->p('RECIPEFORM_TEXT', 11); ?>?</p>
        <p id="p_recipeform_ingredients" style="display:none"></p>
    </div>
</div>
<div style="height:1px;clear:both;"></div>
<hr/>
<div class="column single">
	<div>
		<h5>
			<span><?php $Lang->p('ACTIONS', 29); ?></span>
		</h5>
		<?php if($User->isAdmin()): ?>
		<p class="left" id="recipeform_partner">
			<span class="bold error">(ADMIN) <?php $Lang->p('RECIPEFORM_TEXT', 6); ?>:</span>
			<select id="select_recipeform_partner" class="large">
			<?php
                $PartnersLib = new PartnersLib($DB);
                $partners = $PartnersLib->listing();
				foreach($partners as $partner)
				{
					$id   = (int)$partner['partner_id'];
					$name = htmlspecialchars($partner['partner_name'], ENT_COMPAT, 'UTF-8');
					echo '<option value="', $id, '"', ($id == C::PARTNER_DEFAULT ? 'selected="selected"' : ''), '>', $name, '</option>';
				}
			?>
			</select>
		</p>
		<?php endif; ?>
		<p>
			<span id="recipeform_instructions"></span>
		</p>
		<p class="center" id="recipeform_status">
            <label class="bold">
                <input type="checkbox" id="check_recipeform_public" checked="checked" />
                <span class="click"><?php $Lang->p('RECIPEFORM_TEXT', 8); ?></span>
            </label>
		</p>
		<p class="center">
			<button type="button" class="button_80" id="recipeform_reset"></button>
			<button type="button" class="button_80" id="recipeform_submit"></button>
		</p>
	</div>
</div>
<div style="height:1px;clear:both;"></div>