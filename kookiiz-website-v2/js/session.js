/*******************************************************
Title: Session handler
Authors: Kookiiz Team
Purpose: Load and save sessions
********************************************************/

//Represents an interface to load and save sessions from server
var SessionHandler = Class.create(Observable,
{
    /*******************************************************
    CONSTRUCTOR
    ********************************************************/

    //Class constructor
    //-> (void)
    initialize: function()
    {
        this.loaded = false;    //Has session been loaded at least once?
        this.timer  = 0;        //Periodical session updates timer
    },
    
    /*******************************************************
    INIT
    ********************************************************/

    //Init dynamic functionalities
    //-> (void)
    init: function()
    {
    },
    
    /*******************************************************
    LOAD
    ********************************************************/

    //Load session data from server
    //#updates (object):    structure containing a timestamp for each property to load
    //#callback (function): function to call once content has been loaded
    //-> (void)
    load: function(updates, callback)
    {
        //Add current recipes and users
        updates.recipes = Recipes.export_times();
        updates.users   = Users.export_ids();

        //Create AJAX request
        Kookiiz.api.call('session', 'load',
        {
            'callback': this.onLoad.bind(this, callback),
            'request':  'updates=' + Object.toJSON(updates)
        });
    },
    
    /*******************************************************
    LOGOUT
    ********************************************************/
   
    //Log user out
    //-> (void)
    logout: function()
    {
        window.location = '/logout';
    },

    /*******************************************************
    OBSERVERS
    ********************************************************/
   
    //Parse session retrieved from server
    //#callback (function): callback for session loading process
    //#response (object):   server response object
    //-> (void)
    onLoad: function(callback, response)
    {
        //Import session data
        var session = response.content;
        try
        {
            //Library data
            if(session.quickmeals)
                Quickmeals.import_content(session.quickmeals);
            if(session.recipes)
                Recipes.import_content(session.recipes);
            if(session.users)
                Users.import_content(session.users);

            //User profile
            if(session.user)
                User.profile_import(session.user);
            //Invitations
            if(session.invits)
                Invitations.import_content(session.invits);
            //Notifications
            if(session.notifs)
                Kookiiz.notifications.import_content(session.notifs);
        }
        catch(error){Kookiiz.error.catcher(error);}

        //Callback function
        if(callback) 
            callback();
    },
    
    //Callback for session saving process
    //#callback (function): callback for saving process (optional)
    //#response (object):   server response object
    //-> (void)
    onSave: function(callback, response)
    {
        var updates = response.content;
        if(callback) 
            callback(updates);
    },
    
    //Callback function for update process
    //-> (void)
    onUpdate: function()
    {
        //First time
        if(!this.loaded)
        {
            //Set session as "loaded"
            this.loaded = true;
            this.fire('loaded');
        }

        //Plan next update
		if(Kookiiz.MODE != 'mobile')
		{
			window.clearTimeout(this.timer);
			this.timer = window.setTimeout(this.update.bind(this), SESSION_TIMEOUT * 1000);
		}
    },

    /*******************************************************
    SAVE
    ********************************************************/

    //Save session data on server
    //#content (object):    structure with the data to save
    //#callback (function): callback for saving process
    //#sync (bool):         whether to use a synchronous AJAX request (defaults to false)
    //-> (void)
    save: function(content, callback, sync)
    {
        if(!content) return;

        //Send request to server
        Kookiiz.api.call('session', 'save',
        {
            'callback': this.onSave.bind(this, callback || false),
            'request':  'content=' + Object.toJSON(content),
            'sync':     sync || false
        });
    },

    /*******************************************************
    UPDATE
    ********************************************************/

    //Periodical session update
    //-> (void)
    update: function()
    {
        var updates = {};
        switch(Kookiiz.MODE)
        {
            case 'full':
                updates =
                {
                    'user':     User.profile_times(),
                    'invits':   Invitations.export_times(),
                    'notifs':   0
                };
                break;
            case 'mobile':
                updates =
                {
                    'user': User.profile_times()
                };
                break;
        }
        this.load(updates, this.onUpdate.bind(this));
    }
});