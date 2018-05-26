/*******************************************************
Title: Pictures library
Authors: Kookiiz Team
Purpose: Upload, delete and fetch pictures
********************************************************/

//Represents a library for pictures management
var PicturesLib = Class.create(
{
    object_name: 'pictures_lib',

    /*******************************************************
    CONSTANTS
    ********************************************************/

    DIR_GENERAL: '/pictures',
    DIR_THEME:   '/themes/' + THEME + '/pictures',

    //Lists of pictures to preload
    PRELOAD_GENERAL:    [
                            //Hover
                            '/hover/hover_120.png',
                            '/hover/hover_120_content.png',
                            '/hover/hover_120_content-hover.png',
                            //Icons
                            '/icons/icons_10.png',
                            '/icons/icons_15.png',
                            '/icons/icons_15_white.png',
                            '/icons/icons_20.png',
                            '/icons/icons_20_white.png',
                            '/icons/icons_25.png',
                            '/icons/icons_40.png',
                            //Loader
                            '/icons/loader.gif',
                        ],
    PRELOAD_THEME:      [
                            //Comments
                            '/comments/background.png',
                            '/comments/footer_left.png',
                            '/comments/footer_right.png',
                            '/comments/header.png',
                            //Friends
                            '/friends/item-hover.png',
                            '/friends/item-background-hover.png',
                            //Inputs
                            '/inputs/button_80-hover.png',
                            '/inputs/button_100-hover.png',
                            //Menu
                            '/menu/menu_quickmeal_hover.png',
                            //Panels
                            '/panels/help.png',
                            '/panels/help_down.png',
                            '/panels/help_reverse.png',
                            '/panels/help_reverse_down.png',
                            //Recipes
                            '/preview/background.png',
                            '/preview/fork_plate_knife.png',
                            '/preview/plate_empty.png',
                            '/recipes/background-item-hover.png',
                            '/recipes/recipe-hover.png',
                            '/recipes/recipe-item-hover.png',
                            //Tabs
                            '/tabs/back-selected.png',
                            '/tabs/top-selected.png'
                        ],

    /*******************************************************
    CONSTRUCTOR
    ********************************************************/

    //Class constructor
    //-> (void)
    initialize: function()
    {
        this.callback = false;  //callback for upload process

        //Create preloader element
        this.PRELOADER = new Element('div', {'id': 'pictures_preloader'});
        document.body.appendChild(this.PRELOADER);
    },

    /*******************************************************
    GET
    ********************************************************/

    //Return absolute picture path from picture-dir relative path
    //#path (string):   picture directory relative path
    //#theme (bool):    whether the picture is theme dependent
    //->url (string): full path
    getURL: function(path, theme)
    {
        theme = theme || false;
        if(theme)
            return this.DIR_THEME + path;
        else
            return this.DIR_GENERAL + path;
    },
    
    /*******************************************************
    OBSERVERS
    ********************************************************/
    
    //Called upon submission of the picture upload form
    //->upload (bool): true if the upload process can carry on
    onUpload: function()
    {
        $('picture_upload_error').hide();
        $('picture_upload_inputs').hide();
        $('picture_upload_loader').loading(true).show();
        return true;
    },
    
    //Called upon upload process termination
    //-> (void)
    onUploaded: function()
    {
        var frame = $('iframe_picture_upload'),
            picID = frame.contentWindow.PICID,
            error = frame.contentWindow.ERROR;
        if(typeof(picID) == 'undefined' || typeof(error) == 'undefined') return;
        
        if(error)
        {
            //Hide loader and display input
            $('picture_upload_loader').hide();
            $('picture_upload_inputs').show();

            //Display error message
            var error_field = $('picture_upload_error');
            error_field.innerHTML = PICTURE_ERRORS[error - 1];
            error_field.show();
        }
        else
        {
            if(this.callback)
            {
                this.callback(picID);
                this.callback = false;
            }
            Kookiiz.popup.hide();
        }
    },
    
    /*******************************************************
    PRELOAD
    ********************************************************/

    //Preload pictures
    //-> (void)
    preload: function()
    {
        //Theme-independent pictures
        var pic = new Element('img');
        for(var i = 0, imax = this.PRELOAD_GENERAL.length; i < imax; i++)
        {
            pic = pic.cloneNode(false);
            pic.src = this.DIR_GENERAL + this.PRELOAD_GENERAL[i];
            this.PRELOADER.appendChild(pic);
        }
        //Theme-dependent pictures
        for(i = 0, imax = this.PRELOAD_THEME.length; i < imax; i++)
        {
            pic = pic.cloneNode(false);
            pic.src = this.DIR_THEME + this.PRELOAD_THEME[i];
            this.PRELOADER.appendChild(pic);
        }
    },

    /*******************************************************
    UPLOAD
    ********************************************************/

    //Open picture upload popup
    //#type (string):       type of picture ("recipe", "article", etc.)
    //#callback (function): callback function for successfull picture upload process
    //-> (void)
    upload: function(type, callback)
    {
        this.callback = callback;

        //Open upload popup
        Kookiiz.popup.custom(
        {
            'text':                 PICTURES_TEXT[6],
            'title':                PICTURES_TEXT[5],
            'content_url':          '/dom/picture_upload_popup.php',
            'content_parameters':   'type=' + type,
            'content_init':         this.uploadInit.bind(this)
        });
    },

    //Init functionalities of the upload popup
    //-> (void)
    uploadInit: function()
    {
        $('picture_form').onsubmit = this.onUpload.bind(this);
        $('iframe_picture_upload').onload = this.onUploaded.bind(this);
    },

    /*******************************************************
    SUPPRESS
    ********************************************************/
    
    //Delete picture from server
    //#type (string):   type of picture ("recipe", "user", etc.)
    //#pic_id (int):    ID of the picture
    //#sync (bool):     tells if the request should be synchronous (defaults to false)
    //-> (void)
    suppress: function(type, pic_id, sync)
    {
        Kookiiz.api.call('pictures', 'delete',
        {
            'request':  'type=' + type + '&pic_id=' + pic_id,
            'sync':     sync || false
        });
    }
});