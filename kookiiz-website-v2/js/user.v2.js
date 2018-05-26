/*******************************************************
Title: User
Authors: Kookiiz Team
Purpose: Define a user profile object (public and private)
********************************************************/

//Represents a public user profile
var UserPublic = Class.create(Observable,
{
    object_name: 'user_public',

    /*******************************************************
    CONSTRUCTOR
    ********************************************************/

    //Class constructor
    //#profile (object): user profile structure
    //-> (void)
	initialize: function(profile)
    {
        //Attributes
        this.id         = 0;            //user unique ID
        this.fb_id      = 0;            //Facebook ID (0 if none)
        this.tw_id      = 0;            //Twitter ID (0 if none)
        this.firstname  = '';           //user's firstname
        this.lastname   = '';           //user's lastname
        this.name       = '';           //user's full name
        this.grade      = 0;            //cookies count
        this.pic_id     = 0;            //avatar ID (0 if none)
        this.lang       = LANG_DEFAULT; //language identifier

        Object.extend(this, profile || {});
        this.name = this.firstname + ' ' + this.lastname;
    },

    /*******************************************************
    GETTERS
    ********************************************************/

    //Return user grade
    //->grade (int): user grade
    getGrade: function()
    {
        return this.grade;
    },

    //Return user's FB ID
    //->fb_id (int): user FB ID
    getFBID: function()
    {
        return this.fb_id;
    },
   
    //Return user's unique ID
    //->id (int): unique user ID
    getID: function()
    {
        return this.id;
    },
    
    //Return user's language
    //->lang (string): language identifier
    getLang: function()
    {
        return this.lang;
    },

    //Return user's full name
    //->name (string): user's full name
    getName: function()
    {
        return this.name;
    },
    
    //Return current user pic ID
    //->pic_id (int): user's pic ID
	getPic: function()
    {
        return this.pic_id;
    }
});

