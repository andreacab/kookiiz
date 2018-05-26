<?php
    /*******************************************************
    Title: API glossary
    Authors: Kookiiz Team
    Purpose: API module for glossary-related actions
    ********************************************************/

    //Dependencies
    require_once '../api/api.php';
    require_once '../class/glossary.php';

    //Represents an API handler for glossary-related actions
    class GlossaryAPI extends KookiizAPIHandler
    {
        /**********************************************************
        PROPERTIES
        ***********************************************************/

        const MODULE = 'glossary';

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
                case 'add':
                    $this->add();
                    break;
                case 'delete':
                    $this->delete();
                    break;
                case 'edit':
                    $this->edit();
                    break;
                case 'search':
                    $this->search();
                    break;
                case 'search_recipe':
                    $this->search_recipe();
                    break;
            }
        }

        /**********************************************************
        ADD
        ***********************************************************/

        //Request to add a term to the glossary
        //-> (void)
        private function add()
        {
            //Load and store parameters
            $keyword    = $this->Request->get('keyword');
            $definition = $this->Request->get('definition');
            $lang       = $this->Request->get('lang');

            //Add new glossary term
            $Glossary = new Glossary($this->DB, $this->User);
            $Glossary->add($keyword, $definition, $lang);
        }

        /**********************************************************
        DELETE
        ***********************************************************/

        //Request to delete a term from the glossary
        //-> (void)
        private function delete()
        {
            //Load and store parameters
            $keyword_id = (int)$this->Request->get('keyword_id');

            //Delete glossary term
            $Glossary = new Glossary($this->DB, $this->User);
            $Glossary->delete($keyword_id);
        }

        /**********************************************************
        EDIT
        ***********************************************************/

        //Request to add an existing term of the glossary
        //-> (void)
        private function edit()
        {
            //Load and store parameters
            $keyword_id = (int)$this->Request->get('keyword_id');
            $definition = $this->Request->get('definition');
            $lang       = $this->Request->get('lang');

            //Edit glossary term
            $Glossary = new Glossary($this->DB, $this->User);
            $Glossary->edit($keyword_id, $definition, $lang);
        }

        /**********************************************************
        SEARCH
        ***********************************************************/

        //Request to search glossary terms matching a given keyword
        //-> (void)
        private function search()
        {
            //Load and store parameters
            $keyword = $this->Request->get('keyword');
            $this->responseSetParam('keyword', $keyword);

            //Search glossary
            $Glossary = new Glossary($this->DB, $this->User);
            $this->responseSetContent($Glossary->search($keyword));
        }

        //Request to search glossary terms related to a recipe
        //-> (void)
        private function search_recipe()
        {
            //Load and store parameters
            $recipe_id = (int)$this->Request->get('recipe_id');
            $this->responseSetParam('recipe_id', $recipe_id);

            //Search glossary
            $Glossary = new Glossary($this->DB, $this->User);
            $this->responseSetContent($Glossary->searchRecipe($recipe_id));
        }
    }
?>