/*******************************************************
Title: Status
Authors: Kookiiz Team
Purpose: Manage and update Kookiiz status
********************************************************/

//Represents a user interface for user status management
var StatusUI = Class.create(
{
    object_name: 'status_ui',

    /*******************************************************
    CONSTANTS
    ********************************************************/

    TYPE_DEFAULT: 4,    //Default status type

    /*******************************************************
    CONSTRUCTOR
    ********************************************************/

    //Class constructor
    //-> (void)
    initialize: function()
    {
        this.recipe  = 0;                   //ID of recipe associated with status
        this.type    = this.TYPE_DEFAULT;   //Current status type

        //DOM elements
        this.$input   = $('status_comment_input');
        this.$summary = $('status_summary_display');
    },

    /*******************************************************
    INIT
    ********************************************************/

    //Init dynamic functionalities
    //-> (void)
    init: function()
    {
        //Observers
		$('status_types_area').observe('click', this.onTypeChange.bind(this));
		$('status_cancel_button').observe('click', this.onResetClick.bind(this));
		$('status_share_button').observe('click', this.onShareClick.bind(this));
		this.$input.observe('focus', this.onInputFocus.bind(this));

        //Init social networks options
        var networks = $$('input.share_check');
        networks.each(function(checkbox)
        {
            checkbox.checked = false;
            checkbox.observe('click', this.onNetworkClick.bind(this));
        }, this);

		//Status droppable
		Droppables.add('status_picture_area',
		{
			'accept':     ['recipe_box', 'recipe_item'],
			'hoverclass': 'hover',
			'onDrop':     this.onRecipeDrop.bind(this)
		});
    },
    
    /*******************************************************
    NETWORK SHARING
    ********************************************************/

    //Check authorization for current user on a given social network
    //#network (string): social network name
    //-> (void)
    network_check: function(network)
    {
        Kookiiz.networks.auth(network, this.onNetworkChecked.bind(this));
    },

    /*******************************************************
    EVENTS
    ********************************************************/

    //Called when the status input gains focus
    //#event (event): DOM focus event
    //-> (void)
    onInputFocus: function(event)
    {
        event.stop();

        //Set default type if none is set yet
        if(this.type_get() < 0) 
            this.type_set(this.TYPE_DEFAULT);
    },
    
    /**
     * Called once network auth has been checked
     * #network (string):   social network name
     * #status (int):       authorization status
     * -> (void)
     */
    onNetworkChecked: function(network, status)
    {
        switch(status)
        {
            case NETWORK_STATUS_FAILURE:
                switch(network)
                {
                    case 'facebook':
                        $('status_share_facebook').checked = false;
                        break;
                    case 'twitter':
                        $('status_share_twitter').checked = false;
                        break;
                }
                Kookiiz.popup.alert({'text': SOCIAL_ERRORS[1]});;
                break;
                
            case NETWORK_STATUS_SUCCESS:
                break;
        }
    },

    //Called when a network sharing option is selected
    //#event (object): DOM click event
    //-> (void)
    onNetworkClick: function(event)
    {
        var checkbox = event.findElement();
        if(checkbox.checked)
        {
            var network = checkbox.id.split('_')[2];
            this.network_check(network);
        }
    },

    //Called when a recipe is dropped on the status input
    //#recipe_box (DOM):    recipe box DOM element
    //#picture_area (DOM):  picture area element
    //-> (void)
    onRecipeDrop: function(recipe_box, picture_area)
    {
        //Retrieve recipe parameters
        var recipe_id  = parseInt(recipe_box.id.split('_')[2]),
            recipe_pic = Recipes.get(recipe_id, 'pic_id');

        //Hide caption and show picture
        picture_area.select('span')[0].hide();
        var picture = picture_area.select('img')[0].show();
        picture.src = '/pics/recipes-' + recipe_pic + '-tb';

        //Store recipe ID
        this.recipe = recipe_id;

        //Update status summary
        this.summaryDisplay();
    },
    
    //Callback for click on reset button
    //-> (void)
    onResetClick: function()
    {
        this.reset();
    },

    //Called when the sharing button is clicked
    //-> (void)
    onShareClick: function()
    {
        //Type
        var type = this.type_get();
        if(type < 0) return;

        //Comment
        var comment = '';
        if(this.$input.value != this.$input.title) 
            comment = this.$input.value.stripTags();
        if(!comment && !STATUS_HAS_MESSAGE[type])
        {
            Kookiiz.popup.alert({'text': SHARE_ALERTS[1]});
            return;
        }

        //Content ID
        var content_id = STATUS_HAS_CONTENT[type] ? this.recipe : 0;
        if(!content_id && STATUS_REQUIRE_CONTENT[type])
        {
            Kookiiz.popup.alert({'text': SHARE_ALERTS[0]});
            return;
        }

        //Summary
        var summary = this.summary();

        //Social networks
        var networks = {}, network;
        $$('input.share_check').each(function(checkbox)
        {
            network = checkbox.id.split('_')[2];
            networks[network] = checkbox.checked ? 1 : 0;
        });

        //Share status
        this.share(type, comment, content_id, summary, networks);
    },

    //Called when user clicks on one of the status links
    //#event (object): DOM click event
    //-> (void)
    onTypeChange: function(event)
    {
        var element = event.findElement();
        if(element.hasClassName('status'))
        {
            this.type_set(parseInt(element.readAttribute('data-id')));
            this.$input.target();
        }
    },

    /*******************************************************
    RESET
    ********************************************************/

    //Reset status inputs values
    //-> (void)
    reset: function()
    {
        //Reset status controls
        $('status_types_area').select('.status').invoke('removeClassName', 'selected');
        this.$summary.hide().clean();
        $('status_wrapper').removeClassName('has_pic');
        $('status_input_area').removeClassName('focus');
        this.$input.value = this.$input.title;
        this.$input.removeClassName('focus');
        var picture_area = $('status_picture_area').hide();
        picture_area.select('span')[0].show();
        picture_area.select('img')[0].hide().src = '';
        $('status_actions').hide();

        //Reset parameters
        this.recipe  = 0;
        this.type    = -1;
    },

    /*******************************************************
    SHARE
    ********************************************************/

    //Save status update on the server
    //-> (void)
    share: function(type, comment, content_id, summary, networks)
    {
        Kookiiz.friends.share_status(type, content_id, comment, summary, networks);
        this.reset();
    },

    /*******************************************************
    SUMMARY
    ********************************************************/

    //Display status summary
    //->summary (string): current status summary
    summary: function()
    {
        var type = this.type_get(),
            summary = '';
            
        if(STATUS_HAS_MESSAGE[type])
        {
            summary = STATUS_NAMES[type];
            if(this.recipe)  
                summary += ' ' + SHARE_TEXTS[8] + ' ' + Recipes.get(this.recipe, 'name');
            else            
                summary += '...';
        }
        else
        {
            if(this.recipe)  
                summary += SHARE_TEXTS[10] + ' ' + Recipes.get(this.recipe, 'name');
        }
        return summary;
    },
    
    //Display current status summary
    //-> (void)
    summaryDisplay: function()
    {
        var summary = this.summary();
        if(summary)
            this.$summary.show().innerHTML = '"' + summary + '"';
        else
            this.$summary.hide().innerHTML = '';
    },
    
    /*******************************************************
    TYPE
    ********************************************************/

    //Return current status type
    //->type (int): status type ID
    type_get: function()
    {
        return this.type;
    },

    //Set current status type
    //#type (int): status type ID
    //-> (void)
    type_set: function(type_id)
    {
        this.type = type_id;
        this.update();
    },

    /*******************************************************
    UPDATE
    ********************************************************/

    //Update status controls
    //-> (void)
    update: function()
    {
        var status = this.type_get();

        //Select appropriate status link
        $('status_types_area').select('.status').each(function(stat)
        {
            if(parseInt(stat.readAttribute('data-id')) == status)
                stat.addClassName('selected');
            else
                stat.removeClassName('selected');
        });

        //Display status summary
        this.summaryDisplay();

        //Check if status input must expand
        var input_area = $('status_input_area'),
            input_expanded = input_area.hasClassName('focus');
        if(!input_expanded)
        {
            input_area.addClassName('focus');
            if(Prototype.Browser.IE) 
                this.$input.addClassName('focus');
            else
            {
                new Effect.Morph(this.$input,
                {
                    'style':    'focus',
                    'duration': 0.5
                });
            }
        }

        //Check if a picture may be added to the status
        var picture_area = $('status_picture_area'),
            status_wrapper = $('status_wrapper').removeClassName('has_pic');
        if(STATUS_HAS_CONTENT[status])
        {
            //Set appropriate wrapper style
            status_wrapper.addClassName('has_pic');

            //Display picture area
            if(!input_expanded) 
                new Effect.Appear(picture_area, {'duration': 0.5});
            else               
                picture_area.show();
        }
        else 
            picture_area.hide();

        //Display actions
        $('status_actions').show();
    }
});