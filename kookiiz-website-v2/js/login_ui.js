/*******************************************************
Title: Login UI
Authors: Kookiiz Team
Purpose: Manage login process
********************************************************/

//Represents a user interface for the login process
var LoginUI = Class.create(Observable,
{
    'object_name': 'login_ui',
    
    /*******************************************************
    CONSTRUCTOR
    ********************************************************/
    
    //Class constructor
    //-> (void)
    initialize: function()
    {
    },
    
    /*******************************************************
    INIT
    ********************************************************/
   
    //Init dynamic functionalities
    //#container (DOM/string): login form container
    //-> (void)
    init: function(container)
    {
        //DOM elements
        this.$login  = $(container);
        this.$email  = this.$login.select('input[name=email]')[0];
        this.$passw  = this.$login.select('input[name=password]')[0];
        this.$cookie = this.$login.select('input[name=remember]')[0];
        this.$frame  = this.$login.select('iframe')[0];
        
        //Observers
        this.$frame.onload = this.onLogin.bind(this);
        this.$login.select('button.social').invoke('observe', 'click', this.onNetworkLoginClick.bind(this));
        this.$login.select('span.passlost')[0].observe('click', this.onPassLost.bind(this));
    },
    
    /*******************************************************
    LOGIN
    ********************************************************/
    
    //Redirect iframe to login script (for social network login)
    //-> (void)
    login: function()
    {
        this.$frame.src = '/dom/login.php?remember=1';
    },
    
    /*******************************************************
    OBSERVERS
    ********************************************************/

    //Called if an error occurs during login process
    //Display a notification for user
    //-> (void)
    onError: function()
    {
        Kookiiz.popup.alert({'text': LOGIN_ERRORS[0]});
    },
   
    //Called once login process in hidden iframe is finished
    //-> (void)
    onLogin: function()
    {
        //Retrieve login status
        var status = parseInt(this.$frame.contentWindow.loginStatus);
        if(isNaN(status)) return;   //Abort if login status is not available

        //Successfull login
        if(status)
            this.fire('logged');
        //Error during login
        else
            this.onError();
    },
    
    //Called after social network login process
    //#network (string): social network name
    //#status (int):     network connect status
    //-> (void)
    onNetworkLogin: function(network, status)
    {
        switch(status)
        {
            //Failed to connect to social network
            case NETWORK_STATUS_FAILURE:
                Kookiiz.popup.alert({'text': SOCIAL_ERRORS[1]});
                break;

            //Auth successfull but not yet linked to a Kookiiz account
            case NETWORK_STATUS_PENDING:
                this.fire('pending', {'network': network});
                break;

            //Successfull login
            case NETWORK_STATUS_SUCCESS:
                this.login();
                break;
        }
    },
    
    //Called when user clicks on social network button to login
    //#event (object): DOM click event
    //-> (void)
    onNetworkLoginClick: function(event)
    {
        var network = event.findElement().readAttribute('data-network');
        Kookiiz.networks.auth(network, this.onNetworkLogin.bind(this));
    },
    
    //Called when user clicks on "password lost" link
    //-> (void)
    onPassLost: function()
    {
        if(this.$email.value 
            && this.$email.value != this.$email.title)
        {
            Kookiiz.popup.confirm(
            {
                'text':     PASSWORD_ALERTS[5] + ' ' + this.$email.value + ' ?',
                'callback': this.onPassReset.bind(this, this.$email.value)
            });
        }
        else
            //Ask user to provide his email address
            Kookiiz.popup.alert({'text': PASSWORD_ALERTS[0]});
    },
    
    //Called when user chooses to reset his password
    //#email (string):  user's email address
    //#confirm (bool):  true if user confirmed
    //-> (void)
    onPassReset: function(email, confirm)
    {
        if(confirm)
        {
            //Send request to reset password
            Kookiiz.api.call('session', 'pass_reset',
            {
                'callback': function()
                            {
                                Kookiiz.popup.alert({'text': PASSWORD_ALERTS[1] + ' "' + email + '"'});
                            },
                'request':  'email=' + email
            });
        }
    }
});