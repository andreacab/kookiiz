/*******************************************************
Title: Main
Authors: Kookiiz Team
Purpose: JS init file for Kookiiz main page
********************************************************/

//Utilities
var Cookie = new CookieHandler(),
    Time   = new TimeAPI();

//Data containers
var Ingredients = new IngredientsDB(),
    Invitations = new InvitationsLib(),
    Quickmeals  = new QuickmealsLib(),
    Recipes     = new RecipesLib(),
    Units       = new UnitsLib(),
    Users       = new UsersLib();

//User profile object
var User = new UserPrivate();

//Main Kookiiz object
var Kookiiz =
{
    MODE:       'full',
    VERSION:    1.0,

    ajax:           new AjaxHandler(),
    api:            new API(),
    chart:          new ChartUI(),
    comments:       new CommentsUI(),
    error:          new ErrorsHandler(),
    events:         new EventsUI(),
    feedback:       new FeedbackUI(),
    fridge:         new FridgeUI(),
    friends:        new FriendsUI(),
    glossary:       new GlossaryUI(),
    hash:           URLHash,
    health:         new HealthUI(),
    help:           new HelpUI(),
    hover:          new HoverUI(),
    invitations:    new InvitationsUI(),
    lang:           new LanguageUI(),
    listener:       new Listener(),
    menu:           new MenuUI(),
    navigation:     new NavigationUI(),
    networks:       new NetworksUI(),
    news:           new NewsUI(),
    notifications:  new NotificationsUI(),
    options:        new OptionsUI(),
    panels:         new PanelsUI(),
    partners:       new PartnersUI(),
    pictures:       new PicturesLib(),
    popup:          new PopupHandler(),
    quickmeals:     new QuickmealsUI(),
    recipeform:     new RecipeForm(),
    recipes:        new RecipesUI(),
    session:        new SessionHandler(),
    shopping:       new ShoppingUI(),
    status:         new StatusUI(),
    tabs:           new TabsUI(),
    users:          new UsersUI(),
    videos:         new VideosUI(),
    welcome:        new WelcomeUI(),

    //Init Kookiiz object functionalities
    //-> (void)
    init: function()
    {
        var self = this;
        
        //Preload pictures
        this.pictures.preload();
        
        //Start custom event listener
        this.listener.listen();

        //Init UI components
        this.comments.init();
        this.events.init();
        this.feedback.init();
        this.fridge.init();
        this.friends.init();
        this.glossary.init();
        this.health.init();
        this.help.init();
        this.hover.init();
        this.invitations.init();
        this.menu.init();
        this.news.init();
        this.notifications.init();
        this.options.init();
        this.panels.init();
        this.recipes.init();
        this.shopping.init();
        this.status.init();
        this.tabs.init();
        this.users.init();
        
        //Logout & Sign-up
        if(user_logged())
            $('logout_button').observe('click', function()
            {
                self.session.logout();
            });
        else                
            $$('button.signUp').invoke('observe', 'click', function()
            {
                self.welcome.signup();
            });
        
        //Load session
        this.session.update();

        //Select default shopping list
        if(this.tabs.current_get() != 'shopping_full')
            this.shopping.list_default();
    }
};

//Window onload handler
window.onload = function()
{
    //Reset viewport
    Utilities.viewport_reset();
    
    //Display caption for old browsers
    if(Prototype.Browser.IE6)
        window.alert(FRONTPAGE_TEXT[3]);
    
    //Display loader
    Kookiiz.popup.loader();
    Kookiiz.popup.freeze();

    //Import libraries
    Ingredients.importDB(INGS_DB);
    Units.import_content(UNITS_LIB);

    //Init dynamic functionalities
    Kookiiz.init();
    if(typeof(Admin) != 'undefined') Admin.init();

    //Attach listeners on input elements for focus and blur
    Utilities.observe_focus(false, 'input.focus, textarea.focus');
    
    //Display Facebook social module
    FB.XFBML.parse($('kookiiz_facebook'));
};