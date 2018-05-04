/* jshint ignore:start */
define(['jquery',
    'core/log','filter_poodll/utils_amd'],
    function($, log, utils) {

    "use strict"; // jshint ;_;

    log.debug('PoodLL Plain Recorder: initialising');

    return {
        timeout: 0,
        mediaRecorder: null,
        mediaStream: null,
        audioctx: null, //unused
        msr: null,
        mediaType: null,


        //for making multiple instances
        clone: function(){
            return $.extend(true,{},this);
        },

        // init the recorder
        init: function(msr,mediaStream, audioctx, mediaType) {
                this.msr= msr;
                this.mediaStream= mediaStream;
                this.audioctx = audioctx; //unused
                this.mediaType=mediaType;
        },

        /**
         * This method records MediaStream.
         * @method
         * @memberof MediaStreamRecorder
         * @example
         * recorder.record();
         */
        start: function(timeSlice, __disableLogs) {
        var that = this;
        if (!this.mimeType && this.mediaType == 'video') {
            this.mimeType = 'video/webm';
        }

        if (this.mediaType == 'audio') {
            if (this.mediaStream.getVideoTracks().length && this.mediaStream.getAudioTracks().length) {
                var stream;
                if (!!navigator.mozGetUserMedia) {
                    stream = new MediaStream();
                    stream.addTrack(this.mediaStream.getAudioTracks()[0]);
                } else {
                    // webkitMediaStream
                    stream = new MediaStream(this.mediaStream.getAudioTracks());
                }
               this.mediaStream = stream;
            }
        }

        if (this.mediaType == 'audio') {
            this.mimeType = utils.is_chrome() ? 'audio/webm' : 'audio/ogg';
        }

        var recorderHints = {
            mimeType: this.mimeType
        };


        // http://dxr.mozilla.org/mozilla-central/source/content/media/MediaRecorder.cpp
        // https://wiki.mozilla.org/Gecko:MediaRecorder
        // https://dvcs.w3.org/hg/dap/raw-file/default/media-stream-capture/MediaRecorder.html

        // starting a recording session; which will initiate "Reading Thread"
        // "Reading Thread" are used to prevent main-thread blocking scenarios
        try {
            this.mediaRecorder = new MediaRecorder(this.mediaStream, recorderHints);
        } catch (e) {
            // if someone passed NON_supported mimeType
            // or if Firefox on Android
            this.mediaRecorder = new MediaRecorder(this.mediaStream);
        }

        if ('canRecordMimeType' in this.mediaRecorder && this.mediaRecorder.canRecordMimeType(this.mimeType) === false) {
            log.debug('MediaRecorder API seems unable to record mimeType:' + this.mimeType);
        }

        // i.e. stop recording when <video> is paused by the user; and auto restart recording 
        // when video is resumed. E.g. yourStream.getVideoTracks()[0].muted = true; // it will auto-stop recording.
        this.mediaRecorder.ignoreMutedMedia = this.ignoreMutedMedia || false;


        // Dispatching OnDataAvailable Handler
        this.mediaRecorder.ondataavailable = function(e) {
            //  log.debug('data available:' + e.data.size );
            if (!e.data || !e.data.size ) {
                return;
            }

            var blob = that.getNativeBlob ? e.data : new Blob([e.data], {
                type: that.mimeType || 'video/webm'
            });

            that.msr.ondataavailable(blob);

        };

        this.mediaRecorder.onerror = function(error) {
                if (error.name === 'InvalidState') {
                    log.debug('The MediaRecorder is not in a state in which the proposed operation is allowed to be executed.');
                } else if (error.name === 'OutOfMemory') {
                    log.debug('The UA has exhaused the available memory. User agents SHOULD provide as much additional information as possible in the message attribute.');
                } else if (error.name === 'IllegalStreamModification') {
                    log.debug('A modification to the stream has occurred that makes it impossible to continue recording. An example would be the addition of a Track while recording is occurring. User agents SHOULD provide as much additional information as possible in the message attribute.');
                } else if (error.name === 'OtherRecordingError') {
                    log.debug('Used for an fatal error other than those listed above. User agents SHOULD provide as much additional information as possible in the message attribute.');
                } else if (error.name === 'GenericError') {
                    log.debug('The UA cannot provide the codec or recording option that has been requested.', error);
                } else {
                    log.debug('MediaRecorder Error', error);
                }

            // When the stream is "ended" set recording to 'inactive' 
            // and stop gathering data. Callers should not rely on 
            // exactness of the timeSlice value, especially 
            // if the timeSlice value is small. Callers should 
            // consider timeSlice as a minimum value

            if (!!that.mediaRecorder && that.mediaRecorder.state !== 'inactive' && that.mediaRecorder.state !== 'stopped') {
                that.mediaRecorder.stop();
            }
        };

        //We need a source node to connect the analyser to. The analyser is for visualisations
        var audioInput = this.audioctx.createMediaStreamSource(this.mediaStream);
        audioInput.connect(this.msr.audioanalyser.core);

        // void start(optional long mTimeSlice)
        // The interval of passing encoded data from EncodedBufferCache to onDataAvailable
        // handler. "mTimeSlice < 0" means Session object does not push encoded data to
        // onDataAvailable, instead, it passive wait the client side pull encoded data
        // by calling requestData API.
        try {
            that.mediaRecorder.start(timeSlice);
        } catch (e) {
            that.mediaRecorder = null;
        }

//end of start
    },

        /**
         * This method stops recording MediaStream.
         * @param {function} callback - Callback function, that is used to pass recorded blob back to the callee.
         * @method
         * @memberof MediaStreamRecorder
         * @example
         * recorder.stop(function(blob) {
     *     video.src = URL.createObjectURL(blob);
     * });
         */
        stop: function(callback) {
            if (!this.mediaRecorder) {
                return;
            }

            if (this.mediaRecorder.state === 'recording') {
                this.mediaRecorder.stop();
            }
        },

        /**
         * This method pauses the recording process.
         * @method
         * @memberof MediaStreamRecorder
         * @example
         * recorder.pause();
         */
        pause: function() {
            if (!this.mediaRecorder) {
                return;
            }

            if (this.mediaRecorder.state === 'recording') {
                this.mediaRecorder.pause();
            }
        },

        /**
         * This method resumes the recording process.
         * @method
         * @memberof MediaStreamRecorder
         * @example
         * recorder.resume();
         */
        resume:  function() {
            if (!this.mediaRecorder) {
                return;
            }
            if (this.mediaRecorder.state === 'paused') {
                this.mediaRecorder.resume();
            }
        },

        /**
         * This method resets currently recorded data.
         * @method
         * @memberof MediaStreamRecorder
         * @example
         * recorder.clearRecordedData();
         */
        clearRecordedData:  function() {
            if (!this.mediaRecorder) {
                return;
            }

            this.pause();

            this.stop();
        },

        // Reference to "MediaRecorder" object
      //  var mediaRecorder;

        isMediaStreamActive: function() {
            if ('active' in this.mediaStream) {
                if (!this.mediaStream.active) {
                    return false;
                }
            } else if ('ended' in this.mediaStream) { // old hack
                if (this.mediaStream.ended) {
                    return false;
                }
            }
            return true;
        },

        // this method checks if media stream is stopped
        // or any track is ended.
        looper: function () {
            if (!this.mediaRecorder) {
                return;
            }

            if (this.isMediaStreamActive() === false) {
                this.stop();
                return;
            }

            setTimeout(this.looper, 1000); // check every second
        }
    };// end of returned object
});// total end