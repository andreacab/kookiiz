/*******************************************************
Title: Recipes UI
Authors: Kookiiz Team
Purpose: Functionalities of the recipes user interface
********************************************************/

//Represents a user interface for recipes-related functionalities
var RecipesUI = Class.create(
{
    object_name: 'recipes_ui',

    /*******************************************************
    CONSTANTS
    ********************************************************/

    ING_NAME_MAX:           100,    //Max chars for ingredients on recipe tab
    PREVIEW_DESC_MAX:       350,    //Max chars for recipe preview description
    PREVIEW_ING_MAX:        8,      //Max number of ingredients on preview
    PREVIEW_TITLE_MAX:      50,     //Max chars for recipe preview title
    RECIPE_NAME_BOXMAX:     35,     //Max chars for recipe name in box and list
    RECIPE_NAME_LISTMAX:    40,
    SEARCH_PERPAGE:         18,     //Number of search results per page
    SEARCH_SUMMARY_MAX:     150,    //Max number of chars for search summary
    THEMES_MAX:             6,      //Max number of themes to display at once

    //Type of recipe box modes
    BOX_TYPES:  ['favorite', 'menu', 'viewed', 'searched'],
    //Recipe search themes
    THEMES:     [
                    {'id': 0, 'name': 'quick_success',
                        'criteria': {'quick': 1, 'success': 1}
                    },
                    {'id': 1, 'name': 'quick_veggie',
                        'criteria': {'quick': 1, 'veggie': 1}
                    },
                    {'id': 2, 'name': 'cheap_healthy',
                        'criteria': {'cheap': 1, 'healthy': 1}
                    },
                    {'id': 3, 'name': 'easy_quick',
                        'criteria': {'easy': 1, 'quick': 1}
                    },
                    {'id': 4, 'name': 'quick',
                        'criteria': {'quick': 1}
                    },
                    {'id': 5, 'name': 'desserts',
                        'criteria': {'category': 3}
                    },
                    {'id': 6, 'name': 'breakfast',
                        'criteria': {'category': 6}
                    },
                    {'id': 7, 'name': 'success',
                        'criteria': {'success': 1}
                    }
                ],

    /*******************************************************
    CONSTRUCTOR
    ********************************************************/

    //Class constructor
    //-> (void)
    initialize: function()
    {
        this.box_type           = 'favorite';   //Current recipe box type
        this.box_type_old       = 'favorite';   //Previous box type
        this.criteria           = {};           //Last search criteria
        this.drags              = $H();         //List of draggable elements
        this.preview_enabled    = true;         //Is recipe preview enabled ?
        this.recipe_displayed   = 0;            //Currently displayed recipe
        this.search_results     = [];           //Store last search results
        this.search_timestamp   = 0;            //Store last search timestamp
        this.themes             = [];           //Current recipe themes
        this.viewed             = [];           //Recipes viewed during the session

        //DOM nodes
        this.$boxContent        = $('recipe_box_content');
        this.$boxCriteria       = $('recipe_box_criteria');
        this.$boxDrop           = $('recipe_box_drop');
        this.$boxSorting        = $('recipe_box_sorting');
        this.$recipeDisplay     = $('recipe_display');
        this.$recipePreview     = $('recipe_preview');
        this.$searchButton      = $('recipes_search_button');
        this.$searchCategory    = $('select_search_category');
        this.$searchControls    = $('recipes_results_controls');
        this.$searchCriteria    = $('recipes_search_extended');
        this.$searchHint        = $('recipes_search_hint');
        this.$searchIndex       = $('recipes_search_index');
        this.$searchInput       = $('recipes_search_input');
        this.$searchLoader      = $('recipes_search_loader');
        this.$searchOrigin      = $('select_search_origin');
        this.$searchReset       = $('recipes_search_reset');
        this.$searchResults     = $('recipes_search_results');
        this.$searchSorting     = $('recipes_sorting');
        this.$searchSummary     = $('recipes_search_description');
        this.$searchToggle      = $('recipes_search_toggle');
        this.$themes            = $('recipes_themes_display');
        this.$themesBorder      = $('recipes_themes_border');
        //this.$themesDisplay     = $('recipes_themes_display').select('.display')[0];
        this.$themesDisplay     = this.$searchResults;
        this.$themesTab         = $('recipes_themes_tab');
    },

    /*******************************************************
    INIT
    ********************************************************/

    //Init dynamic functionalities
    //-> (void)
    init: function()
    {
        //DISPLAY TAB
        $('recipe_display_actions').observe('click', this.display_action_click.bind(this));

        //MAIN TAB
        this.$searchLoader.loading(true);
        this.$searchSorting.observe('change', this.search_sorting_change.bind(this));
        $('recipe_edit').onclick = this.onRecipeEdit.bind(this);
        
        //RECIPE FORM BUTTON
        $('button_recipeform').onclick = this.form_open_click.bind(this);

        //RECIPES BOX PANEL
        if(!Kookiiz.panels.is_disabled('recipes'))
        {
            this.$boxSorting.observe('change', this.box_change.bind(this));
            var recipe_tabs = $('recipe_box_types').select('.recipe_tab');
            recipe_tabs.invoke('observe', 'click', this.box_tab_click.bind(this));
            recipe_tabs.invoke('observe', 'mouseout', this.box_tab_out.bind(this));
            recipe_tabs.invoke('observe', 'mouseover', this.box_tab_over.bind(this));
            this.$boxCriteria.select('img.icon15').invoke('observe', 'click', this.box_criteria_click.bind(this));

            //Droppable area
            if(user_logged())
            {
                //Make recipe box droppable for recipes
                Droppables.add('recipe_box_drop',
                {
                    'accept':       ['recipe_box','recipe_item'],
                    'hoverclass':   'hover',
                    'onDrop':       this.box_drop.bind(this)
                });
            }
        }

        //SEARCH
        //Build season selector
        this.search_season_build();

        //Observers
        this.$searchButton.observe('click', this.onSearchChange.bind(this));
        this.$searchCategory.observe('change', this.onSearchChange.bind(this));
        this.$searchOrigin.observe('change', this.onSearchChange.bind(this));
        this.$searchReset.observe('click', this.onSearchReset.bind(this));
        this.$searchToggle.observe('click', this.search_expand.bind(this));
        $$('input.check_search').invoke('observe', 'click', this.onSearchChange.bind(this));
        $$('select.select_search').invoke('observe', 'change', this.onSearchSelect.bind(this));
        Utilities.observe_return(this.$searchInput, this.onSearchChange.bind(this));

        //Disable some search options for visitors
        if(!user_logged())
        {
            //this.search_option_disable('allergy');
            this.search_option_disable('disliked');
            this.search_option_disable('liked');
        }

        //Reset search criteria
        this.search_reset();

        //THEMES
        //this.$themesTab.observe('click', this.themes_tab_click.bind(this));
        //this.$themesTab.observe('mouseover', this.themes_tab_over.bind(this));
        //this.$themesTab.observe('mouseout', this.themes_tab_out.bind(this));
        //$('recipes_themes_refresh').observe('click', this.themes_shuffle_click.bind(this));
        //Shuffle themes
        this.themes_shuffle();
    },
    
    /*******************************************************
    RECIPES BOX
    ********************************************************/

   //Called when the content of the recipe box changes
    //-> (void)
    box_change: function()
    {
        this.box_update();
    },

    //Called when a criteria icon is clicked
    //#event (event): DOM event object
    //-> (void)
    box_criteria_click: function(event)
    {
        var criteria = event.findElement();
        var disabled = criteria.hasClassName('disabled');
        if(disabled)
            criteria.removeClassName('disabled');
        else
            criteria.addClassName('disabled');
        this.box_update();
    },

   //Reset all criteria
    //-> (void)
    box_criteria_reset: function()
    {
        this.$boxCriteria.select('.criteria').each(function(criteria)
        {
            if(!criteria.hasClassName('disabled'))
                criteria.addClassName('disabled');
        });
    },


    //Called when a recipe is dropped on the recipe box
    //#recipe (DOM):    recipe box DOM element
    //#drop_area (DOM): DOM element inside which the recipe was dropped
    //#mouse_x (int):   horizontal mouse position at time of drop
    //#mouse_y (int):   vertical mouse position at time of drop
    //-> (void)
    box_drop: function(recipe, drop_area, mouse_x, mouse_y)
    {
        //Add corresponding recipe to favorites
        var recipe_id = parseInt(recipe.id.split('_')[2]);
        User.favorites_add(recipe_id, true);
    },

    //Hide recipe box drop area (for favorites)
    //-> (void)
    box_drop_hide: function()
    {
        this.$boxDrop.hide();
        this.$boxContent.show();
    },

    //Display recipe box drop area
    //-> (void)
    box_drop_show: function()
    {
        this.$boxContent.hide();
        this.$boxDrop.show();
    },

    //Callback for box recipe element action
    //#recipe_id (int): unique recipe ID
    //#action (string): type of action
    //-> (void)
    box_element_action: function(recipe_id, action)
    {
        switch(action)
        {
            case 'delete':
                User.favorites_delete(recipe_id);
                break;
        }
    },

    //Called when a recipe box tab is clicked
    //#event (event): DOM event object
    //-> (void)
    box_tab_click: function(event)
    {
        //Set recipe box type
        var tab  = event.findElement('.recipe_tab');
        var type = tab.id.split('_')[2];
        this.box_criteria_reset();
        this.box_type_set(type);
    },

    //Called when the mouse leaves a recipe tab
    //-> (void)
    box_tab_out: function()
    {
        //Change panel header to display current type
        var type      = this.box_type_get();
        var type_name = PANEL_RECIPES_TEXT[this.BOX_TYPES.indexOf(type)];
        Kookiiz.panels.header_set('recipes', type_name);
    },

    //Called when the mouse enters a recipe tab
    //#event (event): DOM event object
    //-> (void)
    box_tab_over: function(event)
    {
        //Change panel header to display hovered type
        var tab       = event.findElement('.recipe_tab');
        var type      = tab.id.split('_')[2];
        var type_name = PANEL_RECIPES_TEXT[this.BOX_TYPES.indexOf(type)];
        Kookiiz.panels.header_set('recipes', type_name);
    },

    //Return current type of the recipe box content
    //->type (string): type identifier
    box_type_get: function()
    {
        return this.box_type;
    },

    //Set current recipe box content type
    //#type (string): type identifier
    //-> (void)
    box_type_set: function(type)
    {
        this.box_type_old = this.box_type;
        this.box_type     = type;

        //Set panel header
        var type_name = PANEL_RECIPES_TEXT[this.BOX_TYPES.indexOf(type)];
        Kookiiz.panels.header_set('recipes', type_name);

        //Set tab as "selected"
        var tabs = $('recipe_box_types').select('.recipe_tab');
        for(var i = 0, imax = tabs.length; i < imax; i++)
        {
            var tab_type = tabs[i].id.split('_')[2];
            if(tab_type == type)
                tabs[i].addClassName('selected');
            else
                tabs[i].removeClassName('selected');
        }

        //Update recipe box
        this.box_update();
    },

    //Update recipes box panel
    //The box is not updated if type is not currently displayed
    //If no type is specified the box is updated anyway
    //#type (string): type of recipe content to update (optional)
    //-> (void)
    box_update: function(type)
    {
        var current_type = this.box_type_get();
        if(type && type != current_type) return;    //No update required
        if(type) this.box_criteria_reset();         //Reset criteria because box content is changing

        //Retrieve appropriate recipes for current type
        var recipes;
        switch(current_type)
        {
            case 'favorite':
                recipes = User.favorites_get();
                break;
            case 'menu':
                recipes = User.menu.recipes_get();
                break;
            case 'searched':
                recipes = this.search_results;
                break;
            case 'viewed':
                recipes = this.viewed;
                break;
        }
        //Remove duplicates
        recipes = recipes.uniq();

        //Sort according to checked criteria
        var criteria        = this.search_criteria(true),
            criteria_icons  = this.$boxCriteria.select('.criteria'),
            criteria_icon   = null;
        for(var i = 0, imax = criteria_icons.length; i < imax; i++)
        {
            criteria_icon = criteria_icons[i];
            if(!criteria_icon.hasClassName('disabled'))
            {
                for(var j = 0, jmax = RECIPES_CRITERIA.length; j < jmax; j++)
                {
                    if(criteria_icon.hasClassName(RECIPES_CRITERIA[j]))
                        criteria[RECIPES_CRITERIA[j]] = 1;
                }
            }
        }
        var recipe;
        for(i = 0, imax = recipes.length; i < imax; i++)
        {
            recipe = Recipes.get(recipes[i]);
            if(!recipe || !recipe.match_criteria(criteria))
            {
                recipes.splice(i, 1);
                i--;imax--;
            }
        }

        //Display recipes
        if(recipes.length)
        {
            this.elements_build(this.$boxContent, recipes, current_type,
            {
                'preview':      true,
                'sorting':      this.$boxSorting.value,
                'deletable':    current_type == 'favorite',
                'callback':     current_type == 'favorite' ? this.box_element_action.bind(this) : false,
                'deleteText':   current_type == 'favorite' ? ACTIONS_LONG[4] : ''
            });
        }
        else
        {
            //Clear box content
            this.dragRemove(this.box_type_old);
            this.$boxContent.clean();
            //Display notification
            var empty_text = new Element('p', {'class': 'noresult center'});
            empty_text.innerHTML = RECIPES_ALERTS[0];
            this.$boxContent.appendChild(empty_text);
        }
    },

    /*******************************************************
    DISPLAY
    ********************************************************/

    //Display recipe with provided ID in a dedicated tab
    //#recipe_id (int): recipe unique ID
    //-> (void)
    display: function(recipe_id)
    {
        //Set current display ID
        this.displayed_set(recipe_id);

        //Check if recipe is available client-side
        var recipe = Recipes.fetch(recipe_id, this.display_callback.bind(this, recipe_id));
        if(!recipe)
        {
            Kookiiz.tabs.loading();
            return;
        }
        
        //Ensure recipe title is displayed in URL
        if(Kookiiz.tabs.tempTitleGet() != recipe.name)
            Kookiiz.tabs.show('recipe_full', recipe.id, recipe.name);

        //SET UP PANELS
        //Throw search for related glossary keywords
        Kookiiz.glossary.search_recipe(recipe.id);
        //Display partner or user panel
        if(recipe.partner > 0)
        {
            Kookiiz.panels.hide('user');
            Kookiiz.partners.display(recipe.partner);
            Kookiiz.panels.show('partner', true);
        }
        else
        {
            Kookiiz.panels.hide('partner');
            Kookiiz.users.preview(recipe.author_id);
            Kookiiz.panels.show('user', true);
        }
        //Update nutrition panel
        Kookiiz.health.nutritionUpdate();

        //RETRIEVE RECIPE FIELDS
        var title       = this.$recipeDisplay.select('.display.title')[0],
            rating      = this.$recipeDisplay.select('.display.rating')[0],
            picture     = this.$recipeDisplay.select('.display.picture')[0],
            criteria    = this.$recipeDisplay.select('.display.criteria')[0],
            price       = this.$recipeDisplay.select('.display.price')[0],
            author      = this.$recipeDisplay.select('.display.author')[0],
            category    = this.$recipeDisplay.select('.display.category')[0],
            origin      = this.$recipeDisplay.select('.display.origin')[0],
            guests      = this.$recipeDisplay.select('.display.guests')[0],
            preparation = this.$recipeDisplay.select('.display.preparation')[0],
            cooking     = this.$recipeDisplay.select('.display.cooking')[0],
            level       = this.$recipeDisplay.select('.display.level')[0],
            description = this.$recipeDisplay.select('.display.description')[0],
            ingredients = this.$recipeDisplay.select('.display.ingredients')[0],
            share       = $('recipe_display_actions').select('.share')[0];

        //RETRIEVE OPTIONS
        var currency_id     = User.option_get('currency'),
            currency_value  = CURRENCIES_VALUES[currency_id],
            currency        = CURRENCIES[currency_id];

        //DISPLAY RECIPE PICTURE
        picture.stopObserving('click');
        if(recipe.pic_id)
        {
            picture.removeClassName('click');
            picture.select('.caption')[0].hide();
            picture.setStyle({'backgroundImage': 'url("/pics/recipes-' + recipe.pic_id + '")'});
        }
        else
        {
            //Allow user to click recipe and upload a picture
            picture.addClassName('click');
            picture.select('.caption')[0].show();
            picture.setStyle({'backgroundImage': ''});
            picture.observe('click', this.onPictureUpload.bind(this));
        }

        //SHARING
        var like_url = 'http://www.kookiiz.com/' + URL_HASH_TABS[4] + '-' + recipe.id + '-' + Utilities.text2link(recipe.name),
            like_button = '<fb:like href="' + like_url + '" layout="button_count" font="tahoma" send="true"></fb:like>';
        share.innerHTML = like_button;
        FB.XFBML.parse(share);

        //DISPLAY RECIPE PROPERTIES
        title.innerHTML       = recipe.name;
        price.innerHTML       = Math.round(currency_value * recipe.price) + currency;
        author.innerHTML      = recipe.author_name;
        category.innerHTML    = RECIPES_CATEGORIES[recipe.category];
        origin.innerHTML      = RECIPES_ORIGINS[recipe.origin];
        guests.innerHTML      = recipe.guests + ' ' + RECIPE_DISPLAY_TEXT[18];
        preparation.innerHTML = recipe.preparation ? recipe.preparation + ' ' + VARIOUS[6] : '-';
        cooking.innerHTML     = recipe.cooking ? recipe.cooking + ' ' + VARIOUS[6] : '-';
        level.innerHTML       = RECIPES_LEVELS[recipe.level];
        description.innerHTML = recipe.description.linefeed_replace();
        this.display_icons(criteria, recipe, 'medium');
        this.display_rating(rating, recipe, (user_logged() !== 0), false);
        this.display_ingredients(ingredients, recipe.ingredients);

        //COMMENTS
        Kookiiz.comments.reset('recipe');
        
        //ACTIONS
        if(user_isadmin() || User.getID() == recipe.author_id)
            $('recipe_actions').show();
        else
            $('recipe_actions').hide();

        //HIDE LOADER
        Kookiiz.tabs.loaded();
    },
    
    //Called if recipe display should be updated
    //-> (void)
    displayUpdate: function()
    {
        if(Kookiiz.tabs.current_get() == 'recipe_display')
        {
            var recipe_id = this.displayed_get();
            if(recipe_id) 
                this.display(recipe_id);
        }
    },

    //Called when user clicks on one the display actions
    //#event (object): DOM click event
    //-> (void)
    display_action_click: function(event)
    {
        //Retrieve current recipe ID
        var recipe_id = this.displayed_get();
        if(!recipe_id) return;

        //Check which action was clicked
        var action_item = event.findElement('li');
        if(action_item.hasClassName('print'))
        {
            if(recipe_id > 0)
                window.open('/print/recipe-' + recipe_id);
        }
        else if(action_item.hasClassName('report'))
        {
            var self = this;
            Kookiiz.popup.confirm(
            {
                'text':     RECIPES_ALERTS[2],
                'callback': function(confirm)
                            {
                                if(confirm)
                                    self.report(recipe_id);
                            }
            });
        }
        else if(action_item.hasClassName('save'))
        {
            if(user_logged())
                User.favorites_add(recipe_id);
            else
                Kookiiz.popup.alert({'text': RECIPES_ALERTS[6]});
        }
        else if(action_item.hasClassName('translate'))
            Kookiiz.tabs.show('recipe_translate', recipe_id, Recipes.get(recipe_id, 'name') || '');
    },

    //Called when recipe content was downloaded from server
    //#recipe_id (int): ID of the recipe for which content was loaded
    //-> (void)
    display_callback: function(recipe_id)
    {
        if(Kookiiz.tabs.current_get() == 'recipe_full')
        {
            //Check if display is still on the same recipe
            if(this.displayed_get() == recipe_id)
            {
                //Display recipe if it's now available
                if(Recipes.exist(recipe_id)) 
                    this.display(recipe_id);
                //Else display error tab
                else
                {
                    Kookiiz.tabs.error_404();
                    this.displayed_set(0);
                }
            }
        }
        else 
            this.displayed_set(0);
    },

    //Function to display the recipe icons
    //#container (DOM/string):  container inside which to display the icons
    //#recipe (object):         recipe object
    //#size (string):           either "big", "medium" or "small" (defaults to medium)
    //-> (void)
    display_icons: function(container, recipe, size)
    {
        size = size || 'medium';

        //Set-up container
        container = $(container).clean();

        var icon_size;
        switch(size)
        {
            case 'big':
                icon_size = 'icon25';
                break;
            case 'medium':
                icon_size = 'icon15';
                break;
            case 'small':
                icon_size = 'icon10';
                break;
        }
        var icon, enabled = false;

        //EASY
        enabled = recipe.iseasy();
        icon = new Element('img',
        {
            'class':    icon_size + ' easy' + (enabled ? '' : ' disabled'),
            'src':      ICON_URL,
            'title':    RECIPES_CRITERIA_NAMES[0] + ' (' + (enabled ? VARIOUS[13] : VARIOUS[14]) + ')'
        });
        container.appendChild(icon);

        //HEALTHY
        enabled = recipe.ishealthy();
        icon = new Element('img',
        {
            'class':    icon_size + ' healthy' + (enabled ? '' : ' disabled'),
            'src':      ICON_URL,
            'title':    RECIPES_CRITERIA_NAMES[1] + ' (' + (enabled ? VARIOUS[13] : VARIOUS[14]) + ')'
        });
        container.appendChild(icon);

        //CHEAP
        enabled = recipe.ischeap();
        icon = new Element('img',
        {
            'class':    icon_size + ' cheap' + (enabled ? '' : ' disabled'),
            'src':      ICON_URL,
            'title':    RECIPES_CRITERIA_NAMES[2] + ' (' + (enabled ? VARIOUS[13] : VARIOUS[14]) + ')'
        });
        container.appendChild(icon);

        //QUICK
        enabled = recipe.isquick();
        icon = new Element('img',
        {
            'class':    icon_size + ' quick' + (enabled ? '' : ' disabled'),
            'src':      ICON_URL,
            'title':    RECIPES_CRITERIA_NAMES[3] + ' (' + (enabled ? VARIOUS[13] : VARIOUS[14]) + ')'
        });
        container.appendChild(icon);

        //SUCCESS
        enabled = recipe.issuccess();
        icon = new Element('img',
        {
            'class':    icon_size + ' success' + (enabled ? '' : ' disabled'),
            'src':      ICON_URL,
            'title':    RECIPES_CRITERIA_NAMES[4] + ' (' + (enabled ? VARIOUS[13] : VARIOUS[14]) + ')'
        });
        container.appendChild(icon);

        //VEGGIE
        enabled = recipe.isseason();
        icon = new Element('img',
        {
            'class':    icon_size + ' season' + (enabled ? '' : ' disabled'),
            'src':      ICON_URL,
            'title':    RECIPES_CRITERIA_NAMES[5] + ' (' + (enabled ? VARIOUS[13] : VARIOUS[14]) + ')'
        });
        container.appendChild(icon);

        //VEGGIE
        enabled = recipe.isveggie();
        icon = new Element('img',
        {
            'class':    icon_size + ' veggie' + (enabled ? '' : ' disabled'),
            'src':      ICON_URL,
            'title':    RECIPES_CRITERIA_NAMES[6] + ' (' + (enabled ? VARIOUS[13] : VARIOUS[14]) + ')'
        });
        container.appendChild(icon);
    },

    //Build ingredients table from provided ingredients array and append it to container
    //#container (DOM/string):  container element (or its ID)
    //#ingredients (array):     ingredient collection
    //#limit (int):             limit number of ingredients to value (defaults to none)
    //-> (void)
    display_ingredients: function(container, ingredients, limit)
    {
        //Set-up container
        container = $(container).clean();

        //Display ingredients table
        var list = ingredients.build(
        {
            'quantified': true,
            'limit':      limit,
            'text_max':   this.ING_NAME_MAX,
            'units':      UNITS_SYSTEMS[User.option_get('units')]
        });
        //Check content and append
        if(list.empty())
            container.innerHTML = INGREDIENTS_ALERTS[2];
        else                
            container.appendChild(list);
    },

    //Function to display the rating of a recipe
    //#container (DOM/string):  container element inside which to display the rating
    //#recipe (object):         recipe object
    //#clickable (bool):        defines if the rating can be clicked to vote or is just static (defaults to true)
    //#big (bool):              specifies whether to display a big rating (defaults to false)
    //-> (void)
    display_rating: function(container, recipe, clickable, big)
    {
        var rating = recipe.rating;
        if(typeof(clickable) == 'undefined') clickable = true;
        if(typeof(big) == 'undefined')       big = false;

        //Set-up container
        container = $(container).clean();

        //Build stars rating
        if(clickable)
        {
            var star_icon = null, star_count = Math.round(rating),
                star_class = (big ? 'icon25' : 'icon15') + ' star click'
            for(var i = 1; i <= RECIPE_RATING_MAX; i++)
            {
                star_icon = new Element('img',
                {
                    'alt':      KEYWORDS[10],
                    'class':    star_class,
                    'src':      ICON_URL
                });
                if(i > star_count) 
                    star_icon.addClassName('empty');
                
                star_icon.title = RECIPE_DISPLAY_TEXT[19] + ' ' + i + ' ' + RECIPE_DISPLAY_TEXT[20];
                star_icon.observe('click', this.rating_click.bind(this, i));
                star_icon.observe('mouseover', this.rating_highlight.bind(this, i - 1));
                star_icon.observe('mouseout', this.rating_unhighlight.bind(this));
                container.appendChild(star_icon);
            }
        }
        else
        {
            var rating_img = new Element('img',
            {
                'alt':      rating + ' ' + KEYWORDS[10],
                'class':    (big ? 'icon25_rating' : 'icon15_rating') + ' rate' + rating,
                'src':      ICON_URL
            });
            container.appendChild(rating_img);
        }
    },

    //Get currently displayed recipe
    //->recipe_id (int): ID of the recipe
    displayed_get: function()
    {
        return this.recipe_displayed;
    },

    //Set currently displayed recipe
    //#recipe_id (int): ID of the recipe
    //-> (void)
    displayed_set: function(recipe_id)
    {
        this.recipe_displayed = recipe_id;
        this.viewed_add(recipe_id);
    },

    /*******************************************************
    DRAGGABLES
    ********************************************************/

    //Create a new draggable element
    //#type (string):   recipe type
    //#id (int):        recipe ID
    //#element (DOM):   recipe DOM element
    //-> (void)
    dragCreate: function(type, id, element)
    {
        var drag = new Draggable(element,
        {
            'handle':               'handle',
            'revert':               true,
            'onStart':              this.element_drag_start.bind(this),
            'onEnd':                this.element_drag_stop.bind(this),
            'reverteffect':         function(){return 0;},
            'endeffect':            function(){return 0;},
            'ghosting':             true,
            'scroll':               window,
            'scrollSensitivity':    50
        });
        var list = this.drags.get(type) || this.drags.set(type, $H());
        list.set(id, drag);
    },

    //Remove draggable(s)
    //#type (string):   recipe type
    //#id (int):        recipe ID (optional, defaults to all)
    //-> (void)
    dragRemove: function(type, id)
    {
        var list = this.drags.get(type);
        if(!list) return;
        if(id)
        {
            var drag = list.unset(type + '_' + id);
            if(drag) drag.destroy();
        }
        else
        {
            list.each(function(el)
            {
                el.value.destroy();
            });
            this.drags.set(type, $H());
        }
    },

    /*******************************************************
    ELEMENTS
    ********************************************************/

    //Build recipe box element
    //#recipe (object):     recipe object
    //#type (string):       type of recipe box
    //#options (object):    display options
    //-> (void)
    element_build_box: function(recipe, type, options)
    {
        var id = recipe.id, name = recipe.name.truncate(this.RECIPE_NAME_BOXMAX);

        //Build box
        var recipe_box = new Element('div',
            {
                'class':    'recipe_box ' + type,
                'id':       'recipebox_' + type + '_' + id
            }),
            handle  = new Element('div', {'class': 'handle'}),
            content = new Element('div', {'class': 'content'}),
            footer  = new Element('div', {'class': 'footer'}),
            picture = new Element('div', {'class': 'picture center'}),
            text    = new Element('div', {'class': 'name small'}),
            icons   = new Element('div', {'class': 'icons'});
        recipe_box.appendChild(handle);
        recipe_box.appendChild(content);
        recipe_box.appendChild(footer);
        content.appendChild(picture);
        content.appendChild(text);
        content.appendChild(icons);

        //Text
        picture.appendChild(new Element('img',
        {
            'alt':  name,
            'src':  '/pics/recipes-' + recipe.pic_id + '-tb'
        }));
        text.innerHTML = name;
        text.observe('click', this.element_click.bind(this));
        //Icons
        this.display_icons(icons, recipe, 'small');
        
        //Preview on mouseover
        if(options.preview)
        {
            content.observe('mouseenter', this.element_over.bind(this));
            content.observe('mouseleave', this.element_out.bind(this));
        }

        //Deletion button
        if(options.deletable && options.callback)
        {
            var delete_icon = new Element('img',
            {
                'alt':      ACTIONS[23],
                'class':    'button15 cancel',
                'src':      ICON_URL,
                'title':    options.deleteText
            });
            delete_icon.observe('click', this.element_delete_click.bind(this, id, options.callback));
            content.appendChild(delete_icon);
        }     

        //Return box element
        return recipe_box;
    },

    //Build recipe item for a list (e.g. on the recipes panel)
    //#id (int):            recipe unique ID
    //#name (string):       recipe name
    //#type (string):       type of recipe item
    //#options (object):    display options
    //-> (void)
    element_build_item: function(id, name, type, options)
    {
        name = name.truncate(this.RECIPE_NAME_LISTMAX);

        //Item
        var recipe_item = new Element('li',
            {
                'class':    'recipe_item ' + type,
                'id':       'recipeitem_' + type + '_' + id
            }),
            top     = new Element('div', {'class': 'top'}),
            middle  = new Element('div', {'class': 'middle'}),
            bottom  = new Element('div', {'class': 'bottom'}),
            handle  = new Element('div', {'class': 'handle'}),
            text    = new Element('div', {'class': 'text'});
        recipe_item.appendChild(top);
        recipe_item.appendChild(middle);
        recipe_item.appendChild(bottom);
        middle.appendChild(handle);
        middle.appendChild(text);
        
        //Text
        text.innerHTML = name;
        if(options.clickable) 
            text.observe('click', this.element_click.bind(this));
        
        //Preview on mouseover
        if(options.preview)
        {
            middle.observe('mouseenter', this.element_over.bind(this));
            middle.observe('mouseleave', this.element_out.bind(this));
        }

        //Deletion button
        if(options.deletable && options.callback)
        {
            var delete_icon = new Element('img',
            {
                'alt':      ACTIONS[23],
                'class':    'button15 cancel',
                'src':      ICON_URL,
                'title':    options.deleteText
            });
            delete_icon.observe('click', this.element_delete_click.bind(this, id, options.callback));
            middle.appendChild(delete_icon);
        }

        //Return item element
        return recipe_item;
    },

    //Called when a recipe box is clicked
    //#event (event): DOM event object
    //-> (void)
    element_click: function(event)
    {
        //Retrieve recipe parameters
        var recipe_box  = event.findElement('.recipe_box, .recipe_item'),
            recipe_id   = parseInt(recipe_box.id.split('_')[2]);

        //Hide preview and display recipe in full tab
        this.preview_hide();
        Kookiiz.tabs.show('recipe_full', recipe_id, Recipes.get(recipe_id, 'name') || '');
    },

    //Generic callback for recipe element delete button
    //#id (int):            recipe unique ID
    //#callback (function): function to call upon element deletion
    //-> (void)
    element_delete_click: function(id, callback)
    {
        this.preview_hide();
        callback(id, 'delete');
    },

    //Called when recipe drag and effects are over
    //#draggable (object): scriptaculous draggable object
    //-> (void)
    element_drag_finish: function(draggable)
    {
        //Fix Scriptaculous bugs
        draggable_fix_stop(draggable);

        //Remove drag-specific class
        draggable.element.removeClassName('drag');

        //Check if recipe is dragged from the recipes box and the box content type changed
        var recipe_type = draggable.element.id.split('_')[1];
        if(this.BOX_TYPES.indexOf(recipe_type) >= 0
            && recipe_type != this.box_type_get())
        {
            //Then remove recipe box from the document
            if($(draggable.element)) 
                draggable.element.remove();
        }

        //Enable recipe preview
        this.preview_on();

        //Hide droppables areas
        Kookiiz.menu.drop_hide();
        Kookiiz.menu.moveDown();
        this.box_drop_hide();
    },

    //Called when recipe drag starts
    //Correct buggy behavior of scriptaculous and prepare for drag
    //#draggable (object): scriptaculous draggable object
    //-> (void)
    element_drag_start: function(draggable)
    {
        //Fix Scriptaculous bugs
        draggable_fix_start(draggable);

        //Add drag-specific class
        draggable.element.addClassName('drag');

        //Turn off recipe preview and hint
        this.preview_off();
        this.search_hint_stop();

        //Display droppable areas
        Kookiiz.menu.today();
        Kookiiz.menu.moveUp();
        Kookiiz.menu.drop_show('recipe');
        if(draggable.element.hasClassName('recipe_box')) 
            this.box_drop_show();
    },

    //Called right after recipe drag has ended
    //#draggable (object): scriptaculous draggable object
    //-> (void)
    element_drag_stop: function(draggable)
    {
        var delta = draggable.currentDelta();
        if(draggable.options.ghosting) 
            this.element_drag_finish(draggable);
        else
        {
            new Effect.Fade(draggable.element, 
            {
                'duration':     0.2, 
                'queue':        {'position': 'end', 'scope': draggable.element.id}, 
                'afterFinish':  this.element_drag_finish.bind(this, draggable)
            });
            new Effect.Move(draggable.element, 
            {
                'x':        draggable.delta[0] - delta[0],
                'y':        draggable.delta[1] - delta[1], 
                'duration': 0.1, 
                'queue':    {'position': 'end', 'scope': draggable.element.id}
            });
            new Effect.Appear(draggable.element, 
            {
                'duration': 0, 
                'queue':    {'position': 'end', 'scope': draggable.element.id}
            });
        }
    },

    //Called when the mouse leaves a recipe object
    //-> (void)
    element_out: function()
    {
        //Hide recipe preview
        this.preview_hide();
    },

    //Called when the mouse is hovering the name of a recipe box
    //-> (void)
    element_over: function(event)
    {
        var recipe_box = event.findElement('.recipe_box, .recipe_item');
        this.preview(recipe_box);
    },

    //Build DOM elements from recipes
    //#container (DOM or string):   container element (or its ID)
    //#recipes (array):             list of recipe IDs
    //#type (string):               specifies the kind of recipes to display ("search", "favorite", etc.)
    //#options (object):            display options
    //-> (void)
    elements_build: function(container, recipes, type, options)
    {
        container = $(container);

        //Abort if there are no recipes
        if(!recipes.length)
        {
            this.dragRemove(type);
            container.clean();
            return;
        }

        //Options
        options = Object.extend(
        {
            'mode':         'list',     //specifies whether to display recipes as "box" or "list"
            'sorting':      false,      //specifies if and how to sort recipes ("abc", "rating", "price", "score")
            'clickable':    true,       //specifies if recipe items should be made clickable
            'draggable':    true,       //specifies if recipe items should be made draggable
            'preview':      false,      //specifies if a preview of the recipes should be displayed on hover
            'deletable':    false,      //specifies if recipe items should be made deletable
            'callback':     false,      //callback for recipe element actions
            'deleteText':   ACTIONS[23] //Text to display on deletion icon
        }, options || {});

        //Empty container for list mode
        if(options.mode == 'list')
        {
            this.dragRemove(type);
            container.clean();
        }
        //Remove recipes of current type that are not amongst results anymore (in box mode)
        else if(options.mode == 'box')
        {
            var existing_recipes = container.select('.recipe_box.' + type);
            if(existing_recipes.length)
            {
                var recipe, recipe_id;
                for(var i = 0, imax = existing_recipes.length; i < imax; i++)
                {
                    recipe      = existing_recipes[i];
                    recipe_id   = parseInt(recipe.id.split('_')[2]);
                    if(recipes.indexOf(recipe_id) < 0)
                    {
                        this.dragRemove(type, recipe_id);
                        recipe.remove();
                    }
                }
            }
            else
            {
                this.dragRemove(type);
                container.clean();
            }
        }

        //Sort recipes
        if(options.sorting)
            recipes = this.sort(recipes, options.sorting);

        //Loop through sorted recipes
        var recipes_list = new Element('ul'),
            recipe_key, recipe_box,
            previous_key, previous_box;
        for(i = 0, imax = recipes.length; i < imax; i++)
        {
            recipe_id   = recipes[i];
            recipe      = Recipes.get(recipe_id);
            if(recipe)
            {
                if(options.mode == 'box')
                {
                    recipe_key   = 'recipebox_' + type + '_' + recipe_id;
                    previous_key = 'recipebox_' + type + '_' + recipes[i - 1];
                    recipe_box   = $(recipe_key) || this.element_build_box(recipe, type, options);
                    previous_box = $(previous_key);

                    if(previous_box)
                        container.insertBefore(recipe_box, previous_box.nextSibling);
                    else
                        container.insertBefore(recipe_box, container.firstChild);
                }
                else
                {
                    recipe_box = this.element_build_item(recipe.id, recipe.name, type, options);
                    recipes_list.appendChild(recipe_box);
                }

                //Create draggable
                if(options.draggable)
                    this.dragCreate(type, recipe_id, recipe_box);
            }
            else continue;
        }

        //Append recipes list to container if it's not empty
        if(options.mode == 'list' && !recipes_list.empty())
            container.appendChild(recipes_list);
    },

    /*******************************************************
    FORM
    ********************************************************/

    //Callback for click on recipe form button
    //-> (void)
    form_open_click: function()
    {
        Kookiiz.recipeform.open('create');
    },

    /*******************************************************
    INDEX
    ********************************************************/

    //Build a clickable index of recipes
    //#type (string): recipe box type
    //-> (void)
    index_build: function()
    {
        this.$searchIndex.clean();

        //Create index in reverse order (because index items are floated right)
        var recipes_count = this.$searchResults.childElements().length, index_element;
        for(var i = Math.ceil(recipes_count / this.SEARCH_PERPAGE) - 1, imin = 0; i >= imin; i--)
        {
            index_element = new Element('div',
            {
                'class': 'recipes_index' + (i ? '' : ' selected') + ' bold'
            });
            if(i)
                index_element.observe('click', this.index_switch.bind(this, i));

            index_element.innerHTML = (i + 1) + '\t';
            this.$searchIndex.appendChild(index_element);
        }

        //Show first page of recipes
        this.index_show(0);
    },

    //Get current index
    //->index (int): currently selected index
    index_get: function()
    {
        var indexes = this.$searchIndex.childElements();
        for(var i = 0, imax = indexes.length; i < imax; i++)
        {
            if(indexes[i].hasClassName('selected'))
                return imax - 1 - i;
        }
        return -1;
    },

    //Display recipes corresponding to a given page index
    //#page (int): page index
    //-> (void)
    index_show: function(page)
    {
        //Loop through recipes list
        var recipes = this.$searchResults.childElements(), recipe;
        for(var i = 0, imax = recipes.length; i < imax; i++)
        {
            recipe = recipes[i];

            //Hide/show recipes depending on their position in the list
            if(i < page * this.SEARCH_PERPAGE 
                || i >= (page + 1) * this.SEARCH_PERPAGE)
                recipe.hide();
            else
                recipe.show();
        }
    },

    //Switch between recipes when user clicks on index
    //#page (int):      page index
    //#event (event):   DOM click event
    //-> (void)
    index_switch: function(page, event)
    {
        var index_selected = event.findElement('.recipes_index');

        //Change selected index
        var index_items = this.$searchIndex.childElements(), element;
        for(var i = 0, imax = index_items.length; i < imax; i++)
        {
            element = index_items[i];
            element.removeClassName('selected');
            element.stopObserving('click').observe('click', this.index_switch.bind(this, imax - i - 1));
        }
        index_selected.addClassName('selected');
        index_selected.stopObserving('click');

        //Hide/Show recipes
        this.index_show(page);
    },

    /*******************************************************
    OBSERVERS
    ********************************************************/

    //Called when recipe picture upload button is clicked
    //-> (void)
    onPictureUpload: function()
    {
        if(user_logged())
        {
            var recipe_id = this.displayed_get();
            Kookiiz.pictures.upload('recipes', this.onPictureUploaded.bind(this, recipe_id));
        }
    },

    //Callback for successfull picture upload process
    //#recipe_id (int): ID of the recipe for which a picture was uploaded
    //#pic_id (int):    ID of the uploaded picture
    //-> (void)
    onPictureUploaded: function(recipe_id, pic_id)
    {
        this.pictureChange(recipe_id, pic_id);
        if(recipe_id == this.displayed_get())
            this.display(recipe_id);
    },
    
    //Called when recipe edit button is clicked
    //-> (void)
    onRecipeEdit: function()
    {
        if(user_logged())
            Kookiiz.recipeform.open('edit', this.displayed_get());
    },

    //Called when criteria change or search icon is clicked
    //-> (void)
    onSearchChange: function()
    {
        this.search();			//Throw a new recipe search
        this.search_update();	//Immediately update existing results
    },

    //Called when the recipe search reset link is clicked
    //-> (void)
    onSearchReset: function()
    {
        this.search_reset();
        this.search_retract();
        this.themes_shuffle();
    },

    //Called when one of the criteria select menus changes
    //-> (void)
    onSearchSelect: function(event)
    {
        var search_item = event.findElement('li');
        search_item.select('input')[0].checked = true;
        this.onSearchChange();
    },
    
    /*******************************************************
    PICTURE CHANGE
    ********************************************************/

    //Change picture of a given recipe
    //#recipe_id (int): unique recipe ID
    //#pic_id (int):    ID of the new picture
    //-> (void)
	pictureChange: function(recipe_id, pic_id)
    {
        //Send request to change picture
        Kookiiz.api.call('recipes', 'save_pic',
        {
            'callback': function(response)
                        {
                            var user_grade = response.parameters.user_grade;
                            if(typeof(user_grade) != 'undefined')
                                User.grade_set(user_grade);
                        },
            'request':  'recipe_id=' + recipe_id
                        + '&pic_id=' + pic_id
        });
        //Change picture locally
        var recipe = Recipes.get(recipe_id);
        if(recipe) recipe.pic_id = pic_id;
    },

    /*******************************************************
    PREVIEW
    ********************************************************/

    //Preview recipe on hover
    //#recipe_box (DOM): recipe DOM element
    //-> (void)
    preview: function(recipe_box)
    {
        //Retrieve corresponding recipe object
        var recipe = Recipes.get(parseInt(recipe_box.id.split('_')[2]));
        if(recipe && this.preview_enabled && !Draggables.dragging())
        {
            //Position preview area
            this.preview_setup(recipe_box);

            //Retrieve preview components
            var picture = $('recipe_preview_picture'),
                title   = $('recipe_preview_title'),
                desc    = $('recipe_preview_description'),
                ings    = $('recipe_preview_ingredients'),
                icons   = $('recipe_preview_icons'),
                rating  = $('recipe_preview_rating'),
                prep    = $('recipe_preview_preptime'),
                cook    = $('recipe_preview_cooktime'),
                price   = $('recipe_preview_price');

            //Retrieve currency settings
            var currency_id    = User.option_get('currency'),
                currency_value = CURRENCIES_VALUES[currency_id],
                currency       = CURRENCIES[currency_id];

            //Picture
            if(recipe.pic_id)   
                picture.setStyle({'backgroundImage': 'url("/pics/recipes-' + recipe.pic_id + '")'});
            else                
                picture.setStyle({'backgroundImage': 'url("/themes/' + THEME + '/pictures/recipes/' + RECIPE_PIC_DEFAULT + '")'});
            //Main properties
            title.innerHTML = recipe.name.truncate(this.PREVIEW_TITLE_MAX);
            desc.innerHTML  = recipe.description.linefeed_replace().truncate(this.PREVIEW_DESC_MAX);
            prep.innerHTML  = recipe.preparation ? recipe.preparation + VARIOUS[7] : '-';
            cook.innerHTML  = recipe.cooking ? recipe.cooking + VARIOUS[7] : '-';
            price.innerHTML = Math.round(currency_value * recipe.price) + currency;          
            //Icons & rating
            this.display_icons(icons, recipe, 'medium');
            this.display_rating(rating, recipe, false, false);
            //Ingredients
            this.display_ingredients(ings, recipe.ingredients, this.PREVIEW_ING_MAX);    

            //Display preview
            this.$recipePreview.show();
        }
    },

    //Hide preview area
    //-> (void)
    preview_hide: function()
    {
        this.$recipePreview.hide();
    },

    //Disable recipe preview
    //-> (void)
    preview_off: function()
    {
        this.preview_enabled = false;
        this.preview_hide();
    },

    //Enable recipe preview
    //-> (void)
    preview_on: function()
    {
        this.preview_enabled = true;
    },

    //Position recipe preview area according to provided recipe object
    //#recipe (DOM): recipe DOM element
    //-> (void)
    preview_setup: function(recipe)
    {
        if(this.preview_enabled)
        {
            //Get positioning information
            var viewport_dimensions     = document.viewport.getDimensions();	//Browser viewport dimensions
            var viewport_scroll         = document.viewport.getScrollOffsets();	//Browser viewport offset
            var recipe_position_abs     = recipe.cumulativeOffset();			//Recipe position from absolute top left corner of the website
            var recipe_position_scroll  = recipe.cumulativeScrollOffset();     //Amount by which recipe was scrolled
            var recipe_position_rel     = recipe.viewportOffset();             //Recipe position from top left corner of current view
            var recipe_dimensions       = recipe.getDimensions();				//Recipe box dimensions
            var display_dimensions      = this.$recipePreview.getDimensions();  //Recipe preview dimensions

            //Compute available space around the recipe object
            var recipe_space_top    = recipe_position_rel.top;
            var recipe_space_bottom = viewport_dimensions.height - recipe_space_top - recipe_dimensions.height;
            var recipe_space_left   = recipe_position_rel.left;
            var recipe_space_right  = viewport_dimensions.width - recipe_space_left - recipe_dimensions.width;

            //Check if there is enough space to display the preview from the bottom of the recipe downwards
            //Else, display it from the top of the recipe upwards
            var display_top = 0;
            if(recipe_space_bottom > display_dimensions.height || recipe_space_top < display_dimensions.height)
                display_top = viewport_scroll.top + recipe_position_abs.top - recipe_position_scroll.top + recipe_dimensions.height;
            else
                display_top = viewport_scroll.top + recipe_position_abs.top - recipe_position_scroll.top - display_dimensions.height;

            //Horizontal position depends on column
            var display_left = 0;
            if(recipe.up('#kookiiz_column_left'))
                display_left = viewport_scroll.left + recipe_position_abs.left;
            else if(recipe.up('#kookiiz_column_right'))
                display_left = viewport_scroll.left + recipe_position_abs.left - display_dimensions.width + recipe_dimensions.width;
            else
                display_left = viewport_scroll.left + recipe_position_abs.left - (0.5 * (display_dimensions.width - recipe_dimensions.width))

            //Set computed position
            this.$recipePreview.setStyle({'top': display_top + 'px', 'left': display_left + 'px'});
        }
    },

    /*******************************************************
    RATING
    ********************************************************/

    //Called when user clicks a rating icon
    //Suggest to add a comment to this rating
    //#rating (int): rating value
    //-> (void)
    rating_click: function(rating)
    {
        if(user_logged())
        {
            var recipe_id = this.displayed_get();
            Kookiiz.comments.popup_open(recipe_id, rating);
        }
    },

    //Confirm the recipe has been rated
    //#response (object): server response object
    //-> (void)
    rating_confirm: function(response)
    {
        //Update recipe rating
        var updated_rating = parseInt(response.parameters.updated_rating);
        if(updated_rating >= 0)
        {
            var recipe_id   = parseInt(response.parameters.recipe_id);
            var recipe      = Recipes.get(recipe_id);
            recipe.rating   = updated_rating;

            //Update rating display if required
            var current_id = this.displayed_get();
            if(current_id == recipe_id)
            {
                var container = this.$recipeDisplay.select('.rating')[0];
                this.display_rating(container, recipe, true, false);
            }
        }

        //Tell user that his vote has been taken into account
        Kookiiz.popup.alert({'text': RATING_ALERTS[0]});
    },

    //Highlight the recipe rating when user's cursor is over it
    //#hover_index (int): index of the hovered star
    //-> (void)
    rating_highlight: function(hover_index)
    {
        var container   = this.$recipeDisplay.select('.rating')[0];
        var stars       = container.childElements();

        //Highlight all stars at the left of the one being hovered
        for(var i = 0, imax = stars.length; i < imax; i++)
        {
            if(i <= hover_index)
                stars[i].addClassName('highlight');
            else
                stars[i].removeClassName('highlight');
        }
    },

    //Save a new rating in database
    //#recipe_id (int): recipe unique ID
    //#rating (int):    rating value
    //-> (void)
    rating_save: function(recipe_id, rating)
    {
        //Build a request to update recipe rating in database
        Kookiiz.api.call('recipes', 'rate',
        {
            'callback': this.rating_confirm.bind(this),
            'request':  'recipe_id=' + recipe_id
                        + '&rating=' + rating
        });
    },

    //Display original rating (cancel hovering effect on mouseout)
    //-> (void)
    rating_unhighlight: function()
    {
        var recipe_id = this.displayed_get();
        var container = this.$recipeDisplay.select('.rating')[0];
        this.display_rating(container, Recipes.get(recipe_id), true, false);
    },

    /*******************************************************
    REPORT
    ********************************************************/

    //Report a recipe as "inappropriate"
    //#recipe_id (int): unique recipe ID
    //-> (void)
    report: function(recipe_id)
    {
        Kookiiz.api.call('recipes', 'report',
        {
            'callback': function()
                        {
                            //Display confirmation
                            Kookiiz.popup.alert({'text': RECIPES_ALERTS[3]});
                        },
            'request':  'recipe_id=' + recipe_id
        });
    },

    /*******************************************************
    SEARCH
    ********************************************************/

    //Send a request to search for recipes matching provided criteria
    //#criteria (object): structure containing search criteria values (defaults to current UI settings)
    //-> (void)
    search: function(criteria)
    {
        //Default values
        this.criteria = criteria || this.search_criteria();

        //Display loader
        this.$searchResults.hide();
        this.$searchLoader.show();

        //Store request timestamp locally
        //This is to avoid that a longer and older search erases
        //results from a quicker and more recent one
        this.search_timestamp = Time.get();

        //Make API call
        Kookiiz.api.call('recipes', 'search',
        {
            'callback': this.search_parse.bind(this, this.search_timestamp),
            'request':  'criteria=' + Object.toJSON(this.criteria)
        });
    },

    //Called once recipe content for search results has been loaded from server
    //-> (void)
    search_callback: function()
    {
        //Remove missing recipes from search results
        var recipes_missing = Recipes.check(this.search_results)[1];
        if(recipes_missing.length)
        {
           var recipe_id, recipe_index;
            for(var i = 0, imax = recipes_missing.length; i < imax; i++)
            {
                recipe_id       = recipes_missing[i];
                recipe_index    = this.search_results.indexOf(recipe_id);
                if(recipe_index >= 0)
                    this.search_results.splice(recipe_index, 1);
            }
        }

        //Display search results
        this.search_display();
    },

    //Delete recipe from search results
    //#recipe_id (int): ID of the recipe to remove
    //-> (void)
    search_delete: function(recipe_id)
    {
        //Remove recipe box from results
        var recipe_box = $('recipebox_search_' + recipe_id);
        if(recipe_box) recipe_box.remove();

        //Remove recipe from search results array
        var recipe_index = this.search_results.indexOf(recipe_id);
        this.search_results.splice(recipe_index, 1);

        //Update index
        var index = this.index_get();
        this.index_build();
        this.index_show(index);
    },

    //Display current search results
    //-> (void)
    search_display: function()
    {
        //Hide loader
        this.$searchLoader.hide();
        this.$searchResults.show();

        //Display results and index
        this.elements_build(this.$searchResults, this.search_results, 'search',
        {
            'mode':         'box',
            'preview':      true,
            'sorting':      false, //this.$searchSorting.value,
            'deletable':    true,
            'callback':     this.search_element_action.bind(this),
            'deleteText':   ACTIONS_LONG[3]
        });
        this.index_build('search');

        //Check if recipe area is empty
        var recipes = this.$searchResults.select('.recipe_box');
        if(recipes.length)
        {
            //Display search controls and hint
            this.$searchControls.style.visibility = 'visible';
            this.search_hint.bind(this).delay(1);
        }
        else
            this.search_noresult();

        //Update recipe box search results
        this.box_update('searched');
    },

    //Callback for user action on search DOM element
    //#recipe_id (int): unique recipe ID
    //#action (string): user action
    //-> (void)
    search_element_action: function(recipe_id, action)
    {
        switch(action)
        {
            case 'delete':
                this.search_delete(recipe_id);
                break;
        }
    },

    //Called when the link to open advanced search criteria is clicked
    //-> (void)
    search_expand: function()
    {
        var queue = Effect.Queues.get('search_criteria');
        if(!queue || !queue.size())
        {
            if(!this.$searchCriteria.visible())
            {
                if(User.option_get('fast_mode'))
                    this.$searchCriteria.show();
                else
                {
                    Effect.SlideDown(this.$searchCriteria,
                    {
                        'duration': 0.5,
                        'queue':    {'scope': 'search_criteria'}
                    });
                }
                this.$searchToggle.stopObserving('click').observe('click', this.search_retract.bind(this));
                //this.$searchToggle.clean().appendText(RECIPES_SEARCH_TEXT[1]);
            }
        }
    },

    //Fetch recipe content for provided recipe IDs
    //#recipes_ids (array): list of recipe IDs
    //-> (void)
    search_fetch: function(recipes_ids)
    {
        this.search_results = recipes_ids;
        Recipes.fetch_all(this.search_results, this.search_callback.bind(this));
    },

    //Hide search controls and display "no result" notification
    //-> (void)
    search_noresult: function()
    {
        //Hide search controls
        this.$searchControls.style.visibility = 'hidden';

        //Display "no recipe" caption
        var caption = new Element('div', {'class': 'noresult'}),
            caption_title = new Element('h5', {'class': 'center'}),
            caption_subtitle = new Element('p', {'class': 'center'});
        caption_title.innerHTML = RECIPES_ALERTS[0];
        caption_subtitle.innerHTML = RECIPES_ALERTS[5];
        caption.appendChild(caption_title);
        caption.appendChild(caption_subtitle);
        this.$searchResults.clean().appendChild(caption);
    },

    //Called when recipe search results are returned by the server
    //#timestamp (int):     search request timestamp
    //#response (object):   server response object
    //-> (void)
    search_parse: function(timestamp, response)
    {
        //Check that results are from the last search that was thrown
		if(timestamp < this.search_timestamp) return;

        //Retrieve recipe IDs
        var results = response.content.map(function(recipe_id){return parseInt(recipe_id);});
        this.search_fetch(results);

        //Display notice of result overflow
        if(response.parameters.overflow)
            Kookiiz.popup.alert({'text': RECIPES_ALERTS[1]});
    },

    //Called when the link to close advanced search criteria is clicked
    //-> (void)
    search_retract: function()
    {
        //Hide advanced search criteria
        var queue = Effect.Queues.get('search_criteria');
        if(!queue || !queue.size())
        {
            if(this.$searchCriteria.visible())
            {
                if(User.option_get('fast_mode'))
                    this.$searchCriteria.hide();
                else
                {
                    Effect.SlideUp(this.$searchCriteria,
                    {
                        'duration': 0.5,
                        'queue':    {'scope': 'search_criteria'}
                    });
                }
                this.$searchToggle.stopObserving('click').observe('click', this.search_expand.bind(this));
                //this.$searchToggle.clean().appendText(RECIPES_SEARCH_TEXT[0]);
            }
        }
    },
    
    //Build season selector
    //-> (void)
    search_season_build: function()
    {
        var selector = $('select_search_season'),
            season = Ingredients.getSeason(), option;
        season.sort(function(a, b)
        {
            var name_a = Ingredients.get(a).name,
                name_b = Ingredients.get(b).name;
            return name_a < name_b ? -1 : 1;
        });
        for(var i = 0, imax = season.length; i < imax; i++)
        {
            option = new Element('option');
            option.value = season[i];
            option.innerHTML = Ingredients.get(season[i]).name;
            selector.appendChild(option);
        }
    },

    //Callback for change to recipes sorting select menu
    //#event (event): DOM change event
    //-> (void)
    search_sorting_change: function(event)
    {
        var select = event.findElement();
        
        //Update recipe display and index
        this.elements_build(this.$searchResults, this.search_results, 'search',
        {
            'mode':             'box',
            'preview':          true,
            'sorting':          select.value,
            'deletable':        true,
            'delete_callback':  this.search_delete.bind(this),
            'deleteText':       ACTIONS_LONG[3]
        });
        this.index_build('search');
    },

    /*******************************************************
    SEARCH CRITERIA
    ********************************************************/

    //Return an object with all current search criteria
    //#blank (bool): if true, a blank criteria structure is returned (default selects and no inputs)
    //->criteria (object): structure containing search criteria values
    search_criteria: function(blank)
    {
        if(typeof(blank) == 'undefined') blank = false;

        var criteria = null;
        if(blank)
        {
            criteria =
            {
                'text':			'',
                'tags':			[],
                'category': 	0,
                'origin': 		0,
                'favorites':	0,
                'healthy': 		0,
                'cheap': 		0,
                'easy' : 		0,
                'quick' : 		0,
                'success': 		0,
                'veggie': 		0,
                'chef': 		0,
                'chef_id': 		0,
                'fridge': 		0,
                'season': 		0,
                'liked': 		0,
                'disliked': 	0,
                'allergy':		0,
                'random':		0
            };
        }
        else
        {
            var text = this.$searchInput.value.stripTags();
            if(text == this.$searchInput.title) text = '';

            criteria =
            {
                'text':			text,
                'tags':			[],
                'category': 	parseInt($('select_search_category').value),
                'origin': 		parseInt($('select_search_origin').value),
                'favorites':	$('check_search_favorites').checked ? 1 : 0,
                'healthy': 		$('check_search_healthy').checked ? 1 : 0,
                'cheap': 		$('check_search_cheap').checked ? 1 : 0,
                'easy' : 		$('check_search_easy').checked ? 1 : 0,
                'quick' : 		$('check_search_quick').checked ? 1 : 0,
                'success': 		$('check_search_success').checked ? 1 : 0,
                'veggie': 		$('check_search_veggie').checked ? 1 : 0,
                'fridge': 		$('check_search_fridge').checked ? parseInt($('select_search_fridge').value) : 0,
                'season': 		$('check_search_season').checked ? parseInt($('select_search_season').value) : 0,
                'liked': 		$('check_search_liked').checked ? 1 : 0,
                'disliked': 	$('check_search_disliked').checked ? 1 : 0,
                'allergy':		0,
                'random':		0
            };
        }
        return criteria;
    },
    
    //Returns all ingredient IDs from current search criteria
    //e.g. fridge content, season, liked & disliked, etc.
    //#mode (string): ingredients to "exclude" or "include"
    //->ingredients (array): list of ingredient IDs
    search_criteria_ings: function(mode)
    {
        var ingredients = [];
        if(mode == 'exclude')
        {
            if(this.criteria.disliked)
                ingredients = ingredients.concat(User.tastes_get(TASTE_DISLIKE).export_ids());
        }
        else if(mode == 'include')
        {
            if(this.criteria.fridge)
            {
                if(this.criteria.fridge > 0)
                    ingredients.push(this.criteria.fridge);
                else
                    ingredients = ingredients.concat(User.fridge.export_ids());
            }
            if(this.criteria.season)
            {
                if(this.criteria.season > 0)
                    ingredients.push(this.criteria.season);
                else
                    ingredients = ingredients.concat(Ingredients.getSeason());
            }
            if(this.criteria.liked)
                ingredients = ingredients.concat(User.tastes_get(TASTE_LIKE).export_ids());
        }
        return ingredients.uniq();
    },

    /*******************************************************
    SEARCH HINT
    ********************************************************/

    //Display recipe drag hint
    //-> (void)
    search_hint: function()
    {
        return;
        if(Kookiiz.tabs.current_get() != 'main') return;

        var queue = Effect.Queues.get('recipes_hint');
        if(!queue || !queue.size())
        {
            this.$searchHint.setStyle({'top':'350px', 'left':'350px'});
            this.$searchHint.appear(
            {
                'duration': 0.5,
                'queue':    {'scope': 'recipes_hint', 'position': 'end'}
            });
            new Effect.Morph(this.$searchHint,
            {
                'duration': 0.8,
                'style':    {'top': '550px'},
                'queue':    {'scope': 'recipes_hint', 'position': 'end'}
            });
            this.$searchHint.fade(
            {
                'duration': 0.5,
                'queue':    {'scope': 'recipes_hint', 'position': 'end'}
            });
        }
    },

    //Cancel recipes hint display
    //-> (void)
    search_hint_stop: function()
    {
        Effect.Queues.get('recipes_hint').invoke('cancel');
        this.$searchHint.hide();
    },

    /*******************************************************
    SEARCH OPTIONS
    ********************************************************/

    //Disable a specific search option
    //#name (string): short name of the option to disable
    //-> (void)
    search_option_disable: function(name)
    {
        var search_option = $('check_search_' + name);
        search_option.checked = false;
        search_option.freeze();
        var container = search_option.up('li');
        container.select('img').invoke('addClassName', 'disabled');
        container.select('label').invoke('addClassName', 'disabled');
        var select = container.select('select')[0];
        if(select) select.freeze();
    },

    //Enable a specific search option
    //#name (string): short name of the option to disable
    //-> (void)
    search_option_enable: function(name)
    {
        var search_option = $('check_search_' + name);
        search_option.unfreeze();
        var container = search_option.up('li');
        container.select('img').invoke('removeClassName', 'disabled');
        container.select('label').invoke('removeClassName', 'disabled');
        var select = container.select('select')[0];
        if(select) select.unfreeze();
    },

    /*******************************************************
    SEARCH RESET
    ********************************************************/

    //Reset search controls values
    //-> (void)
    search_reset: function()
    {
        //Clear search input
        this.$searchInput.value = this.$searchInput.title;
        //Init selectors to default value
        this.$searchCategory.selectedIndex = 0;
        $('select_search_fridge').selectedIndex = 0;
        $('select_search_season').selectedIndex = 0;
        //Uncheck all criteria
        $$('input.check_search').each(function(option){option.checked = false;});
    },

    /*******************************************************
    SEARCH SCORE
    ********************************************************/

    //Compute score of a given recipe according to provided criteria
    //Mandatory criteria are omitted because each recipe is supposed to fullfill them
    //#recipe (object): recipe for which to compute a score
    //->score (int): computed score
    search_score: function(recipe)
    {
        var score = 0;

        //Increase score for each ingredient the recipe contains
        var ingredients = this.search_criteria_ings('include');
        for(var i = 0, imax = ingredients.length; i < imax; i++)
        {
            if(recipe.has_ingredient(ingredients[i])) score++;
        }

        return score;
    },

    /*******************************************************
    SEARCH SUMMARY
    ********************************************************/

    //Create a string summarizing search criteria and display it in provided container
    //#criteria (object): structure containing search criteria
    //->summary (string): text summarizing search criteria
    search_summary: function(criteria)
    {
        var text = '', texts = [], ingredients, ingredient;

        //Main criteria
        if(criteria.category)
            texts.push(RECIPES_CATEGORIES[criteria.category].toLowerCase());
        if(criteria.origin)
            texts.push(RECIPES_ORIGINS[criteria.origin].toLowerCase());
        if(criteria.text != '')
            texts.push(RECIPES_SEARCH_TEXT[17] + ' "' + criteria.text + '"');

        //Boolean criteria
        if(criteria.favorites)
            texts.push(RECIPES_SEARCH_TEXT[1]);
        if(criteria.healthy)
            texts.push(RECIPES_SEARCH_TEXT[2]);
        if(criteria.cheap)
            texts.push(RECIPES_SEARCH_TEXT[3]);
        if(criteria.easy)
            texts.push(RECIPES_SEARCH_TEXT[4]);
        if(criteria.quick)
            texts.push(RECIPES_SEARCH_TEXT[5]);
        if(criteria.success)
            texts.push(RECIPES_SEARCH_TEXT[6]);
        if(criteria.veggie)
            texts.push(RECIPES_SEARCH_TEXT[18]);
        
        //Allergies
        if(criteria.allergy)
        {
            text = RECIPES_SEARCH_TEXT[14] + ' (';

            var allergies = User.allergies_get(), found = 0, name = '';
            for(i = 0, imax = ALLERGIES.length; i < imax; i++)
            {
                name = ALLERGIES[i];
                if(allergies[name])
                {
                    text += (found ? ', ': '') + ALLERGIES_NAMES[i].toLowerCase();
                    found++;
                }
            }
            if(!found)
                text += RECIPES_SEARCH_TEXT[16];
            text += ')';
            
            texts.push(text);
        }

        //Chef-related criteria
        if(criteria.chef)
        {
            if(criteria.chef_id <= 0)
                texts.push(RECIPES_SEARCH_TEXT[7]);
            else
            {
                var chefSel  = $('select_search_chef'),
                    chefName = chefSel.childElements()[chefSel.selectedIndex].innerHTML;
                texts.push(RECIPES_SEARCH_TEXT[8] + chefName);
            }
        }

        //Including specific ingredients
        ingredients = this.search_criteria_ings('include');
        if(ingredients.length)
        {
            text = RECIPES_SEARCH_TEXT[22] + ' (';
            for(var i = 0, imax = ingredients.length; i < imax; i++)
            {
                ingredient = Ingredients.get(ingredients[i]);
                text += (i ? ', ': '') + ingredient.name;
            }
            text += ')';
            texts.push(text);
        }
        
        //Excluding specific ingredients
        ingredients = this.search_criteria_ings('exclude');
        if(ingredients.length)
        {
            text = RECIPES_SEARCH_TEXT[23] + ' (';
            for(var i = 0, imax = ingredients.length; i < imax; i++)
            {
                ingredient = Ingredients.get(ingredients[i]);
                text += (i ? ', ': '') + ingredient.name;
            }
            text += ')'
            texts.push(text);
        }

        //Build summary from texts
        var summary = '<strong>' + RECIPES_SEARCH_TEXT[20] + '</strong>: ';
        if(texts.length)
        {
            for(var i = 0, imax = texts.length; i < imax; i++)
            {
                summary += (i ? ', ': '') + texts[i];
            }
        }
        else
            summary += RECIPES_SEARCH_TEXT[21];
        
        //Return summary
        return summary;
    },

    //Display search summary as simple text or title
    //#text (string): text to display
    //#mode (string): either "text" or "title" (defaults to "text")
    //-> (void)
    search_summary_display: function(text, mode)
    {
        if(typeof(mode) == 'undefined') mode = 'text';

        this.$searchSummary.clean();
        if(mode == 'text')
        {
            var text_element = new Element('span');
            if(text.length > this.SEARCH_SUMMARY_MAX)
            {
                text_element.innerHTML = text.truncate(this.SEARCH_SUMMARY_MAX);
                text_element.addClassName('click');
                text_element.observe('click', this.search_summary_popup.bind(this, text));
            }
            else
                text_element.innerHTML = text;
            this.$searchSummary.appendChild(text_element);
        }
        else if(mode == 'title')
        {
            var title       = new Element('h6');
            title.innerHTML = text;
            this.$searchSummary.appendChild(title);
        }
        this.$searchSummary.show();
    },

    //Clear and hide search summary
    //-> (void)
    search_summary_hide: function()
    {
        this.$searchSummary.clean().hide();
    },

    //Open popup to display full search summary
    //#text (string): recipes search summary
    //-> (void)
    search_summary_popup: function(text)
    {
        Kookiiz.popup.custom(
        {
            'title':    RECIPES_SEARCH_TEXT[20],
            'text':     text,
            'confirm':  true
        });
    },

    /*******************************************************
    SEARCH UPDATE
    ********************************************************/

    //Update recipe search results when a criterion changes
    //-> (void)
    search_update: function()
    {
        //Abort if there are currently no results
        if(!this.$searchResults.select('.recipe_box').length) return;

        //Check which recipes do not fullfill criteria anymore
        var recipes_to_delete = [];
        var criteria = this.search_criteria(), recipe_id, recipe;
        for(var i = 0, imax = this.search_results.length; i < imax; i++)
        {
            recipe_id   = this.search_results[i];
            recipe      = Recipes.get(recipe_id);
            if(!recipe || !recipe.match_criteria(criteria))
                recipes_to_delete.push(recipe_id);
        }

        //Remove recipes from results
        for(i = 0, imax = recipes_to_delete.length; i < imax; i++)
        {
            this.search_delete(recipes_to_delete[i]);
        }

        var recipes = this.$searchResults.select('.recipe_box');
        if(!recipes.length)
            this.search_noresult();
    },

    /*******************************************************
    SORT
    ********************************************************/

    //Sort recipes objects
    //#recipes (array): list of recipe IDs to sort
    //#method (string): sorting method ("abc", "score", etc.)
    //->recipes_sorted (array): list of recipe IDs sorted according to "method"
    sort: function(recipes, method)
    {
        if(recipes.length <= 1) return recipes;
        if(!method) method = 'abc';
        var recipes_sorted = recipes;

        //Sort according to method
        var recipe_a, recipe_b;
        if(method == 'abc')
        {
            //Sort recipes by name
            recipes_sorted.sort(function(a, b)
            {
                recipe_a = Recipes.get(a);
                recipe_b = Recipes.get(b);
                return recipe_a.name.toLowerCase() > recipe_b.name.toLowerCase() ? 1 : -1;
            });
        }
        else if(method == 'rating')
        {
            //Sort recipes by rating
            recipes_sorted.sort(function(a, b)
            {
                recipe_a = Recipes.get(a);
                recipe_b = Recipes.get(b);
                return recipe_a.rating < recipe_b.rating ? 1 : -1;
            });
        }
        else if(method == 'price')
        {
            //Sort recipes by price
            recipes_sorted.sort(function(a, b)
            {
                recipe_a = Recipes.get(a);
                recipe_b = Recipes.get(b);
                return recipe_a.price > recipe_b.price ? 1 : -1;
            });
        }
        else if(method == 'score')
        {
            //Sort recipes by score (ie: number of criteria they fullfill)
            var self = this, score_a, score_b;
            recipes_sorted.sort(function(a, b)
            {
                recipe_a = Recipes.get(a);
                recipe_b = Recipes.get(b);

                //Compute score of each recipe
                score_a = self.search_score(recipe_a);
                score_b = self.search_score(recipe_b);

                //Sort recipes by score or by name
                if(score_a < score_b)
                    return 1;
                else if(score_a > score_b)
                    return -1;
                else
                    return recipe_a.name.toLowerCase() > recipe_b.name.toLowerCase() ? 1 : -1;
            });
        }

        return recipes_sorted;
    },

    /*******************************************************
    THEMES
    ********************************************************/

    //Generate a new selection of themes
    //-> (void)
    themes_shuffle: function()
    {
        //Empty container
        this.themes = [];
        this.$themesDisplay.clean();

        //Select random themes for global themes array
        var theme, Theme,
            themes = this.THEMES.random();
        for(var i = 0, imax = Math.min(themes.length, this.THEMES_MAX); i < imax; i++)
        {
            //Create theme object and display it as a button
            theme = themes[i];
            Theme = new RecipeTheme(theme.id, theme.name, theme.criteria);
            Theme.display(this.$themesDisplay);
            this.themes.push(Theme);
        }
    },

    //Callback for click on shuffling button
    //-> (void)
    themes_shuffle_click: function()
    {
        this.themes_shuffle();
    },

    //Called when the tab is clicked
    //-> (void)
    themes_tab_click: function()
    {
        this.themes_toggle();
    },

    //Called when mouse leaves themes tab
    //-> (void)
    themes_tab_out: function()
    {
        if(!this.$themes.visible())
            this.$themesBorder.hide();
    },

    //Called when mouse enters themes tab
    //-> (void)
    themes_tab_over: function()
    {
        this.$themesBorder.show();
    },

    //Display or hide themes overlay
    //#force (string):      force "up" or "down" (optional)
    //#callback (function): to call after toggling
    //-> (void)
    themes_toggle: function(force, callback)
    {
        if(!callback)
            callback = false;
        if(!force
            || (force == 'up' && this.$themes.visible())
            || (force == 'down' && !this.$themes.visible()))
        {
            Effect.toggle(this.$themes, 'slide',
            {
                'duration':     0.5,
                'beforeStart':  this.themes_toggle_start.bind(this),
                'afterFinish':  this.themes_toggle_stop.bind(this, callback)
            });
        }
        else if(callback) callback();
    },

    //Called right before themes overlay toggle
    //#effect (object): toggling effect object
    //-> (void)
    themes_toggle_start: function(effect)
    {
        var themes_display = effect.element;
        if(!themes_display.visible())
        {
            this.$searchIndex.hide();
            this.$themesBorder.show();
            this.$themesTab.addClassName('selected');
        }
    },

    //Called right after last redraw of overlay toggle
    //#callback (function): to call after toggling
    //#effect (object):     toggling effect object
    //-> (void)
    themes_toggle_stop: function(callback, effect)
    {
        var themes_display = effect.element;
        var themes_tab = this.$themesTab.select('h5')[0];
        if(themes_display.visible())
            themes_tab.innerHTML = ACTIONS[16];
        else
        {
            this.$themesBorder.hide();
            this.$themesTab.removeClassName('selected');
            themes_tab.innerHTML = RECIPES_THEMES_TEXT[0];
            this.$searchIndex.show();
        }
        if(callback)
            callback();
    },

    /*******************************************************
    TRANSLATE
    ********************************************************/

    //Load recipe translation form content sent by server
    //#recipe_id (int): ID of the recipe to translate
    //#content (DOM):   recipe translation form
    //-> (void)
    translate_display: function(recipe_id, content)
    {
        var container = $('section_recipe_translate').select('.section_content')[0];
        container.innerHTML = content;
        Kookiiz.tabs.loaded();

        //Open popup for language selection
        Kookiiz.popup.custom(
        {
           'confirm':               true,
           'cancel':                true,
           'callback':              this.translate_select.bind(this),
           'title':                 RECIPES_ALERTS[7],
           'text':                  RECIPES_ALERTS[8] + ' "' + Recipes.get(recipe_id, 'name') + '".',
           'content_url':           '/dom/recipe_translate_popup.php',
           'content_parameters':    'recipe_id=' + recipe_id
        });
    },

    //Open recipe translation form
    //#recipe_id (int): ID of the recipe to translate
    //#lang (string): destination language identifier
    //-> (void)
    translate_load: function(recipe_id)
    {
        Kookiiz.tabs.loading();
        Kookiiz.ajax.request('/dom/recipe_translate.php', 'get',
        {
            'callback': this.translate_display.bind(this, recipe_id),
            'json':     false,
            'request':  'recipe_id=' + recipe_id
        });
    },

    //Called when user confims or cancel language selection
    //#confirm (bool): true if user confirms
    //-> (void)
    translate_select: function(confirm)
    {
        var recipe_id = Kookiiz.tabs.tempContentGet();
        if(confirm)
        {
            var lang_select = $('recipe_translate_lang');
            if(lang_select)
            {
                //Set up translation form
                var lang = lang_select.value, lang_id = LANGUAGES.indexOf(lang);
                $('recipe_translate_target').innerHTML = LANGUAGES_NAMES[lang_id];
                $('button_recipe_translate').stopObserving('click').observe('click', this.translate_validate.bind(this, lang));
            }
            else
                Kookiiz.tabs.show('recipe_full', recipe_id, Recipes.get(recipe_id, 'name'));
        }
        else
            Kookiiz.tabs.show('recipe_full', recipe_id, Recipes.get(recipe_id, 'name'));
    },

    //Validate recipe translation
    //#lang (string): language identifier
    //-> (void)
    translate_validate: function(lang)
    {
        //Check that title is translated
        var title = $('recipe_translate_title').value.stripTags();
        if(!title)
        {
            Kookiiz.popup.alert({'text': RECIPES_ALERTS[9]});
            return;
        }

        //Check that all description steps are translated and build description string
        var description_steps = $('recipe_translate_descriptions').select('.step_input'),
            step, description = '';
        for(var i = 0, imax = description_steps.length; i < imax; i++)
        {
            step = description_steps[i].value.stripTags();
            if(step)
                description += (i + 1) + '. ' + step + '\n\n';
            else
            {
                Kookiiz.popup.alert({'text': RECIPES_ALERTS[10]});
                return;
            }
        }

        //Display loader
        Kookiiz.popup.loader();

        //Send translation to server
        var translation =
        {
            'name':         title,
            'description':  description
        };
        Kookiiz.api.call('recipes', 'translate',
        {
            'callback': this.translate_validated.bind(this),
            'request':  'recipe_id=' + Kookiiz.tabs.tempContentGet()
                        + '&translation=' + Object.toJSON(translation)
                        + '&lang=' + lang
        });
    },

    //Called once the translation have been saved
    //#response (object): server response object
    //-> (void)
    translate_validated: function(response)
    {
        Kookiiz.tabs.close();
        Kookiiz.popup.alert({'text': RECIPES_ALERTS[11]});
    },

    /*******************************************************
    VIEWED
    ********************************************************/

    //Add a recipe to viewed list
    //#recipe_id (int): ID of recipe to add
    //-> (void)
    viewed_add: function(recipe_id)
    {
        if(Recipes.get(recipe_id) && this.viewed.indexOf(recipe_id) < 0)
        {
            this.viewed.push(recipe_id);
            this.box_update('viewed');
        }
    }
});