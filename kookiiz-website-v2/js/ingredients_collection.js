/*******************************************************
Title: Ingredients collection
Authors: Kookiiz Team
Purpose: Define a collection of ingredient objects
********************************************************/

//Represents a list of ingredient amounts
var IngredientsCollection = Class.create(Observable,
{
    object_name: 'ingredients_collection',

    /*******************************************************
    CONSTRUCTOR
    ********************************************************/

    //Class constructor
    //#indexed (bool):  specifies if ingredient collection should be indexed by ID (defaults to false)
    //#data (array):    list of ingredient quantities to import immediately (optional)
    //-> (void)
    initialize: function(indexed, data)
    {
        this.content    = [];
        this.nutrition  = NUT_VALUES.map(function(){return 0;});
        this.price      = 0;
        this.weight     = 0;
        
        this.indexed = indexed ? true : false;
        if(data) 
            this.import_data(data);
    },

    /*******************************************************
    ENUMERATION
    ********************************************************/

    //Required for Prototype's Enumerable methods
    //#iterator (function): function to apply on each element
    //-> (void)
    _each: function(iterator)
    {
        this.content._each(iterator);
    },
    
    /*******************************************************
    BUILD
    ********************************************************/

    //Build list DOM element from ingredients in collection
    //#parameters (object): display options
    //-> (void)
    build: function(params)
    {
        //Build options
        var options = Object.extend(
        {
            'callback':     this.elementAction.bind(this),  //callback for user actions
            'cancelable':   false,      //should ingredients be made cancelable ?
            'deletable':    false,      //should ingredients be made deletable ?
            'editable':     false,      //should ingredients be made editable ?
            'hidePinch':    false,      //whether to hide "pinch" unit
            'hover':        true,       //display hover actions on mouseover ?
            'iconized':     false,      //should an icon be added to the ingredients (if available) ?
            'limit':        0,          //limit of ingredient items in the list
            'quantified':   false,      //should ingredients be displayed with a quantity ?
			'showModif':	true,		//whether to show ingredients that have been modified
            'skip0':        false,      //whether to skip ingredient with quantity <= 0
            'sorting':      '',         //sorting mode
            'text_max':     20,         //maximum chars for ingredient text
            'text_small':   false,      //whether to display small text,
            'transferable': false,      //should ingredients be made transferable ?
            'units':        'metric'    //unit system to use for display
        }, params || {});
        //Ingredient element options
        var el_options =
        {
            'hidePinch':    options.hidePinch,  //whether to hide "pinch" unit
            'iconized':     options.iconized,   //should an icon be added to the ingredient (if available) ?
            'quantified':   options.quantified, //should ingredient be displayed with a quantity ?
			'showModif':	options.showModif,	//whether to show ingredients that have been modified
            'skip0':        options.skip0,      //whether to skip ingredient with quantity <= 0
            'text_max':     options.text_max,   //maximum chars for ingredient text
            'text_small':   options.text_small, //whether to display small text
            'units':        options.units       //unit system to use for display
        }

        //Sort ingredients collection
        this.sort(options.sorting);

        //Loop through ingredients
        var list = new Element('ul', {'class': 'ingredients_list'}), element;
        for(var i = 0, imax = this.content.length; i < imax; i++)
        {
            if(this.content[i])
            {
                if(!options.limit || i < options.limit)
                {
                    element = this.elementBuild(this.content[i], el_options);
                    if(element) 
                        list.appendChild(element);
                }
                else
                {
                    element = new Element('li');
                    element.innerHTML = '(...)';
                    list.appendChild(element);
                    break;
                }
            }
        }
        
        //Add listener for mouse hover
        if(options.hover)
            list.observe('mouseover', this.onHover.bind(this, options));

        //Return list
        return list;
    },
    
    /*******************************************************
    COMPUTATIONS
    ********************************************************/

    //Compute nutritional value of the collection
    //-> (void)
    computeNutrition: function()
    {
        //Init nutrition array with zeros at each nutritional value
        this.nutrition = NUT_VALUES.map(function(){return 0;});

        //Loop through collection
        var amount, quantity;
        for(var i = 0, imax = this.content.length; i < imax; i++)
        {
            amount = this.content[i];
            if(amount)
            {
                quantity = amount.convert(UNIT_GRAMS);
                for(var j = 0, jmax = NUT_VALUES.length; j < jmax; j++)
                {
                    this.nutrition[j] += amount.ingredient.nutrition[j] * (quantity / 100);
                }
            }
        }
    },
    
    //Compute total price of the collection in CHF
    //-> (void)
    computePrice: function()
    {
        this.price = 0;
        for(var i = 0, imax = this.content.length; i < imax; i++)
        {
            if(this.content[i]) 
                this.price += this.content[i].price();
        }
    },
    
    //Compute total weight of the collection
    //-> (void)
    computeWeight: function()
    {
        this.weight = 0;
        for(var i = 0, imax = this.content.length; i < imax; i++)
        {
            if(this.content[i]) 
                this.weight += this.content[i].weight();
        }
    },

    /*******************************************************
    CONTAINS
    ********************************************************/

    //Check if collection contains specific ingredient
    //#ingredient_id (int): ID of the ingredient to search for
    //->found (bool): true if collection contains ingredient
    contains: function(ingredient_id)
    {
        if(this.indexed) 
            return this.content[ingredient_id] ? true : false;
        else
        {
            for(var i = 0, imax = this.content.length; i < imax; i++)
            {
                if(this.content[i].id == ingredient_id) 
                    return true;
            }
            return false;
        }
    },
    
    /*******************************************************
    COPY
    ********************************************************/

    //Return an independent copy of the collection
    //->collection (object): ingredient collection copy
    copy: function()
    {
        var copy = new IngredientsCollection();
        for(var i = 0, imax = this.content.length; i < imax; i++)
        {
            if(this.content[i]) 
                copy.content.push(this.content[i].copy());
        }
        copy.updated();
        return copy;
    },

    /*******************************************************
    COUNT
    ********************************************************/

    //Count number of elements in collection
    //->count (int): number of elements
    count: function()
    {
        return this.content.length;
    },
    
    /*******************************************************
    ELEMENTS
    ********************************************************/

    //Generic callback for user actions on ingredient DOM elements
    //#IngQty (object): corresponding ingredient quantity object
    //#action (string): action performed
    //#params (object): action parameters
    //-> (void)
    elementAction: function(IngQty, action, params)
    {
        switch(action)
        {
            case 'delete':
                this.quantity_delete(IngQty.id);
                break;
            case 'edit':
                this.quantity_delete(IngQty.id);
                this.quantity_add(new IngredientQuantity(IngQty.id, params.new_quantity, params.new_unit), true);
                break;
        }
    },

    //Build a DOM element from an ingredient quantity object
    //#IngQty (object): ingredient quantity
    //#params (object): build options
    //->element (DOM): resulting element (null if skipped)
    elementBuild: function(IngQty, params)
    {
        //OPTIONS
        var options = Object.extend(
        {
            'hidePinch':    false,      //whether to hide "pinch" unit
            'iconized':     false,      //should an icon be added to the ingredients (if available) ?
            'quantified':   false,      //should ingredient be displayed with a quantity ?
			'showModif':	true,		//whether to show ingredients that have been modified
            'skip0':        false,      //whether to skip ingredient with quantity <= 0
            'text_max':     20,         //maximum chars for ingredient text
            'text_small':   false,      //whether to display small text
            'units':        'metric'    //unit system to use for display
        }, params || {});
        
        //Skip if quantity <= 0
        if(options.skip0 && IngQty.quantity <= 0)
            return null;
 
        //INGREDIENT PROPERTIES
        var id   = IngQty.id,
            name = IngQty.ingredient.name,
            pic  = IngQty.ingredient.pic;

        //TEXT CLASS AND LENGTH
        var font_class = '';
        if(IngQty.expired)      
            font_class += ' expired';
        if(IngQty.stocked)      
            font_class += ' stocked';
        if(options.text_small)  
            font_class += ' small';

        //ELEMENT
        var element = new Element('li', {'class': 'ingredient_item'}),
            content = new Element('div', {'class': 'content'}),
            actions = new Element('div', {'class': 'actions'});
        element.writeAttribute('data-id', id);
        element.appendChild(content);
        element.appendChild(actions);

        //ICON
        if(options.iconized)
        {
            content.appendChild(new Element('img',
            {
                'alt':      name,
                'class':    'ingredient_icon ' + (pic ? pic : 'none'),
                'src':      ICON_URL,
                'title':    name
            }));
        }

        //QUANTITY
        var qty_wrap  = new Element('span', {'class': font_class}),
            qty_text  = new Element('span', {'class': 'quantity_text'}),
            unit_text = new Element('span', {'class': 'unit_text'});
        qty_wrap.appendChild(qty_text);
        qty_wrap.appendChild(unit_text);
        content.appendChild(qty_wrap);
        //Convert to appropriate unit system
        var qty = IngQty.quantity, unit = Units.get(IngQty.unit);
        if(!unit.isSystem(options.units))
        {
            unit = Units.get(unit.getEq());
            qty  = Math.round(IngQty.convert(unit.getID()) / unit.getRound()) * unit.getRound();
        }
        qty_text.appendText(qty < 0 ? 0 : (qty % 1 != 0 ? qty.toFixed(1) : qty));
        unit_text.appendText(unit.getName());
        qty_wrap.appendText(' - ');
        //Hide quantity in some cases (qty <= 0, unit = "pinch", etc.)
        if(!options.quantified || qty <= 0 || (options.hidePinch && unit.getID() == UNIT_PINCH))
            qty_wrap.hide();

        //NAME
        var name_text = new Element('span', {'class': font_class});
        if(name.length > options.text_max)  
            name = name.truncate(options.text_max);
        if(IngQty.quantity <= 0)            
            name = name.strike();
        name_text.innerHTML = name + ' ';
        content.appendChild(name_text);

        //MODIFIED
        //Add notification for modified quantities
        if(IngQty.modified && options.showModif) 
            content.appendText('*');

        //RETURN
        return element;
    },
    
    /*******************************************************
    EMPTY
    ********************************************************/

    //Empty ingredient collection
    //-> (void)
    empty: function()
    {
        this.content = [];
        this.updated('empty');
    },
    
    /*******************************************************
    EXPORT
    ********************************************************/
    
    //Export collection in compact format
    //->out (array): list of compact ingredient quantities
    export_content: function()
    {
        var compact = [];
        for(var i = 0; i < this.content.length; i++)
        {
            if(this.content[i])
            {
                compact.push(
                {
                    'i': this.content[i].id,
                    'q': this.content[i].quantity,
                    'u': this.content[i].unit
                });
            }
        }
        return compact;
    },

    //Export ingredient IDs from collection
    //->ids (array): list of ingredient IDs
    export_ids: function()
    {
        return this.content.map(function(item){return item.id;});
    },

    /*******************************************************
    GETTERS
    ********************************************************/

    //Return nutrition values of collection
    //->nutrition (array): list of nutrition values
    getNutrition: function()
    {
        return this.nutrition.slice();
    },

    //Return current collection price in specified currency (or default one)
    //#currency_id (int): currency ID (optional)
    //->price (int): total collection price
    getPrice: function(currency_id)
    {
        return this.price * (currency_id ? CURRENCIES_VALUES[currency_id] : 1);
    },
    
    //Return all ingredient quantities
    //->ingredients (array): list of ingredient quantities the collection contains
    getQuantities: function()
    {
        return this.content.slice();
    },
    
    //Return a specific ingredient quantity from the collection
    //#ingredient_id (int): ID of the ingredient to return
    //->ingredient_quantity (object/bool): ingredient quantity or false (if not found)
    getQuantity: function(ingredient_id)
    {
        if(this.indexed) 
            return this.content[ingredient_id] || false;
        else
        {
            for(var i = 0; i < this.content.length; i++)
            {
                if(this.content[i].id == ingredient_id) 
                    return this.content[i];
            }
            return false;
        }
    },

    //Return current collection weight (in kg)
    //->weight (float): weight in kg
    getWeight: function()
    {
        return this.weight;
    },

    /*******************************************************
    IMPORT
    ********************************************************/

    //Import data in collection
    //Skip badly-formated or out of boundary data
    //#data (array): list of ingredient quantities as:
    //  #i (int):   ingredient ID
    //  #q (float): ingredient quantity
    //  #u (int):   quantity unit ID
    //-> (void)
    import_content: function(data)
    {
        var id, qty, unit;
        for(var i = 0, imax = data.length; i < imax; i++)
        {
            id   = parseInt(data[i].i);
            qty  = parseFloat(data[i].q);
            unit = parseInt(data[i].u);
            if(!id || isNaN(qty) || isNaN(unit) || unit > Units.maxID()) 
                continue;
            if(this.indexed)    
                this.content[id] = new IngredientQuantity(id, qty, unit);
            else                
                this.content.push(new IngredientQuantity(id, qty, unit));
        }
        
        //Collection has been updated
        this.updated('import');
    },

    /*******************************************************
    QUANTITIES
    ********************************************************/

    //Add an ingredient quantity to the collection
    //(sum with any existing quantity of the same ingredient)
    //#ing_new (object): ingredient quantity object
    //#forceUnit (bool): whether the unit of the new quantity should replace pre-existing one (defaults to false)
    //-> (void)
    quantity_add: function(ing_new, forceUnit)
    {
        if(!forceUnit) forceUnit = false;

        //Look for pre-existing quantity
        var ing_existing = null;
        if(this.indexed) 
            ing_existing = this.content[ing_new.id];
        else
        {
            for(var i = 0; i < this.content.length; i++)
            {
                if(this.content[i].id == ing_new.id)
                {
                    ing_existing = this.content[i];
                    break;
                }
            }
        }

        //Ingredient already exists in the collection
        if(ing_existing)
        {
            //Check if quantities are given in the same unit
            if(ing_new.unit != ing_existing.unit)
            {
                //If units are different the quantities (existing and new) are converted in the default unit
                //(except if forceUnit is set to true)
                if(forceUnit) 
                    ing_existing.convert(ing_new.unit)
                else
                {
                    ing_existing.convert_default(true);
                    ing_new.convert_default(true);
                }              
            }

            //Update existing ingredient quantity object
            ing_existing.quantity += ing_new.quantity;
            ing_existing.expired  = ing_existing.expired || ing_new.expired;
            ing_existing.stocked  = ing_existing.stocked || ing_new.stocked;
            ing_existing.modified = ing_existing.modified || ing_new.modified;
        }
        //Ingredient does not exist yet in the collection
        else
        {
            if(this.indexed)
                this.content[ing_new.id] = ing_new.copy();
            else
                this.content.push(ing_new.copy());
        }

        //Collection has been updated
        this.updated('add');
    },

    //Remove ingredient quantity from the collection
    //#ingredient_id (int): ID of the ingredient to remove
    //-> (void)
    quantity_delete: function(ingredient_id)
    {
        if(this.indexed) 
            delete this.content[ingredient_id];
        else
        {
            for(var i = 0; i < this.content.length; i++)
            {
                var current_id = this.content[i].id;
                if(current_id == ingredient_id)
                {
                    this.content.splice(i, 1);
                    break;
                }
            }
        }

        //Collection has been updated
        this.updated('delete');
    },

    /*******************************************************
    EVENTS
    ********************************************************/

    //Called when cancel action is selected on hover menu
    //#element (DOM):       ingredient DOM element
    //#callback (function): callback function for cancelation
    //-> (void)
    onCancel: function(element, callback)
    {
        //Retrieve corresponding ingredient quantity object
        var ing_id = parseInt(element.readAttribute('data-id')),
            IngQty = this.getQuantity(ing_id);
        //Trigger callback and hide hover
        callback(IngQty, 'cancel');
        Kookiiz.hover.hide(true);
    },

    //Called when delete action is selected on hover menu
    //#element (DOM):       ingredient DOM element
    //#callback (function): callback function for deletion
    //-> (void)
    onDelete: function(element, callback)
    {
        //Retrieve corresponding ingredient quantity object
        var ing_id = parseInt(element.readAttribute('data-id')),
            IngQty = this.getQuantity(ing_id);
        //Trigger callback and hide hover
        callback(IngQty, 'delete');
        Kookiiz.hover.hide(true);
    },

    //Called when edit action is selected on hover menu
    //Create a hover menu to edit ingredient quantity
    //#element (DOM):       ingredient DOM element
    //#callback (function): callback function for edition
    //-> (void)
    onEdit: function(element, callback)
    {
        //Retrieve corresponding ingredient quantity object
        var ing_id = parseInt(element.readAttribute('data-id')),
            IngQty = this.getQuantity(ing_id);

        /* CONTAINER */
        var controls = new Element('div'),
            inputs   = new Element('p', {'class': 'ingredient_inputs'}),
            action   = new Element('p', {'class': 'ingredient_actions'});
        controls.appendChild(inputs);
        controls.appendChild(action);

        /* QUANTITY */
        var span_input      = new Element('span', {'class': 'quantity_wrap input_wrap size_60'});
        var quantity_input  = new Element('input',
        {
            'class':        'quantity_input right',
            'maxlength':    6,
            'title':        IngQty.quantity,
            'type':         'text'
        });
        quantity_input.value = IngQty.quantity;
        Utilities.observe_return(quantity_input, this.onValidate.bind(this, element, controls, callback));
        span_input.appendChild(quantity_input);
        inputs.appendChild(span_input);

        /* UNIT */
        var unit_wrap  = new Element('div', {'class': 'unit_wrap'}),
            unit_value = new Element('input',
            {
                'class':    'unit_value',
                'type':     'hidden',
                'value':    IngQty.unit
            }),
            unit_disp  = new Element('div', {'class': 'unit_disp center'}),
            unit_shift = new Element('div', {'class': 'unit_shift'}),
            unit_next  = new Element('img',
            {
                'alt':      VARIOUS[15],
                'class':    'icon10 arrow_up click',
                'src':      ICON_URL,
                'title':    ACTIONS[21]
            }),
            unit_prev  = new Element('img',
            {
                'alt':      VARIOUS[16],
                'class':    'icon10 arrow_down click',
                'src':      ICON_URL,
                'title':    ACTIONS[21]
            });
            
        unit_disp.appendText(Units.get(IngQty.unit, 'name'));
        unit_next.observe('click', this.onUnitShift.bind(this, controls, 1));
        unit_prev.observe('click', this.onUnitShift.bind(this, controls, 0));
        unit_shift.appendChild(unit_next);
        unit_shift.appendChild(unit_prev);
        unit_wrap.appendChild(unit_value);
        unit_wrap.appendChild(unit_disp);
        unit_wrap.appendChild(unit_shift);
        inputs.appendChild(unit_wrap);

        //Validation
        var icon = new Element('img',
        {
            'alt':      ACTIONS[2],
            'class':    'icon15 click accept',
            'src':      ICON_URL,
            'title':    ACTIONS[2]
        });
        var text = new Element('span', {'class': 'click'});
        text.innerHTML = ACTIONS[2];
        [icon, text].invoke('observe', 'click', this.onValidate.bind(this, element, controls, callback));
        action.appendChild(icon);
        action.appendChild(text);

        //Display hover area
        Kookiiz.hover.on(element, controls);

        //Focus input field
        quantity_input.focus();
    },

    //Callback for mouse hover on ingredients list
    //#options (object): list display options
    //#event (object):   DOM mouseenter event
    //-> (void)
    onHover: function(options, event)
    {
        var element = event.findElement('.ingredient_item');
        if(element)
        {
            //Retrieve corresponding ingredient quantity
            var ing_id = parseInt(element.readAttribute('data-id')),
                IngQty = this.getQuantity(ing_id);

            //Build hover list
            var list = new Element('ul'), item, icon, text;

            /* ACTIONS */
            if(options.editable && IngQty.ingredient.unit != UNIT_PINCH)
            {
                item = new Element('li');
                icon = new Element('img',
                {
                    'alt':      ACTIONS[24],
                    'class':    'icon15 click edit',
                    'src':      ICON_URL,
                    'title':    ACTIONS[24]
                });
                text = new Element('span', {'class': 'small click'});
                text.appendText(ACTIONS[24]);
                item.appendChild(icon);
                item.appendChild(text);
                list.appendChild(item);
                [icon, text].invoke('observe', 'click', this.onEdit.bind(this, element, options.callback));
            }
            if(options.cancelable && IngQty.modified)
            {
                item = new Element('li');
                icon = new Element('img',
                {
                    'alt':      ACTIONS[26],
                    'class':    'icon15 click cancel',
                    'src':      ICON_URL,
                    'title':    ACTIONS[26]
                });
                text = new Element('span', {'class': 'small click'});
                text.appendText(ACTIONS[26]);
                item.appendChild(icon);
                item.appendChild(text);
                list.appendChild(item);
                [icon, text].invoke('observe', 'click', this.onCancel.bind(this, element, options.callback));
            }
            if(options.deletable && IngQty.quantity > 0)
            {
                item = new Element('li');
                icon = new Element('img',
                {
                    'alt':      ACTIONS[15],
                    'class':    'icon15 click delete',
                    'src':      ICON_URL,
                    'title':     ACTIONS[15]
                });
                text = new Element('span', {'class': 'small click'});
                text.appendText(ACTIONS[15]);
                item.appendChild(icon);
                item.appendChild(text);
                list.appendChild(item);
                [icon, text].invoke('observe', 'click', this.onDelete.bind(this, element, options.callback));
            }
            if(options.transferable)
            {
                item = new Element('li');
                icon        = new Element('img',
                {
                    'alt':      ACTIONS[10],
                    'class':    'icon15 click transfer',
                    'src':      ICON_URL,
                    'title':    ACTIONS[10]
                });
                text = new Element('span', {'class': 'small click'});
                text.appendText(ACTIONS[10]);
                item.appendChild(icon);
                item.appendChild(text);
                list.appendChild(item);
                [icon, text].invoke('observe', 'click', this.onTransfer.bind(this, element, options.callback));
            }

            /* NOTIFICATIONS */
            var info_item, info_text;
            if(IngQty.stocked || IngQty.expired)
            {
                //Spacer
                var line = new Element('li');
                line.appendChild(new Element('hr'));
                list.appendChild(line);
                //Stock notification
                if(IngQty.stocked)
                {
                    info_item = new Element('li');
                    info_text = new Element('span', {'class': 'tiny stocked'});
                    info_text.appendText(INGREDIENTS_ALERTS[4]);
                    info_item.appendChild(info_text);
                    list.appendChild(info_item);
                }
                //Expired notification
                if(IngQty.expired)
                {
                    info_item = new Element('li');
                    info_text = new Element('span', {'class': 'tiny expired'});
                    info_text.appendText(INGREDIENTS_ALERTS[5]);
                    info_item.appendChild(info_text);
                    list.appendChild(info_item);
                }
            }

            //Display hover if at least one action was added
            if(!list.empty()) 
                Kookiiz.hover.on(element, list);
        }
    },

    //Transfer ingredient quantity
    //#element (DOM):       ingredient DOM element
    //#callback (function): callback function for transfer action
    //-> (void)
    onTransfer: function(element, callback)
    {
        //Retrieve corresponding ingredient quantity object
        var ing_id = parseInt(element.readAttribute('data-id')),
            IngQty = this.getQuantity(ing_id);
        //Trigger callback and hide hover
        callback(IngQty, 'transfer');
        Kookiiz.hover.hide(true);
    },

    //Called when user clicks an arrow to change quantity unit
    //#controls (DOM):  ingredient edition controls container
    //#direction (int): shift direction
    //-> (void)
    onUnitShift: function(controls, direction)
    {
        //Retrieve controls components
        var unit_disp = controls.select('.unit_disp')[0],
            unit_value = controls.select('.unit_value')[0];

        //Find next unit in the list
        var unitSys = UNITS_SYSTEMS[User.option_get('units')],
            order = UNITS_ORDERS[unitSys], max = order.length - 1,
            next = order.indexOf(parseInt(unit_value.value)) + (direction ? 1 : -1);
            
        //Check boundaries
        if(next < 0)
            next = max - 1;
        else if(next > max)
            next = 0;

        //Change unit display
        var unit_id = order[next];
        unit_value.value = unit_id;
        unit_disp.clean().appendChild(document.createTextNode(Units.get(unit_id, 'name')));
    },

    //Validate ingredient quantity modification
    //#element (DOM):       ingredient DOM element
    //#controls (DOM):      ingredient edition controls container
    //#callback (function): callback function for edition
    //-> (void)
    onValidate: function(element, controls, callback)
    {
        var ing_id = parseInt(element.readAttribute('data-id')),
            IngQty = this.getQuantity(ing_id);

        //Retrieve ingredient's components
        var quantity_input = controls.select('input.quantity_input')[0],
            unit_value     = controls.select('input.unit_value')[0];

        //Retrieve input values
        var new_quantity = parseFloat(quantity_input.value),
            new_unit     = parseInt(unit_value.value);
        if(isNaN(new_quantity))
        {
            quantity_input.addClassName('error');
            Kookiiz.popup.alert({'text': INGREDIENTS_ALERTS[1]});
            return;
        }

        //Call callback function
        if(new_quantity != IngQty.quantity || new_unit != IngQty.unit)
        {
            callback(IngQty, 'edit', 
            {
                'new_quantity': new_quantity,
                'new_unit':     new_unit
            });
        }
        Kookiiz.hover.hide(true);
    },

    /*******************************************************
    ROUND
    ********************************************************/

    //Round ingredient quantities according to their units
    //-> (void)
    round: function()
    {
        var round;
        this.content.each(function(ing)
        {
            round = Units.get(ing.unit, 'round');
            ing.quantity = Math.ceil(ing.quantity / round) * round;
        });
        this.updated('rounded');
    },

    /*******************************************************
    SORT
    ********************************************************/

    //Sort ingredient collection
    //#mode (string): sorting mode
    //->collection (object): this ingredient collection
    sort: function(mode)
    {
        switch(mode)
        {
            //Sort by ingredient name
            case 'name':
                this.content.sort(function(ing_a, ing_b)
                {
                    var name_a  = ing_a.ingredient.name,
                        name_b  = ing_b.ingredient.name;
                    return name_a < name_b ? -1 : 1;
                });
                break;

            //Sort by group order, then name
            default:
                this.content.sort(function(ing_a, ing_b)
                {
                    var group_a = ING_CATS_TOGROUP[ing_a.ingredient.cat],
                        group_b = ING_CATS_TOGROUP[ing_b.ingredient.cat],
                        pos_a   = ING_GROUPS_ORDER[group_a],
                        pos_b   = ING_GROUPS_ORDER[group_b],
                        name_a  = ing_a.ingredient.name,
                        name_b  = ing_b.ingredient.name;
                    return pos_a < pos_b ? -1 : ((pos_a > pos_b) ? 1 : (name_a < name_b ? -1 : 1));
                });
                break;
        }
        return this;
    },

    /*******************************************************
    UPDATES
    ********************************************************/

    //Called when collection has been updated
    //#action (string): action description
    //-> (void)
    updated: function(action)
    {
        //Recompute every property
        this.computeNutrition();
        this.computePrice();
        this.computeWeight();

        //Fire event
        this.fire('updated', {'action': action});
    }
});
Object.extend(IngredientsCollection.prototype, Enumerable);