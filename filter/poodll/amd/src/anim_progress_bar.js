/* jshint ignore:start */
define(['jquery','core/log'], function($, log) {

    "use strict"; // jshint ;_;

    log.debug('Bar Progress: initialising');

    return {

        playcanvas: null,
        context: null,
        startx: null,
        starty: null,
        barwidth: null,
        barheight: null,
		enabled: false,


        //for making multiple instances
        clone: function () {
            return $.extend(true, {}, this);
        },

        //pass in config, the jquery video/audio object, and a function to be called when conversion has finshed
        init: function (playcanvas, barcolor) {
            //stash the key actors for calling from draw
            this.playcanvas = playcanvas.get(0);
            this.startx = 0;
            this.starty = 0;
            this.barwidth = this.playcanvas.width;
            this.barheight = this.playcanvas.height;
            this.context = this.playcanvas.getContext('2d');
            this.barcolor = barcolor ? barcolor : "#C2C2C2";

        },
 
        clear: function () {
            this.context.clearRect(this.startx, this.starty, this.barwidth, this.barheight);
        },
        //this function to be overridden by calling class
        //0= 0% 1=100%
        fetchCurrent: function () {
            return 0;
        },

        //stop and clear
        stop: function () {
        	this.enabled= false;
        	this.clear();
        },
        //stop without clearing
        stopthere: function () {
            this.enabled= false;
        },

        start: function () {
            this.clear();
        	this.enabled=true;
        	var that = this;
            //set draw params, later could make this configurable
            this.context.textAlign = "center";
            this.context.font="12px Arial";
            var textcolor = "black";


           

			var draw= function () {
   
                if(!that.enabled){
                   return;
                }
                that.clear();
                that.context.fillStyle = that.barcolor;
                that.context.fillRect(that.startx,that.starty,that.fetchCurrent() * that.barwidth,that.barheight);

                //draw text
                //this distorts unless we set the height and width of the canvas as canvas attributes, and NOT with CSS
                //that didn't really fit with this, so we use text in the page (ie not canvas)
                /*
                that.context.fillStyle = textcolor;
                that.context.fillText(parseInt(that.fetchCurrent() * 100) +'%',that.barwidth/2, that.barheight/2);
                */
                var requestAnimationFrame = window.requestAnimationFrame || window.mozRequestAnimationFrame ||
								  window.webkitRequestAnimationFrame || window.msRequestAnimationFrame;
                requestAnimationFrame(draw);

			}//end of draw
			draw();
    	}//end of enable
    };//end of returned object
});//total end
