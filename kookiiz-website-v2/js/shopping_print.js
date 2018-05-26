/*******************************************************
Title: Shopping print
Authors: Kookiiz Team
Purpose: Display printable shopping list
********************************************************/

window.onload = init;

//Global scope objects
var Ingredients;
var User;

//Printing interface
var ShoppingPrint = new ShoppingPrintUI();

//Init window functionalities
function init()
{
    if(window.opener && window.opener.Kookiiz)
    {
        //Connect to global objects
        Ingredients = window.opener.Ingredients;
        User        = window.opener.User;

        //Retrieve shopping list object
        var list    = window.opener.Kookiiz.shopping.list_get();
        var order   = window.opener.Kookiiz.shopping.markets_order();
        
        //Init printing UI
        ShoppingPrint.init(list, order);
        ShoppingPrint.update();
    }
    else window.location = '/#' + URL_HASH_TABS[6] + '-' + SHOPPING_DAY;
}