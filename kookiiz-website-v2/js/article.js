/*******************************************************
Title: Article
Authors: Kookiiz Team
Purpose: Define the article object
********************************************************/

//Represents an article
var Article = Class.create(
{
	object_name: 'article',

	/*******************************************************
    CONSTRUCTOR
    ********************************************************/

    //Article constructor
    //#id (int):            unique article ID
    //#title (string):      article title
    //#text (string):       article text
    //#partner (int):       ID of the partner which provided the article
    //#date (string):       article creation date ("YYYY-MM-DD")
    //#lang (string):       language identifier
    //#keywords (string):   list of article keywords separated by ","
    //#pics (array):        list of picture IDs
    //#captions (array):    list of picture captions
    //-> (void)
	initialize: function(id, title, text, partner, date, lang, keywords, pics, captions)
    {
        //Attributes
        this.id         = id;
        this.title      = title;
        this.text       = text;
        this.partner    = partner;
        this.date       = date;
        this.lang       = lang;

        //Arrays
        if(keywords)    this.keywords = keywords.split(',');
        else            this.keywords = [];
        this.pics       = pics;
        this.captions   = captions;
    },

    /*******************************************************
    DISPLAY
    ********************************************************/

    //Display article content
    //-> (void)
	display: function()
    {
        //Display article content on page
        $('article_title').innerHTML    = this.title;
        $('article_text').innerHTML     = this.text;
        $('article_keywords').innerHTML = this.keywords.join(', ');
        this.pictures_display();

        //Update partner panel
        Kookiiz.partners.display(this.partner);

        //Update display article value
        Kookiiz.articles.displayed_set(this.id);

        //Show comments for current article
        Kookiiz.comments.update();
    },

    //Add a picture with its caption to provided container
    //-> (void)
	pictures_display: function()
    {
        var container = $('article_pictures').clean();

        //Loop through pictures
        var pictures_list = new Element('ul', {'class': 'pictures_list'});
        var picture_item, picture_container, picture, caption_container, caption;
        for(var i = 0, imax = this.pics.length; i < imax; i++)
        {
            picture_item = new Element('li', {id: 'article_picture_' + this.pics[i]});

            //Picture
            picture_container = new Element('p', {'class': 'center'});
            picture = new Element('img',
            {
                'alt':      ARTICLE_DISPLAY_TEXT[1],
                'class':    'article_picture',
                'src':      '/pics/articles-' + this.pics[i]
            });
            picture_container.appendChild(picture);
            picture_item.appendChild(picture_container);

            //Picture caption
            caption_container   = new Element('p', {'class': 'center'});
            caption             = new Element('span', {'class': 'italic'});
            caption.innerHTML   = this.captions[i];
            caption_container.appendChild(caption);
            picture_item.appendChild(caption_container);

            //Append new picture to list
            pictures_list.appendChild(picture_item);
        }

        if(pictures_list.empty())   container.innerHTML = ARTICLE_ALERTS[1];
        else                        container.appendChild(pictures_list);
    }
});