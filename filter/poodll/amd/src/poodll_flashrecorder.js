/* jshint ignore:start */
define(['jquery','core/log', 'filter_poodll/uploader','filter_poodll/lzflash'], function($, log, uploader, lz) {

    "use strict"; // jshint ;

    log.debug('PoodLL Flash Recorder: initialising');

    return {
		
		instanceprops: [],
		
		fetch_instance_props : function(widgetid){
			return this.instanceprops[widgetid];
		},
		
		init_instance_props: function(widgetid){
			var props = {};
			props.savebutton= null;
			props.audiodatacontrol= null;
			props.config= null;
			props.uploader = null;
			this.instanceprops[widgetid] = props
		},
		
		is_ie: function(){
			var ms_ie = false;
			var ua = window.navigator.userAgent;
			var old_ie = ua.indexOf('MSIE ');
			var new_ie = ua.indexOf('Trident/');
			var edge = ua.indexOf('Edge/');
			if ((old_ie > -1) || (new_ie > -1) || (edge > -1)) {
				ms_ie = true;
			}
			return ms_ie;
		},
    
    	// This recorder supports the current browser
        supports_current_browser: function(config) { 
        	var iOS = /iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream;

        	if (iOS){
        		return false;
        	}else{
        		if(config.mediatype!='audio'){return false;}
        		
        		log.debug('PoodLL Flash Recorder: supports this browser');
        		return true;
        	}
        },
        
        // Perform the embed of this recorder on the page
        //into the element passed in. with config
        embed: function(element, config) { 
        
			//if we are bypassing cloud tweak a few things
			if(config.flashmp3_cloudbypass==1){
				config.posturl = config.cloudbypassurl; 
				config.filename=false;
				config.s3filename =false;
				config.using_s3=false;
			}
			this.init_instance_props(config.widgetid);
			var ip = this.fetch_instance_props(config.widgetid);
			
			//if this is internet explorer, we can't use js uploader
        	//because flash wont pass filedata correctly to DOM
        	//we need to upload direct from flash
        	ip.ie = this.is_ie();
        	if(ip.ie){
        		config.flashmp3audio_widgetjson = config.flashmp3audio_widgetjson.replace('sendmethod=post','sendmethod=ajax');
        	}
			
        	//set config
        	ip.config = config;
	
		   //swf recorder
            var swfopts = $.parseJSON(config.flashmp3audio_widgetjson);                    
        	lz.embed.swf(swfopts);
     
         //savebutton
         var savebuttonstyle = '';
         if(ip.ie){savebuttonstyle = ' style="display: none" ';}
		  var savebuttonid = config.widgetid + '_savebutton'; 
          var savecontrol ='<button id="' + savebuttonid + '" type="button" class="poodll_save-recording"' + savebuttonstyle + '>' + M.util.get_string('recui_save', 'filter_poodll') + '</button>';
		  $(element).append(savecontrol);
		
		   
		   //audio control
		   var audiodatacontrolid = config.widgetid + '_adc'; 
        	var audiocontrol ='<input type="hidden" name="audiodatacontrol" id="' + audiodatacontrolid + '" value="" />';
           $(element).prepend(audiocontrol);
     
     		//init the uploader
			ip.uploader  = uploader.clone();
		    ip.uploader.init(element,config);
     
			//register events
			lz.embed[config.widgetid].setCanvasAttribute('audiodatacontrol',audiodatacontrolid);
			ip.savebutton = $('#' + savebuttonid );
			ip.audiodatacontrol = $('#' + audiodatacontrolid );
			this.registerevents(config.widgetid);
        },
        
        registerevents: function(widgetid){
			var ip = this.fetch_instance_props(widgetid);
			ip.audioblob = false;

        	ip.savebutton.click(function() {            
              //here we convert a string of base64 data into a blob which represents 
              //an mp3 file.  
              var audiodata = atob(ip.audiodatacontrol.val());
              
              //we check if there is actually any data,because if not we want to complain
			  //if this is a resubmit, impossible though, we pass through to upload
				var haveaudiodata=audiodata && audiodata.length > 0;
              if(!haveaudiodata && !ip.audioblob){
			 	ip.uploader.Output(M.util.get_string('recui_nothingtosaveerror','filter_poodll'));
			 	return false;
			  }

			  //create our audioblob if it is empty .. most likely
			  if(!ip.audioblob && haveaudiodata) {
                  var audioblobdata = [];
                  for (var i = 0; i < audiodata.length; i++) {
                      audioblobdata.push(audiodata.charCodeAt(i));
                  }
                  ip.audioblob = new Blob([new Uint8Array(audioblobdata)], {type: 'audio/mpeg3'});
              }

              //and we upload that blob
              ip.uploader.uploadBlob(ip.audioblob,'audio/mpeg3');
              //we would like to disable the recorder here
              var apicall = 'poodllapi.mp3_disable()';
              lz.embed[ip.config.widgetid].callMethod(apicall);

              //we no longer need the data in the audiodata control and it will get submitted
				//with the form which we don't want. So we clear it here. Justin 2017-03/03
                ip.audiodatacontrol.val('');


              //just in case
              return false;
            });//end of save button click
        
        }
    }//end of returned object
});//total end
