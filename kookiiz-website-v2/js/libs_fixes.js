/*******************************************************
Title: Libs fixes
Authors: Kookiiz Team
Purpose: Fixes & additions to JS libraries
********************************************************/

/*******************************************************
ARRAYS
********************************************************/

//Custom methods for Array object

//Map executes the same function for all items of an array
//Original array is NOT modified
//Example: [9,4].map(Math.sqrt) -> [3,2]
//#fun (function): function to apply to each element
//->res (array): resulting array
if(!Array.prototype.map)
{
	Array.prototype.map = function(fun)
	{
		var len = this.length >>> 0;
		if(typeof fun != 'function')
        {
            throw new TypeError();
        }
		var res = new Array(len);
		var thisp = arguments[1];
		for(var i = 0; i < len; i++)
		{
			if(i in this) res[i] = fun.call(thisp, this[i], i, this);
		}
		return res;
	};
}

//Parse all array values to a given type
//#type (string): value type
//->res (array): resulting array
if(!Array.prototype.parse)
{
    Array.prototype.parse = function(type)
    {
        return this.map(function(val)
        {
            switch(type)
            {
                case 'float':
                    return parseFloat(val)
                    break;
                case 'int':
                    return parseInt(val);
                    break;
            }
        });
    };
}

//Fisher-Yates randomization algorithm
//->random_array (array): a randomized COPY of the array
if(!Array.prototype.random)
{
    Array.prototype.random = function()
    {
      var random_array  = this.slice();
      var i             = random_array.length;
      if(i == 0) return false;

      var j, tempi, tempj;
      while(--i)
      {
         j               = Math.floor(Math.random() * (i + 1));
         tempi           = random_array[i];
         tempj           = random_array[j];
         random_array[i] = tempj;
         random_array[j] = tempi;
       }
       return random_array;
    }
}

/*******************************************************
AUTOCOMPLETERS
********************************************************/

//Fixes to scriptaculous autocompleter to avoid window scroll during keyboard selection
Autocompleter.Base.prototype.markPrevious = function()
{
    if(this.index > 0) {this.index--;}
    else
    {
        this.index = this.entryCount - 1;
        this.update.scrollTop = this.update.scrollHeight;
    }
    var selection = this.getEntry(this.index);
    var selection_top = selection.offsetTop;
    if(selection_top < this.update.scrollTop)
    {
        this.update.scrollTop = this.update.scrollTop - selection.offsetHeight;
    }
}
Autocompleter.Base.prototype.markNext = function()
{
    if(this.index < this.entryCount - 1) {this.index++;}
    else
    {
        this.index = 0;
        this.update.scrollTop = 0;
    }
    var selection = this.getEntry(this.index);
    var selection_bottom = selection.offsetTop + selection.offsetHeight;
    if(selection_bottom > this.update.scrollTop + this.update.offsetHeight)
    {
        this.update.scrollTop = this.update.scrollTop + selection.offsetHeight;
    }
}
Autocompleter.Base.prototype.updateChoices = function(choices)
{
    if(!this.changed && this.hasFocus)
    {
        this.update.innerHTML = choices;
        Element.cleanWhitespace(this.update);
        Element.cleanWhitespace(this.update.down());

        if(this.update.firstChild && this.update.down().childNodes)
        {
            this.entryCount = this.update.down().childNodes.length;
            for(var i = 0; i < this.entryCount; i++)
            {
                var entry = this.getEntry(i);
                entry.autocompleteIndex = i;
                this.addObservers(entry);
            }
        }
        else
        {
            this.entryCount = 0;
        }

        this.stopIndicator();
        this.index = 0;

        if(this.entryCount == 1 && this.options.autoSelect)
        {
            this.selectEntry();
            this.hide();
        }
        else
        {
            this.render();
        }
    }
}

/*******************************************************
BROWSER
********************************************************/

