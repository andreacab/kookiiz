<?php
    /*******************************************************
    Title: Pictures library
    Authors: Kookiiz Team
    Purpose: Interact with Kookiiz pictures database
    ********************************************************/

    //Dependencies
    require_once '../class/dblink.php';
    require_once '../class/exception.php';
    require_once '../class/globals.php';
    require_once '../class/request.php';
    require_once '../class/user.php';

    //Represents a library of pictures
    class PicturesLib
    {
        /**********************************************************
        CONSTANTS
        ***********************************************************/

        //Allowed picture extensions
        private static $extensions = array('.jpg', '.jpeg', '.gif', '.png');

        //Picture upload settings
        private static $settings = array(
            'articles'  => array(
                'dir'       => 'articles',
                'height'    => 250,
                'quality'   => 100,
                'size'      => 2000000,
                'thumb'     => false,
                'width'     => 250
            ),
            'news'      => array(
                'dir'       => 'news',
                'height'    => 200,
                'quality'   => 100,
                'size'      => 500000,
                'thumb'     => false,
                'width'     => 200
            ),
            'partners'  => array(
                'dir'       => 'partners',
                'height'    => 200,
                'quality'   => 100,
                'size'      => 500000,
                'thumb'     => false,
                'width'     => 200
            ),
            'recipes'   => array(
                'dir'       => 'recipes',
                'height'    => 150,
                'quality'   => 100,
                'size'      => 5000000,
                'thumb'     => array(
                    'height'    => 50,
                    'width'     => 50
                ),
                'width'     => 150
            ),
            'users'     => array(
                'dir'       => 'users',
                'height'    => 100,
                'quality'   => 100,
                'size'      => 200000,
                'thumb'     => array(
                    'height'    => 50,
                    'width'     => 50
                ),
                'width'     => 100
            )
        );

        /**********************************************************
        PROPERTIES
        ***********************************************************/

        private $DB;
        private $Request;
        private $User;

        /**********************************************************
        CONSTRUCTOR
        ***********************************************************/

        /**
         * Class constructor
         * @param DBLink $DB open database connection
         * @param User $User connected user
         */
        public function __construct(DBLink &$DB, User &$User)
        {
            $this->DB      = $DB;
            $this->User    = $User;
            $this->Request = new RequestHandler();
        }

        /**********************************************************
        CHECK
        ***********************************************************/

        /**
         * Check file extensions for picture upload process
         * @return String picture extension 
         */
        private function checkExtension()
        {
            foreach(self::$extensions as $ext)
            {
                if(preg_match("/$ext\$/i", $_FILES['picture']['name']))
                {
                    return $ext;
                    break;
                }
            }
            throw new KookiizException('picture', 11);
        }

        /**
         * Check for uploaded file errors
         */
        private function checkFileErrors()
        {
            if($_FILES['picture']['error'] > 0)
                throw new KookiizException('picture', $_FILES['picture']['error']);
        }

        /**
         * Check uploaded file size
         * @param Int $max maximum allowed file size
         */
        private function checkFileSize($max)
        {
            if(filesize($_FILES['picture']['tmp_name']) > $max)
                throw new KookiizException('picture', 2);
        }
        
        /**********************************************************
        CLEAN
        ***********************************************************/
        
        /**
         * Remove orphan pictures from server
         * @return Array list of deleted picture paths for each picture type
         */
        public function cleanOrphans()
        {
            $orphans = array();
            foreach(self::$settings as $type => $params)
            {
                switch($type)
                {
                    case 'recipes':
                        $request = 'SELECT recipes_pictures.pic_id, pic_path'
                                . ' FROM recipes_pictures'
                                    . ' LEFT JOIN recipes USING(pic_id)'
                                . ' WHERE recipe_id IS NULL';
                        break;
                    
                    case 'users':
                        $request = 'SELECT users_pictures.pic_id, pic_path'
                                . ' FROM users_pictures'
                                    . ' LEFT JOIN users USING(pic_id)'
                                . ' WHERE user_id IS NULL';
                        break;
                    
                    default:
                        continue 2; //Continue switch + foreach !
                        break;
                }
                
                $orphans[$type] = array();
                $stmt = $this->DB->query($request);
                while($pic = $stmt->fetch(PDO::FETCH_ASSOC))
                {
                    $orphans[$type][] = $pic['pic_path'];
                    $this->delete($type, (int)$pic['pic_id']);
                }
            }
            return $orphans;
        }

        /**********************************************************
        COUNT
        ***********************************************************/

        /**
         * Count pictures in directory
         * @param String $path picture directory
         * @return Int picture count 
         */
        private function countPics($path)
        {
            $folder = opendir($path);
            if(!$folder) return 0;

            //Loop through files in folder
            $file_count = 0;
            while(($file = readdir($folder)) !== false)
            {
                if($file != '.' && $file != '..') 
                    $file_count++;
            }
            return $file_count;
        }

        /**********************************************************
        DELETE
        ***********************************************************/

        /**
         * Remove a picture from library
         * @param String $type picture type
         * @param Int $pic_id unique picture ID
         */
        public function delete($type, $pic_id)
        {
            //Retrieve appropriate parameters for current type
            $settings = self::getSettings($type);
            if(is_null($settings))
                throw new KookiizException('picture', 13);

            //Define database table name
            $pic_table = $settings['dir'] . '_pictures';

            //Retrieve picture path
            $request = "SELECT * FROM $pic_table WHERE pic_id = ?";
            $stmt = $this->DB->query($request, array($pic_id));
            $data = $stmt->fetch();
            if($data)
            {
                //Big picture path
                $big_path = $data['pic_path'];

                //Small picture path
                $small_path     = $data['pic_path'];
                $path_split     = explode('/', $small_path);
                $filename       = $path_split[count($path_split) - 1];
                $filename_split = explode('.', $filename);
                $filename_split[count($filename_split) - 2] .= '_small';
                $path_split[count($path_split) - 1] = implode('.', $filename_split);
                $small_path     = implode('/', $path_split);

                //Delete picture files
                if(file_exists($big_path))      
                    unlink($big_path);
                if(file_exists($small_path))    
                    unlink($small_path);

                //Delete entry in database
                $request = "DELETE FROM $pic_table WHERE pic_id = ?";
                $this->DB->query($request, array($pic_id));
            }
        }

        /**********************************************************
        DISPLAY
        ***********************************************************/

        /**
         * Echoes requested picture file
         */
        public function display()
        {
            $pic_id = $this->Request->get('pic_id');
            $type   = $this->Request->get('type');
            $thumb  = $this->Request->get('thumb');
            $jpeg   = $this->Request->get('jpeg');

            //Set defaults
            if(is_null($pic_id)) die();
            $thumb = is_null($thumb) ? false : (bool)$thumb;
            $jpeg  = is_null($jpeg) ? false : (bool)$jpeg;

            //Get picture info
            list($path, $ext) = $this->getInfo($type, $pic_id, $thumb);
            if($path)
            {
                //Check if file exists
                if(file_exists($path))
                {
                    //Set appropriate header and create image according to picture extension
                    switch($ext)
                    {
                        case '.gif':
                            $image = imagecreatefromGIF($path);
                            if($image)
                            {
                                if($jpeg)
                                {
                                    header('Content-type: image/jpeg');
                                    imagejpeg($image);
                                }
                                else
                                {
                                    header('Content-type: image/gif');
                                    imagegif($image);
                                }
                            }
                            else 
                                $this->displayDefault($type, $thumb, $jpeg);
                            break;

                        case '.jpeg':
                        case '.jpg':
                            $image = imagecreatefromJPEG($path);
                            if($image)
                            {
                                header('Content-type: image/jpeg');
                                imagejpeg($image);
                            }
                            else 
                                $this->displayDefault($type, $thumb, $jpeg);
                            break;

                        case '.png':
                            $image = imagecreatefromPNG($path);
                            if($image)
                            {
                                if($jpeg)
                                {
                                    header('Content-type: image/jpeg');
                                    imagejpeg($image);
                                }
                                else
                                {
                                    header('Content-type: image/png');
                                    imagepng($image);
                                }
                            }
                            else 
                                $this->displayDefault($type, $thumb, $jpeg);
                            break;

                        default:
                            $this->displayDefault($type, $thumb, $jpeg);
                            break;
                    }
                }
                else 
                    $this->displayDefault($type, $thumb, $jpeg);
            }
            else 
                $this->displayDefault($type, $thumb, $jpeg);
        }

        /**
         * Display a default picture with provided parameters
         * @param String $type picture type
         * @param Bool $thumb whether to display a thumbnail version
         * @param Bool $jpeg whether to force JPEG format
         */
        private function displayDefault($type, $thumb = false, $jpeg = false)
        {
            switch($type)
            {
                case 'articles':
                    break;
                case 'news':
                    break;
                case 'partners':
                    break;
                case 'recipes':
                    $dir = '../themes/' . C::THEME . '/pictures/recipes/';
                    if($thumb)  
                        $image = imagecreatefromPNG($dir . C::RECIPE_THUMB_DEFAULT);
                    else        
                        $image = imagecreatefromPNG($dir . C::RECIPE_PIC_DEFAULT);
                    break;
                case 'users':
                    $dir = '../pictures/users/';
                    if($thumb)  
                        $image = imagecreatefromPNG($dir . C::USER_THUMB_DEFAULT);
                    else        
                        $image = imagecreatefromPNG($dir . C::USER_PIC_DEFAULT);
                    break;
            }
            if(!isset($image)) die();

            //Display picture
            if($jpeg)
            {
                //Always JPEG
                header('Content-type: image/jpeg');
                imagejpeg($image);
            }
            else
            {
                //Default PNG
                header('Content-type: image/png');
                imagepng($image);
            }
        }

        /**********************************************************
        GET
        ***********************************************************/

        /**
         * Get path and extension for specific picture
         * @param String $type picture type
         * @param Int $id unique picture ID
         * @param Bool $thumb whether to return the path for a thumbnail
         * @return Array picture path and extension
         */
        private function getInfo($type, $id, $thumb = false)
        {
            //Retrieve settings for provided picture type
            $settings = self::getSettings($type);
            if(is_null($settings)) return null;

            //Define pictures table
            $pic_table = $settings['dir'] . '_pictures';

            //Retrieve picture data
            $request = 'SELECT pic_path, pic_ext'
                    . " FROM $pic_table"
                    . ' WHERE pic_id = ?';
            $stmt = $this->DB->query($request, array($id));
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            if($data)
            {
                //Retrieve pic path for thumbnail
                if($thumb && $settings['thumb'])
                {
                    //Insert "_small" suffix right before picture extension
                    $pathArr  = explode('/', $data['pic_path']);
                    $filename = $pathArr[count($pathArr) - 1];
                    $fileArr  = explode('.', $filename);
                    $fileArr[count($fileArr) - 2] .= '_small';
                    $pathArr[count($pathArr) - 1] = implode('.', $fileArr);
                    $path = implode('/', $pathArr);
                }
                //Retrieve pic path for standard size
                else 
                    $path = $data['pic_path'];

                //Return picture path and extension
                return array($path, $data['pic_ext']);
            }           
            else 
                return null;
        }

        /**
         * Return settings for request picture type
         * @param String $type picture type
         * @return Array associative array of settings (null if type is unknown)
         */
        public static function getSettings($type)
        {
            if(isset(self::$settings[$type]))
                return self::$settings[$type];
            else 
                return null;
        }

        /**********************************************************
        PICTURE OPERATIONS
        ***********************************************************/

        /**
         * Create picture file on disk from image object
         * @param Image $image image object source
         * @param String $path picture file destination path
         * @param String $ext picture file extension
         * @param Int $quality factor
         */
        private function picCreate(&$image, $path, $ext, $quality)
        {
            $success = false;
            switch($ext)
            {
                case '.gif':
                    $success = imagegif($image, $path);
                    break;
                case '.jpeg':	//Same as below
                case '.jpg':    //JPG quality spans from 0 to 100
                    $quality = round(100 * $quality / 100);
                    $success = imagejpeg($image, $path, $quality);
                    break;
                case '.png':    //PNG quality spans from 0 to 9
                    $quality = round(9 * $quality / 100);
                    $success = imagepng($image, $path, $quality);
                    break;
            }
            if(!$success) 
                throw new KookiizException('picture', 12);
        }

        /**
         * Create image object from picture file
         * @param String $path picture file path
         * @param String $ext picture file extension
         * @return Image object 
         */
        private function picFromFile($path, $ext)
        {
            switch($ext)
            {
                case '.gif':
                    $source = imagecreatefromGIF($path);
                    break;
                case '.jpeg':
                case '.jpg':
                    $source = imagecreatefromJPEG($path);
                    break;
                case '.png':
                    $source = imagecreatefromPNG($path);
                    break;
            }
            return $source;
        }
        /**
         * Generate random picture path
         * @param String $dir picture destination directory
         * @param String $ext picture file extension
         * @return Array paths for picture and thumbnail
         */
        private function picPath($dir, $ext)
        {
            $path           = "../pictures/$dir/";
            $pics_count     = $this->countPics($path);
            $random_factor  = rand();
            
            $filename       = $dir . ($pics_count + 1) . '_' . $random_factor . $ext;
            $filepath       = $path . $filename;
            $filename_small = $dir . ($pics_count + 1) . '_' . $random_factor . '_small' . $ext;
            $filepath_small = $path . $filename_small;

            return array($filepath, $filepath_small);
        }

        /**
         * Resize picture object
         * @param Image $final picture object
         * @param Image $source picture object
         * @param Int $new_width final picture width in pixels
         * @param Int $new_height final picture height in pixels
         * @param Int $old_width initial picture width in pixels
         * @param Int $old_height initial picture heigth in pixels
         */
        private function picResize(&$final, &$source, $new_width, $new_height, $old_width, $old_height)
        {
            imagecopyresampled($final, $source,  0,  0,  0,  0,  $new_width,  $new_height,  $old_width,  $old_height);
        }

        /**
         * Get picture resize dimensions, preserving proportions
         * @param Int $old_width initial picture width
         * @param Int $old_height initial picture height
         * @param Int $max_width maximum picture width
         * @param Int $max_height maximum picture height
         * @return Array resized picture width and height
         */
        private function picResizeDim($old_width, $old_height, $max_width, $max_height)
        {
            if($old_width <= $max_width && $old_height <= $max_height)
            {
                if($max_width - $old_width < $max_height - $old_height)
                {
                    $new_width = $max_width;
                    $new_height = (int)(($new_width/$old_width) * $old_height);
                }
                else
                {
                    $new_height = $max_height;
                    $new_width = (int)(($new_height/$old_height) * $old_width);
                }
            }
            else if($old_width > $max_width && $old_height <= $max_height)
            {
                $new_width = $max_width;
                $new_height = (int)(($new_width/$old_width) * $old_height);
            }
            else if($old_width <= $max_width && $old_height > $max_height)
            {
                $new_height = $max_height;
                $new_width = (int)(($new_height/$old_height) * $old_width);
            }
            else if($old_width > $max_width && $old_height > $max_height)
            {
                if($old_width - $max_width > $old_height - $max_height)
                {
                    $new_width = $max_width;
                    $new_height = (int)(($new_width/$old_width) * $old_height);
                }
                else
                {
                    $new_height = $max_height;
                    $new_width = (int)(($new_height/$old_height) * $old_width);
                }
            }
            return array($new_width, $new_height);
        }

        /**
         * Return dimensions of a picture file
         * @param String $path picture file
         * @return Array picture width and heigth
         */
        private function picSize($path)
        {
            return getimagesize($path);
        }

        /**
         * Insert picture path in database
         * @param String $dir picture directory
         * @param String $path picture path
         * @param String $ext picture extension
         * @return Int new picture ID
         */
        private function picToDB($dir, $path, $ext)
        {
            //Add an entry in database to retrieve this picture
            $pictures_table = $dir . '_pictures';
            $request = "INSERT INTO $pictures_table (pic_path, pic_ext)"
                        . ' VALUES (?, ?)';
            $this->DB->query($request, array($path, $ext));

            //Return picture ID
            return $this->DB->insertID();
        }

        /**********************************************************
        OWNER
        ***********************************************************/

        /**
         * Retrieve ID of picture owner
         * @param String $type picture type
         * @param Int $pic_id unique picture ID
         * @return Int unique ID of picture owner (0 for none, -1 for admin)
         */
        public function ownerGet($type, $pic_id)
        {
            switch($type)
            {
                case 'articles':
                case 'news':
                case 'partners':
                    //No owner for these types of picture
                    return -1;
                    break;

                case 'recipes':
                    //Owner is recipe author
                    $request = 'SELECT author_id FROM recipes WHERE pic_id = ?';
                    $stmt = $this->DB->query($request, array($pic_id));
                    $data = $stmt->fetch();
                    if($data)   
                        return $data['author_id'];
                    else        
                        return 0;
                    break;

                case 'users':
                    //Owner is user with this picture ID as avatar
                    $request = 'SELECT user_id FROM users WHERE pic_id = ?';
                    $stmt = $this->DB->query($request, array($pic_id));
                    $data = $stmt->fetch();
                    if($data)   
                        return $data['user_id'];
                    else        
                        return 0;
                    break;

                default:
                    return 0;
                    break;
            }
        }

        /**********************************************************
        UPLOAD
        ***********************************************************/

        /**
         * Upload a new picture and store its path in database
         * @return Int new pic ID
         */
        public function upload()
        {
            //Parameters
            $type = $this->Request->get('type');
            $ext  = $this->checkExtension();

            //Retrieve appropriate settings for current type
            $settings = self::getSettings($type);
            if(is_null($settings))
                //Unknown picture type
                throw new KookiizException('picture', 10);

            //Check authorizations
            $this->uploadAuthorize($type);
            //Check for file errors
            $this->checkFileErrors();
            //Check file size
            $this->checkFileSize($settings['size']);

            //Create random file path
            list($path, $path_thumb) = $this->picPath($settings['dir'], $ext);
            
            //Try to move the picture to the final destination folder
            $this->uploadStore($path);           

            //Create picture
            $source = $this->picFromFile($path, $ext);
            list($old_width, $old_height) = $this->picSize($path);
            list($new_width, $new_height) = $this->picResizeDim($old_width, $old_height, $settings['width'], $settings['height']);
            $final  = imagecreatetruecolor($new_width,  $new_height);
            $this->picResize($final, $source, $new_width, $new_height, $old_width, $old_height);
            $this->picCreate($final, $path, $ext, $settings['quality']);
            
            //Create thumbnail
            if($settings['thumb'])
            {
                list($thumb_width, $thumb_height) = $this->picResizeDim($new_width, $new_height, $settings['thumb']['width'], $settings['thumb']['height']);
                $thumb = imagecreatetruecolor($thumb_width,  $thumb_height);
                $this->picResize($thumb, $final, $thumb_width, $thumb_height, $new_width, $new_height);
                $this->picCreate($thumb, $path_thumb, $ext, $settings['quality']);
            }

            //Store pic path and return new pic ID
            return $this->picToDB($settings['dir'], $path, $ext);
        }

        /**
         * Check if current user has authorization for upload
         * @param String $type picture type
         */
        private function uploadAuthorize($type)
        {
            $allowed = array('users');
            if($this->User->isLogged()) 
                array_push($allowed, 'recipes');
            if($this->User->isAdmin())  
                array_push($allowed, 'articles', 'news', 'partners');
            if(!in_array($type, $allowed))
                throw new KookiizException('session', Error::SESSION_UNAUTHORIZED);
        }

        /**
         * Move uploaded file to destination folder
         * @param String $path destination path
         */
        private function uploadStore($path)
        {
            if(is_uploaded_file($_FILES['picture']['tmp_name']))
            {
                if(!move_uploaded_file($_FILES['picture']['tmp_name'], $path))
                    throw new KookiizException('picture', 8);
            }
            else 
                throw new KookiizException('picture', 9);
        }
    }
?>