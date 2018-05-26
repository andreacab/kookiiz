/*******************************************************
Title: Time
Authors: Kookiiz Team
Purpose: Time-related functionalities
********************************************************/

//Represents a custom date object
var DateTime = Class.create(
{
    object_name: 'date_time',

    /*******************************************************
    CONSTRUCTOR
    ********************************************************/

    //Class constructor
    //#value (int):     either an increment (0 = today, 1 = tomorrow...), a timestamp or a date object (defaults to 0)
    //#mode (string):   specifies if "value" is an "increment", a "timestamp" or a "date" (defaults to "increment")
    //-> (void)
    initialize: function(value, mode)
    {
        value   = value || 0;
        mode    = mode || 'increment';

        //Create date object according to mode
        var date = null;
        if(mode == 'date')
        {
            date = value;
        }
        else if(mode == 'increment')
        {
            date = new Date();
            date.setTime(date.getTime() + value * 24 * 3600 * 1000);
        }
        else if(mode == 'timestamp')
        {
            date = new Date();
            date.setTime(value);
        }

        //Set object properties
        this.set(date);
    },

    /*******************************************************
    DIFFERENCE
    ********************************************************/

    //Compute number of days between current date object and provided date object
    //#datetime (object): datetime object
    //->days diff (int): number of days between current date and provided date
    days_diff: function(datetime)
    {
        var date1 = new Date(this.year, this.monthnum - 1, this.daynum, this.hournum, this.minutenum, this.secondnum);
        var date2 = new Date(datetime.year, datetime.monthnum - 1, datetime.daynum, datetime.hournum, datetime.minutenum, datetime.secondnum);

        //Compute milliseconds difference, then transform it in days
        var milli1      = date1.getTime();
        var milli2      = date2.getTime();
        var milli_diff  = milli1 - milli2;
        return Math.round(milli_diff / 1000 / 60 / 60 / 24);
    },

    /*******************************************************
    SET
    ********************************************************/

    //Set date and time from JS date object
    //#date (object): JS date object
    //-> (void)
    set: function(date)
    {
        //Set date object properties
        this.weekday    = date.getDay();
        this.daynum     = date.getDate();
        this.day        = this.daynum < 10 ? '0' + this.daynum : this.daynum;
        this.dayname    = DAYS_NAMES[this.weekday - 1 < 0 ? 6 : this.weekday - 1];
        this.monthnum   = date.getMonth() + 1;
        this.month      = this.monthnum < 10 ? '0' + this.monthnum : this.monthnum;
        this.monthname  = MONTHS_NAMES[this.month - 1];
        this.year       = date.getFullYear();
        this.hournum    = date.getHours();
        this.hour       = this.hournum < 10 ? '0' + this.hournum : this.hournum;
        this.minutenum  = date.getMinutes();
        this.minute     = this.minutenum < 10 ? '0' + this.minutenum : this.minutenum;
        this.secondnum  = date.getSeconds();
        this.second     = this.secondnum < 10 ? '0' + this.secondnum : this.secondnum;
        //Season
        if((this.monthnum == 3 && this.daynum >= 21)
            || (this.monthnum > 3 && this.monthnum < 6)
            || (this.monthnum == 6 && this.daynum < 21))    
            this.season = 1;
        else if((this.monthnum == 6 && this.daynum >= 21)
            || (this.monthnum > 6 && this.monthnum < 9)
            || (this.monthnum == 9 && this.daynum < 21))    
            this.season = 2;
        else if((this.monthnum == 9 && this.daynum >= 21)
            || (this.monthnum > 9 && this.monthnum < 12)
            || (this.monthnum == 12 && this.daynum < 21))   
            this.season = 3;
    },

    /*******************************************************
    STRING
    ********************************************************/

    //Export datetime object as string
    //->datecode (string): datetime as "YYYY-MM-DD HH:MM:SS"
    toString: function()
    {
        return this.year + '-' + this.month + '-' + this.day + ' ' + this.hour + ':' + this.minute;
    },

    /*******************************************************
    TIME
    ********************************************************/

    //Export datetime object as a timestamp
    //@second (bool): export timestamp in seconds
    //->timestamp (int): datetime UNIX timestamp (in ms OR s)
    toTime: function(second)
    {
        var date = new Date(this.year, this.monthnum - 1, this.daynum, this.hournum, this.minutenum, this.secondnum);
        return second ? Math.round(date.getTime() / 1000) : date.getTime();
    }
});

