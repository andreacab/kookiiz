<?php 
    header('content-type: application/x-javascript; charset=utf-8');
    include '../js/prototype.js'; 
?>

//Snoopy class
var Snoop = Class.create(
{
    object_name: 'snoop',
    
    /*******************************************************
    CONSTRUCTOR
    ********************************************************/
    
    //Class constructor
    //-> (void)
    initialize: function()
    {
        this.recipe  = {};
        this.loading = true;
    },
    
    /*******************************************************
    READ
    ********************************************************/
    
    //Read recipe sheet
    //-> (void)
    read: function()
    {
        //Retrieve recipe information according to website
        var website = window.location.hostname.replace('www.', ''),
            UID, LANG, UNITSYS, CATEGORIES, UNITS,
            title, guests, level, category, ingredients, quantities, units, steps, prep, cook;
        switch(website)
        {
            //iSaveurs
            case 'isaveurs.com':
                //Settings
                UID     = 52;
                LANG    = 'fr';
                UNITSYS = 'metric';
                CATEGORIES =
                {
                    'cok':  7, 
                    'ent':  2, 
                    'plt':  1,
                    'acc':  9,
                    'sau':  10, 
                    'des':  3
                };
                UNITS =
                {
                    'g':                    1, 
                    'pincée':               9, 
                    'cuillère à café':      8,
                    'cuillères à café':     8, 
                    '':                     10, 
                    'cl':                   5, 
                    'cuillère à soupe':     7, 
                    'cuillères à soupe':    7
                };

                //Main recipe properties
                title    = $('recette').select('h1')[0].innerHTML;
                guests   = parseInt($('people').value);
                level    = $('diff1') ? 0 : ($('diff2') ? 1 : 2);
                category = $('photo-recette').select('div')[0].id.split('-')[2];
                category = CATEGORIES[category] || -1;

                //Ingredient quantities
                ingredients = $('ingrList').select('li[itemprop=ingredient] span[itemprop=name]').map(function(e){return e.innerHTML.stripTags();});
                quantities  = $('ingrList').select('li[itemprop=ingredient] span[itemprop=amount]').map(function(e)
                {
                    var quantity = e.innerHTML.split(' ')[0].stripTags();
                    return quantity.replace(',', '.');
                });
                units = $('ingrList').select('li[itemprop=ingredient] span[itemprop=amount]').map(function(e)
                {
                    var unit_name = e.innerHTML.split(' ').slice(1).join(' ').strip().stripTags();
                    return UNITS[unit_name] || -1;
                });

                //Description steps
                steps = $('etapeRecette').select('li[itemprop=instruction] span').map(function(e){return e.innerHTML.stripTags();});

                //Preparation & Cooking times
                if($('prepa'))
                {
                    prep = $('prepa').select('span[itemprop=prepTime]')[0].innerHTML;
                    var prep_split = prep.split(' ');
                    if(prep_split[1] == 'minutes') 
                        prep = parseFloat(prep_split[0]);
                    else if(prep_split[1] == 'heure' || prep_split[1] == 'heures')
                    {
                        prep = 60 * parseFloat(prep_split[0]);
                        if(prep_split[2]) 
                            prep += 60 * parseFloat(prep_split[2]);
                    }
                }
                else 
                    prep = 0;
                if($('cuisson'))
                {
                    cook = $('cuisson').select('span[itemprop=cookTime]')[0].innerHTML;
                    var cook_split = cook.split(' ');
                    if(cook_split[1] == 'minutes') 
                        cook = parseFloat(cook_split[0]);
                    else if(cook_split[1] == 'heure' || cook_split[1] == 'heures')
                    {
                        cook = 60 * parseFloat(cook_split[0]);
                        if(cook_split[2]) 
                            cook += 60 * parseFloat(cook_split[2]);
                    }
                }
                else 
                    cook = 0;
                break;

            //Epicurious
            case 'epicurious.com':
                UID     = 0;
                LANG    = 'en';
                UNITSYS = 'imperial';
                UNITS =
                {
                    'tablespoon':   7,
                    'tablespoons':  7,
                    'teaspoon':     8,
                    'teaspoons':    8,
                    'cup':          17,
                    'cups':         17,
                    'ounce':        11,
                    'ounces':       11
                };

                //Main recipe properties
                title       = $('headline').select('h1.fn')[0].innerHTML;
                guests      = parseInt($('recipe_summary').select('.yield')[0].innerHTML.strip().split(' ')[1]);
                level       = -1;
                category    = -1;

                //Ingredients
                ingredients = [], quantities = [], units = [];
                var ing_data = $('ingredients').select('.ingredientsList li').map(function(ing){return ing.innerHTML;}),
                    split, qty, unit, unit_id, found;
                for(var i = 0, imax = ing_data.length; i < imax; i++)
                {
                    split = ing_data[i].strip().stripTags().split(' ');
                    for(var j = 0, jmax = split.length; j < jmax; j++)
                    {
                        qty = split[j];
                        if(!isNaN(qty))
                        {
                            unit    = split[j + 1] || '';
                            unit_id = typeof(UNITS[unit] != 'undefined') ? UNITS[unit] : -1;
                            
                            ingredients.push(split.splice(0, 2).join(' '));
                            quantities.push(parseFloat(qty));
                            units.push(unit_id);
                            found = true;
                            break;
                        }
                    }
                    if(!found)
                    {
                        ingredients.push(ing_data[i]);
                        quantities.push(0);
                        units.push(-1);
                    }
                }
                
                //Description steps
                steps = $('preparation').select('p.instruction').map(function(step){return step.innerHTML.strip().stripTags();});
                
                //Preparation & Cooking times
                prep = 0;
                cook = 0;
                break;

            default:
                throw new Exception('No settings found for website ' + website + '!');
                return;
                break;
        }
        
        //Store recipe information
        this.recipe =
        {
            'user_id':      UID,
            'lang':         LANG,
            'unitsys':      UNITSYS,
            'name':         title,
            'description':  steps,
            'ingredients':  ingredients,
            'quantities':   quantities,
            'units':        units,
            'guests':       guests,
            'prep':         prep,
            'cook':         cook,
            'level':        level,
            'category':     category
        };
    },
    
    /*******************************************************
    SEND
    ********************************************************/
    
    //Send recipe to Kookiiz server
    //-> (void)
    send: function()
    {
        //Create DOM elements machinery
        var el = $('kookiiz_snoop'), form = null;
        if(el)
            form = el.select('form')[0];
        else
        {
            el = new Element('div', {'id': 'kookiiz_snoop'});
            
            //Form
            form = new Element('form');
            form.method = 'post';
            form.action = 'http://www.kookiiz.com/admin/snoop.php';
            form.target = 'snoop';
            
            //Fake input
            var input = new Element('input', {'name': 'recipe'});
            input.value = Object.toJSON(this.recipe);
            form.appendChild(input);
            
            //Iframe target
            var iframe = new Element('iframe', {'name': 'snoop'});
            iframe.setStyle(
            {
                'position':     'absolute',
                'top':          '10px',
                'right':        '10px',
                'height':       '800px',
                'width':        '500px',
                'padding':      '5px',
                'background':   'White',
                'zIndex':       1000
            });
            
            //Append elements
            el.appendChild(form);
            el.appendChild(iframe);
            document.body.appendChild(el);
        }
        
        //Submit form programmatically
        form.submit();
    },
    
        
    /*******************************************************
    SNIFF
    ********************************************************/
    
    //Run Snoopy
    //-> (void)
    sniff: function()
    {
        try
        {
            this.read();
            this.send();
        }
        catch(e)
        {
            alert('Snoop error: ' + e);
            return;
        }
    }
});

//Init !
var Snoopy = new Snoop();
Snoopy.sniff();