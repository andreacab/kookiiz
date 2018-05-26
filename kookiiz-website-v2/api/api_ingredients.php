<?php
    /*******************************************************
    Title: API ingredients
    Authors: Kookiiz Team
    Purpose: API module for ingredients-related actions
    ********************************************************/

    //Dependencies
    require_once '../api/api.php';
    require_once '../class/ingredients_db.php';

    //Represents an API handler for ingredients-related actions
    class IngredientsAPI extends KookiizAPIHandler
    {
        /**********************************************************
        PROPERTIES
        ***********************************************************/

        const MODULE = 'ingredients';

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
                case 'load':
                    $this->load();
                    break;
                case 'season_create':
                    $this->season_create();
                    break;
            }
        }

        /**********************************************************
        LOAD
        ***********************************************************/

        //Request to load ingredients database
        //-> (void)
        private function load()
        {
            //Load database
            $IngredientsDB = new IngredientsDB($this->DB, $this->User->getLang());
            $this->responseSetContent($IngredientsDB->export());
        }

        /**********************************************************
        SEASON
        ***********************************************************/

        //Request to create an ingredient-month pair
        //-> (void)
        private function season_create()
        {
            //Load and store parameters
            $month          = (int)$this->Request->get('month');
            $ingredient_id  = (int)$this->Request->get('ingredient_id');
            $this->responseSetParam('month', $month);
            $this->responseSetParam('ingredient_id', $ingredient_id);

            //Set season ingredient
            $IngredientsDB = new IngredientsDB($this->DB, $this->User->getLang());
            $IngredientsDB->seasonSet($month, $ingredient_id);
        }
    }
?>