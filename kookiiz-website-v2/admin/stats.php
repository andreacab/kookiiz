<?php
    /**********************************************************
    Title: Stats (ADMIN)
    Authors: Kookiiz Team
    Purpose: Display stats on platform usage
    ***********************************************************/
	
	/**********************************************************
	SET UP
	***********************************************************/
	
	//Dependencies
	require_once '../class/dblink.php';
	require_once '../class/globals.php';
	require_once '../class/user.php';
	
	//Init handlers
	$DB   = new DBLink('kookiiz');
    $User = new User($DB);
    
    //Allow execution from admins only
	if(!$User->isAdmin())
		die('Only admins can execute this script!');
	
	/**********************************************************
	SCRIPT
	***********************************************************/
    
    //Last members
    $request = 'SELECT name, UNIX_TIMESTAMP(user_date) AS date'
            . ' FROM users'
            . ' ORDER BY user_date DESC'
            . ' LIMIT 30';
    $stmt = $DB->query($request);
    $lastUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    //Last visits
    $request = 'SELECT name, UNIX_TIMESTAMP(last_visit) AS date'
            . ' FROM users'
            . ' ORDER BY last_visit DESC'
            . ' LIMIT 30';
    $stmt = $DB->query($request);
    $lastVisits = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    //Last recipes added to menus
    $request = 'SELECT users.name AS user_name, recipes.name AS recipe_name,'
                . ' UNIX_TIMESTAMP(menu_date) AS date'
            . ' FROM users'
                . ' LEFT JOIN users_menus USING(user_id)'
                . ' LEFT JOIN menus_recipes USING(menu_id)'
                . ' LEFT JOIN recipes USING(recipe_id)'
            . ' WHERE recipes.name IS NOT NULL'
            . ' ORDER BY menu_date DESC'
            . ' LIMIT 30';
    $stmt = $DB->query($request);
    $menuRecipes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
    //Last comments
    $request = 'SELECT users.name AS user_name, comment_text,'
                . ' UNIX_TIMESTAMP(comment_date) AS date, content_id'
            . ' FROM users'
                . ' NATURAL JOIN recipes_comments'
            . ' WHERE comment_id IS NOT NULL'
            . ' ORDER BY comment_date DESC LIMIT 30';
    $stmt = $DB->query($request);
    $lastComments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    //Added recipes
    $request = 'SELECT users.name AS user_name, COUNT(*) AS total'
            . ' FROM users'
                . ' LEFT JOIN recipes ON users.user_id = recipes.author_id'
            . ' WHERE recipe_id IS NOT NULL'
            . ' GROUP BY user_id ORDER BY total DESC LIMIT 30';
    $stmt = $DB->query($request);
    $addRecipes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    //Favorite recipes
    $request = 'SELECT users.name AS user_name, COUNT(*) AS total'
            . ' FROM users'
                . ' NATURAL JOIN users_recipes'
            . ' WHERE recipe_id IS NOT NULL'
            . ' GROUP BY user_id ORDER BY total DESC LIMIT 30';
    $stmt = $DB->query($request);
    $favRecipes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    /**********************************************************
	VIEW
	***********************************************************/
?>
<html lang="en">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <!-- Style sheets -->
        <link rel="stylesheet" href="<?php echo '/themes/', C::THEME, '/css/globals.css'; ?>" media="screen" type="text/css" />
        <!-- Page title -->
        <title>Stats</title>
    </head>
    <body>
        <div style="margin:10px;">
            <h4>Statistics</h4>
            <div>
                <h5>Last users</h5>
                <?php if(count($lastUsers)): ?>
                <ul>
                <?php foreach($lastUsers as $row): ?>
                <?php $date = date('d.m.Y', $row['date']); ?>
                    <li><?php echo "$date - {$row['name']}"; ?></li>
                <?php endforeach; ?>
                </ul>
                <?php else: ?>
                <p>none</p>
                <?php endif; ?>
            </div>
            <div>
                <h5>Last visits</h5>
                <?php if(count($lastVisits)): ?>
                <ul>
                <?php foreach($lastVisits as $row): ?>
                <?php $date = date('d.m.Y', $row['date']); ?>
                    <li><?php echo "$date - {$row['name']}"; ?></li>
                <?php endforeach; ?>
                </ul>
                <?php else: ?>
                <p>none</p>
                <?php endif; ?>
            </div>
            <div>
                <h5>Last menu recipes</h5>
                <?php if(count($menuRecipes)): ?>
                <ul>
                <?php foreach($menuRecipes as $row): ?>
                    <?php $date = date('d.m.Y', $row['date']); ?>
                    <li><?php echo "$date - {$row['user_name']} - {$row['recipe_name']}"; ?></li>
                <?php endforeach; ?>
                </ul>
                <?php else: ?>
                <p>none</p>
                <?php endif; ?>
            </div>
            <div>
                <h5>Last recipe comments</h5>
                <?php if(count($lastComments)): ?>
                <ul>
                <?php foreach($lastComments as $row): ?>
                    <?php $date = date('d.m.Y', $row['date']); ?>
                    <li><?php echo "$date - {$row['user_name']} - #{$row['content_id']} - {$row['comment_text']}"; ?></li>
                <?php endforeach; ?>
                </ul>
                <?php else: ?>
                <p>none</p>
                <?php endif; ?>
            </div>
            <div>
                <h5>Added recipes</h5>
                <?php if(count($addRecipes)): ?>
                <ul>
                <?php foreach($addRecipes as $row): ?>
                    <li><?php echo "{$row['total']} - {$row['user_name']}"; ?></li>
                <?php endforeach; ?>
                </ul>
                <?php else: ?>
                <p>none</p>
                <?php endif; ?>
            </div>
            <div>
                <h5>Favorite recipes</h5>
                <?php if(count($favRecipes)): ?>
                <ul>
                <?php foreach($favRecipes as $row): ?>
                    <li><?php echo "{$row['total']} - {$row['user_name']}"; ?></li>
                <?php endforeach; ?>
                </ul>
                <?php else: ?>
                <p>none</p>
                <?php endif; ?>
            </div>
        </div>
    </body>
</html>