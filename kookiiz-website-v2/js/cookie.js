/*******************************************************
Title: Cookie
Authors: Kookiiz Team
Purpose: Provide methods to set, read and erase cookies
********************************************************/

//Represents a handler for browser cookies
var CookieHandler = Class.create(
{
    object_name: 'cookie_handler',

    /*******************************************************
    CONSTRUCTOR
    ********************************************************/

    //Class constructor
    //-> (void)
    initialize: function()
    {
    },

    /*******************************************************
    GET
    ********************************************************/

    //Read value of a given cookie
    //#name (string): cookie name
    //->value (string): cookie value
    get: function(name)
    {
        var key, val, array = document.cookie.split(';');
        for(var i = 0, imax = array.length; i < imax; i++)
        {
            key = array[i].substr(0, array[i].indexOf('='));
            val = array[i].substr(array[i].indexOf('=') + 1);
            key = key.replace(/^\s+|\s+$/g, '');
            if(key == name)
                return unescape(val);
        }
    },

    /*******************************************************
    SET
    ********************************************************/

    //Set a new cookie
    //#name (string):   cookie name
    //#value (string):  cookie value
    //#expiry (int):    expiry in days
    //-> (void)
    set: function(name, value, expiry)
    {
        //Set expiration date
        var exp = new Date();
        exp.setDate(exp.getDate() + expiry);
        //Build cookie string
        var cookie = escape(value) + ((expiry == null) ? '' : '; expires=' + exp.toUTCString());
        //Store cookie
        document.cookie = name + '=' + cookie;
    },

    /*******************************************************
    UNSET
    ********************************************************/

    //Unset a given cookie
    //#name (string): cookie name
    //-> (void)
    unset: function(name)
    {
        this.set(name, '', -1);
    }
});