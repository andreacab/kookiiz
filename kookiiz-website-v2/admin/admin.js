/*******************************************************
Title: Admin
Authors: Kookiiz Team
Purpose: Admin-restricted functionalities
********************************************************/

/*******************************************************
FEEDBACK
********************************************************/

//Represents a user interface for feedback management
var AdminFeedbackUI = Class.create(
{
    object_name: 'admin_feedback_ui',

    /*******************************************************
    CONSTANTS
    ********************************************************/

    STATS_WIDTH: 300,

    /*******************************************************
    CONSTRUCTOR
    ********************************************************/

    //Class constructor
    //-> (void)
    initialize: function()
    {
        //DOM elements
        this.$count   = $('admin_feedback_count');
        this.$display = $('feedback_container');
        this.$stats   = $('feedback_stats');
        this.$type    = $('admin_feedback_type');
    },

    /*******************************************************
    DISPLAY
    ********************************************************/

    //Display users feedback
    //#feedback (array): list of feedback data structures
    //-> (void)
    display: function(feedback)
    {
        this.$display.clean();

        if(feedback.length)
        {
            var feedback_table = new Element('table');

            //Headers
            var feedback_headers = new Element('thead'),
                header_row       = new Element('tr'),
                header_params    = new Element('th'),
                header_text      = new Element('th');
            header_params.innerHTML = 'Paramètres';
            header_text.innerHTML   = 'Commentaire';
            header_row.appendChild(header_params);
            header_row.appendChild(header_text);
            feedback_headers.appendChild(header_row);
            feedback_table.appendChild(feedback_headers);

            //Loop through feedbacks
            var feedback_content = new Element('tbody');
            feedback_table.appendChild(feedback_content);
            var id, date, time, type, user_name, content, text,
                feedback_row, params_cell, params_list, param_item;
            for(var i = 0, imax = feedback.length; i < imax; i++)
            {
                id          = feedback[i].id;
                date        = feedback[i].date;
                time        = feedback[i].time;
                type        = parseInt(feedback[i].type);
                user_name   = feedback[i].user_name;
                content     = feedback[i].content;
                text        = feedback[i].text;

                //Parameters
                feedback_row = new Element('tr');
                params_cell  = new Element('td');
                params_list  = new Element('ul');
                param_item   = new Element('li');
                feedback_row.setAttribute('data-id', id);
                feedback_content.appendChild(feedback_row);
                param_item.innerHTML = '<strong>Type</strong>: ' + FEEDBACK_TYPES[type];
                params_list.appendChild(param_item);
                param_item = param_item.cloneNode(false);
                param_item.innerHTML ='<strong>Date</strong>: ' + date + ' ' + time;
                params_list.appendChild(param_item);
                param_item = param_item.cloneNode(false);
                param_item.innerHTML ='<strong>Auteur</strong>: ' + user_name;
                params_list.appendChild(param_item);
                param_item = param_item.cloneNode(false);
                params_cell.appendChild(params_list);
                feedback_row.appendChild(params_cell);

                //Comment
                var text_cell = new Element('td');
                text_cell.innerHTML = '<strong>Concerne</strong>: ' + content + '<br/>' + text;
                feedback_row.appendChild(text_cell);
                
                //Deletion icon
                var iconDel = new Element('img',
                {
                    'alt':      ACTIONS[23],
                    'class':    'button15 cancel',
                    'src':      ICON_URL,
                    'title':    ACTIONS[23]
                });
                iconDel.observe('click', this.onRemoveClick.bind(this));
                text_cell.appendChild(iconDel);
            }
            this.$display.appendChild(feedback_table);
        }
        else 
            this.$display.innerHTML = ADMIN_FEEDBACK_ALERTS[0];
    },
    
    /*******************************************************
    INIT
    ********************************************************/

    //Init dynamic functionalities
    //-> (void)
    init: function()
    {
        //Observers
        $('admin_feedback_count').observe('change', this.update.bind(this));
        $('admin_feedback_type').observe('change', this.update.bind(this));
        $('admin_feedback_display').observe('click', this.update.bind(this));
        $('admin_feedback_stats').observe('click', this.statsLoad.bind(this));
        $('admin_feedback_enable').observe('click', this.onStatsEnable.bind(this));
        $('adminStatsCheckAll').observe('click', this.onStatsCheckAll.bind(this));
    },

    /*******************************************************
    LOAD
    ********************************************************/

    //Load feedback data from server
    //#type (int):  type of feedback data
    //#count (int): number of feedback to download
    //-> (void)
    load: function(type, count)
    {
        this.$display.loading(true);
        Kookiiz.api.call('feedback', 'load',
        {
            'callback': this.parse.bind(this),
            'request':  'type=' + type
                        + '&count=' + count
        });
    },
    
    /*******************************************************
    OBSERVERS
    ********************************************************/
   
    /**
     * Called when feedback deletion icon is clicked
     * #event (object): DOM click event
     */
    onRemoveClick: function(event)
    {
        var row = event.findElement('tr');
        if(row)
        {
            var feedback_id = parseInt(row.readAttribute('data-id'));
            this.remove(feedback_id);
            row.remove();
        }
    },
    
    /**
     * Called when the "check all" checkbox is checked/unchecked
     */
    onStatsCheckAll: function()
    {
        var checked = $('adminStatsCheckAll').checked == true;
        $$('.stat_item input').each(function(checkbox)
        {
            checkbox.checked = checked;
        });
    },
    
    /**
     * Called when stats enabling button is clicked
     */
    onStatsEnable: function()
    {
        var stats = $$('.stat_item');
        if(!stats.length) return;
        
        var questions = [], id, checkBox;
        stats.each(function(item)
        {
            checkBox = item.select('input')[0];
            if(checkBox && checkBox.checked)
            {
                id = parseInt(item.readAttribute('data-qid'));
                questions.push(id);
            }
        });
        this.statsEnable(questions);
    },

    /*******************************************************
    PARSE
    ********************************************************/

    //Parse feedback data from server
    //#response (object): server response object
    //-> (void)
    parse: function(response)
    {
        var action = response.parameters.action;
        switch(action)
        {
            case 'load':
                this.display(response.content);
                break;
            case 'stats':
                this.statsDisplay(response.content);
                break;
        }
    },
    
    /*******************************************************
    REMOVE
    ********************************************************/
   
    /**
     * Delete a feedback entry
     * #feedback_id (int): ID of the feedback entry
     * -> (void)
     */
    remove: function(feedback_id)
    {
        Kookiiz.api.call('feedback', 'delete',
        {
            'request': 'feedback_id=' + feedback_id
        });
    },
    
    /*******************************************************
    STATS
    ********************************************************/

    //Display feedback stats
    //#stats (array): list of feedback stats indexed by question ID
    //-> (void)
    statsDisplay: function(stats)
    {
        this.$stats.clean();

        var stats_list = new Element('ul'),
            id, yes, total, yes_percent, yes_width, no, no_percent, no_width,
            stat_item, question, yes_container, yes_label, yes_bar, yes_value,
            no_container, no_label, no_bar, no_value, enabled, checkEnable;
        for(var i = 0, imax = stats.length; i < imax; i++)
        {
            //Stats parameters
            id          = parseInt(stats[i].id);
            yes         = parseInt(stats[i].yes);
            total       = parseInt(stats[i].total);
            enabled     = parseInt(stats[i].enabled);
            yes_percent = Math.round(100 * yes / total);
            yes_width   = Math.round(yes_percent * this.STATS_WIDTH / 100);
            no          = total - yes;
            no_percent  = 100 - yes_percent;
            no_width    = Math.round(no_percent * this.STATS_WIDTH / 100);

            //DOM elements
            stat_item     = new Element('li', {'class': 'stat_item'});
            question      = new Element('div');
            yes_container = new Element('div', {'class': 'wrapper'});
            no_container  = new Element('div', {'class': 'wrapper'});
            yes_label     = new Element('div', {'class': 'label'});
            no_label      = new Element('div', {'class': 'label'});
            yes_bar       = new Element('div', {'class': 'bar yes back_color1'});
            no_bar        = new Element('div', {'class': 'bar no back_color3'});
            yes_value     = new Element('div', {'class': 'value'});
            no_value      = new Element('div', {'class': 'value'});
            checkEnable   = new Element('input', 
            {
                'class':    'enable',
                'checked':  enabled == 1,
                'type':     'checkbox'
            });
            stat_item.setAttribute('data-qid', id);
            yes_container.appendChild(yes_label);
            yes_container.appendChild(yes_bar);
            yes_container.appendChild(yes_value);
            no_container.appendChild(no_label);
            no_container.appendChild(no_bar);
            no_container.appendChild(no_value);
            stat_item.appendChild(question);
            stat_item.appendChild(yes_container);
            stat_item.appendChild(no_container);
            stat_item.appendChild(checkEnable);
            stats_list.appendChild(stat_item);

            //Question
            question.innerHTML = FEEDBACK_QUESTIONS[id];

            //Yes percentage
            yes_label.innerHTML = VARIOUS[13] + ' : ';
            yes_bar.setStyle({'width': yes_width + 'px'});
            yes_value.innerHTML = yes + ' (' + yes_percent + '%)';

            //No percentage
            no_label.innerHTML = VARIOUS[14] + ' : ';
            no_bar.setStyle({'width': no_width + 'px'});
            no_value.innerHTML = no + ' (' + no_percent + '%)';
        }

        if(stats_list.empty())  
            this.$stats.innerHTML = ADMIN_FEEDBACK_ALERTS[1];
        else
            this.$stats.appendChild(stats_list);
    },
    
    /**
     * Enable specific set of questions
     * #questions (array): list of question IDs
     * -> (void)
     */
    statsEnable: function(questions)
    {
        Kookiiz.popup.loader();
        Kookiiz.api.call('feedback', 'enable',
        {
            'callback': function()
                        {
                            Kookiiz.popup.alert({'text': ADMIN_FEEDBACK_ALERTS[2]});
                        },
            'request':  'questions=' + Object.toJSON(questions)
        });
    },

    //Load feedback stats
    //-> (void)
    statsLoad: function()
    {
        this.$stats.loading(true);
        Kookiiz.api.call('feedback', 'stats',
        {
            'callback': this.parse.bind(this)
        });
    },

    /*******************************************************
    UPDATE
    ********************************************************/

    //Update feedback UI
    //-> (void)
    update: function()
    {
        var count = parseInt(this.$count.value),
            type  = parseInt(this.$type.value);
        this.load(type, count);
    }
});

