<?php
    /**********************************************************
	Title: Snoop (ADMIN)
	Authors: Kookiiz Team
	Purpose: Provide a form to edit snoop recipes
	***********************************************************/

    /**********************************************************
	SET-UP
	***********************************************************/

    //Dependencies
    require_once '../class/dblink.php';
    require_once '../class/globals.php';
    require_once '../class/lang_db.php';
    require_once '../class/recipes_lib.php';
    require_once '../class/request.php';
    require_once '../class/session.php';
    require_once '../class/units_lib.php';
    require_once '../class/user.php';
    
    //Start session
    Session::start();

    //Init handlers
    $DB         = new DBLink('kookiiz');
    $Lang       = LangDB::getHandler('fr');
    $Request    = new RequestHandler();
    $User       = new User($DB);
    $RecipesLib = new RecipesLib($DB, $User);
    
    //Load parameters
    $data = json_decode($Request->get('recipe'), true);
    
    //Check that user is an admin
    if(!$User->isAdmin())
        die('Only admins can use the Snoop!');
    
    /**********************************************************
	SCRIPT
	***********************************************************/

    //Recipe container
    $recipe = array(
        'name'          => ucfirst(strtolower(str_replace('´', "'", $data['name']))),
        'description'   => array(),
        'ingredients'   => array(),
        'guests'        => (int)$data['guests'],
        'preparation'   => (int)$data['prep'],
        'cooking'       => (int)$data['cook'],
        'category'      => (int)$data['category'],
        'level'         => (int)$data['level']
    );
    

    //Lang
    $lang = $data['lang'];
    //User ID
    $user_id = (int)$data['user_id'];

    //Description
    foreach($data['description'] as $step)
    {
        $step = str_replace('´', "'", $step);
        $step = str_replace("\n", ' ', $step);
        $step = trim($step);
        if($step) 
            $recipe['description'][] = $step;
    }

    //Ingredients
    $saltandpepper = -1;
    foreach($data['ingredients'] as $index => $ing)
    {
        //Array for ingredient matches
        $recipe['ingredients'][$index] = array();
        
        //Format ingredient name
        $ing = strtolower($ing);
        $ing = str_replace("d'", '', $ing);
		$ing = str_replace("d´", '', $ing);
        $ing = str_replace("l'", '', $ing);
        $ing = str_replace("l´", '', $ing);
        //Remove "s" and "x" at the end of words
        $ing_split = explode(' ', $ing);
		foreach($ing_split as $key => $piece)
		{
			if(substr($piece, -1) == 's' || substr($piece, -1) == 'x')
			{
				switch($lang)
				{
					case 'en':
						if(substr($piece, -2) == 'e')
							$ing_split[$key] = substr($piece, 0, -2);
						else
							$ing_split[$key] = substr($piece, 0, -1);
						break;
					case 'fr':
						$ing_split[$key] = substr($piece, 0, -1);
						break;
				}
			}			
		}
            
        //Extract main term
        $main_term = '';
        switch($lang)
        {
            case 'en':
                $main_term = $ing_split[count($ing_split) - 1];
                break;
            case 'fr':
                $main_term = $ing_split[0];
                break;
        }

        //Special case for "sel, poivre"
        switch($lang)
        {
            case 'en':
                if(strpos($ing, 'salt') !== false
                    && (strpos($ing, 'pepper') !== false || strpos($ing, 'black') !== false))
                {
                    $saltandpepper = $index;
                    continue;
                }
                break;
            case 'fr':
                if(strpos($ing, 'sel') !== false 
                    && strpos($ing, 'poivre') !== false)
                {
                    $saltandpepper = $index;
                    continue;
                }
                break;
        }
        
        //Quantity and unit
        $qty  = (float)$data['quantities'][$index];
        $unit = (int)$data['units'][$index];

        //Try to retrieve matching ingredient in database with MATCH function
		$string = implode($ing_split, "* ");
        $request = 'SELECT ingredient_id, ingredient_name_fr, ingredient_wpu, ingredient_price,'
                    . " MATCH(ingredient_name_$lang, ingredient_tags_$lang) AGAINST(? IN BOOLEAN MODE) AS ing_score,"
                    . " LENGTH(ingredient_name_$lang) AS ing_size"
                . ' FROM ingredients'
                . " WHERE MATCH(ingredient_name_$lang, ingredient_tags_$lang)"
                    . ' AGAINST(? IN BOOLEAN MODE)'
                . ' ORDER BY ing_score DESC, ing_size';
        $stmt = $DB->query($request, array($string, $string));
        $matches = $stmt->fetchAll(PDO::FETCH_ASSOC);

        //Try with main term
        $request = "SELECT ingredient_id, ingredient_name_fr, ingredient_wpu, ingredient_price,"
                    . " MATCH(ingredient_name_$lang, ingredient_tags_$lang) AGAINST(? IN BOOLEAN MODE) AS ing_score,"
                    . " LENGTH(ingredient_name_$lang) AS ing_size"
                . ' FROM ingredients'
                . " WHERE MATCH(ingredient_name_$lang, ingredient_tags_$lang)"
                    . ' AGAINST(? IN BOOLEAN MODE)'
                . ' ORDER BY ing_score DESC, ing_size';
        $stmt = $DB->query($request, array("$main_term%", "$main_term%"));
        $matches = array_merge($matches, $stmt->fetchAll(PDO::FETCH_ASSOC));

        //Try to retrieve matching ingredient with LIKE function (for words smaller than 4 chars)
        $request = "SELECT ingredient_id, ingredient_name_fr, ingredient_wpu, ingredient_price"
                . " FROM ingredients WHERE ingredient_name_$lang LIKE ?";
        $stmt = $DB->query($request, array("$main_term%"));
        $matches = array_merge($matches, $stmt->fetchAll(PDO::FETCH_ASSOC));

        //Case where matches were found
        if(count($matches))
        {
            $matches_ids = array();
            foreach($matches as $ing_row)
            {
                $id    = (int)$ing_row['ingredient_id'];
                $name  = $ing_row['ingredient_name_fr'];
                $price = (float)$ing_row['ingredient_price'];
                $wpu   = (int)$ing_row['ingredient_wpu'];
                
                //Missing quantity
                if(!$qty)
                {
                    $qty  = 1;
                    $unit = $wpu ? 10 : 9;
                }
                
                //Store current match
                if(!in_array($id, $matches_ids))
                {
                    //Store ingredient
                    $recipe['ingredients'][$index][] = array(
                        'id'        => $id, 
                        'name'      => $name, 
                        'quantity'  => $qty,
                        'unit'      => $unit, 
                        'price'     => $price, 
                        'wpu'       => $wpu
                    );
                    $matches_ids[] = $id;
                }
            }
        }
        //Case were no matches were found
        else
        {
            //Store ingredient
            $recipe['ingredients'][$index][] = array(
                'id'        => $id,
                'quantity'  => $qty,
                'unit'      => $unit,
                'name'      => implode(' ', $ing_split) . ' ???', 
                'price'     => 0, 
                'wpu'       => 0
            );
        }
    }

    //Add salt and pepper to ingredients
    if($saltandpepper >= 0)
    {
        array_splice($recipe['ingredients'], $saltandpepper, 1);
        $recipe['ingredients'][] = array(array('id' => 126, 'name' => 'sel', 'quantity' => 1, 'unit' => 9, 'wpu' => 0, 'price' => 0));
        $recipe['ingredients'][] = array(array('id' => 1326, 'name' => 'poivre', 'quantity' => 1, 'unit' => 9, 'wpu' => 0, 'price' => 0));
    }
    
    //Check for recipes with similar names
    $similar_recipes = array();
    $similar_ids = $RecipesLib->title_check($recipe['name']);
    if(count($similar_ids)) 
        $similar_recipes = $RecipesLib->load($similar_ids, $mode = 'short');
    
    /**********************************************************
	DOM GENERATION
	***********************************************************/
