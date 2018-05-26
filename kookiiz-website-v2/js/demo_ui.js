/*******************************************************
Title: Demo
Authors: Kookiiz Team
Purpose: Define Kookiiz demo object
********************************************************/

//Represents a user interface to demonstrate Kookiiz functionalities
var DemoUI = Class.create(Observable,
{
    object_name: 'demo_ui',

    /*******************************************************
    CONSTANTS
    ********************************************************/

    DELAY:      15,     //Delay after a tab click before rotation resumes (in seconds)
    TABS:       4,      //Number of tabs
    TIMEOUT:    5,      //Rotation timeout (in seconds)

    /*******************************************************
    CONSTRUCTOR
    ********************************************************/

    //Class constructor
    //#$container (DOM): demo container
    //-> (void)
    initialize: function($container)
    {
        this.tab        = 0;    //current demo tab
        this.timer      = 0;    //demo timer
        this.pause      = 0;    //pause timer
        this.$container = $container;
    },

    /*******************************************************
    DELAY
    ********************************************************/

    //Pause demo rotation for a while
    //-> (void)
    delay: function()
    {
        window.clearTimeout(this.pause);
        window.clearInterval(this.timer);
        var delay = Math.min(this.TIMEOUT, this.DELAY - this.TIMEOUT);
        this.pause = window.setTimeout(this.resume.bind(this), delay * 1000);
    },

    /*******************************************************
    DISPLAY
    ********************************************************/

    //Update demo frame
    //-> (void)
    display: function()
    {
        //Update tab
        this.$menu.select('.tab').each(function(tab)
        {
            tab.removeClassName('selected');
            if(parseInt(tab.readAttribute('data-tab')) == this.tab)
                tab.addClassName('selected');
        }, this);

        //Update demo frame
        this.$frame.hide().className = 'frame tab' + this.tab;
        new Effect.Appear(this.$frame, {'duration': 1});

        //Update text
        this.$captions[0].innerHTML = DEMO_TEXT[this.tab * 2];
        this.$captions[1].innerHTML = DEMO_TEXT[this.tab * 2 + 1];
    },

    /*******************************************************
    GETTERS
    ********************************************************/

    //Return current demo tab
    //->tab (int): current demo tab ID
    getTab: function()
    {
        return this.tab;
    },
    
    /*******************************************************
    OBSERVERS
    ********************************************************/

    //Called when a demo tab is clicked
    //#event (object): DOM click event
    //-> (void)
    onTabClick: function(event)
    {
        var tab = event.findElement('.tab');
        if(tab)
        {
            //Stop rotation
            this.delay();

            //Change demo tab
            var tab_id = parseInt(tab.readAttribute('data-tab'));
            this.setTab(tab_id);
        }
    },

    /*******************************************************
    RESUME
    ********************************************************/

    //Resume demo rotation
    //-> (void)
    resume: function()
    {
        this.pause = 0;
        this.setTimer();
    },

    /*******************************************************
    ROTATE
    ********************************************************/

    //Rotate demo (called automatically at constant time intervals)
    //-> (void)
    rotate: function()
    {
        //Set demo to next tab
        var next_tab = this.tab >= this.TABS - 1 ? 0 : (this.tab + 1);
        if(next_tab != this.tab) this.setTab(next_tab);
    },

    /*******************************************************
    SETTERS
    ********************************************************/

    //Set current demo tab
    //#tab (int): current tab ID
    //-> (void)
    setTab: function(tab)
    {
        this.tab = tab;
        this.display();
    },

    //Set automatic rotation timer
    //-> (void)
    setTimer: function()
    {
        window.clearInterval(this.timer);
        this.timer = window.setInterval(this.rotate.bind(this), this.TIMEOUT * 1000);
    },

    /*******************************************************
    START
    ********************************************************/

    //Start demo rotation
    //-> (void)
    start: function()
    {
        //Stop demo (just in case)
        this.stop();

        //DOM elements
        this.$menu     = this.$container.select('.menu')[0];
        this.$frame    = this.$container.select('img.frame')[0];;
        this.$captions = this.$container.select('.caption');
        this.$title    = $();

        //Set observers
        this.$menu.observe('click', this.onTabClick.bind(this));

        //Display first frame and set automatic demo rotation
        this.display();
        this.setTimer();
    },

    /*******************************************************
    STOP
    ********************************************************/

    //Stop demo rotation
    //-> (void)
    stop: function()
    {
        window.clearInterval(this.timer);
        if(this.$menu) this.$menu.stopObserving('click');
        
        this.tab    = 0;
        this.timer  = 0;
    }
});