/*******************************************************
GLOSSARY
********************************************************/

//Represents a user interface for glossary terms edition
var AdminGlossaryUI = Class.create(
{
    object_name: 'admin_glossary_ui',

    /*******************************************************
    CONSTRUCTOR
    ********************************************************/

    //Class constructor
    //-> (void)
    initialize: function()
    {
        this.keyword_id = 0;    //Current keyword ID

        this.BUTTON_DELETE  = $('admin_glossary_delete');
        this.DEFINITION     = $('admin_glossary_definition');
        this.KEYWORD        = $('admin_glossary_keyword');
        this.LANG           = $('admin_glossary_lang');
    },

    /*******************************************************
    INIT
    ********************************************************/

    //Init dynamic functionalities
    //-> (void)
    init: function()
    {
        //Observers
        $('glossary_results').observe('click', this.keyword_click.bind(this));
        $('admin_glossary_clear').observe('click', this.clear.bind(this));
        $('admin_glossary_validate').observe('click', this.validate_click.bind(this));
        this.BUTTON_DELETE.observe('click', this.delete_click.bind(this));

        //Clear inputs
        this.clear();
    },

    /*******************************************************
    CLEAR
    ********************************************************/

    //Reset all glossary fields
    //-> (void)
    clear: function()
    {
        //Reset keyword ID
        this.keyword_id = 0;

        //Reset input fields
        this.KEYWORD.value      = this.KEYWORD.title;
        this.DEFINITION.value   = this.DEFINITION.title;
        this.KEYWORD.unfreeze();
        this.DEFINITION.unfreeze();
        this.LANG.unfreeze();

        //Clear glossary panel
        Kookiiz.glossary.clear();

        //Disable delete button
        this.BUTTON_DELETE.freeze();
    },

    /*******************************************************
    KEYWORDS
    ********************************************************/

    //Add a keyword to the glossary
    //#keyword (string):    name of the keyword
    //#definition (string): definition of the keyword
    //#lang (string):       language identifier
    //-> (void)
    keyword_add: function(keyword, definition, lang)
    {
        Kookiiz.popup.loader();

        //Check if keyword is long enough
        if(keyword.length < GLOSSARY_KEYWORD_MIN)
        {
            Kookiiz.popup.alert({'text': ADMIN_GLOSSARY_ALERTS[3]});
            return;
        }
        //Check if definition is long enough
        else if(definition.length < GLOSSARY_DEFINITION_MIN)
        {
            Kookiiz.popup.alert({'text': ADMIN_GLOSSARY_ALERTS[5]});
            return;
        }
        else
        {
            this.KEYWORD.freeze();
            this.DEFINITION.freeze();

            //Send request to add a keyword to glossary
            Kookiiz.api.call('glossary', 'add',
            {
                'callback': this.parse.bind(this),
                'request':  'keyword=' + keyword
                            + '&definition=' + definition
                            + '&lang=' + lang
            });
        }
    },

    //Remove a keyword from the glossary
    //Send request to edit glossary keyword
    //#keyword_id (int): ID of the keyword to remove
    //-> (void)
    keyword_delete: function(keyword_id)
    {
        Kookiiz.popup.loader();
        Kookiiz.api.call('glossary', 'delete',
        {
            'callback': this.parse.bind(this),
            'request':  'keyword_id=' + keyword_id
        });
    },

    //Edit a keyword from the glossary
    //#keyword_id (int):    ID of the kewyword to edit
    //#definition (string): updated definition of the keyword
    //#lang (string):       language identifier
    //-> (void)
    keyword_edit: function(keyword_id, definition, lang)
    {
        Kookiiz.popup.loader();

        //Check if keyword is long enough
        if(definition.length < GLOSSARY_DEFINITION_MIN)
        {
            Kookiiz.popup.alert({'text': ADMIN_GLOSSARY_ALERTS[5]});
            return;
        }
        else
        {
            this.KEYWORD.freeze();
            this.DEFINITION.freeze();

            //Send request to edit glossary keyword
            Kookiiz.api.call('glossary', 'edit',
            {
                'callback': this.parse.bind(this),
                'request':  'keyword_id=' + keyword_id
                            + '&definition=' + definition
                            + '&lang=' + lang
            });
        }
    },

    //Select a glossary keyword from search results for edition or deletion
    //#keyword_id (int):    ID of the selected keyword
    //#keyword (string):    name of the keyword
    //#definition (string): definition of the keyword
    //#lang (string):       language identifier
    //-> (void)
    keyword_select: function(keyword_id, keyword, definition, lang)
    {
        //Store current keyword ID
        this.keyword_id = keyword_id;

        //Set keyword properties
        this.KEYWORD.value      = keyword;
        this.DEFINITION.value   = definition;
        this.LANG.value_set(lang);

        //Enable/disable controls
        this.KEYWORD.freeze()
        this.BUTTON_DELETE.unfreeze();
    },

    /*******************************************************
    PARSE
    ********************************************************/

    //Called after a keyword has been added or edited
    //#response (object): server response object
    //-> (void)
    parse: function(response)
    {
        var action = response.parameters.action;
        switch(action)
        {
            case 'add':		
                Kookiiz.popup.alert({'text': ADMIN_GLOSSARY_ALERTS[0]});
                break;
            case 'delete':	
                Kookiiz.popup.alert({'text': ADMIN_GLOSSARY_ALERTS[2]}); 
                break;
            case 'edit':	
                Kookiiz.popup.alert({'text': ADMIN_GLOSSARY_ALERTS[1]});
                break;
        }

        //Clear all glossary input fields
        this.clear();
    },

    /*******************************************************
    CALLBACKS
    ********************************************************/

    //Called when user clicks the delete button
    //-> (void)
    delete_click: function()
    {
        var keyword = this.KEYWORD.value,
            message = ADMIN_GLOSSARY_ALERTS[4] + ' "' + keyword + '" ?';
        Kookiiz.popup.confirm(
        {
            'text':     message,
            'callback': this.delete_confirm.bind(this)
        });
    },

    //Called when admin confirms or cancels keyword deletion
    //#confirm (bool): true if admin cancels keyword deletion
    //-> (void)
    delete_confirm: function(confirm)
    {
        if(confirm)
            this.keyword_delete(this.keyword_id);
    },

    //Callback for click on glossary keyword
    //#event (event): DOM click event
    //-> (void)
    keyword_click: function(event)
    {
        //Retrieve glossary term element
        var glossary_term = event.findElement('dt');
        if(glossary_term)
        {
            //Retrieve keyword properties
            var keyword_id  = parseInt(glossary_term.id.split('_')[2]);
            var keyword     = glossary_term.innerHTML;
            var definition  = glossary_term.nextSibling.innerHTML;
            var lang        = glossary_term.id.split('_')[1];
            this.keyword_select(keyword_id, keyword, definition, lang);
        }
    },

    //Called when user clicks the validation button
    //-> (void)
    validate_click: function()
    {
        //Retrieve keyword parameters
        var definition  = encodeURIComponent(this.DEFINITION.value.stripTags());
        var lang        = this.LANG.value;

        //Edit or add keyword
        if(this.keyword_id) 
            this.keyword_edit(this.keyword_id, definition, lang);
        else
        {
            var keyword = encodeURIComponent(this.KEYWORD.value.stripTags());
            this.keyword_add(keyword, definition, lang);
        }
    }
});

