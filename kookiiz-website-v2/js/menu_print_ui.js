/*******************************************************
Title: Menu Print UI
Authors: Kookiiz Team
Purpose: Display printable menu
********************************************************/

//Represents a user interface for menu printing
var MenuPrintUI = Class.create(
{
    object_name: 'menu_print_ui',

    /*******************************************************
    CONSTANTS
    ********************************************************/

    PERIOD_DEFAULT: 7,  //Default number of days the printed menu spans

    /*******************************************************
    CONSTRUCTOR
    ********************************************************/

    //Class constructor
    //-> (void)
    initialize: function()
    {
        //DOM elements
        this.CHECK_NUT      = $('menu_print_withnut');
        this.CHECK_STATS    = $('menu_print_withstats');
        this.CHECK_TOTNUT   = $('menu_print_withtotalnut');
        this.START          = $('menu_print_start');
        this.STOP           = $('menu_print_stop');
    },

    /*******************************************************
    DISPLAY
    ********************************************************/

    //Update menu print display according to current menu content and printing settings
    //Note: in order to minimize computations, content of invisible menu boxes/meal boxes is NOT updated!
    //#start_day (int):         specifies from which day the displayed menu should start
    //#boxcount (int):          specifies how many menu boxes to display
    //#with_nutrition (bool):   whether to display nutrition values as well
    //-> (void)
    display: function(start_day, boxcount, with_nutrition)
    {
        //Components
        var menu_boxes = $$('div.menu_box'), nutrition_boxes = $$('div.nutrition_box'),
            box_index, menu_box, nut_box, nut_display, meals, shopping, meal_box, 
            meal_content, meal_guests, recipe_id, shopping_status, shopping_text;

        //Loop through menu days
        for(var day = -MENU_DAYS_PAST, daymax = MENU_DAYS_FUTURE; day < daymax; day++)
        {
            box_index = MENU_DAYS_PAST + day;
            menu_box  = menu_boxes[box_index];
            nut_box   = nutrition_boxes[box_index];

            //Check if current day is in the visibility scope
            if(day >= start_day && day < start_day + boxcount)
            {
                //Show menu box
                menu_box.show();

                //Retrieve current menu box components
                meals    = menu_box.select('div.meal_box');
                shopping = menu_box.select('span.shopping_day')[0];

                //Nutrition display
                if(with_nutrition)
                {
                    nut_display = nut_box.select('.display')[0];
                    Kookiiz.health.nutritionDisplay(nut_display, 
                    {
                        'quickmeals':   User.menu.nutrition_get(day, 'quickmeals'),
                        'recipes':      User.menu.nutrition_get(day, 'recipes'),
                        'needs':        User.needs_get(),
                        'values':       MENU_NUTRITION_VALUES
                    }, {'full': false});
                    nut_box.show();
                }
                else 
                    nut_box.hide();

                //Force a new line each 3 boxes or after nutrition box
                if(with_nutrition || !((day - start_day) % 3))
                    menu_box.setStyle({'clear': 'left'});
                else 
                    menu_box.setStyle({'clear': ''});

                //Loop through current day meals
                for(var pos = 0; pos < MENU_MEALS_COUNT; pos++)
                {
                    //Current meal box
                    meal_box     = meals[pos];
                    meal_content = meal_box.select('.meal_content')[0];
                    meal_guests  = meal_box.select('.span_guests_count')[0];

                    //Check if there is a recipe in current meal
                    recipe_id = User.menu.recipe_get(day, pos);
                    if(recipe_id)
                    {
                        meal_content.innerHTML = Recipes.get(recipe_id, 'name');
                        meal_guests.innerHTML  = User.menu.guests_meal_get(day, pos);

                        //Display meal content
                        meal_box.show();
                    }
                    else 
                        meal_box.hide();
                }

                //Shopping display
                shopping_status = User.menu.shopping_status_get(day);
                shopping_text   = document.createTextNode(SHOPPING_STATUS[shopping_status]);
                shopping.clean().appendChild(shopping_text);
            }
            else
            {
                menu_box.hide();    //Hide menu box
                nut_box.hide();     //Hide nutrition box
            }
        }
    },

    //Compute and display recipe categories stats
    //-> (void)
    display_stats: function()
    {
        var container     = $('menu_print_stats').select('.content')[0].clean(),
            empty_message = $('menu_print_stats').select('.empty')[0];

        //Retrieve period parameters
        var start_day = parseInt(this.START.value),
            stop_day  = parseInt(this.STOP.value);

        //Loop through categories
        var empty = true, category_span,
            categories = User.menu.recipes_categorize(start_day, stop_day);
        for(var i = 0, imax = categories.length; i < imax; i++)
        {
            if(categories[i])
            {
                category_span = new Element('span', {'class': 'stats_category'});
                if(i)
                {
                    category_span.innerHTML = RECIPES_CATEGORIES[i] + ': ' + categories[i];
                    container.appendChild(category_span);
                }
                else
                {
                    category_span.innerHTML = MENU_TEXT[23] + ': ' + categories[i];
                    container.appendChild(category_span);
                }
                empty = false;
            }
        }

        //Display stats or empty message
        if(empty)
        {
            container.hide();
            empty_message.show();
        }
        else
        {
            container.show();
            empty_message.hide();
        }
    },

    //Compute and display average nutritonal values over the period
    //-> (void)
    display_totnut: function()
    {
        var container = $('menu_print_totalnut').select('.content')[0].clean();

        //Retrieve period parameters
        var start_day = parseInt(this.START.value),
            stop_day  = parseInt(this.STOP.value);

        //Loop through nutrition values
        var nutrition = Kookiiz.health.nutritionComputeMenu('recipes', start_day, stop_day, MENU_NUTRITION_VALUES),
            needs = User.needs_get(), value, ref, percentage, span_value;
        for(var i = 0, imax = MENU_NUTRITION_VALUES.length; i < imax; i++)
        {
            value       = nutrition[MENU_NUTRITION_VALUES[i]];
            ref         = needs[MENU_NUTRITION_VALUES[i]];
            percentage  = Math.round(100 * (value / ref));

            span_value = new Element('span', {'class': 'nutrition_value'});
            span_value.innerHTML = NUTRITION_VALUES_NAMES[i] + ': ' + Math.round(value) + NUT_UNITS[i] + ' (' + percentage + '%)'
            container.appendChild(span_value);
        }
    },
        
    /*******************************************************
    INIT
    ********************************************************/

    //Init dynamic functionalities
    //-> (void)
    init: function()
    {
        //Build period selectors
        this.START.clean();
        var current_date, day_option;
        for(var i = -MENU_DAYS_PAST, imax = MENU_DAYS_FUTURE; i < imax; i++)
        {
            current_date = new DateTime(i);
            day_option   = new Element('option', {'value': i});
            day_option.innerHTML = current_date.day + '.' + current_date.month;
            this.START.appendChild(day_option);
        }
        this.START.selectedIndex = MENU_DAYS_PAST;
        this.period_limit();

        //Observers
        User.menu.observe('updated', this.menu_updated.bind(this));
        this.CHECK_NUT.observe('click', this.option_click.bind(this));
        this.CHECK_STATS.observe('click', this.option_click.bind(this));
        this.CHECK_TOTNUT.observe('click', this.option_click.bind(this));
        this.START.observe('change', this.start_change.bind(this));
        this.STOP.observe('change', this.stop_change.bind(this));
        $('button_menu_print').observe('click', this.print_click.bind(this));
    },

    /*******************************************************
    PERIOD
    ********************************************************/

    //Limit available "period stop" options according to current "period start"
    //-> (void)
    period_limit: function()
    {
        //Store current day selection and clear select menu
        var stop_day = parseInt(this.STOP.value) || false;
        this.STOP.clean();

        //Redefine stop day options
        var start_day = parseInt(this.START.value),
            current_date, day_option;
        for(var i = start_day, imax = MENU_DAYS_FUTURE; i < imax; i++)
        {
            current_date = new DateTime(i);
            day_option   = new Element('option', {value: i});
            day_option.innerHTML = current_date.day + '.' + current_date.month;
            this.STOP.appendChild(day_option);
        }

        //Try to restore previous selection or set default period or maximum value
        var index = stop_day ? this.STOP.value_set(stop_day) : -1;
        if(index <= 0) 
            this.STOP.selectedIndex = Math.min(this.PERIOD_DEFAULT - 1, this.STOP.childElements().length - 1);
    },

    /*******************************************************
    UPDATE
    ********************************************************/

    //Called after an update of menu print
    //-> (void)
    update: function()
    {
        //Retrieve parameters
        var startDay = parseInt(this.START.value),
            stopDay  = parseInt(this.STOP.value),
            boxCount = stopDay - startDay + 1,
            withNut  = this.CHECK_NUT.checked;

        //Display menu and additional information
        this.display(startDay, boxCount, withNut);
        this.display_totnut();
        this.display_stats();
    },

    /*******************************************************
    CALLBACKS
    ********************************************************/

    //Callback for click on nutrition print check box
    //-> (void)
    option_click: function()
    {
        if(this.CHECK_STATS.checked)    
            $('menu_print_stats').show();
        else                            
            $('menu_print_stats').hide();
        if(this.CHECK_TOTNUT.checked)   
            $('menu_print_totalnut').show();
        else                            
            $('menu_print_totalnut').hide();
        this.update();
    },

    //Called when menu content has been updated
    //-> (void)
    menu_updated: function()
    {
        this.update();
    },

    //Callback for print button click
    //-> (void)
    print_click: function()
    {
        window.print();
    },

    //Called when starting day changes
    //-> (void)
    start_change: function()
    {
        this.period_limit();
        this.update();
    },

    //Called when period stop changes
    //-> (void)
    stop_change: function()
    {
        this.update();
    }
});