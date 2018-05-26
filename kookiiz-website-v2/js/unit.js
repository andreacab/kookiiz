/*******************************************************
Title: Unit
Authors: Kookiiz Team
Purpose: Description of the quantity unit object
********************************************************/

//Represents a unit for ingredient quantities
var Unit = Class.create(
{
    object_name: 'unit',

    /*******************************************************
    CONSTRUCTOR
    ********************************************************/

    //Class constructor
    //-> (void)
    initialize: function(id, props)
    {
        this.id         = id;
        this.display    = true;
        this.eq_id      = 0;
        this.metric     = true;
        this.imperial   = true;
        this.name       = '';
        this.round      = 1;
        this.value      = 0;

        Object.extend(this, props || {});
    },
    
    /*******************************************************
    GETTERS
    ********************************************************/
   
    /**
     * Returns unit equivalent ID in opposite system (metric/imperial)
     * ->eqID (int): unit ID
     */
    getEq: function()
    {
        return this.eq_id;
    },
    
    /**
     * Returns unit unique ID
     * ->ID (int): unique ID
     */
    getID: function()
    {
        return this.id;
    },
    
    /**
     * Returns unit name in current language
     * ->name (string): unit name
     */
    getName: function()
    {
        return this.id == UNIT_NONE ? '' : this.name;
    },
    
    /**
     * Returns unit rounding factor
     * ->round (float): rounding factor
     */
    getRound: function()
    {
        return this.round;
    },
    
    /*******************************************************
    TESTS
    ********************************************************/
    
    /**
     * Check if unit is from provided system
     * #system (string): unit system
     * ->result (bool): true if unit is from provided system
     */
    isSystem: function(system)
    {
        switch(system)
        {
            case 'metric':
                return this.metric;
                break;
            case 'imperial':
                return this.imperial;
                break;
            default:
                return false;
        }
    }
});