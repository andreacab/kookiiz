/*******************************************************
Title: Menu
Authors: Kookiiz Team
Purpose: Define the menu object
********************************************************/

//Represents a user menu
var Menu = Class.create(Observable,
{
    object_name: 'menu',

    /*******************************************************
    CONSTRUCTOR
    ********************************************************/

    //Class constructor
    //-> (void)
    initialize: function()
    {
        //Reset params
        this.plan               = [];
        this.date               = Time.datecode_get();
        this.modified           = false;
        this.MENU_OLD_END       = -1;
        this.MENU_OLD_SHOPPING  = -MENU_DAYS_PAST;

        //Set-up blank menu and define default shopping day
        this.setup();
        this.shopping_default();
    },

    /*******************************************************
    RESET/SETUP
    ********************************************************/

    //Clear every recipe and generate a blank menu
    //-> (void)
    reset: function()
    {
        this.MENU_OLD_END       = -1;
        this.MENU_OLD_SHOPPING  = -MENU_DAYS_PAST;
        this.setup();               //Reset global array
        this.shopping_default();    //Define default shopping day
        this.update('reset');
    },

    //Clear week menu array and build blank one
    //-> (void)
    setup: function()
    {
        this.plan = [];
        for(var i = 0; i < MENU_DAYS_MAX; i++)
        {
            this.plan.push(new MenuDay(i - MENU_DAYS_PAST));
        }
    },

    /*******************************************************
    SHIFT
    ********************************************************/

    //Shift menu days by provided value
    //#value (int): shift value in days
    //-> (void)
    shift: function(value)
    {
        if(value > 0)
        {
            //Loop through the menu
            for(var i = 0, imax = this.plan.length; i < imax; i++)
            {
                //Copy future day or create blank one
                if(this.plan[i + value])    
                    this.plan[i] = this.plan[i + value];
                else                        
                    this.plan[i] = new MenuDay(i - MENU_DAYS_PAST);
            }
        }
        else if(value < 0)
        {
            //Loop through the menu
            for(var i = this.plan.length - 1, imin = 0; i >= imin; i--)
            {
                //Copy past day or create blank one
                if(this.plan[i - value])    
                    this.plan[i] = this.plan[i - value];
                else                        
                    this.plan[i] = new MenuDay(i - MENU_DAYS_PAST);
            }
        }
    },

    /*******************************************************
    IMPORT/EXPORT
    ********************************************************/

    //Export menu content
    //->menu (array): content of the menu ready to be sent to the server
    export_content: function()
    {
        var menu = [];
        for(var i = 0, imax = this.plan.length; i < imax; i++)
        {
            menu[i] =
            {
                'q': this.plan[i].quickmeals_export(),
                'r': this.plan[i].recipes_export(),
                's': this.plan[i].shopping_export()
            };
            if(!menu[i].q && !menu[i].r && !menu[i].s) 
                menu[i] = 0;
        }
        return menu;
    },

    //Restore old menu content in current session
    //#menu (object): menu data as:
    //  #plan (array):  menu content by days
    //  #date (string): menu reference data as "YYYY-MM-DD"
    //-> (void)
    import_content: function(menu)
    {
        var old_menu = menu.plan, 
            old_menu_date = menu.date;

        //Compute age of imported menu
        var menu_age = Time.datecodes_diff(this.date, old_menu_date);
        //Too many days between old and current session -> abort restoring process
        if(menu_age > MENU_DAYS_MAX || menu_age < 0) 
            return;
        //Restore old menu
        else
        {
            //Reset menu
            this.setup();

            //Loop through old menu, from the day it starts overlapping with current menu
            var day_index;
            for(var i = menu_age, imax = MENU_DAYS_MAX; i < imax; i++)
            {
                if(old_menu[i])
                {
                    day_index = i - menu_age;

                    //Quick meals
                    var quickmeals = old_menu[i].q;
                    if(quickmeals) this.plan[day_index].quickmeals_import(quickmeals);

                    //Recipes
                    var recipes = old_menu[i].r;
                    if(recipes)
                    {
                        this.plan[day_index].recipes_import(recipes);
                        this.MENU_OLD_END = day_index - MENU_DAYS_PAST;
                    }

                    //Shopping
                    if(old_menu[i].s)
                    {
                        this.plan[day_index].shopping_status_set(old_menu[i].s.s);
                        this.plan[day_index].shopping.import_content(old_menu[i].s);
                        
                        //Cancel empty shopping lists
                        if(this.plan[day_index].shopping.isEmpty())
                            this.plan[day_index].shopping_status_set(SHOPPING_STATUS_NONE);
                        //Store most recent shopping list of the past
                        else if(day_index < MENU_DAYS_PAST)
                            this.MENU_OLD_SHOPPING = day_index - MENU_DAYS_PAST;
                    }
                }
            }
        }

        //Menu as been updated
        this.update('import');
    },

    /*******************************************************
    SAVE
    ********************************************************/

    //Consider menu as "saved"
    //-> (void)
    saved: function()
    {
        this.modified = false;
        this.fire('saved');
    },

    /*******************************************************
    UPDATE
    ********************************************************/

    //Callback for any change made to the menu
    //#type (string):   type of update (optional)
    //#period (object): "start" and "stop" to limit computations (optional)
    //-> (void)
    update: function(type, period)
    {
        //Default period (all menu)
        period = period || {'start': -MENU_DAYS_PAST, 'stop': MENU_DAYS_FUTURE - 1};
        
        //Run some tests
        this.date_check();
        this.shopping_days_check();

        //Update nutrition, shopping
        this.nutrition_update();
        this.shopping_update(period);

        //Set menu as updated
        this.updated(type);
        //Set menu as saved (for import process)
        if(type == 'import') this.saved();
    },

    //Set menu as updated (not synchronized with server)
    //#action (string): type of update (optional)
    //-> (void)
    updated: function(type)
    {
        this.modified = true;
        this.fire('updated', {'type': type || ''});
    },

    /*******************************************************
    DATE
    ********************************************************/

    //Check if menu should be shifted (if day has changed)
    //->shifted (bool): true if menu was shifted
    date_check: function()
    {
        var today = Time.datecode_get();
        if(today != this.date)
        {
            this.shift(Time.datecodes_diff(today, this.date));
            this.date = today;
            return true;
        }
        return false;
    },

    //Return current menu date
    //->date (string): menu date code as "YYYY-MM-DD"
    date_get: function()
    {
        return this.date;
    },

    /*******************************************************
    GUESTS
    ********************************************************/

    //Decrease the number of guests for a specific day and meal
    //#day (int):   index of the day
    //#meal (int):  index of the meal
    //-> (void)
    guests_decrease: function(day, meal)
    {
        var guests = this.guests_meal_get(day, meal) - 1;
        if(guests < MENU_GUESTS_MIN) 
            guests = MENU_GUESTS_MIN;
        this.guests_set(day, meal, guests);
    },

    //Return all guests values for specified period
    //#day_start (int): index of the first day of the period
    //#day_end (int):   index of the last day of the period
    //->guests (array): number of guests for each meal in the period
    guests_get: function(day_start, day_end)
    {
        if(typeof(day_start) == 'undefined' || day_start < 0 - MENU_DAYS_PAST)  
            day_start = 0 - MENU_DAYS_PAST;
        if(typeof(day_end) == 'undefined' || day_end > MENU_DAYS_FUTURE - 1)    
            day_end = MENU_DAYS_FUTURE - 1;

        //Loop through the menu
        var guests = [], day_index;
        for(var i = day_start, imax = day_end + 1; i < imax; i++)
        {
            day_index = MENU_DAYS_PAST + i;
            guests    = guests.concat(this.plan[day_index].guests_get());
        }
        return guests;
    },

    //Increase the number of guests for a specific day and meal
    //#day (int):   index of the day
    //#meal (int):  index of the meal
    //-> (void)
    guests_increase: function(day, meal)
    {
        var guests = this.guests_meal_get(day, meal) + 1;
        if(guests > MENU_GUESTS_MAX) 
            guests = MENU_GUESTS_MAX;
        this.guests_set(day, meal, guests);
    },

    //Get guests for specific day and meal
    //#day (int):   index of the day
    //#meal (int):  index of the meal
    //->guests (int): number of guests for specific meal
    guests_meal_get: function(day, meal)
    {
        var day_index = MENU_DAYS_PAST + day;
        return this.plan[day_index].guests_get(meal);
    },

    //Set number of guests for specific meal
    //#day (int):    index of the day
    //#meal (int):   index of the meal
    //#guests (int): number of guests
    //-> (void)
    guests_set: function(day, meal, guests)
    {
        var day_index = MENU_DAYS_PAST + day;
        this.plan[day_index].guests_set(meal, guests);
        this.update('guests');
    },

    /*******************************************************
    NUTRITION
    ********************************************************/

    //Return menu nutrition for a given day and type
    //#day (int):       index of the day
    //#type (string):   whether to return "recipes" or "quickmeals" nutrition
    //->nutrition (array): list of nutritional values indexed by ID
    nutrition_get: function(day, type)
    {
        var day_index = MENU_DAYS_PAST + day;
        return this.plan[day_index].nutrition_get(type);
    },

    //Update menu nutrition
    //-> (void)
    nutrition_update: function()
    {
        var nutrition, recipes, quickmeals, recipe, quickmeal, quickmeal_nut;
        for(var i = 0, imax = this.plan.length; i < imax; i++)
        {
            //RECIPES
            //Init nutrition array with zeros at each nutritional value
            nutrition = NUT_VALUES.map(function(){return 0;});

            //Loop through recipes
            recipes = this.plan[i].recipes_get();
            for(var j = 0, jmax = recipes.length; j < jmax; j++)
            {
                //Retrieve current recipe
                recipe = Recipes.get(recipes[j]);
                if(recipe)
                {
                    //Loop through selected nutrition values
                    for(var k = 0, kmax = NUT_VALUES.length; k < kmax; k++)
                    {
                        nutrition[k] += recipe.nutrition[k];
                    }
                }
            }
            this.plan[i].nutrition.recipes = nutrition;

            //QUICK MEALS
            //Init nutrition array with zeros at each nutritional value
            nutrition = NUT_VALUES.map(function(){return 0;});

            //Loop through quick meals
            quickmeals = this.plan[i].quickmeals_get();
            for(j = 0, jmax = quickmeals.length; j < jmax; j++)
            {
                quickmeal = Quickmeals.get(quickmeals[j]);
                if(quickmeal)
                {
                    quickmeal_nut = quickmeal.getNutrition();
                    for(k = 0, kmax = NUT_VALUES.length; k < kmax; k++)
                    {
                        nutrition[k] += quickmeal_nut[k];
                    }
                }
            }
            this.plan[i].nutrition.quickmeals = nutrition;
        }
    },
    
    /*******************************************************
    QUICKMEALS
    ********************************************************/

    //Add a quick meal to a specific day
    //#day (int):           day index
    //#quickmeal_id (int):  unique quick meal ID
    //-> (void)
    quickmeal_add: function(day, quickmeal_id)
    {
        var day_index = MENU_DAYS_PAST + day;
        if(this.plan[day_index].quickmeals.length < MENU_QUICKMEALS_MAX)
        {
            this.plan[day_index].quickmeal_add(quickmeal_id);
            this.update('quickmeals');
        }
        else 
            this.fire('alert', {code: 5});
    },

    //Remove a quick meal from a specific day or any day of the menu
    //#day (int/bool):      day index or false (remove from the whole menu)
    //#quickmeal_id (int):  unique quick meal ID
    //-> (void)
    quickmeal_delete: function(day, quickmeal_id)
    {
        var day_index = MENU_DAYS_PAST + day;
        if(day === false)   
            this.plan.invoke('quickmeal_delete', quickmeal_id);
        else                
            this.plan[day_index].quickmeal_delete(quickmeal_id);
        this.update('quickmeals');
    },

    //Return quick meals of specified period
    //Both start and end days are included in the search
    //#day_start (int): index of day to start from (optional, defaults to first day of the menu)
    //#day_end (int):   index of day on which to stop (optional, defaults to last day of the menu)
    //->quickmeals (array): list of quick meals IDs
    quickmeals_get: function(day_start, day_end)
    {
        if(typeof(day_start) == 'undefined' || day_start < 0 - MENU_DAYS_PAST)  
            day_start = 0 - MENU_DAYS_PAST;
        if(typeof(day_end) == 'undefined' || day_end > MENU_DAYS_FUTURE - 1)   
            day_end = MENU_DAYS_FUTURE - 1;

        //Loop through the menu
        var quickmeals = [], day_index;
        for(var i = day_start, imax = day_end + 1; i < imax; i++)
        {
            day_index   = MENU_DAYS_PAST + i;
            quickmeals  = quickmeals.concat(this.plan[day_index].quickmeals_get());
        }
        return quickmeals;
    },

    /*******************************************************
    RECIPES
    ********************************************************/

    //Add a recipe to a specific day and meal
    //#day (int):       index of the day on which to add the recipe
    //#meal (int):      index of the meal
    //#recipe_id (int): unique ID of the recipe
    //#guests (int):    number of guests (optional)
    //-> (void)
    recipe_add: function(day, meal, recipe_id, guests)
    {
        var day_index = MENU_DAYS_PAST + day;
        if(!Recipes.exist(recipe_id)) return;
        if(typeof(guests) == 'undefined')
            guests = Recipes.get(recipe_id, 'guests');

        //Store recipe in menu
        this.plan[day_index].recipe_add(meal, recipe_id, guests);
        this.update('recipes');
    },

    //Return a day index for each recipe of the menu
    //#day_start (int): index of day to start from (optional, defaults to first day of the menu)
    //#day_end (int):   index of day on which to stop (optional, defaults to last day of the menu)
    //->recipes_days (array): list of days indexes
    recipes_days_get: function(day_start, day_end)
    {
        if(typeof(day_start) == 'undefined' || day_start < 0 - MENU_DAYS_PAST)  
            day_start = 0 - MENU_DAYS_PAST;
        if(typeof(day_end) == 'undefined' || day_end > MENU_DAYS_FUTURE - 1)   
            day_end = MENU_DAYS_FUTURE - 1;

        //Loop through the menu
        var recipes_days = [], day_index, day_recipes;
        for(var i = day_start, imax = day_end + 1; i < imax; i++)
        {
            day_index    = MENU_DAYS_PAST + i;
            day_recipes  = this.plan[day_index].recipes_get();
            recipes_days = recipes_days.concat(day_recipes.map(function(){return i;}));
        }
        return recipes_days;
    },

    //Remove a recipe from a specific meal
    //#day (int): index of the day on which to remove the recipe
    //#meal (int): index of the meal
    //-> (void)
    recipe_delete: function(day, meal)
    {
        var day_index = MENU_DAYS_PAST + day;
        this.plan[day_index].recipe_delete(meal);
        this.update('recipes');
    },

    //Get current recipe for specific day and meal
    //#day (int): index of the day on which to remove the recipe
    //#meal (int): index of the meal
    //->recipe_id (int): ID of the recipe
    recipe_get: function(day, meal)
    {
        var day_index = MENU_DAYS_PAST + day;
        return this.plan[day_index].recipe_get(meal);
    },

    //Compute the number of recipes of each category during the specified period
    //#day_start (int): index of day to start from (optional, defaults to first day of the menu)
    //#day_end (int): index of day on which to stop (optional, defaults to last day of the menu)
    //->categories (array): count of recipes indexed by category ID
    recipes_categorize: function(day_start, day_end)
    {
        if(typeof(day_start) == 'undefined' || day_start < 0 - MENU_DAYS_PAST)  
            day_start = 0 - MENU_DAYS_PAST;
        if(typeof(day_end) == 'undefined' || day_end > MENU_DAYS_FUTURE - 1)    
            day_end = MENU_DAYS_FUTURE - 1;

        var categories = RECIPES_CATEGORIES.map(function(){return 0;}),
            recipes = this.recipes_get(day_start, day_end), recipe;
        recipes.each(function(recipe_id)
        {
            recipe = Recipes.get(recipe_id);
            if(recipe)
            {
                categories[0]++;
                categories[recipe.category]++;
            }
        });

        return categories;
    },

    //Remove all recipes among provided ids from the menu
    //Usefull if a recipe has been deleted from database and need to be removed
    //#recipes_ids (array): IDs of the recipes to remove
    //-> (void)
    recipes_delete: function(recipes_ids)
    {
        if(!recipes_ids.length) return;

        this.plan.invoke('recipes_delete', recipes_ids);
        this.update('recipes');
    },

    //Return all recipes currently in the menu or only those between specified days
    //Both start and end days are included in the search
    //#day_start (int): index of day to start from (optional, defaults to first day of the menu)
    //#day_end (int): index of day on which to stop (optional, defaults to last day of the menu)
    //->recipes (array): list of recipe IDs
    recipes_get: function(day_start, day_end)
    {
        if(typeof(day_start) == 'undefined' || day_start < 0 - MENU_DAYS_PAST)  
            day_start = 0 - MENU_DAYS_PAST;
        if(typeof(day_end) == 'undefined' || day_end > MENU_DAYS_FUTURE - 1)    
            day_end = MENU_DAYS_FUTURE - 1;

        //Loop through the menu
        var recipes = [], day_index, day_recipes = [];
        for(var i = day_start, imax = day_end + 1; i < imax; i++)
        {
            day_index   = MENU_DAYS_PAST + i;
            day_recipes = this.plan[day_index].recipes_get();
            day_recipes.each(function(recipe_id)
            {
                if(recipe_id && Recipes.exist(recipe_id)) 
                    recipes.push(recipe_id);
            });
        }
        return recipes;
    },

    /*******************************************************
    SHOPPING
    ********************************************************/

    //Check if there is no shopping day in the future
    //Create a default one if required
    //->created (bool): true if a default shopping day was created
    shopping_days_check: function()
    {
        if(this.shopping_days_get(0).length) 
            return false;
        else
        {
            this.shopping_default();
            return true;
        }
    },

    //Get all shopping days between day_start and day_end
    //#day_start (int): index of the first day of the period
    //#day_end (int):   index of the last day of the period
    //->shopping_days (array): list of shopping days and times
    shopping_days_get: function(day_start, day_end)
    {
        if(typeof(day_start) == 'undefined' || day_start < 0 - MENU_DAYS_PAST)  
            day_start = 0 - MENU_DAYS_PAST;
        if(typeof(day_end) == 'undefined' || day_end > MENU_DAYS_FUTURE - 1)    
            day_end = MENU_DAYS_FUTURE - 1;

        //Loop through the menu
        var shopping_days = [], day_index, status;
        for(var i = day_start, imax = day_end + 1; i < imax; i++)
        {
            day_index = MENU_DAYS_PAST + i;
            status    = this.plan[day_index].shopping_status_get();
            if(status != SHOPPING_STATUS_NONE)
                shopping_days.push({'day': i, 'time': status});
        }
        return shopping_days;
    },

    //Create default shopping day
    //(today or the day after the most recent recipe of the old menu)
    //-> (void)
    shopping_default: function()
    {
        var default_day = MENU_DAYS_PAST + Math.max(0, this.MENU_OLD_END + 1);
        this.plan[default_day].shopping_status_set(SHOPPING_STATUS_MORNING);
    },
    
    //Get shopping list of provided day
    //#day (int): shopping day index
    //->shopping_list (object/bool): shopping list object (false if no shopping is set)
    shopping_list_get: function(day)
    {
        var day_index = MENU_DAYS_PAST + day,
            status    = this.shopping_status_get(day);
        return status ? this.plan[day_index].shopping : false;
    },

    //Compute period covered by a given shopping day
    //#day (int): day for which to compute the period
    //->period (object/bool): structure with start/stop indexes or false if there is no shopping
    shopping_period: function(day)
    {
        var shopping_status = this.shopping_status_get(day);
        if(shopping_status == SHOPPING_STATUS_NONE) return false;

        //If shopping is done in the evening shopping period starts from the next day
        var start_day = day;
        if(shopping_status == SHOPPING_STATUS_EVENING) start_day++;

        //Retrieve next shopping day (default to last day of the menu, evening)
        var next_shopping_day   = MENU_DAYS_FUTURE - 1;
        var next_shopping_time  = SHOPPING_STATUS_EVENING;

        //Check if there are shopping days after current one
        var future_shopping_days = this.shopping_days_get(day + 1);
        if(future_shopping_days.length)
        {
            //Retrieve following shopping day information
            next_shopping_day   = future_shopping_days[0].day;
            next_shopping_time  = future_shopping_days[0].time;
        }

        //If next shopping is done in the morning, we end current shopping the day before
        var end_day = next_shopping_day;
        if(next_shopping_time == SHOPPING_STATUS_MORNING) end_day--;

        //End box cannot preceed start box
        if(end_day < start_day) end_day = start_day;

        //Return shopping interval
        return {'start': start_day, 'stop': end_day};
    },

    //Return friends with which shopping of provided day has been shared
    //#day (int): day index
    //->shared (array): list of users IDs the shopping list is shared with
    shopping_shared_get: function(day)
    {
        var day_index = MENU_DAYS_PAST + day;
        return this.plan[day_index].shopping.shared_get();
    },

    //Get shopping status of provided day
    //#day (int): day index
    //->status (int): shopping status
    shopping_status_get: function(day)
    {
        var day_index = MENU_DAYS_PAST + day;
        return this.plan[day_index].shopping_status_get();
    },

    //Increase shopping status for provided day
    //#day (int): day index
    //-> (void)
    shopping_status_rotate: function(day)
    {
        var status = this.shopping_status_get(day);
        switch(status)
        {
            case SHOPPING_STATUS_EVENING:
                status = SHOPPING_STATUS_MORNING;
                break;
            case SHOPPING_STATUS_MORNING:
                status = SHOPPING_STATUS_NONE;
                break;
            case SHOPPING_STATUS_NONE:
                status = SHOPPING_STATUS_EVENING;
                break;
        }
        this.shopping_status_set(day, status);
    },
   
    //Set shopping status for specific day
    //#day (int): day index
    //#status (int): shopping status
    //-> (void)
    shopping_status_set: function(day, status)
    {
        var day_index = MENU_DAYS_PAST + day;
        var shopping_days = this.shopping_days_get(0);
        if(day < 0 || status != SHOPPING_STATUS_NONE || shopping_days.length > 1)
        {
            this.plan[day_index].shopping_status_set(status);
			//Reset MENU_OLD_END value if canceled shopping day is menu old last shopping day
			if(day == this.MENU_OLD_SHOPPING) 
                this.MENU_OLD_END = -1;
            //Trigger update
            this.update('shopping');
        }
        //Shopping status cannot be modified
        else 
            this.fire('alert', {'code': 4});
    },

    //Update menu shopping content
    //#limit (object): "start" and "stop" day values for computation
    //-> (void)
    shopping_update: function(limit)
    {
        //Loop through shopping days
        var shopping_days = this.shopping_days_get(limit.start, limit.stop),
            day_index, menu_index, period, recipes, guests, days;
        for(var i = 0, imax = shopping_days.length; i < imax; i++)
        {
            day_index  = shopping_days[i].day;
            menu_index = MENU_DAYS_PAST + day_index;

            //Retrieve recipes, guests and recipes days for shopping list period
            period  = this.shopping_period(day_index);
            recipes = this.recipes_get(period.start, period.stop);
            guests  = this.guests_get(period.start, period.stop);
            days    = this.recipes_days_get(period.start, period.stop);

            //Update shopping list
            this.plan[menu_index].shopping.ingredients_compute(recipes, guests, days);
        }

        //Update stocked params for ingredients in shopping lists
        this.stockUpdate();
    },

    /*******************************************************
    STOCK
    ********************************************************/

    //Called when the content of the fridge changes
    //Check which ingredients of the shopping lists are in stock
    //-> (void)
    stockUpdate: function()
    {
        for(var i = 0, imax = this.plan.length; i < imax; i++)
        {
            this.plan[i].stockUpdate();
        }
    }
});

