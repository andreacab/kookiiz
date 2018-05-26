<?php
	/**********************************************************
    Title: Friend class
    Authors: Kookiiz Team
    Purpose: Define the friend object
    ***********************************************************/

    //Represents a friendship link
	class Friend
	{
        /**********************************************************
        ATTRIBUTES
        ***********************************************************/

		private $id;		//user ID
		private $status;	//status (online/offline)

        /**********************************************************
        CONSTRUCTOR
        ***********************************************************/
		
		//Class constructor
		//@id (int):        ID of the corresponding user
		//@status (bool):   is friend online (true) or offline (false)
		//-> (void)
		public function __construct($id, $status)
		{
			//Define main properties
			$this->id       = $id;
			$this->status   = $status;
		}

        //Return friend ID
        //->id (int): friend user ID
		public function getID()
		{
			return $this->id;
		}

        //Return friend status
        //->status (int): 0 = offline, 1 = online
		public function getStatus()
		{
			return $this->status;
		}

        /**********************************************************
        EXPORT
        ***********************************************************/

        //Export friend information in compact format
        //->friend (object): compact friend structure
        public function export()
        {
            return array(
                'i' => $this->id,
                's' => $this->status
            );
        }
	}