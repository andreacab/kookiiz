<?php
    /*******************************************************
    Title: Lang to CSV
    Authors: Kookiiz Team
    Purpose: Export PHP language arrays into a CSV file
    ********************************************************/

    if(isset($_GET['lang']))
    {
        $lang = $_GET['lang'];
    }
    else die('Missing parameter "lang"!');

    $success = include "../lang/$lang.php";
    if(!$success) die("Could not find file '../lang/$lang.php'!");
    $success = include "../lang/$lang.js.php";
    if(!$success) die("Could not find file '../lang/$lang.js.php'!");

    //Open or create CSV file
    $file = fopen("../lang/$lang.csv", 'wb');
    if(!$file) die("Could not create CSV file '../lang/$lang.csv'!");

    //Header
    $header = array('ARRAY', 'INDEX', 'VALUE', 'TYPE');
    fputcsv($file, $header, ';');

    $arrays = array();
    $special = array('_COOKIE', '_FILES', '_GET', 'GLOBALS', '_POST', '_REQUEST', '_SERVER', '_SESSION');
    foreach($GLOBALS as $name => $array)
	{
		//Check that array is not among special PHP arrays and has a capitalized name
		if(is_array($array) && !in_array($name, $special) && strtoupper($name) == $name)
		{
            $name_exploded = explode('_', $name);
            $js_only       = $name_exploded[0] == 'JS' ? 1 : 0;
            $name_clean     = implode('_', array_slice($name_exploded, $js_only ? 1 : 0));
            $arrays[$name_clean] = array('content' => $array, 'js_only' => $js_only);
        }
	}

    //Sort arrays by name
    ksort($arrays);
    foreach($arrays as $name => $data)
    {
        $js_only = $data['js_only'];
        foreach($data['content'] as $key => $value)
        {
            fputcsv($file, array($name, $key, utf8_decode($value), $js_only), ';');
        }
    }
?>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <title>Lang to CSV</title>
    </head>
    <body>
    <?php
        echo 'Imported data: <pre>';
        var_dump($arrays);
        echo '</pre>';
    ?>
    </body>
</html>