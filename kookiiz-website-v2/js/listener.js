/*******************************************************
Title: Listener
Authors: Kookiiz Team
Purpose: Listen to custom Kookiiz events and trigger appropriate actions
********************************************************/

//Represents an event listener to observe custom Kookiiz events and triggers chain reactions
var Listener = Class.create(
{
    object_name: 'listener',

    /*******************************************************
    CONSTRUCTOR
    ********************************************************/

    //Class constructor
    //-> (void)
    initialize: function()
    {
    },

    /*******************************************************
    LISTEN
    ********************************************************/

    //Init custom event listeners
    //-> (void)
    listen: function()
    {
        Kookiiz.session.observe('loaded', this.onSessionLoaded.bind(this));
        Kookiiz.tabs.observe('change', this.onTabsChange.bind(this));

        User.observe('deleted', this.onUserDeleted.bind(this));
        User.observe('error', this.onError.bind(this));
        User.observe('saved', this.onUserSaved.bind(this));
        User.observe('updated', this.onUserUpdated.bind(this));
    },

    /*******************************************************
    OBSERVERS
    ********************************************************/
   
    //Called when a module throws a custom error
    //#event (event): custom event object
    //-> (void)
    onError: function(event)
    {
        Kookiiz.error.handler(event.memo);
    },

    //Called when session data has been successfully loaded from server
    //-> (void)
    onSessionLoaded: function()
    {
        //Listen to hash changes
        Kookiiz.tabs.listen();
        //Hide popup loader
        Kookiiz.popup.freezeCancel();
        Kookiiz.popup.hide();
        
        //Show welcome screen for visitors
        if(!user_logged())
            Kookiiz.welcome.show();

        //Actions to perform upon session loading
        session_onload();
    },

    //Called when tabs are about to change (before they do)
    //#event (event): custom event object
    //-> (void)
    onTabsChange: function(event)
    {
        var data       = $H(event.memo),
            tab_name   = data.get('tab'),
            content_id = data.get('cid'),
            initial    = data.get('init'),
            panels_set = Kookiiz.tabs.panels_get(tab_name);

        //Prepare display
        Utilities.viewport_reset();
        Kookiiz.recipes.search_hint_stop();
        Kookiiz.panels.set(panels_set);
        
        //Update panels and tab content
        switch(tab_name)
        {
            case 'admin':
                Kookiiz.navigation.update();
                break;
            case 'article_display':
                Kookiiz.articles.display(content_id);
                break;
            case 'health':
                Kookiiz.menu.meals_unselect();
                Kookiiz.health.onProfileCategoryChange();
                Kookiiz.health.nutritionUpdate();
                Kookiiz.panels.attach('offers', 'left');
                Kookiiz.panels.move_up('offers');
                break;
            case 'main':
                Kookiiz.shopping.period_display();
                break;
            case 'profile':
                Kookiiz.navigation.update();
                Kookiiz.panels.header_set('nutrition');
                Kookiiz.options.panelsSetup();
                break;
            case 'recipe_full':
                Kookiiz.recipes.display(content_id);
                Kookiiz.glossary.clear();
                Kookiiz.panels.attach('offers', 'right');
                Kookiiz.panels.move_up('offers');
                break;
            case 'recipe_form':
                if(initial)
                    Kookiiz.recipeform.open();
                Kookiiz.glossary.clear();
                break;
            case 'recipe_translate':
                Kookiiz.recipes.translate_load(content_id);
                Kookiiz.glossary.clear();
                break;
            case 'share':		
                Kookiiz.chart.load();
                Kookiiz.events.load();
                Kookiiz.status.reset();
                Kookiiz.panels.attach('offers', 'left');
                Kookiiz.panels.move_up('offers');
                break;
            case 'shopping_finish':
                Kookiiz.shopping.list_finalize(content_id);
                Kookiiz.glossary.clear();
                Kookiiz.panels.attach('offers', 'right');
                Kookiiz.panels.move_up('offers');
                break;
        }
    },

    //Called when user profile has been deleted
    //#event (event): custom event object
    //-> (void)
    onUserDeleted: function(event)
    {
        Kookiiz.session.logout();
    },
    
    //Called when user profile has been saved
    //#event (event): custom event object
    //-> (void)
    onUserSaved: function(event)
    {
        var data = $H(event.memo),
            prop = data.get('prop');
        
        //Take appropriate actions depending on which profile property was saved
        switch(prop)
        {
            case 'menu':
               Kookiiz.menu.saved();
               break;
        }
    },

    //Called when user profile has been updated
    //#event (event): custom event object
    //-> (void)
    onUserUpdated: function(event)
    {
        var data = $H(event.memo),
            prop = data.get('prop');

        //Take appropriate actions depending on which profile property was updated
        switch(prop)
        {
            case 'activity':
                Kookiiz.health.activity_update();
                break;
            case 'allergies':
                Kookiiz.options.allergiesUpdate();
                break;
            case 'anatomy':
                Kookiiz.health.anatomy_update();
                break;
            case 'breakfast':
                Kookiiz.health.breakfast_update();
                break;
            case 'favorites':
                Kookiiz.recipes.box_update('favorite');
                break;
            case 'fridge':
                Kookiiz.fridge.update();
                Kookiiz.shopping.display();
                break;
            case 'friends':
                Kookiiz.friends.update();
                break;
            case 'markets':
                Kookiiz.shopping.markets_update();
                break;
            case 'menu':
                var type = data.get('type');
                Kookiiz.health.update();
                Kookiiz.menu.updated(type);
                Kookiiz.recipes.box_update('menu');
                Kookiiz.shopping.period_display();
                Kookiiz.shopping.update();
                break;
            case 'options':
                var option = data.get('option');
                Kookiiz.options.optionsUpdate();
                if(!option || option == 'currency' || option == 'units')
                    Kookiiz.recipes.displayUpdate();
                break;
            case 'panels':
                Kookiiz.panels.configRestore();
                break;
            case 'personal':
                Kookiiz.users.updateCard();
                Kookiiz.options.profileUpdate();
                break;
            case 'quickmeals':
                Kookiiz.quickmeals.display();
                break;
            case 'sports':
                Kookiiz.health.sportsUpdate();
                break;
            case 'tastes':
                Kookiiz.options.tastesUpdate();
                break;
        }
    }
});