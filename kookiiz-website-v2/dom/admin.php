<?php
	/**********************************************************
    Title: Admin
    Authors: Kookiiz Team
    Purpose: HTML content of the admin tab
	***********************************************************/

    /**********************************************************
	SET-UP
	***********************************************************/

    //Ensure page was not loaded directly
    if(!defined('PAGE_NAME')) die();

    //Dependencies
	require_once '../class/partners_lib.php';
    
    /**********************************************************
	SCRIPT
	***********************************************************/
    
    //Languages
    $langs = C::get('LANGUAGES');
    $langsNames = C::get('LANGUAGES_NAMES');
    
    //Partners
    $PartnersLib = new PartnersLib($DB);
    $partners = $PartnersLib->listing();
    
    //Months
    $month  = date('n');
    $months = $Lang->get('MONTHS_NAMES');
    
    //Feedback
    $feedbackTypes = $Lang->get('FEEDBACK_TYPES');
    
    /**********************************************************
	DOM
	***********************************************************/
?>
<div class="nav_section column single">
	<h5>
		<img class="icon25 book" src="<?php C::p('ICON_URL'); ?>" alt="Glossaire" />
		<span class="nav_title">Glossaire</span>
	</h5>
	<p>Pour ajouter un terme, remplissez les champs ci-dessous. Pour éditer ou supprimer un terme, cherchez-le dans le panneau glossaire et cliquez dessus.</p>
	<p class="left">
		<span class="bold">Langue:</span>
		<select id="admin_glossary_lang">
		<?php foreach($langs as $id => $key): ?>
            <option value="<?php echo $key; ?>"><?php echo $langsNames[$id]; ?></option>
		<?php endforeach; ?>
		</select>
		<span class="input_wrap size_220">
			<input type="text" id="admin_glossary_keyword" class="focus" title="Mot-clé" value="Mot-clé" />
		</span>
	</p>
	<p class="center">
		<textarea id="admin_glossary_definition" class="focus" title="Définition" cols="20" rows="25"></textarea>
	</p>
	<p class="center">
		<button type="button" class="button_80" id="admin_glossary_validate"><?php $Lang->p('ACTIONS', 2); ?></button>
		<button type="button" class="button_80 disabled" id="admin_glossary_delete" disabled="disabled"><?php $Lang->p('ACTIONS', 23); ?></button>
		<button type="button" class="button_80" id="admin_glossary_clear"><?php $Lang->p('ACTIONS', 15); ?></button>
	</p>
</div>
<div class="nav_section column single">
	<h5>
		<span class="nav_title">Partenaires</span>
	</h5>
	<p class="bold">Ajout</p>
	<p>Tous les champs sont nécessaires.</p>
	<p>
		<span class="input_wrap size_500">
			<input type="text" id="admin_partner_name" class="focus" title="Nom du partenaire" value="Nom du partenaire" />
		</span>
	</p>
	<p>
		<span class="input_wrap size_500">
			<input type="text" id="admin_partner_link" class="focus" title="Adresse web" value="Adresse web" />
		</span>
	</p>
	<p>
		<span class="input_wrap size_500">
			<input type="text" id="admin_partner_banner" class="focus" title="Adresse de la bannière" value="Adresse de la bannière" />
		</span>
	</p>
	<p class="center">
		<label>
            <input type="checkbox" id="admin_partner_valid" />
            <span class="click">Afficher ce partenaire (un accord a été conclu avec lui)</span>
        </label>
	</p>
	<p class="center">
		<button type="button" class="button_80" id="admin_partner_save"><?php $Lang->p('ACTIONS', 0); ?></button>
		<button type="button" class="button_80" id="admin_partner_clear"><?php $Lang->p('ACTIONS', 15); ?></button>
	</p>
	<p class="bold">Edition/Suppression</p>
	<p>
		<span>Sélection du partenaire:</span>
		<select id="admin_partner_select" class="large">
			<option value="0">Partenaire</option>
        <?php foreach($partners as $partner):
                $id = (int)$partner['partner_id'];
                if($id == C::RECIPE_AUTHOR_DEFAULT) continue;
                $name = htmlspecialchars($partner['partner_name'], ENT_COMPAT, 'UTF-8');
        ?>
            <option value="<?php echo $id; ?>"><?php echo $name; ?></option>
        <?php endforeach; ?>
		</select>
		<button type="button" class="button_80" id="admin_partner_edit"><?php $Lang->p('ACTIONS', 25); ?></button>
		<button type="button" class="button_80" id="admin_partner_delete"><?php $Lang->p('ACTIONS', 23); ?></button>
	</p>
