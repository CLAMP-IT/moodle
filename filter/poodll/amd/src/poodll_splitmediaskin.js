/* jshint ignore:start */
define(['jquery','core/log','filter_poodll/utils_amd','filter_poodll/upskin_bar'], function($, log, utils, upskin_bar) {

    "use strict"; // jshint ;_;

    log.debug('PoodLL split Skin: initialising');

    return {
    
        instanceprops: null,
        pmr: null,
        uploaded: false,
        recorded: false,
        played: false,
        mustResetResourcePlayer : false,

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
            // $('#' + controlbarid  + '_messages').hide();
             $('#' + controlbarid + ' > .poodll_savedsuccessfully').show();
        },
        
        onUploadFailure: function(controlbarid){
            return;
        },		

        fetch_status_bar: function(skin){
            var status = '<div class="poodll_status_' + skin + '"></div>';
            return status;
        },
        
        fetch_preview_audio: function(skin){
            var checkplayer = '<audio class="poodll_checkplayer_' + skin + ' hide" controls></audio>';
            return checkplayer;
        },
        fetch_preview_video: function(skin){
            var checkplayer ='<video class="poodll_checkplayer_' + skin + '" width="320" height="240"></video>';
            return checkplayer;
        },
        fetch_resource_audio: function(skin){
            var resourceplayer = '<audio class="poodll_resourceplayer_' + skin + ' hide" src="@@RESOURCEURL@@" playsinline controls></audio>';
            return resourceplayer;
        },
        fetch_resource_video: function(skin){
            var resourceplayer = '<video class="poodll_resourceplayer_' + skin + ' hide" src="@@RESOURCEURL@@" ></video>';
            return resourceplayer;
        },
        fetch_uploader_skin: function(controlbarid, element){
            var ip = this.fetch_instanceprops(controlbarid);
            var upskin = upskin_bar.clone();
            upskin.init(ip.config,element,ip.controlbar.split_progresscanvas,ip.controlbar.status);
            return upskin;
        },
        onMediaError: function(e) {
                console.error('media error', e);
        },

        onMediaSuccess_video: function(controlbarid){
            var ip = this.fetch_instanceprops(controlbarid);
            ip.controlbar.stopbutton.attr('disabled',false);
            ip.controlbar.savebutton.attr('disabled',false);
        },

        onMediaSuccess_audio: function(controlbarid){
            var ip = this.fetch_instanceprops(controlbarid);
            ip.controlbar.checkplayer.attr('src',null);
            ip.controlbar.stopbutton.attr('disabled',false);;
            ip.controlbar.savebutton.attr('disabled',false);
        },

        handle_timer_update: function(controlbarid){
            if(!this.played && !this.recorded){return;}
            var ip = this.fetch_instanceprops(controlbarid);
            var recordingstring = M.util.get_string('recui_recording','filter_poodll');
            this.update_status(controlbarid, recordingstring + ip.timer.fetch_display_time());
            if(ip.timer.seconds==0 && ip.timer.initseconds >0){
                 ip.controlbar.stopbutton.click();
            }
        },

       update_status: function(controlbarid, text){
            var ip = this.fetch_instanceprops(controlbarid);
            ip.controlbar.status.html(text);
        },

        //set visuals for different states (ie recording or playing)
        set_visual_mode: function(mode, ip){
            var self = this;

           switch(mode) {

               case 'recordingmode':
                   self.disable_button(ip.controlbar.resourceplaybutton);
                   ip.controlbar.resourceplaybutton.empty();
                   ip.controlbar.resourceplaybutton.html('<span class="fa fa-microphone fa-3x"></span>');
                   ip.controlbar.resourceplaybutton.addClass('poodll_mediarecorderholder_split_recordcolor');
                   ip.controlbar.status.addClass('poodll_mediarecorderholder_split_recordcolor');
                   self.handle_timer_update(ip.controlbarid);
                   self.enable_button(ip.controlbar.stopbutton);
                   break;

               case 'resourceplayingmode':
                   self.disable_button(ip.controlbar.resourceplaybutton);
                   self.disable_button(ip.controlbar.stopbutton);
                   break;


               case 'neverrecordedmode':
                   ip.controlbar.resourceplaybutton.empty();
                   ip.controlbar.resourceplaybutton.html('<p style="margin-bottom: 0px; font-size: 24px;">Start</p>');

                   self.enable_button(ip.controlbar.resourceplaybutton);
                   self.disable_button(ip.controlbar.stopbutton);

                   this.update_status(ip.controlbarid,M.util.get_string('recui_ready','filter_poodll'));
                       break;
                    
              case 'allstoppedmode':
                  self.disable_button(ip.controlbar.resourceplaybutton);
                  self.disable_button(ip.controlbar.stopbutton);
                  ip.controlbar.resourceplaybutton.empty();
                  ip.controlbar.resourceplaybutton.html('<span class="fa fa-check fa-3x"></span>');
                  ip.controlbar.resourceplaybutton.removeClass('poodll_mediarecorderholder_split_recordcolor');
                  ip.controlbar.status.removeClass('poodll_mediarecorderholder_split_recordcolor');
                  this.update_status(ip.controlbarid,M.util.get_string('recui_finished','filter_poodll'));
                break;
           }

       },

        //insert the control bar and return it to be reused
        insert_controlbar_video: function(element, controlbarid, checkplayer,resourceplayer) {
            var controlbar = this.prepare_controlbar(element,controlbarid, checkplayer,resourceplayer,'video');
        	return controlbar;
        },
        //insert the control bar and return it to be reused
        insert_controlbar_audio: function(element,controlbarid, checkplayer,resourceplayer){
        	var controlbar = this.prepare_controlbar(element,controlbarid, checkplayer,resourceplayer,'audio');
        	return controlbar;
        },
        
        //insert the control bar and return it to be reused
        prepare_controlbar: function(element,controlbarid, checkplayer,resourceplayer, mediatype){
                var ip = this.fetch_instanceprops(controlbarid);
                var skin_style = ip.config.media_skin_style;
                
                var recorder_class = mediatype=='video' ?  'poodll_mediarecorder_video' : 'poodll_mediarecorder_audio';

                //load resource player with the src of the resource audio (or video ...never)
                resourceplayer = resourceplayer.replace('@@RESOURCEURL@@', ip.config.resource);



				var ss = this.pmr.fetch_strings();
                var controls ='<div class="poodll_mediarecorderholder_split '
                	+ recorder_class + '" id="holder_' + controlbarid + '">' ;
                	
                controls +='<div class="poodll_mediarecorderbox_split" id="' + controlbarid + '">' ;
                controls +='<div class="style-holder ' + skin_style + '">' ;

                controls += checkplayer,
                controls += resourceplayer,



                //this is never displayed
                controls +=  '<button type="button" class="poodll_mediarecorder_button_split poodll_start-recording_split hide">'
 				+ '<span class="fa fa-microphone fa-3x"></span>'
                +  '</button>';

                //this is never displayed
                controls += ' <button type="button" class="poodll_mediarecorder_button_split poodll_playback-recording_split hide">'
                + '<span class="fa fa-play-circle fa-3x"></span>'
				+ '</button>';

                //this is never displayed
                controls += ' <button type="button" class="poodll_mediarecorder_button_split poodll_stopplayback-recording_split hide">'
                + '<span class="fa fa-stop-circle fa-3x"></span>'
				+ '</button>';

            //this is never displayed
            controls += '<button type="button" class="poodll_save-recording_split pmr_disabled hide">' + ss['recui_save'] +  '</button></div>';

            //this IS displayed
            controls += '<div class="poodll_mediarecorderholder_split"><button type="button" class="poodll_mediarecorder_button_split poodll_stop-recording_split pmr_disabled" disabled>'
                + '<p style="margin-bottom: 0px;font-size: 24px;">Submit</p>'
                + '</button></div>';

            controls +='<div class="poodll_statusholder_split" >' ;
            controls += '<canvas class="split_range"></canvas>';
            var statushtml = this.fetch_status_bar('split');
            controls += statushtml;
            controls += '</div>';


            //end if div
            controls += '</div></div></div>';

            // add to the page
            $(element).append(controls);


            //play button
            var playbuttonhtml =  '<button type="button" class="poodll_mediarecorder_button_split poodll_play-resource_split">'
            + '<p style="margin-bottom: 0px; font-size: 24px;">Start</p></button>';
            var topsplit='<div class="poodll_mediarecorder_split_topsplit">' + playbuttonhtml + '</div>';

            //divider
            var divider = "<hr />";

            //prepend the top split
            $('.qtext').prepend(divider);
            $('.qtext').prepend(topsplit);


                var controlbar ={
                    split_progresscanvas: $('#' + controlbarid + ' .split_range'),
					marker:  $('#' + controlbarid + '  .marker'),
                    resourceplayer: $('#' + controlbarid + '  .poodll_resourceplayer_split'),
                    checkplayer: $('#' + controlbarid + '  .poodll_checkplayer_split'),
                    stopbutton: $('#' + controlbarid + '  .poodll_stop-recording_split'),
                    resourcestopbutton: $('#' + controlbarid + '  .poodll_stop-resource_split'),
                    startbutton: $('#' + controlbarid + '  .poodll_start-recording_split'),
                    playbackbutton: $('#' + controlbarid + '  .poodll_playback-recording_split'),
                    stopplaybackbutton: $('#' + controlbarid + '  .poodll_stopplayback-recording_split'),
                    savebutton: $('#' + controlbarid + '  .poodll_save-recording_split'),


                    //these are actually outside the control bar above the question text
                    resourceplaybutton: $('.poodll_mediarecorder_button_split.poodll_play-resource_split'),
                    status: $('.poodll_status_split'),
                };
                return controlbar;
        }, //end of fetch_control_bar_split


        register_controlbar_events_video: function(onMediaSuccess, controlbarid) {
            return this.register_controlbar_events_audio(onMediaSuccess,  controlbarid);
        },

        do_callback: function(args){
            //log.debug(args);
            //args will look like this
            /*
            0:"recorderbase5a367e03c2f9319"
            1:"filesubmitted"
            2:"poodllfile5a367e03c2f9318.mp3"
            3:"q101:1_answer_id"
            */
            switch(args[1]){

                case 'filesubmitted':
                    //record the url on the html page,
                    var filenamecontrol = document.getElementById(args[3]);
                    if(filenamecontrol==null){ filenamecontrol = parent.document.getElementById(args[3]);}
                    if(filenamecontrol){
                        filenamecontrol.value = args[2];
                    }

                    //enable the next button
                    $('#responseform input[name=next]').attr('disabled',false);
                   //we used to click it. But client doesn't want that
                   // $('#responseform input[name=next]').click();
            }
        },



        register_controlbar_events_audio: function(onMediaSuccess, controlbarid){

            var self = this;
            var pmr=this.pmr;
            var ip = this.fetch_instanceprops(controlbarid);

            this.set_visual_mode('neverrecordedmode',ip);
            ip.config.callbackjs= self.do_callback;

            //disable the forms next button.
            //its not a recorder button so we do not use our disable_button method here
            $('#responseform input[name=next]').attr('disabled',true);


			
	
			
			function poodll_resource_play(count_down) {   
				var cd;
                var playingstring = M.util.get_string('recui_playing','filter_poodll');
                self.update_status(controlbarid, playingstring + ip.timer.fetch_display_time(count_down));
				cd = setInterval(function() {
					count_down--;
					if(count_down < 0) {
						clearInterval(cd); 
					} else {
                        self.update_status(controlbarid, playingstring + ip.timer.fetch_display_time(count_down));
					}
				}, 1000);   
			}        
			
			/*
			//this code was for the case when the recording should start AFTER the playback ends
			ip.controlbar.resourceplayer.on('ended', function() {
				ip.controlbar.startbutton.trigger( "click" );
			});
			*/


            ip.controlbar.startbutton.click(function() {

                pmr.do_start_audio(ip, onMediaSuccess);
                self.recorded = true;

                //recording timer setup
                ip.timer.reset();
                ip.timer.start();

                self.set_visual_mode('recordingmode',ip);
				
				
            });
            
            ip.controlbar.stopbutton.click(function() {
                //stop playing
                self.do_stop_resource(ip);
				//stop recording
                pmr.do_stop_audio(ip);
                self.disable_button(this);

               //click the 'save' button
                //this timeout is ugly but we just need a few ms for the blobs to arrive
                setTimeout(function() {
                    ip.controlbar.savebutton.click();
                },100);


                //recording timer
                ip.timer.stop();
                self.set_visual_mode('allstoppedmode',ip);


            });


            ip.controlbar.resourceplaybutton.click(function(){
				
                //we do not want to start the timer or get going if recording is off limits
                //so we first call getUserMedia, and force a permissions check
                navigator.mediaDevices.getUserMedia({"audio": true}).then(function(stream){
                   ip.controlbar.startbutton.trigger( "click" );

                    //flag played
                    self.played = true;


                    if (ip.config.resource != '') {
                        self.do_play_resource(ip);
                    }


                }).catch(function(err) {
                    alert(err);
                });


            });
            
             ip.controlbar.resourcestopbutton.click(function(){
				self.do_stop_resource(ip); 
                
               //do visuals
               if(self.recorded){
                  self.set_visual_mode('allstoppedmode',ip);
                }else{
                  self.set_visual_mode('neverrecordedmode',ip);
                }
                
            });

           ip.controlbar.savebutton.click(function() {
              self.disable_button(this);
              if(ip.blobs && ip.blobs.length > 0){
                  pmr.do_save_audio(ip);
                  self.uploaded = true;
                  self.disable_button(ip.controlbar.startbutton);
                }else{
                    ip.uploader.Output(M.util.get_string('recui_nothingtosaveerror','filter_poodll'));
                }//end of if self.blobs		
                //probably not necessary  ... but getting odd ajax errors occasionally
                return false;
            });//end of save recording

            
        }, //end of register_control_bar_events_split
        

        set_completion: function(completed,ip){
            /*On hold for now Justin 20171007 */
            return;

            var completioncheck = ip.controlbar.completioncheck;
            if(completed){
                completioncheck.removeClass('fa-circle');
                completioncheck.addClass('fa-check-circle');
            }else{
                completioncheck.removeClass('fa-check-circle');
                completioncheck.addClass('fa-circle');
            }
        },

        //DO stop playing the resource
        do_stop_resource: function(ip){
        	console.log('stopped the resource');
        	var resourceplayer = ip.controlbar.resourceplayer.get(0);
            resourceplayer.pause();
            resourceplayer.currentTime=0;
            
             if(ip.mustResetResourcePlayer){
                ip.mustResetResourcePlayer = false;
                resourceplayer.src=ip.config.resource;                
                var ppromise = resourceplayer.load(); 
                /* 
				// playPromise won’t be defined.
				if (ppromise !== undefined) {
					ppromise.then(function() {resourceplayer.pause();});
				}else{
					resourceplayer.oncanplay(resourceplayer.pause());
				}
				*/
			}
		},
        
        
        //do the play of resource
        do_play_resource: function(ip){
        	//if was used to play recording, we need to reset it
                var resourceplayer = ip.controlbar.resourceplayer.get(0);
                resourceplayer.play();
                resourceplayer.currentTime = 0;
        },

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
