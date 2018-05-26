/*******************************************************
Title: Videos
Authors: Kookiiz Team
Purpose: User interface for videos
********************************************************/

//Represents a user interface for videos display
var VideosUI = Class.create(
{
    object_name: 'videos_ui',

    /*******************************************************
    CONSTRUCTOR
    ********************************************************/

    //Class constructor
    //-> (void)
    initialize: function()
    {
    },

    /*******************************************************
    DISPLAY
    ********************************************************/

    //Open a popup to display selected video
    //#id (int):        video unique ID
    //#title (string):  video title
    //-> (void)
    display: function(id, title)
    {
        Kookiiz.popup.custom(
        {
           'title':                 title,
           'confirm':               true,
           'confirm_label':         ACTIONS[16],
           'content_url':           '/dom/video_popup.php',
           'content_parameters':    'video=' + id
        });
    },

    /*******************************************************
    LIST
    ********************************************************/

    //Display list of video as thumbnails in provided container
    //#container (DOM/string): container DOM element (or its ID)
    //-> (void)
    list: function(container)
    {
        container = $(container).clean();

        //Loop through video IDs
        var list = new Element('ul'),
            index, id, title, item, pic;
        for(var i = 0, imax = VIDEOS_IDS.length; i < imax; i++)
        {
            id      = VIDEOS_IDS[i];
            title   = VIDEOS_TITLES[i];

            //List element
            item = new Element('li');
            item.writeAttribute('data-id', id);
            item.writeAttribute('data-title', title);
            list.appendChild(item);

            //Picture
            pic = new Element('img',
            {
                'alt':      title,
                'class':    'video_pic video_' + i + ' click',
                'src':      ICON_URL,
                'title':    title
            });
            item.appendChild(pic);
        }

        if(!list.empty())
        {
            //Attach observer and append list to container
            list.observe('click', this.onClick.bind(this));
            container.appendChild(list);
        }
    },

    /*******************************************************
    OBSERVERS
    ********************************************************/

    //Called when a video thumbnail is clicked
    //#event (object): DOM click event
    //-> (void)
    onClick: function(event)
    {
        var video = event.findElement('li');
        if(video)
        {
            var id = parseInt(video.readAttribute('data-id')),
                title = video.readAttribute('data-title');
            this.display(id, title);
        }
    }
});