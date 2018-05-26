<?php
    /*******************************************************
    Title: Chefs library
    Authors: Kookiiz Team
    Purpose: Manage database of chefs
    ********************************************************/

    //Dependencies
    require_once '../class/dblink.php';
    require_once '../class/user.php';

    //Represents a library of chefs
    class ChefsLib
    {
        /**********************************************************
        PROPERTIES
        ***********************************************************/

        private $db;    //database link
        private $user;  //user connected to the library

        /**********************************************************
        CONSTRUCTOR
        ***********************************************************/

        //Class constructor
        //@db (object):     open database connection
        //@user (object):   user connected to the library
        //-> (void)
        public function __construct(DBLink $db, User $user)
        {
            $this->db   = $db;
            $this->user = $user;
        }

        /**********************************************************
        LOAD
        ***********************************************************/

        //Load information for provided chef ID
        //@chef_id (int):   ID of the chef (defaults to random)
        //@full (bool):     load a full profile (true) or a short preview (false)
        //->chef (object): list of chef properties (false if not found)
        public function load($chef_id = 0, $full = true)
        {
            //Retrieve chef data from database
            $params = array();
            if($full)   $request = 'SELECT * FROM chefs ';
            else        $request = 'SELECT chef_id, chef_name, chef_pic FROM chefs ';
            if($chef_id > 0)
            {
                $request .= 'WHERE chef_id = ?';
                $params[] = $chef_id;
            }
            else $request .= ' ORDER BY RAND() LIMIT 1';
            $stmt = $this->db->query($request, $params);
            $data = $stmt->fetch();

            //Build chef structure
            if($data)
            {
                $chef = array(
                    'id'    => (int)$data['chef_id'],
                    'name'  => htmlspecialchars($data['chef_name'], ENT_COMPAT, 'UTF-8'),
                    'pic'   => htmlspecialchars($data['chef_pic'], ENT_COMPAT, 'UTF-8')
                );
                if($full) $chef['bio'] = htmlspecialchars($data['chef_bio'], ENT_COMPAT, 'UTF-8');
            }

            //Return chef data
            return $chef ? $chef : false;
        }
    }
?>
