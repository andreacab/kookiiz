/*******************************************************
Title: Chart
Authors: Kookiiz Team
Purpose: User interface for the members chart
********************************************************/

//Represents a user interface for the members chart
var ChartUI = Class.create(
{
    object_name: 'chart_ui',

    /*******************************************************
    CONSTRUCTOR
    ********************************************************/

    //Class constructor
    //-> (void)
    initialize: function()
    {
        //Is members chart loaded?
        this.loaded = false;

        //DOM elements
        this.$chart = $('members_chart');
    },

    /*******************************************************
    LOAD
    ********************************************************/

    //Load members chart from server
    //-> (void)
    load: function()
    {
        if(!this.loaded && user_logged())
        {
            this.$chart.loading(true);
            Kookiiz.ajax.request('/dom/members_chart.php', 'post',
            {
                'callback': this.parse.bind(this),
                'json':     false
            });
        }
    },

    /*******************************************************
    PARSE
    ********************************************************/

    //Parse members chart content
    //#content (DOM): HTML content of the members chart
    //-> (void)
    parse: function(content)
    {
        this.$chart.clean().innerHTML = content;
        this.loaded = true;
    }
})