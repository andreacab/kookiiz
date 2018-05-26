/*******************************************************
Title: Hover
Authors: Kookiiz Team
Purpose: Class that displays a hover on DOM elements
********************************************************/

var HoverUI = Class.create(
{
    object_name: 'hover_ui',

    /*******************************************************
    CONSTANTS
    ********************************************************/

    TIMEOUT: 500,   //Delay before hover area disappearance

    /*******************************************************
    CONSTRUCTOR
    ********************************************************/

    //Class constructor
    //-> (void)
    initialize: function()
    {
        this.hovered = false;    //True when hover area is hovered
        this.target  = null;     //Store current DOM target
        this.timer   = 0;        //Disappearance timer

        this.$hover = $('kookiiz_hover');       
    },

    /*******************************************************
    INIT
    ********************************************************/

    //Init dynamic functionalities
    //-> (void)
    init: function()
    {
        this.$hover.observe('mouseenter', this.enter.bind(this));
        this.$hover.observe('mouseleave', this.leave.bind(this));
    },

    /*******************************************************
    ON
    ********************************************************/

    //Display hover area for provided DOM element and content
    //#element (DOM/string):    DOM element or its ID
    //#content (DOM):           HTML content to display inside hover area
    //#size (int):              hover area size (defaults to 120)
    //-> (void)
    on: function(element, content, size)
    {
        this.hide(true);
        
        //Apply class for size
        this.$hover.className = '';
        this.$hover.addClassName('hover_' + (size || 120));

        element = $(element);
        if(element)
        {
            //Set-up target element
            this.target = element;
            this.target.stopObserving('mouseleave').observe('mouseleave', this.element_leave.bind(this));
            this.target.addClassName('selected');

            //Update hover content
            this.$hover.select('.content')[0].clean().appendChild(content);

            //Position and display hover area
            this.position();
            this.show();
        }
    },

    /*******************************************************
    HIDE/SHOW
    ********************************************************/

    //Hide hover area
    //#force (bool): force hiding (defaults to false)
    //-> (void)
    hide: function(force)
    {
        if(!this.hovered || force)
        {
            if(this.target)
            {
                this.target.removeClassName('selected');
                this.target = null;
            }
            this.hovered = false;   //Reset "hovered" flag (case "force" is true)
            this.$hover.hide();
            
            window.clearTimeout(this.timer);
        }
    },

    //Show hover area
    //-> (void)
    show: function()
    {
        this.$hover.show();
    },

    /*******************************************************
    POSITION
    ********************************************************/

    //Position hover area relatively to an element
    //-> (void)
    position: function()
    {
        var viewport_scroll = document.viewport.getScrollOffsets();     //Browser viewport offset
        var area_dimensions = this.$hover.getDimensions();               //Hover area dimensions
        var dimensions      = this.target.getDimensions();				//Element dimensions
        var position_abs    = this.target.cumulativeOffset();			//Position from absolute top left corner of the website
        var position_scroll = this.target.cumulativeScrollOffset();		//Amount by which element was scrolled

        var display_top     = viewport_scroll.top + position_abs.top - position_scroll.top + dimensions.height;
        var display_left    = viewport_scroll.left + position_abs.left + dimensions.width - area_dimensions.width;

        this.$hover.setStyle({'top': display_top + 'px', 'left': display_left + 'px'});
    },

    /*******************************************************
    CALLBACKS
    ********************************************************/

    //Called when the mouse leaves the element triggering hover area
    //-> (void)
    element_leave: function()
    {
        window.clearTimeout(this.timer);
        this.timer = window.setTimeout(this.timeout.bind(this), this.TIMEOUT);
    },

    //Called when the mouse enters the hover area
    //-> (void)
    enter: function()
    {
        if(this.$hover.visible()) 
            this.hovered = true;
    },

    //Called when the mouse leaves the hover area
    //-> (void)
    leave: function(event)
    {
        event.stop();
        var element = $(event.findElement());
        if(element.tagName == 'SELECT' 
            || element.tagName == 'OPTION') 
            return;

        if(this.$hover.visible())
        {
            this.hovered = false;
            this.hide();
        }
    },

    //Called by the timer
    //#delay (int): timer delay
    //-> (void)
    timeout: function(delay)
    {
        this.hide();
    }
});