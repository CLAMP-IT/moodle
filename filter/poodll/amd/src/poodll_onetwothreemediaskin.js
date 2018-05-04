define(['jquery','jqueryui','core/log','filter_poodll/utils_amd', 'filter_poodll/anim_progress_bar', 'filter_poodll/speech_poodll','filter_poodll/dlg_devicesettings','filter_poodll/upskin_bar'], function($, jqui, log, utils, anim_progress_bar,browserrecognition,settings, upskin_bar) {
/* jshint ignore:start */

    "use strict"; // jshint ;_;

    log.debug('PoodLL One Two Three Skin: initialising');

    return {
    
        instanceprops: null,
        pmr: null,
        stage: 'none',
        uploaded: false,
        devsettings: null,

        //for making multiple instances
        clone: function(){
            return $.extend(true,{},this);
        },
		
        init: function(ip, pmr){
            this.instanceprops=ip;
            this.pmr=pmr;
            this.devsettings=settings.clone();
            this.devsettings.init(pmr,ip);
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
            var status = '<div class="poodll_status_' + skin + '" height="50">00:00:00</div>';
            return status;
        },
        
        fetch_preview_audio: function(skin){
            var preview = '<audio class="audio_preview_123 poodll_preview_' + skin + ' hide" width="100%" height="100%" controls></audio>';
            return preview;
        },
        fetch_preview_video: function(skin){
            var preview ='<video class="video_preview_123 poodll_preview_' + skin + '" width="100%" height="300px"></video>';
            return preview;
        },
        fetch_resource_audio: function(skin){
            var resourceplayer = '<audio class="poodll_resourceplayer_' + skin + ' hide" ></audio>';
            return resourceplayer;
        },
        fetch_resource_video: function(skin){
            var resourceplayer = '<video class="poodll_resourceplayer_' + skin + ' hide" ></video>';
            return resourceplayer;
        },
        fetch_uploader_skin: function(controlbarid, element){
            var ip = this.fetch_instanceprops(controlbarid);
            var upskin = upskin_bar.clone();
            upskin.init(ip.config,element,ip.controlbar.progresscanvas,ip.controlbar.timer);
            return upskin;
        },


        onMediaError: function(e) {
                console.error('media error', e);
        },

        onMediaSuccess_video: function(controlbarid){
            var ip = this.fetch_instanceprops(controlbarid);

            //clear messages
            ip.uploader.Output('');

            this.set_visual_mode('recordmode',controlbarid);
            //timer and status bar
            ip.timer.reset();
            ip.timer.start();
            this.update_status(controlbarid);

            //set recording stage
            this.stage="recorded";
        },

        onMediaSuccess_audio: function(controlbarid){
            var ip = this.fetch_instanceprops(controlbarid);

            //clear messages
            ip.uploader.Output('');

            this.set_visual_mode('recordmode',controlbarid);
            //timer and status bar
            ip.timer.reset();
            ip.timer.start();
            this.update_status(controlbarid);
            ip.controlbar.preview.attr('src',null);

            //set recording stage
            this.stage="recorded";

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
					ip.controlbar.progress.hide();
                    ip.controlbar.preview.addClass('poodll_recording');
                    ip.controlbar.status.addClass('poodll_recording');
                    if(ip.config.mediatype=='audio'){
                        ip.controlbar.preview.addClass('hide');
                    }
                    ip.controlbar.status.removeClass('hide');
					self.disable_button(this);
					self.disable_button(ip.controlbar.playbutton);
					ip.controlbar.playbutton.show();  
					self.disable_button(ip.controlbar.stopbutton);
					ip.controlbar.stopbutton.hide();
					self.disable_button(ip.controlbar.savebutton);
					ip.controlbar.savebutton.show();          
					self.enable_button(ip.controlbar.stoprecbutton);
					ip.controlbar.stoprecbutton.show();
                   ip.controlbar.startbutton.hide();
					
					
                    break;

               case 'previewmode':
                   ip.controlbar.playbutton.hide();
					self.enable_button(ip.controlbar.stopbutton);
					ip.controlbar.stopbutton.show();
					self.disable_button(ip.controlbar.startbutton);
					ip.controlbar.startbutton.show();
					self.disable_button(ip.controlbar.resumebutton);
					ip.controlbar.resumebutton.hide();
					self.disable_button(ip.controlbar.playbutton);
                    ip.controlbar.preview.removeClass('poodll_recording');
                    ip.controlbar.status.removeClass('poodll_recording');
                    if(ip.config.mediatype=='audio'){
                        ip.controlbar.preview.removeClass('hide');
                    }else{
                        ip.controlbar.status.removeClass('hide');
                    }

                    ip.controlbar.status.addClass('hide');
                    if ( ip.controlbar.progresscanvas.hasClass("hide") ) {
                        ip.controlbar.progresscanvas.removeClass('hide');
                         ip.controlbar.progresscanvas.show();
                    }

					if(!$(this).hasClass('played')){
						$(this).addClass('played');
						ip.controlbar.steptwo.empty();
						ip.controlbar.steptwo.append('<i class="fa fa-check" aria-hidden="true"></i>').hide().fadeIn(1000);
					}
					
					
                    break;

               case 'pausedmode':

					self.enable_button(ip.controlbar.startbutton);
					ip.controlbar.startbutton.show();
					self.enable_button(ip.controlbar.playbutton);
					ip.controlbar.playbutton.show();
					self.disable_button(ip.controlbar.savebutton);
					ip.controlbar.savebutton.show();
                    ip.controlbar.preview.removeClass('poodll_recording');
                    ip.controlbar.status.removeClass('poodll_recording');
                    ip.controlbar.stoprecbutton.hide();

						
                    ip.controlbar.timer.html('00:00:00');

                    ip.controlbar.status.addClass('hide');
                    ip.controlbar.progress.show();

					ip.controlbar.stepone.empty();
					ip.controlbar.stepone.append('<i class="fa fa-check" aria-hidden="true"></i>').hide().fadeIn(1000);

					self.disable_button(ip.controlbar.stoprecbutton);
                   
				   break;
                
		
				case 'stopplayingmode':
					if(!self.uploaded){
						self.enable_button(ip.controlbar.savebutton);
						self.enable_button(ip.controlbar.startbutton);
					} 

                    if(!ip.controlbar.progresscanvas.hasClass('hide')){
                        ip.controlbar.timer.html('00:00:00');
                        ip.controlbar.progresscanvas.addClass('hide');
                        ip.controlbar.progresscanvas.hide();
                    }
                    ip.controlbar.stopbutton.hide();
					ip.controlbar.playbutton.show();
					ip.controlbar.startbutton.show();
					self.enable_button(ip.controlbar.playbutton);
					self.disable_button(ip.controlbar.stopbutton);
				break;
				
               case 'uploadmode':

                   self.disable_button(ip.controlbar.startbutton);
                   ip.controlbar.progresscanvas.removeClass('hide');
                   ip.controlbar.progresscanvas.show();

                    break;

               case 'savemode':
				
					self.disable_button(ip.controlbar.savebutton);
					ip.controlbar.stepthree.empty();
					ip.controlbar.stepthree.append('<i class="fa fa-check" aria-hidden="true"></i>').hide().fadeIn(1000);
				break;
           }

       },

       //insert the control bar and return it to be reused
        insert_controlbar_video: function(element, controlbarid, preview, resource) {
            var controlbar = this.prepare_controlbar(element,controlbarid, preview,resource,'video');
        	return controlbar;
        },
        //insert the control bar and return it to be reused
        insert_controlbar_audio: function(element,controlbarid, preview, resource){
        	var controlbar = this.prepare_controlbar(element,controlbarid, preview,resource,'audio');
        	return controlbar;
        },
        
        //insert the control bar and return it to be reused
        prepare_controlbar: function(element,controlbarid, preview,resource, mediatype){
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
				var ss = this.pmr.fetch_strings();
                var controls ='<div class="one-two-three-main-wrapper poodll_mediarecorderholder_onetwothree ' + recorder_class + ' ' + size_class + '" id="holder_' + controlbarid + '">' ;
  	
					controls +='<div class="modal-box poodll_mediarecorderbox_onetwothree one-two-three-wrap" id="' + controlbarid + '">' ;
						controls +='<div class="style-holder ' + skin_style + '">' ;
							var status = this.fetch_status_bar('onetwothree');
							controls += status,
							controls += '<div class="hp_slide" id="slide_'+controlbarid+'">';
							controls += '<div class="hp_timer"></div><canvas class="hp_range"></canvas></div>';
							controls += preview,
							controls += '<div class="settingsicon" id="settingsicon_'+controlbarid+'"><button type="button" class="btn btn-info btn-lg" data-toggle="modal" data-target="#myModal"><i class="fa fa-cogs" aria-hidden="true"></i></button></div>';
							controls +=  '<button type="button" class="poodll_mediarecorder_button_onetwothree poodll_start-recording_onetwothree"><i class="fa fa-microphone" aria-hidden="true"></i></button> ';
							controls += '<button type="button" class="poodll_mediarecorder_button_onetwothree poodll_stop-recording_onetwothree pmr_disabled hide" disabled><i class="fa fa-stop" aria-hidden="true"></i></button>';
							controls += '<button type="button" class="poodll_mediarecorder_button_onetwothree poodll_pause-recording_onetwothree pmr_disabled hide" disabled><i class="fa fa-pause" aria-hidden="true"></i></button>';
							controls += ' <button type="button" class="poodll_mediarecorder_button_onetwothree poodll_resume-recording_onetwothree pmr_disabled hide" disabled><i class="fa fa-microphone" aria-hidden="true"></i></button>';
							controls += '<button type="button" class="poodll_mediarecorder_button_onetwothree poodll_play-recording_onetwothree pmr_disabled" disabled><i class="fa fa-play" aria-hidden="true"></i></button> ';
							controls += '<button type="button" class="poodll_mediarecorder_button_onetwothree poodll_stop-playing_onetwothree pmr_disabled hide" disabled><i class="fa fa-stop" aria-hidden="true"></i></button>';
							controls += '<button type="button" class="poodll_mediarecorder_button_onetwothree poodll_save-recording_onetwothree pmr_disabled" disabled><i class="fa fa-upload" aria-hidden="true"></i></button>';
							controls += '<div style="clear:both;">';
							controls += '<div class="task-helper">';
								controls += '<p class="step-1">'+ ss['recui_record'] +'</p>';
								controls += '<p class="step-2">' + ss['recui_play'] +'</p>';
								controls += '<p class="step-3">' + ss['recui_save'] + '</p>';
							controls += '</div>';
						controls += '</div>';
					controls += '</div>';
					controls += this.devsettings.fetch_dialogue_box();
                    controls += ip.errordialog.fetch_dialogue_box();
                controls += '</div>';
				
                $(element).prepend(controls);

                var controlbar ={
                    settingsdialog: $('#' + controlbarid + ' .poodll_dialogue_box_settings'),
                    errorsdialog: $('#' + controlbarid + ' .poodll_dialogue_box_errors'),
					settingsicon: $('#' + controlbarid + ' .settingsicon'),
					stepone: $('#' + controlbarid + ' .step-1'),
					steptwo: $('#' + controlbarid + ' .step-2'),
					stepthree: $('#' + controlbarid + ' .step-3'),
                    progresscanvas: $('#' + controlbarid + ' .hp_range'),
                	progress: $('#' + controlbarid + ' .hp_slide'),
					timer: $('#' + controlbarid + ' .hp_timer'),
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
                
                //settings and error dialogs
                //They use the same dialog and just fill it with diofferent stuff
                //settings is on 'this' because it is shown from skkn events, but errors are from pmr stuff
                ip.errordialog.set_dialogue_box(controlbar.errorsdialog);
                this.devsettings.set_dialogue_box(controlbar.settingsdialog);
                
                return controlbar;
        }, //end of fetch_control_bar_onetwothree


        register_controlbar_events_video: function(onMediaSuccess,  controlbarid) {
            return this.register_controlbar_events_audio(onMediaSuccess, controlbarid);
        },
		
        register_controlbar_events_audio: function(onMediaSuccess, controlbarid){
            var self = this;
            var pmr=this.pmr;
            var stage= this.stage;
            var ip = this.fetch_instanceprops(controlbarid);
            
            //init radial progress
            var hprogress = anim_progress_bar.clone();
            hprogress.init(ip.controlbar.progresscanvas);

			ip.controlbar.settingsicon.click(function(){
				self.devsettings.open();
			});
			
			ip.controlbar.startbutton.click(function() {
				pmr.do_start_audio(ip, onMediaSuccess);
            });

            ip.controlbar.stoprecbutton.click(function() {
				pmr.do_stop_audio(ip);
				self.set_visual_mode('pausedmode',controlbarid);
                //timer and status bar
                ip.timer.stop();
                ip.timer.reset();
                self.update_status(controlbarid);
            });
                
            
            ip.controlbar.stopbutton.click(function() {
				self.set_visual_mode('stopplayingmode',controlbarid);
				//stop playing
				var preview = ip.controlbar.preview.get(0);
				pmr.do_stopplay_audio(ip,preview);
                hprogress.stop();
            });
             
            ip.controlbar.playbutton.click(function() {

                ip.controlbar.preview.on('timeupdate', function(){
                    var currentTime = this.currentTime;
                    ip.controlbar.timer.html(ip.timer.fetch_display_time(currentTime));
                });

                var preview = ip.controlbar.preview.get(0);
                pmr.do_play_audio(ip,preview);

               //do visuals
				self.set_visual_mode('previewmode',controlbarid);

                //set recording stage
                stage="played";
				
				//start animation
                hprogress.clear();
                hprogress.fetchCurrent=function(){
                    var ct = ip.controlbar.preview.prop('currentTime');
                    var duration = ip.controlbar.preview.prop('duration');
                    if(!isFinite(duration)){duration=ip.timer.finalseconds;}
                    return ct/duration;
                };
                hprogress.start();

            });
            
			ip.controlbar.savebutton.click(function() {
				self.set_visual_mode('savemode',controlbarid);
				log.debug('save');
				if(ip.blobs && ip.blobs.length > 0){
					  pmr.do_save_audio(ip);
					  self.uploaded = true;
					  self.set_visual_mode('uploadmode',controlbarid);
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

