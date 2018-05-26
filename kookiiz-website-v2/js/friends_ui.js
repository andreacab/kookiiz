/*******************************************************
Title: Friends UI
Authors: Kookiiz Team
Purpose: Functionalities of the friends user interface
********************************************************/

//Represents a user interface for friends-related functionalities
var FriendsUI = Class.create(
{
    object_name: 'friends_ui',

	/*******************************************************
    CONSTRUCTOR
    ********************************************************/

    //Class constructor
    //-> (void)
	initialize: function()
    {
        //DOM elements
        this.$list   = $('friends_list');
        this.$loader = $('friends_loader');

        //List of Draggable elements
        this.drags = $H();
    },

    /*******************************************************
    ACTIONS
    ********************************************************/

    //Report friend action to server
    //#friend_id (int): unique ID of the friend
    //#action (string): action ("add", "block", etc.)
    //-> (void)
    action: function(friend_id, action)
    {
        Kookiiz.api.call('friends', action,
        {
            'callback': this.parse.bind(this),
            'request':  'friend_id=' + friend_id
        });
    },

    //Called when user chooses to remove a friend
    //Ask for confirmation before performing the removal
    //#friend_id (int): unique ID of the friend
    //-> (void)
    remove: function(friend_id)
    {
        Kookiiz.popup.confirm(
        {
            'text':     FRIENDS_ALERTS[5],
            'callback': this.remove_confirm.bind(this, friend_id)
        });
    },

    //Called when user confirms or cancels removal process
    //#friend_id (int): unique ID of the friend
    //#confirm (bool):  true if user confirms his action
    //-> (void)
    remove_confirm: function(friend_id, confirm)
    {
        if(confirm)
        {
            //Remove friend DOM element from the list
            this.listRemove(friend_id);
            //Send removal request to server
            this.action(friend_id, 'remove');
        }
    },

    /*******************************************************
    ELEMENTS
    ********************************************************/

    //Callback for action on friend DOM element
    //#friend_id (int): friend ID
    //#action (string): user action
    //#params (object): action parameters (optional)
    //-> (void)
    element_action: function(friend_id, action, params)
    {
        switch(action)
        {
            case 'remove':
                this.remove(friend_id);
                break;
        }
    },

    //Callback for click on friend deletion icon
    //#friend_id (int): friend ID
    //#event (event):   DOM click event
    //-> (void)
    element_delete_click: function(friend_id, event)
    {
        this.element_action(friend_id, 'remove');
    },

    //Build friend DOM element from friend object
    //#friend (object): friend object
    //->element (DOM): friend DOM element (LI)
    element_build: function(friend)
    {
        //Item structure
        var element         = new Element('li', {'class': 'friend_item'});
        var friend_top      = new Element('div', {'class': 'top'});
        var friend_middle   = new Element('div', {'class': 'middle'});
        var friend_bottom   = new Element('div', {'class': 'bottom'});
        element.appendChild(friend_top);
        element.appendChild(friend_middle);
        element.appendChild(friend_bottom);

        //Handle
        var handle = new Element('div', {'class': 'handle'});
        friend_middle.appendChild(handle);

        //AVATAR
        var avatar = new Element('div');
        Kookiiz.users.displayPic(friend.user, avatar);
        friend_middle.appendChild(avatar);

        //NAME
        var friend_name = new Element('p', {'class': 'name'});
        friend_name.innerHTML = friend.user.name;
        friend_middle.appendChild(friend_name);

        //Status
        var status = friend.getStatus();
        var friend_status = new Element('img',
        {
            'alt':      status ? FRIENDS_STATUS[0] : FRIENDS_STATUS[1],
            'class':    'icon15 status' + (status ? ' connected' : ' disconnected'),
            'src':      ICON_URL,
            'title':    status ? FRIENDS_STATUS[0] : FRIENDS_STATUS[1]
        });
        friend_middle.appendChild(friend_status);

        //Delete icon
        var action_remove = new Element('img',
        {
            'alt':      FRIENDS_ACTIONS[1],
            'class':    'button15 cancel',
            'src':      ICON_URL,
            'title':    FRIENDS_ACTIONS[1]
        });
        var callback = this.element_delete_click.bind(this, friend.getID());
        action_remove.observe('click', callback);
        friend_middle.appendChild(action_remove);

        //Return element
        return element;
    },

    //Add drag & drop functionnalities to provided friend item
    //#element (DOM): friend DOM element
    //->draggable (object): friend draggable object
    element_dragdrop: function(element)
    {
        //Recipes can be dropped on friends for sharing purposes
        Droppables.add(element,
        {
            'accept':       ['recipe_box', 'recipe_item'],
            'hoverclass':   'hover',
            'onDrop':       this.recipe_drop.bind(this),
            'scrollParent': this.$list
        });

        //Friend box can be dragged on invitations
        return new Draggable(element,
        {
            'handle':               'handle',
            'revert':               true,
            'onStart':              this.element_drag_start.bind(this),
            'onEnd':                this.element_drag_end.bind(this),
            'reverteffect':         function(){return 0;},
            'endeffect':            function(){return 0;},
            'ghosting':             true,
            'scroll':               window,
            'scrollSensitivity':    50
        });
    },

    //Update DOM element status display
    //#element (DOM):   friend DOM element
    //#friend (object): corresponding friend object
    //-> (void)
    element_update: function(element, friend)
    {
        var status = friend.getStatus(), status_el = element.select('.status')[0];
        if(status)
            status_el.removeClassName('disconnected').addClassName('connected');
        else
            status_el.removeClassName('connected').addClassName('disconnected');
    },
    
    /*******************************************************
    INIT
    ********************************************************/

    //Init dynamic functionalities
    //-> (void)
    init: function()
    {
        //Panel
        if(!Kookiiz.panels.is_disabled('friends'))
        {
            $('friends_search_open').observe('click', this.search_popup.bind(this));
            this.$loader.loading();
        }
    },

    /*******************************************************
    LIST
    ********************************************************/

    //Create a new friend DOM element and assign it a unique ID
    //#friend (object): friend source object
    //->friend (object): friend DOM element
    listCreate: function(friend)
    {
        //Build friend DOM element
        var element = this.element_build(friend);
        element.id  = 'friend_item_' + friend.getID();
        //Create draggable and store it
        var drag = this.element_dragdrop(element);
        this.drags.set(friend.getID(), drag);
        //Return friend element
        return element;
    },

    //Display friends list
    //-> (void)
    listDisplay: function()
    {
        var friends = User.friends_get();
        if(friends.length)
        {
            //Sort friends
            friends.sort(this.sort.bind(this));

            //Retrieve or build friends list
            var friends_list = this.$list.select('ul')[0];
            if(!friends_list) friends_list = new Element('ul');

            //Loop through friends and update or create DOM elements
            var friends_ids = [], friend, friend_id,
                friend_el = null, previous_el = null;
            for(var i = 0, imax = friends.length; i < imax; i++)
            {
                friend      = friends[i];
                friend_id   = friend.getID();
                friend_el   = this.listGet(friend_id);

                //Update existing element
                if(friend_el)
                    this.element_update(friend_el, friend);
                //Create new element
                else
                {
                    friend_el = this.listCreate(friend);
                    //Insert element after previous one (if any) or at the end
                    if(previous_el)
                        previous_el.insert({'after': friend_el});
                    else
                        friends_list.appendChild(friend_el);
                }

                //Store friend key
                friends_ids.push(friend_id);
                //Update previous element pointer
                previous_el = friend_el;
            }

            //Remove deleted friends
            var current = this.listIDs();
            for(i = 0, imax = current.length; i < imax; i++)
            {
                friend_id = current[i];
                if(friends_ids.indexOf(friend_id) < 0)
                    this.listRemove(friend_id);
            }

            //Append friends list to container
            if(!friends_list.parentNode)
                this.$list.clean().appendChild(friends_list);
        }
        //No friends to display
        else
            this.$list.clean().innerHTML = FRIENDS_ALERTS[1];

        //Hide loader and display list
        this.$loader.hide();
        this.$list.show();
    },

    //Return friend DOM element from the list
    //#friend_id (int): ID of the friend to return
    //->element (DOM): corresponding DOM element (if any)
    listGet: function(friend_id)
    {
        return $('friend_item_' + friend_id);
    },

    //Return all friend IDs from the list
    //->friends_ids (array): list of friend IDs
    listIDs: function()
    {
        return $$('.friend_item').map(function(el)
        {
            return parseInt(el.id.split('_')[2]);
        });
    },

    //Remove a friend element from the list
    //#friend_id (int): unique friend ID
    //-> (void)
    listRemove: function(friend_id)
    {
        var element = $('friend_item_' + friend_id);
        //Remove droppable
        Droppables.remove(element);
        //Destroy draggable
        var drag = this.drags.unset(friend_id);
        if(drag) drag.destroy();
        //Remove DOM element
        element.remove();
    },

	/*******************************************************
    LOAD
    ********************************************************/

    //Load friends list for current user
    //-> (void)
    load: function()
    {
        this.$list.hide();
        this.$loader.show();
        User.profile_load(['friends']);
    },

    /*******************************************************
    PARSE
    ********************************************************/

    //Callback for all friends request to server
    //#response (object): response object from server
    //-> (void)
    parse: function(response)
    {
        //Take appropriate actions
        var action = response.parameters.action;
        switch(action)
        {
            //Friend added
            case 'add':
                var status = parseInt(response.parameters.status);
                switch(status)
                {
                    case FRIEND_STATUS_PENDING:
                        Kookiiz.popup.alert({'text': FRIENDS_ALERTS[7]});
                        break;
                    case FRIEND_STATUS_VALID:
                        Kookiiz.popup.alert({'text': FRIENDS_ALERTS[8]});
                        this.load();    //Reload friends list
                        break;
                }
                break;
            //Results returned from search for friends
            case 'search':
                this.search_display(response.content);
                break;
            //Sharing action
            case 'share':
                Kookiiz.events.reload();
                break;
        }
    },

    /*******************************************************
    SEARCH
    ********************************************************/

    //Search for users matching provided keyword
    //#keyword (string): string the name must match
    //#callback (function): function to call with the response
    //-> (void)
    search: function(keyword)
    {
        if(keyword && keyword != '')
        {
            Kookiiz.api.call('users', 'search',
            {
                'callback': this.parse.bind(this),
                'request':  'keyword=' + encodeURIComponent(keyword)
            });
        }
    },

    //Display search results
    //#data (object): search results returned by server
    //-> (void)
    search_display: function(data)
    {
        var container = $('friends_search_results').clean(),
            table = new Element('table'),
            table_content = new Element('tbody');
        table.appendChild(table_content);

        //Loop through search results
        var user_id, user_name, user_row, user_info, user_actions, action_add;
        for(var i = 0, imax = data.length; i < imax; i++)
        {
            user_id     = data[i].i;
            user_name   = data[i].n;

            user_row = new Element('tr',
            {
                'class':    'friend_search',
                'id':       'friend_search_' + user_id
            });

            user_info = new Element('td', {'class': 'info left'});
            user_info.innerHTML = user_name;
            user_row.appendChild(user_info);

            user_actions = new Element('td', {'class': 'icons center'});
            action_add = new Element('img',
            {
                'class':    'button15 plus',
                'src':      ICON_URL,
                'title':    FRIENDS_ACTIONS[0]
            });
            action_add.observe('click', this.add_click.bind(this));
            user_actions.appendChild(action_add);
            user_row.appendChild(user_actions);

            //Append search result to table
            table_content.appendChild(user_row);
        }

        if(table_content.empty())
            container.innerHTML = FRIENDS_ALERTS[1];
        else
        {
            //Create table header
            var header          = new Element('thead');
            var header_row      = new Element('tr');
            var header_info     = new Element('th', {'class': 'left'});
            var header_actions  = new Element('th', {'class': 'center'});
            header_info.innerHTML       = FRIENDS_TEXT[2];
            header_actions.innerHTML    = ACTIONS[14];
            header_row.appendChild(header_info);
            header_row.appendChild(header_actions);
            header.appendChild(header_row);
            table.insertBefore(header, table_content);

            //Append table to container
            container.appendChild(table);
        }
    },

    //Called when friend search popup is loaded
    //-> (void)
    search_init: function()
    {
        Utilities.observe_return('input_friends_search', this.search_throw.bind(this))
        $('icon_friends_search').observe('click', this.search_throw.bind(this));
        $('friends_search_fb').observe('click', this.search_fbclick.bind(this));
    },

    //Open friend search popup
    //-> (void)
    search_popup: function()
    {
        Kookiiz.popup.custom(
        {
            'text':         FRIENDS_TEXT[1],
            'title':        FRIENDS_TEXT[0],
            'confirm':      true,
            'cancel':       true,
            'content_url':  '/dom/friends_search_popup.php',
            'content_init': this.search_init.bind(this)
        });
    },

    //Throw a new friend search
    //-> (void)
    search_throw: function()
    {
        var input = $('input_friends_search'),
            term  = input.value.stripTags();

        //Check if typed term is not empty
        if(term && term != input.title)
        {
            //Clear input and display loader
            input.value = '';
            $('friends_search_results').loading(true);

            //Search for friends
            this.search(term);
        }
    },
    
    /*******************************************************
    SHARING
    ********************************************************/

    //Share provided recipe with provided friend
    //#friend_id (int): ID of the friend
    //#recipe_id (int): ID of the recipe
    //-> (void)
    share_recipe: function(friend_id, recipe_id)
    {
        //Save sharing action on server
        Kookiiz.api.call('friends', 'share',
        {
            'callback': this.parse.bind(this),
            'request':  'content_type=recipe'
                        + '&friend_id=' + friend_id
                        + '&recipe_id=' + recipe_id
        });
    },

    //Share provided shopping list with provided friend
    //#friend_id (int): ID of the friend with whom to share the list
    //#day (int):       day of the menu that contains the shopping list
    //-> (void)
    share_shopping: function(friend_id, day)
    {
        //Share shopping list
        User.menu.shopping_share(day, friend_id);

        //Save sharing action on server
        Kookiiz.api.call('friends', 'share',
        {
            'callback': this.parse.bind(this),
            'request':  'content_type=shopping'
                        + '&friend_id=' + friend_id
                        + '&shopping_date=' + Time.datecode_get(day)
        });
    },

    //Accept shopping sharing proposal from friend
    //#share_id (int): unique ID of the sharing action
    //-> (void)
    share_shopping_accept: function(share_id)
    {
        Kookiiz.api.call('friends', 'share_accept',
        {
            'request':  'content_type=shopping'
                        + '&share_id=' + share_id
        });
    },

    //Deny shopping sharing proposal from friend
    //#share_id (int): unique ID of the sharing action
    //-> (void)
    share_shopping_deny: function(share_id)
    {
        Kookiiz.api.call('friends', 'share_deny',
        {
            'request':  'content_type=shopping'
                        + '&share_id=' + share_id
        });
    },

    //Share textual status with friends
    //#type (int):          type of status
    //#content_id (int):    ID of related content
    //#comment (string):    status comment
    //#summary (string):    status as a sentence (for social network sharing)
    //#networks (object):   structure indicating on which social networks status should be shared
    //-> (void)
    share_status: function(type, content_id, comment, summary, networks)
    {
        Kookiiz.api.call('friends', 'share',
        {
            'callback': this.parse.bind(this),
            'request':  'content_type=status'
                        + '&status_type=' + type
                        + '&content_id=' + content_id
                        + '&comment=' + encodeURIComponent(comment)
                        + '&summary=' + encodeURIComponent(summary)
                        + '&networks=' + Object.toJSON(networks)
        });
    },

    //Cancel recipe sharing
    //#friend_id (int):         ID of the friend
    //#recipe_id (int):         ID of the recipe
    //#canceled_by (string):    is cancel action triggered by "user" (sharer) or "friend" (receiver)
    //-> (void)
    unshare_recipe: function(friend_id, recipe_id, canceled_by)
    {
        //Send request to cancel recipe sharing
        Kookiiz.api.call('friends', 'unshare',
        {
            'request':  'content_type=recipe'
                        + '&canceled_by=' + canceled_by
                        + '&recipe_id=' + recipe_id
                        + '&friend_id=' + friend_id
        });
    },

    //Called when user chooses to cancel shopping sharing with a friend
    //#friend_id (int): ID of the friend with whom the list was shared
    //#day (int):       the day of the menu that contains the shopping list
    //-> (void)
    unshare_shopping: function(friend_id, day)
    {
        Kookiiz.popup.confirm(
        {
            'text':     FRIENDS_ALERTS[6],
            'callback': this.unshare_shopping_confirm.bind(this, friend_id, day, 'user')
        });
    },

    //Called when user cancels or confirms "unshare" action
    //#friend_id (int):         ID of the friend with whom the list was shared
    //#day (int):               day of the menu that contains the shopping list
    //#canceled_by (string):    is cancel action triggered by "user" (sharer) or "friend" (receiver)
    //#confirm (bool):          true if the action is confirmed
    //-> (void)
    unshare_shopping_confirm: function(friend_id, day, canceled_by, confirm)
    {
        if(confirm)
        {
            //Cancel sharing
            if(canceled_by == 'user')
                User.menu.shopping_unshare(day, friend_id);

            //Send request to cancel sharing
            Kookiiz.api.call('friends', 'unshare',
            {
                'request':  'content_type=shopping'
                            + '&canceled_by=' + canceled_by
                            + '&friend_id=' + friend_id
                            + '&shopping_date=' + Time.datecode_get(day)
            });
        }
    },

    /*******************************************************
    SORT
    ********************************************************/

    //Sort friends by name
    //#friend_a (object): first friend to sort
    //#friend_b (object): second friend to sort
    //->sorting (int): -1 (a before b), 0 (no sorting), 1 (a after b)
    sort: function(friend_a, friend_b)
    {
        var stat_a = friend_a.getStatus(),
            stat_b = friend_b.getStatus(),
            name_a = friend_a.user.name,
            name_b = friend_b.user.name;
        return stat_a > stat_b ? -1 : (name_a < name_b ? -1 : 1);
    },

    /*******************************************************
    UPDATE
    ********************************************************/

    //To be called after friends content has been updated
    //-> (void)
    update: function()
    {
        this.listDisplay();
    },

    /*******************************************************
    CALLBACKS - ACTIONS
    ********************************************************/

    //Callback for click on the icon to add a new friend
    //#event (event): DOM click event
    //-> (void)
    add_click: function(event)
    {
        var friend_row  = event.findElement('.friend_search');
        var friend_id   = friend_row.id.split('_')[2];
        this.action(friend_id, 'add');
        Kookiiz.popup.hide();
    },

    //Called when a recipe has been dropped on a friend
    //#recipe_box (DOM):    DOM element of the recipe box
    //#friend_box (DOM):    DOM element of the friend box
    //#mouse_x (int):       horizontal position of the mouse cursor at time of drop
    //#mouse_y (int):       vertical position of the mouse cursor at time of drop
    //-> (void)
    recipe_drop: function(recipe_box, friend_box, mouse_x, mouse_y)
    {
        var friend_id = parseInt(friend_box.id.split('_')[2]);
        var recipe_id = parseInt(recipe_box.id.split('_')[2]);
        this.share_recipe(friend_id, recipe_id);
    },

    //Called when a Facebook friend is clicked on the search popup
    //#event (event): Prototype event object
    //-> (void)
    search_fbclick: function(event)
    {
        var fb_friend = event.findElement('.fbfriend_search');
        if(fb_friend)
        {
            var friend_id = parseInt(fb_friend.id.split('_')[2]);
            this.action(friend_id, 'add');
            Kookiiz.popup.hide();
        }
    },

    /*******************************************************
    CALLBACKS - ELEMENTS
    ********************************************************/

    //Called right after friend box is dropped
    //#draggable (object): scriptaculous draggable object
    //-> (void)
    element_drag_end: function(draggable)
    {
        var element = draggable.element,
            delta = draggable.currentDelta(),
            top_offset = delta[1] - draggable.delta[1],
            left_offset = delta[0] - draggable.delta[0];

        if(draggable.options.ghosting)
            this.element_drag_finish(draggable);
        else
        {
            new Effect.Fade(element,
            {
                'duration':     0.2,
                'queue':        {'position': 'end', 'scope': element.id},
                'afterFinish':  this.element_drag_finish.bind(this, draggable)
            });
            new Effect.Move(element,
            {
                'x':        -left_offset,
                'y':        -top_offset,
                'duration': 0.1,
                'queue':    {'position': 'end', 'scope': element.id}});
            new Effect.Appear(element,
            {
                'duration': 0,
                'queue':    {'position': 'end', 'scope': element.id}
            });
        }
    },

    //Called after dragging and effects are over
    //#draggable (object): scriptaculous draggable object
    //-> (void)
    element_drag_finish: function(draggable)
    {
        //Fix Scriptaculous bugs
        draggable_fix_stop(draggable);

        //Remove "drag" class name
        draggable.element.removeClassName('drag');

        //Enable recipe preview
        Kookiiz.recipes.preview_on();
    },

    //Called when friend drag starts
    //#draggable (object): scriptaculous draggable object
    //-> (void)
    element_drag_start: function(draggable)
    {
        //Fix Scriptaculous bugs
        draggable_fix_start(draggable);

        //Add "drag" class name
        draggable.element.addClassName('drag');

        //Turn off recipe preview
        Kookiiz.recipes.preview_off();
    }
});