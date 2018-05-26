<?php
    /*******************************************************
    Title: Theme Builder
    Authors: Kookiiz Team
    Purpose: Build CSS style sheet for a theme
    ********************************************************/

    //Represents a builder for a theme
    class ThemeBuilder
    {
        /*******************************************************
        PROPERTIES
        ********************************************************/

        private static $GROUPS;
        private $theme;
        private $errors;
        private $updates;

        /*******************************************************
        CONSTRUCTOR
        ********************************************************/

        //Class constructor
        //-> (void)
        public function __construct($theme)
        {
            self::$GROUPS = require '../themes/groups.php';
            $this->theme = $theme;
        }

        /*******************************************************
        RUN
        ********************************************************/

        //Build theme
        //-> (void)
        public function run()
        {
            //Storage for errors and updated files
            $this->errors  = array();
            $this->updates = array();

            //Style update timestamp
            $style_url  = "../themes/{$this->theme}/style.php";
            $style_time = filemtime($style_url);

            //Loop through CSS style sheet groups
            foreach(self::$GROUPS as $group => $files)
            {
                $group_url = "../themes/{$this->theme}/css/$group.css";
                
                //Check if any group file has a more recent timestamp than the group itself
                $this->updates[$group] = array();
                if(file_exists($group_url))
                {
                    $group_time = filemtime($group_url);
                    if($style_time > $group_time)
                        //Style definitions are more recent than CSS group file
                        $this->updates[$group][] = $style_url;
                    else
                    {
                        //Loop through group files
                        foreach($files as $source_url)
                        {
                            @$file_time = filemtime($source_url);
                            if($file_time && $file_time > $group_time)
                                $this->updates[$group][] = $source_url;
                        }
                    }
                }
                //No group file -> all sub-files are more recent
                else 
                    $this->updates[$group] = $files;
                
                //Skip group if no sub-file was updated
                if(!count($this->updates[$group])) continue;

                //Try to open or create CSS file for group
                @$group_file = fopen($group_url, 'wb');
                if(!$group_file)
                    throw new Exception("Unable to open or create file '$group_url'!");

                //Start buffering
                ob_start();

                //Loop through group files
                foreach($files as $source_url)
                {
                    @$source = file($source_url);
                    if(!$source)
						throw new Exception("Unable to read file '$source_url'");

                    //Loop through each line of the source file
                    foreach($source as $index => $line)
                    {
                        //Search and replace variables
                        preg_match_all('/\$([A-Za-z0-9_-]+)/', $line, $matches);
                        if(count($matches[1]))
                        {
                            foreach($matches[1] as $const)
                            {
                                if(defined('Style::' . $const))
                                {
                                    $value = constant('Style::' . $const);
                                    $line = preg_replace('/\$' . $const . '/', $value, $line);
                                }
                                else
                                {
                                    //Store error for unknown constant
                                    $this->errors[] = array(
                                        'group' => $group,
                                        'file'  => $source_url,
                                        'line'  => $index,
                                        'const' => $const
                                    );
                                }
                            }
                        }

                        //Search and replace theme path
                        $line = preg_replace('/THEME/', "/themes/{$this->theme}/pictures", $line);

                        //Send current line to buffer
                        echo $line;
                    }
                    //Add carriage return at end of source
                    echo "\n\n";
                }

                //Write CSS group file and clean buffer
                $content = ob_get_contents();
                fwrite($group_file, $content);
                ob_end_clean();
            }
        }

        /*******************************************************
        SUMMARY
        ********************************************************/

        //Display build process summary
        //-> (void)
        public function summary()
        {
            echo "BUILD PROCESS SUMMARY<br/>";
            echo "Theme: {$this->theme}<br/><br/>";

            //Loop through updates
            foreach($this->updates as $group => $files)
            {
                echo "Group '<strong>$group</strong>'<br/>";
                if(count($files))
                {
                    echo "Group file was built to reflect updates on:<br/>";
                    foreach($files as $file)
                        echo "$file<br/>";
                }
                else
                    echo "Group file was skipped because no source CSS file update was detected.<br/>";
                echo "<br/>";
            }
            echo "<br/>";

            echo "<strong>Errors</strong><br/>";
            if(count($this->errors))
            {
                //Loop through errors
                foreach($this->errors as $error)
                    echo "Group '{$error['group']}': in file '{$error['file']}' on line #{$error['line']}, found unknown constant '{$error['const']}'<br/>";
            }
            else
                echo "No error occurred.";
        }
    }
?>