//Represents a tool box of time-related functionalities
var TimeAPI = Class.create(
{
    object_name: 'time_api',

    /*******************************************************
    CONSTRUCTOR
    ********************************************************/

    //Class constructor
    //-> (void)
    initialize: function()
    {
        //this.CLOCK = $('clock');
    },

    /*******************************************************
    BISSEXTILE
    ********************************************************/

    //Check if provided year is bissextile
    //#year (int): the year to check
    //->bissextile (bool): is the year bissextile ?
    is_bissextile: function(year)
    {
        if(isNaN(year)) return false;

        year = parseInt(year);
        if((!(year % 4) && (year % 100)) || !(year % 400))  
            return true;
        else                                                
            return false;
    },

    /*******************************************************
    GET
    ********************************************************/

    //Return current timestamp (in milliseconds)
    //#seconds (bool): return timestamp in seconds
    //->timestamp (int): current timestamp
    get: function(seconds)
    {
        var date = new Date();
        return seconds ? Math.round(date.getTime() / 1000) : date.getTime();
    },
    
    /*******************************************************
    DATE CODES
    ********************************************************/

    //Return a date code to identify a particular day
    //#increment (int):     specifies which day to retrieve (0 = today, 1 = tomorrow, etc.)
    //#separator (string):  specifies the separator to user between date components
    //->date code (string): date as "yyyy(separator)mm(separator)dd"
    datecode_get: function(increment, separator)
    {
        if(typeof(increment) == 'undefined') increment = 0;
        if(typeof(separator) == 'undefined') separator = '-';

        var date = new DateTime(increment);
        return date.year + separator + date.month + separator + date.day;
    },

    //Compute number of days between datecode1 and datecode2
    //#datecode1 (string): first date as a string of the type "yyyy(separator)mm(separator)dd"
    //#datecode2 (string): second date of the same type "yyyy(separator)mm(separator)dd"
    //#separator (string): separator used in the date strings between date components (defaults to "-")
    //->days diff (int): number of days between datecode1 and datecode2 (datecode1 - datecode2)
    datecodes_diff: function(datecode1, datecode2, separator)
    {
        if(typeof(separator) == 'undefined') separator = '-';

        //Retrieve dates components
        var year1   = datecode1.split(separator)[0];
        var month1  = datecode1.split(separator)[1];
        if(month1.charAt(0) == '0') month1 = month1.charAt(1);
        var day1    = datecode1.split(separator)[2];
        if(day1.charAt(0) == '0') day1 = day1.charAt(1);
        var year2   = datecode2.split(separator)[0];
        var month2  = datecode2.split(separator)[1];
        if(month2.charAt(0) == '0') month2 = month2.charAt(1);
        var day2    = datecode2.split(separator)[2];
        if(day2.charAt(0) == '0') day2 = day2.charAt(1);

        //Create date objects from components
        var date1 = new Date(parseInt(year1), parseInt(month1) - 1, parseInt(day1));
        var date2 = new Date(parseInt(year2), parseInt(month2) - 1, parseInt(day2));

        //Retrieve number of milliseconds between dates and 1st january 1970
        var milli1 = date1.getTime();
        var milli2 = date2.getTime();

        //Compute milliseconds difference, then transform it in days
        var milli_diff = milli1 - milli2;
        return Math.round(milli_diff / 1000 / 60 / 60 / 24);
    },

    /*******************************************************
    SORTING
    ********************************************************/

    //Sort JS date objects from most recent to oldest
    //#date_a (object): first date to sort
    //#date_b (object): second date to sort
    //->sorting (int): -1 (a before b), 0 (no sorting), 1 (a after b)
    dates_sort: function(date_a, date_b)
    {
        var difference = date_a.getTime() - date_b.getTime();
        return difference < 0 ? -1 : (difference > 0 ? 1 : 0);
    },

    //Sort datecodes from most recent to oldest
    //#datecode_a (string): first datecode to sort as "yyyy-mm-dd"
    //#datecode_b (string): second datecode to sort as "yyyy-mm-dd"
    //->sorting (int): -1 (a before b), 0 (no sorting), 1 (a after b)
    datecodes_sort: function(datecode_a, datecode_b)
    {
        //Retrieve dates components
        var year_a  = datecode_a.split('-')[0];
        var year_b  = datecode_b.split('-')[0];
        var month_a = datecode_a.split('-')[1];
        if(month_a.charAt(0) == '0') month_a = month_a.charAt(1);
        var month_b = datecode_b.split('-')[1];
        if(month_b.charAt(0) == '0') month_b = month_b.charAt(1);
        var day_a = datecode_a.split('-')[2];
        if(day_a.charAt(0) == '0') day_a = day_a.charAt(1);
        var day_b = datecode_b.split('-')[2];
        if(day_b.charAt(0) == '0') day_b = day_b.charAt(1);

        var date_a = new Date(year_a, month_a, day_a);
        var date_b = new Date(year_b, month_b, day_b);
        return Time.dates_sort(date_a, date_b);
    },

    /*******************************************************
    CLOCK
    ********************************************************/

    //Update clock display at consecutive time intervals
    //-> (void)
    clock_update: function()
    {
        var date = new DateTime();
        this.CLOCK.innerHTML = date.dayname + ' ' + date.daynum + ' ' + date.monthname + ', '
                                + date.hour + ":" + date.minute + ':' + date.second;
        this.clock_update.delay(0.5);   //Call this function every 0.5s
    }
});

