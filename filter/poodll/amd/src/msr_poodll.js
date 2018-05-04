/* jshint ignore:start */
define(['jquery',
    'core/log','filter_poodll/utils_amd','filter_poodll/msr_stereoaudio','filter_poodll/msr_whammy','filter_poodll/msr_plain'],
    function($, log,utils,stereoaudiorecorder, whammyrecorder, plainrecorder) {

    "use strict"; // jshint ;_;

    log.debug('PoodLL MS Recorder: initialising');

    return {

        sampleRate: 44100,
        mimeType: 'audio/wav',
        audioChannels: 1,
        bufferSize: 2048,
        therecorder: null,
        audioctx: null,
        audioanalyser: null,

        //for making multiple instances
        clone: function(){
            return $.extend(true,{},this);
        },

        // init the poodll recorder
        // basically we check the users preferred recorders and if the rec supports the browser
        init: function(mediaStream,audioctx,audioanalyser,mediaType) {
                //we want to use the same context for absolutely everything
                //so we pass it around. analyser should be available to skins but we set it up here
                this.audioctx = audioctx;
                this.audioanalyser= audioanalyser;


            //this is where we choose which recorder/encoder set we will use
            //if browser has mediarecorder, lets use it! (FF/Chrome)
            if (typeof MediaRecorder !== 'undefined') {
                this.therecorder =  plainrecorder;
                log.debug('using plain recorder');

            } else if (utils.is_chrome() || utils.is_opera || utils.is_edge()) {
                if (mediaType=='video') {
                    this.therecorder = whammyrecorder;
                    log.debug('using whammy recorder');
                } else if (mediaType == 'audio') {
                    this.therecorder = stereoaudiorecorder;
                    log.debug('using stereo recorder');
                    //before init is called, set mimeType/sampleRate/audioChannels
                    //etc on this object, they will be picked up when stereoaudiorecorder helper runs
                }
            }
            if (this.therecorder) {
                this.therecorder.init(this, mediaStream, audioctx,mediaType);
            }

        },

        start: function() {
            this.therecorder.start();
            //start audio analyser which generates events for wav/freq visualisations
            this.audioanalyser.start();
        },

        stop: function() {
            this.therecorder.stop();
            this.audioanalyser.clear();
        },

        pause: function() {
            this.therecorder.pause();
            this.audioanalyser.clear();
        },

        resume: function() {
            this.therecorder.resume();
            this.audioanalyser.start();
        },

        ondataavailable: function(blob) {
            log.debug('ondataavailable:' + blob);
        },

        onStartedDrawingNonBlankFrames: function() {
            log.debug('started drawing non blank frames:');
        },

        onstop: function(error) {
            log.debug(error);
        }
    };// end of returned object
});// total end
