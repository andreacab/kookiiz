/*******************************************************
Title: Event
Authors: Kookiiz Team
Purpose: Define the event item object
********************************************************/

//Represents an item of the events flux
var EventItem = Class.create(
{
	object_name: 'event',

    /*******************************************************
    CONSTRUCTOR
    ********************************************************/

    //Class constructor
    //#id (int):            ID of the event
    //#type (int):          event type
    //#user_id (int):       ID of the user who originated the event
    //#timestamp (int):     UNIX timestamp (seconds) of event creation
    //#parameters (object): structure containing optional event properties
    //	#friend_id (int):   ID of the friend related to this event (if any or false)
    //	#content (object):  related content information
    //		#id (int):      ID of a content (article, recipe) the event is related to (defaults to 0)
    //		#name (string): name of the content the event is related to (defaults to "")
    //		#pic (int):     pic ID of the content the event is related to (defaults to 0)
    //		#value (int):   value related to the content (defaults to 0)
    //	#status_type (int): type of status update (for event_share_status only)
    //	#comment (string):  comment added to the event
    //-> (void)
	initialize: function(id, type, user_id, timestamp, parameters)
    {
        //Attributes
        this.id          = id;
        this.type        = type;
        this.user_id     = user_id;
        this.friend_id   = 0;
        this.date        = new DateTime(timestamp * 1000, 'timestamp');
        this.content     = {'id': 0, 'name': '', 'pic': 0, 'value': 0};
        this.status_type = 0;
        this.comment     = '';

        //Import parameters
        Object.extend(this, parameters || {});
    },

    /*******************************************************
    BUILD
    ********************************************************/

    //Build an event block element from its properties
    //->event_item (DOM): event DOM element
	build: function()
    {
        //Build empty event structure
        var event_item = new Element('li',
        {
            'class':    'event_item',
            'id':       'event_item_' + this.id
        });
        var wrapper      = new Element('div', {'class': 'wrapper'}),
            block_top    = new Element('div', {'class': 'block_top'}),
            block_middle = new Element('div', {'class': 'block_middle'}),
            block_bottom = new Element('div', {'class': 'block_bottom'}),
            block_left   = new Element('div', {'class': 'block_left'}),
            block_center = new Element('div', {'class': 'block_center'}),
            block_right  = new Element('div', {'class': 'block_right'});
        event_item.appendChild(wrapper);
        wrapper.appendChild(block_top);
        wrapper.appendChild(block_middle);
        wrapper.appendChild(block_bottom);
        block_middle.appendChild(block_left);
        block_middle.appendChild(block_center);
        block_middle.appendChild(block_right);

        //Left block
        var icon = new Element('div', {'class': 'icon ' + EVENTS_TYPES[this.type]});
        block_left.appendChild(icon);

        //Center block
        var event_date  = new Element('p', {'class': 'small right'}),
            event_text1 = new Element('p'),
            event_text2 = new Element('p');
        block_center.appendChild(event_date);
        block_center.appendChild(event_text1);
        block_center.appendChild(event_text2);
        event_date.appendText(this.date.day + '.' + this.date.month + '.' + this.date.year
                                + ' ' + this.date.hour + ':' + this.date.minute);

        //Specific event configuration
        var text1 = null, text2 = null, link = null, image = null;
        switch(this.type)
        {
            case EVENT_TYPE_ADDRECIPE:
                text1 = new Element('span');
                link  = new Element('a', {'class': 'text_color1 bold'});
                image = new Element('img');
                this.setup_addrecipe(text1, link, image);
                event_text1.appendChild(text1);
                event_text1.appendChild(link);
                block_right.appendChild(image);
                break;

            case EVENT_TYPE_COMMENTRECIPE:
                text1 = new Element('span');
                text2 = new Element('span', {'class': 'small'});
                link  = new Element('a', {'class': 'text_color1 bold'});
                image = new Element('img');
                this.setup_commentrecipe(text1, link, text2, image);
                event_text1.appendChild(text1);
                event_text1.appendChild(link);
                event_text2.appendChild(text2);
                block_right.appendChild(image);
                break;

            case EVENT_TYPE_NEWMEMBER:
                text1 = new Element('span');
                this.setup_newmember(text1);
                event_text1.appendChild(text1);
                break;

            case EVENT_TYPE_RATERECIPE:
                text1 = new Element('span');
                text2 = new Element('span', {'class': 'tiny bold'});
                link  = new Element('a', {'class': 'text_color1 bold'});
                image = new Element('img');
                this.setup_raterecipe(text1, link, text2, image);
                event_text1.appendChild(text1);
                event_text1.appendChild(link);
                event_text2.appendChild(text2);
                block_right.appendChild(image);
                break;

            case  EVENT_TYPE_SHARERECIPE:
                text1 = new Element('span');
                link  = new Element('a', {'class': 'text_color1 bold'});
                image = new Element('img');
                this.setup_sharerecipe(text1, link, image);
                event_text1.appendChild(text1);
                event_text1.appendChild(link);
                block_right.appendChild(image);
                break;

            case EVENT_TYPE_SHARESTATUS:
                text1 = new Element('span');
                text2 = new Element('span', {'class': 'small'});
                link  = new Element('a', {'class': 'text_color1 bold'});
                image = new Element('img');
                this.setup_sharestatus(text1, link, text2, image);
                if(text1.innerHTML || link.innerHTML)
                {
                    event_text1.appendChild(text1);
                    event_text1.appendChild(link);
                }
                else
                    event_text1.remove();
                if(text2.innerHTML)
                    event_text2.appendChild(text2);
                else
                    event_text2.remove();
                if(image.src) 
                    block_right.appendChild(image);
                break;

            default:
                return false;
                break;
        }
        return event_item;
    },

    /*******************************************************
    SETUP
    ********************************************************/

    //Setup an event item of the type "add recipe"
    //#text (DOM):  main text element of the event
    //#link (DOM):  link element of the event
    //#image (DOM): event illustration element
    //-> (void)
	setup_addrecipe: function(text, link, image)
    {
        //Message
        var user;
        if(this.user_id == User.id)
            text.clean().appendText(EVENTS_ADDEDRECIPE_TEXT[1] + ' ');
        else
        {
            user = new Element('span', {'class': 'text_color2 bold'});
            user.appendText(Users.get(this.user_id, 'name'))
            text.clean().appendChild(user);
            text.appendText(' ' + EVENTS_ADDEDRECIPE_TEXT[0] + ' ');
        }

        //Link to the recipe
        link.href = Kookiiz.tabs.toURL(URL_HASH_TABS[4], this.content.id, this.content.name);
        link.clean().appendText(this.content.name);

        //Image
        image.writeAttribute(
        {
            'alt':   EVENTS_TEXT[0],
            'class': 'illustration',
            'src':   '/pics/recipes-' + this.content.pic + '-tb'
        });
    },

    //Setup an event item of the type "comment recipe"
    //#text1 (DOM): main text element of the event
    //#link (DOM):  link element of the event
    //#text2 (DOM): secondary text element of the event
    //#image (DOM): event illustration element
    //-> (void)
    setup_commentrecipe: function(text1, link, text2, image)
    {
        //Message
        var user;
        if(this.user_id == User.id)
            text1.clean().appendText(EVENTS_COMMENTRECIPE_TEXT[1] + ' ');
        else
        {
            user = new Element('span', {'class': 'text_color2 bold'});
            user.appendText(Users.get(this.user_id, 'firstname'))
            text1.clean().appendChild(user);
            text1.appendText(' ' + EVENTS_COMMENTRECIPE_TEXT[0] + ' ');
        }

        //Link to the recipe
        link.href = Kookiiz.tabs.toURL(URL_HASH_TABS[4], this.content.id, this.content.name);
        link.clean().appendText(this.content.name);

        //Comment
        text2.clean().appendText(this.comment ? ('"' + this.comment + '"') : '');

        //Image
        image.writeAttribute(
        {
            'alt':   EVENTS_TEXT[0],
            'class': 'illustration',
            'src':   '/pics/recipes-' + this.content.pic + '-tb'
        });
    },

    //Setup an event item of the type "new member"
    //#text (DOM): main text element of the event
    //-> (void)
	setup_newmember: function(text)
    {
        //Message
        var user;
        if(this.user_id == User.id)
            text.clean().appendText(EVENTS_NEWMEMBER_TEXT[1]);
        else
        {
            user = new Element('span', {'class': 'text_color2 bold'});
            user.appendText(Users.get(this.user_id, 'name'))
            text.clean().appendChild(user);
            text.appendText(' ' + EVENTS_NEWMEMBER_TEXT[0] + ' ');
        }
    },

    //Setup an event item of the type "rate recipe"
    //#text1 (DOM): main text element of the event
    //#link (DOM):  link element of the event
    //#text2 (DOM): secondary text element of the event
    //#image (DOM): event illustration element
    //-> (void)
	setup_raterecipe: function(text1, link, text2, image)
    {
        //Message
        var user;
        if(this.user_id == User.id)
            text1.clean().appendText(EVENTS_RATEDRECIPE_TEXT[1] + ' ');
        else
        {
            user = new Element('span', {'class': 'text_color2 bold'});
            user.appendText(Users.get(this.user_id, 'firstname'))
            text1.clean().appendChild(user);
            text1.appendText(' ' + EVENTS_RATEDRECIPE_TEXT[0] + ' ');
        }

        //Link to the recipe
        link.href = Kookiiz.tabs.toURL(URL_HASH_TABS[4], this.content.id, this.content.name);
        link.clean().appendText(this.content.name);

        //Rating
        text2.clean().appendText(EVENTS_RATEDRECIPE_TEXT[2]);
        for(var i = 0; i < this.content.value; i++)
        {
            var star = new Element('img',
            {
                'alt':   KEYWORDS[10],
                'class': 'icon15 star',
                'src':   ICON_URL
            });
            text2.appendChild(star);
        }

        //Image
        image.writeAttribute(
        {
            'alt':   EVENTS_TEXT[0],
            'class': 'illustration',
            'src':   '/pics/recipes-' + this.content.pic + '-tb'
        });
    },

    //Setup an event item of the type "share recipe"
    //#text (DOM):  main text element of the event
    //#link (DOM):  link element of the event
    //#image (DOM): event illustration element
    //-> (void)
	setup_sharerecipe: function(text, link, image)
    {
        //Message
        var user;
        if(this.user_id == User.id)
        {
            user = new Element('span', {'class': 'text_color2 bold'});
            user.appendText(Users.get(this.friend_id, 'firstname'))
            text.clean().appendText(EVENT_SHAREDRECIPE_TEXT[1] + ' ');
            text.appendChild(user);
            text.appendText(' ' + EVENTS_SHAREDRECIPE_TEXT[2] + ' ');
        }
        else
        {
            user = new Element('span', {'class': 'text_color2 bold'});
            user.appendText(Users.get(this.user_id, 'firstname'));
            text.clean().appendChild(user);
            text.appendText(' ' + EVENTS_SHAREDRECIPE_TEXT[0] + ' ');
        }

        //Link to the recipe
        link.href = Kookiiz.tabs.toURL(URL_HASH_TABS[4], this.content.id, this.content.name);
        link.clean().appendText(this.content.name);

        //Image
        image.writeAttribute(
        {
            'alt':   EVENTS_TEXT[0],
            'class': 'illustration',
            'src':   '/pics/recipes-' + this.content.pic + '-tb'
        });
    },

    //Setup an event item of the type "share status"
    //#text1 (DOM): main text element of the event
    //#link (DOM):  link element of the event
    //#text2 (DOM): secondary text element of the event
    //#image (DOM): event illustration element
    //-> (void)
	setup_sharestatus: function(text1, link, text2, image)
    {
        //Check if there is a content related to the status update
        var user;
        if(this.content.id)
        {
            //Automatic message
            if(STATUS_HAS_MESSAGE[this.status_type])
            {
                if(this.user_id == User.id)
                    text1.clean().appendText(STATUS_NAMES[this.status_type] + ' ' + EVENTS_SHAREDSTATUS_TEXT[4] + ' ');
                else
                {
                    user = new Element('span', {'class': 'text_color2 bold'});
                    user.appendText(Users.get(this.user_id, 'firstname'));
                    text1.clean().appendChild(user);
                    text1.appendText(' ' + EVENTS_SHAREDSTATUS_TEXT[this.status_type] + ' ' + EVENTS_SHAREDSTATUS_TEXT[4] + ' ');
                }
            }

            //Link to the recipe
            link.href = Kookiiz.tabs.toURL(URL_HASH_TABS[4], this.content.id, this.content.name);
            link.clean().appendText(this.content.name);

            //Image
            image.writeAttribute(
            {
                'alt':   EVENTS_TEXT[0],
                'class': 'illustration',
                'src':   '/pics/recipes-' + this.content.pic + '-tb'
            });
        }
        //No related content
        else
        {
            //Automatic message
            if(STATUS_HAS_MESSAGE[this.status_type])
            {
                if(this.user_id == User.id)
                    text1.clean().appendText(STATUS_NAMES[this.status_type]);
                else
                {
                    user = new Element('span', {'class': 'text_color2 bold'});
                    user.appendText(Users.get(this.user_id, 'firstname'));
                    text1.clean().appendChild(user);
                    text1.appendText(' ' + EVENTS_SHAREDSTATUS_TEXT[this.status_type]);
                }
            }
        }

        //Comment
        if(STATUS_HAS_MESSAGE[this.status_type])
            text2.clean().appendText(this.comment ? ('"' + this.comment + '"') : '');
        else
        {
            if(this.user_id == User.id)
            {
                user = new Element('span', {'class': 'text_color2 bold'});
                user.appendText(USER_TEXT[0]);
            }
            else
            {
                user = new Element('span', {'class': 'text_color2 bold'});
                user.appendText(Users.get(this.user_id, 'firstname'));
            }
            text2.clean().appendChild(user);
            text2.appendText(': "' + this.comment + '"');
        }
    }
});