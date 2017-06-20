/* jshint ignore:start */
define(['jquery','core/log','filter_poodll/utils_amd', 'filter_poodll/uploader', 'filter_poodll/poodll_uploadrecorder',], function($, log, utils, uploader, uploadrec) {

    "use strict"; // jshint ;_;

    log.debug('PoodLL Mobile Recorder: initialising');

    return {
	
		instanceprops: [],
		
		fetch_instance_props : function(widgetid){
			return this.instanceprops[widgetid];
		},
		
		init_instance_props: function(widgetid){
			var props = {};
			props.config = null;
			props.uploader = null;
			props.linkid = null;
			this.instanceprops[widgetid] = props
		},
    	
		// This recorder supports the current browser
        supports_current_browser: function(config) { 
				if(config.mediatype!='audio' && config.mediatype!='video'){return false;}
                var supports = utils.is_ios() ;
				return supports;//or false
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

            ip.linkid = 'filter_poodll_mobilerecorder_link_' + config.widgetid;
            switch(config.mediatype){
                case 'audio':
                	this.insert_upload_button(element,config.widgetid);
                	if(config.showmobile==1){
                    	this.insert_audio_button(element,config.widgetid);
                    }               
                    break;
                case 'video':
                    this.insert_upload_button(element,config.widgetid);
                    if(config.showmobile==1){
                    	this.insert_video_button(element,config.widgetid);
                    }
                    break;
            
            }
            
            this.register_events(element, config.widgetid);
            return true;//or false
        },
        
        // handle audio/video/image file uploads for Mobile
        register_events: function(element, widgetid) {
			var ip = this.fetch_instance_props(widgetid);
            var config = ip.config;
            var mobilerecorder =this;
            //launch the app from the link
            $('#' + ip.linkid).on('mousedown touchstart',function(e){
                    //to make sure both "confirm" and "openapp"
                    //happened. I had to do this
                   // e.preventDefault();
                  //  e.stopPropagation();
                    mobilerecorder.confirm_s3_arrival(widgetid);
                   // window.location=$(this).attr('href');
                    ip.uploader.Output(M.util.get_string('recui_awaitingconfirmation', 'filter_poodll'));
                    return;
                    
                    //but I wanted to just do this
                    /*
                    mobilerecorder.confirm_s3_arrival();
                    uploader.Output("awaiting confirmation");
                    return true;
                    */
                }
              );
              //launch the upload dialog (if no app or whatever)
              $('#' + ip.linkid + '_uploadafile').on('mousedown touchstart',function(e){
              		$(element).empty();
              		uploadrec.embed(element,config);
                }
            );
        },
        
        insert_video_button: function(element, widgetid){
			var ip = this.fetch_instance_props(widgetid);
			var controls = '<a class ="filter_poodll_mobilerecorderlink" id="' + ip.linkid + 
                                '" href="poodllrecorder://?presignedurl=' + encodeURIComponent(ip.config.quicktimesignedurl) + 
                                '&type=' + ip.config.mediatype + '&quality=' + ip.config.mobilequality + 
                                '&camera=' + ip.config.mobilecamera + 
                                '&timelimit='+ ip.config.timelimit + '">' + 
                                M.util.get_string('recui_openrecorderapp', 'filter_poodll') + '</a>';
			$(element).prepend(controls);        
        },
        insert_audio_button: function(element,widgetid){
			var ip = this.fetch_instance_props(widgetid);
			var controls = '<a class ="filter_poodll_mobilerecorderlink"  id="' + ip.linkid + 
                                '" href="poodllrecorder://?presignedurl=' + encodeURIComponent(ip.config.posturl) + 
                                '&type=' + ip.config.mediatype + '&quality=' + ip.config.mobilequality + 
								'&camera=' + ip.config.mobilecamera +
                                '&timelimit='+ ip.config.timelimit + '">' +
                                M.util.get_string('recui_openrecorderapp', 'filter_poodll') + '</a>';
			$(element).prepend(controls);        
        },
        
        insert_upload_button: function(element,widgetid){
			var ip = this.fetch_instance_props(widgetid);
			var controls = '<a class ="filter_poodll_uploadafilelink" id="' + ip.linkid + '_uploadafile' +
                                '" href="#">' + 
                                M.util.get_string('recui_uploadafile', 'filter_poodll') + '</a>';
			$(element).prepend(controls);        
        },
        
        confirm_s3_arrival: function(widgetid){
            var ip = this.fetch_instance_props(widgetid);
            var xhr = new XMLHttpRequest();
			var config = ip.config;

             
            var posturl = config.wwwroot + '/filter/poodll/poodllfilelib.php';
            var params = "datatype=confirmarrival";
            params += "&mediatype=" + config.mediatype;
            params += "&filename=" + config.filename;
            xhr.open("POST",posturl, true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            xhr.setRequestHeader("Cache-Control", "no-cache");
            xhr.setRequestHeader("Content-length", params.length);
            xhr.setRequestHeader("Connection", "close");
            
            xhr.addEventListener("load", function () {
                if(xhr.response && xhr.response.indexOf(config.filename)>0){
                    ip.uploader.pokeFilename(config.filename, ip.uploader);
                    ip.uploader.postprocess_s3_upload(ip.uploader);
                    ip.uploader.Output( M.util.get_string('recui_uploadsuccess', 'filter_poodll'));
                    ip.uploader.doCallback(ip.uploader,config.filename);
                }else{
                   // setTimeout(mobilerecorder.confirm_s3_arrival,2000);
                    setTimeout(function(){
                        xhr.open("POST",posturl, true);
                        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
                        xhr.setRequestHeader("Cache-Control", "no-cache");
                        xhr.setRequestHeader("Content-length", params.length);
                        xhr.setRequestHeader("Connection", "close");
                        xhr.send(params);
                    },2000);
                }
            });
            
            xhr.send(params);
        }
    }//end of returned object
});//total end
