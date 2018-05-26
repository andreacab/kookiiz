/*******************************************************
Title: Shopping item
Authors: Kookiiz Team
Purpose: Define the shopping item object
********************************************************/

//Represents a custom shopping list product
var ShoppingItem = Class.create(
{
    object_name: 'shopping_item',

    /*******************************************************
    CONSTRUCTOR
    ********************************************************/

    //Class constructor
    //#id (int):      shopping item ID
    //#text (string): shopping item description
    //#group (int):   ingredient group ID
    //-> (void)
    initialize: function(id, text, group)
    {
        this.id    = id;
        this.text  = text;
        this.group = group;
    },

    /*******************************************************
    COPY
    ********************************************************/

    //Copy shopping item object
    //->copy (object): copy of this object
    copy: function()
    {
        var copy = new ShoppingItem();
        copy.id    = this.id;
        copy.text  = this.text;
        copy.group = this.group;
        return copy;
    },

    /*******************************************************
    EXPORT
    ********************************************************/

    //Export item properties
    //->data (object): item data
    export_content: function()
    {
        var data = 
        {
            'i': this.id, 
            't': this.text, 
            'c': this.group
        }
        return data;
    }
});