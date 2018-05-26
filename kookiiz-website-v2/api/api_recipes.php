<?php
    /*******************************************************
    Title: API recipes
    Authors: Kookiiz Team
    Purpose: API module for recipes-related actions
    ********************************************************/

    //Dependencies
    require_once '../api/api.php';
    require_once '../class/email.php';
    require_once '../class/events_lib.php';
    require_once '../class/glossary.php';
    require_once '../class/pictures_lib.php';
    require_once '../class/recipes_lib.php';
    require_once '../class/users_lib.php';

    //Represents an API handler for feedback-related actions
    class RecipesAPI extends KookiizAPIHandler
    {
        /**********************************************************
        PROPERTIES
        ***********************************************************/

        const MODULE = 'recipes';

        /**********************************************************
        CONSTRUCTOR
        ***********************************************************/

        /**
         * Class constructor
         * @global Array $API_AUTHORIZATIONS list of API authorization levels
         */
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

        /**
         * Class destructor
         */
        public function __destruct()
        {
            parent::__destruct();
        }

        /**********************************************************
        ACTION
        ***********************************************************/

        /**
         * Take appropriate action
         */
        protected function action()
        {
            switch($this->action)
            {
                case 'check_title':
                    $this->check_title();
                    break;
                case 'delete':
                    $this->delete();
                    break;
                case 'dismiss':
                    $this->dismiss();
                    break;
                case 'edit':
                    $this->edit();
                    break;
                case 'express':
                    $this->express();
                    break;
                case 'list_dismiss':
                    $this->listDismiss();
                    break;
                case 'load':
                    $this->load();
                    break;
                case 'rate':
                    $this->rate();
                    break;
                case 'report':
                    $this->report();
                    break;
                case 'save':
                    $this->save();
                    break;
                case 'save_pic':
                    $this->save_pic();
                    break;
                case 'search':
                    $this->search();
                    break;
                case 'translate':
                    $this->translate();
                    break;
                case 'validate':
                    $this->validate();
                    break;
            }
        }

        /**********************************************************
        CHECK TITLE
        ***********************************************************/

        /**
         * Request to check if a recipe title already exists
         */
        private function check_title()
        {
            //Load and store parameters
            $title = $this->Request->get('title');
            $this->responseSetParam('title', $title);

            //Retrieve recipes with similar titles
            $RecipesLib = new RecipesLib($this->DB, $this->User);
            $this->responseSetContent($RecipesLib->title_check($title));
        }

        /**********************************************************
        DELETE
        ***********************************************************/

        /**
         * Request to delete a recipe
         */
        private function delete()
        {
            //Load and store parameters
            $recipe_id = (int)$this->Request->get('recipe_id');
            $this->responseSetParam('recipe_id', $recipe_id);

            //Retrieve and delete recipe picture
            $RecipesLib  = new RecipesLib($this->DB, $this->User);
            $PicturesLib = new PicturesLib($this->DB, $this->User);
            $pic_id = $RecipesLib->getPic($recipe_id);
            if($pic_id) 
                $PicturesLib->delete('recipes', $pic_id);

            //Delete recipe content
            $RecipesLib->delete($recipe_id);
        }
        
        /**********************************************************
        DISMISS
        ***********************************************************/

        /**
         * Request to dismiss a recipe
         */
        private function dismiss()
        {
            //Load and store parameters
            $recipe_id = (int)$this->Request->get('recipe_id');
            $this->responseSetParam('recipe_id', $recipe_id);
            
            //Dismiss recipe in library
            $RecipesLib = new RecipesLib($this->DB, $this->User);
            $RecipesLib->dismiss($recipe_id);
        }
        
        /**********************************************************
        EDIT
        ***********************************************************/
        
        /**
         * Request to edit an existing recipe
         */
        private function edit()
        {
            //Load and store parameters
            $recipe_id = (int)$this->Request->get('recipe_id');
            $recipe    = json_decode($this->Request->get('recipe'), true);
            $this->responseSetParam('recipe_id', $recipe_id);
            
            //Edit recipe
            $RecipesLib = new RecipesLib($this->DB, $this->User);
            $RecipesLib->edit($recipe_id, $recipe);
            
            //Load edited recipe data
            $recipe = $RecipesLib->load(array($recipe_id));
            $recipe = $recipe[0];
            $public = (int)$recipe['public'];
            $this->responseSetContent($recipe);

            //For public recipes
            if($public)
            {
                //Find related glossary keywords and insert links in cross table
                $Glossary = new Glossary($this->DB, $this->User);
                $keywords = $Glossary->match($recipe['desc'], $recipe['lang']);
                $Glossary->link($recipe_id, $keywords);
            }
        }
        
        /**********************************************************
        EXPRESS
        ***********************************************************/

        /**
         * Request for menu express recipes
         */
        private function express()
        {
            //Load and store parameters
            $criteria = json_decode($this->Request->get('criteria'), true);
            $starters = (int)$this->Request->get('starters');
            $mains    = (int)$this->Request->get('mains');
            $desserts = (int)$this->Request->get('desserts');
            $this->responseSetParam('criteria', $criteria);
            $this->responseSetParam('starters', $starters);
            $this->responseSetParam('mains', $mains);
            $this->responseSetParam('desserts', $desserts);

            //Load required recipes
            $RecipesLib = new RecipesLib($this->DB, $this->User);
            $recipes = array(
                'desserts'  => array(),
                'mains'     => array(),
                'starters'  => array()
            );
            $criteria['random'] = true;
            if($desserts)
            {
                $criteria['category'] = C::RECIPE_CATEGORY_DESSERT;
                $recipes['desserts']  = $RecipesLib->search($criteria, $desserts);
            }
            if($mains)
            {
                $criteria['category'] = C::RECIPE_CATEGORY_MAIN;
                $recipes['mains']     = $RecipesLib->search($criteria, $mains);
            }
            if($starters)
            {
                $criteria['category'] = C::RECIPE_CATEGORY_STARTER;
                $recipes['starters']  = $RecipesLib->search($criteria, $starters);
            }
            $this->responseSetContent($recipes);
        }
        
        /**********************************************************
        LIST
        ***********************************************************/
        
        /**
         * Request to list dismissed recipes
         */
        private function listDismiss()
        {
            //Load recipes content
            $RecipesLib = new RecipesLib($this->DB, $this->User);
            $dismissed = $RecipesLib->listDismiss();
			$this->responseSetContent($RecipesLib->load($dismissed, 'short'));
        }

        /**********************************************************
        LOAD
        ***********************************************************/

        /**
         * Request to retrieve recipes content
         */
        private function load()
        {
            //Load and store parameters
            $recipes_ids = json_decode($this->Request->get('recipes_ids'));
            $this->responseSetParam('recipes_ids', $recipes_ids);

            //Load recipes content
            $RecipesLib = new RecipesLib($this->DB, $this->User);
			$this->responseSetContent($RecipesLib->load($recipes_ids));
        }

        /**********************************************************
        RATE
        ***********************************************************/

        /**
         * Request to rate a recipe
         */
        private function rate()
        {
            //Load and store parameters
            $recipe_id = (int)$this->Request->get('recipe_id');
            $rating    = (int)$this->Request->get('rating');
            $this->responseSetParam('recipe_id', $recipe_id);
            $this->responseSetParam('rating', $rating);

            //Rate recipe
            $RecipesLib = new RecipesLib($this->DB, $this->User);
            list($rating_id, $new_rating) = $RecipesLib->rate($recipe_id, $rating);
            $this->responseSetParam('updated_rating', $new_rating);
            
            //Register event
            if($rating_id)
            {
                $EventsLib = new EventsLib($this->DB, $this->User);
                $EventsLib->register($this->User->getID(), EventsLib::TYPE_RATERECIPE, $public = false, $rating_id);
            }
        }

        /**********************************************************
        REPORT
        ***********************************************************/

        /**
         * Report recipe content as inappropriate
         */
        private function report()
        {
            //Load and store parameters
            $recipe_id = (int)$this->Request->get('recipe_id');
            $this->responseSetParam('recipe_id', $recipe_id);

            //Report recipe
            $RecipesLib = new RecipesLib($this->DB, $this->User);
			$RecipesLib->report($recipe_id);
        }

        /**********************************************************
        SAVE
        ***********************************************************/

        /**
         * Request to save a new recipe in database
         */
        private function save()
        {
            //Load and store parameters
            $recipe     = json_decode($this->Request->get('recipe'), true);
            $lang       = $this->Request->get('lang');
            $public     = (int)$this->Request->get('public');
            $partner_id = (int)$this->Request->get('partner_id');

            //Insert new recipe in database
            $RecipesLib = new RecipesLib($this->DB, $this->User);
            $recipe_id  = $RecipesLib->insert($recipe, $public, $lang, $this->User->isAdmin() ? $partner_id : 0);
            $this->responseSetParam('recipe_id', $recipe_id);

            //Load new recipe data
            $recipe = $RecipesLib->load(array($recipe_id));
            $recipe = $recipe[0];
            $public = (int)$recipe['public'];
            $this->responseSetContent($recipe);

            //For public recipes
            if($public)
            {
                //Find related glossary keywords and insert links in cross table
                $Glossary = new Glossary($this->DB, $this->User);
                $keywords = $Glossary->match($recipe['desc'], $lang);
                $Glossary->link($recipe_id, $keywords);

                //Register event
                $EventsLib = new EventsLib($this->DB, $this->User);
                $EventsLib->register($this->User->getID(), EventsLib::TYPE_ADDRECIPE, $public = true, $recipe_id, $lang);

                //Update user's grade
                $UsersLib   = new UsersLib($this->DB, $this->User);
                $pic_id     = (int)$recipe['pic'];
                $cookies    = $pic_id 
                            ? C::COOKIES_VALUE_RECIPE + C::COOKIES_VALUE_PICTURE
                            : C::COOKIES_VALUE_RECIPE;
                $new_grade  = $UsersLib->grade_update($this->User->getID(), $cookies, $action = 'add');
                if($new_grade !== false) 
                    $this->responseSetParam('new_grade', $new_grade);
            }

            //Send email confirmation to author
            if($this->User->options_get('email_recipe'))
            {
                $Email = new EmailHandler($this->DB);
                $Email->pattern(EmailHandler::TYPE_RECIPEADDED, array(
                    'content'       => 'text',
                    'recipient'     => $this->User->getEmail(),
                    'firstname'     => $this->User->getFirstname(),
                    'recipe_id'     => $recipe_id,
                    'recipe_name'   => $recipe['name'],
                    'public'        => $public
                ));
            }
        }

        /**
         * Request to save a new pic for an existing recipe
         */
        private function save_pic()
        {
            //Load and store parameters
            $recipe_id = (int)$this->Request->get('recipe_id');
            $pic_id    = (int)$this->Request->get('pic_id');
            $this->responseSetParam('recipe_id', $recipe_id);
            $this->responseSetParam('pic_id', $pic_id);

            //Save new recipe pic ID
            $RecipesLib = new RecipesLib($this->DB, $this->User);
            $RecipesLib->pic_save($recipe_id, $pic_id);

            //Update user's grade
            $UsersLib  = new UsersLib($this->DB, $this->User);
            $new_grade = $UsersLib->grade_update($this->User->getID(), C::COOKIES_VALUE_PICTURE, 'add');
            $this->responseSetParam('user_grade', $new_grade);
        }

        /**********************************************************
        SEARCH
        ***********************************************************/

        /**
         * Request to search for recipes IDs matching a given set of criteria
         */
        private function search()
        {
            //Load and store parameters
            $criteria  = $this->Request->get('criteria');
            $criteria  = $criteria ? json_decode($criteria, true) : array();
            $timestamp = $this->Request->get('timestamp');
            $this->responseSetParam('criteria', $criteria);
            $this->responseSetParam('timestamp', $timestamp);

            //Search for matching recipes
            $RecipesLib = new RecipesLib($this->DB, $this->User);
            $this->responseSetContent($RecipesLib->search($criteria));
            $this->responseSetParam('overflow', $RecipesLib->searchOverflow());
        }

        /**********************************************************
        TRANSLATE
        ***********************************************************/

        /**
         * Request to save a recipe translation
         */
        private function translate()
        {
            //Load and store parameters
            $recipe_id   = (int)$this->Request->get('recipe_id');
            $translation = json_decode($this->Request->get('translation'), true);
            $lang        = $this->Request->get('lang');
            $this->responseSetParam('recipe_id', $recipe_id);
            $this->responseSetParam('lang', $lang);

            //Store recipe translation
            $RecipesLib = new RecipesLib($this->DB, $this->User);
            $translate_id = $RecipesLib->translate($recipe_id, $translation, $lang);
            $this->responseSetParam('translated_id', $translate_id);
            
            //Update user's grade
            $UsersLib  = new UsersLib($this->DB, $this->User);
            $new_grade = $UsersLib->grade_update($this->User->getID(), C::COOKIES_VALUE_TRANSLATE, 'add');
            $this->responseSetParam('user_grade', $new_grade);
        }
        
        /**********************************************************
        VALIDATE
        ***********************************************************/
        
        /**
         * Request to validate a dismissed recipe
         */
        private function validate()
        {
            //Load and store parameters
            $recipe_id = (int)$this->Request->get('recipe_id');
            $this->responseSetParam('recipe_id', $recipe_id);
            
            //Validate recipe
            $RecipesLib = new RecipesLib($this->DB, $this->User);
            $RecipesLib->validate($recipe_id);
        }
    }
?>