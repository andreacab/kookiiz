/*******************************************************
Title: News UI
Authors: Kookiiz Team
Purpose: Display and rotate news
********************************************************/

var NewsUI = Class.create(
{
    object_name: 'news_ui',
    
    /**********************************************************
	CONSTANTS
	***********************************************************/
    
    FEED_SIZE:  10,
    KOOKIIZ_ID: '168559256517709',
    INIT_DELAY: 5,                  //Delay before news download
    TEXT_MAX:   200,
    TIMEOUT:    15,
    
    /**********************************************************
	CONSTRUCTOR
	***********************************************************/
    
    //Class constructor
    //-> (void)
    initialize: function()
    {
        this.feed  = [];
        this.index = 0;
        this.timer = 0;
        
        this.$news = $('kookiiz_news');
        this.$feed = this.$news.select('.feed')[0].loading();
        this.$date = this.$news.select('.date')[0];
        this.$numb = this.$news.select('.numb')[0];
        this.$next = this.$news.select('img.transfer')[0];
    },
    
    /**********************************************************
	DISPLAY
	***********************************************************/
    
    //Display current news piece
    //-> (void)
    display: function()
    {
        //Check if feed is empty
        if(!this.feed.length)
        {
            this.$feed.clean().appendChild(document.createTextNode(NEWS_TEXT[1]));
            this.$date.clean();
            this.$numb.clean();
            return;
        }
        
        //Cancel previous effect (if any)
        Effect.Queues.get('news').invoke('cancel');
        this.$feed.hide().clean();
        this.$date.clean();
        this.$numb.clean();
        
        //Display next news item
        var text = this.feed[this.index].message.truncate(this.TEXT_MAX),
            date = this.feed[this.index].created_time,
            numb = (this.index + 1) + '/' + this.feed.length;
        this.$feed.appendChild(document.createTextNode(text));
        if(this.feed[this.index].link)
        {
            var link_text = this.feed[this.index].link;
            if(link_text.include('kookiiz.com/') && !link_text.include('/#/'))
                link_text = link_text.sub('.com/', '.com/#/');
            var link = new Element('a', {'href': link_text, 'class': 'text_color2'});
            link.appendChild(document.createTextNode(link_text));
            this.$feed.appendChild(new Element('br'));
            this.$feed.appendChild(link);
        }
        this.$date.appendChild(document.createTextNode(date));
        this.$numb.appendChild(document.createTextNode(numb));
        this.$feed.appear(
        {
            'duration': 2,
            'queue':    {'scope': 'news'}
        });
    },
    
    /**********************************************************
	INIT
	***********************************************************/
    
    //Init dynamic functionalities
    //-> (void)
    init: function()
    {
        this.load.bind(this).delay(this.INIT_DELAY);
        this.$next.observe('click', this.onNext.bind(this));
    },
    
    /**********************************************************
	LOAD
	***********************************************************/
   
    //Load news from server
    //-> (void)
    load: function()
    {
        Kookiiz.api.call('news', 'load',
        {
            'callback': this.onNewsReady.bind(this),
            'request':  'limit=' + this.FEED_SIZE
        });
    },
    
    /**********************************************************
	OBSERVERS
	***********************************************************/
    
    //Called when news feed has been downloaded from FB
    //Set-up everything and start news feed rotation
    //#response (object): news data
    //-> (void)
    onNewsReady: function(response)
    {
        if(response.content.length)
            this.feed = response.content;
        
        //Clean feed content
        for(var i = 0, imax = this.feed.length; i < imax; i++)
        {
            //Remove posts without message or not from Kookiiz
            if(!this.feed[i].message 
                || this.feed[i].from.id != this.KOOKIIZ_ID)
            {
                this.feed.splice(i, 1);
                i--; imax--;
            }
            else
            {
                //Remove link from message
                if(this.feed[i].link)
                    this.feed[i].message = this.feed[i].message.sub(this.feed[i].link, '');
            }
        }

        this.display();
        if(this.feed.length > 1)
            this.setTimer();
    },
    
    //Called when user presses "next" button
    //Rotates the news
    //-> (void)
    onNext: function()
    {
        this.rotate();
        this.setTimer();
    },
    
    /**********************************************************
	ROTATE
	***********************************************************/
    
    //Rotate news index
    //-> (void)
    rotate: function()
    {
        this.index++;
        if(this.index > this.feed.length - 1)
            this.index = 0;
        this.display();
    },
    
    /**********************************************************
	SETTERS
	***********************************************************/
    
    //Start news rotation
    //-> (void)
    setTimer: function()
    {
        window.clearInterval(this.timer);
        this.timer = window.setInterval(this.rotate.bind(this), this.TIMEOUT * 1000);
    }
});