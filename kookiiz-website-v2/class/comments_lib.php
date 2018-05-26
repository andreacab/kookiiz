<?php
    /*******************************************************
    Title: Comments library
    Authors: Kookiiz Team
    Purpose: Store and manage users comments
    ********************************************************/

    //Dependencies
    require_once '../class/dblink.php';
    require_once '../class/exception.php';
    require_once '../class/globals.php';
    require_once '../class/user.php';

    //Represents a library of comments
    class CommentsLib
    {
        /**********************************************************
        CONSTANTS
        ***********************************************************/

        //Class constants
        const POST_DELAY    = 30;   //Delay (in seconds) between two posts from the same user
        const PRIVATE_MAX   = 5;    //Max number of private comments per user and content
        const RATING_MIN    = -20;  //Rating threshold for automatic comment deletion

        //Globals
        const LENGTH_MAX    = C::COMMENT_LENGTH_MAX;
        const LENGTH_MIN    = C::COMMENT_LENGTH_MIN;
        const TYPE_PRIVATE  = C::COMMENT_TYPE_PRIVATE;
        const TYPE_PUBLIC   = C::COMMENT_TYPE_PUBLIC;

        /**********************************************************
        PROPERTIES
        ***********************************************************/

        private $DB;
        private $User;

        /**********************************************************
        CONSTRUCTOR
        ***********************************************************/

        /**
         * Class constructor
         * @param DBLink $DB open database connection
         * @param User $User connected user
         */
        public function __construct(DBLink &$DB, User &$User)
        {
            $this->DB   = $DB;
            $this->User = $User;
        }

        /**********************************************************
        DELETE
        ***********************************************************/
        
        /**
         * Delete existing comment (private notes only)
         * @param String $content_type type of content (article, recipe) the comment is related to
         * @param Int $id unique ID of the comment
         */
        public function delete($content_type, $id)
        {
            //Find appropriate comments table
            if($content_type == 'article')      
                $table = 'articles_comments';
            else if($content_type == 'recipe')  
                $table = 'recipes_comments';

            //Delete comment (comment and user IDs must match, comment must be private)
            $request = "DELETE FROM $table"
                    . ' WHERE comment_id = ? AND user_id = ?'
                        . ' AND comment_type = ' . self::TYPE_PRIVATE;
            $stmt = $this->DB->query($request, array($id, $this->User->getID()));

            //Comment deletion failed
            if(!$stmt->rowCount()) 
                throw new KookiizException('comments', 5);
        }
        
        /**********************************************************
        EDIT
        ***********************************************************/

        /**
         * Edit existing comment (private notes only)
         * @param String $content_type type of content (article, recipe) the comment is related to
         * @param Int $id unique ID of the comment
         * @param String $text edited text of the comment
         */
        function edit($content_type, $id, $text)
        {
            //Check comment text length
            if(strlen($text) < self::LENGTH_MIN) 
                throw new KookiizException('comments', 6);
            if(strlen($text) > self::LENGTH_MAX) 
                throw new KookiizException('comments', 7);

            //Find appropriate comments table
            if($content_type == 'article')      
                $table = 'articles_comments';
            else if($content_type == 'recipe')  
                $table = 'recipes_comments';

            //Edit comment (content, comment and user IDs must match)
            //The comment must be of "private" type (personal note)
            $request = "UPDATE $table"
                        . ' SET comment_text = ?'
                    . ' WHERE comment_id = ? AND user_id = ?'
                        . ' AND comment_type = ' . self::TYPE_PRIVATE;
            $params = array($text, $id, $this->User->getID());
            $stmt = $this->DB->query($request, $params);

            //Comment could not be edited
            if(!$stmt->rowCount()) 
                throw new KookiizException('comments', 4);
        }
        
        /**********************************************************
        LOAD
        ***********************************************************/

        /**
         * Load comments from database
         * @param String $content_type type of content (article, recipe) the comments are related to
         * @param Int $content_id ID of content (article, recipe) the comments are related to
         * @param Int $type type of comment (private/public)
         * @param Int $count number of comments per page
         * @param Int $page current page
         * @return Array list of comments
         */
        public function load($content_type, $content_id, $type, $count, $page)
        {
            $comments = array(
                'data'  => array(),
                'total' => 0
            );

            //Find appropriate comments table
            if($content_type == 'article')      
                $table = 'articles_comments';
            else if($content_type == 'recipe')  
                $table = 'recipes_comments';

            //Request to retrieve comments content
            $request = 'SELECT comment_id, content_id, users.user_id, name, comment_text, comment_type,'
                        . ' comment_rating, UNIX_TIMESTAMP(comment_date) AS comment_date'
                    . " FROM $table"
                        . ' NATURAL JOIN users';
            $params = array();
            //Request to count comments
            $request_total = "SELECT 1 FROM $table NATURAL JOIN users";
            $params_total = array();

            //Restrict to public comments OR private comments from current user
            if($type == self::TYPE_PUBLIC)
            {
                $request .= ' WHERE comment_type = ?';
                $params[] = $type;
                $request_total .= ' WHERE comment_type = ?';
                $params_total[] = $type;
            }
            else
            {
                $request .= ' WHERE users.user_id = ? AND comment_type = ?';
                $params[] = $this->User->getID();
                $params[] = $type;
                $request_total .= ' WHERE users.user_id = ? AND comment_type = ?';
                $params_total[] = $this->User->getID();
                $params_total[] = $type;
            }

            //Restrict to valid comments
            $request .= ' AND valid = 1';
            $request_total .= ' AND valid = 1';

            //Specify for which recipe/article we have to retrieve comments
            $request .= ' AND content_id = ?';
            $params[] = $content_id;
            $request_total .= ' AND content_id = ?';
            $params_total[] = $content_id;

            //Take most recents first and limit total number (for public comments only)
            $request .= ' ORDER BY comment_date DESC';
            if($type == self::TYPE_PUBLIC)
            {
                $request .= " LIMIT ?, ?";
                $params[] = $page * $count;
                $params[] = $count;
            }

            //Loop through comments
            $stmt = $this->DB->query($request, $params);
            while($comment = $stmt->fetch())
            {
                $comments['data'][] = array(
                    'id'    => (int)$comment['comment_id'],
                    'type'  => (int)$comment['comment_type'],
                    'user'  => array(
                        'id'    => (int)$comment['user_id'],
                        'name'  => htmlspecialchars($comment['name'], ENT_COMPAT, 'UTF-8')
                    ),
                    'text'  => htmlspecialchars($comment['comment_text'], ENT_COMPAT, 'UTF-8'),
                    'rate'  => (int)$comment['comment_rating'],
                    'date'  => date('d.m.Y', $comment['comment_date']),
                    'time'  => date('H:i', $comment['comment_date'])
                );
            }

            //Compute total comments
            $stmt = $this->DB->query($request_total, $params_total);
            $comments['total'] = count($stmt->fetchAll());

            //Return comments
            return $comments;
        }
        
        /**********************************************************
        RATE
        ***********************************************************/

        /**
         * Rate an existing comment
         * @param String $content_type type of content (article, recipe) the comment is related to
         * @param Int $id unique ID of the comment
         * @param Int $rating negative or positive evaluation (0 or 1)
         */
        public function rate($content_type, $id, $rating)
        {
            //Find appropriate comments table
            if($content_type == 'article')     
                $table = 'articles_comments';
            else if($content_type == 'recipe') 
                $table = 'recipes_comments';

            //Retrieve comment information
            $request = 'SELECT user_id, content_id'
                    . " FROM $table"
                    . ' WHERE comment_id = ?';
            $stmt = $this->DB->query($request, array($id));
            $data = $stmt->fetch();
            if($data)
            {
                $author_id  = (int)$data['user_id'];
                $content_id = (int)$data['content_id'];
            }
            else 
                throw new KookiizException('comments', 9);

            //Check that user is not rating his own comment
            if($user_id == $author_id) 
                throw new KookiizException('comments', 2);

            //Insert new rating in table or update existing one
            $request = "INSERT INTO $table" . "_ratings (comment_id, user_id, content_id, rating)"
                        . ' VALUES(?, ?, ?, ?) ON DUPLICATE KEY UPDATE rating = ?, rating_date = NOW()';
            $params = array($id, $this->User->getID(), $content_id, $rating, $rating);
            $this->DB->query($request, $params);

            //Compute new comment rating
            $request = 'SELECT SUM(IF(rating, 1, -1)) AS rating'
                    . " FROM $table" . "_ratings"
                    . ' WHERE comment_id = ?'
                    . ' GROUP BY comment_id';
            $stmt = $this->DB->query($request, array($id));
            $data = $stmt->fetch();

            //Check if updated comment rating is above critical threshold
            $new_rating = (int)$data['rating'];
            if($new_rating >= self::RATING_MIN)
            {
                //Update comment's global rating
                $request = "UPDATE $table"
                            . ' SET comment_rating = ?'
                        . ' WHERE comment_id = ?';
                $this->DB->query($request, array($new_rating, $id));
            }
            //Comment rating is too low
            else
            {
                //Flag comment as invalid
                $request = "UPDATE $table"
                            . ' SET valid = 0'
                        . ' WHERE comment_id = ?';
                $this->DB->query($request, array($id));
            }
        }

        /**********************************************************
        SAVE
        ***********************************************************/

        /**
         * Save a comment in database
         * @param String $content_type type of content (article, recipe) the comment is related to
         * @param Int $content_id ID of content (article, recipe) the comment is related to
         * @param Int $type comment type (private/public)
         * @param String $text comment text
         * @return Int new comment ID
         */
        public function save($content_type, $content_id, $type, $text)
        {
            //Check comment text length
            if(strlen($text) < self::LENGTH_MIN) 
                throw new KookiizException('comments', 6);
            if(strlen($text) > self::LENGTH_MAX) 
                throw new KookiizException('comments', 7);

            //Find appropriate comments table
            if($content_type == 'article')      
                $table = 'articles_comments';
            else if($content_type == 'recipe')  
                $table = 'recipes_comments';

            //Check time between now and last comment from this user (only for public comments)
            if($type == self::TYPE_PUBLIC)
            {
                $request = 'SELECT UNIX_TIMESTAMP(comment_date) AS date'
                        . " FROM $table"
                        . ' WHERE user_id = ?'
                        . ' ORDER by date DESC LIMIT 1';
                $stmt = $this->DB->query($request, array($this->User->getID()));
                $data = $stmt->fetch();
                if($data)
                {
                    //Throw error if another comment from this user was posted very recently
                    $date = $data['date'];
                    if(time() - $date < self::POST_DELAY)
                        throw new KookiizException('comments', 1);
                }
            }

            //Check how many private comments were added by this user for this content
            if($type == self::TYPE_PRIVATE)
            {
                $request = 'SELECT 1'
                        . " FROM $table"
                        . ' WHERE content_id = ?'
                            .' AND user_id = ?'
                            . 'AND comment_type = ?';
                $params = array($content_id, $this->User->getID(), $type);
                $stmt = $this->DB->query($request, $params);
                $count = count($stmt->fetchAll(PDO::FETCH_COLUMN, 0));
                if($count >= self::PRIVATE_MAX)
                    throw new KookiizException('comments', 3);
            }

            //Save comment in database
            $request = "INSERT INTO $table (content_id, user_id, comment_text, comment_type, comment_date)"
                        . ' VALUES (:content_id, :user_id, :text, :type, NOW())';
            $stmt = $this->DB->query($request, array(
                ':content_id'   => $content_id,
                ':user_id'      => $this->User->getID(),
                ':text'         => $text,
                ':type'         => $type
            ));

            //Return comment ID or throw exception
            if($stmt->rowCount())
                return $this->DB->insertID();
            else                    
                throw new KookiizException('comments', 8);
        }
    }
?>
