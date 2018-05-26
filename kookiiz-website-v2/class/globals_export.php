<?php
    /*******************************************************
    Title: Globals export
    Authors: Kookiiz Team
    Purpose: Export PHP globals to Javascript
    ********************************************************/

    //Represents a handler for globals exportation
    class GlobalsExport
    {
        /**********************************************************
        CONSTRUCTOR
        ***********************************************************/

        //Class constructor
        //-> (void)
        public function __construct()
        {
            
        }

        /**********************************************************
        EXPORT
        ***********************************************************/

        //Export constants from a given class to JS
        //@class (string):  class name
        //@prefix (string): prefix for JS name (defaults to class name in upper case, false for none)
        //-> (void)
        public function exportConstants($class, $prefix = null)
        {
            if(is_null($prefix)) $prefix = strtoupper($class);

            $Reflector = new ReflectionClass($class);
            $constants = $Reflector->getConstants();
            foreach($constants as $name => $value)
            {
                if($prefix) echo "var $prefix", '_', "$name = ", (is_numeric($value) ? $value : "'$value'"), ";\n";
                else        echo "var $name = ", (is_numeric($value) ? $value : "'$value'"), ";\n";
            }
        }

        //Export static properties of a given class to JS
        //@class (string):  class name
        //@prefix (string): prefix for JS name (defaults to class name in upper case, false for none)
        //-> (void)
        public function exportStatic($class, $prefix = null)
        {
            if(is_null($prefix)) $prefix = strtoupper($class);

            $Reflector = new ReflectionClass($class);
            $properties = $Reflector->getStaticProperties();
            foreach($properties as $name => $value)
            {
                if($prefix) echo "var $prefix", '_', "$name = ", json_encode($value), ";\n";
                else        echo "var $name = ", json_encode($value), ";\n";
            }
        }
    }
?>
