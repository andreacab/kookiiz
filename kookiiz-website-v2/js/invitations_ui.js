/*******************************************************
Title: Invitations UI
Authors: Kookiiz Team
Purpose: Create and send invitations between users
********************************************************/

//Represents a user interface for interactions with invitation objects
var InvitationsUI = Class.create(
{
    object_name: 'invitations_ui',

    /*******************************************************
    CONSTRUCTOR
    ********************************************************/

    //Class constructor
    //-> (void)
    initialize: function()
    {
        this.dateSel = null;    //Date selector
        
        //DOM elements
        this.BUTTON_CANCEL  = $('invitation_cancel');
        this.BUTTON_GIVEUP  = $('invitation_giveup');
        this.BUTTON_NEW     = $('invitation_new');
        this.BUTTON_SAVE    = $('invitation_save');
        this.BUTTON_SEND    = $('invitation_send');
        this.DISPLAY        = $('invitation_content');
        this.LOADER         = $('invitation_loader');
        this.MENU           = $('invitation_menu');
        this.MENU_CONTENT   = $('invitation_menu_content');
        this.SELECT         = $('select_invitation');
        this.TABLE          = $('invitation_table');
        
        this.$title         = $('invitation_title');
        this.$title_input   = $('invitation_title_input');
        this.$datetime      = $('invitation_datetime');
        this.$date          = $('invitation_date');
        this.$time          = $('invitation_time');
        this.$text          = $('invitation_text');
        this.$text_input    = $('invitation_text_input');
    },

    /*******************************************************
    INIT
    ********************************************************/

    //Init dynamic functionalities
    //-> (void)
    init: function()
    {
        //Panel
        if(!Kookiiz.panels.is_disabled('invitations'))
        {
            //Observers
            this.SELECT.observe('change', this.select_change.bind(this));
            this.BUTTON_CANCEL.observe('click', this.cancel_click.bind(this));
            this.BUTTON_GIVEUP.observe('click', this.giveup_click.bind(this));
            this.BUTTON_NEW.observe('click', this.new_click.bind(this));
            this.BUTTON_SAVE.observe('click', this.save_click.bind(this));
            this.BUTTON_SEND.observe('click', this.send_click.bind(this));
        }
    },

    /*******************************************************
    CREATE
    ********************************************************/

    //Create a new invitation
    //-> (void)
    create: function()
    {
        //Display loader
        this.DISPLAY.hide();
        this.LOADER.show();

        //Send request to create invitation
        Kookiiz.api.call('invitations', 'create',
        {
            'callback': this.parse.bind(this)
        });
    },

    /*******************************************************
    CANCEL
    ********************************************************/

    //Cancel an invitation
    //#inv (object): invitation object
    //-> (void)
	cancel: function(inv)
    {
        //Send request to delete invitation from database
        Kookiiz.api.call('invitations', 'delete',
        {
            'request':  'invitation_id=' + inv.id
        });

        //Remove invitation from library
        Invitations.remove(inv.id);

        //Update invitations UI
        this.update();
    },

    //Called when invitation cancel button is clicked
    //-> (void)
    cancel_click: function()
    {
        if(this.current.editable)
        {
            Kookiiz.popup.confirm(
            {
                'text':     INVITATIONS_ALERTS[6],
                'callback': this.cancel_confirm.bind(this)
            });
        }
    },

    //Called when user cancels or confirms the invitation cancelation process
    //#confirm (bool): true if user confirms the cancellation
    //-> (void)
    cancel_confirm: function(confirm)
    {
        if(confirm)
            this.cancel(this.current);
    },
    
    /*******************************************************
    DISPLAY
    ********************************************************/

	//Display invitation on side panel
    //#inv (object): invitation object to display
    //-> (void)
    display: function(inv)
    {
        //Remove listeners
        Droppables.remove('invitation_table_droppable');
        this.$title.stopObserving('click').removeClassName('click');
        this.$datetime.stopObserving('mouseenter').removeClassName('click');
        this.$text.stopObserving('click').removeClassName('click');

        //Display textual information
        this.$title.innerHTML = inv.title || INVITATIONS_TEXT[6];
        this.$text.innerHTML  = inv.text || PANEL_INVITATIONS_TEXT[7];
        this.$date.innerHTML  = inv.time.day + '.' + inv.time.month + '.' + inv.time.year;
        this.$time.innerHTML  = inv.time.hour + 'h' + inv.time.minute;

        //Invitation is editable (created by current user)
        if(inv.editable)
        {
            //Add observers
            this.$title.addClassName('click');
            this.$datetime.addClassName('click');
            this.$text.addClassName('click');
            this.$title.observe('click', this.title_edit_click.bind(this));
            this.$datetime.observe('mouseenter', this.onTimeHover.bind(this));
            this.$text.observe('click', this.text_edit_click.bind(this));
            
            //Make table droppable
            Droppables.add('invitation_table_droppable',
            {
                'accept':       ['friend_box', 'friend_item', 'recipe_box', 'recipe_item'],
                'hoverclass':   'hover',
                'onDrop':       this.object_drop.bind(this)
            });

            //Display appropriate buttons
            this.BUTTON_GIVEUP.hide();
            [this.BUTTON_SAVE, this.BUTTON_SEND, this.BUTTON_CANCEL].invoke('show');
            if(inv.modified)                    
                this.BUTTON_SAVE.unfreeze();
            else                                
                this.BUTTON_SAVE.freeze();
            if(inv.status == INV_STATUS_SENT)   
                this.BUTTON_SEND.freeze();
            else                                
                this.BUTTON_SEND.unfreeze();
        }
        //Invitation is not editable (received from someone else)
        else
        {
            [this.BUTTON_SAVE, this.BUTTON_SEND, this.BUTTON_CANCEL].invoke('hide');
            this.BUTTON_GIVEUP.show();
        }

        //Display guests and recipes
        this.display_guests(inv);
        this.display_recipes(inv);
    },

    //Display related guests
    //#inv (object): invitation object
    //-> (void)
    display_guests: function(inv)
    {
        //Remove existing guests
        this.TABLE.select('.invitation_guest').invoke('remove');

        //Add host to guests
        var guests = [{'id': inv.user_id, 'status': INV_STATUS_HOST}].concat(inv.guests);

        //Set proper table length
        var guests_count = guests.length,
            table_height = Math.ceil(Math.max(1, (guests_count - 4) / 2)) * 55;
        $('invitation_table_middle').setStyle({'height': table_height + 'px'});

        //Loop through guests
        var id, status, host, user, name, side, top,
            guest_element, avatar_wrapper, status_square,
            guest_name, guest_name_left, guest_name_middle,
            guest_name_right, guest_delete;
        for(var i = 0; i < guests_count; i++)
        {
            //Retrieve guest info
            id     = guests[i].id;
            status = guests[i].status;
            host   = status == INV_STATUS_HOST;
            user   = Users.get(id);
            name   = (id == User.id ? USER_TEXT[0] : user.name);
            side   = i % 2 ? 'right' : 'left';
            top    = Math.floor(i / 2) * 55;

            //Create guest element and set its position
            guest_element = new Element('div',
            {
                'class':    'invitation_guest ' + side + (host ? ' host' : ''),
                'id':       'invitation_guest_' + id
            });
            guest_element.setStyle({'top': top + 'px'});

            //Avatar
            Kookiiz.users.displayPic(user, guest_element);

            //Status
            avatar_wrapper = guest_element.select('.avatar_wrapper')[0];
            status_square = new Element('div', {'class': 'status'});
            if(status == INV_STATUS_SENT)
                status_square.addClassName('back_color2');
            else if(status == INV_STATUS_ACCEPT)
                status_square.addClassName('back_color1');
            else if(status == INV_STATUS_DENY)
                status_square.addClassName('back_color3');
            avatar_wrapper.appendChild(status_square);

            //Name
            guest_name        = new Element('div', {'class': 'name center'});
            guest_name_left   = new Element('div', {'class': 'left'});
            guest_name_middle = new Element('div', {'class': 'middle text_color0 bold'});
            guest_name_right  = new Element('div', {'class': 'right'});
            if(side == 'left')
            {
                guest_name.appendChild(guest_name_left);
                guest_name.appendChild(guest_name_middle);
                guest_name.appendChild(guest_name_right);
            }
            else
            {
                guest_name.appendChild(guest_name_right);
                guest_name.appendChild(guest_name_middle);
                guest_name.appendChild(guest_name_left);
            }
            guest_name_middle.innerHTML = name + (host ? ' (' + INVITATIONS_TEXT[15] + ')' : '');
            guest_element.appendChild(guest_name);

            //Actions
            if(inv.user_id == User.id && id != User.id)
            {
                //Delete icon
                guest_delete = new Element('img',
                {
                    'alt':      INVITATIONS_ACTIONS[0],
                    'class':    'button15 cancel',
                    'src':      ICON_URL,
                    'title':    INVITATIONS_ACTIONS[0]
                });
                guest_delete.observe('click', this.guest_remove_click.bind(this));
                guest_element.appendChild(guest_delete);
            }

            //Add guest to table
            this.TABLE.appendChild(guest_element);
        }
    },

    //Display invitation recipes
    //#inv (object): invitation object
    //-> (void)
    display_recipes: function(inv)
    {
        Kookiiz.recipes.elements_build(this.MENU_CONTENT, inv.recipes, 'invitation',
        {
            'deletable':    true,
            'preview':      true,
            'callback':     this.recipe_element_action.bind(this),
            'deleteText':   ACTIONS_LONG[5]
        });

        if(this.MENU_CONTENT.empty())
            this.MENU.hide();
        else
            this.MENU.show();
    },

    //Called when invitation display changes
    //-> (void)
    display_update: function()
    {
        this.LOADER.hide();

        //Display currently selected invitation
        var inv_id      = parseInt(this.SELECT.value);
        var invitation  = Invitations.get(inv_id);
        if(invitation)
        {
            this.current = invitation;
            this.display(invitation);
            this.DISPLAY.show();
        }
        //No invitation selected
        else
        {
            this.current = null;
            this.DISPLAY.hide();
            this.BUTTON_SAVE.hide();
            this.BUTTON_SEND.hide();
            this.BUTTON_CANCEL.hide();
            this.BUTTON_GIVEUP.hide();
        }
    },
    
    /*******************************************************
    GIVE UP
    ********************************************************/

    //Called when user clicks on invitation giveup button
    //-> (void)
    giveup_click: function()
    {
        if(!this.current.editable)
        {
            Kookiiz.popup.confirm(
            {
                'text':     INVITATIONS_ALERTS[9],
                'callback': this.giveup_confirm.bind(this)
            });
        }
    },

    //Called when user cancels or confirms giveup action
    //#confirm (bool): true if user confirms action
    //-> (void)
    giveup_confirm: function(confirm)
    {
        if(confirm)
            this.respond(this.current, false);
    },
    
    /*******************************************************
    GUESTS
    ********************************************************/

    //Called when a guest removal button is clicked
    //#event (event): DOM click event
    //-> (void)
    guest_remove_click: function(event)
    {
        var guest_el = event.findElement('.invitation_guest'),
            guest_id = parseInt(guest_el.id.split('_')[2]);

        //Display confirmation popup
        Kookiiz.popup.confirm(
        {
            'text':     INVITATIONS_ALERTS[10],
            'callback': this.guest_remove_confirm.bind(this, guest_id)
        });
    },

    //Called once user confirms or cancels guest removal
    //#guest_id (int): ID of the guest
    //#confirm (bool): true if user confirms the removal
    //-> (void)
    guest_remove_confirm: function(guest_id, confirm)
    {
        if(confirm)
        {
            //Remove guest from invitation
            this.current.guests_remove(guest_id);
            this.display_update();
        }
    },
    
    /*******************************************************
    OBSERVERS
    ********************************************************/
   
    //Called when new date and time are validated by user
    //Update invitation date/time property
    //-> (void)
    onTimeConfirm: function()
    {
        //Retrieve input values
        var day = this.dateSel.getDay(),
            mon = this.dateSel.getMonth(),
            yea = this.dateSel.getYear(),
            hou = this.dateSel.getHour(),
            min = this.dateSel.getMinute(),
            date = new Date(yea, mon - 1, day, hou, min, 0);
        
        //Set invitation date/time and update display
        this.current.time_set(new DateTime(date, 'date'));
        this.display(this.current);
        
        //Hide hover
        Kookiiz.hover.hide(true);
    },
   
    //Called when date/time is hovered on editable invitation
    //Display hover area for edition
    //-> (void)
    onTimeHover: function()
    {
        //DOM elements
        var content = new Element('div',
            {
                'id': 'invitation_date_sel'
            }),
            ok_icon = new Element('img', 
            {
                'alt':      ACTIONS[2],
                'class':    'icon15 click accept',
                'src':      ICON_URL,
                'title':    ACTIONS[2]
            });
            
        //Build date/time selector
        this.dateSel = new DateSelector();
        this.dateSel.set(this.current.time);
        this.dateSel.display(content);
        
        //Validation icon
        content.appendChild(ok_icon);
        ok_icon.observe('click', this.onTimeConfirm.bind(this));
        
        //Display hover      
        Kookiiz.hover.on(this.$datetime, content, 200);
    },

    /*******************************************************
    PARSE
    ********************************************************/

    //Parse invitation content received from server
    //#response (object): server response object
    //-> (void)
    parse: function(response)
    {
        var invitation_id = parseInt(response.parameters.invitation_id),
            action = response.parameters.action, invitation;
        switch(action)
        {
            //After invitation creation
            case 'create':
                var parameters = {'update': parseInt(response.parameters.time)};
                invitation = new Invitation(invitation_id, parameters);
                Invitations.store(invitation);

                //Update UI and select new invitation
                this.update();
                this.select(invitation_id);
                break;
                
            //After an invitation has been saved
            case 'save':
                var send = parseInt(response.parameters.send);
                var time = parseInt(response.parameters.time);

                //Update invitation timestamp
                invitation = Invitations.get(invitation_id);
                invitation.timestamp = time;

                //Display confirmation
                if(send)    
                    Kookiiz.popup.alert({'text': INVITATIONS_ALERTS[3]});
                else        
                    Kookiiz.popup.alert({'text': INVITATIONS_ALERTS[2]});
                break;
        }
    },
    
    /*******************************************************
    RESPOND
    ********************************************************/

    //Respond to current invitation
    //#inv (object):    invitation object
    //#accept (bool):   true if the invitation is accepted
    //-> (void)
    respond: function(inv, accept)
    {
        var status = accept ? INV_STATUS_ACCEPT : INV_STATUS_DENY;

        //Send request to update guest status
        Kookiiz.api.call('invitations', 'respond',
        {
            'request': 'invitation_id=' + inv.id + '&status=' + status
        });

        //Remove or update invitation
        if(status == INV_STATUS_DENY)
        {
            Invitations.remove(inv.id);
            this.update();
        }
        else
        {
            inv.guests_status_set(User.id, status);
            this.update_display();
        }
    },

    /*******************************************************
    SAVE
    ********************************************************/

    //Save current invitation in database
    //#inv (object):    invitation object
    //#send (bool):     specifies if the invitation should be sent as well
    //-> (void)
    save: function(inv, send)
    {
        if(typeof(send) == 'undefined') send = false;

        //Send request for invitation saving
        Kookiiz.api.call('invitations', 'save',
        {
            'callback': this.parse.bind(this),
            'request':  'invitation_id=' + inv.id
                        + '&send=' + (send ? 1 : 0)
                        + '&invitation=' + Object.toJSON(inv.export_content())
        });

        //Set invitation as "sent" or "saved"
        if(send)
            inv.sent();
        else
            inv.saved();
    },

    /*******************************************************
    SELECT
    ********************************************************/

    //Select a given invitation for display
    //#inv_id (int): invitation ID
    //-> (void)
    select: function(inv_id)
    {
        this.SELECT.value_set(inv_id);
        this.display_update();
    },

    //Update content of the invitation select menu
    //-> (void)
    select_update: function()
    {
        var selected = this.SELECT.value;
        this.SELECT.clean();

        //Create invitations groups
        var inv_created  = new Element('optgroup', {'label': INVITATIONS_TEXT[16], 'class': 'created'});
        var inv_received = new Element('optgroup', {'label': INVITATIONS_TEXT[17], 'class': 'received'});

        //Loop through invitations
        var invitations = Invitations.getAll();
        if(invitations.length)
        {
            invitations.sort(this.sort.bind(this));

            var inv, inv_option;
            for(var i = 0, imax = invitations.length; i < imax; i++)
            {
                inv = invitations[i];

                inv_option = new Element('option', {'value': inv.id + (inv.temp ? '_temp' : '')});
                inv_option.innerHTML = inv.title || PANEL_INVITATIONS_TEXT[1];
                if(inv.editable)
                    inv_created.appendChild(inv_option);
                else
                    inv_received.appendChild(inv_option);
            }
            if(!inv_created.empty())
                this.SELECT.appendChild(inv_created);
            if(!inv_received.empty())
                this.SELECT.appendChild(inv_received);
        }
        else
        {
            var option_none = new Element('option', {'class': 'italic', 'value': -1});
            option_none.innerHTML = INVITATIONS_ALERTS[4];
            this.SELECT.appendChild(option_none);
        }

        //Restore selected index
        var restored = false;
        if(selected)
        {
            var index = this.SELECT.value_search(selected);
            if(index >= 0)
            {
                this.SELECT.selectedIndex   = index;
                restored                    = true;
            }
        }
        if(!restored)
            this.SELECT.selectedIndex = 0;
    },

    /*******************************************************
    SORT
    ********************************************************/

    //Sort invitations by date
    //#inv_a (object): first invitation to sort
    //#inv_b (object): second invitation to sort
    //->sorting (int): -1 (a before b), 0 (no sorting), 1 (a after b)
    sort: function(inv_a, inv_b)
    {
        var time_a = inv_a.time;
        var time_b = inv_b.time;
        var date_a = new Date(time_a.year, time_a.monthnum, time_a.daynum, time_a.hournum, time_a.minutenum);
        var date_b = new Date(time_b.year, time_b.monthnum, time_b.daynum, time_b.hournum, time_b.minutenum);
        return Time.dates_sort(date_a, date_b);
    },
    
    /*******************************************************
    UPDATE
    ********************************************************/

    //Update everything
    //-> (void)
    update: function()
    {
        this.select_update();
        this.display_update();
    },

    /*******************************************************
    CALLBACKS
    ********************************************************/

    //Called when the location is clicked
    //-> (void)
    location_edit_click: function()
    {
        $('input_invitation_location').swap('invitation_location', this.location_validate_click.bind(this));
    },

    //Called when new location is validated
    //-> (void)
    location_validate_click: function()
    {
        var location = $('invitation_location');
        $('input_invitation_location').swap(location, this.location_edit_click.bind(this));
        if(this.current.location != location.innerHTML)
        {
            this.current.location_set(location.innerHTML);
            this.display_update();
        }
    },

    //Called when invitation new button is clicked
    //-> (void)
    new_click: function()
    {
        this.create();
    },

    //Called when an object is dropped on the invitation table
    //#item (DOM):      element that was dropped
    //#table (DOM):     invitation table DOM element
    //#mouse_x (int):   horizontal mouse position at time of drop
    //#mouse_y (int):   vertical mouse position at time of drop
    //-> (void)
    object_drop: function(item, table, mouse_x, mouse_y)
    {
        //A friend box has been dropped
        if(item.hasClassName('friend_box') || item.hasClassName('friend_item'))
        {
            var friend_id = parseInt(item.id.split('_')[2]);
            this.current.guests_add(friend_id);
        }
        //A recipe box has been dropped
        else if(item.hasClassName('recipe_box') || item.hasClassName('recipe_item'))
        {
            var recipe_id = parseInt(item.id.split('_')[2]);
            this.current.recipes_add(recipe_id, Recipes.get(recipe_id, 'name'));
        }
        this.display_update();
    },

    //Callback for user action on recipe DOM element
    //#recipe_id (int): unique recipe ID
    //#action (string): user action
    //-> (void)
    recipe_element_action: function(recipe_id, action)
    {
        switch(action)
        {
            case 'delete':
                this.current.recipes_remove(recipe_id);
                this.display_update();
                break;
        }
    },

    //Called when invitation save button is clicked
    //-> (void)
    save_click: function()
    {
        if(this.current.editable && this.current.modified)
        {
            this.save(this.current);
            this.display_update();
        }       
    },

    //Invitation selection changed
    //-> (void)
    select_change: function()
    {
        this.display_update();
    },

    //Called when invitation send button is clicked
    //-> (void)
    send_click: function()
    {
        if(this.current.editable && this.current.status == INV_STATUS_NONE)
        {
            this.save(this.current, true);
            this.display_update();
        } 
    },

    //Called when the invitation message is clicked
    //Swap text for input
    //-> (void)
    text_edit_click: function()
    {
        this.$text_input.swap(this.$text, this.text_validate_click.bind(this)).focus();
        if(this.$text_input.value == this.$text_input.title)
            this.$text_input.value = '';
    },

    //Called when new invitation message is validated
    //-> (void)
    text_validate_click: function()
    {
        //Swap input for text
        this.$text_input.swap(this.$text, this.text_edit_click.bind(this));

        //Update current invitation text
        if(this.$text_input.value != this.$text_input.title
            && this.current.text != this.$text.innerHTML)
        {
            this.current.text_set(this.$text.innerHTML);
            this.update();
        }
    },

    //Called when user confirms or cancels time modification
    //#confirm (bool): true if user confirms modification
    //-> (void)
    time_confirm: function(confirm)
    {
        if(confirm)
        {
            //Retrieve date components
            var minute  = parseInt($('invitation_minute').value);
            var hour    = parseInt($('invitation_hour').value);
            var day     = parseInt($('invitation_day').value);
            var month   = parseInt($('invitation_month').value);
            var year    = parseInt($('invitation_year').value);

            //Set invitation time
            var datetime = new DateTime(new Date(year, month - 1, day, hour, minute), 'date');
            this.current.time_set(datetime);

            //Update invitations UI
            this.update();
        }
    },

    //Invitation title was clicked
    //-> (void)
    title_edit_click: function()
    {
        this.$title_input.swap(this.$title, this.title_validate_click.bind(this));
    },

    //New title was validated
    //-> (void)
    title_validate_click: function()
    {
        //Swap input for text
        this.$title_input.swap(this.$title, this.title_edit_click.bind(this));

        //Update current invitation title
        if(this.$title_input.value != this.$title_input.title
            && this.current.title != this.$title.innerHTML)
        {
            this.current.title_set(this.$title.innerHTML);
            this.update();
        }
    }
});