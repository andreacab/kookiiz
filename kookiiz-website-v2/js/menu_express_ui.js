/*******************************************************
Title: Menu Express UI
Authors: Kookiiz Team
Purpose: Generate express menu from search options
********************************************************/

//Represents a user interface for menu express generation
var MenuExpressUI = Class.create(
{
    object_name: 'menu_express',

    /*******************************************************
    CONSTRUCTOR
    ********************************************************/

    //Class constructor
    //-> (void)
    initialize: function()
    {
        this.recipes = []; //List of recipes

        //DOM elements
        this.$start = $('menu_express');
    },

    /*******************************************************
    CONSTRUCTOR
    ********************************************************/

    //Init dynamic functionalities
    //-> (void)
    init: function()
    {
        this.$start.observe('click', this.onStart.bind(this));
    },

    /*******************************************************
    DISPLAY
    ********************************************************/

    //Display menu express results
    //-> (void)
    display: function()
    {
        //Retrieve criteria
        var from        = 0;
        var to          = 2;
        var starters    = 1;
        var mains       = 1;
        var desserts    = 0;

        //Dispatch search results in categories
        var recipes = this.recipes.random(), recipe_id, category,
            categories = {'starters': [], 'mains': [], 'desserts': []};
        for(var i = 0, imax = recipes.length; i < imax; i++)
        {
            recipe_id = recipes[i];
            category  = Recipes.get(recipe_id, 'category');
            switch(category)
            {
                case 1:
                    categories.mains.push(recipe_id);
                    break;
                case 5:
                    categories.starters.push(recipe_id);
                    break;
                case 3:
                    categories.desserts.push(recipe_id);
                    break;
            }
        }

        //Check if required count is reached
        var missing = false;
        if(categories.starters.length < starters)       missing = true;
        else if(categories.mains.length < mains)        missing = true;
        else if(categories.desserts.length < desserts)  missing = true;

        //Loop through selected period
        var pos;
        for(i = from; i <= to; i++)
        {
            pos = 0;

            //Loop through the starters
            for(var j = 0; j < starters; j++)
            {
                if(pos > MENU_MEALS_COUNT) break;
                recipe_id = categories.starters.pop();
                if(recipe_id)
                {
                    Kookiiz.menu.recipe_add(i, pos, recipe_id, MENU_GUESTS_DEFAULT);
                    pos++;
                }
            }
            //Loop through the main courses
            for(j = 0; j < mains; j++)
            {
                if(pos > MENU_MEALS_COUNT) break;
                recipe_id = categories.mains.pop();
                if(recipe_id)
                {
                    Kookiiz.menu.recipe_add(i, pos, recipe_id, MENU_GUESTS_DEFAULT);
                    pos++;
                }
            }
            //Loop through the desserts
            for(j = 0; j < desserts; j++)
            {
                if(pos > MENU_MEALS_COUNT) break;
                recipe_id = categories.desserts.pop();
                if(recipe_id)
                {
                    Kookiiz.menu.recipe_add(i, pos, recipe_id, MENU_GUESTS_DEFAULT);
                    pos++;
                }
            }
        }

        //Hide loader
        Kookiiz.popup.hide();

        //Display notification if recipes were missing
        if(missing) 
            Kookiiz.popup.alert({'text': MENU_ALERTS[6]});
    },
    
    /*******************************************************
    OBSERVERS
    ********************************************************/
   
    //Callback for click on menu express button
    //-> (void)
    onStart: function()
    {
        Kookiiz.tabs.show('main');
        this.search();
    },

    /*******************************************************
    PARSE
    ********************************************************/

    //Treat recipes results returned by server
    //#response (object): server response object
    //-> (void)
    parse: function(response)
    {

    },

    /*******************************************************
    SEARCH
    ********************************************************/

    //Throw a recipe search for menu express
    //-> (void)
    search: function()
    {
        //Display loader
        Kookiiz.popup.loader();

        //Retrieve current search criteria and tune them
        var criteria = Kookiiz.recipes.search_criteria();
        criteria.category = 0;  //No category
        criteria.random   = 1;	//Random results

        //Retrieve options
        var from     = 0,
            to       = 2,
            days     = Math.abs(to - from) + 1,
            starters = 1,
            mains    = 1,
            desserts = 0;

        //Create request to search for recipes
        Kookiiz.api.call('recipes', 'express', 
        {
            'callback': this.parse.bind(this),
            'request':  'criteria=' + Object.toJSON(criteria)
                        + '&starters=' + (starters * days)
                        + '&mains=' + (mains * days)
                        + '&desserts=' + (desserts * days)
        });
    }
});