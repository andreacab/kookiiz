<?php
    /*******************************************************
    Title: Recipes Controller
    Authors: Kookiiz Team
    Purpose: Display recipes
    ********************************************************/

    //Dependencies
    require_once '../class/dblink.php';
    require_once '../class/globals.php';
    require_once '../class/ingredients_db.php';
    require_once '../class/lang_db.php';
    require_once '../class/recipes_lib.php';
    require_once '../class/units_lib.php';
    require_once '../class/user.php';

    //Represents a controller for recipes display
    class recipesController 
    {
        /**********************************************************
        PROPERTIES
        ***********************************************************/
        
        //Objects
        private $DB;
        private $IngDB;
        private $Lang;
        private $RecLib;
        private $User;
        
        //Other
        private $recipes;
        private $sort;

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
            $this->RecLib = new RecipesLib($this->DB, $this->User);
            
            //Recipes storage and sorting mode
            $this->recipes = array();
            $this->sort = 'abc';
        }
        
        /**********************************************************
        DISPLAY
        ***********************************************************/

        /**
         * Display a recipe in full page
         * @param Int $recipe_id unique ID of the recipe
         * @return Bool success or failure
         */
        public function displayFull($recipe_id)
        {
            //Try to load recipe data
            $recipe = $this->RecLib->load(array($recipe_id));
            if(count($recipe))
                $recipe = $recipe[0];
            else
                return false;

            //Retrieve required texts
            $Titles  = $this->Lang->get('RECIPE_DISPLAY_TITLES');
            $Texts   = $this->Lang->get('RECIPE_DISPLAY_TEXT');
            $Various = $this->Lang->get('VARIOUS');
            $curText = C::get('CURRENCIES', $this->User->options_get('currency'));
            $icoURL  = C::ICON_URL;

            //Title
            echo "<h5>{$recipe['name']}</h5>",
            //Picture
                 "<img id='recipe_picture' src='/pics/recipes-{$recipe['pic']}' alt='{$recipe['name']}' />",
            //Properties
                 '<ul id="recipe_properties">',
                    '<li>';
                        $this->displayRating($recipe['rate']);
            echo    '</li>',
                    '<li>',
                        "<img src='$icoURL' class='icon25 guests' alt='{$Texts[4]}' />",
                        "<span>{$recipe['guest']} {$Texts[18]}</span>",
                    '</li>',
                    '<li>',
                        "<img src='$icoURL' class='icon25 preparation' alt='{$Texts[5]}' />",
                        "<span>{$recipe['prep']}{$Various[7]}</span>",
                        '<span>&nbsp;&nbsp;&nbsp;&nbsp;</span>',
                        "<img src='$icoURL' class='icon25 cooking' alt='$Texts[6]' />",
                        "<span>{$recipe['cook']}{$Various[7]}</span>",
                    '</li>',
                    '<li>',
                        "<img src='$icoURL' class='icon25 price' alt='$Texts[8]' />",
                        "<span>{$recipe['price']}$curText</span>",
                        "<span>{$Texts[9]}</span>",
                    '</li>',
                 '</ul>',
            //Ingredients
                 "<p id='recipe_ingredients' class='bold'>{$Titles[2]}</p>";
                 $this->displayIngredients($recipe['ing']);
            //Description
            echo "<p class='bold'>{$Titles[0]}</p>",
                 '<p>', nl2br($recipe['desc']), '</p>';

            //Success
            return true;
        }
        
        /**
         * Display index for recipes in memory
         * @param Int $page current page
         * @param Int $max number of recipes per page
         */
        public function displayIndex($page, $max)
        {
            if(!count($this->recipes)) return;
            $pageName = $this->Lang->get('MOBILE_PAGES', 1);

            //Build index
            for($i = 0, $imax = ceil(count($this->recipes) / $max); $i < $imax; $i++)
            {
                echo '<p class="index ', ($i == $page ? 'selected' : ''), '">';
                if($i == $page)
                    echo '<span>', $i + 1, '</span>';
                else
                    echo '<a href="', "/m/$pageName?sort={$this->sort}&p=$i", '">', $i + 1, '</a>';
                echo '</p>';
            }
        }
        
        /**
         * Display recipes in memory as a list
         * @param Int $page current page
         * @param Int $max number of recipes per page
         */
        public function displayList($page, $max)
        {
            if(count($this->recipes))
            {
                $pageName = $this->Lang->get('MOBILE_PAGES', 2);
                
                //List recipes
                echo '<ul>';
                foreach($this->recipes as $index => $recipe)
                {
                    if($index < $page * $max || $index > ($page + 1) * $max - 1) continue;
                    echo '<li>',
                            '<img class="recipe" src="/pics/recipes-', $recipe['pic'], '" alt="', $recipe['name'], '" />',
                            '<a href="/m/', "$pageName-{$recipe['id']}", '">', $recipe['name'], '</a>',
                         '</li>';
                }
                echo '</ul>';
            }
            else
                echo '<p class="center">', $this->Lang->get('RECIPES_ALERTS',0), '</p>';
        }
        
        /**
         * Display recipe ingredients as a list
         * @param Array $ingredients list of recipe ingredient quantities
         */
        private function displayIngredients($ingredients)
        {
            echo '<ul>';
            foreach($ingredients as $ingQty)
            {
                $Ing  = $this->IngDB->getIngredient($ingQty['i']);
                $unit = $this->Lang->get('UNITS_NAMES', $ingQty['u']);
                if($ingQty['u'] == C::UNIT_NONE) $unit = '';
                echo "<li>{$ingQty['q']}$unit - {$Ing->get('name')}</li>";
            }
            echo '</ul>';
        }

        /**
         * Display recipe rating as stars
         * @param Float $rating recipe rating
         */
        private function displayRating($rating)
        {
            $Keywords = $this->Lang->get('KEYWORDS');
            $icoURL   = C::ICON_URL;

            for($i = 0; $i < C::RECIPE_RATING_MAX; $i++)
            {
                if($i + 1 <= $rating)
                    echo "<img alt='{$Keywords[10]}' class='icon15 star' src='$icoURL' />";
                else
                    echo "<img alt='{$Keywords[10]}' class='icon15 star empty' src='$icoURL' />";
            }
        }
        
        /**********************************************************
        LOAD
        ***********************************************************/
        
        /**
         * Load user's favorites in memory
         * @param String $sort 
         */
        public function loadFav($sort)
        {
            $this->recipes = $this->RecLib->load($this->User->favorites_get());
            $this->sort = $sort;
            $this->sort();
        }

        /**********************************************************
        SEARCH
        ***********************************************************/

        /**
         * Search for recipes matching provided criteria, load them in memory and sort them
         * @param Array $criteria list of search criteria
         * @param String $sort (default to 'score')
         */
        public function search(array $criteria, $sort = 'score')
        {
            $results = $this->RecLib->search($criteria);
            $this->recipes = $this->RecLib->load($results);
            $this->sort = $sort;
            $this->sort();
        }
        
        /**********************************************************
        SORT
        ***********************************************************/
        
        /**
         * Sort recipes currently in memory
         */
        private function sort()
        {
            switch($this->sort)
            {
                default:
                case 'abc':
                    $sortFunc = 'sortABC';
                    break;
                case 'price':
                    $sortFunc = 'sortPrice';
                    break;
                case 'rating':
                    $sortFunc = 'sortRating';
                    break;
                case 'score':
                    return;
            }
            usort($this->recipes, array('RecipesLib', $sortFunc));
        }
    }
?>