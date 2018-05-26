/*******************************************************
Title: Recipes library
Authors: Kookiiz Team
Purpose: Store and manage recipe objects
********************************************************/

//Represents a collection of recipes
var RecipesLib = Class.create(Library,
{
    object_name: 'recipes_library',

    /*******************************************************
    CONSTRUCTOR
    ********************************************************/

    //Class constructor
    //#$super (function): super class constructor
    //-> (void)
    initialize: function($super)
    {
        $super();
    },

    /*******************************************************
    EXPORT
    ********************************************************/

    //Export current recipe library timestamps
    //->updates (array): pairs of recipe ID/timestamp
    export_times: function()
    {
        var updates = [];
        this.library.each(function(recipe)
        {
            updates.push(
            {
                'i': recipe.id,
                't': recipe.update
            });
        });
        return updates;
    },

    /*******************************************************
    FETCH
    ********************************************************/

    //Get recipe object if available or load it from server
    //#recipe_id (int):     unique recipe ID
    //#callback (function): function to call once content is loaded from server
    //->recipe (object/false): recipe object (false if not found)
    fetch: function(recipe_id, callback)
    {
        var recipe = this.get(recipe_id);
        if(recipe) 
            return recipe;
        else
        {
            if(callback) 
                this.load([recipe_id], callback);
            return false;
        }
    },

    //Same functionality than "fetch" method, but for a list of recipe IDs
    //#recipes_ids (array): list of recipe IDs
    //#callback (function): function to call once content is loaded from server
    //-> (void)
    fetch_all: function(recipes_ids, callback)
    {
        var missing_recipes = this.check(recipes_ids)[1];
        if(missing_recipes.length)  
            this.load(missing_recipes, callback);
        else                        
            callback();
    },

    /*******************************************************
    IMPORT
    ********************************************************/

    //Import recipes content into the library
    //#data (array): list of recipe data structures
    //-> (void)
    import_content: function(data)
    {
        //Loop through recipes content to create recipe objects
        var id, name, properties, recipe;
        for(var i = 0, imax = data.length; i < imax; i++)
        {
            id   = parseInt(data[i].id);
            name = data[i].name.stripTags();

            //Retrieve recipe properties
            properties =
            {
                'description':  data[i].desc.stripTags(),
                'pic_id':       parseInt(data[i].pic),
                'guests':       parseInt(data[i].guest),
                'author_id':    parseInt(data[i].auth_id),
                'author_name':  data[i].auth_name.stripTags(),
                'preparation':  parseInt(data[i].prep),
                'cooking':      parseInt(data[i].cook),
                'price':        parseInt(data[i].price),
                'healthy':      parseInt(data[i].healthy),
                'veggie':       parseInt(data[i].veggie),
                'rating':       parseInt(data[i].rate),
                'level':        parseInt(data[i].lev),
                'category':     parseInt(data[i].cat),
                'origin':       parseInt(data[i].orig),
                'chef':         parseInt(data[i].chef_id),
                'partner':      parseInt(data[i].partner_id),
                'valid':        parseInt(data[i].valid),
                'lang':         data[i].lang,
                'update':       parseInt(data[i].update)
            }

            //Remove previous copy of this recipe (if any)
            this.remove(id);
            
            //Create new recipe object
            recipe = new Recipe(id, name, properties);
            recipe.ingredients.import_content(data[i].ing);
            this.library.push(recipe);
        }
    },

    /*******************************************************
    LOAD
    ********************************************************/

    //Load recipe content from server
    //#recipes_ids (array): list of recipe IDs to load
    //#callback (function): function to call when recipe content is made available
    //-> (void)
    load: function(recipes_ids, callback)
    {
        Kookiiz.api.call('recipes', 'load', 
        {
            'callback': this.parse.bind(this, callback),
            'request':  'recipes_ids=' + Object.toJSON(recipes_ids)
        });
    },

    /*******************************************************
    PARSE
    ********************************************************/

    //Parse recipes content received from server
    //#callback (function): function to call once content is loaded
    //#response (object):   server response object
    //-> (void)
    parse: function(callback, response)
    {
        //Import recipes content
        this.import_content(response.content);

        //Trigger callback function
        callback();
    }
});