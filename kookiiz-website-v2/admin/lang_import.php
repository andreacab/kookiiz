<?php
	/**********************************************************
	Title: Lang import (ADMIN)
	Authors: Kookiiz Team
	Purpose: Import CSV language files in database
	***********************************************************/

    /**********************************************************
	INFO
	***********************************************************/

    /*
     * This script reads and imports the CSV language file into the database
     *
     * Required parameters
     * @lang (string): language identifier ("fr", "en", etc.)
     *
     * The CSV language file is expected to be stored in the "lang" folder ("../lang/") and be named as "lang.csv"
     * It should contain the following columns:
     *      "ARRAY":    name of the language array (limited to 50 chars)
     *      "INDEX":    numeric or string index (max. 20 chars)
     *      "TYPE":     integer value to specify the array scope (0 = "all", 1 = "JS only", 2 = "PHP only")
     * Then one column per language with the two-chars lang code as a header ("fr", "en", "de", etc.)
     * The CSV separator is defined below.
     *
     * The script displays which array items have been inserted, updated and deleted.
    */

    define('SEPARATOR', ';');
	
	/**********************************************************
	SET-UP
	***********************************************************/

    //Constants
    define('KVERSION', 2);

    //Dependencies
	require_once $_SERVER['DOCUMENT_ROOT'] . '/class/dblink.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/class/globals.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/class/request.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/class/user.php';
	
	//Init handlers
	$DB         = new DBLink('kookiiz');
    $Request    = new RequestHandler();
    $User       = new User($DB);

    //Load parameters
    $lang = $Request->get('lang');
    
    //Set locale for proper CSV reading
    setlocale(LC_ALL, 'en_US.UTF-8');
	
	/**********************************************************
	TESTS
	***********************************************************/
	
	//Allow execution from admins only
	if(!$User->isAdmin())
		die('Only admins can run this script!');

    //Open CSV source file
    $url = $_SERVER['DOCUMENT_ROOT'] . ((KVERSION == 2) ? '/lang/lang.v2.csv' : '/lang/lang.csv');
    @$file = fopen($url, 'rb');
    if(!$file) 
        die("Unable to open file '$url'!");

    //Read header line to find language index
    $INDEX  = -1;
    $header = fgetcsv($file, 0, SEPARATOR);
    foreach($header as $index => $field)
    {
        $field = strtolower($field);
        if($field === $lang)
        {
            //Store index and break
            $INDEX = $index;
            break;
        }
    }
    if($INDEX === -1)
        die("Could not find data for language '$lang'!");

    //Init language handler
    $LangDB = LangDB::getHandler($lang, 'ADMIN');
    //Check if language database exists
    if(!$LangDB->checkTable())
        die("Could not find database table lang.$lang!");

    /**********************************************************
	INSERT/UPDATE
	***********************************************************/

    //Loop through file lines
    $arrays = array();
    while(!feof($file))
    {
        //Read current line
        $line = fgetcsv($file, 0, SEPARATOR);

        //Skip empty row
        if(count($line) === 1) continue;

        //Parameters
        $name   = $line[0];
        $index  = $line[1];
        $type   = (int)$line[2];
        $value  = $line[$INDEX];

        //Insert item and store status
        $status = $LangDB->insert($name, $index, $type, $value);

        //Store array data and update status
        if(!isset($arrays[$name]))
        {
            $arrays[$name] = array(
                'content'   => array(),
                'type'      => $type,
                'updated'   => array()
            );
        }
        $arrays[$name]['content'][$index] = $value;
        $arrays[$name]['updated'][$index] = $status;
    }

    /**********************************************************
	DELETE
	***********************************************************/
	
    //Download complete language database
    $database = $LangDB->export();

	//Loop through current database
	$deleted = array();
    foreach($database as $name => $array)
    {
        //Current database array exists in CSV file
        if(isset($arrays[$name]))
        {
            //Loop through database array content
            foreach($array as $key => $value)
            {
                //A specific array key has been deleted in CSV file
                if(!isset($arrays[$name]['content'][$key]))
                {
                    //Delete specific array key from database
                    $LangDB->delete($name, $key);
                    //Store delete operation
                    $deleted[] = array('name' => $name, 'index' => $key);
                }
            }
        }
        //The entire array has been deleted from CSV file
        else
        {
            //Delete entire array from database
            $LangDB->delete($name);
            //Store delete operation
            $deleted[] = array('name' => $name, 'index' => null);
        }
    }

    /**********************************************************
	DISPLAY
	***********************************************************/
?>
<html lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<title>Language importation</title>
</head>
<body>
<?php
    //Display updated and deleted arrays
	echo '<strong>INSERTED AND UPDATED</strong><br/>';
    $updated = false;
	foreach($arrays as $name => $data)
    {
        foreach($data['updated'] as $index => $state)
        {
            switch($state)
            {
                case 0:
                    break;
                case 1:
                    echo "$name [$index] (inserted)<br/>";
                    $updated = true;
                    break;
                case 2:
                    echo "$name [$index] (updated)<br/>";
                    $updated = true;
                    break;
            }
        }
    }
	if(!$updated) echo 'none<br/>';
	echo '<strong>DELETED</strong><br/>';
	if(count($deleted))
	{
		foreach($deleted as $info)
		{
			$name   = $info['name'];
			$index  = $info['index'];           
			if(is_null($index)) 
                echo "$name (all)<br/>";
			else                
                echo "$name [$index]<br/>";
		}
	}
	else 
        echo 'none<br/>';
?>
</body>
</html>