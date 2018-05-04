/* jshint ignore:start */
define(['jquery','core/log', 'filter_poodll/speech_poodll'], function($, log, speechrecognition) {

    "use strict"; // jshint ;_;

    log.debug('anim_words: initialising');

    return {

        analyser: null,
        cvs: null,
        cvsctx: null,
        speechrec: null,
        enabled: false,

        //for making multiple instances
        clone: function () {
            return $.extend(true, {}, this);
        },


        //init
        init: function (analyser, cvs) {
            this.cvs = cvs;
            this.cvsctx=cvs.getContext("2d");
            this.analyser = analyser;

            this.speechrec = speechrecognition.clone();
            this.speechrec.init('en-US');

        },
        //clear
        //more specifically stop, but to be consistent with how we do other anims, we call it clear
        clear: function(){
            this.cvsctx.clearRect(0, 0, this.cvs.width,this.cvs.height);
            this.enabled=false;
            this.speechrec.stop();
        },


        //start the anim
        start: function(){
            //set up variables used in drawing
            this.enabled = true;
            var that = this;
            this.analyser.core.fftSize = 2048;
            var bufferLength = this.analyser.core.fftSize;
            var dataArray = new Uint8Array(bufferLength);
            var cwidth = this.cvs.width;
            var cheight = this.cvs.height;

            //clear the canvas
            this.cvsctx.clearRect(0, 0, cwidth,this.ceight);

            //set up speechrecognizer to fill words array
            var words = ['..','..','..','..','..','..','..','..'];
            this.speechrec.oninterimspeechcapture = function(speechtext){
                var newwords= speechtext.split(' ');
                words = words.concat(newwords);
            };
            this.speechrec.start();

            var draw = function () {

                //cancel out if no longer active is null.
                if(!that.enabled){return;}

                //this is the loop that continually calls itself to draw
                var reqAnimFrame = window.requestAnimationFrame || window.mozRequestAnimationFrame ||
                    window.webkitRequestAnimationFrame || window.msRequestAnimationFrame;
                var drawVisual = reqAnimFrame(draw);



                //this is the audio data
                that.analyser.core.getByteTimeDomainData(dataArray);

                //this fills grey, but its lame lets just leave it clear
                //that.cvsctx.fillStyle = 'rgb(200, 200, 200)';
                that.cvsctx.clearRect(0, 0, cwidth,cheight);

                //sets up the pen
                that.cvsctx.lineWidth = 2;
                that.cvsctx.strokeStyle = 'rgb(0, 0, 0)';
                that.cvsctx.beginPath();
                //how long to drw each datapoint
                var sliceWidth = cwidth * 1.0 / bufferLength;
                var x = 0;
                //draw all the points
                for (var i = 0; i < bufferLength; i++) {

                    var v = dataArray[i] / 128.0;
                    var y = v * cheight / 2;

                    if (i === 0) {
                        that.cvsctx.moveTo(x, y);
                    } else {
                        that.cvsctx.lineTo(x, y);
                    }

                    x += sliceWidth;
                }

                that.cvsctx.lineTo(cwidth, cheight / 2);
                that.cvsctx.stroke();

                //draw words
                that.cvsctx.font = "14px Comic Sans MS";
                that.cvsctx.fillStyle = "black";
                that.cvsctx.textAlign = "center";
                var cellvcenter = cheight/4;
                var cellwidth=cwidth / 4;
                var cellhcenter = cwidth / 8;
                for (i=1; i<9;i++){
                    that.cvsctx.fillText(words[words.length-i], (cellwidth * (i % 4)) + cellhcenter, i <5 ? cellvcenter : cellvcenter *3);
                }
            };

            draw();
        }//END OF START
    };//end of returned object
});//total end
