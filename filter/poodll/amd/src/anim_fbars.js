/* jshint ignore:start */
define(['jquery','core/log'], function($, log) {

    "use strict"; // jshint ;_;

    log.debug('anim_freq bars: initialising');

    return {

        analyser: null,
        cvs: null,
        cvsctx: null,

        //for making multiple instances
        clone: function () {
            return $.extend(true, {}, this);
        },


        //init
        init: function (analyser, cvs) {
            this.cvs = cvs;
            this.cvsctx=cvs.getContext("2d");
            this.analyser = analyser;
        },

        clear: function(){
            this.cvsctx.clearRect(0, 0, this.cvs.width,this.cvs.height);
        },

        start: function(){
            this.analyser.core.fftSize = 256;
            var bufferLength = this.analyser.core.frequencyBinCount;
            var dataArray = new Uint8Array(bufferLength);
            var cwidth = this.cvs.width;
            var cheight = this.cvs.height;
            var canvasCtx = this.cvsctx;
            var analyser = this.analyser;
            this.clear();

            var draw = function () {
                //this sets up the loop
                var drawVisual = requestAnimationFrame(draw);

                //cancel out if the theinterval is null
                if(!analyser.theinterval){return;}

                analyser.core.getByteFrequencyData(dataArray);

                //filling is rubbish, we just clear it
                //canvasCtx.fillStyle = 'rgb(0, 0, 0)';
                //canvasCtx.fillRect(0, 0, cwidth, cheight);
                canvasCtx.clearRect(0, 0, cwidth,cheight);

                var barWidth = (cwidth / bufferLength) * 2.5;
                var barHeight;
                var x = 0;

                for(var i = 0; i < bufferLength; i++) {
                    barHeight = dataArray[i];

                    canvasCtx.fillStyle = 'rgb(' + (barHeight+100) + ',50,50)';
                    canvasCtx.fillRect(x,cheight-barHeight/2,barWidth,barHeight/2);

                    x += barWidth + 1;
                }
            };

            draw();
        }//END OF START
    };//end of returned object
});//total end
