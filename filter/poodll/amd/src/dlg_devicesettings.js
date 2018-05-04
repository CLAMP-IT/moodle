define(['jquery','core/log','filter_poodll/dlg_poodll'], function($, log,dialog) {
	
	 log.debug('Device settings dialog: initialising');
	
	
	
	return{
		dlg: null,
		pmr: null,
		instanceprops: null,
		dlgbox: null,
		mediatype: null,
		
		init: function(pmr, instanceprops){       
			this.dlg=dialog.clone();	
			this.dlg.setHeader('settings');	
			this.pmr=pmr;
			this.instanceprops= instanceprops;
			this.mediatype= instanceprops.config.mediatype;
        },
        //for making multiple instances
        clone: function(){
            return $.extend(true,{},this);
        },

		fetch_dialogue_box: function(){
			//this returns html that will be set to the DOM
			return this.dlg.fetch_dialogue_box('settings');
		},
		set_dialogue_box: function(dlgbox){
			//this is the jquery object that is the dlgbox in the DOM
			this.dlgbox = dlgbox;
			this.dlg.set_dialogue_box(dlgbox);

		},
		open: function(){
			var self = this;
			var ip = this.instanceprops;
			// fetch the audio and video devices
				navigator.mediaDevices.enumerateDevices()
				.then(function(devices) {
				  	var audiodevices = [];
				  	var videodevices = [];

				  devices.forEach(function(device) {
					switch (device.kind){
						case 'audioinput': 
							audiodevices.push(device);
							break;
						case 'videoinput':
							videodevices.push(device);
							break;
					}
				  });
				  //make select boxes of devices
				  var audioselect = '<div class="devicesettings_select"><span class="devicesettings_select_label">Audio: </span>' + self.makeSelect(audiodevices,'audio') + '</div>';
				  var videoselect = '<div class="devicesettings_select"><span class="devicesettings_select_label">Video: </span>' + self.makeSelect(videodevices,'video')  + '</div>';
				  var content = '<div class="filter_poodll_mediadevices">';	
		
				  if(self.mediatype=='video'){
				  	content += audioselect + '<br>' + videoselect;
				  }else{
				  	content += audioselect;
				  }
				  content += '</div>';
				  //set the html to the dialog and DOM
				  self.dlg.setContent(content);
				  //register events for the select boxes
				  self.registerEvents();
				  
				  if(self.instanceprops.config.mediatype=='video'){
				  	self.dlg.onclose=function(){self.resetUserInterface();};
				  }
				  
				  //open the dialog
				  self.dlg.open();
				}).catch(function(e){
					log.debug(e);
				});
		},
		
		registerEvents: function(){
			var self=this;
			var ip = this.instanceprops;
			var preview = ip.controlbar.preview[0];
			

			if(preview){
				preview.pause();
			}
			if(ip.mediaRecorder){
				ip.mediaRecorder.stop();
			}
			
			this.dlgbox.find('.select_settings_audio').change(function() {
				self.instanceprops.useraudiodeviceid = this.value;
			});
			
			this.dlgbox.find('.select_settings_video').change(function() {
				self.instanceprops.uservideodeviceid = this.value;	
				
				//This kind of worked but actually it was problematic in android
				//and even on desktop because we reset the video when it was hidden
				//we moved it to the onclose event
				//self.resetUserInterface();		
			});
		},
		
		resetUserInterface: function(){

			//set up refs to use in inline functions and keep it brief
			var ip = this.instanceprops;
			var preview = ip.controlbar.preview[0];
			var pmr = this.pmr
			//fetch video constraints
			var constraints = pmr.fetch_video_constraints(ip);
			
			 //We always tidy up old streams before calling getUserMedia
        	pmr.tidy_old_stream(ip.controlbarid);

			//get a new mediastream based on those constraints and update PMR accordingly
			navigator.mediaDevices.getUserMedia(constraints).then(function(stream){
			
				//stop any playing tracks of the current stream	
				pmr.restream_preview_video_player(ip.controlbarid,stream)
				
               
			}).catch(function(err) {
				log.debug('location 4567');
				log.debug(err);
			});
			
		},
		
		makeSelect: function(devices,devicetype){
			//if no devices we are not ready
			//must replace with lang string
			if(devices.length==1 && devices[0].label==''){
				return '<div>No devices available yet.</div>';
			}
			//get selected useraudiodevice and videodevice (if any)
			switch(devicetype){
				case 'audio':
					var userdeviceid = this.instanceprops.useraudiodeviceid;
					break;
				case 'video':
					var userdeviceid = this.instanceprops.uservideodeviceid;
			}
		
			//build dropdown from device list
			var dlg_dropdown = '<select class="select_settings_'+ devicetype +'">';
			devices.forEach(function(device){
				var selected ='';
				if(userdeviceid == device.deviceId){
					selected='selected';
				}
				dlg_dropdown += '<option value="' + device.deviceId + '" '+ ' ' + selected +'>' + device.label  + '</option>';
			});
			dlg_dropdown += '</select>';
			//return dropdown
			return dlg_dropdown;
		}
	}
	
});