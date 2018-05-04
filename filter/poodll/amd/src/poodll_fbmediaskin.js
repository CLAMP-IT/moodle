/* jshint ignore:start */
define(['jquery','core/log','filter_poodll/utils_amd'], function($, log, utils) {

    "use strict"; // jshint ;_;

    log.debug('PoodLL Fluencybuilder Skin: initialising');

    return {
    
        instanceprops: null,
        pmr: null,
        uploaded: false,

        //for making multiple instances
        clone: function(){
            return $.extend(true,{},this);
        },

        init: function(ip, pmr){
            this.instanceprops=ip;
	    	this.instanceprops.warmedup=false;
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
            var status = '<div class="poodll_status_' + skin + ' hide"></div>';
            return status;
        },
        
        fetch_preview_audio: function(skin){
            var checkplayer = '<audio class="poodll_checkplayer_' + skin + ' hide" ></audio>';
            return checkplayer;
        },
        fetch_model_audio: function(){
            var modelplayer = '<audio class="poodll_modelplayer_fluencybuilder hide" src="' + M.cfg.wwwroot + '/filter/poodll/ding.mp3"></audio>';
            return modelplayer;
        },
        fetch_preview_video: function(skin){
            var checkplayer ='<video class="poodll_checkplayer_' + skin + '" width="320" height="240"></video>';
            return checkplayer;
        },
        fetch_resource_audio: function(skin){
            var resourceplayer = '<audio class="poodll_resourceplayer_' + skin + ' hide" src="' + M.cfg.wwwroot + '/filter/poodll/ding.mp3" ></audio>';
            return resourceplayer;
        },
        fetch_resource_video: function(skin){
            var resourceplayer = '<video class="poodll_resourceplayer_' + skin + ' hide" src="@@RESOURCEURLx@@" ></video>';
            return resourceplayer;
        },
        fetch_uploader_skin: function(controlbarid, element){
            return false;
        },
        fetch_ding_player: function() {
            var skin = 'fluencybuilder';
            var dingplayer = '<audio class="poodll_dingplayer_' + skin + ' hide"  src="' + M.cfg.wwwroot + '/filter/poodll/ding.mp3" ></audio>';
            return dingplayer;
        },
        onMediaError: function(e) {
                console.error('media error', e);
        },

        onMediaSuccess_video: function(controlbarid){
            var ip = this.fetch_instanceprops(controlbarid);

            this.highlight_button('bwrapper_start-recording', controlbarid);

            //clear messages
            ip.uploader.Output('');

            //timer and status bar
            ip.timer.reset();
            ip.timer.start();
            this.update_status(controlbarid);

            ip.controlbar.stopbutton.attr('disabled',false);
            ip.controlbar.savebutton.attr('disabled',false);

        },

        onMediaSuccess_audio: function(controlbarid){
            var ip = this.fetch_instanceprops(controlbarid);
            ip.controlbar.checkplayer.attr('src',null);
            this.pmr.do_pause_audio(ip);

        },

        handle_timer_update: function(controlbarid){
            var ip = this.fetch_instanceprops(controlbarid);
            this.update_status(controlbarid);
            if(ip.timer.seconds==0 && ip.timer.initseconds >0){
                 ip.controlbar.stoprecbutton.click();
            }
        },

       update_status: function(controlbarid){
            /*
            var ip = this.fetch_instanceprops(controlbarid);
            ip.controlbar.status.html(ip.timer.fetch_display_time());
            */
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

                //fetch html for players and items that t
                var resourceplayer = resourceplayer.replace('@@RESOURCEURL@@', ip.config.resource);
                var modelplayer = this.fetch_model_audio();
                modelplayer = modelplayer.replace('@@MODELURL@@', ip.config.resource2);
                var dingplayer = this.fetch_ding_player();
                var status = this.fetch_status_bar('fluencybuilder');

                //fetch the size class we use. Not really meaningful in fluency builder
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


                var controls ='<div class="poodll_mediarecorderholder_fluencybuilder '
                	+ recorder_class + ' ' + size_class + '" id="holder_' + controlbarid + '">' ;
                	
                controls +='<div class="poodll_mediarecorderbox_fluencybuilder" id="' + controlbarid + '">' ;
                controls +='<div class="style-holder ' + skin_style + '">' ;
                controls += status,
                controls += dingplayer,
                    controls += modelplayer,
                controls += checkplayer,
                controls += resourceplayer,
                controls +=  '<div class="fb_bwrapper bwrapper_play-resource mod_fluencybuilder_autobutton_inactive"><button type="button" class="poodll_mediarecorder_button_fluencybuilder poodll_play-resource_fluencybuilder"><i class="fa fa-assistive-listening-systems" aria-hidden="true"></i></button></div>';
                controls +=  '<div class="fb_bwrapper bwrapper_start-recording mod_fluencybuilder_autobutton_inactive"><button type="button" class="poodll_mediarecorder_button_fluencybuilder poodll_start-recording_fluencybuilder" disabled><i class="fa fa-microphone" aria-hidden="true"></i></button></div>';
                controls += '<div class="fb_bwrapper bwrapper_play-model mod_fluencybuilder_autobutton_inactive"> <button type="button" class="poodll_mediarecorder_button_fluencybuilder poodll_play-model_fluencybuilder" disabled><i class="fa fa-play-circle" aria-hidden="true"></i></button></div>';

                /* hidden buttons: they still are used internally so we need them, but user doesn't see them */
                controls += '<button type="button" class="poodll_mediarecorder_button_fluencybuilder poodll_play-recording_fluencybuilder hide">' + M.util.get_string('recui_play', 'filter_poodll') + '</button>';
                controls += '<button type="button" class="poodll_mediarecorder_button_fluencybuilder poodll_stop-playing_fluencybuilder hide">' + 'stopplay' + '</button>';
                controls += '<button type="button" class="poodll_mediarecorder_button_fluencybuilder poodll_stop-recording_fluencybuilder hide">' + M.util.get_string('recui_stop', 'filter_poodll') + '</button>';
                controls += '<button type="button" class="poodll_save-recording_fluencybuilder pmr_disabled hide">' + M.util.get_string('recui_save', 'filter_poodll') + '</button>';
                controls += '</div></div></div>';
                $(element).prepend(controls);

                var controlbar ={
                    status: $('#' + controlbarid + '  .poodll_status_fluencybuilder'),
                    dingplayer: $('#' + controlbarid + '  .poodll_dingplayer_fluencybuilder'),
                    modelplayer: $('#' + controlbarid + '  .poodll_modelplayer_fluencybuilder'),
                    resourceplayer: $('#' + controlbarid + '  .poodll_resourceplayer_fluencybuilder'),
                    checkplayer: $('#' + controlbarid + '  .poodll_checkplayer_fluencybuilder'),
                    resourcebutton: $('#' + controlbarid + '  .poodll_play-resource_fluencybuilder'),
                    startbutton: $('#' + controlbarid + '  .poodll_start-recording_fluencybuilder'),
                    stoprecbutton: $('#' + controlbarid + '  .poodll_stop-recording_fluencybuilder'),
                    stopbutton: $('#' + controlbarid + '  .poodll_stop-playing_fluencybuilder'),
                    playbutton: $('#' + controlbarid + '  .poodll_play-recording_fluencybuilder'),
                    playmodelbutton: $('#' + controlbarid + '  .poodll_play-model_fluencybuilder'),
                    savebutton: $('#' + controlbarid + '  .poodll_save-recording_fluencybuilder'),
                    resourcewrapper: $('#' + controlbarid + '  .bwrapper_play-resource'),
                    startrecwrapper: $('#' + controlbarid + '  .bwrapper_start-recording'),
                    playmodelwrapper: $('#' + controlbarid + '  .bwrapper_play-model')
                };
                return controlbar;
        }, //end of fetch_control_bar_fluencybuilder


        register_controlbar_events_video: function(onMediaSuccess, controlbarid) {
            return this.register_controlbar_events_audio(onMediaSuccess, controlbarid);
        },


        /*
        * Plays a ding and calls the next action
        *
        *
         */
        play_ding: function(action, controlbarid){

            var ip = this.fetch_instanceprops(controlbarid);
            ip.controlbar.dingplayer[0].play();
            switch (action){
                case 'click_start':
                     ip.controlbar.startbutton.click();
                    break;
                case 'click_play':
                    ip.controlbar.playmodelbutton.click();
                    break;
                case 'show_dialog':
                    alert('dialog');
                    break;
                default:
                    ip.controlbar.dingplayer[0].onended=null;
            }
        },

        /*
         * Highlights the current button (wrapper) and dehighlishts the rest
         *
         *
         */
        highlight_button: function(highlightwrapper, controlbarid){
            var wrappers = $('#' + controlbarid + '  .fb_bwrapper');
            $(wrappers).each(function(index){
                if($(this).hasClass(highlightwrapper)){
                    $(this).removeClass('mod_fluencybuilder_autobutton_inactive');
                    $(this).addClass('mod_fluencybuilder_autobutton_active');
                }else{
                    $(this).removeClass('mod_fluencybuilder_autobutton_active');
                    $(this).addClass('mod_fluencybuilder_autobutton_inactive');
                }
            });
        },

	warmup_recorder: function(controlbarid, onMediaSuccess){
     	var ip = this.fetch_instanceprops(controlbarid);
		//warm up preview player and audiocontext
		this.pmr.warmup_context(ip);
		this.pmr.warmup_preview(ip);

		//We start recording here for mobile sfari click proximity
        //but immediately pause it in onMediaSuccess
        //when we really start (start-rec button click event), we "resume" recording
        this.pmr.do_start_audio(ip, onMediaSuccess);

		
		//fetch players and info	
		var model = ip.controlbar.modelplayer.get(0);
		var ding = ip.controlbar.dingplayer.get(0);
		var resource = ip.controlbar.resourceplayer.get(0);
		
		
		if(!ip.warmedup){
			//play from players
			try {
				resource.play();
				ding.play();
				model.play();
				resource.pause();		            
				ding.pause();       
				model.pause();
	   
			}catch(e){
				//do nothing
			}
        }
		

		//set src on urls
		var dingurl = M.cfg.wwwroot + '/filter/poodll/ding.mp3';
		$(model).attr('src',ip.config.resource2);
		$(ding).attr('src',dingurl);
		$(resource).attr('src',ip.config.resource);
		//flag all warmed up
		ip.warmedup=true;
	},

        register_controlbar_events_audio: function(onMediaSuccess, controlbarid){
            var self = this;
            var pmr=this.pmr;
            var ip = this.fetch_instanceprops(controlbarid);

            //when audio prompt finishes , play ding, then start recording
            //AUTOMATION
            ip.controlbar.resourceplayer.on('ended',function(){
		if(ip.warmedup){
			self.play_ding('click_start', controlbarid);
		}
	     });
		
            ip.controlbar.modelplayer.on('ended',function(){
                self.highlight_button('', controlbarid);
            });


            //when start recording button clicked
            ip.controlbar.startbutton.click(function() {

                //pmr.do_start_audio(ip, onMediaSuccess);
                pmr.do_resume_audio(ip);



                self.highlight_button('bwrapper_start-recording', controlbarid);

                //clear messages
                ip.uploader.Output('');

                //timer and status bar
                ip.timer.reset();
                ip.timer.start();
                self.update_status(controlbarid);

                ip.controlbar.stopbutton.attr('disabled',false);
                ip.controlbar.savebutton.attr('disabled',false);

            });



            //when the stop recording button is pressed
            ip.controlbar.stoprecbutton.click(function() {
                pmr.do_stop_audio(ip);

                //AUTOMATION
                self.play_ding('click_play', controlbarid);

                //timer and status bar
                ip.timer.stop()
                self.update_status(controlbarid);
            });

            //when the stop playing recording button is pressed
            ip.controlbar.stopbutton.click(function() {

                self.highlight_button('none',controlbarid);

               //timer and status bar
               ip.timer.stop()
               self.update_status(controlbarid);

            });

            //when the play recording button is pressed
            ip.controlbar.playmodelbutton.click(function() {
                var modelplayer = ip.controlbar.modelplayer.get(0);
                modelplayer.play();
                self.highlight_button('bwrapper_play-model', controlbarid);

            });

            //when the play recording button is pressed
            ip.controlbar.playbutton.click(function() {
                var checkplayer = ip.controlbar.checkplayer.get(0);
                pmr.do_play_audio(ip,checkplayer);
            });

            //when the play prompt button is pressed
            ip.controlbar.resourcebutton.click(function(){

		        self.warmup_recorder(controlbarid,onMediaSuccess);

                self.disable_button(this);
                var resourceplayer = ip.controlbar.resourceplayer.get(0);
                resourceplayer.play();
                self.highlight_button('bwrapper_play-resource', controlbarid);
            });

            //when the save button is pressed
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
        }, //end of register_control_bar_events_fluencybuilder

        /*
         * convenient functions to hide/show/enable/disable buttons
         *
         *
         */
        enable_button: function(button){
           $(button).prop('disabled',false);

           // $(button).removeClass('pmr_disabled');
        },
        disable_button: function(button){
            $(button).prop('disabled',true);
          //  $(button).addClass('pmr_disabled');
        },
        show_button: function(button){
           button.show();
        },
        hide_button: function(button){
            button.hide();
        },

    };//end of returned object
});//total end
