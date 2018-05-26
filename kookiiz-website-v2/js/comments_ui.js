/*******************************************************
Title: Comments UI
Authors: Kookiiz Team
Purpose: Functionalities of the comments user interface
********************************************************/

//Represents a user interface for comments
var CommentsUI = Class.create(
{
    object_name: 'comments_ui',

    /*******************************************************
    CONSTANTS
    ********************************************************/

    TYPE_DEFAULT: COMMENT_TYPE_PUBLIC, //Default comment type

    /*******************************************************
    CONSTRUCTOR
    ********************************************************/

    //Class constructor
    //-> (void)
    initialize: function()
    {
        //Comments and index DOM containers
        this.COMMENTS =
        {
            'recipe':   $('recipe_comments')
        };
        this.INDEX =
        {
            'recipe':   $('recipe_comments_index')
        };

        //Comment controls
        this.COUNT_SELECT =
        {
            'recipe':   $('recipe_comments_count')
        };
        this.PER_PAGE =
        {
            'recipe':   $('recipe_comments_perpage')
        };
        this.TYPE_SELECT =
        {
            'recipe':   $('recipe_comments_type')
        };

        //Comments input
        this.inputChars = $('textarea_comment_chars');
        this.inputClear = $('button_clear_comment');
        this.inputField = $('textarea_comment');
        this.inputSend  = $('button_send_comment');
        this.inputType  = $('select_comment_type');
    },
    
    /*******************************************************
    CLEAR
    ********************************************************/

    //Clear comments and index for specific content
    //#content_type (string): type of content (article or recipe)
    //-> (void)
    clear: function(content_type)
    {
        this.COMMENTS[content_type].clean();
        this.INDEX[content_type].clean();
    },

    //Clear comment input fields
    //-> (void)
    clear_inputs: function()
    {
        this.inputField.value = '';
        this.inputChars.clean();
    },

    /*******************************************************
    CONTEXT
    ********************************************************/

    //Return current comment context
    //->context (object or bool): properties of the current context (or false)
    context_get: function()
    {
        //Retrieve current content type and ID
        var content_type = '', content_id = 0;
        switch(Kookiiz.tabs.current_get())
        {
            case 'article_display':
                content_type = 'article';
                content_id = Kookiiz.articles.displayed_get();
                break;
            case 'recipe_full':
                content_type = 'recipe';
                content_id = Kookiiz.recipes.displayed_get();
                break;
        }
        if(content_type && content_id)
        {
            //Retrieve comments parameters
            var comment_type   = parseInt(this.TYPE_SELECT[content_type].value),
                comment_count  = parseInt(this.COUNT_SELECT[content_type].value),
                index_selected = this.INDEX[content_type].select('.selected')[0],
                comment_page   = index_selected ? parseInt(index_selected.id.split('_')[2]) : 0;
            var context =
            {
                'content_type': content_type,
                'content_id':   content_id,
                'type':         comment_type,
                'count':        comment_count,
                'page':         comment_page
            };
            return context;
        }
        else 
            return false;
    },

    /*******************************************************
    CREATE
    ********************************************************/

    //Create a request to save a new comment in database
    //#content_type (string):   type of content the comment is related to
    //#content_id (int):        ID of the content the comment is related to
    //#type (int):              type of comment (private or public)
    //#text (string):           text of the comment
    //-> (void)
    create: function(content_type, content_id, type, text)
    {
        //Check comment length
        if(text.length < COMMENT_LENGTH_MIN)
        {
            Kookiiz.popup.alert({'text': COMMENTS_ERRORS[5]});
            return;
        }
        else if(text.length > COMMENT_LENGTH_MAX)
        {
            Kookiiz.popup.alert({'text': COMMENTS_ERRORS[6]});
            return;
        }

        //Select comment type and display loader
        this.TYPE_SELECT[content_type].value_set(type);
        this.COMMENTS[content_type].loading();

        //Create ajax request
        var context = this.context_get();
        Kookiiz.api.call('comments', 'save',
        {
            'callback': this.parse.bind(this),
            'request':  'content_type=' + content_type
                        + '&content_id=' + content_id
                        + '&type=' + type
                        + '&text=' + encodeURIComponent(text)
                        + '&count=' + context.count
                        + '&page=0'
        });
    },

    /*******************************************************
    DISPLAY
    ********************************************************/

    //Display comments
    //#container (DOM or string):   DOM element (or ID of) inside which to display comments
    //#comments (array):            list of comment objects
    //#content_type (string):       type of content the comments are related to ("recipe", "article", etc.)
    //#type (int):                  type of the comments to display (private, public...)
    //-> (void)
    display: function(container, comments, content_type, type)
    {
        container = $(container).clean();

        if(comments.length)
        {
            //Sort comments
            comments.sort(this.sort.bind(this));

            //Loop through comments
            var options = {'callback': this.element_action.bind(this)},
                comments_list = new Element('ul'), item = null;
            for(var i = 0, imax = comments.length; i < imax; i++)
            {
                options.side = i % 2 ? 'left' : 'right';
                item = comments[i].build(options);
                if(item) 
                    comments_list.appendChild(item);
            }
            container.appendChild(comments_list);
        }
        //No comment to display
        else 
            container.innerHTML = type ? COMMENTS_ALERTS[3] : COMMENTS_ALERTS[4];
    },

    /*******************************************************
    EDIT
    ********************************************************/

    //Edit text of existing comment (private notes only)
    //#content_type (string):   type of content the comment is related to
    //#id (int):                ID of the comment to edit
    //#text (string):           edited text of the comment
    //-> (void)
    edit: function(content_type, id, text)
    {
        //Send request to edit comment
        Kookiiz.api.call('comments', 'edit',
        {
            'request':  'content_type=' + content_type
                        + '&id=' + id
                        + '&text=' + encodeURIComponent(text)
        });
    },

    /*******************************************************
    FETCH
    ********************************************************/

    //Fetch comments data from server
    //#comments_data (array):   arrays of comments properties
    //#content_type (string):   type of content the comments are related to ("article", "recipe", etc.)
    //#content_id (int):        ID of the content (article, recipe) the comments are related to
    //->comments (array): list of comment objects
    fetch: function(data, content_type, content_id)
    {
        //Loop through comments
        var comments = [],
            id, type, user_id, user_name, text, rating, date, time;
        for(var i = 0, imax = data.length; i < imax; i++)
        {
            id        = parseInt(data[i].id);
            type      = parseInt(data[i].type);
            user_id   = parseInt(data[i].user.id);
            user_name = data[i].user.name.stripTags();
            text      = decodeURIComponent(data[i].text.stripTags());
            rating    = parseInt(data[i].rate);
            date      = data[i].date;
            time      = data[i].time;

            //Store current comment
            comments.push(new Comment(id, type, content_type, content_id, user_id,
                                        user_name, text, rating, date, time));
        }
        return comments;
    },
    
    /*******************************************************
    INDEX
    ********************************************************/

    //Create an index for comments and hide all comments lists except first one
    //#container (DOM or string):   DOM element (or ID of) inside which to display comments index
    //#content_type (string):       type of content the comments are related to ("recipe", "article", etc.)
    //#count (int):                 number of comments to display per page
    //#total (int):                 total number of comments for this content
    //#page (int):                  currently selected page
    //-> (void)
    index_build: function(container, content_type, count, total, page)
    {
        container = $(container).clean();
        if(total > count)
        {
            container.innerHTML = 'Page ';
            
            //Loop through pages
            var index_text = null;
            for(var i = 0, imax = total; i < imax; i += count)
            {
                index_text = new Element('span',
                {
                    'class':    i == page ? 'comment_index selected' : 'comment_index unselected',
                    'id':       content_type + '_commentindex_' + i
                });
                if(i) 
                    index_text.observe('click', this.index_switch.bind(this));
                
                index_text.innerHTML = (Math.round(i / count) + 1) + '\t';
                container.appendChild(index_text);
            }
            container.display();
        }
        else
            container.hide();
    },

    //Switch between comments pages when an index is clicked
    //#event (event): DOM click event
    //-> (void)
    index_switch: function(event)
    {
        //Retrieve index parameters
        var context = this.context_get();
        if(context)
        {
            //Load comments for selected page
            var index_el = event.findElement(),
                index = parseInt(index_el.id.split('_')[2]);
            this.COMMENTS[context.content_type].loading();
            this.load(context.content_type, context.content_id, context.type, context.count, index);

            //Change selected index
            var index_container = this.INDEX[context.content_type],
                index_items = index_container.childElements(),
                current_item, current_index;
            for(var i = 0, imax = index_items.length; i < imax; i++)
            {
                current_item  = index_items[i];
                current_index = parseInt(current_item.id.split('_')[2]);
                if(current_index == index)
                {
                    current_item.removeClassName('unselected');
                    current_item.addClassName('selected');
                    current_item.stopObserving('click');
                }
                else
                {
                    current_item.removeClassName('selected');
                    current_item.addClassName('unselected');
                    current_item.stopObserving('click');
                    current_item.observe('click', this.index_switch.bind(this));
                }
            }
        }
    },
    
    /*******************************************************
    INIT
    ********************************************************/

    //Init dynamic functionalities
    //-> (void)
    init: function()
    {
        //Display controls
        var control;
        for(control in this.COUNT_SELECT)
        {
            this.COUNT_SELECT[control].value_set(COMMENTS_COUNT_DEFAULT);
            this.COUNT_SELECT[control].observe('change', this.count_change.bind(this));
        }
        for(control in this.TYPE_SELECT)
        {
            this.TYPE_SELECT[control].value_set(this.TYPE_DEFAULT);
            this.TYPE_SELECT[control].observe('change', this.type_change.bind(this));
        }

        //Panel controls
        if(user_logged())
        {
            this.inputClear.observe('click', this.clear_click.bind(this));
            this.inputSend.observe('click', this.new_click.bind(this));
            this.inputField.observe('keyup', this.text_keyup.bind(this));
        }
    },
    
    /*******************************************************
    LOAD
    ********************************************************/

    //Load comments related to specified content
    //#content_type (string):   content type identifier ("article", "recipe", etc.)
    //#content_id (int):        ID of the related content (article or recipe)
    //#type (int):              type of comments (private or public)
    //#count (int):             number of comments per page
    //#page (int):              comment page number
    //#callback (function):     function to call with server response
    //-> (void)
    load: function(content_type, content_id, type, count, page, callback)
    {
        if(typeof(page) == 'undefined')     page = 0;
        if(typeof(callback) == 'undefined') callback = this.parse.bind(this);

        //Create request for comments content
        Kookiiz.api.call('comments', 'load',
        {
            'callback': callback,
            'request':  'content_type=' + content_type
                        + '&content_id=' + content_id
                        + '&type=' + type
                        + '&count=' + count
                        + '&page=' + page
        });
    },

    /*******************************************************
    PARSE
    ********************************************************/

    //Receive comments data from server
    //#response (object): response from server
    //-> (void)
    parse: function(response)
    {
        //Retrieve comments content and parameters
        var comments_data = response.content.data,
            content_type  = response.parameters.content_type,
            content_id    = parseInt(response.parameters.content_id),
            type          = parseInt(response.parameters.type),
            count         = parseInt(response.parameters.count),
            page          = parseInt(response.parameters.page),
            total         = parseInt(response.content.total);

        //Parse comments
        var comments = this.fetch(comments_data, content_type, content_id);

        //Display comments and build index
        this.display(this.COMMENTS[content_type], comments, content_type, type);
        if(type == COMMENT_TYPE_PUBLIC) 
            this.index_build(this.INDEX[content_type], content_type, count, total, page);
        else                            
            this.INDEX[content_type].clean();
    },

    /*******************************************************
    POPUP
    ********************************************************/

    //Init popup functionalities when it has been loaded
    //-> (void)
    popup_init: function()
    {
        $('comments_popup_input').observe('keyup', this.text_keyup.bind(this));
    },

    //Open the comment popup when user rates a recipe
    //#recipe_id (int): the recipe that has just been rated
    //#rating (int):    the rating that was selected for the recipe
    //-> (void)
    popup_open: function(recipe_id, rating)
    {
        Kookiiz.popup.custom(
        {
            'text':         COMMENTS_TEXT[5],
            'title':        COMMENTS_TEXT[4],
            'confirm':      true,
            'cancel':       true,
            'callback':     this.popup_confirm.bind(this, recipe_id, rating),
            'content_url':  '/dom/comments_popup.php',
            'content_init': this.popup_init.bind(this)
        });
    },

    //Called when user closes the popup
    //#recipe_id (int): unique recipe ID
    //#rating (int):    user's rating for this recipe
    //#confirm (bool):  true if the user chose to add a comment, false otherwise
    //-> (void)
    popup_confirm: function(recipe_id, rating, confirm)
    {
        //Save rating
        Kookiiz.recipes.rating_save(recipe_id, rating);

        //If user chose to add a comment
        if(confirm)
        {
            var input = $('comments_popup_input'), text = input.value.stripTags();
            if(text && text != input.title)
                this.create('recipe', recipe_id, COMMENT_TYPE_PUBLIC, text);
        }
    },

    /*******************************************************
    RATE
    ********************************************************/

    //Store comment evaluation
    //#content_type (string):   type of content the comment is related to
    //#id (int):                ID of the comment to rate
    //#rating (int):            rating attributed to the comment
    //-> (void)
    rate: function(content_type, id, rating)
    {
        if(user_logged())
        {
            Kookiiz.api.call('comments', 'rate', 
            {
                'request':  'content_type=' + content_type
                            + '&id=' + id
                            + '&rating=' + rating
            });
        }
    },
    
    /*******************************************************
    RESET
    ********************************************************/
   
    //Reset comments controls
    //#content_type (string): type of content to reset controls for
    //-> (void)
    reset: function(content_type)
    {
        this.COUNT_SELECT[content_type].value_set(COMMENTS_COUNT_DEFAULT);
        this.TYPE_SELECT[content_type].value_set(this.TYPE_DEFAULT);
        this.update();
    },

    /*******************************************************
    SORTING
    ********************************************************/

    //Sort comments by rating (higher first)
    //->sorting (int): -1 (a before b), 0 (no sorting), 1 (a after b)
    sort: function(com_a, com_b)
    {
        return com_a.rating > com_b.rating ? -1 : (com_a.rating < com_b.rating) ? 1 : 0;
    },

    /*******************************************************
    SUPPRESS
    ********************************************************/

    //Delete comment from database (private notes only)
    //#content_type (string):   type of content the comment is related to
    //#id (int):                ID of the comment to delete
    //-> (void)
    suppress: function(content_type, id)
    {
        //Send request to delete comment
        Kookiiz.api.call('comments', 'delete', 
        {
            'request':  'content_type=' + content_type
                        + '&id=' + id
        });
    },

    /*******************************************************
    UPDATE
    ********************************************************/

    //Update comments display when context changes
    //-> (void)
    update: function()
    {
        //Retrieve parameters
        var context = this.context_get();
        if(context)
        {
            //Take appropriate action depending on comment type
            switch(context.type)
            {
                case COMMENT_TYPE_PRIVATE:
                    this.COUNT_SELECT[context.content_type].hide();
                    this.PER_PAGE[context.content_type].hide();
                    this.INDEX[context.content_type].hide();
                    this.COMMENTS[context.content_type].loading();
                    this.load(context.content_type, context.content_id, context.type, context.count);
                    break;

                case COMMENT_TYPE_PUBLIC:
                    this.COUNT_SELECT[context.content_type].show();
                    this.PER_PAGE[context.content_type].show();
                    this.INDEX[context.content_type].show();
                    this.COMMENTS[context.content_type].loading();
                    this.load(context.content_type, context.content_id, context.type, context.count);
                    break;

                default:
                    this.COUNT_SELECT[context.content_type].hide();
                    this.PER_PAGE[context.content_type].hide();
                    this.INDEX[context.content_type].hide();
                    this.clear(context.content_type);
                    break;
            }
        }
    },
    
    /*******************************************************
    CALLBACKS - CONTROLS
    ********************************************************/

    //Callback for change of comments "count per page" selector
    //-> (void)
    count_change: function()
    {
        this.update();
    },

    //Callback for change of comments type select menu
    //-> (void)
    type_change: function()
    {
        this.update();
    },

    /*******************************************************
    CALLBACKS - ELEMENTS
    ********************************************************/

    //Callback for user actions on comment DOM elements
    //#comment_id (int):    ID of the comment on which an action was performed
    //#action (string):     action that was performed
    //#parameters (object): action parameters
    //-> (void)
    element_action: function(comment_id, action, parameters)
    {
        var context = this.context_get();
        switch(action)
        {
            case 'edit':
                this.edit(context.content_type, comment_id, parameters.text);
                break;
            case 'delete':
                this.suppress(context.content_type, comment_id);
                this.update();
                break;
            case 'rate':    
                this.rate(context.content_type, comment_id, parameters.rating);
                break;
        }
    },

    /*******************************************************
    CALLBACKS - PANEL
    ********************************************************/

    //Callback for input clearing icon
    //-> (void)
    clear_click: function()
    {
        this.clear_inputs();
    },

    //Callback for click on comment send button
    //-> (void)
    new_click: function()
    {
        var context = this.context_get();
        if(context)
        {
            //Send new comment
            var type = parseInt(this.inputType.value);
            var text = this.inputField.value.stripTags();
            this.create(context.content_type, context.content_id, type, text);

            //Clear comment input fields
            this.clear_inputs();
        }
    },

    //Callback for keyup in comment text area
    //#event (event): DOM event
    //-> (void)
    text_keyup: function(event)
    {
        var input = event.findElement();
        input.chars_limit(COMMENT_LENGTH_MAX, input.id + '_chars');
    }
});
