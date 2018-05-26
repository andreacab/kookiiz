<?php
    /*******************************************************
    Title: Quick meal
    Authors: Kookiiz Team
    Purpose: Define the quick meal object
    ********************************************************/

    //Dependencies
    require_once '../class/globals.php';
    require_once '../class/ingredient_qty.php';

    //Represents a quick meal
    class Quickmeal
    {
        /**********************************************************
        PROPERTIES
        ***********************************************************/

        private $id;
        private $name;
        private $mode;

        private $ingredients    = array();
        private $nutrition      = array();

        /**********************************************************
        CONSTRUCTOR
        ***********************************************************/

        //Class constructor
        //-> (void)
        public function __construct($id, $name, $mode, $content = null)
        {
            $this->id   = $id;
            $this->name = $name;
            $this->mode = $mode;

            if(!is_null($content))
            {
                if($this->mode == C::QM_MODE_INGREDIENTS)
                {
                    $this->ingredients = $content;
                }
                else
                {
                    $this->nutrition = $content;
                }
            }
        }

        /**********************************************************
        EXPORT
        ***********************************************************/

        //Export quick meal content
        //->quickmeal (object): compact quick meal content
        public function export()
        {
            $quickmeal = array(
                'id'    => $this->id,
                'name'  => $this->name,
                'mode'  => $this->mode
            );
            if($this->mode == C::QM_MODE_INGREDIENTS)
            {
                $quickmeal['ing'] = array();
                foreach($this->ingredients as $ing)
                {
                    $quickmeal['ing'][] = $ing->export();
                }
            }
            else
            {
                $quickmeal['nut'] = $this->nutrition;
            }
            return $quickmeal;
        }

        /**********************************************************
        IMPORT
        ***********************************************************/

        //Import quick meal ingredients
        //@ingredients (array): list of compact ingredient quantities
        //-> (void)
        public function importIngredients($ingredients)
        {
            if($this->mode != C::QM_MODE_INGREDIENTS) return;
            
            foreach($ingredients as $ing)
            {
                $id         = (int)$ing['i'];
                $quantity   = (float)$ing['q'];
                $unit       = (int)$ing['u'];
                $this->ingredients[] = new IngredientQty($id, $quantity, $unit);
            }
        }

        //Import quick meal nutrition values
        //@nutrition (array): list of nutrition values indexed by ID
        //-> (void)
        public function importNutrition($nutrition)
        {
            if($this->mode != C::QM_MODE_NUTRITION) return;

            $this->nutrition = array_map('intval', $nutrition);
        }
    }
?>
