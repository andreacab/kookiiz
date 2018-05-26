<?php
    /*******************************************************
    Title: Feedback
    Authors: Kookiiz Team
    Purpose: Manage user feedbacks
    ********************************************************/

    //Dependencies
    require_once '../class/dblink.php';
    require_once '../class/lang_db.php';
    require_once '../class/user.php';

    //Represents a handler to save and load feedbacks
    class FeedbackHandler
    {
        /**********************************************************
        PROPERTIES
        ***********************************************************/
        
        private $DB;        //Database handler
        private $Lang;      //Language handler
        private $User;      //Connected user

        /**********************************************************
        CONSTRUCTOR
        ***********************************************************/

        /**
         * Class constructor
         * @param DBLink $DB database handler
         * @param User $User connected user
         */
        public function __construct(DBLink &$DB, User &$User)
        {
            //Start session
            Session::start();

            //Set up properties
            $this->DB   = $DB;
            $this->Lang = LangDB::getHandler(Session::getLang());
            $this->User = $User;
        }
        
        /**********************************************************
        DELETE
        ***********************************************************/
        
        /**
         * Delete a feedback entry
         * @param Int $id feedback entry ID
         */
        public function delete($id)
        {
            $request = 'DELETE FROM feedback WHERE feedback_id = ?';
            $this->DB->query($request, array($id));
        }
        
        /**********************************************************
        LOAD
        ***********************************************************/

        /**
         * Load feedbacks
         * @param Int $type feedback type ID (optional)
         * @param Int $count number of feedback to display (defaults to 0 = all)
         * @return Array list of feedback objects
         */
        public function load($type = -1, $count = 0)
        {
            $feedbacks = array();

            $request = "SELECT user_id, IF(name IS NOT NULL, name, 'guest') AS name,"
                        . ' feedback_id AS id, feedback.type, feedback.content, feedback.text,'
                        . ' UNIX_TIMESTAMP(date) AS date'
                    . ' FROM feedback'
                        . ' NATURAL LEFT JOIN users';
            $params = array();
            if($type >= 0)
            {
                $request .= ' WHERE type = ?';
                $params[] = $type;
            }
            if($count > 0)
            {
                $request .= ' ORDER BY date DESC LIMIT ?';
                $params[] = $count;
            }
            else
                $request .= ' ORDER BY date DESC';
            $stmt = $this->DB->query($request, $params);
            
            while($feedback = $stmt->fetch())
            {
                $feedbacks[] = array(
                    'id'        => (int)$feedback['id'],
                    'user_id'   => (int)$feedback['user_id'],
                    'user_name' => htmlspecialchars($feedback['name'], ENT_COMPAT, 'UTF-8'),
                    'type'      => (int)$feedback['type'],
                    'content'   => htmlspecialchars($feedback['content'], ENT_COMPAT, 'UTF-8'),
                    'text'      => htmlspecialchars($feedback['text'], ENT_COMPAT, 'UTF-8'),
                    'date'      => date('d.m.Y', (int)$feedback['date']),
                    'time'      => date('H:i', (int)$feedback['date'])
                );
            }
            return $feedbacks;
        }

        /**********************************************************
        QUESTION
        ***********************************************************/

        /**
         * Save a quick feedback and load next question (if available)
         * @param Int $question_id ID of the question (can be -1 to load a first question)
         * @param Int $answer yes (1) or no (0)
         * @return Int new question ID (-1 if all questions have been answered)
         */
        public function question($question_id, $answer)
        {
            if($question_id >= 0)
            {
                //Insert or update question answer
                $request = 'INSERT INTO feedback_quick (question_id, answer, user_id)'
                            . ' VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE answer = ?';
                $params = array($question_id, $answer, $this->User->getID(), $answer);
                $this->DB->query($request, $params);
            }

            //Store list of available and enabled questions
            $request = 'SELECT question_id AS id FROM feedback_enable WHERE enabled = 1';
            $stmt = $this->DB->query($request);
            $questions_available = array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN, 0));

            //Retrieve already answered questions for this user (only for logged users)
            $questions_answered = array();
            if($this->User->isLogged())
            {
                $request = 'SELECT question_id FROM feedback_quick WHERE user_id = ?';
                $stmt = $this->DB->query($request, array($this->User->getID()));
                $questions_answered = array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN, 0));
            }
            else
                $questions_answered[] = $question_id;

            //Compute list of non-answered questions and select one randomly
            $questions_left = array_diff($questions_available, $questions_answered);
            if(count($questions_left))
            {
                shuffle($questions_left);
                return $questions_left[0];
            }
            else return -1;
        }

        /**
         * Skip a question
         * @param Int $question_id ID of the question to skip
         */
        public function question_skip($question_id)
        {
            $request = 'INSERT IGNORE INTO feedback_quick (question_id, answer, user_id, skip)'
                        . ' VALUES (?, 0, ?, 1)';
            $this->DB->query($request, array($question_id, $this->User->getID()));
        }
        
        /**
         * Enable provided questions
         * @param Array $questions list of questions IDs
         */
        public function questionsEnable(array $questions)
        {
            //Disable all questions
            $request = 'UPDATE feedback_enable SET enabled = 0';
            $this->DB->query($request);
            
            //Enable specified questions
            $params = array();
            $request = 'INSERT INTO feedback_enable (question_id, enabled)'
                        . ' VALUES (?, 1) ON DUPLICATE KEY UPDATE enabled = 1';
            foreach($questions as $id) $params[] = array($id);
            $this->DB->query($request, $params);
        }
        
        /**********************************************************
        SAVE
        ***********************************************************/

        /**
         * Save new feedback in database
         * @param Int $type feedback type ID
         * @param String $content related content description
         * @param String $text feedback comment
         * @param String $browser content of "user-agent" server string
         */
        public function save($type, $content, $text, $browser)
        {
            //Store new feedback
            $request = 'INSERT INTO feedback (user_id, type, content, text, browser)'
                        . ' VALUES (:user_id, :type, :content, :text, :browser)';
            $stmt = $this->DB->query($request, array(
                ':user_id'  => $this->User->getID(),
                ':type'     => $type,
                ':content'  => $content,
                ':text'     => $text,
                ':browser'  => $browser
            ));

            //Failed to save feedback
            if(!$stmt->rowCount()) 
                throw new KookiizException('feedback', 1);
        }

        /**********************************************************
        STATISTICS
        ***********************************************************/

        /**
         * Load feedback statistics
         * @return Array stats per question ID
         */
        public function statistics()
        {
            $stats = array();
            $request = 'SELECT question_id, SUM(answer) as yes_count,'
                        . ' COUNT(*) as total, IF(enabled, enabled, 0) AS enabled'
                    . ' FROM feedback_quick'
                        . ' LEFT JOIN feedback_enable USING (question_id)'
                    . ' WHERE skip = 0'
                    . ' GROUP BY question_id ORDER BY question_id';
            $stmt = $this->DB->query($request);
            while($question = $stmt->fetch())
            {
                $stats[] = array(
                    'id'        => (int)$question['question_id'],
                    'yes'       => (int)$question['yes_count'],
                    'total'     => (int)$question['total'],
                    'enabled'   => (int)$question['enabled']
                );
            }
            return $stats;
        }
    }
?>
