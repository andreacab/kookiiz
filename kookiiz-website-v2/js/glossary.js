/*******************************************************
Title: Glossary
Authors: Kookiiz Team
Purpose: Class definitions for glossary functionalities
********************************************************/

//Represents a user interface for glossary searches
var GlossaryUI = Class.create(
{
    object_name: 'glossary_ui',

    /*******************************************************
    CONSTRUCTOR
    ********************************************************/

    //Class constructor
    //-> (void)
	initialize: function()
    {
        this.$input   = $('input_glossary_search');
        this.$results = $('glossary_results');
    },

    /*******************************************************
    INIT
    ********************************************************/

    //Init dynamic functionalities
    //-> (void)
    init: function()
    {
        //Panel
        if(!Kookiiz.panels.is_disabled('glossary'))
        {
            Utilities.observe_return(this.$input, this.search_throw.bind(this));
            $('icon_glossary_search').observe('click', this.search_throw.bind(this));
            this.$results.observe('click', this.keyword_click.bind(this));
        }
    },

    /*******************************************************
    CLEAR
    ********************************************************/

    //Clear glossary panel
    //-> (void)
    clear: function()
    {
        this.$results.clean();
        this.$input.value = this.$input.title;
    },

    /*******************************************************
    DISPLAY
    ********************************************************/
    
    //Display glossary terms in provided container
    //#data (array):    list of glossary terms (id/keyword/definition)
    //#mode (string):   either "search" or "recipe"
    //-> (void)
    display: function(data, mode)
    {
        this.$results.clean();

        var glossary_list = new Element('dl', {'id': 'glossary_list'}),
            keyword_id, keyword, definition, lang, glossary_term, glossary_def;
        for(var i = 0, imax = data.length; i < imax; i++)
        {
            keyword_id  = parseInt(data[i].id);
            keyword     = data[i].name;
            definition  = data[i].def;
            lang        = data[i].lang;

            //Build glossary term
            glossary_term = new Element('dt',
            {
                'class':    'click',
                'id':       'glossary_' + lang + '_' + keyword_id
            });
            glossary_def = new Element('dd');
            glossary_term.innerHTML = keyword;
            glossary_def.innerHTML  = definition;
            glossary_def.hide();
            glossary_list.appendChild(glossary_term);
            glossary_list.appendChild(glossary_def);
        }

        //Display "no result" caption
        if(glossary_list.empty())
        {
            if(mode == 'search')
                this.$results.innerHTML = GLOSSARY_ALERTS[0];
        }
        else 
            this.$results.appendChild(glossary_list);
    },

    /*******************************************************
    PARSE
    ********************************************************/

    //Parse glossary content received from server
    //#response (object): server response
    //-> (void)
    parse: function(response)
    {
        var action = response.parameters.action;
        this.display(response.content, action == 'search' ? 'search': 'recipe');
    },

    /*******************************************************
    SEARCH
    ********************************************************/

    //Search glossary for a given keyword
    //#keyword (string): the keyword to search for
    //-> (void)
    search: function(keyword)
    {
        if(keyword && keyword != '')
        {
            Kookiiz.api.call('glossary', 'search', 
            {
                'callback': this.parse.bind(this),
                'request':  'keyword=' + keyword
                            + '&lang_all=' + (Kookiiz.tabs.current_get() == 'admin' ? 1 : 0)
            });
        }
    },

    //Look for glossary keywords related to a given recipe
    //#recipe_id (int): ID of the recipe
    //-> (void)
    search_recipe: function(recipe_id)
    {
        this.$results.loading();
        Kookiiz.api.call('glossary', 'search_recipe', 
        {
            'callback': this.parse.bind(this),
            'request':  'recipe_id=' + parseInt(recipe_id)
        });
    },

    /*******************************************************
    CALLBACKS
    ********************************************************/

    //Display definition of clicked glossary item
    //#event (event): DOM click event
    //-> (void)
    keyword_click: function(event)
    {
        //Retrieve glossary term element
        var glossary_term = event.findElement('dt');
        if(glossary_term)
        {
            this.$results.select('dd').invoke('hide');
            glossary_term.nextSibling.show();
        }
    },

    //Called when search button is clicked or enter key is pressed
    //-> (void)
    search_throw: function()
    {
        var keyword = encodeURIComponent(this.$input.value.stripTags());

        //Check if typed keyword is not empty
        if(keyword && keyword != this.$input.title)
        {
            this.$input.value = '';
            this.$results.loading();
            this.search(keyword, this.parse.bind(this));
        }
    }
});