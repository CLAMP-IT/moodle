/* jshint ignore:start */
define(['jquery','core/log'], function($, log) {

    "use strict"; // jshint ;_;

    log.debug('anim_ripple: initialising');

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
            this.analyser.core.fftSize = 2048;
            var bufferLength = this.analyser.core.fftSize;
            var dataArray = new Uint8Array(bufferLength);
            var cwidth = this.cvs.width;
            var cheight = this.cvs.height;
            var canvasCtx = this.cvsctx;
            var analyser = this.analyser;
            this.clear();

            var draw = function () {

                var drawVisual = requestAnimationFrame(draw);

                //cancel out if the theinterval is null
                if(!analyser.theinterval){return;}

                analyser.core.getByteTimeDomainData(dataArray);

                //this fills grey, but its lame lets just leave it clear
                //canvasCtx.fillStyle = 'rgb(200, 200, 200)';
                canvasCtx.clearRect(0, 0, cwidth,cheight);

                canvasCtx.lineWidth = 2;
                canvasCtx.setLineDash([15, 5]);
                canvasCtx.strokeStyle = 'rgb(0, 0, 0)';

                canvasCtx.beginPath();

                var recwidth= 100;
                if(bufferLength>0) {
                    var stepsize = 1 + bufferLength / 5;
                }


                for (var i = 0; i < bufferLength; i=i+stepsize) {
                    var v = dataArray[i] / 128.0;
                    var y = v * (cheight - recwidth) / 4;
                    var radius = recwidth / 2  + y
                    canvasCtx.arc(cwidth/2,cheight/2,radius,0,2*Math.PI);
                }
                canvasCtx.stroke();
            };

            draw();
        }//END OF START
    };//end of returned object
});//total end
