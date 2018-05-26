/*******************************************************
Title: Events UI
Authors: Kookiiz Team
Purpose: Functionalities of the event flux
********************************************************/

//Represents a user interface for the events flux
var EventsUI = Class.create(
{
    object_name: 'events_ui',

    /*******************************************************
    CONSTANTS
    ********************************************************/

    TIMEOUT: 5 * 60 * 1000, //Events reload timeout (in seconds)

    /*******************************************************
    CONSTRUCTOR
    ********************************************************/

    //Class constructor
    //-> (void)
    initialize: function()
    {
        this.last_id        = 0;        //Most recent event ID
        this.timer          = 0;        //Events timeout
        this.first_display  = true;     //True as long as events have NOT been displayed once

        //DOM elements
        this.$flux = $('share_events');
    },

    /*******************************************************
    INIT
    ********************************************************/

    //Init dynamic functionalities
    //-> (void)
    init: function()
    {
        //$('events_types').observe('click', this.type_click.bind(this));
    },

    /*******************************************************
    DISPLAY
    ********************************************************/

    //Display events flux in provided container
    //#events (array): list of event objects to display
    //-> (void)
    display: function(events)
    {
        //No events to display
        if(!events.length)
        {
            //In case the container is empty
            if(!$$('.event_item').length)
            {
                //Clean flux and display notification
                this.$flux.clean();
                this.$flux.innerHTML = EVENTS_ALERTS[0];
            }
            return;
        }

        //Retrieve events flux or create new one
        var events_flux = $('events_flux');
        if(!events_flux)
        {
            events_flux = new Element('ul', {'id': 'events_flux'});
            this.$flux.clean().appendChild(events_flux);
        }

        //Loop through events
        events.sort(this.sort.bind(this));
        var event, flux_size, event_item;
        for(var i = 0, imax = events.length; i < imax; i++)
        {
            event     = events[i];
            flux_size = events_flux.childElements().length;

            //Abort if this event already exists
            if($('event_item_' + event.id)) continue;

            //Build current event
            event_item = event.build();
            if(event_item)
            {
                //Eventually remove events in excess
                if(flux_size >= EVENT_FLUX_MAX)
                    events_flux.lastChild.remove();

                //Prepend new event without displaying it
                event_item.hide();
                events_flux.insertBefore(event_item, events_flux.firstChild);

                //Show new event (with or without effect)
                if(this.first_display || User.option_get('fast_mode')) 
                    event_item.show();
                else
                {
                    new Effect.Appear(event_item.id,
                    {
                        'duration': 0.5,
                        'queue':    {'scope': 'events_flux', 'position': 'end'}
                    });
                    event_item.highlight('events_flux');
                }
            }
        }

        //Account for first display
        this.first_display = false;
    },

    /*******************************************************
    FETCH
    ********************************************************/

    //Fetch events flux from server
    //#response (object): server response
    //-> (void)
    fetch: function(response)
    {
        //Retrieve events parameters
        var events_data = response.content.events;
        var users_data  = response.content.users;

        //Parse data
        var events = this.parse(events_data);
        Users.import_content(users_data);

        //Update last ID
        this.last_id = parseInt(response.parameters.last_event);

        //Display events flux
        this.display(events);
    },

    /*******************************************************
    GET
    ********************************************************/

    //Return currently selected event types
    //->types (array): list of event types
    getTypes: function()
    {
        /*
        var types = [];
        var id = 0, input = null;
        $$('.event_type').each(function(el)
        {
            input = el.select('input')[0];
            if(input && input.checked)
            {
                id = parseInt(input.id.split('_')[2]);
                types.push(id);
            }
        });
        */
        var types = $A($R(0, EVENTS_TYPES.length - 1));
        return types;
    },

    /*******************************************************
    LOAD
    ********************************************************/

    //Load events flux
    //-> (void)
    load: function()
    {
        //Event timeout must be reset and user logged
        if(!this.timer && user_logged())
        {
            //Display a loader if events flux is empty
            if(!this.last_id) 
                this.$flux.loading(true);

            //Retrieve currently selected types
            var types = this.getTypes();
            if(!types.length)
            {
                this.display([]);
                return;
            }

            //Create request for events data
            Kookiiz.api.call('events', 'load',
            {
                'callback': this.fetch.bind(this),
                'request':  'last_event=' + this.last_id
                            + '&types=' + Object.toJSON(types)
            });

            //Set a new event timeout to download events every once in a while
            this.timer = window.setTimeout(this.reload.bind(this), this.TIMEOUT);
        }
    },

    //Force events reloading by canceling the timeout
    //-> (void)
    reload: function()
    {
        //Clear events timeout
        window.clearTimeout(this.timer);
        this.timer = 0;

        //Load events
        this.load();
    },

    /*******************************************************
    PARSE
    ********************************************************/

    //Create events objects from events data
    //#events_data (array): raw events data from server
    //->events (array): list of event objects
    parse: function(events_data)
    {
        var events = [];
        var event, id, type, user_id, timestamp, parameters;
        for(var i = 0, imax = events_data.length; i < imax; i++)
        {
            //Common parameters
            event       = events_data[i];
            id          = parseInt(event.id);
            type        = parseInt(event.type);
            user_id     = parseInt(event.user_id);
            timestamp   = event.time;

            //Type-specific parameters
            parameters = {};
            switch(type)
            {
                case EVENT_TYPE_ADDRECIPE:
                    parameters.content      = {};
                    parameters.content.id   = parseInt(event.recipe_id);
                    parameters.content.name = event.recipe_name.stripTags();
                    parameters.content.pic  = parseInt(event.pic);
                    break;
                    
                case EVENT_TYPE_COMMENTRECIPE:
                    parameters.comment      = decodeURIComponent(event.comment_text.stripTags());
                    parameters.content      = {};
                    parameters.content.id   = parseInt(event.recipe_id);
                    parameters.content.name = event.recipe_name.stripTags();
                    parameters.content.pic  = parseInt(event.pic);
                    break;

                case EVENT_TYPE_NEWMEMBER:
                    break;

                case EVENT_TYPE_RATERECIPE:
                    parameters.content          = {};
                    parameters.content.id       = parseInt(event.recipe_id);
                    parameters.content.name     = event.recipe_name.stripTags();
                    parameters.content.pic      = parseInt(event.pic);
                    parameters.content.value    = parseInt(event.rating);
                    break;

                case EVENT_TYPE_SHARERECIPE:
                    parameters.friend_id    = parseInt(event.friend_id);
                    parameters.content      = {};
                    parameters.content.id   = parseInt(event.recipe_id);
                    parameters.content.name = event.recipe_name.stripTags();
                    parameters.content.pic  = parseInt(event.pic);
                    break;

                case EVENT_TYPE_SHARESTATUS:
                    parameters.status_type  = parseInt(event.status_type);
                    parameters.comment      = decodeURIComponent(event.status_comment.stripTags());
                    parameters.content      = {};
                    parameters.content.id   = parseInt(event.content_id);
                    parameters.content.name = event.content_title.stripTags();
                    parameters.content.pic  = parseInt(event.pic);
                    break;

                default:
                    continue;
                    break;
            }

            //Create a new event object
            events.push(new EventItem(id, type, user_id, timestamp, parameters));
        }
        return events;
    },

    /*******************************************************
    RESET
    ********************************************************/

    //Clean events flux
    //-> (void)
    reset: function()
    {
        this.$flux.clean();
        this.last_id        = 0;
        this.first_display  = true;
    },

    /*******************************************************
    SORT
    ********************************************************/

    //Sort events by date (most recent last, to be shown first)
    //#event_a (object): first event to sort
    //#event_b (object): second event to sort
    //->sorting (int): -1 (a before b), 1 (a after b)
    sort: function(event_a, event_b)
    {
        var id_a    = event_a.id;
        var id_b    = event_b.id;
        return id_a > id_b ? 1 : -1;
    },

    /*******************************************************
    TYPES
    ********************************************************/

    //Callback for click on event type check box
    //#event (event): DOM click event
    //-> (void)
    type_click: function(event)
    {
        if(event.findElement('.event_type input'))
        {
            //Reload events
            this.reset();
            this.reload();
        }
    }
});