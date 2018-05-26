<?php
	/**********************************************************
    Title: Recipes library
    Authors: Kookiiz Team
    Purpose: Provide an interface for the recipes library
    ***********************************************************/

    //Dependencies
    require_once '../class/dblink.php';
    require_once '../class/globals.php';
    require_once '../class/ingredients_db.php';
    require_once '../class/lang_db.php';
    require_once '../class/exception.php';
    require_once '../class/user.php';

    //Represents a library of recipes
    class RecipesLib
    {
        /**********************************************************
        CONSTANTS
        ***********************************************************/

        const CHEAP_THRESHOLD       = C::RECIPE_CHEAP_THRESHOLD;
        const CHEF_BEST_MAX         = 20;
        const DESCRIPTION_MAX       = C::RECIPE_DESCRIPTION_MAX;
        const DESCRIPTION_MIN       = C::RECIPE_DESCRIPTION_MIN;
        const EASY_THRESHOLD        = C::RECIPE_EASY_THRESHOLD;
        const HEALTHY_SCORE_FACTOR  = 9;    //The higher the factor, the faster the decrease
        const HEALTHY_SCORE_MAX     = 10;   //Max score for healthy criterion
        const HEALTHY_SCORE_MIN     = 0;    //Min score for healthy criterion
        const HEALTHY_SUM_IDEAL     = 1.75; //Ideal sum of nutrition fractions (5 * 0.35)
        const HEALTHY_THRESHOLD     = C::RECIPE_HEALTHY_THRESHOLD;
        const HISTORY_MAX           = 20;
        const INGREDIENTS_MIN       = C::RECIPE_ING_MIN;
        const LEVEL_MIN             = 0;    //Min difficulty level
        const LEVEL_MAX             = 4;    //Max difficulty level
        const GUESTS_MAX            = C::RECIPE_GUESTS_MAX;
        const GUESTS_MIN            = C::RECIPE_GUESTS_MIN;
        const QUICK_THRESHOLD       = C::RECIPE_QUICK_THRESHOLD;
        const RATING_MAX            = C::RECIPE_RATING_MAX;
        const RATING_MIN            = C::RECIPE_RATING_MIN;
        const REPORTS_LIMIT         = 10;   //Max number of reports before a recipe is removed from public results
        const SEARCH_MAX            = 80;   //Max number of search results
        const SEARCH_WORD_MIN       = 3;    //Min length of a word to be taken into account    
        const STEPS_MAX             = C::RECIPE_STEPS_MAX;
        const SUCCESS_THRESHOLD     = C::RECIPE_SUCCESS_THRESHOLD;
        const SUGGESTIONS_MAX       = 15;   //Max number of search suggestions
        const TITLE_MAX             = C::RECIPE_TITLE_MAX;

        //Default search criteria
        private static $CRITERIA_DEFAULTS = array(
            'mode'      => 'AND',
            'text'      => '',
            'tags'      => array(),
            'category'  => 0,
            'origin'    => 0,
            'favorites' => 0,
            'healthy'   => 0,
            'cheap'     => 0,
            'easy'      => 0,
            'quick'     => 0,
            'success'   => 0,
            'veggie'    => 0,
            'chef'      => 0,
            'chef_id'   => 0,
            'fridge'    => 0,
            'season'    => 0,
            'liked'     => 0,
            'disliked'  => 0,
            'allergy'   => 0,
            'random'    => 0
        );

        /**********************************************************
        PROPERTIES
        ***********************************************************/

        private $DB;    //link to the database
        private $User;  //user connected to the library

        private $overflow = false;  //Flag for search results overflow

        /**********************************************************
        CONSTRUCTOR
        ***********************************************************/

        /**
         * Class constructor
         * @param DBLink $DB open database connection
         * @param User $User user connected to the library
         */
        public function __construct(DBLink &$DB, User &$User)
        {
            $this->DB   = $DB;
            $this->User = $User;
        }

        /**********************************************************
        AUTHORIZE
        ***********************************************************/

        /**
         * Check if provided recipes can be viewed by a given user
         * Note: order of recipe IDs is not preserved!
         * @param Array $recipes_ids list of recipe IDs to check
         * @return Array list of authorized recipe IDs 
         */
        private function authorize(array $recipes_ids)
        {
            $authorized = array();
            $recipes_ids = array_values(array_unique($recipes_ids));
            if(count($recipes_ids))
            {
                $request = 'SELECT recipes.recipe_id'
                        . ' FROM recipes'
                            . ' LEFT JOIN recipes_authorized USING (recipe_id)'
                        . ' WHERE recipes.recipe_id IN (' . implode(', ', $recipes_ids) . ')'
                            . ' AND (public OR author_id = ? OR recipes_authorized.user_id = ?)';
                $stmt = $this->DB->query($request, array($this->User->getID(), $this->User->getID()));
                $authorized = array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN, 0));
            }
            return $authorized;
        }
        
        /**********************************************************
        CHECK
        ***********************************************************/
        
        /**
         * Check recipe data before insertion
         * @param Array $recipe object to check
         * @param String $lang recipe language identifier
         * @return Array cleaned-up recipe data
         */
        private function check(array $recipe, $lang)
        {
            //Get globals language arrays
            $Lang = LangDB::getHandler($lang);
            $categories = $Lang->get('RECIPES_CATEGORIES');
            $origins    = $Lang->get('RECIPES_ORIGINS');

            //Format recipe properties
            $name    = $recipe['name'];
            $desc    = $recipe['description'];
            $ings    = $recipe['ingredients'];
            $pic_id  = (int)$recipe['pic_id'];
            $guests  = (int)$recipe['guests'];
            $cat     = (int)$recipe['category'];
            $origin  = (int)$recipe['origin'];
            $level   = (int)$recipe['level'];
            $prep    = (int)$recipe['preparation'];
            $cook    = (int)$recipe['cooking'];
            $price   = (int)$recipe['price'];
            $valid   = 1;

            //Check that minimum requirements are fullfilled or set valid to false
            if($cat < 0 || ($cat > count($categories) - 1)  
                || $origin < 0 || ($origin > count($origins) - 1)     
                || count($ings) < self::INGREDIENTS_MIN           
                || !in_array($lang, C::get('LANGUAGES')))               
                $valid = 0;

            //Constrain other recipe properties to valid values
            $name = substr($name, 0, self::TITLE_MAX);
            $desc = substr($desc, 0, self::STEPS_MAX * self::DESCRIPTION_MAX);
            if($guests < self::GUESTS_MIN)      $guests = self::GUESTS_MIN;
            else if($guests > self::GUESTS_MAX) $guests = self::GUESTS_MAX;
            if($level < self::LEVEL_MIN)        $level = self::LEVEL_MIN;
            else if($level > self::LEVEL_MAX)   $level = self::LEVEL_MAX;

            //Check picture validity
            if($pic_id)
            {
                //Check that picture reference exists
                $request = 'SELECT pic_path FROM recipes_pictures WHERE pic_id = ?';
                $stmt = $this->DB->query($request, array($pic_id));
                $data = $stmt->fetch();
                //Check that picture file exists
                if(!$data || !file_exists($data['pic_path'])) 
                    $pic_id = 0;
            }
            else
                $pic_id = 0;
            
            //Return clean content
            return array(
                'name'      => $name,
                'desc'      => $desc,
                'ings'      => $ings,
                'pic_id'    => $pic_id,
                'guests'    => $guests,
                'cat'       => $cat,
                'origin'    => $origin,
                'level'     => $level,
                'prep'      => $prep,
                'cook'      => $cook,
                'price'     => $price,
                'valid'     => $valid
            );
        }

        /**********************************************************
        CHEF
        ***********************************************************/

        /**
         * List high-rating recipes from provided chef ID
         * @param Int $chef_id unique chef ID
         * @return Array list of recipe IDs from selected chef 
         */
        public function chef_best($chef_id)
        {
            //Retrieve chef's recipes
            $request = 'SELECT recipe_id'
                    . ' FROM recipes, users'
                    . ' WHERE author_id = user_id'
                        . ' AND chef_id = ?'
                    . ' ORDER BY recipes.rating DESC'
                    . ' LIMIT ' . self::CHEF_BEST_MAX;
            $stmt = $this->DB->query($request, array($chef_id));
            return array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN, 0));
        }

        /**********************************************************
        COUNT
        ***********************************************************/

        /**
         * Count recipes in library
         * @return Int current recipe count 
         */
        public function count()
        {
            $request = 'SELECT COUNT(*) AS total FROM recipes';
            $stmt = $this->DB->query($request);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            return (int)$data['total'];
        }

        /**********************************************************
        DELETE
        ***********************************************************/

        /**
         * Delete a given recipe from database
         * @param Int $recipe_id unique recipe ID
         */
        public function delete($recipe_id)
        {
            //Delete recipe from content tables
            $request = 'DELETE FROM recipes WHERE recipe_id = ?';
            $this->DB->query($request, array($recipe_id));
            $request = 'DELETE FROM recipes_authorized WHERE recipe_id = ?';
            $this->DB->query($request, array($recipe_id));
            $request = 'DELETE FROM recipes_comments WHERE content_id = ?';
            $this->DB->query($request, array($recipe_id));
            $request = 'DELETE FROM recipes_comments_ratings WHERE content_id = ?';
            $this->DB->query($request, array($recipe_id));
            $request = 'DELETE FROM recipes_glossary WHERE recipe_id = ?';
            $this->DB->query($request, array($recipe_id));
            $request = 'DELETE FROM recipes_ingredients WHERE recipe_id = ?';
            $this->DB->query($request, array($recipe_id));
            $request = 'DELETE FROM recipes_ratings WHERE recipe_id = ?';
            $this->DB->query($request, array($recipe_id));
            $request = 'DELETE FROM recipes_updates WHERE recipe_id = ?';
            $this->DB->query($request, array($recipe_id));
        }
        
        /**
         * Delete all ingredients of a given recipe
         * @param Int $recipe_id unique recipe ID
         */
        private function deleteIngredients($recipe_id)
        {
            $request = 'DELETE FROM recipes_ingredients WHERE recipe_id = ?';
            $this->DB->query($request, array($recipe_id));
        }
        
        /**********************************************************
        DISMISS
        ***********************************************************/

        /**
         * Dismiss a recipe (tag it as "not valid")
         * @param Int $recipe_id unique recipe ID
         */
        public function dismiss($recipe_id)
        {
            $request = 'UPDATE recipes SET valid = 0 WHERE recipe_id = ?';
            $this->DB->query($request, array($recipe_id));
        }
        
        /**********************************************************
        EDIT
        ***********************************************************/
        
        /**
         * Edit existing recipe
         * @param Int $recipe_id unique recipe ID
         * @param Array $recipe edited recipe data
         */
        public function edit($recipe_id, array $recipe)
        {
            //Retrieve existing data
            $existing = $this->load(array($recipe_id));
            $existing = count($existing) ? $existing[0] : null;
            if(is_null($existing))
                throw new KookiizException('recipeform', 2);
            
            //Check that current user is the recipe author or and admin
            if(!$this->User->isAdmin() && $existing['auth_id'] != $this->User->getID())
                throw new KookiizException('recipeform', 3);
            
            //Validate recipe data
            $recipe = $this->check($recipe, $existing['lang']);
            
            //Update recipe data in DB
            $request = 'UPDATE recipes'
                    . ' SET name = :name,'
                        . ' description = :desc,'
                        . ' pic_id = :pic_id,'
                        . ' guests = :guests,'
                        . ' category = :category,'
                        . ' origin = :origin,'
                        . ' preparation = :prep,'
                        . ' cooking = :cook,'
                        . ' price = :price,'
                        . ' level = :level,'
                        . ' valid = :valid'
                    . ' WHERE recipe_id = :id LIMIT 1';
            $this->DB->query($request, array(
                ':name'     => $recipe['name'],
                ':desc'     => $recipe['desc'],
                ':pic_id'   => $recipe['pic_id'],
                ':guests'   => $recipe['guests'],
                ':category' => $recipe['cat'],
                ':origin'   => $recipe['origin'],
                ':prep'     => $recipe['prep'],
                ':cook'     => $recipe['cook'],
                ':price'    => $recipe['price'],
                ':level'    => $recipe['level'],
                ':valid'    => $recipe['valid'],
                ':id'       => $recipe_id
            ));
            
            //Update ingredients
            $this->deleteIngredients($recipe_id);
            $this->insertIngredients($recipe_id, $recipe['ings']);
            
            //Update healthy and veggie scores
            $this->test_healthy($recipe_id, $update = true);
            $this->test_veggie($recipe_id, $update = true);
        }

        /**********************************************************
        EXIST
        ***********************************************************/

        /**
         * Check if provided recipes exist
         * @param Array $recipes_ids list of recipe IDs to check
         * @return Array list of recipe IDs that exist
         */
        public function exist(array $recipes_ids)
        {
            $existing = array();
            $recipes_ids = array_map('intval', array_values(array_unique($recipes_ids)));
            if(count($recipes_ids))
            {
                $request = 'SELECT recipe_id FROM recipes'
                        . ' WHERE recipe_id IN (' . implode(', ', $recipes_ids) . ')';
                $stmt = $this->DB->query($request);
                $existing = array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN, 0));
            }
            return $existing;
        }

        /**********************************************************
        GET
        ***********************************************************/

        /**
         * Get pic ID of a given recipe
         * @param Int $recipe_id unique ID of the recipe
         * @return Int ID of the picture (0 if not found)
         */
        public function getPic($recipe_id)
        {
            $request = 'SELECT pic_id'
                    . ' FROM recipes'
                    . ' WHERE recipe_id = ?';
            $stmt = $this->DB->query($request, array($recipe_id));
            $data = $stmt->fetch();
            if($data)   
                return (int)$data['pic_id'];
            else        
                return 0;
        }

        /**
         * Get list of recipe IDs
         * @return Array list of all recipe IDs in library
         */
        public function getList()
        {
            $request = 'SELECT recipe_id FROM recipes';
            $stmt = $this->DB->query($request);
            return array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN, 0));
        }
        
        /**********************************************************
        HISTORY
        ***********************************************************/

        /**
         * List most recent recipes
         * @return Array list of most recent recipe IDs
         */
        public function history_get()
        {
            $request = 'SELECT recipe_id FROM recipes'
                    . ' WHERE valid = 1 AND public = 1 AND lang = ?'
                    . ' ORDER BY date_created LIMIT ' . self::HISTORY_MAX;
            $stmt = $this->DB->query($request, array($this->User->getLang()));
            return array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN, 0));
        }
        
        /**********************************************************
        INSERT
        ***********************************************************/

        /**
         * Save a new recipe in database
         * @param Array $recipe associative array of recipe properties
         * @param Bool $public whether the recipe should be available for everyone
         * @param String $lang recipe language identifier
         * @param Int $partner_id ID of the partner associated to the recipe (if any)
         * @return Int ID of the recipe that was saved or 0 (in case of error)
         */
        public function insert(array $recipe, $public, $lang, $partner_id = 0)
        {
            //Validate recipe data
            $recipe = $this->check($recipe, $lang);

            //Retrieve partner's virtual user ID
            if($partner_id)
            {
                $request = 'SELECT user_id FROM users WHERE partner_id = ?';
                $stmt = $this->DB->query($request, array($partner_id));
                $data = $stmt->fetch();
                if($data) 
                    $author = (int)$data['user_id'];
            }
            else
                $author = $this->User->getID();

            //Insert recipe in database
            $request = 'INSERT INTO recipes (name, date_created, description, pic_id, guests, author_id, category,'
                            . ' origin, preparation, cooking, price, level, valid, public, lang)'
                        . ' VALUES (:name, NOW(), :desc, :pic_id, :guests, :author, :category,'
                            . ' :origin, :prep, :cook, :price, :level, :valid, :public, :lang)';
            $stmt = $this->DB->query($request, array(
                ':name'     => $recipe['name'],
                ':desc'     => $recipe['desc'],
                ':pic_id'   => $recipe['pic_id'],
                ':guests'   => $recipe['guests'],
                ':author'   => $author,
                ':category' => $recipe['cat'],
                ':origin'   => $recipe['origin'],
                ':prep'     => $recipe['prep'],
                ':cook'     => $recipe['cook'],
                ':price'    => $recipe['price'],
                ':level'    => $recipe['level'],
                ':valid'    => $recipe['valid'],
                ':public'   => $public,
                ':lang'     => $lang
            ));
            if($stmt->rowCount())
            {
                //Retrieve recipe ID
                $recipe_id = $this->DB->insertID();

                //Insert ingredients quantities
                $this->insertIngredients($recipe_id, $recipe['ings']);
                //Compute "healthy" and "veggie" scores
                $this->test_healthy($recipe_id, $update = true);
                $this->test_veggie($recipe_id, $update = true);

                //Return new recipe ID
                return $recipe_id;
            }
            else 
                //Insertion failed
                throw new KookiizException('recipeform', 1);
        }
        
        /**
         * Insert recipe ingredients in database
         * @param Int $recipe_id unique recipe ID
         * @param Array $ingredients list of recipe ingredient quantities
         */
        private function insertIngredients($recipe_id, array $ingredients)
        {
            $request = 'INSERT INTO recipes_ingredients (recipe_id, ingredient_id, quantity, unit)'
                        . ' VALUES (:recipe_id, :ing_id, :quantity, :unit)';
            $stmt = $this->DB->prepare($request);
            foreach($ingredients as $ingredient)
            {
                $ing_id = (int)$ingredient['i'];
                if(!$ing_id) continue;

                $this->DB->execute($stmt, array(
                    ':recipe_id'    => $recipe_id,
                    ':ing_id'       => $ing_id,
                    ':quantity'     => $ingredient['q'],
                    ':unit'         => $ingredient['u']
                ));
            }
        }

        /**********************************************************
        LANG
        ***********************************************************/

        /**
         * Return languages in which provided recipe is available
         * @param Int $recipe_id unique ID of the recipe
         * @return Array list of language identifiers
         */
        public function langsGet($recipe_id)
        {
            $langs = array();

            //Retrieve recipe initial language
            $request = 'SELECT lang FROM recipes WHERE recipe_id = ?';
            $stmt = $this->DB->query($request, array($recipe_id));
            $data = $stmt->fetch();
            if($data)   
                $langs[] = $data['lang'];
            else        
                die();

            //Look for existing translations of this recipe
            $request = 'SELECT lang FROM recipes_translations'
                    . ' WHERE recipe_id = ? OR translated_id = ?';
            $stmt = $this->DB->query($request, array($recipe_id, $recipe_id));
            $langs = array_merge($langs, $stmt->fetchAll(PDO::FETCH_COLUMN, 0));

            //Return list of languages
            return $langs;
        }
        
        /**********************************************************
        LIST
        ***********************************************************/
        
        /**
         * List dismissed recipes
         * @return Array list of dismissed recipe IDs
         */
        public function listDismiss()
        {
            $request = 'SELECT recipe_id FROM recipes WHERE valid = 0';
            $stmt = $this->DB->query($request);
            return array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN, 0));
        }
        
        /**********************************************************
        LOAD
        ***********************************************************/

        /**
         * Load content of provided recipes IDs
         * Only recipes authorized for current user will be returned!
         * @param Array $recipes_ids +ordered+ list of recipe IDs to load
         * @param String $mode either 'full' (all recipe content) or 'short' (ID and name)
         * @param Bool $random specifies if recipes should be retrieved in a random order (optional)
         * @return Array list of recipe objects sorted according to $recipes_ids
         */
        public function load($recipes_ids, $mode = 'full', $random = false)
        {
            $recipes = array(); $authorized = array();
            $autorized = $this->authorize($recipes_ids);    //Check authorizations (order is lost!)
            
            if(count($autorized))
            {
                //Return recipes full content
                if($mode == 'full')
                {
                    //Retrieve recipes content
                    $request = 'SELECT recipes.*, UNIX_TIMESTAMP(date_updated) AS updated, recipes_ingredients.*,'
                                . ' users.user_id, users.name AS author_name, users.chef_id, users.partner_id,'
                                . ' partners.valid AS partner_valid'
                            . ' FROM recipes'
                                . ' NATURAL JOIN recipes_ingredients'
                                . ' LEFT JOIN users ON author_id = user_id'
                                . ' LEFT JOIN partners ON users.partner_id = partners.partner_id'
                            . ' WHERE recipes.recipe_id IN (' . implode(', ', $autorized) . ')';
                    if($random) 
                        $request .= ' ORDER BY RAND()';
                    $stmt = $this->DB->query($request);

                    //Build results table
                    while($recipe = $stmt->fetch())
                    {
                        $id = (int)$recipe['recipe_id'];
                        if(!isset($recipes[$id]))
                        {
                            $recipes[$id] = array(
                                'id'        => $id,
                                'name'      => htmlspecialchars($recipe['name'], ENT_COMPAT, 'UTF-8'),
                                'desc'      => htmlspecialchars($recipe['description'], ENT_COMPAT, 'UTF-8'),
                                'pic'       => (int)$recipe['pic_id'],
                                'guest'     => (int)$recipe['guests'],
                                'prep'      => (int)$recipe['preparation'],
                                'cook'      => (int)$recipe['cooking'],
                                'price'     => (float)$recipe['price'],
                                'healthy'   => (int)$recipe['healthy'],
                                'veggie'    => (int)$recipe['veggie'],
                                'rate'      => (float)$recipe['rating'],
                                'lev'       => (int)$recipe['level'],
                                'cat'       => (int)$recipe['category'],
                                'orig'      => (int)$recipe['origin'],
                                'valid'     => (int)$recipe['valid'],
                                'public'    => (int)$recipe['public'],
                                'lang'      => $recipe['lang'],
                                'update'    => (int)$recipe['updated'],
                                'order'     => array_search($recipe['recipe_id'], $recipes_ids)
                            );

                            //User data
                            //Check that user ID is not NULL and, if user is a partner, that this partner is valid
                            if($recipe['user_id'] && (!$recipe['partner_id'] || $recipe['partner_valid']))
                            {
                                $recipes[$id]['auth_id']    = (int)$recipe['user_id'];
                                $recipes[$id]['auth_name']  = htmlspecialchars($recipe['author_name'], ENT_COMPAT, 'UTF-8');
                                $recipes[$id]['chef_id']    = (int)$recipe['chef_id'];
                                $recipes[$id]['partner_id'] = (int)$recipe['partner_id'];
                            }
                            //All other cases (non-existing user or non-valid partner)
                            else
                            {
                                $recipes[$id]['auth_id']    = C::RECIPE_AUTHOR_DEFAULT;
                                $recipes[$id]['auth_name']  = C::RECIPE_AUTHORNAME_DEFAULT;
                                $recipes[$id]['chef_id']    = 0;
                                $recipes[$id]['partner_id'] = 1;
                            }

                            //Initialize ingredients array
                            $recipes[$id]['ing'] = array();
                        }

                        //Add current ingredient
                        $recipes[$id]['ing'][] = array(
                            'i' => (int)$recipe['ingredient_id'],
                            'q' => (float)$recipe['quantity'],
                            'u' => (int)$recipe['unit']
                        );
                    }

                    //Update 'viewed' parameter for selected recipes
                    $this->viewed($autorized);
                }
                //Return only recipes IDs and names
                else if($mode == 'short')
                {
                    $request = 'SELECT recipe_id, name'
                            . ' FROM recipes'
                            . ' WHERE recipe_id IN (' . implode(', ', $autorized) . ')';
                    if($random) 
                        $request .= ' ORDER BY RAND()';
                    $stmt = $this->DB->query($request);

                    //Build results table
                    while($recipe = $stmt->fetch())
                    {
                        $recipes[] = array(
                            'id'    => (int)$recipe['recipe_id'],
                            'name'  => htmlspecialchars($recipe['name'], ENT_COMPAT, 'UTF-8'),
                            'order' => array_search($recipe['recipe_id'], $recipes_ids)
                        );
                    }
                }
            }

            //Keep only indexes with actual recipe content
            $recipes = array_values($recipes);
            
            //Sort recipes according to search order
            usort($recipes, array('RecipesLib', 'sortOrder'));

            //Return content
            return $recipes;
        }
        
        /**********************************************************
        PICTURE
        ***********************************************************/

        /**
         * Save a picture for a recipe
         * @param Int $recipe_id unique ID of the recipe
         * @param Int $pic_id unique ID of the picture
         */
        function pic_save($recipe_id, $pic_id)
        {
            //Check that the recipe exists
            $request = 'SELECT pic_id FROM recipes WHERE recipe_id = ?';
            $stmt = $this->DB->query($request, array($recipe_id));
            $data = $stmt->fetch();
            if($data)
            {
                //Check that the recipe does not have a picture yet
                $current_pic = (int)$data['pic_id'];
                if(!$current_pic)
                {
                    //Update recipe picture
                    $request = 'UPDATE recipes SET pic_id = ?'
                            . ' WHERE recipe_id = ?';
                    $params = array($pic_id, $recipe_id);
                    $this->DB->query($request, $params);
                }
                //Recipe already has a picture
                else 
                    throw new KookiizException('recipes', Error::RECIPE_HASPIC);
            }
            //Recipe does not exist
            else 
                throw new KookiizException('recipes', Error::RECIPE_UNKNOWN);
        }
        
        /**********************************************************
        RATE
        ***********************************************************/

        /**
         * Rate a recipe
         * @param Int $recipe_id unique ID of the recipe to rate
         * @param Int $rating rating value
         * @return Array rating ID (0 if rating was just updated) 
         *              & new recipe rating (-1 if no change)
         */
        public function rate($recipe_id, $rating)
        {
            //Limit rating value
            if($rating < self::RATING_MIN)
                $rating = self::RATING_MIN;
            else if($rating > self::RATING_MAX) 
                $rating = self::RATING_MAX;

            //Request for already existing vote for this recipe from this user
            $request = 'SELECT * FROM recipes_ratings'
                    . ' WHERE user_id = ? AND recipe_id = ?';
            $stmt = $this->DB->query($request, array($this->User->getID(), $recipe_id));
            $rating_data = $stmt->fetch();

            //Case where there is already a vote for this recipe from this user
            if($rating_data)
            {
                $existing = (int)$rating_data['rating'];

                //Case where the new rating is different from existing one
                if($rating != $existing)
                {
                    //Update recipes_ratings table with the new rating
                    $request = 'UPDATE recipes_ratings SET rating = :rating'
                            . ' WHERE user_id = :user_id AND recipe_id = :recipe_id';
                    $this->DB->query($request, array(
                        ':rating'       => $rating,
                        ':user_id'      => $this->User->getID(),
                        ':recipe_id'    => $recipe_id
                    ));

                    //Get current rating and votes count for this recipe
                    $request = 'SELECT rating, votes FROM recipes WHERE recipe_id = ?';
                    $stmt = $this->DB->query($request, array($recipe_id));
                    $rating_data = $stmt->fetch();
                    $old_rating  = (float)$rating_data['rating'];
                    $votes       = (int)$rating_data['votes'];

                    //Compute updated rating
                    $new_rating = $old_rating + (($rating - $old_rating)  / $votes);

                    //Update recipe table with new rating
                    $request = 'UPDATE recipes SET rating = ?'
                            . ' WHERE recipe_id = ?';
                    $this->DB->query($request, array($new_rating, $recipe_id));

                    //No new rating ID, but updated recipe rating
                    return array(0, $new_rating);
                }
                else 
                    //No new rating ID and no change in recipe rating
                    return array(0, -1);
            }
            //Case where current user has not voted for this recipe yet
            else
            {
                //Insert a new vote in the table and retrieve unique rating ID
                $request = 'INSERT INTO recipes_ratings (recipe_id, user_id, rating)'
                            . ' VALUES (:recipe_id, :user_id, :rating)';
                $this->DB->query($request, array(
                    ':recipe_id'    => $recipe_id,
                    ':user_id'      => $this->User->getID(),
                    ':rating'       => $rating
                ));
                $rating_id = $this->DB->insertID();

                //Get current rating and votes count for this recipe
                $request = 'SELECT rating, votes FROM recipes WHERE recipe_id = ?';
                $stmt = $this->DB->query($request, array($recipe_id));

                //Compute updated rating
                $rating_data = $stmt->fetch();
                $old_rating  = (float)$rating_data['rating'];
                $old_votes   = (int)$rating_data['votes'];
                $new_votes   = $old_votes + 1;
                $new_rating  = ($old_rating * $old_votes + $rating) / $new_votes;

                //Update recipe table
                $request = 'UPDATE recipes SET rating = :new_rating, votes = :new_votes'
                        . ' WHERE recipe_id = :recipe_id';
                $this->DB->query($request, array(
                    ':new_rating'   => $new_rating,
                    ':new_votes'    => $new_votes,
                    ':recipe_id'    => $recipe_id
                ));

                //Return new rating ID and updated recipe rating
                return array($rating_id, $new_rating);
            }
        }
        
        /**********************************************************
        REPORT
        ***********************************************************/

        /**
         * Report a recipe as unappropriate
         * @param Int $recipe_id unique recipe ID
         */
        public function report($recipe_id)
        {
            //Report recipe
            $request = 'INSERT IGNORE INTO recipes_reports (recipe_id, user_id)'
                        . ' VALUES (:recipe_id, :user_id)';
            $stmt = $this->DB->query($request, array(
                ':recipe_id'    => $recipe_id,
                ':user_id'      => $this->User->getID()
            ));

            //Check if recipe should be removed
            if($stmt->rowCount())
            {
                //Sum all current reports for this recipe
                $request = 'SELECT SUM(1) AS reports'
                        . ' FROM recipes_reports'
                        . ' WHERE recipe_id = ?';
                $stmt = $this->DB->query($request, array($recipe_id));
                $data = $stmt->fetch();

                //Dismiss recipe if reports count exceeds limit
                $reports = (int)$data['reports'];
                if($reports > self::REPORTS_LIMIT)
                    $this->dismiss($recipe_id);
            }
        }

        /**********************************************************
        SEARCH
        ***********************************************************/

        /**
         * Search for recipes matching a set of criteria
         * @param Array $criteria associative array of integers/booleans
         * @param Int $max number of search results to return (optional)
         * @return Array list of recipe IDs matching provided criteria
         */
        public function search(array $criteria, $max = self::SEARCH_MAX)
        {
            $recipes = array();
            $this->overflow = false;
            $lang = $this->User->getLang();
            $IngredientsDB = new IngredientsDB($this->DB, $lang);

            //Ensure all criteria are set to something
            $criteria = array_merge(self::$CRITERIA_DEFAULTS, $criteria);
            //Ignore very short words for textual search
            $textLength = strlen(utf8_decode($criteria['text']));
            if($textLength < self::SEARCH_WORD_MIN) $criteria['text'] = '';

            //FROM INGREDIENTS
            //Look for recipes containing or excluding specific ingredients
            $ingIncluded = array(); $ingExcluded = array();
            $recIncluded = array(); $recExcluded = array();

            //INCLUDE
            /* Fridge */
            if($criteria['fridge'])
            {
                if($criteria['fridge'] > 0)
                    $ingIncluded[] = (int)$criteria['fridge'];
                else
                    $ingIncluded = array_merge($ingIncluded, $this->User->fridge_ids());
            }
            /* Season */
            if($criteria['season'])
            {
                if($criteria['season'] > 0)
                    $ingIncluded[] = (int)$criteria['season'];
                else
                    $ingIncluded = array_merge($ingIncluded, $IngredientsDB->seasonGet());
            }
            /* Liked */
            if($criteria['liked'])
                $ingIncluded = array_merge($ingIncluded, $this->User->tastes_get(C::TASTE_LIKE));
            //Recipes to include
            $ingIncluded = array_values(array_unique($ingIncluded));
            $recIncluded = $this->searchIngredients($ingIncluded);
            if(count($ingIncluded) && !count($recIncluded)) return array();

            //EXCLUDE
            /* Allergies */
            if($criteria['allergy'])
            {
                $allergies = $this->User->allergies_get();
                $ingExcluded = array_merge($ingExcluded, $IngredientsDB->searchAllergies($allergies));
            }
            /* Disliked */
            if($criteria['disliked'])
                $ingExcluded = array_merge($ingExcluded, $this->User->tastes_get(C::TASTE_DISLIKE));
            //Recipes to exclude
            $ingExcluded = array_values(array_unique($ingExcluded));
            $recExcluded = $this->searchIngredients($ingExcluded, $similar = false);

            //Remove excluded from included
            $recIncluded = array_diff($recIncluded, $recExcluded);

            //MAIN REQUEST
            //Search for recipes matching all required criteria (text, category, etc.)
            //Taking into account included and excluded recipes from above sub-requests
            //If a text string was provided, results are sorted by full text search score
            $request = 'SELECT DISTINCT recipes.recipe_id'; $params = array();           
            if($criteria['text'])
            {
                if($textLength < C::MYSQL_MIN_WORD)
                {
                    $request .= ', recipes.name LIKE ? AS score';
                    $params[] = "{$criteria['text']} %";
                }
                else
                {
                    $request .= ", MATCH(recipes.name, ingredient_name_$lang, ingredient_tags_$lang)"
                                . ' AGAINST (? IN BOOLEAN MODE) AS score';
                    $params[] = "{$criteria['text']}*";
                }
            }

            //FAVORITES
            if($criteria['favorites'])
            {
                //Choose from recipes amongst current user's favorites
                //Include non-valid and expired recipes
                $request .= ' FROM users_recipes'
                            . ' NATURAL JOIN recipes'
                            . ' NATURAL JOIN recipes_ingredients'
                            . ' NATURAL JOIN ingredients'
                            . ' LEFT JOIN recipes_tags USING(recipe_id)'
                          . ' WHERE users_recipes.user_id = ?';
                $params[] = $this->User->getID();
            }
            else
            {
                //Search among all valid recipes
                //Exclude non-valid recipes
                //Restrict to current language setting               
                $request .= ' FROM recipes'
                            . ' NATURAL JOIN recipes_ingredients'
                            . ' NATURAL JOIN ingredients'
                            . ' LEFT JOIN recipes_tags USING(recipe_id)'
                          . ' WHERE recipes.valid = 1 AND recipes.lang = ?';
                $params[] = $this->User->getLang();
            }

            //Take only public recipes or those from current user
            $request .= ' AND (recipes.public = 1 OR recipes.author_id = ?)';
            $params[] = $this->User->getID();

            //Search for text
            if($criteria['text'])
            {
                //Search in recipe title with LIKE for short words
                if($textLength < C::MYSQL_MIN_WORD)
                {
                    $request .= ' AND (recipes.name LIKE ?';
                    $params[] = "{$criteria['text']} %";
                }
                //Search in recipe title, ingredient name and tags with MATCH
                else
                {
                    $request .= " AND MATCH(recipes.name, ingredient_name_$lang, ingredient_tags_$lang)"
                                . ' AGAINST (? IN BOOLEAN MODE)';
                    $params[] = "{$criteria['text']}*";
                }
            }
            //Search for specific tag IDs
            if(count($criteria['tags']))
            {
                $criteria['tags'] = array_map('intval', $criteria['tags']);
                $request .= ' AND tag_id IN (' . implode(', ', $criteria['tags']) . ')';
            }
            //Search for recipes of a given category ID
            if($criteria['category'] > 0)
            {
                $request .= ' AND recipes.category = ?';
                $params[] = $criteria['category'];
            }
            //Search of recipes of a given origin ID
            if($criteria['origin'] > 0)
            {
                $request .= ' AND recipes.origin = ?';
                $params[] = $criteria['origin'];
            }
            
            //Booleans
            $booleans = array(
                'cheap'     => $criteria['cheap'],
                'easy'      => $criteria['easy'],
                'healthy'   => $criteria['healthy'],
                'quick'     => $criteria['quick'],
                'success'   => $criteria['success'],
                'veggie'    => $criteria['veggie']
            );
            foreach($booleans as $criterion => $value)
            {
                if($value)
                {
                    $request .= ' AND ';
                    switch($criterion)
                    {
                        case 'cheap':
                            $request .= 'recipes.price < ?';
                            $params[] = self::CHEAP_THRESHOLD;
                            break;
                        case 'easy':
                            $request .= 'recipes.level <= ?';
                            $params[] = self::EASY_THRESHOLD;
                            break;
                        case 'healthy':
                            $request .= 'recipes.healthy > ?';
                            $params[] = self::HEALTHY_THRESHOLD;
                            break;
                        case 'quick':
                            $request .= 'recipes.preparation + recipes.cooking < ?';
                            $params[] = self::QUICK_THRESHOLD;
                            break;
                        case 'success':
                            $request .= 'recipes.rating >= ?';
                            $params[] = self::SUCCESS_THRESHOLD;
                            break;
                        case 'veggie':
                            $request .= 'recipes.veggie >= ?';
                            $params[] = C::VEGGIE_REGULAR;
                            break;
                    }
                }
            }

            //Take into account included/excluded recipes
            if(count($recIncluded)) 
                $request .= ' AND recipes.recipe_id IN (' . implode(', ', $recIncluded) . ')';
            if(count($recExcluded)) 
                $request .= ' AND recipes.recipe_id NOT IN (' . implode(', ', $recExcluded) . ')';

            //Sort randomly or by relevancy (if full text search is enabled)
            if($criteria['random'])     
                $request .= ' ORDER BY RAND()';
            else if($criteria['text'])
                $request .= ' ORDER BY score DESC';

            //Retrieve recipe IDs fullfilling above criteria
            $stmt    = $this->DB->query($request, $params);
            $recipes = array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN, 0));

            //Set overflow flag
            if(count($recipes) >= self::SEARCH_MAX)
                $this->overflow = true;

            //Return results
            return $recipes;
        }

        /**
         * Look for recipes from any chef or a specific one
         * @param Int $chef_id ID of the chef
         * @param Int $limit results limit
         * @return Array list of recipe IDs from any chef or specific one
         */
        private function searchChef($chef_id = 0, $limit = 0)
        {
            //Build query
            $request = 'SELECT recipe_id'
                    . ' FROM recipes INNER JOIN users ON author_id = user_id';
            $params = array();
            if($chef_id)
            {
                $request .= ' WHERE chef_id = :chef_id';
                $params[':chef_id'] = $chef_id;
            }
            else
                $request .= 'WHERE chef_id != 0';
            if($limit)
            {
                $request .= ' ORDER BY rating LIMIT :limit';
                $params[':limit'] = $limit;
            }
            //Retrieve results
            $stmt = $this->DB->query($request, $params);
            return array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN, 0));
        }

        /**
         * Look for recipes which include specific ingredients
         * @param Array $ingredients list of ingredient IDs
         * @param Bool $similar whether to include similar ingredients
         * @param Int $limit max results (defaults to 0 = no limit)
         * @return Array list of recipe IDs containing specified ingredients
         */
        private function searchIngredients(array $ingredients, $similar = true, $limit = 0)
        {
            $recipes = array();
            if(count($ingredients) && !is_nan($ingredients[0]))
            {
                if($similar)
                {
                    $IngredientsDB = new IngredientsDB($this->DB, $this->User->getLang());
                    $ingredients = $IngredientsDB->searchSimilar($ingredients);
                }
                
                //Prepare request statement
                $request = 'SELECT recipes.recipe_id'
                        . ' FROM recipes'
                            . ' NATURAL JOIN recipes_ingredients'
                        . ' WHERE ingredient_id IN (' . implode(', ', $ingredients) . ')'
                            . ' AND lang = ?';
                $params = array($this->User->getLang());
                if($limit)
                {
                    $request .= ' ORDER BY rating LIMIT ?';
                    $params[] = $limit;
                }
                //Retrieve results
                $stmt    = $this->DB->query($request, $params);
                $recipes = array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN, 0));
            }
            return $recipes;
        }

        /**
         * Check if last search had more results than the max
         * @return Bool whether there was an overflow
         */
        public function searchOverflow()
        {
            return $this->overflow;
        }
        
        /**********************************************************
        SHARED
        ***********************************************************/

        /**
         * Load information on recipes shared with current user
         * @return Array list of shared recipes data
         */
        public function shared_load()
        {
            $shared_recipes = array();
            $request = 'SELECT recipes.recipe_id, recipes.name AS recipe_name, recipes.pic_id,'
                        . ' users.user_id AS author_id, users.firstname AS author_name,'
                        . ' UNIX_TIMESTAMP(shared_recipes.share_date) AS date, shared_recipes.viewed'
                    . ' FROM shared_recipes'
                        .' NATURAL JOIN recipes'
                        . ' LEFT JOIN users ON shared_recipes.user_1 = users.user_id'
                    . ' WHERE shared_recipes.user_2 = ?'
                    . ' ORDER BY date DESC';
            $stmt = $this->DB->query($request, array($this->User->getID()));
            while($recipe = $stmt->fetch())
            {
                $shared_recipes[] = array(
                    'recipe_id'     => (int)$recipe['recipe_id'],
                    'recipe_name'   => htmlspecialchars($recipe['recipe_name'], ENT_COMPAT, 'UTF-8'),
                    'recipe_pic'    => (int)$recipe['pic_id'],
                    'author_id'     => (int)$recipe['author_id'],
                    'author_name'   => htmlspecialchars($recipe['author_name'], ENT_COMPAT, 'UTF-8'),
                    'date'          => date('d.m.Y', (int)$recipe['date']),
                    'time'          => date('H:i', (int)$recipe['date']),
                    'viewed'        => (int)$recipe['viewed']
                );
            }

            //Set viewed as true for shared recipes
            $request = 'UPDATE shared_recipes SET viewed = 1 WHERE user_2 = ?';
            $this->DB->query($request, array($this->User->getID()));

            //Return shared recipes data
            return $shared_recipes;
        }
        
        /**********************************************************
        SORT
        ***********************************************************/
        
        /**
         * Sort recipes by name
         * @param Array $rec_a first recipe to sort
         * @param Array $rec_b second recipe to sort
         * @return Int -1: (a before b), 1: (b before a), 0: no sorting
         */
        public static function sortABC($rec_a, $rec_b)
        {
            return $rec_a['name'] < $rec_b['name'] ? -1 : 1;
        }

        /**
         * Sort recipe IDs by custom order
         * @param Array $rec_a first recipe to sort
         * @param Array $rec_b second recipe to sort
         * @return Int <0: (a before b), >0: (b before a), 0: no sorting
         */
        public static function sortOrder($rec_a, $rec_b)
        {
            if($rec_a['order'] === false && $rec_b['order'] === false) return 0;
            else if($rec_a['order'] === false) return 1;
            else if($rec_b['order'] === false) return -1;
            else return $rec_a['order'] - $rec_b['order'];

            //Closures do no work in PHP version < 5.3!
            /*
            return function($rec_a, $rec_b) use ($order)
            {
                $pos_a = array_search($rec_a['id'], $order);
                $pos_b = array_search($rec_b['id'], $order);
                if($pos_a === false && $pos_b === false) return 0;
                else if($pos_a === false) return 1;
                else if($pos_b === false) return -1;
                else return $pos_a - $pos_b;
            };
            */
        }
        
        /**
         * Sort recipes by increasing price
         * @param Array $rec_a first recipe to sort
         * @param Array $rec_b second recipe to sort
         * @return Int -1: (a before b), 1: (b before a), 0: no sorting
         */
        public static function sortPrice($rec_a, $rec_b)
        {
            return $rec_a['price'] < $rec_b['price'] ? -1
                    : ($rec_a['price'] > $rec_b['price'] ? 1 : self::sortABC($rec_a, $rec_b));
        }
        
        /**
         * Sort recipes by decreasing rating
         * @param Array $rec_a first recipe to sort
         * @param Array $rec_b second recipe to sort
         * @return Int -1: (a before b), 1: (b before a), 0: no sorting
         */
        public static function sortRating($rec_a, $rec_b)
        {
            return $rec_a['rate'] > $rec_b['rate'] ? -1
                    : ($rec_a['rate'] < $rec_b['rate'] ? 1 : self::sortABC($rec_a, $rec_b));
        }
        
        /**********************************************************
        SUGGESTIONS
        ***********************************************************/

        /**
         * Suggest a list of recipes with pictures
         * @return Array random list of recipe IDs
         */
        public function suggestions_get()
        {
            $request = 'SELECT recipe_id'
                    . ' FROM recipes'
                    . ' WHERE pic_id != 0'
                        . ' AND valid = 1'
                        . ' AND public = 1'
                        . " AND lang = ?"
                    . ' ORDER BY RAND() LIMIT ' . self::SUGGESTIONS_MAX;
            $stmt = $this->DB->query($request, array($this->User->getLang()));
            return array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN, 0));
        }
        
        /**********************************************************
        TAGS
        ***********************************************************/

        /**
         * Delete a recipe tag
         * @param Int $recipe_id unique ID of the recipe
         * @param Int $tag_id tag unique ID
         */
        public function tags_delete($recipe_id, $tag_id)
        {
            $request = 'DELETE FROM recipes_tags WHERE recipe_id = ? AND tag_id = ?';
            $params = array($recipe_id, $tag_id);
            $this->DB->query($request, $params);
        }

        /**
         * Load recipe tags
         * @param Int $recipe_id unique ID of the recipe
         * @return Array list of tag IDs
         */
        public function tags_load($recipe_id)
        {
            $request = 'SELECT tag_id FROM recipes_tags WHERE recipe_id = ?';
            $stmt = $this->DB->query($request, array($recipe_id));
            return array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN, 0));
        }

        /**
         * Save a recipe tag
         * @param Int $recipe_id unique recipe ID
         * @param Int $tag_id unique tag ID
         */
        public function tags_save($recipe_id, $tag_id)
        {
            if(!$recipe_id || !$tag_id) return;

            //Insert tag in cross table
            $request = 'INSERT IGNORE INTO recipes_tags (recipe_id, tag_id)'
                        . ' VALUES (?, ?)';
            $this->DB->query($request, array($recipe_id, $tag_id));
        }
        
        /**********************************************************
        TEST
        ***********************************************************/

        /**
         * Compute healthy score for given recipe
         * @param Int $recipe_id unique recipe ID
         * @param Bool $update whether to update recipe healthy value in DB
         * @return Int computed health score
         */
        public function test_healthy($recipe_id, $update = false)
        {
            $references = C::get('NUT_REFERENCES');
            $values     = C::get('NUT_VALUES');

            //Retrieve reference values (for an adult woman)
            $kcal_ref   = $references[0][array_search('kcal', $values)];
            $carb_ref   = $references[0][array_search('carb', $values)];
            $fat_ref    = $references[0][array_search('fat', $values)];
            $prot_ref   = $references[0][array_search('prot', $values)];
            $fibre_ref  = $references[0][array_search('fibre', $values)];
            $chol_ref   = $references[0][array_search('chol', $values)];

            //For each essential nutriment, the request computes the total percentage of the recipe nutritional
            //content relatively to the reference daily value according to the formula: (q * u->g * nut/100g) / (100 * ref * guests)
            //Where:
            //		q           = quantity of current ingredient (any unit),
            //		u->g        = conversion of ingredient unit to grams,
            //		nut/100g    = quantity of current nutriment for 100g of current ingredient,
            //		ref         = daily reference value for current nutriment,
            //		guests      = number of guests the recipe is meant for
            $request = 'SELECT recipes.recipe_id,'
                        . " SUM(quantity * IF(units.value != 0, units.value, ingredient_wpu) * ingredient_kcal / (100 * $kcal_ref * guests)) AS kcal,"
                        . " SUM(quantity * IF(units.value != 0, units.value, ingredient_wpu) * ingredient_carb / (100 * $carb_ref * guests)) AS carb,"
                        . " SUM(quantity * IF(units.value != 0, units.value, ingredient_wpu) * ingredient_fat / (100 * $fat_ref * guests)) AS fat,"
                        . " SUM(quantity * IF(units.value != 0, units.value, ingredient_wpu) * ingredient_prot / (100 * $prot_ref * guests)) AS prot,"
                        . " SUM(quantity * IF(units.value != 0, units.value, ingredient_wpu) * ingredient_fibre / (100 * $fibre_ref * guests)) AS fibre,"
                        . " SUM(quantity * IF(units.value != 0, units.value, ingredient_wpu) * ingredient_chol / (100 * $chol_ref * guests)) AS chol"
                    . ' FROM recipes, recipes_ingredients, ingredients, units'
                    . ' WHERE recipes.recipe_id = recipes_ingredients.recipe_id'
                        . ' AND recipes_ingredients.ingredient_id = ingredients.ingredient_id'
                        . ' AND recipes_ingredients.unit = units.unit_id'
                        . ' AND recipes.recipe_id = ?'
                    . ' GROUP BY recipe_id';
            $stmt = $this->DB->query($request, array($recipe_id));
            $data = $stmt->fetch();

            //Retrieve values
            $kcal   = $data['kcal'];
            $carb   = $data['carb'];
            $fat    = $data['fat'];
            $prot   = $data['prot'];
            $fibre  = $data['fibre'];
            $chol   = $data['chol'];

            //Compute score
            $score = 0;
            $sum = $kcal + $carb + $fat + $prot + $fibre + $chol;
            if($sum <= self::HEALTHY_SUM_IDEAL)
            {
                $slope  = self::HEALTHY_SCORE_MAX / self::HEALTHY_SUM_IDEAL;
                $origin = 0;
            }
            else
            {
                $slope  = 0 - (self::HEALTHY_SCORE_FACTOR * self::HEALTHY_SCORE_MAX / self::HEALTHY_SUM_IDEAL);
                $origin = (self::HEALTHY_SCORE_FACTOR + 1) * self::HEALTHY_SCORE_MAX;
            }
            $score = round($slope * $sum + $origin);
            //Keep it in boundaries
            if($score < self::HEALTHY_SCORE_MIN)
                $score = self::HEALTHY_SCORE_MIN;

            //Update "healthy" score for current recipe
            if($update)
            {
                $request = 'UPDATE recipes SET healthy = ? WHERE recipe_id = ?';
                $this->DB->query($request, array($score, $recipe_id));
            }

            //Return computed score
            return $score;
        }

        /**
         * Check if recipe is veggie
         * @param Int $recipe_id unique recipe ID
         * @param Bool $update whether to update recipe veggie value in DB
         * @return Int veggie score as: 0 (not veggie), 1 (veggie) or 2 (vegan)
         */
        public function test_veggie($recipe_id, $update = false)
        {
            //Ask for categories of each ingredient of the recipe
            $request = 'SELECT ingredients.ingredient_cat'
                    . ' FROM recipes'
                        . ' NATURAL JOIN recipes_ingredients'
                        . ' NATURAL JOIN ingredients'
                    . ' WHERE recipes.recipe_id = ?';
            $stmt = $this->DB->query($request, array($recipe_id));
            $data = $stmt->fetch();

            //Loop through ingredients
            $vegan = true; $veggie = true;
            $catsVegan  = C::get('ING_CATS_VEGAN');
            $catsVeggie = C::get('ING_CATS_VEGGIE');
            while($ingredient = $stmt->fetch())
            {
                $category = (int)$ingredient['ingredient_cat'];
                if(!in_array($category, $catsVegan))
                    $vegan = false;
                if(!in_array($category, $catsVeggie))
                {
                    //If one ingredient is not veggie, stop here
                    $veggie = false;
                    break;
                }
            }
            $score = $vegan ? C::VEGGIE_STRICT : ($veggie ? C::VEGGIE_REGULAR : C::VEGGIE_NONE);

            //Update "veggie" parameter for current recipe
            if($update)
            {
                $request = 'UPDATE recipes SET veggie = ? WHERE recipe_id = ?';
                $this->DB->query($request, array($score, $recipe_id));
            }

            //Return computed score
            return $score;
        }

        /**********************************************************
        TITLE
        ***********************************************************/

        /**
         * Check if recipe title already exists
         * @param String $title recipe title to search for
         * @return Array list of existing recipe IDs with a similar title 
         */
        public function title_check($title)
        {
            //Explode title and retrieve important words
            $important_words = array();
            $title = explode(' ', $title);
            for($i = 0, $imax = count($title); $i < $imax; $i++)
            {
                if(strlen($title[$i]) > 2) 
                    $important_words[] = $title[$i];
            }

            //Ask for recipes containing at least all important words
            $existing_recipes = array();
            if(count($important_words))
            {
                $request = 'SELECT recipe_id FROM recipes'
                        . ' WHERE MATCH(name) AGAINST(? IN BOOLEAN MODE)';
                $params = array('*');
                foreach($important_words as $word)
                {
                    $params[0] .= "+$word ";
                }
                $stmt = $this->DB->query($request, $params);

                //Loop through retrieved recipes
                while($recipe = $stmt->fetch())
                {
                    $existing_recipes[] = (int)$recipe['recipe_id'];
                }
            }
            return $existing_recipes;
        }
        
        /**********************************************************
        TRANSLATE
        ***********************************************************/

        /**
         * Save recipe translation
         * @param Int $recipe_id ID of the original recipe
         * @param Array $translation array with translation properties
         * @param String $lang destination language identifier
         * @return Int unique ID of the translated recipe
         */
        public function translate($recipe_id, array $translation, $lang)
        {
            //Check if recipe is not already translated
            $request = 'SELECT 1 FROM recipes_translations'
                    . ' WHERE recipe_id = ? AND lang = ?';
            $stmt = $this->DB->query($request, array($recipe_id, $lang));
            $data = $stmt->fetch();
            if($data) 
                throw new KookiizException('recipes', Error::RECIPE_TRANSLATEFAILED);

            //Retrieve existing recipe properties
            $request = 'SELECT * FROM recipes WHERE recipe_id = ?';
            $stmt = $this->DB->query($request, array($recipe_id));
            $data = $stmt->fetch();
            if($data)
            {
                //Check that new lang is different from original one
                $old_lang = $data['lang'];
                if($old_lang == $lang) 
                    throw new KookiizException('recipes', Error::RECIPE_TRANSLATEFAILED);

                //Retrieve recipe fields and include translations
                $author_id = (int)$data['author_id'];
                $public =    (int)$data['public'];
                $recipe = array(
                    'name'          => $translation['name'],
                    'description'   => $translation['description'],
                    'pic_id'        => (int)$data['pic_id'],
                    'guests'        => (int)$data['guests'],
                    'category'      => (int)$data['category'],
                    'origin'        => (int)$data['origin'],
                    'level'         => (int)$data['level'],
                    'preparation'   => (int)$data['preparation'],
                    'cooking'       => (int)$data['cooking'],
                    'price'         => (int)$data['price'],
                    'ingredients'   => array()
                );

                //Retrieve related ingredients
                $request = 'SELECT * FROM recipes_ingredients WHERE recipe_id = ?';
                $stmt = $this->DB->query($request, array($recipe_id));
                while($ingredient = $stmt->fetch())
                {
                    $recipe['ingredients'][] = array(
                        'id'        => (int)$ingredient['ingredient_id'],
                        'quantity'  => (float)$ingredient['quantity'],
                        'unit'      => (int)$ingredient['unit']
                    );
                }

                //Save new recipe
                $new_id = $this->insert($recipe, $public, $lang);

                //Save translation information (in both directions !)
                $request = 'INSERT INTO recipes_translations (recipe_id, translated_id, user_id, lang)'
                            . ' VALUES (?, ?, ?, ?), (?, ?, ?, ?)';
                $this->DB->query($request, array(
                    $recipe_id, $new_id, $user_id, $lang,
                    $new_id, $recipe_id, $author_id, $old_lang
                ));

                //Return new recipe ID
                return $new_id;
            }
            //Original recipe was not found
            else 
                throw new KookiizException('recipes', Error::RECIPE_TRANSLATEFAILED);
        }

        /**********************************************************
        UPDATED
        ***********************************************************/

        /**
         * Return recipes from provided array that have been updated since provided timestamps
         * @param Array $ids list of recipe IDs
         * @param Array $times list of recipe timestamps (with respect to $ids)
         * @return type list of updated recipe IDs
         */
        public function updatedGet(array $ids, array $times)
        {
            //Look for updated recipes
            $updated = array();
            if(count($ids))
            {
                $request = 'SELECT recipe_id AS id, UNIX_TIMESTAMP(date_updated) AS time'
                        . ' FROM recipes WHERE recipe_id IN (' . implode(', ', $ids) . ')';
                $stmt = $this->DB->query($request);
                while($recipe = $stmt->fetch())
                {
                    $id    = (int)$recipe['id'];
                    $time  = (int)$recipe['time'];
                    $index = array_search($id, $ids);
                    if($index !== false && $time > $times[$index]) 
                        $updated[] = $id;
                }
            }
            return $updated;
        }
        
        /**********************************************************
        VALIDATE
        ***********************************************************/
        
        /**
         * Validate a dismissed recipe
         * @param Int $recipe_id unique recipe ID
         */
        public function validate($recipe_id)
        {
            $request = 'UPDATE recipes SET valid = 1 WHERE recipe_id = ?';
            $this->DB->query($request, array($recipe_id));
        }

        /**********************************************************
        VIEWED
        ***********************************************************/
        
        /**
         * Increase "viewed" counter for provided recipes
         * @param Array $recipes_ids list of recipe IDs
         */
        private function viewed(array $recipes_ids)
        {
            $request = 'INSERT INTO recipes_updates (recipe_id, viewed)'
                        . ' VALUES (?, ?) ON DUPLICATE KEY UPDATE viewed = viewed + 1';
            $params = array();
            foreach($recipes_ids as $recipe_id)
            {
                $params[] = array($recipe_id, 0);
            }
            $this->DB->query($request, $params);
        }
    }
?>