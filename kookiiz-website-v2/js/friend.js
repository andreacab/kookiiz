/*******************************************************
Title: Friend
Authors: Kookiiz Team
Purpose: Define the friend object
********************************************************/

//Represents a friendship link
var Friend = Class.create(
{
	object_name: 'friend',

	/*******************************************************
    CONSTRUCTOR
    ********************************************************/

    //Class constructor
    //#id (int):        ID of the corresponding user
    //#status (int):    friend status (0 = offline, 1 = online)
    //-> (void)
	initialize: function(id, status)
    {
        this.id     = id;
        this.status = status;
        this.user   = Users.get(id);    //Pointer on corresponding user profile
    },

    /*******************************************************
    GET
    ********************************************************/

    //Return friend ID
    //->id (int): friend unique ID
    getID: function()
    {
        return this.id;
    },

    //Return friend's status
    //->status (int): current friend status
    getStatus: function()
    {
        return this.status;
    },

    //Return corresponding user object
    //->user (object): friend's user object
    getUser: function()
    {
        return this.user;
    },

    /*******************************************************
    SET
    ********************************************************/

    //Set friend's status
    //#status (int): current status value
    //-> (void)
    setStatus: function(status)
    {
        this.status = status;
    }
});