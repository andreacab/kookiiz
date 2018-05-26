/*******************************************************
Title: Users library
Authors: Kookiiz Team
Purpose: Store and manage users profiles
********************************************************/

//Represents a user library
var UsersLib = Class.create(Library,
{
    object_name: 'users_lib',

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
    FETCH
    ********************************************************/

    //Load public user profile from library or server (if callback is provided)
    //#user_id (int):       user unique ID
    //#callback (function): function to call once profile is loaded
    //->user (object/bool): user object (false if it's not available yet)
    fetch: function(user_id, callback)
    {
        var user = this.get(user_id);
        if(user)
            return user;
        else
        {
            this.load(user_id, callback);
            return false;
        }
    },

    /*******************************************************
    IMPORT
    ********************************************************/

    //Import public user profiles in library
    //#users (array): list of user compact profiles
    //-> (void)
    import_content: function(users)
    {
        for(var i = 0, imax = users.length; i < imax; i++)
        {
            this.store(new UserPublic(
            {
                'id':           parseInt(users[i].id),
                'fb_id':        parseInt(users[i].fb_id),
                'tw_id':        parseInt(users[i].tw_id),
                'firstname':    users[i].first.stripTags(),
                'lastname':     users[i].last.stripTags(),
                'date':         users[i].date.stripTags(),
                'grade':        parseInt(users[i].grade),
                'pic_id':       parseInt(users[i].pic_id),
                'lang':         users[i].lang.stripTags(),
                'recipes':      users[i].recipes
            }));
        }
    },

    /*******************************************************
    LOAD
    ********************************************************/

    //Load user profile from server
    //#user_id (int):       unique user ID
    //#callback (function): function to call once profile is available
    //-> (void)
    load: function(user_id, callback)
    {
        Kookiiz.api.call('users', 'preview',
        {
            'callback': this.parse.bind(this, callback),
            'request':  'user_id=' + user_id
        });
    },

    /*******************************************************
    PARSE
    ********************************************************/

    //Parse user profile information fetched from server
    //#callback (function): function to call once profile is loaded
    //#response (object):   server response object
    //-> (void)
    parse: function(callback, response)
    {
        var user_id = parseInt(response.parameters.user_id);
        this.import_content(response.content);
        callback(user_id);
    }
});