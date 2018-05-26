/*******************************************************
Title: Tabs
Authors: Kookiiz Team
Purpose: Functionalities of the main tabs
********************************************************/

//Represents a user interface for main tabs
var TabsUI = Class.create(Observable,
{
    object_name: 'tabs_ui',

    /*******************************************************
    CONSTANTS
    ********************************************************/

    DEFAULT_URL:    '/' + URL_HASH_TABS[0],
    PANELS_SETS:    {
                        'admin':            ['glossary', 'navigation'],
                        'article_display':  ['comments', 'feedback', 'glossary'],
                        'chef_display':     ['feedback', 'recipes'],
                        'health':           ['feedback', 'health_profile', 'nutrition', 'offers'],
                        'main':             ['facebook', 'feedback', 'fridge', 'recipes', 'search', 'shopping'],
                        'profile':          ['fridge', 'invitations', 'navigation', 'nutrition', 'recipes'],
                        'recipe_full':      ['comments', 'feedback', 'glossary', 'nutrition', 'offers', 'recipes'],
                        'recipe_form':      ['feedback', 'glossary', 'nutrition', 'recipes'],
                        'recipe_translate': ['feedback', 'glossary', 'offers', 'recipes'],
                        'share':            ['facebook', 'feedback', 'friends', 'invitations', 'offers', 'recipes'],
                        'shopping_finish':  ['feedback', 'fridge', 'glossary', 'offers', 'recipes'],
                        'tips':             ['feedback', 'glossary']
                    },

    /*******************************************************
    CONSTRUCTOR
    ********************************************************/

    //Class constructor
    //-> (void)
    initialize: function()
    {
        this.mode     = 'hash';                 //URL mode ("hash" or "state")
        this.tab_name = '';                     //Current tab
        this.context  = '';                     //Current context
        this.temp = {'id': -1, 'title': ''};    //Temporary tab info

        this.$loader   = $('tab_loader').loading(true);
        this.$sections = $$('.kookiiz_section');
        this.$tabs     = $$('.tab');
        this.$tabsArea = $('tabs_main');
        this.$tabsTemp = $$('.tab.temp');
    },
    
    /*******************************************************
    CLOSE
    ********************************************************/

    //Close a temporary tab and returns to current context
    //-> (void)
    close: function()
    {
        this.$tabsTemp.invoke('hide');
        this.tempContentSet(-1);
        this.tempTitleSet('');
        this.show(this.context_get());
    },

    /*******************************************************
    CURRENT CONTEXT
    ********************************************************/

    //Returns current context
    //->context (string): text ID of current context
    context_get: function()
    {
        return this.context;
    },

    //Set current context depending on current tab
    //Context does not change for temporary tabs
    //#tab_name (string): text ID of current tab
    //-> (void)
    context_set: function(tab_name)
    {
        var tab_id = TABS.indexOf(tab_name);
        if(!TABS_TEMP[tab_id]) 
            this.context = tab_name;
    },

    /*******************************************************
    CURRENT TAB
    ********************************************************/

    //Returns currently displayed tab
    //->tab_name (string): text ID of current tab
    current_get: function()
    {
        return this.tab_name;
    },

    //Set current tab value
    //#tab_name (string): text ID of current tab
    //-> (void)
    current_set: function(tab_name)
    {
        this.tab_name = tab_name;
        this.context_set(tab_name);
    },
    
    /*******************************************************
    DEFAULT
    ********************************************************/
   
    //Go to default tab
    //-> (void)
    defaultTab: function()
    {
        Kookiiz.hash.go(URL_HASH_TABS[0]);
    },

    /*******************************************************
    DISPLAY
    ********************************************************/

    //Display current tab
    //-> (void)
    display: function()
    {
        //Retrieve current tab and parameters
        var tab_name = this.current_get(),
            tab_id   = TABS.indexOf(tab_name),
            tab      = $('tab_' + tab_name),
            temp     = TABS_TEMP[tab_id],
            title    = TABS_TITLES[tab_id];
        
        //Change window title
        document.title = 'Kookiiz - ' + title;

        //Hide all temporary tabs, then show current one
        if(temp)
        {
            this.$tabsTemp.invoke('hide');
            tab.show();
        }
        //Unselect all tabs, then select current tab
        this.$tabs.invoke('removeClassName', 'selected');
        tab.addClassName('selected');

        //Display associated section
        this.display_section();
    },

    //Display section for current tab setting
    //-> (void)
    display_section: function()
    {
        var tab_name  = this.current_get(),
            tab_id    = TABS.indexOf(tab_name),
            menu_show = TABS_MENU_SHOW[tab_id];
        
        //Hide all sections, then show selected one
        this.$loader.hide();
        this.$sections.invoke('hide');
        if(User.option_get('fast_mode'))
            //No transition effect
            $('section_' + tab_name).show();
        else
        {
            //Make section appear
            Effect.Appear('section_' + tab_name,
            {
                'duration':     0.5,
                'queue':        {'scope': 'tabs'}
            });
        }
        
        //Hide/Show menu on specific tabs
        if(menu_show)
            $('kookiiz_menu').show();
        else
            $('kookiiz_menu').hide();
    },

    /*******************************************************
    404 - NOT FOUND
    ********************************************************/

    //Display "not found" tab
    //-> (void)
    error_404: function()
    {
        Effect.Queues.get('tabs').invoke('cancel');
        this.show('error_404');
    },
    
    /*******************************************************
    INIT
    ********************************************************/

    //Init dynamic functionalities
    //-> (void)
    init: function()
    {
        this.$tabs.invoke('observe', 'click', this.onTabClick.bind(this));
        $$('.tab img.cancel').invoke('observe', 'click', this.onClose.bind(this));
    },
    
    /*******************************************************
    LISTEN
    ********************************************************/
    
    //Listen to hash changes
    //-> (void)
    listen: function()
    {
        Kookiiz.hash.init(this.onHashChange.bind(this), $('kookiiz_hash_iframe'));
        //window.History.Adapter.bind(window, 'hashchange', this.onStateChange.bind(this));
        //this.onStateChange();
    },

    /*******************************************************
    LOADER
    ********************************************************/

    //Hide loader and display current tab content
    //-> (void)
    loaded: function()
    {
        this.$loader.hide();
        this.display_section();
    },

    //Show loader and hide current tab content
    //-> (void)
    loading: function()
    {
        Effect.Queues.get('tabs').invoke('cancel');
        this.$sections.invoke('hide');
        this.$loader.show();
    },

    /*******************************************************
    EVENTS
    ********************************************************/

    //Called after a click on tab closing icon
    //#event (object): DOM click event
    //-> (void)
    onClose: function(event)
    {
        event.stop();
        this.close();
    },

    //Callback for URL hash change
    //#hash (string):  current URL hash
    //#initial (bool): true if it was called from initial page load
    //-> (void)
    onHashChange: function(hash, initial)
    {
        if(initial && !hash) 
            this.defaultTab();
        else
        {
            var hash_split = hash.split('-');

            //Check tab name
            var tab = hash_split[0],
                tab_id = URL_HASH_TABS.indexOf(tab),
                tab_name = TABS[tab_id];
            if(tab_id < 0 || !$('tab_' + tab_name))
            {
                this.defaultTab();
                return;
            }
            else
                this.current_set(tab_name);

            //Related content ID
            var content_id = hash_split.length > 1 ? parseInt(hash_split[1]) : -1;
            if(TABS_HAS_CONTENT[tab_id])
            {
                if(content_id < 0)
                {
                   this.defaultTab();
                   return;
                }
                else
                    this.tempContentSet(content_id);
            }

            //Display tab
            this.display();
            //Fire tab change event
            this.fire('change', {'tab': tab_name, 'cid': content_id, 'init': initial});
        }
    },

    //Called by history module when URL state changes
    //-> (void)
    onStateChange: function()
    {
        var state = window.History.getState(), hash = state.hash;
        if(hash == '/' || hash == '/#/')
            window.History.pushState(null, null, this.DEFAULT_URL);
        else
        {
            //Remove leading "/" and split on "-"
            hash = hash.replace(/\//g, '').replace(/#/, '').split('-');
            //Check tab value
            var tab_id = URL_HASH_TABS.indexOf(hash[0]);
            if(tab_id < 0)
            {
                window.History.pushState(null, null, this.DEFAULT_URL);
                return;
            }
            //Check content ID value
            var content_id = hash.length > 1 ? parseInt(hash[1]) : -1;
            if(TABS_HAS_CONTENT[tab_id] && content_id < 0)
            {
                window.History.pushState(null, null, this.DEFAULT_URL);
                return;
            }
            //Display selected tab
            this.display(tab_id, content_id);
        }
    },

    //Callback for tabs click
    //-> (void)
    onTabClick: function(event)
    {
        var tab = event.findElement('.tab');
        if(tab)
        {
            //Show selected tab
            var tabName    = tab.id.sub('tab_', ''),
                tabID      = TABS.indexOf(tabName),
                hasContent = TABS_HAS_CONTENT[tabID];
            if(hasContent)
                this.show(tabName, this.tempContentGet(), this.tempTitleGet());
            else
                this.show(tabName);
        }
    },

    /*******************************************************
    PANELS
    ********************************************************/

    //Return panel set for current or provided tab
    //#tab_name (string): short panel name
    //->panels_set (array): list of panel names
    panels_get: function(tab_name)
    {
        tab_name = tab_name || this.current_get();
        return this.PANELS_SETS[tab_name] || [];
    },
    
    /*******************************************************
    SHOW
    ********************************************************/

    //Switch to specified tab
    //#tab_name (string):       text ID of the tab to display
    //#content_id (int):        ID of the content displayed in the tab (optional)
    //#content_title (string):  text to display after the content ID (optional)
    //-> (void)
    show: function(tab_name, content_id, content_title)
    {
        if(typeof(content_id) == 'undefined' || isNaN(content_id)) content_id = -1;
        if(typeof(content_title) == 'undefined') content_title = '';
        
        //Build tab URL
        var tab_id  = TABS.indexOf(tab_name),
            tab_key = URL_HASH_TABS[tab_id],
            temp    = TABS_TEMP[tab_id];
        var tab_url = tab_key + (content_id >= 0 ? ('-' + content_id) : '');
        if(content_id >= 0 && content_title)
            tab_url += '-' + Utilities.text2link(content_title);
        
         //Check if selected tab is not already open and if a tab switch is not already in queue
        if((tab_name != this.current_get() || content_id != this.tempContentGet())
            && (!Effect.Queues.get('tabs') || !Effect.Queues.get('tabs').size()))
        {
            //Update current context
            this.current_set(tab_name);
            if(temp)
            {
                this.tempContentSet(content_id);
                this.tempTitleSet(content_title);
            }
            
            //window.History.pushState(null, null, '/' + url);
            Kookiiz.hash.go(tab_url);
        }
        //Only title changed
        else if(content_title != this.tempTitleGet())
        {
            this.tempTitleSet(content_title);
            Kookiiz.hash.go(tab_url, true);
        }  
    },

    /*******************************************************
    TEMPORARY TABS
    ********************************************************/

    tempContentGet: function()
    {
        return this.temp.id;
    },

    tempContentSet: function(id)
    {
        this.temp.id = id;
    },

    tempTitleGet: function()
    {
        return this.temp.title;
    },

    tempTitleSet: function(title)
    {
        this.temp.title = title;
    },
    
    /*******************************************************
    URL
    ********************************************************/
   
    //Return formated URL from tab, content ID and content title
    //#tab (string):    tab name
    //#cid (int):       content ID
    //#title (string):  content title
    //->url (string): formated URL
    toURL: function(tab, cid, title)
    {
        var hash = tab;
        if(cid && cid > 0)
        {
            hash += '-' + cid;
            if(title)
                hash += Utilities.text2link(title);
        }

        if(this.mode == 'state')
            return '/' + hash;
        else
            return '/#/' + hash;
    }
});