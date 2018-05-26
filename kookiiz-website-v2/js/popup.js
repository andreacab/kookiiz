/*******************************************************
Title: Popup
Authors: Kookiiz Team
Purpose: Manage popup events and display popup window
********************************************************/

//Represents a popup event instance
var PopupEvent = Class.create(
{
	object_name: 'popup_event',

	/*******************************************************
    CONSTRUCTOR
    ********************************************************/

    //Class constructor
    //#mode (string):       popup event mode ("alert", "confirm", etc.)
    //#options (object):    popup event options structure
    //-> (void)
	initialize: function(mode, options)
    {
        this.mode               = mode;

        //Defaults
        this.text               = '';
        this.title              = '';
        this.large              = false;
        this.confirm            = false;
        this.confirm_label      = ACTIONS[22];
        this.cancel             = false;
        this.cancel_label       = ACTIONS[5];
        this.callback           = false;
        this.content_init       = false;
        this.content_url        = '';
        this.content_parameters = '';

        //Load options
        Object.extend(this, options || {});

        //Setup popup event
        this.setup();
    },

    /*******************************************************
    SETUP
    ********************************************************/

    //Setup popup event depending on its mode
    //-> (void)
    setup: function()
    {
        switch(this.mode)
        {
            case 'alert':
                this.title      = POPUP_TEXT[0];
                this.confirm    = true;
                break;
            case 'confirm':
                this.title      = POPUP_TEXT[1];
                this.confirm    = true;
                this.cancel     = true;
                break;
            case 'loader':
                this.title      = POPUP_TEXT[2];
                break;
        }
    }
});

