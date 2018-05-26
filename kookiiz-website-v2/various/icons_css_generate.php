<?php
	header('Content-type: text/plain');

	$names = array(
					array('flange','chicken_liver','chicken_leg','chicken','calf_liver','beef_stew','beef','vienna_sausage','veal_kidneys','veal_cutlet','veal_chop','sausage','salami','veal_roast','ham','lean_veal','minced_meat'),
					array('pork_roast','pork _tenderloin','pork_steak','pork_chop','pork','pancetta','horse_steak'),
					array('anchovies','tuna_oil','tuna','trout','salmon_smoked','shrimp','scampi','sardine','sardines','salmon','saithe','herring','oysters','mussles','lobster','flounder','eel'),
					array('cod'),
					array('orange_juice','orange','melon','medlar','mango','lemon','kiwi','grenada','grape','cocconut','cherries','blackcurrant','blackberries','berries','peach','pear','raspberries'),
					array('banana','apricot','apple_red','apple_green','ananas','watermelon','tomato','strawberries'),
					array('dry_prunes','dry_apricots'),
					array('asparagus','artichoke','zucchini','yellow_pepper','chickpeas','squash2','squash','spinach','salsify','lettuce_butterhead','lettuce_iceberg','red_pepper','rating_chards','rampon','avocado','black_olive','carot'),
					array('radish','potato','pickle','chili','peas','onion','lettuce_red','lettuce','leek','kale','green_olive','green_beans','fennel','eggplant','broccoli','brussel_sprouts','celery'),
					array('ramps','corn'),
					array('lentils','soy_beans'),
					array('yogourt','milk','roquefort','quark_cream','parmesan','mayonnaise','gruyere','emmental','egg','cheese_cottage','chocolate','camembert','butter','brie'),
					array('rye_bread','white_bread','valaisan_rye_bread','ticino_bread','graham_bread','croissant'),
					array('peanuts','oatmeal','oat_bran','linseeds','hazlenuts','cashew_nuts','brazil_nuts','almonds','wheat_germs','sunflower_seeds','soybeans','soy_flour','seed_whole_wheat','pumpkin_seeds','pistachio'),
					array('macaroni','spaghetti','brown_rice','bran'),
					array('mushrooms','morel','pleurotes','boletus'),
					array('yeast','tofu','salt','ketchup','jam','water','soft_drink','potato_chips','olive_oil','parsley')
				);
	
	$lines = array();
	for($i = 0, $imax = count($names); $i < $imax; $i++)
	{
		for($j = 0, $jmax = count($names[$i]); $j < $jmax; $j++)
		{
			$name = $names[$i][$j];
			$pos_x = $j * 15;
			$pos_y = $i * 15;
			$lines[] = ".ingredient_icon.$name {background-position:" . ($pos_x ? "-" : "") . $pos_x . "px " . ($pos_y ? "-" : "") . $pos_y . "px;}\n";
		}
	}
	
	sort($lines);
	foreach($lines as $text) echo $text;
	
?>