</div>
<div class="nav_section column single">
	<h5>
		<span class="nav_title">Ingrédients</span>
	</h5>
	<p class="bold">Ingrédients de saison</p>
	<p>Cette fonctionnalité permet de créer une association ingrédient-mois.</p>
	<p class="center">
		<span class="input_wrap size_180">
			<input type="text" class="focus" id="admin_season_ingredient" autocomplete="off" title="nom de l'ingrédient" value="nom de l'ingrédient" />
		</span>
		<select id="admin_season_month">
		<?php foreach($months as $i => $name): ?>
            <option value="<?php echo $i + 1; ?>" <?php echo ($i + 1 == $month ? 'selected="selected">' : '>'), $name; ?></option>
        <?php endforeach; ?>
		</select>
		<button type="button" class="button_80" id="admin_season_create"><?php $Lang->p('ACTIONS', 18); ?></button>
	</p>
</div>
<div class="nav_section column single">
	<h5>
		<span class="nav_title">Feedback</span>
	</h5>
	<p>Sélectionner le type et le nombre de feedbacks à afficher.</p>
	<p class="center">
		<span class="bold">Type</span>
		<select id="admin_feedback_type">
			<option value="-1">Tous</option>
        <?php foreach($feedbackTypes as $id => $type): ?>
			<option value="<?php echo $id; ?>"><?php echo $type; ?></option>
        <?php endforeach; ?>
		</select>
		<span class="bold">Nombre</span>
		<select id="admin_feedback_count">
			<option value="0">Tous</option>
			<option value="10">10</option>
			<option value="20">20</option>
			<option value="50">50</option>
			<option value="100">100</option>
		</select>
		<button type="button" class="button_80" id="admin_feedback_display">Afficher</button>
	</p>
	<div id="feedback_container" class="center"></div>
</div>
<div class="nav_section column single">
	<h5>
		<span class="nav_title">Feedback statistique</span>
	</h5>
    <p>
        Appuyer sur "<?php $Lang->p('ACTIONS', 42); ?>" pour charger les statistiques. Pour sélectionner les questions à soumettre
        aux utilisateurs, cocher/décocher la case en regard de chaque question et appuyer sur "<?php $Lang->p('ACTIONS', 43); ?>".
    </p>
	<p class="center">
		<button type="button" class="button_80" id="admin_feedback_stats"><?php $Lang->p('ACTIONS', 42); ?></button>
		<button type="button" class="button_80" id="admin_feedback_enable"><?php $Lang->p('ACTIONS', 43); ?></button>
	</p>
    <p class="right">
        <span>tous</span>
        <input type="checkbox" id="adminStatsCheckAll"></input>
    </p>
	<div id="feedback_stats" class="center"></div>
</div>
<?php if($User->isAdminSup()): ?>
<div class="nav_section column single">
	<h5>
		<span class="nav_title">Recettes</span>
	</h5>
	<p class="bold">Validation ou supression</p>
	<p>
        Cette fonctionnalité permet de consulter la liste des recettes qui ont été invalidées,
        puis de choisir de les supprimer définitivement ou de les rendre à nouveau valides.
    </p>
	<div class="center">
		<button type="button" class="button_80" id="admin_recipes_dismisslist"><?php $Lang->p('ACTIONS', 42); ?></button>
        <p id="adminRecipesDismissList"></p>
	</div>
</div>
<?php endif; ?>
<?php if($User->isAdminSup()): ?>
<div class="nav_section column single">
	<h5>
		<span class="nav_title">Utilisateurs</span>
	</h5>
	<p class="bold">Créer un administrateur</p>
	<p>Cette fonctionnalité permet d'élever les privilèges d'un utilisateur pour en faire un administrateur.</p>
	<p class="center">
		<span>ID de l'utilisateur:</span>
		<span class="input_wrap size_60">
			<input type="text" class="focus" id="admin_user_admin_id" maxlength="5" autocomplete="off" title="0" value="0" />
		</span>
		<button type="button" class="button_80" id="admin_user_elect"><?php $Lang->p('ACTIONS', 2); ?></button>
	</p>
</div>
<?php endif; ?>