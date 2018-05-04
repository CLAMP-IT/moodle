/* jshint ignore:start */
define(['jquery','core/log','filter_poodll/utils_amd'], function($, log, utils) {

    "use strict"; // jshint ;_;

    log.debug('PoodLL Burnt Rose Skin: initialising');

    return {
    
        instanceprops: null,
        pmr: null,

        //for making multiple instances
        clone: function(){
            return $.extend(true,{},this);
        },

        init: function(ip, pmr, controlbarid){
            this.instanceprops=ip;
            this.pmr=pmr;
        },

        fetch_instanceprops : function(){
            return this.instanceprops;
        },


        onUploadSuccess: function(widgetid){
             log.debug('from poodllmediarecorder: uploadsuccess');
             var controlbarid = 'filter_poodll_controlbar_' + widgetid;
             $('#' + controlbarid + ' > .poodll_save-recording').hide();
             $('#' + controlbarid + ' > .poodll_savedsuccessfully').show();
        },
        
        onUploadFailure: function(controlbarid){
            log.debug('from poodllmediarecorder: uploadfailure');
        },		

        fetch_status_bar: function(skin){
            var status = '<div class="poodll_status_' + skin + '" width="320" height="50">00:00:00</div>';
            return status;
        },
        
        fetch_preview_audio: function(skin){
            var preview = '<audio class="poodll_preview_' + skin + ' hide" controls></audio>';
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

        onMediaError: function(e) {
                console.error('media error', e);
        },

        onMediaSuccess_video: function(controlbarid){
            var ip = this.fetch_instanceprops(controlbarid);
            ip.controlbar.stopbutton.attr('disabled',false);
            ip.controlbar.pausebutton.attr('disabled',false);
            ip.controlbar.savebutton.attr('disabled',false);
        },

        onMediaSuccess_audio: function(controlbarid){
            var ip = this.fetch_instanceprops(controlbarid);
            ip.controlbar.preview.attr('src',null);
            ip.controlbar.stopbutton.attr('disabled',false);
            ip.controlbar.pausebutton.attr('disabled',false);
            ip.controlbar.savebutton.attr('disabled',false);
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

        //
        // BURNTROSE SKIN
        //
        //

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
                    break;

               case 'pausedmode':
                    ip.controlbar.preview.removeClass('poodll_recording');
                    ip.controlbar.status.removeClass('poodll_recording');
                    break;
           }

       },

        
        //insert the control bar and return it to be reused
        insert_controlbar_audio: function(element,controlbarid, preview,resource){
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
			
                var controls ='<div class="poodll_mediarecorderbox_burntrose ' + skin_style + ' ' + size_class + '" id="' + controlbarid + '">' ;
                var status = this.fetch_status_bar('burntrose');
                controls += status,
                controls += preview,
                controls +=  '<span class="poodll_start-recording" title="' + M.util.get_string('recui_record', 'filter_poodll') + '"></span>';
                controls += '<span class="poodll_stop-recording hide" title="' + M.util.get_string('recui_stop', 'filter_poodll') + '"></span>';
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
                    status: $('#' + controlbarid + ' .poodll_status_burntrose'),
                    preview: $('#' + controlbarid + ' .poodll_preview_burntrose'),
                    startbutton: $('#' + controlbarid + ' .poodll_start-recording'),
                    stopbutton: $('#' + controlbarid + '  .poodll_stop-recording'),
                    pausebutton: $('#' + controlbarid + ' .poodll_pause-recording'),
                    resumebutton: $('#' + controlbarid + ' .poodll_resume-recording'),
                    play1: $('#' + controlbarid + ' .poodll_play-recording'),
                    playbutton: $('#' + controlbarid + ' > .poodll_playsave'),
                    save1: $('#' + controlbarid + ' .poodll_savebtn'),
                    savebutton: $('#' + controlbarid + ' .poodll_save-recording_burntrose'),
                    savesuccess: $('#' + controlbarid + ' .poodll_savedsuccessfully'),

                    playermic: $('#'+controlbarid + ' .poodll_mic'),
                    recordmic: $('#'+controlbarid + ' .poodll_recmic'),
                    resumemic: $('#'+controlbarid + ' .poodll_resume_mic')
                };
                return controlbar;
        }, //end of fetch_control_bar_burntrose,
        
        //insert the control bar and return it to be reused
        insert_controlbar_video: function(element,controlbarid, preview, resource){
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

            var controls ='<div class="poodll_mediavideobox '+ skin_style + ' ' + size_class + '" id="' + controlbarid + '">' ;
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

        register_controlbar_events_video: function(onMediaSuccess, controlbarid) {
              return  this.register_controlbar_events_audio(onMediaSuccess, controlbarid);
        },

        register_controlbar_events_audio: function(onMediaSuccess, controlbarid){
            var self = this;
            var pmr = this.pmr;
            var ip = this.fetch_instanceprops(controlbarid);
            
             ip.controlbar.startbutton.click(function() {
                this.disabled = false;
                
                //clear messages
                $('#' + ip.config.widgetid  + '_messages').text('');

                 pmr.do_start_audio(ip,  onMediaSuccess);

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



                pmr.do_stop_audio(ip);
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
                pmr.do_pause_audio(ip);
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
                pmr.do_resume_audio(ip);
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

                pmr.do_play_audio(ip,preview);
                
                ip.controlbar.startbutton.show();
            });
            
           ip.controlbar.savebutton.click(function() {
                this.disabled = false;

              if(ip.blobs && ip.blobs.length > 0){
                  pmr.do_save_audio(ip);
                  ip.uploaded = true;
                  ip.controlbar.startbutton.attr('disabled',true);
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



