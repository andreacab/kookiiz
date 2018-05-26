/*******************************************************
Title: Quick meals UI
Authors: Kookiiz Team
Purpose: Create and manage quick meal objects
********************************************************/

//Represents a user interface for quick meals management
var QuickmealsUI = Class.create(
{
    object_name: 'quickmeals_ui',

    /*******************************************************
    CONSTRUCTOR
    ********************************************************/

    //Class constructor
    //-> (void)
	initialize: function()
    {
        this.form_quickmeal = null;
        this.drags          = $H();   //List of draggable elements

        //DOM elements
        this.$list = $('quick_meals_display');
    },
    
    /*******************************************************
    DISPLAY
    ********************************************************/

    //Display user's quick meals
    //-> (void)
    display: function()
    {
        this.dragRemove();
        this.$list.clean();

        //Loop through quick meals
        var quickmeals = User.quickmeals_get(),
            quickmeals_list = new Element('ul');
        var options =
        {
            'sorting':      'abc',
            'clickable':    false,
            'deletable':    true,
            'callback':     this.element_action.bind(this),
            'deleteText':   ACTIONS_LONG[6]
        };
        var quickmeal_id, quickmeal, type = 'quickmeal', quickmeal_item;
        for(var i = 0, imax = quickmeals.length; i < imax; i++)
        {
            quickmeal_id    = quickmeals[i];
            quickmeal       = Quickmeals.get(quickmeal_id);
            quickmeal_item  = Kookiiz.recipes.element_build_item(quickmeal.id, quickmeal.name, type, options);
            if(quickmeal_item)
            {
                quickmeals_list.appendChild(quickmeal_item);
                this.dragCreate(quickmeal_id, quickmeal_item);
            }
        }

        if(quickmeals_list.empty())
            this.$list.innerHTML = QUICKMEALS_ALERTS[0];
        else
            this.$list.appendChild(quickmeals_list);
    },

    /*******************************************************
    DRAG
    ********************************************************/

    //Create draggable element
    //#id (int):        unique quick meal ID
    //#element (DOM):   quickmeal DOM element
    //-> (void)
    dragCreate: function(id, element)
    {
        var drag = new Draggable(element,
        {
            'handle':               'handle',
            'revert':               true,
            'onStart':              this.element_drag_start.bind(this),
            'onEnd':                this.element_drag_stop.bind(this),
            'reverteffect':         function(){return 0;},
            'endeffect':            function(){return 0;},
            'ghosting':             true,
            'scroll':               window,
            'scrollSensitivity':    50
        });
        this.drags.set(id, drag);
    },

    //Remove draggable quick meal
    //#id (int): unique quick meal ID (optional, defaults to all)
    //-> (void)
    dragRemove: function(id)
    {
        if(id)
        {
            var drag = this.drags.unset(id);
            if(drag) drag.destroy();
        }
        else
        {
            this.drags.each(function(el)
            {
                el.value.destroy();
            });
            this.drags = $H();
        }
    },

    /*******************************************************
    ELEMENT
    ********************************************************/

    //Callback for action on quick meal DOM element
    //#id (int):        quick meal ID
    //#action (string): user action
    //-> (void)
    element_action: function(id, action)
    {
        switch(action)
        {
            case 'delete':
                this.onDelete(id);
                break;
        }
    },

    //Called when recipe drag and effects are over
    //#draggable (object): scriptaculous draggable object
    //-> (void)
    element_drag_finish: function(draggable)
    {
        //Fix Scriptaculous bugs
        draggable_fix_stop(draggable);

        //Remove drag-specific class
        draggable.element.removeClassName('drag');

        //Enable recipe preview
        Kookiiz.recipes.preview_on();

        //Hide droppables areas
        Kookiiz.menu.drop_hide();
        Kookiiz.menu.moveDown();
    },

    //Called when recipe drag starts
    //Correct buggy behavior of scriptaculous and prepare for drag
    //#draggable (object): scriptaculous draggable object
    //-> (void)
    element_drag_start: function(draggable)
    {
        //Fix Scriptaculous bugs
        draggable_fix_start(draggable);

        //Add drag-specific class
        draggable.element.addClassName('drag');

        //Turn off recipe preview and hint
        Kookiiz.recipes.preview_off();
        Kookiiz.recipes.search_hint_stop();

        //Display droppable areas
        Kookiiz.menu.today();
        Kookiiz.menu.moveUp();
        Kookiiz.menu.drop_show('quickmeal');
    },

    //Called right after recipe drag has ended
    //#draggable (object): scriptaculous draggable object
    //-> (void)
    element_drag_stop: function(draggable)
    {
        var delta = draggable.currentDelta();
        if(draggable.options.ghosting) 
            this.element_drag_finish(draggable);
        else
        {
            new Effect.Fade(draggable.element, 
            {
                'duration':     0.2, 
                'queue':        {'position': 'end', 'scope': draggable.element.id}, 
                'afterFinish':  this.element_drag_finish.bind(this, draggable)
            });
            new Effect.Move(draggable.element, 
            {
                'x':        draggable.delta[0] - delta[0], 
                'y':        draggable.delta[1] - delta[1], 
                'duration': 0.1, 
                'queue':    {'position': 'end', 'scope': draggable.element.id}
            });
            new Effect.Appear(draggable.element, 
            {
                'duration': 0, 
                'queue':    {'position': 'end', 'scope': draggable.element.id}
            });
        }
    },

    /*******************************************************
    FORM
    ********************************************************/

    //Called when user cancels quick meal creation
    //-> (void)
    form_cancel: function()
    {
        this.form_quickmeal = null;
        Kookiiz.popup.hide();
    },

    //Called when user confirms quick meal creation
    //-> (void)
    form_confirm: function()
    {
        var error = 0, quickmeal = this.form_quickmeal;
        if(!quickmeal.name)
            error = 1;
        else if(quickmeal.name.length < QM_NAME_MIN)
            error = 2;
        else if(quickmeal.name.length > QM_NAME_MAX)
            error = 3;

        var error_display = $('quickmeal_error');
        if(error)
        {
            error_display.innerHTML = QUICKMEALS_ERRORS[error - 1];
            error_display.show();
        }
        else
            this.form_save();
    },

    //Add an ingredient to currently edited quick meal
    //-> (void)
    form_ingredient_add: function()
    {
        if(this.form_quickmeal.mode != QM_MODE_INGREDIENTS)
            return;

        var ingredient_name = $('input_quickmeal_ingredient').value.stripTags(),
            ingredient_id = Ingredients.search(ingredient_name, 'id');
        if(ingredient_id)
        {
            var quantity = parseFloat($('input_quickmeal_quantity').value);
            if(!isNaN(quantity) && quantity > 0)
            {
                var unit = parseInt($('select_quickmeal_unit').value);
                this.form_quickmeal.ingredients.quantity_add(new IngredientQuantity(ingredient_id, quantity, unit));
                this.form_ingredients_update();
                this.form_inputs_clear();

                $('input_quickmeal_ingredient').target();
            }
            else 
                Kookiiz.popup.alert({'text': INGREDIENTS_ALERTS[1]});
        }
        else 
            Kookiiz.popup.alert({'text': INGREDIENTS_ALERTS[0]});
    },

    //Called when an ingredient is selected using the autocompleter
    //#ingredient (object): corresponding ingredient object
    //-> (void)
    form_ingredient_select: function(ingredient)
    {
        $('select_quickmeal_unit').value_set(ingredient.unit);
        $('input_quickmeal_quantity').target();
    },

    //Display ingredients of currently edited quick meal
    //-> (void)
    form_ingredients_display: function()
    {
        var container = $('quickmeal_ingredients_display').clean();
        var list = this.form_quickmeal.ingredients.build(
        {
            'deletable':    true,
            'editable':     true,
            'quantified':   true,
            'text_max':     20
        });
        if(list.empty())
            container.appendText(INGREDIENTS_ALERTS[3]);
        else
            container.appendChild(list);
    },

    //Called when quick meal ingredients have been updated
    //-> (void)
    form_ingredients_update: function()
    {
        this.form_ingredients_display();
        this.form_nutrition_display();
    },

    //Clear quick meal edition fields
    //-> (void)
    form_inputs_clear: function()
    {
        var ingredient_input = $('input_quickmeal_ingredient'),
            quantity_input   = $('input_quickmeal_quantity');
        ingredient_input.value = ingredient_input.title;
        quantity_input.value   = '';
        $('select_quickmeal_unit').value_set(ING_UNIT_DEFAULT);
    },

    //Called when quick mode changes
    //#event (event): DOM event object
    //-> (void)
    form_mode_change: function(event)
    {
        var mode = event.findElement().value;
        this.form_quickmeal.mode = mode == 'ingredients' ? QM_MODE_INGREDIENTS : QM_MODE_NUTRITION;
        if(mode == 'ingredients')
        {
            $('quickmeal_mode_description').innerHTML = QUICKMEALS_TEXT[5];
            $('quickmeal_mode_nutrition').hide();
            $('quickmeal_mode_ingredients').show();
        }
        else if(mode == 'nutrition')
        {
            $('quickmeal_mode_description').innerHTML = QUICKMEALS_TEXT[6];
            $('quickmeal_mode_ingredients').hide();
            $('quickmeal_mode_nutrition').show();
        }
        this.form_nutrition_update();
    },

    //Display nutrition values of currently edited quick meal
    //-> (void)
    form_nutrition_display: function()
    {
        Kookiiz.health.nutritionDisplay('quickmeal_nutrition',
        {
            'quickmeals':   this.form_quickmeal.getNutrition(),
            'needs':        User.needs_get(),
            'values':       MENU_NUTRITION_VALUES
        });
    },

    //Update nutrition values of currently edited quick meal
    //-> (void)
    form_nutrition_update: function()
    {
        var mode = $('select_quickmeal_mode').value;
        if(mode == 'nutrition')
        {
            //Reset nutrition values
            this.form_quickmeal.nutrition = $A($R(0, NUT_VALUES.length - 1)).map(function(){return 0;});

            //Set nutrition values from user input
            var nutrition_fields = $('quickmeal_mode_nutrition').select('input'),
                input, parent, value_id;
            for(var i = 0, imax = nutrition_fields.length; i < imax; i++)
            {
                input       = nutrition_fields[i];
                parent      = input.up('tr');
                value_id    = parseInt(parent.id.split('_')[2]);
                this.form_quickmeal.nutrition[value_id] = parseInt(input.value);
            }
        }
        this.form_nutrition_display();
    },

    //Open quick meals creation popup
    //-> (void)
    form_popup: function()
    {
        Kookiiz.popup.custom(
        {
            'text':         QUICKMEALS_TEXT[1],
            'title':        QUICKMEALS_TEXT[0],
            'content_url':  '/dom/quickmeal_popup.php',
            'content_init': this.form_popup_init.bind(this)
        });
    },

    //Init functionalities of the quick meals popup
    //-> (void)
    form_popup_init: function()
    {
        //Create empty quick meal
        this.form_quickmeal = new QuickMeal(0, User.id, '', QM_MODE_INGREDIENTS);
        this.form_quickmeal.ingredients.observe('updated', this.form_ingredients_update.bind(this));

        //Init listeners
        $('input_quickmeal_name').observe('blur', this.form_title_change.bind(this));
        $('select_quickmeal_mode').observe('change', this.form_mode_change.bind(this));
        $('quickmeal_ingredient_add').observe('click', this.form_ingredient_add.bind(this));
        $('quickmeal_mode_nutrition').select('input').invoke('observe', 'blur', this.form_nutrition_update.bind(this));
        $('button_quickmeal_confirm').observe('click', this.form_confirm.bind(this));
        $('button_quickmeal_cancel').observe('click', this.form_cancel.bind(this));
        Utilities.observe_return('input_quickmeal_quantity', this.form_quantity_enter.bind(this));

        //Autocompleter
        Ingredients.autocompleter_init('input_quickmeal_ingredient', this.form_ingredient_select.bind(this));

        //Display nutrition
        this.form_nutrition_display();
    },

    //Called when enter key is pressed in the quantity input field
    //#event (event): DOM event
    //-> (void)
    form_quantity_enter: function(event)
    {
        var input = event.findElement();
        if(input.value)
            this.form_ingredient_add();
    },

    //Save current quick meal
    //-> (void)
    form_save: function()
    {
        //Display loader
        Kookiiz.popup.hide();
        Kookiiz.popup.loader();
        //Send request to save quick meal
        Kookiiz.api.call('quickmeals', 'create',
        {
            'callback': this.form_saved.bind(this),
            'request':  'quickmeal=' + Object.toJSON(this.form_quickmeal.export_content())
        });
    },

    //Called once the quick meal has been saved
    //#response (object): server response object
    //-> (void)
    form_saved: function(response)
    {
        //Retrieve new quick meal ID
        var quickmeal_id = parseInt(response.parameters.quickmeal_id);
        this.form_quickmeal.id = quickmeal_id;
        //Store new quick meal
        Quickmeals.store(this.form_quickmeal);
        User.quickmeals_add(quickmeal_id);
        this.form_quickmeal = null;
        //Hide loader
        Kookiiz.popup.hide();
    },

    //Called when quick meal title is edited
    //#event (event): DOM event object
    //-> (void)
    form_title_change: function(event)
    {
        var input = event.findElement();
        if(input.value != input.title)
            this.form_quickmeal.name = input.value.stripTags();
    },
    
    /*******************************************************
    LIST
    ********************************************************/

    //Create a list of quick meals
    //#container (DOM/string):  container inside which the list will be displayed
    //#quickmeals (array):      list of quickmeals IDs
    //#options (object):        list options
    //-> (void)
    list: function(container, quickmeals, options)
    {
        container = $(container).clean();

        //Options
        options = Object.extend(
        {
            'callback':     false,  //callback for user actions
            'deletable':    false   //should quick meal items be made deletable ?
        }, options || {});

        //List quick meals
        var quickmeals_list = new Element('ul');
        var quickmeal, quickmeal_item, text, delete_icon;
        for(var i = 0, imax = quickmeals.length; i < imax; i++)
        {
            quickmeal = Quickmeals.get(quickmeals[i]);
            if(quickmeal)
            {
                quickmeal_item = new Element('li', {'class': 'quickmeal_item'});
                quickmeals_list.appendChild(quickmeal_item);

                //Name
                text = new Element('span');
                text.innerHTML = quickmeal.name;
                quickmeal_item.appendChild(text);

                //Delete icon
                if(options.deletable)
                {
                    delete_icon = new Element('img',
                    {
                        'alt':      ACTIONS[23],
                        'class':    'button15 cancel',
                        'src':      ICON_URL,
                        'title':    ACTIONS[23]
                    });
                    delete_icon.observe('click', options.callback.curry(quickmeal.id));
                    quickmeal_item.appendChild(delete_icon);
                }
            }
        }

        if(quickmeals_list.empty())
            container.innerHTML = QUICKMEALS_ALERTS[2];
        else
            container.appendChild(quickmeals_list);
    },
    
    /*******************************************************
    OBSERVERS
    ********************************************************/
    
    //Delete quick meal with provided ID
    //#id (int): quick meal ID
    //-> (void)
    onDelete: function(id)
    {
        Kookiiz.popup.confirm(
        {
            'text':     QUICKMEALS_ALERTS[1],
            'callback': function(confirm)
                        {
                            if(confirm)
                            {
                                //Remove quick meal from user profile
                                User.quickmeals_delete(id);
                                //Delete quick meal from server
                                Kookiiz.api.call('quickmeals', 'delete', 
                                {
                                    'request': 'quickmeal_id=' + id
                                });
                            }
                        }
        });
    }
});