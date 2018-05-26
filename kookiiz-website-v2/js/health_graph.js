/*******************************************************
Title: Health Graph
Authors: Kookiiz Team
Purpose: Draw and handle events on the health graph
********************************************************/

//Represents a graph for nutrition values display
var HealthGraph = Class.create(Observable,
{
    object_name: 'health_graph',

    /*******************************************************
    CONSTANTS
    ********************************************************/

    BORDER_WIDTH:       1,      //Width (in pixels) of the borders around the areas
    DRAW_DELAY:         10,     //Delay (in ms) between two hoverCheck() calls
    HEIGHT:             300,    //Graph height
    HEIGHT_TEXT:        15,     //Height of a text line
    PERCENT_SPACING:    5,      //Vertical spacing (in pixels) between columns and percentage text
    REF_MARGIN:         0.15,   //Height of the acceptable area as a fraction of the reference value
    SPACING:            0.1,    //Spacing fraction
    WIDTH:              520,    //Graph width

    //Types of column divisions
    DIV_TYPES: ['breakfast', 'recipes', 'quickmeals'],

    /*******************************************************
    CONSTRUCTOR
    ********************************************************/

    //Class constructor
    //#container (DOM/string): graph container element (or its ID)
    //-> (void)
    initialize: function(container)
    {
        //DOM content
        this.canvas     = Utilities.available.canvas;
        this.container  = $(container);
        this.graph      = null;
        this.context    = null;

        //Computed parameters
        this.columns    = [];
        this.hovered    = -1;
        this.ref        = null;

        //Graph data
        this.data       = [];
        this.data_ref   = 0;

        //Hover parameters
        this.hover      = null;
        this.mouse      = null;
        this.timer      = 0;

        //Set-up graph area
        this.setUp();
    },

    /*******************************************************
    CLEAR
    ********************************************************/

    //Clear graph area
    //-> (void)
    clear: function()
    {
        if(this.canvas)
            this.context.clearRect(0, 0, this.graph.width, this.graph.height);
        else
            this.graph.clean();
    },

    /*******************************************************
    COMPUTE
    ********************************************************/

    //Compute properties of graph elements
    //-> (void)
    compute: function()
    {
        //Empty columns storage
        this.columns = [];

        //Compute data metrics
        var tot, avg = 0, max = this.data_ref * (1 + this.REF_MARGIN);
        for(var i = 0, imax = this.data.length; i < imax; i++)
        {
            tot = this.data[i].r + this.data[i].q + this.data[i].b;
            avg += tot / imax;
            max = tot > max ? tot : max;
        }

        //Compute global params
        var px_day  = this.WIDTH / (this.data.length || 1);             //Pixels per day (y)
        var px_val  = (this.HEIGHT - 3 * this.HEIGHT_TEXT) / max;       //Pixels per nutrition value unit
        var y_base  = Math.round(this.HEIGHT - 2 * this.HEIGHT_TEXT);   //Y pos of columns baseline
        var offset  = Math.round(px_day * 0.5 * this.SPACING);          //Offset between left border and first column
        var width   = Math.round(px_day * (1 - this.SPACING));          //Width of a column
        var txt1_y  = Math.round(y_base + this.HEIGHT_TEXT);            //Vertical position of day caption
        var txt2_y  = Math.round(txt1_y + this.HEIGHT_TEXT);            //Vertical position of date caption
        var ref_px  = this.data_ref * px_val;
        var ref_pos = Math.round(y_base - ref_px);
        var ref_max = Math.round(y_base - (1 + this.REF_MARGIN) * ref_px);
        var ref_min = Math.round(y_base - (1 - this.REF_MARGIN) * ref_px);

        //Compute reference line properties
        this.ref =
        {
            'pos': ref_pos,
            'max': ref_max,
            'min': ref_min
        };

        //Loop through data points
        var data, total, percent, column, height, pos_x, pos_y, txt_x;
        for(i = 0, imax = this.data.length; i < imax; i++)
        {
            data = this.data[i];

            //Compute column properties
            total   = data.b + data.r + data.q;
            percent = Math.round(100 * total / this.data_ref);
            pos_x   = Math.round(offset + i * px_day);
            txt_x   = Math.round(pos_x + width / 2);

            //Column data
            column =
            {
                'pos_x':    pos_x,
                'txt_x':    txt_x,
                'width':    width,
                'divs':     [],
                'text':     []
            };

            //Divisions
            height  = Math.round(data.b * px_val);
            pos_y   = y_base - height;
            column.divs.push(
            {
                'type':     'breakfast',
                'pos_y':    pos_y,
                'height':   height
            });
            height  = Math.round(data.r * px_val);
            pos_y   -= height;
            column.divs.push(
            {
                'type':     'recipes',
                'pos_y':    pos_y,
                'height':   height
            });
            height  = Math.round(data.q * px_val);
            pos_y   -= height;
            column.divs.push(
            {
                'type':     'quickmeals',
                'pos_y':    pos_y,
                'height':   height
            });

            //Text
            pos_y   -= this.PERCENT_SPACING;
            column.text.push(
            {
                'text':     percent + '%',
                'pos_y':    pos_y
            });
            column.text.push(
            {
                'text':     data.date.dayname.substr(0, 2).toUpperCase(),
                'pos_y':    txt1_y
            });
            column.text.push(
            {
                'text':     data.date.day + '.' + data.date.month,
                'pos_y':    txt2_y
            });

            //Store column
            this.columns.push(column);
        }
    },

    /*******************************************************
    DRAW
    ********************************************************/

    //Draw graph
    //-> (void)
    draw: function()
    {
        //Clear graph
        this.clear();

        //Draw reference line
        this.drawRef();

        //Loop through graph columns
        var col, div, hover, text;
        for(var i = 0, imax = this.columns.length; i < imax; i++)
        {
            col = this.columns[i];

            //Loop through column divisions
            for(var j = 0, jmax = col.divs.length; j < jmax; j++)
            {
                div = col.divs[j];
                hover = this.hover && this.hover.col == i && this.hover.div == j;
                this.drawDiv(col.pos_x, div.pos_y, col.width, div.height, div.type, hover);
            }

            //Loop through texts
            for(j = 0, jmax = col.text.length; j < jmax; j++)
            {
                text = col.text[j];
                this.drawText(col.txt_x, text.pos_y, col.width, text.text);
            }
        }
    },

    //Draw a column division
    //#x (int):         x pos (in pixels)
    //#y (int):         y pos (in pixels)
    //#w (int):         width (in pixels)
    //#h (int):         height (in pixels)
    //#type (string):   type of area ("recipes", etc.)
    //#hover (bool):    whether the area is currently hovered
    //-> (void)
    drawDiv: function(x, y, w, h, type, hover)
    {
        //Abort if area is empty
        if(w <= 0 || h <= 0) return;

        //Use canvas methods
        if(this.canvas)
        {
            //Compute global parameters
            var final_h = Math.max(0, h - 2 * this.BORDER_WIDTH);
            var final_w = Math.max(0, w - 2 * this.BORDER_WIDTH);
            var radius  = 0.1 * final_w;

            //Draw path depending on area type
            switch(type)
            {
                //Rectangle with rounded edges at the bottom
                case 'breakfast':
                    this.context.beginPath();
                    this.context.moveTo(x, y);
                    this.context.lineTo(x + final_w, y);
                    this.context.lineTo(x + final_w, y + final_h - radius);
                    this.context.arc(x + final_w - radius, y + final_h - radius, radius, 0, Math.PI / 2);
                    this.context.lineTo(x + radius, y + final_h);
                    this.context.arc(x + radius, y + final_h - radius, radius, Math.PI / 2, Math.PI);
                    this.context.lineTo(x, y);
                    break;

                //Rectangle with rounded edges on top
                case 'quickmeals':
                    this.context.beginPath();
                    this.context.moveTo(x + radius, y);
                    this.context.lineTo(x + final_w - radius, y);
                    this.context.arc(x + final_w - radius, y + radius, radius, 3 * Math.PI / 2, 2 * Math.PI);
                    this.context.lineTo(x + final_w, y + final_h);
                    this.context.lineTo(x, y + final_h);
                    this.context.lineTo(x, y + radius);
                    this.context.arc(x + radius, y + radius, radius, Math.PI, 3 * Math.PI / 2);
                    break;

                //Rectangle with no rounded edges
                case 'recipes':
                    this.context.beginPath();
                    this.context.rect(x, y, final_w, final_h);
                    break;
            }

            //Fill path with appropriate colors
            this.context.fillStyle   = this.getFillColor(type, hover);
            this.context.lineWidth   = this.BORDER_WIDTH;
            this.context.strokeStyle = this.getStrokeColor(type);
            this.context.fill();
            this.context.stroke();
        }
        //Use DOM elements
        else
        {
            var self = this;
            var element = new Element('div', {'class': 'area'});
            element.setStyle(
            {
                'background':   self.getFillColor(type, hover),
                'border':       self.BORDER_WIDTH + 'px solid ' + self.getStrokeColor(type),
                'height':       h + 'px',
                'left':         x + 'px',
                'position':     'absolute',
                'top':          y + 'px',
                'width':        w + 'px'
            });
            this.graph.appendChild(element);
        }
    },

    //Draw reference line
    //-> (void)
    drawRef: function()
    {
        if(this.canvas)
        {
            //Gradient
            var grd_x   = this.WIDTH / 2;
            var grd_y0  = this.ref.max;
            var grd_y1  = this.ref.min;
            var grad    = this.context.createLinearGradient(grd_x, grd_y0, grd_x, grd_y1);
            grad.addColorStop(0, COLOR_BACKGROUND);
            grad.addColorStop(0.5, COLOR_PRIMARY_LIGHT);
            grad.addColorStop(1, COLOR_BACKGROUND);
            this.context.beginPath();
            this.context.rect(0, grd_y0, this.WIDTH, grd_y1 - grd_y0);
            this.context.fillStyle = grad;
            this.context.fill();

            //Reference line
            this.context.beginPath();
            this.context.moveTo(0, this.ref.pos);
            this.context.lineTo(this.WIDTH, this.ref.pos);
            this.context.strokeStyle = COLOR_PRIMARY;
            this.context.stroke();
        }
        else
        {
            var self = this;
            var el = new Element('hr');
            el.setStyle(
            {
                'background':   COLOR_PRIMARY,
                'color':        COLOR_PRIMARY,
                'left':         0,
                'position':     'absolute',
                'top':          self.ref.pos + 'px'
            });
            this.graph.appendChild(el);
        }
    },

    //Draw graph caption
    //#x (int):         x position
    //#y (int):         y position
    //#w (int):         max width
    //#text (string):   text content
    //-> (void)
    drawText: function(x, y, w, text)
    {
        if(this.canvas)
        {
            this.context.font       = '10pt Tahoma';
            this.context.textAlign  = 'center';
            this.context.fillStyle  = COLOR_TEXT;
            this.context.fillText(text, x, y);
        }
        else
        {
            var el = new Element('p', {'class': 'center'});
            el.setStyle(
            {
                'font':     '10pt Tahoma',
                'left':     Math.round(x - w / 2) + 'px' ,
                'position': 'absolute',
                'top':      y + 'px',
                'width':    w + 'px'
            });
            el.innerHTML = text;
            this.graph.appendChild(el);
        }
    },

    /*******************************************************
    GET
    ********************************************************/

    //Get area filling color
    //#type (string):   type of area ("recipes", "breakfast", etc.)
    //#hover (bool):    whether the bar is currently hovered
    //->color (string): color code
    getFillColor: function(type, hover)
    {
        switch(type)
        {
            case 'breakfast':
                return hover ? COLOR_PRIMARY : COLOR_PRIMARY_LIGHT;
                break;
            case 'quickmeals':
                return hover ? COLOR_SECONDARY : COLOR_SECONDARY_LIGHT;
                break;
            case 'recipes':
                return hover ? COLOR_TERNARY : COLOR_TERNARY_LIGHT;
                break;
        }
    },

    //Get area border color
    //#type (string): type of area ("recipes", "breakfast", etc.)
    //->color (string): color code
    getStrokeColor: function(type)
    {
        switch(type)
        {
            case 'breakfast':
                return COLOR_PRIMARY;
                break;
            case 'quickmeals':
                return COLOR_SECONDARY;
                break;
            case 'recipes':
                return COLOR_TERNARY;
                break;
        }
    },

    /*******************************************************
    HOVER
    ********************************************************/

    //Check if mouse is currently hovering any graph column
    //This function is called periodically through the timer
    //-> (void)
    hoverCheck: function()
    {
        if(!this.mouse) return;

        var col, div, hover = null;
        for(var i = 0, imax = this.columns.length; i < imax; i++)
        {
            col = this.columns[i];
            if(this.mouse.x > col.pos_x
                && this.mouse.x < (col.pos_x + col.width))
            {
                for(var j = 0, jmax = col.divs.length; j < jmax; j++)
                {
                    div = col.divs[j];
                    if(this.mouse.y > div.pos_y
                        && this.mouse.y < (div.pos_y + div.height))
                    {
                        hover = {'col': i, 'div': j};
                        break;
                    }
                }
                if(hover) break;
            }
        }

        //Check if hover state changed
        if(hover !== this.hover)
        {
            //Fire "cleared" event for previous hover (if any)
            if(this.hover)
            {
                this.fire('cleared',
                {
                    'day':  this.hover.col,
                    'type': this.DIV_TYPES[this.hover.div]
                });
            }
            //Fire "hovered" event for current hover (if any)
            if(hover)
            {
                this.fire('hovered',
                {
                    'day':  hover.col,
                    'type': this.DIV_TYPES[hover.div]
                });
            }

            //Update stored state
            this.hover = hover;

            //Re-draw graph
            this.draw();
        }
    },

    /*******************************************************
    EVENTS
    ********************************************************/

    //Called when mouse enters the graph
    //Set-up hovering timer
    //#event (object): DOM mouse enter event
    //-> (void)
    onEnter: function(event)
    {
        window.clearInterval(this.timer);
        this.timer = window.setInterval(this.hoverCheck.bind(this), this.DRAW_DELAY);
    },

    //Called when mouse moves over the graph
    //Update mouse position data
    //#event (event): DOM mouse move event
    //-> (void)
    onMove: function(event)
    {
        var offset = this.graph.cumulativeOffset(),
            scroll = this.graph.cumulativeScrollOffset();
        this.mouse =
        {
            'x':    event.clientX - offset.left + scroll.left,
            'y':    event.clientY - offset.top + scroll.top
        };
    },

    //Called when mouse leaves the graph
    //Clear hovering parameters and timer
    //#event (event): DOM mouse out event
    //-> (void)
    onOut: function(event)
    {
        window.clearInterval(this.timer);
        this.hover = null;
        this.mouse = null;
        this.draw();
        this.fire('cleared', {'type': 'all'});
    },

    /*******************************************************
    SET
    ********************************************************/

    //Set graph data (triggers an update)
    //#data (array):    list of data points
    //#ref (int):       nutritional reference value
    //-> (void)
    setData: function(data, ref)
    {
        this.data = [];
        for(var i = 0, imax = data.length; i < imax; i++)
        {
            this.data.push(
            {
                'date': new DateTime(data[i].day),
                'r':    data[i].recipes || 0,
                'b':    data[i].breakfast || 0,
                'q':    data[i].quickmeals || 0
            });
        }
        this.data_ref = ref ? Math.round(ref) : 0;
        this.update();
    },

    /*******************************************************
    SET-UP
    ********************************************************/

    //Set-up graph area
    //-> (void)
    setUp: function()
    {
        //Build graph element
        this.container.clean();
        if(this.canvas)
        {
            this.graph = new Element('canvas');
            this.graph.height = this.HEIGHT;
            this.graph.width  = this.WIDTH;
            this.context = this.graph.getContext('2d');
        }
        else
        {
            this.graph = new Element('div');
            this.graph.setStyle(
            {
                'height':   this.HEIGHT + 'px',
                'position': 'relative',
                'width':    this.WIDTH + 'px'
            });
        }
        this.container.appendChild(this.graph);

        //Init event observers
        this.graph.observe('mouseenter', this.onEnter.bind(this));
        this.graph.observe('mousemove', this.onMove.bind(this));
        this.graph.observe('mouseout', this.onOut.bind(this));
    },

    /*******************************************************
    UPDATE
    ********************************************************/

    //Called after an update of graph data
    //-> (void)
    update: function()
    {
        this.compute();
        this.draw();
    }
});