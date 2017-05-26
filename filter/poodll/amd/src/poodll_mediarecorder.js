/* jshint ignore:start */
define(['jquery','core/log','filter_poodll/utils_amd',  'filter_poodll/MediaStreamRecorder',
        'filter_poodll/adapter', 'filter_poodll/uploader','filter_poodll/timer',
        'filter_poodll/poodll_basemediaskin',
        'filter_poodll/poodll_burntrosemediaskin'], function($, log, utils, msr, adapter, uploader,timer,baseskin,burntroseskin) {

    "use strict"; // jshint ;_;

    log.debug('PoodLL Media Recorder: initialising');

    return {
    
		instanceprops: [],
        skins: [],
		
		fetch_instanceprops : function(controlbarid){
			return this.instanceprops[controlbarid];
		},

        fetch_skin : function(controlbarid){
            return this.skins[controlbarid];
        },


    	// This recorder supports the current browser
        supports_current_browser: function(config) {
			
			if(config.mediatype!='audio' && config.mediatype!='video'){return false;}
			var protocol_ok = M.cfg.wwwroot.indexOf('https:')==0 ||
                M.cfg.wwwroot.indexOf('http://localhost')==0 ;
        	if(protocol_ok
        	 	&& navigator && navigator.mediaDevices 
        	 	&& navigator.mediaDevices.getUserMedia){
        	 	    var ret = false;
        	 	    switch(config.mediatype){
        	 	        case 'audio': 
        	 	            ret = true;
        	 	             break;
        	 	        case 'video': 
        	 	                var IsEdge = navigator.userAgent.indexOf('Edge') !== -1 &&
        	 	                    (!!navigator.msSaveBlob || !!navigator.msSaveOrOpenBlob);
        	 	               if(!IsEdge){ret=true;}
        	 	    }
        	 	    if(ret){
        	 	        log.debug('PoodLL Media Recorder: supports this browser');
        	 	    }
        		  return ret;
        	}else{
        		  return false;
        	}
        }, 
        
        // Perform the embed of this recorder on the page
        //into the element passed in. with config
        embed: function(element, config) {
            var that = this;
			var controlbarid = "filter_poodll_controlbar_" + config.widgetid;
			this.init_instance_props(controlbarid);
			var ip = this.fetch_instanceprops(controlbarid);
			ip.config = config;
			ip.timeinterval = config.media_timeinterval;
			ip.audiomimetype = config.media_audiomimetype;
			ip.videorecordertype = config.media_videorecordertype;
			ip.videocaptureheight = config.media_videocaptureheight;

			//init our skin
            var theskin = this.init_skin(controlbarid, ip.config.media_skin, ip);

            //add callbacks for uploadsuccess and upload failure
            ip.config.onuploadsuccess = function(widgetid){that.onUploadSuccess(widgetid,theskin)};
            ip.config.onuploadfailure = function(widgetid){that.onUploadFailure(widgetid,theskin)};
            
			switch(config.mediatype){
                case 'audio':
                    var preview = theskin.fetch_preview_audio(config.media_skin);
                    ip.controlbar = this.fetch_controlbar_audio(element,controlbarid, preview);
					ip.uploader = uploader.clone();
                    ip.uploader.init(element,config);
                    this.register_events_audio(controlbarid);
                    break;
                case 'video':
                    var preview = theskin.fetch_preview_video(config.media_skin);
                    ip.controlbar = this.fetch_controlbar_video(element,controlbarid,preview);
					ip.uploader = uploader.clone();
                    ip.uploader.init(element,config);
                    this.register_events_video(controlbarid);
                    break;
                   
            }
			//init timer
            ip.timer = timer.clone();
			ip.timer.init(0,function(){
					ip.controlbar.status.html(ip.timer.fetch_display_time());
					}
				);
        },
		
		init_instance_props: function(controlbarid){
			this.instanceprops[controlbarid] = {};
			this.instanceprops[controlbarid].recorded_index= 0;
			this.instanceprops[controlbarid].mediaRecorder= null;
			this.instanceprops[controlbarid].blobs= [];
			this.instanceprops[controlbarid].timeinterval= 5000;
			this.instanceprops[controlbarid].audiomimetype= 'audio/webm';
			this.instanceprops[controlbarid].videorecordertype= 'auto';//mediarec or webp
			this.instanceprops[controlbarid].videocapturewidth= 320;
			this.instanceprops[controlbarid].videocaptureheight= 240;
			this.instanceprops[controlbarid].controlbar= '';
			this.instanceprops[controlbarid].previewvolume= 1;
			this.instanceprops[controlbarid].timer= {};
			this.instanceprops[controlbarid].uploader= {};
			this.instanceprops[controlbarid].uploaded= false;
		},

        init_skin: function (controlbarid,skinname,instanceprops){
            switch (skinname) {
                case 'burntrose':
                    this.skins[controlbarid] = burntroseskin.clone();
                    break;
                case 'plain':
                case 'standard':
                default:
                    this.skins[controlbarid] = baseskin.clone();
                    break;

            }
            this.skins[controlbarid].init(instanceprops, this);
            return this.skins[controlbarid];
        },
		
        onUploadSuccess: function(widgetid,theskin){
        	 log.debug('from poodllmediarecorder: uploadsuccess');		
        	 var controlbarid = 'filter_poodll_controlbar_' + widgetid;
             theskin.onUploadSuccess(controlbarid);
        },
        
        onUploadFailure: function(widgetid,theskin){
        	log.debug('from poodllmediarecorder: uploadfailure');
            var controlbarid = 'filter_poodll_controlbar_' + widgetid;
            theskin.onUploadFailure(controlbarid);
        },		
		

        onMediaError: function(e) {
                console.error('media error', e);
        },
        
        captureUserMedia: function(mediaConstraints, successCallback, errorCallback) {
                navigator.mediaDevices.getUserMedia(mediaConstraints).then(successCallback).catch(errorCallback);
        },

        do_start_audio: function(ip,mediaConstraints, onMediaSuccess){
            ip.blobs=[];
            this.captureUserMedia(mediaConstraints, onMediaSuccess, this.onMediaError);
        },
        do_start_video: function(ip, onMediaSuccess){

        },

        do_play_audio: function(ip,preview){
            if(ip.blobs && ip.blobs.length > 0) {
                log.debug('playing type:' + ip.blobs[0].type);
                switch (ip.blobs[0].type) {
                    case 'audio/wav':
                        //log.debug('concat wavs');
                        //mediastreamrecorder adds a header to each wav blob,
                        //we remove them and combine audodata and new header
                        utils.concatenateWavBlobs(ip.blobs, function (concatenatedBlob) {
                            var mediaurl = URL.createObjectURL(concatenatedBlob);
                            preview.src = mediaurl;
                            preview.controls = true;
                            preview.volume = ip.previewvolume;
                            preview.play();
                        });
                        break;
                    case 'video/webm':
                    case 'audio/ogg':
                    case 'audio/webm':
                    default:
                        var concatenatedBlob = utils.simpleConcatenateBlobs(ip.blobs, ip.blobs[0].type);
                        var mediaurl = URL.createObjectURL(concatenatedBlob);
                        preview.src = mediaurl;
                        preview.controls = true;
                        preview.volume = ip.previewvolume;
                        preview.play();
                        break;

                    case 'olddefault':
                        utils.concatenateBlobs(ip.blobs, ip.blobs[0].type, function (concatenatedBlob) {
                            var mediaurl = URL.createObjectURL(concatenatedBlob);
                            preview.src = mediaurl;
                            preview.controls = true;
                            preview.volume = ip.previewvolume;
                            preview.play();
                        }); //end of concatenate blobs
                }//end of switch
            }//end of if blobs
        },
        do_play_video: function(ip){

        },
        do_save_audio: function(ip){
            //We do want to allow multiple submissions off one page load BUT
            //this will require a new filename. The filename is the basis of the
            //s3filename, s3uploadurl and filename for moodle. The problem with
            //allowing mulitple uploads is that once the placeholder is overwritten
            //the subsequent submissions ad_hoc move task can no longer find the file to
            //replace. So we need a whole new filename or to cancel the previous ad hoc move.
            //This should probably be
            //an ajax request from the uploader, or even a set of 10 filenames/s3uploadurls
            //pulled down at PHP time ..
            //this is one of those cases where a simple thing is hard ...J 20160919
            if(ip.blobs && ip.blobs.length > 0){
                switch(ip.blobs[0].type){
                    case 'audio/wav':
                        //mediastreamrecorder adds a header to each wav blob,
                        //we remove them and combine audodata and new header
                        utils.concatenateWavBlobs(ip.blobs,  function(concatenatedBlob) {
                            ip.uploader.uploadBlob(concatenatedBlob,ip.blobs[0].type);
                        });
                        break;
                    case 'audio/ogg':
                    case 'audio/webm':
                    case 'video/webm':
                    default:
                        var concatenatedBlob = utils.simpleConcatenateBlobs(ip.blobs, ip.blobs[0].type);
                        ip.uploader.uploadBlob(concatenatedBlob,ip.blobs[0].type);
                        break;
                    case 'old default':
                        utils.concatenateBlobs(ip.blobs, ip.blobs[0].type, function(concatenatedBlob) {
                            ip.uploader.uploadBlob(concatenatedBlob,ip.blobs[0].type);
                        }); //end of concatenate blobs
                }//end of switch case
                ip.uploaded = true;
                ip.controlbar.startbutton.attr('disabled',true);
            }//end of if self.blobs
        },
        do_save_video: function(ip){

        },
        do_stop_audio: function(ip){
            ip.mediaRecorder.stop();
        },
        do_stop_video: function(ip){

        },
        do_pause_audio: function(ip){
            ip.mediaRecorder.resume();
            ip.mediaRecorder.pause();
        },
        do_pause_video: function(ip){

        },
        do_resume_audio: function(ip){
            ip.mediaRecorder.resume();
        },
        do_resume_video: function(ip){

        },


        
        register_events_audio: function(controlbarid){
        	
			var self = this;
			var ip = this.fetch_instanceprops(controlbarid);
			var skin = this.skins[controlbarid];

            var mediaConstraints = {
                audio: true
            };       
            
            var onMediaSuccess =function(stream) {

        	log.debug('onmediasuccess');

                // get blob after specific time interval
                ip.mediaRecorder= new MediaStreamRecorder(stream);
               // self.controlbar.preview.attr('src',URL.createObjectURL(stream));
                ip.mediaRecorder.mimeType = ip.audiomimetype;
                ip.mediaRecorder.audioChannels = 1;
                //ip.mediaRecorder.recorderType = StereoAudioRecorder;
                ip.mediaRecorder.start(ip.timeinterval);
                log.debug(ip.timeinterval);
                ip.mediaRecorder.ondataavailable =  function(blob) {
                    log.debug('we got blob');
        			ip.blobs.push(blob);
        			};

                skin.onMediaSuccess_audio(controlbarid);
            };
            
            skin.register_controlbar_events_audio(onMediaSuccess, mediaConstraints, controlbarid);
          
        },//end of register audio events
        
        
        register_events_video: function(controlbarid){
		
			var self = this;
			var ip = this.fetch_instanceprops(controlbarid);
            var skin = this.skins[controlbarid];
        	
            var mediaConstraints = {
                audio: !IsOpera && !IsEdge,
                video: true
            };

            var onMediaSuccess =function(stream) {

                //create recorder
                ip.mediaRecorder = new MediaStreamRecorder(stream);
                //create preview
                // self.controlbar.preview.attr('src',stream.url);
               // debugger;

                var preview = ip.controlbar.preview[0];
                preview.srcObject = stream;
                //preview.src=window.URL.createObjectURL(stream);
                preview.controls=false;
                preview.volume = 0;
                preview.play();


              
                //set recorder type
                if (ip.videorecordertype === 'mediarec') {
                    ip.mediaRecorder.recorderType = MediaRecorderWrapper;
                }
                if (ip.videorecordertype === 'webp') {
                    ip.mediaRecorder.recorderType = WhammyRecorder;
                }
                
                //set capture size
                ip.mediaRecorder.videoWidth = ip.videocapturewidth;
                ip.mediaRecorder.videoHeight = ip.videocaptureheight;
                
                //staert recording
                ip.mediaRecorder.start(ip.timeinterval);
                ip.mediaRecorder.ondataavailable =  function(blob) {
                    ip.blobs.push(blob);
            		log.debug('We got a blobby');
            		//log.debug(URL.createObjectURL(blob));
        		};

                skin.onMediaSuccess_video(controlbarid);
            };
            
             skin.register_controlbar_events_video(onMediaSuccess, mediaConstraints,controlbarid);
        },//end of register video events
	   
	   update_status: function(controlbarid){
			var ip = this.fetch_instanceprops(controlbarid);
		    ip.controlbar.status.html(ip.timer.fetch_display_time());
		},


        
        fetch_controlbar_audio: function(element, controlbarid, preview){
        	var ip = this.fetch_instanceprops(controlbarid);
        	var skin= this.fetch_skin(controlbarid);
        	var controlbar = skin.insert_controlbar_audio(element, controlbarid, preview);
         	return controlbar;
        },
        
        fetch_controlbar_video: function(element,controlbarid,preview){
        	var ip = this.fetch_instanceprops(controlbarid);
            var skin= this.fetch_skin(controlbarid);
            var controlbar = skin.insert_controlbar_video(element, controlbarid, preview);
        	return controlbar;
        }
    };//end of returned object
});//total end
