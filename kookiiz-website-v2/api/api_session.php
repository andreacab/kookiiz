<?php
    /*******************************************************
    Title: API session
    Authors: Kookiiz Team
    Purpose: API module for session-related actions
    ********************************************************/

    //Dependencies
    require_once '../api/api.php';
    require_once '../class/email.php';
    require_once '../class/ingredients_db.php';
    require_once '../class/invitations_lib.php';
    require_once '../class/lang_db.php';
    require_once '../class/notifications.php';
    require_once '../class/password.php';
    require_once '../class/recipes_lib.php';
    require_once '../class/units_lib.php';
    require_once '../class/users_lib.php';

    //Represents an API handler for session-related actions
    class SessionAPI extends KookiizAPIHandler
    {
        /**********************************************************
        CONSTANTS
        ***********************************************************/

        const MODULE = 'session';

        /**********************************************************
        CONSTRUCTOR
        ***********************************************************/

        //Class constructor
        //-> (void)
        public function __construct()
        {
            global $API_AUTHORIZATIONS;

            //Construct generic API handler
            parent::__construct();

            //Define authorizations for current API module
            $this->AUTH = $API_AUTHORIZATIONS[self::MODULE];
        }

        /**********************************************************
        DESTRUCTOR
        ***********************************************************/

        //Class destructor
        //-> (void)
        public function __destruct()
        {
            parent::__destruct();
        }

        /**********************************************************
        ACTION
        ***********************************************************/

        //Take appropriate action
        //-> (void)
        protected function action()
        {
            switch($this->action)
            {
                case 'lang_change':
                    $this->lang_change();
                    break;
                case 'load':
                    $this->load();
                    break;
                case 'login':
                    $this->login();
                    break;
				case 'logout':
					$this->logout();
					break;
                case 'pass_reset':
                    $this->password_reset();
                    break;
                case 'save':
                    $this->save();
                    break;
                case 'update':
                    $this->update();
                    break;
            }
        }
        
        /**********************************************************
        GLOBALS
        ***********************************************************/

        //Transform a regular array into an 2D associative array with the keys "id" and "name"
        //corresponding to the numerical key and the value of each element of the input array
        //@input (array): array to export
        //->output (array): exported array
        private function globals_export($input)
        {
            $output = array();
            foreach($input as $index => $value)
            {
                $output[] = array(
                    'id'    => $index,
                    'name'  => $value
                );
            }
            return $output;
        }

        //Load session-related globals
        //-> (void)
        private function globals_load()
        {
            $globals = array();

            //Groups of ingredient categories
            $catgroups  = array();
            $cattogroup = C::get('ING_CATS_TOGROUP');
            foreach($cattogroup as $cat_id => $group_id)
            {
                $catgroups[] = array(
                    'cat_id'    => $cat_id,
                    'group_id'  => $group_id
                );
            }
            $globals['ing_catgroups'] = $catgroups;

            //Unit properties
            $globals['units'] = UnitsLib::exportAll();

            //Language arrays
            $globals['allergies_names']     = $this->globals_export($this->Lang->get('ALLERGIES_NAMES'));
            $globals['ing_categories']      = $this->globals_export($this->Lang->get('INGREDIENTS_CATEGORIES'));
            $globals['ing_groups']          = $this->globals_export($this->Lang->get('INGREDIENTS_GROUPS_NAMES'));
            $globals['recipes_categories']  = $this->globals_export($this->Lang->get('RECIPES_CATEGORIES'));
            $globals['recipes_levels']      = $this->globals_export($this->Lang->get('RECIPES_LEVELS'));
            $globals['recipes_origins']     = $this->globals_export($this->Lang->get('RECIPES_ORIGINS'));
            $globals['units_names']         = $this->globals_export($this->Lang->get('UNITS_NAMES'));

            //Return globals data
            return $globals;
        }

        /**********************************************************
        LANG
        ***********************************************************/

        //Request to change language setting
        //-> (void)
        private function lang_change()
        {
            $lang_new = $this->Request->get('lang');
            $lang_old = Session::getLang();

            if($lang_new != $lang_old)
            {
                $this->User->langChange($lang_new);
                $this->responseSetParam('changed', 1);
            }
            else 
                $this->responseSetParam('changed', 0);
        }

        /**********************************************************
        LOAD
        ***********************************************************/

        //Load session data
        //-> (void)
        private function load()
        {
            $session        = array();
            $invitations    = array();
            $quickmeals     = array();
            $recipes        = array();
            $users          = array($this->User->getID());

            //Retrieve session updates sent by client
            $updates = json_decode($this->Request->get('updates'), true);
            if(is_null($updates)) return;

            //Libraries
            $IngredientsDB  = new IngredientsDB($this->DB, $this->User->getLang());
            $InvitationsLib = new InvitationsLib($this->DB, $this->User);
            $QuickmealsLib  = new QuickmealsLib($this->DB, $this->User);
            $RecipesLib     = new RecipesLib($this->DB, $this->User);
            $UsersLib       = new UsersLib($this->DB, $this->User);

            //GLOBALS
            if(isset($updates['globals']))
            {
                $lang_timestamp = filemtime('../lang/fr.csv');
                if($lang_timestamp > $updates['globals'])
                {
                    $session['globals'] = array(
                        'data'  => $this->globals_load(),
                        'time'  => $lang_timestamp
                    );
                }
            }

            //INGREDIENTS
            if(isset($updates['ingredients']))
            {
                $ingredients_timestamp = max(filemtime('../ingredients/ingredients_nutrition_ca.csv'),
                                            filemtime('../ingredients/ingredients_properties_ca.csv'));
                if($ingredients_timestamp > $updates['ingredients'])
                {
                    $session['ingredients'] = array(
                        'data'  => $IngredientsDB->export(),
                        'time'  => $ingredients_timestamp
                    );
                }
            }

            //INVITATIONS
            if(isset($updates['invits']))
            {
                //Store client IDs and timestamps separately
                $ids = array(); $times = array();
                foreach($updates['invits'] as $inv)
                {
                    $ids[]      = (int)$inv['i'];
                    $times[]    = (int)$inv['t'];
                }

                //Check which invitations need to be loaded
                $invits_list    = $InvitationsLib->getList();
                $invits_req     = $InvitationsLib->updatedGet($ids, $times);
                $invits_req     = array_merge($invits_req, array_diff($invits_list, $ids));
                $invitations    = $InvitationsLib->get($invits_req);
                
                //Export invitations and store related recipes and users
                $session['invits'] = array();
                foreach($invitations as $Invitation)
                {
                    $session['invits'][] = $Invitation->export();
                    $recipes    = array_merge($recipes, $Invitation->getRecipes());
                    $users      = array_merge($users, $Invitation->getUsers());
                }
            }
            
            //NOTIFICATIONS
            if(isset($updates['notifs']))
            {
                $NotifHandler = new NotifHandler($this->DB, $this->User);
                $session['notifs'] = $NotifHandler->get();
            }

            //USER PROFILE
            if(isset($updates['user']))
            {
                $session['user'] = $this->User->export($updates['user']);
                if(isset($session['user']['favorites']))
                    $recipes = array_merge($recipes, $this->User->favorites_get());
                if(isset($session['user']['friends']))
                    $users = array_merge($users, $this->User->friends_ids_get());
                if(isset($session['user']['menu']))
                {
                    $recipes    = array_merge($recipes, $this->User->menu_recipes());
                    $quickmeals = array_merge($quickmeals, $this->User->menu_quickmeals());
                }
                if(isset($session['user']['quickmeals']))
                    $quickmeals = array_merge($quickmeals, $this->User->quickmeals_get());
            }

            //RECIPES         
            //Check which session recipes are missing client-side
            if(isset($updates['recipes']))
            {
                //Store IDs and timestamps separately
                $ids = array(); $times = array();
                foreach($updates['recipes'] as $recipe)
                {
                    $ids[]      = (int)$recipe['i'];
                    $times[]    = (int)$recipe['t'];
                }

                //Check which recipes need to be downloaded
                $recipes_req    = $RecipesLib->updatedGet($ids, $times);
                $recipes_req    = array_merge($recipes_req, array_diff($recipes, $ids));
            }
            //All recipes are required
            else 
                $recipes_req = $recipes;

            //USERS          
            if(isset($updates['users']))
                //Keep only user profiles missing client-side
                $users = array_diff($users, $updates['users']);
            
            //Load library data
            $session['quickmeals']  = $QuickmealsLib->export($quickmeals);
            $session['recipes']     = $RecipesLib->load($recipes_req);
            $session['users']       = $UsersLib->export($users);

            //Include session in response object
            $this->responseSetContent($session);
        }

        /**********************************************************
        LOGIN
        ***********************************************************/

        //Request to log user in
        //-> (void)
        private function login()
        {
            //Load and store parameters
            $email  = $this->Request->get('email');
            $pass   = $this->Request->get('password');
            $cookie = (int)$this->Request->get('cookie');
            $this->responseSetParam('cookie', $cookie);

            //Create password hash
            $PasswordHandler = new PasswordHandler();
            $salt   = $PasswordHandler->salt_from_email($this->DB, $email);
            $hash   = $PasswordHandler->hash($pass, $salt);

            //Try to login
            Session::destroy();
            $user_id = Session::login($email, $hash, $cookie === 1);
            $this->responseSetParam('user_id', $user_id);
        }
		
		//Request to log user out
		//-> (void)
		private function logout()
		{
			Session::destroy($cookie = true);
		}

        /**********************************************************
        PASSWORD LOST
        ***********************************************************/

        //Request to reset user's password and generate a temporary one
        //-> (void)
        private function password_reset()
        {
            //Load and store parameters
            $email = $this->Request->get('email');
            $this->responseSetParam('email', $email);

            //Try to retrieve corresponding user
            $UsersLib = new UsersLib($this->DB, $this->User);
            $user = $UsersLib->getFromEmail($email);
            if($user)
            {
                //Generate temporary password
                $PasswordHandler = new PasswordHandler();
                $password = $PasswordHandler->tempCreate($this->DB, $user->getID());

                //Send email with instructions
                $EmailHandler = new EmailHandler($this->DB);
                $EmailHandler->pattern(EmailHandler::TYPE_PASSWORDRESET, array(
                    'content'       => 'text',
                    'recipient'     => $email,
                    'user_id'       => $user->getID(),
                    'firstname'     => $user->getFirstname(),
                    'password'      => $password
                ));
            }
            //Do not produce an error here because it would tell a potential attacker
            //that this email does not exist !
            else 
                $this->close();
        }

        /**********************************************************
        SAVE
        ***********************************************************/

        //Request to save session data
        //-> (void)
        private function save()
        {
            $updates = array();

            //Retrieve session content
            $content = json_decode($this->Request->get('content'), true);
            if(is_null($content)) return;

            //USER
            if(isset($content['user']))
                $updates['user'] = $this->User->import($content['user']);
            
            //Store update timestamps in response
            $this->responseSetContent($updates);
        }

        /**********************************************************
        UPDATE
        ***********************************************************/

        //Request to simultaneously load and save session content
        //-> (void)
        private function update()
        {
            $this->save();
            $this->load();
        }
    }
?>