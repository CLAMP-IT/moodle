<?php
/**
 * PoodLL Audio Recording SDK
 *
* @author Justin Hunt (@link http://www.poodll.com)
* @copyright 2013 onwards Justin Hunt http://www.poodll.com
* @license JustinsPlainEnglishLicense ( http://www.poodll.com/justinsplainenglishlicense.txt )
 */
 
//Get our poodll resource handling lib
require_once('../audiohelper.php');
?>
<html>
<head>
<script type="text/javascript">
//<![CDATA[
//this function shows how the recorders can be configured to return info to a callback function
function poodllcallback(args){
	console.log ("poodllcallback:" + args[0] + ":" + args[1] + ":" + args[2] + ":" + args[3] + ":" + args[4] + ":" + args[5] + ":" + args[6]);
	
	switch(args[1]){
		case 'statuschanged':
							break;
		case 'filesubmitted':
				//audio filename
				var audlabel=document.createTextNode("filename: " + args[2]);
				
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
				
				//to disablerecorder after exporting
				if(lz.embed[args[0]] != null){
					lz.embed[args[0]].callMethod('poodllapi.mp3_disable()');
				}
				
				
				break;
		case 'uploadstarted':
							break;
		case 'actionerror':
							break;
		case 'timeouterror':
							break;
		case 'nosound':
							break;
		case 'conversionerror':
							break;
		case 'beginningconversion':
							break;
		case 'conversioncomplete':
							break;
		case 'timerevent':
			if(args[2]!='0'){
				document.getElementById('displaytime').innerHTML= lz.embed[args[0]].getCanvasAttribute('displaytime');
			}
							break;
	
	
	
	}


}
//this function shows how to call the MP3 recorder's API to export the recording to the server
function doexport(recorderid){
	if(lz.embed[recorderid] != null){
		lz.embed[recorderid].callMethod('poodllapi.mp3_export()');
	}else{
		deferredexport(recorderid);
	}
}

//this function shows how to call the MP3 recorder's API to commence recording
function dorecord(recorderid){
	if(lz.embed[recorderid] != null){
		lz.embed[recorderid].callMethod('poodllapi.mp3_record()');
	}
}
//this function shows how to call the MP3 recorder's API to pause the recording
function dopause(recorderid){
	if(lz.embed[recorderid] != null){
		lz.embed[recorderid].callMethod('poodllapi.mp3_pause()');
	}
}
//this function shows how to call the MP3 recorder's API to playback the recording
function doplay(recorderid){
	if(lz.embed[recorderid] != null){
		lz.embed[recorderid].callMethod('poodllapi.mp3_play()');
	}
}
//this function shows how to call the MP3 recorder's API to stop the recording or playback
function dostop(recorderid){
	if(lz.embed[recorderid] != null){
		lz.embed[recorderid].callMethod('poodllapi.mp3_stop()');
	}
}
//this function shows how to call the MP3 recorder's API to stop the recording or playback
function dodisable(recorderid){
	if(lz.embed[recorderid] != null){
		lz.embed[recorderid].callMethod('poodllapi.mp3_disable()');
	}
}
//]]>
</script>
<script type="text/javascript" src="../embed-compressed.js"></script>
<script type="text/javascript" src="../mobileupload.js"></script>
<link rel="stylesheet" type="text/css" href="../styles.css">
</head>
<body>
<h1>PoodLL Audio Recording Sample Project</h1>
<br/>
<br/>
Record your voice, or upload a video/audio file. It will be received, optionally converted, and an HTML5 audio player displayed on the page beneath the recorder. For PC users this demonstration will work best with browsers,that reliably play back MP3 files in HTML5(Chrome or Safari). However all browsers should record successfully.
<br/>
<br/>
<table>
<tr><td>
<?php 
//recorder 1
$ah = new audiohelper();
echo $ah->fetchRecorder("","poodllcallback" ,"p1","p2","p3","p4","p5","therecorderid","false", "volume");
?></td></tr>
<tr><td>
<?php $browser = new Browser(); //get our browser object for detecting Os and browser types
	//ismobile accepts 'always' (always HTML5) never (never HTML5) mobile (if mobile HTML5) or webkit (if mobile or webkit browser HTML5)
	if(!$ah->isMobile('mobile',$browser)){
?>
	<center><h1><div id="displaytime">0:00:00</div></h1></center>
	<button name="record" id="id_record" onclick="dorecord('therecorderid');">record</button>
	<button name="pause" id="id_pause" onclick="dopause('therecorderid');">pause</button>
	<button name="stop" id="id_stop" onclick="dostop('therecorderid');">stop</button>
	<button name="play" id="id_play" onclick="doplay('therecorderid');">play</button>
	<button name="export" id="id_export" onclick="doexport('therecorderid');">submit</button>
<?php }else{ ?>
	<button style="width:180px;height:40px" name="export" id="id_export" onclick="doexport('therecorderid');">submit</button>
<?php } ?>

</td></tr></table>
<div id="players" />
</body>
</html>