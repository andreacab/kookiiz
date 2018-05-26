<?php
    /*******************************************************
    Title: Request
    Authors: Kookiiz Team
    Purpose: Handle request params
    ********************************************************/

    //Represents a handler for GET and POST requests
    class RequestHandler
    {
        /**********************************************************
        CONSTRUCTOR
        ***********************************************************/

        //Class constructor
        //-> (void)
        public function __construct()
        {
            //Prevent session injection
            if(isset($_REQUEST['_SESSION'])) die();
        }

        /**********************************************************
        GET
        ***********************************************************/

        //Return a request param
        //@param (string):  param name
        //@mode (string):   "get", "post" or "request" (defaults to "request")
        //->value (mixed): param value with slashes stripped
        public function get($param, $mode = 'REQUEST')
        {
            //Retrieve appropriate array
            $ARRAY = array();
            $method = strtoupper($mode);
            switch($method)
            {
                case 'FILES':
                    $ARRAY = $_FILES;
                    break;
                case 'GET':			
                    $ARRAY = $_GET;
                    break;
                case 'POST':		
                    $ARRAY = $_POST;
                    break;
                case 'REQUEST':		
                    $ARRAY = $_REQUEST;
                    break;
                default:
                    return null;
            }

            //Return required variable or null
            if(isset($ARRAY[$param]))
            {
                if($method != 'FILES' && get_magic_quotes_gpc())
                    return stripslashes($ARRAY[$param]);
                else
                    return $ARRAY[$param];
            }
            else
                return null;
        }

        /**********************************************************
        QUERY FROM/TO SESSION
        ***********************************************************/

        //Retrieve and parse query string from session
        //@page (string): query string page name
        //-> (void)
        public function queryFromSession($page)
        {
            $query = Session::get('query_' . $page);
            if($query)
            {
                parse_str($query, $_GET);
                Session::clear('query_' . $page);
            }
        }

        //Store query string in session
        //@page (string): query string page name
        //-> (void)
        public function queryToSession($page)
        {
            if($_SERVER['QUERY_STRING'])
                Session::set('query_' . $page, $_SERVER['QUERY_STRING']);
        }
    }
?>
