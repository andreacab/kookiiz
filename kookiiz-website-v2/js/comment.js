/*******************************************************
Title: Comment
Authors: Kookiiz Team
Purpose: Define the comment object
********************************************************/

//Represents a user comment on a recipe or article
var Comment = Class.create(
{
	object_name: 'comment',

    /*******************************************************
    CONSTRUCTOR
    ********************************************************/

	//Class constructor
    //#id (int):                unique ID of the comment
    //#type (int):              type of comment (private or public)
    //#content_type (string):   type of content (article, recipe) the comment is related to
    //#user_id (int):           ID of the user who wrote the comment
    //#user_name (string):      name of the user who wrote the comment
    //#text (string):           text of the comment
    //#rating (int):            current rating of the comment
    //#date (string):           date at which the comment was written or modified as "dd.mm.yy"
    //#time (string):           time at which the comment was written or modified as "hh:mm"
    //-> (void)
	initialize: function(id, type, content_type, content_id, user_id, user_name, text, rating, date, time)
    {
        this.id             = id;
        this.type           = type;
        this.content_type   = content_type;
        this.content_id     = content_id;
        this.user_id        = user_id;
        this.user_name      = user_name;
        this.text           = text;
        this.rating         = rating;
        this.date           = date;
        this.time           = time;
    },

    /*******************************************************
    BUILD
    ********************************************************/

	//Build a comment DOM element
    //#options (object): comment display options
    //-> (void)
	build: function(options)
    {
        options = Object.extend(
        {
            'callback':   false,  //callback function for user actions
            'side':       'left'  //side on which the comment will be displayed (for public comment bubbles)
        }, options || {});

        //Generic comment item
        var comment_item = new Element('li', {'class': 'comment ' + this.content_type});

        //PUBLIC COMMENT
        if(this.type == COMMENT_TYPE_PUBLIC)
        {
            comment_item.addClassName('public ' + options.side);

            //BUBBLE
            var comment_bubble  = new Element('div', {'class': 'bubble'});
            var comment_top     = new Element('div', {'class': 'top'});
            var comment_center  = new Element('div', {'class': 'center'});
            var comment_bottom  = new Element('div', {'class': 'bottom'});
            comment_bubble.appendChild(comment_top);
            comment_bubble.appendChild(comment_center);
            comment_bubble.appendChild(comment_bottom);

            //Date
            var comment_date = new Element('p', {'class': 'date'});
            comment_date.innerHTML = 'Le ' + this.date + ' à ' + this.time;
            comment_top.appendChild(comment_date);

            //Text
            var comment_text = new Element('p', {'class': 'text'});
            comment_text.innerHTML = this.text;
            comment_center.appendChild(comment_text);

            //RATING
            //Buttons
            var norating = this.user_id == User.id;
            var comment_pos = new Element('img',
            {
                'alt':      COMMENTS_TEXT[8],
                'class':    'rate_plus icon20 click arrow_up' + (norating ? ' disabled' : ''),
                'src':      ICON_URL,
                'title':    COMMENTS_TEXT[6]
            });
            if(options.callback && !norating) 
                comment_pos.observe('click', this.element_rate.bind(this, options.callback));
            var comment_neg = new Element('img',
            {
                'alt':      COMMENTS_TEXT[9],
                'class':    'rate_minus icon20 click arrow_down' + (norating ? ' disabled' : ''),
                'src':      ICON_URL,
                'title':    COMMENTS_TEXT[7]
            });
            if(options.callback && !norating) 
                comment_neg.observe('click', this.element_rate.bind(this, options.callback));

            //Display
            var comment_rating = new Element('p', {'class': 'rating center tiny bold'});
            comment_rating.innerHTML = this.rating;
            if(this.rating <= -5)                           
                comment_rating.addClassName('bad');
            else if(this.rating > - 5 && this.rating < 5)   
                comment_rating.addClassName('neutral');
            else if(this.rating >= 5)                       
                comment_rating.addClassName('good');
            comment_item.appendChild(comment_pos);
            comment_item.appendChild(comment_neg);
            comment_item.appendChild(comment_rating);

            //INFO
            var comment_info = new Element('div', {'class': 'info'});

            //Avatar
            var comment_author_avatar = new Element('img',
            {
                'alt':      KEYWORDS[1],
                'class':    'comment_avatar icon15 ' + (options.side == 'left' ? 'cook' : 'cook_mirror'),
                'src':      ICON_URL
            });

            //Name
            var comment_author_name = new Element('span');
            comment_author_name.innerHTML = this.user_name;

            //WRAP UP
            if(options.side == 'left')
            {
                comment_info.appendChild(comment_author_avatar);
                comment_info.appendChild(comment_author_name);
            }
            else
            {
                comment_info.appendChild(comment_author_name);
                comment_info.appendChild(comment_author_avatar);
            }
            comment_item.appendChild(comment_bubble);
            comment_item.appendChild(comment_info);
        }
        //PRIVATE NOTE
        else if(this.type == COMMENT_TYPE_PRIVATE)
        {
            comment_item.addClassName('private');

            //NOTE
            var comment_note = new Element('div');

            //Date
            var comment_date = new Element('p', {'class': 'date'});
            comment_date.innerHTML = 'Le ' + this.date + ' à ' + this.time;

            //Text
            var comment_text = new Element('p', {'class': 'text'});
            comment_text.innerHTML = this.text;
            if(options.callback) 
                comment_text.observe('click', this.element_edit.bind(this, options.callback));

            //Input
            var comment_input = new Element('textarea', {'class': 'input'});
            comment_input.hide();

            //Actions
            if(options.callback)
            {
                var comment_edit = new Element('img',
                {
                    'alt':      ACTIONS[25],
                    'class':    'icon15 click edit',
                    'src':      ICON_URL,
                    'title':    COMMENTS_TEXT[10]
                });
                comment_edit.observe('mousedown', this.element_edit.bind(this, options.callback));

                //Delete icon
                var comment_delete = new Element('button',
                {
                    'alt':      ACTIONS[23],
                    'class':    'button15 click cancel',
                    'title':    COMMENTS_TEXT[11]
                });
                comment_delete.observe('click', this.element_delete.bind(this, options.callback));
            }

            //WRAP UP
            comment_note.appendChild(comment_date);
            comment_note.appendChild(comment_text);
            comment_note.appendChild(comment_input);
            comment_note.appendChild(comment_edit);
            comment_note.appendChild(comment_delete);
            comment_item.appendChild(comment_note);
        }

        //Return comment item
        return comment_item;
    },

    /*******************************************************
    ELEMENT
    ********************************************************/

    //Callback for click on comment deletion icon
    //#callback (function): external function to handle the event
    //#event (event):       DOM click event
    //-> (void)
    element_delete: function(callback, event)
    {
		//Remove comment item from DOM
		event.findElement('.comment').remove();

        //Fire callback
        callback(this.id, 'delete');
    },

    //Callback for click on comment edition icon
    //#callback (function): external function to handle the event
    //#event (event):       DOM click event
    //-> (void)
    element_edit: function(callback, event)
    {
        event.stop();
        
        var comment_item = event.findElement('.comment'),
            input = comment_item.select('.input')[0],
            text  = comment_item.select('.text')[0],
            icon  = comment_item.select('.edit')[0];
        input.swap(text, this.element_validate.bind(this, callback), icon);
    },

    //Called when a comment rating button is clicked
    //#callback (function): external function to handle the event
    //#event (event):       DOM click event
    //-> (void)
    element_rate: function(callback, event)
    {
        //Retrieve comment and rating parameters
        var rating_element = event.findElement(),
            comment_item   = rating_element.up('.comment'),
            rating         = rating_element.hasClassName('rate_plus') ? 1 : 0,
            rating_text    = comment_item.select('.rating')[0];

		//Disable rating icons and update rating text
        var rating_count = parseInt(rating_text.innerHTML) + (rating ? 1 : -1);
		comment_item.select('img.rate_minus')[0].addClassName('disabled');
		comment_item.select('img.rate_plus')[0].addClassName('disabled');
		rating_text.clean().appendChild(document.createTextNode(rating_count));

        //Fire callback
        callback(this.id, 'rate', {'rating': rating});
    },

    //Callback for click on comment validation icon
    //#callback (function): external function to handle the event
    //#event (event):       DOM click event
    //-> (void)
    element_validate: function(callback, event)
    {
        event.stop();
        
        //Retrieve content and comment info
		var comment_item = event.findElement('.comment'),
            input = comment_item.select('.input')[0],
            text = comment_item.select('.text')[0],
            icon = comment_item.select('.accept')[0],
            input_value = input.value.stripTags();

        //Fire callback if text changed
        if(input_value != text.innerHTML) 
            callback(this.id, 'edit', {'text': input_value});

        //Hide input and show text
        input.swap(text, this.element_edit.bind(this, callback), icon);
    }
});