/*******************************************************
Title: Ingredient quantity
Authors: Kookiiz Team
Purpose: Define the ingredient quantity object
********************************************************/

//Represents an amount of ingredient
var IngredientQuantity = Class.create(
{
    object_name: 'ingredient_quantity',

	/*******************************************************
    CONSTRUCTOR
    ********************************************************/

    //Ingredient quantity constructor
    //#id (int):            ID of the corresponding ingredient
    //#quantity (float):    quantity of ingredient
    //#unit (int):          ID of the unit in which the quantity is expressed
    //#expired (bool):      whether ingredient quantity is expired (defaults to false)
    //#stocked (bool):      whether ingredient quantity is currently in stock (in user's fridge)
    //#modified (bool):     whether ingredient quantity has been modified by user
    //-> (void)
	initialize: function(id, quantity, unit, expired, stocked, modified)
    {
        this.id         = id;
        this.quantity   = quantity;
        this.unit       = unit;
        this.expired    = expired   || false;
        this.stocked    = stocked   || false;
        this.modified   = modified  || false;

        //Pointer on corresponding ingredient object
        this.ingredient = Ingredients.get(this.id);
    },

    /*******************************************************
    CONVERT
    ********************************************************/

    //Convert ingredient quantity from current unit to another
    //#unit (int):      ID of the output unit
    //#update (bool):   whether to update current quantity object
    //->final_quantity (float): converted quantity
    convert: function(unit, update)
    {
        var final_quantity  = this.quantity;
        if(unit != this.unit)
        {
            var wpu = this.ingredient.wpu;

            //Convert init unit to g (if needed)
            var unit_value;
            if(this.unit != UNIT_GRAMS && this.unit != UNIT_MILLILITERS)
            {
                unit_value = Units.get(this.unit, 'value');
                if(unit_value)  
                    final_quantity *= unit_value;
                else            
                    //No unit -> multiply by wpu
                    final_quantity *= wpu;    
            }

            //Convert from g to final unit (if needed)
            if(unit != UNIT_GRAMS && unit != UNIT_MILLILITERS)
            {
                unit_value = Units.get(unit, 'value');
                if(unit_value)  
                    final_quantity /= unit_value;
                else            
                    //No unit -> divide by wpu
                    final_quantity /= wpu;
            }
        }
        if(update)
        {
            this.quantity = final_quantity;
            this.unit     = unit;
        }
        return final_quantity;
    },

    //Convert ingredient quantity from current unit to its default
    //#update (bool): whether to update current quantity object
    //->final_quantity (float): converted quantity
    convert_default: function(update)
    {
        return this.convert(this.ingredient.unit, update);
    },

    /*******************************************************
    COPY
    ********************************************************/

    //Return a copy of this ingredient quantity
    //->copy (object): new ingredient quantity
    copy: function()
    {
        return new IngredientQuantity(this.id, this.quantity, this.unit, this.expired, this.stocked, this.modified);
    },

    /*******************************************************
    EXPIRY
    ********************************************************/

    //Update expiry status according to conservation
    //#conservation (int): days during which the ingredient will be stored
    //-> (void)
    expiry: function(conservation)
    {
        var expiry   = this.ingredient.exp;
        this.expired = expiry && (conservation > expiry);
    },

    /*******************************************************
    PRICE
    ********************************************************/

    //Return ingredient quantity price
    //->price ()
    price: function()
    {
        var quantity = this.quantity < 0 ? 0 : this.convert(UNIT_GRAMS);
        return quantity * (this.ingredient.price / 100);
    },

    /*******************************************************
    WEIGHT
    ********************************************************/

    //Return ingredient quantity weight
    //->weight ()
    weight: function()
    {
        return this.quantity < 0 ? 0 : this.convert(UNIT_KILOGRAMS);
    }
});