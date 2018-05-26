<?php
    /**********************************************************
    Title: Recipes update (ADMIN)
    Authors: Kookiiz Team
    Purpose: Routine to update "healthy" and "veggie" criteria
    ***********************************************************/

	/**********************************************************
	SET UP
	***********************************************************/

    //Dependencies
	require_once '../class/dblink.php';
	require_once '../class/globals.php';
	require_once '../class/recipes_lib.php';
	require_once '../class/session.php';
	require_once '../class/user.php';
	
	//Init handlers
	$DB     = new DBLink('kookiiz');
    $User   = new User($DB);

    /**********************************************************
	SCRIPT
	***********************************************************/

    //Restrict use to admins
    if(!$User->isAdmin()) die('Only admins can run this script!');
	
	//List recipes
	$RecipesLib = new RecipesLib($DB, $User);
    $recipes_ids    = $RecipesLib->getList();
    $recipes        = $RecipesLib->load($recipes_ids, $mode = 'short');

    //Loop through recipes list
	$healthy = 0; $veggie = 0; $vegan = 0;
	$ids = array(); $names = array();
    $h_scores = array(); $v_scores = array();
    foreach($recipes as $index => $recipe)
    {
        //Current recipe params
        $id     = $recipe['id'];
        $name   = $recipe['name'];

        //Store recipe info
        $ids[$index]    = $id;
        $names[$index]  = $name;

        //Store healthy and veggie score for current recipe
        $h_scores[$index] = $RecipesLib->test_healthy($id, $update = true);
		$v_scores[$index] = $RecipesLib->test_veggie($id, $update = true);

        //Update healthy counter
        $healthy += $h_scores[$index] > C::RECIPE_HEALTHY_THRESHOLD ? 1 : 0;

        //Update vegan and veggie counters
		if($v_scores[$index] == 2)      $vegan++;
		else if($v_scores[$index] == 1) $veggie++;
    }
?>
<html lang="en">
	<head>
        <!-- Meta data -->
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">

        <!-- Style sheets -->
		<link rel="stylesheet" href="<?php echo '/themes/', C::THEME, '/css/globals.css'; ?>" media="screen" type="text/css" />

        <!-- Page title -->
		<title>Recipes update routine</title>
	</head>
	<body>
		<h5>SUMMARY</h5>
        <ul>
            <li>Recipes (total): <?php echo count($recipes); ?></li>
            <li>Healthy recipes (total): <?php echo $healthy; ?></li>
            <li>Veggie recipes (total): <?php echo $veggie; ?></li>
            <li>Veggie recipes (total): <?php echo $veggie; ?></li>
        </ul>
        <h5>DETAIL</h5>
        <p>Healthy score threshold: <?php C::p('RECIPE_HEALTHY_THRESHOLD'); ?></p>
        <ul>
        <?php
            foreach($ids as $index => $id)
            {
				$name       = $names[$index];
				$h_score    = $h_scores[$index];
				$v_score    = $v_scores[$index];
				echo "<li>",
                        "<span class='bold'>#$id - $name</span><br/>",
						"<span>Healthy score: $h_score</span></br>",
						"<span>Veggie score: $v_score</span></br>",
                    '</li>';
            }
        ?>
        </ul>
	</body>
</html>