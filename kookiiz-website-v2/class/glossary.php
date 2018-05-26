<?php
    /*******************************************************
    Title: Glossary
    Authors: Kookiiz Team
    Purpose: Search and manage glossary terms
    ********************************************************/

    //Dependencies
    require_once '../class/globals.php';

    //Represents an interface for Kookiiz glossary
    class Glossary
    {
        /**********************************************************
        CONSTANTS
        ***********************************************************/

        const DEFINITION_MIN    = C::GLOSSARY_DEFINITION_MIN;
        const KEYWORD_MIN       = C::GLOSSARY_KEYWORD_MIN;

        const MATCH_FRACTION    = 0.5;
        const MATCH_SPREAD_MAX  = 25;
        const SEARCH_MAX        = 20;

        /**********************************************************
        PROPERTIES
        ***********************************************************/

        private $DB;        //link to the database
        private $User;      //user connected to the glossary

        //PDO statement object with last result set
        private $Result = null;

        /**********************************************************
        CONSTRUCTOR
        ***********************************************************/

        //Class constructor
        //@DB (object):     open database connection
        //@User (object):   user connected to the glossary
        //-> (void)
        public function __construct(DBLink &$DB, User &$User)
        {
            //Set up properties
            $this->DB   = $DB;
            $this->User = $User;
        }
        
        /**********************************************************
        ADD
        ***********************************************************/

        //Add a new keyword to glossary
        //@keyword (string):    keyword name
        //@definition (string): keyword definition
        //@lang (string):       language identifier
        //-> (void)
        public function add($keyword, $definition, $lang)
        {
            //Check if the keyword is valid
            if(strlen($keyword) < self::KEYWORD_MIN)
                //Keyword is too short
                throw new KookiizException('admin_glossary', 1);
            else if(strlen($definition) < self::DEFINITION_MIN)
                //Keyword is too long
                throw new KookiizException('admin_glossary', 5);
            else
            {
                //Insert new keyword in database (if it does not exist yet)
                $request = 'INSERT IGNORE INTO glossary (keyword, definition, lang)'
                            . ' VALUES(:keyword, :def, :lang)';
                $stmt = $this->DB->query($request, array(
                    ':keyword'  => $keyword,
                    ':def'      => $definition,
                    ':lang'     => $lang
                ));

                //Keyword could not be inserted
                if(!$stmt->rowCount())
                    throw new KookiizException('admin_glossary', 2);
            }
        }

        /**********************************************************
        DELETE
        ***********************************************************/

        //Delete existing glossary keyword
        //@keyword_id (int): ID of the keyword to delete
        //->error (int): error code (0 = no error)
        public function delete($keyword_id)
        {
            $request = 'DELETE FROM glossary'
                    . ' WHERE keyword_id = ?';
            $stmt = $this->DB->query($request, array($keyword_id));

            //Keyword could not be removed (was not found)
            if(!$stmt->rowCount())
                throw new KookiizException('admin_glossary', 4);
        }
        
        /**********************************************************
        EDIT
        ***********************************************************/

        //Edit existing glossary keyword
        //@keyword_id (int):    ID of the keyword to edit
        //@definition (string): new (edited) definition of the keyword
        //@lang (string):       language identifier
        //->error (int): error code (0 = no error)
        public function edit($keyword_id, $definition, $lang)
        {
            if(strlen($definition) < self::DEFINITION_MIN)
                //Keyword definition is too short
                throw new KookiizException('admin_glossary', 5);
            else
            {
                $request = 'UPDATE glossary'
                            .' SET definition = ?, lang = ?'
                        . ' WHERE keyword_id = ?';
                $params = array($definition, $lang, $keyword_id);
                $stmt = $this->DB->query($request, $params);

                //Failed to edit keyword
                if(!$stmt->rowCount())
                    throw new KookiizException('admin_glossary', 3);
            }
        }

        /**********************************************************
        FETCH
        ***********************************************************/

        //Fetch and return current result set
        //->result (mixed): result set as an associative array
        private function fetch()
        {
            $result = array();
            if($this->Result)
            {
                while($keyword = $this->Result->fetch())
                {
                    $result[] = array(
                        'id'    => (int)$keyword['id'],
                        'name'  => htmlspecialchars($keyword['name'], ENT_COMPAT, 'UTF-8'),
                        'def'   => htmlspecialchars($keyword['def'], ENT_COMPAT, 'UTF-8'),
                        'lang'  => $keyword['lang']
                    );
                }
            }
            return $result;
        }
        
        /**********************************************************
        LINK
        ***********************************************************/

        //Store glossary-recipe links in database
        //@recipe_id (int):     ID of the recipe
        //@keywords (array):    list of keyword IDs to link with the recipe
        //-> (void)
        public function link($recipe_id, $keywords)
        {
            $keywords = array_values(array_unique($keywords));
            if(count($keywords))
            {
                //Insert links to the keywords in cross table (avoiding duplicates)
                $request = 'INSERT IGNORE INTO recipes_glossary (recipe_id, keyword_id)'
                            . ' VALUES (?, ?)';
                $stmt = $this->DB->prepare($request);
                foreach($keywords as $keyword_id)
                    $this->DB->execute($stmt, array($recipe_id, $keyword_id));
            }
        }
        
        /**********************************************************
        MATCH
        ***********************************************************/

        //Search for glossary matches inside provided text
        //@text (string): text inside which glossary terms must be searched
        //@lang (string): language identifier
        //->ids (array): list of keyword IDs that matches provided text
        public function match($text, $lang)
        {
            $ids = array();

            //Retrieve glossary content
            $request = 'SELECT keyword_id AS id, keyword AS name'
                    . ' FROM glossary'
                    . ' WHERE lang = ?';
            $stmt     = $this->DB->query($request, array($lang));
            $glossary = $stmt->fetchAll();

            //Loop through glossary
            foreach($glossary as $keyword)
            {
                $matches = array();
                
                $keyword_id     = (int)$keyword['id'];
                $keyword        = $keyword['name'];
                $keyword_split  = explode(' ', $keyword);
                $keyword_size   = count($keyword_split);

                //Loop through words inside the keyword
                foreach($keyword_split as $pos => $word)
                {
                    $matches[$pos] = array();

                    //Skip very short strings
                    if(strlen($word) < self::KEYWORD_MIN)
                    {
                        $keyword_size--;
                        continue;
                    }
                    else
                    {
                        //Look for occurences of current keyword piece in text
                        $old_pos = 0; $new_pos = 0;
                        do
                        {
                            $old_pos = $new_pos;
                            $new_pos = stripos($text, $word, $old_pos + 1);
                            if($new_pos !== false) 
                                $matches[$pos][] = $new_pos;
                        }
                        while($new_pos !== false);
                    }
                }

                //If this keyword has more than one piece
                $match_success = 0;
                if(count($matches) > 1)
                {
                    //Loop through all matches for all pieces of this keyword
                    for($piece = 0, $piece_max = count($matches); $piece < $piece_max; $piece++)
                    {
                        //Loop through matches for current keyword piece
                        for($i = 0, $imax = count($matches[$piece]); $i < $imax; $i++)
                        {
                            //Loop through next keyword pieces
                            for($next = $piece, $next_max = count($matches); $next < $next_max; $next++)
                            {
                                //Loop through matches of next keyword piece
                                for($j = 0, $jmax = count($matches[$next]); $j < $jmax; $j++)
                                {
                                    //Match success is increased if the position differential between current keyword piece
                                    //and one of its successors is smaller than GLOSSARY_SPREAD_MAX
                                    $pos        = $matches[$piece][$i];
                                    $next_pos   = $matches[$next][$j];
                                    $diff       = $next_pos - $pos;
                                    if($diff > 0 && $diff < self::MATCH_SPREAD_MAX) 
                                        $match_success++;
                                }
                            }
                        }
                    }
                }
                //Single word -> match success is equal to the number of matches found for this piece
                else 
                    $match_success = count($matches[0]);

                //Check if current keyword has a sufficient match score
                $minimum_match = min(1, round(self::MATCH_FRACTION * $keyword_size));
                if($match_success >= $minimum_match) 
                    $ids[] = $keyword_id;
            }

            return array_values(array_unique($ids));
        }
        
        /**********************************************************
        SEARCH
        ***********************************************************/

        //Search for a specific term in glossary
        //@text (string): text to search for
        //->keywords (array): list of matching keyword objects
        public function search($text)
        {
            //Reset result pointer
            $this->Result = null;

            //Search for matching keywords
            $request = 'SELECT'
                        . ' keyword_id AS id,'
                        . ' keyword AS name,'
                        . ' definition AS def,'
                        . ' lang'
                    . ' FROM glossary'
                    . ' WHERE MATCH(keyword) AGAINST (? IN BOOLEAN MODE)';
            $params = array("$text*");
            if(!$this->User->isAdmin())
            {
                $request .= ' AND lang = ?';
                $params[] = $this->User->getLang();
            }
            $request .= ' ORDER BY keyword LIMIT ' . self::SEARCH_MAX;
            $this->Result = &$this->DB->query($request, $params);

            //Return fetched result
            return $this->fetch();
        }

        //Search for glossary terms related to a given recipe
        //@recipe_id (int): ID of the recipe
        //->keywords (array): list of matching keyword objects
        public function searchRecipe($recipe_id)
        {
            //Reset result pointer
            $this->Result = null;

            //Search for matching keywords
            $request = 'SELECT'
                        . ' glossary.keyword_id AS id,'
                        . ' glossary.keyword AS name,'
                        . ' glossary.definition AS def,'
                        . ' glossary.lang AS lang'
                    . ' FROM recipes_glossary'
                        . ' NATURAL JOIN glossary'
                    . ' WHERE recipes_glossary.recipe_id = ?'
                    . ' ORDER BY glossary.keyword'
                    . ' LIMIT ' . self::SEARCH_MAX;
            $this->Result = $this->DB->query($request, array($recipe_id));

            //Return fetched result
            return $this->fetch();
        }
    }
?>
