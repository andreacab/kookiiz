<?php
	/**********************************************************
    Title: Shopping class
    Authors: Kookiiz Team
    Purpose: Define a shopping list object
    ***********************************************************/
	
	class shopping
	{
		private $user_id;
		private $user_name;
		private $date;
		private $menu_id;				//Menu this list belongs to
		private $menu_day;				//Day of the above menu for which this list is set
		private $status;				//Shopping status (morning/evening)
		private $ingredients;			//Required ingredient quantities
		private $items;					//Manually added items
		private $modifications;			//Modifications made to above ingredients quantities
		private $shared;				//Users with whom this list is shared (array of IDs)
		
		//Init shopping list properties
		//@menu_id (int): unique ID of the menu this list belongs to
		//@menu_day (int): day index inside the menu
		//-> (void)
		function __construct($menu_id, $menu_day)
		{
            $this->user_id = 0;
            $this->user_name = '';
            $this->date = date('Y-m-d');
			$this->menu_id = (int)$menu_id;
			$this->menu_day = (int)$menu_day;
            $this->status = 0;
			$this->ingredients = array();
			$this->items = array();
			$this->modifications = array();
			$this->shared = array();
		}
		
		//Load shopping list data using provided database connection
		//@db (mysqli): open database connection
		//->success (bool): true if loading succeeded
		function load($db)
		{
			//Author
			$request = "SELECT users.user_id, users.name,"
					. " UNIX_TIMESTAMP(menu_date) AS date"
					. " FROM users_menus JOIN users USING(user_id)"
					. " WHERE users_menus.menu_id = $this->menu_id";
			$user_data = mysql_safe_request($db, $request);
			$user_data = $user_data->fetch_assoc();
            if($user_data)
            {
                $this->user_id = (int)$user_data['user_id'];
                $this->user_name = htmlspecialchars($user_data['name'], ENT_COMPAT, 'UTF-8');
                $this->date = date('Y-m-d', (int)$user_data['date'] + ($this->menu_day - 14) * 24 * 3600);
            }
            else return false;
			
			//Status
			$request = 'SELECT status FROM menus_shopping'
					. " WHERE menu_id = $this->menu_id AND menu_day = $this->menu_day";
			$status_data = mysql_safe_request($db, $request);
			$status_data = $status_data->fetch_assoc();
            if($status_data)
            {
                $this->status = (int)$status_data['status'];
            }
            else return false;
		
			//Ingredients
			$request = "SELECT ing_id, quantity, unit"
					. " FROM shopping_ingredients"
					. " WHERE menu_id = $this->menu_id"
					. " AND menu_day = $this->menu_day";
			$shopping_data = mysql_safe_request($db, $request);
			while($shopping = $shopping_data->fetch_assoc())
			{
				$id = (int)$shopping['ing_id'];
				$quantity = (float)$shopping['quantity'];
				$unit = $shopping['unit'];
				array_push($this->ingredients, array('id' => $id, 'quantity' => $quantity, 'unit' => $unit));
			}
			
			//Items
			$request = "SELECT item_id, item_text, item_category"
					. " FROM shopping_items"
					. " WHERE menu_id = $this->menu_id"
					. " AND menu_day = $this->menu_day";
			$shopping_data = mysql_safe_request($db, $request);
			while($shopping = $shopping_data->fetch_assoc())
			{
				$id = (int)$shopping['item_id'];
				$text = $shopping['item_text'];
				$category = (int)$shopping['item_category'];
				array_push($this->items, array('id' => $id, 'text' => $text, 'category' => $category));
			}
			
			//Modifications
			$request = "SELECT ing_id, quantity, unit"
					. " FROM shopping_modifications"
					. " WHERE menu_id = $this->menu_id"
					. " AND menu_day = $this->menu_day";
			$shopping_data = mysql_safe_request($db, $request);
			while($shopping = $shopping_data->fetch_assoc())
			{
				$id = (int)$shopping['ing_id'];
				$quantity = (float)$shopping['quantity'];
				$unit = $shopping['unit'];
				array_push($this->modifications, array('id' => $id, 'quantity' => $quantity, 'unit' => $unit));
			}
		
			//People with whom this list is shared
			$request = "SELECT friend_id"
					. " FROM shared_shopping"
					. " WHERE menu_id = $this->menu_id"
					. " AND shopping_date = '$this->date'";
			$shopping_data = mysql_safe_request($db, $request);	
			while($shopping = $shopping_data->fetch_assoc())
			{
				$id = (int)$shopping['friend_id'];
				array_push($this->shared, $id);
			}

            return true;
		}
		
		//Export shopping list content
		//->shopping (object): shopping list structure
		function export()
		{
			$shopping = array();
			
			//Properties
			$shopping['user_id'] = $this->user_id;
			$shopping['user_name'] = $this->user_name;
			$shopping['date'] = $this->date;
			
			//Arrays
			$shopping['ing'] = $this->ingredients_export($this->ingredients); 
			$shopping['it'] = array();
			for($i = 0, $imax = count($this->items); $i < $imax; $i++)
			{
				$id = $this->items[$i]['id'];
				$text = $this->items[$i]['text'];
				$category = $this->items[$i]['category'];
				$shopping['it'][] = array('i' => $id, 't' => $text, 'c' => $category);
			}
			$shopping['m'] = $this->ingredients_export($this->modifications);
			
			return $shopping;
		}
		
		//Export ingredient quantities in compact format
		//@ingredients (array): list of ingredient quantities
		//->ingredients_compact (array): list of compact ingredient quantities
		private function ingredients_export($ingredients)
		{
			$ingredients_compact = array();
			foreach($ingredients as $ing_qty)
			{
				$id = $ing_qty['id'];
				$quantity = $ing_qty['quantity'];
				$unit = $ing_qty['unit'];
				$ingredients_compact[] = array('i' => $id, 'q' => $quantity, 'u' => $unit);
			}
			return $ingredients_compact;
		}
	}
?>