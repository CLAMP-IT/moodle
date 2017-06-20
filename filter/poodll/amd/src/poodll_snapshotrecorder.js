/* jshint ignore:start */
define(['jquery','core/log', 'filter_poodll/utils_amd', 'filter_poodll/uploader','filter_poodll/webcam'], function($, log, utils, uploader, Webcam) {

    "use strict"; // jshint ;_;

    log.debug('PoodLL Snapshot Recorder: initialising');

    return {
		
		instanceprops: [],
		
		fetch_instance_props : function(widgetid){
			return this.instanceprops[widgetid];
		},
		
		init_instance_props: function(widgetid){
			var props = {};
			props.imagefile = false;
			props.htmlthings = '';
			props.config = null;
			props.uploader = null;
			this.instanceprops[widgetid] = props
		},
    	
		// This recorder supports the current browser
        supports_current_browser: function(config) { 
        	var iOS = utils.is_ios();
        	if (iOS){
        		return false;
        	}else{
        		if(config.mediatype!='snapshot'){return false;}
        		
        		log.debug('PoodLL Snapshot Recorder: supports this browser');
        		return true;
        	}
        },
        
        // Perform the embed of this recorder on the page
        //into the element passed in. with config
        embed: function(element, config) { 
			this.init_instance_props(config.widgetid);
			var ip = this.fetch_instance_props(config.widgetid);
        	//set config
        	ip.config = config;
			
			//set uploader
			ip.uploader = uploader.clone();
			ip.uploader.init(element,config);
			
        	this.insert_controls(element, config.widgetid);
      		this.register_events(config.widgetid);
        },

        insert_controls: function(element, widgetid){
			var ip = this.fetch_instance_props(widgetid);
		
         	//for now.
         	var acceptmedia = '';
         	var config = ip.config;

			
			 var htmlthings ={
            	camera: config.widgetid + '_poodll_snapshot_camera',
            	preview: config.widgetid + '_poodll_snapshot_preview',
            	snapbutton: config.widgetid + '_poodll_take-snapshot',
            	savebutton: config.widgetid + '_poodll_save-snapshot',
            	cancelbutton: config.widgetid + '_poodll_cancel-snapshot'
            };
            ip.htmlthings =htmlthings;
			
			
         	//html5 snapshot maker proper
			//camera
			var controls= '<div id="' + htmlthings.camera + '" style="width:320px; height:240px;"></div>';
			//preview
			controls += '<div id="' + htmlthings.preview + '" class="hide" style="width:320px; height:240px;"></div>';
			//snap button
			controls += '<button type="button" class="poodll_take-snapshot" id="' + htmlthings.snapbutton + '">' +
				 M.util.get_string('recui_takesnapshot', 'filter_poodll') + 
				 '</button>';
			//cancel button
			controls += '<button type="button" class="poodll_cancel-snapshot" id="' + htmlthings.cancelbutton + '">' +
				 M.util.get_string('recui_cancelsnapshot', 'filter_poodll') + 
				 '</button>';
			//save button
			controls += '<button type="button" class="poodll_save-recording" id="'+ htmlthings.savebutton + '">' + 
				M.util.get_string('recui_save', 'filter_poodll') + 
				'</button>';
			$(element).prepend(controls); 
		},
        
         // handle image file uploads for Mobile
        register_events: function(widgetid) {

			var ip = this.fetch_instance_props(widgetid);
            var config = ip.config;

			Webcam.set('swfURL',M.cfg.wwwroot + '/filter/poodll/3rdparty/webcam/webcam.swf');
            Webcam.attach('#' + ip.htmlthings.camera);
            
            $('#' + ip.htmlthings.savebutton).on('click',function(e){
                if(ip.imagefile){
					var mimetype = 'image/jpeg';
					var imageblob = ip.uploader.dataURItoBlob(ip.imagefile,mimetype);
                	ip.uploader.uploadFile(imageblob,mimetype);
                }else{
                    ip.uploader.Output(M.util.get_string('recui_nothingtosaveerror','filter_poodll'));
                }//end of if ip.imagefile		
            });
            
            $('#'  + ip.htmlthings.cancelbutton).on('click',function(e){
            		ip.imagefile = false;
            		$('#' + ip.htmlthings.preview).addClass('hide').html('');
            		$('#' + ip.htmlthings.camera).removeClass('hide');
            });
            
            $('#'  + ip.htmlthings.snapbutton).on('click',function(e){
            	Webcam.snap( function(data_uri) {
            		ip.imagefile = data_uri;
                	$('#' + ip.htmlthings.preview).html('<img src="'+data_uri+'"/>').removeClass('hide');
                	$('#' + ip.htmlthings.camera).addClass('hide');
            	} );
            });

        }
    }//end of returned object
});//total end
