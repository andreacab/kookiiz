/*******************************************************
Title: Users UI
Authors: Kookiiz Team
Purpose: Provide an interface for users related actions
********************************************************/

//Represents a user interface for users-related actions
var UsersUI = Class.create(
{
    object_name: 'users_ui',

    /*******************************************************
    CONSTRUCTOR
    ********************************************************/

    //Class constructor
    //-> (void)
	initialize: function()
    {
        this.$cardAvatar = $('user_area_avatar');
        this.$cardGrade  = $('user_area_grade');
        this.$cardName   = $('user_area_name');
        this.$panel      = $('user_info');
    },
    
    /*******************************************************
    INIT
    ********************************************************/

    //Init dynamic functionalities
    //-> (void)
    init: function()
    {
        if(user_logged())
        {
            this.$cardAvatar.observe('click', this.onProfileClick.bind(this));
            this.$cardName.observe('click', this.onProfileClick.bind(this));
        }
    },

    //Init profile window
    //-> (void)
    initProfile: function()
    {
        //Hide fastMode option for IE
        if(Prototype.Browser.IE)
            $($('check_fast_mode').parentNode).hide();

        //DOM elements
        this.$profileAvatar         = $('profile_display_avatar');
        this.$profileDelete         = $('profile_delete');
        this.$profileDeleteAvatar   = $('profile_delete_avatar');
        this.$profileEditEmail      = $('profile_edit_email');
        this.$profileEditPass       = $('profile_edit_pass');
        this.$profileEmail          = $('profile_display_email');
        this.$profileLang           = $('profile_display_lang');
        this.$profileName           = $('profile_display_name');
        this.$tasteAdd              = $('profile_add_taste');
        this.$tastesError           = $('profile_error_taste');
        this.$tastesInput           = $('profile_input_taste');
        this.$tastesType            = $('profile_select_tastetype');

        //Allergies
        //$$('.option_allergy input').invoke('observe', 'click', this.onAllergyChange.bind(this));

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

        //Profile
        this.$profileDelete.observe('click', this.onProfileDelete.bind(this));
        this.$profileDeleteAvatar.observe('click', this.onAvatarDelete.bind(this));
        this.$profileEditEmail.observe('click', this.onEmailChangeClick.bind(this));
        this.$profileEditPass.observe('click', this.onPassChangeClick.bind(this));
        this.$profileLang.observe('change', this.onLangChange.bind(this));

        //Tastes
        this.$tasteAdd.observe('click', this.onTasteAdd.bind(this));
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
        Kookiiz.popup.hide();
        Kookiiz.pictures.upload('users', this.onAvatarUpload.bind(this));
    },

    //Called when user choses to remove his avatar
    //-> (void)
    onAvatarDelete: function()
    {
        Kookiiz.popup.hide();
        Kookiiz.popup.confirm(
        {
            'text':     OPTIONS_ALERTS[2],
            'callback': this.onAvatarDeleteConfirm.bind(this)
        });
    },

    //Called when user confirms or cancels avatar deletion
    //-> (void)
    onAvatarDeleteConfirm: function(confirm)
    {
        if(confirm)
        {
            //Delete picture from server
            Kookiiz.pictures.suppress('users', User.getPic());

            //Update user's pic
            User.pic_set(0);
        }

        //Display profile again
        this.profileOpen();
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

        //Display profile again
        this.profileOpen();
    },

    //Open popup for user to change his email address
    //-> (void)
    onEmailChangeClick: function()
    {
        Kookiiz.popup.hide();
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
            this.profileOpen();
        }
    },

    //Called when email change popup is loaded
    //-> (void)
    onEmailChangeReady: function()
    {
        $('email_change').select('form')[0].onsubmit = this.onEmailSubmit.bind(this);
        $('email_change').select('iframe')[0].onload = this.onEmailChanged.bind(this);
        $('email_change_cancel').observe('click', this.onProfileEditCancel.bind(this));
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

    //Open a popup to change user's password
    //-> (void)
    onPassChangeClick: function()
    {
        Kookiiz.popup.hide();
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
            this.profileOpen();
        }
    },

    //Init password changing popup functionalities
    //-> (void)
    onPassPopupReady: function()
    {
        $('password_change').select('form')[0].onsubmit = this.onPassSubmit.bind(this);
        $('password_change').select('iframe')[0].onload = this.onPassChanged.bind(this);
        $('password_change_cancel').observe('click', this.onProfileEditCancel.bind(this));
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
   
    //Callback for user preview content loaded from server
    //#user_id (int): ID of the user for which to display a preview
    //-> (void)
    onPreviewReady: function(user_id)
    {
        if(Users.exist(user_id))
            this.preview(user_id);
        else
            this.$panel.clean();
    },
   
    //Callback for click on profile avatar or name
    //-> (void)
    onProfileClick: function()
    {
        this.profileOpen();
    },

    //Called when user clicks on profile delete button
    //-> (void)
    onProfileDelete: function()
    {
        Kookiiz.popup.hide();
        Kookiiz.popup.confirm(
        {
            'text':     USER_ALERTS[1],
            'callback': this.onProfileDeleteConfirm.bind(this)
        });
    },

    //Called when user confirms or cancels profile deletion
    //#confirm (bool): whether the user confirmed or not
    //-> (void)
    onProfileDeleteConfirm: function(confirm)
    {
        if(confirm)
            User.profile_delete();
        else
            this.profileOpen();
    },

    //Close email or password change popup
    //-> (void)
    onProfileEditCancel: function()
    {
        Kookiiz.popup.hide();
        this.profileOpen();
    },

    //Called when the profile popup is closed
    //#confirm (bool): true if user pressed "OK"
    //-> (void)
    onProfilePopupClose: function(confirm)
    {
        if(confirm) this.profileSave();
    },

    //Callback for profile popup initialization
    //-> (void)
    onProfilePopupLoad: function()
    {
        this.initProfile();
        this.displayProfile();
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
                this.displayTastesAll();
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
            {
                this.$tastesError.innerHTML = OPTIONS_ERRORS[error - 1];
                this.$tastesError.show();
            }
            else
            {
                this.$tastesError.clean();
                this.$tastesError.hide();
                this.displayTastes(taste_type);
            }
        }
        //No matching ingredient found
        else
        {
            this.$tastesError.innerHTML = OPTIONS_ALERTS[0];
            this.$tastesError.show();
        }

        //Reset inputs
        this.$tastesInput.value = '';
        this.$tastesInput.target();
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
    PREVIEW
    ********************************************************/

    //Display public profile info on side panel
    //#user_id (int): ID of the user
    //-> (void)
    preview: function(user_id)
    {
        //Try to fetch user profile
        var user = Users.fetch(user_id, this.onPreviewReady.bind(this, user_id));
        if(user)
            //User profile available client-side
            this.displayPreview(user, this.$panel);
        else
            //User profile to be loaded from server
            this.$panel.loading();
    },

    /*******************************************************
    PROFILE
    ********************************************************/

    //Display user profile popup
    //-> (void)
    profileOpen: function()
    {
        Kookiiz.popup.custom(
        {
           'confirm':               true,
           'cancel':                false,
           'callback':              this.onProfilePopupClose.bind(this),
           'large':                 true,
           'title':                 OPTIONS_TITLES[3],
           'text':                  '',
           'content_url':           '/dom/profile_popup.php',
           'content_init':          this.onProfilePopupLoad.bind(this)
        });
    },

    //Save current user profile
    //-> (void)
    profileSave: function()
    {
        var props   = ['allergies', 'options', 'tastes'],
            options = {'silent': false};
        Kookiiz.popup.hide();
        User.profile_save(props, options);
    },
    
    /*******************************************************
    DISPLAY
    ********************************************************/

    //Display profile allergies
    //#user (user): user for which to display allergies
    //-> (void)
    displayAllergies: function(user)
    {
        if(typeof(user) == 'undefined') user = User

        var allergies = user.allergies_get(), name, check;
        for(var i = 0, imax = ALLERGIES.length; i < imax; i++)
        {
            name  = ALLERGIES[i];
            check = $('check_allergy_' + name);
            if(check)
                check.checked = allergies[name] ? true : false;
        }
    },

    //Update user's card display
    //-> (void)
    displayCard: function()
    {
        if(user_logged())
        {
            this.displayPic(User, this.$cardAvatar);
            this.displayGrade(User, this.$cardGrade, true);
        }
    },

    //Display user's grade in provided container
    //#user (user):             user from which to display grade
    //#container (DOM/string):  DOM element inside which to display grade
    //#compact (bool):          if true display the grade in a compact manner
    //-> (void)
	displayGrade: function(user, container, compact)
    {
        if(typeof(user) == 'undefined') user = User

        container = $(container).clean();
        if(typeof(compact) == 'undefined') compact = false;

        var points = user.getGrade(), cookie = null;
        if(compact)
        {
            container.innerHTML = points;
            cookie = new Element('img',
            {
                'alt':      USER_GRADE_TEXT[0],
                'class':    'icon15 cookie1',
                'src':      ICON_URL,
                'title':    '1 ' + USER_GRADE_TEXT[1]
            });
            container.appendChild(cookie);
        }
        else
        {
            var value;
            for(var i = 0, imax = COOKIE_VALUES.length; i < imax; i++)
            {
                value = COOKIE_VALUES[i];
                while(points >= value)
                {
                    cookie = new Element('img',
                    {
                        'alt':      USER_GRADE_TEXT[0],
                        'class':    'icon15 cookie' + value,
                        'src':      ICON_URL,
                        'title':    value + ' ' + USER_GRADE_TEXT[1]
                    });
                    container.appendChild(cookie);
                    points -= value;
                }
            }
        }
    },

    //Display user options
    //-> (void)
    displayOptions: function()
    {
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
    },

    //Display personal information
    //-> (void)
    displayPersonal: function()
    {
        //Avatar
        this.displayPic(User, this.$profileAvatar, true, true);
        var avatar = this.$profileAvatar.select('.avatar')[0];
        avatar.observe('click', this.onAvatarClick.bind(this)).addClassName('click');
        //Avatar deletion button
        if(User.getPic())
            this.$profileDeleteAvatar.show();
        else
            this.$profileDeleteAvatar.hide();
        //Other profile properties
        this.$profileName.innerHTML  = User.name;
        this.$profileEmail.innerHTML = User.email;
        this.$profileLang.value_set(User.lang);
    },

    //Display user's avatar in provided container
    //#user (user):             user to display picture of
    //#container (DOM/string):  container element (or its ID)
    //#noFB (bool):             true to force Kookiiz avatar instead of FB (defaults to false)
    //#big (bool):              true to display a 100x100 pic, false for 50x50 (defaults to false)
    //-> (void)
	displayPic: function(user, container, noFB, big)
    {
        if(typeof(user) == 'undefined') user = User
        if(typeof(noFB) == 'undefined') noFB = false;
        if(typeof(big) == 'undefined')  big = false;
        container = $(container).clean();

        //Avatar element
        var wrap = new Element('div', {'class': 'avatar_wrapper'}),
            pic = new Element('img', {'alt': user.name, 'class': 'avatar'});
        container.appendChild(wrap);
        wrap.appendChild(pic);

        //User has a Kookiiz avatar or his account is not linked to FB
        var pic_id = user.getPic(), fb_id = user.getFBID();
        if(pic_id || !fb_id || noFB)
            pic.src = '/pics/users-' + pic_id + (big ? '' : '-tb');
        //User has no Kookiiz avatar and his account is linked to FB
        else
            pic.src = 'http://graph.facebook.com/' + fb_id + '/picture?type=' + (big ? 'normal' : 'square');
    },

    //Display user preview
    //#user (user):             user to display preview of
    //#container (DOM/string):  container element (or its ID)
    //-> (void)
    displayPreview: function(user, container)
    {
        if(typeof(user) == 'undefined') user = User
        container = $(container).clean();

        //Build table
        var table = new Element('table'),
            body  = new Element('tbody'),
            row   = new Element('tr'),
            left  = new Element('td'),
            right = new Element('td', {'class': 'right'}),
            text  = new Element('p'),
            rate  = new Element('p');
        right.appendChild(text);
        right.appendChild(rate);
        row.appendChild(left);
        row.appendChild(right);
        body.appendChild(row);
        table.appendChild(body);

        //Display properties
        this.displayPic(user, left);
        this.displayGrade(user, rate, true);
        text.innerHTML = user.getName();

        //Append table
        container.appendChild(table);
    },

    //Display user profile
    //-> (void)
    displayProfile: function()
    {
        this.displayAllergies(User);
        this.displayOptions();
        this.displayPersonal();
        this.displayTastesAll();
    },

    //Display list of tastes of specific type
    //#type (int): tastes type
    //-> (void)
    displayTastes: function(type)
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

    //Display all tastes
    //-> (void)
    displayTastesAll: function()
    {
        this.displayTastes(TASTE_DISLIKE);
        this.displayTastes(TASTE_LIKE);
    }
});