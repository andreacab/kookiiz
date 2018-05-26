/*******************************************************
Title: Shopping items list
Authors: Kookiiz Team
Purpose: Define a list of shopping items
********************************************************/

//Represents a list of shopping items
var ShoppingItemsList = Class.create(
{
    object_name: 'shopping_items_list',

    /*******************************************************
    CONSTRUCTOR
    ********************************************************/

    //Class constructor
    //-> (void)
    initialize: function()
    {
        this.content = [];
    },

    /*******************************************************
    ADD
    ********************************************************/

    //Add a shopping item to the list
    //Generate a new ID if already existing
    //#Item (object): item object
    //-> (void)
    add: function(Item)
    {
        if(this.getItem(Item.id))
            this.create(Item.text, Item.group);
        else 
            this.content.push(Item);
    },

    /*******************************************************
    BUILD
    ********************************************************/

    //Build list DOM element from items list
    //#options (object): display options
    //-> (void)
    build: function(options)
    {
        options = Object.extend(
        {
            'callback':     false,  //callback for items actions
            'deletable':    false,  //should items be made deletable ?
            'hover':        true,   //whether to display a hover for actions
			'showModif':  	true,	//whether to show items as "modified"
            'text_max':     25,     //maximum chars for item text
            'text_small':   false   //whether to display small text
        }, options || {});
        var item_options =
        {
			'showModif':  	options.showModif,
            'text_max':     options.text_max,
            'text_small':   options.text_small
        };

        //Loop through content
        var list = new Element('ul', {'class': 'items_list'}), element;
        for(var i = 0, imax = this.content.length; i < imax; i++)
        {
            element = this.elementBuild(this.content[i], item_options);
            if(element) 
                list.appendChild(element);
        }

        //Add listener for mouse hover
        if(options.hover)
            list.observe('mouseover', this.onHover.bind(this, options));

        //Return list
        return list;
    },

    /*******************************************************
    COPY
    ********************************************************/

    //Copy list content
    //->list (object): items list copy
    copy: function()
    {
        var copy = new ShoppingItemsList();
        for(var i = 0, imax = this.content.length; i < imax; i++)
        {
            copy.content.push(this.content[i].copy());
        }
        return copy;
    },

    /*******************************************************
    COUNT
    ********************************************************/

    //Count number of items in list
    //->count (int): number of items
    count: function()
    {
        return this.content.length;
    },

    /*******************************************************
    CREATE
    ********************************************************/

    //Add an item and generate a new ID for it
    //#text (string):   item text
    //#group (int):     item group ID
    //-> (void)
    create: function(text, group)
    {
        var id = 0;
        for(var i = 0, imax = this.content.length; i < imax; i++)
        {
            if(this.content[i].id >= id)
                id = this.content[i].id + 1;
        }
        this.content.push(new ShoppingItem(id, text, group));
    },

    /*******************************************************
    ELEMENTS
    ********************************************************/

    //Build DOM element from shopping item object
    //#item (object):       shopping item object
    //#options (object):    build options
    //->element (DOM): item DOM element
    elementBuild: function(Item, options)
    {
        //Text class and length
        var font_class = '';
        if(options.text_small)  
			font_class += ' small';

        //Build element
        var item_element = new Element('li', {'class': 'ingredient_item'}),
			item_content = new Element('div', {'class': 'content' + font_class});
        item_element.writeAttribute('data-id', Item.id);
        item_element.appendChild(item_content);

        //Text
        var item_text       = new Element('span', {'class': 'item_text'});
        item_text.innerHTML = Item.text.truncate(options.text_max) + (options.showModif ? '*' : '');
        item_content.appendChild(item_text);

        //Return DOM element
        return item_element;
    },

    /*******************************************************
    EMPTY
    ********************************************************/

    //Empty shopping items list
    //-> (void)
    empty: function()
    {
        this.content = [];
    },
    
    /*******************************************************
    EXPORT
    ********************************************************/

    //Export list content
    //->data (array): compact list data
    export_content: function()
    {
        var data = [];
        for(var i = 0, imax = this.content.length; i < imax; i++)
        {
            data.push(this.content[i].export_content());
        }
        return data;
    },
    

    /*******************************************************
    GET
    ********************************************************/

    //Get all shopping items in the list
    //->items (array): list of item objects
    getAll: function()
    {
        return this.content.slice();
    },

    //Return item with provided ID
    //#id (int): item ID
    //->item (object): item object
    getItem: function(id)
    {
        var item;
        for(var i = 0, imax = this.content.length; i < imax; i++)
        {
            item = this.content[i];
            if(item.id == id)
            {
                return item;
                break;
            }
        }
        return null;
    },

    /*******************************************************
    IMPORT
    ********************************************************/

    //Import items data
    //#data (array): list of items data
    //-> (void)
    import_content: function(data)
    {
        var id, text, grp;
        for(var i = 0, imax = data.length; i < imax; i++)
        {
            id   = parseInt(data[i].i);
            text = data[i].t;
            grp  = parseInt(data[i].c);
            this.content.push(new ShoppingItem(id, text, grp));
        }
    },

    /*******************************************************
    EVENTS
    ********************************************************/

    //Called when delete action is selected on hover menu
    //#element (DOM):       shopping item DOM element
    //#callback (function): callback function for deletion
    //-> (void)
    onDelete: function(element, callback)
    {
        //Retrieve corresponding item object
        var item_id = parseInt(element.readAttribute('data-id'));
        var Item = this.getItem(item_id);
        //Trigger callback and hide hover
        callback(Item, 'delete');
        Kookiiz.hover.hide(true);
    },

    //Callback for mouse hover on items list
    //#options (object):    list display options
    //#event (object):      DOM mouseenter event
    //-> (void)
    onHover: function(options, event)
    {
        var element = event.findElement('.ingredient_item');
        if(element)
        {
            var actions_list = new Element('ul');
            var action_item = null, action_icon = null, action_label = null;

            //Delete
            if(options.deletable)
            {
                action_item = new Element('li');
                action_icon = new Element('img',
                {
                    'alt':      ACTIONS[15],
                    'class':    'icon15 click delete',
                    'src':      ICON_URL,
                    'title':    ACTIONS[15]
                });
                action_label            = new Element('span', {'class': 'click'});
                action_label.innerHTML  = ACTIONS[15];
                action_item.appendChild(action_icon);
                action_item.appendChild(action_label);
                action_item.observe('click', this.onDelete.bind(this, element, options.callback));
                actions_list.appendChild(action_item);
            }

            //Display hover if at least one action was added
            if(!actions_list.empty()) Kookiiz.hover.on(element, actions_list);
        }
    },

    /*******************************************************
    REMOVE
    ********************************************************/

    //Remove an item from the list
    //#item_id (int): item ID
    //-> (void)
    remove: function(item_id)
    {
        for(var i = 0, imax = this.content.length; i < imax; i++)
        {
            if(this.content[i].id == item_id)
            {
                this.content.splice(i, 1);
                break;
            }
        }
    }
});