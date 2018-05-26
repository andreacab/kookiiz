/*******************************************************
Title: Navigation
Authors: Kookiiz Team
Purpose: Navigation panel functionalities
********************************************************/

//Represents a user interface for the navigation panel
var NavigationUI = Class.create(
{
    object_name: 'navigation_ui',

    /*******************************************************
    CONSTRUCTOR
    ********************************************************/

    //Class constructor
    //-> (void)
    initialize: function()
    {
        this.$container = $('navigation_container');
    },
    
    /*******************************************************
    DISPLAY
    ********************************************************/

    //Display navigation list for current tab
    //-> (void)
    display: function()
    {
        //Empty container and get current tab
        this.$container.clean();
        var tab_name = Kookiiz.tabs.current_get();

        //Build navigation list
        var section_item, section_title;
        var sections_list   = new Element('ul', {'id': 'nav_list'});
        var sections_titles = $('section_' + tab_name).select('.nav_title').map(function(title){return title.innerHTML;});
        for(var i = 0, imax = sections_titles.length; i < imax; i++)
        {
            section_item    = new Element('li', {'class': 'nav_item'});
            section_title   = new Element('span', {'class': 'click'});
            section_item.writeAttribute('data-sectionid', i);
            section_title.innerHTML = sections_titles[i];
            section_item.appendChild(section_title);
            sections_list.appendChild(section_item);
        }

        //Append sections list (if it's not empty)
        if(sections_list.empty()) 
            this.$container.innerHTML = NAVIGATION_ALERTS[0];
        else
        {
            sections_list.observe('click', this.onNavClick.bind(this));
            this.$container.appendChild(sections_list);
        }
    },

    /*******************************************************
    OBSERVERS
    ********************************************************/

    //Called when the navigation list is clicked
    //#event (object): DOM click event
    //-> (void)
    onNavClick: function(event)
    {
        var item = event.findElement('.nav_item');
        if(item)
        {
            var item_id = parseInt(item.readAttribute('data-sectionid'));
            this.show(item_id);
        }
    },

    /*******************************************************
    SHOW
    ********************************************************/

    //Show section with provided ID
    //#section_id (int): ID of the section
    //-> (void)
    show: function(section_id)
    {
        //Hide all sections except selected one
        var tab_name = Kookiiz.tabs.current_get();
        var sections = $('section_' + tab_name).select('.nav_section');
        sections.each(function(section, index)
        {
            if(index != section_id)
                section.hide();
        });

        //Display selected section directly or with effect
        if(!sections[section_id].visible())
        {
            if(User.option_get('fast_mode'))
                sections[section_id].show();
            else
                new Effect.Appear(sections[section_id], {'duration': 0.5});
        }
    },

    /*******************************************************
    UPDATE
    ********************************************************/

    //Update navigation panel for current context
    //-> (void)
    update: function()
    {
        //Check if exactly one navigation section is shown
        var tab_name    = Kookiiz.tabs.current_get();
        var visible     = $('section_' + tab_name).select('.nav_section').findAll(function(sec){return sec.visible();});
        if(visible.length != 1)
            this.show(0);

        //Display navigation list
        this.display();
    }
})