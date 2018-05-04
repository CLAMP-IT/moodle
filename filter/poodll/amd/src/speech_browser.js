/* jshint ignore:start */
define(['jquery','core/log'], function($, log) {

    "use strict"; // jshint ;_;

    log.debug('speech_browser: initialising');

    return {

        recognition: null,
        recognizing: false,
        ignore_onend: false,
        final_transcript: '',
        start_timestamp: 0,
        lang: 'en-US',


    //for making multiple instances
        clone: function(){
            return $.extend(true,{},this);
        },

        init: function(lang){
            var SpeechRecognition = SpeechRecognition || webkitSpeechRecognition;
            this.recognition = new SpeechRecognition();
            this.recognition.continuous = true;
            this.recognition.interimResults = true;
            this.lang= lang ? lang : 'en-US';

            this.register_events();
        },
        start: function(){
            if (this.recognizing) {
                return;
            }
            this.final_transcript = '';
            this.recognition.lang = this.lang;//select_dialect.value;
            this.recognition.start();
            this.ignore_onend = false;
            this.start_timestamp = Date.now();//event.timeStamp;

        },
        stop: function(){
            if (this.recognizing) {
                this.recognition.stop();
                return;
            }
        },

        register_events: function(){

           var recognition= this.recognition;
           var that= this;

            recognition.onstart = function() {
                that.recognizing = true;

            };
            recognition.onerror = function(event) {
                if (event.error == 'no-speech') {
                    log.debug('info_no_speech');
                    that.ignore_onend = true;
                }
                if (event.error == 'audio-capture') {
                    log.debug('info_no_microphone');
                    that.ignore_onend = true;
                }
                if (event.error == 'not-allowed') {
                    if (event.timeStamp - that.start_timestamp < 100) {
                        log.debug('info_blocked');
                    } else {
                        log.debug('info_denied');
                    }
                    that.ignore_onend = true;
                }
            };
            recognition.onend = function() {
                that.recognizing = false;

            };
            recognition.onresult = function(event) {
                var interim_transcript = '';
                for (var i = event.resultIndex; i < event.results.length; ++i) {
                    if (event.results[i].isFinal) {
                        that.final_transcript += event.results[i][0].transcript;
                        that.onfinalspeechcapture(that.final_transcript);
                        that.final_transcript='';
                    } else {
                        interim_transcript += event.results[i][0].transcript;
                        that.oninterimspeechcapture(interim_transcript);
                    }
                }


            };
        },//end of register events

        onfinalspeechcapture: function(speechtext){
            log.debug(speechtext);
        },
        oninterimspeechcapture: function(speechtext){
            log.debug(speechtext);
        }

    };//end of returned object
});//total end