/*******************************************************
INGREDIENTS
********************************************************/

//Represents a user interface to manage ingredients database
var AdminIngredientsUI = Class.create(
{
    object_name: 'admin_ingredients_ui',

    /*******************************************************
    CONSTRUCTOR
    ********************************************************/

    //Class constructor
    //-> (void)
    initialize: function()
    {
        this.SEASON_ING     = $('admin_season_ingredient');
        this.SEASON_MONTH   = $('admin_season_month');
    },

    /*******************************************************
    INIT
    ********************************************************/

    //Init dynamic functionalities
    //-> (void)
    init: function()
    {
        //Observers
        $('admin_season_create').observe('click', this.season_create_click.bind(this));
        
        //Ingredient suggestions
        Ingredients.autocompleter_init(this.SEASON_ING);
    },
    
    /*******************************************************
    SEASON
    ********************************************************/

    //Create an association "ingredient-month"
    //#ingredient_id (int): unique ingredient ID
    //#month (int):         month ID
    //-> (void)
    season_create: function(ingredient_id, month)
    {
        Kookiiz.popup.loader();
        Kookiiz.api.call('ingredients', 'season_create', 
        {
            'callback': this.season_created.bind(this),
            'request':  'ingredient_id=' + ingredient_id
                        + '&month=' + month
        });
    },

    //Called when ingreident-month pair has been saved
    //#response (object): server response object
    //-> (void)
    season_created: function(response)
    {
        Kookiiz.popup.hide();
        this.season_reset();
    },

    //Reset season fields
    //-> (void)
    season_reset: function()
    {
        this.SEASON_ING.value = this.SEASON_ING.title;
    },

    /*******************************************************
    CALLBACKS
    ********************************************************/

    //Called when admin clicks the season create button
    //-> (void)
    season_create_click: function()
    {
        var ingredient_name = this.SEASON_ING.value;
        var ingredient_id   = Ingredients.search(ingredient_name, 'id');
        if(ingredient_id)
        {
            var month = parseInt(this.SEASON_MONTH.value);
            this.season_create(ingredient_id, month);
        }
        else 
            Kookiiz.popup.alert({'text': ADMIN_INGREDIENTS_ALERTS[0]});
    }
});

