/*******************************************************
Title: API
Authors: Kookiiz Team
Purpose: Provide methods to make and handle calls to Kookiiz API
********************************************************/

//Represents a handler for Kookiiz API
var API = Class.create(
{
    object_name: 'api',

    /*******************************************************
    CONSTRUCTOR
    ********************************************************/

    //Class constructor
    //-> (void)
    initialize: function()
    {
        this.key = Cookie.get('session_key') || '';
    },

    /*******************************************************
    CALL
    ********************************************************/

    //Make an API call
    //#module (string): API module
    //#action (string): API action
    //#options (object):
    //  #callback (function):   function to call with response
    //  #request (string):      Ajax request parameters
    //  #sync (bool):           whether to use a synchronous request (defaults to false)
    //-> (void)
    call: function(module, action, options)
    {
        var defaults =
        {
            'callback': false,
            'request':  '',
            'sync':     false
        };
        options = Object.extend(defaults, options || {});

        //Send AJAX request
        var self    = this;
        var url     = '../api/' + module + '/';
        var request = 'action=' + action
                    + '&key=' + this.key;
        if(options.request)
            request += '&' + options.request;
        new Ajax.Request(url,
        {
            'method':         'post',
            'parameters':     request,
            'onSuccess':      self.handle.bind(self, options),
            'asynchronous':   !options.sync
        });
    },

    /*******************************************************
    HANDLE
    ********************************************************/

    //Handler API response
    //#options (object):
    //  #callback (function):   function to call with the server response
    //  #sync (bool):           specifies if the request should be synchronous (default to false)
    //#xmlHttp (object): XMLHttp object
    handle: function(options, xmlHttp)
    {
        var response = xmlHttp.responseText;

        //Check if response is valid JSON
        var error_data;
        if(response.isJSON())
        {
            //Eval JSON content
            response = response.evalJSON(true);

            //Update session key
            var key = response.key;
            if(key) this.key = key; 

            //Display error if specified
            var error = response.parameters.error;
            if(error.code)
            {
                error_data =
                {
                    'mode': 'code',
                    'code': error.code,
                    'type': error.type
                };
                Kookiiz.error.handler(error_data);
            }
            //Call callback function
            else if(options.callback)
                options.callback(response);
        }
        //Response is not valid JSON
        else
        {
            //Throw raw error data
            error_data =
            {
                'mode':   'text',
                'text':   response,
                'type':   'ajax'
            };
            Kookiiz.error.handler(error_data);
        }
    }
});