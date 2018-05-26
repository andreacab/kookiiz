/*******************************************************
Title: Quick meals library
Authors: Kookiiz Team
Purpose: Store and manage quick meal objects
********************************************************/

//Represents a library of quick meals
var QuickmealsLib = Class.create(Library,
{
    object_name: 'quickmeals_lib',

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
    IMPORT
    ********************************************************/

    //Import quick meals content into the library
    //#quickmeals (array): list of quick meal data structures
    //-> (void)
    import_content: function(quickmeals)
    {
        var id, name, mode, meal;
        for(var i = 0, imax = quickmeals.length; i < imax; i++)
        {
            id   = parseInt(quickmeals[i].id);
            name = decodeURIComponent(quickmeals[i].name.stripTags());
            mode = parseInt(quickmeals[i].mode);
            meal = new QuickMeal(id, name, mode);
            
            if(mode == QM_MODE_INGREDIENTS)
                meal.ingredients.import_content(quickmeals[i].ing);
            else
                meal.nutrition = quickmeals[i].nut.parse('float');
            this.store(meal);
        }
    }
});