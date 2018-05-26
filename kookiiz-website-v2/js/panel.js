/*******************************************************
Title: Panel
Authors: Kookiiz Team
Purpose: Define a side panel object
********************************************************/

//Represents a side panel
var Panel = Class.create(Observable,
{
    object_name: 'panel',

    /*******************************************************
    CONSTANTS
    ********************************************************/

    DISABLED_OPACITY: 0.4,  //Opacity value of disabled panels

    /*******************************************************
    CONSTRUCTOR
    ********************************************************/

    //Class constructor
    //#panel (DOM): panel DOM element
    //-> (void)
    initialize: function(panel)
    {
        this.id         = parseInt(panel.id.split('_')[1]);
        this.element    = panel;
        this.frozen     = false;
        this.disabled   = false;

        //Retrieve panel components
        this.ICON_HELP      = this.element.select('img.panel_help')[0];
        this.ICON_TOGGLE    = this.element.select('img.panel_toggle')[0];

        //Disable panel if class name is set
        if(this.element.hasClassName('disabled'))
        {
            this.disable();
        }
        //Freeze panel PERMANENTLY if class name is set
        if(this.element.hasClassName('frozen'))
        {
            this.freeze(true);
        }
        //Init event listeners
        this.ICON_HELP.observe('click', this.help_click.bind(this));
        this.ICON_TOGGLE.observe('click', this.toggle_click.bind(this));
    },

    /*******************************************************
    ATTACH
    ********************************************************/

    //Attach panel to a given column
    //#column (DOM/string): column DOM element (or its ID)
    //-> (void)
    attach: function(column)
    {
        column = $(column);
        if(column) column.appendChild(this.element);
    },

    /*******************************************************
    DISABLE/ENABLE
    ********************************************************/

    //Disable panel
    //Disabled panels are semi-transparent and cannot be opened
    //-> (void)
    disable: function()
    {
        //Disable panel functionality
        this.element.setOpacity(this.DISABLED_OPACITY);
        this.element.addClassName('disabled');
        this.toggle('close', true);
        this.freeze();

        //Set status variable
        this.disabled = true;
    },

    //Enable panel
    //-> (void)
    enable: function()
    {
        //Unset status variable
        this.disabled = false;

        //Enable full panel functionality
        this.element.setOpacity(1);
        this.element.removeClassName('disabled');
        this.toggle('open', true);
        this.unfreeze();
    },

    /*******************************************************
    FREEZE
    ********************************************************/

    //Freeze panel
    //Frozen panels cannot be toggled
    //#permanent (bool): whether the freeze must be permanent
    //-> (void)
    freeze: function(permanent)
    {
        this.ICON_TOGGLE.hide();
        this.frozen = true;
        if(permanent) this.unfreeze = function(){}; //Freezing cannot be canceled
    },

    //Cancel freezing (if panel is not disabled)
    //-> (void)
    unfreeze: function()
    {
        if(!this.is_disabled())
        {
            this.ICON_TOGGLE.show();
            this.frozen = false;
        }
    },

    /*******************************************************
    HEADER
    ********************************************************/

    //Set panel header
    //#text (string): text to write on panel header
    //-> (void)
    header_set: function(text)
    {
        if(!text || text == 'undefined') text = PANELS_HEADERS[this.id];
		this.element.select('.panel_header')[0].innerHTML = text;
    },

    /*******************************************************
    MOVE
    ********************************************************/

    //Move panel at the very bottom of its current column
    //-> (void)
    move_down: function()
    {
        var column = this.element.parentNode;
        column.appendChild(this.element);
    },

    //Move panel at the very top of its current column
    //-> (void)
    move_up: function()
    {
        var column = this.element.parentNode;
        column.insertBefore(this.element, column.firstChild);
    },

    /*******************************************************
    PROPERTIES
    ********************************************************/

    //Return panel numerical ID
    //->id (int): panel ID
    get_id: function()
    {
        return this.id;
    },

    //Return current panel side
    //->side (int): 0 = left, 1 = right
    get_side: function()
    {
        return this.element.parentNode.id.split('_')[2] == 'left' ? 0 : 1;
    },

    //Return current panel status
    //->status (int): 0 = closed, 1 = open
    get_status: function()
    {
        return this.element.select('.panel_content')[0].visible() ? 1 : 0;
    },

    //Check if panel is disabled
    //->disabled (bool): true if panel is disabled
    is_disabled: function()
    {
        return this.disabled;
    },

    //Check if panel is frozen
    //->frozen (bool): true if panel is frozen
    is_frozen: function()
    {
        return this.frozen;
    },

    /*******************************************************
    SHOW AND HIDE
    ********************************************************/

    //Hide panel
    //-> (void)
    hide: function()
    {
        this.element.hide();
    },

    //Show panel
    //#fastMode (bool): if true panel is shown without transition effect
    //-> (void)
    show: function(fastMode)
    {
        if(fastMode) 
            this.element.show();
        else
        {
            var opacity = this.is_disabled() ? this.DISABLED_OPACITY : 1.0;
            new Effect.Appear(this.element, {'duration': 0.5, 'to': opacity});
        }
    },

    /*******************************************************
    TOGGLE
    ********************************************************/

    //Toggle visibility of panel content
    //#mode (string):   either "open" or "close"
    //#fastMode (bool): if set to true, toggling does not use any transition effect
    //#user (bool):     whether the toggling was user triggered
    //-> (void)
    toggle: function(mode, fastMode, user)
    {
        //Disabled and frozen panels cannot be toggled
        if(!this.is_disabled() && !this.is_frozen())
        {
            //Get toggle icon and panel content
            var panel_content = this.element.select('.panel_content')[0];

            //Toggle
            var queue, queue_id = 'panel' + this.id;
            if(mode == 'close' && this.get_status())
            {
                //Close panel
                queue = Effect.Queues.get(queue_id);
                if(!queue || !queue.size())
                {
                    if(fastMode)
                    {
                        panel_content.hide();
                        if(user) this.fire('toggle');
                    }
                    else            
                        Effect.SlideUp(panel_content, 
                        {
                            'duration':     0.5,
                            'queue':        {'scope': queue_id},
                            'afterFinish':  user ? this.fire.bind(this, 'toggle') : false
                        });
                    
                    this.ICON_TOGGLE.removeClassName('arrow_up').addClassName('arrow_down');
                    this.ICON_TOGGLE.title = PANELS_TEXT[5];
                    this.ICON_TOGGLE.alt   = PANELS_TEXT[4];
                }
            }
            else if(mode == 'open' && !this.get_status())
            {
                //Open panel
                queue = Effect.Queues.get(queue_id);
                if(!queue || !queue.size())
                {
                    if(fastMode)
                    {
                        panel_content.show();
                        if(user) this.fire('toggle');
                    }
                    else            
                        Effect.SlideDown(panel_content, 
                        {
                            'duration':     0.5, 
                            'queue':        {'scope': queue_id},
                            'afterFinish':  user ? this.fire.bind(this, 'toggle') : false
                        });
                    
                    this.ICON_TOGGLE.removeClassName('arrow_down').addClassName('arrow_up');
                    this.ICON_TOGGLE.title = PANELS_TEXT[7];
                    this.ICON_TOGGLE.alt   = PANELS_TEXT[6];
                }
            }
        }
    },

    /*******************************************************
    CALLBACKS
    ********************************************************/

    //Called when help icon is clicked
    //#event (event): DOM click event
    //-> (void)
    help_click: function(event)
    {
        event.stop();
        this.fire('help');
    },

    //Called when the panel toggle button is clicked
    //-> (void)
    toggle_click: function()
    {
        if(!this.frozen)
        {
            var mode = this.get_status() ? 'close' : 'open'
            this.toggle(mode, User.option_get('fast_mode'), true);
        }
    }
});