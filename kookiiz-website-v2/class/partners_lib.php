<?php
    /*******************************************************
    Title: Partners library
    Authors: Kookiiz Team
    Purpose: Manage Kookiiz partners
    ********************************************************/

    //Dependencies
    require_once '../class/dblink.php';
    require_once '../class/exception.php';
    require_once '../class/globals.php';

    //Represents a library of partners
    class PartnersLib
    {
        /**********************************************************
        PROPERTIES
        ***********************************************************/

        private $DB;    //database link

        /**********************************************************
        CONSTRUCTOR
        ***********************************************************/

        //Class constructor
        //@DB (object): open database connection
        //-> (void)
        public function __construct(DBLink &$DB)
        {
            $this->DB = $DB;
        }

        /**********************************************************
        ADD
        ***********************************************************/

        //Add a new partner in database
        //@name (string):       name of the partner
        //@link (string):       link to its website
        //@pic_link (string):   link to its picture
        //@valid (bool):        should the partner be displayed (do we have a deal)?
        //->partner_id (int): ID of the new partner (0 in case of failure)
        public function add($name, $link, $pic_link, $valid)
        {
            //Add "http://" in front of the links (if missing)
            if(stripos($link, 'http://') !== 0)     
                $link = 'http://' . $link;
            if(stripos($pic_link, 'http://') !== 0) 
                $pic_link = 'http://' . $pic_link;

            //Create partner
            $request = 'INSERT INTO partners (partner_name, partner_link, partner_pic, valid)'
                        . ' VALUES (:name, :link, :pic, :valid)';
            $stmt = $this->DB->query($request, array(
                ':name'     => $name,
                ':link'     => $link,
                ':pic'      => $pic_link,
                ':valid'    => $valid
            ));
            if($stmt->rowCount())   
                return $this->DB->insertID();
            else                    
                return 0;
        }
        
        /**********************************************************
        DELETE
        ***********************************************************/

        //Delete a partner from database
        //@partner_id (int): ID of the partner to delete
        //->error (int): error code (0 = no error)
        public function delete($partner_id)
        {
            //Delete partner
            $request = 'DELETE FROM partners WHERE partner_id = ?';
            $stmt = $this->DB->query($request, array($partner_id));

            //Partner could not be deleted
            if(!$stmt->rowCount()) 
                    throw new KookiizException('admin_partners', 3);
        }

        /**********************************************************
        EDIT
        ***********************************************************/

        //Edit an existing partner
        //@id (int):            ID of the partner
        //@name (string):       name of the partner
        //@link (string):       link to its website
        //@pic_link (string):   link to its picture
        //@valid (bool):        should the partner be displayed (do we have a deal)?
        //-> (void)
        public function edit($id, $name, $link, $pic_link, $valid)
        {
            //Add "http://" in front of the links (if missing)
            if(stripos($link, 'http://') !== 0)     
                $link = 'http://' . $link;
            if(stripos($pic_link, 'http://') !== 0) 
                $pic_link = 'http://' . $pic_link;

            //Create partner
            $request = 'UPDATE partners'
                    . ' SET partner_name = :name, partner_link = :link,'
                        . ' partner_pic = :pic, valid = :valid'
                    . ' WHERE partner_id = :id';
            $stmt = $this->DB->query($request, array(
                ':name'     => $name,
                ':link'     => $link,
                ':pic'      => $pic_link,
                ':valid'    => $valid,
                ':id'       => $id
            ));

            //Partner could not be edited
            if(!$stmt->rowCount()) 
                throw new KookiizException('admin_partners', 4);
        }
        
        /**********************************************************
        LISTING
        ***********************************************************/

        //List all available partners
        //->partners (array): list of partners
        public function listing()
        {
            $request = 'SELECT * FROM partners ORDER by partner_name';
            $stmt = $this->DB->query($request);
            return $stmt->fetchAll();
        }

        /**********************************************************
        LOAD
        ***********************************************************/

        //Load partner information (or default partner)
        //@partner_id (int):    ID of the partner to load
        //@enforce (bool):      if true the partner is returned even if it is not valid (for admin edition)
        //->partner (object): properties of selected partner (or default partner, or false if not found)
        public function load($partner_id, $enforce = false)
        {
            $request = 'SELECT * FROM partners WHERE partner_id = ?';
            if(!$enforce) 
                $request .= ' AND valid = 1';
            $stmt = $this->DB->query($request, array($partner_id));
            $data = $stmt->fetch();
            if(!$data)
            {
                if($enforce) 
                    return false;
                else
                {
                    //Load default partner
                    $request = 'SELECT * FROM partners'
                            . ' WHERE partner_id = ' . C::PARTNER_DEFAULT;
                    $stmt = $this->DB->query($request);
                    return $stmt->fetch();
                }
            }
            else 
                return $data;
        }
    }
?>
