/*******************************************************
Title: Invitation
Authors: Kookiiz Team
Purpose: Define the invitation object
********************************************************/

//Represents an invitation
var Invitation = Class.create(
{
	object_name: 'invitation',

    /*******************************************************
    CONSTRUCTOR
    ********************************************************/

	//Class constructor
    //#id (int):            invitation unique ID
    //#parameters (object): invitation data structure (optional)
    //  #guests (array):    list of guest IDs
    //  #location (string): invitation location
    //  #recipes (array):   list of recipe IDs
    //  #text (string):     invitation message
    //  #time (object):     DateTime object
    //  #title (string):    invitation title
    //  #update (int):      last update timestamp
    //  #user_id (int):     ID of the author
    //-> (void)
	initialize: function(id, parameters)
    {
        this.id = id;

        //Init parameters
        this.guests     = parameters.guests     || [];
        this.location   = parameters.location   || '';
        this.recipes    = parameters.recipes    || [];
        this.status     = parameters.status     || INV_STATUS_NONE;
        this.text       = parameters.text       || '';
        this.title      = parameters.title      || '';
        this.update     = parameters.update     || 0;
        this.user_id    = parameters.user_id    || User.id;

        //Init date
        if(parameters.time)
            this.time = parameters.time;
        else
        {
            var now = new Date();
            now.setHours(INV_HOUR_DEFAULT);
            now.setMinutes(INV_MINUTE_DEFAULT);
            this.time = new DateTime(now, 'date');
        }

        //Status parameters
        this.editable   = (this.user_id == User.id ? 1 : 0);
        this.modified   = false;
    },

    /*******************************************************
    EXPORT
    ********************************************************/

    //Export invitation as a compact data structure
    //-> (void)
    export_content: function()
    {
        var invitation = 
        {
            'id':       this.id,
            'guests':   this.guests.map(function(g){return {'i': g.id, 's': g.status};}),
            'location': encodeURIComponent(this.location),
            'recipes':  this.recipes,
            'text':     encodeURIComponent(this.text),
            'time':     this.time.toTime(true),
            'title':    encodeURIComponent(this.title),
            'update':   this.update,
            'user_id':  this.user_id
        };
        return invitation;
    },

    /*******************************************************
    GUESTS
    ********************************************************/

    //Add a guest to the invitation
    //#user_id (int):   ID of the guest
    //#status (int):    guest status (optional)
    //-> (void)
    guests_add: function(user_id, status)
    {
        if(this.editable && Users.exist(user_id))
        {
            if(typeof(status) == 'undefined') 
                status = INV_STATUS_NONE;

            //Check if guest has already been added to the invitation
            for(var i = 0, imax = this.guests.length; i < imax; i++)
            {
                if(this.guests[i].id == user_id)
                {
                    Kookiiz.popup.alert({'text': INVITATIONS_ALERTS[0]});
                    return;
                }
            }

            //Add guest to the invitation
            this.guests.push({'id': user_id, 'status': status});
            this.status_update();
        }
    },

    //Remove guest from the invitation
    //#user_id (int): ID of the guest
    //-> (void)
    guests_remove: function(user_id)
    {
        if(this.editable)
        {
            //Loop through guests
            for(var i = 0, imax = this.guests.length; i < imax; i++)
            {
                if(this.guests[i].id == user_id)
                {
                    this.guests.splice(i, 1);
                    this.status_update();
                    break;
                }
            }
        }
    },

    //Get status of a given guest
    //#guest_id (int): ID of the guest
    //->status (int): current guest status
    guests_status_get: function(guest_id)
    {
        for(var i = 0, imax = this.guests.length; i < imax; i++)
        {
            if(this.guests[i].id == guest_id)
                return this.guests[i].status;
        }
        return false;
    },

    //Set status of a given guest
    //#guest_id (int):  ID of the guest
    //#status (int):    new guest status
    //-> (void)
    guests_status_set: function(guest_id, status)
    {
        for(var i = 0, imax = this.guests; i < imax; i++)
        {
            if(this.guests[i].id == guest_id)
            {
                this.guests[i].status = status;
                this.status_update();
                break;
            }
        }
    },

    /*******************************************************
    LOCATION
    ********************************************************/

    //Set invitation location
    //#location (string): new invitation location
    //-> (void)
	location_set: function(location)
    {
        if(this.editable)
        {
            this.location = location.stripTags();
            this.updated();
        }
    },

    /*******************************************************
    RECIPES
    ********************************************************/

	//Add a recipe to the invitation
    //#recipe_id (int): ID of the recipe
    //-> (void)
    recipes_add: function(recipe_id)
    {
        if(this.editable)
        {
            //Check if the recipe was already added
            for(var i = 0, imax = this.recipes.length; i < imax; i++)
            {
                if(this.recipes[i] == recipe_id)
                {
                    Kookiiz.popup.alert({'text': INVITATIONS_ALERTS[1]});
                    return;
                }
            }

            //Add recipe to the invitation
            this.recipes.push(recipe_id);
            this.updated();
        }
    },

    //Remove a recipe from the invitation
    //#recipe_id (int): ID of the recipe
    //-> (void)
    recipes_remove: function(recipe_id)
    {
        if(this.editable)
        {
            //Loop through recipes
            for(var i = 0, imax = this.recipes.length; i < imax; i++)
            {
                if(this.recipes[i] == recipe_id)
                {
                    //Remove recipe and update display
                    this.recipes.splice(i, 1);
                    this.updated();
                    break;
                }
            }
        }
    },

    /*******************************************************
    STATUS
    ********************************************************/

    //Set invitation status as "saved"
    //-> (void)
    saved: function()
    {
        this.modified = false;
    },

    //Set invitation as "sent" for all guests
    //-> (void)
    sent: function()
    {
        for(var i = 0, imax = this.guests.length; i < imax; i++)
        {
            if(this.guests[i].status == INV_STATUS_NONE)
                this.guests[i].status = INV_STATUS_SENT;
        }
        this.status = INV_STATUS_SENT;
        this.saved();
    },

    //Update current invitation status ("sent" or "not sent")
    //-> (void)
    status_update: function()
    {
        //Loop through guests list
        for(var i = 0, imax = this.guests.length; i < imax; i++)
        {
            //Check if at least one guest has "not sent" status
            if(this.guests[i].status == INV_STATUS_NONE)
            {
                this.status = INV_STATUS_NONE;
                this.updated();
                return;
            }
        }
        this.status = INV_STATUS_SENT;
        this.updated();
    },

    /*******************************************************
    TEXT
    ********************************************************/

    //Set invitation text
    //#text (string): new invitation text
    //-> (void)
    text_set: function(text)
    {
        if(this.editable)
        {
            this.text = text.stripTags();
            this.updated();
        }
    },

    /*******************************************************
    TIME
    ********************************************************/

    //Set invitation time
    //#datetime (object): datetime object
    //-> (void)
    time_set: function(datetime)
    {
        if(this.editable)
        {
            this.time = datetime;
            this.updated();
        }
    },

    /*******************************************************
    TITLE
    ********************************************************/

    //Set invitation title
    //#title (string): new title
    //-> (void)
    title_set: function(title)
    {
        if(this.editable)
        {
            this.title = title.stripTags();
            this.updated();
        }
    },

    /*******************************************************
    UPDATE
    ********************************************************/

   //Update invitation with provided content
    //#parameters (object): invitation parameters
    //-> (void)
    updateContent: function(parameters)
    {
        //Invitation created by current user
        if(this.editable)
        {
            //Server invitation is more recent
            if(this.update < parameters.update)
                this.update = parameters.update;

            //Update guests statuses
            var id, status, guest;
            for(var i = 0, imax = parameters.guests.length; i < imax; i++)
            {
                id      = parameters.guests[i].id;
                status  = parameters.guests[i].status;
                guest   = this.guests.find(function(g){return g.id == id;});
                if(guest)
                    guest.status = status;
            }
        }
        //Invitation received by current user
        else
            Object.extend(this, parameters || {});
    },

    //Set invitation as "updated" and modify timestamp
    //-> (void)
    updated: function()
    {
        this.modified   = true;
        this.update     = Time.get(true);
    }
});