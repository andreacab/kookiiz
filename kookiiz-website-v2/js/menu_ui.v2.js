/*******************************************************
Title: Menu UI
Authors: Kookiiz Team
Purpose: Enable users to edit their menu
********************************************************/

//Represents a user interface for the menu
var MenuUI = Class.create(
{
    object_name: 'menu_ui',

    /*******************************************************
    CONSTANTS
    ********************************************************/

    AUTOSAVE_DELAY: 5,  //Delay in seconds before the menu is autosaved after a modification
    MEAL_CHARS_MAX: 30, //Max chars for recipe names on meals

    /*******************************************************
    CONSTRUCTOR
    ********************************************************/

    //Class constructor
    //-> (void)
    initialize: function()
    {
        this.start           = 0;    //Index of the first menu slate
        this.express_recipes = [];   //List of recipe IDs for express menu
        this.midnight_timer  = 0;    //Timer for automatic shift at midnight
        this.autosave_timer  = 0;    //Timer for autosaving system

        this.BUTTON_BACK    = $('menu_back');
        this.BUTTON_FORWARD = $('menu_forward');
        this.BUTTON_PRINT   = $('menu_print');
        this.BUTTON_RESET   = $('menu_reset');
        this.BUTTON_SAVE    = $('menu_save');
        this.BUTTON_TODAY   = $('menu_today');
        this.MEAL_BOXES     = $$('.meal_box');
        this.MENU_BOXES     = $$('.menu_box');
        this.MENU_CONTENT   = $('menu_content');
    },

    /*******************************************************
    AUTOSAVE
    ********************************************************/

    //Autosave menu after a modification
    //The function waits for a specific delay before triggering the save
    //-> (void)
    autosave: function()
    {
        window.clearTimeout(this.autosave_timer);
        this.autosave_timer = window.setTimeout(this.autosave_callback.bind(this), this.AUTOSAVE_DELAY * 1000);
    },

    //Callback for autosaving timeout
    //-> (void)
    autosave_callback: function()
    {
        this.save(true);
    },

    /*******************************************************
    BOX MODE
    ********************************************************/

    //Switch between several menu box display modes
    //#mode (string):           display mode
    //#menu_box (DOM/string):   menu box for which display mode must change (defaults to all)
    //-> (void)
    box_mode: function(mode, menu_box)
    {
        //Loop through menu boxes
        var menu_boxes = menu_box ? [$(menu_box)] : this.MENU_BOXES;
        var box, meals, nutrition, quickmeals;
        for(var i = 0, imax = menu_boxes.length; i < imax; i++)
        {
            box         = menu_boxes[i];
            meals       = box.select('.meals')[0];
            nutrition   = box.select('.nutrition')[0];
            quickmeals  = box.select('.quickmeals')[0];
            switch(mode)
            {
                case 'meals':
                    nutrition.hide();
                    quickmeals.hide();
                    meals.show();
                    break;
                case 'nutrition':
                    meals.hide();
                    quickmeals.hide();
                    nutrition.show();
                    break;
                case 'quickmeals':
                    meals.hide();
                    nutrition.hide();
                    quickmeals.show();
                    break;
            }
        }
    },

   /*******************************************************
    DISPLAY
    ********************************************************/

    //Display menu content on UI
    //-> (void)
    display: function()
    {
        //Variables used in the loop
        var menu_box, day_index, menu_index, has_recipes, has_quickmeals,
            title, date_text, meals, shopping, nutrition_box, nutrition_icon,
            quickmeals_box, quickmeals_icon, date, recipe_id, meal_box,
            meal_content, meal_guests, meal_controls, meal_delete, shopping_status;

        //Loop through menu boxes
        for(var i = 0, imax = this.MENU_BOXES.length; i < imax; i++)
        {
            //Retrieve current day parameters
            menu_box    = this.MENU_BOXES[i];
            day_index   = this.start_get() + i;
            menu_index  = MENU_DAYS_PAST + day_index;

            //Hide box if it is out of menu range
            if(menu_index >= MENU_DAYS_MAX)
            {
                menu_box.hide();
                continue;
            }
            else 
                menu_box.show();

            //Add special class if menu box is today
            var today = day_index == 0;
            if(today)   
                menu_box.addClassName('today');
            else        
                menu_box.removeClassName('today');

            //Check if menu box should be frozen (i.e. is from the past)
            var frozen = day_index < 0;
            if(frozen)  
                menu_box.addClassName('frozen');
            else        
                menu_box.removeClassName('frozen');

            //Parameters telling if current day contains recipes and/or quick meals
            has_recipes     = false;
            has_quickmeals  = User.menu.quickmeals_get(day_index, day_index).length > 0;

            //Menu box components
            title           = menu_box.select('.menu_title')[0];
            date_text       = menu_box.select('.menu_date')[0];
            meals           = menu_box.select('.meal_box');
            shopping        = menu_box.select('.shopping')[0];
            nutrition_box   = menu_box.select('.nutrition')[0];
            nutrition_icon  = menu_box.select('.icon_nutrition')[0];
            quickmeals_box  = menu_box.select('.quickmeals .list')[0];
            quickmeals_icon = menu_box.select('.icon_quickmeals')[0];

            //DATE
            date = new DateTime(day_index);
            if(today)   
                title.innerHTML = VARIOUS[9].toUpperCase();
            else        
                title.innerHTML = date.dayname.toUpperCase();
            date_text.innerHTML = date.daynum + ' ' + date.monthname.capitalize();

            //MEALS
            for(var pos = 0, posmax = meals.length; pos < posmax; pos++)
            {
                //Current meal box
                meal_box        = meals[pos];
                meal_content    = meal_box.select('.meal_content')[0];
                meal_guests     = meal_box.select('.guests_count')[0];
                meal_controls   = meal_box.select('.meal_controls')[0];
                meal_delete     = meal_box.select('.meal_delete')[0];

                //Check if there is a recipe in this meal
                recipe_id = User.menu.recipe_get(day_index, pos);
                if(recipe_id)
                {
                    has_recipes = true;
                    meal_box.removeClassName('empty').addClassName('full');

                    //Recipe name and guests
                    meal_content.innerHTML = Recipes.get(recipe_id, 'name').truncate(this.MEAL_CHARS_MAX);
                    meal_guests.innerHTML  = User.menu.guests_meal_get(day_index, pos);

                    //Meal content and controls
                    meal_content.show();
                    if(frozen)
                    {
                        meal_controls.hide();
                        meal_delete.hide();

                    }
                    else
                    {
                        meal_controls.show();
                        meal_delete.show();
                    }
                }
                else
                {
                    meal_box.removeClassName('full').addClassName('empty');

                    //Hide meal content and controls
                    meal_content.hide();
                    meal_controls.hide();
                    meal_delete.hide();
                }
            }

            //NUTRITION
            if(has_recipes || has_quickmeals)
                nutrition_icon.show();
            else
                nutrition_icon.hide();
            this.display_nutrition(nutrition_box, day_index);

            //QUICK MEALS
            if(has_quickmeals)
                quickmeals_icon.show();
            else
                quickmeals_icon.hide();
            this.display_quickmeals(quickmeals_box, day_index);

            //SHOPPING
            shopping_status = User.menu.shopping_status_get(day_index);
            shopping.removeClassName('evening').removeClassName('morning').removeClassName('none');
            if(shopping_status == SHOPPING_STATUS_EVENING)      
                shopping.addClassName('evening');
            else if(shopping_status == SHOPPING_STATUS_MORNING) 
                shopping.addClassName('morning');
            else if(shopping_status == SHOPPING_STATUS_NONE)    
                shopping.addClassName('none');
        }
    },

    //Hide/show menu backward/forward arrows
    //-> (void)
    display_buttons: function()
    {
        var start = this.start_get();

        if(start <= -MENU_DAYS_PAST)
            this.BUTTON_BACK.style.visibility = 'hidden';
        else
            this.BUTTON_BACK.style.visibility = 'visible';

        if(start >= MENU_DAYS_FUTURE - MENU_DAYS_COUNT)
            this.BUTTON_FORWARD.style.visibility = 'hidden';
        else
            this.BUTTON_FORWARD.style.visibility = 'visible';
    },

    //Display nutritional values on a specific menu box
    //#container (DOM/string):  container DOM element
    //#day (int):               day index
    //-> (void)
    display_nutrition: function(container, day)
    {
        Kookiiz.health.nutritionDisplay(container,
        {
            'breakfast':    User.breakfast.getNutrition(),
            'quickmeals':   User.menu.nutrition_get(day, 'quickmeals'),
            'recipes':      User.menu.nutrition_get(day, 'recipes'),
            'needs':        User.needs_get(),
            'values':       MENU_NUTRITION_VALUES
        },
        {
            'small': true
        });
    },

    //Display quickmeals list on a specific menu box
    //#container (DOM/string):  container DOM element
    //#day (int):               day index
    //-> (void)
    display_quickmeals: function(container, day)
    {
        Kookiiz.quickmeals.list(container, User.menu.quickmeals_get(day, day), 
        {
            'callback':  this.quickmeal_delete_click.bind(this),
            'deletable': true
        });
    },

    /*******************************************************
    DROPPABLES
    ********************************************************/

    //Make a menu component droppable for recipe objects
    //#element (DOM): menu DOM object which should be made droppable
    //-> (void)
    drop_add: function(element)
    {
        var self = this;
        if(element.hasClassName('meal_box') || element.hasClassName('meal_drop'))
        {
            Droppables.add(element,
            {
                'accept':     ['recipe_box', 'recipe_item'],
                'hoverclass': 'hover',
                'onDrop':     this.recipe_drop.bind(self),
                'posFixed':   true
            });
        }
        else if(element.hasClassName('quickmeal_drop'))
        {
            Droppables.add(element,
            {
                'accept':     ['recipe_item'],
                'hoverclass': 'hover',
                'onDrop':     this.quickmeal_drop.bind(self),
                'posFixed':   true
            });
        }
    },

    //Hide droppable areas
    //-> (void)
    drop_hide: function()
    {
        $$('.quickmeal_drop, .meal_drop').each(function(area)
        {
            this.drop_remove(area);
            area.hide();
        }, this);
        $$('.meal_box').each(function(meal)
        {
            this.drop_remove(meal);
            meal.show();
        }, this);
    },

    //Remove element from droppables
    //#element (DOM): menu DOM object which should be removed
    //-> (void)
    drop_remove: function(element)
    {
        Droppables.remove(element);
    },

    //Hide empty meals and show droppables area
    //#type (string): type of content to be dropped
    //-> (void)
    drop_show: function(type)
    {
        //Rise menu up
        

        //Show meals and hide everything else
        this.box_mode('meals');

        if(type == 'recipe')
        {
            this.meals_unselect();

            var meal, meal_id, meal_drop, empty, menu_box, frozen;
            for(var i = 0, imax = this.MEAL_BOXES.length; i < imax; i++)
            {
                meal     = this.MEAL_BOXES[i];
                empty    = meal.hasClassName('empty');
                menu_box = meal.up('.menu_box');
                frozen   = menu_box.hasClassName('frozen');
                if(!frozen)
                {
                    if(empty)
                    {
                        meal_id     = parseInt(meal.id.split('_')[2]);
                        meal_drop   = $('meal_drop_' + meal_id);
                        meal.hide();
                        meal_drop.show();
                        this.drop_add(meal_drop);
                    }
                    else this.drop_add(meal);
                }
            }
        }
        else if(type == 'quickmeal')
        {
            this.MEAL_BOXES.invoke('hide');
            $$('.quickmeal_drop').each(function(area)
            {
                this.drop_add(area);
                area.show();
            }, this);
        }
    },

    /*******************************************************
    GUESTS
    ********************************************************/

    //Callback for guests count change
    //#event (object): DOM event
    //-> (void)
    guests_change: function(event)
    {
        //Retrieve DOM elements
        var element  = event.findElement(),
            meal_box = element.up('.meal_box'),
            menu_box = meal_box.up('.menu_box'),
            meal_id  = parseInt(meal_box.id.split('_')[2]),
            box_id   = parseInt(menu_box.id.split('_')[2]),
            meal_pos = meal_id - 3 * box_id;

        //Increase or decrease guests count
        var start_day = this.start_get();
        if(element.hasClassName('plus'))
            User.menu.guests_increase(start_day + box_id, meal_pos);
        else                                
            User.menu.guests_decrease(start_day + box_id, meal_pos);
    },

    /*******************************************************
    INIT
    ********************************************************/

    //Init dynamic functionalities
    //-> (void)
    init: function()
    {
        //Days
        $$('.menu_box .icon_nutrition').invoke('observe', 'click', this.nutrition_click.bind(this));
        $$('.menu_box .icon_quickmeals').invoke('observe', 'click', this.quickmeals_click.bind(this));
        $$('.menu_box .shopping img.cart').invoke('observe', 'click', this.shopping_change.bind(this));
        $$('.menu_box .shopping img.status').invoke('observe', 'click', this.shopping_click.bind(this));

        //Meals
        $$('.meal_box .meal_delete').invoke('observe', 'click', this.meal_delete.bind(this));
        $$('.meal_box .meal_content').invoke('observe', 'click', this.meal_click.bind(this));
        $$('.meal_box .guests_control').invoke('observe', 'click', this.guests_change.bind(this));

        //Buttons
        [this.BUTTON_BACK, this.BUTTON_FORWARD].invoke('observe', 'click', this.skip_days.bind(this));
        this.BUTTON_PRINT.observe('click', this.print_click.bind(this));
        this.BUTTON_RESET.observe('click', this.reset_click.bind(this));
        this.BUTTON_SAVE.observe('click', this.save_click.bind(this));
        this.BUTTON_TODAY.observe('click', this.today.bind(this));

        //Midnight shift
        this.midnight_shift();
    },
    
    /*******************************************************
    MEALS
    ********************************************************/

    //Display current recipe on meal click
    //#event (event): Prototype event object
    //-> (void)
    meal_click: function(event)
    {
        //Retrieve DOM elements
        var meal_box = event.findElement('.meal_box'),
            meal_id  = parseInt(meal_box.id.split('_')[2]),
            menu_box = meal_box.up('.menu_box'),
            box_id   = parseInt(menu_box.id.split('_')[2]),
            meal_pos = meal_id - 3 * box_id;

        //Retrieve recipe ID in menu and display it
        var start_day = this.start_get(),
            recipe_id = User.menu.recipe_get(start_day + box_id, meal_pos);
        Kookiiz.tabs.show('recipe_full', recipe_id, Recipes.get(recipe_id, 'name'));
    },

    //Remove recipes from a specific day box on click
    //#event (event): Prototype event object
    //-> (void)
    meal_delete: function(event)
    {
        //Retrieve DOM elements
        var meal_box = event.findElement('.meal_box'),
            meal_id  = parseInt(meal_box.id.split('_')[2]),
            menu_box = meal_box.up('.menu_box'),
            box_id   = parseInt(menu_box.id.split('_')[2]),
            meal_pos = meal_id - 3 * box_id;

        //Remove recipe from MENU array
        var start_day = this.start_get();
        User.menu.recipe_delete(start_day + box_id, meal_pos);
    },

    //Select all meals contained in boxes for provided period
    //#day_start (int): index of the first day of the period
    //#day_end (int):   index of the last day of the period
    //#color (string):  highlight color (defaults to "yellow")
    //-> (void)
    meals_select: function(day_start, day_end, color)
    {
        if(!color) color = 'yellow';
        var start = this.start_get();

        //Loop through menu boxes
        var day_index, meals;
        for(var i = 0, imax = this.MENU_BOXES.length; i < imax; i++)
        {
            day_index = start + i;
            meals     = this.MENU_BOXES[i].select('.meal_box');

            //Check if menu box is in the period
            if(day_index >= day_start && day_index <= day_end)
            {
                meals.each(function(meal, pos)
                {
                    if(User.menu.recipe_get(day_index, pos))   
                        meal.addClassName('selected ' + color);
                    else                                       
                        meal.removeClassName('selected ' + color);
                });
            }
            else 
                meals.invoke('removeClassName', 'selected ' + color)
        }
    },

    //Unselect all meals
    //-> (void)
    meals_unselect: function()
    {
        this.MEAL_BOXES.invoke('removeClassName', 'selected');
        this.MEAL_BOXES.invoke('removeClassName', 'yellow');
        this.MEAL_BOXES.invoke('removeClassName', 'green');
        this.MEAL_BOXES.invoke('removeClassName', 'red');
    },

    /*******************************************************
    MIDNIGHT TIMER
    ********************************************************/

    //Set-up midnight shift
    //-> (void)
    midnight_shift: function()
    {
        var time           = new Date().getTime(),
            tomorrow       = new DateTime(1),
            midnight_time  = new Date(tomorrow.year, tomorrow.monthnum - 1, tomorrow.daynum, 0, 0, 1, 0).getTime(),
            ms_to_midnight = midnight_time - time;

        window.clearTimeout(this.midnight_timer);
        this.midnight_timer = window.setTimeout(this.midnight_shift_callback.bind(this), ms_to_midnight);
    },

    //Called when clock goes through midnight
    //-> (void)
    midnight_shift_callback: function()
    {
        User.menu.update();
        this.midnight_shift();
    },
    
    /*******************************************************
    MOVE
    ********************************************************/
   
    //Move down one or more menu boxes
    //#box (int): box ID (optional)
    //-> (void)
    moveDown: function(box)
    {
        if(box)
            this.MENU_BOXES[box].removeClassName('up');
        else
            this.MENU_BOXES.invoke('removeClassName', 'up');
    },
   
    //Move up one or more menu boxes
    //#box (int): box ID (optional)
    //-> (void)
    moveUp: function(box)
    {
        if(box)
            this.MENU_BOXES[box].addClassName('up');
        else
            this.MENU_BOXES.invoke('addClassName', 'up');
    },

    /*******************************************************
    NUTRITION
    ********************************************************/

    //Called when user clicks on nutrition icon
    //#event (event): Prototype event object
    //-> (void)
    nutrition_click: function(event)
    {
        var element   = event.findElement(),
            menu_box  = element.up('.menu_box'),
            nutrition = menu_box.select('.nutrition')[0];

        //Toggle nutrition visibility
        element.toggleClassName('selected');
        if(nutrition.visible()) 
            this.box_mode('meals', menu_box);
        else                    
            this.box_mode('nutrition', menu_box);
    },

    /*******************************************************
    PRINT
    ********************************************************/

    //Callback for click on menu print button
    //-> (void)
    print_click: function()
    {
        window.open('/print/menu');
    },

    /*******************************************************
    CALLBACKS - DROP
    ********************************************************/

    //Called when user clicks on quick meal deletion icon
    //#quickmeal_id (int):  unique ID of the quick meal
    //#event (object):      DOM click event
    //-> (void)
    quickmeal_delete_click: function(quickmeal_id, event)
    {
        //Retrieve DOM elements
        var menu_box    = event.findElement('.menu_box');
        var box_id      = parseInt(menu_box.id.split('_')[2]);

        //Remove selected quick meal
        var start_day   = this.start_get();
        var day         = start_day + box_id;
        User.menu.quickmeal_delete(day, quickmeal_id);

        //Display meals if quick meals list is now empty
        var quickmeals = User.menu.quickmeals_get(day, day);
        if(!quickmeals.length) this.box_mode('meals', menu_box);
    },

    //Called when a quick meal is dropped on the dedicated area
    //#quickmeal_box (DOM):     quick meal DOM element
    //#quickmeal_drop (DOM):    quick meal droppable area
    //#mouse_x (int):           horizontal mouse position at drop time
    //#mouse_y (int):           vertical mouse position at drop time
    //-> (void)
    quickmeal_drop: function(quickmeal_box, quickmeal_drop, mouse_x, mouse_y)
    {
        //Retrieve DOM elements
        var quickmeal_id = parseInt(quickmeal_box.id.split('_')[2]),
            menu_box     = quickmeal_drop.up('.menu_box'),
            box_id       = parseInt(menu_box.id.split('_')[2]);

        //Add quick meal and show quick meals list
        var start_day = this.start_get();
        User.menu.quickmeal_add(start_day + box_id, quickmeal_id);
        this.box_mode('quickmeals', menu_box);
    },

    //Called when user clicks on quickmeal icon
    //#event (object): DOM click event
    //-> (void)
    quickmeals_click: function(event)
    {
        //Retrieve DOM elements
        var element    = event.findElement(),
            menu_box   = element.up('.menu_box'),
            quickmeals = menu_box.select('.quickmeals')[0];

        //Toggle quick meals visibility
        element.toggleClassName('selected');
        if(quickmeals.visible())    
            this.box_mode('meals', menu_box);
        else                        
            this.box_mode('quickmeals', menu_box);
    },

    //Select all quick meals contained in boxes for provided period
    //#day_start (int): index of the first day of the period
    //#day_end (int):   index of the last day of the period
    //-> (void)
    quickmeals_select: function(day_start, day_end)
    {
        var start = this.start_get();

        //Loop through menu boxes
        var day_index, quickmeals;
        for(var i = 0, imax = this.MENU_BOXES.length; i < imax; i++)
        {
            day_index  = start + i;
            quickmeals = this.MENU_BOXES[i].select('.quickmeals')[0];

            //Check if menu box is in the period
            if(day_index >= day_start && day_index <= day_end)
                quickmeals.addClassName('selected');
            else 
                quickmeals.removeClassName('selected');
        }
    },

    //Unselect all quickmeals
    //-> (void)
    quickmeals_unselect: function()
    {
        $$('.menu_box .quickmeals').invoke('removeClassName', 'selected');
    },

    /*******************************************************
    RECIPES
    ********************************************************/

    //Function that handles recipes drop on meals
    //#recipe_box (DOM):    recipe box which has been dropped
    //#meal_box (DOM):      meal box on which the recipe was dropped
    //#mouse_x (int):       horizontal mouse position at drop time
    //#mouse_y (int):       vertical mouse position at drop time
    //-> (void)
    recipe_drop: function(recipe_box, meal_box, mouse_x, mouse_y)
    {
        //Create a customized event for meal box DOM object
        $(meal_box).fire('menu:recipe_dropped');

        //Retrieve DOM elements
        var recipe_id = parseInt(recipe_box.id.split('_')[2]),
            meal_id   = parseInt(meal_box.id.split('_')[2]),
            menu_box  = meal_box.up('.menu_box'),
            box_id    = parseInt(menu_box.id.split('_')[2]),
            meal_pos  = meal_id - 3 * box_id;

        //Add recipe to the menu
        var start_day = this.start_get();
        User.menu.recipe_add(start_day + box_id, meal_pos, recipe_id);
    },

    /*******************************************************
    RESET
    ********************************************************/

    //Callback for click on menu reset button
    //Open a popup to ask user for confirmation
    //-> (void)
    reset_click: function()
    {
        Kookiiz.popup.confirm(
        {
            'text':     MENU_ALERTS[2],
            'callback': this.reset_confirm.bind(this)
        });
    },

    //Called when user confirms or cancels menu reset
    //#confirm (bool): true if user confirms the reset
    //-> (void)
    reset_confirm: function(confirm)
    {
        if(confirm)
        {
            User.menu.reset();
            this.save(true);
        }
    },

    /*******************************************************
    SAVE
    ********************************************************/

    //Save user's menu
    //#silent (bool): whether to save menu in the background
    //-> (void)
    save: function(silent)
    {
        if(User.menu.updated)
        {
            User.profile_save(['menu'], 
            {
                'message':  MENU_ALERTS[3],
                'silent':   silent || false
            });
        }
    },

    //Callback for click on menu save button
    //-> (void)
    save_click: function()
    {
        this.save();
    },

    //Called when menu content is synchronized with server
    //-> (void)
    saved: function()
    {
        this.BUTTON_SAVE.freeze();
    },
    
    /*******************************************************
    HIDE/SHOW
    ********************************************************/
   
    slatesHide: function()
    {
        $('kookiiz_menu').hide();
    },
    
    slatesShow: function()
    {
        $('kookiiz_menu').show();
    },
    
    /*******************************************************
    SHOPPING
    ********************************************************/

    //Called when the cart icon is clicked
    //Rotate shopping status
    //#event (event): DOM click event
    //-> (void)
    shopping_change: function(event)
    {
        var menu_box = event.findElement('.menu_box'),
            frozen   = menu_box.hasClassName('frozen');
        if(!frozen)
        {
            var start_day = this.start_get(),
                box_id    = parseInt(menu_box.id.split('_')[2]);
            User.menu.shopping_status_rotate(start_day + box_id);
        }
    },

    //Called when the shopping area is clicked
    //Select specific shopping status
    //#event (object): DOM click event
    //-> (void)
    shopping_click: function(event)
    {
        var element     = event.findElement();
        var menu_box	= element.up('.menu_box');
        var frozen 		= menu_box.hasClassName('frozen');
        if(!frozen)
        {
            //Retrieve shopping status
            var status = 0;
            if(element.hasClassName('evening'))        
                status = SHOPPING_STATUS_EVENING;
            else if(element.hasClassName('morning'))   
                status = SHOPPING_STATUS_MORNING;
            else if(element.hasClassName('none'))      
                status = SHOPPING_STATUS_NONE;

            //Set shopping status
            var box_id    = parseInt(menu_box.id.split('_')[2]);
            var start_day = this.start_get();
            User.menu.shopping_status_set(start_day + box_id, status);
        }
    },

    /*******************************************************
    SKIP
    ********************************************************/

    //Skip menu days on arrow click
    //#event (object): DOM click event
    //-> (void)
    skip_days: function(event)
    {
        //Retrieve current parameters
        var element   = event.findElement(),
            start_day = this.start_get();

        //Compute new starting day
        if(element.hasClassName('arrow_left'))  
            start_day -= MENU_DAYS_COUNT;
        else                                   
            start_day += MENU_DAYS_COUNT;
        //Check boundaries
        if(start_day < -MENU_DAYS_PAST)         
            start_day = -MENU_DAYS_PAST;
        if(start_day > MENU_DAYS_FUTURE - 1)    
            start_day = MENU_DAYS_FUTURE - 1;

        //Show meals and change starting day
        this.box_mode('meals');
        this.start_set(start_day);
    },

    /*******************************************************
    STARTING DAY
    ********************************************************/

    //Return current menu start day
    //->start_day (int): day index
    start_get: function()
    {
        return this.start;
    },

    //Set menu start day
    //#day_index (int): day index
    //-> (void)
    start_set: function(day)
    {
        //Set start day
        this.start = parseInt(day);
        //Display menu and buttons
        this.display();
        this.display_buttons();
        //Update shopping period display
        Kookiiz.shopping.period_display();
    },

    /*******************************************************
    TODAY
    ********************************************************/

    //Set menu to today
    //-> (void)
    today: function()
    {
        //Show meals and change menu starting day
        this.box_mode('meals');
        this.start_set(0);
    },

    /*******************************************************
    UPDATE
    ********************************************************/

    //Called when menu content has been updated
    //#type (string): type of menu update
    //-> (void)
    updated: function(type)
    {
        //Reflect menu update on UI
        this.display();

        //For updates after initial import
        if(type != 'import')
        {
            //Enable save button
            this.BUTTON_SAVE.unfreeze();
            //Trigger autosave process
            this.autosave();
        }
    }
});