//Additional browser information
Prototype.Browser.IE6 = Prototype.Browser.IE && parseInt(navigator.userAgent.substring(navigator.userAgent.indexOf("MSIE")+5)) == 6;
Prototype.Browser.IE7 = Prototype.Browser.IE && parseInt(navigator.userAgent.substring(navigator.userAgent.indexOf("MSIE")+5)) == 7;
Prototype.Browser.IE8 = Prototype.Browser.IE && !Prototype.Browser.IE6 && !Prototype.Browser.IE7;

/*******************************************************
DRAGGABLES
********************************************************/

//Method to check if any draggable is currently being dragged
//->draggable (object): active draggable or NULL
Draggables.dragging = function()
{
    return this.activeDraggable;
};

//Fix "isAffected" to take into account window scroll
//Without this the recipes cannot be dropped properly into the menu when the window is scrolled!
Droppables.isAffected = function(point, element, drop)
{
    var scroll = [0, 0];
    if(drop.posFixed)
        scroll = drop.element.cumulativeScrollOffset();
    else if(drop.scrollParent)
        scroll = [-drop.scrollParent.scrollLeft, -drop.scrollParent.scrollTop];
    
    return (
      (drop.element!=element) &&
      ((!drop._containers) ||
        this.isContained(element, drop)) &&
      ((!drop.accept) ||
        (Element.classNames(element).detect(
          function(v) { return drop.accept.include(v) } ) )) &&
      Position.within(drop.element, point[0] - scroll[0], point[1] - scroll[1]) );
}

//Fix draggable properties on drag start
//#draggable (object): Scriptaculous draggable object
//-> (void)
function draggable_fix_start(draggable)
{
    //Clone mode
    if(draggable.options.ghosting)
    {
        //Resize box properly using clone dimensions
        draggable.element.style.height  = draggable._clone.style.height;
        draggable.element.style.width   = draggable._clone.style.width;

        //Force clone to background
        draggable._clone.setStyle({'zIndex': 0});
    }

    //Fixes scroll offset
    var scroll          = draggable.element.cumulativeScrollOffset();
    var window_scroll   = document.viewport.getScrollOffsets();
    draggable.offset[0] += scroll[0] - window_scroll[0];
    draggable.offset[1] += scroll[1] - window_scroll[1];

    //Store element parent and sibling in draggable object
	draggable.parent    = draggable.element.parentNode;
	draggable.sibling   = draggable.element.next() || false;

    //Absolutize element position
    draggable.element.style.position = 'absolute';
    if(draggable.element.tagName == 'LI')
        $('draggable_fake_list').appendChild(draggable.element);
    else 
        document.body.appendChild(draggable.element);
}

//Fix draggable properties on drag stop
//#draggable (object): Scriptaculous draggable object
//-> (void)
function draggable_fix_stop(draggable)
{
    //Relativize element position
    draggable.element.style.position = 'relative';
    draggable.element.setStyle({'top': 0, 'left': 0});

    //Try to return element in its original container
    if($(draggable.sibling))        
        draggable.parent.insertBefore(draggable.element, draggable.sibling);
    else if($(draggable.parent))    
        draggable.parent.appendChild(draggable.element);
    else                            
        draggable.element.remove();
}

/*******************************************************
ELEMENTS
********************************************************/

//Custom methods for Prototype DOM Element object

