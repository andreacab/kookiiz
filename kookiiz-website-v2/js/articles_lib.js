/*******************************************************
Title: Articles library
Authors: Kookiiz Team
Purpose: Store and manage articles
********************************************************/

//Represents a library of articles
var ArticlesLib = Class.create(Library,
{
    object_name: 'articles_library',

    /*******************************************************
    CONSTRUCTOR
    ********************************************************/

    //Class constructor
    //#$super (function): super class constructor
    //-> (void)
    initialize: function($super)
    {
        $super();
    },

    /*******************************************************
    GET
    ********************************************************/

    //Return article with provided ID
    //#article_id (int):    unique article ID
    //#callback (function): function to call after article content has been retrieved from server
    //->article (object): article object (false if it must be downloaded from server)
    get: function(article_id, callback)
    {
        var article = this.find(article_id);
        if(article) return article;
        else
        {
            this.load(article_id, callback);
            return false;
        }
    },

    /*******************************************************
    LOAD
    ********************************************************/

    //Load article content from server
    //#article_id (int):    unique article ID
    //#callback (function): function to call after article content has been retrieved from server
    //-> (void)
    load: function(article_id, callback)
    {
        //Ask server for article content
        var params = {};
        params.callback = this.parse.bind(this, callback);
        params.request  = 'article_id=' + article_id;
        Kookiiz.api.call('articles', 'load', params);
    },

    /*******************************************************
    PARSE
    ********************************************************/

    //Receive and store article data from server
    //#response (object):   server response object
    //#callback (function): function to call after article content has been retrieved from server
    //-> (void)
    parse: function(response, callback)
    {
        var article_content = response.content;
        var id              = parseInt(article_content.id);
        var title           = article_content.title.stripTags();
        var text            = replace_carriage_return(article_content.text.stripTags());
        var partner         = parseInt(article_content.partner);
        var date            = article_content.date;
        var lang            = article_content.lang;
        var keywords        = article_content.keywords.stripTags();
        var pics            = article_content.pics.map(function(pic){return parseInt(pic);});
        var captions        = article_content.captions.map(function(cap){return cap.stripTags();});

        //Store article locally
        var article = new Article(id, title, text, partner, date, lang, keywords, pics, captions);
        this.library.push(article);
        callback(article.id);
    }
});