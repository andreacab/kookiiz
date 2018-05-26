<?php
    /*******************************************************
    Title: Lang array
    Authors: Kookiiz Team
    Purpose: Store language strings
    ********************************************************/

    //Represents a language array
    class LangArray
    {
        /**********************************************************
        PROPERTIES
        ***********************************************************/

        private $content = array();

        /**********************************************************
        CONSTRUCTOR
        ***********************************************************/

        //Class constructor
        //-> (void)
        public function __construct()
        {
        }

        /**********************************************************
        ADD
        ***********************************************************/

        //Add a key to the language array
        //@key (int/string):    array key
        //@value (mixed):       corresponding array value
        //-> (void)
        public function add($key, $value)
        {
            $this->content[$key] = $value;
        }

        /**********************************************************
        EXPORT
        ***********************************************************/

        //Echoes array content as JSON
        //-> (void)
        public function exportJSON()
        {
            echo json_encode($this->content);
        }

        /**********************************************************
        GET
        ***********************************************************/

        //Get array content or value of a specific key
        //@key (int/string): specific array key (optional)
        //->value (mixed): entire array content or specific value or null if not found
        public function get($key = null)
        {
            if(is_null($key))
                return $this->content;
            else
            {
                if(isset($this->content[$key]))
                    return $this->content[$key];
                else
                    return null;
            }
        }

        /**********************************************************
        SORT
        ***********************************************************/

        //Re-order array keys
        //-> (void)
        public function sort()
        {
            ksort($this->content);
        }
    }
?>