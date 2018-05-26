<?php
    /*******************************************************
    Title: Shopping Controller
    Authors: Kookiiz Team
    Purpose: Generate shopping list
    ********************************************************/

    //Dependencies
    require_once '../class/dblink.php';
    require_once '../class/globals.php';
    require_once '../class/ingredients_db.php';
    require_once '../class/lang_db.php';
    require_once '../class/units_lib.php';
    require_once '../class/user.php';

    //Represents a controller for shopping lists
    class shoppingController 
    {
        /**********************************************************
        PROPERTIES
        ***********************************************************/
        
        private static $CATTOGROUP = array();
        private static $SHOPORDER = array();
        
        private $DB;
        private $IngDB;
        private $Lang;
        private $User;

        /**********************************************************
        CONSTRUCTOR
        ***********************************************************/

        /**
         * Class constructor
         * @param DBLink $DB open database connection
         * @param User $User current user
         */
        public function __construct(DBLink &$DB, User &$User) 
        {
            $this->DB    = $DB;
            $this->User  = $User;
            $this->Lang  = LangDB::getHandler($this->User->getLang());
            $this->IngDB = new IngredientsDB($this->DB, $this->User->getLang());
            
            self::$CATTOGROUP = C::get('ING_CATS_TOGROUP');
            self::$SHOPORDER  = $this->User->markets_order_get();
        }
        
        /**********************************************************
        CONVERT
        ***********************************************************/
        
        /**
         * Convert quantity from unit1 to unit2
         * @param Float $qty initial quantity
         * @param Int $unit1 initial unit
         * @param Int $unit2 final unit
         * @param Float $wpu weight per unit of the ingredient
         * @return Float final quantity
         */
        private function convert($qty, $unit1, $unit2, $wpu)
        {
            $final = $qty;
            if($unit1 != $unit2) 
            {
                if($unit1 != C::UNIT_GRAMS && $unit1 != C::UNIT_MILLILITERS)
                {
                    $unitObj1 = UnitsLib::get($unit1);
                    $value = $unitObj1->getValue();
                    if($value)
                        $final *= $value;
                    else
                        $final *= $wpu;
                }
                if($unit2 != C::UNIT_GRAMS && $unit2 != C::UNIT_MILLILITERS)
                {
                    $unitObj2 = UnitsLib::get($unit2);
                    $value = $unitObj2->getValue();
                    if($value)
                        $final /= $value;
                    else
                        $final /= $wpu;
                }
            }
            return $final;
        }
        
        /**********************************************************
        DISPLAY
        ***********************************************************/

        /**
         * Display shopping list for a given day
         * @param Int $day index of the day
         */
        public function display($day)
        {
            $list = $this->User->menu_shopping_get($day);           
            $groups = $this->group($list);
            if(count($groups))
            {
                foreach($groups as $group)
                {
                    $name = $this->Lang->get('INGREDIENTS_GROUPS_NAMES', $group['id']);
                    echo 
                        "<h6 class='title'>
                            <img class='category_icon ", C::get('ING_GROUPS', $group['id']), "' src='", C::ICON_URL, "'>
                            <p>$name</p>
                        </h6>";
                    if(count($group['ings']))
                    {
                        echo '<ul>';
                        foreach($group['ings'] as $IngQty)
                        {
                            //Get ingredient and unit objects
                            $IngObj = $this->IngDB->getIngredient($IngQty->getID());
                            $UnitObj = UnitsLib::get($IngObj->get('unit'));
                            
                            //Convert quantity to default ingredient unit
                            $qty = $this->convert($IngQty->getQuantity(), $IngQty->getUnit(), $IngObj->get('unit'), $IngObj->get('wpu'));
                            if($IngObj->get('unit') == C::UNIT_NONE)
                            {
                                //Ceil quantity for "no unit"
                                $qty = ceil($qty);
                                $unit = '';
                            }
                            else
                            {
                                //Round quantity for all other units
                                $qty = round($qty / $UnitObj->getRound()) * $UnitObj->getRound();
                                $unit = $this->Lang->get('UNITS_NAMES', $IngObj->get('unit'));
                            }
                            echo "<li class='shopIng'>{$qty}$unit - {$IngObj->get('name')}</li>";
                        }
                        echo '</ul>';
                    }
                    if(count($group['items']))
                    {
                        echo '<ul>';
                        foreach($group['items'] as $item)
                            echo "<li class='shopItem'>{$item['text']}</li>";
                        echo '</ul>';
                    }
                }
            }
            else
                echo '<p class="center">', $this->Lang->get('SHOPPING_TEXT', 29),'</p>';
        }
        
        /**********************************************************
        GROUP
        ***********************************************************/
        
        /**
         * Group shopping list elements and order them according to user's preferences
         * @param Array $list initial shopping list data
         * @return Array ordered shopping groups 
         */
        private function group($list)
        {
            $groups = array();
            //Loop through ingredients
            foreach($list['ingredients'] as $IngQty)
            {
                if($IngQty->getQuantity() <= 0) continue;
                
                $IngObj  = $this->IngDB->getIngredient($IngQty->getID());
                $groupID = self::$CATTOGROUP[$IngObj->get('cat')];
                if(!isset($groups[$groupID]))
                {
                    $groups[$groupID] = array(
                        'id'    => $groupID,
                        'ings'  => array(),
                        'items' => array()
                    );
                }
                $groups[$groupID]['ings'][] = $IngQty;
            }
            //Loop through items
            foreach($list['items'] as $item)
            {
                $groupID = $item['category'];
                if(!isset($groups[$groupID]))
                {
                    $groups[$groupID] = array(
                        'id'    => $groupID,
                        'ings'  => array(),
                        'items' => array()
                    );
                }
                $groups[$groupID]['items'][] = $item;
            }
            $groups = array_values($groups);
            usort($groups, array('shoppingController', 'groupSort'));
            return $groups;
        }
        
        /**
         * Sort groups according to user's preferred order
         * @param Array $grpA
         * @param Array $grpB
         * @return Int sorting as -1 (a before b), 1 (a after b), 0 (a == b) 
         */
        public static function groupSort($grpA, $grpB)
        {
            $indexA = array_search($grpA['id'], self::$SHOPORDER);
            $indexB = array_search($grpB['id'], self::$SHOPORDER);
            if($indexA !== false && $indexB !== false)
                return $indexA > $indexB ? 1 : -1;
            if($indexA === false && $indexB !== false)
                return 1;
            if($indexA !== false && $indexB === false)
                return -1;
            if($indexA === false && $indexB === false)
                return 0;
        }
        
        /**********************************************************
        LIST
        ***********************************************************/
        
        /**
         * Generate options for shopping list selector
         * @param Int $daySel selected day
         */
        public function listOptions($daySel)
        {
            $selected = false;
            $ref  = strtotime($this->User->menu_reference_get());
            $days = $this->User->menu_shopping_getDays();
            foreach($days as $day)
            {
                $date    = date('d.m', $ref + $day * 24 * 3600);
                $dayID   = date('N', $ref + $day * 24 * 3600);
                $dayName = $this->Lang->get('DAYS_NAMES', $dayID - 1);
                if($day == $daySel)
                {
                    echo "<option value='$day' selected='selected'>$dayName $date</option>";
                    $selected = true;
                }
                else
                    echo "<option value='$day'>$dayName $date</option>";
            }
        }
    }
?>