/* jshint ignore:start */
define(['jquery','jqueryui','core/log','filter_poodll/utils_amd', 'filter_poodll/anim_progress_radial',
    'filter_poodll/anim_hwave','filter_poodll/anim_fbars','filter_poodll/anim_ripple',
    'filter_poodll/anim_words','filter_poodll/speech_poodll','filter_poodll/dlg_devicesettings'], function($,jqui, log, utils,radialprogress, hwave,fbars,ripple,words, speechrecognition,settings) {

    "use strict"; // jshint ;_;

    log.debug('PoodLL Gold Skin: initialising');

    return {
    
        instanceprops: null,
        pmr: null,
        devsettings: null,
        therecanim: null,

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
            var status = '<div class="poodll_status_' + skin + '" width="320" height="50">00:00:00</div>';
            return status;
        },
        
        fetch_preview_audio: function(skin){
            var preview = '<audio class="poodll_preview_' + skin + ' hide"></audio>';
            return preview;
        },
        fetch_preview_video: function(skin){
            var preview ='<video class="poodll_preview_' + skin + '" width="320" height="240"></video>';
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
            return false;
        },
        
        onMediaError: function(e) {
                console.error('media error', e);
        },

        onMediaSuccess_video: function(controlbarid){
            var ip = this.fetch_instanceprops(controlbarid);
            this.set_visual_mode('recordingmode',controlbarid);
        },

        onMediaSuccess_audio: function(controlbarid){
            var ip = this.fetch_instanceprops(controlbarid);
            ip.controlbar.preview.attr('src',null);

            //clear messages
            ip.uploader.Output('');

            //wave animation
            this.therecanim.start();

            //timer and status bar
            ip.timer.reset();
            ip.timer.start();
            this.update_status(controlbarid);

            //visuals
            this.set_visual_mode('recordingmode',controlbarid);
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

           switch(mode){

               case 'startmode':
                   self.enable_button(ip.controlbar.startbutton);
                   self.disable_button(ip.controlbar.playbutton);
                   self.disable_button(ip.controlbar.stopbutton);
                   //hide the save buttons if necessary
                   if(ip.showupload){
                       self.disable_button(ip.controlbar.savebutton);
                   }else{
                       ip.controlbar.savebutton.hide();
                   }

                   self.disable_button(ip.controlbar.restartbutton);
                   break;


               case 'recordingmode':
                   self.enable_button(ip.controlbar.stopbutton);
                   self.disable_button(ip.controlbar.startbutton);
                   self.disable_button(ip.controlbar.playbutton);
                   if(ip.showupload){
                       self.disable_button(ip.controlbar.savebutton);
                   }else{
                       ip.controlbar.savebutton.hide();
                   }
                   self.disable_button(ip.controlbar.restartbutton);



                   /*Added Feature for the one button recorder Video*/
                   if(ip.config.mediatype=='video'){
                       ip.controlbar.preview.removeClass('hide');
                   }
                   break;

               case 'previewmode':

                   if(ip.showupload){
                       //show save button
                       ip.controlbar.savebutton.removeClass('hide');
                       ip.controlbar.savebutton.removeClass('pmr_disabled');
                       ip.controlbar.savebutton.attr('disabled',false);
					   
				
                   }else{
                       ip.controlbar.savebutton.hide();
					   if(ip.config.mediatype=='video'){
						   ip.controlbar.preview.addClass('hide');
					   }
                   }

                   if(!ip.uploaded){
                       self.enable_button(ip.controlbar.startbutton);
                       self.enable_button(ip.controlbar.restartbutton);
                   }
                   self.enable_button(ip.controlbar.stopbutton);
                   self.enable_button(ip.controlbar.playbutton);

                    break;

               case 'playingmode':
                   self.enable_button(ip.controlbar.stopbutton);
				   if(ip.config.mediatype=='video'){
					    self.enable_button(ip.controlbar.preview);
						ip.controlbar.preview.removeClass('hide');
				   }
                  
                   self.disable_button(ip.controlbar.startbutton);
                   self.disable_button(ip.controlbar.playbutton);
                   self.disable_button(ip.controlbar.restartbutton);
                   break;

           }

       },

       //insert the control bar and return it to be reused
        insert_controlbar_video: function(element, controlbarid, preview, resource) {
            var controlbar = this.prepare_controlbar(element,controlbarid, preview, resource,'video');
        	return controlbar;
        },
        //insert the control bar and return it to be reused
        insert_controlbar_audio: function(element,controlbarid, preview, resource){
        	var controlbar = this.prepare_controlbar(element,controlbarid, preview, resource,'audio');
        	return controlbar;
        },
        
        //insert the control bar and return it to be reused
        prepare_controlbar: function(element,controlbarid, preview, resource, mediatype){
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

                var controls ='<div class="poodll_mediarecorderholder_gold ' 
                	+ recorder_class + '" id="holder_' + controlbarid + '">' ;
                	
                controls +='<div class="poodll_mediarecorderbox_gold" id="' + controlbarid + '">' ;
                controls += this.devsettings.fetch_dialogue_box();
                controls += ip.errordialog.fetch_dialogue_box();
                controls +='<div class="style-holder ' + skin_style + '">' ;
                var status = this.fetch_status_bar('gold');
                controls += status,
                controls += preview,
				controls += '<div class="settingsicon" id="settingsicon_'+controlbarid+'"><button type="button" class="btn btn-info btn-lg" data-toggle="modal" data-target="#myModal"><i class="fa fa-cogs" aria-hidden="true"></i></button></div>';
				controls += '<canvas id="' + controlbarid + '_playcanvas"> width="250" height="250"></canvas>';
                controls +=  '<button type="button" class="poodll_mediarecorder_button_gold poodll_start-recording_gold"><i class="fa fa-microphone" aria-hidden="true"></i></button>';
                controls += '<button type="button" class="poodll_mediarecorder_button_gold poodll_stop-recording_gold"><i class="fa fa-stop" aria-hidden="true"></i></button>';
                controls += '<div class="gold-save-button-wrapper"><a class="btn btn-primary poodll_save-recording_gold">Upload</a></div>';
                controls += ' <button type="button" class="poodll_mediarecorder_button_gold poodll_play-recording_gold"><i class="fa fa-play" aria-hidden="true"></i></button>';
                controls += '<div class="gold-restart-button-wrapper"><a class="btn btn-success poodll_restart_gold " >' + ss['recui_restart'] + '</a></div>';
                controls += '</div></div></div>';
                $(element).prepend(controls);
                var controlbar ={
                    settingsdialog: $('#' + controlbarid + ' .poodll_dialogue_box_settings'),
                    errorsdialog: $('#' + controlbarid + ' .poodll_dialogue_box_errors'),
					settingsicon: $('#' + controlbarid + ' .settingsicon'),
                    status: $('#' + controlbarid + ' .poodll_status_gold'),
                    preview: $('#' + controlbarid + ' .poodll_preview_gold'),
                    startbutton: $('#' + controlbarid + ' .poodll_start-recording_gold'),
                    stopbutton: $('#' + controlbarid + ' .poodll_stop-recording_gold'),
                    stopplayingbutton: $('#' + controlbarid + ' .poodll_stop-playing_gold'),
                    playbutton: $('#' + controlbarid + ' .poodll_play-recording_gold'),
                    savebutton: $('#' + controlbarid + ' .poodll_save-recording_gold'),
                    restartbutton: $('#' + controlbarid + ' .poodll_restart_gold'),
                    playcanvas: $('#' + controlbarid + '_playcanvas')    
                };
            //settings and error dialogs
            //They use the same dialog and just fill it with diofferent stuff
            //settings is on 'this' because it is shown from skkn events, but errors are from pmr stuff
            ip.errordialog.set_dialogue_box(controlbar.errorsdialog);
            this.devsettings.set_dialogue_box(controlbar.settingsdialog);

                return controlbar;
        }, //end of fetch_control_bar_gold


        register_controlbar_events_video: function(onMediaSuccess, controlbarid) {
            return this.register_controlbar_events_audio(onMediaSuccess,controlbarid);
        },

        register_controlbar_events_audio: function(onMediaSuccess,controlbarid){

            var self = this;
            var pmr=this.pmr;
            var ip = this.fetch_instanceprops(controlbarid);

            //set visual mode
            this.set_visual_mode('startmode',controlbarid);

            //init radial progress
            var rprogress = radialprogress.clone();
            rprogress.init(ip.controlbar.playcanvas);

            //init recording anim
            var recanim = null;
            switch(ip.config.recanim) {
                case 'hwave': recanim=hwave.clone();break;
                case 'fbars': recanim=fbars.clone();break;
                case 'ripple': recanim=ripple.clone();break;
                case 'words': recanim=words.clone();break;
                default: ip.config.recanim = 'ripple'; var recanim=ripple.clone();

            }
           // log.debug('recanim=' + ip.config.recanim);
            recanim.init(ip.audioanalyser,ip.controlbar.playcanvas.get(0));
			

			ip.controlbar.settingsicon.click(function(){
				self.devsettings.open();
			});


			/*Added Feature for the one button recorder Video */ 
				if(ip.config.mediatype=='video'){
					ip.controlbar.preview.addClass('hide');	
				}
				ip.controlbar.preview.click(function(){
					console.log('clicked preview');
					
					$(this).addClass('hide');
					$(ip.controlbar.stopbutton).trigger( "click" );
					
					if($(this).hasClass('recorded')){
						$(this).removeClass('recorded');	
					}else{
						$(this).addClass('recorded');
					}
					
				});
			/* */
			
			
			
			
            //Start button click
            ip.controlbar.startbutton.click(function() {
                //moved the true logic into onMediaSuccess
                //so we need to stash recanim to make it available
                self.therecanim = recanim;
                pmr.do_start_audio(ip,  onMediaSuccess);

            });
            
            //Restart link clicked
            ip.controlbar.restartbutton.click(function() {
                //visuals
                self.set_visual_mode('startmode',controlbarid);
            });

			//Stop button click
            ip.controlbar.stopbutton.click(function() {

                //stop recording
                pmr.do_stop_audio(ip);

                //stop previewing (if that is what we are doing)
                var preview = ip.controlbar.preview.get(0);
                preview.pause();

                //wave animation
                recanim.clear();

                //radial progress clear
                rprogress.stop();

                //timer and status bar
               ip.timer.stop()
               self.update_status(controlbarid);

                //set visuals
                self.set_visual_mode('previewmode',controlbarid);

            });
			
			//Play button click
            ip.controlbar.playbutton.click(function() {
				
				if(ip.config.mediatype=='video'){
					if($(ip.controlbar.preview).hasClass('recorder')){
						$(ip.controlbar.preview).removeClass('recorder');
						$(ip.controlbar.preview).removeClass('hide');
						//ip.controlbar.preview.show();	
					}
				}
				

                //commence playback
                var preview = ip.controlbar.preview.get(0);
                pmr.do_play_audio(ip,preview);

                //init and run radial progress animation
                rprogress.clear();
                rprogress.fetchCurrent=function(){
                    var ct = ip.controlbar.preview.prop('currentTime');
                    var duration = ip.controlbar.preview.prop('duration');
                    if(!isFinite(duration)){duration=ip.timer.finalseconds;}
                    return ct/duration;
                };
                rprogress.start();

                //set visuals
                self.set_visual_mode('playingmode',controlbarid);

            });
            
            //Save button click
           ip.controlbar.savebutton.click(function() {


              if(ip.blobs && ip.blobs.length > 0){
                  pmr.do_save_audio(ip);
                  ip.uploaded = true;
                  self.disable_button(ip.controlbar.startbutton);
                }else{
                    ip.uploader.Output(M.util.get_string('recui_nothingtosaveerror','filter_poodll'));
                }//end of if self.blobs

               //set visuals
               self.set_visual_mode('previewmode',controlbarid);

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
        }, //end of register_control_bar_events_gold

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
