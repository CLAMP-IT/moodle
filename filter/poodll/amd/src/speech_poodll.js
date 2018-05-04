/* jshint ignore:start */
define(['jquery','core/log','filter_poodll/speech_browser'], function($, log,browserrecognition) {

    "use strict"; // jshint ;_;

    log.debug('speech_poodll: initialising');

    return {

        recognizer: null,


    //for making multiple instances
        clone: function(){
            return $.extend(true,{},this);
        },

        init: function(lang){
            //in future we would like to have multiple recognizers presenting a single interface
            if('webkitSpeechRecognition' in window || 'SpeechRecognition' in window){
                this.recognizer=browserrecognition.clone();
                this.recognizer.init(lang);
            }else{
                log.debug('no usable speech recognizer found');
            }
        },
        start: function(){
            this.recognizer.onfinalspeechcapture = this.onfinalspeechcapture;
            this.recognizer.oninterimspeechcapture = this.oninterimspeechcapture;
            if (this.recognizer) {
                this.recognizer.start();
            }
        },
        stop: function(){
            if (this.recognizer) {
                this.recognizer.stop();
            }
        },

        onfinalspeechcapture: function(speechtext){
            log.debug('final:' + speechtext);
        },
        oninterimspeechcapture: function(speechtext){
            log.debug('interim:' + speechtext);
        }
    };//end of returned object
});//total end
