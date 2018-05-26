/*******************************************************
Title: Utilities
Authors: Kookiiz Team
Purpose: Toolbox of handy general purpose functions
********************************************************/

//Utilities toolbox
var Utilities =
{
    /*******************************************************
    FUNCTIONALITIES AVAILABILITY
    ********************************************************/

    //Store availability value of specific functionalities
    available:
    {
        'canvas': (function()
        {
            var canvas = document.createElement('canvas');
            return !!(canvas && canvas.getContext && canvas.getContext('2d').fillText)
        })(),
        
        'history': (function()
        {
            return !!(window.history && window.history.pushState);
        })()
    },

    /*******************************************************
    OBSERVERS
    ********************************************************/

    //Attach event listeners to enhance focus/blur events on input fields
    //#container (DOM/string):  container DOM element (or its ID, or false for whole document)
    //#selector (string):       CSS selector for the input fields to observe
    //-> (void)
    observe_focus: function(container, selector)
    {
        container = $(container);

        //Define callbacks
        var blur_callback = function()
        {
            if(this.value == '')
                this.value = this.title;
        };
        var focus_callback = function()
        {
            if(this.value == this.title)
                this.value = '';
        };
        
        var elements = container ? container.select(selector) : $$(selector);
        elements.each(function(el)
        {
            el.stopObserving('blur', 'blur_callback').stopObserving('focus', 'focus_callback');
            el.observe('blur', blur_callback);
            el.observe('focus', focus_callback);
        });
    },

    //Attach a callback for "enter key" event on provided input
    //#input (DOM/string):  input DOM element (or its ID)
    //#callback (function): callback function
    //-> (void)
    observe_return: function(input, callback)
    {
        if(!(input = $(input))) return;

        //Observe key up event on input
        input.observe('keyup', function(event)
        {
            //Don't trigger actions on invisible fields
            if(!this.visible())
            {
                event.stop();
                return;
            }
            //Retrieve key ID
            var keynum = window.event ? event.keyCode : event.which;
            if(keynum == Event.KEY_RETURN) callback(event);
        });
    },

    /*******************************************************
    SPRITES
    ********************************************************/

    //Replace CSS sprites by regular images for printing purposes
    //#selector (string): CSS selector for elements to replace
    //-> (void)
    sprites_replace: function(selector)
    {
        var elements = $$(selector);
        elements.each(function(element)
        {
            var back_position = element.getStyle('background-position');
            if(!back_position) back_position = element.getStyle('background-position-x') + ' ' + element.getStyle('background-position-y');
            var back_image  = element.getStyle('background-image'),
                height      = element.getStyle('height'),
                width       = element.getStyle('width'),
                index1      = back_image.indexOf('http'),
                index2      = back_image.indexOf('")');
                if(index2 < 0) index2 = back_image.indexOf(')');

            back_position   = back_position.split(' ');
            back_image      = back_image.substring(index1, index2);

            var replacement = new Element('a', {'class': 'sprite_replace ' + element.className});
            replacement.setStyle(
            {
                'height':       height,
                'width':        width,
                'background':   'White'
            });
            replacement.style.zoom = 1;
            var img_replace = new Element('img',
            {
                'alt': '',
                'src': back_image
            });
            img_replace.setStyle(
            {
                'marginLeft': back_position[0],
                'marginTop': back_position[1]
            });
            replacement.appendChild(img_replace);
            element.replace(replacement);
        });
    },

    /*******************************************************
    SOURCES
    ********************************************************/

    //Display Kookiiz sources
    //-> (void)
    sources_display: function()
    {
        Kookiiz.popup.custom(
        {
            'title':        SOURCES_TEXT[0],
            'confirm':      true,
            'content_url':  '../dom/sources_popup.php',
            'large':        true
        });
    },
    
    /*******************************************************
    TERMS
    ********************************************************/

    //Display terms of use
    //-> (void)
    terms_display: function()
    {
        Kookiiz.popup.custom(
        {
            'text':         '',
            'title':        TERMS_ALERTS[0],
            'confirm':      true,
            'content_url':  '../dom/terms_popup.php',
            'large':        true
        });
    },
    
    /*******************************************************
    TEXT 2 LINK
    ********************************************************/
   
    //Convert a text to link format (replacing whitespaces and commas)
    text2link: function(text)
    {
        return text.accents_strip().gsub(/[,]/, '').gsub(/['\s]+/, '-').toLowerCase();
    },

    /*******************************************************
    VIEWPORT
    ********************************************************/

    //Scroll window up
    //-> (void)
    viewport_reset: function()
    {
        window.scrollTo(0, 0);
    }
}