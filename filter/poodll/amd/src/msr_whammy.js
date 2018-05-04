/* jshint ignore:start */
define(['jquery',
    'core/log','filter_poodll/msr_helper_whammy'],
    function($, log, helper) {

    "use strict"; // jshint ;_;

    log.debug('PoodLL MSR Whammy: initialising');

    return {
        timeout: 0,
        mediaRecorder: null,
        mediaStream: null,
        msr: null,
        audioctx: null, //unused
        mediaType: 'video',

        //for making multiple instances
        clone: function(){
            return $.extend(true,{},this);
        },

        // init the poodll recorder
        // basically we check the users preferred recorders and if the rec supports the browser
        init: function(msr,mediaStream,audioctx,mediaType) {
            this.msr = msr;
            this.mediaStream = mediaStream;
            this.audioctx = audioctx; //unused
            this.mediaType = mediaType; //always video
        },

        start:  function(timeSlice,audioctx) {
            timeSlice = timeSlice || 1000;
            var that=this;

            this.mediaRecorder = helper.clone();
            this.mediaRecorder.init(this.msr,this.mediaStream, this.audioctx);

            this.mediaRecorder.record();

            this.timeout = setInterval(function() {
                that.mediaRecorder.requestData();
            }, timeSlice);
        },

        stop:  function() {
            if (this.mediaRecorder) {
                //this will trigger the final requestData() [msr_helper_whammy] and
                // the final onMediaAvailable() [poodll_mediarecorder]
                this.mediaRecorder.stop();
                clearTimeout(this.timeout);
            }
        },
        clearOldRecordedFrames: function() {
            if (this.mediaRecorder) {
                this.mediaRecorder.clearOldRecordedFrames();
            }
        },
        pause: function() {
            if (!this.mediaRecorder) {
                return;
            }

            this.mediaRecorder.pause();
        },

        resume:  function() {
            if (!this.mediaRecorder) {
                return;
            }
            this.mediaRecorder.resume();
        },

        ondataavailable: function(blob) {
            log.debug('ondataavailable:' + blob);
        },

        onstop: function(error) {
            log.debug(error);
        }
    };// end of returned object
});// total end
