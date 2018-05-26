<?php
    /*******************************************************
     Title: cURL handler
     Authors: Kookiiz Team
     Purpose: Provide easy access to cURL functionalities
     ********************************************************/

    //Represents a handler for cURL requests
    class cURLHandler 
    {
        /**********************************************************
        CONSTRUCTOR
        ***********************************************************/

        /**
         * Class constructor
         */
        public function __construct() 
        {
        }

        /**********************************************************
        READ
        ***********************************************************/
        
        /**
         * Read distant file with cURL
         * @param String $url file URL (should be properly encoded)
         * @return String file content (null upon failure)
         */
        public function read($url)
        {
            $ch = curl_init();

            //Options
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_URL, $url);

            //Read URL
            $content = curl_exec($ch);

            //Close ressources
            curl_close($ch);

            //Return file content
            return $content ? $content : null;
        }
    }

?>
