/*******************************************************
Title: Health
Authors: Kookiiz Team
Purpose: Health and nutrition related functionalities
********************************************************/

//Represents a user interface for health-related functionalities
var HealthUI = Class.create(
{
    object_name: 'health_ui',

    /*******************************************************
    CONSTANTS
    ********************************************************/

    GRAPH_DAYS_MAX:             14,     //Max number of days on the graph
    GRAPH_DEVIATION_INTERVAL:   0.1,    //Acceptable deviation from reference nutrition value
    GRAPH_HEIGHT:               300,    //Graph height (in pixels)
    GRAPH_START_DEFAULT:        -11,    //Default start day index
    GRAPH_WIDTH:                520,    //Graph width (in pixels)
    //Categories of nutrition values
    NUTRITION_CATEGORIES:       [
                                    [0, 1, 2, 3, 4],                                //Essentials
                                    [5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16],    //Vitamins
                                    [17, 18, 19, 20, 21, 22 ,23, 24, 25],           //Minerals
                                    [26, 27, 28, 29, 30]                            //Others
                                ],

    /*******************************************************
    CONSTRUCTOR
    ********************************************************/

    //Class constructor
    //-> (void)
    initialize: function()
    {
        //Health tab
        this.GRAPH              = $('health_graph');
        this.GRAPH_NEEDS        = $('health_graph_needs');
        this.GRAPH_TIPS         = $('health_graph_tip');
        this.GRAPH_VALUE        = $('nutrition_history_value');
        this.PERIOD_START       = $('nutrition_history_start');
        this.PERIOD_STOP        = $('nutrition_history_stop');
        
        //Profile panel
        this.BREAKFAST_DISPLAY  = $('health_breakfast_display');
        this.PROFILE_ACTIVITY   = $('health_profile_activity');
        this.PROFILE_ANATOMY    = $('health_profile_anatomy');
        this.PROFILE_BREAKFAST  = $('health_profile_breakfast');
        this.PROFILE_QUICKMEAL  = $('health_profile_quickmeal');
        this.PROFILE_SAVE       = $('health_profile_save');
        this.PROFILE_SELECT     = $('health_profile_category');
        this.SPORT              = $('health_profile_sport');
        this.SPORT_FREQUENCY    = $('health_profile_sport_frequency');
        this.SPORTS_DISPLAY     = $('health_sports_display');

        //Nutrition panel
        this.NUTRITION_PANEL    = $('nutrition_display');
        this.NUTRITION_CATEGORY = $('select_nutrition_category');
    },

    /*******************************************************
    ACTIVITY
    ********************************************************/

    //Display user's activity settings
    //-> (void)
    activity_display: function()
    {
        var activity = User.activity_get(), name, select_menu;
        for(var i = 0, imax = USER_ACTIVITY.length; i < imax; i++)
        {
            name = USER_ACTIVITY[i];
            select_menu = $('health_profile_' + name);
            if(select_menu) 
                select_menu.selectedIndex = activity[name];
        }
    },

    //Called when activity settings have changed
    //-> (void)
    activity_update: function()
    {
        this.activity_display();
        this.update();
    },

    /*******************************************************
    ANATOMY
    ********************************************************/

    //Display user's anatomy settings
    //-> (void)
    anatomy_display: function()
    {
        var anatomy = User.anatomy_get(), name, select_menu;
        for(var i = 0, imax = USER_ANATOMY.length; i < imax; i++)
        {
            name = USER_ANATOMY[i];
            select_menu = $('health_profile_' + name);
            if(select_menu) 
                select_menu.value_set(anatomy[name]);
        }
    },

    //Called when anatomy settings have changed
    //-> (void)
    anatomy_update: function()
    {
        this.anatomy_display();
        this.imc_update();
        this.update();
    },
    
    /*******************************************************
    BREAKFAST
    ********************************************************/

    //Clear breakfast inputs
    //-> (void)
    breakfast_clear: function()
    {
        var ingredient_input = $('input_breakfast_ingredient');
        ingredient_input.value = ingredient_input.title;
        $('input_breakfast_quantity').value = '';
        $('select_breakfast_unit').value_set(ING_UNIT_DEFAULT);
    },

    //Display breakfast content
    //-> (void)
    breakfast_display: function()
    {
        var container = $('health_breakfast_display').select('.middle')[0].clean();

        //Build list
        var list = User.breakfast.build(
        {
            'deletable':    true,
            'editable':     true,
            'quantified':   true
        });
        if(list.empty())    
            container.innerHTML = INGREDIENTS_ALERTS[3];
        else                
            container.appendChild(list);
    },

    //Try to add an ingredient to the breakfast
    //-> (void)
    breakfast_ingredient_add: function()
    {
        var message_display = $('breakfast_popup_message').show();
        message_display.removeClassName('error');

        //Retrieve ingredient name and check its validity
        var ingredient_name = $('input_breakfast_ingredient').value.stripTags();
        var ingredient_id   = Ingredients.search(ingredient_name, 'id');
        if(ingredient_id)
        {
            //Retrieve quantity and check its validity
            var quantity = parseFloat($('input_breakfast_quantity').value);
            if(!isNaN(quantity) && quantity > 0)
            {
                var unit = parseInt($('select_breakfast_unit').value);

                //Add ingredient quantity to user's breakfast
                User.breakfast.quantity_add(new IngredientQuantity(ingredient_id, quantity, unit));

                //Reset controls
                this.breakfast_clear();
                $('input_breakfast_ingredient').target();

                //Display confirmation
                message_display.innerHTML = HEALTH_TEXT[12];
            }
            else
            {
                message_display.addClassName('error');
                message_display.innerHTML = INGREDIENTS_ALERTS[1];
            }
        }
        else
        {
            message_display.addClassName('error');
            message_display.innerHTML = INGREDIENTS_ALERTS[0];
        }
        message_display.highlight();
    },

    //Open breakfast popup
    //-> (void)
    breakfast_popup: function()
    {
        Kookiiz.popup.custom(
        {
            'title':            HEALTH_TEXT[10],
            'text':             HEALTH_TEXT[11],
            'confirm':          true,
            'confirm_label':    ACTIONS[29],
            'cancel':           false,
            'content_url':      '/dom/breakfast_popup.php',
            'content_init':     this.breakfast_popup_init.bind(this)
        });
    },

    //Init breakfast popup controls
    //-> (void)
    breakfast_popup_init: function()
    {
        $('button_breakfast_add').observe('click', this.onBreakfastAddClick.bind(this));
        Ingredients.autocompleter_init('input_breakfast_ingredient', this.onBreakfastIngSelect.bind(this));
        Utilities.observe_return('input_breakfast_quantity', this.onBreakfastQuantityEnter.bind(this));
    },

    //Called when the breakfast content is updated
    //-> (void)
    breakfast_update: function()
    {
        this.breakfast_display();
        this.update();
    },

    /*******************************************************
    GRAPH
    ********************************************************/

    //Display nutrition needs in graph caption
    //#value_id (int):  ID of the nutritional value
    //#needs (float):   reference daily value
    //-> (void)
    graphNeedsDisplay: function(value_id, needs)
    {
        this.GRAPH_NEEDS.innerHTML = Math.round(needs) + NUT_UNITS[value_id];
    },

    //Limit the graph period to a given value
    //#boundary (string): tells which end of the period should be modified (either "start" or "stop")
    //-> (void)
    graphPeriodLimit: function(boundary)
    {
        var start_day   = parseInt(this.PERIOD_START.value);
        var stop_day    = parseInt(this.PERIOD_STOP.value);
        if(stop_day - start_day >= this.GRAPH_DAYS_MAX)
        {
            if(boundary == 'start') 
                this.PERIOD_START.value_set(stop_day - this.GRAPH_DAYS_MAX + 1);
            else                    
                this.PERIOD_STOP.value_set(start_day + this.GRAPH_DAYS_MAX - 1);
        }
    },

    //Update "end day" select menu for nutrition computation
    //-> (void)
    graphPeriodUpdateStop: function()
    {
        var current_start = parseInt(this.PERIOD_START.value),
            current_stop  = parseInt(this.PERIOD_STOP.value);
        this.PERIOD_STOP.clean();

        //Nutrition "stop day" options range from current nutrition "start day" to the end of the menu
        var date, day_option;
        for(var i = current_start, imax = MENU_DAYS_FUTURE; i < imax; i++)
        {
            date = new DateTime(i);
            day_option = new Element('option', {value: i});
            day_option.innerHTML = date.day + '.' + date.month;
            this.PERIOD_STOP.appendChild(day_option);
        }

        //Try to restore selected value
        this.PERIOD_STOP.value_set(current_stop);
    },

    //Update health graph when a parameter changes
    //-> (void)
    graphUpdate: function()
    {
        //Retrieve selected nutrition value ID
        var value_id = parseInt(this.GRAPH_VALUE.value);

        //Compute list of days to display on the health graph
        var day_start = parseInt(this.PERIOD_START.value),
            day_end = parseInt(this.PERIOD_STOP.value),
            days = $A($R(day_start, day_end));

        //Retrieve nutrition data
        var breakfast   = User.breakfast.nutrition[value_id],
            quickmeals  = this.nutritionComputeHistory('quickmeals', day_start, day_end, value_id),
            recipes     = this.nutritionComputeHistory('recipes', day_start, day_end, value_id),
            reference   = User.needs_get()[value_id];

        //Build data set
        var data = [];
        for(var i = 0, imax = days.length; i < imax; i++)
        {
            data.push(
            {
                'day':          days[i],
                'breakfast':    breakfast,
                'quickmeals':   quickmeals[i],
                'recipes':      recipes[i]
            });
        }

        //Update graph
        this.Graph.setData(data, reference);
        //Display reference value
        this.graphNeedsDisplay(value_id, reference);
    },

    /*******************************************************
    IMC
    ********************************************************/

    //Compute IMC score
    //#height (float): body height in meters
    //#weight (float): body weight in kg
    //-> imc (float): IMC value for provided values
    imc_compute: function(height, weight)
    {
        return Math.round(10 * weight / (height * height)) / 10;
    },

    //Update IMC display
    //-> (void)
    imc_update: function()
    {
        var height = parseInt($('health_profile_height').value) / 100,
            weight = parseInt($('health_profile_weight').value);
        $('health_profile_imc').innerHTML = this.imc_compute(height, weight);
    },
    
    /*******************************************************
    INIT
    ********************************************************/

    //Init dynamic functionalities
    //-> (void)
    init: function()
    {
        //HEALTH TAB
        //Health graph object and observers
        this.Graph = new HealthGraph(this.GRAPH);
        this.Graph.observe('hovered', this.onGraphHover.bind(this));
        this.Graph.observe('cleared', this.onGraphOut.bind(this));
        //Attach event listeners to controls
        this.GRAPH_VALUE.observe('change', this.onGraphValueChange.bind(this));
        this.PERIOD_START.observe('change', this.onGraphStartChange.bind(this));
        this.PERIOD_STOP.observe('change', this.onGraphStopChange.bind(this));
        //Select default health graph period
        this.PERIOD_START.value_set(this.GRAPH_START_DEFAULT);
        this.graphPeriodUpdateStop();
        this.PERIOD_STOP.value_set(this.GRAPH_START_DEFAULT + this.GRAPH_DAYS_MAX - 1);

        //PROFILE PANEL
        if(!Kookiiz.panels.is_disabled('health_profile'))
        {
            this.PROFILE_SELECT.observe('change', this.onProfileCategoryChange.bind(this));
            this.PROFILE_SAVE.observe('click', this.onProfileSave.bind(this));
            $$('select.health_profile_select').invoke('observe', 'change', this.onProfileChange.bind(this));
            $('health_sport_add').observe('click', this.onSportsAdd.bind(this));
            $('health_breakfast_add').observe('click', this.onBreakfastPopupClick.bind(this));
            $('health_quickmeal_create').observe('click', this.onQuickmealsPopupClick.bind(this));

            //Display current user profile
            this.profileUpdate();
        }

        //NUTRITION PANEL
        if(!Kookiiz.panels.is_disabled('nutrition'))
        {
            this.NUTRITION_CATEGORY.observe('change', this.onNutritionChange.bind(this));
        }
    },

    /*******************************************************
    NUTRITION
    ********************************************************/

    //Clear nutrition panel
    //-> (void)
    nutritionClear: function()
    {
        this.NUTRITION_PANEL.innerHTML = NUTRITION_ALERTS[0];
    },

    //Compute history for a specific nutritional value over a given period
    //#type (string):   whether to compute nutrition for "quickmeals" or "recipes"
    //#day_start (int): index of the first day of the period
    //#day_end (int):   index of the last day of the period
    //#value_id (int):  ID of the nutritional value for which to compute history
    //->nutrition_history (array): amount of selected nutritional value for each day of the period (0 = day_start)
    nutritionComputeHistory: function(type, day_start, day_end, value_id)
    {
        //Init nutrition array with zeros on each day
        var history = $A($R(0, day_end - day_start)).map(function(){return 0;});

        //Loop through the days of the period
        for(var i = day_start, imax = day_end + 1; i < imax; i++)
        {
            history[i - day_start] += User.menu.nutrition_get(i, type)[value_id];
        }
        return history;
    },

    //Compute average nutritional value per day over a given period
    //#type (string): whether to compute nutrition for "quickmeals" or "recipes"
    //#day_start (int): index of the first day of the period
    //#day_end (int): index of the last day of the period
    //#values_ids (array): list of IDs of the nutritional values to compute (defaults to all)
    //->nutrition (array): amount of each nutritional values indexed by ID
    nutritionComputeMenu: function(type, day_start, day_end, values_ids)
    {
        //Compute all nutritional values if none is specified
        if(!values_ids) values_ids = $A($R(0, NUT_VALUES.length - 1)).map(function(){return 0;});

        //Init nutrition array with zeros at each nutritional value
        var nutrition = NUT_VALUES.map(function(){return 0;});

        //Loop through the days of the period
        var day_nutrition, value_id;
        for(var i = day_start, imax = day_end + 1; i < imax; i++)
        {
            day_nutrition = User.menu.nutrition_get(i, type);

            //Loop through selected nutrition values
            for(var j = 0, jmax = values_ids.length; j < jmax; j++)
            {
                value_id            = values_ids[j];
                nutrition[value_id] += day_nutrition[value_id];
            }
        }
        var days_count = Math.abs(day_end - day_start) + 1;
        return nutrition.map(function(value){return value / days_count;});
    },

    //Display nutrition information in provided container
    //#container (DOM or string):   DOM element where nutrition information should be displayed (or its ID)
    //#nutrition (object):          structure containing arrays of nutrition values
    //#options (object):            structure containing display options
    //-> (void)
    nutritionDisplay: function(container, nutrition, options)
    {
        container = $(container).clean();
        
        var nutrition_defaults =
        {
            'breakfast':    [], //nutritional content of the breakfast for each nutritional value indexed by ID
            'quickmeals':   [], //nutritional content of the quick meals for each nutritional value indexed by ID
            'recipes':      [],	//nutritional content of the recipes for each nutritional value indexed by ID
            'needs':        [], //reference daily value of each nutritional value indexed by ID
            'values':       []  //IDs of the nutritional values to display
        };
        nutrition = Object.extend(nutrition_defaults, nutrition || {});
        var defaults =
        {
            'full':     true,   //specifies if percentage bars and icons should be displayed
            'small':    false	//if true display nutrition with tiny text and thin percentage bars
        };
        options = Object.extend(defaults, options || {});

        //Add zero values to unavailable data
        //Return if some nutrition needs are missing
        var value_id;
        for(var i = 0, imax = nutrition.values.length; i < imax; i++)
        {
            value_id = nutrition.values[i];
            if(!nutrition.breakfast[value_id])  
                nutrition.breakfast[value_id] = 0;
            if(!nutrition.quickmeals[value_id]) 
                nutrition.quickmeals[value_id] = 0;
            if(!nutrition.recipes[value_id])    
                nutrition.recipes[value_id] = 0;
            if(!nutrition.needs[value_id])
                return;
        }

        //Create nutrition list
        var nutrition_list = new Element('ul',
        {
            'class': 'nutrition_list' + (options.small ? ' small' : '')
        });

        //Loop through nutritional values (i.e. energy, carbohydrates...)
        var value_name, value_unit, reference, decimals,
            breakfast_value, quickmeals_value, recipes_value, total_value,
            breakfast_percent, quickmeals_percent, recipes_percent, total_percent;
        for(i = 0, imax = nutrition.values.length; i < imax; i++)
        {
            //Retrieve information on current nutritional value
            value_id    = nutrition.values[i];
            value_name  = NUTRITION_VALUES_NAMES[value_id];
            value_unit  = NUT_UNITS[value_id];
            reference   = nutrition.needs[value_id];

            //Retrieve provided value for this nutritional value
            breakfast_value     = nutrition.breakfast[value_id];
            quickmeals_value    = nutrition.quickmeals[value_id];
            recipes_value       = nutrition.recipes[value_id];
            total_value         = breakfast_value + quickmeals_value + recipes_value;
            //Round factor is one 100th of the power of 10 closest to the reference value
            decimals = 2 - Math.round(Math.log(reference) / Math.LN10);
            if(decimals >= 0)
                total_value = total_value.toFixed(decimals);
            else
            {
                var factor  = Math.pow(10, -decimals);
                total_value = Math.round(total_value / factor) * factor;
            }


            //Compute current value percentage
            breakfast_percent   = Math.round(100 * (breakfast_value / reference));
            quickmeals_percent  = Math.round(100 * (quickmeals_value / reference));
            recipes_percent     = Math.round(100 * (recipes_value / reference));
            total_percent       = Math.round(100 * (breakfast_value + quickmeals_value + recipes_value) / reference);

            /*
            NUTRITION TEXT
            */

            var text_class = '';
            if(options.full && !options.small)
                text_class = 'small bold';
            else if(options.small)              
                text_class = 'tiny';

            var nutrition_item = new Element('li', {'class': 'nutrition_item'});
            var nutrition_text = new Element('span', {'class': text_class});
            nutrition_text.innerHTML = '<strong>' + value_name + '</strong>'
                                        + ': ' + total_value + value_unit
                                        + ' (' + total_percent + '%)';
            nutrition_item.appendChild(nutrition_text);
            nutrition_list.appendChild(nutrition_item);

            /*
            PERCENTAGE BARS
            */

            if(options.full)
            {
                //Avoid percentage bars overflow
                if(breakfast_percent > 100) breakfast_percent = 100;
                recipes_percent     = Math.min(recipes_percent, 100 - breakfast_percent);
                quickmeals_percent  = Math.min(quickmeals_percent, 100 - recipes_percent - breakfast_percent);

                //Create percentage bars
                nutrition_item = new Element('li', {'class': 'nutrition_item'});
                var percent_container = new Element('div', {'class': 'percent_container'});
                if(breakfast_percent)
                {
                    var div_percent_breakfast = new Element('div', {'class': 'percent_breakfast'});
                    div_percent_breakfast.setStyle({'width': breakfast_percent + '%'});
                    percent_container.appendChild(div_percent_breakfast);
                }
                if(recipes_percent)
                {
                    var div_percent_recipe = new Element('div', {'class': 'percent_recipes'});
                    div_percent_recipe.setStyle({'width': recipes_percent + '%'});
                    percent_container.appendChild(div_percent_recipe);
                }
                if(quickmeals_percent)
                {
                    var div_percent_quickmeals = new Element('div', {'class': 'percent_quickmeals'});
                    div_percent_quickmeals.setStyle({'width': quickmeals_percent + '%'});
                    percent_container.appendChild(div_percent_quickmeals);
                }
                nutrition_item.appendChild(percent_container);
                nutrition_list.appendChild(nutrition_item);
            }
        }

        //Append nutrition list to provided container
        container.appendChild(nutrition_list);
    },

    //Update nutrition UI
    //-> (void)
    nutritionUpdate: function()
    {
        var self = this;

        //Create loader on nutrition panel
        this.NUTRITION_PANEL.loading();

        //Check selected nutrition category
        var category    = parseInt(this.NUTRITION_CATEGORY.value);
        var values_ids  = this.NUTRITION_CATEGORIES[category];

        //Update nutrition information depending on mode
        var nutrition = null;
        switch(Kookiiz.tabs.current_get())
        {
            //Display average nutritional content for selected period
            case 'health':
                var day_start   = parseInt($('nutrition_history_start').value);
                var day_end     = parseInt($('nutrition_history_stop').value);
                nutrition =
                {
                    'breakfast':    User.breakfast.getNutrition(),
                    'quickmeals':   self.nutritionComputeMenu('quickmeals', day_start, day_end, values_ids),
                    'recipes':      self.nutritionComputeMenu('recipes', day_start, day_end, values_ids),
                    'needs':        User.needs_get(),
                    'values':       values_ids
                };
                Kookiiz.panels.header_set('nutrition');
                break;

            //Display nutritional content of the recipe being edited
            case 'recipe_form':
                var recipe = Kookiiz.recipeform.getRecipe();
                if(!recipe) return;
                nutrition =
                {
                    'recipes':      recipe.nutrition,
                    'needs':        User.needs_get(),
                    'values':       values_ids
                };
                Kookiiz.panels.header_set('nutrition', recipe.name);
                break;

            //Display nutritional content of currently displayed recipe
            case 'recipe_full':
                var recipe_id   = Kookiiz.recipes.displayed_get();
                var recipe      = Recipes.get(recipe_id);
                if(recipe)
                {
                    nutrition =
                    {
                        'recipes':  recipe.nutrition,
                        'needs':    User.needs_get(),
                        'values':   values_ids
                    };
                    Kookiiz.panels.header_set('nutrition', recipe.name);

                }
                else
                {
                    //Set default panel header and clear content
                    this.nutritionClear();
                    Kookiiz.panels.header_set('nutrition');
                }
                break;
        }
        if(nutrition)
        {
            //Display nutritional content
            this.nutritionDisplay(this.NUTRITION_PANEL, nutrition);
            //Force nutrition panel open
            Kookiiz.panels.toggle('nutrition', 'open', true);
        }
    },

    /*******************************************************
    OBSERVERS
    ********************************************************/

    //Called when user clicks button to add an ingredient to the breakfast
    //-> (void)
    onBreakfastAddClick: function()
    {
        this.breakfast_ingredient_add();
    },

    //Called upon selection of a breakfast ingredient in the autocompleter
    //#ingredient (object): selected ingredient object
    //-> (void)
    onBreakfastIngSelect: function(ingredient)
    {
        $('select_breakfast_unit').value_set(ingredient.unit);
        $('input_breakfast_quantity').target();
    },

    //Called when user click on button to open breakfast popup
    //-> (void)
    onBreakfastPopupClick: function()
    {
        this.breakfast_popup();
    },

    //Called when enter key is pressed in the quantity input field
    //-> (void)
    onBreakfastQuantityEnter: function()
    {
        var input = $('input_breakfast_quantity');
        if(input.value) this.breakfast_ingredient_add();
    },

    //Called when a bar of the graph is hovered with the mouse
    //Highlight corresponding recipes in the menu or breakfast content
    //#event (object): custom event object
    //-> (void)
    onGraphHover: function(event)
    {
        var start = parseInt(this.PERIOD_START.value),
            data  = $H(event.memo),
            day   = start + data.get('day'),
            type  = data.get('type');
        switch(type)
        {
            case 'breakfast':
                //Select breakfast category on health profile
                this.PROFILE_SELECT.selectedIndex = 1;
                this.onProfileCategoryChange();
                //Highlight breakfast options
                this.BREAKFAST_DISPLAY.addClassName('selected');
                break;

            case 'quickmeals':
                //Move menu to day of interest and highlight quick meals list
                Kookiiz.menu.start_set(Math.max(day - 1, -14));
                Kookiiz.menu.box_mode('quickmeals', 'menu_box_1');
                Kookiiz.menu.quickmeals_select(day, day);
                Kookiiz.menu.moveUp(1);
                break;

            case 'recipes':
                //Move menu to day of interest and highlight recipes
                Kookiiz.menu.start_set(Math.max(day - 1, -14));
                Kookiiz.menu.meals_select(day, day, 'red');
                Kookiiz.menu.moveUp(1);
                break;
        }
    },

    //Called when the mouse leaves a bar of the graph
    //Remove highlight on recipes or breakfast
    //#event (object): custom event object
    //-> (void)
    onGraphOut: function(event)
    {
        var data = $H(event.memo),
            type = data.get('type');
        switch(type)
        {
            case 'all':
                //Cancel all highlighting
                this.BREAKFAST_DISPLAY.removeClassName('selected');
                Kookiiz.menu.box_mode('meals', 'menu_box_1');
                Kookiiz.menu.quickmeals_unselect();
                Kookiiz.menu.meals_unselect();
                Kookiiz.menu.moveDown();
                break;
            case 'breakfast':
                //Unselect breakfast display
                this.BREAKFAST_DISPLAY.removeClassName('selected');
                break;

            case 'quickmeals':
                //Return to recipes box mode and unselect quick meals
                Kookiiz.menu.box_mode('meals', 'menu_box_1');
                Kookiiz.menu.quickmeals_unselect();
                Kookiiz.menu.moveDown();
                break;

            case 'recipes':
                //Unselect recipes
                Kookiiz.menu.meals_unselect();
                Kookiiz.menu.moveDown();
                break;
        }
    },

    //Called when graph "start day" changes
    //-> (void)
    onGraphStartChange: function()
    {
        this.graphPeriodUpdateStop();
        this.graphPeriodLimit('stop');
        this.update();
    },

    //Called when graph "stop day" changes
    //-> (void)
    onGraphStopChange: function()
    {
        this.graphPeriodLimit('start');
        this.update();
    },

    //Called when the graph nutrition value selector changes
    //-> (void)
    onGraphValueChange: function()
    {
        this.graphUpdate();
    },

    //Called when nutrition panel parameters change
    //-> (void)
    onNutritionChange: function()
    {
        this.nutritionUpdate();
    },

    //Called when health profile category changes
    //-> (void)
    onProfileCategoryChange: function()
    {
        //Hide all categories
        $$('.health_profile_category').invoke('hide');

        //Display selected one
        var breakfast_add       = $('health_breakfast_add');
        var quickmeal_create    = $('health_quickmeal_create');
        var category            = parseInt(this.PROFILE_SELECT.value);
        switch(category)
        {
            //Anatomy
            case 0:
                this.PROFILE_ANATOMY.show();
                quickmeal_create.hide();
                breakfast_add.hide();
                this.PROFILE_SAVE.show();
                break;

            //BREAKFAST
            case 1:
                this.PROFILE_BREAKFAST.show();
                quickmeal_create.hide();
                breakfast_add.show();
                this.PROFILE_SAVE.show();
                break;

            //ACTIVITY
            case 2:
                this.PROFILE_ACTIVITY.show();
                quickmeal_create.hide();
                breakfast_add.hide();
                this.PROFILE_SAVE.show();
                break;

            //QUICK MEALS
            case 3:
                this.PROFILE_QUICKMEAL.show();
                this.PROFILE_SAVE.hide();
                breakfast_add.hide();
                quickmeal_create.show();
                break;
        }
    },

    //Called when a health profile control changes
    //#event (event): DOM change event
    //-> (void)
    onProfileChange: function(event)
    {
        var select  = event.findElement();
        var name    = select.id.split('_')[2];
        if(select.hasClassName('activity'))     User.activity_set(name, select.selectedIndex);
        else if(select.hasClassName('anatomy')) User.anatomy_set(name, parseInt(select.value));

        //Update UI
        this.update();
    },

    //Called when user saves his profile
    //-> (void)
    onProfileSave: function()
    {
        var props   = ['activity', 'anatomy', 'breakfast', 'sports'];
        var options = {'silent': false};
        User.profile_save(props, options);
    },

    //Callback for click on quick meals popup button
    //-> (void)
    onQuickmealsPopupClick: function()
    {
        Kookiiz.quickmeals.form_popup();
    },

    //Called when the user clicks to add a new sport
    //-> (void)
    onSportsAdd: function()
    {
        var sport_id = parseInt(this.SPORT.value),
            freq_id  = parseInt(this.SPORT_FREQUENCY.value);
        User.sports_add(sport_id, freq_id);
    },

    //Called when the user clicks a button to remove a sport
    //-> (void)
    onSportsDelete: function(sport_id)
    {
        User.sports_delete(sport_id);
    },

    /*******************************************************
    PROFILE
    ********************************************************/

    //Display user's health profile
    //-> (void)
    profileUpdate: function()
    {
        this.activity_display();
        this.anatomy_display();
        this.breakfast_display();
        this.sportsDisplay();
    },
    
    /*******************************************************
    PYRAMID
    ********************************************************/

    //Build health pyramid with percentages of each category of food
    //-> (void)
    pyramidUpdate: function()
    {
        //Init pyramid structure with food categories
        var pyramid =
        {
            'veg':      0,
            'cereals':  0,
            'milk':     0,
            'meat':     0,
            'fat':      0,
            'sugar':    0
        };

        //Retrieve currently selected period
        var day_start   = parseInt(this.PERIOD_START.value);
        var day_end     = parseInt(this.PERIOD_STOP.value);
        var days_count  = day_end - day_start + 1;

        //Loop through menu recipes
        var recipes = User.menu.recipes_get(day_start, day_end),
            recipe, ingredients, ing_qty, quantity;
        for(var i = 0, imax = recipes.length; i < imax; i++)
        {
            recipe = Recipes.get(recipes[i]);
            if(!recipe) continue;

            //Loop through ingredients of current recipe
            ingredients = recipe.ingredients;
            for(var j = 0, jmax = ingredients.length; j < jmax; j++)
            {
                ing_qty     = ingredients[j];
                quantity    = ing_qty.convert(UNIT_GRAMS);
                switch(ing_qty.ingredient.category)
                {
                    case 1:
                        pyramid.milk += quantity / days_count;
                        break;
                    case 2:
                    case 3:
                    case 4:
                        pyramid.meat += quantity / days_count;
                        break;
                    case 5:
                    case 7:
                        pyramid.fat += quantity / days_count;
                        break;
                    case 6:
                        pyramid.cereals += quantity / days_count;
                        break;
                    case 8:
                    case 9:
                        pyramid.veg += quantity / days_count;
                        break;
                    case 10:
                        pyramid.sugar += quantity / days_count;
                        break;
                    default:break;
                }
            }
        }
    },
    
    /*******************************************************
    SPORTS
    ********************************************************/

    //Display user's sports list
    //-> (void)
    sportsDisplay: function()
    {
        this.SPORTS_DISPLAY.clean();

        //Loop through user's sports
        var sports = User.sports_get(),
            sports_list = new Element('ul', {'class': 'sports_list'}),
            sport_id, freq_id, sport_item, sport_text, sport_remove;
        for(var i = 0, imax = sports.length; i < imax; i++)
        {
            //Parameters
            sport_id    = sports[i].id;
            freq_id     = sports[i].freq;

            //Item
            sport_item = new Element('li', {'class': 'sport_item'});

            //Text
            sport_text = new Element('span');
            sport_text.innerHTML = SPORTS_NAMES[sport_id] + ' (' + SPORTS_FREQUENCIES[freq_id] + ')';

            //Button
            sport_remove = new Element('img',
            {
                'alt':      ACTIONS[23],
                'class':    'button15 cancel',
                'src':      ICON_URL,
                'title':    ACTIONS[23]
            });
            sport_remove.observe('click', this.onSportsDelete.bind(this, sport_id));

            //Wrap-up
            sport_item.appendChild(sport_text);
            sport_item.appendChild(sport_remove);
            sports_list.appendChild(sport_item);
        }
        if(sports_list.empty()) 
            this.SPORTS_DISPLAY.innerHTML = SPORTS_ALERTS[0];
        else                    
            this.SPORTS_DISPLAY.appendChild(sports_list);
    },

    //Called after sports have been updated
    //-> (void)
    sportsUpdate: function()
    {
        this.sportsDisplay();
        this.update();
    },

    /*******************************************************
    UPDATE
    ********************************************************/

    //Update all health UI components
    //-> (void)
    update: function()
    {
        this.graphUpdate();
        this.nutritionUpdate();
    }
});