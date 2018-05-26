<?php
	/**********************************************************
    Title: Quick meals library
    Authors: Kookiiz Team
    Purpose: Provide an interface for the quick meals database
    ***********************************************************/

    //External files
    require_once '../class/dblink.php';
    require_once '../class/exception.php';
    require_once '../class/globals.php';
    require_once '../class/quickmeal.php';
    require_once '../class/user.php';

    //Represents a library of quick meals
    class QuickmealsLib
    {
        /**********************************************************
        PROPERTIES
        ***********************************************************/

        private $DB;    //link to the database
        private $User;  //user connected to the library

        /**********************************************************
        CONSTRUCTOR
        ***********************************************************/

        //Class constructor
        //@DB (object):     open database connection
        //@User (object):   user connected to the library
        //-> (void)
        public function __construct(DBLink &$DB, User &$User)
        {
            //Set up properties
            $this->DB   = $DB;
            $this->User = $User;
        }

        /**********************************************************
        CREATE
        ***********************************************************/

        //Save a new quick meal
        //@quickmeal (object): quickmeal structure
        //->quickmeal_id (int): ID of the new quickmeal (0 if any error occured)
        public function create($quickmeal)
        {
            $name = $quickmeal['name'];
            $mode = (int)$quickmeal['mode'];

            //Save quick meal
            $request = 'INSERT INTO quickmeals (user_id, quickmeal_name, quickmeal_mode)'
                    . ' VALUES (:user_id, :name, :mode)';
            $params = array(
                ':user_id'  => $this->User->getID(),
                ':name'     => $name,
                ':mode'     => $mode
            );
            $stmt = $this->DB->query($request, $params);
            if($stmt->rowCount())   $quickmeal_id = $this->DB->insertID();
            else                    return 0;

            //Save quick meal ingredients or nutrition
            if($quickmeal['mode'] == C::QM_MODE_INGREDIENTS)
            {
                $ingredients = $quickmeal['ingredients'];
                if(count($ingredients))
                {
                    $request = 'INSERT IGNORE INTO quickmeals_ingredients (quickmeal_id, ingredient_id, quantity, unit)'
                            . ' VALUES (:id, :ing_id, :quantity, :unit)';
                    $params = array();
                    foreach($ingredients as $ingredient)
                    {
                        $params[] = array(
                            ':id'       => $quickmeal_id,
                            ':ing_id'   => (int)$ingredient['i'],
                            ':quantity' => (float)$ingredient['q'],
                            ':unit'     => (int)$ingredient['u']
                        );
                    }
                    $this->DB->query($request, $params);
                }
            }
            else
            {
                $nutrition = array_map('floatval', $quickmeal['nutrition']);
                $request = 'INSERT IGNORE INTO quickmeals_nutrition'
                        . ' (quickmeal_id, nutrition_values) VALUES (?, ?)';
                $params = array($quickmeal_id, implode(',', $nutrition));
                $this->DB->query($request, $params);
            }

            //Return new quick meal ID
            return $quickmeal_id;
        }

        /**********************************************************
        DELETE
        ***********************************************************/

        //Remove an existing quick meal from database
        //@quickmeal_id (int): unique ID of the quick meal
        //-> (void)
        public function delete($quickmeal_id)
        {
            //Check that user is really the owner of the quick meal
            $request = 'SELECT quickmeal_mode FROM quickmeals'
                    . ' WHERE quickmeal_id = ? AND user_id = ?';
            $stmt = $this->DB->query($request, array($quickmeal_id, $this->User->getID()));
            $data = $stmt->fetch();
            if($data)
            {
                $mode = (int)$data['quickmeal_mode'];

                //Delete from quick meals table and secondary tables depending on mode
                $request = 'DELETE FROM quickmeals WHERE quickmeal_id = ?';
                $this->DB->query($request, array($quickmeal_id));
                if($mode == C::QM_MODE_INGREDIENTS)
                {
                    $request = 'DELETE FROM quickmeals_ingredients WHERE quickmeal_id = ?';
                }
                else
                {
                    $request = 'DELETE FROM quickmeals_nutrition WHERE quickmeal_id = ?';
                }
                $this->DB->query($request, array($quickmeal_id));
            }
            //Quick meal was not found
            else throw new KookiizException('quickmeals', 5);
        }

        /**********************************************************
        EXIST
        ***********************************************************/

        //Check if provided quick meals exist
        //@quickmeals (array): list of quick meal IDs to check
        //->quickmeals (array): list of existing quick meal IDs
        public function exist(array $quickmeals)
        {
            $existing   = array();
            $quickmeals = array_map('intval', array_values(array_unique($quickmeals)));

            if(count($quickmeals))
            {
                $request = 'SELECT quickmeal_id FROM quickmeals'
                        . ' WHERE quickmeal_id IN (' . implode(', ', $quickmeals) . ')';
                $stmt = $this->DB->query($request);
                $existing = array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN, 0));
            }
            return $existing;
        }

        //Export data for provided list of quick meal IDs
        //@quickmeals (array): list of quick meal IDs
        //->content (object): list of compact quick meal data structures
        public function export(array $quickmeals)
        {
            $content = array();
            $objects = $this->get($quickmeals);
            foreach($objects as $quickmeal)
            {
                $content[] = $quickmeal->export();
            }
            return $content;
        }

        /**********************************************************
        GET
        ***********************************************************/

        //Get quick meal objects from a list of IDs
        //@quickmeals (array): list of quick meal IDs
        //->quickmeals (array): list of quick meals objects
        public function get(array $quickmeals)
        {
            $objects    = array();
            $quickmeals = array_map('intval', array_values(array_unique($quickmeals)));
            if(!count($quickmeals)) return $objects;

            //Load "ingredient mode" quick meals
            $request = 'SELECT quickmeal_id, quickmeal_name,'
                        . ' GROUP_CONCAT(ingredient_id) AS ingredients_ids,'
                        . ' GROUP_CONCAT(quantity) AS quantities,'
                        . ' GROUP_CONCAT(unit) AS units'
                    . ' FROM quickmeals NATURAL JOIN quickmeals_ingredients'
                    . ' WHERE quickmeal_mode = ' . C::QM_MODE_INGREDIENTS
                        . ' AND quickmeal_id IN (' . implode(', ', $quickmeals) . ')'
                    . ' GROUP BY quickmeal_id';
            $stmt = $this->DB->query($request);
            while($quickmeal = $stmt->fetch())
            {
                $id     = (int)$quickmeal['quickmeal_id'];
                $name   = htmlspecialchars($quickmeal['quickmeal_name'], ENT_COMPAT, 'UTF-8');
                $mode   = C::QM_MODE_INGREDIENTS;

                $ingredients        = array();
                $ingredients_ids    = explode(',', $quickmeal['ingredients_ids']);
                $quantities         = explode(',', $quickmeal['quantities']);
                $units              = explode(',', $quickmeal['units']);
                foreach($ingredients_ids as $index => $ing_id)
                {
                    $quantity       = $quantities[$index];
                    $unit           = $units[$index];
                    $ingredients[]  = new IngredientQty($ing_id, $quantity, $unit);
                }

                //Store quick meal object
                $objects[] = new Quickmeal($id, $name, $mode, $ingredients);
            }

            //Load "nutrition mode" quick meals
            $request = 'SELECT quickmeal_id, quickmeal_name, nutrition_values'
                    . ' FROM quickmeals'
                        . ' NATURAL JOIN quickmeals_nutrition'
                    . ' WHERE quickmeal_mode = ' . C::QM_MODE_NUTRITION
                        . ' AND quickmeal_id IN (' . implode(', ', $quickmeals) . ')';
            $stmt = $this->DB->query($request);
            while($quickmeal = $stmt->fetch())
            {
                $id         = (int)$quickmeal['quickmeal_id'];
                $name       = htmlspecialchars($quickmeal['quickmeal_name'], ENT_COMPAT, 'UTF-8');
                $mode       = C::QM_MODE_NUTRITION;
                $nutrition  = explode(',', $quickmeal['nutrition_values']);

                //Store quick meal object
                $objects[] = new Quickmeal($id, $name, $mode, $nutrition);
            }

            //Return list of quick meal objects
            return $objects;
        }
    }
?>