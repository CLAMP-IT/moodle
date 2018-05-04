/* jshint ignore:start */
define(['jquery',
    'core/log'],
    function($, log) {

    "use strict"; // jshint ;_;

    log.debug('PoodLL Stereo Audio Recorder Helper: initialising');

    return {

        //globals??
        scriptprocessornode: null,
        requestDataInvoked: false,
        recordingLength: 0,
        isPaused: false,

        // variables
        deviceSampleRate: 44100, // range: 22050 to 96000
        leftchannel: [],
        rightchannel: [],
        recording:  false,
        volume: null,
        audioInput: null,
        context: null,
        sampleRate: 0,
        mimeType: 0,
        isPCM: false,
        numChannels: 1,
        msr: null,
        audioctx: null,
        mediaStream: null,
        volumeGainNode: null,

        //for making multiple instances
        clone: function(){
            return $.extend(true,{},this);
        },


        init: function(msr,mediaStream,audioctx){

            this.msr=msr;
            this.audioctx = audioctx;
            this.mediaStream = mediaStream;
            this.deviceSampleRate= audioctx.sampleRate;
            this.sampleRate = msr.sampleRate || this.deviceSampleRate;
            this.mimeType= msr.mimeType || 'audio/wav';
            this.isPCM= this.mimeType.indexOf('audio/pcm') > -1;
            this.numChannels= msr.audioChannels || 2;

            //and then further init'ing
            this.misc();

        },

        misc: function(){
            var that=this;
            // creates the audio context
            var context = this.audioctx;

            // creates a gain node
            this.volumeGainNode = context.createGain();

            var volume = this.volumeGainNode;

            // creates an audio node from the microphone incoming stream
            //the node chain is audioinput -> volume -> analyser -> scriptprocessingnode
            this.audioInput = context.createMediaStreamSource(this.mediaStream);

            // creates an audio node from the microphone incoming stream
            var audioInput = this.audioInput;

            // connect the stream to the gain node
            audioInput.connect(volume);

            /* From the spec: This value controls how frequently the audioprocess event is
             dispatched and how many sample-frames need to be processed each call.
             Lower values for buffer size will result in a lower (better) latency.
             Higher values will be necessary to avoid audio breakup and glitches
             Legal values are 256, 512, 1024, 2048, 4096, 8192, and 16384.*/
            var bufferSize = this.msr.bufferSize || 2048;
            if (this.msr.bufferSize === 0) {
                bufferSize = 0;
            }

            var scriptprocessornode = null;
            if (context.createJavaScriptNode) {
                scriptprocessornode = context.createJavaScriptNode(bufferSize, this.numChannels, this.numChannels);
            } else if (context.createScriptProcessor) {
                scriptprocessornode = context.createScriptProcessor(bufferSize, this.numChannels, this.numChannels);
            } else {
                throw 'WebAudio API has no support on this browser.';
            }

            this.bufferSize = scriptprocessornode.bufferSize;
            this.requestDataInvoked = false;

            // sometimes "scriptprocessornode" disconnects from he destination-node
            // and there is no exception thrown in this case.
            // and obviously no further "ondataavailable" events will be emitted.
            // below global-scope variable is added to debug such unexpected but "rare" cases.
            this.scriptprocessornode = scriptprocessornode;

            if (this.numChannels === 1) {
                console.debug('All right-channels are skipped.');
            }

            this.isPaused = false;

            //http://webaudio.github.io/web-audio-api/#the-scriptprocessornode-interface
            scriptprocessornode.onaudioprocess = function(e) {
                if (!that.recording || that.requestDataInvoked || that.isPaused) {
                    return;
                }

                var left = e.inputBuffer.getChannelData(0);
                that.leftchannel.push(new Float32Array(left));

                if (that.numChannels === 2) {
                    var right = e.inputBuffer.getChannelData(1);
                    that.rightchannel.push(new Float32Array(right));
                }
                that.recordingLength += that.bufferSize;
            };

            volume.connect(this.msr.audioanalyser.core);
           // volume.connect(this.scriptprocessornode);
            this.msr.audioanalyser.core.connect(scriptprocessornode);
            scriptprocessornode.connect(context.destination);

        },

        record: function() {
            this.recording = true;
            // reset the buffers for the new recording
            this.leftchannel.length = this.rightchannel.length = 0;
            this.recordingLength = 0;
        },

        requestData:  function() {

            if (this.isPaused) {
                return;
            }

            if (this.recordingLength === 0) {
                this.requestDataInvoked = false;
                return;
            }

            this.requestDataInvoked = true;
            // clone stuff
            var internalLeftChannel = this.leftchannel.slice(0);
            var internalRightChannel = this.rightchannel.slice(0);
            var internalRecordingLength = this.recordingLength;

            // reset the buffers for the new recording
            this.leftchannel.length = this.rightchannel.length = [];
            this.recordingLength = 0;
            this.requestDataInvoked = false;

            // we flat the left and right channels down
            var leftBuffer = this.mergeBuffers(internalLeftChannel, internalRecordingLength);

            var interleaved = leftBuffer;

            // we interleave both channels together
            if (this.numChannels === 2) {
                var rightBuffer = this.mergeBuffers(internalRightChannel, internalRecordingLength); // bug fixed via #70,#71
                this.interleaved = this.interleave(leftBuffer, rightBuffer);
            }

            if (this.isPCM) {
                // our final binary blob
                var blob = new Blob([this.convertoFloat32ToInt16(interleaved)], {
                    type: 'audio/pcm'
                });

                this.msr.ondataavailable(blob);
                return;
            }

            // we create our wav file
            var buffer = new ArrayBuffer(44 + interleaved.length * 2);
            var view = new DataView(buffer);

            // RIFF chunk descriptor
            this.writeUTFBytes(view, 0, 'RIFF');

            // -8 (via #97)
            view.setUint32(4, 44 + interleaved.length * 2 - 8, true);

            this.writeUTFBytes(view, 8, 'WAVE');
            // FMT sub-chunk
            this.writeUTFBytes(view, 12, 'fmt ');
            view.setUint32(16, 16, true);
            view.setUint16(20, 1, true);
            // stereo (2 channels)
            view.setUint16(22, this.numChannels, true);
            view.setUint32(24, this.sampleRate, true);
            view.setUint32(28, this.sampleRate * this.numChannels * 2, true); // numChannels * 2 (via #71)
            view.setUint16(32, this.numChannels * 2, true);
            view.setUint16(34, 16, true);
            // data sub-chunk
            this.writeUTFBytes(view, 36, 'data');
            view.setUint32(40, interleaved.length * 2, true);

            // write the PCM samples
            var lng = interleaved.length;
            var index = 44;
            var volume = 1;
            for (var i = 0; i < lng; i++) {
                view.setInt16(index, interleaved[i] * (0x7FFF * volume), true);
                index += 2;
            }

            // our final binary blob
            var blob = new Blob([view], {
                type: 'audio/wav'
            });
            this.msr.ondataavailable(blob);
        },

        stop: function() {
            // we stop recording
            this.recording = false;
            this.requestData();
            this.audioInput.disconnect();
        },

        interleave: function(leftChannel, rightChannel) {
            var length = leftChannel.length + rightChannel.length;
            var result = new Float32Array(length);

            var inputIndex = 0;

            for (var index = 0; index < length;) {
                result[index++] = leftChannel[inputIndex];
                result[index++] = rightChannel[inputIndex];
                inputIndex++;
            }
            return result;
        },

        mergeBuffers: function(channelBuffer, recordingLength) {
            var result = new Float32Array(recordingLength);
            var offset = 0;
            var lng = channelBuffer.length;
            for (var i = 0; i < lng; i++) {
                var buffer = channelBuffer[i];
                result.set(buffer, offset);
                offset += buffer.length;
            }
            return result;
        },

        writeUTFBytes: function (view, offset, string) {
            var lng = string.length;
            for (var i = 0; i < lng; i++) {
                view.setUint8(offset + i, string.charCodeAt(i));
            }
        },

        convertoFloat32ToInt16: function (buffer) {
            var l = buffer.length;
            var buf = new Int16Array(l)

            while (l--) {
                buf[l] = buffer[l] * 0xFFFF; //convert to 16 bit
            }
            return buf.buffer
        },



        pause: function() {
            this.isPaused = true;
        },

        resume: function() {
            this.isPaused = false;
        }

    };// end of returned object
});// total end
