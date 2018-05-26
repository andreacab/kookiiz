<?php
	/**********************************************************
    Title: Ingredients translate (ADMIN)
    Authors: Kookiiz Team
    Purpose: Translate ingredient names and tags
    ***********************************************************/

	/**********************************************************
	INFO
	***********************************************************/

    /*
     * This script makes calls to Google Translate server in order to translate
     * ingredients names and tags.
     *
     * Required parameters:
     * @source (string):    ingredients source ("ca" => Canadian DB, "ch" => Swiss DB, etc.)
     * @field (string):     name of the field to translate ("name" or "tags")
     * @langin (string):    identifier of the input language ("fr", "en", etc.)
     * @langout (string):   identifier of the output language ("fr", "en", etc.)
     *
     * In a first phase, the script reads the CSV file of the specified source in "ingredients" directory.
     * It looks for the value of the input field, sends a request to Google Translate server and store the results.
     * In a second phase, the scripts writes the translation results in the same CSV file.
     * Simultaneously, the updated content of the CSV file is displayed in the browser.
     *
     * Officially, Google does not allow automatic translation requests from a script. It it thus necessary to
     * limit the number of requests at once and to make a pause between requests to avoid beeing banned.
     * These parameters can be specified below:
    */

    define('SLEEP', 500000);    //Duration of the pause between two requests to Google servers, in microseconds.
    define('TOTAL', 50);        //Number of translations to perform during one script execution

	/**********************************************************
	SET UP
	***********************************************************/
	
	//Dependencies
	require_once '../class/dblink.php';
	require_once '../class/globals.php';
	require_once '../class/request.php';
	require_once '../class/user.php';
	
	//Init handlers
    $DB         = new DBLink('kookiiz');
    $Request    = new RequestHandler();
    $User       = new User($DB);
	
	//Load parameters
    $source     = $Request->get('source');
	$field      = $Request->get('field');
	$langin     = $Request->get('langin');
	$langout    = $Request->get('langout');

    //Ingredient properties file URL
    $URL = "../ingredients/ingredients_properties_$source.csv";
?>
<html lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<title>Ingredients translation</title>
</head>
<body>
<?php
    /**********************************************************
	CHECK-UP
	***********************************************************/

    //Allow execution from admins only
	if(!$User->isAdmin())
	{
		die('Only admins can run this script!');
	}

    //Check that all required parameters are provided
	if(!is_null($source) || !is_null($field) || !is_null($langin) || !is_null($langout))
	{
		die('Parameters "source", "field", "langin" and "langout" are required.');
	}
	
	//Try to open input ingredient properties file
	@$prop_in  = fopen($URL, 'rb');
    if(!$prop_in) die("Unable to open file '$URL' with reading rights!");
	
	//Retrieve field positions in file header
	$header = fgetcsv($prop_in, 0, ';');
    $input  = $field . '_' . $langin;
    $output = $field . '_' . $langout;
    $REF_POS    = array_search('ref', $header);
    $INPUT_POS  = array_search($input, $header);
    $OUTPUT_POS = array_search($output, $header);
    if(!$REF_POS)       die("Field 'ref' not found in header!");
    if(!$INPUT_POS)     die("Field '$input' not found in header!");
    if(!$OUTPUT_POS)    die("Field '$output' not found in header!");

    /**********************************************************
	TRANSLATION
	***********************************************************/
	
	//Request parameters
	$parameters = '&v=1.0';
	if(!empty($_SERVER['REMOTE_ADDR'])) 
	{
		$parameters .= '&userip=' . $_SERVER['REMOTE_ADDR'];
	}

    //Init output array and store header
    $output = array();
    $output[] = $header;
	
	//Loop through the file
    $index = 0;
	while(!feof($prop_in))
	{
		//Read one line of the CSV file
		$line = fgetcsv($prop_in, 0, ';');
		
		//Skip if the line contains no properties
		if(count($line) > 1)
		{
            $ref = $line[$REF_POS];
            if(is_numeric($ref)) $ref = (int)$ref;
			$field_in   = $line[$INPUT_POS];
			$field_out  = $line[$OUTPUT_POS];
			
			//Ignore if the field is empty OR already translated OR total is reached
			if($field_in && !$field_out && $index < TOTAL)
			{
				//Retrieve field in input language
				$field_encoded = urlencode(utf8_encode($field_in));
				
				//Translate fields
				$request    = "http://ajax.googleapis.com/ajax/services/language/translate?q=$field_encoded&langpair=$langin|$langout" . $parameters;
				$response   = file_get_contents($request);
				$field_data = json_decode($response);
				if($field_data && $field_data->responseStatus == 200)
				{
					//Extract translation result
					$line[$OUTPUT_POS] = strtolower(urldecode($field_data->responseData->translatedText));
				}
				else die("Failure of the translation request: $response");

                //Sleep between calls to Google Translate server
				usleep(SLEEP);
                
                //Increase index
				$index++;
			}
            
            //Store output data
            $output[] = $line;
		}
	}
    
    //Close ingredient properties file
	fclose($prop_in);

    /**********************************************************
	SAVING AND DISPLAY
	***********************************************************/

    //Open ingredient properties file with write rights
	@$prop_out = fopen($URL, 'wb');
    if(!$prop_out) die("Unable to open file '$URL' with writing rights!");

	//Write and display data
    echo "<p>Updated content of '$URL':</p><br/>";
	echo "<table><tbody>\n";
	foreach($output as $line)
	{
		//Write current line on output file
		fputcsv($prop_out, $line, ';');
		
		//Display translation data in browser
        $ref        = (int)$line[0];
		$field_in   = utf8_encode($line[$INPUT_POS]);
		$field_out  = utf8_encode($line[$OUTPUT_POS]);
		echo "<tr>\n",
                "<td>", ($i == 0 ? "<strong>" : ''), $ref, ($i == 0 ? '</strong>' : ''), "</td>\n",
				"<td>", ($i == 0 ? "<strong>" : ''), $field_in, ($i == 0 ? '</strong>' : ''), "</td>\n",
				"<td>", ($i == 0 ? "<strong>" : ''), $field_out, ($i == 0 ? '</strong>' : ''), "</td>\n",
			"</tr>\n";
	}
	echo "</tbody></table>\n";
?>