/* jshint ignore:start */
define(['jquery','core/log'], function($, log) {

    "use strict"; // jshint ;_;

    log.debug('audioanalyser: initialising');

    return {
    
        acontext: null,
		core: null,
		freq_data: null,
		wav_data: null,
        theinterval: null,
		
        //for making multiple instances
        clone: function(){
            return $.extend(true,{},this);
        },

        clear: function(){
            clearInterval(this.theinterval);
            this.theinterval=null;
        },

        //init
        init: function(acontext){
            if(this.theinterval){
                this.clear();
            }
            this.acontext = acontext;
            this.core=this.acontext.createAnalyser();
        },

        //start
        start: function(){
            if(this.theinterval){
                this.clear();
            }
            var bufferLength = this.core.frequencyBinCount;
            this.freq_data=new Uint8Array(bufferLength);
            this.wav_data=new Uint8Array(bufferLength);

            //this runs the events loop that we use to publish data
            var thisthing =this;
            this.theinterval= setInterval(function(){
                    thisthing.process_recent_data(thisthing);
                }
                ,2000);

        },

		process_recent_data: function(that){

			
			//prepare the loop that will roll over publishing data
            /*
			var raf = window.requestAnimationFrame || window.mozRequestAnimationFrame ||
						  window.webkitRequestAnimationFrame || window.msRequestAnimationFrame;
			raf(that.process_recent_data);
			*/
			//get store and publish wav data
  			that.core.getByteTimeDomainData(that.wav_data);
			that.wav_event(that.wav_data);
			
			//get store and publish freq data
			that.core.getByteFrequencyData(that.freq_data);
			that.freq_event(that.freq_data);
		},

        //overwrite these to get events 5 a second
        //but probably you will just call getByteTimeDomainData or gtByteFrequencyData from
        //requestanimationframe in your drawing code if youa re using visualisations
		//wav_event: function(data){log.debug(data);},
        wav_event: function(data){},
		freq_event: function(data){}
    };//end of returned object
});//total end
