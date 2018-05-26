<?php
	/**********************************************************
    Title: Menu
    Authors: Kookiiz Team
    Purpose: Manage menu content
    ***********************************************************/

    //Dependencies
    require_once '../class/dblink.php';
    require_once '../class/globals.php';
    require_once '../class/ingredient_qty.php';
    require_once '../class/quickmeals_lib.php';
    require_once '../class/recipes_lib.php';
    require_once '../class/user.php';

    //Represents a user menu
	class Menu
	{
        /**********************************************************
        PROPERTIES
        ***********************************************************/

        private $DB;        //database connection
		private $User;      //user the menu belongs to
        
		private $id;		//unique ID of the menu
		private $date;		//reference date of the menu (date of the day #14)
		private	$content;	//array containing the menu content

        private $loaded = false;    //Has menu content been loaded from database?

        /**********************************************************
        CONSTRUCTOR
        ***********************************************************/

        /**
         * Class constructor
         * @param DBLink $DB open database connection
         * @param User $User menu user
         */
		public function __construct(DBLink &$DB, User &$User)
		{
			//Define user ID and reference date
            $this->DB   = $DB;
			$this->User = $User;
            $this->date = date('Y-m-d');
			
			//Init menu content
			$this->setup();
		}

        /**********************************************************
        CHECK
        ***********************************************************/
        
        /**
         * Check menu content for non-valid recipes, etc.
         */
        private function check()
        {
            //Init libraries
            $RecipesLib    = new RecipesLib($this->DB, $this->User);
            $QuickmealsLib = new QuickmealsLib($this->DB, $this->User);

            //Get list of valid recipes and quick meals
            $valid_recipes    = $RecipesLib->exist($this->getRecipes());
            $valid_quickmeals = $QuickmealsLib->exist($this->getQuickmeals());

            //Loop through menu days and remove non-valid recipes and quick meals
            foreach($this->content as $day)
            {
                //Quick meals
                $day['quickmeals'] = array_intersect($day['quickmeals'], $valid_quickmeals);

                //Recipes
                foreach($day['recipes'] as $pos => $recipe_id)
                {
                    if(!in_array($recipe_id, $valid_recipes))
                        $day['recipes'][$pos] = 0;
                }
            }
        }

		/**********************************************************
        EXPORT
        ***********************************************************/

        /**
         * Export compact menu data (without redundant information)
         * @return Array compact menu content ready to be sent to clien
         */
		public function export()
		{
            if(!$this->loaded) $this->load();

            //Check loaded content
            $this->check();

            //Loop through menu content
			$menu = array();
			foreach($this->content as $index => $day)
			{
				$menu[$index] = array();

				//Quick meals
				$quickmeal_found = false;
				if(count($day['quickmeals']))
				{
					$menu[$index]['q'] = $day['quickmeals'];
					$quickmeal_found = true;
				}
				else 
                    $menu[$index]['q'] = 0;

				//Recipes
				$recipe_found = false;
				foreach($day['recipes']['ids'] as $recipe_id)
				{
					if($recipe_id)
					{
						$menu[$index]['r'] = array();
						$menu[$index]['r']['i'] = $day['recipes']['ids'];
						$menu[$index]['r']['g'] = $day['recipes']['guests'];
						$recipe_found = true;
						break;
					}
				}
				if(!$recipe_found) 
                    $menu[$index]['r'] = 0;

				//Shopping
				$shopping_status = $day['shopping']['status'];
				if($shopping_status)
				{
					$menu[$index]['s'] = array();
					$menu[$index]['s']['s'] = $shopping_status;

					//Ingredients
					$menu[$index]['s']['ing'] = array();
                    foreach($day['shopping']['ingredients'] as $ing)
                        $menu[$index]['s']['ing'][] = $ing->export();

					//Modifications
					$menu[$index]['s']['m'] = array();
                    foreach($day['shopping']['modifications'] as $ing)
                        $menu[$index]['s']['m'][] = $ing->export();

					//Items
					$menu[$index]['s']['it'] = array();
					foreach($day['shopping']['items'] as $item)
					{
						$menu[$index]['s']['it'][] = array(
                            'i' => $item['id'],
                            't' => $item['text'],
                            'c' => $item['category']
                        );
					}

					//Shared
					$menu[$index]['s']['sh'] = $day['shopping']['shared'];
				}
				else 
                    $menu[$index]['s'] = 0;

				if(!$quickmeal_found && !$recipe_found && !$shopping_status)
                    $menu[$index] = 0;
			}
			return $menu;
		}
        
        /**********************************************************
        GET
        ***********************************************************/

        /**
         * Return current menu date
         * @return String reference date of current menu as "yyyy-mm-dd"
         */
		public function getDate()
		{
            if(!$this->loaded) $this->load();
			return $this->date;
		}

        /**
         * Return current menu ID
         * @return Int unique ID of the current menu
         */
		public function getID()
		{
            if(!$this->loaded) $this->load();
			return $this->id;
		}

        /**
         * Return IDs of all quick meals in the menu
         * @return Array list of quick meal IDs
         */
        public function getQuickmeals()
        {
            if(!$this->loaded) $this->load();

            $quickmeals = array();
            foreach($this->content as $day)
                $quickmeals = array_merge($quickmeals, $day['quickmeals']);
            return array_values(array_unique($quickmeals));
        }

        /**
         * Return IDs of all recipes in the menu
         * @return Array list of recipe IDs the menu contains
         */
		public function getRecipes()
		{
            if(!$this->loaded) $this->load();

			$recipes_ids = array();
			foreach($this->content as $day)
			{
				$recipes = $day['recipes']['ids'];
				foreach($recipes as $recipe_id)
				{
					if($recipe_id) 
                        $recipes_ids[] = $recipe_id;
				}
			}
			return array_values(array_unique($recipes_ids));
		}
        
        /**
         * Return shopping data for a given day
         * @param Int $day index of the day (-MENU_DAYS_PAST <= day < +MENU_DAYS_FUTURE)
         * @return Array shopping data
         */
        public function getShopping($day)
        {
            if(!$this->loaded) $this->load();
            if($day < -C::MENU_DAYS_PAST)
                $day = -C::MENU_DAYS_PAST;
            else if($day > C::MENU_DAYS_FUTURE - 1)
                $day = C::MENU_DAYS_FUTURE - 1;
            return $this->content[C::MENU_DAYS_PAST + $day]['shopping'];
        }
        
        /**
         * Return list of days for which shopping is planned
         * @return Array list of day indexes
         */
        public function getShoppingDays()
        {
            $days = array();
            foreach($this->content as $day => $data)
            {
                if($data['shopping']['status'])
                    $days[] = $day - C::MENU_DAYS_PAST;
            }
            return $days;
        }

        /**********************************************************
        IMPORT
        ***********************************************************/

        /**
         * Import provided menu data with security methods
         * @param Array $menu content of the menu in compact format
         */
		public function import($menu)
		{
            //Loop through menu data
			foreach($menu as $index => $day)
			{
				//Check if there is some content for current day
				if($day)
				{
					//Quick meals
					$quickmeals = $day['q'];
					if($quickmeals)
					{
						//Note: quick meals count per day is limited and additional meals are ignored !
						foreach($quickmeals as $pos => $quickmeal_id)
						{
                            if($pos > C::MENU_QUICKMEALS_MAX) break;
							$this->content[$index]['quickmeals'][] = (int)$quickmeal_id;
						}
					}

					//Recipes
					$recipes = $day['r'];
					if($recipes)
					{
						foreach($recipes['i'] as $pos => $recipe_id)
						{
                            if($pos > C::MENU_MEALS_COUNT) break;
							$this->content[$index]['recipes']['ids'][$pos]    = (int)$recipe_id;
							$this->content[$index]['recipes']['guests'][$pos] = (int)$recipes['g'][$pos];
						}
					}

					//Shopping
					$shopping = $day['s'];
					if($shopping)
					{
						$status = (int)$shopping['s'];
						$this->content[$index]['shopping']['status'] = $status;

						if($status)
						{
							//Ingredients
							$this->content[$index]['shopping']['ingredients'] = array();
                            foreach($shopping['ing'] as $ing)
                            {
                                $id   = (int)$ing['i'];
                                $qty  = (float)$ing['q'];
                                $unit = (int)$ing['u'];
                                $this->content[$index]['shopping']['ingredients'][] = new IngredientQty($id, $qty, $unit);
                            }

							//Modifications
							$this->content[$index]['shopping']['modifications'] = array();
                            foreach($shopping['m'] as $ing)
                            {
                                $id   = (int)$ing['i'];
                                $qty  = (float)$ing['q'];
                                $unit = (int)$ing['u'];
                                $this->content[$index]['shopping']['modifications'][] = new IngredientQty($id, $qty, $unit);
                            }

							//Items
                            $this->content[$index]['shopping']['items'] = array();
							foreach($shopping['it'] as $item)
							{
								$this->content[$index]['shopping']['items'][] = array(
                                    'id'        => (int)$item['i'],
                                    'text'      => $item['t'],
                                    'category'  => (int)$item['c']
                                );
							}
						}
					}
				}
			}

            //Set menu as "loaded"
            $this->loaded = true;

            //Check imported content
            $this->check();

            //Save imported menu content
            $this->save();
		}
		
		/**********************************************************
        LOAD
        ***********************************************************/

        /**
         * Load menu content from database
         */
		private function load()
		{
			//Set-up empty menu structure
			$this->setup();

            //Abort if user is not logged
            if(!$this->User->isLogged()) return;
		
			//Main parameters
			$request = 'SELECT menu_id AS id, UNIX_TIMESTAMP(menu_date) AS date'
					. ' FROM users_menus WHERE user_id = ?';
			$stmt = $this->DB->query($request, array($this->User->getID()));
            $data = $stmt->fetch();
			if($data)
			{
				$this->id   = (int)$data['id'];
				$this->date = date('Y-m-d', (int)$data['date']);
			}
			else 
                //Stop here if no menu was retrieved
                return;	
			
			//Quick meals
			$request = 'SELECT menu_day, quickmeal_id'
					. ' FROM menus_quickmeals WHERE menu_id = ?';
			$stmt = $this->DB->query($request, array($this->id));
			while($quickmeal = $stmt->fetch())
			{
				$day = (int)$quickmeal['menu_day'];
				$this->content[$day]['quickmeals'][] = (int)$quickmeal['quickmeal_id'];
			}
		
			//Recipes
			$request = 'SELECT menu_day, pos, recipe_id, guests'
					. ' FROM menus_recipes WHERE menu_id = ?';
			$stmt = $this->DB->query($request, array($this->id));
			while($recipe = $stmt->fetch())
			{
				$day = (int)$recipe['menu_day'];
				$pos = (int)$recipe['pos'];
				$this->content[$day]['recipes']['ids'][$pos]    = (int)$recipe['recipe_id'];
				$this->content[$day]['recipes']['guests'][$pos] = (int)$recipe['guests'];
			}
			
			//Shopping status
			$request = 'SELECT menu_day, status'
					. ' FROM menus_shopping'
					. ' WHERE menu_id = ?'
					. ' GROUP BY menu_day';
			$stmt = $this->DB->query($request, array($this->id));
			while($shopping = $stmt->fetch())
			{	
				$day = (int)$shopping['menu_day'];
				$this->content[$day]['shopping']['status'] = (int)$shopping['status'];
			}
			
			//Ingredients
			$request = 'SELECT menu_day,'
                        . ' GROUP_CONCAT(ing_id) AS ids,'
                        . ' GROUP_CONCAT(quantity) AS quantities,'
                        . ' GROUP_CONCAT(unit) AS units'
					. ' FROM shopping_ingredients'
					. ' WHERE menu_id = ?'
					. ' GROUP BY menu_day';
			$stmt = $this->DB->query($request, array($this->id));
			while($shopping = $stmt->fetch())
			{
				$day      = (int)$shopping['menu_day'];
				$ing_ids  = explode(',', $shopping['ids']);
				$ing_qty  = explode(',', $shopping['quantities']);
				$ing_unit = explode(',', $shopping['units']);
				for($j = 0, $jmax = count($ing_ids); $j < $jmax; $j++)
				{
                    $id   = (int)$ing_ids[$j];
                    $qty  = (float)$ing_qty[$j];
                    $unit = (int)$ing_unit[$j];
					$this->content[$day]['shopping']['ingredients'][] = new IngredientQty($id, $qty, $unit);
				}
			}
			
			//Items
			$request = 'SELECT menu_day,'
                        . ' GROUP_CONCAT(item_id) AS ids,'
                        . ' GROUP_CONCAT(item_text) AS texts,'
                        . ' GROUP_CONCAT(item_category) AS categories'
					. ' FROM shopping_items'
					. ' WHERE menu_id = ?'
					. ' GROUP BY menu_day';
			$stmt = $this->DB->query($request, array($this->id));
			while($shopping = $stmt->fetch())
			{
				$day       = (int)$shopping['menu_day'];
				$item_ids  = explode(',', $shopping['ids']);
				$item_text = explode(',', $shopping['texts']);
				$item_cat  = explode(',', $shopping['categories']);
				for($j = 0, $jmax = count($item_ids); $j < $jmax; $j++)
				{
					$this->content[$day]['shopping']['items'][] = array(
                        'id'        => (int)$item_ids[$j],
                        'text'      => $item_text[$j],
                        'category'  => (int)$item_cat[$j]
                    );
				}
			}
			
			//Modifications
			$request = 'SELECT menu_day,'
                        . ' GROUP_CONCAT(ing_id) AS ids,'
                        . ' GROUP_CONCAT(quantity) AS quantities,'
                        . ' GROUP_CONCAT(unit) AS units'
					. ' FROM shopping_modifications'
					. ' WHERE menu_id = ?'
					. ' GROUP BY menu_day';
			$stmt = $this->DB->query($request, array($this->id));
			while($shopping = $stmt->fetch())
			{
				$day      = (int)$shopping['menu_day'];
				$mod_ids  = explode(',', $shopping['ids']);
				$mod_qty  = explode(',', $shopping['quantities']);
				$mod_unit = explode(',', $shopping['units']);
				for($j = 0, $jmax = count($mod_ids); $j < $jmax; $j++)
				{
					$id   = (int)$mod_ids[$j];
                    $qty  = (float)$mod_qty[$j];
                    $unit = (int)$mod_unit[$j];
					$this->content[$day]['shopping']['modifications'][] = new IngredientQty($id, $qty, $unit);
				}
			}
			
			//Shared
			$request = 'SELECT GROUP_CONCAT(friend_id) AS friends_ids,'
                        . ' (DATEDIFF(shopping_date, ?) + ' . C::MENU_DAYS_PAST . ') AS menu_day'
					. ' FROM shared_shopping WHERE menu_id = ?'
					. ' GROUP BY menu_day';
			$stmt = $this->DB->query($request, array($this->date, $this->id));
			while($shared = $stmt->fetch())
			{
				$day = (int)$shared['menu_day'];
				$ids = explode(',', $shared['friends_ids']);
				$this->content[$day]['shopping']['shared'] = $ids;
			}

            //Account for menu loading
            $this->loaded = true;
		}

        /**********************************************************
        SAVE
        ***********************************************************/
        
        /**
         * Save current menu content in database
         */
		private function save()
		{
            //Abort if user is not logged
            if(!$this->User->isLogged()) return;
            
			//Retrieve menu ID
			$request = 'SELECT menu_id FROM users_menus WHERE user_id = ?';
			$stmt = $this->DB->query($request, array($this->User->getID()));
            $data = $stmt->fetch();
			//A menu already exists
			if($data)
				$this->id = (int)$data['menu_id'];
			//Create a new menu ID
			else
			{
				$request = 'INSERT INTO users_menus (user_id) VALUES (?)';
				$this->DB->query($request, array($this->User->getID()));
				$this->id = $this->DB->insertID();
			}
			
			//Update menu date and timestamp
			$request = 'UPDATE users_menus'
                    . ' SET menu_date = :date'
					. ' WHERE user_id = :user_id AND menu_id = :id';
			$this->DB->query($request, array(
                ':date'     => $this->date,
                ':user_id'  => $this->User->getID(),
                ':id'       => $this->id
            ));
		
			//Delete all data with current menu ID
			$request = 'DELETE FROM menus_quickmeals WHERE menu_id = ?';
			$this->DB->query($request, array($this->id));
			$request = 'DELETE FROM menus_recipes WHERE menu_id = ?';
			$this->DB->query($request, array($this->id));
			$request = 'DELETE FROM menus_shopping WHERE menu_id = ?';
			$this->DB->query($request, array($this->id));
			$request = 'DELETE FROM shopping_ingredients WHERE menu_id = ?';
			$this->DB->query($request, array($this->id));
			$request = 'DELETE FROM shopping_items WHERE menu_id = ?';
			$this->DB->query($request, array($this->id));
			$request = 'DELETE FROM shopping_modifications WHERE menu_id = ?';
			$this->DB->query($request, array($this->id));
			//Delete only shared shopping list that are older than 2 weeks
			$request = 'DELETE FROM shared_shopping WHERE menu_id = ?'
					. ' AND DATEDIFF(shopping_date, CURDATE()) < -' . C::MENU_DAYS_PAST;
			$this->DB->query($request, array($this->id));
			
			//Create requests to insert menu content in database
			$quickmeals_request = 'INSERT INTO menus_quickmeals (menu_id, menu_day, quickmeal_id) VALUES (?, ?, ?)';
			$recipes_request    = 'INSERT INTO menus_recipes (menu_id, menu_day, pos, recipe_id, guests) VALUES (?, ?, ?, ?, ?)';
			$shopping_request   = 'INSERT INTO menus_shopping (menu_id, menu_day, status) VALUES (?, ?, ?)';
			$ing_request        = 'INSERT INTO shopping_ingredients (menu_id, menu_day, ing_id, quantity, unit) VALUES (?, ?, ?, ?, ?)';
			$items_request      = 'INSERT INTO shopping_items (menu_id, menu_day, item_id, item_text, item_category) VALUES (?, ?, ?, ?, ?)';
			$modif_request      = 'INSERT INTO shopping_modifications (menu_id, menu_day, ing_id, quantity, unit) VALUES (?, ?, ?, ?, ?)';
			
			//Loop through the menu content
			$quickmeals_params  = array();
            $recipes_params     = array();
            $shopping_params    = array();
            $shared_params      = array();
            $ing_params         = array();
            $items_params       = array();
            $modif_params       = array();
			foreach($this->content as $day_index => $day)
			{
				//Quick meals
				$quickmeals = $day['quickmeals'];
				foreach($quickmeals as $quickmeal_id)
				{
                    $quickmeals_params[] = array(
                        $this->id,
                        $day_index,
                        $quickmeal_id
                    );
				}
			
				//Recipes
				$recipes = $day['recipes'];
				foreach($recipes['ids'] as $pos => $recipe_id)
				{
                    if($recipe_id)
					{
						$recipes_params[] = array(
                            $this->id,
                            $day_index,
                            $pos,
                            $recipe_id,
                            $recipes['guests'][$pos]
                        );
					}
				}
				
				//Shopping
				$shopping = $day['shopping'];
				if($shopping['status'])
				{
					$status = $shopping['status'];
					$ing    = $shopping['ingredients'];
					$items  = $shopping['items'];
					$modif  = $shopping['modifications'];
					
					//Status
					$shopping_params[] = array(
                        $this->id,
                        $day_index,
                        $status
                    );
					
					//Ingredients
					foreach($ing as $ing_qty)
					{
						$ing_params[] = array(
                            $this->id,
                            $day_index,
                            $ing_qty->getID(),
                            $ing_qty->getQuantity(),
                            $ing_qty->getUnit()
                        );
					}
					
					//Items
					foreach($items as $item)
					{
						$items_params[] = array(
                            $this->id,
                            $day_index,
                            $item['id'],
                            $item['text'],
                            $item['category']
                        );
					}
					
					//Modifications
					foreach($modif as $ing_qty)
					{
						$modif_params[] = array(
                            $this->id,
                            $day_index,
                            $ing_qty->getID(),
                            $ing_qty->getQuantity(),
                            $ing_qty->getUnit()
                        );
                        
					}
				}
			}
			
			//Send requests
			if(count($quickmeals_params))   
                $this->DB->query($quickmeals_request, $quickmeals_params);
			if(count($recipes_params))      
                $this->DB->query($recipes_request, $recipes_params);
			if(count($shopping_params))     
                $this->DB->query($shopping_request, $shopping_params);
			if(count($ing_params))          
                $this->DB->query($ing_request, $ing_params);
			if(count($items_params))        
                $this->DB->query($items_request, $items_params);
			if(count($modif_params))        
                $this->DB->query($modif_request, $modif_params);
		}

        /**********************************************************
        SET
        ***********************************************************/

        /**
         * Set menu date
         * @param String $date menu reference date as "YYYY-MM-DD"
         */
        public function setDate($date)
        {
            $this->date = $date;
        }

        /**********************************************************
        SETUP
        ***********************************************************/
        /**
         * Set-up menu structure
         */
		private function setup()
		{
            //Loop through menu days
			for($i = 0; $i < C::MENU_DAYS_MAX; $i++)
			{
				$this->content[$i] = array(
                    'recipes'   => array(),
                    'shopping'  => array()
                );

				$this->content[$i]['quickmeals'] = array();

				$this->content[$i]['recipes']['ids']    = array_pad(array(), C::MENU_MEALS_COUNT, 0);
				$this->content[$i]['recipes']['guests'] = array_pad(array(), C::MENU_MEALS_COUNT, C::MENU_GUESTS_DEFAULT);

				$this->content[$i]['shopping']['status']        = 0;
				$this->content[$i]['shopping']['ingredients']   = array();
				$this->content[$i]['shopping']['items']         = array();
				$this->content[$i]['shopping']['modifications'] = array();
				$this->content[$i]['shopping']['shared']        = array();
			}
		}
	}
?>