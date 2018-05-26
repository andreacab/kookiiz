<?php
    /*******************************************************
    Title: Exception
    Authors: Kookiiz Team
    Purpose: Define custom Kookiiz exception
    ********************************************************/

    //Error codes shortcuts
    class Error
    {
        const NONE                    = 0;

        //RECIPES
        const RECIPE_HASPIC           = 2;
        const RECIPE_TRANSLATEFAILED  = 3;
        const RECIPE_UNKNOWN          = 1;

        //SESSION
        const SESSION_EXPIRED         = 1;
        const SESSION_UNAUTHORIZED    = 2;

        //USER
        const USER_SAVEFAILED         = 4;
    }

    //Represents a custom Kookiiz exception
    class KookiizException extends Exception
    {
        /**********************************************************
        PROPERTIES
        ***********************************************************/

        private $type;

        /**********************************************************
        CONSTRUCTOR
        ***********************************************************/

        //Class constructor
        //@type (string):   error category
        //@code (int):      error code
        //-> (void)
        public function __construct($type, $code)
        {
            $this->type = $type;
            parent::__construct($message = '', $code);
        }

        /**********************************************************
        GET
        ***********************************************************/

        //Return exception type
        //->type (string): exception type
        public function getType()
        {
            return $this->type;
        }

        /**********************************************************
        TO STRING
        ***********************************************************/

        //Create string representation of API exception
        //->message (string): string representation of exception
        public function __toString()
        {
            return 'Error ' . $this->type . '#' . $this->code;
        }
    }
?>