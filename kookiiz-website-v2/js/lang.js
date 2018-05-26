/*******************************************************
Title: Lang
Authors: Kookiiz Team
Purpose: User interface for language selection
********************************************************/

//Represents a user interface for language selection
var LanguageUI = Class.create(
{
    object_name: 'language_ui',

    /*******************************************************
    CONSTRUCTOR
    ********************************************************/

    //Class constructor
    //-> (void)
    initialize: function()
    {   
    },
    
    /*******************************************************
    CHANGE
    ********************************************************/

    //Called when language setting changes
    //#lang (string): language identifier
    //-> (void)
    change: function(lang)
    {
        if(lang != session_lang())
        {
            Kookiiz.popup.hide();
            Kookiiz.popup.loader();
            Kookiiz.api.call('session', 'lang_change', 
            {
                'callback': this.changed.bind(this),
                'request':  'lang=' + lang
            });
        }
    },

    //Called once the language has been changed
    //#response (object): server response object
    //-> (void)
    changed: function(response)
    {
        //Check if language has been changed and reload the page
        var changed = parseInt(response.parameters.changed);
        if(changed) 
            window.location.reload();
        else        
            Kookiiz.popup.hide();
    },

    /*******************************************************
    POPUP
    ********************************************************/

    //Open language changing popup
    //-> (void)
    popup: function()
    {
        Kookiiz.popup.custom(
        {
           'title':         LANGUAGES_ALERTS[0],
           'text':          LANGUAGES_ALERTS[1],
           'cancel':        true,
           'content_url':   '/dom/language_popup.php'
        });
    }
});