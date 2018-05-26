/*******************************************************
Title: Observable
Authors: Kookiiz Team
Purpose: Add event triggering capabilities to JS classes
********************************************************/

//Represents a special ancestor class that handles custom events
//Classes that inherit from this class can be observed for custom events
var Observable = Class.create(
{
    /*******************************************************
    CONSTRUCTOR
    ********************************************************/

    //Class constructor
    //-> (void)
    initialize: function()
    {
        this._observers = $H();
    },

    /*******************************************************
    METHODS
    ********************************************************/

    //Fire a custom event
    //#event_name (string): name of the custom event
    //#memo (object): hash to pass to the event handler
    //->class (class): current class instance
    fire: function(event_name, memo)
    {
        if(typeof(this._observers) == 'undefined')                  this._observers = $H();
        if(typeof(event_name) == 'undefined')                       return this;
        if(typeof(this._observers.get(event_name)) == 'undefined')  return this;

        var event;
        if(document.createEvent)
        {
            event = document.createEvent("HTMLEvents");
            event.initEvent("dataavailable", true, true);
        }
        else
        {
            event = document.createEventObject();
            event.eventType = "ondataavailable";
        }
        event.eventName = event_name;
        event.memo      = memo || {};

        this._observers.get(event_name).each(function(handler){handler(event);});
        return this;
    },

    //Add an event handler to the class
    //#event_name (string): name of the custom event
    //#callback (function): event handler
    //->class (class): current class instance
    observe: function(event_name, callback)
    {
        if(typeof(this._observers) == 'undefined')  this._observers = $H();
        if(typeof(event_name) == 'undefined')       return this;
        if(typeof(callback) == 'undefined')         return this;
        if(typeof(this._observers.get(event_name)) != 'undefined')
        {
            if(this._observers.get(event_name).include(callback.toString())) return this;
        }
        else this._observers.set(event_name, $A());

        this._observers.get(event_name).push(callback);
        return this;
    },

    //Remove event handler on the class
    //Remove all event handlers for event_name if callback is not specified
    //#event_name (string): name of the custom event
    //#callback (function): event handler (optional)
    //->class (class): current class instance
    stopObserving: function(event_name, callback)
    {
        if(typeof(this._observers) == 'undefined')                  this._observers = $H();
        if(typeof(event_name) == 'undefined')                       return this;
        if(typeof(this._observers.get(event_name)) == 'undefined')  return this;

        if(typeof(callback) == 'undefined') this._observers.get(event_name).clear();
        else                                this._observers.set(event_name, this._observers.get(event_name).without(callback.toString()));
        return this;
    }
});