//ALL
Element.addMethods(
{
    //Append a text node properly (shortcut for document.createTextNode)
    //#element (DOM/string): DOM element (or its ID)
    //->element (DOM): DOM element
    appendText: function(element, text)
    {
        if(!(element = $(element))) return null;
        element.appendChild(document.createTextNode(text));
        return element;
    },
    
    //Remove all content from element
    //#element (DOM/string): DOM element (or its ID)
    //->element (DOM): DOM element
    clean: function(element)
    {
        if(!(element = $(element))) return null;
        var children = element.childElements();
        children.each(function(el)
        {
            el.purge();
            el.remove();
        });
        element.innerHTML = '';     //For remaining text nodes, any alternative ?
        return element;
    },

    //Highligh provided element with Effect.Highlight
    //#element (DOM/string):    DOM element (or its ID)
    //#queue (string):          effect queue (optional)
    //->element (DOM): DOM element
    highlight: function(element, queue)
    {
        if(!(element = $(element))) return null;
        if(!queue)
        {
            //If no queue is provided, a default queue is created
            //In this case, effect creation is aborted if the queue is not empty (to avoid serial highlights)
            queue = 'highlight_' + element.identify();
            if(Effect.Queues.get(queue) 
                && Effect.Queues.get(queue).size()) 
                return null;
        }
        new Effect.Highlight(element,
        {
            'startcolor':           COLOR_SECONDARY,
            'endcolor':             COLOR_BACKGROUND,
            'duration':             1,
            'keepBackgroundImage':  true,
            'queue':                {'scope': queue}
        });
        return element;
    },

    //Create loader in element
    //#element (DOM/string): DOM element (or its ID)
    //#big (bool): if true a big loader is displayed (defaults to false)
    //->element (DOM): DOM element
    loading: function(element, big)
    {
        if(!(element = $(element)))     return;
        if(typeof(big) == 'undefined')  big = false;

        //Empty element
        element.clean();

        //Create loader
        var loader_class = 'loader ' + (big ? 'big' : 'small');
        var icon = new Element('img',
        {
            'alt':      'Loader',
            'class':    loader_class,
            'src':      '/pictures/icons/loader' + (big ? '_big' : '') + '.gif'
        });
        var text = new Element('span', {'class': loader_class});
        text.innerHTML = VARIOUS[0];
        element.appendChild(icon);
        element.appendChild(text);

        //Return element
        return element;
    }
});

//INPUT, TEXTAREA
Element.addMethods(['INPUT', 'TEXTAREA'],
{
    //Limit the number of characters in an input
    //#element (DOM/string):    input field DOM element (or its ID)
    //#limit (int):             max number of chars for the input
    //#display (DOM/string):    DOM element where chars left should be displayed (optional)
    //->element (DOM): DOM element
    chars_limit: function(element, limit, display)
    {
        if(!(element = $(element))) return;
        display = $(display);

        //Compute chars left
        var chars_left = limit - element.value.length;
        //Case where the max is reached
        if(chars_left < 0)
        {
            element.value = element.value.substring(0, limit);
            chars_left    = 0;
        }

        //Display chars left
        if(display)
        {
            if(chars_left)  
                display.innerHTML = chars_left + ' ' + VARIOUS[8];
            else            
                display.innerHTML = VARIOUS[10] + '!';
        }

        //Return element
        return element;
    },

    //Hide text element and show input for edition / Hide input and show text element upon validation
    //#element (DOM/string):    input field DOM element (or its ID)
    //#text (DOM/string):       text DOM element
    //#callback (function):     the function called to revert the process
    //#icon (DOM):              img object or object ID that switches between "edit" and "accept" icons (optional)
    //->element (DOM): DOM element
    swap: function(element, text, callback, icon)
    {
        if(!(element = $(element))) return;
        if(!(text = $(text)))       return;
        icon = $(icon) || false;

        //Case where text element is visible
        if(text.visible())
        {
            //Hide text element and display input for edition
            text.hide();
            element.value = text.innerHTML != element.title ? text.innerHTML : '';
            element.show().stopObserving('blur').observe('blur', callback).focus();         
            if(icon) 
                icon.removeClassName('edit').addClassName('accept');
        }
        //Case where text element is hidden
        else
        {
            //See if there is at least one character (other than whitespaces) in input
            //If not, display input title in text field (instead of input value)
            if(/\S/.test(element.value))
                text.clean().appendChild(document.createTextNode(element.value));
            else 
                text.clean().appendChild(document.createTextNode(element.title));

            //Hide input and display text element
            element.hide().stopObserving('blur');
            text.show().stopObserving('click').observe('click', callback);
            if(icon) 
                icon.removeClassName('accept').addClassName('edit');
        }

        //Icon action
        if(icon) 
            icon.stopObserving('click').stopObserving('mousedown').observe('mousedown', callback);

        //Return element
        return element;
    },

    //Focus input field and clear its value
    //#element (DOM/string): input field DOM element (or its ID)
    //->element (DOM): DOM element
    target: function(element)
    {
        if(!(element = $(element))) return;

        if(element.value == element.title)
            element.value = '';
        element.focus();

        //Return element
        return element;
    }
});

