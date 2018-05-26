/*******************************************************
Title: Recipe theme
Authors: Kookiiz Team
Purpose: Define a recipe theme object
********************************************************/

//Represents a recipe search theme
var RecipeTheme = Class.create(
{
	object_name: 'recipe_theme',

    /*******************************************************
    CONSTRUCTOR
    ********************************************************/

    //Class constructor
    //#id (int):            theme numerical ID
    //#name (string):       theme name
    //#criteria (object):   related search criteria
    //-> (void)
	initialize: function(id, name, criteria)
    {
        this.id       = id;
        this.name     = name;
        this.criteria = criteria;
        this.title    = RECIPES_THEMES_NAMES[this.id];
    },

    /*******************************************************
    APPLY
    ********************************************************/

	//Activate the theme
    //-> (void)
	apply: function()
    {
        //Define search criteria
        var criteria = Object.extend(Kookiiz.recipes.search_criteria(true), this.criteria);

        //Throw new recipe search
        Kookiiz.recipes.search_reset();
        Kookiiz.recipes.search(criteria);

        //Display theme name
        //var theme_name = RECIPES_THEMES_TEXT[2] + ' : ' + this.title;
        //Kookiiz.recipes.search_summary_display(theme_name, 'title');
    },

    /*******************************************************
    DISPLAY
    ********************************************************/

    //Display theme as clickable button
    //#container (DOM/string):  container DOM element (or its ID)
    //-> (void)
	display: function(container)
    {
        container = $(container);

        //Define theme class
        var theme_class = 'recipe_theme';
        if(this.name == 'season')
        {
            var now = time_date_get();
            theme_class += ' season_' + now.season;
        }
        else
            theme_class += ' ' + this.name;

        //Build theme structure
        var theme         = new Element('div', {'class': theme_class}),
            theme_header  = new Element('div', {'class': 'header'}),
            theme_footer  = new Element('div', {'class': 'footer'}),
            theme_content = new Element('div', {'class': 'content'}),
            theme_image   = new Element('div', {'class': 'image'}),
            theme_title   = new Element('h6', {'class': 'title center'});
        theme_content.appendChild(theme_image);
        theme_title.innerHTML = this.title;
        theme.appendChild(theme_header);
        theme.appendChild(theme_content);
        theme.appendChild(theme_footer);
        theme.appendChild(theme_title);
        theme.observe('click', this.onClick.bind(this));

        //Append theme to container
        theme.hide();
        container.appendChild(theme);
        theme.appear({'duration': 0.5});
    },

    /*******************************************************
    OBSERVERS
    ********************************************************/

    //Callback for theme click
    //-> (void)
    onClick: function()
    {
        this.apply();
    }
});