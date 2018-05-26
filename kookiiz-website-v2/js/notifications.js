/*******************************************************
Title: Notifications
Authors: Kookiiz Team
Purpose: Display and manage session notifications
********************************************************/

//Represents a user interface for the notification system
var NotificationsUI = Class.create(
{
    object_name: 'notifications_ui',

    /*******************************************************
    CONSTANTS
    ********************************************************/

    //Notification types
    TYPES: ['friends', 'invitations', 'shared'],

    /*******************************************************
    CONSTRUCTOR
    ********************************************************/

    //Class constructor
    //-> (void)
    initialize: function()
    {
        //Notifications counters
        this.counters = {};
        for(var i = 0, imax = this.TYPES.length; i < imax; i++)
        {
            this.counters[this.TYPES[i]] = 0;
        }
    },
    
    /*******************************************************
    COUNT
    ********************************************************/

    //Decrease count for a given type
    //#type (string): notification type
    //-> (void)
    countDecrease: function(type)
    {
        this.setCount(type, this.getCount(type) - 1);
    },

    /*******************************************************
    DISPLAY
    ********************************************************/

    //Display current notifications counts
    //-> (void)
    display: function()
    {
        if(!user_logged()) return;

        var type, count, container, icon, counter;
        for(var i = 0, imax = this.TYPES.length; i < imax; i++)
        {
            type        = this.TYPES[i];
            count       = this.counters[type];
            container   = $('user_area_notifications').select('.container.' + type)[0];
            icon        = container.select('img.notification')[0].removeClassName('disabled');
            counter     = container.select('.counter')[0];
            if(count)
            {
                counter.innerHTML = count;
                counter.show();
            }
            else
            {
                icon.addClassName('disabled');
                counter.innerHTML = 0;
                counter.hide();
            }
        }
    },
    
    /*******************************************************
    GETTERS
    ********************************************************/
   
    //Get current notifications count for a given type
    //#type (string): notification type
    //->count (int): number of notifications of this type
    getCount: function(type)
    {
        if(user_logged())   
            return this.counters[type];
        else                
            return 0;
    },
    
    /*******************************************************
    IMPORT
    ********************************************************/

    //Load notifications from server
    //#notifications (object): number of notifications per type
    //-> (void)
    import_content: function(notifications)
    {
        var count;
        for(var type in this.counters)
        {
            count = parseInt(notifications[type]) || 0;
            if(count) this.counters[type] = count;
        }
        this.display();
    },
    
    /*******************************************************
    INIT
    ********************************************************/

    //Init dynamic functionalities
    //-> (void)
    init: function()
    {
        if(user_logged())
            $('user_area_notifications').select('.container').invoke('observe', 'click', this.onOpen.bind(this));
    },
    
    /*******************************************************
    OBSERVERS
    ********************************************************/
   
    //Called when a friend request action button is clicked
    //#event (event): DOM click event
    //-> (void)
    onFriendAction: function(event)
    {
        //Retrieve parameters
        var request_row = event.findElement('tr'),
            button = event.findElement('img.button15'),
            friend_id = parseInt(request_row.id.split('_')[2]);

        //Accept, block or deny friendship request
        if(button.hasClassName('accept'))         
            Kookiiz.friends.action(friend_id, 'add');
        else if(button.hasClassName('block'))     
            Kookiiz.friends.action(friend_id, 'block');
        else if(button.hasClassName('cancel'))    
            Kookiiz.friends.action(friend_id, 'remove');

        //Update notifications count
        this.countDecrease('friends');

        //Hide popup
        Kookiiz.popup.hide();
    },
   
    //Called when user clicks the invitation accept/deny buttons
    //#status (string): either "accept" or "deny"
    //#event (event):   DOM click event
    //-> (void)
    onInvitRespond: function(status, event)
    {
        var invitation_row = event.findElement('tr'),
            invitation_id  = parseInt(invitation_row.id.split('_')[2]);

        //Reload popup
        var parameters = 'action=' + status + '&invitation_id=' + invitation_id,
            callback   = this.onPopupReady.bind(this, 'invitations', true);
        Kookiiz.popup.reload(parameters, callback);

        //Update session data
        Kookiiz.session.update();
    },
   
    //Called when a notification button is clicked
    //#event (event): DOM click event
    //-> (void)
    onOpen: function(event)
    {
        var button = event.findElement(), type = '';
        this.TYPES.each(function(name)
        {
            if(button.hasClassName(name)) type = name;
        });
        if(type) 
            this.popup(type);
    },
    
    //Init popup functionalities
    //#type (string): type of notifications popup
    //#reload (bool): whether the popup content is being reloaded (defaults to false)
    //-> (void)
    onPopupReady: function(type, reload)
    {
        switch(type)
        {
            case 'friends':
                var requests_table = $('friends_requests_table');
                if(requests_table)
                    requests_table.select('img.accept, img.cancel').invoke('observe', 'click', this.onFriendAction.bind(this));
                break;

            case 'invitations':
                var requests_table = $('invitations_requests_table');
                if(requests_table)
                {
                    requests_table.select('img.accept').invoke('observe', 'click', this.onInvitRespond.bind(this, 'accept'));
                    requests_table.select('img.cancel').invoke('observe', 'click', this.onInvitRespond.bind(this, 'deny'));
                }
                if(reload)
                {
                    Kookiiz.tabs.show('share');
                    Kookiiz.invitations_load();
                }
                break;
                
            case 'shared':
                var recipes_table = $('shared_recipes_table');
                if(recipes_table)
                {
                    recipes_table.select('img.accept').invoke('observe', 'click', this.onRecipeView.bind(this));
                    recipes_table.select('img.cancel').invoke('observe', 'click', this.onRecipeClear.bind(this));
                    recipes_table.select('img.save').invoke('observe', 'click', this.onRecipeSave.bind(this));
                }
                var shopping_table = $('shared_shopping_table');
                if(shopping_table)
                {
                    shopping_table.select('img.accept').invoke('observe', 'click', this.onShoppingAccept.bind(this));
                    shopping_table.select('img.cancel').invoke('observe', 'click', this.onShoppingDeny.bind(this));
                }
                break;
        }
    },
    
    //Called when the user chooses to clear a shared recipe
    //#event (event): DOM click event
    //-> (void)
    onRecipeClear: function(event)
    {
        //Retrieve parameters
        var recipe_row = event.findElement('tr'),
            recipe_tbl = recipe_row.parentNode,
            friend_id = parseInt(recipe_row.select('input.sharer')[0].value),
            recipe_id = parseInt(recipe_row.id.split('_')[2]);

        //Cancel sharing
        Kookiiz.friends.unshare_recipe(friend_id, recipe_id, 'friend');

        //Remove row from table
        recipe_tbl.removeChild(recipe_row);

        //Check if there are no more shared recipes
        if(recipe_tbl.empty())
        {
            var container = $('shared_recipes_table').parentNode.clean();
            var caption = new Element('p', {'class': 'center'});
            caption.innerHTML = NOTIFICATIONS_TEXT[5];
            container.appendChild(caption);
        }
    },
    
    //Called when the user chooses to save a shared recipe
    //#event (event): DOM click event
    //-> (void)
    onRecipeSave: function(event)
    {
        //Retrieve parameters
        var recipe_row = event.findElement('tr'),
            recipe_id  = parseInt(recipe_row.id.split('_')[2]);

        //Add recipe to user's favorites
        User.favorites_add(recipe_id, true);
    },
    
    //Called when user chooses to view a shared recipe
    //#event (event): DOM click event
    //-> (void)
    onRecipeView: function(event)
    {
        //Retrieve parameters
        var recipe_row = event.findElement('tr'),
            recipe_id  = parseInt(recipe_row.id.split('_')[2]);

        //Close pop-up and display recipe
        Kookiiz.popup.hide();
        Kookiiz.tabs.show('recipe_full', recipe_id, Recipes.get(recipe_id, 'name'));
    },
    
    //Called when user accepts a shopping list sharing proposal
    //#event (event): DOM click event
    //-> (void)
    onShoppingAccept: function(event)
    {
        var shopping_row = event.findElement('tr'),
            shopping_tbl = shopping_row.parentNode,
            share_id = parseInt(shopping_row.select('input.share_id')[0].value);
        Kookiiz.friends.share_shopping_accept(share_id);

        //Remove row from table
        shopping_tbl.removeChild(shopping_row);

        //Check if there are no more shopping sharing requests
        if(shopping_tbl.empty())
        {
            var container = $('shared_shopping_table').parentNode.clean();
            var caption = new Element('p', {'class': 'center'});
            caption.innerHTML = NOTIFICATIONS_TEXT[7];
            container.appendChild(caption);
        }
    },

    //Called when user denies a shopping list sharing proposal
    //#event (event): DOM click event
    //-> (void)
    onShoppingDeny: function(event)
    {
        var shopping_row = event.findElement('tr'),
            shopping_tbl = shopping_row.parentNode,
            share_id = parseInt(shopping_row.select('input.share_id')[0].value);
        Kookiiz.friends.share_shopping_deny(share_id);

        //Remove row from table
        shopping_tbl.removeChild(shopping_row);

        //Check if there are no more shopping sharing requests
        if(shopping_tbl.empty())
        {
            var container = $('shared_shopping_table').parentNode.clean();
            var caption = new Element('p', {'class': 'center'});
            caption.innerHTML = NOTIFICATIONS_TEXT[7];
            container.appendChild(caption);
        }
    },

    /*******************************************************
    POPUP
    ********************************************************/

    //Open notifications popup for provided type
    //#type (string):   type of notifications popup
    //#params (string): parameters to pass to the popup
    //-> (void)
    popup: function(type, params)
    {
        params = params || '';

        //Retrieve parameters for current notification popup type
        var title = '', text = '', url = '';
        switch(type)
        {
            case 'friends':
                title = NOTIFICATIONS_ALERTS[0];
                text  = NOTIFICATIONS_ALERTS[1];
                url   = '/dom/notifications_friends_popup.php';
                break;

            case 'invitations':
                title = NOTIFICATIONS_ALERTS[2];
                text  = NOTIFICATIONS_ALERTS[3];
                url   = '/dom/notifications_invitations_popup.php';
                break;

            case 'shared':
                title = NOTIFICATIONS_ALERTS[4];
                text  = NOTIFICATIONS_ALERTS[5];
                url   = '/dom/notifications_shared_popup.php';
                this.setCount(type, 0);
                break;

            default:				
                return;
                break;
        }

        //Open notifications popup
        Kookiiz.popup.hide();
        Kookiiz.popup.custom(
        {
            'text':                 text,
            'title':                title,
            'confirm':              true,
            'content_url':          url,
            'content_parameters':   params,
            'content_init':         this.onPopupReady.bind(this, type)
        });
    },
    
    /*******************************************************
    SETTERS
    ********************************************************/
   
    //Update notification count for provided type
    //#type (string): notification type
    //#count (int):   number of notifications of current type (defaults to 0)
    //-> (void)
    setCount: function(type, count)
    {
        if(count < 0) count = 0;
        this.counters[type] = count;
        this.display();
    }
});