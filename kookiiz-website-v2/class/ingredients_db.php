<?php
    /**********************************************************
    Title: Ingredients DB class
    Authors: Kookiiz Team
    Purpose: Provide an interface for ingredient DB interactions
    ***********************************************************/

    require_once '../class/dblink.php';
    require_once '../class/globals.php';
    require_once '../class/ingredient.php';

    //Represents an ingredient database
    class IngredientsDB
    {
        /**********************************************************
        CONSTANTS
        ***********************************************************/
        
        //Properties for import process
        private static $importProps = array(
            'ref'       => array('type' => 'i', 'def' => null),
            'name_fr'   => array('type' => 's', 'def' => null), 
            'name_en'   => array('type' => 's', 'def' => ''),
            'tags_fr'   => array('type' => 's', 'def' => ''), 
            'tags_en'   => array('type' => 's', 'def' => ''), 
            'cat'       => array('type' => 'i', 'def' => null),
            'pic'       => array('type' => 's', 'def' => ''), 
            'unit'      => array('type' => 'i', 'def' => 1),
            'wpu'       => array('type' => 'i', 'def' => 0), 
            'price'     => array('type' => 'f', 'def' => 0), 
            'exp'       => array('type' => 'i', 'def' => 1000), 
            'prob'      => array('type' => 'i', 'def' => 3));
        
        /**********************************************************
        PROPERTIES
        ***********************************************************/

        private $DB;        //Database handler
        private $lang;      //Current language identifier

        private $content = array();
        private $loaded  = false;
        private $log     = '';

        /**********************************************************
        CONSTRUCTOR
        ***********************************************************/

        //Class constructor
        //@DB (object):     open database connection
        //@lang (string):   language identifier
        //-> (void)
        public function __construct(DBLink &$DB, $lang)
        {
            $this->DB   = $DB;
            $this->lang = $lang;
        }

        /**********************************************************
        EXPORT
        ***********************************************************/

        //Export database content in compact format
        //->ingredients (object): arrays of ingredient properties
        public function export()
        {
            //Check that DB has been loaded
            if(!$this->loaded) $this->load();

            //Structure of returned data
            $ingredients = array();
            $properties  = array_merge(C::get('ING_PROPERTIES'), C::get('NUT_VALUES'));
            foreach($properties as $prop)
                $ingredients[$prop] = array();

            //Loop through database content
            foreach($this->content as $Ingredient)
            {
                //Loop through ingredient properties
                foreach($properties as $prop)
                    $ingredients[$prop][] = $Ingredient->get($prop);
            }
            
            //Include season suggestions
            $ingredients['season'] = $this->seasonExport();
            
            //Return compact ingredients DB
            return $ingredients;
        }
        
        /**********************************************************
        GETTERS
        ***********************************************************/
        
        /**
         * Get a specific ingredient from database
         * @param Int $id unique ingredient ID
         * @return Ingredient corresponding ingredient object 
         */
        public function getIngredient($id)
        {
            //Check that DB has been loaded
            if(!$this->loaded) $this->load();
            
            if(isset($this->content[$id]))
                return $this->content[$id];
            else
                return null;
        }
        
        /**********************************************************
        IMPORT
        ***********************************************************/
        
        /**
         * Import ingredients data from CSV
         * @param String $source one of "ca", "ch" or "custom"
         */
        public function import($source)
        {
            //Start buffering
            ob_start();
            
            //Try to open ingredient files
            $urlProp   = "../ingredients/ingredients_properties_$source.csv";
            @$fileProp = fopen($urlProp, 'rb');
            if(!$fileProp) 
                die("Unable to open file '$urlProp'");
            $urlNut   = "../ingredients/ingredients_nutrition_$source.csv";
            @$fileNut = fopen($urlNut, 'rb');
            if(!$fileNut) 
                die("Unable to open file '$urlNut'");
            
            //Retrieve property names and their respective positions on the first line of the file
            //The first line of the file should contain column headers !
            $props  = array_keys(self::$importProps);
            $fields = fgetcsv($fileProp, 0, ';');
            $propsPos = array();
            foreach($props as $prop)
            {
                $index = array_search($prop, $fields);
                if($index !== false)
                    $propsPos[$prop] = $index;
                else                    
                    die("Missing field '$prop' in ingredient properties file!");
            }
            
            //Loop through ingredient properties file
            $ingredients = array();
            $ingPropCount = 0; $refMax = 0;
            $propsCount = count($propsPos);
            while(!feof($fileProp))
            {	
                //Read one line of the CSV file
                $line = fgetcsv($fileProp, 0, ';');

                //This test avoid issues with a blank line at the end of the file
                //Any valid line should have values for each ingredient properties
                if(count($line) == $propsCount)
                {
                    $ingPropCount++;

                    //Retrieve ingredient reference index
                    $ref = (int)$line[$propsPos['ref']];
                    if($ref > $refMax) $refMax = $ref;

                    //Loop through ingredient properties
                    foreach($propsPos as $prop => $pos)
                    {
                        $value = $line[$pos];
                        $type  = self::$importProps[$prop]['type'];
                        $def   = self::$importProps[$prop]['def'];

                        //Force numeric values to proper type
                        switch($type)
                        {
                            case 'f':   
                                $value = (float)$value;
                                break;
                            case 'i':   
                                $value = (int)$value;
                                break;
                            case 's':  
                                $value = mb_check_encoding($value, 'UTF-8') ? $value : utf8_encode($value);
                                break;
                        }

                        //Set default value
                        if($value === '')
                        {
                            if(is_null($def))  
                                echo "Property '$prop' is missing on line $ingPropCount and has no default value.</br>";
                            else                    
                                $value = $def;
                        }

                        //Store current value
                        $ingredients[$ref][$prop] = $value;
                    }
                }
            }
            
            //Loop through ingredient nutrition file
            $ingNutCount = 0;
            while(!feof($fileNut))
            {
                //Read one line of the CSV file
                $values = fgetcsv($fileNut, 0, ';');
                if(count($values) == 3)
                {
                    $ingNutCount++;

                    //Each line contains the reference index, property name and value
                    $ref    = (int)$values[0];
                    $prop   = $values[1];
                    $value  = (float)$values[2];

                    //Store value at appropriate position
                    $ingredients[$ref][$prop] = $value;
                }
            }
            
            //Fill missing nutrition values
            $nutValues = C::get('NUT_VALUES'); $missCount = 0;
            foreach($ingredients as &$ingredient)
            {
                foreach($nutValues as $value)
                {
                    if(!isset($ingredient[$value]))
                    {
                        $ingredient[$value] = 0;
                        $missCount++;
                    }
                }
            }
            unset($ingredient);
            
            
            //Check if values are still missing
            if($ingNutCount + $missCount < $ingPropCount * count($nutValues))
                die('Missing values in nutrition file!');
            
            //Prepare generic insert request
            $props = array_merge(array_keys($propsPos), $nutValues);
            $request = 'INSERT INTO ingredients (ingredient_' . implode(', ingredient_', $props) . ')'
                        . ' VALUES (:' . implode(', :', $props) . ') ON DUPLICATE KEY UPDATE ';
            foreach($props as $index => $prop)
            {
                if($index) $request .= ', ';
                $request .= "ingredient_$prop = VALUES(ingredient_$prop)";
            }

            //Loop through ingredients
            $inserted = 0; $updated = 0;
            foreach($ingredients as $ingredient)
            {
                //Store values for current ingredient and execute query
                $params = array();
                foreach($props as $prop)
                    $params[":$prop"] = $ingredient[$prop];
                $stmt = $this->DB->query($request, $params);

                //Display request results
                switch($stmt->rowCount())
                {
                    //Nothing happened
                    case 0:
                        break;
                    //A row was inserted
                    case 1:
                        echo '#', $ingredient['ref'], ' - ', $ingredient['name_en'], ' - INSERTED</br>';
                        $inserted++;
                        break;
                    //A row was updated
                    case 2:
                        echo '#', $ingredient['ref'], ' - ', $ingredient['name_en'], ' - UPDATED</br>';
                        $updated++;
                        break;
                }
            }
            echo 'TOTAL INSERTED ROWS: ', $inserted, '</br>';
            echo 'TOTAL UPDATED ROWS: ', $updated, '<br/>';
            
            //Store log
            $this->log = ob_get_contents();
            ob_end_clean();
        }

        /**********************************************************
        LOAD
        ***********************************************************/

        //Load all ingredients from database
        //->ingredients (object): arrays of ingredient properties
        private function load()
        {
            $properties         = C::get('ING_PROPERTIES');
            $properties_lang    = C::get('ING_PROPERTIES_LANG');
            $nutrition_values   = C::get('NUT_VALUES');

            //Request for ingredient information from database
            $request = 'SELECT ';
            foreach($properties as $prop_id => $prop)
            {
                if($prop_id) 
                    $request .= ', ';
                if($properties_lang[$prop_id])
                    $request .= 'ingredient_' . $prop . '_' . $this->lang . " AS $prop";
                else 
                    $request .= "ingredient_$prop AS $prop";
            }
            foreach($nutrition_values as $value)
            {
                $request .= ", ingredient_$value AS $value";
            }
            $request .= ' FROM ingredients ORDER BY ingredient_id';
            $stmt = $this->DB->query($request);

            //Fetch ingredient information into objects
            $this->content = array();
            $stmt->setFetchMode(PDO::FETCH_CLASS|PDO::FETCH_PROPS_LATE, 'Ingredient');
            while($Ingredient = $stmt->fetch())
            {
                $Ingredient->clean();
                $this->content[$Ingredient->id] = $Ingredient;
            }

            //Attest that DB has been loaded
            $this->loaded = true;
        }
        
        /**********************************************************
        LOG
        ***********************************************************/
        
        /**
         * Display current log
         */
        public function log()
        {
            echo $this->log;
        }
        
        
        /**********************************************************
        MATCH
        ***********************************************************/
        
        /**
         * Build ingredient matches table
         */
        public function match()
        {
            //Check that DB has been loaded
            if(!$this->loaded) $this->load();
            
            //Delete existing matches
            $request = 'DELETE FROM ingredients_matches WHERE 1';
            $this->DB->query($request);
            
            //Find matches
            $matches = array(); $keys = array();
            $request = 'SELECT ingredient_id FROM ingredients'
                    . " WHERE MATCH(ingredient_name_{$this->lang}) AGAINST (? IN BOOLEAN MODE)";
            $stmt = $this->DB->prepare($request);
            foreach($this->content as $Ing)
            {
                $name = explode(' ', $Ing->get('name'));
                $name = $name[0];
                $this->DB->execute($stmt, array("$name"));
                $data = array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN, 0));
                foreach($data as $id)
                {
                    $key1 = "{$Ing->get('id')}-$id";
                    $key2 = "$id-{$Ing->get('id')}";
                    if($key1 != $key2 && !isset($pairs[$key1]) && !isset($pairs[$key2]))
                    {
                        $matches[] = array($Ing->get('id'), $id);
                        $pairs[$key1] = true;
                    }
                }
            }
            
            //Store matches in table
            $request = 'INSERT IGNORE into ingredients_matches (ing1, ing2)'
                        . ' VALUES (?, ?)';
            $stmt = $this->DB->prepare($request);
            foreach($matches as $pair)
            {
                $this->DB->execute($stmt, $pair);
            }
        }
        
        /**
         * Display ingredient matches table
         */
        public function matchList()
        {
            //Check that DB has been loaded
            if(!$this->loaded) $this->load();
            
            //Display all matches
            $curID = 0; $oldID = 0; $Ing1 = null; $Ing2 = null; $first = true;
            $request = 'SELECT * FROM ingredients_matches ORDER BY ing1';
            $stmt = $this->DB->query($request);
            while($data = $stmt->fetch())
            {
                $curID = (int)$data['ing1'];
                $Ing1 = $this->content[$curID];
                $Ing2 = $this->content[(int)$data['ing2']];
                
                if($curID != $oldID)
                {
                    if(!$first)
                        echo '</ul>';

                    echo "<p class='bold'>{$Ing1->get('name')}<p>";
                    echo '<ul>';
                    $first = false;
                }
                
                echo "<li>{$Ing2->get('name')}</li>";
                    
                $oldID = $curID;
            }
        }
        
        /**********************************************************
        SEARCH
        ***********************************************************/
        
        /**
         * Search for ingredients matching specific text
         * @param String $term text to search for
         * @return Array corresponding ingredient IDs
         */
        public function search($term)
        {
            $term = "$term*";
            $request = 'SELECT ingredient_id FROM ingredients'
                    . " WHERE MATCH(ingredient_name_{$this->lang}, ingredient_tags_{$this->lang})"
                        . ' AGAINST (? IN BOOLEAN MODE)';
            $stmt = $this->DB->query($request, array($term));
            return array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN, 0));
        }
        
        /**
         * Search for ingredients triggering specific allergies
         * @param Array $allergies user allergies as name/value pairs
         * @return Array IDs of ingredients triggering specified allergies 
         */
        public function searchAllergies($allergies)
        {
             //Retrieve allergy keywords
            $LangDB = LangDB::getHandler($this->User->getLang());
            $keywords = $LangDB->get('ALLERGIES_KEYWORDS');

            //Check if at least one allergy is specified
            $has_allergy = false;
            foreach($allergies as $allergy)
            {
                if($allergy)
                {
                    $has_allergy = true;
                    break;
                }
            }
            if($has_allergy)
            {
                //Retrieve ingredients that may cause allergic reactions
                $ingredients = array();
                $name_field = 'ingredient_name_' . $this->User->getLang();
                $request = 'SELECT ingredient_id FROM ingredients WHERE 0';
                //GLUTEN
                //Cereal products category
                if($allergies['gluten'])
                    $request .= ' OR ingredient_cat = 20';
                //MILK
                //Contains lactose
                if($allergies['milk'])
                    $request .= ' OR ingredient_lact != 0';
                //EGG
                //"egg" in ingredient's name
                if($allergies['egg'])
                    $request .= " OR MATCH($name_field) AGAINST('*{$keywords[0]}*' IN BOOLEAN MODE)";
                //FISH
                //Fish products
                if($allergies['fish'])
                    $request .= ' OR ingredient_cat = 15';
                //CRUST
                //Fish products (SUB-CATEGORY REQUIRED !)
                if($allergies['crust'])
                    $request .= ' OR ingredient_cat = 15';
                //SOY
                //"soy" in ingredient's name
                if($allergies['soy'])
                    $request .= " OR MATCH($name_field) AGAINST('*{$keywords[1]}*' IN BOOLEAN MODE)";
                //NUTS
                //Nuts products category
                if($allergies['nuts'])
                    $request .= ' OR ingredient_cat = 12';
                //SESAME
                //"sesame" in ingredient's name
                if($allergies['sesame'])
                    $request .= " OR MATCH($name_field) AGAINST('*{$keywords[2]}*' IN BOOLEAN MODE)";
                //CELERY
                //"celery" in ingredient's name
                if($allergies['celery'])
                    $request .= " OR MATCH($name_field) AGAINST('*{$keywords[3]}*' IN BOOLEAN MODE)";
                
                //Fetch ingredient IDs
                $stmt = $this->DB->query($request);
                return array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN, 0));
            }
            else
                return array();
        }
        
        /**
         * Search ingredients similar to provided list
         * @param Array $ingredients list of IDs to search for
         * @return Array list of similar ingredient IDs
         */
        public function searchSimilar(array $ingredients)
        {
            $similar = array();
            if(count($ingredients))
            {
                $request = 'SELECT ing1, ing2 FROM ingredients_matches'
                        . ' WHERE ing1 IN (' . implode(', ', $ingredients) . ')'
                            . ' OR ing2 IN (' . implode(', ', $ingredients) . ')';
                $stmt = $this->DB->query($request);
                while($match = $stmt->fetch())
                {
                    $similar[] = (int)$match['ing1'];
                    $similar[] = (int)$match['ing2'];
                }
                $similar = array_values(array_unique($similar));
            }
            return $similar;
        }

        /**********************************************************
        SEASON
        ***********************************************************/
        
        //Export season suggestions
        //->season (array): all season suggestions ordered by month
        public function seasonExport()
        {
            $season = array();
            for($i = 0; $i < 12; $i++) $season[] = array();
            
            $request = 'SELECT ingredient_id AS id, month FROM ingredients_months';
            $stmt = $this->DB->query($request);
            while($pair = $stmt->fetch())
            {
                $season[(int)$pair['month'] - 1][] = (int)$pair['id'];
            }
            
            return $season;
        }

        //Return ingredient suggestions for provided month
        //@month (int): month number (from 1 to 12, defaults to current)
        //->suggestions (array): list of ingredient IDs
        public function seasonGet($month = 0)
        {
            if(!$month) $month = (int)date('n');

            //Load season ingredients from database
            $request = 'SELECT ingredient_id FROM ingredients_months WHERE month = ?';
            $stmt = $this->DB->query($request, array($month));
            return array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN, 0));
        }

        //Create an ingredient-month pair
        //@month (int):         month number (from 1 to 12)
        //@ingredient_id (int): ID of the ingredient
        //-> (void)
        public function seasonSet($month, $ingredient_id)
        {
            $request = 'INSERT IGNORE INTO ingredients_months'
                    . ' (month, ingredient_id) VALUES (?, ?)';
            $stmt = $this->DB->query($request, array($month, $ingredient_id));

            //Failed to create season pair
            if(!$stmt->rowCount()) 
                throw new KookiizException('admin_ingredients', 1);
        }
    }
?>