//BUTTON, INPUT, TEXTAREA, SELECT
Element.addMethods(['BUTTON', 'INPUT', 'SELECT', 'TEXTAREA'],
{
    //Freeze input element
    //#element (DOM/string): input field DOM element (or its ID)
    //->element (DOM): input DOM element
    freeze: function(element)
    {
        if(!(element = $(element))) return;

        element.disabled = true;
        element.addClassName('disabled');

        //For special Kookiiz inputs
        var parent = $(element.parentNode);
        if(parent.hasClassName('input_wrap')) 
            parent.addClassName('disabled');

        //Return element
        return element;
    },

    //Un-freeze input element
    //#element (DOM/string): input field DOM element (or its ID)
    //->element (DOM): input DOM element
    unfreeze: function(element)
    {
        if(!(element = $(element))) return;

        element.disabled = false;
        element.removeClassName('disabled');

        //For special Kookiiz inputs
        var parent = $(element.parentNode);
        if(parent.hasClassName('input_wrap')) 
            parent.removeClassName('disabled');

        //Return element
        return element;
    }
});

//SELECT
Element.addMethods('SELECT',
{
    //Search for option value in select menu and return option index
    //#element (DOM/string): select menu DOM element (or its ID)
    //#value (mixed):        value to search for
    //->index (int): index of the option corresponding to provided value (-1 if not found)
    value_search: function(element, value)
    {
        if(!(element = $(element))) return;

        var options = element.select('option');
        for(var i = 0, imax = options.length; i < imax; i++)
        {
            if(options[i].value == value) 
                return i;
        }
        return -1;
    },

    //Select option with provided value
    //#element (DOM/string): select menu DOM element (or its ID)
    //#value (mixed):        value to select
    //->index (int/bool): index of the option corresponding to provided value (-1 if not found)
    value_set: function(element, value)
    {
        if(!(element = $(element))) return;

        var options = element.select('option');
        for(var i = 0, imax = options.length; i < imax; i++)
        {
            if(options[i].value == value) 
                return element.selectedIndex = i;
        }
        return -1;
    }
});

/*******************************************************
STRING
********************************************************/

//Custom methods for String object

//Replace all kinds of line feeds ("\n\r", "\r\n", "\n" or "\r") by "<br/>" (HTML line break)
//->out (string): output string
String.prototype.linefeed_replace = function()
{
    return this.replace(/(\n\r|\r\n|\n|\r)/g, '<br/>');
};

//JS-equivalent of PHP preg_replace function
//#pattern (array):         list of chars to replace
//#pattern_replace (array): list of replacements
//->out (string): output string
if(!String.prototype.preg_replace)
{
    String.prototype.preg_replace = function(pattern, pattern_replace)
    {
        var out = String(this);
        for(var i = 0, imax = pattern.length; i < imax; i++)
        {
            out = out.replace(RegExp(pattern[i], 'gi'), pattern_replace[i]);
        }
        return out;
    };
}

//Remove accentuated chars from string
//->out (string): string without accents
String.prototype.accents_strip = function()
{
	var pattern_accent  = ['é', 'è', 'ê', 'ë', 'ç', 'à', 'â', 'ä', 'î', 'ï', 'ù', 'ô', 'ó', 'ö'],
        replace_accent  = ['e', 'e', 'e', 'e', 'c', 'a', 'a', 'a', 'i', 'i', 'u', 'o', 'o', 'o'];
	return this.preg_replace(pattern_accent, replace_accent)
}