//Represents a day of the menu
var MenuDay = Class.create(
{
    object_name: 'menu_day',

    /*******************************************************
    CONSTRUCTOR
    ********************************************************/

    //Class constructor
    //#day_index (int): menu day index
    //-> (void)
    initialize: function(day_index)
    {
        this.day  = day_index;
        this.date = new DateTime(day_index);
        this.nutrition  =
        {
            'recipes':      NUT_VALUES.map(function(){return 0;}),
            'quickmeals':   NUT_VALUES.map(function(){return 0;})
        };
        this.quickmeals = [];
        this.recipes    =
        {
            'ids'   :   $A($R(0, MENU_MEALS_COUNT - 1)).map(function(){return 0;}),
            'guests':   $A($R(0, MENU_MEALS_COUNT - 1)).map(function(){return MENU_GUESTS_DEFAULT;})
        };
        this.shopping        = new ShoppingList(this.day);
        this.shopping_status = SHOPPING_STATUS_NONE;
    },
    
    /*******************************************************
    GUESTS
    ********************************************************/

    //Return the number of guests for a specific meal or all meals of the day WHICH HAVE A RECIPE
    //#meal (int): meal index (optional)
    //->guests (int/array): guests count for specific meal or list of guests per meal
    guests_get: function(meal)
    {
        if(typeof(meal) == 'undefined')
        {
            var guests = [];
            this.recipes.ids.each(function(id, pos)
            {
                if(id) guests.push(this.recipes.guests[pos]);
            }, this);
            return guests;
        }
        else 
            return this.recipes.guests[meal];
    },

    //Set number of guests for specific meal
    //#meal (int): index of the meal
    //#guests (int): number of guests
    //-> (void)
    guests_set: function(meal, guests)
    {
        this.recipes.guests[meal] = guests;
    },

    /*******************************************************
    NUTRITION
    ********************************************************/

    //Get current day nutrition values
    //#type (string): whether to return "recipes" or "quickmeals" nutrition
    //->nutrition (array): list of nutritional values indexed by ID
    nutrition_get: function(type)
    {
        return type == 'quickmeals' ? this.nutrition.quickmeals.slice() : this.nutrition.recipes.slice();
    },

    /*******************************************************
    QUICKMEALS
    ********************************************************/

    //Add a quick meal to current day
    //#quickmeal_id (int): ID of the quickmeal
    //-> (void)
    quickmeal_add: function(quickmeal_id)
    {
        this.quickmeals.push(quickmeal_id);
        this.quickmeals = this.quickmeals.uniq();
    },

    //Remove quick meal from current day
    //#quickmeal_id (int): ID of the quickmeal
    //-> (void)
    quickmeal_delete: function(quickmeal_id)
    {
        for(var i = 0, imax = this.quickmeals.length; i < imax; i++)
        {
            if(this.quickmeals[i] == quickmeal_id)
            {
                this.quickmeals.splice(i, 1);
                i--; imax--;
            }
        }
    },

    //Return quick meals of the day
    //->quickmeals (array): list of quick meals IDs
    quickmeals_get: function()
    {
        return this.quickmeals.slice();
    },

    //Export quick meals content
    //->quickmeals (array): list of quick meals IDs
    quickmeals_export: function()
    {
        return this.quickmeals.length ? this.quickmeals.slice() : 0;
    },

    //Import quick meals data from server
    //#quickmeals (array): list of quick meals IDs
    quickmeals_import: function(quickmeals)
    {
        this.quickmeals = quickmeals.map(function(id){return parseInt(id);});
    },

    /*******************************************************
    RECIPES
    ********************************************************/

    //Add a recipe to a specific meal of current day
    //#meal (int): meal index
    //#recipe_id (int): unique recipe ID
    //#guests (int): number of guests (optional)
    //-> (void)
    recipe_add: function(meal, recipe_id, guests)
    {
        this.recipes.ids[meal]    = recipe_id;
        this.recipes.guests[meal] = guests ? guests : MENU_GUESTS_DEFAULT;
    },

    //Remove recipe from a specific meal of current day
    //#meal (int): meal index
    //-> (void)
    recipe_delete: function(meal)
    {
        this.recipes.ids[meal] = 0;
        this.recipes.guests[meal] = MENU_GUESTS_DEFAULT;
    },

    //Get recipe from a specific meal
    //#meal (int): meal index
    //->recipe_id (int): ID of the recipe
    recipe_get: function(meal)
    {
        return this.recipes.ids[meal];
    },

    //Remove recipes among provided IDs
    //#recipes_ids (array): list of recipe IDs to remove
    //-> (void)
    recipes_delete: function(recipes_ids)
    {
        var recipe_id = 0;
        for(var i = 0, imax = this.recipes.ids.length; i < imax; i++)
        {
            recipe_id = this.recipes.ids[i];
            for(var j = 0, jmax = recipes_ids.length; j < jmax; j++)
            {
                if(recipes_ids[j] == recipe_id) 
                    this.recipe_delete(i);
            }
        }
    },

    //Export recipes content
    //->recipes (object): arrays containing recipe IDs and guests
    recipes_export: function()
    {
        if(this.recipes.ids.any(function(id){return id > 0}))
            return {'i': this.recipes.ids.slice(), 'g': this.recipes.guests.slice()};
        else 
            return 0;
    },

    //Return all recipes from current day
    //->recipes_ids (array): list of recipe IDs
    recipes_get: function()
    {
        return this.recipes.ids.without(0);
    },

    //Import recipes from server
    //#recipes (object): recipes data
    //-> (void)
    recipes_import: function(recipes)
    {
        var recipe_id;
        for(var i = 0, imax = recipes.i.length; i < imax; i++)
        {
            recipe_id = recipes.i[i];
            if(Recipes.exist(recipe_id))
            {
                this.recipes.ids[i]    = recipe_id;
                this.recipes.guests[i] = recipes.g[i];
            }
        }
    },

    /*******************************************************
    SHOPPING
    ********************************************************/

    //Export shopping content of current day
    //->shopping (object): compact shopping structure
    shopping_export: function()
    {
        if(this.shopping_status != SHOPPING_STATUS_NONE)
        {
            var shopping = this.shopping.export_content();
            if(shopping)
            {
                shopping.s = this.shopping_status;
                return shopping;
            }
            else return 0;
        }
        else return 0;
    },

    //Get shopping status of current day
    //->status (int): shopping status
    shopping_status_get: function()
    {
        return this.shopping_status;
    },

    //Set shopping status for current day
    //#status (int): shopping status
    //-> (void)
    shopping_status_set: function(status)
    {
        this.shopping_status = status;
        //For past days, empty shopping content upon cancellation
		if(this.day < 0 && status == SHOPPING_STATUS_NONE)
            this.shopping.clear();
    },

    /*******************************************************
    STOCK
    ********************************************************/

    //Called when the content of the fridge changes
    //Check which ingredients of the shopping list are in stock
    //-> (void)
    stockUpdate: function()
    {
        this.shopping.ingredients.each(function(IngQty)
        {
            IngQty.stocked = User.fridge.contains(IngQty.id);
        });
    }
});