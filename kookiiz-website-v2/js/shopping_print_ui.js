/*******************************************************
Title: Shopping Print UI
Authors: Kookiiz Team
Purpose: Display printable shopping list
********************************************************/

//Represents a user interface to print shopping lists
var ShoppingPrintUI = Class.create(
{
    object_name: 'shopping_print_ui',

    /*******************************************************
    CONSTRUCTOR
    ********************************************************/

    //Class constructor
    //-> (void)
    initialize: function()
    {
        this.order = ING_GROUPS_ORDER;
        this.list  = null;

        //DOM elements
        this.$list     = $('shopping_full');
        this.$info     = $('shopping_print_info');
        this.$optIcons = $('shopping_icons_check');
        this.$optInfo  = $('shopping_info_check');
    },

    /*******************************************************
    DISPLAY
    ********************************************************/

    //Display printable shopping list
    //-> (void)
    display: function()
    {
        //Display shopping list
        this.list.display(this.$list,
        {
            'full':      true,
            'hover':     false,
            'id_class':  'shopprintgroup',
            'order':     this.order,
			'showModif': false,
            'skip0':     true
        });

        //Display information (price, weight)
        var info = this.$info.select('.content')[0].clean(),
            span_price  = new Element('span'),
            span_weight = new Element('span');
        info.appendChild(span_price);
        info.appendChild(span_weight);
        this.list.display_price(span_price);
        this.list.display_weight(span_weight);
        span_price.innerHTML = SHOPPING_TEXT[27] + ' : ' + span_price.innerHTML;
        span_weight.innerHTML = SHOPPING_TEXT[28] + ' : ' + span_weight.innerHTML;

        //Setup icons
        Utilities.sprites_replace('img.category_icon');
        $$('.category_icon').invoke('hide').invoke('setStyle', {'float': 'left'});
    },
    
    /*******************************************************
    INIT
    ********************************************************/

    //Init dynamic functionalities
    //#list (object):   shopping list object to print
    //#order (array):   ordered list of ingredient group IDs
    //-> (void)
    init: function(list, order)
    {
        this.list  = list.copy();
        this.order = order;

        //Observers
        this.$optIcons.observe('click', this.onOptionClick.bind(this));
        this.$optInfo.observe('click', this.onOptionClick.bind(this));
        $('button_shopping_print').observe('click', this.onPrint.bind(this));
    },
    
    /*******************************************************
    OBSERVERS
    ********************************************************/
   
    //Callback for display option click
    //-> (void)
    onOptionClick: function()
    {
        if(this.$optIcons.checked)   
            $$('.category_icon').invoke('show');
        else                            
            $$('.category_icon').invoke('hide');
        
        if(this.$optInfo.checked)    
            this.$info.show();
        else                            
            this.$info.hide();
    },
   
    //Callback for click on print button
    //-> (void)
    onPrint: function()
    {
        window.print();
    },

    /*******************************************************
    UPDATE
    ********************************************************/

    //Update shopping print UI
    //-> (void)
    update: function()
    {
        this.display();
    }
});