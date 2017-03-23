/* jshint ignore:start */
define(['jquery','core/log', 'filter_poodll/uploader'], function($, log, uploader) {

    "use strict"; // jshint ;_;

    log.debug('PoodLL upload Recorder: initialising');

    return {
    
    	config: null,
		
		instanceprops: [],
		
		fetch_instance_props : function(widgetid){
			return this.instanceprops[widgetid];
		},
		
		init_instance_props: function(widgetid){
			var props = {};
			props.config= null;
			props.uploader = null;
			this.instanceprops[widgetid] = props
		},
    	
		// This recorder supports the current browser
        supports_current_browser: function(config) { 
        	return true;//or false
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
			
			//get form and evenets working
        	this.insert_controls(element, config.widgetid);
      		this.register_events(config.widgetid);
        },

        insert_controls: function(element, widgetid){
         	var ip = this.fetch_instance_props(widgetid);
			
			//for now
         	var acceptmedia = '';
         	var config = ip.config;
         	switch(config.mediatype){
         		case 'video': 
         		  acceptmedia='video/*';
         		  break;
                case 'snapshot':
				case 'image':
					acceptmedia='image/*';
				case 'audio':
         		 var iOS = /iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream;
         		 if(iOS){
         		 	acceptmedia='video/*';
         		 }else{
         		 	acceptmedia='audio/*';
         		 }
				default:
                    acceptmedia='';
         	}

         //html5 recorder proper
			var controls= '<div class="p_btn_wrapper">';

			controls += '<input type="file" id="' + config.widgetid + '_poodllfileselect" name="poodllfileselect[]" accept="' +  acceptmedia + '"/>';
			controls += '<button type="button" class="p_btn">' + M.util.get_string('recui_recordorchoose', 'filter_poodll')  + '</button>'; 
			controls += '</div>';
			$(element).prepend(controls); 
		},
        
         // handle audio/video/image file uploads for Mobile
        register_events: function(widgetid) {

			var self =this;
            $('#' + widgetid + '_poodllfileselect').on('change',function(e){
                    self.FileSelectHandler(e,widgetid); }
                );

        },

        // file selection
        FileSelectHandler: function(e,widgetid) {
			var ip = this.fetch_instance_props(widgetid);
			
            // fetch FileList object
            var files = e.target.files || e.dataTransfer.files;

            // process all File objects
            for (var i = 0, file; file = files[i]; i++) {
                //this.ParseFile(f,widgetid);
                ip.uploader.uploadBlob(file,file.type);
            }
        },

        // output file information
        ParseFile: function(file,widgetid) {
			var ip = this.fetch_instance_props(widgetid);
            
			// start upload
            var filedata ="";
            var reader = new FileReader();
            reader.onloadend = function(e) {
                filedata = e.target.result;
                ip.uploader.uploadFile(filedata, file.type);
            }
            reader.readAsDataURL(file);

        }

    }//end of returned object
});//total end