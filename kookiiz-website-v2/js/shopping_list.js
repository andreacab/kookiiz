/*******************************************************
Title: Shopping list
Authors: Kookiiz Team
Purpose: Define the shopping list object
********************************************************/

//Represents a shopping list with ingredients quantities, user modifications and sharing options
var ShoppingList = Class.create(
{
    object_name: 'shopping_list',

    /*******************************************************
    CONSTANTS
    ********************************************************/

    //Max chars for ingredient and item names on shopping list
    ING_CHARS_FULL:     30,
    ING_CHARS_PANEL:    25,
    ITEM_CHARS_FULL:    35,
    ITEM_CHARS_PANEL:   25,

    /*******************************************************
    CONSTRUCTOR
    ********************************************************/

    //Class constructor
    //#day (int): shopping day index
    //-> (void)
    initialize: function(day)
    {
        this.day                = day;
        this.ingredients        = new IngredientsCollection();
        this.ingredients_orig   = new IngredientsCollection();
        this.modifications      = new IngredientsCollection();
        this.items              = new ShoppingItemsList();
        this.shared             = [];
    },

    /*******************************************************
    IMPORT/EXPORT
    ********************************************************/

    //Export shopping list content
    //->shopping (object): compact shopping list
    export_content: function()
    {
        var shopping =
        {
            'ing':  this.ingredients.export_content(),
            'm':    this.modifications.export_content(),
            'it':   this.items.export_content(),
            'sh':   this.shared.slice()
        };

        var empty = true;
        for(var data in shopping)
        {
            if(data.length) 
                empty = false;
            else            
                shopping[data] = 0;
        }

        if(empty)   
            return 0;
        else        
            return shopping;
    },

    //Import shopping data from server
    //#shopping (object): shopping compact structure
    //-> (void)
    import_content: function(shopping)
    {
        if(shopping.ing)
            this.ingredients.import_content(shopping.ing);
        if(shopping.m)
            this.modifications.import_content(shopping.m);
        if(shopping.it)
            this.items.import_content(shopping.it);
        if(shopping.sh)
            this.shared = shopping.sh.parse('int');
    },

    /*******************************************************
    COPY
    ********************************************************/

    //Return a copy of current shopping list
    //#day (int): new shopping day index (optional)
    //->copy (object): shopping list copy
    copy: function(day)
    {
        var copy = new ShoppingList();
        copy.day              = day || this.day;
        copy.ingredients      = this.ingredients.copy();
        copy.ingredients_orig = this.ingredients_orig.copy();
        copy.modifications    = this.modifications.copy();
        copy.items            = this.items.copy();
        copy.shared           = this.shared;
        return copy;
    },

    /*******************************************************
    CLEAR
    ********************************************************/

    //Empty shopping list
    //-> (void)
    clear: function()
    {
        this.ingredients.empty();
        this.ingredients_orig.empty();
        this.modifications.empty();
        this.items.empty();
        this.shared = [];
    },

    /*******************************************************
    DISPLAY
    ********************************************************/

    //Display shopping list in provided container
    //#container (DOM/string):  container DOM element (or its ID)
    //#params (object):         display options
    //-> (void)
    display: function(container, params)
    {
        container = $(container).clean();

        //Display options
        var options = Object.extend(
        {
            'callback':   false,  //element edition callback
            'full':       false,  //whether to display a full shopping list
            'hover':      true,   //whether to display a hover on ingredient elements
            'id_class':   'shop', //class of IDs for shopping list groups
            'old':        false,  //whether the shopping list is expired
            'order':      [],     //shopping list groups order,
			'showModif':  true,	  //whether to show ingredients that have been modified
            'skip0':      false   //whether to skip ingredient with quantity <= 0 
        }, params || {});

        //Element options
        var ing_options =
        {
            'callback':     options.callback,
            'cancelable':   !options.old,
            'deletable':    !options.old,
            'editable':     !options.old,
            'hidePinch':    true,
            'hover':        options.hover,
            'quantified':   true,
			'showModif':    options.showModif,
            'skip0':        options.skip0,
            'text_max':     options.full ? this.ING_CHARS_FULL : this.ING_CHARS_PANEL,
            'text_small':   !options.full,
            'transferable': options.old,
            'units':        UNITS_SYSTEMS[User.option_get('units')]
        };
        var item_options =
        {
            'callback':     options.callback,
            'deletable':    !options.old,
            'hover':        options.hover,
			'showModif':    options.showModif,
            'text_max':     options.full ? this.ITEM_CHARS_FULL : this.ITEM_CHARS_PANEL,
            'text_small':   !options.full
        };

        //Prepare shopping groups
        var groups = this.group();

        //Loop through groups
        var shopping_list = new Element('ul', {'class': 'shopping_list'}),
            groups_ordered  = [], groups_unordered = [],
            group_id, group_key, group_name, group_pos, group_el, group_list,
            group_top, group_middle, group_bottom, group_title, group_icon, group_text,
            group_ings, group_items, ings_list, items_list;
        for(var i = 0, imax = ING_GROUPS.length; i < imax; i++)
        {
            //Retrieve group parameters
            group_id   = i;
            group_key  = ING_GROUPS[i] || 'none';
            group_name = INGREDIENTS_GROUPS_NAMES[i];
            group_pos  = options.order.indexOf(group_id);

            //Build group element
            group_el = new Element('li',
            {
                'class':    'shopping_group', 
                'id':       options.id_class + '_' + group_id
            });
            group_list  = new Element('div', {'class': 'list'});
            if(options.full)
            {
                group_top    = new Element('div', {'class': 'top'});
                group_middle = new Element('div', {'class': 'middle'});
                group_bottom = new Element('div', {'class': 'bottom'});
                group_title  = new Element('h5', {'class': 'title'});
                group_icon   = new Element('img', {'class': 'category_icon ' + group_key, 'src': ICON_URL});
                group_text   = new Element('p');
                group_text.innerHTML = group_name;
                group_title.appendChild(group_icon);
                group_title.appendChild(group_text);
                group_middle.appendChild(group_title);
                group_middle.appendChild(group_list);
                group_el.appendChild(group_top);
                group_el.appendChild(group_middle);
                group_el.appendChild(group_bottom);
            }
            else 
                group_el.appendChild(group_list);

            //Add ingredients and/or items list (if any) or hide group
            if(groups[group_id])
            {
                group_ings  = groups[group_id].ingredients;
                group_items = groups[group_id].items;
                if(group_ings.count())
                {
                    ings_list = group_ings.build(ing_options);
                    if(!ings_list.empty())
                        group_list.appendChild(ings_list);
                }
                if(group_items.count())
                {
                    items_list = group_items.build(item_options);
                    if(!items_list.empty())
                        group_list.appendChild(items_list);
                }
                if(group_list.empty())
                    group_el.hide();
                else
                    group_el.show();
            }
            else 
                group_el.hide();

            //Store group at its position (if any) or in the unordered list
            if(group_pos >= 0) 
                groups_ordered[group_pos] = group_el;
            else               
                groups_unordered.push(group_el);
        }
        //Loop through ordered groups
        var visible_groups = 0;
        for(i = 0, imax = groups_ordered.length; i < imax; i++)
        {
            if(groups_ordered[i])
            {
                shopping_list.appendChild(groups_ordered[i]);
                if(groups_ordered[i].visible())
                {
                    //For printed shopping list, every third group is cleared to the left
                    if(!(visible_groups % 3)) 
                        groups_ordered[i].addClassName('clear');
                    visible_groups++;
                }
            }
        }
        //Loop through unordered groups
        for(i = 0, imax = groups_unordered.length; i < imax; i++)
        {
            if(groups_unordered[i])
            {
                shopping_list.appendChild(groups_unordered[i]);
                if(groups_unordered[i].visible())
                {
                    //For printed shopping list, every third group is cleared to the left
                    if(!(visible_groups % 3)) 
                        groups_unordered[i].addClassName('clear');
                    visible_groups++;
                }
            }
        }

        //Append shopping list to container or display empty message
        if(visible_groups)  
            container.appendChild(shopping_list);
        else                
            container.innerHTML = SHOPPING_ALERTS[0];
    },

    //Display shopping list price
    //#container (DOM/string): container DOM element (or its ID)
    //-> (void)
    display_price: function(container)
    {
        var currency_id = User.option_get('currency'),
            currency    = CURRENCIES[currency_id],
            price       = Math.round(10 * this.ingredients.getPrice(currency_id)) / 10;
        $(container).clean().innerHTML = price + currency;
    },

    //Display name of people with whom the list is shared
    //#container (DOM/string): container DOM element (or its ID)
    //-> (void)
    display_sharing: function(container)
    {
        container = $(container).clean();
    },

    //Display shopping list weight
    //#container (DOM/string): container DOM element (or its ID)
    //-> (void)
    display_weight: function(container)
    {
        container = $(container).clean();
        container.innerHTML = Math.round(10 * this.ingredients.getWeight()) / 10 + 'kg';
    },
    
    /*******************************************************
    GETTERS
    ********************************************************/
   
    /**
     * Returns all ingredient quantities from the shopping list (w/ modifs)
     * ->ingredients (array): list of ingredient quantities
     */
    getIngredients: function()
    {
        return this.ingredients.getQuantities();
    },

    /*******************************************************
    GROUP
    ********************************************************/

    //Group shopping list ingredient and items by category
    //->groups (array): list of groups
    group: function()
    {
        var groups = [];

        //Ingredients
        var ingredients = this.ingredients.getQuantities(),
            ing_qty, ingredient, category, group_id;
        for(var i = 0, imax = ingredients.length; i < imax; i++)
        {
            //Current ingredient information
            ing_qty    = ingredients[i];
            ingredient = ing_qty.ingredient;
            category   = ingredient.cat;
            group_id   = ING_CATS_TOGROUP[category];

            //Store in existing group or create new one
            if(!groups[group_id])
            {
                groups[group_id] =
                {
                    'ingredients':  new IngredientsCollection(),
                    'items':        new ShoppingItemsList()
                }
            }
            groups[group_id].ingredients.quantity_add(ing_qty);
        }

        //Items
        var items = this.items.getAll(), item;
        for(i = 0, imax = items.length; i < imax; i++)
        {
            //Current item
            item     = items[i];
            group_id = item.group;

            //Store in existing group or create new one
            if(!groups[group_id])
            {
                groups[group_id] =
                {
                    'ingredients':  new IngredientsCollection(),
                    'items':        new ShoppingItemsList()
                }
            }
            groups[group_id].items.add(item);
        }

        //Return groups list
        return groups;
    },

    /*******************************************************
    EMPTY
    ********************************************************/

    //Check if shopping list is currently empty
    //->empty (bool): true if shopping list is empty
    isEmpty: function()
    {
        return !this.ingredients.count()
                && !this.modifications.count()
                && !this.items.count();
    },

    /*******************************************************
    INGREDIENTS
    ********************************************************/

    //Update shopping list content with ingredients for provided recipes and guests
    //User's modifications are also taken into account
    //#recipes (array): list of recipe IDs
    //#guests (array):  list of guests counts for each recipe
    //#days (array):    day index of each recipe
    //-> (void)
    ingredients_compute: function(recipes, guests, days)
    {
        this.ingredients.empty();

        //Loop through recipes
        var recipe, menu_guests, conservation, unit,
            unitSys = UNITS_SYSTEMS[User.option_get('units')];
        for(var i = 0, imax = recipes.length; i < imax; i++)
        {
            //Retrieve recipe and menu information
            recipe       = Recipes.get(recipes[i]);  //Current recipe
            conservation = days[i] - this.day;       //Number of days ingredients will be stored
            menu_guests  = guests[i];                //Number of guests set for current recipe in the menu

            //Loop through recipe ingredients quantities
            recipe.ingredients.each(function(ing)
            {
                //Skip useless ingredients (salt, pepper)
                if(SHOPPING_SKIP.indexOf(ing.id) != -1)
                    return;

                //Make a copy of current ingredient quantity
                ing = ing.copy();
                //Adapt quantity to guests count and convert it in default unit
                ing.quantity *= menu_guests / recipe.guests;
                ing.convert_default(true);
                //Adapt to current units system
                unit = Units.get(ing.unit);
                if(!unit.isSystem(unitSys))
                {
                    unit = Units.get(unit.getEq());
                    ing.convert(unit.getID(), true);
                }
                //Round quantity for countable ingredients
                if(unit.getID() == UNIT_NONE)
                    ing.quantity = Math.ceil(ing.quantity);
                //Set expiry and stock
                ing.expiry(conservation);
                //Add quantity to ingredients list
                this.ingredients.quantity_add(ing);
            }, this);
        }

        //Store a copy of original ingredient quantities (as computed from menu content)
        this.ingredients_orig = this.ingredients.copy();

        //Take user modifications into account
        this.modifications_compute();
        this.ingredients.round();
    },

    /*******************************************************
    ITEMS
    ********************************************************/

    //Add an item to the shopping list
    //#text (string):   item text
    //#group (int)      ingredient group ID
    //-> (void)
    items_add: function(text, group)
    {
        this.items.create(text, group);
    },

    //Clear a custom product/item from the shopping list
    //#item_id (int): ID of the item to delete
    //-> (void)
    items_delete: function(item_id)
    {
        this.items.remove(item_id);
    },

    /*******************************************************
    MODIFICATIONS
    ********************************************************/

    //Store a modification the user made to a specific ingredient
    //#id (int):    ID of the ingredient
    //#qty (float): quantity to add
    //#unit (int):  ID of the unit
    //-> (void)
    modifications_add: function(id, qty, unit)
    {
        var IngQty = new IngredientQuantity(id, qty, unit);
        this.modifications.quantity_add(IngQty, true);
        this.modifications_compute();
        this.ingredients.round();
    },

    //Cancel all user modifications made on a specific ingredient
    //#id (int): unique ingredient ID
    //-> (void)
    modifications_cancel: function(id)
    {
        this.modifications.quantity_delete(id);
        this.modifications_compute();
        this.ingredients.round();
    },

    //Take user modifications into account
    //-> (void)
    modifications_compute: function()
    {
        //Start from original ingredients list
        this.ingredients = this.ingredients_orig.copy();

        //Loop through user's modifications
        var ing_modif, ing_existing,
            modifications = this.modifications.getQuantities();
        for(var i = 0, imax = modifications.length; i < imax; i++)
        {
            ing_modif    = modifications[i];
            ing_existing = this.ingredients.getQuantity(ing_modif.id);
            
            //Set quantity as "modified"
            ing_modif.modified = true;

            //Check if modification is positive or a quantity already exists
            if(ing_modif.quantity > 0 || ing_existing)
                this.ingredients.quantity_add(ing_modif, true);
            //Else remove modification
            else
                this.modifications.quantity_delete(ing_modif.id);
        }
    },

    /*******************************************************
    PARAMETERS
    ********************************************************/

    //Return shopping list parameters
    //->params (object): structure containing shopping list parameters
    parameters_get: function()
    {
        //Init parameters structure
        var params =
        {
            'empty':    true,
            'expired':  false,
            'items':    false,
            'modified': false,
            'shared':   this.shared.length ? true : false,
            'stocked':  false
        };

        //Loop through ingredients
        this.ingredients.each(function(ing)
        {
            params.empty = false;
            if(ing.modified)    
                params.modified = true;
            if(ing.expired)     
                params.expired = true;
            if(ing.stocked)     
                params.stocked = true;
        });

        //Check if list contains at least one custom item
        if(this.items.count())
        {
            params.empty = false;
            params.items = true;
        }

        //Return parameters
        return params;
    },

    /*******************************************************
    SEND
    ********************************************************/

    //Return ready-to-send list
    //#order (array): ordered list of ingredient categories
    //->list (array): compact shopping list
    send: function(order)
    {
        var list = [];

        //Loop through ingredients
        var ingredients = this.ingredients.getQuantities(),
            ingredient, quantity, unit_id, unit, name, category, group_id, group_index;
        for(var i = 0, imax = ingredients.length; i < imax; i++)
        {
            ingredient  = ingredients[i].ingredient;
            quantity    = ingredients[i].quantity;
            unit_id     = ingredients[i].unit;
            if(quantity <= 0) continue;
            
            unit        = unit_id == UNIT_NONE ? '' : Units.get(unit_id, 'name');
            name        = ingredient.name;
            category    = ingredient.cat;
            group_id    = ING_CATS_TOGROUP[category];
            group_index = order.indexOf(group_id);

            if(!list[group_index])
            {
                list[group_index] =
                {
                    'group':        group_id,
                    'ingredients':  [],
                    'items':        []
                };
            }
            var ingredient_text = encodeURIComponent(quantity + unit + ' - ' + name);
            list[group_index].ingredients.push(ingredient_text);
        }

        //Add all items for current shopping day
        var items = this.items.getAll(), item_text;
        for(i = 0, imax = items.length; i < imax; i++)
        {
            item_text   = items[i].text;
            group_id    = items[i].group;
            group_index = order.indexOf(group_id);

            if(!list[group_index])
            {
                list[group_index] =
                {
                    'group':        group_id,
                    'ingredients':  [],
                    'items':        []
                };
            }
            list[group_index].items.push(encodeURIComponent(item_text));
        }

        //Remove empty array positions
        var list_clean = [];
        for(i = 0, imax = list.length; i < imax; i++)
        {
            if(list[i]) 
                list_clean.push(list[i]);
        }

        return list_clean;
    },

    /*******************************************************
    SETUP
    ********************************************************/

    //Setup shopping list components
    //-> (void)
    setup: function()
    {

    },

    /*******************************************************
    SHARING
    ********************************************************/

    //Return friends with which shopping list has been shared
    //->shared (array): list of users IDs the shopping list is shared with
    shared_get: function()
    {
        return this.shared.slice();
    },

    //Share shopping list with a friend
    //#friend_id (int): ID of the friend
    //-> (void)
    share: function(friend_id)
    {
        if(this.shared.indexOf(friend_id) < 0) 
            this.shared.push(friend_id);
        this.updated();
    },

    //Cancel sharing with provided friend
    //#friend_id (int): ID of the friend
    //-> (void)
    unshare: function(friend_id)
    {
        var friend_index = this.shared.indexOf(friend_id);
        if(friend_index >= 0) 
            this.shared.splice(friend_index, 1);
        this.updated();
    }
});