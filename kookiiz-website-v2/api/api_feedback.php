<?php
    /*******************************************************
    Title: API feedback
    Authors: Kookiiz Team
    Purpose: API module for feedback-related actions
    ********************************************************/

    //Dependencies
    require_once '../api/api.php';
    require_once '../class/feedback.php';

    //Represents an API handler for feedback-related actions
    class FeedbackAPI extends KookiizAPIHandler
    {
        /**********************************************************
        PROPERTIES
        ***********************************************************/

        const MODULE = 'feedback';

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
                case 'enable':
                    $this->enable();
                    break;
                case 'load':
                    $this->load();
                    break;
                case 'question':
                    $this->question();
                    break;
                case 'save':
                    $this->save();
                    break;
                case 'stats':
                    $this->stats();
                    break;
            }
        }
        
        /**********************************************************
        DELETE
        ***********************************************************/
        
        /**
         * Delete a feedback entry
         */
        private function delete()
        {
            //Load and store parameters
            $id = (int)$this->Request->get('feedback_id');
            $this->responseSetParam('feedback_id', $id);
            
            //Delete feedback entry
            $Feedback = new FeedbackHandler($this->DB, $this->User);
            $Feedback->delete($id);
        }
        
        /**********************************************************
        ENABLE
        ***********************************************************/
        
        /**
         * Request to enable specific questions
         */
        private function enable()
        {
            //Load and store parameters
            $questions = json_decode($this->Request->get('questions'), true);
            $this->responseSetParam('questions', $questions);
            
            //Enable provided questions
            $Feedback = new FeedbackHandler($this->DB, $this->User);
            $Feedback->questionsEnable($questions);
        }

        /**********************************************************
        LOAD
        ***********************************************************/

        //Request to load feedback data
        //-> (void)
        private function load()
        {
            //Load and store parameters
            $type   = (int)$this->Request->get('type');
            $count  = (int)$this->Request->get('count');
            $this->responseSetParam('type', $type);
            $this->responseSetParam('count', $count);

            //Load feedback data
            $Feedback = new FeedbackHandler($this->DB, $this->User);
            $this->responseSetContent($Feedback->load($type, $count));
        }

        /**********************************************************
        QUESTION
        ***********************************************************/

        //Request for a new feedback question
        //-> (void)
        private function question()
        {
            //Load and store parameters
            $question_id = (int)$this->Request->get('question_id');
            $answer      = (int)$this->Request->get('answer');
            $skip        = (int)$this->Request->get('skip');
            $this->responseSetParam('old_question', $question_id);
            $this->responseSetParam('answer', $answer);
            $this->responseSetParam('skip', $skip);

            //Save answer and load next question
            $Feedback = new FeedbackHandler($this->DB, $this->User);
            if($skip && $this->User->isLogged())
                $Feedback->question_skip($question_id);
            $this->responseSetParam('new_question', $Feedback->question($question_id, $answer));
        }

        /**********************************************************
        SAVE
        ***********************************************************/

        //Request to save a new feedback
        //-> (void)
        private function save()
        {
            //Load and store parameters
            $type       = (int)$this->Request->get('type');
            $content    = $this->Request->get('content');
            $text       = $this->Request->get('text');
            $browser    = $_SERVER['HTTP_USER_AGENT'];
            $this->responseSetParam('type', $type);
            $this->responseSetParam('content', $content);
            $this->responseSetParam('text', $text);

            //Save new feedback
            $Feedback = new FeedbackHandler($this->DB, $this->User);
            $Feedback->save($type, $content, $text, $browser);
        }

        /**********************************************************
        STATS
        ***********************************************************/

        //Request stats on feedback questions
        //-> (void)
        private function stats()
        {
            //Load statistics
            $Feedback = new FeedbackHandler($this->DB, $this->User);
            $this->responseSetContent($Feedback->statistics());
        }
    }
?>