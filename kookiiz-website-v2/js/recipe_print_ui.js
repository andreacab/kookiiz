/*******************************************************
Title: Recipe print UI
Authors: Kookiiz Team
Purpose: Provide functionalities to print a recipe sheet
********************************************************/

//Represents a user interface for recipe printing
var RecipePrintUI = Class.create(
{
    object_name: 'recipe_print_ui',

	/*******************************************************
    CONSTRUCTOR
    ********************************************************/

    //Class constructor
    //#recipe_id (int): recipe_id for which the UI is set up
    //-> (void)
	initialize: function(recipe_id)
    {
        this.recipe_id = recipe_id;

        //DOM elements
        this.$comments = $('recipe_print_comments');
    },

    /*******************************************************
    INIT
    ********************************************************/

    //Init dynamic functionalities
    //-> (void)
    init: function()
    {
        //Observers
        var options = $$('.print_option'), check_box;
        for(var i = 0, imax = options.length; i < imax; i++)
        {
            check_box = options[i].select('input')[0];
            check_box.checked = true;
            check_box.observe('click', this.onOptionCheck.bind(this));
        }
        $('print_button').observe('click', this.onPrint.bind(this));
    },

    /*******************************************************
    COMMENTS
    ********************************************************/

    //Display printable recipe comments
    //#comments (array): list of comment objects
    //-> (void)
    comments_display: function(comments)
    {
        this.$comments.clean();

        //Loop through comments
        var comments_list = new Element('ul'),
            comment, comment_item, comment_date, comment_text;
        for(var i = 0, imax = comments.length; i < imax; i++)
        {
            //Build element
            comment = comments[i];
            comment_item = new Element('li');
            comment_date = new Element('p', {'class': 'italic'});
            comment_text = new Element('p', {'class': 'text'});
            comment_item.appendChild(comment_date);
            comment_item.appendChild(comment_text);
            comments_list.appendChild(comment_item);

            //Date & Text
            comment_date.innerHTML = VARIOUS[3] + ' ' + comment.date + ' ' + VARIOUS[4] + ' ' + comment.time;
            comment_text.innerHTML = comment.text;
        }

        //Display list or notification
        if(comments_list.empty())
            this.$comments.innerHTML = COMMENTS_ALERTS[4];
        else
            this.$comments.appendChild(comments_list);
    },

    //Load private comments for the recipe
    //-> (void)
    comments_load: function()
    {
        $('recipe_print_comments').loading();
        Kookiiz.comments.load('recipe', this.recipe_id, COMMENT_TYPE_PRIVATE, 0, 0, this.comments_parse.bind(this));
    },

    //Parse recipe comments fetched from server
    //#response (object): server response object
    //-> (void)
    comments_parse: function(response)
    {
        var comments = Kookiiz.comments.fetch(response.content, 'recipe', this.recipe_id);
        this.comments_display(comments);
    },

    /*******************************************************
    DISPLAY
    ********************************************************/

    //Display printable recipe
    //#recipe_id (int): ID of the recipe
    //-> (void)
    display: function(recipe_id)
    {
        var recipe = Recipes.fetch(recipe_id, this.onRecipeLoaded.bind(this));
        if(!recipe) return;

        //Set window title
        document.title = recipe.name + ' (' + RECIPE_PRINT_TEXT[6] + ')';

        //DOM elements
        var container   = $('recipe_print');
        var title       = container.select('.display.title')[0];
        var rating      = container.select('.display.rating')[0];
        var picture     = container.select('.display.picture')[0];
        var icons       = container.select('.display.icons')[0];
        var price       = container.select('.display.price')[0];
        var author      = container.select('.display.author')[0];
        var category    = container.select('.display.category')[0];
        var origin      = container.select('.display.origin')[0];
        var guests      = container.select('.display.guests')[0];
        var preparation = container.select('.display.preparation')[0];
        var cooking     = container.select('.display.cooking')[0];
        var level       = container.select('.display.level')[0];
        var description = container.select('.display.description')[0];
        var ingredients = container.select('.display.ingredients')[0];
        var nutrition   = container.select('.display.nutrition')[0];

        //Display recipe properties
        title.innerHTML         = recipe.name;
        picture.src             = '/pics/recipes-' + recipe.pic_id;
        price.innerHTML         = Math.round(recipe.price);
        author.innerHTML        = VARIOUS[11] + ' ' + recipe.author_name;
        category.innerHTML      = RECIPES_CATEGORIES[recipe.category];
        origin.innerHTML        = RECIPES_ORIGINS[recipe.origin];
        guests.innerHTML        = recipe.guests + ' ' + RECIPE_DISPLAY_TEXT[18];
        preparation.innerHTML   = recipe.preparation ? recipe.preparation + ' ' + VARIOUS[6] : '-';
        cooking.innerHTML       = recipe.cooking ? recipe.cooking + ' ' + VARIOUS[6] : '-';
        level.innerHTML         = RECIPES_LEVELS[recipe.level];
        description.innerHTML   = recipe.description.linefeed_replace();
        //Sub functions
        Kookiiz.recipes.display_icons(icons, recipe, 'big');
        Kookiiz.recipes.display_ingredients(ingredients, recipe.ingredients);
        Kookiiz.recipes.display_rating(rating, recipe, false, true);

        //Nutrition
        Kookiiz.health.nutritionDisplay(nutrition,
        {
            'needs':    User.needs_get(),
            'recipes':  recipe.nutrition,
            'values':   MENU_NUTRITION_VALUES
        },
        {'full': false});

        //Replace CSS sprites
        Utilities.sprites_replace('img.icon25');
        Utilities.sprites_replace('.rating_static');
    },
    
    /*******************************************************
    OBSERVERS
    ********************************************************/
   
    //Deal with clicks on printing options
    //#event (event): DOM click event
    //-> (void)
    onOptionCheck: function(event)
    {
        var option      = event.findElement('li');
        var checkbox    = option.select('input')[0];
        var option_name = checkbox.id.split('_')[1];
        switch(option_name)
        {
            case 'comments':
                checkbox.checked ? $('recipe_comments_module').show() : $('recipe_comments_module').hide();
                break;
            case 'nutrition':
                checkbox.checked ? $('recipe_print_nutrition').show() : $('recipe_print_nutrition').hide();
                break;
            case 'picture':
                checkbox.checked ? $('recipe_print_picture').show() : $('recipe_print_picture').hide();
                break;
            case 'wines':
                //checkbox.checked ? $('recipe_print_wines').show() : $('recipe_print_wines').hide();
                //break;
            default:
                return;
        }
    },
    
    //Callback on print button
    //-> (void)
    onPrint: function()
    {
        window.print();
    },
    
    //Callback for recipe download process
    //-> (void)
    onRecipeLoaded: function()
    {
        if(Recipes.exist(this.recipe_id))
            this.display(this.recipe_id);
        else
        {
            Kookiiz.popup.alert({'text': RECIPES_ERRORS[0]});
            window.close();
        }
    },

    /*******************************************************
    UPDATE
    ********************************************************/

    //Update printing display
    //-> (void)
    update: function()
    {
        this.comments_load();
        this.display(this.recipe_id);
    }
});