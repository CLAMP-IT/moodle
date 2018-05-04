/* jshint ignore:start */
define(['jquery','jqueryui','core/log','filter_poodll/utils_amd','filter_poodll/dlg_devicesettings','filter_poodll/anim_progress_bar', 'filter_poodll/upskin_bar'], function($,jqui, log, utils,settings,anim_progress_bar, upskin_bar) {

    "use strict"; // jshint ;_;

    log.debug('PoodLL BMR Skin: initialising');


	
    return {
    
        instanceprops: null,
        pmr: null,
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
            // $('#' + controlbarid  + '_messages').hide();
             $('#' + controlbarid + ' > .poodll_savedsuccessfully').show();
        },
        
        onUploadFailure: function(controlbarid){
            return;
        },		

        fetch_status_bar: function(skin){
           // var status = '<div class="poodll_status_' + skin + '" width="320" height="50">00:00:00</div>';
            var status = '<div class="poodll_status_' + skin + '">00:00:00</div>';
            return status;
        },
        
        fetch_preview_audio: function(skin){
            var preview = '<audio class="poodll_preview_' + skin + ' hide" controls></audio>';
            return preview;
        },
        fetch_preview_video: function(skin){
            var preview ='<video class="poodll_preview_' + skin + '"></video>';
            return preview;
        },
        fetch_resource_audio: function(skin){
            var resourceplayer = '';
            return resourceplayer;
        },
        fetch_resource_video: function(skin){
            var resourceplayer = '';
            return resourceplayer;
        },

        fetch_uploader_skin: function(controlbarid, element){
            var ip = this.fetch_instanceprops(controlbarid);
            var upskin = upskin_bar.clone();
            upskin.init(ip.config,element,ip.controlbar.bmr_progresscanvas,ip.controlbar.status);
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
        },

        onMediaSuccess_audio: function(controlbarid){


            var ip = this.fetch_instanceprops(controlbarid);
            //clear messages
            ip.uploader.Output('');
            ip.controlbar.preview.attr('src',null);
            this.set_visual_mode('recordmode',controlbarid);

            //timer and status bar
            ip.timer.reset();
            ip.timer.start();
            this.update_status(controlbarid);
        },

        handle_timer_update: function(controlbarid){
            var ip = this.fetch_instanceprops(controlbarid);
            ip.controlbar.status.html(ip.timer.fetch_display_time());
            if(ip.timer.seconds==0 && ip.timer.initseconds >0){
                 ip.controlbar.stopbutton.click();
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
            this.visualmode=mode;

           switch(mode){

               case 'recordmode':

                   self.disable_button(ip.controlbar.startbutton);
                   self.show_element(ip.controlbar.startbutton);

                   self.hide_element(ip.controlbar.bmr_progresscanvas);

                   self.enable_button(ip.controlbar.pausebutton);
                   self.show_element(ip.controlbar.pausebutton);

                   self.disable_button(ip.controlbar.playbutton);
                   self.hide_element(ip.controlbar.resumebutton);
                   self.enable_button(ip.controlbar.stopbutton);

                   //disable save button
                   self.disable_button(ip.controlbar.savebutton);

                    ip.controlbar.preview.addClass('poodll_recording');
                    ip.controlbar.status.addClass('poodll_recording');

                    if(ip.config.mediatype=='audio'){
                        self.hide_element(ip.controlbar.preview);
                    }
                    break;

               case 'previewmode':

                   self.disable_button(ip.controlbar.stopbutton);
                   self.enable_button(ip.controlbar.playbutton);
                   self.disable_button(ip.controlbar.pausebutton);
                   self.enable_button(ip.controlbar.savebutton);

                   //reset timer button
                   ip.controlbar.status.html(ip.timer.fetch_display_time(ip.timer.finalSeconds));


                   //If stop has been pressed there is no "resuming"
                   self.show_element(ip.controlbar.startbutton);

                   self.disable_button(ip.controlbar.resumebutton);
                   self.hide_element(ip.controlbar.resumebutton);
                   self.hide_element(ip.controlbar.bmr_progresscanvas);

                   if(!self.uploaded){
                       self.enable_button(ip.controlbar.startbutton);
                       self.enable_button(ip.controlbar.savebutton);
                   }

                   self.show_element(ip.controlbar.pausebutton);

                   ip.controlbar.preview.removeClass('poodll_recording');
                    ip.controlbar.status.removeClass('poodll_recording');


                    if(ip.config.mediatype=='audio'){
                        self.show_element(ip.controlbar.preview);
                    }
                   // ip.controlbar.status.addClass('hide');
					//ip.controlbar.bmr_progresscanvas.removeClass('hide');
					self.enable_button(ip.controlbar.playbutton);

                    break;

               case 'playmode':
                   ip.controlbar.status.html('00:00:00');
                   self.disable_button(ip.controlbar.playbutton);
                   self.enable_button(ip.controlbar.stopbutton);
                   self.disable_button(ip.controlbar.startbutton);
                   self.disable_button(ip.controlbar.resumebutton);
                   self.disable_button(ip.controlbar.playbutton);
                   self.show_element(ip.controlbar.bmr_progresscanvas);

                   //If play has been pressed there is no "resuming"
                   self.show_element(ip.controlbar.startbutton);
                   self.hide_element(ip.controlbar.resumebutton);

                   break;


               case 'pausedmode':

                   self.disable_button(ip.controlbar.pausebutton);
                   self.hide_element(ip.controlbar.startbutton);
                   self.show_element(ip.controlbar.resumebutton);
                   self.enable_button(ip.controlbar.resumebutton);
                   self.enable_button(ip.controlbar.savebutton);

                    ip.controlbar.preview.removeClass('poodll_recording');
                    ip.controlbar.status.removeClass('poodll_recording');
                    break;

               case 'uploadmode':
                   self.disable_button(ip.controlbar.savebutton);
                   self.disable_button(ip.controlbar.startbutton);
                   self.show_element(ip.controlbar.bmr_progresscanvas);
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
				var hideshowupload = ip.showupload ? '' : 'hide';
                var record_icon = mediatype=='video' ?  'fa-video-camera' : 'fa-microphone';
				var recorder_class = mediatype=='video' ?  'poodll_mediarecorder_video' : 'poodll_mediarecorder_audio';

                var controls ='<div class="poodll_mediarecorderholder_bmr poodll_videorecorderholder_bmr ' 
                	+ recorder_class + ' ' + size_class + '" id="holder_' + controlbarid + '">' ;
                	
                controls +='<div class="poodll_mediarecorderbox_bmr" id="' + controlbarid + '">' ;
                controls +='<audio style="display: none !important;" class="poodll-alert-recording" id="'+ controlbarid +'"><source src="https://poodll.com/wp-content/themes/Poodll-Theme/images/ding.mp3" type="audio/mpeg"></audio>' ;
                controls +='<div class="style-holder ' + skin_style + '">' ;
                controls +='<div class="poodll_statusholder_bmr" >' ;
                  controls += '<canvas class="bmr_range"></canvas>';
                  var status = this.fetch_status_bar('bmr');
                  controls += status;
                controls += '</div>';

                controls += preview,
				controls += '<div class="settingsicon" id="settingsicon_'+controlbarid+'"><button type="button" class="btn btn-info btn-lg" data-toggle="modal" data-target="#myModal"><i class="fa fa-cogs" aria-hidden="true"></i></button></div>';
                controls +=  '<button type="button" class="poodll_mediarecorder_button_bmr poodll_start-recording_bmr"><i class="fa ' + record_icon + '" aria-hidden="true"></i></button>';
                controls += ' <button type="button" class="poodll_mediarecorder_button_bmr poodll_resume-recording_bmr bmr_disabled hide" disabled><i class="fa ' + record_icon + '" aria-hidden="true"></i></button>';
                controls += '<button type="button" class="poodll_mediarecorder_button_bmr poodll_stop-recording_bmr bmr_disabled" disabled><i class="fa fa-stop" aria-hidden="true"></i></button>';
                controls += '<button type="button" class="poodll_mediarecorder_button_bmr poodll_pause-recording_bmr bmr_disabled" disabled><i class="fa fa-pause" aria-hidden="true"></i></button>';
                controls += ' <button type="button" class="poodll_mediarecorder_button_bmr poodll_play-recording_bmr bmr_disabled" disabled><i class="fa fa-play" aria-hidden="true"></i></button>';
                controls += '<button type="button" class="poodll_save-recording_bmr ' + hideshowupload + '" disabled>' + ss['recui_save'] +'</button>';
						controls += '</div>';
						controls += this.devsettings.fetch_dialogue_box();
                        controls += ip.errordialog.fetch_dialogue_box();
					controls += '</div>';		
                controls += '</div>';
                $(element).prepend(controls);
                var controlbar ={
					poodll_recording_alert: $('#' + controlbarid + ' .poodll-alert-recording'),
					bmr_progresscanvas: $('#' + controlbarid + ' .bmr_range'),
                    settingsdialog: $('#' + controlbarid + ' .poodll_dialogue_box_settings'),
                    errorsdialog: $('#' + controlbarid + ' .poodll_dialogue_box_errors'),
					settingsicon: $('#' + controlbarid + ' .settingsicon'),
                    status: $('#' + controlbarid + ' .poodll_status_bmr'),
                    preview: $('#' + controlbarid + ' .poodll_preview_bmr'),
                    startbutton: $('#' + controlbarid + ' .poodll_start-recording_bmr'),
                    stopbutton: $('#' + controlbarid + ' .poodll_stop-recording_bmr'),
                    pausebutton: $('#' + controlbarid + ' .poodll_pause-recording_bmr'),
                    resumebutton: $('#' + controlbarid + ' .poodll_resume-recording_bmr'),
                    playbutton: $('#' + controlbarid + ' .poodll_play-recording_bmr'),
                    savebutton: $('#' + controlbarid + ' .poodll_save-recording_bmr')    
                };


            //settings and error dialogs
            //They use the same dialog and just fill it with diofferent stuff
            //settings is on 'this' because it is shown from skkn events, but errors are from pmr stuff
            ip.errordialog.set_dialogue_box(controlbar.errorsdialog);
            this.devsettings.set_dialogue_box(controlbar.settingsdialog);
			return controlbar;

        }, //end of fetch_control_bar_bmr


        register_controlbar_events_video: function(onMediaSuccess,  controlbarid) {
            return this.register_controlbar_events_audio(onMediaSuccess, controlbarid);
        },

        register_controlbar_events_audio: function(onMediaSuccess, controlbarid){
            var self = this;
            var pmr=this.pmr;
            var ip = this.fetch_instanceprops(controlbarid);

			//Open the settings dialog
			ip.controlbar.settingsicon.click(function(){
				self.devsettings.open();
			});
			
			 //init progress bar
            var hprogress = anim_progress_bar.clone();
            hprogress.init(ip.controlbar.bmr_progresscanvas);

			
            ip.controlbar.startbutton.click(function() {
				
				//Play alert sound
				//ip.controlbar.poodll_recording_alert.get(0).play();

                //start recording
                pmr.do_start_audio(ip, onMediaSuccess);

            });
            
            ip.controlbar.stopbutton.click(function() {
                //stop the audio
                pmr.do_stop_audio(ip);

				var preview = ip.controlbar.preview;
                if(preview && preview.get(0)){
                    preview.get(0).pause();
                }

                //timer and status bar
                ip.timer.stop();
                self.update_status(controlbarid);

               //do visuals
               self.set_visual_mode('previewmode',controlbarid);

            });
          
            ip.controlbar.pausebutton.click(function() {

                pmr.do_pause_audio(ip);
                
                //timer and status bar
                ip.timer.pause();
                self.update_status(controlbarid);
                //do visuals
                self.set_visual_mode('pausedmode',controlbarid);
            });
            
            ip.controlbar.resumebutton.click(function() {

                pmr.do_resume_audio(ip);
                
                //timer and status bar
                ip.timer.resume();
                self.update_status(controlbarid);
                //do visuals
                self.set_visual_mode('recordmode',controlbarid);
            });

            ip.controlbar.preview.on('timeupdate', function(){
                if(self.visualmode!='playmode'){return;}
                var currentTime = this.currentTime;
                ip.controlbar.status.html(ip.timer.fetch_display_time(currentTime));
            });

            ip.controlbar.playbutton.click(function() {

                var preview = ip.controlbar.preview.get(0);
                pmr.do_play_audio(ip,preview);

                //do visuals
                self.set_visual_mode('playmode',controlbarid);

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

              if(ip.blobs && ip.blobs.length > 0){
                  self.set_visual_mode('uploadmode',controlbarid);
                  pmr.do_save_audio(ip);
                  self.uploaded = true;
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
                    preview.get(0).controls = false;
                }
            };
			
        }, //end of register_control_bar_events_bmr

        show_element: function(element){
            $(element).removeClass('hide');
            $(element).show();
        },
        hide_element: function(element){
            $(element).addClass('hide');
            $(element).hide();
        },
        enable_button: function(button){
            $(button).attr('disabled',false);
			$(button).removeClass('bmr_disabled');		
        },
        disable_button: function(button){
            $(button).attr('disabled',true);
			$(button).addClass('bmr_disabled');   
        },
		
    };//end of returned object
	
	
	
	
});//total end