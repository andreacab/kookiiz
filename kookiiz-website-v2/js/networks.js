/*******************************************************
Title: Networks
Authors: Kookiiz Team
Purpose: Connect with social networks
********************************************************/

//Represents a user interface for social networks
var NetworksUI = Class.create(
{
    object_name: 'networks_ui',

    /*******************************************************
    CONSTRUCTOR
    ********************************************************/

    //Class constructor
    //-> (void)
    initialize: function()
    {
        this.auth_params =
        {
            'network':  '',
            'callback': null,
            'popup':    null
        }
    },

    /*******************************************************
    AUTH
    ********************************************************/

    //Open OAuth popup for provided social network
    //#network (string):    social network name
    //#callback (function): callback function for auth process
    //-> (void)
    auth: function(network, callback)
    {
        //Store request info
        this.auth_params.callback = callback;
        this.auth_params.network  = network;
        
        //Open authentication popup
        var url = '', title = '';
        switch(network)
        {
            case 'facebook':
                url     = '/dom/oauth_fb.php';
                title   = 'Facebook';
                break;
            case 'twitter':
                url     = '/dom/oauth_tw.php';
                title   = 'Twitter';
                break;
            default:
                return;
                break;
        }
        this.auth_params.popup = window.open(url, title, 'menubar=no,width=790,height=360,toolbar=no');
    },

    /*******************************************************
    CONNECT
    ********************************************************/

    //Open social connect popup
    //#network (string):    social network name ("facebook", "twitter", etc.)
    //#callback (function): callback for connect process
    //-> (void)
    connect: function(network, callback)
    {
        //Retrieve network name
        var network_name = '';
        switch(network)
        {
            case 'facebook':
                network_name = 'Facebook';
                break;
            case 'twitter':
                network_name = 'Twitter';
                break;
        }
        
        //Open a popup to link to an existing account or create a new one
        Kookiiz.popup.custom(
        {
            'title':                SOCIAL_ALERTS[0] + ' ' + network_name,
            'text':                 SOCIAL_ALERTS[6],
            'content_url':          '/dom/network_connect_popup.php',
            'content_parameters':   'network=' + network,
            'content_init':         this.onConnectReady.bind(this, network, callback)
        });
    },

    /*******************************************************
    GET
    ********************************************************/

    //Get user data from a given social network
    //#network (string):    social network name
    //#callback (function): function to call with the data
    //-> (void)
    getUserInfo: function(network, callback)
    {
        //Send request to get social network info
        Kookiiz.api.call('users', 'network_info',
        {
            'callback': this.onUserInfo.bind(this, network, callback),
            'request':  'network=' + network
        });
    },

    /*******************************************************
    OBSERVERS
    ********************************************************/

    //Called by PHP script once auth process is over
    //#status (int): network authorization status
    //-> (void)
    onAuth: function(status)
    {
        //Close network popup
        this.auth_params.popup.close();

        //Trigger callback function (if any)
        if(this.auth_params.callback)
            this.auth_params.callback(this.auth_params.network, status);

        //Clear everything
        this.auth_params.network  = '';
        this.auth_params.callback = null;
        this.auth_params.popup    = null;
    },

    //Called once the connect process is over
    //#network (string):    social network name
    //#callback (function): callback function
    //-> (void)
    onConnect: function(network, callback)
    {
        //Retrieve error code
        var error = $('network_connect').select('iframe')[0].contentWindow.ERROR;
        if(typeof(error) == 'undefined') return;

        if(error)
        {
            //Display error message
            $('network_connect_login').show();
            $('network_connect_loader').clean();
            var error_text = $('network_connect_error');
            error_text.innerHTML = SOCIAL_ERRORS[error - 1];
            error_text.show();
        }
        else
            if(callback) callback(network);
    },

    //Called once connect popup content is loaded
    //#network (string):    social network name
    //#callback (function): callback function
    //-> (void)
    onConnectReady: function(network, callback)
    {
        $('network_connect').select('form')[0].onsubmit = this.onConnectSubmit.bind(this);
        $('network_connect').select('iframe')[0].onload = this.onConnect.bind(this, network, callback);
    },

    //Called before the network connect popup form is submitted
    //->confirm (bool): true if submit process can carry on
    onConnectSubmit: function()
    {
        //Hide login info and display loader
        $('network_connect_login').hide();
        $('network_connect_loader').loading(true);
        return true;
    },

    //Called when social network info on user is made available
    //#network (string):    social network name
    //#callback (function): callback function
    //#response (object):   server response object
    //-> (void)
    onUserInfo: function(network, callback, response)
    {
        //Trigger callback function
        if(callback)
            callback(response.content);
    }
});