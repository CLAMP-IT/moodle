/* jshint ignore:start */
define(['jquery',
    'core/log','filter_poodll/utils_amd','filter_poodll/poodll_whammy'],
    function($, log,utils, whammyencoder) {

    "use strict"; // jshint ;_;

    log.debug('PoodLL Whammy Recorder Helper: initialising');

    return {


        requestDataInvoked: false,
        isOnStartedDrawingNonBlankFramesInvoked: false,
        isStopDrawing: false,
        canvas: null,
        context: null,
        video: false,
        lastTime: false,
        whammy: false,
        isPaused: false,
        width: false,
        height: false,
        speed: 0.8, //?
        quality: 100, //?
        mediaStream: null,
        msr: null,
        audioctx: null,

        //for making multiple instances
        clone: function(){
            return $.extend(true,{},this);
        },

        // init the class
        init: function(msr,mediaStream,audioctx) {
            this.canvas = document.createElement('canvas');
            this.context = this.canvas.getContext('2d');
            this.mediaStream = mediaStream;
            this.msr=msr;
            this.audioctx = audioctx;
            log.debug('initing whammy helper');
        },

        record: function(timeSlice) {
            log.debug('recordingin whammy helper');

            if (!this.width) {
            this.width = 320;
        }
        if (!this.height) {
            this.height = 240;
        }

        if (this.video && this.video instanceof HTMLVideoElement) {
            if (!this.width) {
                this.width = this.video.videoWidth || this.video.clientWidth || 320;
            }
            if (!this.height) {
                this.height = this.video.videoHeight || this.video.clientHeight || 240;
            }
        }

        if (!this.video) {
            this.video = {
                width: this.width,
                height: this.height
            };
        }

        if (!this.canvas || !this.canvas.width || !this.canvas.height) {
            this.canvas = {
                width: this.width,
                height: this.height
            };
        }

        // setting defaults
            // some confusion between this.video and video (renamed video to tempvideo)
        if (this.video && this.video instanceof HTMLVideoElement) {
            this.isHTMLObject = true;
        } else {

            this.video = document.createElement('video');
            this.video.srcObject=this.mediaStream;
/*
            this.video.width = this.video.width;
            this.video.height = this.video.height;
            */
        }

        this.video.muted = true;
        this.video.play();

        this.lastTime = new Date().getTime();
        this.whammy = whammyencoder.Video;
        this.whammy.init(this.speed, this.quality);

        this.drawFrames(this);
    },

        clearOldRecordedFrames: function() {
            this.whammy.frames = [];
        },


        requestData: function() {
            var that = this;
            if (this.isPaused) {
                return;
            }

            if (!this.whammy.frames.length) {
                this.requestDataInvoked = false;
                return;
            }

            this.requestDataInvoked = true;
            // clone stuff
            var internalFrames = this.whammy.frames.slice(0);

            // reset the frames for the new recording

            this.whammy.frames = this.dropBlackFrames(internalFrames, -1);

            this.whammy.compile(function(whammyBlob) {
                that.msr.ondataavailable(whammyBlob);
                log.debug('video recorded blob size:' + utils.bytesToSize(whammyBlob.size));
            });


            this.whammy.frames = [];

            this.requestDataInvoked = false;
        },



        drawFrames: function(self) {
            if (self.isPaused) {
                self.lastTime = new Date().getTime();
                setTimeout(function(){self.drawFrames(self);}, 500);
                return;
            }

            if (self.isStopDrawing) {
                return;
            }

            if (self.requestDataInvoked) {
                return setTimeout(function(){self.drawFrames(self);}, 100);
            }

            var duration = new Date().getTime() - self.lastTime;
            if (!duration) {
                return self.drawFrames(self);
            }

            // via webrtc-experiment#206, by Jack i.e. @Seymourr
            self.lastTime = new Date().getTime();

            if (!self.isHTMLObject && self.video.paused) {
                self.video.play(); // Android
            }

            self.context.drawImage(self.video, 0, 0, self.canvas.width, self.canvas.height);

            if (!self.isStopDrawing) {
                self.whammy.frames.push({
                    duration: duration,
                    image: self.canvas.toDataURL('image/webp')
                });
            }

            if (!self.isOnStartedDrawingNonBlankFramesInvoked && !self.isBlankFrame(self.whammy.frames[self.whammy.frames.length - 1])) {
                self.isOnStartedDrawingNonBlankFramesInvoked = true;
                self.msr.onStartedDrawingNonBlankFrames();
            }
            setTimeout(function(){self.drawFrames(self);}, 10);
        },



        stop: function() {
            this.isStopDrawing = true;
            this.requestData();
        },


        isBlankFrame: function(frame, _pixTolerance, _frameTolerance) {
            var localCanvas = document.createElement('canvas');
            localCanvas.width = this.canvas.width;
            localCanvas.height = this.canvas.height;
            var context2d = localCanvas.getContext('2d');

            var sampleColor = {
                r: 0,
                g: 0,
                b: 0
            };
            var maxColorDifference = Math.sqrt(
                Math.pow(255, 2) +
                Math.pow(255, 2) +
                Math.pow(255, 2)
            );
            var pixTolerance = _pixTolerance && _pixTolerance >= 0 && _pixTolerance <= 1 ? _pixTolerance : 0;
            var frameTolerance = _frameTolerance && _frameTolerance >= 0 && _frameTolerance <= 1 ? _frameTolerance : 0;

            var matchPixCount, endPixCheck, maxPixCount;

            var image = new Image();
            image.src = frame.image;
            context2d.drawImage(image, 0, 0, this.canvas.width, this.canvas.height);
            var imageData = context2d.getImageData(0, 0, this.canvas.width, this.canvas.height);
            matchPixCount = 0;
            endPixCheck = imageData.data.length;
            maxPixCount = imageData.data.length / 4;

            for (var pix = 0; pix < endPixCheck; pix += 4) {
                var currentColor = {
                    r: imageData.data[pix],
                    g: imageData.data[pix + 1],
                    b: imageData.data[pix + 2]
                };
                var colorDifference = Math.sqrt(
                    Math.pow(currentColor.r - sampleColor.r, 2) +
                    Math.pow(currentColor.g - sampleColor.g, 2) +
                    Math.pow(currentColor.b - sampleColor.b, 2)
                );
                // difference in color it is difference in color vectors (r1,g1,b1) <=> (r2,g2,b2)
                if (colorDifference <= maxColorDifference * pixTolerance) {
                    matchPixCount++;
                }
            }

            if (maxPixCount - matchPixCount <= maxPixCount * frameTolerance) {
                return false;
            } else {
                return true;
            }
        },

        dropBlackFrames: function(_frames, _framesToCheck, _pixTolerance, _frameTolerance) {
            var localCanvas = document.createElement('canvas');
            localCanvas.width = this.canvas.width;
            localCanvas.height = this.canvas.height;
            var context2d = localCanvas.getContext('2d');
            var resultFrames = [];

            var checkUntilNotBlack = _framesToCheck === -1;
            var endCheckFrame = (_framesToCheck && _framesToCheck > 0 && _framesToCheck <= _frames.length) ?
                _framesToCheck : _frames.length;
            var sampleColor = {
                r: 0,
                g: 0,
                b: 0
            };
            var maxColorDifference = Math.sqrt(
                Math.pow(255, 2) +
                Math.pow(255, 2) +
                Math.pow(255, 2)
            );
            var pixTolerance = _pixTolerance && _pixTolerance >= 0 && _pixTolerance <= 1 ? _pixTolerance : 0;
            var frameTolerance = _frameTolerance && _frameTolerance >= 0 && _frameTolerance <= 1 ? _frameTolerance : 0;
            var doNotCheckNext = false;

            for (var f = 0; f < endCheckFrame; f++) {
                var matchPixCount, endPixCheck, maxPixCount;

                if (!doNotCheckNext) {
                    var image = new Image();
                    image.src = _frames[f].image;
                    context2d.drawImage(image, 0, 0, this.canvas.width, this.canvas.height);
                    var imageData = context2d.getImageData(0, 0, this.canvas.width, this.canvas.height);
                    matchPixCount = 0;
                    endPixCheck = imageData.data.length;
                    maxPixCount = imageData.data.length / 4;

                    for (var pix = 0; pix < endPixCheck; pix += 4) {
                        var currentColor = {
                            r: imageData.data[pix],
                            g: imageData.data[pix + 1],
                            b: imageData.data[pix + 2]
                        };
                        var colorDifference = Math.sqrt(
                            Math.pow(currentColor.r - sampleColor.r, 2) +
                            Math.pow(currentColor.g - sampleColor.g, 2) +
                            Math.pow(currentColor.b - sampleColor.b, 2)
                        );
                        // difference in color it is difference in color vectors (r1,g1,b1) <=> (r2,g2,b2)
                        if (colorDifference <= maxColorDifference * pixTolerance) {
                            matchPixCount++;
                        }
                    }
                }

                if (!doNotCheckNext && maxPixCount - matchPixCount <= maxPixCount * frameTolerance) {
                    // console.log('removed black frame : ' + f + ' ; frame duration ' + _frames[f].duration);
                } else {
                    // console.log('frame is passed : ' + f);
                    if (checkUntilNotBlack) {
                        doNotCheckNext = true;
                    }
                    resultFrames.push(_frames[f]);
                }
            }

            resultFrames = resultFrames.concat(_frames.slice(endCheckFrame));

            if (resultFrames.length <= 0) {
                // at least one last frame should be available for next manipulation
                // if total duration of all frames will be < 1000 than ffmpeg doesn't work well...
                resultFrames.push(_frames[_frames.length - 1]);
            }

            return resultFrames;
        },


        pause: function() {
            this.isPaused = true;
        },

        resume: function() {
            this.isPaused = false;
        }
    };// end of returned object
});// total end
