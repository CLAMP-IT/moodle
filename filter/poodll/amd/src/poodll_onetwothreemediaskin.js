/* jshint ignore:start */
define(['jquery','core/log','filter_poodll/utils_amd'], function($, log, utils) {

    "use strict"; // jshint ;_;

    log.debug('PoodLL Base Skin: initialising');

    return {
    
        instanceprops: null,
        pmr: null,
        stage: 'none',
        uploaded: false,

        //for making multiple instances
        clone: function(){
            return $.extend(true,{},this);
        },

        init: function(ip, pmr){
            this.instanceprops=ip;
            this.pmr=pmr;
        },


        fetch_instanceprops : function(){
            return this.instanceprops;
        },


        onUploadSuccess: function(controlbarid){
             $('#' + controlbarid + ' > .poodll_save-recording').hide();
             $('#' + controlbarid + ' > .poodll_savedsuccessfully').show();
        },
        
        onUploadFailure: function(controlbarid){
            return;
        },		

        fetch_status_bar: function(skin){
            var status = '<div class="poodll_status_' + skin + '" width="320" height="50">00:00:00</div>';
            return status;
        },
        
        fetch_preview_audio: function(skin){
            var preview = '<audio class="poodll_preview_' + skin + ' hide" width="100%" height="100%" controls></audio>';
            return preview;
        },
        fetch_preview_video: function(skin){
            var preview ='<video class="poodll_preview_' + skin + '" width="100%" height="100%"></video>';
            return preview;
        },
        
        onMediaError: function(e) {
                console.error('media error', e);
        },

        onMediaSuccess_video: function(controlbarid){
            var ip = this.fetch_instanceprops(controlbarid);
            ip.controlbar.stoprecbutton.attr('disabled',false);
        },

        onMediaSuccess_audio: function(controlbarid){
            var ip = this.fetch_instanceprops(controlbarid);
            ip.controlbar.preview.attr('src',null);
            ip.controlbar.stoprecbutton.attr('disabled',false);
        },

        handle_timer_update: function(controlbarid){
            var ip = this.fetch_instanceprops(controlbarid);
            ip.controlbar.status.html(ip.timer.fetch_display_time());
            if(ip.timer.seconds==0 && ip.timer.initseconds >0){
                 ip.controlbar.stoprecbutton.click();
            }
        },

       update_status: function(controlbarid){
            var ip = this.fetch_instanceprops(controlbarid);
            ip.controlbar.status.html(ip.timer.fetch_display_time());
        },

        //set visuals for different states (ie recording or playing)
        set_visual_mode: function(mode, controlbarid){
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
        insert_controlbar_video: function(element, controlbarid, preview) {
            var controlbar = this.prepare_controlbar(element,controlbarid, preview,'video');
        	return controlbar;
        },
        //insert the control bar and return it to be reused
        insert_controlbar_audio: function(element,controlbarid, preview){
        	var controlbar = this.prepare_controlbar(element,controlbarid, preview,'audio');
        	return controlbar;
        },
        
        //insert the control bar and return it to be reused
        prepare_controlbar: function(element,controlbarid, preview, mediatype){
                var ip = this.fetch_instanceprops(controlbarid);
                var skin_style = ip.config.media_skin_style;
                
                var recorder_class = mediatype=='video' ?  'poodll_mediarecorder_video' : 'poodll_mediarecorder_audio';
                
                var size_class = 'poodll_mediarecorder_size_auto';
                switch(ip.config.size){
                	case 'small':
	                	size_class = 'poodll_mediarecorder_size_small';
                		break;
                	case 'big':
                		size_class = 'poodll_mediarecorder_size_big';
                		break;
                	case 'auto':
	                	size_class = 'poodll_mediarecorder_size_auto';		
                }

                var controls ='<div class="one-two-three-main-wrapper poodll_mediarecorderholder_onetwothree ' + recorder_class + ' ' + size_class + '" id="holder_' + controlbarid + '">' ;
                	
					controls +='<div class="poodll_mediarecorderbox_onetwothree one-two-three-wrap" id="' + controlbarid + '">' ;
						controls +='<div class="style-holder ' + skin_style + '">' ;
							var status = this.fetch_status_bar('onetwothree');
							controls += status,
							controls += preview,
							controls +=  '<button type="button" class="poodll_mediarecorder_button_onetwothree poodll_start-recording_onetwothree"><i class="fa fa-microphone" aria-hidden="true"></i></button> ';
							controls += '<button type="button" class="poodll_mediarecorder_button_onetwothree poodll_stop-recording_onetwothree pmr_disabled hide" disabled><i class="fa fa-stop" aria-hidden="true"></i></button>';
							controls += '<button type="button" class="poodll_mediarecorder_button_onetwothree poodll_pause-recording_onetwothree pmr_disabled hide" disabled><i class="fa fa-pause" aria-hidden="true"></i></button>';
							controls += ' <button type="button" class="poodll_mediarecorder_button_onetwothree poodll_resume-recording_onetwothree pmr_disabled hide" disabled><i class="fa fa-microphone" aria-hidden="true"></i></button>';
							controls += '<button type="button" class="poodll_mediarecorder_button_onetwothree poodll_play-recording_onetwothree pmr_disabled" disabled><i class="fa fa-play" aria-hidden="true"></i></button> ';
							controls += '<button type="button" class="poodll_mediarecorder_button_onetwothree poodll_stop-playing_onetwothree pmr_disabled hide" disabled><i class="fa fa-stop" aria-hidden="true"></i></button>';
							controls += '<button type="button" class="poodll_mediarecorder_button_onetwothree poodll_save-recording_onetwothree pmr_disabled" disabled><i class="fa fa-upload" aria-hidden="true"></i></button>';
							controls += '<div style="clear:both;">';
							controls += '<div class="task-helper">';
								controls += '<p class="step-1">'+ M.util.get_string('recui_record', 'filter_poodll') +'</p>';
								controls += '<p class="step-2">' + M.util.get_string('recui_play', 'filter_poodll') + '</p>';
								controls += '<p class="step-3">' + M.util.get_string('recui_save', 'filter_poodll') + '</p>';
							controls += '</div>';
						controls += '</div>';
					controls += '</div>';
                controls += '</div>';
				
                $(element).prepend(controls);
                var controlbar ={
                    status: $('#' + controlbarid + ' .poodll_status_onetwothree'),
                    preview: $('#' + controlbarid + ' .poodll_preview_onetwothree'),
                    startbutton: $('#' + controlbarid + ' .poodll_start-recording_onetwothree'),
                    stoprecbutton: $('#' + controlbarid + ' .poodll_stop-recording_onetwothree'),
                    stopbutton: $('#' + controlbarid + ' .poodll_stop-playing_onetwothree'),
                    pausebutton: $('#' + controlbarid + ' .poodll_pause-recording_onetwothree'),
                    resumebutton: $('#' + controlbarid + ' .poodll_resume-recording_onetwothree'),
                    playbutton: $('#' + controlbarid + ' .poodll_play-recording_onetwothree'),
                    savebutton: $('#' + controlbarid + ' .poodll_save-recording_onetwothree')    
                };
                return controlbar;
        }, //end of fetch_control_bar_onetwothree


        register_controlbar_events_video: function(onMediaSuccess, mediaConstraints, controlbarid) {
            return this.register_controlbar_events_audio(onMediaSuccess, mediaConstraints, controlbarid);
        },

        register_controlbar_events_audio: function(onMediaSuccess, mediaConstraints, controlbarid){
            var self = this;
            var pmr=this.pmr;
            var stage= this.stage;
            var ip = this.fetch_instanceprops(controlbarid);



      
        
        
            ip.controlbar.startbutton.click(function() {
                 log.debug('video starting');
                pmr.do_start_audio(ip, mediaConstraints, onMediaSuccess);

                //clear messages
                $('#' + ip.config.widgetid  + '_messages').text('');
               self.disable_button(this);

                self.disable_button(ip.controlbar.playbutton);
               ip.controlbar.playbutton.show();
               
               self.disable_button(ip.controlbar.stopbutton);
               ip.controlbar.stopbutton.hide();
      
               self.disable_button(ip.controlbar.savebutton);
                ip.controlbar.savebutton.show();          
           
                self.enable_button(ip.controlbar.stoprecbutton);
                ip.controlbar.stoprecbutton.show();
               
                
                
                $(this).hide();
                self.set_visual_mode('recordmode',controlbarid);
                
                //timer and status bar
                ip.timer.reset();
                ip.timer.start();
                self.update_status(controlbarid);
                
                //set recording stage
                stage="recorded";
            });
            
            ip.controlbar.stoprecbutton.click(function() {
				$('#holder_' + controlbarid + ' .task-helper p.step-1').empty();
				$('#holder_' + controlbarid + ' .task-helper p.step-1').append('<i class="fa fa-check" aria-hidden="true"></i>').hide().fadeIn(1000);;
				
				
				
				
                self.disable_button(this);
                $(this).hide();
              pmr.do_stop_audio(ip);
              
                 self.enable_button(ip.controlbar.startbutton);
                 ip.controlbar.startbutton.show();
                
                 self.enable_button(ip.controlbar.playbutton);
                 ip.controlbar.playbutton.show();
                 
                 self.disable_button(ip.controlbar.savebutton);
                 ip.controlbar.savebutton.show();
                
                self.set_visual_mode('pausedmode',controlbarid);
                
                //timer and status bar
                ip.timer.stop();
                ip.timer.reset();
                self.update_status(controlbarid);
            });
            
            // currently not used
            ip.controlbar.pausebutton.click(function() {
                this.disabled = true;
                $(this).hide();
               
                ip.controlbar.resumebutton.show();
                pmr.do_pause_audio(ip);
                self.enable_button(ip.controlbar.resumebutton);
                
                 self.enable_button(ip.controlbar.playbutton);
                 ip.controlbar.playbutton.show();
                 
                 self.enable_button(ip.controlbar.savebutton);
                 ip.controlbar.savebutton.show();
                
                self.set_visual_mode('pausedmode',controlbarid);
                
                //timer and status bar
                ip.timer.pause();
                self.update_status(controlbarid);
            });
            
            //currently not used
            ip.controlbar.resumebutton.click(function() {
                self.disable_button(this);
                $(this).hide();
                ip.controlbar.pausebutton.show();
                pmr.do_resume_audio(ip);
                self.enable_button(ip.controlbar.pausebutton);
                self.set_visual_mode('recordmode',controlbarid);
                
                //timer and status bar
                ip.timer.resume();
                self.update_status(controlbarid);
            });
            
            
            ip.controlbar.stopbutton.click(function() {

				

				
				
             //stop playing
             var preview = ip.controlbar.preview.get(0);
              pmr.do_stopplay_audio(ip,preview);
             
               self.enable_button(ip.controlbar.playbutton);
               ip.controlbar.playbutton.show();

              if(!self.uploaded){
                self.enable_button(ip.controlbar.savebutton);
                self.enable_button(ip.controlbar.startbutton);
        
              } 
      
              ip.controlbar.startbutton.show();
              
               self.disable_button(this);
                $(this).hide();
            });
            
            ip.controlbar.playbutton.click(function() {
				
				if(!$(this).hasClass('played')){
					$(this).addClass('played');
					$('#holder_' + controlbarid + ' .task-helper p.step-2').empty();
					$('#holder_' + controlbarid + ' .task-helper p.step-2').append('<i class="fa fa-check" aria-hidden="true"></i>').hide().fadeIn(1000);
				}
				
                
               //turn border black etc
				self.set_visual_mode('previewmode',controlbarid);
                var preview = ip.controlbar.preview.get(0);
                pmr.do_play_audio(ip,preview);

                self.enable_button(ip.controlbar.stopbutton);
                ip.controlbar.stopbutton.show();
                
                self.disable_button(ip.controlbar.startbutton);
                ip.controlbar.startbutton.show();
                
                self.disable_button(ip.controlbar.resumebutton);
                ip.controlbar.resumebutton.hide();
                
                self.disable_button(this);
                $(this).hide();
                
                //set recording stage
                stage="played";
                
            });
            
           ip.controlbar.savebutton.click(function() {
                
                  
              self.disable_button(this);
				$('#holder_' + controlbarid + ' .task-helper p.step-3').empty();
				$('#holder_' + controlbarid + ' .task-helper p.step-3').append('<i class="fa fa-check" aria-hidden="true"></i>').hide().fadeIn(1000);
			  
			  
			  
               log.debug('save');
              if(ip.blobs && ip.blobs.length > 0){
                  pmr.do_save_audio(ip);
                  self.uploaded = true;
                  self.disable_button(ip.controlbar.startbutton);
                  
                  //set recording stage
                  stage="saved";
                }else{
                    ip.uploader.Output(M.util.get_string('recui_nothingtosaveerror','filter_poodll'));
                }//end of if self.blobs		
                //probably not necessary  ... but getting odd ajax errors occasionally
                return false;
            });//end of save recording
            
            window.onbeforeunload = function() {
                self.enable_button(ip.controlbar.startbutton);
                var preview = ip.controlbar.preview;
                if(preview && preview.get(0)){
                    preview.get(0).pause();
                }
            };
        }, //end of register_control_bar_events_onetwothree
        
        enable_button: function(button){
            $(button).attr('disabled',false);
            $(button).removeClass('pmr_disabled');
        },
        disable_button: function(button){
            $(button).attr('disabled',true);
            $(button).addClass('pmr_disabled');
        },

    };//end of returned object
    
});//total end