?>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <link rel="stylesheet" href="<?php echo '/themes/', C::THEME, '/css/main.css'; ?>" media="screen" type="text/css" />
    </head>
    <body>
        <form method="post" action="http://www.kookiiz.com/admin/snoop_confirm.php">
            <input type="hidden" name="user_id" value="<?php echo $user_id; ?>" />
            <input type="hidden" name="ing_count" value="<?php echo count($recipe['ingredients']); ?>" />
            <input type="hidden" name="steps" value="<?php echo count($recipe['description']); ?>" />
            <h4>
                <input type="text" name="name" value="<?php echo $recipe['name']; ?>" style="width:90%;"/><br/>
            </h4>
            <p class="error bold">
            <?php
                if(count($similar_recipes))
                {
                    echo '<span>Titres similaires: ';
                    foreach($similar_recipes as $index => $recipe_obj)
                    {
                        if($index) echo ', ';
                        echo $recipe_obj['name'];
                    }
                    echo '</span>';
                }
            ?>
            </p>
            <ul>
                <li>
                    <span style="display:inline-block;width:100px;">Langue: </span>
                    <input type="text" name="lang" value="<?php echo $lang; ?>"/>
                </li>
                <li>
                    <span style="display:inline-block;width:100px;">Personnes: </span>
                    <input type="text" name="guests" value="<?php echo $recipe['guests']; ?>"/>
                </li>
                <li>
                    <span style="display:inline-block;width:100px;">Préparation: </span>
                    <input type="text" name="preparation" value="<?php echo $recipe['preparation']; ?>"/> minutes
                </li>
                <li>
                    <span style="display:inline-block;width:100px;">Cuisson: </span>
                    <input type="text" name="cooking" value="<?php echo $recipe['cooking']; ?>"/> minutes
                </li>
                <li>
                    <span style="display:inline-block;width:100px;">Difficulté: </span>
                    <select name="level">
                    <?php
                        $levels = $Lang->get('RECIPES_LEVELS');
                        foreach($levels as $id => $name)
                        {
                            echo '<option value="', $id, '"', ($id == $recipe['level'] ? ' selected="selected">' : '>'), $name, '</option>';
                        }
                    ?>
                    </select>
                    <span class="error bold"><-- Vérifier la difficulté !!!</span>
                </li>
                <li>
                    <span style="display:inline-block;width:100px;">Catégorie: </span>
                    <select name="category">
                    <?php
                        $categories = $Lang->get('RECIPES_CATEGORIES');
                        asort($categories);
                        foreach($categories as $id => $name)
                        {
                            if(!$id) continue;
                            echo '<option value="', $id, '"', ($id == $recipe['category'] ? ' selected="selected">' : '>'), $name, '</option>';
                        }
                    ?>
                    </select>
                    <span class="error bold"><-- Vérifier la catégorie !!!</span>
                </li>
                <li>
                    <span style="display:inline-block;width:100px;">Origine: </span>
                    <select name="origin">
                    <?php
                        $origins = $Lang->get('RECIPES_ORIGINS');
                        asort($origins);
                        foreach($origins as $id => $name)
                        {
                            if(!$id) continue;
                            echo '<option value="', $id, '">', $name, '</options>';
                        }
                    ?>
                    </select>
                    <span class="error bold"><-- Spécifier l'origine !!!</span>
                </li>
            </ul>
            <p>
                <span class="bold">Pour supprimer une étape de préparation il suffit d'effacer le texte dans la textarea !</span><br/>
                <?php
                    foreach($recipe['description'] as $index => $step)
                    {
                        echo '<textarea name="step_', $index, '" cols="50" rows="2">', $step, '</textarea><br/>';
                    }
                ?>
            </p>
            <p class="bold">Ingrédients</p>
            <p class="error bold">Les éléments en rouge n'ont pas été reconnus.</p>
            <p>Si un ingrédient est présent plusieurs fois, c'est la dernière occurrence qui écrasera les autres!</p>
            <table>
                <thead>
                    <tr>
                        <th>Quantité</th>
                        <th>Unité</th>
                        <th>Nom</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                    $units = UnitsLib::getAll($data['unitsys']);
                    foreach($recipe['ingredients'] as $index => $list)
                    {
                        $qty  = $list[0]['quantity'];
                        $unit = $list[0]['unit'];
                        
                        //Quantity
                        $style = '';
                        if($unit < 0) 
                            $style = "border:2px solid Red";
                        
                        echo '<tr>',
                                '<td>',
                                    '<input type="text" name="input_qty_', $index, '" value="', $qty, '" style="', $style, '"/>',
                                '</td>',
                                '<td>',
                                    '<select name="select_unit_', $index, '" style="', $style, '">';
                                    foreach($units as $Unit)
                                    {
                                        $unit_id = $Unit->getID();
                                        $unit_name = $Lang->get('UNITS_NAMES', $unit_id);
                                        echo '<option value="', $unit_id, '"', ($unit_id == $unit ? ' selected="selected">' : '>'), $unit_name, '</option>';
                                    }
                        echo        '</select>',
                                '</td>';
                        
                        //Name
                        $style = '';
                        if($list[0]['id'] == 0) 
                            $style = "border:2px solid Red";
                        
                        echo    '<td>',
                                    '<select name="select_name_', $index, '" style="', $style, '">';
                                    foreach($list as $ing_index => $ing)
                                    {
                                        $id = $ing['id'];
                                        $name = $ing['name'];
                                        echo '<option value="', $id, '"', ($ing_index ? '>' : ' selected="selected">'), $name, '</option>';
                                    }
                        echo        '</select>',
                                '</td>',
                             '</tr>';
                    }
                ?>
                </tbody>
            </table>
            <p class="center">
                <button type="submit" class="button_80">Valider</button>
            </p>
        </form>
    </body>
</html>