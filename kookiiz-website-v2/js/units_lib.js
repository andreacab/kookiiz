/*******************************************************
Title: Units library
Authors: Kookiiz Team
Purpose: Storage for quantity units
********************************************************/

//Represents a library of quantity units
var UnitsLib = Class.create(Library,
{
    object_name: 'units_lib',

    /*******************************************************
    CONSTRUCTOR
    ********************************************************/

    //Class constructor
    //#$super (function): superclass constructor
    //-> (void)
    initialize: function($super)
    {
        $super();
    },

    /*******************************************************
    IMPORT
    ********************************************************/

    //Import units data
    //#data (array): list of unit objects
    //-> (void)
    import_content: function(data)
    {
        var id, props;
        for(var i = 0, imax = data.length; i < imax; i++)
        {
            id = parseInt(data[i].id);
            props =
            {
                'display':  data[i].disp,
                'eq_id':    parseInt(data[i].eq_id),
                'metric':   data[i].met,
                'imperial': data[i].imp,
                'name':     UNITS_NAMES[id],
                'round':    parseFloat(data[i].round),
                'value':    parseFloat(data[i].val)
            }
            this.library.push(new Unit(id, props));
        }
        this.library.sort();
    },

    /*******************************************************
    SORT
    ********************************************************/

    //Sort units according to cycle order
    //#unit_a (object): first unit to sort
    //#unit_b (object): second unit to sort
    //->sorting (int): -1 (a before b), 0 (no sorting), 1 (a after b)
    sort: function(unit_a, unit_b)
    {
        var index_a = this.ORDER.indexOf(unit_a),
            index_b = this.ORDER.indexOf(unit_b);
        return index_a < index_b ? -1 : (index_a > index_b ? 1 : 0);
    }
});