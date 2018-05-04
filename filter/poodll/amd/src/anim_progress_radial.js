/* jshint ignore:start */
define(['jquery','core/log'], function($, log) {

    "use strict"; // jshint ;_;

    log.debug('Radial Progress: initialising');

    return {

        playcanvas: null,
        context: null,
        x: null,
        y: null,
        currenttime: 0,
		enabled: false,


        //for making multiple instances
        clone: function () {
            return $.extend(true, {}, this);
        },

        //pass in config, the jquery video/audio object, and a function to be called when conversion has finshed
        init: function (playcanvas) {
            //stash the key actors for calling from draw
            this.playcanvas = playcanvas.get(0);
            this.x = this.playcanvas.width / 2;
            this.y = this.playcanvas.height / 2;
            this.context = this.playcanvas.getContext('2d');

        },

        clear: function () {
            this.context.clearRect(0, 0, this.playcanvas.width, this.playcanvas.height);
        },
        fetchCurrent: function () {
            return 0;
        },

        stop: function () {
        	this.enabled= false;
        	this.clear();
        },

        start: function () {
            this.clear();
        	this.enabled=true;
        	var that = this;

            //set draw params, later could make this configurable
            this.context.lineWidth = 10;
            this.context.strokeStyle = '#ad2323';
            this.context.setLineDash([]);
            this.context.shadowOffsetX = 0;
            this.context.shadowOffsetY = 0;
            this.context.shadowBlur = 10;
            this.context.shadowColor = '#fff';

			var draw= function () {
                if(!that.enabled){
                   return;
                }

				var radius = 65;
				var counterClockwise = false;
				var circ = Math.PI * 2;
				var quart = Math.PI / 2;
				that.context.beginPath();
				that.context.arc(that.x, that.y, radius, -(quart), ((circ) * that.fetchCurrent()) - quart, counterClockwise);
				that.context.stroke();

				var requestAnimationFrame = window.requestAnimationFrame || window.mozRequestAnimationFrame ||
								  window.webkitRequestAnimationFrame || window.msRequestAnimationFrame;
                requestAnimationFrame(draw);

			}//end of draw
			draw();
    	}//end of enable
    };//end of returned object
});//total end
