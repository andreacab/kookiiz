<?php
    /**********************************************************
    Title: Ingredient
    Authors: Kookiiz Team
    Purpose: Describe ingredient object
    ***********************************************************/

    //Dependencies
    require_once '../class/globals.php';

    //Represents a recipe ingredient
    class Ingredient
    {
        /**********************************************************
        PROPERTIES
        ***********************************************************/

        public $id    = 0;
        public $name  = '';
        public $tags  = '';
        public $cat   = 0;
        public $pic   = '';
        public $unit  = 0;
        public $wpu   = 0;
        public $price = 0;
        public $exp   = 1000;
        public $prob  = 0;

        /**********************************************************
        CONSTRUCTOR
        ***********************************************************/

        //Class constructor
        //-> (void)
        public function __construct()
        {
            $this->NUTRITION_VALUES = C::get('NUT_VALUES');
            foreach($this->NUTRITION_VALUES as $value)
                $this->{$value} = 0;
        }

        /**********************************************************
        CLEAN
        ***********************************************************/

        //Force ingredient properties to proper type
        //-> (void)
        public function clean()
        {
            $this->id    = (int)$this->id;
            $this->name  = htmlspecialchars($this->name, ENT_COMPAT, 'UTF-8');
            $this->tags  = htmlspecialchars($this->tags, ENT_COMPAT, 'UTF-8');
            $this->cat   = (int)$this->cat;
            $this->pic   = htmlspecialchars($this->pic, ENT_COMPAT, 'UTF-8');
            $this->unit  = (int)$this->unit;
            $this->wpu   = (int)$this->wpu;
            $this->price = (float)$this->price;
            $this->exp   = (int)$this->exp;
            $this->prob  = (int)$this->prob;
            foreach($this->NUTRITION_VALUES as $value)
                $this->{$value} = (float)$this->{$value};
        }
        
        /**********************************************************
        GET
        ***********************************************************/
        
        //Return a given ingredient property
        //@prop (string): property name
        //->value (mixed): property value (null if not found)
        public function get($prop)
        {
            if(isset($this->{$prop}))
                return $this->{$prop};
            else
                return null;
        }
    }
?>
