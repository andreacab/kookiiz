/*******************************************************
Title: Shopping UI
Authors: Kookiiz Team
Purpose: Display and edit shopping lists
********************************************************/

//Represents a user interface for shopping-related controls
var ShoppingUI = Class.create(
{
    object_name: 'shopping_ui',

    /*******************************************************
    CONSTRUCTOR
    ********************************************************/

    //Class constructor
    //-> (void)
    initialize: function()
    {
        //Init properties
        this.shopping_order = [];

        //MARKETS
        this.$marketDel  = $('shopping_market_delete');
        this.$marketNew  = $('shopping_market_new');
        this.$marketSave = $('shopping_market_save');
        this.$marketSel  = $('select_shopping_market');

        //SHOPPING PANEL
        this.$panelCancel    = $('shopping_short_cancel');
        this.$panelFinalize  = $('shopping_short_finalize');
        this.$panelInfo      = $('div_shopping_info');
        this.$panelNoticeOld = $('shopping_short_notice_old');
        this.$panelPrice     = $('span_shopping_price');
        this.$panelPrint     = $('shopping_short_print');
        this.$panelTransfer  = $('shopping_short_transfer');
        this.$panelWeight    = $('span_shopping_weight');
        this.$shopPanel      = $('shopping_short');
        this.$shopSel        = $('select_shopping_day');

        //SHOPPING TAB
        this.$inputProduct = $('input_shopping_add');
        this.$inputQty     = $('input_shopping_quantity');
        this.$noticeExp    = $('shopping_notice_expired');
        this.$noticeMod    = $('shopping_notice_modified');
        this.$noticeSto    = $('shopping_notice_stocked');
        this.$notices      = $('shopping_full_notices');
        this.$selGroup     = $('select_shopping_group');
        this.$selUnit      = $('select_shopping_unit');
        this.$shopEmail    = $('shopping_email');
        this.$shopFull     = $('shopping_full');
        this.$shopPrint    = $('shopping_print');
        this.$shopShare    = $('shopping_share');
    },
    
    /*******************************************************
    CLEAR
    ********************************************************/

    //Clear shopping input fields
    //-> (void)
    clearInputs: function()
    {
        this.$inputProduct.value    = '';
        this.$inputQty.value        = '';
        this.$selUnit.selectedIndex = ING_UNIT_DEFAULT;
        this.$selGroup.selectIndex  = 0;
        
        this.$inputProduct.unfreeze();
        this.$selGroup.unfreeze();
    },
    
    /*******************************************************
    CONTROLS
    ********************************************************/

    //Display shopping controls according to provided parameters
    //#parameters (object): structure containing shopping list parameters
    //-> (void)
    controlsDisplay: function(parameters)
    {
        parameters = parameters || {};

        if(parameters.cancelable || parameters.items)
            this.$noticeMod.show();
        if(parameters.expired)
            this.$noticeExp.show();
        if(parameters.stocked)
            this.$noticeSto.show();
        if(parameters.old)
        {
            this.$panelNoticeOld.show();
            this.$panelCancel.show();
            this.$panelTransfer.show();
        }
        if(!parameters.empty)
        {
            if(!parameters.old)
                this.$panelFinalize.show();

            this.$panelInfo.show();
            this.$panelPrint.show();
            this.$shopEmail.unfreeze();
            this.$shopPrint.unfreeze();
            //this.$shopShare.unfreeze();
        }
        //Check if at least one caption is displayed
        if(parameters.cancelable || parameters.expired
            || parameters.stocked || parameters.items)
        {
            this.$notices.show();
        }
    },

    //Hide all shopping captions and controls
    //-> (void)
    controlsHide: function()
    {
        this.$notices.hide();
        this.$noticeExp.hide();
        this.$noticeMod.hide();
        this.$noticeSto.hide();
        this.$panelCancel.hide();
        this.$panelFinalize.hide();
        this.$panelInfo.hide();
        this.$panelNoticeOld.hide();
        this.$panelPrint.hide();
        this.$panelTransfer.hide();
        this.$shopEmail.freeze();
        this.$shopPrint.freeze();
        //this.$shopShare.freeze();
    },

    /*******************************************************
    DISPLAY
    ********************************************************/

    //Update shopping UI display
    //-> (void)
    display: function()
    {
        var day  = this.list_day_get(),
            list = this.list_get(day),
            old  = day < 0;
        if(!list) return;

        //Hide all shopping controls
        this.controlsHide();

        //Display side panel shopping list and period
        this.displayPanel(list, old);
        this.period_display();       
        //Display full shopping list      
        this.displayTab(list, old);
        
        //Display shopping list captions and controls
        var params = list.parameters_get();
        params = Object.extend(params,
        {
            'cancelable':   params.modified && !old,
            'old':          old
        });
        this.controlsDisplay(params);
    },

    //Display side panel shopping list
    //#list (object):   shopping list object
    //#old (bool):      whether shopping list has expired
    //-> (void)
    displayPanel: function(list, old)
    {
        list.display(this.$shopPanel, 
        {
            'callback':   this.list_action.bind(this),
            'full':       false,
            'hover':      true,
            'id_class':   'shopshortgroup',
            'old':        old,
            'order':      this.shopping_order
        });       
        list.display_price(this.$panelPrice);
        list.display_weight(this.$panelWeight);
    },

    //Display full shopping list in dedicated tab
    //#list (object):   shopping list object
    //#old (bool):      whether shopping list has expired
    //-> (void)
    displayTab: function(list, old)
    {
        //Display shopping list
        list.display(this.$shopFull, 
        {
            'callback': this.list_action.bind(this),
            'full':     true,
            'hover':    true,
            'id_class': 'shopfullgroup',
            'old':      old,
            'order':    this.shopping_order
        });

        //Create Sortable on shopping list
        var list_element = this.$shopFull.select('.shopping_list')[0];
        if(list_element)
        {
            Sortable.create(list_element,
            {
                'tag':          'li',
                'only':         'shopping_group',
                'handle':       'title',
                'dropOnEmpty':  true,
                'constraint':   'vertical',
                'overlap':      'vertical',
                'markDropZone': true,
                'onUpdate':     this.markets_change.bind(this),
                'onStart':      this.markets_category_drag.bind(this),
                'onEnd':        this.markets_category_drop.bind(this)
            });
        }      
    },
    
    /*******************************************************
    INIT
    ********************************************************/

    //Init dynamic functionalities
    //-> (void)
    init: function()
    {
        //MARKETS
        this.$marketSel.observe('change', this.markets_select_change.bind(this));
        this.$marketDel.observe('click', this.markets_delete_click.bind(this));
        this.$marketNew.observe('click', this.markets_new_click.bind(this));
        this.$marketSave.observe('click', this.markets_save_click.bind(this));

        //SHOPPING PANEL
        this.$shopSel.observe('change', this.list_select_change.bind(this));
		this.$panelCancel.observe('click', this.list_cancel_click.bind(this));
		this.$panelFinalize.observe('click', this.list_finalize_click.bind(this));
		this.$panelPrint.observe('click', this.list_print_click.bind(this));
		this.$panelTransfer.observe('click', this.list_transfer_click.bind(this));

        //SHOPPING TAB
        Ingredients.autocompleter_init(this.$inputProduct, this.onIngredientSelect.bind(this));
        if(user_logged())
        {
            this.$shopEmail.unfreeze().observe('click', this.onSend.bind(this));
            this.$shopShare.freeze();   //SHARING IS NOT AVAILABLE YET !!!
        }
        else
        {
            this.$shopEmail.freeze();
            this.$shopShare.freeze();
        }
        this.$shopPrint.observe('click', this.print.bind(this));
        $('button_shopping_clear').observe('click', this.clearInputs.bind(this));
        $('button_shopping_add').observe('click', this.onIngredientAdd.bind(this));
        Utilities.observe_return(this.$inputQty, this.onIngredientEnter.bind(this));
    },

    /*******************************************************
    LISTS
    ********************************************************/

    //Triggered when user makes an action on a shopping list element
    //#element (object): ingredient quantity or shopping item object
    //#action (string):  action that was performed
    //#params (object):  action parameters
    //-> (void)
    list_action: function(element, action, params)
    {
        var list = this.list_get();
        if(element.object_name == 'ingredient_quantity')
        {
            switch(action)
            {
                case 'cancel':
                    list.modifications_cancel(element.id);
                    break;

                case 'delete':
                    list.modifications_add(element.id, -element.quantity, element.unit);
                    break;

                case 'edit':
                    var old_quantity;
                    if(params.new_unit == element.unit)
                        old_quantity = element.quantity;
                    else
                        old_quantity = element.convert(params.new_unit);
                    list.modifications_add(element.id, params.new_quantity - old_quantity, params.new_unit);
                    break;

                case 'transfer':
                    this.transfer(element);
                    break;
            }
        }
        else if(element.object_name == 'shopping_item')
        {
            switch(action)
            {
                case 'delete':
                    list.items_delete(element.id);
                    break;
            }
        }

        //Trigger update
        this.list_updated();
    },

    //Cancel current shopping list
    //-> (void)
    list_cancel_click: function()
    {
        //Set shopping status as none for current day
        var day = this.list_day_get();
        User.menu.shopping_status_set(day, SHOPPING_STATUS_NONE);
    },

    //Return current shopping list day index
    //->day (int): shopping list day index
    list_day_get: function()
    {
        return parseInt(this.$shopSel.value);
    },

    //Select default shopping day
    //-> (void)
    list_default: function()
    {
        var default_day = User.menu.shopping_days_get(0)[0].day;
        this.list_select(default_day);
    },

    //Display specified or current shopping list in dedicated tab
    //#day (int): shopping list day (defaults to current)
    //-> (void)
    list_finalize: function(day)
    {
        day = day || this.list_day_get();
        if(day >= 0)
        {
            var list = this.list_get(day);
            if(list)    
                this.list_select(day);
            else        
                Kookiiz.tabs.error_404();
        }
        else
            Kookiiz.tabs.error_404();
    },

    //Called when finalization button is clicked
    //-> (void)
    list_finalize_click: function()
    {
        var day = this.list_day_get();
        Kookiiz.tabs.show('shopping_finish', day);
    },

    //Return specified or current shopping list object
    //#day (int): shopping list day (defaults to current)
    //->shopping_list (object): shopping list object (false if none is found for provided day)
    list_get: function(day)
    {
        day = day || this.list_day_get();
        return User.menu.shopping_list_get(day);
    },

    //Callback for click on print button
    //-> (void)
    list_print_click: function()
    {
        this.print();
    },

    //Select shopping list of provided day index
    //#day (int): shopping list day
    //-> (void)
    list_select: function(day)
    {
        var options = this.$shopSel.select('option');
        for(var i = 0, imax = options.length; i < imax; i++)
        {
            if(options[i].value == day)
            {
                this.$shopSel.selectedIndex = i;
                this.display();
                break;
            }
        }
    },

    //Callback for shopping day select menu change
    //-> (void)
    list_select_change: function()
    {
        this.display();
    },

    //Callback for click on transfer button
    //-> (void)
    list_transfer_click: function()
    {
        this.transfer();
    },

    //Called after current shopping list has been modified by user action
    //-> (void)
    list_updated: function()
    {
        var day = this.list_day_get();
        User.menu.update('shopping',
        {
            'start': day,
            'stop':  day
        });
    },

    /*******************************************************
    MARKETS
    ********************************************************/

    //Called when shopping category drag starts
    //#draggable (object): scriptaculous draggable object
    //-> (void)
    markets_category_drag: function(draggable)
    {
        draggable.element.addClassName('drag');
    },

    //Called when shopping category drag stops
    //#draggable (object): scriptaculous draggable object
    //-> (void)
    markets_category_drop: function(draggable)
    {
        draggable.element.removeClassName('drag');
    },

   //Called when shopping group order is changed by drag and drop
    //#groups_list (DOM): sortable container
    //-> (void)
    markets_change: function(groups_list)
    {
        var market_id = parseInt(this.$marketSel.value);
        if(market_id > 0) 
            this.$marketSave.show();
    },

    //Delete an existing market configuration
    //#market_id (int): ID of the market configuration
    //-> (void)
    markets_delete: function(market_id)
    {
        Kookiiz.api.call('shopping', 'market_delete',
        {
            'callback': this.markets_delete_callback.bind(this),
            'request':  'market_id=' + market_id
        });
    },

    //Callback for market deletion process
    //#response (object): server response object
    //-> (void)
    markets_delete_callback: function(response)
    {
        var id   = parseInt(response.parameters.market_id),
            time = parseInt(response.parameters.time);
        User.markets_delete(id, time);
    },

    //Called when market delete button is clicked
    //Delete current market selection
    //-> (void)
    markets_delete_click: function()
    {
        var market_id = parseInt(this.$marketSel.value);
        if(market_id > 0)
        {
            //Remove market option
            var market_index = this.$marketSel.value_search(market_id);
            this.$marketSel.childElements()[market_index].remove();

            //Delete market order
            User.markets_delete(market_id);
            this.markets_update();
        }
    },

    //Open a popup to create a new market
    //-> (void)
    markets_new: function()
    {
        //Check that user did not exceed the maximum number of saved shopping orders
        var markets = User.markets_get();
        if(markets.length < USER_MARKETS_MAX)
        {
            Kookiiz.popup.custom(
            {
                'text':         SHOPPING_ALERTS[9],
                'title':        SHOPPING_ALERTS[8],
                'confirm':      true,
                'cancel':       true,
                'callback':     this.markets_new_confirm.bind(this),
                'content_url':  '/dom/shopping_market_popup.php'
            });
        }
        else 
            Kookiiz.popup.alert({'text': SHOPPING_ALERTS[3]});
    },

    //Callback for new market creation
    //#response (object): server response object
    //-> (void)
    markets_new_callback: function(response)
    {
        var id    = parseInt(response.parameters.market_id),
            name  = response.parameters.name.stripTags(),
            order = response.parameters.order.map(function(id){return parseInt(id);}),
            time  = parseInt(response.parameters.time);
        User.markets_add(id, name, order, time);
        Kookiiz.popup.hide();
    },

    //Called on click of the new market button
    //-> (void)
    markets_new_click: function()
    {
        this.markets_new();
    },

    //Called when user cancels or confirms the new market popup
    //#confirm (bool): true if user confirms his action
    //-> (void)
    markets_new_confirm: function(confirm)
    {
        if(confirm)
        {
            Kookiiz.popup.loader();
            
            var name  = $('input_shopping_market').value.stripTags(),
                order = this.markets_order();
            Kookiiz.api.call('shopping', 'market_create',
            {
                'callback': this.markets_new_callback.bind(this),
                'request':  'name=' + encodeURIComponent(name)
                            + '&order=' + Object.toJSON(order)
            });
        }
    },

    //Return current market order
    //->order (array): list of shopping groups IDs
    markets_order: function()
    {
        //Retrieve current order
        var order = [], groups = this.$shopFull.select('.shopping_group');
        for(var i = 0, imax = groups.length; i < imax; i++)
        {
            order.push(parseInt(groups[i].id.split('_')[1]));
        }
        return order;
    },

    //Save market configuration
    //#market_id (int): ID of the market configuration to save
    //#order (array):   list of shopping category IDs
    //-> (void)
    markets_save: function(market_id, order)
    {
        Kookiiz.api.call('shopping', 'market_save',
        {
            'callback': this.markets_save_callback.bind(this),
            'request':  'market_id=' + market_id
                        + '&order=' + Object.toJSON(order)
        });
    },

    //Callback for market saving process
    //#response (object): server response object
    //-> (void)
    markets_save_callback: function(response)
    {
        var market_id = parseInt(response.parameters.market_id),
            order = response.parameters.order,
            time  = parseInt(response.parameters.time);
        User.markets_order_save(market_id, order, time);

        //Display confirmation
        Kookiiz.popup.alert({'text': SHOPPING_ALERTS[5]});
    },

    //Called on click of the save market button
    //#event (object): DOM click event
    //-> (void)
    markets_save_click: function(event)
    {
        var market_id = parseInt(this.$marketSel.value);
        if(market_id > 0)
        {
            var order = this.markets_order();
            this.markets_save(market_id, order);
            event.findElement().hide(); //Hide button
        }
    },

    //Assign preset category order
    //#market_id (int): ID of the market to select (defaults to default one)
    //-> (void)
    markets_select: function(market_id)
    {
        //Update markets data and select menu
        this.$marketSel.value_set(market_id);

        //A market configuration is selected
        if(market_id && market_id >= 0)
        {
            //Save selection on server
            Kookiiz.api.call('shopping', 'market_select',
            {
                'callback': this.markets_select_callback.bind(this),
                'request':  'market_id=' + market_id
            });
            //Update shopping order
            this.shopping_order = User.markets_order_get(market_id);
            //Show icon to delete current market
            this.$marketDel.show();
        }
        //Default market
        else
        {
            this.shopping_order = [];
            var groups_ordered = INGREDIENTS_GROUPS_NAMES.slice().sort();
            for(var i = 0, imax = groups_ordered.length; i < imax; i++)
            {
                var group_id = INGREDIENTS_GROUPS_NAMES.indexOf(groups_ordered[i]);
                this.shopping_order.push(group_id);
            }

            //Hide icons to save/delete market
            this.$marketDel.hide();
            this.$marketSave.hide();
        }

        //Apply selected category order
        this.update();
    },

    //Callback for market selection process
    //#response (object): server response object
    //-> (void)
    markets_select_callback: function(response)
    {
        var market_id = parseInt(response.parameters.market_id),
            time      = parseInt(response.parameters.time);
        User.markets_select(market_id, time);
    },

    //Called on change of the market select menu
    //-> (void)
    markets_select_change: function()
    {
        this.markets_select(parseInt(this.$marketSel.value));
    },

    //Called after an update of the shopping orders
    //-> (void)
    markets_update: function()
    {
        //Remove pre-existing options except default
        while(this.$marketSel.childElements().length > 1)
        {           
            $(this.$marketSel.lastChild).remove();
        }

        //Loop through markets and add an option for each of them
        var markets = User.markets_get(), market_option, current;
        for(var i = 0, imax = markets.length; i < imax; i++)
        {
            if(markets[i].selected) current = markets[i];
            market_option = new Element('option',
            {
                'selected': markets[i].selected ? 'selected' : '',
                'value':    markets[i].id
            });
            market_option.innerHTML = markets[i].name;
            this.$marketSel.appendChild(market_option);
        }

        //Set current shopping order
        this.shopping_order = current ? current.order : ING_GROUPS_ORDER;
    },
    
    /*******************************************************
    OBSERVERS
    ********************************************************/
   
    //Called when user presses the button to add an item/ingredient to the shopping list
    //Check if the input is a valid ingredient or a custom product and call appropriate function
    //-> (void)
    onIngredientAdd: function()
    {
        //Retrieve user input
        var product = this.$inputProduct.value.stripTags();
        if(product)
        {
            var day  = this.list_day_get(),
                list = User.menu.shopping_list_get(day),
                qty  = parseFloat(this.$inputQty.value),
                unit = parseInt(this.$selUnit.value);

            //Look for a corresponding ingredient in global array
            var ingredient_id = Ingredients.search(product.toLowerCase(), 'id');
            if(ingredient_id > 0)
            {
                if(!isNaN(qty) && qty > 0)
                    //Case where the input is a valid ingredient with a valid quantity
                    list.modifications_add(ingredient_id, qty, unit);
                else 
                    //Quantity is not valid
                    Kookiiz.popup.alert({'text': INGREDIENTS_ALERTS[1]});
            }
            else
            {
                //Retrieve selected ingredient group
                var group = parseInt(this.$selGroup.value);
                if(group < 0) group = 0; //Default to "other"

                //Create a plain text item instead of an ingredient
                var unit_name = Units.get(unit, 'name'),
                    item_text = (qty ? qty + unit_name + ' - ' : '') + product;

                //Add item to shopping list
                list.items_add(item_text, group);
            }

            //Trigger list update
            this.list_updated();
            //Clear inputs
            this.clearInputs();
            this.$inputProduct.target();
        }
        else 
            Kookiiz.popup.alert({'text': SHOPPING_ALERTS[4]});
    },
    
    //Called when the enter key is pressed in the quantity input field
    //-> (void)
    onIngredientEnter: function()
    {
        if(this.$inputProduct.value && this.$inputQty.value)
            this.onIngredientAdd();
    },
   
    //Called when user selects an ingredient using the autocompleter
    //#ingredient (object): corresponding ingredient object
    //-> (void)
    onIngredientSelect: function(ingredient)
    {
        var group_id = ING_CATS_TOGROUP[ingredient.cat];

        //Freeze product selection
        this.$inputProduct.freeze();

        //Auto-select appropriate group
        this.$selGroup.value_set(group_id);
        this.$selGroup.freeze();

        //Suggest default unit
        this.$selUnit.value_set(ingredient.unit);
        //Hide "no unit" option if ingredient is not countable
        var noUnitIndex = this.$selUnit.value_search(UNIT_NONE);
        if(!ingredient.wpu) 
            this.$selUnit[noUnitIndex].hide();
        else                
            this.$selUnit[noUnitIndex].show();

        //Focus quantity input
        this.$inputQty.target();
    },
   
    //Callback for shopping email click
    //Open popup to send shopping list by email
    //-> (void)
    onSend: function()
    {
        Kookiiz.popup.custom(
        {
            'text':         SHOPPING_TEXT[16],
            'title':        SHOPPING_TEXT[15],
            'confirm':      true,
            'cancel':       true,
            'callback':     this.onSendConfirm.bind(this),
            'content_url':  '/dom/shopping_send_popup.php',
            'content_init': this.onSendReady.bind(this)
        });
    },
    
    //Called when user confirms or cancels shopping send
    //#confirm (bool): true if user confirms the sending action
    //-> (void)
    onSendConfirm: function(confirm)
    {
        if(confirm)
        {
            var mode = parseInt($('shopping_send_mode').value), email = '';
            switch(mode)
            {
                case SHOPPING_SEND_EMAIL:
                    email = $('shopping_send_email').value;
                    this.send(email);
                    break;

                case SHOPPING_SEND_FRIEND:
                    email = $('shopping_send_friend').value;
                    if(email)   
                        this.send(email);
                    else       
                        Kookiiz.popup.alert({'text': SHOPPING_ALERTS[11]});
                    break;
            }
        }
    },
    
    //Called when mode changes
    //#event (event): DOM change event
    //-> (void)
    onSendModeChange: function(event)
    {
        var mode = parseInt(event.findElement().value);
        switch(mode)
        {
            case SHOPPING_SEND_EMAIL:
                $('shopping_send_friend').hide();
                $('shopping_send_input').show();
                break;

            case SHOPPING_SEND_FRIEND:
                $('shopping_send_input').hide();
                $('shopping_send_friend').show();
                break;
        }
    },
    
    //Called when the shopping send popup is loaded
    //Init shopping send controls
    //-> (void)
    onSendReady: function()
    {
        $('shopping_send_mode').observe('change', this.onSendModeChange.bind(this));
    },
    
    //Callback for shopping share click
    //Open popup to share shopping list with a friend
    //-> (void)
    onShareClick: function()
    {
        var shared = User.menu.shopping_shared_get(this.list_day_get());
        Kookiiz.popup.custom(
        {
            'text':                 SHOPPING_TEXT[18],
            'title':                SHOPPING_TEXT[17],
            'confirm':              true,
            'cancel':               true,
            'callback':             this.onShareConfirm.bind(this),
            'content_url':          '/dom/shopping_share_popup.php',
            'content_parameters':   'shared=' + Object.toJSON(shared)
        });
    },
    
    //Called when user cancels or confirms sharing action
    //-> (void)
    onShareConfirm: function(confirm)
    {
        if(confirm)
        {
            var fid = parseInt($('shopping_share_friend').value), 
                day = this.list_day_get();
            if(fid)   
                Kookiiz.friends.share_shopping(fid, day);
            else            
                Kookiiz.popup.alert({'text': SHOPPING_ALERTS[12]});
        }
    },

    /*******************************************************
    PERIOD
    ********************************************************/

    //Highlight current shopping period
    //-> (void)
    period_display: function()
    {
        if(Kookiiz.tabs.current_get() == 'main')
        {
            var period = User.menu.shopping_period(this.list_day_get());
            Kookiiz.menu.meals_select(period.start, period.stop, 'green');
        }
    },

    /*******************************************************
    PRINT
    ********************************************************/

    //Generate printable shopping list in new window
    //-> (void)
    print: function()
    {
        window.open('/print/shopping');
    },

    /*******************************************************
    SEND
    ********************************************************/

    //Send current shopping list by email
    //#email (string): recipient email
    //-> (void)
    send: function(email)
    {
        var shopping = this.list_get().send(this.shopping_order);
        Kookiiz.api.call('shopping', 'send', 
        {
            'callback': function()
                        {
                            Kookiiz.popup.alert({'text': SHOPPING_ALERTS[7] + ' ' + email});
                        },
            'request':  'shopping=' + Object.toJSON(shopping)
                        + '&email=' + email
        });
    },

    /*******************************************************
    TRANSFER
    ********************************************************/

    //Transfer ingredients from old shopping list to new one
    //#ing_qty (object): ingredient quantity object (optional)
    //-> (void)
    transfer: function(ing_qty)
    {
        //Find future shopping day
        var shopDays = User.menu.shopping_days_get(0);
        if(shopDays.length)
        {
            //Keep day indexes only
            shopDays = shopDays.map(function(shop){return shop.day;});

            //Build popup title and text
            var title = '', text = '';
            if(ing_qty)
            {
                var ing_name  = ing_qty.ingredient.name,
                    unit_name = ing_qty.unit == UNIT_NONE ? '' : Units.get(ing_qty.unit, 'name');

                title = SHOPPING_TEXT[1];
                text  = SHOPPING_TEXT[3] + ' "' + ing_qty.quantity + unit_name + ' - ' + ing_name + '".';
            }
            else
            {
                title = SHOPPING_TEXT[2];
                text  = SHOPPING_TEXT[4];
            }

            //Open popup to select shopping list
            Kookiiz.popup.custom(
            {
                'text':                 text,
                'title':                title,
                'confirm':              true,
                'cancel':               true,
                'callback':             this.transfer_confirm.bind(this, ing_qty || null),
                'content_url':          '/dom/shopping_transfer_popup.php',
                'content_parameters':   'shopping_days=' + Object.toJSON(shopDays)
            });
        }
        else 
            Kookiiz.popup.alert({'text': SHOPPING_ALERTS[6]});
    },

    //Confirms the transfer of an ingredient (or an entire shopping list)
    //#ingQty (object): ingredient quantity object ("null" for entire list)
    //#confirm (bool):  true if user confirms the transfer
    //-> (void)
    transfer_confirm: function(ingQty, confirm)
    {
        if(confirm)
        {
            var dayCur = this.list_day_get(),
                dayFut = parseInt($('select_shopping_transfer').value),
                listCur = User.menu.shopping_list_get(dayCur),
                listFut = User.menu.shopping_list_get(dayFut);

            //Transfer a specific ingredient
            if(ingQty)
            {
                //Transfer ingredient as a modification on the destination list
                //listCur.modifications_add(ingQty.id, -ingQty.quantity, ingQty.unit);
                listFut.modifications_add(ingQty.id, ingQty.quantity, ingQty.unit);
            }
            //Transfer all ingredients
            else
            {
                var ingredients = listCur.getIngredients();
                for(var i = 0, imax = ingredients.length; i < imax; i++)
                {
                    ingQty = ingredients[i];
                    //listCur.modifications_add(ingQty.id, -ingQty.quantity, ingQty.unit);
                    listFut.modifications_add(ingQty.id, ingQty.quantity, ingQty.unit);
                }
            }

            //Display future shopping list
            this.list_select(dayFut);
        }
    },
    
    /*******************************************************
    UPDATE
    ********************************************************/

    //Shopping UI update (called when shopping data changes)
    //-> (void)
    update: function()
    {
        this.update_select();
        this.display();
    },

    //Update shopping select menu on side panel to reflect added/removed shopping lists
    //-> (void)
    update_select: function()
    {
        //Retrieve menu shopping days
        var shopping_days = User.menu.shopping_days_get();

        //Retrieve shopping day select menu, save current value and clear it
        var selected_day = this.$shopSel.value;  //Save selected day
        this.$shopSel.clean();					 //Clear select menu

        //Loop through shopping days
        var shopping_group = new Element('optgroup', {'class': 'menu', 'label': SHOPPING_TEXT[21]});
        for(var i = 0, imax = shopping_days.length; i < imax; i++)
        {
            //Retrieve day parameters
            var curDay  = shopping_days[i].day,
                curDate = new DateTime(curDay),
                shared  = User.menu.shopping_shared_get(curDay);

            //Create an option for current day
            var current_option = new Element('option', {'value': curDay});
            current_option.innerHTML = curDate.dayname + ' ' + curDate.day + '.' + curDate.month;
            if(curDay < 0)     
                current_option.innerHTML += ' (' + SHOPPING_TEXT[9] + ')';
            else if(shared.length) 
                current_option.innerHTML += ' (' + SHOPPING_TEXT[10] + ')';
            shopping_group.appendChild(current_option);
        }
        this.$shopSel.appendChild(shopping_group);

        //Restore selected day or select most recent
        var restored = this.$shopSel.value_set(selected_day);
        if(restored == -1)
        {
            var options = shopping_group.select('option');
            this.$shopSel.selectedIndex = options.length - 1;
        }
    }
});