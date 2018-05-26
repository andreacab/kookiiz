/*******************************************************
Title: Subscribe UI
Authors: Kookiiz Team
Purpose: Provide a user interface for members subscription
********************************************************/

//Represents a user interface for the subscription form
var SubscribeUI = Class.create(Observable,
{
    object_name: 'subscribe_ui',

    /*******************************************************
    CONSTANTS
    ********************************************************/

    STEPS: 1,

    /*******************************************************
    CONSTRUCTOR
    ********************************************************/

    //Class constructor
    //#$container (DOM): user form container element
    //-> (void)
    initialize: function($container)
    {
        this.step       = 1;            //Current step
        this.$container = $container;
    },

    /*******************************************************
    AUTOFILL
    ********************************************************/

    //Request social network information to fill some fields automatically
    //#network (string): social network name
    //-> (void)
    autoFill: function(network)
    {
        Kookiiz.networks.getUserInfo(network, this.onAutoFill.bind(this));
    },
    
    /*******************************************************
    AVATAR
    ********************************************************/

    //Remove current user avatar
    //-> (void)
    avatarDelete: function()
    {
        this.$avatarID.value = 0;
        this.$avatar.src = '/pics/users-0';
        this.$avatar.observe('click', this.onAvatarClick.bind(this));
        this.$avatar.addClassName('click');
        this.$avatarDel.hide();
        this.$avatarUp.show();
    },

    /*******************************************************
    FIELDS
    ********************************************************/

    //Check validity of field value
    //#field (DOM): input field to check
    //-> (void)
    fieldCheck: function(field)
    {
        var error = 0;
        switch(field.name)
        {
            case 'email':
                var email = field.value;
                if(email)
                {
                    Kookiiz.api.call('email', 'check',
                    {
                        'callback': this.onEmailChecked.bind(this),
                        'request':  'email=' + encodeURIComponent(email)
                    });
                }
                else
                    this.fieldError(field, error = 8);
                break;

            case 'firstname':
                var firstname = field.value;
                var pattern   = new RegExp(REGEXP_NAME_PATTERN, 'g');
                if(firstname.length < USER_FIRSTNAME_MIN)
                    error = 2;
                else if(firstname.length > USER_FIRSTNAME_MAX)
                    error = 3;
                else if(!pattern.test(firstname))
                    error = 4;

                //Update error display
                this.fieldError(field, error);
                break;

            case 'lastname':
                var lastname = field.value;
                var pattern  = new RegExp(REGEXP_NAME_PATTERN, 'g');
                if(lastname.length < USER_FIRSTNAME_MIN)
                    error = 11;
                else if(lastname.length > USER_FIRSTNAME_MAX)
                    error = 12;
                else if(!pattern.test(lastname))
                    error = 13;

                //Update error display
                this.fieldError(field, error);
                break;

            case 'password1':
            case 'password2':
                var password1 = this.$password1.value;
                var password2 = this.$password2.value;
                if(password1.length < USER_PASSWORD_MIN)
                    error = 6;
                else if(password1.length > USER_PASSWORD_MAX)
                    error = 7;
                else if(password1 != password2)
                    error = 5;

                //Update error display
                this.fieldError(this.$password1, error);
                this.fieldError(this.$password2, error, false);
                break;
        }
    },

    //Display error for a given input field
    //#field (DOM/string):  input field DOM element (or its ID)
    //#error (int):         error code (0 = no error)
    //#text (bool):         if true, error text is displayed (defaults to true)
    //-> (void)
    fieldError: function(field, error, text)
    {
        field = $(field);
        if(typeof(text) == 'undefined') text = true;

        var container   = field.up('li'),
            error_field = container.select('.error')[0],
            icon_valid  = container.select('img.accept')[0],
            icon_error  = container.select('img.delete')[0];
        if(error)
        {
            if(text) error_field.innerHTML = SUBSCRIBE_ERRORS[error - 1];
            icon_valid.hide();
            icon_error.show();
        }
        else
        {
            error_field.clean();
            icon_error.hide();
            icon_valid.show();
        }
    },
    
    //Reset field error
    //#field (DOM/string):  input field DOM element (or its ID)
    //-> (void)
    fieldErrorReset: function(field)
    {
        field = $(field);
        
        var container   = field.up('li'),
            error_field = container.select('.error')[0],
            icon_valid  = container.select('img.accept')[0],
            icon_error  = container.select('img.delete')[0];
            
        error_field.clean();
        icon_valid.hide(); 
        icon_error.show();
    },

    /*******************************************************
    INIT
    ********************************************************/

    //Init dynamic functionalities
    //-> (void)
    init: function()
    {
        //DOM form
        this.$form      = this.$container.select('form')[0];
        this.$frame     = this.$container.select('iframe')[0];
        //DOM fields
        this.$avatar    = this.$container.select('img.avatar')[0];
        this.$avatarID  = this.$container.select('input[name=pic_id]')[0];
        this.$firstname = this.$container.select('input[name=firstname]')[0];
        this.$lastname  = this.$container.select('input[name=lastname]')[0];
        this.$email     = this.$container.select('input[name=email]')[0];
        this.$password1 = this.$container.select('input[name=password1]')[0];
        this.$password2 = this.$container.select('input[name=password2]')[0];
        this.$lang      = this.$container.select('select[name=lang]')[0];
        this.$terms     = this.$container.select('input[name=terms]')[0];
        this.$network   = this.$container.select('input[name=network]')[0];
        //DOM buttons
        this.$avatarUp  = this.$container.select('button.avatar.upload')[0];
        this.$avatarDel = this.$container.select('button.avatar.delete')[0];
        this.$next      = this.$container.select('button.next')[0];
        this.$submit    = this.$container.select('button.submit')[0];

        //Observers for avatar management
        this.$avatar.observe('click', this.onAvatarClick.bind(this));
        this.$avatarUp.observe('click', this.onAvatarClick.bind(this));
        this.$avatarDel.observe('click', this.onAvatarDelete.bind(this));

        //Observers for field error checking
        this.$firstname.observe('keyup', this.onFieldChange.bind(this));
        this.$lastname.observe('keyup', this.onFieldChange.bind(this));
        this.$email.observe('blur', this.onFieldChange.bind(this));
        this.$password1.observe('keyup', this.onFieldChange.bind(this));
        this.$password2.observe('keyup', this.onFieldChange.bind(this));

        //Observers for form submitting
        this.$form.onsubmit = this.onSubmit.bind(this);
        this.$next.observe('click', this.onNext.bind(this));
        this.$frame.onload = this.onSubmitted.bind(this);
        
        //Observers for social networks
        this.$container.select('button.social').invoke('observe', 'click', this.onSocialClick.bind(this));
        
        //Back link
        this.$container.select('span.back')[0].observe('click', this.onBack.bind(this));

        //Go to step 1
        this.setStep(1);
    },

    /*******************************************************
    OBSERVERS
    ********************************************************/

    //Handle data received from social network to autofill the form
    //#data (object): user info
    //-> (void)
    onAutoFill: function(data)
    {
        //Autofill form fields
        this.$email.value     = data.email;
        this.$firstname.value = data.firstname;
        this.$lastname.value  = data.lastname;
        this.$lang.value_set(data.lang);
        
        //Clear errors
        this.fieldCheck(this.$email);
        this.fieldCheck(this.$firstname);
        this.fieldCheck(this.$lastname)
    },

    //Callback for click on user avatar
    //#event (object): DOM click event
    //-> (void)
    onAvatarClick: function(event)
    {
        Kookiiz.pictures.upload('users', this.onAvatarUpload.bind(this));
    },

    //Callback for click on avatar deletion buton
    //#event (object): DOM click event
    //-> (void)
    onAvatarDelete: function(event)
    {
        this.avatarDelete();
    },

    //Called when an avatar has been successfully uploaded
    //#pic_id (int): ID of the avatar picture
    //-> (void)
    onAvatarUpload: function(pic_id)
    {
        //Update avatar ID
        this.$avatarID.value = pic_id;

        //Update UI
        this.$avatar.src = '/pics/users-' + pic_id;
        this.$avatar.stopObserving('click').removeClassName('click');
        this.$avatarUp.hide();
        this.$avatarDel.show();
    },
    
    //Called when user clicks on "back" link
    //-> (void)
    onBack: function()
    {
        this.reset();
        this.fire('canceled');
    },

    //Called once email address has been checked by server
    //#response (object): server response object
    //-> (void)
    onEmailChecked: function(response)
    {
        //Determine error code from email status
        var status = parseInt(response.parameters.status), error = 0;
        switch(status)
        {
            //Email already exists
            case EMAIL_STATUS_EXISTING:
                error = 14;
                break;
            //Email format is not valid
            case EMAIL_STATUS_NOTVALID:
                error = 8;
                break;
        }
        //Display error (if any)
        this.fieldError(this.$email, error);
    },

    //Callback for content change inside a form field
    //#event (event): DOM event
    //-> (void)
    onFieldChange: function(event)
    {
        var field = event.findElement();
        this.fieldCheck(field);
    },

    //Called when "next" button is clicked
    //#event (object): DOM click event
    //-> (void)
    onNext: function(event)
    {
        if(this.step < this.STEPS)
            this.stepNext();
    },

    //Called when social network button is clicked
    //Call network connection function
    //#event (object): DOM click event
    //-> (void)
    onSocialClick: function(event)
    {
        var button  = event.findElement(),
            network = button.readAttribute('data-network');
        Kookiiz.networks.auth(network, this.onSocialConnect.bind(this));
    },

    //Called back by the social network connection function
    //#network (string):    social network name
    //#status (int):        social network status
    //-> (void)
    onSocialConnect: function(network, status)
    {
        switch(status)
        {
            //Authorization refused or other issue
            case NETWORK_STATUS_FAILURE:
                this.$network.value = '';
                Kookiiz.popup.alert({'text': SOCIAL_ERRORS[1]});
                break;
            //Got authorization from social network
            case NETWORK_STATUS_PENDING:
                this.autoFill(network);
                this.$network.value = network;
                Kookiiz.popup.alert({'text': SOCIAL_ALERTS[7]});
                break;
            //The network ID is already tied to a Kookiiz account
            case NETWORK_STATUS_SUCCESS:
                this.$network.value = network;
                Kookiiz.popup.confirm(
                {
                    'text':             SOCIAL_ALERTS[4],
                    'confirm_label':    ACTIONS[17],
                    'cancel_label':     ACTIONS[23],
                    'callback':         this.onSocialConfirm.bind(this, network)
                });
                break;
        }
    },

    //Called when social ID is already linked to a Kookiiz account and user chose an action
    //#network (string):    social network name
    //#connect (bool):      true if user chose to log into the existing account
    //-> (void)
    onSocialConfirm: function(network, connect)
    {
        if(connect)
            this.fire('canceled', {'reason': 'login'});
        else
        {
            this.autoFill(network);
            this.$network.value = network;
            Kookiiz.popup.alert({'text': SOCIAL_ALERTS[7]});
        } 
    },

    //Called when social signup was successfull
    //-> (void)
    onSocialSuccess: function()
    {
        window.location.reload();
    },

    //Called right before the user form get submitted
    //Check fields for remaining errors
    //->valid (bool): true if all form fields are considered valid
    onSubmit: function()
    {
        //Check if any error is still displayed
        var errors = $$('.fields img.delete');
        for(var i = 0, imax = errors.length; i < imax; i++)
        {
            if(errors[i].visible())
            {
                Kookiiz.popup.alert({'text': SUBSCRIBE_ALERTS[2]});
                return false;
            }
        }

        //Check if terms of use were accepted
        if(!this.$terms.checked)
        {
            Kookiiz.popup.alert({'text': SUBSCRIBE_ALERTS[3]});
            return false;
        }

        //No error
        return true;
    },

    //Called once the form has been submitted and server response is available in hidden iframe
    //-> (void)
    onSubmitted: function()
    {
        //Retrieve error value
        var error = this.$frame.contentWindow.ERROR;
        if(typeof(error) == 'undefined') return;

        //Display error or success notice
        if(error.code)
        {
            switch(error.type)
            {
                case 'social':
                    Kookiiz.popup.alert({'text': SOCIAL_ERRORS[error.code - 1]});
                    break;
                case 'subscribe':
                    Kookiiz.popup.alert({'text': SUBSCRIBE_ERRORS[error.code - 1]}); 
                    break;
            }
        }
        else
        {
            var mode = this.$frame.contentWindow.MODE;
            switch(mode)
            {
                case 'network':
                    //Successful social network sign-up
                    Kookiiz.popup.alert({'text': SUBSCRIBE_ALERTS[4], 'callback': this.onSocialSuccess.bind(this)});
                    break;
                case 'standard':
                    //Inform user on email validation process
                    Kookiiz.popup.alert({'text': SUBSCRIBE_ALERTS[0] + ' ' + SUBSCRIBE_ALERTS[1]});
                    this.fire('submitted');
                    this.reset();
                    break;
            }
            
        }
    },
    
    /*******************************************************
    RESET
    ********************************************************/
   
    //Reset form
    //-> (void)
    reset: function()
    {
        //Clear all fields
        this.avatarDelete();
        this.$firstname.value   = '';
        this.$lastname.value    = '';
        this.$email.value       = '';
        this.$password1.value   = '';
        this.$password2.value   = '';
        this.$terms.checked     = false;
        this.$network.value     = '';
        this.$lang.value_set(session_lang());
        
        //Remove all errors
        this.fieldErrorReset(this.$firstname);
        this.fieldErrorReset(this.$lastname);
        this.fieldErrorReset(this.$email);
        this.fieldErrorReset(this.$password1);
        this.fieldErrorReset(this.$password2);
        
        //Go to step 1
        this.setStep(1);
    },

    /*******************************************************
    SETTERS
    ********************************************************/

    //Set current step
    //#step (int): step number
    //-> (void)
    setStep: function(step)
    {
        this.step = step;
        this.stepDisplay(true);
    },

    /*******************************************************
    SOCIAL
    ********************************************************/

    //Automatically submit the form for social network subscription
    //#network (string): social network name
    //-> (void)
    socialSubmit: function(network)
    {
        this.$frame.src = '/dom/signup_social.php?network=' + network;
    },

    /*******************************************************
    STEPS
    ********************************************************/

    //Display current step
    //#fast (bool): if true no transition effect is used (defaults to false)
    //-> (void)
    stepDisplay: function(fast)
    {
        var steps = this.$form.select('.step').invoke('hide'),
            step, step_id;
        for(var i = 0, imax = steps.length; i < imax; i++)
        {
            step    = steps[i];
            step_id = parseInt(step.readAttribute('data-step'));
            if(step_id === this.step)
            {
                if(fast)
                    step.show();
                else
                    step.appear({'duration': 2});
                break;
            }
        }

        //Adapt interface to current step
        switch(this.step)
        {
            //Mandatory info
            case 1:
                this.$submit.show();
                //this.$next.show();
                break;
            //Optional info
            case 2:
                //this.$next.hide();
                //this.$submit.show();
        }
    },

    //Next step
    //-> (void)
    stepNext: function()
    {
        this.step++;
        this.stepDisplay();
    }
});