/*******************************************************
Title: Articles UI
Authors: Kookiiz Team
Purpose: Functionalities of the article user interface
********************************************************/

//Represents a user interface for articles display and edition
var ArticlesUI = Class.create(
{
    object_name: 'articles_ui',

    /*******************************************************
    CONSTRUCTOR
    ********************************************************/

    //Class constructor
    //-> (void)
    initialize: function()
    {
        this.displayed_article = 0;     //ID of the article beeing displayed

        //DOM elements
        this.$historyDisplay = $('articles_history');
        this.$searchDisplay  = $('articles_search_results');
        this.$searchInput    = $('input_articles_search');
    },

    /*******************************************************
    CURRENT DISPLAY
    ********************************************************/

    //Get currently displayed article
    //->article_id (int): current article ID
    displayed_get: function()
    {
        return this.displayed_article;
    },

    //Set currently displayed article
    //#article_id (int): current article ID
    //-> (void)
    displayed_set: function(article_id)
    {
        this.displayed_article = article_id;
    },

    /*******************************************************
    DISPLAY
    ********************************************************/

    //Load article content from local array or database
    //#article_id (int): ID of the article to load
    //-> (void)
    display: function(article_id)
    {
        //Search for article in local array
        var article = Articles.get(article_id, this.display.bind(this));
        if(article)
        {
            article.display();
            Kookiiz.tabs.loaded();
        }
        else 
            Kookiiz.tabs.loading();
    },

    //Display a list of articles in provided container
    //#container (DOM/string):  container DOM element (or its ID)
    //#articles (array):        list of article objects to display
    //#detailed (bool):         specifies if an icon should be added to each article
    //-> (void)
    display_list: function(container, articles, detailed)
    {
        container = $(container).clean();
        if(typeof(detailed) == 'undefined') detailed = false;

        var articles_list = new Element('ul'),
            article, article_id, article_type, article_title,
            article_date, article_item, article_text;
        for(var i = 0, imax = articles.length; i < imax; i++)
        {
            article         = articles[i];
            article_id      = parseInt(article.id);
            article_type    = parseInt(article.type);
            article_title   = article.title;
            article_date    = article.date;

            article_item = new Element('li',
            {
                'class':    'article_history',
                'id':       'article_' + article_type + '_' + article_id
            });
            article_item.observe('click', this.element_click.bind(this));
            articles_list.appendChild(article_item);

            if(detailed)
            {
                var article_icon = new Element('img',
                {
                    'alt':      ARTICLE_CATEGORIES[article_type],
                    'class':    'icon15 ' + (article_type == 0 ? 'keys' : 'healthy'),
                    'src':      ICON_URL,
                    'title':    ARTICLE_CATEGORIES[article_type]
                });
                article_item.appendChild(article_icon);
            }

            article_text = new Element('span', {'class': 'click'});
            article_text.innerHTML = article_title + (detailed ? ' (' + article_date + ')' : '');
            article_item.appendChild(article_text);
        }

        if(articles_list.empty())   
            container.innerHTML = ARTICLE_ALERTS[0];
        else                        
            container.appendChild(articles_list);
    },

    /*******************************************************
    HISTORY
    ********************************************************/

    //Load articles history for both types (tips and health)
    //-> (void)
    history_load: function()
    {
        this.$historyDisplay.loading();
        Kookiiz.api.call('articles', 'history', 
        {
            'callback': this.history_parse.bind(this)
        });
    },

    //Called when article history is fetched from server
    //#response (object): server response object
    //-> (void)
    history_parse: function(response)
    {
        this.display_list(this.$historyDisplay, response.content, true);
    },
    
    /*******************************************************
    INIT
    ********************************************************/

    //Init dynamic functionalities
    //-> (void)
    init: function()
    {
        //Panel
        if(!Kookiiz.panels.is_disabled('articles'))
        {
            $('icon_articles_search').observe('click', this.search_click.bind(this));
        }
    },

    /*******************************************************
    SEARCH
    ********************************************************/

    //Search for articles
    //#type (int):          article type
    //#keyword (string):    text to search for
    //-> (void)
    search: function(type, keyword)
    {
        //Ask server for article content
        Kookiiz.api.call('articles', 'search', 
        {
            'callback': this.search_parse.bind(this),
            'request':  'type=' + type
                        + '&keyword=' + keyword
        });
    },

    //Parse articles search results
    //#response (object): server response object
    //-> (void)
    search_parse: function(response)
    {
        this.display_list(this.$searchDisplay, response.content);
    },

    //Throw a new article search
    //-> (void)
    search_throw: function()
    {
        var keyword = encodeURIComponent(this.$searchInput.value.stripTags());
        if(keyword && keyword != this.$searchInput.title)
        {
            var context = Kookiiz.tabs.context_get();
            if(context == 'tips')           
                this.search(ARTICLE_TYPE_TIPS, keyword);
            else if(context == 'health')    
                this.search(ARTICLE_TYPE_HEALTH, keyword);

            this.$searchDisplay.loading();
        }
    },

    /*******************************************************
    CALLBACKS
    ********************************************************/

    //Callback for article item click
    //#event (event): DOM click event
    //-> (void)
    element_click: function(event)
    {
        var article_el = event.findElement(),
            article_id = parseInt(article_el.id.split('_')[2]);
        Kookiiz.tabs.show('article_display', article_id);
    },

    //Callback for search icon click
    //-> (void)
    search_click: function()
    {
        this.search_throw();
    }
});