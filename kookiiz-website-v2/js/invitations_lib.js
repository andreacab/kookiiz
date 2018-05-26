/*******************************************************
Title: Invitations library
Authors: Kookiiz Team
Purpose: Store and manage invitation objects
********************************************************/

//Represents a storage of invitations
var InvitationsLib = Class.create(Library,
{
    object_name: 'invitations_library',

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
    EXPORT
    ********************************************************/

    //Export pairs of invitation ID/timestamp
    //->updates (array): list of invitation ID/timestamp pairs
    export_times: function()
    {
        var updates = [];
        for(var i = 0, imax = this.library.length; i < imax; i++)
        {
            updates.push(
            {
                'i':    this.library[i].id,
                't':    this.library[i].update
            });
        }
        return updates;
    },

    /*******************************************************
    IMPORT
    ********************************************************/

    //Import invitations data in library
    //#invitations (array): list of invitation data structures
    //-> (void)
    import_content: function(invitations)
    {
        //Loop through invitations data
        var data, id, parameters, guests, guest_id,
            guest_status, status, existing;
        for(var i = 0, imax = invitations.length; i < imax; i++)
        {
            //Retrieve invitation parameters
            data    = invitations[i];
            id      = parseInt(data.id);

            //Parse guests data
            guests = [], status = INV_STATUS_SENT;
            for(var j = 0, jmax = data.guests.length; j < jmax; j++)
            {
                guest_id = parseInt(data.guests[j].i);
                if(Users.exist(guest_id))
                {
                    guest_status = parseInt(data.guests[j].s);
                    guests.push({id: guest_id, status: guest_status});
                    if(guest_status == INV_STATUS_NONE)
                    {
                        status = INV_STATUS_NONE;
                    }
                }
            }

            //Set-up parameters
            parameters =
            {
                guests:     guests,
                location:   data.location.stripTags(),
                recipes:    data.recipes.parse('int'),
                status:     status,
                text:       data.text.stripTags(),
                time:       new DateTime(parseInt(data.time) * 1000, 'timestamp'),
                title:      data.title.stripTags(),
                update:     parseInt(data.update),
                user_id:    parseInt(data.user_id)
            };

            //Check if invitation already exists in library
            existing = this.find(id);
            if(existing)    existing.updateContent(parameters);
            else            this.library.push(new Invitation(id, parameters));
        }

        //Update invitations UI
        Kookiiz.invitations.update();
    },

    /*******************************************************
    LOAD
    ********************************************************/

    //Load invitations data from server
    //-> (void)
    load: function()
    {
        var updates = this.export_times();
        Kookiiz.session.load({'invits': updates});
    }
});