//Represents a private/full user profile
var UserPrivate = Class.create(UserPublic,
{
    object_name: 'user_private',

    /*******************************************************
    CONSTRUCTOR
    ********************************************************/

    //Class constructor
    //#$super (function): reference to superclass constructor
    //#profile (object): user profile data
    //-> (void)
	initialize: function($super, profile)
    {
        //Attributes
        this.email = '';

        //Call public profile constructor
        $super(profile);

        //Activity
        this.activity = {};
        USER_ACTIVITY.each(function(name, index)
        {
            this[name] = USER_ACTIVITY_DEFAULTS[index];
        }, this.activity);

        //Allergies
        this.allergies = {};
        ALLERGIES.each(function(name, index)
        {
            this[name] = ALLERGIES_DEFAULTS[index];
        }, this.allergies);

        //Anatomy
        this.anatomy = {};
        USER_ANATOMY.each(function(name, index)
        {
            this[name] = USER_ANATOMY_DEFAULTS[index];
        }, this.anatomy);

        //Breakfast
        this.breakfast = new IngredientsCollection();
        this.breakfast.observe('updated', this.breakfast_observer.bind(this));

        //Favorites
        this.favorites = [];

        //Fridge
        this.fridge = new IngredientsCollection();
        this.fridge.observe('updated', this.fridge_observer.bind(this));

        //Friends
        this.friends = [];

        //Markets
        this.markets = [];

        //Menu
        this.menu = new Menu();
        this.menu.observe('alert', this.menu_observer.bind(this));
        this.menu.observe('saved', this.menu_observer.bind(this));
        this.menu.observe('updated', this.menu_observer.bind(this));

        //Needs
        this.needs = NUT_REFERENCES[0];

        //Options
        this.options = {};
        USER_OPTIONS.each(function(name, index)
        {
            this[name] = USER_OPTIONS_DEFAULTS[index];
        }, this.options);

        //Panels
        this.panels =
        {
            'ids':      [],
            'sides':    [],
            'status':   []
        };

        //Quick meals
        this.quickmeals = [];

        //Sports
        this.sports = [];

        //Tastes
        this.tastes = [];

        //Timestamps
        this.times =
        {
            'activity':     0,
            'allergies':    0,
            'anatomy':      0,
            'breakfast':    0,
            'favorites':    0,
            'fridge':       0,
            'friends':      0,
            'markets':      0,
            'menu':         0,
            'options':      0,
            'panels':       0,
            'personal':     0,
            'quickmeals':   0,
            'sports':       0,
            'tastes':       0
        }
    },

    /*******************************************************
    ACTIVITY
    ********************************************************/

    //Export user's activities in compact format
    //->activity (array): list of activity values indexed by ID
	activity_export: function()
    {
        var activity = [];
        for(var i = 0, imax = USER_ACTIVITY.length; i < imax; i++)
        {
            var name = USER_ACTIVITY[i];
            activity.push(this.activity[name]);
        }
        return activity;
    },

    //Return user's activities
    //->activity (object): activity values indexed by name
	activity_get: function()
    {
        return this.activity;
    },

    //Import compact activity data from server
    //#activity (array):    list of activity values indexed by ID
    //#time (int):          activity data timestamp
    //-> (void)
	activity_import: function(activity, time)
    {
        for(var i = 0, imax = USER_ACTIVITY.length; i < imax; i++)
        {
            if(typeof(activity[i]) != 'undefined')
            {
                var name = USER_ACTIVITY[i];
                this.activity[name] = parseInt(activity[i]);
            }
        }
        this.activity_updated(time);
    },

    //Set activity value
    //#name (string):   activity short name
    //#value (int):     activity value
    //-> (void)
	activity_set: function(name, value)
    {
        if(typeof(this.activity[name]) != 'undefined')
        {
            this.activity[name] = parseInt(value);
            this.activity_updated();
        }
    },

    //Called when activity data has been updated
    //#time (int): activity update timestamp
    //-> (void)
    activity_updated: function(time)
    {
        this.needs_update();
        this.profile_updated('activity', time);
        this.fire('updated', {'prop': 'activity'});
    },

    /*******************************************************
    ALLERGIES
    ********************************************************/
   
    //Set value of a specific allergy
    //#name (string):   allergy name
    //#value (bool):    true if user is allergic
    //-> (void)
	allergy_set: function(name, value)
    {
        if(typeof(this.allergies[name]) != 'undefined')
        {
            this.allergies[name] = parseInt(value);
            this.allergies_updated();
        }
    },

    //Export allergy values
    //->allergies (array): allergy values indexed by ID
	allergies_export: function()
    {
        var allergies = [];
        for(var i = 0, imax = ALLERGIES.length; i < imax; i++)
        {
            var name = ALLERGIES[i];
            allergies.push(this.allergies[name]);
        }
        return allergies;
    },

    //Get user's allergies
    //->allergies (object): list of allergies indexed by name
	allergies_get: function()
    {
        return this.allergies;
    },

    //Import allergy values from server
    //#allergies (array): allergy values indexed by ID
    //-> (void)
	allergies_import: function(allergies, time)
    {
        this.allergies = {};
        for(var i = 0, imax = ALLERGIES.length; i < imax; i++)
        {
            if(typeof(allergies[i]) != 'undefined')
            {
                var name = ALLERGIES[i];
                this.allergies[name] = parseInt(allergies[i]);
            }
        }
        this.allergies_updated(time);
    },

    //Called when allergy data has been updated
    //#time (int): allergy update timestamp
    //-> (void)
    allergies_updated: function(time)
    {
        this.profile_updated('allergies', time);
        this.fire('updated', {'prop': 'allergies'});
    },

    /*******************************************************
    ANATOMY
    ********************************************************/

    //Export user's anatomy values in compact format
    //->anatomy (array): list of anatomy values indexed by ID
	anatomy_export: function()
    {
        var anatomy = [];
        for(var i = 0, imax = USER_ANATOMY.length; i < imax; i++)
        {
            var name = USER_ANATOMY[i];
            anatomy.push(this.anatomy[name]);
        }
        return anatomy;
    },

    //Return user's anatomy values
    //->anatomy (object): list of anatomy values indexed by name
	anatomy_get: function()
    {
        return this.anatomy;
    },

    //Import compact anatomy data from server
    //#anatomy (array): list of anatomy values indexed by ID
    //#time (int):      anatomy data timestamp
    //-> (void)
	anatomy_import: function(anatomy, time)
    {
        var name;
        for(var i = 0, imax = USER_ANATOMY.length; i < imax; i++)
        {
            if(typeof(anatomy[i]) != 'undefined')
            {
                name                = USER_ANATOMY[i];
                this.anatomy[name]  = parseInt(anatomy[i]);
            }
        }
        this.anatomy_updated(time);
    },

    //Set a specific anatomy value
    //#name (string):   anatomy value short name
    //#value (int):     anatomy value
    //-> (void)
	anatomy_set: function(name, value)
    {
        if(typeof(this.anatomy[name]) != 'undefined')
        {
            this.anatomy[name] = parseInt(value);
            this.anatomy_updated();
        }
    },

    //Called when anatomy data has been updated
    //#time (int): anatomy update timestamp
    //-> (void)
    anatomy_updated: function(time)
    {
        this.needs_update();
        this.profile_updated('anatomy', time);
        this.fire('updated', {'prop': 'anatomy'});
    },

    /*******************************************************
    BREAKFAST
    ********************************************************/

    //Export user's breakfast in compact format
    //->breakfast (object): arrays of breakfast properties
	breakfast_export: function()
    {
        return this.breakfast.export_content();
    },

    //Get current user breakfast
    //->breakfast (object): ingredients collection
	breakfast_get: function()
    {
        return this.breakfast;
    },

    //Import breakfast data
    //#breakfast (array):   list of compact ingredient quantities
    //#time (int):          breakfast data timestamp
    //-> (void)
    breakfast_import: function(breakfast, time)
    {
        this.breakfast.import_content(breakfast);
        this.breakfast_updated(time);
    },

    //Observe breakfast content events
    //#event (event): event object
    //-> (void)
    breakfast_observer: function(event)
    {
        this.breakfast_updated();
    },

    //Called when breakfast content has been updated
    //#time (int): breakfast update timestamp
    //-> (void)
    breakfast_updated: function(time)
    {
        this.profile_updated('breakfast', time);
        this.fire('updated', {'prop': 'breakfast'});
    },

    /*******************************************************
    ERRORS
    ********************************************************/

    //Fire an error
    //#type (string):   error type
    //#code (int):      error code
    //-> (void)
    error: function(type, code)
    {
        this.fire('error',
        {
            'mode':     'code',
            'type':     type,
            'code':     code
        });
    },
    
    /*******************************************************
    FAVORITES
    ********************************************************/

    //Add a recipe to user's favorites
    //#recipe_id (int): ID of the recipe to add
    //#silent (bool):   if true no confirmation is displayed
    //-> (void)
    favorites_add: function(recipe_id, silent)
    {
        //Check if this recipe has not been saved yet
        if(this.favorites.indexOf(recipe_id) < 0)
        {
            this.favorites.push(recipe_id);
            this.favorites_update();
            this.profile_save(['favorites'],
            {
                'message':  RECIPES_ALERTS[4],
                'silent':   silent
            });

            //Fetch recipe if it's missing locally
            if(!Recipes.get(recipe_id))
                Recipes.fetch(recipe_id, this.favorites_update.bind(this));
        }
        else 
            Kookiiz.popup.alert({'text': FAVORITES_ALERTS[0]});
    },

    //Delete recipes from favorites panel
    //#recipe_id (int): ID of the recipe to delete
    //-> (void)
    favorites_delete: function(recipe_id)
    {
        var index = this.favorites.indexOf(recipe_id);
        if(index >= 0) 
        {
            this.favorites.splice(index, 1);
            this.favorites_update();
            this.profile_save(['favorites']);
        }
    },

    //Export favorite recipes list
    //->favorites (array): list of favorite recipe IDs
    favorites_export: function()
    {
        return this.favorites.slice();
    },

    //Return favorite recipes list
    //->favorites (array): list of favorite recipe IDs
    favorites_get: function()
    {
        return this.favorites.slice();
    },

    //Import favorites data
    //#favorites (array):   list of favorite recipes IDs
    //#time (int):          favorites data timestamp
    //-> (void)
    favorites_import: function(favorites, time)
    {
        this.favorites = favorites.map(function(id){return parseInt(id);});
        this.favorites_update(time);
    },

    //Called after an update of the favorite recipes
    //#time (int): favorites update timestamp
    //-> (void)
    favorites_update: function(time)
    {
        this.profile_updated('favorites', time);
        this.fire('updated', {'prop': 'favorites'});
    },

    /*******************************************************
    FRIDGE
    ********************************************************/

    //Export compact fridge content
    //->fridge (array): list of fridge ingredient quantities
    fridge_export: function()
    {
        return this.fridge.export_content();
    },

    //Import fridge content
    //#fridge (object): fridge data
    //#time (int):      fridge data timestamp
    //-> (void)
    fridge_import: function(fridge, time)
    {
        this.fridge.import_content(fridge);
        this.fridge_update(time);
    },

    //Called when a fridge event is triggered
    //#event (event): custom fridge event
    //-> (void)
    fridge_observer: function(event)
    {
        var data    = $H(event.memo);
        var action  = data.get('action');

        //Ignore import event
        if(action != 'import')
        {
            //Event bubbling
            this.fridge_update();
            //Save fridge content
            this.profile_save(['fridge']);
        }
    },

    //Called when fridge content has been updated
    //#time (int): fridge update timestamp (optional)
    fridge_update: function(time)
    {
        //Compute "stocked" param for all shopping lists
        this.menu.stockUpdate();

        //Trigger profile update and bubble event
        this.profile_updated('fridge', time);
        this.fire('updated', {'prop': 'fridge'});
    },

    /*******************************************************
    FRIENDS
    ********************************************************/

    //Return friend with provided ID or an array with all friends
    //#friend_id (int): ID of the friend to return (optional)
    //-> friend (object/array): friend object/list of friend objects
    friends_get: function(friend_id)
    {
        if(friend_id && friend_id > 0)
        {
            for(var i = 0, imax = this.friends.length; i < imax; i++)
            {
                if(this.friends[i].id == friend_id)
                    return this.friends[i];
            }
            return null;
        }
        else
            return this.friends.slice();
    },

    //Import friends data from server
    //#friend (array): list of compact friend objects
    //-> (void)
    friends_import: function(friends)
    {
        this.friends = [];
        var user_id, status;
        for(var i = 0, imax = friends.length; i < imax; i++)
        {
            user_id = parseInt(friends[i].i);
            status  = parseInt(friends[i].s);
            this.friends.push(new Friend(user_id, status));
        }
        this.friends_update();
    },

    //Called when friends list has been updated
    //-> (void)
    friends_update: function()
    {
        this.fire('updated', {'prop': 'friends'});
    },

    /*******************************************************
    GRADE
    ********************************************************/

    //Update user's grade value
    //#grade (int): new grade value
    //-> (void)
	grade_set: function(grade)
    {
        this.grade = parseInt(grade);
        this.personal_update(null, 'grade');
    },
    
    /*******************************************************
    LOGIN
    ********************************************************/
   
    //Check if user is logged
    //->logged (bool): true if user is logged
    isLogged: function()
    {
        return this.id > 0;
    },

    /*******************************************************
    MARKETS
    ********************************************************/

    //Save a new market configuration
    //#id (int):        unique market ID
    //#name (string):   market name
    //#order (array):   shopping categories order
    //#time (int):      new markets timestamp
    //-> (void)
    markets_add: function(id, name, order, time)
    {
        //Save market
        var existing = this.markets.find(function(market){return market.id == id;});
        if(!existing)
        {
            this.markets.push(
            {
                'id':       id,
                'name':     name,
                'order':    order,
                'selected': false
            });
            this.markets_update(time);
        }
    },

    //Remove market with specified ID
    //#market_id (int): unique market ID
    //-> (void)
    markets_delete: function(market_id, time)
    {
        for(var i = 0, imax = this.markets.length; i < imax; i++)
        {
            if(this.markets[i].id == market_id)
            {
                this.markets.splice(i, 1);
                break;
            }
        }
        this.markets_update(time);
    },

    //Export markets data
    //->markets (array): list of market configurations
    markets_export: function()
    {
        var markets = [];
        for(var i = 0, imax = this.markets.length; i < imax; i++)
        {
            markets.push(
            {
                'id':       this.markets[i].id,
                'name':     this.markets[i].name,
                'order':    this.markets[i].order,
                'selected': this.markets[i].selected ? 1 : 0
            });
        }
        return markets;
    },

    //Return current market configuration list
    //->markets (array): list of market configurations
    markets_get: function()
    {
        return this.markets.slice();
    },

    //Import markets data from server
    //#markets (array): list of market configurations
    //#time (int):      markets data timestamp
    //-> (void)
    markets_import: function(markets, time)
    {
        //Store markets
        this.markets = [];
        var selected, selected_market = 0;
        for(var i = 0, imax = markets.length; i < imax; i++)
        {
            selected = parseInt(markets[i].selected) && !selected_market;
            this.markets.push(
            {
                'id':       markets[i].id,
                'name':     markets[i].name,
                'order':    markets[i].order.parse('int'),
                'selected': selected
            });
            if(selected) 
                selected_market = markets[i].id;
        }
        this.markets_update(time);
    },

    //Get shopping order for provided market or currently selected one
    //#market_id (int): ID of the market (optional)
    //->order (array):  shopping categories order
    markets_order_get: function(market_id)
    {
        var market = null;
        if(market_id)
            market = this.markets.find(function(market){return market.id == market_id;});
        else            
            market = this.markets.find(function(market){return market.selected;});

        if(market)
            return market.order;
        else
            return [];
    },

    //Save current shopping configuration in provided market
    //#market_id (int): ID of the market
    //#order (array):   categories order
    //-> (void)
    markets_order_save: function(market_id, order, time)
    {
        var market = this.markets.find(function(market){return market.id == market_id;});
        if(market)
        {
            market.order = order;
            this.markets_update(time);
        }
    },

    //Account for market selection
    //#market_id (int): market that was just selected
    //-> (void)
    markets_select: function(market_id, time)
    {
        for(var i = 0, imax = this.markets.length; i < imax; i++)
        {
            if(this.markets[i].id == market_id)
                this.markets[i].selected = true;
            else
                this.markets[i].selected = false;
        }
        this.markets_update(time);
    },

    //Called when markets have been updated
    //#time (int): markets update timestamp
    //-> (void)
    markets_update: function(time)
    {
        this.profile_updated('markets', time);
        this.fire('updated', {'prop': 'markets'});
    },

    /*******************************************************
    MENU
    ********************************************************/

    //Export menu content
    //->menu (object): compact menu data
    menu_export: function()
    {
        var menu =
        {
            'plan': this.menu.export_content(),
            'date': this.menu.date_get()
        };
        return menu;
    },

    //Import menu data
    //#menu (object):   compact menu data
    //#time (int):      menu data timestamp
    //-> (void)
    menu_import: function(menu, time)
    {
        this.menu.import_content(menu);
        this.menu_updated('import', time);
    },

    //Observe menu events
    //#event (event): event object
    menu_observer: function(event)
    {
        var mode = event.eventName,
            data = $H(event.memo);

        switch(mode)
        {
            case 'alert':
                var code = data.get('code');
                Kookiiz.popup.alert({'text': MENU_ALERTS[code]});
                break;

            case 'saved':
                this.menu_saved();
                break;

            case 'updated':
                var type = data.get('type');
                if(type != 'import')
                    this.menu_updated(type);
                break;
        }
    },
    
    //Called when menu data has been saved
    //-> (void)
    menu_saved: function()
    {
        this.fire('saved', {'prop': 'menu'});
    },

    //Called when menu data has been updated
    //#type (string):   type of update
    //#time (int):      menu update timestamp (optional)
    //-> (void)
    menu_updated: function(type, time)
    {
        this.profile_updated('menu', time);
        this.fire('updated', {'prop': 'menu', 'type': type});
    },

    /*******************************************************
    NUTRITIONAL NEEDS
    ********************************************************/

    //Return current nutritional needs
    //->needs (array): list of needs for each nutritional value indexed by IDs
	needs_get: function()
    {
        return this.needs;
    },

    //Update user's nutritional needs
    //-> (void)
	needs_update: function()
    {
        //Retrieve user anatomy values
        var now    = new DateTime(),
            age    = now.year - this.anatomy.birth,
            gender = this.anatomy.gender,
            weight = this.anatomy.weight;

        //Init nutrition needs to default values depending on age and gender
        if(age > 18)
            this.needs = NUT_REFERENCES[gender].slice();
        else
            this.needs = NUT_REFERENCES[2 + gender].slice();

        //ENERGY NEEDS
        var kcal_index = NUT_VALUES.indexOf('kcal');

        //Physical component
        if(weight <= 50)
            this.needs[kcal_index] = gender == USER_GENDER_FEMALE ? 25 * weight : 29 * weight;
        else if(weight > 50 && weight <= 55)
            this.needs[kcal_index] = gender == USER_GENDER_FEMALE ? 23 * weight : 27 * weight;
        else if(weight > 55 && weight <= 60)
            this.needs[kcal_index] = gender == USER_GENDER_FEMALE ? 22 * weight : 26 * weight;
        else if(weight > 60 && weight <= 65)
            this.needs[kcal_index] = gender == USER_GENDER_FEMALE ? 21 * weight : 25 * weight;
        else if(weight > 65 && weight <= 70)
            this.needs[kcal_index] = gender == USER_GENDER_FEMALE ? 20 * weight : 24 * weight;
        else if(weight > 70 && weight <= 75)
            this.needs[kcal_index] = gender == USER_GENDER_FEMALE ? 19 * weight : 23 * weight;
        else if(weight > 75 && weight <= 80)
            this.needs[kcal_index] = gender == USER_GENDER_FEMALE ? 18 * weight : 22 * weight;
        else if(weight > 80 && weight <= 85)
            this.needs[kcal_index] = gender == USER_GENDER_FEMALE ? 18 * weight : 22 * weight;
        else if(weight > 85 && weight <= 90)
            this.needs[kcal_index] = gender == USER_GENDER_FEMALE ? 18 * weight : 22 * weight;
        else if(weight > 90)
            this.needs[kcal_index] = gender == USER_GENDER_FEMALE ? 17 * weight : 21 * weight;

        //Activity component
        var score = 0;
        USER_ACTIVITY.each(function(name, index)
        {
            score += USER_ACTIVITY_VALUES[index][this.activity[name]];
        }, this)
        var slope  = (NUT_ACTIVITY_FACTOR_MAX - NUT_ACTIVITY_FACTOR_MIN) / (USER_ACTIVITY_MAX - USER_ACTIVITY_MIN),
            origin = (0 - USER_ACTIVITY_MIN) * slope + NUT_ACTIVITY_FACTOR_MIN;
        this.needs[kcal_index] *= origin + score * slope;

        //Sports
        var nominal, factor;
        this.sports.each(function(sport)
        {
            nominal = SPORTS_VALUES[sport.id];
            factor  = SPORTS_FREQ_VALUES[sport.freq];
            this.needs[kcal_index] += nominal * weight * factor;
        }, this);

        //OTHER NEEDS
        var kcal_need   = this.needs[kcal_index],
            carb_index  = NUT_VALUES.indexOf('carb'),
            fat_index   = NUT_VALUES.indexOf('fat'),
            prot_index  = NUT_VALUES.indexOf('prot'),
            fibre_index = NUT_VALUES.indexOf('fibre'),
            stach_index = NUT_VALUES.indexOf('sta');
        this.needs[carb_index]  = NUT_KCAL_FRACTION[carb_index] * kcal_need / NUT_KCAL_VALUE[carb_index];
        this.needs[fat_index]   = NUT_KCAL_FRACTION[fat_index] * kcal_need / NUT_KCAL_VALUE[fat_index];
        this.needs[prot_index]  = NUT_PROT_PERWEIGHT * weight;
        this.needs[fibre_index] = age < 18 ? NUT_FIBRE_PERKCAL * kcal_need / 1000 : this.needs[fibre_index];
        this.needs[stach_index] = this.needs[carb_index];   //All carbs should be absorbed as stach if possible

        //Round result
        this.needs = this.needs.map(function(x){return Math.round(x);});
    },

    /*******************************************************
    OPTIONS
    ********************************************************/

    //Return value of specific option
    //#name (string): option short name
    //->value (int): option value
	option_get: function(name)
    {
        return this.options[name];
    },

    //Set specific option
    //#name (string):   option short name
    //#value (mixed):   option value
    //-> (void)
    option_set: function(name, value)
    {
        if(typeof(this.options[name]) != 'undefined')
        {
            this.options[name] = value;
            this.options_update(-1, name);
        }
    },

    //Export user's options in compact format
    //->options (array): list of options values indexed by ID
	options_export: function()
    {
        var options = [];
        for(var i = 0, imax = USER_OPTIONS.length; i < imax; i++)
        {
            options[i] = this.options[USER_OPTIONS[i]];
        }
        return options;
    },

    //Return current user options
    //->options (array): list of options values indexed by name
	options_get: function()
    {
        return this.options;
    },

    //Import compact options data from server
    //#options (array): list of options values indexed by ID
    //#time (int):      options data timestamp
    //-> (void)
	options_import: function(options, time)
    {
        this.options = {};
        for(var i = 0, imax = USER_OPTIONS.length; i < imax; i++)
        {
            if(typeof(options[i]) != 'undefined')
                this.options[USER_OPTIONS[i]] = parseInt(options[i]);
        }
        this.options_update(time, '', 'import');
    },

    //Set user's options
    //#options (array): list of options values indexed by name
    //-> (void)
	options_set: function(options)
    {
        for(var i = 0, imax = USER_OPTIONS.length; i < imax; i++)
        {
            if(typeof(options[USER_OPTIONS[i]]) != 'undefined')
                this.options[USER_OPTIONS[i]] = options[USER_OPTIONS[i]];
        }
        this.options_update();
    },

    //Called when user options have been updated
    //#time (int):      options update timestamp
    //#option (string): option that has been updated (optional)
    //#type (string):   update type (optional)
    //-> (void)
    options_update: function(time, option, type)
    {
        this.profile_updated('options', time);
        if(!option || option == 'currency' || option == 'units')
            this.menu.update(type);
        this.fire('updated', {'prop': 'options', 'option': option || ''});
    },

    /*******************************************************
    PANELS
    ********************************************************/

    //Export panels configuration
    //->config (object): panels configuration data
    panels_export: function()
    {
        return this.panels;
    },

    //Return current panels configuration
    //->config (object): panels configuration
    panels_get: function()
    {
        return this.panels;
    },

    //Import panels configuration from server
    //#panels (object): panels configuration data
    //#time (int):      panels data timestamp
    //-> (void)
    panels_import: function(panels, time)
    {
        this.panels =
        {
            'ids':    panels.ids.map(function(id){return parseInt(id);}),
            'sides':  panels.sides.map(function(side){return parseInt(side);}),
            'status': panels.status.map(function(status){return parseInt(status);})
        }
        this.panels_update(time);
    },

    //Set panels configuration
    //#config (object): panels configuration data
    //-> (void)
    panels_set: function(config)
    {
        this.panels = config;
        this.panels_update();
        this.profile_save(['panels']);
    },

    //Called when panels configuration has been updated
    //#time (int): panels update timestamp
    //-> (void)
    panels_update: function(time)
    {
        this.profile_updated('panels', time);
        this.fire('updated', {'prop': 'panels'});
    },

    /*******************************************************
    PERSONAL
    ********************************************************/

    //Import personal profile info
    //#personal (object):   personal data structure
    //#time (int):          personal data timestamp
    //-> (void)
    personal_import: function(personal, time)
    {
        this.id         = parseInt(personal.id);
        this.fb_id      = parseInt(personal.fb_id);
        this.tw_id      = parseInt(personal.tw_id);
        this.firstname  = personal.firstname.stripTags();
        this.lastname   = personal.lastname.stripTags();
        this.name       = this.firstname + ' ' + this.lastname;
        this.email      = personal.email.stripTags();
        this.grade      = parseInt(personal.grade)
        this.lang       = personal.lang.stripTags();
        this.pic_id     = parseInt(personal.pic_id);
        this.personal_update(time);
    },

    //Called when personal data has been updated
    //#time (int):      personal update timestamp
    //#field (string):  name of the specific field that was updated (optional)
    //-> (void)
    personal_update: function(time, field)
    {
        this.profile_updated('personal', time);
        this.fire('updated', {'prop': 'personal', 'field': field || ''});
    },

    /*******************************************************
    PICTURE
    ********************************************************/

    //Save user's pic on server
    //-> (void)
    pic_save: function()
    {
        Kookiiz.api.call('users', 'pic_save',
        {
            'callback': this.pic_saved.bind(this),
            'request':  'pic_id=' + this.pic_id
        });
    },

    //Called once picture has been saved
    //#response (object): server response object
    //-> (void)
    pic_saved: function(response)
    {
        var time = parseInt(response.pic_id);
        this.personal_update(time, 'pic');
    },

    //Set user's pic ID
    //#pic_id (int): pic unique ID
    //-> (void)
	pic_set: function(pic_id)
    {
        this.pic_id = parseInt(pic_id);
        this.pic_save();
    },

    /*******************************************************
    PROFILE
    ********************************************************/

    //Delete user profile !
    //-> (void)
    profile_delete: function()
    {
        Kookiiz.popup.loader();
        Kookiiz.api.call('users', 'delete',
        {
            'callback': this.profile_deleted.bind(this)
        });
    },

    //Callback for profile deletion process
    //#response (object): server response object
    //-> (void)
    profile_deleted: function(response)
    {
        this.fire('deleted');
    },

    //Export specific user profile data
    //#prop (string): user property to export
    //->value (mixed): exported property value (null if not found or not exportable)
    profile_export: function(prop)
    {
        switch(prop)
        {
            case 'activity':
                return this.activity_export();
                break;
            case 'allergies':
                return this.allergies_export();
                break;
            case 'anatomy':
                return this.anatomy_export();
                break;
            case 'breakfast':
                return this.breakfast_export();
                break;
            case 'favorites':
                return this.favorites_export();
                break;
            case 'fridge':
                return this.fridge_export();
                break;
            case 'menu':
                return this.menu_export();
                break;
            case 'panels':
                return this.panels_export();
                break;
            case 'options':
                return this.options_export();
                break;
            case 'sports':
                return this.sports_export();
                break;
            case 'tastes':
                return this.tastes_export();
                break;
            default:
                return null;
                break;
        }
    },

    //Import user profile data from server
    //#profile (object): structure indexed by profile properties with:
    //  #data (mixed):  property compact data
    //  #time (int):    data timestamp
    //-> (void)
    profile_import: function(profile)
    {
        for(var prop in profile)
        {
            switch(prop)
            {
                case 'activity':
                    this.activity_import(profile.activity.data, profile.activity.time);
                    break;
                case 'allergies':
                    this.allergies_import(profile.allergies.data, profile.allergies.time);
                    break;
                case 'anatomy':
                    this.anatomy_import(profile.anatomy.data, profile.anatomy.time);
                    break;
                case 'breakfast':
                    this.breakfast_import(profile.breakfast.data, profile.breakfast.time);
                    break;
                case 'favorites':
                    this.favorites_import(profile.favorites.data, profile.favorites.time);
                    break;
                case 'fridge':
                    this.fridge_import(profile.fridge.data, profile.fridge.time);
                    break;
                case 'friends':
                    this.friends_import(profile.friends.data, profile.friends.time);
                    break;
                case 'markets':
                    this.markets_import(profile.markets.data, profile.markets.time);
                    break;
                case 'menu':
                    this.menu_import(profile.menu.data, profile.menu.time);
                    break;
                case 'options':
                    this.options_import(profile.options.data, profile.options.time);
                    break;
                case 'panels':
                    this.panels_import(profile.panels.data, profile.panels.time);
                    break;
                case 'personal':
                    this.personal_import(profile.personal.data, profile.personal.time);
                    break;
                case 'quickmeals':
                    this.quickmeals_import(profile.quickmeals.data, profile.quickmeals.time);
                    break;
                case 'sports':
                    this.sports_import(profile.sports.data, profile.sports.time);
                    break;
                case 'tastes':
                    this.tastes_import(profile.tastes.data, profile.tastes.time);
                    break;
            }
        }
    },

    //Send request to server to load content of a specific user property
    //Force content reloading by setting timestamp to 0
    //#props (array): list of user properties to load
    //-> (void)
    profile_load: function(props)
    {
        //Abort if user is not logged
        if(!this.id) return;

        //Build updates structure
        var updates = {};
        for(var i = 0, imax = props.length; i < imax; i++)
        {
            updates[props[i]] = 0;
        }

        //Request session content
        Kookiiz.session.load({'user': updates});
    },

    //Save user profile (allergies, options and tastes)
    //#props (array):       list of user properties to save
    //#options (object):    saving options
    //  #callback (function): function to call once saving process is over (defaults to none)
    //  #message (string):    message to display in a popup once content is saved (optional)
    //  #silent (bool):       if true no popup loader or confirmation message is displayed (defaults to true)
    //  #sync (bool):         whether to save profile with a synchronized request (defaults to false)
    //-> (void)
	profile_save: function(props, options)
    {
        //Abort if user is not logged
        if(!this.id) return;

        //Default saving options
        options = Object.extend(
        {
            'callback': false,
            'message':  '',
            'silent':   true,
            'sync':     false
        }, options || {});

        //Display loader
        if(!options.silent)
            Kookiiz.popup.loader();

        //Build content structure
        var content = {}, times = this.profile_times(), prop = '';
        for(var i = 0, imax = props.length; i < imax; i++)
        {
            prop = props[i];
            content[prop] =
            {
                'data': this.profile_export(prop),
                'time': times[prop]
            };
        }

        //Save to server
        Kookiiz.session.save({'user': content}, this.profile_saved.bind(this, options), options.sync);
    },

    //Called when user profile has been saved
    //#options (object):  saving options
    //  message (string): message to display in a popup once content is saved
    //  silent (bool):    whether the properties were saved silently
    //#updates (object):  new timestamps for each saved property
    //-> (void)
    profile_saved: function(options, updates)
    {
        //Loop through saved properties
        var times = this.profile_times(),
            updated = false, toload = {};
        for(var prop in updates.user)
        {
            //Check if some data needs to be reloaded (DB version is more recent)
            var db_time = parseInt(updates[prop]);
            if(db_time > times[prop])
            {
                updated = true;
                toload[prop] = times[prop];
            }
            //Perform specific action
            switch(prop)
            {
                case 'menu':
                    this.menu.saved();
                    break;
            }
        }

        //Check if
        if(updated)
            Kookiiz.session.load({'user': toload});

        //Call callback function
        if(options.callback)
            options.callback();

        //Display popup confirmation
        if(!options.silent)
        {
            options.message = options.message || USER_ALERTS[0];
            Kookiiz.popup.alert({'text': options.message});
        }
    },

    //Return current profile timestamps
    //->times (object): timestamp values indexed by user property name
    profile_times: function()
    {
        return this.times;
    },

    //Called when any profile data has been updated
    //#prop (string):   name of the updated property
    //#time (int):      update timestamp (defaults to now)
    //-> (void)
    profile_updated: function(prop, time)
    {
        if(typeof(time) == 'undefined' || time < 0) time = Time.get(true);
        this.times[prop] = time;
    },

    /*******************************************************
    QUICK MEALS
    ********************************************************/

    //Store a new quick meal
    //#quickmeal_id (int): quick meal ID
    //-> (void)
    quickmeals_add: function(quickmeal_id)
    {
        this.quickmeals.push(quickmeal_id);
        this.quickmeals_update();
    },

    //Delete an existing quick meal
    //#quickmeal_id (int): quick meal ID
    //-> (void)
    quickmeals_delete: function(quickmeal_id)
    {
        for(var i = 0, imax = this.quickmeals.length; i < imax; i++)
        {
            if(this.quickmeals[i] == quickmeal_id)
            {
                this.quickmeals.splice(i, 1);
                break;
            }
        }
        this.menu.quickmeal_delete(false, quickmeal_id);
        this.quickmeals_update();
    },

    //Return list of user's quick meal IDs
    //->quickmeals (array): list of quick meal IDs
    quickmeals_get: function()
    {
        return this.quickmeals.slice();
    },

    //Import list of user's quick meals
    //#quickmeals (array):  list of quick meal IDs
    //#time (int):          quick meals data timestamp
    //-> (void)
    quickmeals_import: function(quickmeals, time)
    {
        this.quickmeals = quickmeals.parse('int');
        this.quickmeals_update(time);
    },

    //Called when quick meals content has been updated
    //#time (int): quickmeals update timestamp
    //-> (void)
    quickmeals_update: function(time)
    {
        this.profile_updated('quickmeals', time);
        this.fire('updated', {'prop': 'quickmeals'});
    },

    /*******************************************************
    SPORTS
    ********************************************************/

    //Add a new sport
    //#sport_id (int):  ID of the sport to add
    //#freq_id (int):   ID of the frequency
    //-> (void)
	sports_add: function(sport_id, freq_id)
    {
        //Check that the maximum number of sports is not reached
        if(this.sports.length == USER_SPORTS_MAX)
        {
            this.error('sports', 1);
            return;
        }

        //Check that this sport has not been added to user's sports already
        for(var i = 0, imax = this.sports.length; i < imax; i++)
        {
            if(this.sports[i].id == sport_id)
            {
                this.error('sports', 2);
                return;
            }
        }

        //Add sport
        this.sports.push({'id': sport_id, 'freq': freq_id});
        this.sports_update();
    },

    //Remove an existing sport
    //#sport_id (int): ID of the sport to remove
    //-> (void)
	sports_delete: function(sport_id)
    {
        for(var i = 0, imax = this.sports.length; i < imax; i++)
        {
            if(this.sports[i].id == sport_id)
            {
                this.sports.splice(i, 1);
                break;
            }
        }
        this.sports_update();
    },

    //Export user's sports
    //->sports (array): list of sports objects (sport ID/freq ID pairs)
	sports_export: function()
    {
        return this.sports;
    },

    //Return current user's sports
    //->sports (array): list of sports objects (sport ID/freq ID pairs)
	sports_get: function()
    {
        return this.sports;
    },

    //Import sports data from server
    //#sports (array):  list of sports objects (sport ID/freq ID pairs)
    //#time (int):      sports data timestamp
    //-> (void)
	sports_import: function(sports, time)
    {
        this.sports = [];
        for(var i = 0, imax = sports.length; i < imax; i++)
        {
            this.sports.push(
            {
                'id':   parseInt(sports[i].id),
                'freq': parseInt(sports[i].freq)
            });
        }
        this.sports_update(time);
    },

    //Called when sports have been updated
    //#time (int): sports update timestamp
    //-> (void)
    sports_update: function(time)
    {
        this.needs_update();
        this.profile_updated('sports', time);
        this.fire('updated', {'prop': 'sports'});
    },

    /*******************************************************
    TASTES
    ********************************************************/

    //Add a new taste
    //#ingredient_id (int): ID of the ingredient
    //#type (bool):         type of taste (like/dislike)
    //->error (int): error code (0 = no error)
	tastes_add: function(ingredient_id, type)
    {
        //Check that maximum number of tastes is not reached
        if(this.tastes.length == USER_TASTES_MAX) return 1;

        //Check that this ingredient has not been added to user's tastes already
        for(var i = 0, imax = this.tastes.length; i < imax; i++)
        {
            if(this.tastes[i].id == ingredient_id) return 2;
        }

        //Store new taste
        this.tastes.push({'id': ingredient_id, 'type': type});
        this.tastes_update();
        return 0;
    },

    //Remove an existing taste
    //#ingredient_id (int): ID of the ingredient
    //-> (void)
	tastes_delete: function(ingredient_id)
    {
        for(var i = 0, imax = this.tastes.length; i < imax; i++)
        {
            if(this.tastes[i].id == ingredient_id)
            {
                this.tastes.splice(i, 1);
                this.tastes_update();
                break;
            }
        }
    },

    //Check if user dislikes provided ingredient
    //#ingredient_id (int): ID of ingredient to test
    //->dislike (bool): true if user dislikes ingredient
    tastes_dislike: function(ingredient_id)
    {
        return this.tastes.any(function(taste)
        {
            return (taste.type == TASTE_DISLIKE) && (taste.id == ingredient_id);
        });
    },

    //Export user's tastes in compact format
    //->tastes (array): list of taste objects (ingredient ID/type pairs)
	tastes_export: function()
    {
        return this.tastes.slice();
    },

    //Return current user's tastes
    //#type (int): type of tastes to return
    //->tastes (object): tastes as an ingredient collection
	tastes_get: function(type)
    {
        var tastes = new IngredientsCollection();
        for(var i = 0, imax = this.tastes.length; i < imax; i++)
        {
            if(this.tastes[i].type == type)
                tastes.quantity_add(new IngredientQuantity(this.tastes[i].id, 1, 0));
        }
        return tastes;
    },

    //Import compact tastes data from server
    //#tastes (array):  list of taste objects (ingredient ID/type pairs)
    //#time (int):      taste data timestamp
    //-> (void)
	tastes_import: function(tastes, time)
    {
        this.tastes = [];
        for(var i = 0, imax = tastes.length; i < imax; i++)
        {
            this.tastes[i] =
            {
                'id':   parseInt(tastes[i].id),
                'type': parseInt(tastes[i].type)
            };
        }
        this.tastes_update(time);
    },

    //Check if user likes provided ingredient
    //#ingredient_id (int): ID of ingredient to test
    //->like (bool): true if user likes ingredient
    tastes_like: function(ingredient_id)
    {
        return this.tastes.any(function(taste)
        {
            return (taste.type == TASTE_LIKE) && (taste.id == ingredient_id);
        });
    },

    //Called when tastes have been updated
    //#time (int): taste update timestamp
    //-> (void)
    tastes_update: function(time)
    {
        this.profile_updated('tastes', time);
        this.fire('updated', {'prop': 'tastes'});
    }
});