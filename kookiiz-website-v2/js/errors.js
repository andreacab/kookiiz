/*******************************************************
Title: Errors
Authors: Kookiiz Team
Purpose: Errors handling functionalities
********************************************************/

//Represents an error handler
var ErrorsHandler = Class.create(
{
    object_name: 'error_handler',

    /*******************************************************
    CONSTRUCTOR
    ********************************************************/

    //Class constructor
    //-> (void)
    initialize: function()
    {
        this.errors = [];
    },

    /*******************************************************
    CATCH
    ********************************************************/

    //Error handler for try...catch structures
    //#error (error): javascript error
    //-> (void)
    catcher: function(error)
    {
        var text = error.fileName + '/' + error.lineNumber + ':' + error;
        this.errors.push(text);
        window.alert(text);
    },
    
    /*******************************************************
    HANDLE
    ********************************************************/

    //Handle a custom error
    //#error_memo (object): memo data from error object
    //-> (void)
    handler: function(error_memo)
    {
        var error = $H(error_memo),
            mode  = error.get('mode');

        var text = '', callback = false;
        if(mode == 'text')
        {
            switch(error.get('type'))
            {
                //Error from ajax module
                case 'ajax':
                    text = error.get('text');
                    if(text != '') 
                        text += '<br/>' + ERRORS[1];
                    else
                    {
                        return;
                        //No response from server
                        //text = SERVER_ERRORS[0];
                        //callback = this.serverError.bind(this);
                    }
                    break;
            }
        }
        //Numeric error code
        else if(mode == 'code')
        {
            var texts = [],
                code = error.get('code');
            switch(error.get('type'))
            {
                case 'admin_glossary':
                    texts = ADMIN_GLOSSARY_ERRORS;
                    break;
                case 'admin_ingredients':
                    texts = ADMIN_INGREDIENTS_ERRORS;
                    break;
                case 'admin_partners':
                    texts = ADMIN_PARTNERS_ERRORS;
                    break;
                case 'admin_recipes':
                    texts = ADMIN_RECIPES_ERRORS;
                    break;
                case 'admin_users':
                    texts = ADMIN_USERS_ERRORS;
                    break;
                case 'articles':
                    texts = ARTICLES_ERRORS;
                    break;
                case 'comments':
                    texts = COMMENTS_ERRORS;
                    break;
                case 'events':
                    texts = EVENTS_ERRORS;
                    break;
                case 'feedback':
                    texts = FEEDBACK_ERRORS;
                    break;
                case 'friends':
                    texts = FRIENDS_ERRORS;
                    break;
                case 'glossary':
                    texts = GLOSSARY_ERRORS;
                    break;
                case 'ingredients':
                    texts = INGREDIENTS_ERRORS;
                    break;
                case 'invitations':
                    texts = INVITATIONS_ERRORS;
                    break;
                case 'partners':
                    texts = PARTNERS_ERRORS;
                    break;
                case 'password':
                    texts = PASSWORD_ERRORS;
                    break;
                case 'picture':
                    texts = PICTURE_ERRORS;
                    break;
                case 'quickmeals':
                    texts = QUICKMEALS_ERRORS;
                    break;
                case 'recipeform':
                    texts = RECIPEFORM_ERRORS;
                    break;
                case 'recipes':
                    texts = RECIPES_ERRORS;
                    break;
                case 'server':
                    texts = SERVER_ERRORS;
                    break;
                case 'session':
                    texts = SESSION_ERRORS;
                    if(code == ERROR_SESSION_EXPIRED)
                        callback = this.sessionTimeout.bind(this);
                    break;
                case 'sports':
                    texts = SPORTS_ERRORS;
                    break;
                case 'user':
                    texts = USER_ERRORS;
                    break;
            }
            text = texts[code - 1] || ERRORS[0];
        }
        this.errors.push(text);

        //Display error popup
        Kookiiz.popup.alert({'text': text, 'callback': callback});
    },

    /*******************************************************
    SERVER
    ********************************************************/

    //Special error case due to lost connection
    //-> (void)
    serverError: function()
    {
        //Trigger session update to try and reconnect
        Kookiiz.session.update();
    },

    /*******************************************************
    SESSION
    ********************************************************/

    //Special error case due to session timeout
    //Reload page
    //-> (void)
    sessionTimeout: function()
    {
        window.location.reload();
    }
});