/*******************************************************
PARTNERS
********************************************************/

//Represents a user interface to manage partners
var AdminPartnersUI = Class.create(
{
    object_name: 'admin_partners_ui',

    /*******************************************************
    CONSTRUCTOR
    ********************************************************/

    //Class constructor
    //-> (void)
    initialize: function()
    {
        this.partner_id = 0;    //Current partner ID

        //DOM elements
        this.BANNER = $('admin_partner_banner');
        this.LINK   = $('admin_partner_link');
        this.LIST   = $('admin_partner_select');
        this.NAME   = $('admin_partner_name');
        this.VALID  = $('admin_partner_valid');
    },

    /*******************************************************
    INIT
    ********************************************************/

    //Init dynamic functionalities
    //-> (void)
    init: function()
    {
        $('admin_partner_save').observe('click', this.save_click.bind(this));
        $('admin_partner_clear').observe('click', this.clear.bind(this));
        $('admin_partner_edit').observe('click', this.edit_click.bind(this));
        $('admin_partner_delete').observe('click', this.delete_click.bind(this));
    },

    /*******************************************************
    ADD
    ********************************************************/

    //Add a new partner to database
    //#name (string):       name of the partner
    //#link (string):       link to the partner's website
    //#pic_link (string):   link to partner's banner
    //#valid (bool):        should the partner be displayed
    //-> (void)
    add: function(name, link, pic_link, valid)
    {
        //Format parameters
        name        = encodeURIComponent(name);
        link        = encodeURIComponent(link);
        pic_link    = encodeURIComponent(pic_link);
        valid       = valid ? 1 : 0;

        //Create loader
        Kookiiz.popup.loader();

        //Send request
        Kookiiz.api.call('partners', 'add',
        {
            'callback': this.parse.bind(this),
            'request':  'name=' + name
                        + '&link=' + link
                        + '&pic_link=' + pic_link
                        + '&valid=' + valid
        });
    },

    /*******************************************************
    CLEAR
    ********************************************************/

    //Clear partner input fields
    //-> (void)
    clear: function()
    {
        this.partner_id     = 0;
        this.NAME.value     = this.NAME.title;
        this.LINK.value     = this.LINK.title;
        this.BANNER.value   = this.BANNER.title;
        this.VALID.checked  = false;
    },

    /*******************************************************
    DISPLAY
    ********************************************************/

    //Display a partner for edition
    //#partner (object): partner properties
    //-> (void)
    display: function(partner)
    {
        this.partner_id     = parseInt(partner.partner_id);
        this.NAME.value     = partner.partner_name;
        this.LINK.value     = partner.partner_link;
        this.BANNER.value   = partner.partner_pic;
        this.VALID.checked  = parseInt(partner.valid) == 1;
    },

    /*******************************************************
    EDIT
    ********************************************************/

    //Edit existing partner
    //#partner_id (int):    ID of the partner to edit
    //#name (string):       new name of the partner
    //#link (string):       new link to the partner's website
    //#pic_link (string):   link to partner's banner
    //#valid (bool):        should the partner be displayed
    //-> (void)
    edit: function(partner_id, name, link, pic_link, valid)
    {
        //Format parameters
        name        = encodeURIComponent(name);
        link        = encodeURIComponent(link);
        pic_link    = encodeURIComponent(pic_link);
        valid       = valid ? 1 : 0;

        //Create loader
        Kookiiz.popup.loader();

        //Send request
        Kookiiz.api.call('partners', 'edit',
        {
            'callback': this.parse.bind(this),
            'request':  'id=' + partner_id
                        + '&name=' + name
                        + '&link=' + link
                        + '&pic_link=' + pic_link
                        + '&valid=' + valid
        });
    },

    /*******************************************************
    LIST
    ********************************************************/

    //Update partners select menu
    //#partners (array): list of partner ID/name pairs
    //-> (void)
    list: function(partners)
    {
        //Remove current options (except default)
        var options = this.LIST.select('option'), partner_id;
        for(var i = 0, imax = options.length; i < imax; i++)
        {
            partner_id = options[i].value;
            if(partner_id) this.LIST.removeChild(options[i]);
        }

        //Add an option for each partner
        var id, name, option;
        for(i = 0, imax = partners.length; i < imax; i++)
        {
            id      = parseInt(partners[i].partner_id);
            name    = partners[i].partner_name;
            option  = new Element('option', {'value': id});
            option.innerHTML = name;
            this.LIST.appendChild(option);
        }
    },

    /*******************************************************
    LOAD
    ********************************************************/

    //Load a partner for edition
    //#partner_id (int): ID of the partner to load
    //-> (void)
    load: function(partner_id)
    {
        //Create loader
        Kookiiz.popup.loader();

        //Send request
        Kookiiz.api.call('partners', 'load',
        {
            'callback': this.parse.bind(this),
            'request':  'partner_id=' + partner_id + '&enforce=1'
        });
    },

    /*******************************************************
    PARSE
    ********************************************************/

    //Manage response from server
    //#response (object): server response object
    //-> (void)
    parse: function(response)
    {
        Kookiiz.popup.hide();

        var action = response.parameters.action;
        switch(action)
        {
            case 'add':
                this.clear();
                this.update();
                Kookiiz.popup.alert({'text': ADMIN_PARTNERS_ALERTS[0]});
                break;
            case 'delete':
                this.clear();
                this.update();
                Kookiiz.popup.alert({'text': ADMIN_PARTNERS_ALERTS[6]});
                break;
            case 'edit':
                this.clear();
                this.update();
                Kookiiz.popup.alert({'text': ADMIN_PARTNERS_ALERTS[7]});
                break;
            case 'list':
                this.list(response.content);
                break;
            case 'load':
                this.display(response.content);
                break;
        }
    },

    /*******************************************************
    REMOVE
    ********************************************************/

    //Delete a partner from database
    //#partner_id (int): ID of the partner to remove
    //-> (void)
    remove: function(partner_id)
    {
        //Create loader
        Kookiiz.popup.loader();

        //Send request
        Kookiiz.api.call('partners', 'delete',
        {
            'callback': this.parse.bind(this),
            'request':  'partner_id=' + partner_id
        });
    },

    /*******************************************************
    UPDATE
    ********************************************************/

    //Called when partners are updated
    //-> (void)
    update: function()
    {
        //Download new partners list from server
        Kookiiz.api.call('partners', 'list',
        {
            'callback': this.parse.bind(this)
        });
    },

    /*******************************************************
    CALLBACKS
    ********************************************************/

    //Called upon click on the partner delete button
    //-> (void)
    delete_click: function()
    {
        var partner_id = parseInt(this.LIST.value),
            partner_name = this.LIST.childElements()[this.LIST.selectedIndex].innerHTML;
        if(partner_id)
        {
            Kookiiz.popup.confirm(
            {
                'text':     ADMIN_PARTNERS_ALERTS[5] + ' "' + partner_name + '" ?',
                'callback': this.delete_confirm.bind(this, partner_id)
            });
        }
        else 
            Kookiiz.popup.alert({'text': ADMIN_PARTNERS_ALERTS[4]});
    },

    //Called when user cancels or confirms partner deletion process
    //#partner_id (int):    ID of partner to delete
    //#confirm (bool):      true if the action is confirmed
    //-> (void)
    delete_confirm: function(partner_id, confirm)
    {
        if(confirm)
            this.remove(partner_id);
    },

    //Called upon click on the partner delete button
    //-> (void)
    edit_click: function()
    {
        var partner_id = parseInt(this.LIST.value);
        if(partner_id)  
            this.load(partner_id);
        else            
            Kookiiz.popup.alert({'text': ADMIN_PARTNERS_ALERTS[4]});
    },

    //Called upon click on the partner add button
    //-> (void)
    save_click: function()
    {
        //Retrieve and check partner values
        var name = this.NAME.value.stripTags();
        if(!name || name == this.NAME.title)
        {
            Kookiiz.popup.alert({'text': ADMIN_PARTNERS_ALERTS[1]});
            return;
        }
        var link = this.LINK.value.stripTags();
        if(!link || link == this.LINK.title)
        {
            Kookiiz.popup.alert({'text': ADMIN_PARTNERS_ALERTS[2]});
            return;
        }
        var pic_link = this.BANNER.value.stripTags();
        if(!pic_link || pic_link == this.BANNER.title)
        {
            Kookiiz.popup.alert({'text': ADMIN_PARTNERS_ALERTS[3]});
            return;
        }
        var valid = this.VALID.checked;

        //Call appropriate function depending on mode
        var mode = this.partner_id ? 'edit' : 'add';
        if(mode == 'add')   
            this.add(name, link, pic_link, valid);
        else                
            this.edit(this.partner_id, name, link, pic_link, valid);
    }
});

