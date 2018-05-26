/*******************************************************
Title: Recipe form
Authors: Kookiiz Team
Purpose: Class to handle recipe form functionalities
********************************************************/

//Represents a handler for recipe form functionalities
var RecipeForm = Class.create(
{
    object_name: 'recipe_form',

    /*******************************************************
    CONSTRUCTOR
    ********************************************************/

    //Class constructor
    //-> (void)
    initialize: function()
    {
        this.action = 'create'; //Current form action
        this.loaded = false;    //Has recipe form been loaded?
        this.recipe = null;     //Recipe object beeing edited

        //DOM elements
        this.$container = $('section_recipe_form').select('.section_content')[0];
    },

    /*******************************************************
    CHECK
    ********************************************************/

    //Check if all recipe fields are properly filled
    //->valid (bool): true if all recipe fields are properly filled
    check: function()
    {
        //Title
        var title_input = $('input_recipeform_title');
        if(!this.recipe.name || this.recipe.name == title_input.title)
        {
            Kookiiz.popup.alert({'text': RECIPEFORM_ALERTS[12]});
            return false;
        }
        //Timing
        if(!this.recipe.preparation || isNaN(this.recipe.preparation))
        {
            Kookiiz.popup.alert({'text': RECIPEFORM_ALERTS[13]});
            return false;
        }
        if(isNaN(this.recipe.cooking))
        {
            Kookiiz.popup.alert({'text': RECIPEFORM_ALERTS[14]});
            return false;
        }
        //Description
        if(!this.recipe.description)
        {
            Kookiiz.popup.alert({'text': RECIPEFORM_ALERTS[15]});
            return false;
        }
        //Ingredients
        if(this.recipe.ingredients.count() < RECIPE_ING_MIN)
        {
            Kookiiz.popup.alert({'text': RECIPEFORM_ALERTS[16] + RECIPE_ING_MIN + ' ' + RECIPEFORM_ALERTS[17]});
            return false;
        }
        return true;
    },

    /*******************************************************
    CLEAN UP
    ********************************************************/

    //Clean up recipe form temporary data
    //-> (void)
    cleanup: function()
    {
        if(this.loaded && this.recipe)
        {
            this.recipe = null;
            this.loaded = false;
        }
    },
    
    /*******************************************************
    DESCRIPTION
    ********************************************************/
   
    //Callback for click on the button to add a new preparation step
    //-> (void)
    descriptionAdd: function()
    {
        var container = $('recipeform_description'),
            steps     = container.childElements();

        if(steps.length < RECIPE_STEPS_MAX)
        {
            //Retrieve text of new step
            var description_text  = this.$descInput.value.stripTags();
            //Check the length of this step
            if(description_text.length < RECIPE_DESCRIPTION_MIN)        
                Kookiiz.popup.alert({'text': RECIPEFORM_ALERTS[0]});
            else if(description_text.length > RECIPE_DESCRIPTION_MAX)   
                Kookiiz.popup.alert({'text': RECIPEFORM_ALERTS[1]});
            else
            {
                //Create new preparation step
                var step      = new Element('div', {'class': 'step'}),
                    header    = new Element('div', {'class': 'header'}),
                    content   = new Element('div', {'class': 'content'}),
                    footer    = new Element('div', {'class': 'footer'}),
                    text      = new Element('p'),
                    actions   = new Element('p', {'class': 'right'}),
                    stepText  = new Element('span', {'class': 'text'}),
                    stepInput = new Element('textarea');
                text.appendChild(stepText);
                text.appendChild(stepInput);
                content.appendChild(text);
                content.appendChild(actions);
                step.appendChild(header);
                step.appendChild(content);
                step.appendChild(footer);

                //Actual text and input
                stepText.innerHTML = (steps.length + 1) + '. ' + description_text;
                stepText.observe('click', this.onStepEdit.bind(this));
                stepInput.hide();

                //Actions icons
                var step_edit = new Element('img',
                {
                    'alt':      ACTIONS[25],
                    'class':    'icon15 click edit',
                    'src':      ICON_URL,
                    'title':    ACTIONS[25]
                });
                step_edit.observe('click', this.onStepEdit.bind(this));
                actions.appendChild(step_edit);
                var step_delete = new Element('img',
                {
                    'alt':      ACTIONS[15],
                    'class':    'button15 cancel',
                    'src':      ICON_URL,
                    'title':    RECIPEFORM_TEXT[1]
                });
                step_delete.observe('click', this.onStepDelete.bind(this));
                actions.appendChild(step_delete);

                //Append step to container
                container.appendChild(step);

                //Clear input and update chars left
                this.$descInput.value = '';
                this.$descInput.chars_limit(RECIPE_DESCRIPTION_MAX, 'recipeform_description_chars');

                //Update recipe description
                this.descriptionUpdate();
            }
        }
        //Too many description steps
        else 
            Kookiiz.popup.alert({'text': RECIPEFORM_ALERTS[3]});
    },
    
    //Display current recipe description as editable steps
    //-> (void)
    descriptionDisplay: function()
    {
        var container = $('recipeform_description').clean();
        
        //Loop through recipe description steps
        var steps = this.recipe.description.linefeed_replace().split('<br/><br/>'),
            step, header, content, footer, text, actions, stepText, stepInput;
        for(var i = 0, imax = steps.length; i < imax; i++)
        {
            step      = new Element('div', {'class': 'step'}),
            header    = new Element('div', {'class': 'header'}),
            content   = new Element('div', {'class': 'content'}),
            footer    = new Element('div', {'class': 'footer'}),
            text      = new Element('p'),
            actions   = new Element('p', {'class': 'right'}),
            stepText  = new Element('span', {'class': 'text'}),
            stepInput = new Element('textarea');
            text.appendChild(stepText);
            text.appendChild(stepInput);
            content.appendChild(text);
            content.appendChild(actions);
            step.appendChild(header);
            step.appendChild(content);
            step.appendChild(footer);
            
            //Actual text and input
            stepText.appendChild(document.createTextNode(steps[i]));
            stepText.observe('click', this.onStepEdit.bind(this));
            stepInput.hide();

            //Actions icons
            var step_edit = new Element('img',
            {
                'alt':      ACTIONS[25],
                'class':    'icon15 click edit',
                'src':      ICON_URL,
                'title':    ACTIONS[25]
            });
            step_edit.observe('click', this.onStepEdit.bind(this));
            actions.appendChild(step_edit);
            var step_delete = new Element('img',
            {
                'alt':      ACTIONS[15],
                'class':    'button15 cancel',
                'src':      ICON_URL,
                'title':    RECIPEFORM_TEXT[1]
            });
            step_delete.observe('click', this.onStepDelete.bind(this));
            actions.appendChild(step_delete);

            //Append step to container
            container.appendChild(step);
        }
    },
   
    //Called when description steps have been re-ordered
    //Number every step in the correct order
    //-> (void)
    descriptionOrder: function()
    {
        var steps = $('recipeform_description').select('.step'), element, text;
        for(var i = 0, imax = steps.length; i < imax; i++)
        {
            element = steps[i].select('.text')[0];
            text    = element.innerHTML;
            if(text.search(/[0-9]+.\s/) === 0)
                text = text.sub(/[0-9]+.\s/, '');
            element.innerHTML = (i + 1) + '. ' + text;
        }
        this.descriptionUpdate();
    },

    //Save current recipe description
    //-> (void)
    descriptionUpdate: function()
    {
        var description = '', steps = $('recipeform_description').select('.step');
        for(var i = 0, imax = steps.length; i < imax; i++)
        {
            //Append description step with line feeds
            description += steps[i].innerHTML.stripTags();
            if(i < imax - 1) 
                description += '\n\n';
        }
        this.recipe.description = description;
    },
    
    /*******************************************************
    DISPLAY
    ********************************************************/
   
    //Display current recipe content
    //-> (void)
    display: function()
    {
        this.pictureDisplay();
        this.propertiesDisplay();
        this.descriptionDisplay();
        this.ingredientsDisplay();
        this.nutritionUpdate();
        this.priceUpdate();
    },
    
    /*******************************************************
    GETTERS
    ********************************************************/
    
    //Return current recipe draft
    //->recipe (object): recipe object
    getRecipe: function()
    {
        return this.recipe;
    },
    
    //Return current recipe title
    //->title (string): current recipe title
    getTitle: function()
    {
        return $('span_recipeform_title').innerHTML.stripTags();
    },

    /*******************************************************
    INGREDIENTS
    ********************************************************/

    //Add a new ingredient quantity to the recipe
    //-> (void)
    ingredientsAdd: function()
    {
        //Check ingredients fields values
        var ing_id = this.ingredientsCheck();
        if(ing_id)
        {
            //Add ingredient to recipe
            var qty  = parseFloat($('input_recipeform_quantity').value),
                unit = parseInt($('select_recipeform_unit').value);
            this.recipe.ingredients.quantity_add(new IngredientQuantity(ing_id, qty, unit));

            //Reset fields and focus input
            this.ingredientsReset();
            $('input_recipeform_ingredient').target();
        }
    },

    //Check values of ingredient fields
    //->ingredient_id (int/bool): return either ingredient ID or false (if test failed)
    ingredientsCheck: function()
    {
        //Submitted values are stored.
        var ingredient_input = $('input_recipeform_ingredient'),
            quantity_input   = $('input_recipeform_quantity'),
            ingredient_name  = ingredient_input.value.stripTags(),
            ingredient_id    = Ingredients.search(ingredient_name, 'id'),
            quantity         = parseFloat(quantity_input.value);

        //Ingredient field is empty or contains HTML tags only
        if(!ingredient_name)
        {
            //Clear input and display alert
            ingredient_input.value = '';
            ingredient_input.unfreeze();
            Kookiiz.popup.alert({'text': RECIPEFORM_ALERTS[4]});
            return false;
        }
        //Ingredient name is not in database
        else if(ingredient_id <= 0)
        {
            //Display alert
            ingredient_input.unfreeze();
            Kookiiz.popup.alert({'text': RECIPEFORM_ALERTS[6]});
            return false;
        }

        //Check ingredient quantity
        if(!quantity)
        {
            Kookiiz.popup.alert({'text': INGREDIENTS_ALERTS[1]});
            return false;
        }

        //All fields are valid
        return ingredient_id;
    },

    //Display all ingredients that were added to the recipe
    //-> (void)
    ingredientsDisplay: function()
    {
        var container = $('p_recipeform_ingredients').clean();
        var list = this.recipe.ingredients.build(
        {
            'deletable':    true,
            'editable':     true,
            'quantified':   true,
            'text_max':     25,
            'units':        UNITS_SYSTEMS[User.option_get('units')]
        });
        if(list.empty()) 
            container.hide();
        else                
            container.show().appendChild(list);
    },

    //Clear recipe form ingredients fields
    //-> (void)
    ingredientsReset: function()
    {
        var input = $('input_recipeform_ingredient');
        input.value = input.title;
        $('input_recipeform_quantity').value = '';
        var unit_select = $('select_recipeform_unit');
        unit_select.childElements()[UNIT_NONE].show();
        unit_select.selectedIndex = UNIT_GRAMS;
    },

    //Called after ingredients modifications
    //-> (void)
    ingredientsUpdate: function()
    {
        this.ingredientsDisplay();
    },
    
    /*******************************************************
    INIT
    ********************************************************/

    //Init dynamic functionalities
    //-> (void)
    init: function()
    {
        //DOM nodes
        this.$picture   = $('recipeform_picture');
        this.$picDel    = $('recipeform_picture_delete');
        this.$langSel   = $('select_recipeform_lang');
        this.$title     = $('span_recipeform_title');
        this.$guestSel  = $('select_recipeform_guests');
        this.$catSel    = $('select_recipeform_category');
        this.$origSel   = $('select_recipeform_origin');
        this.$levelSel  = $('select_recipeform_level');
        this.$prepInput = $('input_recipeform_prep');
        this.$cookInput = $('input_recipeform_cook');
        this.$descInput = $('recipeform_description_input');
        this.$instruct  = $('recipeform_instructions');
        this.$partner   = $('recipeform_partner');
        this.$status    = $('recipeform_status');
        this.$reset     = $('recipeform_reset');
        this.$submit    = $('recipeform_submit');
        
        //Action-specific init
        if(this.action == 'edit')
        {
            this.display();
            this.$instruct.clean().appendChild(document.createTextNode(RECIPEFORM_TEXT[12]));
            this.$reset.clean().appendChild(document.createTextNode(ACTIONS[5]));
            this.$submit.clean().appendChild(document.createTextNode(ACTIONS[0]));
            this.$langSel.freeze();
            this.$status.hide();
            if(this.$partner) 
                this.$partner.hide();
        }
        else
       {
           this.$instruct.clean().appendChild(document.createTextNode(RECIPEFORM_TEXT[7]));
           this.$reset.clean().appendChild(document.createTextNode(ACTIONS[15]));
           this.$submit.clean().appendChild(document.createTextNode(ACTIONS[6]));
           this.$langSel.unfreeze();
           this.$status.show();
           if(this.$partner)
               this.$partner.show();
       }

        //Init values
        $('recipeform_description_chars').innerHTML = ' (' + RECIPE_DESCRIPTION_MAX + ' ' + VARIOUS[8] + ')';

        //Event observers
        $('img_recipeform_edit').observe('click', this.onTitleEdit.bind(this));
        this.$title.observe('click', this.onTitleEdit.bind(this));
        this.$guestSel.observe('change', this.onPropertyChange.bind(this));
        this.$catSel.observe('change', this.onPropertyChange.bind(this));
        this.$origSel.observe('change', this.onPropertyChange.bind(this));
        this.$levelSel.observe('change', this.onPropertyChange.bind(this));
        this.$prepInput.observe('keyup', this.onPropertyChange.bind(this));
        this.$cookInput.observe('keyup', this.onPropertyChange.bind(this));
        this.$descInput.observe('keyup', this.onDescriptionType.bind(this));
        $('recipeform_add_step').observe('click', this.descriptionAdd.bind(this));
        this.$picture.observe('click', this.onPictureUpload.bind(this));
        this.$picDel.observe('click', this.onPictureDelete.bind(this));
        $('recipeform_ingredient_add').observe('click', this.ingredientsAdd.bind(this));
        $('recipeform_ing_missing').observe('click', this.onIngredientMissing.bind(this));
        this.$reset.observe('click', this.onReset.bind(this));
        this.$submit.observe('click', this.submit.bind(this));

        //Input field events
        Utilities.observe_focus(this.$container, 'input.focus, textarea.focus');
        Utilities.observe_return('input_recipeform_quantity', this.onIngredientEnter.bind(this));
        Utilities.observe_return('input_recipeform_title', this.onTitleValidate.bind(this));

        //Local Ajax autocompleter
        Ingredients.autocompleter_init('input_recipeform_ingredient', this.onIngredientSelect.bind(this));
        
        //Update UI
        Kookiiz.health.nutritionUpdate();
        Kookiiz.tabs.loaded();
    },

    /*******************************************************
    LOAD
    ********************************************************/

    //Ask server for recipe form content
    //-> (void)
    load: function()
    {
        Kookiiz.tabs.loading();
        Kookiiz.ajax.request('/dom/recipe_form.php', 'get',
        {
            'callback': this.onLoad.bind(this),
            'json':     false
        });
    },

    /*******************************************************
    NUTRITION
    ********************************************************/

    //Update nutrition display
    //-> (void)
    nutritionUpdate: function()
    {
        Kookiiz.health.nutritionUpdate();
    },
    
    /*******************************************************
    OBSERVERS
    ********************************************************/
   
    //Compute chars left for recipe description and prevent user input after max is reached
    //-> (void)
    onDescriptionType: function()
    {
        this.$descInput.chars_limit(RECIPE_DESCRIPTION_MAX, 'recipeform_description_chars');
    },
    
    //Called when the enter key is pressed in the quantity field
    //-> (void)
    onIngredientEnter: function()
    {
        if(parseFloat($('input_recipeform_quantity').value))
            this.ingredientsAdd();
    },
    
    //Called when user clicks on "missing ingredient" link
    //Opens feedback popup
    //-> (void)
    onIngredientMissing: function()
    {
        Kookiiz.feedback.popupOpen(2);
    },
    
    //Callback for ingredient selection on the autocompleter
    //#ingredient (object): corresponding ingredient object
    //-> (void)
    onIngredientSelect: function(ingredient)
    {
        //Suggest default unit
        var unit_select = $('select_recipeform_unit');
        unit_select.value_set(ingredient.unit);

        //Hide "no unit" option if ingredient is not countable
        var noUnitIndex = unit_select.value_search(UNIT_NONE);
        if(!ingredient.wpu) 
            unit_select.select('option')[noUnitIndex].hide();
        else                
            unit_select.select('option')[noUnitIndex].show();

        //Focus quantity input
        $('input_recipeform_quantity').target();
    },
    
    //Callback to display the recipe form
    //#content (DOM): recipe form HTML content
    //-> (void)
    onLoad: function(content)
    {
        this.$container.clean().innerHTML = content;
        this.loaded = true;
        this.init();
    },
    
    //Called when deletion button is clicked
    //#event (event): DOM click event
    //-> (void)
    onPictureDelete: function(event)
    {
        event.stop();
        this.pictureDelete();
    },
    
    //Callback for click on picture upload button
    //-> (void)
    onPictureUpload: function()
    {
        Kookiiz.pictures.upload('recipes', this.onPictureUploaded.bind(this));
    },
    
    //Callback for successful picture upload
    //#pic_id (int): ID of the newly uploaded picture
    //-> (void)
    onPictureUploaded: function(pic_id)
    {
        this.recipe.pic_id = pic_id;
        this.pictureDisplay();
    },
    
    //Callback for change of one of the recipe properties
    //-> (void)
    onPropertyChange: function()
    {
        this.recipe.setGuests(parseInt(this.$guestSel.value));
        this.recipe.category    = parseInt(this.$catSel.value);
        this.recipe.origin      = parseInt(this.$origSel.value);
        this.recipe.level       = parseInt(this.$levelSel.value);
        this.recipe.preparation = parseInt(this.$prepInput.value);
        this.recipe.cooking     = parseInt(this.$cookInput.value);
    },

    //Called when recipe content has been updated
    //#event (event): custom event
    //-> (void)
    onRecipeUpdate: function(event)
    {
        var data = $H(event.memo),
            prop = data.get('prop');
        switch(prop)
        {
            case 'ingredients':
                this.ingredientsUpdate();
                break;
            case 'nutrition':
                this.nutritionUpdate();
                break;
            case 'price':
                this.priceUpdate();
                break;
        }
    },
    
    //Called when reset button is clicked
    //-> (void)
    onReset: function()
    {
        var self = this;
        Kookiiz.popup.confirm(
        {
            'text':     RECIPEFORM_ALERTS[this.action == 'create' ? 18 : 19],
            'callback': function(confirm)
                        {
                            if(confirm)
                            {
                                self.reset();
                                if(self.action == 'create')
                                    self.open('create');
                                else if(self.action == 'edit')
                                    Kookiiz.tabs.close();
                            }
                        }
        });
    },
    
    //Callback for click on recipe step deletion icon
    //#event (event): DOM click event
    //-> (void)
    onStepDelete: function(event)
    {
        event.findElement('.step').remove();
        this.descriptionOrder();
        this.descriptionUpdate();
    },
    
    //Called when edition button is clicked
    //#event (event): DOM click event
    //-> (void)
    onStepEdit: function(event)
    {
        event.stop();
        
        var step   = event.findElement('.step'),
            input  = step.select('textarea')[0],
            textEl = step.select('.text')[0],
            icon   = step.select('img.edit')[0];

        var text = textEl.innerHTML;
        if(text.search(/[0-9]+.\s/) === 0) 
            textEl.innerHTML = text.sub(/[0-9]+.\s/, '');
        input.swap(textEl, this.onStepValidate.bind(this), icon);
    },
    
    //Called when validation icon is clicked
    //#event (event): DOM click event
    //-> (void)
    onStepValidate: function(event)
    {
        event.stop();
        
        var step  = event.findElement('.step'),
            input = step.select('textarea')[0],
            text  = step.select('.text')[0],
            icon  = step.select('img.accept')[0];

        input.swap(text, this.onStepEdit.bind(this), icon);
        this.descriptionOrder();
        this.descriptionUpdate();
    },
    
    //Callback for the recipe submission
    //#response (object): server response object
    //-> (void)
    onSubmitConfirm: function(response)
    {
        //Retrieve recipe and display it
        var action = response.parameters.action,
            recipe_id = parseInt(response.parameters.recipe_id),
            recipe = response.content;
        
        //Import and display recipe content
        Recipes.import_content([recipe]);
        Kookiiz.tabs.show('recipe_full', recipe_id, recipe.name);
            
        if(action == 'edit')
            //Display confirmation popup
            Kookiiz.popup.alert({'text': RECIPEFORM_ALERTS[9]});
        else if(action == 'save')
        {
            var new_grade = parseInt(response.parameters.new_grade);
            
            //Add recipe to user's favorites and update user's grade
            User.favorites_add(recipe_id, true);
            User.grade_set(new_grade);
            
            //Display confirmation popup
            var text = RECIPEFORM_ALERTS[7] + ' "' + recipe.name + '" ' + RECIPEFORM_ALERTS[8];
            if(recipe['public'])    
                text += ' ' + RECIPEFORM_ALERTS[10];
            else                    
                text += ' ' + RECIPEFORM_ALERTS[11];
            Kookiiz.popup.alert({'text': text});
        }

        //Reset recipe object
        this.recipe = null;
        this.loaded = false;
    },
    
    //Called once the recipe title has been checked
    //#response (object): server response object
    //-> (void)
    onTitleChecked: function(response)
    {
        var existing = response.content,
            link = $('recipeform_existing_link').stopObserving('click');
        if(existing.length)
        {
            link.observe('click', this.titleExistingShow.bind(this, existing));
            $('recipeform_existing_caption').show();
        }
        else 
            $('recipeform_existing_caption').hide();
    },
    
    //Callback for click on the recipe title or edition icon
    //#event (event): DOM click event
    //-> (void)
    onTitleEdit: function(event)
    {
        event.stop();
        
        //Hide similar titles caption
        $('recipeform_existing_caption').hide();

        //Retrieve title components
        var title = event.findElement().up('.title'),
            input = title.select('input')[0],
            text  = title.select('span')[0],
            icon  = title.select('img')[0];

        //Swap text for input
        input.swap(text, this.onTitleValidate.bind(this), icon);

        //Disable recipe validation button
        this.$submit.freeze();
    },
    
    //Callback for the validation of a new title
    //#event (event): DOM click event
    //-> (void)
    onTitleValidate: function(event)
    {
        event.stop();
        
        //Retrieve title components
        var title   = event.findElement().up('.title'),
            input   = title.select('input')[0],
            text    = title.select('span')[0],
            icon    = title.select('img')[0],
            changed = input.value != input.title;

        //Swap input for text
        input.value = input.value.capitalize();
        input.swap(text, this.onTitleEdit.bind(this), icon);

        //Save and check new title
        var title_text = this.getTitle();
        Kookiiz.panels.header_set('nutrition', title_text);
        if(changed)
        {
            this.recipe.name = title_text;
            this.titleCheck();
        }
        
        //Enable recipe validation button
        this.$submit.unfreeze();
    },
    
    /*******************************************************
    OPEN
    ********************************************************/
   
    //Open recipe form
    //#action (string): either "create" (default) or "edit"
    //#recipe_id (int): unique recipe ID for "edit" mode
    //-> (void)
    open: function(action, recipe_id)
    {
        if(!user_logged())
        {
            Kookiiz.popup.alert(
            {
                'callback': function()
                            {
                                Kookiiz.welcome.signup();
                            },
                'text':     RECIPEFORM_ALERTS[20]
            });
			return;
        }
        action = action || 'create';
        recipe_id = recipe_id || 0;
        var self = this;
        
        //Display form tab
        Kookiiz.tabs.show('recipe_form');
        
        //Check if the recipe form is already loaded
        if(this.loaded)
        {
            //Check for unsaved recipe that could be erased (if action or recipe ID change)
            if((action != this.action) || this.recipe.id != recipe_id)
            {
                Kookiiz.popup.confirm(
                {
                    'text':     RECIPEFORM_ALERTS[5],
                    'callback': function(confirm)
                                {
                                    if(confirm)
                                    {
                                        self.reset();
                                        self.open(action, recipe_id);
                                    }
                                }
                });
                return;
            }
        }
        else
        {
            //Update action value
            this.action = action;
            if(action == 'edit')
            {
                var recipe = Recipes.fetch(recipe_id, function()
                {
                    if(Recipes.get(recipe_id))
                        self.open(action, recipe_id);
                    else
                        Kookiiz.tabs.error_404();
                });
                if(!recipe) return;
                this.recipe = recipe.copy();
            }
            else if(action == 'create')
                this.recipe = new Recipe(0, RECIPEFORM_TEXT[0]);

            //Observe recipe events
            this.recipe.observe('updated', this.onRecipeUpdate.bind(this));

            //Load form content
            this.load();
        }      
    },

    /*******************************************************
    PICTURE
    ********************************************************/
   
    //Display recipe picture
    //-> (void)
    pictureDisplay: function()
    {
        //Show uploaded picture and remove observer
        if(this.recipe.pic_id)
        {
            this.$picture.select('.caption')[0].hide();
            this.$picture.setStyle({'backgroundImage': 'url("/pics/recipes-' + this.recipe.pic_id + '")'});
            this.$picture.stopObserving('click').removeClassName('click');
            
            //Show delete button
            this.$picDel.show();
        }
        else
        {
            this.$picture.stopObserving('click').observe('click', this.onPictureUpload.bind(this));
            this.$picture.setStyle({'backgroundImage': ''}).addClassName('click');
            this.$picture.select('.caption')[0].show();
            
            //Hide delete button
            this.$picDel.hide();
        }
    },

    //Remove current recipe picture
    //-> (void)
    pictureDelete: function()
    {
        Kookiiz.pictures.suppress('recipes', this.recipe.pic_id);
        this.recipe.pic_id = 0;
        this.pictureDisplay();
    },

    /*******************************************************
    PRICE
    ********************************************************/

    //Compute and store recipe price
    //-> (void)
    priceUpdate: function()
    {
        var currency_id = User.option_get('currency'),
            price = this.recipe.getPrice(currency_id);
        $('span_recipeform_price').innerHTML = price + CURRENCIES[currency_id];
    },
    
    /*******************************************************
    PROPERTIES
    ********************************************************/
   
    //Display current recipe properties
    //-> (void)
    propertiesDisplay: function()
    {
        this.$title.clean().appendChild(document.createTextNode(this.recipe.name));
        this.$langSel.value_set(this.recipe.lang);
        this.$guestSel.value_set(this.recipe.guests);
        this.$catSel.value_set(this.recipe.category);
        this.$origSel.value_set(this.recipe.origin);
        this.$levelSel.value_set(this.recipe.level);
        this.$prepInput.value = this.recipe.preparation;
        this.$cookInput.value = this.recipe.cooking;
    },
    
    /*******************************************************
    RESET
    ********************************************************/

    //Reset the recipe form
    //-> (void)
    reset: function()
    {
        if(this.action == 'create')
            this.pictureDelete();
        this.recipe = null;
        this.loaded = false;
    },

    /*******************************************************
    SUBMIT
    ********************************************************/

    //Called when user clicks on submit button
    //-> (void)
    submit: function()
    {
        //Check that all recipe fields are filled properly
        if(this.check())
        {
            var recipe = encodeURIComponent(Object.toJSON(this.recipe.exportData()))
            
            //Edit existing recipe
            if(this.action == 'edit')
            {
                //AJAX request to edit recipe
                Kookiiz.api.call('recipes', 'edit',
                {
                    'callback': this.onSubmitConfirm.bind(this),
                    'request':  'recipe_id=' + this.recipe.id
                                + '&recipe=' + recipe
                });
            }
            //Submit new recipe
            else if(this.action == 'create')
            {
                //Prepare data
                var lang       = this.$langSel.value,
                    status     = $('check_recipeform_public').checked ? 1 : 0,
                    partnerSel = $('select_recipeform_partner'),
                    partnerID  = partnerSel ? parseInt(partnerSel.value) : 0;

                //Launch an AJAX request to add the recipe to the database
                Kookiiz.api.call('recipes', 'save',
                {
                    'callback': this.onSubmitConfirm.bind(this),
                    'request':  'recipe=' + recipe
                                + '&lang=' + lang
                                + '&public=' + status
                                + '&partner_id=' + partnerID
                });

                //Scroll window up and display loader
                Utilities.viewport_reset();
                Kookiiz.tabs.loading();
            }
        }
    },

    /*******************************************************
    TITLE
    ********************************************************/

    //Check if title already exists
    //-> (void)
    titleCheck: function()
    {
        Kookiiz.api.call('recipes', 'check_title',
        {
            'callback': this.onTitleChecked.bind(this),
            'request':  'title=' + this.recipe.name
        });
    },

    //Display search results for recipes with a similar title
    //#recipes_ids (array): list of existing recipe IDs
    //-> (void)
    titleExistingShow: function(recipes_ids)
    {
        Kookiiz.recipes.search_reset();
        Kookiiz.recipes.search_fetch(recipes_ids);
        Kookiiz.recipes.search_summary_hide();
        Kookiiz.recipes.themes_toggle('up');
        Kookiiz.tabs.show('main');
    }
});