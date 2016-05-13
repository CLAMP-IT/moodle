// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * JavaScript library for skin3 of PoodLLAudioSDK
 *
 * @copyright  2015 Justin Hunt (poodllsupport@gmail.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

poodll_audiosdk = {};
poodll_audiosdk.audiohelper = {	
	recorderid: 'therecorderid',
	recordbutton: 'poodll_audiosdk_record_button',
	playbutton: 'poodll_audiosdk_play_button',
	stopbutton: 'poodll_audiosdk_stop_button',
	statusbar: 'poodll_audiosdk_recorder_status_panel',
	iframeclass: 'filter_poodll_mp3skinned_recorder',
	smalliframeclass: 'filter_poodll_mp3skinned_recorder_small_80',
	messages: {},
	gotsound: false,
	recorderallowed: false,
	recordingcontainer: 'poodll_audiosdk_recording_cont',
	recordercontainer: 'poodll_audiosdk_recorder_cont',
	progresscontainer: null,
	status: 'stopped',

	init: function(opts){
		$('.' + this.recordbutton).click(this.recordbuttonclick);
		$('.' + this.stopbutton).click(this.stopbuttonclick);
		$('.' + this.playbutton).click(this.doplay);
		this.initmessages();
		if(!this.recorderallowed){
			this.disablebuttons();
		}
	},
	initmessages: function(){
		this.messages.finished='-- Finished --';
		this.messages.uploading='Uploading';
		this.messages.converting='Converting';
		this.messages.recording='Recording:  ';
	},
	//this is not used, but this is how to inject a recorder into a div.
	//the div id is one of the params, and is by convention [recorderid] + 'Container'
	initrecorder: function(recorderjson){
		lzOptions = {ServerRoot: '\\'};
		lz.embed.swf(JSON.parse(recorderjson));
	},
	recordbuttonclick: function(){
		var m = poodll_audiosdk.audiohelper;
		m.dorecord();
	},
	stopbuttonclick: function(){
		var m = poodll_audiosdk.audiohelper;
		if(m.fetchrecstatus() !='stopped'){
			m.dostop();
		}
	},
	transformrecorder: function(){
		$('#' + this.recorderid + 'Container').attr('style','width: 1px; height: 1px;');
		$("." + this.iframeclass,parent.document).addClass(this.smalliframeclass);
		// var iframeWin = parent.document.getElementById("yourIframeID");
		//iframeWin.height = document.body.scrollHeight;

	},
	fetchrecstatus: function(){
		return this.fetchrecproperty('recorderstatus');
	},
	fetchrecproperty: function(propertyname){
		return lz.embed[this.recorderid].getCanvasAttribute(propertyname);
	},
	poodllcallback: function(args){
		if(args[1] !='timerevent' && args[1] !='volumeevent' ){
			console.log ("poodllcallback:" + args[0] + ":" + args[1] + ":" + args[2] + ":" + args[3] + ":" + args[4] + ":" + args[5] + ":" + args[6]);
		}
		switch(args[1]){
			case 'allowed':
					this.recorderallowed = args[2]; 
					if (this.recorderallowed){
						this.transformrecorder();
						this.enablebuttons();
					}
					break;
			/*		
			case 'statuschanged':
					this.status = args[2]; 
					if(this.status =='haverecorded'){
						this.doexport(this.recorderid);
					}
					break;
			*/
			case 'filesubmitted':
					this.status ='finished';
					this.updatepage(args[2]);
					this.enablebuttons();
					this.updatestatusbar('message',this.messages['finished']);
					break;
					
			case 'spinnermessage':
				switch(args[2]){
					case 'Converting':
						this.status ='converting';
						this.disablebuttons();
						this.updatestatusbar('message',this.messages['converting']);
						break;
					case 'Uploading':
						this.status ='uploading';
						this.updatestatusbar('message',this.messages['uploading']);
						break;
					
				}
				break;

			case 'uploadstarted':
								break;
			case 'showerror':
						//probably should have better error logic than this.
						this.status ='finished';
						this.enablebuttons();
						this.updatestatusbar('error',args[2]);
						break;
			case 'actionerror':
								break;
			case 'timeouterror':
								break;
			case 'nosound':	alert('No sound captured');
								break;
			case 'conversionerror':
								break;
			case 'beginningconversion':
								break;
			case 'conversioncomplete':
								break;
			case 'timerevent':
				if(args[2]!='0'){
					  //update onscreen timer
					  this.updatestatusbar('recording',this.messages['recording'] + lz.embed[args[0]].getCanvasAttribute('displaytime'));
				
					 //we rather lamely hijack this to run our volume events
					 //this.dogotsound(lz.embed[args[0]].getCanvasAttribute('currentvolume'));
				}
				break;
			case 'volumeevent':
				if(args[2] > 0){
					//we no longer use this cos its hard to make a graph when vol don't change
					//console.log('volume:' + args[2]);
					//this.dogotsound(args[2]);
				}
				break;
			
			case 'volume':
				console.log('volume:' + args[2]);
				break;
		
		}
	},
	updatepage: function(newfilename){
		var updatecontrolname = this.fetchrecproperty('updatecontrol');	
		var theparent =null;
		for(var x=0;x<10;x++){
			if(!theparent){
				theparent = parent;
			}else{
				theparent = theparent.parent;
			}
			if(!theparent){break;}
			var updatecontrol = theparent.document.getElementById(updatecontrolname);
			if(updatecontrol){
				updatecontrol.value=newfilename;
				break;
			}	
		}
		
	},
	disablebuttons: function(){
		$('.' + this.recordbutton).prop('disabled', true); 
		$('.' + this.stopbutton).prop('disabled', true); 
		$('.' + this.playbutton).prop('disabled', true); 
	},
	enablebuttons: function(){
		$('.' + this.recordbutton).prop('disabled', false); 
		$('.' + this.stopbutton).prop('disabled', false); 
		$('.' + this.playbutton).prop('disabled', false); 
	},
	updatestatusbar: function($type,message){
			switch($type){
				case 'error':
					$('.' + this.statusbar).removeClass('poodll_audiosdk_recorder_plain_message');
					$('.' + this.statusbar).removeClass('poodll_audiosdk_recorder_recording_message');
					$('.' + this.statusbar).addClass('poodll_audiosdk_recorder_error_message');
					break;
				case 'message':
					$('.' + this.statusbar).removeClass('poodll_audiosdk_recorder_error_message');
					$('.' + this.statusbar).removeClass('poodll_audiosdk_recorder_recording_message');
					$('.' + this.statusbar).addClass('poodll_audiosdk_recorder_plain_message');
				case 'recording':
					$('.' + this.statusbar).removeClass('poodll_audiosdk_recorder_error_message');
					$('.' + this.statusbar).removeClass('poodll_audiosdk_recorder_plain_message');	
					$('.' + this.statusbar).addClass('poodll_audiosdk_recorder_recording_message');					
			}
			$('.' + this.statusbar).text(message);
	},
	//handles calls into the recorder
	dorecorderapi: function(callingfunction){
		if(lz.embed[this.recorderid] != null){
			var apicall = '';
			switch(callingfunction){
				case 'dorecord': apicall = 'poodllapi.mp3_record()';break;
				case 'dostop': apicall = 'poodllapi.mp3_stop()';break;
				case 'dopause': apicall = 'poodllapi.mp3_pause()';break;
				case 'doshowsettings': apicall = 'poodllapi.mp3_show_settings()';break;
				case 'doplay': apicall = 'poodllapi.mp3_play()';break;
				case 'dodisable': apicall = 'poodllapi.mp3_disable()';break;
				case 'doenable': apicall = 'poodllapi.mp3_enable()';break;
			}
			lz.embed[this.recorderid].callMethod(apicall);
		}
	
	},
	//this function shows how to call the MP3 recorder's API to export the recording to the server
	doexport: function(){
		if(lz.embed[this.recorderid] != null){
			lz.embed[this.recorderid].callMethod('poodllapi.mp3_export()');
		}else{
			deferredexport(this.recorderid);
		}
	},
	
	//this function shows how to call the MP3 recorder's API to commence recording
	doshowsettings: function(){
		this.dorecorderapi('doshowsettings');
	},

	//this function shows how to call the MP3 recorder's API to commence recording
	dorecord: function(){
		this.dorecorderapi('dorecord');
		//$('.' + this.dummyrecorder).removeClass(this.dummyrecorder + '_stopped');
		//$('.' + this.dummyrecorder).addClass(this.dummyrecorder + '_recording');
	},

	//this function shows how to call the MP3 recorder's API to playback the recording
	doplay: function(){
		var m = poodll_audiosdk.audiohelper;
		m.dorecorderapi('doplay');
	},
	
	//this function shows how to call the MP3 recorder's API to playback the recording
	dopause: function(){
		var m = poodll_audiosdk.audiohelper;
		m.dorecorderapi('dopause');
	},
	
	//this function shows how to call the MP3 recorder's API to stop the recording or playback
	dostop: function(){
		var m = poodll_audiosdk.audiohelper;
		if(m.status=='recording'){
			//$('.' + m.dummyrecorder).removeClass(m.dummyrecorder + '_recording');
			//$('.' + m.dummyrecorder).addClass(m.dummyrecorder + '_stopped');
		}
		m.dorecorderapi('dostop');
	},
	
	//this function shows how to call the MP3 recorder's API to stop the recording or playback
	dodisable: function(){
		var m = poodll_audiosdk.audiohelper;
		m.dorecorderapi('dodisable');
	}

};
