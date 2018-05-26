/*******************************************************
Title: Ajax
Authors: Kookiiz Team
Purpose: Ajax related functionalities
********************************************************/

//Represents an interface to handle Ajax calls
var AjaxHandler = Class.create(
{
    object_name: 'ajax_handler',

    /*******************************************************
    CONSTRUCTOR
    ********************************************************/

    //Class constructor
    //-> (void)
    initialize: function()
    {
    },

    /*******************************************************
    CALLBACK
    ********************************************************/

    //Generic AJAX callback to process server response before calling a specific callback function
    //#options (object): structure containing the following options
    //  #callback (function):   function to call with the server response
    //  #json (bool):           specifies if the server response is in JSON format (default to true)
    //  #sync (bool):           specifies if the request should be synchronous (default to false)
    //#xmlHttp (object): XMLHttp object
    //-> (void)
    callback: function(options, xmlHttp)
    {
        var response = xmlHttp.responseText;

        //Server response is supposed to be in JSON format
        if(options.json)
        {
            //Check if response is valid JSON
            var error_data;
            if(response.isJSON())
            {
                //Eval JSON content
                response = response.evalJSON(true);
                options.callback(response);
            }
            //Response is not valid JSON
            else
            {
                //Throw raw error data
                error_data =
                {
                    'mode': 'text',
                    'text': response,
                    'type': 'ajax'
                };
                Kookiiz.error.handler(error_data);
            }
        }
        //Call specific callback with server response text
        else if(options.callback) 
            options.callback(response);
    },

    /*******************************************************
    REQUEST
    ********************************************************/

    //Init an AJAX request
    //#url (string):        address of the php file to reach (from javascript folder)
    //#mode (string):       request mode (either "post" or "get")
    //#options (object):    structure containing any of the following options
    //  #request (string):      string of "parameter=value" pairs
    //  #callback (function):   function to call with the server response
    //  #json (bool):           specifies if the server response is in json format (default to true)
    //  #sync (bool):           specifies if the request should be synchronous (default to false)
    //-> (void)
    request: function(url, mode, options)
    {
        var self = this;

        var defaults =
        {
            'callback': false,
            'json':     true,
            'request':  '',
            'sync':     false
        };
        options = Object.extend(defaults, options || {});

        new Ajax.Request(url,
        {
            'method':       mode,
            'parameters':   options.request,
            'onSuccess':    self.callback.bind(self, options),
            'asynchronous': !options.sync
        });
    }
});