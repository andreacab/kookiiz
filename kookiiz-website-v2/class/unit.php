<?php
    /*******************************************************
    Title: Unit
    Authors: Kookiiz Team
    Purpose: Define a unit
    ********************************************************/

    //Represents a unit
    class Unit
    {
        /**********************************************************
        PROPERTIES
        ***********************************************************/

        private $id;        //Unique unit ID
        private $display;   //Whether the unit should appear on shopping list
        private $eq_id;     //ID of equivalent unit in opposite system (metric/imperial)
        private $metric;    //Whether the unit is part of the metric system
        private $imperial;  //Whether the unit is part of the imperial system
        private $round;     //Unit rounding factor
        private $value;     //Value in grams

        /**********************************************************
        CONSTRUCTOR
        ***********************************************************/

        /**
         * Class constructor
         * @param Int $id unique unit ID
         * @param Bool $display whether the unit should appear on shopping list
         * @param Int $eq_id ID of a replacement unit in the opposite system (metric/imperial)
         * @param Bool $metric whether the unit is part of the metric system
         * @param Bool $imperial whether the unit is part of the imperial system
         * @param Float $round unit rounding factor
         * @param Float $value unit value in grams
         */
        public function __construct($id, $display, $eq_id, $metric, $imperial, $round, $value)
        {
            $this->id       = $id;
            $this->display  = $display;
            $this->eq_id    = $eq_id;
            $this->metric   = $metric;
            $this->imperial = $imperial;
            $this->round    = $round;
            $this->value    = $value;
        }

        /**********************************************************
        EXPORT
        ***********************************************************/

        /**
         * Export unit properties
         * @return Array units properties
         */
        public function export()
        {
            return array(
                'id'    => $this->id,
                'disp'  => $this->display,
                'eq_id' => $this->eq_id,
                'met'   => $this->metric,
                'imp'   => $this->imperial,
                'round' => $this->round,
                'val'   => $this->value
            );
        }

        /**********************************************************
        GET
        ***********************************************************/

        /**
         * Returns display flag value
         * @return Bool whether unit should be displayed on shopping list
         */
        public function getDisplay()
        {
            return $this->display;
        }

        /**
         * Returns replacement unit ID in opposite system (metric/imperial)
         * @return Int equivalent unit ID
         */
        public function getEqID()
        {
            return $this->eq_id;
        }

        /**
         * Returns unique unit ID
         * @return Int unique unit ID
         */
        public function getID()
        {
            return $this->id;
        }

        /**
         * Returns unit rounding factor
         * @return Float unit rounding factor
         */
        public function getRound()
        {
            return $this->round;
        }

        /**
         * Returns unit value in grams
         * @return Float unit value in grams
         */
        public function getValue()
        {
            return $this->value;
        }
        
        /**
         * Tells if unit is part of the imperial system
         * @return Bool whether unit is part of the imperial system
         */
        public function isImperial()
        {
            return $this->imperial;
        }
        
        /**
         * Tells if unit is part of the metric system
         * @return Bool whether unit is part of the metric system
         */
        public function isMetric()
        {
            return $this->metric;
        }
    }
?>
