/*******************************************************
Title: Welcome UI
Authors: Kookiiz Team
Purpose: User interface for welcome screen
********************************************************/

//Represents the user interface of the welcome screen
var WelcomeUI = Class.create(
{
    object_name: 'welcome_ui',

    /*******************************************************
    CONSTRUCTOR
    ********************************************************/

    //Class constructor
    //-> (void)
    initialize: function()
    {
        this.loaded = false;
        this.mode   = 'demo';

        //DOM elements
        this.$container = $('kookiiz_welcome');
        this.$curtain   = $$('div.curtain.welcome')[0];
    },

    /**********************************************************
	CLOSE
	***********************************************************/

    //Close welcome screen
    //-> (void)
    close: function()
    {
        this.Demo.stop();
        this.loaded = false;
        this.$container.hide().clean();
        this.$curtain.stopObserving('click').hide();
    },

    /**********************************************************
	DISPLAY
	***********************************************************/

    //Update welcome screen display according to current mode
    //-> (void)
    display: function()
    {
        var queue = Effect.Queues.get('welcome');
        if(!queue || !queue.size())
        {
            var mode = this.getMode(), hide, show;
            switch(mode)
            {
                case 'demo':
                    this.$login.style.visibility = 'visible';
                    hide = this.$form;
                    show = this.$demo;
                    break;
                case 'form':
                    this.$login.style.visibility = 'hidden';
                    hide = this.$demo;
                    show = this.$form;
                    break;
            }
            if(!show.visible())
            {
                hide.hide();
                show.appear(
                {
                    'duration': 1,
                    'queue':    {'position': 'end', 'scope': 'welcome'}
                });
            }
        }
    },

    /**********************************************************
	GET
	***********************************************************/

    //Return current welcome screen mode
    //->mode (string): current mode
    getMode: function()
    {
        return this.mode;
    },

    /**********************************************************
	INIT
	***********************************************************/

    //Init dynamic functionalities
    //-> (void)
    init: function()
    {
        //DOM elements
        this.$demo  = this.$container.select('.demo')[0];
        this.$form  = this.$container.select('.form')[0];
        this.$login = $('kookiiz_login');

        //Objects
        this.Demo  = new DemoUI(this.$demo);
        this.Form  = new SubscribeUI(this.$form);
        this.Login = new LoginUI();

        //Observers
        this.$demo.select('button.subscribe')[0].observe('click', this.onSubscribe.bind(this));
        this.$demo.select('button.try')[0].observe('click', this.onTry.bind(this));
        this.$container.select('.title_bar img.cancel')[0].observe('click', this.close.bind(this));
        this.Form.observe('canceled', this.onFormCanceled.bind(this));
        this.Form.observe('submitted', this.onFormSubmitted.bind(this));
        this.Login.observe('logged', this.onLoginSuccess.bind(this));
        this.Login.observe('pending', this.onLoginPending.bind(this));
        Utilities.observe_focus(this.$container, 'input.focus');

        //List video thumbnails
        Kookiiz.videos.list(this.$container.select('.videos .list')[0]);

        //Init objects
        this.Demo.start();
        this.Form.init();
        this.Login.init(this.$login);
    },

    /**********************************************************
	LOAD
	***********************************************************/

    //Load welcome screen content from server
    //-> (void)
    load: function()
    {
        Kookiiz.popup.loader();      
        Kookiiz.ajax.request('/dom/welcome.php', 'get',
        {
            'callback': this.load_callback.bind(this),
            'json':     false
        });
    },
   
    //Callback for welcome screen content loading process
    //#response (object): server response object
    //-> (void)
    load_callback: function(response)
    {
        //Insert content and set-up listeners
        this.$container.innerHTML = response;
        this.loaded = true;
        this.init();

        //Hide loader
        Kookiiz.popup.hide();

        //Open welcome screen
        this.open();
    },

    /**********************************************************
	OBSERVERS
	***********************************************************/
    
    //Called when subscription process is canceled
    //#event (object): custom event object
    //-> (void)
    onFormCanceled: function(event)
    {
        var data = $H(event.memo),
            reason = data.get('reason');
        switch(reason)
        {
            case 'login':
                this.Login.login();
                break;
            default:
                this.show('demo');
                break;
        }
    },

    //Called once form has been successfully submitted
    //-> (void)
    onFormSubmitted: function()
    {
        this.show('demo');
    },
    
    //Called when network login is pending
    //Ask user what he wants to do with his social ID
    //#event (object): custom event object
    //-> (void)
    onLoginPending: function(event)
    {
        var data = $H(event.memo),
            network = data.get('network');
        
        Kookiiz.popup.confirm(
        {
            'text':             SOCIAL_ALERTS[5],
            'confirm_label':    ACTIONS[17],
            'cancel_label':     ACTIONS[18],
            'callback':         this.onNetworkPending.bind(this, network)
        });
    },
    
    //Called when login process was successfull
    //-> (void)
    onLoginSuccess: function()
    {
        window.location.reload();
    },

    //Called when social network was successfully linked to a Kookiiz account
    //#network (string): social network name
    //-> (void)
    onNetworkConnect: function(network)
    {
        window.location.reload();
    },

    //Called when user choses what to do with its social ID
    //#network (string): social network name
    //#connect (bool):   true for "connect", false for "create a new account"
    //-> (void)
    onNetworkPending: function(network, connect)
    {
        if(connect)
            //Connect to existing account
            Kookiiz.networks.connect(network, this.onNetworkConnect.bind(this));
        else
            //Create new account
            this.Form.socialSubmit(network);
    },

    //Called when subscribe button is clicked
    //Show user form on welcome screen
    //-> (void)
    onSubscribe: function()
    {
        this.show('form');
    },

    //Called when try button is clicked
    //Close the welcome screen
    //-> (void)
    onTry: function()
    {
        this.close();
    },

    /**********************************************************
	OPEN
	***********************************************************/

    //Open welcome popup
    //-> (void)
    open: function()
    {
        this.$curtain.observe('click', this.close.bind(this)).show();
        this.$container.show();
        this.position();
        this.display();
    },

    /**********************************************************
	POSITION
	***********************************************************/

    //Position welcome popup
    //-> (void)
    position: function()
    {
        var welcome  = this.$container.getDimensions(),
            viewport = document.viewport.getDimensions(),
            scroll   = document.viewport.getScrollOffsets();
        this.$container.setStyle(
        {
            'top':  Math.round(scroll.top + (viewport.height - welcome.height) / 3) + 'px',
            'left': Math.round(scroll.left + (viewport.width - welcome.width) / 2) + 'px'
        });
    },

    /**********************************************************
	SET
	***********************************************************/

    //Set current welcome screen mode
    //#mode (string): welcome screen mode
    //-> (void)
    setMode: function(mode)
    {
        this.mode = mode;
    },

    /**********************************************************
	SHOW
	***********************************************************/

    //Show welcome screen content
    //#mode (string): welcome screen mode
    //-> (void)
    show: function(mode)
    {
        //Set display mode
        this.setMode(mode || 'demo');

        //Load or display content
        if(this.loaded)
            this.display();
        else
            this.load();
    },
    
    /**********************************************************
	SIGN UP
	***********************************************************/
   
    //Show user form
    //-> (void)
    signup: function()
    {
        this.show('form');
    }
});