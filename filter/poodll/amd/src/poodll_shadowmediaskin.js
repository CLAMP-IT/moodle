/* jshint ignore:start */
define(['jquery','core/log','filter_poodll/utils_amd'], function($, log, utils) {

    "use strict"; // jshint ;_;

    log.debug('PoodLL Shadow Skin: initialising');

    return {
    
        instanceprops: null,
        pmr: null,
        uploaded: false,
        recorded: false,
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
            return false;
        },
        onMediaError: function(e) {
                console.error('media error', e);
        },

        onMediaSuccess_video: function(controlbarid){
            var ip = this.fetch_instanceprops(controlbarid);

            this.recorded = true;

            //also start the model audio playback
            this.do_play_resource(ip);
            ip.controlbar.resourceplayer.bind('ended',function(){
                ip.controlbar.stopbutton.click();
            });

            self.set_visual_mode('recordingmode',ip);
            /*
            ip.controlbar.stopbutton.attr('disabled',false);
            ip.controlbar.savebutton.attr('disabled',false);
            */
        },

        onMediaSuccess_audio: function(controlbarid){
            var ip = this.fetch_instanceprops(controlbarid);
            ip.controlbar.checkplayer.attr('src',null);


            this.recorded = true;

            //also start the model audio playback
            this.do_play_resource(ip);
            ip.controlbar.resourceplayer.bind('ended',function(){
                ip.controlbar.stopbutton.click();
            });

            self.set_visual_mode('recordingmode',ip);
            /*
            ip.controlbar.stopbutton.attr('disabled',false);;
            ip.controlbar.savebutton.attr('disabled',false);
            */
        },

        handle_timer_update: function(controlbarid){
            var ip = this.fetch_instanceprops(controlbarid);
            this.update_status(controlbarid);
            if(ip.timer.seconds==0 && ip.timer.initseconds >0){
                 ip.controlbar.stopbutton.click();
            }
        },

       update_status: function(controlbarid){
            /*
            var ip = this.fetch_instanceprops(controlbarid);
            ip.controlbar.status.html(ip.timer.fetch_display_time());
            */
        },

        //set visuals for different states (ie recording or playing)
        set_visual_mode: function(mode, ip){
            var self = this;

           switch(mode){

               case 'recordingmode':
               //hide  buttons
              	self.disable_button(ip.controlbar.startbutton);
                ip.controlbar.startbutton.hide();
                 self.disable_button(ip.controlbar.stopplaybackbutton);
                ip.controlbar.stopplaybackbutton.hide();
                self.disable_button(ip.controlbar.resourcestopbutton);
                ip.controlbar.resourcestopbutton.hide();
                  
                
                //show  buttons  
                self.enable_button(ip.controlbar.stopbutton);
                ip.controlbar.stopbutton.show();
                self.disable_button(ip.controlbar.playbackbutton);
                ip.controlbar.playbackbutton.show();
                self.disable_button(ip.controlbar.resourceplaybutton);
                ip.controlbar.resourceplaybutton.show();
               
                break;

               case 'resourceplayingmode':
               //hide  buttons
              	self.disable_button(ip.controlbar.stopbutton);
                ip.controlbar.stopbutton.hide();
                 self.disable_button(ip.controlbar.playbackbutton);
                ip.controlbar.playbackbutton.hide();
                self.disable_button(ip.controlbar.resourceplaybutton);
                ip.controlbar.resourceplaybutton.hide(); 
                
                //show buttons  
                self.disable_button(ip.controlbar.startbutton);
                ip.controlbar.startbutton.show();
                self.disable_button(ip.controlbar.stopplaybackbutton);
                ip.controlbar.stopplaybackbutton.show();
                self.enable_button(ip.controlbar.resourcestopbutton);
                ip.controlbar.resourcestopbutton.show();
                break;

               case 'playingbackmode':
               	//hide  buttons
              	self.disable_button(ip.controlbar.stopbutton);
                ip.controlbar.stopbutton.hide();
                 self.disable_button(ip.controlbar.playbackbutton);
                ip.controlbar.playbackbutton.hide();
                self.disable_button(ip.controlbar.resourcestopbutton);
                ip.controlbar.resourcestopbutton.hide();
                
                //show buttons  
                self.disable_button(ip.controlbar.startbutton);
                ip.controlbar.startbutton.show();
                self.enable_button(ip.controlbar.stopplaybackbutton);
                ip.controlbar.stopplaybackbutton.show();
                self.disable_button(ip.controlbar.resourceplaybutton);
                ip.controlbar.resourceplaybutton.show(); 
                break;
            
               case 'neverrecordedmode':
               	//hide buttons
              	self.disable_button(ip.controlbar.stopbutton);
                ip.controlbar.stopbutton.hide();
                self.disable_button(ip.controlbar.stopplaybackbutton);
                ip.controlbar.stopplaybackbutton.hide();
                self.disable_button(ip.controlbar.resourcestopbutton);
                ip.controlbar.resourcestopbutton.hide();
              
              //show  buttons  
                self.enable_button(ip.controlbar.startbutton);
                ip.controlbar.startbutton.show();
                self.disable_button(ip.controlbar.playbackbutton);
                ip.controlbar.playbackbutton.show();
                self.enable_button(ip.controlbar.resourceplaybutton);
                ip.controlbar.resourceplaybutton.show(); 
                    break;
                    
              case 'allstoppedmode':
              //hide buttons
              	self.disable_button(ip.controlbar.stopbutton);
                ip.controlbar.stopbutton.hide();
                self.disable_button(ip.controlbar.stopplaybackbutton);
                ip.controlbar.stopplaybackbutton.hide();
                self.disable_button(ip.controlbar.resourcestopbutton);
                ip.controlbar.resourcestopbutton.hide();
              
              //show  buttons  
                self.enable_button(ip.controlbar.startbutton);
                ip.controlbar.startbutton.show();
                self.enable_button(ip.controlbar.playbackbutton);
                ip.controlbar.playbackbutton.show();
                self.enable_button(ip.controlbar.resourceplaybutton);
                ip.controlbar.resourceplaybutton.show();    
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
                var controls ='<div class="poodll_mediarecorderholder_shadow '
                	+ recorder_class + ' ' + size_class + '" id="holder_' + controlbarid + '">' ;
                	
                controls +='<div class="poodll_mediarecorderbox_shadow" id="' + controlbarid + '">' ;
                controls +='<div class="style-holder ' + skin_style + '">' ;
                var status = this.fetch_status_bar('shadow');
                controls += status,
                controls += checkplayer,
                controls += resourceplayer,
                
                controls +=  '<button type="button" class="poodll_mediarecorder_button_shadow poodll_play-resource_shadow">' 
                + '<span class="fa fa-play-circle fa-4x"></span>' 
				+ '</button>';
               
                controls +=  '<button type="button" class="poodll_mediarecorder_button_shadow poodll_stop-resource_shadow  hide">' 
                + '<span class="fa fa-stop-circle fa-4x"></span>' 
				+ '</button>';
                
                controls +=  '<button type="button" class="poodll_mediarecorder_button_shadow poodll_start-recording_shadow">'
                + '<span class="fa fa-play-circle fa-4x"></span>' 
				+ '<hr style="color: white">'
 				+ '<span class="fa fa-microphone fa-4x"></span>' 
                +  '</button>';
                
                controls += '<button type="button" class="poodll_mediarecorder_button_shadow poodll_stop-recording_shadow pmr_disabled hide" disabled>'
                + '<span class="fa fa-stop-circle fa-4x"></span>' 
				+ '</button>';
				
                controls += ' <button type="button" class="poodll_mediarecorder_button_shadow poodll_playback-recording_shadow pmr_disabled" disabled>' 
                + '<span class="fa fa-play-circle fa-4x"></span>' 
				+ '</button>';
				
                controls += ' <button type="button" class="poodll_mediarecorder_button_shadow poodll_stopplayback-recording_shadow hide">'
                + '<span class="fa fa-stop-circle fa-4x"></span>' 
				+ '</button>';

                //completioncheck /*On hold for now Justin 20171007 */
               // controls += ' <div class="poodll_mediarecorder_completion_shadow fa fa-circle fa-4x"></div>';
				
                //controls += '<button type="button" class="poodll_save-recording_shadow pmr_disabled disabled hide>' + ss['recui_save'] +  '</button>';
				
				controls += '<div class="marker hide"><i class="fa fa-check" aria-hidden="true"></i></div>';
                controls += '</div></div></div>';
                $(element).prepend(controls);

                var controlbar ={
					marker:  $('#' + controlbarid + '  .marker'),
                    status: $('#' + controlbarid + '  .poodll_status_shadow'),
                    resourceplayer: $('#' + controlbarid + '  .poodll_resourceplayer_shadow'),
                    checkplayer: $('#' + controlbarid + '  .poodll_checkplayer_shadow'),
                    resourceplaybutton: $('#' + controlbarid + '  .poodll_play-resource_shadow'),
                    resourcestopbutton: $('#' + controlbarid + '  .poodll_stop-resource_shadow'),
                    startbutton: $('#' + controlbarid + '  .poodll_start-recording_shadow'),
                    stopbutton: $('#' + controlbarid + '  .poodll_stop-recording_shadow'),
                    playbackbutton: $('#' + controlbarid + '  .poodll_playback-recording_shadow'),
                    stopplaybackbutton: $('#' + controlbarid + '  .poodll_stopplayback-recording_shadow'),
                    /*On hold for now Justin 20171007 */
                    //completioncheck: $('#' + controlbarid + '  .poodll_mediarecorder_completion_shadow'),
                    savebutton: $('#' + controlbarid + '  .poodll_save-recording_shadow')
                };
                return controlbar;
        }, //end of fetch_control_bar_shadow


        register_controlbar_events_video: function(onMediaSuccess, controlbarid) {
            return this.register_controlbar_events_audio(onMediaSuccess,  controlbarid);
        },

        register_controlbar_events_audio: function(onMediaSuccess, controlbarid){
            var self = this;
            var pmr=this.pmr;
            var ip = this.fetch_instanceprops(controlbarid);

            ip.controlbar.startbutton.click(function() {

                pmr.do_start_audio(ip, onMediaSuccess);

            });
            
            ip.controlbar.stopbutton.click(function() {
				 pmr.do_stop_audio(ip);
                self.disable_button(this);
                var preview = ip.controlbar.resourceplayer;
                if(preview && preview.get(0)){
                    preview.get(0).pause();
                }
  
              	self.set_visual_mode('allstoppedmode',ip);
               
               //stop model playback
               self.do_stop_resource(ip);


            });

            ip.controlbar.playbackbutton.click(function() {				
                self.disable_button(this);
				
				
				if(ip.controlbar.marker.hasClass('hide')){
					ip.controlbar.marker.removeClass('hide');
					console.log('check mark shown');
				}
                
                var resourceplayer = ip.controlbar.resourceplayer.get(0);
                pmr.do_play_audio(ip,resourceplayer);
                //flag resource player as needing reset
                ip.mustResetResourcePlayer = true;
 
                ////reset buttons when finished
                 ip.controlbar.resourceplayer.bind('ended',function(){
					self.set_visual_mode('allstoppedmode',ip);
            	 });
            	 
            	 //do visuals
                 self.set_visual_mode('playingbackmode',ip);

                 //complete the 'activity'
                self.set_completion(true,ip);

            });
            
             ip.controlbar.stopplaybackbutton.click(function(){

				self.do_stop_resource(ip); 
				                
                //do visuals
                 self.set_visual_mode('allstoppedmode',ip);
    
            });

            ip.controlbar.resourceplaybutton.click(function(){
				
				/*GLEN*/
				/*
				$(this).removeClass('shadow-active');
				$('.poodll_stop-resource_shadow').addClass('shadow-active');
				*/
				
				//actually play resource (or recording)
				self.do_play_resource(ip);
				
				////reset buttons when finished
                 ip.controlbar.resourceplayer.bind('ended',function(){
					 if(self.recorded){
                  		self.set_visual_mode('allstoppedmode',ip);
                	}else{
                  		self.set_visual_mode('neverrecordedmode',ip);
                	}
            	 });
				
                //do visuals
                 self.set_visual_mode('resourceplayingmode',ip);
                
            });
            
             ip.controlbar.resourcestopbutton.click(function(){
				 
				/* GLEN */ 
				//console.log('clicked');
				/*
				$(this).removeClass('shadow-active');
				$('.poodll_start-recording_shadow').addClass('shadow-active');
				*/
				
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
            
            window.onbeforeunload = function() {
                self.enable_button(ip.controlbar.startbutton);
                var checkplayer = ip.controlbar.checkplayer;
                if(checkplayer && checkplayer.get(0)){
                    checkplayer.get(0).pause();
                }
            };           
            
        }, //end of register_control_bar_events_shadow
        

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
