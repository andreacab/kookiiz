/*******************************************************
Title: Mobile
Authors: Kookiiz Team
Purpose: Main JS for mobile website version
********************************************************/

//Utilities
var Cookie = new CookieHandler(),
    Time   = new TimeAPI();

//Main Kookiiz object
var Kookiiz =
{
    MODE:     'mobile',
    VERSION:  1.0,

    ajax:     new AjaxHandler(),
    api:      new API(),
    error:    new ErrorsHandler(),
    lang:     new LanguageUI(),
    mobile:   new MobileUI(),
    popup:    new PopupHandler(),

    //Init Kookiiz object functionalities
    //-> (void)
    init: function()
    {
        if(user_logged())
        {
            this.mobile.init();
        }
    }
};

//Window onload handler
window.onload = function()
{
    //Init dynamic functionalities
    Kookiiz.init();

    //Attach listeners on input elements for focus and blur
    Utilities.observe_focus(false, 'input.focus, textarea.focus');
};