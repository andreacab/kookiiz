/*******************************************************
Title: Mobile UI
Authors: Kookiiz Team
Purpose: User interface for mobile website version
********************************************************/

//Represents a user interface for mobile users
var MobileUI = Class.create(
{
    object_name: 'mobile_ui',
    
    /*******************************************************
    CONSTANTS
    ********************************************************/
    
    AUTOSAVE_DELAY: 5,
    
    /*******************************************************
    CONSTRUCTOR
    ********************************************************/
    
    initialize: function()
    {
        this.timer = 0;
        
        this.$content  = $('kookiiz_content');
        this.$pageSel  = $('select_page');
        
        switch(getPageID())
        {
            case 0:
                this.$shopSel  = $('shopping_select');
                this.$shopWrap = $('shopping_wrap');
                break;
                
            case 1:
                this.$favList  = $('favorites_list');
                this.$favIndex = $('favorites_index');
                this.$favSort  = $('favorites_sorting');
                break;
        }
    },
    
    /*******************************************************
    AUTOSAVE
    ********************************************************/
    
    autosave: function()
    {
        window.clearTimeout(this.timer);
        this.timer = window.setTimeout(this.save.bind(this, true), this.AUTOSAVE_DELAY * 1000);
    },

    
    /*******************************************************
    INIT
    ********************************************************/
    
    init: function()
    {
        //General
        this.$pageSel.observe('change', this.onPageChange.bind(this));
        
        //Page-specific
        switch(getPageID())
        {
            //Shopping
            case 0:
                this.$shopSel.observe('change', this.onShopSelect.bind(this));
                this.$shopWrap.observe('click', this.onShopClick.bind(this));
                break;
            
            //Recipes
            case 1:
                this.$favSort.observe('change', this.onFavSortChange.bind(this));
                break;
        }
    },
    
    /*******************************************************
    OBSERVERS
    ********************************************************/
    
    onFavSortChange: function()
    {
        window.location = '/m/' + MOBILE_PAGES[1] + '?sort=' + this.$favSort.value;
    },
    
    onPageChange: function()
    {
        var pageID = parseInt(this.$pageSel.value);
        window.location = '/m/' + MOBILE_PAGES[pageID];
    },
    
    onShopClick: function(event)
    {
        var el = event.findElement('.shopIng, .shopItem');
        if(el)
        {
            if(el.innerHTML == el.innerHTML.stripTags())
                el.innerHTML = el.innerHTML.strike();
            else
                el.innerHTML = el.innerHTML.stripTags();
            el.highlight();
        }
    },
    
    onShopSelect: function()
    {
        window.location = '/m/' + MOBILE_PAGES[0] + '?day=' + this.$shopSel.value;
    }
});