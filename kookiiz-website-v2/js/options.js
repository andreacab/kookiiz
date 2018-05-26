/*******************************************************
Title: Options
Authors: Kookiiz Team
Purpose: Display and edit user profile options
********************************************************/

//Represents a user interface for options
var OptionsUI = Class.create(
{
    object_name: 'options_ui',

    /*******************************************************
    CONSTRUCTOR
    ********************************************************/

    //Class constructor
    //-> (void)
    initialize: function()
    {
        //Hide fastMode option for IE
        if(Prototype.Browser.IE) 
            $($('check_fast_mode').parentNode).hide();

        //DOM elements
        this.$tastesInput = $('input_taste_ingredient');
        this.$tastesType  = $('select_taste_type');
    },

    /*******************************************************
    ALLERGIES
    ********************************************************/

    //Update allergies display
    //-> (void)
    allergiesUpdate: function()
    {
        var allergies = User.allergies_get(), name, check;
        for(var i = 0, imax = ALLERGIES.length; i < imax; i++)
        {
            name  = ALLERGIES[i];
            check = $('check_allergy_' + name);
            if(check) 
                check.checked = allergies[name] ? true : false;
        }
    },
    
    /*******************************************************
    EMAIL
    ********************************************************/

    //Check email validity before submitting
    //->error (int): error code (0 = no error)
    emailCheck: function()
    {
        var error = 0,
            email_old  = User.email,
            email_new1 = $('options_email_new1').value.stripTags(),
            email_new2 = $('options_email_new2').value.stripTags();
        if(email_new1 != email_new2)       
            error = 2;
        else if(email_new1 == email_old)
            error = 4;
        if(error)
        {
            var error_caption = $('email_change_error');
            error_caption.innerHTML = EMAIL_ERRORS[error - 1];
            error_caption.show().highlight();
            return false;
        }
        else
        {
            $('email_change_error').hide();
            return true;
        }
    },
    
    /*******************************************************
    INIT
    ********************************************************/

    //Init dynamic functionalities
    //-> (void)
    init: function()
    {
        //Allergies
        $$('.option_allergy input').invoke('observe', 'click', this.onAllergyChange.bind(this));

        //Options
        var options = $$('.user_option');
        for(var i = 0, imax = options.length; i < imax; i++)
        {
            switch(options[i].tagName)
            {
                case 'INPUT':
                    options[i].observe('click', this.onOptionChange.bind(this));
                    break;
                case 'SELECT':
                    options[i].observe('change', this.onOptionChange.bind(this));
                    break;
            }
        }

        //Panels
        var option_panel_labels = $$('.option_panel label');
        option_panel_labels.invoke('observe', 'mouseout', this.onPanelOut.bind(this));
        option_panel_labels.invoke('observe', 'mouseover', this.onPanelOver.bind(this));

        //Profile
        $('options_profile_delete').observe('click', this.onProfileDelete.bind(this));
        $('profile_email_edit').observe('click', this.onEmailChangeClick.bind(this));
        $('profile_password_edit').observe('click', this.onPassChangeClick.bind(this));
        $('options_avatar_delete').observe('click', this.onAvatarDelete.bind(this));
        $('select_profile_lang').observe('change', this.onLangChange.bind(this));

        //Save
        $$('button.options_save').invoke('observe', 'click', this.onSave.bind(this));

        //Tastes
        $('taste_ingredient_add').observe('click', this.onTasteAdd.bind(this));
        Ingredients.autocompleter_init(this.$tastesInput);
    },
    
    /*******************************************************
    OBSERVERS
    ********************************************************/
   
    //Called when an allergy option changes
    //-> (void)
    onAllergyChange: function(event)
    {
        var allergyElement = event.findElement(),
            allergyName    = allergyElement.id.sub('check_allergy_', ''),
            allergyValue   = allergyElement.checked ? 1 : 0;
        User.allergy_set(allergyName, allergyValue);
    },
   
    //Callback for click on user avatar
    //Open picture upload popup
    //-> (void)
    onAvatarClick: function()
    {
        Kookiiz.pictures.upload('users', this.onAvatarUpload.bind(this));
    },

    //Called when user choses to remove his avatar
    //-> (void)
    onAvatarDelete: function()
    {
        Kookiiz.popup.confirm(
        {
            'text':     OPTIONS_ALERTS[2],
            'callback': function(confirm)
                        {
                            if(confirm)
                            {
                                //Hide delete button
                                $('options_avatar_delete').hide();

                                //Delete picture from server
                                Kookiiz.pictures.suppress('users', User.getPic());

                                //Update user's pic
                                User.pic_set(0);
                                User.profile_save('picture', true);
                            }
                        }
        });
    },
    
    //Called when a new avatar was successfully uploaded
    //#pic_id (int): ID of the new avatar
    //-> (void)
    onAvatarUpload: function(pic_id)
    {
        //Delete pre-existing pic
        var current_pic = User.getPic();
        if(current_pic) 
            Kookiiz.pictures.suppress('users', current_pic);

        //Update user's picture
        User.pic_set(pic_id);
    },
   
    //Open popup for user to change his email address
    //-> (void)
    onEmailChangeClick: function()
    {
        Kookiiz.popup.custom(
        {
            'text':                 EMAIL_ALERTS[2],
            'title':                EMAIL_ALERTS[1],
            'content_url':          '/dom/email_change_popup.php',
            'content_parameters':   'email=' + encodeURIComponent(User.email),
            'content_init':         this.onEmailChangeReady.bind(this)
        });
    },
    
    //Called once the email changing script returns
    //-> (void)
    onEmailChanged: function()
    {
        var iframe = $('email_change').select('iframe')[0],
            error = iframe.contentWindow.ERROR;
        if(typeof(error) == 'undefined') return;
        
        $('email_change_loader').hide();
        $('email_change_inputs').show();
        if(error)
        {
            //Display error caption
            var error_caption = $('email_change_error');
            error_caption.innerHTML = EMAIL_ERRORS[error - 1];
            error_caption.show().highlight();
        }
        else
        {
            //Display alert for validation process
            Kookiiz.popup.hide();
            Kookiiz.popup.alert({'text': OPTIONS_ALERTS[3]});
        }
    },
    
    //Called when email change popup is loaded
    //-> (void)
    onEmailChangeReady: function()
    {
        $('email_change').select('form')[0].onsubmit = this.onEmailSubmit.bind(this);
        $('email_change').select('iframe')[0].onload = this.onEmailChanged.bind(this);
        $('email_change_cancel').observe('click', this.onPopupCancel.bind(this));
    },
    
    //Called when user submits his change of email
    //-> (void)
    onEmailSubmit: function()
    {
        if(this.emailCheck())
        {
            //Display loader during password changing phase
            $('email_change_inputs').hide();
            $('email_change_loader').loading(true).show();
            return true;
        }
        else 
            return false;
    },
    
    //Called when the language selector is changed
    //#event (event): DOM change event
    //-> (void)
    onLangChange: function(event)
    {
        var selector = event.findElement();
        Kookiiz.lang.change(selector.value);
    },
    
    //Called when a user option changes
    //#event (event): DOM event (click or change)
    //-> (void)
    onOptionChange: function(event)
    {
        var option = event.findElement(),
            option_name  = option.name,
            option_value = option.tagName == 'INPUT' ? (option.checked ? 1 : 0) : option.selectedIndex;
        User.option_set(option_name, option_value);
    },
    
    //Called when the mouse leaves a panel display option
    //-> (void)
    onPanelOut: function()
    {
        Kookiiz.panels.help_hide();
    },

    //Called when the mouse is over a panel display option
    //#event (event): mouseover event
    //-> (void)
    onPanelOver: function(event)
    {
        //Display help (description) of corresponding panel
        var option = event.findElement('li'),
            checkbox   = option.select('input')[0],
            panel_name = checkbox.id.split('_')[2];
        Kookiiz.panels.help_display(panel_name, true);
    },
   
    //Open a popup to change user's password
    //-> (void)
    onPassChangeClick: function()
    {
        Kookiiz.popup.custom(
        {
            'text':         PASSWORD_ALERTS[3],
            'title':        PASSWORD_ALERTS[2],
            'content_url':  '/dom/password_change_popup.php',
            'content_init': this.onPassPopupReady.bind(this)
        });
    },
    
    //Called once the password changing script returns
    //-> (void)
    onPassChanged: function()
    {
        var iframe = $('password_change').select('iframe')[0],
            error = iframe.contentWindow.ERROR;
        if(typeof(error) == 'undefined') return;
        
        $('password_change_loader').hide();
        $('password_change_inputs').show();
        if(error)
        {
            //Display error caption
            var error_caption = $('password_change_error');
            error_caption.innerHTML = PASSWORD_ERRORS[error - 1];
            error_caption.show();
        }
        else
        {
            //Display confirmation
            Kookiiz.popup.hide();
            Kookiiz.popup.alert({'text': PASSWORD_ALERTS[4]});
        }
    },
    
    //Init password changing popup functionalities
    //-> (void)
    onPassPopupReady: function()
    {
        $('password_change').select('form')[0].onsubmit = this.onPassSubmit.bind(this);
        $('password_change').select('iframe')[0].onload = this.onPassChanged.bind(this);
        $('password_change_cancel').observe('click', this.onPopupCancel.bind(this));
    },
    
    //Called when the password change form is submitted
    //-> (void)
    onPassSubmit: function()
    {
        //Check fields validity
        if(this.passwordCheck())
        {
            //Create loader and hide inputs
            $('password_change_inputs').hide();
            $('password_change_loader').loading(true).show();
            return true;
        }
        else 
            return false;
    },
    
    //Close email or password change popup
    //-> (void)
    onPopupCancel: function()
    {
        Kookiiz.popup.hide();
    },
    
    //Called when user clicks on profile delete button
    //-> (void)
    onProfileDelete: function()
    {
        //Ask for confirmation
        Kookiiz.popup.confirm(
        {
            'text':     USER_ALERTS[1],
            'callback': function(confirm)
                        {
                            if(confirm) User.profile_delete();
                        }
        });
    },
    
    //Called when one of the save buttons is clicked
    //-> (void)
    onSave: function()
    {
        var props   = ['allergies', 'options', 'tastes'],
            options = {'silent': false};
        User.profile_save(props, options);
    },
    
    //Callback for user action on taste ingredient DOM element
    //#ing_qty (object): corresponding ingredient quantity object
    //#action (string):  user action
    //-> (void)
    onTasteAction: function(ing_qty, action)
    {
        switch(action)
        {
            case 'delete':
                User.tastes_delete(ing_qty.id);
                break;
        }
    },
    
    //Callback for click on taste validation button
    //-> (void)
    onTasteAdd: function()
    {
        //Find corresponding ingredient in database
        var ingredient = this.$tastesInput.value.stripTags(),
            ingredient_id = Ingredients.search(ingredient, 'id');
        if(ingredient_id > 0)
        {
            //Add taste
            var taste_type = parseInt(this.$tastesType.value),
                error = User.tastes_add(ingredient_id, taste_type);
            if(error) 
                Kookiiz.popup.alert({'text': OPTIONS_ERRORS[error - 1]});
        }
        //No matching ingredient found
        else
            Kookiiz.popup.alert({'text': OPTIONS_ALERTS[0]});

        //Reset inputs
        this.tastesReset();
    },

    /*******************************************************
    OPTIONS
    ********************************************************/

    //Update options display
    //-> (void)
    optionsUpdate: function()
    {
        //Set up options
        var options  = User.options_get(),
            controls = $$('.user_option'), control;
        for(var i = 0, imax = controls.length; i < imax; i++)
        {
            control = controls[i];
            if(typeof(options[control.name]) != 'undefined')
            {
                switch(control.tagName)
                {
                    case 'INPUT':
                        control.checked = options[control.name] ? true : false;
                        break;
                    case 'SELECT':
                        control.selectedIndex = options[control.name];
                        break;
                }
            }
        }

        //Set-up panels
        if(Kookiiz.tabs.current_get() == 'profile')
            this.panelsSetup();
    },
        
    /*******************************************************
    PANELS
    ********************************************************/

    //Set-up panels for options tab
    //-> (void)
    panelsSetup: function()
    {
        var panels_set = Kookiiz.tabs.panels_get('profile');
        Kookiiz.panels.set(panels_set, true);
        Kookiiz.panels.attach(false, 'right');
        Kookiiz.panels.toggle(false, 'close', false, true);
        Kookiiz.panels.attach('navigation', 'left');
        Kookiiz.panels.toggle('navigation', 'open', false, true);
        Kookiiz.panels.freeze();
        Kookiiz.panels.configFreeze();
    },
    
    /*******************************************************
    PASSWORD
    ********************************************************/

    //Check fields validity before submitting
    //->valid (bool): true if fields are valid
    passwordCheck: function()
    {
        var error = 0,
            passNew1 = $('options_password_new1').value,
            passNew2 = $('options_password_new2').value;
        if(passNew1 != passNew2)                  
            error = 3;
        else if(passNew1.length < USER_PASSWORD_MIN)  
            error = 4;
        else if(passNew1.length > USER_PASSWORD_MAX)   
            error = 5;
        
        //Display error (if any)
        if(error)
        {
            var error_caption = $('password_change_error');
            error_caption.innerHTML = PASSWORD_ERRORS[error - 1];
            error_caption.show();
            return false;
        }
        else
        {
            $('password_change_error').hide();
            return true;
        }
    },

    /*******************************************************
    PROFILE
    ********************************************************/

    //Update profile display
    //-> (void)
    profileUpdate: function()
    {
        //Avatar
        User.displayPic('options_avatar_area', true, true);
        var avatar = $('options_avatar_area').select('.avatar')[0];
        avatar.observe('click', this.onAvatarClick.bind(this)).addClassName('click');
        //Avatar deletion button
        if(User.getPic())
            $('options_avatar_delete').show();
        else                
            $('options_avatar_delete').hide();
        //Other profile properties
        $('span_profile_name').innerHTML  = User.name;
        $('span_profile_email').innerHTML = User.email;
        $('select_profile_lang').value_set(User.lang);
    },

    /*******************************************************
    TASTES
    ********************************************************/

    //Display list of tastes of specific type
    //#type (int): tastes type
    //-> (void)
    tastesDisplay: function(type)
    {
        //Retrieve and clean container
        var selector  = (type == TASTE_LIKE ? '.like' : '.dislike') + ' .list',
            container = $('tastes_display').select(selector)[0].clean();

        //Display tastes lists
        var list = User.tastes_get(type).build(
        {
            'callback':     this.onTasteAction.bind(this),
            'deletable':    true,
            'iconized':     true,
            'sorting':      'name'
        });
        if(list.empty())    
            container.innerHTML = TASTES_ALERTS[0];
        else                
            container.appendChild(list);
    },

    //Reset tastes inputs
    //-> (void)
    tastesReset: function()
    {
        this.$tastesInput.value = '';
        this.$tastesInput.target();
    },

    //Display user's tastes
    //-> (void)
    tastesUpdate: function()
    {
        this.tastesDisplay(TASTE_LIKE);
        this.tastesDisplay(TASTE_DISLIKE);
    },
    
    /*******************************************************
    UPDATE
    ********************************************************/

    //Update options UI
    //-> (void)
    update: function()
    {
        this.allergiesUpdate();
        this.optionsUpdate();
        this.profileUpdate();
        this.tastesUpdate();
    }
});