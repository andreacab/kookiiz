/*******************************************************
Title: Library
Authors: Kookiiz Team
Purpose: Generic class for objects storage
********************************************************/

//Represents a storage for a collection of objects
var Library = Class.create(
{
    object_name: 'library',

    /*******************************************************
    CONSTRUCTOR
    ********************************************************/

    //Class constructor
    //-> (void)
    initialize: function()
    {
        this.library = [];
    },

    /*******************************************************
    CHECK
    ********************************************************/

    //Check if provided object IDs are already available client-side
    //#object_ids (array):  list of object IDs
    //->partition (array): two arrays with IDs that were found and those that were not
    check: function(object_ids)
    {
        return object_ids.partition(function(id){return this.exist(id);}, this);
    },

    /*******************************************************
    COUNT
    ********************************************************/

    //Get number of objects in library
    //->count (int): number of objects
    count: function()
    {
        return this.library.length;
    },

    /*******************************************************
    EXIST
    ********************************************************/

    //Check if object with provided ID is available in library
    //#id (int): unique object ID
    //->exist (bool): true if object is available
    exist: function(id)
    {
        return this.library.any(function(obj){return obj.id == id;});
    },

    /*******************************************************
    EXPORT
    ********************************************************/

    //Export object IDs stored in library
    //->ids (array): list of object IDs
    export_ids: function()
    {
        return this.library.map(function(obj){return obj.id;});
    },

    /*******************************************************
    FIND
    ********************************************************/

    //Return object with provided ID
    //#id (int): unique object ID
    //->item (object): corresponding object (false if not found)
    find: function(id)
    {
        return this.library.find(function(obj){return obj.id == id;});
    },

    /*******************************************************
    GET
    ********************************************************/

    //Get specific object property or full object
    //#id (int):        unique object ID
    //#prop (string):   property to return
    //->value (mixed): object property or object
    get: function(id, prop)
    {
        var obj = this.find(id);
        if(obj)
        {
            if(prop)    return obj[prop];
            else        return obj;
        }
        else return null;
    },

    //Return all objects from library
    //->objects (array): list of library objects
    getAll: function()
    {
        return this.library.slice();
    },

    /*******************************************************
    MAX
    ********************************************************/

    //Return current library highest ID
    //->max (int): max ID in library
    maxID: function()
    {
        var max = -1, id;
        for(var i = 0, imax = this.library.length; i < imax; i++)
        {
            id = this.library[i].id
            if(id > max) max = id;
        }
        return max;
    },

    /*******************************************************
    REMOVE
    ********************************************************/

    //Remove a given object from the library
    //#id (int): unique ID of the object to remove
    //-> (void)
    remove: function(id)
    {
        for(var i = 0, imax = this.library.length; i < imax; i++)
        {
            if(this.library[i].id == id)
            {
                this.library.splice(i, 1);
                break;
            }
        }
    },

    //Remove objects from a list of IDs
    //#ids (array): list of object IDs
    //-> (void)
    remove_all: function(ids)
    {
        for(var i = 0, imax = this.library.length; i < imax; i++)
        {
            if(ids.indexOf(this.library[i].id) >= 0)
            {
                this.library.splice(i, 1);
                i--; imax--;
            }
        }
    },

    /*******************************************************
    STORE
    ********************************************************/

    //Add object to library (and delete pre-existing one)
    //#obj (object): new object
    //-> (void)
    store: function(obj)
    {
        this.remove(obj.id);
        this.library.push(obj);
    }
});