/*******************************************************
Title: Fridge
Authors: Kookiiz Team
Purpose: Fridge user interface class
********************************************************/

//Represents a user interface for the fridge content
var FridgeUI = Class.create(
{
    object_name: 'fridge_ui',

    /*******************************************************
    CONSTANTS
    ********************************************************/

    INGREDIENT_CHARS: 20,   //Number of chars for ingredients display

    /*******************************************************
    CONSTRUCTOR
    ********************************************************/

    //Class constructor
    //-> (void)
    initialize: function()
    {
        //DOM nodes
        this.$content       = $('fridge_content');
        this.$input         = $('fridge_input');
        this.$buttonSearch  = $('fridge_search');
        this.$selectSearch  = $('select_search_fridge');
    },

    /*******************************************************
    INIT
    ********************************************************/

    //Start dynamic fridge UI functionalities
    //-> (void)
    init: function()
    {
        //Observers
        $('fridge_ingredient_add').observe('click', this.onIngredientAdd.bind(this));
        this.$buttonSearch.observe('click', this.onRecipeSearch.bind(this));

        //Autocompleter
        Ingredients.autocompleter_init(this.$input, this.onIngredientSelect.bind(this));
    },

    /*******************************************************
    DISPLAY
    ********************************************************/

    //Display fridge content
    //-> (void)
    display: function()
    {
        //Clear fridge content
        var container = this.$content.clean();

        //Build fridge ingredients list
        var list = User.fridge.build(
        {
            'deletable':    true,
            'iconized':     true,
            'sorting':      'name',
            'text_max':     this.INGREDIENT_CHARS
        });

        //Fridge list is empty
        if(list.empty())
        {
            //Display notification
            container.innerHTML = FRIDGE_ALERTS[0];

            //Disable fridge search controls
            Kookiiz.recipes.search_option_disable('fridge');
            this.$buttonSearch.hide();
        }
        //Fridge list contains something
        else
        {
            //Loop through fridge table rows
            var fridge_items = list.select('li');
            for(var i = 0, imax = fridge_items.length; i < imax; i++)
            {
                //Add a spacer between ingredients
                var spacer = new Element('li', {'class': 'spacer'});
                if(fridge_items[i + 1]) 
                    list.insertBefore(spacer, fridge_items[i + 1]);
                else                    
                    list.appendChild(spacer);
            }

            //Append list to fridge container
            container.appendChild(list)

            //Enable fridge search controls
            Kookiiz.recipes.search_option_enable('fridge');
            this.$buttonSearch.show();
        }
    },

    //Display fridge search options
    //-> (void)
    display_search: function()
    {
        //Remove current options except defaults
        var container = this.$selectSearch;
        while(container.childElements().length > 2)
        {
            container.removeChild(container.lastChild);
        }

        //Add an option for each fridge ingredient
        var option = null;
        User.fridge.sort('name');
        User.fridge.each(function(ingredient)
        {
            option = new Element('option');
            option.value = ingredient.id;
            option.innerHTML = Ingredients.get(ingredient.id).name;
            container.appendChild(option);
        });
    },
    
    /*******************************************************
    INGREDIENTS
    ********************************************************/

    //Add a new ingredient in the fridge
    //#ingredient (object): ingredient object to add
    //-> (void)
    ingredient_add: function(ingredient)
    {
        //Check that fridge is not full yet
        if(User.fridge.count() < FRIDGE_MAX)
        {            
            //Check if fridge already contains this ingredient
            if(User.fridge.contains(ingredient.id))
            {
                Kookiiz.popup.alert({'text': FRIDGE_ALERTS[3]});
                return;
            }
            //Add ingredient quantity to fridge content
            User.fridge.quantity_add(new IngredientQuantity(ingredient.id, FRIDGE_QTY_DEFAULT, FRIDGE_UNIT_DEFAULT));
        }
        else 
            Kookiiz.popup.alert({'text': FRIDGE_ALERTS[1]});

        //Clear user input
        this.input_clear();
    },

    /*******************************************************
    INPUT
    ********************************************************/

    //Clear fridge input fields
    //-> (void)
    input_clear: function()
    {
        this.$input.value = '';
    },
    
    /*******************************************************
    OBSERVERS
    ********************************************************/
   
    //Called when ingredient adding button is clicked
    //#event (event): DOM click event
    //-> (void)
    onIngredientAdd: function()
    {
        var ingredient = Ingredients.search(this.$input.value.stripTags());
        if(ingredient)  
            this.ingredient_add(ingredient);
        else            
            Kookiiz.popup.alert({'text': FRIDGE_ALERTS[2]});
    },
    
    //Called upon selection of a suggestion in the autocompleter list
    //#ingredient (object): corresponding ingredient object
    onIngredientSelect: function(ingredient)
    {
        this.ingredient_add(ingredient);
    },
    
    //Throw fridge recipe search
    //-> (void)
    onRecipeSearch: function()
    {
        //Show main tab
        Kookiiz.tabs.show('main');

        //Set criteria
        var criteria = Kookiiz.recipes.search_criteria(true);
        criteria.fridge = -1;

        //Throw a new recipe search
        Kookiiz.recipes.search_reset();
        Kookiiz.recipes.search(criteria);
    },

    /*******************************************************
    UPDATE
    ********************************************************/

    //Update fridge UI when fridge content changes
    //-> (void)
    update: function()
    {
        this.display();
        this.display_search();
    }
});