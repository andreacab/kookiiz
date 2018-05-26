<?php
	/**********************************************************
    Title: Fridge class
    Authors: Kookiiz Team
    Purpose: Manage the content of a user's fridge
    ***********************************************************/

    //External files
    require_once '../class/dblink.php';
    require_once '../class/globals.php';
    require_once '../class/ingredient_qty.php';
    require_once '../class/user.php';

    //Represents the user's fridge content
	class Fridge
	{
        /**********************************************************
        PROPERTIES
        ***********************************************************/

        private $DB;                        //link to the database
		private $User;                      //user of this fridge
		private $ingredients    = array();  //content of the fridge
        private $loaded         = false;    //Has fridge content been loaded from DB?

        /**********************************************************
        CONSTRUCTOR
        ***********************************************************/
	
		//Class constructor
        //@DB (object):     database connection object
        //@User (object):   fridge owner
        //-> (void)
		function __construct(DBLink &$DB, User &$User)
		{
            $this->DB   = $DB;
			$this->User = $User;
		}

        /**********************************************************
        EXPORT
        ***********************************************************/
		
		//Export compact fridge content
		//->fridge_clean (array): list of ingredient quantities
		public function export()
		{
            if(!$this->loaded) $this->load();

			$fridge = array();
			foreach($this->ingredients as $amount)
			{
				$fridge[] = $amount->export();
			}
			return $fridge;
		}
		
		//Extract IDs of fridge ingredients
		//->fridge_ids (array): list of ingredient IDs
		public function export_ids()
		{
            if(!$this->loaded) $this->load();

			$fridge_ids = array();
			foreach($this->ingredients as $amount)
			{
				$fridge_ids[] = $amount->getID();
			}
			return $fridge_ids;
		}

        /**********************************************************
        IMPORT
        ***********************************************************/
		
		//Import compact fridge data
		//@fridge (array): list of ingredient quantities
		//-> (void)
		public function import($fridge)
		{
            //Import fridge data
			$this->ingredients = array();
			foreach($fridge as $index => $amount)
			{
                if($index > C::FRIDGE_MAX) break;
                
				$id         = (int)$amount['i'];
				$quantity   = (float)$amount['q'];
				$unit       = (int)$amount['u'];
				$this->ingredients[] = new IngredientQty($id, $quantity, $unit);
			}

            //Fridge content is up to date
            $this->loaded = true;

            //Save imported data in database
            $this->save();
		}

        /**********************************************************
        LOAD
        ***********************************************************/
					
		//Load fridge content from provided database connection
		//-> (void)
		private function load()
		{
            //Abort is user is not logged
            if(!$this->User->isLogged()) return;

            //Load fridge content from database
			$request = 'SELECT ing_id, quantity, unit'
                    . ' FROM users_fridge WHERE user_id = ?';
			$stmt = $this->DB->query($request, array($this->User->getID()));
			while($fridge_item = $stmt->fetch())
			{
				$id         = (int)$fridge_item['ing_id'];
				$quantity   = (float)$fridge_item['quantity'];
				$unit       = (int)$fridge_item['unit'];
				$this->ingredients[] = new IngredientQty($id, $quantity, $unit);
			}

            //Fridge content is up to date
            $this->loaded = true;
		}

        /**********************************************************
        SAVE
        ***********************************************************/
		
		//Save current fridge content using provided database connection
		//-> (void)
		private function save()
		{
            //Abort is user is not logged
            if(!$this->User->isLogged()) return;

            //Delete existing fridge content from database
			$request = 'DELETE FROM users_fridge WHERE user_id = ?';
            $stmt = $this->DB->query($request, array($this->User->getID()));

            //Store ingredients in database
			if(count($this->ingredients))
			{
				$request = 'INSERT INTO users_fridge (user_id, ing_id, quantity, unit)'
                         . ' VALUES (:user_id, :ing_id, :quantity, :unit)';
                $params = array();
				foreach($this->ingredients as $amount)
				{
                    $params[] = array(
                        ':user_id'  => $this->User->getID(),
                        ':ing_id'   => $amount->getID(),
                        ':quantity' => $amount->getQuantity(),
                        ':unit'     => $amount->getUnit()
                    );
				}
                $this->DB->query($request, $params);
			}
		}
	}
?>