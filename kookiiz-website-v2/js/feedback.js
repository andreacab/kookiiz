/*******************************************************
Title: Feedback
Authors: Kookiiz Team
Purpose: User interface for feedback
********************************************************/

//Represents a user interface for visitors to provide feedback
var FeedbackUI = Class.create(
{
    object_name: '',

    /*******************************************************
    CONSTANTS
    ********************************************************/

    DELAY: 1,   //Delay between two feedback question (in seconds)

    /*******************************************************
    CONSTRUCTOR
    ********************************************************/

    //Class constructor
    //-> (void)
    initialize: function()
    {
        this.qID = -1;  //Current question ID

        //DOM elements
        this.$question = $('feedback_question');
        this.$thanks   = $('feedback_thanks');

        //Display temporary loader
        this.$question.select('.question')[0].loading();
    },

    /*******************************************************
    INIT
    ********************************************************/

    //Init dynamic functionalities
    //-> (void)
    init: function()
    {
        $('button_feedback').observe('click', this.onPopupClick.bind(this));
        this.questionLoad.bind(this).delay(3);
    },
    
    /*******************************************************
    OBSERVERS
    ********************************************************/
   
    //Called when user answers a feedback question
    //#event (event): DOM click event
    //-> (void)
    onAnswerClick: function(event)
    {
        var answer = event.findElement();

        //Display thanks
        this.$question.hide();
        new Effect.Appear(this.$thanks,
        {
            'duration': 0.5,
            'queue':    {'scope': 'feedback_thanks'}
        });
        this.$thanks.highlight('feedback_thanks');

        //Load a new question
        this.questionLoad(answer.hasClassName('yes') ? 1 : 0);
    },
    
    //Called when user clicks the feedback button
    //-> (void)
    onPopupClick: function()
    {
        this.popupOpen();
    },
    
    //Called when user confirms or cancels the feedback
    //#confirm (bool): true if user confirmed the feedback
    //-> (void)
    onPopupConfirm: function(confirm)
    {
        if(confirm)
        {
            var type = parseInt($('select_feedback_type').value),
                text = encodeURIComponent($('input_feedback_text').value),
                content = encodeURIComponent($('input_feedback_content').value);
            this.popupSend(type, content, text);
        }
    },
    
    //Called when the popup is loaded
    //#type (int): feedback type ID
    //-> (void)
    onPopupReady: function(type)
    {
        var select = $('select_feedback_type');
        select.value_set(type);
        select.observe('change', this.onPopupTypeChange.bind(this));
        this.onPopupTypeChange();
    },
    
    //Called when the value of the feedback type selector changes
    //#event (event): DOM change event
    //-> (void)
    onPopupTypeChange: function(event)
    {
        var type  = parseInt($('select_feedback_type').value),
            input = $('input_feedback_content');
        input.value = input.title = FEEDBACK_CONTENTS[type];
    },
    
    //Called when user decides to skip a given question
    //-> (void)
    onQuestionSkip: function()
    {
        this.$question.hide();
        this.questionLoad(0, true);
    },

    /*******************************************************
    POPUP
    ********************************************************/

    //Open the feedback popup
    //#type (int): feedback type ID (optional)
    //-> (void)
    popupOpen: function(type)
    {
        if(typeof(type) == 'undefined') type = 0;
        
        Kookiiz.popup.custom(
        {
            'text':         FEEDBACK_ALERTS[1],
            'title':        FEEDBACK_ALERTS[2],
            'confirm':      true,
            'cancel':       true,
            'callback':     this.onPopupConfirm.bind(this),
            'content_url':  '/dom/feedback_popup.php',
            'content_init': this.onPopupReady.bind(this, type)
        });
    },

    //Send a new feedback to the server
    //-> (void)
    popupSend: function(type, content, text)
    {
        Kookiiz.api.call('feedback', 'save',
        {
            'callback': function()
                        {
                            Kookiiz.popup.alert({'text': FEEDBACK_ALERTS[0]});
                        },
            'request':  'type=' + type
                        + '&content=' + content
                        + '&text=' + text
        });
    },
    
    /*******************************************************
    QUESTIONS
    ********************************************************/

    //Display new feedback question
    //#question_id (int): ID of the question to display
    //-> (void)
    questionDisplay: function(question_id)
    {
        this.$thanks.hide();
        this.$question.hide();
        
        var text     = this.$question.select('.question')[0],
            controls = this.$question.select('.controls')[0].hide();
        if(question_id >= 0)
        {
            var answers = controls.select('.answer'),
                skip    = controls.select('.skip')[0];
            
            text.innerHTML = FEEDBACK_QUESTIONS[question_id];
            answers.invoke('stopObserving', 'click');
            answers.invoke('observe', 'click', this.onAnswerClick.bind(this));
            skip.stopObserving('click').observe('click', this.onQuestionSkip.bind(this));
            this.$question.appear({'duration': 0.5, 'afterFinish': function(){controls.show();}});
        }
        else
        {
            text.innerHTML = FEEDBACK_ALERTS[3];
            this.$question.appear({'duration': 0.5});
        }

        //Update current question value
        this.qID = question_id;
    },

    //Save feedback answer and load new question from server
    //#answer (int):    answer to the previous question: 1 for yes and 0 for no (optional to load the 1st question)
    //#skip (bool):     if true skip the question (answer will not be recorded)
    //-> (void)
    questionLoad: function(answer, skip)
    {
        Kookiiz.api.call('feedback', 'question',
        {
            'callback': this.questionParse.bind(this),
            'request':  'question_id=' + this.qID
                        + '&answer=' + (answer || 0)
                        + '&skip=' + (skip ? 1 : 0)
        });
    },

    //Receive new feedback question from server
    //#response (object): server response object
    //-> (void)
    questionParse: function(response)
    {
        var old_question = parseInt(response.parameters.old_question),
            new_question = parseInt(response.parameters.new_question),
            skip = parseInt(response.parameters.skip);
            
        if(old_question >= 0 && !skip)  
            this.questionDisplay.bind(this).delay(this.DELAY, new_question);
        else                            
            this.questionDisplay(new_question);
    }
});