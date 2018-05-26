<?php
    /*******************************************************
    Title: Globals
    Authors: Kookiiz Team
    Purpose: Store and export global-scope constants
    ********************************************************/

    //Represents a container for global-scope constants
    class C
    {
        /**********************************************************
        CONSTANTS
        ***********************************************************/

        //COMMENTS
        const COMMENT_LENGTH_MAX        = 1000; //Max chars for a comment
        const COMMENT_LENGTH_MIN        = 10;   //Min chars for a comment
        const COMMENT_TYPE_PRIVATE      = 0;    
        const COMMENT_TYPE_PUBLIC       = 1;
        const COMMENTS_COUNT_DEFAULT    = 15;   //Default number of comments per page

        //COOKIES
        const COOKIES_VALUE_COMMENT     = 1;    //Cookie values for several user contributions
        const COOKIES_VALUE_EVALUATION  = 2;
        const COOKIES_VALUE_PICTURE     = 10;
        const COOKIES_VALUE_RECIPE      = 5;
        const COOKIES_VALUE_TRANSLATE   = 5;

        //EMAIL
        const EMAIL_STATUS_EXISTING     = 1;    //Email statuses
        const EMAIL_STATUS_NOTVALID     = 2;
        const EMAIL_STATUS_VALID        = 0;

        //FACEBOOK
        const FACEBOOK_APP_ID   = 443919890091;   //Application ID for Facebook Connect
        const FACEBOOK_PERMS    = 'email,publish_stream,create_event';  //Permissions list

        //FRIDGE
        const FRIDGE_MAX            = 10;       //Max number of fridge ingredients
        const FRIDGE_QTY_DEFAULT    = 1;        //Default fridge quantity
        const FRIDGE_UNIT_DEFAULT   = 10;       //Default fridge unit

        //FRIENDS
        const FRIEND_STATUS_PENDING = 0;        //Friend statuses
        const FRIEND_STATUS_VALID   = 1;
        const FRIENDS_SEARCH_FBMAX  = 10;       //Max number of Facebook friends suggestions

        //GLOSSARY
        const GLOSSARY_DEFINITION_MIN   = 20;   //Minimum size of a glossary definition
        const GLOSSARY_KEYWORD_MIN      = 3;    //Minimum size of a glossary keyword

        //HTTP
        const HTTP_STATUS_OK = 200;

        //ICONS
        const ICON_URL = '/pictures/icons/nopicture.png';

        //INGREDIENTS
        const ING_GROUP_DEFAULT = 0;            //ID of default ingredient group
        const ING_QTY_CHARS     = 6;            //Number of chars for ingredient quantity inputs
        const ING_UNIT_DEFAULT  = 1;            //ID of default ingredient unit

        //INVITATIONS
        const INV_HOUR_DEFAULT      = 20;       //Default invitation time
        const INV_LOCATION_MAX      = 25;       //Max chars for invitation location field
        const INV_MINUTE_DEFAULT    = 30;
        const INV_STATUS_ACCEPT     = 2;        //Host/Guests statuses
        const INV_STATUS_DENY       = 3;
        const INV_STATUS_HOST       = 4;
        const INV_STATUS_NONE       = 0;
        const INV_STATUS_SENT       = 1;
        const INV_TEXT_MAX          = 1000;     //Max chars for invitation text
        const INV_TITLE_MAX         = 25;       //Max chars for invitation title

        //LANGUAGE
        const LANG_DEFAULT = 'fr';

        //LOGIN
        const LOGIN_STATUS_FAILURE  = 0;        //Login statuses
        const LOGIN_STATUS_SUCCESS  = 1;

        //MARKETS
        const MARKET_NAME_MAX   = 25;

        //MENU
        const MENU_DAYS_COUNT       = 3;        //Menu days settings
        const MENU_DAYS_FUTURE      = 14;
        const MENU_DAYS_MAX         = 28;
        const MENU_DAYS_PAST        = 14;
        const MENU_GUESTS_DEFAULT   = 2;        //Guests settings
        const MENU_GUESTS_MAX       = 10;
        const MENU_GUESTS_MIN       = 1;
        const MENU_MEALS_COUNT      = 3;        //Meals settings
        const MENU_QUICKMEALS_MAX   = 4;        //Max number of quick meals per day
        
        //MOBILE
        const MOBILE_PAGE_DEFAULT    = 1;        //Default mobile page
        const MOBILE_RECIPES_PERPAGE = 20;       //Max number of recipes per page on mobile platform
        
        //MYSQL
        const MYSQL_MIN_WORD        = 4;        //Minimum length of a word to be taken into account for full-text searches (CANNOT BE CHANGED!)

        //NETWORKS
        const NETWORK_STATUS_AUTHREQ    = 0;    //Social network statuses
        const NETWORK_STATUS_FAILURE    = 1;
        const NETWORK_STATUS_PENDING    = 2;
        const NETWORK_STATUS_SUCCESS    = 3;

        //NUTRITION
        const NUT_ACTIVITY_FACTOR_MAX   = 1.3;  //Multiplication factor to take user's activity into account
        const NUT_ACTIVITY_FACTOR_MIN   = 1.0;  //during nutrition needs computations
        const NUT_FIBRE_PERKCAL         = 10;   //Amount of fibre (g) per kcal (below 18 years old)
        const NUT_PROT_PERWEIGHT        = 0.9;  //Amount of proteins (g) per body weight kg

        //PARTNERS
        const PARTNER_DEFAULT = 3;              //ID of the default partner (Kookiiz Team)

        //QUICK MEALS
        const QM_MODE_INGREDIENTS   = 0;        //Quick meal modes
        const QM_MODE_NUTRITION     = 1;
        const QM_NAME_MAX           = 40;       //Quick meal name chars length
        const QM_NAME_MIN           = 5;

        //RECIPES
        const RECIPE_AUTHOR_DEFAULT     = 3;    //Default author for recipes
        const RECIPE_AUTHORNAME_DEFAULT = 'Kookiiz Team';
        const RECIPE_CHEAP_THRESHOLD    = 10;	//Cost per person in CHF the recipe must not exceed to be considered cheap
        const RECIPE_DESCRIPTION_MAX    = 1000;
        const RECIPE_DESCRIPTION_MIN    = 10;
        const RECIPE_CATEGORY_DEFAULT   = 1;	//Default recipe category
        const RECIPE_CATEGORY_DESSERT   = 3;	//Main recipe categories
        const RECIPE_CATEGORY_MAIN      = 1;
        const RECIPE_CATEGORY_STARTER   = 2;
        const RECIPE_EASY_THRESHOLD     = 0;	//Max difficulty level for a recipe to be considered easy
        const RECIPE_GUESTS_DEFAULT     = 1;
        const RECIPE_GUESTS_MAX         = 10;
        const RECIPE_GUESTS_MIN         = 1;
        const RECIPE_HEALTHY_THRESHOLD  = 5;	//Min healthy score for a recipe to be considered health
        const RECIPE_ING_MIN            = 2;
        const RECIPE_LEVEL_DEFAULT      = 0;
        const RECIPE_ORIGIN_DEFAULT     = 1;
        const RECIPE_PIC_DEFAULT        = 'nopicture.png';
        const RECIPE_QUICK_THRESHOLD    = 30;	//Max number of minutes for the recipe to be quick
        const RECIPE_RATING_MAX         = 5;    //Max number of stars a recipe can have
        const RECIPE_RATING_MIN         = 0;    //Min number of stars a recipe can have
        const RECIPE_STEPS_MAX          = 20;   //Max number of description steps
        const RECIPE_SUCCESS_THRESHOLD  = 4;	//Min rating of a recipe to be considered a "success"
        const RECIPE_THUMB_DEFAULT      = 'nopicture_small.png';
        const RECIPE_TITLE_MAX          = 80;

        //REGEXP
        const REGEXP_NAME_PATTERN = '^[ÀÁÂÃÄÅÇÈÉÊËÌÍÎÏÒÓÔÕÖÙÚÛÜÝàáâãäåçèéêëìíîïðòóôõöùúûüýÿa-zA-Z ]+$';

        //SESSION
        const SESSION_TIMEOUT = 180;            //Delay (in s) between two session updates

        //SHOPPING
        const SHOPPING_SEND_EMAIL       = 0;    //Shopping sending modes
        const SHOPPING_SEND_FRIEND      = 1;
        const SHOPPING_STATUS_EVENING   = 2;    //Shopping statuses
        const SHOPPING_STATUS_MORNING   = 1;
        const SHOPPING_STATUS_NONE      = 0;

        //TASTES
        const TASTE_LIKE    = 1;
        const TASTE_DISLIKE = 0;

        //THEME
        const THEME         = 'original';       //Current Kookiiz theme
        const THEME_DEFAULT = 'original';       //Default Kookiiz theme

        //UNITS
        const UNIT_GRAMS        = 1;            //Some unit ID shortcuts
        const UNIT_KILOGRAMS    = 0;
        const UNIT_MILLILITERS  = 6;
        const UNIT_NONE         = 10;
        const UNIT_PINCH        = 9;

        //USER
        const USER_ACTIVITY_MAX     = 8;        //Maximum activity score on health profile
        const USER_ACTIVITY_MIN     = -10;      //Minimum activity score on health profile
        const USER_GENDER_FEMALE    = 0;
        const USER_GENDER_MALE      = 1;
        const USER_FIRSTNAME_MAX    = 40;
        const USER_FIRSTNAME_MIN    = 3;
        const USER_LASTNAME_MAX     = 40;
        const USER_LASTNAME_MIN     = 3;
        const USER_MARKETS_MAX      = 10;       //Maximum number of market selections a user can make
        const USER_PASSWORD_MAX     = 50;		//Maximum chars for user's password
        const USER_PASSWORD_MIN     = 6;		//Minimum chars for user's password
        const USER_PIC_DEFAULT      = 'nopicture.png';
        const USER_SPORTS_MAX       = 10;       //Maximum number of sports a user can specify
        const USER_TASTES_MAX       = 20;
        const USER_THUMB_DEFAULT    = 'nopicture_small.png';

        //VEGGIE
        const VEGGIE_NONE       = 0;            //Veggie statuses
        const VEGGIE_REGULAR    = 1;
        const VEGGIE_STRICT     = 2;

        /**********************************************************
        CONSTANT ARRAYS
        ***********************************************************/

        //ALLERGIES
        private static $ALLERGIES           = array('gluten', 'milk', 'egg', 'fish', 'crust', 'soy', 'nuts', 'sesame', 'celery');
        private static $ALLERGIES_DEFAULTS  = array(0, 0, 0, 0, 0, 0, 0, 0, 0);

        //COOKIES
        //Values of grade cookies
        private static $COOKIES_VALUES = array(50, 10, 5, 1);

        //CURRENCIES
        private static $CURRENCIES          = array('CHF', 'EUR', '$');
        private static $CURRENCIES_VALUES   = array(1, 0.66, 0.94);

        //DEMO
        private static $DEMO_TYPES  = array('menu', 'health', 'tips', 'share');
        //For each demo type, list of video indexes referring to the "VIDEOS_IDS" array
        private static $DEMO_VIDEOS = array(array(0, 1, 2), array(3, 4), array(5), array(6));

        //EVENTS
        private static $EVENTS_TYPES = array('share_recipe', 'add_recipe', 'share_status', 'rate_recipe',
                                                'new_member', 'comment_recipe', 'comment_article');

        //INGREDIENTS
        //Conversion from category to group
        private static $ING_CATS_TOGROUP    = array(0, 1, 2, 3, 4, 5, 6, 5, 1, 8, 5, 9, 10, 5,
                                                    11, 12, 13, 5, 14, 15, 7, 0, 0, 0, 0, 16);
        //Vegan ingredient categories
        private static $ING_CATS_VEGAN      = array(2, 4, 8, 9, 11, 12, 14, 16, 18, 19, 20);
        //Veggie ingredient categories
        private static $ING_CATS_VEGGIE     = array(1, 2, 4, 8, 9, 11, 12, 14, 16, 18, 19, 20);
        //Type of each ingredient property
        private static $ING_DATATYPES       = array('i', 's', 's', 'i', 's', 'i', 'i', 'f', 'i', 'i');
        //Ingredient groups short names
        private static $ING_GROUPS          = array('others', 'dairyandeggs', 'herbs', '', 'oils',
                                                    'meats',  '', 'cerealsandpasta', 'fruitsandjuices', 'vegetables',
                                                    'nutsandseeds', 'drinks', 'fishandshellfish', 'legumes', 'bakery',
                                                    '', 'snacks');
        //Default position of each ingredient group on the shopping list, ordered by group ID
        private static $ING_GROUPS_ORDER    = array(12, 8, 16, 11, 14, 0, 13, 5, 3, 2, 6, 9, 1, 4, 7, 15, 10);
        //Names of ingredient fields in database
        private static $ING_PROPERTIES      = array('id', 'name', 'tags', 'cat', 'pic', 'unit', 'wpu', 'price', 'exp', 'prob');
        //Tells if an ingredient property is language dependent
        private static $ING_PROPERTIES_LANG = array(0, 1, 1, 0, 0, 0, 0, 0, 0, 0);

        //LANGUAGE
        private static $LANGUAGES       = array('fr', 'en');
        private static $LANGUAGES_FULL  = array('fr_FR', 'en_US');
        private static $LANGUAGES_NAMES = array('Français', 'English');

        //MENU
        //IDs of nutrition values to display on the menu
        private static $MENU_NUTRITION_VALUES = array(0, 1, 2, 3, 4);
        
        //MOBILE
        //Whether a mobile page should appear in the top select menu
        private static $MOBILE_PAGE_LISTED = array(1, 1, 0, 1);

        //NUTRITION
        //Fraction of kcal needs that each nutriment should contribute
        private static $NUT_KCAL_FRACTION   = array(100, 0.55, 0.3, 0.15, 0, 0, 0, 0, 0, 0,
                                                    0, 0, 0, 0, 0, 0, 0, 0, 0, 0,
                                                    0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0);
        //Kcal contribution of each nutriment per gram
        private static $NUT_KCAL_VALUE      = array(1, 4, 9, 4, 0, 0, 0, 0, 0, 0,
                                                    0, 0, 0, 0, 0, 0, 0, 0, 0, 0,
                                                    0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0);
        //Reference daily values for each nutriment in its default unit
        private static $NUT_REFERENCES      = array(
                                                //Woman
                                                array(2000, 270, 65, 50, 30, 3000, 800, 1, 1.2, 13,
                                                        6, 1.2, 400, 3, 100, 5, 60, 1000, 1.25, 15,
                                                        300, 700, 2000, 50, 550, 7, 270, 400, 300, 1, 2000),
                                                //Man
                                                array(2500, 310, 80, 65, 30, 3000, 1000, 1.2, 1.4, 16,
                                                        6, 1.5, 400, 3, 100, 5, 70, 1000, 1.25, 10,
                                                        350, 700, 2000, 50, 550, 10, 310, 400, 700, 1, 2000),
                                                //Girl
                                                array(2000, 270, 65, 50, 20, 3000, 950, 1.05, 1.25, 14,
                                                        6, 1.3, 400, 3, 100, 5, 55, 1200, 1.25, 15,
                                                        330, 1250, 1950, 46.25, 550, 7, 270, 400, 700, 1, 2000),
                                                //Boy
                                                array(2000, 270, 65, 50, 20, 3000, 1100, 1.35, 1.55, 17.5,
                                                        6, 1.5, 400, 3, 100, 5, 60, 1200, 1.25, 12,
                                                        355, 1250, 1950, 46.25, 550, 9.75, 270, 400, 700, 1, 2000)
                                                );
        //Default units for each nutriment
        private static $NUT_UNITS           = array('kcal', 'g', 'g', 'g', 'g', 'ug', 'ug', 'mg', 'mg', 'NE',
                                                    'mg', 'mg', 'ug', 'ug', 'mg', 'ug', 'ug', 'mg', 'mg', 'mg',
                                                    'mg', 'mg', 'mg', 'ug', 'mg', 'mg', 'g', 'mg', 'mg', 'g', 'cl');
        //Short name of each nutritional value
        private static $NUT_VALUES          = array('kcal', 'carb', 'fat', 'prot', 'fibre', 'proa', 'vita', 'vitb1', 'vitb2', 'vitb3',
                                                    'vitb5', 'vitb6', 'vitb9', 'vitb12', 'vitc', 'vitd', 'vitk', 'ca', 'cu', 'fe',
                                                    'mg', 'p', 'k', 'se', 'na', 'zn', 'sta', 'caf', 'chol', 'lact', 'water');

        //PANELS
        //Default panels order (left column first)
        private static $PANELS_ORDER_DEFAULT    = array(0, 1, 6, 7, 9, 10, 11, 14, 3, 18, 2, 4, 5, 8, 12, 13, 15, 16, 17);
         //Default panels sides (0 = left, 1 = right)
        private static $PANELS_SIDES_DEFAULT    = array(0, 0, 0, 1, 0, 0, 0, 0, 1, 1, 1, 1, 1, 1, 1, 0, 1, 0, 1);
        //Default panels states (0 = closed, 1 = open)
        private static $PANELS_STATES_DEFAULT   = array(1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1);

        //RECIPES
        private static $RECIPES_CRITERIA = array('easy', 'healthy', 'cheap', 'quick', 'success', 'season', 'veggie');

        //SHOPPING
        private static $SHOPPING_SKIP = array(126, 1326);

        //SOURCES
        private static $SOURCES_LINKS = array('http://www.santecanada.gc.ca/fcen', 'http://www.sge-ssn.ch');

        //SPORTS
        //Kcal per kg of body weight and hour of sport
        private static $SPORTS_VALUES       = array(8, 8, 8, 8, 8, 8, 8, 8, 8, 8, 8, 8);
        private static $SPORTS_FREQ_VALUES  = array(0.15, 0.3, 0.45, 0.6, 0.70, 0.85, 1, 1.15, 1.3, 1.45, 1.6);

        //STATUS
        private static $STATUS_HAS_CONTENT      = array(1, 0, 1, 1, 1);
        private static $STATUS_HAS_MESSAGE      = array(1, 1, 1, 1, 0);
        private static $STATUS_REQUIRE_CONTENT  = array(0, 0, 1, 1, 0);

        //TABS
        private static $TABS = array('main', 'health', 'share', 'profile',
                                     'recipe_full', 'recipe_form', 'shopping_finish', 'article_display',
                                     'admin', 'recipe_translate', 'error_404');
        //Is tab related to a content ID ?
        private static $TABS_HAS_CONTENT    = array(0, 0, 0, 0, 1, 0, 1, 1, 0, 1, 0);
        //Should menu be shown on specific tab ?
        private static $TABS_MENU_SHOW      = array(1, 1, 0, 0, 0, 0, 1, 0, 0, 0, 0);
        //Is tab temporary or not ?
        private static $TABS_TEMP           = array(0, 0, 0, 0, 1, 1 ,1, 1, 0, 1, 1);
        
        //UNITS
        //Systems
        private static $UNITS_ORDERS    = array(
                                                    'imperial'  => array(12, 17, 11, 13, 16, 15, 14, 7, 8, 9, 10),
                                                    'metric'    => array(0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 17, 10)
                                                );
        private static $UNITS_SYSTEMS   = array('metric', 'imperial');

        //USER
        //Activity categories
        private static $USER_ACTIVITY           = array('occupation', 'occupation_rate', 'transport', 'walk', 'lazy', 'laying');
        //Default index of activity selectors
        private static $USER_ACTIVITY_DEFAULTS  = array(1, 2, 1, 0, 0, 0);
        //Values of activity choices
        private static $USER_ACTIVITY_VALUES    = array(array(0, 1, 2, 2), array(0, 1, 2), array(0, 0, 2, 2),
                                                        array(0, 1, 2), array(0, -2, -4), array(-2, -4, -6));
        //Anatomical parameters
        private static $USER_ANATOMY            = array('height', 'weight', 'gender', 'birth');
        //Default value of anatomy selectors
        private static $USER_ANATOMY_DEFAULTS   = array(180, 75, self::USER_GENDER_FEMALE, 1980);
        //Options keywords
        private static $USER_OPTIONS            = array('currency', 'units', 'email_friendship', 'email_invitation', 'email_recipe',
                                                        'fast_mode', 'panel_articles', 'panel_fridge', 'panel_invitations',
                                                        'panel_nutrition', 'panel_recipes', 'panel_ww');
        private static $USER_OPTIONS_DEFAULTS   = array(0, 0, 1, 1, 1, 0, 1, 1, 1, 1, 1, 1);

        //VIDEOS
        //Vimeo video IDs
        private static $VIDEOS_IDS = array(17320701, 17320853, 17321207, 17321338, 17321555, 17321687, 17321771);

        /**********************************************************
        CONSTRUCTOR
        ***********************************************************/

        //Class constructor (PRIVATE!)
        //-> (void)
        private function __construct()
        {
        }

        /**********************************************************
        GET
        ***********************************************************/

        //Get constant array or a specific key
        //@array (string):  array name
        //@key (string):    array key
        //->value (mixed): array, array value or null
        public static function get($array, $key = null)
        {
            if(isset(self::${$array}))
            {
                if(is_null($key)) 
                    return self::${$array};
                else
                {
                    if(isset(self::${$array}[$key]))    
                        return self::${$array}[$key];
                    else                                
                        return null;
                }
            }
            else 
                return null;
        }

        /**********************************************************
        PRINT
        ***********************************************************/

        //Echoes a constant
        //@const (string): constant name
        //-> (void)
        public static function p($const)
        {
            $value = constant('self::' . $const);
            echo is_null($value) ? 'null' : $value;
        }
    }
?>