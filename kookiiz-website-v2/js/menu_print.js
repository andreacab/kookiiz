/*******************************************************
Title: Menu print
Authors: Kookiiz Team
Purpose: Functionalities of the menu printing window
********************************************************/

window.onload = init;

//Global scope objects
var Kookiiz;
var Recipes;
var User;

//Printing UI
var MenuPrint = new MenuPrintUI();

//Init window functionalities
function init()
{
    if(window.opener && window.opener.Kookiiz)
	{
        //Connect to global objects
        Kookiiz = window.opener.Kookiiz;
        Recipes = window.opener.Recipes;
        User    = window.opener.User;

        //Init printing UI
        MenuPrint.init();
        MenuPrint.update();
    }
    else window.location = '/';
}