//Represents a custom popup handler
var PopupHandler = Class.create(
{
    object_name: 'popup_handler',

    /*******************************************************
    CONSTRUCTOR
    ********************************************************/

    //Class constructor
    //-> (void)
    initialize: function()
    {
        this.event  = null;
        this.mode   = false;
        this.frozen = false;
        this.events = [];

        //Store main DOM nodes
        this.$window  = $('kookiiz_popup');
        this.$curtain = $$('div.curtain.popup')[0];
        this.$content = this.$window.select('.content')[0]
        this.$loader  = this.$window.select('.loader')[0];
    },

    /*******************************************************
    POPUP TRIGGERS
    ********************************************************/

    //Display an alert-style popup
    //#options (object): structure with popup options
    //-> (void)
    alert: function(options)
    {
        this.events.push(new PopupEvent('alert', options));
        this.pop();
    },

    //Display a confirm-style popup
    //#options (object): structure with popup options
    //-> (void)
    confirm: function(options)
    {
        this.events.push(new PopupEvent('confirm', options));
        this.pop();
    },

    //Display a popup with custom content
    //#options (object): structure with popup options
    //-> (void)
    custom: function(options)
    {
        this.events.push(new PopupEvent('custom', options));
        this.pop();
    },

    //Display a loader in a popup
    //-> (void)
    loader: function()
    {
        this.events.push(new PopupEvent('loader'));
        this.pop();
    },
    
    /*******************************************************
    FREEZE
    ********************************************************/
   
    //Freeze popup (prevents hiding)
    //-> (void)
    freeze: function()
    {
        this.frozen = true;
    },
    
    //Cancel freezing
    //-> (void)
    freezeCancel: function()
    {
        this.frozen = false;
    },
        
    /*******************************************************
    HIDE
    ********************************************************/

    //Hide current popup window and trigger next event
    //-> (void)
    hide: function()
    {
        if(!this.frozen)
        {
            this.$window.hide();
            this.$curtain.hide();
            this.pop();
        }
    },

    /*******************************************************
    POP
    ********************************************************/

    //Pop next event
    //-> (void)
    pop: function()
    {
        //Abort if a popup is already displayed (except for loader)
        if(this.$window.visible() && this.mode_get() != 'loader')
            return;
        //Display next event (if any)
        else if(this.events.length)
        {
            //Empty popup content
            this.$content.clean();

            //Display next popup event
            this.event = this.events[0];
            this.events.splice(0, 1);
            this.display();

            //Position popup window
            this.position();

            //Update popup mode value
            this.mode_set(this.event.mode);
        }
        else
        {
            this.event = null;
            this.mode_set(false);
        }
    },

    /*******************************************************
    MODE
    ********************************************************/

    //Get current popup mode
    //->mode (string): popup mode (false if popup is hidden)
    mode_get: function()
    {
        return this.mode;
    },

    //Set current popup mode
    //#mode (string): popup mode (false for no popup)
    //-> (void)
    mode_set: function(mode)
    {
        this.mode = mode;
    },

    /*******************************************************
    POSITION
    ********************************************************/

    //Position popup window
    //-> (void)
    position: function()
    {
        var popup      = this.$window.getLayout(),
            viewport   = document.viewport.getDimensions(),
            scroll     = document.viewport.getScrollOffsets(),
            popup_top  = Math.round(scroll.top + (viewport.height - popup.get('height')) / 3),
            popup_left = Math.round(scroll.left + (viewport.width - popup.get('width')) / 2);
        this.$window.setStyle({'top': popup_top + 'px', 'left': popup_left + 'px'});
    },

    /*******************************************************
    DISPLAY
    ********************************************************/

    //Display a popup on screen
    //-> (void)
    display: function()
    {
        try
        {
            //Hide window during setup
            this.$window.hide();

            //Clicking on curtain hide popup
            this.$curtain.stopObserving('click').observe('click', this.hide.bind(this));

            //Check if a large popup should be used
            if(this.event.large)
                this.$window.addClassName('large');
            else
                this.$window.removeClassName('large');

            //Retrieve DOM components
            var title   = this.$window.select('.title')[0],
                loader  = this.$window.select('.loader')[0],
                middle  = this.$window.select('.middle')[0],
                message = this.$window.select('.message')[0],
                content = this.$window.select('.content')[0],
                bottom  = this.$window.select('.bottom')[0],
                close   = this.$window.select('img.cancel')[0],
                confirm = this.$window.select('button.confirm')[0],
                cancel  = this.$window.select('button.deny')[0];

            //Text
            title.innerHTML = this.event.title;
            if(this.event.text)
            {
                message.innerHTML = this.event.text;
                message.show();
            }
            else
                message.hide();

            //Content
            if(this.event.content_url)
            {
                this.content_load();
                content.show();
            }
            else
                content.hide();

            //Buttons
            bottom.hide();
            [close, confirm, cancel].invoke('stopObserving', 'click');
            close.stopObserving('click').observe('click', this.hide.bind(this));
            if(this.event.confirm)
            {
                confirm.innerHTML = this.event.confirm_label;
                confirm.observe('click', this.callback.bind(this, 'confirm'));
                confirm.show();
                bottom.show();
            }
            else
                confirm.hide();
            if(this.event.cancel)
            {
                cancel.innerHTML = this.event.cancel_label;
                cancel.observe('click', this.callback.bind(this, 'cancel'));
                cancel.show();
                bottom.show();
            }
            else
                cancel.hide();

            //Loader-specific
            if(this.event.mode == 'loader')
            { 
                this.$loader.loading(true);
                middle.hide();
                loader.show();
                close.hide();
            }
            else
            {
                loader.hide();
                middle.show();
                close.show();
            }

            //Show curtain and popup
            this.$curtain.show();
            this.$window.show();
        }
        catch(error){Kookiiz.error.catcher(error);}
    },

    /*******************************************************
    CONTENT
    ********************************************************/

    //Load custom popup event content from URL
    //-> (void)
    content_load: function()
    {
        if(this.event.content_url)
        {
            this.$content.loading(true);
            Kookiiz.ajax.request(this.event.content_url, 'get',
            {
                'callback': this.content_parse.bind(this),
                'json':     false,
                'request':  this.event.content_parameters
            });
        }
    },

    //Parse custom popup content received from server
    //#content (DOM): HTML content
    //-> (void)
    content_parse: function(content)
    {
        //Display loaded content
        this.$content.innerHTML = content;

        //Reset popup position
        this.position();

        //Attach generic event listeners to inputs (for focus and blur)
        Utilities.observe_focus(this.$content, 'input.focus, textarea.focus');
        //Call content init callback (if any)
        if(this.event.content_init)
            this.event.content_init();
        //Eval scripts in the popup content
        content.evalScripts();
    },

    /*******************************************************
    RELOAD
    ********************************************************/

    //Reload current popup event (for custom popup only)
    //#parameters (string): new content parameters (optional)
    //#callback (function): new function to call once content has been reloaded (optional)
    //-> (void)
    reload: function(parameters, callback)
    {
        if(this.mode_get() == 'custom')
        {
            if(parameters)
                this.event.content_parameters = parameters;
            if(callback)
                this.event.content_init = callback;
            this.display();
        }
    },

    /*******************************************************
    CALLBACK
    ********************************************************/

    //Generic callback for popup "confirm" and "cancel" buttons
    //Calls specific callback for current popup event (if any)
    //#mode (string): either "confirm" or "cancel"
    //-> (void)
    callback: function(mode)
    {
        if(this.event && this.event.callback)
            this.event.callback(mode == 'confirm');
        this.hide();
    }
});