/*******************************************************
RECIPES
********************************************************/

//Represents a user interface for recipes management
var AdminRecipesUI = Class.create(
{
    object_name: 'admin_recipes_ui',

    /*******************************************************
    CONSTRUCTOR
    ********************************************************/

    //Class constructor
    //-> (void)
    initialize: function()
    {
        this.tagRecipeID = 0;     //ID of the recipe beeing tagged
    },
    
    /*******************************************************
    DISMISS
    ********************************************************/
   
    //Dismiss a recipe
    //#recipe_id (int): unique recipe ID
    //-> (void)
    dismiss: function(recipe_id)
    {
        Kookiiz.api.call('recipes', 'dismiss',
        {
            'callback': function()
                        {
                            Kookiiz.popup.alert({'text': ADMIN_RECIPES_ALERTS[8]});
                        },
            'request':  'recipe_id=' + recipe_id
        });
    },
    
    /*******************************************************
    INIT
    ********************************************************/

    //Init dynamic functionalities
    //-> (void)
    init: function()
    {
        $('admin_recipe_delete').onclick = this.onDeleteClick.bind(this);
        $('admin_recipe_dismiss').onclick = this.onDismiss.bind(this);
        $('admin_recipe_tags').onclick = this.onTag.bind(this);
        $('admin_recipes_dismisslist').onclick = this.onDismissList.bind(this);
    },
    
    /*******************************************************
    LIST
    ********************************************************/
   
    //List dismissed recipes
    //#recipes (array): list of recipe data
    //-> (void)
    listDismissed: function(recipes)
    {
        var container = $('adminRecipesDismissList').clean(),
            list = new Element('ul'), item, link, buttonDel, buttonVal;
            
        for(var i = 0, imax = recipes.length; i < imax; i++)
        {
            item = new Element('li');
            item.setAttribute('data-id', recipes[i].id);
            link = new Element('a', {'href': '/#/' + URL_HASH_TABS[4] + '-' + recipes[i].id});
            buttonVal = new Element('img', {'alt': ACTIONS[2], 'class': 'button15 accept', 'src': ICON_URL, 'title': ACTIONS[2]});
            buttonDel = new Element('img', {'alt': ACTIONS[23], 'class': 'button15 cancel', 'src': ICON_URL, 'title': ACTIONS[23]});
            link.appendChild(document.createTextNode(recipes[i].name));
            item.appendChild(link);
            item.appendChild(buttonVal);
            item.appendChild(buttonDel);
            list.appendChild(item);
        }
        
        if(list.empty())
            container.appendChild(document.createTextNode(ADMIN_RECIPES_ALERTS[9]));
        else
        {
            list.observe('click', this.onDismissAction.bind(this));
            container.appendChild(list);
        }
    },
    
    /*******************************************************
    OBSERVERS
    ********************************************************/

    //Called when admin clicks on recipe delete button
    //-> (void)
    onDeleteClick: function()
    {
        var recipe_id = Kookiiz.recipes.displayed_get(),
            recipe = Recipes.get(recipe_id);
        Kookiiz.popup.confirm(
        {
            'text':     ADMIN_RECIPES_ALERTS[1] + '"' + recipe.name + '" ?',
            'callback': this.onDeleteConfirm.bind(this, recipe_id)
        });
    },

    //Called when the user confirms or cancels recipe deletion in popup
    //#recipe_id (int): unique recipe ID
    //#confirm (bool):  true if the user confirms the action
    //-> (void)
    onDeleteConfirm: function(recipe_id, confirm)
    {
        if(confirm)
            this.suppress(recipe_id);
    },
   
    //Called when admin chooses to dismiss a recipe
    //Ask for confirmation
    //-> (void)
    onDismiss: function()
    {
        var recipe_id = Kookiiz.recipes.displayed_get(),
            recipe = Recipes.get(recipe_id);
        Kookiiz.popup.confirm(
        {
            'text':     ADMIN_RECIPES_ALERTS[7] + '"' + recipe.name + '" ?',
            'callback': this.onDismissConfirm.bind(this, recipe_id)
        });
    },
    
    //Called when the list of dismissed recipes is clicked
    //-> (void)
    onDismissAction: function(event)
    {
        var button = event.findElement('.button15');
        if(button)
        {
            var item = event.findElement('li'),
                recipe_id = item.readAttribute('data-id'),
                recipe_name = item.select('a')[0].innerHTML;
            
            if(button.hasClassName('accept'))
            {
                this.validate(recipe_id);
                item.remove();
            }
            else if(button.hasClassName('cancel'))
            {
                var self = this;
                Kookiiz.popup.confirm(
                {
                    'text':     ADMIN_RECIPES_ALERTS[1] + '"' + recipe_name + '" ?',
                    'callback': function(confirm)
                                {
                                    if(confirm)
                                    {
                                        self.suppress(recipe_id);
                                        item.remove();
                                    }
                                }
                });
            }
        }
    },
    
    //Called when admin chooses to display the list of dismissed recipes
    //-> (void)
    onDismissList: function()
    {
        $('adminRecipesDismissList').loading(true);
        Kookiiz.api.call('recipes', 'list_dismiss',
        {
            'callback': this.onDismissListReady.bind(this)
        });
    },
    
    //Called when list of dismissed recipes is returned by the server
    //#response (object): server response object
    //-> (void)
    onDismissListReady: function(response)
    {
        this.listDismissed(response.content);
    },
    
    //Called when dismiss process is confirmed or canceled
    //#recipe_id (int): unique recipe ID
    //#confirm (bool):  
    //-> (void)
    onDismissConfirm: function(recipe_id, confirm)
    {
        if(confirm)
            this.dismiss(recipe_id);
    },
    
    //Open popup to edit recipe tags
    //-> (void)
    onTag: function()
    {
        //Update ID of recipe being tagged
        this.tagRecipeID = Kookiiz.recipes.displayed_get();

        //Open tagging popup
        Kookiiz.popup.custom(
        {
            'title':                ADMIN_RECIPES_ALERTS[3],
            'text':                 ADMIN_RECIPES_ALERTS[4],
            'confirm':              true,
            'content_url':          '/dom/recipes_tags_popup.php',
            'content_parameters':   'recipe_id=' + this.tagRecipeID,
            'content_init':         this.tagsInit.bind(this)
        });
    },
    
    //Called when a tag is added
    //-> (void)
    onTagAdd: function()
    {
        var tag_id = parseInt($('recipe_tag_select').value);
        if(tag_id) this.tagAdd(tag_id);
    },
    
    //Called when a tag deletion icon is clicked
    //#event (event): DOM click event
    //-> (void)
    onTagDelete: function(event)
    {
        var tag_id = parseInt(event.findElement('.tag_item').id.split('_')[2]);
        if(tag_id) this.tagDelete(tag_id);
    },
    
    /*******************************************************
    SUPPRESS
    ********************************************************/

    //Call PHP script to delete a recipe
    //#recipe_id (int): ID of the recipe to delete
    //-> (void)
    suppress: function(recipe_id)
    {
        Kookiiz.api.call('recipes', 'delete',
        {
            'callback': function(response)
                        {
                            Kookiiz.popup.alert({'text': ADMIN_RECIPES_ALERTS[6]});
                        },
            'request':  'recipe_id=' + recipe_id
        });
    },

    /*******************************************************
    TAGS
    ********************************************************/

    //Add a tag to the recipe
    //#tag_id (int): ID of the tag to add
    //-> (void)
    tagAdd: function(tag_id)
    {
        var params = 'action=save&tag_id=' + tag_id + '&recipe_id=' + this.tagRecipeID;
        Kookiiz.popup.reload(params);
    },

    //Delete a given tag
    //#tag_id (int): ID of the tag to remove
    //-> (void)
    tagDelete: function(tag_id)
    {
        var params = 'action=delete&tag_id=' + tag_id + '&recipe_id=' + this.tagRecipeID;
        Kookiiz.popup.reload(params);
    },

    //Called once the tag popup has been loaded
    //-> (void)
    tagsInit: function()
    {
        var tag_selector = $('recipe_tag_select');
        if(tag_selector.empty())
        {
          tag_selector.hide();
          $('recipe_tags_add').hide();
          $('recipe_notag_caption').show();
        }
        else
        {
          $('recipe_tags_add').observe('click', this.onTagAdd.bind(this));
          $('recipe_notag_caption').hide();
        }
        $('recipes_tags_popup').select('.tag_item .cancel').invoke('observe', 'click', this.onTagDelete.bind(this));
    },
    
    /*******************************************************
    VALIDATE
    ********************************************************/
   
    //Validate a dismissed recipe
    //#recipe_id (int): unique recipe ID
    //-> (void)
    validate: function(recipe_id)
    {
        Kookiiz.api.call('recipes', 'validate',
        {
            'request':  'recipe_id=' + recipe_id
        });
    }
});

