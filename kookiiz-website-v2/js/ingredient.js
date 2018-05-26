/*******************************************************
Title: Ingredient
Authors: Kookiiz Team
Purpose: Define the ingredient object
********************************************************/

//Represents a recipe ingredient
var Ingredient = Class.create(
{
	object_name: 'ingredient',

	/*******************************************************
    CONSTRUCTOR
    ********************************************************/

    //Ingredient constructor
    //#properties (object): structure of ingredient properties
    //  #id (int):              ID of the ingredient
    //  #name (string):         ingredient name in session language
    //  #name_clean (string):   ingredient name without special chars
    //  #tags (array):          list of tags
    //  #tags_clean (array):    list of tags without special chars
    //  #cat (int):             ID of ingredient category
    //  #pic (string):          name of the pic
    //  #nutrition (array):     amount of nutritional values indexed by ID
    //  #unit (int):            ID of the default unit of the ingredient
    //  #wpu (int):             quantity of one unit of the ingredient (if it's countable, or 0)
    //  #price (float):         price in CHF for 100g of the ingredient
    //  #expiry (int):          number of conservation days after which ingredient can expire
    //  #prob (int):            ingredient probability (higher prob appears higher on suggestions list)
    //-> (void)
	initialize: function(properties)
    {
        Object.extend(this, properties);
    },

	/*******************************************************
    METHODS
    ********************************************************/

    //Check if an ingredient triggers a given allergy
    //#allergy (int): ID of the allergy to test
    //->allergenic (bool): true if ingredient can trigger specified allergy
	allergy_test: function(allergy)
    {
        if(!isNaN(allergy)) 
            allergy = ALLERGIES[allergy];
        if(!allergy) 
            return false;

        switch(allergy)
        {
            //Cereal products
            case 'gluten':
                if(this.cat == 20) 
                    return true;
                break;
            //Contains lactose
            case 'milk':
                var lactose_id = NUTRITION_NAMES.indexOf('lact');
                if(this.nutrition[lactose_id]) 
                    return true;
                break;
            //Ingredient name contains "egg"
            case 'egg':
                if(this.name.include(ALLERGIES_KEYWORDS[0])) 
                    return true;
                break;
            //Fish products
            case 'fish':
                if(this.cat == 15) 
                    return true;
                break;
            //Fish products (SUB-CATEGORY REQUIRED !)
            case 'crust':
                if(this.cat == 15)
                    return true;
                break;
            //Ingredient name contains "soja"
            case 'soy':
                if(this.name.include(ALLERGIES_KEYWORDS[1])) 
                    return true;
                break;
            //Nuts products
            case 'nuts':
                if(this.cat == 12) 
                    return true;
                break;
            //Ingredient name contains "sesame"
            case 'sesame':
                if(this.name.include(ALLERGIES_KEYWORDS[2])) 
                    return true;
                break;
            //Ingredient name contains "celery"
            case 'celery':
                if(this.name.include(ALLERGIES_KEYWORDS[3]))
                    return true;
                break;
        }
        return false;
    },

    //Return amount of a given nutriment in the ingredient
    //#value_id (int/string): ID of the nutritional value to return (or its short name)
    //->value (float): amount of the nutritional value contained in the ingredient
	nutrition_get: function(value_id)
    {
        if(isNaN(value_id)) 
            value_id = NUT_VALUES.indexOf(value_id);
        if(value_id < 0)    
            return false;
        return this.nutrition[value_id];
    }
});