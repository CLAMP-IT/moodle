/* jshint ignore:start */
define(['jquery','core/log','filter_poodll/utils_amd',  'filter_poodll/MediaStreamRecorder', 'filter_poodll/gumadapter', 'filter_poodll/uploader','filter_poodll/timer'], function($, log, utils, msr, gum, uploader,timer) {

    "use strict"; // jshint ;_;

    log.debug('PoodLL Media Recorder: initialising');

    return {
    
		instanceprops: [],
		
		fetch_instanceprops : function(controlbarid){
			return this.instanceprops[controlbarid];
		},
    	
    	// This recorder supports the current browser
        supports_current_browser: function(config) {
			
			if(config.mediatype!='audio' && config.mediatype!='video'){return false;}
			 	 
        	if(M.cfg.wwwroot.indexOf('https:')==0
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
			var controlbarid = "filter_poodll_controlbar_" + config.widgetid;
			this.init_instance_props(controlbarid);
			var ip = this.fetch_instanceprops(controlbarid);
			
			ip.config = config;
			ip.timeinterval = config.media_timeinterval;
			ip.audiomimetype = config.media_audiomimetype;
			ip.videorecordertype = config.media_videorecordertype;
			ip.videocaptureheight = config.media_videocaptureheight; 
			
			//add callbacks for uploadsuccess and upload failure
            ip.config.onuploadsuccess = this.on_upload_success;
            ip.config.onuploadfailure = this.on_upload_failure;
            
			switch(config.mediatype){
                case 'audio':
                    var preview = this.fetch_audio_preview(config.media_skin);
                    ip.controlbar = this.insert_fetch_control_bar(element,controlbarid, preview);
					ip.uploader = uploader.clone();
                    ip.uploader.init(element,config);
                    this.register_audio_events(controlbarid);
                    break;
                case 'video':
                    var preview = this.fetch_video_preview(config.media_skin);
                    ip.controlbar = this.insert_fetch_control_bar_video(element,controlbarid,preview);
					ip.uploader = uploader.clone();
                    ip.uploader.init(element,config);
                    this.register_video_events(controlbarid);
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
		
        on_upload_success: function(widgetid){
        	 log.debug('from poodllmediarecorder: uploadsuccess');		
        	 var controlbarid = 'filter_poodll_controlbar_' + widgetid;
			 $('#' + controlbarid + ' > .poodll_save-recording').hide();
            // $('#' + controlbarid  + '_messages').hide();
             $('#' + controlbarid + ' > .poodll_savedsuccessfully').show();
        },
        
        on_upload_failure: function(controlbarid){
        	log.debug('from poodllmediarecorder: uploadfailure');
        },		
		
		fetch_status_bar: function(skin){
			var status = '<div class="poodll_status_' + skin + '" width="320" height="50">00:00:00</div>';
            return status;
        },
        
		fetch_audio_preview: function(skin){
			var preview = '<audio class="poodll_preview_' + skin + ' hide" controls></audio>';
            return preview;
        },
        fetch_video_preview: function(skin){
            var preview ='<video class="poodll_preview_' + skin + '" width="320" height="240"></video>';
            return preview;
        },
        
        onMediaError: function(e) {
                console.error('media error', e);
        },
        
        captureUserMedia: function(mediaConstraints, successCallback, errorCallback) {
                navigator.mediaDevices.getUserMedia(mediaConstraints).then(successCallback).catch(errorCallback);
        },
        
        bytesToSize: function(bytes) {
                var k = 1000;
                var sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
                if (bytes === 0) return '0 Bytes';
                var i = parseInt(Math.floor(Math.log(bytes) / Math.log(k)), 10);
                return (bytes / Math.pow(k, i)).toPrecision(3) + ' ' + sizes[i];
        },
        
         // below function via: http://goo.gl/6QNDcI
        getTimeLength: function(milliseconds) {
                var data = new Date(milliseconds);
                return data.getUTCHours() + " hours, " + data.getUTCMinutes() + " minutes and " + data.getUTCSeconds() + " second(s)";
        },
        
          register_audio_events: function(controlbarid){
        	
			var self = this;
			var ip = this.fetch_instanceprops(controlbarid);
			
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
                ip.mediaRecorder.start(ip.timeInterval);
                ip.mediaRecorder.ondataavailable =  function(blob) {
        			ip.blobs.push(blob);
        			};
                ip.controlbar.preview.attr('src',null);               
                ip.controlbar.stopbutton.attr('disabled',false);
                ip.controlbar.pausebutton.attr('disabled',false);
                ip.controlbar.savebutton.attr('disabled',false);
            };
            
            this.register_controlbar_events(onMediaSuccess, mediaConstraints, controlbarid);
          
        },//end of register audio events
        
        
        register_video_events: function(controlbarid){
		
			var self = this;
			var ip = this.fetch_instanceprops(controlbarid);
        	
            var mediaConstraints = {
                audio: !IsOpera && !IsEdge,
                video: true
            };

            var onMediaSuccess =function(stream) {

                //create recorder
                ip.mediaRecorder= new MediaStreamRecorder(stream);
                //create preview
               // self.controlbar.preview.attr('src',stream.url);
                ip.controlbar.preview.attr('src',window.URL.createObjectURL(stream));
                ip.controlbar.preview.attr('controls',false);
                ip.controlbar.preview.get(0).volume=0;
                ip.controlbar.preview.get(0).play();
              
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
                ip.mediaRecorder.start(self.timeInterval);
                ip.mediaRecorder.ondataavailable =  function(blob) {
                    ip.blobs.push(blob);
            		//log.debug('We got a blobby');
            		//log.debug(URL.createObjectURL(blob));
        		};
                
                ip.controlbar.stopbutton.attr('disabled',false);
                ip.controlbar.pausebutton.attr('disabled',false);
                ip.controlbar.savebutton.attr('disabled',false);
              
            };
            
             this.register_controlbar_events(onMediaSuccess, mediaConstraints,controlbarid);
        },//end of register video events
	   
	   update_status: function(controlbarid){
			var ip = this.fetch_instanceprops(controlbarid);
		    ip.controlbar.status.html(ip.timer.fetch_display_time());
		},
		
		//
		//branching functions for skins
        //
       set_visual_mode: function(mode, controlbarid){
        	var ip = this.fetch_instanceprops(controlbarid);
        	switch(ip.config.media_skin){
        		case 'standard': this.set_visual_mode_standard(mode, controlbarid);
        			break;
        		case 'burntrose':  this.set_visual_mode_burntrose(mode, controlbarid);
        	}
        },
		
		
		
       register_controlbar_events: function(onMediaSuccess, mediaConstraints, controlbarid){
        	var ip = this.fetch_instanceprops(controlbarid);
        	switch(ip.config.media_skin){
        		case 'standard': this.register_controlbar_events_standard(onMediaSuccess, mediaConstraints, controlbarid);
        			break;
        		case 'burntrose': this.register_controlbar_events_burntrose(onMediaSuccess, mediaConstraints, controlbarid);
        	}
        },

        
         insert_fetch_control_bar: function(element,controlbarid, preview){
        	var ip = this.fetch_instanceprops(controlbarid);
        	var controlbar = '';
        	switch(ip.config.media_skin){
        		case 'standard':  controlbar = this.insert_fetch_control_bar_standard(element,controlbarid, preview);
        			break;
        		case 'burntrose': controlbar = this.insert_fetch_control_bar_burntrose(element,controlbarid, preview);
        	}
        	return controlbar;
        },
        
        insert_fetch_control_bar_video: function(element,controlbarid,preview){
        	var ip = this.fetch_instanceprops(controlbarid);
        	var controlbar = '';
        	switch(ip.config.media_skin){
        		case 'standard':  controlbar = this.insert_fetch_control_bar_standard(element,controlbarid, preview);
        			break;
        		case 'burntrose': controlbar = this.insert_fetch_control_bar_video_burntrose(element,controlbarid, preview);
        	}
        	return controlbar;
        
        },
        
        //
        // STANDARD SKIN
        //
        //
		
		//set visuals for different states (ie recording or playing)
        set_visual_mode_standard: function(mode, controlbarid){
			var self = this;
			var ip = this.fetch_instanceprops(controlbarid);
	   
		   switch(mode){
			   
			   case 'recordmode':
					ip.controlbar.preview.addClass('poodll_recording');
					ip.controlbar.status.addClass('poodll_recording');
					if(ip.config.mediatype=='audio'){
						ip.controlbar.preview.addClass('hide');
					}	
					ip.controlbar.status.removeClass('hide');		
					break;
				
			   case 'previewmode':
					ip.controlbar.preview.removeClass('poodll_recording');
					ip.controlbar.status.removeClass('poodll_recording');
					if(ip.config.mediatype=='audio'){
						ip.controlbar.preview.removeClass('hide');
					}
					ip.controlbar.status.addClass('hide');
					break;
			   
			   case 'pausedmode':
					ip.controlbar.preview.removeClass('poodll_recording');
					ip.controlbar.status.removeClass('poodll_recording');
					break;
		   }
		   
	   },
		
		
		
        //insert the control bar and return it to be reused
        insert_fetch_control_bar_standard: function(element,controlbarid, preview){
            	var controls ='<div class="poodll_mediarecorderbox_standard" id="' + controlbarid + '">' ;
            	var status = this.fetch_status_bar('standard');
                controls += status,
                controls += preview,
                controls +=  '<button type="button" class="poodll_mediarecorder_button_standard poodll_start-recording_standard">' + M.util.get_string('recui_record', 'filter_poodll') + '</button>';
                controls += '<button type="button" class="poodll_mediarecorder_button_standard poodll_stop-recording_standard" disabled>' + M.util.get_string('recui_stop', 'filter_poodll') + '</button>';
                controls += '<button type="button" class="poodll_mediarecorder_button_standard poodll_pause-recording_standard" disabled>' + M.util.get_string('recui_pause', 'filter_poodll') + '</button>';
                controls += ' <button type="button" class="poodll_mediarecorder_button_standard poodll_resume-recording_standard hide" disabled>' + M.util.get_string('recui_continue', 'filter_poodll') + '</button>';
                controls += ' <button type="button" class="poodll_mediarecorder_button_standard poodll_play-recording_standard" disabled>' + M.util.get_string('recui_play', 'filter_poodll') + '</button>';
                controls += '<button type="button" class="poodll_save-recording_standard" disabled>' + M.util.get_string('recui_save', 'filter_poodll') + '</button>';
                controls += '</div>';
                $(element).prepend(controls);
                var controlbar ={
					status: $('#' + controlbarid + ' > .poodll_status_standard'),
                    preview: $('#' + controlbarid + ' > .poodll_preview_standard'),
                    startbutton: $('#' + controlbarid + ' > .poodll_start-recording_standard'),
                    stopbutton: $('#' + controlbarid + ' > .poodll_stop-recording_standard'),
                    pausebutton: $('#' + controlbarid + ' > .poodll_pause-recording_standard'),
                    resumebutton: $('#' + controlbarid + ' > .poodll_resume-recording_standard'),
                    playbutton: $('#' + controlbarid + ' > .poodll_play-recording_standard'),
                    savebutton: $('#' + controlbarid + ' > .poodll_save-recording_standard')    
                };
                return controlbar;
        }, //end of fetch_control_bar_standard
        
        register_controlbar_events_standard: function(onMediaSuccess, mediaConstraints, controlbarid){
            var self = this;
			var ip = this.fetch_instanceprops(controlbarid);

            ip.controlbar.startbutton.click(function() {
            	//clear messages
            	$('#' + ip.config.widgetid  + '_messages').text('');
            	
                this.disabled = true;
                ip.blobs=[]; 
                self.captureUserMedia(mediaConstraints, onMediaSuccess, self.onMediaError);          
                ip.controlbar.playbutton.attr('disabled',true);
                ip.controlbar.resumebutton.hide();
                ip.controlbar.pausebutton.show();
                ip.controlbar.pausebutton.attr('disabled',false);
                self.set_visual_mode('recordmode',controlbarid);
                
                //timer and status bar
                ip.timer.reset();
                ip.timer.start();
                self.update_status(controlbarid);
            });
            
            ip.controlbar.stopbutton.click(function() {
                this.disabled = true;
                ip.mediaRecorder.stop();
                //this throws an error, do we worry?
                //self.mediaRecorder.stream.stop();
                
                 var preview = ip.controlbar.preview;
                if(preview && preview.get(0)){
                    preview.get(0).pause();
                }
                
               //turn border black etc
               self.set_visual_mode('previewmode',controlbarid);
               //timer and status bar
               ip.timer.stop()
               self.update_status(controlbarid);
                
               ip.controlbar.playbutton.attr('disabled',false);
               ip.controlbar.pausebutton.attr('disabled',true);
              if(!ip.uploaded){
               	ip.controlbar.startbutton.attr('disabled',false);
              } 
               ip.controlbar.resumebutton.hide();
               ip.controlbar.pausebutton.show();
            });
          
            ip.controlbar.pausebutton.click(function() {
                this.disabled = true;
                $(this).hide();
                ip.controlbar.resumebutton.show();
                ip.mediaRecorder.resume();
                ip.mediaRecorder.pause();
                ip.controlbar.resumebutton.attr('disabled',false) ;
                self.set_visual_mode('pausedmode',controlbarid);
                
                //timer and status bar
                ip.timer.pause();
                self.update_status(controlbarid);
            });
            
            ip.controlbar.resumebutton.click(function() {
                this.disabled = true;
                $(this).hide();
                ip.controlbar.pausebutton.show();
                ip.mediaRecorder.resume();
                ip.controlbar.pausebutton.attr('disabled',false);
                self.set_visual_mode('recordmode',controlbarid);
                
                //timer and status bar
                ip.timer.resume();
                self.update_status(controlbarid);
            });
            
            ip.controlbar.playbutton.click(function() {
                this.disabled = true;
                var preview = ip.controlbar.preview.get(0);
                if(ip.blobs && ip.blobs.length > 0){
                    log.debug(ip.blobs[0].type);
                    if(ip.blobs[0].type=='audio/wav'){
                        //mediastreamrecorder adds a header to each wav blob, 
                        //we remove them and combine audodata and new header
                        utils.concatenateWavBlobs(ip.blobs,  function(concatenatedBlob) {
                                 var mediaurl = URL.createObjectURL(concatenatedBlob);
                                 preview.src= mediaurl;
                                 preview.controls =true;
                                 preview.volume = ip.previewvolume;
                                 preview.play();
                        });
                    }else{
                        ConcatenateBlobs(ip.blobs, ip.blobs[0].type, function(concatenatedBlob) {
                                 var mediaurl = URL.createObjectURL(concatenatedBlob);
                                 preview.src= mediaurl;
                                 preview.controls =true;
                                 preview.volume = ip.previewvolume;
                                 preview.play();
                        }); //end of concatenate blobs
                    }
                }        
                ip.controlbar.stopbutton.attr('disabled',false);
                ip.controlbar.startbutton.attr('disabled',true);
            });
            
           ip.controlbar.savebutton.click(function() {
                this.disabled = true;
                 
                //I know you want to allow multiple submissions off one page load BUT
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
                   if(ip.blobs[0].type=='audio/wav'){
                        //mediastreamrecorder adds a header to each wav blob, 
                        //we remove them and combine audodata and new header
                        utils.concatenateWavBlobs(ip.blobs,  function(concatenatedBlob) {
                                ip.uploader.uploadBlob(concatenatedBlob,ip.blobs[0].type);
                                ip.controlbar.startbutton.attr('disabled',true);
                                ip.uploaded = true;
                        });
                   }else{
                        ConcatenateBlobs(ip.blobs, ip.blobs[0].type, function(concatenatedBlob) {
                                ip.uploader.uploadBlob(concatenatedBlob,ip.blobs[0].type);
                                ip.controlbar.startbutton.attr('disabled',true);
                                ip.uploaded = true;
                        }); //end of concatenate blobs
                   }//end of if audio/wav
                }else{
                    ip.uploader.Output(M.util.get_string('recui_nothingtosaveerror','filter_poodll'));
                }//end of if self.blobs		
            	//probably not necessary  ... but getting odd ajax errors occasionally
            	return false;
            });//end of save recording
            
            window.onbeforeunload = function() {
                ip.controlbar.startbutton.attr('disabled',false);
                var preview = ip.controlbar.preview;
                if(preview && preview.get(0)){
                    preview.get(0).pause();
                }
            };
        },//end of register_control_bar_events_standard
        
        //
        // BURNTROSE SKIN
        //
        //
		
		//set visuals for different states (ie recording or playing)
        set_visual_mode_burntrose: function(mode, controlbarid){
			var self = this;
			var ip = this.fetch_instanceprops(controlbarid);
	   
		   switch(mode){
			   
			   case 'recordmode':
					ip.controlbar.preview.addClass('poodll_recording');
					ip.controlbar.status.addClass('poodll_recording');
					if(ip.config.mediatype=='audio'){
						ip.controlbar.preview.addClass('hide');
					}	
					ip.controlbar.status.removeClass('hide');		
					break;
				
			   case 'previewmode':
					ip.controlbar.preview.removeClass('poodll_recording');
					ip.controlbar.status.removeClass('poodll_recording');
					break;
			   
			   case 'pausedmode':
					ip.controlbar.preview.removeClass('poodll_recording');
					ip.controlbar.status.removeClass('poodll_recording');
					break;
		   }
		   
	   },
		
        
        //insert the control bar and return it to be reused
        insert_fetch_control_bar_burntrose: function(element,controlbarid, preview){
            	var controls ='<div class="poodll_mediarecorderbox" id="' + controlbarid + '">' ;
            	var status = this.fetch_status_bar('burntrose');
                controls += status,
                controls += preview,
                controls +=  '<span class="poodll_start-recording" title="' + M.util.get_string('recui_record', 'filter_poodll') + '"></span>';
                controls += '<span class="poodll_stop-recording hide" title="' + M.util.get_string('recui_stop', 'filter_poodll') + '></span>';
                controls += '<span class="poodll_pause-recording hide" title="' + M.util.get_string('recui_pause', 'filter_poodll') + '"></span>';
                controls += ' <span class="poodll_resume-recording hide"title="' + M.util.get_string('recui_continue', 'filter_poodll') + '" ></span>';
                controls += ' <span class="poodll_play-recording" ></span>';
				controls += ' <span class="poodll_playsave hide" title="' + M.util.get_string('recui_play', 'filter_poodll') + '" ></span>';
				controls += ' <span class="poodll_mic" ></span>';
				controls += ' <span class="poodll_recmic hide" ></span>';
				controls += ' <span class="poodll_resume_mic hide" ></span>';
                controls += '<span class="poodll_savebtn" ></span>';
				controls += '<span class="poodll_save-recording_burntrose hide" title="' + M.util.get_string('recui_save', 'filter_poodll') + '" ></span>';
				controls += '<span class="poodll_savedsuccessfully hide"></span>';
                controls += '</div>';
                $(element).prepend(controls);
                var controlbar ={
					status: $('#' + controlbarid + ' > .poodll_status_burntrose'),
                    preview: $('#' + controlbarid + ' > .poodll_preview_burntrose'),
                    startbutton: $('#' + controlbarid + ' > .poodll_start-recording'),
                    stopbutton: $('#' + controlbarid + ' > .poodll_stop-recording'),
                    pausebutton: $('#' + controlbarid + ' > .poodll_pause-recording'),
                    resumebutton: $('#' + controlbarid + ' > .poodll_resume-recording'),
                    play1: $('#' + controlbarid + ' > .poodll_play-recording'),
					playbutton: $('#' + controlbarid + ' > .poodll_playsave'),
                    save1: $('#' + controlbarid + ' > .poodll_savebtn'),
					savebutton: $('#' + controlbarid + ' > .poodll_save-recording_burntrose'),
					savesuccess: $('#' + controlbarid + ' > .poodll_savedsuccessfully'),
					
					playermic: $('#'+controlbarid + '> .poodll_mic'),
					recordmic: $('#'+controlbarid + '> .poodll_recmic'),
					resumemic: $('#'+controlbarid + '> .poodll_resume_mic')
                };
                return controlbar;
        }, //end of fetch_control_bar_burntrose,
        
        //insert the control bar and return it to be reused
        insert_fetch_control_bar_video_burntrose: function(element,controlbarid, preview){
            	var controls ='<div class="poodll_mediavideobox" id="' + controlbarid + '">' ;
			var status = this.fetch_status_bar('burntrose');
            controls += status,
			controls += preview,
            controls +=  '<div class="poodll_mediavideobox2" id="' + controlbarid + '">' ;
                controls +=  '<span class="poodll_start-recording" title="' + M.util.get_string('recui_record', 'filter_poodll') + '"></span>';
                controls += '<span class="poodll_stop-recording hide" title="' + M.util.get_string('recui_stop', 'filter_poodll') + '"></span>';
                controls += '<span class="poodll_pause-recording hide" title="' + M.util.get_string('recui_pause', 'filter_poodll') + '" ></span>';
                controls += ' <span class="poodll_resume-recording hide" title="' + M.util.get_string('recui_continue', 'filter_poodll') + '"></span>';
                controls += ' <span class="poodll_play-recording" ></span>';
				controls += ' <span class="poodll_playsave hide" title="' + M.util.get_string('recui_play', 'filter_poodll') + '"></span>';
				controls += ' <span class="poodll_mic hide" ></span>';
				controls += ' <span class="poodll_recmic hide" ></span>';
				controls += ' <span class="poodll_resume_mic hide" ></span>';

                 controls += '<span class="poodll_savebtn "title="' + M.util.get_string('recui_save', 'filter_poodll') + '" ></span>';
                 controls += '<span class="poodll_save-recording_burntrose hide "title="' + M.util.get_string('recui_save', 'filter_poodll') + '" ></span>';
                controls += '<span class="poodll_savedsuccessfully hide" ></span>';
                controls += '</div>';
				controls += '</div>';
               
               
                $(element).prepend(controls);
                var controlbar ={
					status: $('#' + controlbarid + ' > .poodll_status_burntrose'),
                    preview: $('#' + controlbarid + ' > .poodll_preview_burntrose'),
                    startbutton: $('#' + controlbarid + ' > .poodll_start-recording'),
                    stopbutton: $('#' + controlbarid + ' > .poodll_stop-recording'),
                    pausebutton: $('#' + controlbarid + ' > .poodll_pause-recording'),
                    resumebutton: $('#' + controlbarid + ' > .poodll_resume-recording'),
                    play1: $('#' + controlbarid + ' > .poodll_play-recording'),
					playbutton: $('#' + controlbarid + ' > .poodll_playsave'),
                    save1: $('#' + controlbarid + ' > .poodll_savebtn'),
					savebutton: $('#' + controlbarid + ' > .poodll_save-recording_burntrose'),
					savesuccess: $('#' + controlbarid + ' > .poodll_savedsuccessfully'),
					
					playermic: $('#'+controlbarid + '> .poodll_mic'),
					recordmic: $('#'+controlbarid + '> .poodll_recmic'),
					resumemic: $('#'+controlbarid + '> .poodll_resume_mic')					
                };
                return controlbar;
        }, //end of fetch_control_bar_video_burntrose
        
        register_controlbar_events_burntrose: function(onMediaSuccess, mediaConstraints,controlbarid){
            var self = this;
			var ip = this.fetch_instanceprops(controlbarid);
            
             ip.controlbar.startbutton.click(function() {
                this.disabled = false;
                
                //clear messages
                $('#' + ip.config.widgetid  + '_messages').text('');

                ip.blobs=[]; 
                self.captureUserMedia(mediaConstraints, onMediaSuccess, self.onMediaError);          
               

			     ip.controlbar.playermic.hide();
				 ip.controlbar.recordmic.show();
				 ip.controlbar.playbutton.hide();
				 ip.controlbar.play1.hide();
                 ip.controlbar.pausebutton.show();
                 ip.controlbar.pausebutton.attr('disabled',false);

                 ip.controlbar.startbutton.hide();

                 ip.controlbar.stopbutton.show();
                 ip.controlbar.stopbutton.attr('disabled',false);
				
			     ip.controlbar.savebutton.hide();
				 ip.controlbar.savesuccess.hide();
				 ip.controlbar.save1.show();
				 



                self.set_visual_mode('recordmode',controlbarid);
                
                //timer and status bar
               //timer and status bar
                ip.timer.reset();
                ip.timer.start();
                self.update_status(controlbarid);
				
            });
			
            
            ip.controlbar.stopbutton.click(function() {
               this.disabled = false;
                
                ip.controlbar.stopbutton.hide();

                 ip.controlbar. startbutton.show();
                 ip.controlbar.startbutton.attr('disabled',false);


				 //ip.controlbar.savebutton.removeClass('poodll_save-recording_burntrose');
			     //ip.controlbar.savebutton.addClass('poodll_savebtn');
				

                 ip.controlbar.resumebutton.hide();
				 ip.controlbar.pausebutton.hide();
				 ip.controlbar.play1.hide();
				 ip.controlbar.playbutton.show();
				 
				 ip.controlbar.save1.hide();
				 ip.controlbar.savesuccess.hide();
				 ip.controlbar.savebutton.show();
				
				 
                 ip.controlbar.resumemic.hide();
			     ip.controlbar.recordmic.hide();
				 ip.controlbar.playermic.show();
				 
				 
				 
                ip.mediaRecorder.stop();
                //this throws an error, do we worry?
                //ip.mediaRecorder.stream.stop();
                
                 var preview = ip.controlbar.preview;
                if(preview && preview.get(0)){
                    preview.get(0).pause();
                }
                
               //turn border black etc
               self.set_visual_mode('previewmode',controlbarid);
               //timer and status bar
               ip.timer.stop()
               self.update_status(controlbarid);
                
               
              if(!ip.uploaded){
               	ip.controlbar.startbutton.attr('disabled',false);
              } 
              
			   
			   
            });
          
            ip.controlbar.pausebutton.click(function() {
               this.disabled = true;
                $(this).hide();
                ip.controlbar.resumebutton.show();
                ip.mediaRecorder.resume();
                ip.mediaRecorder.pause();
                ip.controlbar.resumebutton.attr('disabled',false) ;
                self.set_visual_mode('pausedmode',controlbarid);
                
                //timer and status bar
                ip.timer.pause();
                self.update_status(controlbarid);
							  
             ip.controlbar.recordmic.hide();
             ip.controlbar.resumemic.show();
            });
            
           ip.controlbar.resumebutton.click(function() {
                this.disabled = true;
                $(this).hide();
                ip.controlbar.pausebutton.show();
                ip.mediaRecorder.resume();
                ip.controlbar.pausebutton.attr('disabled',false);
                self.set_visual_mode('recordmode',controlbarid);
                
                //timer and status bar
                ip.timer.resume();
                self.update_status(controlbarid);
				 ip.controlbar.resumemic.hide();
             ip.controlbar.recordmic.show();
            });
            
            ip.controlbar.playbutton.click(function() {
                this.disabled = false;
                var preview = ip.controlbar.preview.get(0);
				
				//if we are playing already, lets stop
				if(preview.currentTime > 0 && !preview.paused){
					preview.pause();
					preview.currentTime=0;
					 return;
				}
				
                if(ip.blobs && ip.blobs.length > 0){
                    if(ip.blobs[0].type=='audio/wav'){
                        //mediastreamrecorder adds a header to each wav blob, 
                        //we remove them and combine audodata and new header
                        utils.concatenateWavBlobs(ip.blobs,  function(concatenatedBlob) {
                                 var mediaurl = URL.createObjectURL(concatenatedBlob);
                                 preview.src= mediaurl;
                                 preview.controls =true;
                                 preview.volume = ip.previewvolume;
                                 preview.play();
                        });
                    }else{
                        ConcatenateBlobs(ip.blobs, ip.blobs[0].type, function(concatenatedBlob) {
                                 var mediaurl = URL.createObjectURL(concatenatedBlob);
                                 preview.src= mediaurl;
                                 preview.controls =true;
                                 preview.volume = ip.previewvolume;
                                 preview.play();
                        }); //end of concatenate blobs
                    }
                }       
                
				ip.controlbar.startbutton.show();
            });
            
           ip.controlbar.savebutton.click(function() {
                this.disabled = false;

              //I know you want to allow multiple submissions off one page load BUT
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
                   if(ip.blobs[0].type=='audio/wav'){
                        //mediastreamrecorder adds a header to each wav blob, 
                        //we remove them and combine audodata and new header
                        utils.concatenateWavBlobs(ip.blobs,  function(concatenatedBlob) {
                                ip.uploader.uploadBlob(concatenatedBlob,ip.blobs[0].type);
                                ip.controlbar.startbutton.attr('disabled',true);
                                ip.uploaded = true;
                        });
                   }else{
                        ConcatenateBlobs(ip.blobs, ip.blobs[0].type, function(concatenatedBlob) {
                                ip.uploader.uploadBlob(concatenatedBlob,ip.blobs[0].type);
                                ip.controlbar.startbutton.attr('disabled',true);
                                ip.uploaded = true;
                        }); //end of concatenate blobs
                   }//end of if audio/wav
                }else{
                    ip.uploader.Output(M.util.get_string('recui_nothingtosaveerror','filter_poodll'));
                }//end of if ip.blobs		
            	//probably not necessary  ... but getting odd ajax errors occasionally
            	return false;
            });//end of save recording
            
            window.onbeforeunload = function() {
                ip.controlbar.startbutton.attr('disabled',false);
                var preview = ip.controlbar.preview;
                if(preview && preview.get(0)){
                    preview.get(0).pause();
                }
            };
        }//end of register_controlbar_events_burntrose
        
    };//end of returned object
});//total end
