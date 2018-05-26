/*******************************************************
Title: Partner
Authors: Kookiiz Team
Purpose: Define the partner object
********************************************************/

//Represents a Kookiiz partner
var Partner = Class.create(
{
	object_name: 'partner',

    /*******************************************************
    CONSTRUCTOR
    ********************************************************/

    //Class constructor
    //#id (int):            ID of the partner
    //#name (string):       name of the partner
    //#link (string):       link to its website
    //#pic_link (string):   link to its banner
    //-> (void)
    initialize: function(id, name, link, pic_link)
    {
        this.id         = id;
        this.name       = name;
        this.link       = link;
        this.pic_link   = pic_link;
    },

    /*******************************************************
    DISPLAY
    ********************************************************/

	//Display partner banner in provided container
    //#container (DOM/string): container DOM element (or its ID)
    //-> (void)
	display: function(container)
    {
        container = $(container).clean();

        //Link
        var partner_link = new Element('a',
        {
            'href':     this.link,
            'target':   '_blank'
        });
        //Banner
        var partner_picture = new Element('img',
        {
            'alt':      this.name,
            'class':    'partner_banner click',
            'src':      this.pic_link,
            'title':    this.name
        });
        partner_link.appendChild(partner_picture);

        container.appendChild(partner_link).highlight();
    }
});