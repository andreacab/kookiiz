/*******************************************************
Title: Recipe
Authors: Kookiiz Team
Purpose: Definition of the recipe object
********************************************************/

//Represents a cooking recipe
var Recipe = Class.create(Observable,
{
	object_name: 'recipe',

	/*******************************************************
    CONSTRUCTOR
    ********************************************************/

    //Class constructor
    //#id (int):            unique recipe ID
    //#name (string):       recipe full name
    //#properties (object): structure with recipe properties (optional)
    //-> (void)
	initialize: function(id, name, properties)
    {
        //Main attributes
        this.id   = parseInt(id);   //recipe unique ID
        this.name = name;           //recipe title

        //Attributes (secondary)
        this.description = '';                       //recipe preparation steps
        this.pic_id      = 0;                        //ID of associated pic (0 if there is none)
        this.guests      = RECIPE_GUESTS_DEFAULT;    //number of guests the recipe is meant for
        this.author_id   = 0;                        //ID of the author
        this.author_name = '';                       //name of the author
        this.preparation = 0;                        //preparation time in minutes
        this.cooking     = 0;                        //cooking time in minutes
        this.price       = 0;                        //price per person in CHF
        this.healthy     = 0;                        //recipe healthy score
        this.veggie      = 0;                        //recipe veggie score
        this.rating      = 0;                        //recipe rating
        this.level       = RECIPE_LEVEL_DEFAULT;     //recipe difficulty level
        this.category    = RECIPE_CATEGORY_DEFAULT;  //recipe category ID
        this.origin      = RECIPE_ORIGIN_DEFAULT;    //recipe origin ID
        this.lang        = LANG_DEFAULT;             //language identifier
        this.chef        = 0;                        //ID of related chef (0 if none)
        this.partner     = 0;                        //ID of the partner providing the recipe (0 if none)
        this.valid       = 1;                        //is the recipe valid ?
        this.update      = 0;                        //last recipe update

        //Arrays
        this.ingredients = new IngredientsCollection();  //ingredients collection
        this.nutrition   = [];                           //nutrition values

        //Observe ingredient modifications
        this.ingredients.observe('updated', this.ingredients_update.bind(this));

        //Import provided properties
        if(properties)
        {
            Object.extend(this, properties);
            this.full = true;
        }
        else 
            this.full = false;
    },
    
    /*******************************************************
    COPY
    ********************************************************/
   
    //Return an independent copy of current recipe
    //-> (void)
    copy: function()
    {
        var copy = new Recipe(this.id, this.name,
        {
            'description':  this.description,
            'pic_id':       this.pic_id,
            'guests':       this.guests,
            'author_id':    this.author_id,
            'author_name':  this.author_name,
            'preparation':  this.preparation,
            'cooking':      this.cooking,
            'price':        this.price,
            'healthy':      this.healthy,
            'veggie':       this.veggie,
            'rating':       this.rating,
            'level':        this.level,
            'category':     this.category,
            'origin':       this.origin,
            'lang':         this.lang,
            'chef':         this.chef,
            'partner':      this.partner,
            'valid':        this.valid,
            'update':       this.update
        });
        copy.ingredients.import_content(this.ingredients.export_content());
        
        return copy;
    },

	/*******************************************************
    CRITERIA
    ********************************************************/

    //Check if recipe triggers user's allergies
    //->allergy (bool): true if recipe triggers any of user's allergies
	isallergy: function()
    {
        var allergies = User.allergies_get(), ing;
        for(var i = 0, imax = allergies; i < imax; i++)
        {
            if(allergies[i])
            {
                for(var j = 0, jmax = this.ingredients.length; j < jmax; j++)
                {
                    ing = Ingredients.get(this.ingredients[j].id);
                    if(ing.allergy_test(i)) 
                        return true;
                }
            }
        }
        return false;
    },

    //Check if the recipe is cheap
    //->cheap (bool): true if recipe is cheap
	ischeap: function()
    {
        return this.price < RECIPE_CHEAP_THRESHOLD;
    },

    //Check if the recipe is from a chef
    //#chef_id (int): ID of specific chef (optional)
    //->chef (bool): true if recipe is from a chef (from specific chef if chef_id is specified)
	ischef: function(chef_id)
    {
        if(typeof(chef_id) == 'undefined') 
            return this.chef > 0;
        else 
            return this.chef == chef_id;
    },

    //Check if the recipe contains disliked ingredients
    //->disliked (bool): true if recipe contains disliked ingredients
	isdisliked: function()
    {
        return this.ingredients.any(function(ingredient)
        {
            return User.tastes_dislike(ingredient.id);
        });
    },

    //Check if the recipe is easy
    //->easy (bool): true if recipe is easy
	iseasy: function()
    {
        return this.level <= RECIPE_EASY_THRESHOLD;
    },

    //Check if the recipe contains fridge ingredients
    //->fridge (bool): true if recipe contains fridge ingredients
    isfridge: function()
    {
        return this.ingredients.any(function(ingredient)
        {
            return User.fridge.contains(ingredient.id);
        });
    },

    //Check if the recipe is healthy
    //->healthy (bool): true if recipe is healthy
	ishealthy: function()
    {
        return this.healthy > RECIPE_HEALTHY_THRESHOLD;
    },

   //Check if the recipe contains liked ingredients
    //->disliked (bool): true if recipe contains liked ingredients
	isliked: function()
    {
        return this.ingredients.any(function(ingredient)
        {
            return User.tastes_like(ingredient.id);
        });
    },

    //Check if the recipe is quick
    //->quick (bool): true if recipe is quick
	isquick: function()
    {
        return this.preparation + this.cooking < RECIPE_QUICK_THRESHOLD;
    },

    //Check if the recipe contains season ingredients
    //->success (bool): true if recipe contains season ingredients
	isseason: function()
    {
        var ingredients = Ingredients.getSeason();
        for(var i = 0, imax = ingredients.length; i < imax; i++)
        {
            if(this.has_ingredient(ingredients[i])) 
                return true;
        }
        return false;
    },

    //Check if the recipe is a success
    //->success (bool): true if recipe is a success
	issuccess: function()
    {
        return this.rating >= RECIPE_SUCCESS_THRESHOLD;
    },

    //Check if the recipe is veggie
    //->veggie (bool): true if recipe is at least regular veggie
	isveggie: function()
    {
        return this.veggie >= VEGGIE_REGULAR;
    },

    //Check whether recipe fullfill provided criteria or not
    //#criteria (object): structure containing search criteria
    //->fullfill (bool): true if recipe fullfills criteria
	match_criteria: function(criteria)
    {
        //Main criteria
        if(criteria.allergy         && this.isallergy())                            return false;
        if(criteria.category > 0    && this.category != criteria.category)          return false;
        if(criteria.disliked        && this.isdisliked())                           return false;
        if(criteria.favorites       && User.favorites_get().indexOf(this.id) < 0)   return false;
        if(criteria.origin > 0      && this.origin != criteria.origin)              return false;
        if(criteria.text            && !this.name.include(criteria.text))           return false;
        if(criteria.chef            && !this.ischef())                              return false;
        if(criteria.chef > 0        && !this.ischef(criteria.chef))                 return false;
        //Boolean properties
        if(criteria.cheap           && !this.ischeap())                             return false;
        if(criteria.easy            && !this.iseasy())                              return false;
        if(criteria.healthy         && !this.ishealthy())                           return false;
        if(criteria.quick           && !this.isquick())                             return false;
        if(criteria.success         && !this.issuccess())                           return false;
        if(criteria.veggie          && !this.isveggie())                            return false;
        
        //Ingredient-related
        var ingredients = Kookiiz.recipes.search_criteria_ings('include'), match;
        if(ingredients.length)
        {
            for(var i = 0, imax = ingredients.length; i < imax; i++)
            {
                if(this.has_ingredient(ingredients[i]))
                {
                    match = true;
                    break;
                }
            }
            if(!match) return false;
        }
        
        //Match
        return true;
    },

    /*******************************************************
    EXPORT
    ********************************************************/

    //Export recipe content in compact format
    //-> (void)
    exportData: function()
    {
        var content = 
        {
            'author_id':    this.author_id,
            'name':         this.name,
            'description':  this.description,
            'ingredients':  this.ingredients.export_content(),
            'pic_id':       this.pic_id,
            'guests':       this.guests,
            'category':     this.category,
            'origin':       this.origin,
            'level':        this.level,
            'preparation':  this.preparation,
            'cooking':      this.cooking,
            'price':        this.price
        };
        return content;
    },

    /*******************************************************
    GETTERS
    ********************************************************/

    //Return recipe price in provided currency (or default)
    //#currency_id (int): ID of the currency (optional)
    //->price (int): recipe price (rounded to 1 currency unit)
    getPrice: function(currency_id)
    {
        return Math.round(this.price * (currency_id ? CURRENCIES_VALUES[currency_id] : 1));
    },

	/*******************************************************
    INGREDIENTS
    ********************************************************/

    //Check if a specific ingredient is contained in the recipe
    //#id (int): ID of the ingredient
    //-> (void)
	has_ingredient: function(id)
    {
        return this.ingredients.any(function(ingredient)
        {
            return ingredient.id == id;
        });
    },

    //Called when ingredients collection has been updated
    //-> (void)
    ingredients_update: function()
    {
        this.nutrition_update();
        this.price_update();
        this.fire('updated', {'prop': 'ingredients'});
    },

    /*******************************************************
    NUTRITION
    ********************************************************/

    //Compute and update recipe nutritional content
    //-> (void)
	nutrition_update: function()
    {
        var guests = this.guests;
        this.nutrition = this.ingredients.nutrition.map(function(val){return val / guests});
        this.fire('updated', {'prop': 'nutrition'});
    },

    /*******************************************************
    PRICE
    ********************************************************/

    //Update recipe price
    //-> (void)
    price_update: function()
    {
        this.price = Math.round(this.ingredients.getPrice() / this.guests);
        this.fire('updated', {'prop': 'price'});
    },

    /*******************************************************
    SET
    ********************************************************/

    //Set number of guests for recipe
    //#guests (int): new guests count
    //-> (void)
    setGuests: function(guests)
    {
        //Check boundaries
        if(guests < RECIPE_GUESTS_MIN)
            guests = RECIPE_GUESTS_MIN;
        else if(guests > RECIPE_GUESTS_MAX) 
            guests = RECIPE_GUESTS_MAX;

        //Update guests value
        this.guests = guests;

        //Trigger updates
        this.nutrition_update();
        this.price_update();
        this.fire('updated', {'prop': 'guests'});
    }
});