/*******************************************************
USERS
********************************************************/

//Represents a user interface for users management
var AdminUsersUI = Class.create(
{
    object_name: 'admin_users_ui',

    /*******************************************************
    CONSTRUCTOR
    ********************************************************/

    //Class constructor
    //-> (void)
    initialize: function()
    {
    },

    /*******************************************************
    INIT
    ********************************************************/

    //Init dynamic functionalities
    //-> (void)
    init: function()
    {
        if(user_isadminsup())
            $('admin_user_elect').observe('click', this.elect_click.bind(this));
    },

    /*******************************************************
    ELECT
    ********************************************************/

    //Elect user to admin status
    //-> (void)
    elect: function(user_id)
    {
        Kookiiz.api.call('users', 'admin_elect',
        {
            'callback': this.elected.bind(this),
            'request':  'user_id=' + user_id
        });
    },

    //Called when the election process is over
    //#response (object): server response object
    //-> (void)
    elected: function(response)
    {
        Kookiiz.popup.alert({'text': ADMIN_USERS_ALERTS[1]});
    },

    /*******************************************************
    CALLBACKS
    ********************************************************/

    //Called when super admin choose to elect user as admin
    //-> (void)
    elect_click: function()
    {
        var user_id = parseInt($('admin_user_admin_id').value);
        if(user_id)
            this.elect(user_id);
        else        
            Kookiiz.popup.alert({'text': ADMIN_USERS_ALERTS[0]});
    }
});

/*******************************************************
ADMIN OBJECT
********************************************************/

//Structure to encapsulate all admin UIs
var Admin =
{
    feedback:       new AdminFeedbackUI(),
    glossary:       new AdminGlossaryUI(),
    ingredients:    new AdminIngredientsUI(),
    partners:       new AdminPartnersUI(),
    recipes:        new AdminRecipesUI(),
    users:          new AdminUsersUI(),

    /*******************************************************
    INIT
    ********************************************************/

    //Init dynamic functionalities
    //-> (void)
    init: function()
    {
        //Init UI components
        this.feedback.init();
        this.glossary.init();
        this.ingredients.init();
        this.partners.init();
        this.recipes.init();
        this.users.init();
    }
};