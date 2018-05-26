/*******************************************************
Title: Help UI
Authors: Kookiiz Team
Purpose: Manage help popup
********************************************************/

//Represents a user interface for help topics
var HelpUI = Class.create(
{
    object_name: 'help',

    /**********************************************************
	CONSTANTS
	***********************************************************/

    //List of help themes and their related topic IDs
    THEMES: {
                'menu':     {'id': 0, 'topics': [0, 1, 2, 3, 4, 5]},
                'health':   {'id': 1, 'topics': [6, 7, 8]},
                'social':   {'id': 2, 'topics': [9, 10]}
            },

    /**********************************************************
	CONSTRUCTOR
	***********************************************************/

    //Class constructor
    //-> (void)
    initialize: function()
    {
        this.theme  = 'menu';
        this.topic  = 0;

        this.$help      = $('kookiiz_help');
        this.$curtain   = $$('div.curtain.help')[0];
    },

    /**********************************************************
	CLOSE
	***********************************************************/

    //Close help popup
    //-> (void)
    close: function()
    {
        this.$help.hide().clean();
        this.$curtain.stopObserving('click').hide();
    },

    /**********************************************************
	DISPLAY
	***********************************************************/

    //Change current help illustration
    //-> (void)
    display: function()
    {
        this.$picture.className = 'topic_' + this.topic;
        this.$text.innerHTML    = HELP_TEXTS[this.topic];
    },

    /**********************************************************
	INIT
	***********************************************************/

    //Init dynamic functionalities
    //-> (void)
    init: function()
    {
        $('help_button').observe('click', this.open.bind(this));
    },

    /**********************************************************
	LIST TOPICS
	***********************************************************/

    //List topics for current theme
    //-> (void)
    listTopics: function()
    {
        this.$sidebar.clean();
        var topics = this.THEMES[this.theme].topics,
            topics_list = new Element('ul'), topic_item
        for(var i = 0, imax = topics.length; i < imax; i++)
        {
            topic_item = new Element('li', {'class': 'topic'});
            topic_item.writeAttribute('data-topic', topics[i]);
            topic_item.innerHTML = HELP_TOPICS[topics[i]];
            topics_list.appendChild(topic_item);
        }
        this.$sidebar.appendChild(topics_list);
    },

    /**********************************************************
	LOAD
	***********************************************************/

    //Load help content from server
    //-> (void)
    load: function()
    {
        Kookiiz.ajax.request('/dom/help.php', 'get',
        {
            'callback': this.load_callback.bind(this),
            'json':     false
        });
    },

    //Callback for help content loading process
    //#response (object): server response object
    //-> (void)
    load_callback: function(response)
    {
        this.$help.innerHTML = response;
        Kookiiz.popup.hide();
        this.$curtain.show();
        this.$help.show();
        this.position();
        this.setUp();
    },

    /**********************************************************
	EVENTS
	***********************************************************/

    //Called when a theme tab is clicked
    //#event (object): DOM click event
    //-> (void)
    onThemeClick: function(event)
    {
        var theme_el = event.findElement('.theme');
        if(theme_el)
        {
            var theme = theme_el.readAttribute('data-theme');
            this.setTheme(theme);
        }
    },

    //Called when a help topic is clicked
    //#event (object): DOM click event
    //-> (void)
    onTopicClick: function(event)
    {
        var topic_el = event.findElement('.topic');
        if(topic_el)
        {
            var topic_id = parseInt(topic_el.readAttribute('data-topic'));
            this.setTopic(topic_id);
        }
    },

    /**********************************************************
	OPEN
	***********************************************************/

    //Open help popup
    //-> (void)
    open: function()
    {
        Kookiiz.popup.loader();
        this.load();
    },
    
    /**********************************************************
	POSITION
	***********************************************************/

    //Position help popup
    //-> (void)
    position: function()
    {
        var popup       = this.$help.getDimensions();
        var viewport    = document.viewport.getDimensions();
        var scroll      = document.viewport.getScrollOffsets();
        var popup_top   = Math.round(scroll.top + (viewport.height - popup.height) / 3);
        var popup_left  = Math.round(scroll.left + (viewport.width - popup.width) / 2);
        this.$help.setStyle({'top': popup_top + 'px', 'left': popup_left + 'px'});
    },

    /**********************************************************
	SET
	***********************************************************/

    //Change current help theme
    //#theme (string): theme name
    //-> (void)
    setTheme: function(theme)
    {
        this.theme = theme;
        this.updateTheme();
        this.listTopics();
        this.setTopic();
    },

    //Change current help topic
    //#topic_id (int): ID of the topic (defaults to first topic of current theme)
    //-> (void)
    setTopic: function(topic_id)
    {
        if(typeof(topic_id) == 'undefined')
            topic_id = this.THEMES[this.theme].topics[0];

        this.topic = topic_id;
        this.updateTopic();
        this.display();
    },


    /**********************************************************
	SET-UP
	***********************************************************/

    //Set-up help popup once loaded
    //-> (void)
    setUp: function()
    {
        this.$picture   = this.$help.select('.content img')[0];
        this.$sidebar   = this.$help.select('.sidebar')[0];
        this.$topmenu   = this.$help.select('.topmenu')[0];
        this.$text      = this.$help.select('.content .text')[0];

        this.$curtain.observe('click', this.close.bind(this));
        this.$topmenu.observe('click', this.onThemeClick.bind(this));
        this.$sidebar.observe('click', this.onTopicClick.bind(this));
        $('help_close').observe('click', this.close.bind(this));

        this.setTheme('menu');
    },

    /**********************************************************
	UPDATE
	***********************************************************/

    //Update selected theme tab
    //-> (void)
    updateTheme: function()
    {
        //Unselect all themes
        var themes = this.$help.select('.theme').invoke('removeClassName', 'selected');

        //Select current theme
        themes.each(function(theme)
        {
            if(theme.readAttribute('data-theme') == this.theme)
            {
                theme.addClassName('selected');
                throw $break;
            }
        }, this);
    },

    //Update selected topic
    //-> (void)
    updateTopic: function()
    {
        //Unselect all steps
        var topics = this.$help.select('.topic').invoke('removeClassName', 'selected');

        //Select current step
        topics.each(function(topic)
        {
            if(parseInt(topic.readAttribute('data-topic')) == this.topic)
            {
                topic.addClassName('selected');
                throw $break;
            }
        }, this);
    }
});