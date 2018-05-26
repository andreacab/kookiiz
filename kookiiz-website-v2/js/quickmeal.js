/*******************************************************
Title: Quick meal
Authors: Kookiiz Team
Purpose: Define the quick meal object
********************************************************/

//Represents a quick meal
var QuickMeal = Class.create(
{
	object_name: 'quickmeal',

	/*******************************************************
    CONSTRUCTOR
    ********************************************************/

    //Class constructor
    //#id (int):        unique quick meal ID
    //#name (string):   quick meal name
    //#mode (string):   whether the quick meal is built from ingredients or nutrition values
    //-> (void)
	initialize: function(id, name, mode)
    {
        this.id         = id;
        this.name       = name;
        this.mode       = mode;
        if(mode == QM_MODE_INGREDIENTS)  this.ingredients = new IngredientsCollection();
        else                             this.nutrition   = [];
    },

    /*******************************************************
    EXPORT
    ********************************************************/

    //Export compact quick meal content
    //->quickmeal (object): compact quick meal content
    export_content: function()
    {
        var self = this;
        var quickmeal =
        {
            'id':           self.id,
            'mode':         self.mode,
            'name':         encodeURIComponent(self.name)
        };
        if(this.mode == QM_MODE_INGREDIENTS)
        {
            quickmeal.ingredients = this.ingredients.export_content();
        }
        else
        {
            quickmeal.nutrition = this.nutrition;
        }
        return quickmeal;
    },

    /*******************************************************
    NUTRITION
    ********************************************************/

    //Return quick meal nutrition values
    //->nutrition (array): list of nutrition values indexed by ID
    getNutrition: function()
    {
        if(this.mode == QM_MODE_INGREDIENTS) return this.ingredients.nutrition;
        else                                        return this.nutrition;
    }
});