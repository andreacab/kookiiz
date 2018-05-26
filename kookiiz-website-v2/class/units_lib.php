<?php
    /*******************************************************
    Title: Units library
    Authors: Kookiiz Team
    Purpose: Manage units
    ********************************************************/

    //Dependencies
    require_once '../class/dblink.php';
    require_once '../class/globals.php';
    require_once '../class/lang_db.php';
    require_once '../class/unit.php';

    //Represents a library of units
    class UnitsLib
    {
        /**********************************************************
        PROPERTIES
        ***********************************************************/

        private static $library = array();
        private static $loaded  = false;
        private static $system  = 'metric';

        /**********************************************************
        EXPORT
        ***********************************************************/

        /**
         * Export all units from library
         * @return Array unit data indexed by unit ID
         */
        public static function exportAll()
        {
            if(!self::$loaded) self::load();

            $library = array();
            foreach(self::$library as $Unit)
            {
                $library[] = $Unit->export();
            }
            return $library;
        }
        
        /**********************************************************
        GET
        ***********************************************************/
        
        /**
         * Return specific unit object
         * @param Int $unit_id unique unit ID
         * @return Unit corresponding unit object
         */
        public static function get($unit_id)
        {
            if(!self::$loaded) self::load();
            
            foreach(self::$library as $Unit)
            {
                if($Unit->getID() === $unit_id)
                    return $Unit;
            }
            return null;
        }
        
        /**
         * Get all unit objects for a given unit system
         * @param String $system unit system (metric/imperial)
         */
        public static function getAll($system)
        {
            if(!self::$loaded) self::load();
            self::$system = $system;
            
            $units = array();
            foreach(self::$library as $Unit)
            {
                switch($system)
                {
                    case 'metric':
                        if($Unit->isMetric())
                            $units[] = $Unit;
                        break;
                    case 'imperial':
                        if($Unit->isImperial())
                            $units[] = $Unit;
                        break;
                }
            }
            
            usort($units, array('UnitsLib', 'sort'));
            return $units;
        }

        /**********************************************************
        LOAD
        ***********************************************************/

        /**
         * Load library content from database
         */
        private static function load()
        {
            self::$library = array();

            //Query database for units information
            $DB = new DBLink('kookiiz');
            $request = 'SELECT unit_id, value, display, metric,'
                        . ' imperial, equivalent_id, round'
                    . ' FROM units ORDER BY unit_id';
            $stmt = $DB->query($request);
            while($unit = $stmt->fetch())
            {
                $id         = (int)$unit['unit_id'];
                $display    = (bool)$unit['display'];
                $eq_id      = (int)$unit['equivalent_id'];
                $metric     = (bool)$unit['metric'];
                $imperial   = (bool)$unit['imperial'];
                $round      = (float)$unit['round'];
                $value      = (float)$unit['value'];
                self::$library[$id] = new Unit($id, $display, $eq_id, $metric, $imperial, $round, $value);
            }
        }
        
        /**********************************************************
        SORT
        ***********************************************************/
        
        /**
         * Sort units according to predefined order
         * @param Unit $unit_a first unit object to sort
         * @param Unit $unit_b second unit object to sort
         * @return Int sorting value (-1, 0 or 1)
         */
        public static function sort($unit_a, $unit_b)
        {
            $ORDER = C::get('UNITS_ORDERS', self::$system);
            $pos_a = array_search($unit_a->getID(), $ORDER);
            $pos_b = array_search($unit_b->getID(), $ORDER);
            return $pos_a < $pos_b ? -1 : 1;
        }
    }
?>
