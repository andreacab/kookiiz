<?php
    /*******************************************************
    Title: Ingredient quantity
    Authors: Kookiiz Team
    Purpose: Describe ingredient quantity object
    ********************************************************/

    //Represents a quantity of a given ingredient
    class IngredientQty
    {
        /**********************************************************
        ATTRIBUTES
        ***********************************************************/

        private $id;
        private $quantity;
        private $unit;

        /**********************************************************
        CONSTRUCTOR
        ***********************************************************/

        //Class constructor
        //@id (int):            corresponding ingredient ID
        //@quantity (float):    ingredient quantity
        //@unit (int):          unit ID
        //-> (void)
        public function __construct($id, $quantity, $unit)
        {
            $this->id       = $id;
            $this->quantity = $quantity;
            $this->unit     = $unit;
        }

        /**********************************************************
        EXPORT
        ***********************************************************/

        //Export ingredient quantity in a compact format
        //->ing_qty (object): compact ingredient quantity structure
        public function export()
        {
            return array(
                'i' => $this->id,
                'q' => $this->quantity,
                'u' => $this->unit
            );
        }

        /**********************************************************
        GET METHODS
        ***********************************************************/

        //Return ingredient ID
        //->ingredient_id (int): unique ingredient ID
        public function getID()
        {
            return $this->id;
        }

        //Return ingredient quantity
        //->quantity (float): quantity of ingredient
        public function getQuantity()
        {
            return $this->quantity;
        }

        //Return ingredient unit
        //->unit (int): unit ID
        public function getUnit()
        {
            return $this->unit;
        }
    }
?>
