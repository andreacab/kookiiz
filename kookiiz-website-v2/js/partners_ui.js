/*******************************************************
Title: Partners UI
Authors: Kookiiz Team
Purpose: Display and manage Kookiiz partners
********************************************************/

//Represents a user interface for partners
var PartnersUI = Class.create(
{
    object_name: 'partners_ui',

    /*******************************************************
    CONSTRUCTOR
    ********************************************************/

    //Class constructor
    //-> (void)
    initialize: function()
    {
        this.DISPLAY = $('partner_display');
    },

    /*******************************************************
    DISPLAY
    ********************************************************/

    //Display information on partner with provided ID
    //#partner_id (int): unique partner ID
    //-> (void)
    display: function(partner_id)
    {
        this.DISPLAY.loading();
        this.load(partner_id);
    },

    /*******************************************************
    LOAD
    ********************************************************/

    //Load partner information
    //#partner_id (int): ID of the partner
    //-> (void)
    load: function(partner_id)
    {
        if(partner_id > 0)
        {
            var params = {};
            params.callback = this.parse.bind(this);
            params.request  = 'partner_id=' + partner_id;
            Kookiiz.api.call('partners', 'load', params);
        }
    },

    /*******************************************************
    PARSE
    ********************************************************/

    //Create partner object from raw partner data
    //#response (object): server response object
    //-> (void)
    parse: function(response)
    {
        var data        = response.content
        var id          = parseInt(data.partner_id);
        var name        = data.partner_name.stripTags();
        var link        = data.partner_link.stripTags();
        var pic_link    = data.partner_pic.stripTags();
        var partner     = new Partner(id, name, link, pic_link);
        partner.display(this.DISPLAY);
    }
});