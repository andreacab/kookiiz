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
    },
    
 
    
    /*******************************************************
    INIT
    ********************************************************/

    //Init dynamic functionalities
    //-> (void)
    init: function()
    {
        //Hide fastMode option for IE
        if(Prototype.Browser.IE)
            $($('check_fast_mode').parentNode).hide();

        //DOM elements
        this.$profileAvatar    = $('profile_display_avatar');
        this.$profileEditEmail = $('profile_edit_email');
        this.$profileEditPass  = $('profile_edit_pass');
        this.$profileEmail     = $('profile_display_email');
        this.$profileLang      = $('profile_display_lang');
        this.$profileName      = $('profile_display_name');
        this.$tastesInput      = $('input_taste_ingredient');
        this.$tastesType       = $('select_taste_type');

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
        $('options_profile_delete').observe('click', this.onProfileDelete.bind(this));
        $('options_avatar_delete').observe('click', this.onAvatarDelete.bind(this));
        this.$profileEditEmail.observe('click', this.onEmailChangeClick.bind(this));
        this.$profileEditPass.observe('click', this.onPassChangeClick.bind(this));
        this.$profileLang.observe('change', this.onLangChange.bind(this));

        //Save
        $$('button.options_save').invoke('observe', 'click', this.onSave.bind(this));

        //Tastes
        $('taste_ingredient_add').observe('click', this.onTasteAdd.bind(this));
        Ingredients.autocompleter_init(this.$tastesInput);
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
    }
});