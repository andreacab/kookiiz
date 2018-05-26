/*******************************************************
Title: Recipe print
Authors: Kookiiz Team
Purpose: Functionalities of recipe printing window
********************************************************/

//Global scope objects
var Ingredients;
var Kookiiz;
var Recipes;
var User;

//Printing interface
var RecipePrint = new RecipePrintUI(RECIPE_ID);

//Init window functionalities
window.onload = function()
{
    if(window.opener && window.opener.Kookiiz)
    {
        //Connect to global objects
        Kookiiz     = window.opener.Kookiiz;
        Ingredients = window.opener.Ingredients;
        Recipes     = window.opener.Recipes;
        User        = window.opener.User;

        //Init printing UI
        RecipePrint.init();
        RecipePrint.update();
    }
    else
        window.location = '/#' + URL_HASH_TABS[4] + '-' + RECIPE_ID;
}