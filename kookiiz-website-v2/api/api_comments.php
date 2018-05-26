<?php
    /*******************************************************
    Title: API comments
    Authors: Kookiiz Team
    Purpose: API module for comments-related actions
    ********************************************************/

    //Dependencies
    require_once '../api/api.php';
    require_once '../class/comments_lib.php';
    require_once '../class/events_lib.php';
    require_once '../class/users_lib.php';

    //Represents an API handler for comments-related actions
    class CommentsAPI extends KookiizAPIHandler
    {
        /**********************************************************
        PROPERTIES
        ***********************************************************/

        const MODULE = 'comments';

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
                    $this->edit();
                    break;
                case 'load':
                    $this->load();
                    break;
                case 'rate':
                    $this->rate();
                    break;
                case 'save':
                    $this->save();
                    break;
            }
        }
        
        /**********************************************************
        DELETE
        ***********************************************************/

        //Request to delete a comment
        //-> (void)
        private function delete()
        {
            //Load and store parameters
            $id             = (int)$this->Request->get('id');
            $content_type   = $this->Request->get('content_type');
            $this->responseSetParam('id', $id);
            $this->responseSetParam('content_type', $content_type);

            //Delete comment from library
            $CommentsLib = new CommentsLib($this->DB, $this->User);
            $CommentsLib->delete($content_type, $id);
        }

        /**********************************************************
        EDIT
        ***********************************************************/

        //Request to edit an existing comment
        //-> (void)
        private function edit()
        {
            //Load and store parameters
            $id             = (int)$this->Request->get('id');
            $text           = $this->Request->get('text');
            $content_type   = $this->Request->get('content_type');

            //Edit comment in library
            $CommentsLib = new CommentsLib($this->DB, $this->User);
            $CommentsLib->edit($content_type, $id, $text);
        }

        /**********************************************************
        LOAD
        ***********************************************************/

        //Request to load comments
        //-> (void)
        private function load()
        {
            //Load and store parameters
            $content_type   = $this->Request->get('content_type');
            $content_id     = (int)$this->Request->get('content_id');
            $type           = (int)$this->Request->get('type');
            $count          = (int)$this->Request->get('count');
            $page           = (int)$this->Request->get('page');
            $this->responseSetParam('content_type', $content_type);
            $this->responseSetParam('content_id', $content_id);
            $this->responseSetParam('type', $type);
            $this->responseSetParam('count', $count);
            $this->responseSetParam('page', $page);

            //Load comments from database
            $CommentsLib = new CommentsLib($this->DB, $this->User);
            $comments = $CommentsLib->load($content_type, $content_id, $type, $count, $page);
            $this->responseSetContent($comments);
        }

        /**********************************************************
        RATE
        ***********************************************************/

        //Request to rate a comment
        //-> (void)
        private function rate()
        {
            //Load and store parameters
            $id             = (int)$this->Request->get('id');
            $rating         = (int)$this->Request->get('rating');
            $content_type   = $this->Request->get('content_type');
            $this->responseSetParam('id', $id);
            $this->responseSetParam('rating', $rating);
            $this->responseSetParam('content_type', $content_type);

            //Rate comment in library
            $CommentsLib = new CommentsLib($this->DB, $this->User);
            $CommentsLib->rate($content_type, $id, $rating);

            //Update grade of comment's author (add/remove cookies)
            $UsersLib = new UsersLib($this->DB, $this->User);
            if($rating) $UsersLib->grade_update($author_id, C::COOKIES_VALUE_COMMENT, 'add');
            else        $UsersLib->grade_update($author_id, C::COOKIES_VALUE_COMMENT, 'remove');
        }

        /**********************************************************
        SAVE
        ***********************************************************/

        //Request to save a new comment
        //-> (void)
        private function save()
        {
            //Load and store parameters
            $text           = $this->Request->get('text');
            $content_type   = $this->Request->get('content_type');
            $content_id     = (int)$this->Request->get('content_id');
            $type           = (int)$this->Request->get('type');
            $count          = (int)$this->Request->get('count');
            $page           = (int)$this->Request->get('page');
            $this->responseSetParam('content_type', $content_type);
            $this->responseSetParam('content_id', $content_id);
            $this->responseSetParam('type', $type);
            $this->responseSetParam('count', $count);
            $this->responseSetParam('page', $page);

            //Save comment
            $CommentsLib = new CommentsLib($this->DB, $this->User);
            $comment_id = $CommentsLib->save($content_type, $content_id, $type, $text);

            //Register an event for public comments
            if($type == C::COMMENT_TYPE_PUBLIC)
            {
                //Register event
                $event_type = -1;
                if($content_type == 'article')      $event_type = EventsLib::TYPE_COMMENTARTICLE;
                else if($content_type == 'recipe')  $event_type = EventsLib::TYPE_COMMENTRECIPE;
                if($event_type >= 0)
                {
                    $EventsLib = new EventsLib($this->DB, $this->User);
                    $EventsLib->register($this->User->getID(), $event_type, $public = false, $comment_id);
                }
            }

            //Reload comments
            $comments = $CommentsLib->load($content_type, $content_id, $type, $count, $page);
            $this->responseSetContent($comments);
        }
    }
?>
