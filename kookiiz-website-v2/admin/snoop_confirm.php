<?php
    /**********************************************************
	Title: Snoop confirm (ADMIN)
	Authors: Kookiiz Team
	Purpose: Store final Snoop recipes in database
	***********************************************************/

    /**********************************************************
	SET-UP
	***********************************************************/

    //Dependencies
    require_once '../class/dblink.php';
    require_once '../class/globals.php';
    require_once '../class/events_lib.php';
    require_once '../class/recipes_lib.php';
    require_once '../class/request.php';
    require_once '../class/session.php';
    require_once '../class/units_lib.php';
    require_once '../class/user.php';
    
    //Start session
    Session::start();

    //Init handlers
    $DB         = new DBLink('kookiiz');
    $Request    = new RequestHandler();
    $User       = new User($DB);
    $EventsLib  = new EventsLib($DB, $User);
    $RecipesLib = new RecipesLib($DB, $User);
    
    //Load parameters
    $lang       = $Request->get('lang');
    $user_id    = (int)$Request->get('user_id');
    
    //Check that user is an admin
    if(!$User->isAdmin())
        die('Only admins can use the Snoop!');
    
    /**********************************************************
	SCRIPT
	***********************************************************/
    
    /* BUILD */

    //Build recipe object
    $recipe = array(
        'name'          => $Request->get('name'),
        'description'   => '',
        'ingredients'   => array(),
        'guests'        => (int)$Request->get('guests'),
        'preparation'   => (int)$Request->get('preparation'),
        'cooking'       => (int)$Request->get('cooking'),
        'origin'        => (int)$Request->get('origin'),
        'level'         => (int)$Request->get('level'),
        'category'      => (int)$Request->get('category')
    );

    //User/partner ID    
    $request = 'SELECT partner_id FROM users WHERE user_id = ?';
    $stmt = $DB->query($request, array($user_id));
    $data = $stmt->fetch();
    if($data)
        $partner_id = (int)$data['partner_id'];
    else 
        $partner_id = 0;

    //Description
    $steps_counter = 1; $steps_count = (int)$Request->get('steps');
    for($i = 0; $i < $steps_count; $i++)
    {
        $step = $Request->get("step_$i");
        if($step != '')
        {
            $recipe['description'] .= $steps_counter . ". $step\n\n";
            $steps_counter++;
        }
    }

    //Ingredients
    $ingredients_count = (int)$Request->get('ing_count');
    for($i = 0; $i < $ingredients_count; $i++)
    {
        $recipe['ingredients'][] = array(
            'i' => (int)$Request->get("select_name_$i"), 
            'q' => (float)$Request->get("input_qty_$i"), 
            'u' => (int)$Request->get("select_unit_$i")
        );
    }    

    //Compute price
    $recipe['price'] = 0;
    foreach($recipe['ingredients'] as $ing)
    {
        $id   = $ing['i'];
        $qty  = $ing['q'];
        $unit = $ing['u'];
        
        //Convert quantity to grams
        if($unit != UNIT_GRAMS)
        {
            $request = 'SELECT ingredient_wpu, ingredient_price FROM ingredients WHERE ingredient_id = ?';
            $stmt = $DB->query($request, array($id));
            $data = $stmt->fetch();
            if($data)
            {
                $wpu    = (int)$data['ingredient_wpu'];
                $price  = (float)$data['ingredient_price'];
                if($unit != UNIT_NONE)
                {
                    $Unit = UnitsLib::get($unit);
                    $qty = $qty * $Unit->getValue();
                }
                else 
                    $qty = $qty * $wpu;
            }
            else continue;
        }
        //Compute price
        $recipe['price'] += $price * $qty / 100;
    }
    $recipe['price'] = round($recipe['price'] / $recipe['guests']);

    /* SAVE */
    
    //Insert recipe in database
    $recipe_id = $RecipesLib->insert($recipe, $public = 1, $lang, $partner_id);

    //Register event
	if($recipe_id)
        $EventsLib->register(C::PARTNER_DEFAULT, EventsLib::TYPE_ADDRECIPE, $public = true, $recipe_id, $lang);
?>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <link rel="stylesheet" href="<?php echo '/themes/', C::THEME, '/css/main.css'; ?>" media="screen" type="text/css" />
    </head>
    <body>
        <p><?php echo "JSON de la recette importée: " . json_encode($recipe); ?></p>
    </body>
</html>