//Represents a pack of three select menus to choose a date
var DateSelector = Class.create(
{
    object_name: 'date_selector',

    /*******************************************************
    CONSTRUCTOR
    ********************************************************/

    //Class constructor
    //-> (void)
    initialize: function()
    {
        //DOM elements
        this.$day   = new Element('select');
        this.$month = new Element('select');
        this.$year  = new Element('select');
        this.$hour  = new Element('select');
        this.$min   = new Element('select');

        //Build selectors
        this.build();
        
        //Observers
        this.$month.observe('change', this.change.bind(this));
        this.$year.observe('change', this.change.bind(this));      
    },
    
    /*******************************************************
    BUILD
    ********************************************************/
   
    //Build DOM selects
    //-> (void)
    build: function()
    {
        //Clear selectors
        this.$year.clean();
        this.$month.clean();
        this.$hour.clean();
        this.$min.clean();
        
        //Year
        var option, now = new Date(), year = now.getFullYear();
        for(var y = year, ymax = year + 1; y <= ymax; y++)
        {
            option = new Element('option');
            option.value = option.innerHTML = y;
            this.$year.appendChild(option);
        }
        //Month
        for(var m = 1, mmax = 12; m <= mmax; m++)
        {
            option = new Element('option');
            option.value        = m;
            option.innerHTML    = MONTHS_SHORTS[m - 1];
            this.$month.appendChild(option);
        }
        //Hour
        for(var h = 0, hmax = 23; h <= hmax; h++)
        {
            option = new Element('option');
            option.value = h;
            option.innerHTML = h < 10 ? '0' + h : h;
            this.$hour.appendChild(option);
        }
        //Minute
        for(m = 0, mmax = 45; m <= mmax; m += 15)
        {
            option = new Element('option');
            option.value = m;
            option.innerHTML = m < 10 ? '0' + m : m;
            this.$min.appendChild(option);
        }
        //Compute available days for current year & month
        this.change();
    },

    /*******************************************************
    CHANGE
    ********************************************************/

    //Called when month or year selection change
    //Compute new days options
    //-> (void)
    change: function()
    {
        //Retrieve current values
        var day     = parseInt(this.$day.value);
        var month   = parseInt(this.$month.value);
        var year    = parseInt(this.$year.value);

        //Determine max days according to current month and year
        var days_max = 0;
        switch(month)
        {
            //31 days months
            case 1:
            case 3:
            case 5:
            case 7:
            case 8:
            case 10:
            case 12:
                days_max = 31;
                break;
            //30 days months
            case 4:
            case 6:
            case 9:
            case 11:
                days_max = 30;
                break;
            //February
            case 2:
                days_max = Time.is_bissextile(year) ? 29 : 28;
                break;
        }

        //Rebuild days select menu
        this.$day.clean();
        var day_option = null;
        for(var i = 1, imax = days_max + 1; i < imax; i++)
        {
            day_option = new Element('option');
            day_option.value        = i;
            day_option.innerHTML    = i < 10 ? '0' + i : i;
            this.$day.appendChild(day_option);
        }

        //Select day
        if(day <= days_max) 
            this.$day.value_set(day);
    },
    
    /*******************************************************
    DISPLAY
    ********************************************************/
   
    //Display selectors in provided container
    //#container (DOM): container DOM element (or its ID)
    //-> (void)
    display: function(container)
    {
        container = $(container).clean();
        
        //Create table
        var tble = new Element('table'),
            body = new Element('tbody'),
            date = new Element('tr'),
            time = new Element('tr');
        tble.appendChild(body);
        body.appendChild(date);
        body.appendChild(time);
        
        //Date label
        var cell = new Element('td');
        cell.innerHTML = VARIOUS[3];
        date.appendChild(cell);
        //Date selectors
        cell = new Element('td');
        cell.appendChild(this.$day);
        cell.appendChild(this.$month);
        cell.appendChild(this.$year);
        date.appendChild(cell);
        //Time label
        cell = new Element('td');
        cell.innerHTML = VARIOUS[4];
        time.appendChild(cell);
        //Time selectors
        cell = new Element('td');
        cell.appendChild(this.$hour);
        cell.appendChild(document.createTextNode(':'));
        cell.appendChild(this.$min);
        time.appendChild(cell);
        
        //Append list to container
        container.appendChild(tble);
    },
    
    /*******************************************************
    GETTERS
    ********************************************************/
   
    //Return currently selected day
    //->day (int): current day
    getDay: function()
    {
        return parseInt(this.$day.value);
    },
    
    //Return currently selected hour
    //->hour (int): current hour
    getHour: function()
    {
        return parseInt(this.$hour.value);
    },
    
    //Return currently selected minute
    //->minute (int): current minute
    getMinute: function()
    {
        return parseInt(this.$min.value);
    },
    
    //Return currently selected month
    //->month (int): current month
    getMonth: function()
    {
        return parseInt(this.$month.value);
    },
    
    //Return currently selected year
    //->year (int): current year
    getYear: function()
    {
        return parseInt(this.$year.value);
    },
    
    /*******************************************************
    SETTERS
    ********************************************************/
    
    //Set selector to a given datetime
    //#datetime (object): DateTime object
    //-> (void)
    set: function(datetime)
    {
        this.$year.value_set(datetime.year);
        this.$month.value_set(datetime.monthnum);
        this.change();
        this.$day.value_set(datetime.daynum);
        this.$hour.value_set(datetime.hournum);
        this.$min.value_set(datetime.minutenum);
    }
});