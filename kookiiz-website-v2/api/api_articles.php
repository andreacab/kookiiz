<?php
    /*******************************************************
    Title: API articles
    Authors: Kookiiz Team
    Purpose: API module for articles-related actions
    ********************************************************/

    //Dependencies
    require_once '../api/api.php';
    require_once '../class/articles_lib.php';

    //Represents an API handler for articles-related actions
    class ArticlesAPI extends KookiizAPIHandler
    {
        /**********************************************************
        CONSTANTS
        ***********************************************************/

        const MODULE = 'articles';

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
                case 'delete':
                    $this->delete();
                    break;
                case 'edit':
                case 'save':
                    $this->edit();
                    break;
                case 'history':
                    $this->history();
                    break;
                case 'load':
                    $this->load();
                    break;
                case 'search':
                    $this->search();
                    break;
            }
        }

        /**********************************************************
        DELETE
        ***********************************************************/

        //Request to delete an article from database
        //-> (void)
        private function delete()
        {
            //Load and store parameters
            $article_id = (int)$this->Request->get('article_id');
            $this->responseSetParam('article_id', $article_id);

            //Delete article from database
            $ArticlesLib = new ArticlesLib($this->DB, $this->User);
            $ArticlesLib->delete($article_id);
        }

        /**********************************************************
        EDIT
        ***********************************************************/

        //Request to edit or save an article
        //-> (void)
        private function edit()
        {
            //Load and store parameters
            $article_id = (int)$this->Request->get('article_id');
            $type       = (int)$this->Request->get('type');
            $title      = $this->Request->get('title');
            $text       = $this->Request->get('text');
            $source     = (int)$this->Request->get('source');
            $keywords   = implode(', ', json_decode($this->Request->get('keywords')));
            $pics       = array_map('intval', json_decode($this->Request->get('pics')));
            $captions   = json_decode($this->Request->get('captions'));

            //Save article in database
            $ArticlesLib = new ArticlesLib($this->DB, $this->User);
            if($article_id)
            {
                $ArticlesLib->edit($article_id, $type, $source, $title, $text, $keywords, $pics, $captions);
                $this->responseSetParam('article_id', $article_id);
            }
            else
            {
                $article_id = $ArticlesLib->insert($type, $source, $title, $text, $keywords, $pics, $captions, $lang);
                $this->responseSetParam('article_id', $article_id);
            }
        }

        /**********************************************************
        HISTORY
        ***********************************************************/

        //Request for most recent articles
        //-> (void)
        private function history()
        {
            //Retrieve articles history
            $ArticlesLib = new ArticlesLib($this->DB, $this->User);
            $this->responseSetContent($ArticlesLib->history());
        }

        /**********************************************************
        LOAD
        ***********************************************************/

        //Request for the content of a specific article
        //-> (void)
        private function load()
        {
            //Load and store parameters
            $article_id = (int)$this->Request->get('article_id');
            $this->responseSetParam('article_id', $article_id);

            //Retrieve article content
            $ArticlesLib = new ArticlesLib($this->DB, $this->User);
            $this->responseSetContent($ArticlesLib->load($article_id));
        }

        /**********************************************************
        SEARCH
        ***********************************************************/

        //Request for articles of a given type matching provided keyword
        //-> (void)
        private function search()
        {
            //Load and store parameters
            $type       = (int)$this->Request->get('type');
            $keyword    = $this->Request->get('keyword');
            $this->responseSetParam('type', $type);
            $this->responseSetParam('keyword', $keyword);

            //Throw article search
            $ArticlesLib = new ArticlesLib($this->DB, $this->User);
            $this->responseSetContent($ArticlesLib->search($type, $keyword));
        }
    }
?>