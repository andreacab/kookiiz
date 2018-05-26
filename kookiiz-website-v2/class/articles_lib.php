<?php
    /*******************************************************
    Title: Articles library
    Authors: Kookiiz Team
    Purpose: Manage database of articles
    ********************************************************/

    //Dependencies
    require_once '../class/dblink.php';
    require_once '../class/exception.php';
    require_once '../class/user.php';

    //Represents a library of articles
    class ArticlesLib
    {
        /**********************************************************
        CONSTANTS
        ***********************************************************/

        const HISTORY_MAX   = 10;
        const SEARCH_MAX    = 50;
        const TYPE_HEALTH   = 0;
        const TYPE_TIPS     = 1;

        /**********************************************************
        PROPERTIES
        ***********************************************************/

        private $DB;    //Database handler
        private $User;  //User connected to the library

        /**********************************************************
        CONSTRUCTOR
        ***********************************************************/

        //Class constructor
        //@DB (object):     open database connection
        //@User (object):   user connected to the library
        //-> (void)
        public function __construct(DBLink &$DB, User &$User)
        {
            $this->DB   = $DB;
            $this->User = $User;
        }

        /**********************************************************
        DELETE
        ***********************************************************/

        //Delete article from database
        //@id (int): article ID
        //-> (void)
        public function delete($id)
        {
            //Delete article
            $request = 'DELETE FROM articles WHERE article_id = ?';
            $this->DB->query($request, array($id));

            //Find article pics IDs
            $request = 'SELECT pic_id FROM articles_pics WHERE article_id = ?';
            $stmt = $this->DB->query($request, array($id));
            $pics = array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN, 0));
            if(count($pics))
            {
                //Delete article pics entries in articles_pics table
                $request = 'DELETE FROM articles_pics WHERE article_id = ?';
                $this->DB->query($request, array($id));

                //Retrieve pics paths and delete them
                $request = 'SELECT pic_path FROM articles_pictures'
                        . ' WHERE pic_id IN (' . implode(', ', $pics) . ')';
                $stmt = $this->DB->query($request);
                while($pic = $stmt->fetch())
                {
                    //Delete actual picture file
                    $path = $pic['pic_path'];
                    unlink($path);
                }

                //Delete pics entries in pictures table
                $request = 'DELETE FROM articles_pictures'
                        . ' WHERE pic_id IN (' . implode(', ', $pics) . ')';
                $this->DB->query($request);
            }

            //Delete article comments
            $request = 'DELETE FROM articles_comments WHERE content_id = ?';
            $this->DB->query($request, array($id));
            $request = 'DELETE FROM articles_comments_ratings WHERE content_id = ?';
            $this->DB->query($request, array($id));
        }

        /**********************************************************
        EDIT
        ***********************************************************/

        //Edit existing article
        //@id (int):            article ID
        //@type (int):          article type
        //@source (int):        unique ID of the partner who provided the article
        //@title (string):      article title
        //@text (string):       article text
        //@keywords (string):   list of keywords separated by ","
        //@pics (array):        list of picture IDs
        //@captions (array):    list of picture captions (in the same order than pics array)
        //-> (void)
        public function edit($id, $type, $source, $title, $text, $keywords, $pics, $captions)
        {
            //Update article content
            $request = 'UPDATE articles SET'
                        . ' article_type = :type,'
                        . ' article_source = :source,'
                        . ' article_title = :title,'
                        . ' article_text = :text,'
                        . ' article_keywords = :keywords,'
                        . ' article_date = NOW()'
                    . ' WHERE article_id = :id';
            $params = array(
                ':type'     => $type,
                ':source'   => $source,
                ':title'    => $title,
                ':text'     => $text,
                ':keywords' => $keywords,
                ':id'       => $id
            );
            $this->DB->query($request, $params);

            //Delete existing pictures
            $request = 'DELETE FROM articles_pics WHERE article_id = ?';
            $this->DB->query($request, array($id));

            //Insert new pictures
            if(count($pics))
            {
                $request = 'INSERT INTO articles_pics (article_id, pic_id, caption) VALUES (?, ?, ?)';
                $params = array();
                foreach($pics as $index => $pic_id)
                {
                    $params[] = array($id, $pic_id, $captions[$index]);
                }
                $this->DB->query($request, $params);
            }
        }
        
        /**********************************************************
        HISTORY
        ***********************************************************/

        //Retrieve a list of most recent articles
        //->history (array): list of article structures
        public function history()
        {
            $history = array();
            $request = 'SELECT article_id, article_type, article_title,'
                        . ' UNIX_TIMESTAMP(article_date) as article_date'
                    . ' FROM articles'
                    . ' WHERE lang = ?'
                    . ' ORDER BY article_date DESC LIMIT ' . self::HISTORY_MAX;
            $stmt = $this->DB->query($request, array($this->User->getLang()));
            while($article = $stmt->fetch())
            {
                $history[] = array(
                    'id'    => (int)$article['article_id'],
                    'type'  => (int)$article['article_type'],
                    'title' => htmlspecialchars($article['article_title'], ENT_COMPAT, 'UTF-8'),
                    'date'  => date('d.m.y', (int)$article['article_date'])
                );
            }
            return $history;
        }

        /**********************************************************
        INSERT
        ***********************************************************/

        //Save a new article in database
        //@type (int):          article type
        //@source (int):        unique ID of the partner who provided the article
        //@title (string):      article title
        //@text (string):       article text
        //@keywords (string):   list of keywords separated by ","
        //@pics (array):        list of picture IDs
        //@captions (array):    list of picture captions (in the same order than pics array)
        //@lang (string):       language identifier
        //->id (int): unique ID of the new article
        public function insert($type, $source, $title, $text, $keywords, $pics, $captions, $lang)
        {
            //Insert article content
            $request = 'INSERT INTO articles (article_type, article_partner,'
                        . '	article_title, article_text, article_keywords, lang)'
                    . ' VALUES (:type, :source, :title, :text, :keywords, :lang)';
            $params = array(
                ':type'     => $type,
                ':source'   => $source,
                ':title'    => $title,
                ':text'     => $text,
                ':keywords' => $keywords,
                ':lang'     => $lang
            );
            $this->DB->query($request, $params);

            //Retrieve article unique ID
            $article_id = $this->DB->insertID();

            //Insert pictures
            if(count($pics))
            {
                $request = 'INSERT INTO articles_pics (article_id, pic_id, caption) VALUES (?, ?, ?)';
                $params = array();
                foreach($pics as $index => $pic_id)
                {
                    $params[] = array($id, $pic_id, $captions[$index]);
                }
                $this->DB->query($request, $params);
            }

            //Return article ID
            return $article_id;
        }

        /**********************************************************
        LOAD
        ***********************************************************/

        //Load a given article from database
        //@id (int): article unique ID
        //->article (object): structure containing article content (false if not found)
        public function load($id)
        {
            //Request for the full content of selected article
            $request = 'SELECT * FROM articles WHERE article_id = ?';
            $stmt = $this->DB->query($request, array($id));
            $data = $stmt->fetch();
            if($data)
            {
                //Retrieve article information
                $title      = htmlspecialchars($article['article_title'], ENT_COMPAT, 'UTF-8');
                $text       = htmlspecialchars($article['article_text'], ENT_COMPAT, 'UTF-8');
                $partner_id = (int)$article['article_partner'];
                $date       = date('Y-m-d', (int)$article['article_date']);
                $lang       = $article['lang'];
                $keywords   = htmlspecialchars($article['article_keywords'], ENT_COMPAT, 'UTF-8');

                //Ask for partner data
                $request    = 'SELECT * FROM partners WHERE partner_id = ?';
                $stmt       = $this->DB->query($request, array($partner_id));
                $partner    = $stmt->fetch();

                //Ask for related pictures
                $pics = array(); $captions = array();
                $request = 'SELECT pic_id, pic_caption FROM articles_pics WHERE article_id = ?';
                $stmt = $this->DB->query($request, array($id));
                while($picture = $stmt->fetch())
                {
                    $pics[]     = (int)$picture['pic_id'];
                    $captions[] = htmlspecialchars($picture['pic_caption'], ENT_COMPAT, 'UTF-8');
                }

                //Return article content
                return array(
                    'id'        => $article_id,
                    'title'     => $title,
                    'text'      => $text,
                    'partner'   => $partner,
                    'date'      => $date,
                    'lang'      => $lang,
                    'keywords'  => $keywords,
                    'pics'      => $pics,
                    'captions'  => $captions
                );
            }
            //Article was not found
            else throw new KookiizException('articles', 1);
        }

        /**********************************************************
        SEARCH
        ***********************************************************/

        //Search for articles matching provided type and keyword
        //@type (int):          article type
        //@keyword (string):    search keyword
        //->articles (array): list of articles structures
        public function search($type, $keyword)
        {
            $articles = array();

            //Try to find matches for the keyword inside article titles or keywords
            $request = 'SELECT article_id, article_type, article_title,'
                        .' UNIX_TIMESTAMP(article_date) as article_date'
                    . ' FROM articles'
                    . ' WHERE article_type = ? AND lang = ?'
                        . ' AND MATCH(article_title, article_keywords) AGAINST (? IN BOOLEAN MODE)'
                    . ' LIMIT ' . self::SEARCH_MAX;
            $params = array($type, $this->User->getLang(), "$keyword*");
            $stmt = $this->DB->query($request, $params);

            //Loop through results
            while($article = $stmt->fetch())
            {
                $articles[] = array(
                    'id'    => (int)$article['article_id'],
                    'type'  => (int)$article['article_type'],
                    'title' => htmlspecialchars($article['article_title'], ENT_COMPAT, 'UTF-8'),
                    'date'  => date('d.m.y', (int)$article['article_date'])
                );
            }

            return $articles;
        }
    }
?>
