/*******************************************************
Title: Ingredients database
Authors: Kookiiz Team
Purpose: Store and manage ingredient objects
********************************************************/

//Represents an ingredient database interface
var IngredientsDB = Class.create(
{
    object_name: 'ingredients_db',

    /*******************************************************
    CONSTRUCTOR
    ********************************************************/

    //Class constructor
    //-> (void)
    initialize: function()
    {
        this.db      = [];   //Ingredient database
        this.matches = [];   //Ingredient names for autocompleters
        this.season  = [];   //IDs of seasonal ingredients

        //Autocompleter suggestions container
        this.$suggest = new Element('div',
        {
            'class':    'suggest',
            'id':       'autocompleter'
        });
        this.$suggest.hide();
        document.body.appendChild(this.$suggest);
    },

    /*******************************************************
    AUTOCOMPLETERS
    ********************************************************/

    //Custom function to suggest ingredients while user is typing something in an input field
    //#instance (object): autocompleter object as defined by scriptaculous
    //->suggestions_list (DOM): HTML content of the list of suggestions (<ul>)
    autocomplete: function(instance)
    {
        var matches = [],   //Matches at the beggining of the string
            partial = [],   //Matches inside the string
            fromTag = [],   //Matches in ingredient tags
            entry = instance.getToken().accents_strip();  //Typed text

        var ingredients = instance.options.array, ingredient = null,
            name = '', name_clean = '', tags_clean = '', pos, pattern = /\s|\(/;
        for(var i = 0, imax = ingredients.length; i < imax; i++)
        {
            //Retrieve ingredient properties
            ingredient  = this.db[ingredients[i]];
            name        = ingredient.name;
            name_clean  = ingredient.name_clean;
            tags_clean  = ingredient.tags_clean;

            //Search typed text in ingredient name (taking into account case-sensitive option)
            pos = instance.options.ignoreCase ? name_clean.toLowerCase().indexOf(entry.toLowerCase()) : name_clean.indexOf(entry);

            //Match at the beginning of the string
            if(pos == 0)
            {
                //Add match to array
                matches.push('<li><strong>' + name.substr(0, entry.length) + '</strong>' + name.substr(entry.length) + '</li>');
            }
            //Match inside the string
            else if(pos > 0 && instance.options.partialSearch && entry.length >= instance.options.partialChars)
            {
                //Check if fullSearch option is set OR there is a space or a "(" before the match
                //Else reject the match (because it is inside a word)
                if(instance.options.fullSearch || pattern.test(name_clean.substr(pos - 1,1)))
                {
                    partial.push('<li>' + name.substr(0, pos)
                                        + '<strong>' + name.substr(pos, entry.length) + '</strong>'
                                        + name.substr(pos + entry.length) + '</li>');
                }
            }
            //No match in ingredient name -> search typed text in ingredient tags
            else if(pos < 0)
            {
                var tag;
                for(var j = 0, jmax = tags_clean.length; j < jmax; j++)
                {
                    tag = tags_clean[j];
                    pos = instance.options.ignoreCase ? tag.toLowerCase().indexOf(entry.toLowerCase()) : tag.indexOf(entry);
                    if(pos == 0)
                    {
                        //Add match to array
                        fromTag.push('<li>' + name + '</li>');
                        break;
                    }
                }
            }

            //Stop search if enough matches were found
            if(matches.length >= instance.options.choices) break;
        }

        //Append partial and tag matches to general matches
        if(partial.length)
            matches = matches.concat(partial.slice(0, instance.options.choices - matches.length));
        if(fromTag.length)
            matches = matches.concat(fromTag.slice(0, instance.options.choices - matches.length));

        //Return results as a list
        return '<ul>' + matches.join('') + '</ul>';
    },

    //Init an ingredient autocompleter
    //#input (DOM/string):  input field DOM element (or its ID)
    //#callback (function): function to call upon ingredient selection
    //-> (void)
    autocompleter_init: function(input, callback)
    {
        new Autocompleter.Local(input, this.$suggest, this.matches,
		{
			'minChars':             1,
			'choices':              10,
			'selector':             this.autocomplete.bind(this),
			'afterUpdateElement':   this.autocompleter_select.bind(this, callback)
		});
    },

    //Callback for ingredient selection in autocompleter
    //#callback (function): function to call with ingredient object
    //#input (DOM)          input field DOM element
    //#selection (DOM):     selection DOM element
    //-> (void)
    autocompleter_select: function(callback, input, selection)
    {
        if(callback)
        {
            var ingredient = this.search(input.value.stripTags());
            if(ingredient) 
                callback(ingredient);
        }
    },

    //Sort ingredients for autocompleters
    //#id_a (int): ID of the first ingredient to sort
    //#id_b (int): ID of the second ingredient to sort
    //->sorting (int): -1 (a before b), 0 (no sorting), 1 (a after b)
    autocompleter_sort: function(id_a, id_b)
    {
        var ing_a = this.db[id_a],
            ing_b = this.db[id_b];
        return ing_a.prob > ing_b.prob ? -1 : (ing_a.prob < ing_b.prob ? 1 : (ing_a.name < ing_b.name ? -1 : 1));
    },

    /*******************************************************
    GETTERS
    ********************************************************/

    //Return ingredient with provided ID
    //#id (int): unique ingredient ID
    //->ingredient (object): ingredient object ("undefined" if not found)
    get: function(id)
    {
        return this.db[id];
    },
    
    //Get a list of seasonal ingredients
    //->ingredients (array): list of ingredient IDs
    getSeason: function()
    {
        return this.season.slice();
    },

    /*******************************************************
    IMPORT
    ********************************************************/

    //Load ingredients from server
    //#data (object): ingredients database as exported from server
    //-> (void)
    importDB: function(data)
    {
        //Reset arrays
        this.db      = [];
        this.matches = [];
        
        //Loop through server data
        var properties = {}, prop = '', type = '', value = null;
        for(var i = 0, imax = data.id.length; i < imax; i++)
        {
            for(var j = 0, jmax = ING_PROPERTIES.length; j < jmax; j++)
            {
                prop  = ING_PROPERTIES[j];
                type  = ING_DATATYPES[j];
                value = data[prop][i];
                switch(type)
                {
                    case 'f':
                        value = parseFloat(value);
                        break;
                    case 'i':
                        value = parseInt(value);
                        break;
                }
                properties[prop] = value;
            }

            //Create clean strings and tags as an array
            properties['name_clean'] = properties['name'].accents_strip();
            properties['tags_clean'] = properties['tags'].accents_strip();
            properties['tags_clean'] = properties['tags_clean'].split(',');
            properties['tags']       = properties['tags'].split(',');

            //Retrieve nutritional values
            properties.nutrition = {};
            for(j = 0, jmax = NUT_VALUES.length; j < jmax; j++)
            {
                properties.nutrition[j] = data[NUT_VALUES[j]][i];
            }

            //Create ingredient object
            this.db[properties.id] = new Ingredient(properties);
            
            //Store ID in matches array
            this.matches.push(properties.id);
        }

        //Sort matches by probability then name
        this.matches.sort(this.autocompleter_sort.bind(this));
        
        //Import season suggestions
        var now = new Date();
        this.season = data.season[now.getMonth()].map(function(id){return parseInt(id);});
    },

    /*******************************************************
    LOAD
    ********************************************************/

    //Load ingredients database from server
    //-> (void)
    load: function()
    {
        Kookiiz.api.call('ingredients', 'load',
        {
            'callback': this.parse.bind(this),
            'sync':     true
        });
    },
    
    /*******************************************************
    PARSE
    ********************************************************/

    //Called once server returns ingredients database
    //#response (object): server response object
    //-> (void)
    parse: function(response)
    {
        this.importDB(response.content);
    },

    /*******************************************************
    SEARCH
    ********************************************************/

    //Return ingredient with specified name
    //#name (string): name of the ingredient to search for
    //#mode (string): type of data to return, either "id" or "object" (default)
    //->ingredient (object/int): corresponding ingredient (or its ID)
    search: function(name, mode)
    {
        mode = mode || 'object';

        for(var i = 0, imax = this.db.length; i < imax; i++)
        {
            if(!this.db[i]) continue;
            if(name == this.db[i].name) 
                return mode == 'object' ? this.db[i] : i;
        }
        return null;
    }
});