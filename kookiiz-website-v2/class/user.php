<?php
	/**********************************************************
    Title: User
    Authors: Kookiiz Team
    Purpose: Describe Kookiiz user profiles
    ***********************************************************/

    //Required classes
    require_once '../class/dblink.php';
    require_once '../class/exception.php';
    require_once '../class/fridge.php';
    require_once '../class/friend.php';
    require_once '../class/globals.php';
    require_once '../class/ingredient_qty.php';
    require_once '../class/menu.php';
    require_once '../class/quickmeal.php';
    require_once '../class/lang_db.php';
    require_once '../class/session.php';

    //Represents a public user profile
    class UserPublic
    {
        /**********************************************************
        PROPERTIES
        ***********************************************************/

        protected $id;
        protected $fb_id;
        protected $tw_id;
        protected $firstname;
        protected $lastname;
        protected $email;
        protected $date;
        protected $pic_id;
        protected $lang;

        protected $recipes = array();   //user's recipes

        /**********************************************************
        CONSTRUCTOR
        ***********************************************************/

		//Class constructor
        //@props (object): structure with user properties
		//-> (void)
		public function __construct($props)
		{
			//Import properties
			$this->id           = isset($props['id'])           ? $props['id'] : 0;
			$this->fb_id        = isset($props['fb_id'])        ? $props['fb_id'] : 0;
			$this->tw_id        = isset($props['tw_id'])        ? $props['tw_id'] : 0;
			$this->firstname    = isset($props['firstname'])    ? $props['firstname'] : '';
			$this->lastname     = isset($props['lastname'])     ? $props['lastname'] : '';
            $this->email        = isset($props['email'])        ? $props['email'] : '';
            $this->date         = isset($props['date'])         ? $props['date'] : '';
			$this->pic_id       = isset($props['pic_id'])       ? $props['pic_id'] : 0;
			$this->grade        = isset($props['grade'])        ? $props['grade'] : 0;
            $this->lang         = isset($props['lang'])         ? $props['lang'] : C::LANG_DEFAULT;
            $this->recipes      = isset($props['recipes'])      ? $props['recipes'] : array();

            //Other set-up
			$this->name = $this->firstname . ' ' . $this->lastname;
		}

        /**********************************************************
        GET
        ***********************************************************/

        //Return user's subscription date
        //->date (string): subscription date as "YYYY-MM-DD"
        public function getDate()
        {
            return $this->date;
        }

        //Return user's email (empty string if user is not a friend)
        //->email (string): user's email
        public function getEmail()
        {
            return $this->email;
        }

        //Get current user first name
        //->firstname (string): user first name
        public function getFirstname()
        {
            return $this->firstname;
        }

        //Get current user ID
        //->id (int): unique user ID
        public function getID()
        {
            return $this->id;
        }

        //Get current user grade
        //->grade (int): user's grade
        public function getGrade()
        {
            return $this->grade;
        }

        //Get current user language setting
        //->lang (string): language identifier
        public function getLang()
        {
            return $this->lang;
        }

        //Get current user last name
        //->lastname (string): user last name
        public function getLastname()
        {
            return $this->lastname;
        }

        //Get current user full name
        //->name (string): user full name ("firstname lastname")
        public function getName()
        {
            return $this->name;
        }

        //Get count of recipes created by this user
        //->count (int): count of recipes
        public function getRecipesCount()
        {
            return count($this->recipes);
        }

        /**********************************************************
        GRADE
        ***********************************************************/

        //Display user's grade as a list of cookies
        //@Lang (object):   language handler
        //@compact (bool):  should the grade be displayed in a compact manner?
        //-> (void)
        public function grade_display(LangDB &$Lang, $compact = false)
        {
            $grade = $this->grade;
            if($compact)
            {
                echo $grade,
                    '<img class="icon15 cookie1"',
                    ' src="', C::ICON_URL, '"',
                    ' alt="', $Lang->get('USER_GRADE_TEXT', 0), '"',
                    ' title="1 ', $Lang->get('USER_GRADE_TEXT', 1), '" />';
            }
            else
            {
                //Loop through available cookie values
                $cookies_values = C::get('COOKIES_VALUES');
                foreach($cookies_values as $value)
                {
                    while($grade >= $value)
                    {
                        echo '<img class="icon15 cookie', $value, '"',
                            ' src="', C::ICON_URL, '"',
                            ' alt="', $Lang->get('USER_GRADE_TEXT', 0), '"',
                            ' title="1 ', $Lang->get('USER_GRADE_TEXT', 1), '" />';

                        $grade -= $value;
                    }
                }
            }
        }

        /**********************************************************
        EXPORT
        ***********************************************************/

        //Export compact user profile
        //->profile (object): compact user profile data
        public function export()
        {
            return array(
                'id'        => $this->id,
                'fb_id'     => $this->fb_id,
                'tw_id'     => $this->tw_id,
                'first'     => $this->firstname,
                'last'      => $this->lastname,
                'date'      => $this->date,
                'grade'     => $this->grade,
                'lang'      => $this->lang,
                'pic_id'    => $this->pic_id,
                'recipes'   => $this->recipes
            );
        }

        /**********************************************************
        SORT
        ***********************************************************/

        //Static method to sort user profile objects by grade
        //@user_a (object): first user object
        //@user_b (object): second user object
        //->sorting (int): -1 (a before b), 0 (no sorting), 1 (a after b)
        public static function sortGrade(UserPublic $user_a, UserPublic $user_b)
        {
            return $user_a->getGrade() > $user_b->getGrade() ? -1
                    : ($user_a->getGrade() < $user_b->getGrade() ? 1 : 0);
        }
    }

    //Represents current user profile
	class User extends UserPublic
	{
        /**********************************************************
        CONSTANTS
        ***********************************************************/

        const AGE_MAX       = 100;
        const AGE_MIN       = 10;
        const HEIGHT_MAX    = 250;
        const HEIGHT_MIN    = 50;
        const WEIGHT_MAX    = 250;
        const WEIGHT_MIN    = 50;

        /**********************************************************
        PROPERTIES
        ***********************************************************/

        //Private objects
        private $DB;                        //database connection
        private $Fridge;                    //user's fridge
        private $Menu;                      //user's menu
        
        //Private properties		
		private $partner_id;				//partner ID
		private $chef_id;					//chef ID
		private $admin;						//is user an admin?
		private $admin_sup;					//is user a sup admin?
        private $first_visit;               //is it user's first visit?
        private $last_visit;				//last time the user came to the website
		private $virtual;					//virtual users cannot login

        //Private properties arrays
		private $activity       = array();		//activities
		private $allergies      = array();		//allergies
		private $anatomy        = array();		//height, weight, etc.
		private $breakfast      = array();		//breakfast content
        private $favorites      = array();      //user's favorite recipes
        private $friends        = array();      //user's friends
        private $markets        = array();      //user market configurations
        private $needs          = array();      //nutritional needs
		private $options        = array();		//options
		private $panels         = array();		//panels configuration
        private $quickmeals     = array();      //quick meals
		private $sports         = array();		//sports
		private $tastes         = array();		//tastes
        private $updates        = array();      //updates timestamps

        //Loading states
        private $loaded = array(
            'activity'      => false,
            'allergies'     => false,
            'anatomy'       => false,
            'breakfast'     => false,
            'favorites'     => false,
            'friends'       => false,
            'markets'       => false,
            'options'       => false,
            'panels'        => false,
            'personal'      => false,
            'quickmeals'    => false,
            'sports'        => false,
            'tastes'        => false,
            'updates'       => false
        );

        //Properties list
        private static $PROPERTIES = array(
            'activity', 'allergies', 'anatomy', 'breakfast',
            'favorites', 'fridge', 'friends', 'markets',
            'menu', 'options', 'panels', 'personal',
            'quickmeals', 'sports', 'tastes'
        );

        /**********************************************************
        CONSTRUCTOR
        ***********************************************************/
	
		//Class constructor
        //@DB (object): database handler
		//-> (void)
		public function __construct(DBLink &$DB)
		{
            //Reference arrays
            $this->ACTIVITY             = C::get('USER_ACTIVITY');
            $this->ACTIVITY_DEFAULTS    = C::get('USER_ACTIVITY_DEFAULTS');
            $this->ALLERGIES            = C::get('ALLERGIES');
            $this->ALLERGIES_DEFAULTS   = C::get('ALLERGIES_DEFAULTS');
            $this->ANATOMY              = C::get('USER_ANATOMY');
            $this->ANATOMY_DEFAULTS     = C::get('USER_ANATOMY_DEFAULTS');
            $this->OPTIONS              = C::get('USER_OPTIONS');
            $this->OPTIONS_DEFAULTS     = C::get('USER_OPTIONS_DEFAULTS');
            
            //Store DB handler
            $this->DB = $DB;

            //Start session
            Session::start();
		
			//Fetch user data from session
			$this->id   = Session::getID();
            $this->lang = Session::getLang();
			
			//Init properties
            $this->Fridge       = new Fridge($this->DB, $this);
            $this->Menu         = new Menu($this->DB, $this);
			$this->fb_id        = 0;
			$this->tw_id        = 0;
			$this->partner_id   = 0;
			$this->chef_id      = 0;
			$this->firstname    = '';
			$this->lastname     = '';
			$this->name         = '';
            $this->email        = '';
			$this->admin        = 0;
			$this->admin_sup    = 0;
			$this->pic_id       = 0;
			$this->date         = date('Y-m-d');
			$this->grade        = 0;
			$this->last_visit   = date('Y-m-d H:i');
			$this->virtual      = false;

            //Init properties arrays
			foreach($this->ACTIVITY as $id => $name)
			{
				$this->activity[$name] = $this->ACTIVITY_DEFAULTS[$id];
			}
			foreach($this->ALLERGIES as $id => $name)
			{
				$this->allergies[$name] = $this->ALLERGIES_DEFAULTS[$id];
			}
			foreach($this->ANATOMY as $id => $name)
			{
				$this->anatomy[$name] = $this->ANATOMY_DEFAULTS[$id];
			}
			foreach($this->OPTIONS as $id => $name)
			{
				$this->options[$name] = $this->OPTIONS_DEFAULTS[$id];
			}
            foreach(self::$PROPERTIES as $prop)
            {
                $this->updates[$prop] = 0;
            }
            $this->panels = array(
                'ids'       => C::get('PANELS_ORDER_DEFAULT'),
                'sides'     => C::get('PANELS_SIDES_DEFAULT'),
                'status'    => C::get('PANELS_STATES_DEFAULT')
            );
		}

        /**********************************************************
        GET
        ***********************************************************/

        //Get current user email
        //->email (string): user email
        public function getEmail()
        {
            $this->load('personal');
            return $this->email;
        }

        //Return user's Facebook ID
        //->fb_id (int): Facebook ID (0 if none)
        public function getFbID()
        {
            $this->load('personal');
            return $this->fb_id;
        }

        //Get current user first name
        //->firstname (string): user first name
        public function getFirstname()
        {
            $this->load('personal');
            return $this->firstname;
        }

        //Return user's grade
        //->grade (int): user's grade
        public function getGrade()
        {
            $this->load('personal');
            return $this->grade;
        }

        //Get current user last name
        //->lastname (string): user last name
        public function getLastname()
        {
            $this->load('personal');
            return $this->lastname;
        }

        //Get current user full name
        //->name (string): user full name ("firstname lastname")
        public function getName()
        {
            $this->load('personal');
            return $this->name;
        }

        //Return user's Twitter ID
        //->fb_id (int): Twitter ID (0 if none)
        public function getTwID()
        {
            $this->load('personal');
            return $this->tw_id;
        }

        /**********************************************************
        STATES
        ***********************************************************/

        //Specify is current user is an admin
        //->admin (bool): true if user is an admin, false otherwise
        public function isAdmin()
        {
            $this->load('personal');
            return $this->admin;
        }

        //Specify is current user is a super administrator
        //->admin_sup (bool): true if user is a super admin, false otherwise
        public function isAdminSup()
        {
            $this->load('personal');
            return $this->admin_sup;
        }

        //Specify if current user is logged (has an ID != 0)
        //->logged (bool): true if user is logged, false otherwise
        public function isLogged()
        {
            return $this->id ? true : false;
        }

        //Specify if it's user's first visit
        //->new (bool): true if it is user's first visit, false otherwise
        public function isNew()
        {
            $this->load('personal');
            return $this->first_visit;
        }
		
		/**********************************************************
        ACTIVITY
        ***********************************************************/
		
		//Export user's activity values in a compact format
		//->activity (array): list of activity values indexed by ID
		public function activity_export()
		{
            $this->load('activity');

            //Loop through user activity data
            $activity = array();
            foreach($this->activity as $name => $value)
            {
                $index = array_search($name, $this->ACTIVITY);
                $activity[$index] = $value;
            }
            return $activity;
		}

        //Return current user activity settings
        //->activity (object): activity values indexed by name
        public function activity_get()
        {
            $this->load('activity');
            return $this->activity;
        }

		//Import compact user activity values from client
		//@activity (array):    list of activity values indexed by ID
        //@time (int):          activity data timestamp
		//-> (void)
		public function activity_import($activity, $time = 0)
		{
            //Import activity data
			foreach($activity as $id => $value)
			{
				$name = $this->ACTIVITY[$id];
				if($name) $this->activity[$name] = (int)$activity[$index];
			}
            //Save imported data
            $this->activity_save($time);
		}
		
		//Load user's activity values from database
		//-> (void)
		private function activity_load()
		{
            //Abort if user is not logged
			if(!$this->id) return;

            //Retrieve user's activity values
			$request = 'SELECT ' . implode(', ', $this->ACTIVITY)
                    . ' FROM users_activity WHERE user_id = ?';
			$stmt = $this->DB->query($request, array($this->id));
            $activity = $stmt->fetch(PDO::FETCH_ASSOC);
			if($activity)
			{
				foreach($activity as $name => $value)
				{
					$this->activity[$name] = (int)$value;
				}
			}
		}
		
		//Save current user activity values in database
        //@time (int): activity data timestamp
		//-> (void)
		private function activity_save($time = 0)
		{
            //Abort if user is not logged
			if(!$this->id) return;

            //Remove existing activity values
            $request = 'DELETE FROM users_activity WHERE user_id = ?';
			$this->DB->query($request, array($this->id));

            //Insert activity values in database
            $fields = 'user_id';
            $values = '?';
            $params = array($this->id);
            foreach($this->activity as $field => $value)
            {
                $fields     .= ", $field";
                $values     .= ', ?';
                $params[]   = $value;
            }
            $request = "INSERT INTO users_activity ($fields) VALUES ($values)";
            $this->DB->query($request, $params);

            //Update related timestamp
            $this->updates_set('activity', $time);
		}
		
		/**********************************************************
        ALLERGIES
        ***********************************************************/
		
		//Export user's allergies data
		//->allergies (array): list of booleans indexed by allergy ID
		public function allergies_export()
		{
            $this->load('allergies');

			//Loop through allergies
			$allergies = array();
			foreach($this->allergies as $name => $value)
			{
				$index = array_search($name, $this->ALLERGIES);
				$allergies[$index] = $value;
			}			
			return $allergies;
		}

        //Return current allergy settings
        //->allergies (array): list of allergy values indexed by name
        public function allergies_get()
        {
            $this->load('allergies');
            return $this->allergies;
        }

		//Import user's allergies data
		//@allergies (array):   list of booleans indexed by allergy ID
        //@time (int):          allergies data timestamp
		//-> (void)
		public function allergies_import($allergies, $time = 0)
		{
			$allergies = array_map('intval', $allergies);

			//Loop through provided allergy data
			$this->allergies = array();
			foreach($allergies as $id => $value)
			{
				$name = $this->ALLERGIES[$id];
				if($name) $this->allergies[$name] = $allergies[$id];
			}
            //Save imported data
            $this->allergies_save($time);
		}
		
		//Load allergies from database
		//-> (void)
		private function allergies_load()
		{
            //Abort if user is not logged
			if(!$this->id) return;

			//Retrieve user's allergies
			$request = 'SELECT ' . implode(', ', $this->ALLERGIES)
                    . ' FROM users_allergies WHERE user_id = ?';
			$stmt = $this->DB->query($request, array($this->id));
            $allergies = $stmt->fetch(PDO::FETCH_ASSOC);
			if($allergies)
			{
				foreach($allergies as $name => $value)
				{
					$this->allergies[$name] = (int)$value;
				}
			}
		}
		
		//Save user's allergies in database
        //@time (int): allergies data timestamp
		//-> (void)
		private function allergies_save($time = 0)
		{
			//Abort if user is not logged
			if(!$this->id) return;

            //Remove existing allergy values
            $request = 'DELETE FROM users_allergies WHERE user_id = ?';
			$this->DB->query($request, array($this->id));

            //Insert allergy values in database
            $fields = 'user_id';
            $values = '?';
            $params = array($this->id);
            foreach($this->allergies as $field => $value)
            {
                $fields     .= ", $field";
                $values     .= ', ?';
                $params[]   = $value;
            }
            $request = "INSERT INTO users_allergies ($fields) VALUES ($values)";
            $this->DB->query($request, $params);

            //Update related timestamp
            $this->updates_set('allergies', $time);
		}
		
		/**********************************************************
        ANATOMY
        ***********************************************************/
		
		//Export user's anatomy values in a compact format
		//->activity (array): list of anatomy values indexed by ID
		public function anatomy_export()
		{
            $this->load('anatomy');

			//Loop through anatomy data
            $anatomy = array();
			foreach($this->anatomy as $name => $value)
			{
				$index = array_search($name, $this->ANATOMY);
				$anatomy[$index] = $value;
			}
			return $anatomy;
		}

		//Import user's activity values from client
		//@activity (array):    list of activity values indexed by ID
        //@time (int):          activity data timestamp
        //-> (void)
		public function anatomy_import($anatomy, $time = 0)
		{
            //Import activity data
			foreach($anatomy as $id => $value)
			{
				$name = $this->ANATOMY[$id];
				if($name) $this->anatomy[$name] = (int)$value;
			}
            //Save imported data
            $this->anatomy_save($time);
		}
		
		//Load user's anatomy values from database
		//-> (void)
		private function anatomy_load()
		{
            //Abort if user is not logged
			if(!$this->id) return;

            //Fetch anatomy data from server
			$request = 'SELECT ' . implode(', ', $this->ANATOMY)
                    . ' FROM users_anatomy WHERE user_id = ?';
			$stmt = $this->DB->query($request, array($this->id));
            $anatomy = $stmt->fetch(PDO::FETCH_ASSOC);
			if($anatomy)
			{
				foreach($anatomy as $name => $value)
				{
					$this->anatomy[$name] = (int)$value;
				}
			}
		}
		
		//Save user's activity values in database
        //@time (int): anatomy data timestamp
		//-> (void)
		private function anatomy_save($time = 0)
		{
            //Abort if user is not logged
			if(!$this->id) return;

            //Remove existing anatomy values
            $request = 'DELETE FROM users_anatomy WHERE user_id = ?';
			$this->DB->query($request, array($this->id));

            //Insert anatomy values in database
            $fields = 'user_id';
            $values = '?';
            $params = array($this->id);
            foreach($this->anatomy as $field => $value)
            {
                $fields     .= ", $field";
                $values     .= ', ?';
                $params[]   = $value;
            }
            $request = "INSERT INTO users_anatomy ($fields) VALUES ($values)";
            $this->DB->query($request, $params);

            //Update related timestamp
            $this->updates_set('anatomy', $time);
		}
		
		/**********************************************************
        BREAKFAST
        ***********************************************************/
		
		//Export user breakfast in compact format
		//->breakfast (object): arrays of breakfast properties
		public function breakfast_export()
		{
            $this->load('breakfast');

            //Loop through breakfast data
			$breakfast = array();
			foreach($this->breakfast as $ing)
			{
				$breakfast[] = $ing->export();
			}
			return $breakfast;
		}

		//Import breakfast content from client
		//@breakfast (object):  arrays of breakfast properties
        //@time (int):          breakfast data timestamp
		//-> (void)
		public function breakfast_import($breakfast, $time = 0)
		{
            //Import breakfast data
			$this->breakfast = array();
			foreach($breakfast as $ing)
			{
				$id                 = (int)$ing['i'];
				$quantity           = (float)$ing['q'];
				$unit               = (int)$ing['u'];
				$this->breakfast[]  = new IngredientQty($id, $quantity, $unit);
			}
            //Save imported data
            $this->breakfast_save($time);
		}
		
		//Load breakfast content from database
		//-> (void)
		private function breakfast_load()
		{
            //Abort if user is not logged
			if(!$this->id) return;

            //Query database from breakfast content
			$this->breakfast = array();
			$request = 'SELECT ingredient_id, quantity, unit'
                    . ' FROM users_breakfast WHERE user_id = ?';
			$stmt = $this->DB->query($request, array($this->id));
            while($ing = $stmt->fetch())
			{
				$id         = (int)$ing['ingredient_id'];
				$quantity   = (float)$ing['quantity'];
				$unit       = (int)$ing['unit'];
				$this->breakfast[] = new IngredientQty($id, $quantity, $unit);
			}
		}
		
		//Save breakfast content in database
        //@time (int): breakfast data timestamp
		//-> (void)
		private function breakfast_save($time = 0)
		{
            //Abort if user is not logged
			if(!$this->id) return;

			//Remove existing breakfast
			$request = 'DELETE FROM users_breakfast WHERE user_id = ?';
			$this->DB->query($request, array($this->id));
			
			//Store current breakfast
			if(count($this->breakfast))
			{
				$request = 'INSERT INTO users_breakfast (user_id, ingredient_id, quantity, unit)'
                        . ' VALUES (:user_id, :ing_id, :quantity, :unit)';
                $params = array();
				foreach($this->breakfast as $ing)
				{
					$params[] = array(
                        ':user_id'  => $this->id,
                        ':ing_id'   => $ing->getID(),
                        ':quantity' => $ing->getQuantity(),
                        ':unit'     => $ing->getUnit()
                    );
				}
				$this->DB->query($request, $params);
			}

            //Update related timestamp
            $this->updates_set('breakfast', $time);
		}

        /**********************************************************
        DELETE
        ***********************************************************/

		//Delete current user
        //@user_id (int): unique user ID
		//-> (void)
		public function delete($user_id)
		{
			//Set user as "deleted"
			$request = 'UPDATE users SET deleted = 1 WHERE user_id = ?';
			$this->DB->query($request, array($user_id));
		}

        /**********************************************************
        EMAIL
        ***********************************************************/

        //Change user email
        //@email (string): new email address
        //-> (void)
        public function email_change($email)
        {
            if(!$this->id) return;
            
            $request = 'UPDATE users SET email = ? WHERE user_id = ?';
            $stmt = $this->DB->query($request, array($email, $this->id));

            //Email could not be updated
            if($stmt->rowCount())
                $this->email = $email;
            else                    
                throw new KookiizException('user', Error::USER_SAVEFAILED);
        }

        /**********************************************************
        EXPORT
        ***********************************************************/

		//Export specified properties of user profile
        //@props (object): pairs of user property names and timestamps
		//->profile (object): user's profile data
		public function export($props)
		{
            $profile = array();
            foreach($props as $prop => $time)
            {
                $db_time = $this->updates_get($prop);
                if(!$time || $db_time > $time)
                {
                    $profile[$prop] = array(
                        'data'  => $this->export_prop($prop),
                        'time'  => $db_time
                    );
                }
            }
            return $profile;
		}

        //Export data for a given property
        //@prop (string): user property name
        //->content (mixed): exported property data
        private function export_prop($prop)
        {
            switch($prop)
            {
                case 'activity':
                    return $this->activity_export();
                    break;
                case 'allergies':
                    return $this->allergies_export();
                    break;
                case 'anatomy':
                    return $this->anatomy_export();
                    break;
                case 'breakfast':
                    return $this->breakfast_export();
                    break;
                case 'favorites':
                    return $this->favorites_export();
                    break;
                case 'fridge':
                    return $this->fridge_export();
                    break;
                case 'friends':
                    return $this->friends_export();
                    break;
                case 'markets':
                    return $this->markets_export();
                    break;
                case 'menu':
                    return $this->menu_export();
                    break;
                case 'options':
                    return $this->options_export();
                    break;
                case 'panels':
                    return $this->panels_export();
                    break;
                case 'personal':
                    return $this->personal_export();
                    break;
                case 'quickmeals':
                    return $this->quickmeals_export();
                    break;
                case 'sports':
                    return $this->sports_export();
                    break;
                case 'tastes':
                    return $this->tastes_export();
                    break;
            }
        }

        /**********************************************************
        FAVORITES
        ***********************************************************/

        //Save a new favorite recipe
        //@recipe_id (int): ID of the recipe to save
        //-> (void)
        public function favorites_add($recipe_id)
        {
            //Check that the recipe exists
            $request = 'SELECT 1 FROM recipes WHERE recipe_id = ?';
            $stmt = $this->DB->query($request, array($recipe_id));
            $data = $stmt->fetch();
            if($data)
            {
                //Add saved recipe to database
                $request = 'INSERT IGNORE INTO users_recipes'
                        . ' (user_id, recipe_id) VALUES (?, ?)';
                $this->DB->query($request, array($this->id, $recipe_id));

                //Reset local favorites data
                $this->reset('favorites');

                //Update related timestamp
                $this->updates_set('favorites');
            }
        }

        //Delete a favorite recipe (all of them if none is specified)
        //@recipe_id (int): ID of a specific favorite recipe to delete (optional)
        //-> (void)
        public function favorites_delete($recipe_id = 0)
        {
            $request = 'DELETE FROM users_recipes WHERE user_id = ?';
            $params = array($this->id);
            if($recipe_id)
            {
                $request .= ' AND recipe_id = ?';
                $params[] = $recipe_id;
            }
            $this->DB->query($request, $params);

            //Reset local favorites data
            $this->reset('favorites');

            //Update related timestamp
            $this->updates_set('favorites');
        }

        //Export current favorites list
        //->favorites (array): list of favorite recipe IDs
        public function favorites_export()
        {
            $this->load('favorites');
            return $this->favorites;
        }

        //Return current favorites list
        //->favorites (array): list of favorite recipe IDs
        public function favorites_get()
        {
            $this->load('favorites');
            return $this->favorites;
        }

        //Import favorite recipes
        //@favorites (array):   list of favorite recipe IDs
        //@time (int):          favorites data timestamp
        //-> (void)
        public function favorites_import($favorites, $time = 0)
        {
            //Import favorites data
            $this->favorites = array();
            foreach($favorites as $recipe_id)
                $this->favorites[] = (int)$recipe_id;
            //Save imported data
            $this->favorites_save($time);
        }

        //Load user's favorite recipes from server
        //-> (void)
        private function favorites_load()
        {
            //Abort if user is not logged
			if(!$this->id) return;

            //Retrieve user's favorite recipes
            $this->favorites = array();
            $request = 'SELECT recipe_id FROM users_recipes'
                    . ' WHERE user_id = ?';
            $stmt = $this->DB->query($request, array($this->id));
            $this->favorites = array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN, 0));
        }

        //Save user's favorites in database
        //@time (int): favorites data timestamp
        //-> (void)
        private function favorites_save($time = 0)
        {
            //Abort if user is not logged
			if(!$this->id) return;

            //Remove existing favorites
            $request = 'DELETE FROM users_recipes WHERE user_id = ?';
            $this->DB->query($request, array($this->id));

            //Save current ones
            if(count($this->favorites))
            {
                $request = 'INSERT INTO users_recipes (user_id, recipe_id)'
                            . ' VALUES (:user_id, :recipe_id)';
                $parameters = array();
                foreach($this->favorites as $recipe_id)
                {
                    $parameters[] = array(
                        ':user_id'      => $this->id,
                        ':recipe_id'    => $recipe_id
                    );
                }
                $this->DB->query($request, $parameters);
            }

            //Update related timestamp
            $this->updates_set('favorites', $time);
        }

        /**********************************************************
        FRIDGE
        ***********************************************************/

        //Export compact fridge content
        //->fridge (array): list of fridge ingredient quantities
        public function fridge_export()
        {
            return $this->Fridge->export();
        }

        //List current fridge ingredient IDs
        //->fridge_ids (array): list of fridge ingredient IDs
        public function fridge_ids()
        {
            return $this->Fridge->export_ids();
        }

        //Import fridge content
        //@data (object):   fridge compact content
        //@time (int):      fridge data timestamp
        //-> (void)
        public function fridge_import($data, $time)
        {
            $this->Fridge->import($data);

            //Update related timestamp
            $this->updates_set('fridge', $time);
        }

        /**********************************************************
        FRIENDS
        ***********************************************************/

        //Add a new user to friends
        //@friend_id (int): unique friend ID
        //->status (int): 0 = pending, 1 = valid
        public function friends_add($friend_id)
        {
            if(!$friend_id) throw new KookiizException('friends', 11);

            //Check for existing friendship links
            $request = 'SELECT user_1, user_2, valid, blocked'
                    . ' FROM friends'
                    . ' WHERE (user_1 = ? AND user_2 = ?)'
                        . ' OR (user_1 = ? AND user_2 = ?)';
            $params = array($this->id, $friend_id, $friend_id, $this->id);
            $stmt = $this->DB->query($request, $params);
            $link = $stmt->fetch();
            if($link)
            {
                //Valid friendship link already exists between these users
                if((int)$link['valid']) throw new KookiizException('friends', 1);
                //User already sent a request for friendship
                else if($link['user_1'] == $this->id && !(int)$link['valid'])
                {
                    if((int)$link['blocked'])   throw new KookiizException('friends', 6);
                    else                        throw new KookiizException('friends', 2);
                }
                //Target user already sent a request for friendship with current user
                else if($link['user_2'] == $this->id && !(int)$link['valid'])
                {
                    //Set friendship link validity status to 1 (and clear "blocked" flag)
                    $request = 'UPDATE friends'
                                . ' SET valid = 1, blocked = 0'
                            . ' WHERE user_1 = ? AND user_2 = ?';
                    $this->DB->query($request, array($link['user_1'], $this->id));

                    //Reset local friends data
                    $this->reset('friends');

                    //Update related timestamp
                    $this->updates_set('friends');

                    //Friendship link is already valid
                    return C::FRIEND_STATUS_VALID;
                }
            }
            else
            {
                //Friendship link does not exist yet between these users
                //Create request for friendship from user_id to friend_id
                $request = 'INSERT INTO friends (user_1, user_2) VALUES(?, ?)';
                $this->DB->query($request, array($this->id, $friend_id));

                //Friendship link is pending on user acceptance
                return C::FRIEND_STATUS_PENDING;
            }
        }

        //Block a friend
        //@friend_id (int): ID of the friend to block
        //-> (void)
        public function friends_block($friend_id)
        {
            //Set friendship link as blocked
            $request = 'UPDATE friends SET valid = 0, blocked = 1'
                    . ' WHERE user_1 = ? AND user_2 = ?';
            $stmt = $this->DB->query($request, array($friend_id, $this->id));

            //Friendship link was not found
            if(!$stmt->rowCount()) throw new KookiizException('friends', 5);
        }

        //Deny friendship request
        //@friend_id (int): ID of the friend to deny
        //-> (void)
        public function friends_deny($friend_id)
        {
            //Remove request from database
            $request = 'DELETE FROM friends'
                    . ' WHERE user_1 = ? AND user_2 = ?';
            $stmt = $this->DB->query($request, array($friend_id, $this->id));
            
            //Friendship link was not found
            if(!$stmt->rowCount()) throw new KookiizException('friends', 5);
        }

        //Return user's friends as objects
        //->friends (array): list of friends objects
        public function friends_get()
        {
            $this->load('friends');
            return $this->friends;
        }

        //Export friends list in compact format
        //->friends (array): list of compact friend objects
        public function friends_export()
        {
            $this->load('friends');

            $friends = array();
            foreach($this->friends as $friend)
            {
                $friends[] = $friend->export();
            }
            return $friends;
        }

        //Return user's friend IDs
        //->friends_ids (array): list of friend IDs
        public function friends_ids_get()
        {
            $this->load('friends');

            $friends_ids = array();
            foreach($this->friends as $friend)
            {
                $friends_ids[] = $friend->getID();
            }
            return $friends_ids;
        }

        //Load user's friends from database
		//-> (void)
        private function friends_load()
        {
            //Abort if user is not logged
			if(!$this->id) return;

            //Max time between now and last connection for friends to be considered online
            $online_delay = 2 * C::SESSION_TIMEOUT;
            
            //Query database for friendship links
            $this->friends = array();
            $request = 'SELECT user_id, TIMESTAMPDIFF(SECOND, last_visit, NOW()) AS delay'
                    . ' FROM friends, users'
                    . ' WHERE ((user_1 = ? AND user_2 = users.user_id)'
                    . ' OR (user_1 = users.user_id AND user_2 = ?)) AND valid = 1';
            $stmt = $this->DB->query($request, array($this->id, $this->id));
            while($friend = $stmt->fetch())
            {
                $id                 = (int)$friend['user_id'];
                $status             = (int)$friend['delay'] < $online_delay ? 1 : 0;
                $this->friends[]    = new Friend($id, $status);
            }
        }

        //Remove a friend
        //@friend_id (int): ID of the friend to remove
        //-> (void)
        public function friends_remove($friend_id)
        {
            //Delete friendship link
            $request = 'DELETE FROM friends WHERE (user_1 = ? AND user_2 = ?)'
                    . ' OR (user_1 = ? AND user_2 = ?)';
            $params = array($this->id, $friend_id, $friend_id, $this->id);
            $this->DB->query($request, $params);

            //Reset local friends data
            $this->reset('friends');

            //Update related timestamp
            $this->updates_set('friends');
        }

        //Load pending friendship requests
        //->requests (array): list of request objects as:
        //  #user_id (int): ID of user requesting friendship
        //  #fb_id (int):   Facebook ID of the user
        //  #name (string): user's name
        //  #pic_id (int):  user's picture ID
        //  #date (string): date of the friendship request as "DD-MM-YYYY"
        //  #time (string): time of the friendhsip request as "HH:MM"
        public function friends_requests()
        {
            $requests = array();

            //Ask server for requests data
            $request = 'SELECT user_id, facebook_id, name, pic_id,'
                        . ' UNIX_TIMESTAMP(friend_date) AS date'
                    . ' FROM friends'
                        . ' INNER JOIN users ON user_1 = user_id'
                    . ' WHERE user_2 = ? AND valid = 0 AND blocked = 0'
                    . ' ORDER BY date DESC';
            $stmt = $this->DB->query($request, array($this->id));
            while($request = $stmt->fetch())
            {
                $requests[] = array(
                    'user_id'   => (int)$request['user_id'],
                    'fb_id'     => (int)$request['facebook_id'],
                    'name'      => htmlspecialchars($request['name'], ENT_COMPAT, 'UTF-8'),
                    'pic_id'    => (int)$request['pic_id'],
                    'date'      => date('d.m.Y', (int)$request['date']),
                    'time'      => date('H:i', (int)$request['date'])
                );
            }
            return $requests;
        }

        /**********************************************************
        IMPORT
        ***********************************************************/

        //Import user profile data and return new timestamps
        //@profile (object): compact profile data to import
        //->updates (array): client or DB timestamp, whichever is more recent
        public function import($profile)
        {
            //Return array
            $updates = array();

            //Loop through provided data
            foreach($profile as $prop => $content)
            {
                $data    = $content['data'];
                $time    = (int)$content['time'];
                $db_time = $this->updates_get($prop);

                //Check if client time is more recent
                if($time > $db_time)
                {
                    //Import current data
                    switch($prop)
                    {
                        case 'activity':
                            $this->activity_import($data, $time);
                            break;
                        case 'allergies':
                            $this->allergies_import($data, $time);
                            break;
                        case 'anatomy':
                            $this->anatomy_import($data, $time);
                            break;
                        case 'breakfast':
                            $this->breakfast_import($data, $time);
                            break;
                        case 'favorites':
                            $this->favorites_import($data, $time);
                            break;
                        case 'fridge':
                            $this->fridge_import($data, $time);
                            break;
                        case 'menu':
                            $this->menu_import($data, $time);
                            break;
                        case 'options':
                            $this->options_import($data, $time);
                            break;
                        case 'panels':
                            $this->panels_import($data, $time);
                            break;
                        case 'sports':
                            $this->sports_import($data, $time);
                            break;
                        case 'tastes':
                            $this->tastes_import($data, $time);
                            break;
                        default:
                            continue;
                            break;
                    }

                    //Return client timestamp
                    $updates[$prop] = $time;
                }
                else
                {
                    //DB time is more recent
                    $updates[$prop] = $db_time;
                }
            }

            //Return updated timestamps
            return $updates;
        }

        /**********************************************************
        LANG
        ***********************************************************/

        //Change user's language setting
        //@lang (string): new language identifier
        //-> (void)
        public function langChange($lang)
        {
            //Update session setting
            Session::set('lang', $lang);

            //Update local setting
            $this->lang = $lang;

            //Update database setting
            if($this->isLogged())
            {
                $request = 'UPDATE users SET lang = ? WHERE user_id = ?';
                $this->DB->query($request, array($lang, $this->id));
            }
        }

        /**********************************************************
        LOAD
        ***********************************************************/

		//Load a given user property from database
        //@prop (string): property name
		//-> (void)
		private function load($prop)
		{
            //Abort if property content has already been loaded from database
            if($this->loaded[$prop]) return;

            //Call appropriate loading function depending on property
            switch($prop)
            {
                case 'activity':
                    $this->activity_load();
                    break;
                case 'allergies':
                    $this->allergies_load();
                    break;
                case 'anatomy':     
                    $this->anatomy_load();
                    break;
                case 'breakfast':   
                    $this->breakfast_load();
                    break;
                case 'favorites':   
                    $this->favorites_load();
                    break;
                case 'friends':     
                    $this->friends_load();
                    break;
                case 'invitations':
                    $this->invitations_load();
                    break;
                case 'markets':     
                    $this->markets_load();
                    break;
                case 'options':    
                    $this->options_load();
                    break;
                case 'panels':
                    $this->panels_load();
                    break;
                case 'personal':    
                    $this->personal_load();
                    break;
                case 'quickmeals':  
                    $this->quickmeals_load();
                    break;
                case 'sports':      
                    $this->sports_load();   
                    break;
                case 'tastes':      
                    $this->tastes_load();
                    break;
                case 'updates':
                    $this->updates_load();
                    break;
            }

            //Set property as loaded
            $this->loaded[$prop] = true;
		}

        /**********************************************************
        MARKETS
        ***********************************************************/

        //Create a new market configuration
        //@name (string):   configuration name
        //@order (array):   list of shopping category IDs
        //->market_id (int): ID of the new market configuration
        public function markets_create($name, array $order)
        {
            //Abort if user is not logged
            if(!$this->id) return;

            //Check if user exceeded the maximum number of market configurations
            $request = 'SELECT 1 FROM users_markets WHERE user_id = ?';
            $stmt = $this->DB->query($request, array($this->id));
            $count = count($stmt->fetchAll(PDO::FETCH_COLUMN, 0));
            if($count >= C::USER_MARKETS_MAX)
                throw new KookiizException('user', 4);

            //Insert new configuration in database
            $request = 'INSERT INTO users_markets (user_id, name, sequence, selected)'
                    . ' VALUES (:user_id, :name, :order, 1)';
            $this->DB->query($request, array(
                ':user_id'  => $this->id,
                ':name'     => $name,
                ':order'    => implode(',', $order)
            ));
            $market_id = $this->DB->insertID();

            //Update related timestamp
            $this->reset('markets');
            $this->updates_set('markets');

            //Return new market ID
            return $market_id;
        }

        //Delete existing market configuration
        //@id (int): ID of the market to delete
        //-> (void)
        public function markets_delete($id)
        {
            //Abort if user is not logged
            if(!$this->id) return;

            //Delete market from database
            $request = 'DELETE FROM users_markets'
                    . ' WHERE user_id = ? AND market_id = ?';
            $this->DB->query($request, array($this->id, $id));

            //Update related timestamp
            $this->reset('markets');
            $this->updates_set('markets');
        }

        //Export current market configurations
        //->markets (array): list of market configurations
        public function markets_export()
        {
            $this->load('markets');
            return $this->markets;
        }

        //Load user markets
		//-> (void)
        private function markets_load()
        {
            //Abort if user is not logged
			if(!$this->id) return;

            //Query database for markets data
            $this->markets = array();
            $request = 'SELECT market_id AS id, name, sequence, selected'
                    . ' FROM users_markets WHERE user_id = ?';
            $stmt = $this->DB->query($request, array($this->id));
            while($market = $stmt->fetch())
            {
                $this->markets[] = array(
                    'id'        => (int)$market['id'],
                    'name'      => htmlspecialchars($market['name'], ENT_COMPAT, 'UTF-8'),
                    'order'     => explode(',', $market['sequence']),
                    'selected'  => (int)$market['selected']);
            }
        }
        
        /**
         * Return shopping order for currently selected market, or default
         * @return Array list of ingredient group IDs
         */
        public function markets_order_get()
        {
            $this->load('markets');
            foreach($this->markets as $market)
            {
                if($market['selected'])
                    return $market['order'];
            }
            return C::get('ING_GROUPS_ORDER');
        }

        //Update an existing market configuration
        //@id (int):        ID of the market to save
        //@order (array):   list of shopping category IDs
        //-> (void)
        public function markets_save($id, $order)
        {
            //Abort if user is not logged
			if(!$this->id) return;

            //Save configuration
            $request = 'UPDATE users_markets SET sequence = ?'
                    . ' WHERE user_id = ? AND market_id = ?';
            $params = array(implode(',', $order), $this->id, $id);
            $this->DB->query($request, $params);

            //Update related timestamp
            $this->reset('markets');
            $this->updates_set('markets');
        }

        //Mark a given user market as selected
        //@id (int): ID of the market to select
        //-> (void)
        public function markets_select($id)
        {
            //Abort if user is not logged
            if(!$this->id) return;

            //Unselect all markets
            $request = 'UPDATE users_markets SET selected = 0'
                    . ' WHERE user_id = ?';
            $this->DB->query($request, array($this->id));
            //Select specific market
            $request = 'UPDATE users_markets SET selected = 1'
                    . ' WHERE user_id = ? AND market_id = ?';
            $this->DB->query($request, array($this->id, $id));

            //Update related timestamp
            $this->reset('markets');
            $this->updates_set('markets');
        }

        /**********************************************************
        MENU
        ***********************************************************/

        /**
         * Export user menu content
         * @return Array menu structure with:
         *      Array plan:  list of menu data per day
         *      String date: menu reference date as "YYYY-MM-DD"
         */
        public function menu_export()
        {
            return $menu = array(
                'plan'  => $this->Menu->export(),
                'date'  => $this->Menu->getDate()
            );
        }

        /**
         * Import menu content
         * @param Array $menu menu structure with:
         *      Array plan:  list of menu data per day
         *      String date: menu reference date as "YYYY-MM-DD"
         * @param String $time menu data timestamp
         */
        public function menu_import($menu, $time)
        {
            $this->Menu->setDate($menu['date']);
            $this->Menu->import($menu['plan']);

            //Update related timestamp
            $this->updates_set('menu', $time);
        }

        /**
         * Return current menu quick meals
         * @return Array list of quick meals IDs
         */
        public function menu_quickmeals()
        {
            return $this->Menu->getQuickmeals();
        }

        /**
         * Return current menu recipes
         * @return Array list of recipe IDs
         */
        public function menu_recipes()
        {
            return $this->Menu->getRecipes();
        }
        
        public function menu_reference_get()
        {
            return $this->Menu->getDate();
        }
        
        /**
         * Return menu shopping data for a given day
         * @param Int $day index of the day
         * @return Array shopping data
         */
        public function menu_shopping_get($day)
        {
            return $this->Menu->getShopping($day);
        }
        
        /**
         * Return list of days for which shopping is planned
         * @return Array list of day indexes
         */
        public function menu_shopping_getDays()
        {
            return $this->Menu->getShoppingDays();
        }

        /**********************************************************
        NUTRITIONAL NEEDS
        ***********************************************************/

        //Compute user's nutritional needs from anatomy and activity
        //-> (void)
        public function needs_compute()
        {
            $this->needs = array();
        }
		
		/**********************************************************
        OPTIONS
        ***********************************************************/
		
		//Export user's options data
		//->options (array): list of options values indexed by option ID
		public function options_export()
		{
			$this->load('options');

            $options = array();
			foreach($this->OPTIONS as $id => $name)
			{
				$options[$id] = $this->options[$name];
			}			
			return $options;
		}

        /**
         * Get value of a specific option or all of them
         * @param String $name option name (optional)
         * @return Mixed option value or list of option values indexed by name
         */
        public function options_get($name = null)
        {
            $this->load('options');
            
            if(is_null($name))  
                return $this->options;
            else                
                return isset($this->options[$name]) ? $this->options[$name] : null;
        }

		//Import user's options data
		//@options (array): list of options values indexed by option ID
        //@time (int):      options data timestamp
		//-> (void)
		public function options_import($options, $time = 0)
		{
            $options = array_map('intval', $options);

			//Loop through provided options data
			$this->options = array();
			foreach($options as $id => $value)
			{
				//Retrieve name of current option and store its value
				$name = $this->OPTIONS[$id];
				if($name) 
                    $this->options[$name] = $value;
			}
            //Save imported data
            $this->options_save($time);
		}
		
		//Load user's options from database
		//-> (void)
		private function options_load()
		{
			//Abort if user is not logged
			if(!$this->id) return;
			
			//Retrieve user's options
			$request    = 'SELECT ' . implode(', ', $this->OPTIONS)
                        .' FROM users_options WHERE user_id = ?';
			$stmt       = $this->DB->query($request, array($this->id));
            $options    = $stmt->fetch(PDO::FETCH_ASSOC);
			if($options)
			{
				foreach($options as $name => $value)
				{
					$this->options[$name] = (int)$value;
				}
			}
		}
		
		//Save user's options
        //@time (int): options data timestamp
		//-> (void)
		private function options_save($time = 0)
		{
			//Abort if user is not logged
			if(!$this->id) return;

            //Remove existing options
            $request = 'DELETE FROM users_options WHERE user_id = ?';
			$this->DB->query($request, array($this->id));
		
			//Insert options in database
            $fields = 'user_id'; $values = '?'; $params = array($this->id);
            foreach($this->options as $field => $value)
            {
                $fields     .= ", $field";
                $values     .= ', ?';
                $params[]   = $value;
            }
            $request = "INSERT INTO users_options ($fields) VALUES ($values)";
            $this->DB->query($request, $params);

            //Update related timestamp
            $this->updates_set('options', $time);
		}
		
		/**********************************************************
        PICTURE
        ***********************************************************/
		
		//Save current pic ID in database
        //@time (int): picture update timestamp (optional)
		//-> (void)
		private function picture_save($time)
		{
			//Abort if user is not logged
			if(!$this->id) return;

            //Insert new pic ID in database
			$request = 'UPDATE users SET pic_id = ? WHERE user_id = ?';
			$this->DB->query($request, array($this->pic_id, $this->id));

            //Update related timestamp
            $this->updates_set('personal', $time);
		}
		
		//Set user's pic ID
		//@pic_id (int): ID of user's picture
		//-> (void)
		public function picture_set($pic_id)
		{
			$this->pic_id = (int)$pic_id;
            $time         = time();
            $this->picture_save($time);
            return $time;
		}

        /**********************************************************
        PANELS
        ***********************************************************/

        //Export compact panels configuration data
        //->config (object): compact panels configuration
        public function panels_export()
        {
            $this->load('panels');
            return $this->panels;
        }

        //Import panels configuration
        //@config (object): panels configuration data
        //@time (int):      panels data timestamp
        //-> (void)
        public function panels_import($config, $time = 0)
        {
            $this->panels = array(
                'ids'       => array_map('intval', $config['ids']),
                'sides'     => array_map('intval', $config['sides']),
                'status'    => array_map('intval', $config['status'])
            );
            $this->panels_save($time);
        }

        //Load panels configuration from database
        //-> (void)
        private function panels_load()
        {
            if(!$this->id) return;

            $this->panels = array(
                'ids'       => array(),
                'sides'     => array(),
                'status'    => array()
            );

            $request = 'SELECT sequence, side, status FROM users_panels WHERE user_id = ?';
            $stmt = $this->DB->query($request, array($this->id));
            $data = $stmt->fetch();
            if($data)
            {
                $this->panels['ids']    = array_map('intval', explode(', ', $data['sequence']));
                $this->panels['sides']  = array_map('intval', explode(', ', $data['side']));
                $this->panels['status'] = array_map('intval', explode(', ', $data['status']));
            }
        }

        //Save panels configuration in database
        //@time (int): panels configuration timestamp
        //-> (void)
        private function panels_save($time = 0)
        {
            if(!$this->id) return;

            //Store configuration in database
            $request = 'INSERT INTO users_panels (user_id, sequence, side, status)'
                        . ' VALUES (:user_id, :sequence, :sides, :status)'
                    . ' ON DUPLICATE KEY UPDATE'
                        . ' sequence = VALUES(sequence),'
                        . ' side = VALUES(side),'
                        . ' status = VALUES(status)';
            $params = array(
                ':user_id'  => $this->id,
                ':sequence' => implode(', ', $this->panels['ids']),
                ':sides'    => implode(', ', $this->panels['sides']),
                ':status'   => implode(', ', $this->panels['status'])
            );
            $this->DB->query($request, $params);

            //Update related timestamp
            $this->updates_set('panels', $time);
        }

        /**********************************************************
        PASSWORD
        ***********************************************************/

        //Change user's password
        //@password_new (string): new password hash
        //-> (void)
        public function password_change($password_new)
        {
            $request = 'UPDATE users SET password = ? WHERE user_id = ?';
            $stmt = $this->DB->query($request, array($password_new, $this->id));

            //Password could not be updated
            if(!$stmt->rowCount()) throw new KookiizException('user', Error::USER_SAVEFAILED);
        }

		/**********************************************************
        PERSONAL
        ***********************************************************/

        //Export personal user data
        //->profile (object): main profile properties
        public function personal_export()
        {
            $this->load('personal');

            return array(
                'id'         => $this->id,
                'fb_id'      => $this->fb_id,
                'tw_id'      => $this->tw_id,
                'partner_id' => $this->partner_id,
                'chef_id'    => $this->chef_id,
                'firstname'  => $this->firstname,
                'lastname'   => $this->lastname,
                'date'       => $this->date,
                'email'      => $this->email,
                'pic_id'     => $this->pic_id,
                'grade'      => $this->grade,
                'lang'       => $this->lang,
                'admin'      => $this->admin,
                'admin_sup'  => $this->admin
            );
        }
		
		//Load personal user data
		//-> (void)
		private function personal_load()
		{
            //Abort if user is not logged
			if(!$this->id) return;

            //Query database for user's profile properties
			$request = 'SELECT facebook_id, IF(twitter_id IS NOT NULL, twitter_id, 0) AS twitter_id,'
                        . ' partner_id, chef_id, firstname, lastname, name, first_visit,'
                        . ' admin, admin_sup, email, pic_id, UNIX_TIMESTAMP(user_date) AS date,'
                        . ' user_grade, UNIX_TIMESTAMP(last_visit) AS visit, lang, virtual'
					. ' FROM users'
                        .' LEFT JOIN users_twitter USING (user_id)'
                    . ' WHERE user_id = ?';
			$stmt = $this->DB->query($request, array($this->id));
            $user = $stmt->fetch();
			if($user)
			{
				$this->fb_id        = (int)$user['facebook_id'];
				$this->tw_id        = (int)$user['twitter_id'];
				$this->partner_id   = (int)$user['partner_id'];
				$this->chef_id      = (int)$user['chef_id'];
				$this->firstname    = htmlspecialchars($user['firstname'], ENT_COMPAT, 'UTF-8');
				$this->lastname     = htmlspecialchars($user['lastname'], ENT_COMPAT, 'UTF-8');
				$this->name         = htmlspecialchars($user['name'], ENT_COMPAT, 'UTF-8');
				$this->admin        = (int)$user['admin'];
				$this->admin_sup    = (int)$user['admin_sup'];
				$this->email        = htmlspecialchars($user['email'], ENT_COMPAT, 'UTF-8');
				$this->pic_id       = (int)$user['pic_id'];
				$this->date         = date('Y-m-d', $user['date']);
				$this->grade        = (int)$user['user_grade'];
				$this->first_visit  = (int)$user['first_visit'];
				$this->last_visit   = date('Y-m-d H:i', $user['visit']);
				$this->lang         = htmlspecialchars($user['lang'], ENT_COMPAT, 'UTF-8');
				$this->virtual      = (int)$user['virtual'];
			}
		}

        /**********************************************************
        QUICK MEALS
        ***********************************************************/

		//Export user's quick meals
		//->quickmeals (array): list of quick meal IDs
		public function quickmeals_export()
		{
            $this->load('quickmeals');
            return $this->quickmeals;
		}

        //Get a list of user's quick meals
        //->quickmeals (array): list of quick meal IDs
        public function quickmeals_get()
        {
            $this->load('quickmeals');
            return $this->quickmeals;
        }

		//Load user's quick meals from database
		//-> (void)
		private function quickmeals_load()
		{
            //Abort if user is not logged
			if(!$this->id) return;

            //Empty quick meals list
			$this->quickmeals = array();

			//Look for quick meals created by user
            $request = 'SELECT quickmeal_id AS id FROM quickmeals'
                    . ' WHERE user_id = ?';
            $stmt = $this->DB->query($request, array($this->id));
            while($quickmeal = $stmt->fetch())
            {
                $this->quickmeals[] = (int)$quickmeal['id'];
            }
		}

        /**********************************************************
        RESET
        ***********************************************************/

        //Reset local property data (e.g. when data has changed and must be downloaded again)
        //@property (string): name of the property to reset
        //-> (void)
        private function reset($property)
        {
            //Set property as "not loaded"
            $this->loaded[$property] = false;
        }
		
		/**********************************************************
        SPORTS
        ***********************************************************/

        //Export user's sports data
        //->sports (array): list of sport ID/freq ID pairs
        public function sports_export()
        {
            $this->load('sports');
            return $this->sports;
        }
		
		//Return user's sports
		//->sports (array): list of sport ID/freq ID pairs
		public function sports_get()
		{
            $this->load('sports');
			return $this->sports;
		}

		//Import user's sport data
		//@sports (array):  list of sport ID/freq ID pairs
        //@time (int):      sports data timestamp
		//-> (void)
		public function sports_import($sports, $time = 0)
		{
			foreach($sports as $index => $sport)
			{
				if($index > C::USER_SPORTS_MAX) break;
                
                $this->sports[] = array(
                    'id'    => (int)$sport['id'],
                    'freq'  => (int)$sport['freq']);
			}
            //Save imported data
            $this->sports_save($time);
		}
		
		//Load user's sports from database
		//-> (void)
		private function sports_load()
		{
            //Abort if user is not logged
			if(!$this->id) return;

            //Empty sports list
			$this->sports = array();

            //Query database for user's sports data
			$request = 'SELECT sport_id, freq_id FROM users_sports'
					. ' WHERE user_id = ?';
			$stmt = $this->DB->query($request, array($this->id));
			while($sport = $stmt->fetch())
			{
				$this->sports[] = array(
                    'id'    => (int)$sport['sport_id'],
                    'freq'  => (int)$sport['freq_id']);
			}
		}
		
		//Save user's sports in database
        //@time (int): sports data timestamp
		//-> (void)
		private function sports_save($time = 0)
		{
            //Abort if user is not logged
			if(!$this->id) return;
		
			//Remove existing sports
			$request = 'DELETE FROM users_sports WHERE user_id = ?';
			$this->DB->query($request, array($this->id));
			
			if(count($this->sports))
			{
				//Insert updated tastes
				$request = 'INSERT INTO users_sports (user_id, sport_id, freq_id)'
                        . ' VALUES (:user_id, :sport_id, :freq_id)';
                $parameters = array();
				foreach($this->sports as $sport)
				{
                    $parameters[] = array(
                        ':user_id'  => $this->id,
                        ':sport_id' => $sport['id'],
                        ':freq_id'  => $sport['freq']
                    );
				}
				$this->DB->query($request, $parameters);
			}

            //Update related timestamp
            $this->updates_set('sports', $time);
		}
				
		/**********************************************************
        TASTES
        ***********************************************************/

        //Export user's tastes in compact format
        //->tastes (array): list of taste ID/type pairs
        public function tastes_export()
        {
            $this->load('tastes');
            return $this->tastes;
        }
		
		//Return user's tastes
        //#type (int): type of user tastes to return (optional)
		//->tastes (array): list of taste ID/type pairs or only taste IDs if type is specified
		public function tastes_get($type = null)
		{
            $this->load('tastes');

			if(is_null($type)) return $this->tastes;
            else
            {
                $tastes = array();
                foreach($this->tastes as $taste)
                {
                    if($taste['type'] == $type) $tastes[] = $taste['id'];
                }
                return $tastes;
            }
		}

		//Import user tastes data from client
		//@tastes (array):  list of ingredient ID/taste type pairs
        //@time (int):      tastes data timestamp
		//-> (void)
		public function tastes_import($tastes, $time = 0)
		{
            //Empty tastes list
			$this->tastes = array();

			//Loop through provided tastes data
			foreach($tastes as $index => $taste)
			{
                if($index > C::USER_TASTES_MAX) break;

				$this->tastes[] = array(
                    'id'    => (int)$taste['id'],
                    'type'  => (int)$taste['type']
                );
			}
            //Save imported data
            $this->tastes_save($time);
		}
		
		//Load user's tastes
		//-> (void)
		private function tastes_load()
		{
            //Abort if user is not logged
			if(!$this->id) return;

            //Empty tastes list
			$this->tastes = array();

            //Query tastes data from server
			$request = 'SELECT ingredient_id, type FROM users_tastes'
					. ' WHERE user_id = ?';
            $stmt = $this->DB->query($request, array($this->id));
			while($taste = $stmt->fetch())
			{
				$this->tastes[] = array(
                    'id'    => (int)$taste['ingredient_id'],
                    'type'  => (int)$taste['type']
                );
			}
		}
		
		//Save user's tastes
        //@time (int): tastes data timestamp
		//-> (void)
		private function tastes_save($time = 0)
		{
			//Abort if user is not logged
			if(!$this->id) return;
		
			//Remove existing tastes
			$request = 'DELETE FROM users_tastes WHERE user_id = ?';
			$this->DB->query($request, array($this->id));
			
			if(count($this->tastes))
			{
				//Insert updated tastes
				$request = 'INSERT INTO users_tastes (user_id, ingredient_id, type)'
                        . ' VALUES (:user_id, :ing_id, :type)';
                $params = array();
				foreach($this->tastes as $taste)
				{
                    $params[] = array(
                        ':user_id'  => $this->id,
                        ':ing_id'   => $taste['id'],
                        ':type'     => $taste['type']
                    );
				}
				$this->DB->query($request, $params);
			}

            //Update related timestamp
            $this->updates_set('tastes', $time);
		}

        /**********************************************************
        UPDATES
        ***********************************************************/

        //Return timestamp value for a specific user property
        //@prop (string): property name
        //->time (int): property timestamp
        public function updates_get($prop)
        {
            $this->load('updates');
            return isset($this->updates[$prop]) ? $this->updates[$prop] : 0;
        }

        //Update timestamp value for a given user properties
        //@prop (string):   name of the property
        //@time (int):      new UNIX timestamp (defaults to now)
        //-> (void)
        private function updates_set($prop, $time = 0)
        {
            if(!in_array($prop, self::$PROPERTIES)) return;

            //Format timestamp
            if(!$time) $time = time();
            $date = date('Y-m-d H:i:s', $time);

            //Set update in database
            $request = "INSERT INTO users_updates (user_id, $prop)"
                    . " VALUES (?, ?) ON DUPLICATE KEY UPDATE $prop = VALUES($prop)";
            $this->DB->query($request, array($this->id, $date));

            //Store timestamp locally
            $this->updates[$prop] = $time;
        }

        //Get current timestamp values for a given set of user properties
        //@props (array): list of properties for which to retrieve updates (defaults to all)
        //-> (void)
        private function updates_load($props = null)
        {
            //Request timestamp(s) from server
            $request = 'SELECT ' . implode(', ', self::$PROPERTIES)
                    . ' FROM users_updates'
                    . ' WHERE user_id = ?';
            $stmt = $this->DB->query($request, array($this->id));
            $data = $stmt->fetch();
            if($data)
            {
                foreach($data as $prop => $time)
                {
                    $timestamp              = strtotime($data[$prop]);
                    $this->updates[$prop]   = $timestamp ? $timestamp : 0;
                }
            }
            else
            {
                //Set all updates to now
                $now = time();
                $request = 'INSERT INTO users_updates (user_id, ' . implode(', ', self::$PROPERTIES) . ')'
                            . ' VALUES (?, ' . implode(', ', array_fill(0, count(self::$PROPERTIES), '?')) . ')';
                $params = array_pad(array($this->id), count(self::$PROPERTIES) + 1, date('Y-m-d H:i:s', $now));
                $stmt = $this->DB->query($request, $params);
                foreach($this->updates as &$time)
                {
                    $time = $now;
                }
                unset($time);
            }
        }

        /**********************************************************
        VISITS
        ***********************************************************/

        //Account for user's visit
        //-> (void)
        public function visit()
        {
            //Abort if user is not logged
			if(!$this->id) return;

            //Update "last_visit" timestamp (and clear "first_visit" flag)
            $time = time();
            $request = 'UPDATE users'
                    . ' SET first_visit = 0, last_visit = FROM_UNIXTIME(?)'
                    . ' WHERE user_id = ?';
            $this->DB->query($request, array($time, $this->id));

            //Update local variables
            $this->first_visit = false;
            $this->last_visit  = date('Y-m-d H:i', $time);
        }
	}
?>