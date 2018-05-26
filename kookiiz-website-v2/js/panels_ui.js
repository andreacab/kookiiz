/*******************************************************
Title: Panels UI
Authors: Kookiiz Team
Purpose: Display, toggle and move side panels
********************************************************/

//Represents a user interface for Kookiiz side panels
var PanelsUI = Class.create(
{
    object_name: 'panels_ui',

    /*******************************************************
    CONSTANTS
    ********************************************************/

    COLUMN_HEIGHT:      300,    //Default height (in px) of an empty panel column
    HELP_LEFT:          20,     //Panel help positioning
    HELP_REVERSE_LEFT:  -440,
    HELP_REVERSE_TOP:   -205,
    HELP_TOP:           -205,
    //Short name of each panel
    KEYS:               ['search', 'recipes', 'fridge', 'shopping', 'nutrition',
                            'friends', 'chefs', 'ww', 'comments', 'partner',
                            'glossary', 'health_profile', 'articles', 'invitations', 'user',
                            'navigation', 'feedback', 'offers', 'facebook'],

    /*******************************************************
    CONSTRUCTOR
    ********************************************************/

    //Class constructor
    //-> (void)
    initialize: function()
    {
        this.config     = 
        {
            'ids':      [],
            'sides':    [],
            'status':   []
        };
        this.config_frozen  = false;
        this.help_id        = -1;
        this.panels         = [];

        //Create a panel object instance for each panel DOM element
        $$('div.kookiiz_panel').each(function(panel)
        {
            this.panels.push(new Panel(panel));
        }, this);

        //Store panel-related nodes
        this.PANELS_LEFT  = $('kookiiz_panels_left');
        this.PANELS_RIGHT = $('kookiiz_panels_right');
    },

    /*******************************************************
    CONFIG
    ********************************************************/

    //Apply provided panels configuration
    //#config (object): panels configuration
    //-> (void)
    configApply: function(config)
    {
        var id, side, status, panel;
        for(var i = 0, imax = config.ids.length; i < imax; i++)
        {
            id      = parseInt(config.ids[i]);
            side    = parseInt(config.sides[i]);
            status  = parseInt(config.status[i]);
            panel   = this.panel_get(id);
            if(panel)
            {
                panel.attach(side == 0 ? this.PANELS_LEFT : this.PANELS_RIGHT);
                panel.toggle(status ? 'open' : 'close', true);
            }
        }
        this.configOverride();
    },

    //Set current configuration as frozen, to avoid updates
    //-> (void)
    configFreeze: function()
    {
        this.config_frozen = true;
    },

    //Tells if panels configuration is currently frozen
    //->frozen (bool): true if configuration is frozen
    configFrozen: function()
    {
        return this.config_frozen;
    },

    //Special rules that override current config
    //-> (void)
    configOverride: function()
    {
        this.move_down('feedback');
        this.move_down('offers');
    },

    //Cancel freezing
    //-> (void)
    configRelease: function()
    {
        this.config_frozen = false;
    },

    //Restore configuration from last save
    //-> (void)
    configRestore: function()
    {       
        var config   = User.panels_get(),
            old_json = Object.toJSON(config),
            new_json = Object.toJSON(this.config),
            changed  = old_json != new_json;
        if(changed) 
            this.configSet(config);
    },

    //Save panels configuration
    //-> (void)
    configSave: function()
    {
        User.panels_set(this.config);
    },

    //Set panels configuration variable
    //Apply it immediately if current config is not frozen
    //#config (object): new panels configuration
    //-> (void)
    configSet: function(config)
    {
        //Check config structure validity
        if(!config.ids) return;
        this.panels.each(function(panel)
        {
            if(config.ids.indexOf(panel.get_id()) === -1)
            {
                config.ids.push(panel.get_id());
                config.sides.push(panel.get_side());
                config.status.push(panel.get_status());
            }
        });

        //Apply new config if current config is not frozen
        if(!this.configFrozen())
        {
            this.config = config;
            this.configApply(config);
        }
    },

    //Called when panels configuration is updated by user action or function call
    //Save current configuration in local variable
    //#save (bool): whether to save new configuration on server as well (defaults to false)
    //-> (void)
    configUpdate: function(save)
    {
        var panels = $$('.kookiiz_panel'),
            config = {'ids': [], 'sides': [], 'status': []},
            panel_id, panel;
        panels.each(function(element)
        {
            panel_id = parseInt(element.id.split('_')[1]);
            panel    = this.panel_get(panel_id);
            config.ids.push(panel.get_id());
            config.sides.push(panel.get_side());
            config.status.push(panel.get_status());
        }, this);

        //Check that config really changed
        var json_cur = Object.toJSON(this.config),
            json_new = Object.toJSON(config);
        if(json_cur != json_new)
        {
            //Update configuration variable
            this.config = config;
            //Save panels configuration in database
            if(user_logged() && save)
                this.configSave();
        }
    },
    
    /*******************************************************
    INIT
    ********************************************************/

    //Init dynamic functionalities
    //-> (void)
    init: function()
    {
        //Create panels Sortables
        if(this.PANELS_LEFT)
        {
            Sortable.create(this.PANELS_LEFT,
            {
                'tag':          'div',
                'only':         'kookiiz_panel',
                'handle':       'handle',
                'dropOnEmpty':  true,
                'constraint':   false,
                'overlap':      'vertical',
                'containment':  [this.PANELS_LEFT, this.PANELS_RIGHT],
                'markDropZone': true,
                'onUpdate':     this.drop.bind(this)
            });
        }
        if(this.PANELS_RIGHT)
        {
            Sortable.create(this.PANELS_RIGHT,
            {
                'tag':          'div',
                'only':         'kookiiz_panel',
                'handle':       'handle',
                'dropOnEmpty':  true,
                'constraint':   false,
                'overlap':      'vertical',
                'containment':  [this.PANELS_LEFT, this.PANELS_RIGHT],
                'markDropZone': true,
                'onUpdate':     this.drop.bind(this)
            });
        }

        //Init help system
        this.PANELS_HELP = $('panels_help');
        this.PANELS_HELP.select('img.cancel')[0].observe('click', this.help_hide.bind(this));
        this.panels.each(function(panel)
        {
            panel.observe('help', this.help_callback.bind(this, panel));
            panel.observe('toggle', this.toggle_callback.bind(this, panel));
        }, this);

        //Save default panel configuration
        this.configUpdate();
    },

    /*******************************************************
    PANEL OPERATIONS
    ********************************************************/

    //Attach panel to a given column (or all of them)
    //#name (string):   panel short name (false for all of them)
    //#side (string):   either "left" or "right"
    //#save (bool):     whether to save panels configuration after action
    //-> (void)
    attach: function(name, side, save)
    {
        var column = side == 'left' ? this.PANELS_LEFT : this.PANELS_RIGHT;
        if(name)    
            this.panel_get(name).attach(column);
        else        
            this.panels.invoke('attach', column);

        //Report configuration update
        this.configUpdate(save);
    },

    //Prevent toggling for a specific panel or all of them
    //#name (string):   panel short name (defaults to all)
    //-> (void)
    freeze: function(name)
    {
        if(name)    
            this.panel_get(name).freeze();
        else        
            this.panels.invoke('freeze');
    },

    //Set header of panel with provided name
    //#name (string): panel short name
    //#text (string): panel header (defaults to default header)
    //-> (void)
    header_set: function(name, text)
    {
        this.panel_get(name).header_set(text);
    },

    //Hide panel with provided name
    //#name (string): panel short name
    //-> (void)
    hide: function(name)
    {
        this.panel_get(name).hide();
    },

    //Check if a specific panel is disabled
    //#name (string): panel short name
    //-> (void)
    is_disabled: function(name)
    {
        return this.panel_get(name).is_disabled();
    },

    //Move a specific panel at the bottom of its column
    //#name (string):   panel short name
    //#save (bool):     whether to save panels configuration after action
    //-> (void)
    move_down: function(name, save)
    {
        this.panel_get(name).move_down();
        this.configUpdate(save);
    },

    //Move a specific panel at the top of its column
    //#name (string):   panel short name
    //#save (bool):     whether to save panels configuration after action
    //-> (void)
    move_up: function(name, save)
    {
        this.panel_get(name).move_up();
        this.configUpdate(save);
    },

    //Show panel with provided name
    //#name (string): panel short name
    //#forceUp (bool): move panel at the top of its column as well (defaults to false)
    //-> (void)
    show: function(name, forceUp)
    {
        var panel = this.panel_get(name);
        if(forceUp) panel.move_up();
        panel.show(true);
    },

    //Open/close specific panel (or all of them)
    //#name (string):   panel short name (false for all of them)
    //#mode (string):   either "open" or "close"
    //#save (bool):     whether to save panels configuration ON SERVER after toggling (defaults to true)
    //#fastMode (bool): if set to true panel is toggled without any transition effect (defaults to user's setting)
    //-> (void)
    toggle: function(name, mode, save, fastMode)
    {
        if(typeof(save) == 'undefined')     save = true;
        if(typeof(fastMode) == 'undefined') fastMode = User.option_get('fast_mode');

        //Toggle
        if(name)    
            this.panel_get(name).toggle(mode, fastMode, false);
        else
            this.panels.invoke('toggle', mode, fastMode, false);

        //Report configuration update
        this.configUpdate(save);
    },

    //Unfreeze a specific panel (or all of them)
    //#name (string):   panel short name (defaults to all)
    //-> (void)
    unfreeze: function(name)
    {
        if(name)    
            this.panel_get(name).unfreeze();
        else        
            this.panels.invoke('unfreeze');
    },

    /*******************************************************
    GET
    ********************************************************/

    //Return specific panel object
    //#identifier (string/int): panel short name or panel ID
    //->panel (object): panel object
    panel_get: function(identifier)
    {
        var panel_id = typeof(identifier) == 'number' ? identifier : this.KEYS.indexOf(identifier);
        return this.panels.detect(function(panel){return panel.get_id() == panel_id;});
    },
    
    /*******************************************************
    SHOW
    ********************************************************/

    //Display a given set of panels
    //#panels_set (array):  list of panel names or IDs
    //#fastMode (bool):     if set to true the panels are displayed without any transition effect (defaults to user's setting)
    //-> (void)
    set: function(panels_set, fastMode)
    {
        if(typeof(panels_set) == 'undefined')   panels_set  = [];
        if(typeof(fastMode) == 'undefined')     fastMode    = User.option_get('fast_mode');

        //Hide panel help
        this.help_hide();

        //Restore panels configuration
        this.unfreeze();        //Remove temporary panels freezing
        this.configRelease();   //Release lock on panels configuration
        this.configRestore();   //Restore last saved configuration

        //Loop through panels and show those from the given panels set
        var name = '', excluded = false;
        this.panels.each(function(panel)
        {
            name        = this.KEYS[panel.get_id()];
            excluded    = User.option_get('panel_' + name) === 0;
            if(panels_set.indexOf(name) >= 0 && !excluded)  
                panel.show(fastMode);
            else                                            
                panel.hide();
        }, this);

        //Check if any panel column ends up empty
        this.column_empty_check();
    },

    /*******************************************************
    HELP
    ********************************************************/

    //Called when a panel triggers the help event
    //#panel (object): panel object
    //-> (void)
    help_callback: function(panel)
    {
        this.help_display(this.KEYS[panel.get_id()]);
    },

    //Display contextual help for specified panel
    //#name (string): panel short name
    //#fastMode (bool): if set to true, no transition effect is used (defaults to user setting)
    //-> (void)
    help_display: function(name, fastMode)
    {
        if(typeof(fastMode) == 'undefined') 
            fastMode = User.option_get('fast_mode');
        
        var panel = this.panel_get(name),
            panel_id = panel.get_id();

        //Check if help for this panel is already displayed
        if(this.help_id == panel_id)
        {
            //Then close help and abort function
            this.help_hide();
            return;
        }

        //Don't do anything if an effect is already taking place
        var queue = Effect.Queues.get('panels_help');
        if(!queue || !queue.size())
        {
            //Panel side
            var panel_side          = panel.get_side() ? 'right' : 'left';

            //Retrieve panel help icon position
            var panel_help          = panel.element.select('img.panel_help')[0];
            var help_position       = panel_help.cumulativeOffset();
            var help_position_rel   = panel_help.viewportOffset();

            //Retrieve help bubble components
            var help_dimensions     = this.PANELS_HELP.getDimensions();
            var help_content        = this.PANELS_HELP.select('.content')[0];
            var help_close_button   = this.PANELS_HELP.select('img.cancel')[0];

            //Setup help area
            this.PANELS_HELP.hide();
            if(panel_side == 'left' && help_position_rel.top > help_dimensions.height)
            {
                this.PANELS_HELP.setStyle(
                {
                    'backgroundImage':  'url(' + Kookiiz.pictures.getURL('/panels/help.png', true) + ')',
                    'top':              help_position.top + this.HELP_TOP + 'px',
                    'left':             help_position.left + this.HELP_LEFT + 'px'
                });
                help_content.setStyle({'top': '5px'});
                help_close_button.setStyle({'top': '135px'});
            }
            else if(panel_side == 'left' && help_position_rel.top < help_dimensions.height)
            {
                this.PANELS_HELP.setStyle(
                {
                    'backgroundImage':  'url(' + Kookiiz.pictures.getURL('/panels/help_down.png', true) + ')',
                    'top':              help_position.top + 'px',
                    'left':             help_position.left + this.HELP_LEFT + 'px'
                });
                help_content.setStyle({'top': '35px'});
                help_close_button.setStyle({'top': '170px'});
            }
            else if(panel_side == 'right' && help_position_rel.top > help_dimensions.height)
            {
                this.PANELS_HELP.setStyle(
                {
                    'backgroundImage':  'url(' + Kookiiz.pictures.getURL('/panels/help_reverse.png', true) + ')',
                    'top':              help_position.top + this.HELP_REVERSE_TOP + 'px',
                    'left':             help_position.left + this.HELP_REVERSE_LEFT + 'px'
                });
                help_content.setStyle({'top': '5px'});
                help_close_button.setStyle({'top': '135px'});
            }
            else if(panel_side == 'right' && help_position_rel.top < help_dimensions.height)
            {
                this.PANELS_HELP.setStyle(
                {
                    'backgroundImage':  'url(' + Kookiiz.pictures.getURL('/panels/help_reverse_down.png', true) + ')',
                    'top':              help_position.top + 'px',
                    'left':             help_position.left + this.HELP_REVERSE_LEFT + 'px'
                });
                help_content.setStyle({'top': '35px'});
                help_close_button.setStyle({'top': '170px'});
            }

            //Display help text in bubble
            this.PANELS_HELP.select('.text')[0].innerHTML = PANELS_HELP[panel_id];

            //Show the help bubble
            if(fastMode) 
                this.PANELS_HELP.show();
            else
            {
                new Effect.Appear(this.PANELS_HELP,
                {
                    'duration': 0.4,
                    'queue':    {'scope': 'panels_help'}
                });
            }

            //Update current panel help value
            this.help_id = panel_id;

            //Set up listener on body to close the help bubble
            document.observe('click', this.help_document_click.bind(this));
        }
    },

    //Hide contextual help
    //-> (void)
    help_hide: function()
    {
        document.stopObserving('click');
        this.PANELS_HELP.hide();
        this.help_id = -1;
    },

    /*******************************************************
    TOGGLE
    ********************************************************/

    //Called when a panel triggers a toggle event
    //#panel (object): panel object
    //-> (void)
    toggle_callback: function(panel)
    {
        this.configUpdate(true);
    },

    /*******************************************************
    COLUMNS
    ********************************************************/

    //Check if a panel column is empty and set a default height
    //#panels_area (DOM/string): panel column DOM element (or its ID)
    //-> (void)
    column_empty_check: function(panels_area)
    {
        if(panels_area) 
            panels_area = $(panels_area);
        else
        {
            this.column_empty_check(this.PANELS_LEFT);
            this.column_empty_check(this.PANELS_RIGHT);
            return;
        }

        //Set a default height if panels area is empty or contains only hidden panels
        var panels = panels_area.childElements(), 
            empty = !panels.any(function(panel)
            {
                return panel.visible();
            });
        if(empty)   
            panels_area.style.height = this.COLUMN_HEIGHT + 'px';
        else        
            panels_area.style.height = '';
    },

    /*******************************************************
    CALLBACKS
    ********************************************************/

    //Called when a panel is dropped in the left or right column
    //#panels_area (DOM): DOM element inside which panel has been dropped
    //-> (void)
    drop: function(panels_area)
    {
        this.configUpdate(true);
        this.column_empty_check(panels_area);
    },

    //Called when any part of the page is clicked when panels help is displayed
    //#event (event): DOM click event
    //-> (void)
    help_document_click: function(event)
    {
        var help_icon = event.findElement('.panel_help');
        if(help_icon)   
            return;
        else            
            this.help_hide();
    }
});