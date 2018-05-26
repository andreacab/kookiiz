<?php
    /*******************************************************
    Title: Lang handler
    Authors: Kookiiz Team
    Purpose: Manage PHP language arrays
    ********************************************************/

    //Dependencies
    require_once $_SERVER['DOCUMENT_ROOT'] . '/class/dblink.php';
    require_once $_SERVER['DOCUMENT_ROOT'] . '/class/globals.php';
    require_once $_SERVER['DOCUMENT_ROOT'] . '/class/lang_array.php';
    require_once $_SERVER['DOCUMENT_ROOT'] . '/class/session.php';

    //Represents a database of language data
    //This class has a private constructor and must be instanciated through "getHandler()"
    class LangDB
    {
        /**********************************************************
        CONSTANTS
        ***********************************************************/

        //Language array types
        const TYPE_ADMIN_JS  = 3;    //Javascript admin
        const TYPE_ADMIN_PHP = 4;    //PHP admin
        const TYPE_ALL       = 0;    //All contexts
        const TYPE_JS        = 1;    //Client-side (Javascript)
        const TYPE_PHP       = 2;    //Server-side (PHP)

        /**********************************************************
        PROPERTIES
        ***********************************************************/

        //Array of handler instances
        private static $instances = array();

        private static $DB;         //Database handler
        private static $context;    //Current context
        private static $admin;      //Whether to include admin data as well

        //Language data storage
        private $data = array();

        private $lang;                      //Language setting
        private $loaded         = false;    //Has language data been loaded?
        private $deleteSTMT     = null;     //Delete statement object
        private $deleteAllSTMT  = null;     //Delete statement object
        private $insertSTMT     = null;     //Insert statement object

        /**********************************************************
        CONSTRUCTOR
        ***********************************************************/

        //Class constructor
        //@lang (string): language identifier
        //-> (void)
        private function __construct($lang)
        {
            $this->lang = $lang;
        }
        
        /**********************************************************
        CHECK
        ***********************************************************/

        //Check if database table exists for current language
        //->exists (bool): true if table exists
        public function checkTable()
        {
            $request = 'SELECT COUNT(*) AS count'
                    . ' FROM information_schema.tables'
                    . " WHERE table_schema = 'lang'"
                        . " AND table_name = ?";
            $stmt = self::$DB->query($request, array($this->lang));
            $data = $stmt->fetch();
            return (int)$data['count'] > 0;
        }

        /**********************************************************
        DELETE
        ***********************************************************/

        //Delete language array or specific index
        //@name (string):   array name
        //@index (mixed):   array index (int or string, optional)
        //-> (void)
        public function delete($name, $index = null)
        {
            if(is_null($index))
            {
                if(is_null($this->deleteAllSTMT))
                {
                    $table = $this->lang;
                    $request = "DELETE FROM $table"
                            . ' WHERE array_name = ?';
                    $this->deleteAllSTMT = self::$DB->prepare($request);
                }

                //Delete entire array
                self::$DB->execute($this->deleteAllSTMT, array($name));
            }
            else
            {
                if(is_null($this->deleteSTMT))
                {
                    $table = $this->lang;
                    $request = "DELETE FROM $table"
                            . ' WHERE array_name = ? AND array_index = ?';
                    $this->deleteSTMT = self::$DB->prepare($request);
                }

                //Delete specific array index
                self::$DB->execute($this->deleteSTMT, array($name, $index));
            }
        }

        /**********************************************************
        GETTERS
        ***********************************************************/

        //Return a given language array or a specific key of this array
        //@array (string):  array name
        //@key (mixed):     numerical or textual key (optional)
        //->value (mixed): either an entire array, a specific value or null if not found
        public function get($array, $key = null)
        {
            if(!$this->loaded) $this->load();

            if(isset($this->data[$array]))
                return $this->data[$array]->get($key);
            else 
                return null;
        }
        
        /**
         * Get current language code (e.g. fr_FR, en_US, etc.)
         * @return String language code
         */
        public function getCode()
        {
            $langID = array_search($this->lang, C::get('LANGUAGES'));
            return C::get('LANGUAGES_FULL', $langID);
        }

        //Return Singleton instance
        //@lang (string):       language identifier
        //@context (string):    current context (defaults to "PHP")
        //@admin (bool):        whether to load admin language data as well (defaults to false)
        //->langDB (object): lang handler
        public static function getHandler($lang, $context = 'PHP', $admin = false)
        {
            //Init DB handler if required
            if(is_null(self::$DB))
                self::$DB = new DBLink('lang', 'kook_lang');

            //Check if context changed
            if(self::$context != $context)
            {
                //Reset everything
                self::$instances = array();
                self::$context = $context;
            }

            //Check if admin status changed
            if(self::$admin != $admin)
            {
                self::$instances = array();
                self::$admin = $admin;
            }

            //Create handler instance for specified language
            if(!isset(self::$instances[$lang]))
                self::$instances[$lang] = new LangDB($lang);

            //Return handler instance
            return self::$instances[$lang];
        }

        //Get provided language array in all available languages
        //@array (string): language array name
        //->translation (object): array of language arrays indexed by lang identifier
        public static function getTranslation($array)
        {
            $translation = array();
            foreach(C::get('LANGUAGES') as $lang)
            {
                $translation[$lang] = array();
                $request = "SELECT $lang.array_index, $lang.array_value"
                        . " FROM $lang WHERE array_name = ?";
                $stmt = self::$DB->query($request, array($array));
                while($item = $stmt->fetch())
                {
                    $key    = is_numeric($item['array_index'])
                            ? (int)$item['array_index']
                            : $item['array_index'];
                    $value  = $item['array_value'];
                    $translation[$lang][$key] = $value;
                }
            }
            return $translation;
        }

        /**********************************************************
        EXPORT
        ***********************************************************/

        //Export database content
        //->content (array): associative array indexed by array name
        public function export()
        {
            if(!$this->loaded) $this->load();

            $content = array();
            foreach($this->data as $name => $array)
            {
                $content[$name] = $array->get();
            }
            return $content;
        }

        //Export language database in JSON
        //-> (void)
        public function exportJSON()
        {
            if(!$this->loaded) $this->load();

            //Loop through language arrays
            foreach($this->data as $name => $array)
            {
                echo "var $name = ", $array->exportJSON(), ";\n";
            }
        }

        /**********************************************************
        INSERT
        ***********************************************************/

        //Insert language item into the database
        //@name (string):   array name
        //@index (mixed):   array index (int or string)
        //@type (int):      array type code
        //@value (string):  array value
        //->status (int): 0 = no action, 1 = inserted, 2 = updated
        public function insert($name, $index, $type, $value)
        {
            $this->loaded = false;

            //Prepare insert statement
            if(is_null($this->insertSTMT))
            {
                $table = $this->lang;
                $request = "INSERT INTO $table (array_name, array_index, array_value, array_type)"
                            . ' VALUES (:array, :index, :value, :type)'
                        . ' ON DUPLICATE KEY UPDATE'
                            . ' array_type = VALUES(array_type),'
                            . ' array_value = VALUES(array_value)';
                $this->insertSTMT = self::$DB->prepare($request);
            }

            //Insert language item
            self::$DB->execute($this->insertSTMT, array(
                ':array'    => $name,
                'index'     => $index,
                ':value'    => $value,
                ':type'     => $type
            ));

            //Return statement status
            return $this->insertSTMT->rowCount();
        }

        /**********************************************************
        LOAD
        ***********************************************************/

        //Load language data from database
        //-> (void)
        private function load()
        {
            //Table names
            $def = C::LANG_DEFAULT;
            $cur = $this->lang;
            if($def == $cur)
                $tables = $def;
            else
                $tables = "$def LEFT JOIN $cur USING (array_name, array_index)";

            //Condition
            switch(self::$context)
            {
                case 'ADMIN':
                    $condition = '1';
                    break;
                case 'JS':
                    $condition = "$def.array_type = " . self::TYPE_ALL
                                . " OR $def.array_type = " . self::TYPE_JS;
                    if(self::$admin) $condition .= " OR $def.array_type = " . self::TYPE_ADMIN_JS;
                    break;
                case 'PHP':
                    $condition = "$def.array_type = " . self::TYPE_ALL
                                . " OR $def.array_type = " . self::TYPE_PHP;
                    if(self::$admin) $condition .= " OR $def.array_type = " . self::TYPE_ADMIN_PHP;
                    break;
            }

            //Fetch language data from DB
            $request = "SELECT $def.array_name, $def.array_index, $def.array_type,"
                        . " IF($cur.array_value IS NULL, $def.array_value, $cur.array_value) AS array_value"
                    . " FROM $tables"
                    . " WHERE $condition"
                    . ' ORDER BY array_name';
            $stmt = self::$DB->query($request);
            while($lang_item = $stmt->fetch())
            {
                $array  = $lang_item['array_name'];
                $key    = is_numeric($lang_item['array_index'])
                        ? (int)$lang_item['array_index']
                        : $lang_item['array_index'];
                $value  = $lang_item['array_value'];
                $type   = (int)$lang_item['array_type'];

                if(!isset($this->data[$array]))
                {
                    $this->data[$array] = new LangArray();
                }
                $this->data[$array]->add($key, $value);
            }

            //Re-order array indexes
            foreach($this->data as $array)
            {
                $array->sort();
            }

            //Set loaded flag
            $this->loaded = true;
        }

        /**********************************************************
        PRINT
        ***********************************************************/

        //Echoes a language string
        //@array (string):  array name
        //@key (mixed):     numerical or textual key
        //-> (void)
        public function p($array, $key)
        {
            $string = $this->get($array, $key);
            echo is_null($string) ? '' : $string;
        }
    }
?>
