/*******************************************************
Title: Frontpage
Authors: Kookiiz Team
Purpose: Main JS for Kookiiz frontpage
********************************************************/

//Init on page load
window.onload = init;

//Utilities
var Cookie  = new CookieHandler();
var Time    = new TimeAPI();

//Kookiiz frontpage UI
var Frontpage = new FrontpageUI();

//Kookiiz main object
var Kookiiz =
{
    VERSION:    1.0,

    ajax:       new AjaxHandler(),
    api:        new API(),
    demo:       new DemoUI(),
    error:      new ErrorsHandler(),
    hash:       URLHash,
    lang:       new LanguageUI(),
    pictures:   new PicturesLib(),
    popup:      new PopupHandler(),
    subscribe:  new SubscribeUI(),
    videos:     new VideosUI()
};

//Init frontpage functionalities
//-> (void)
function init()
{
    //Init UI components
    Kookiiz.demo.init();
    Frontpage.init();
}