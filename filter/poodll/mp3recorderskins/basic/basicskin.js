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
	recordbutton: 'poodll_audiosdk_recordbutton',
	pausebutton: 'poodll_audiosdk_pausebutton',
	playbutton: 'poodll_audiosdk_playbutton',
	exportbutton: 'poodll_audiosdk_exportbutton',
	stopbutton: 'poodll_audiosdk_stopbutton',
	gotsound: false,
	hider: null,
	sounds: Array(),
	awaitingpermission: false,
	recorderallowed: false,
	recordingcontainer: 'poodll_audiosdk_recording_cont',
	recordercontainer: 'poodll_audiosdk_recorder_cont',
	dummyrecorder: 'poodll_audiosdk_dummy_recorder',
	progresscontainer: null,
	recspacercontainerright: 'poodll_audiosdk_recorder_spacer_right',
	recspacercontainerleft: 'poodll_audiosdk_recorder_spacer_left',
	status: 'stopped',

	init: function(opts){
		//$('.' + this.recordbutton).click(this.recordbuttonclick);
		$('.' + this.recordbutton).click(this.beginall);
		$('.' + this.stopbutton).click(this.stopbuttonclick);
		$('.' + this.playbutton).click(this.doplay);
		$('.' + this.pausebutton).click(this.dopause);
		$('.' + this.exportbutton).click(this.doexport);
	},
	//this is not used, but this is how to inject a recorder into a div.
	//the div id is one of the params, and is by convention [recorderid] + 'Container'
	initrecorder: function(recorderjson){
		lzOptions = {ServerRoot: '\\'};
		lz.embed.swf(JSON.parse(recorderjson));
	},
	beginall: function(){
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
		$('.' + this.dummyrecorder).removeClass(this.dummyrecorder + '_hidden');
		$('.' + this.dummyrecorder).addClass(this.dummyrecorder + '_stopped');
		//$('.' + this.dummyrecorder).css('background-image','url("microphone.png")');
	},
	getpermissionmode: function(){
		this.awaitingpermission=true;
		this.doshowsettings();
		$('.' + this.recspacercontainerleft).addClass('poodll_audiosdk_getpermissionmode');
		$('.' + this.recspacercontainerright).addClass('poodll_audiosdk_getpermissionmode');
	},
	clearpermissionmode: function(){
		this.awaitingpermission=false;
		$('.' + this.recspacercontainerleft).removeClass('poodll_audiosdk_getpermissionmode');
		$('.' + this.recspacercontainerright).removeClass('poodll_audiosdk_getpermissionmode');
	},
	recordbuttonclick: function(){
		var m = poodll_audiosdk.audiohelper;
		if(poodll_audiosdk.audiohelper.awaitingpermission){
			//$(this).text('Record');
			poodll_audiosdk.audiohelper.clearpermissionmode();
			return;
		}
		if(m.fetchrecstatus() =='stopped' || m.fetchrecstatus() =='paused'){
			if(!m.recorderallowed){
				poodll_audiosdk.audiohelper.getpermissionmode();
				return;
			}
			m.dorecord();
		}else{
			//reset the text label
			$(this).text('Recccord');
			m.dostop();
			if(m.gotsound){
				//$('.' + m.recordbutton).hide();
				$('.' + m.startbutton).prop('disabled',false);
			}else{
				alert('No sound captured');
			}
		}
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
						this.init();
						this.transformrecorder();
						//if allowed was done after pressing record button
						//commence recording
						if(this.awaitingpermission){
							this.dorecord();
						}
						this.clearpermissionmode();
					}
					break;
					
			case 'statuschanged':
					this.status = args[2]; 
					if(this.status =='haverecorded'){
						this.doexport(this.recorderid);
					}
					break;
			case 'filesubmitted':
					this.doshowplayer(args[2]);
					break;

			case 'uploadstarted':
								break;
			case 'showerror':
						//probably should have better error logic than this.
						this.doerrorlayout();
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
 					  $('#displaytime').text(lz.embed[args[0]].getCanvasAttribute('displaytime'));
				
					 //we rather lamely hijack this to run our volume events
					 //console.log(lz.embed[args[0]].getCanvasAttribute('displaytime'));
					 this.dogotsound(lz.embed[args[0]].getCanvasAttribute('currentvolume'));
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
	makecanvas: function(div) {
		var canvas = this.fetchcanvas(div);
		if(!canvas){
			canvas = document.createElement('canvas');
		}
        var thediv = document.getElementById(div); 
        canvas.id     = div + '_canvas';
		canvas.className = 'poodll_audiosdk_voicecanvas';
        thediv.appendChild(canvas);
		return canvas;
    },
	fetchcanvas: function(div) {
		return document.getElementById(div + '_canvas');
	},
	drawvoicechart: function(div){
		var canvas = this.fetchcanvas(div);
		if(!canvas){return;}
		var ctx = canvas.getContext("2d");
		var xsteps = this.sounds.length * 2;
		ctx.clearRect(0,0,canvas.width,canvas.height);
		var cxzero = 0;
		var cyzero = canvas.height / 2;
		var cyratio = cyzero / canvas.height;
		var cxratio = canvas.width / xsteps;
		
		//get y values by x steps (10 points each side)
		var ys = Array();
		for(var x=0; x< xsteps;x+=2){
			ys[x] = (this.sounds[x] * cyratio) + cyzero;
			ys[x+1] = (this.sounds[x] * cyratio * -1) + cyzero;
		}
		//reverse array to go out to in
		// this got weird. so canned it
		//ys.reverse();
		
		//draw from right to center
		ctx.beginPath();
		ctx.moveTo(canvas.width,cyzero);
		for(var x=0; x< xsteps;x++){
			ctx.lineTo(canvas.width - (x*cxratio),ys[x]);
		}
		ctx.stroke();
		//draw from left to center
		ctx.beginPath();
		ctx.moveTo(0,cyzero);
		for(var x=0; x< xsteps;x++){
			ctx.lineTo(x*cxratio,ys[x]);
		}
		ctx.stroke();
	},
	doshowplayer: function(filename){
				//audio filename
				var audlabel=document.createTextNode("filename: " + filename);
				
				//audio element
				var aud=document.createElement('audio');
				aud.controls="controls";
				
				//audio source
				var dasrc = document.createElement('source');
				dasrc.type= 'audio/mpeg';
				dasrc.src="out/" + args[2];
				dasrc.setAttribute("preload","auto");
				
				//set audio src
				aud.appendChild(dasrc);
				aud.load();	

				//put it all on the page
				var players = document.getElementById('players');
				players.appendChild(audlabel);
				players.appendChild(document.createElement('br'));
				players.appendChild(aud);
				players.appendChild(document.createElement('br'));
	},
	dogotsound: function(level){
		if(this.sounds.length > 10){
			this.sounds.shift();
		}
		this.sounds.push(level);
		if(level>0){
			this.gotsound=true;
		}
		if(this.fetchrecstatus()!== 'stopped'){
			this.drawvoicechart(this.dummyrecorder);
		}
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
		$('.' + this.dummyrecorder).removeClass(this.dummyrecorder + '_stopped');
		$('.' + this.dummyrecorder).addClass(this.dummyrecorder + '_recording');
		this.makecanvas(this.dummyrecorder);
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
			$('.' + m.dummyrecorder).removeClass(m.dummyrecorder + '_recording');
			$('.' + m.dummyrecorder).addClass(m.dummyrecorder + '_stopped');
		}
		m.dorecorderapi('dostop');
	},
	
	//this function shows how to call the MP3 recorder's API to stop the recording or playback
	dodisable: function(){
		var m = poodll_audiosdk.audiohelper;
		m.dorecorderapi('dodisable');
	}

};
