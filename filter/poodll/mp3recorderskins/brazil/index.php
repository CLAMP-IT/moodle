<?php
/**
 * PoodLL Audio Recording SDK
 *
* @author Justin Hunt (@link http://www.poodll.com)
* @copyright 2013 onwards Justin Hunt http://www.poodll.com
* @license JustinsPlainEnglishLicense ( http://www.poodll.com/justinsplainenglishlicense.txt )
 */
 
//Get our poodll resource handling lib
require_once(dirname(dirname(__FILE__)) . '/lib/audiohelper.php');
?>
<html>
<link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="brazilskin.css">
<link rel="stylesheet" type="text/css" href="../lib/styles.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/2.1.4/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
<script src="brazilskin.js"></script>
<script type="text/javascript" src="../lib/embed-compressed.js"></script>
<script type="text/javascript" src="../lib/mobileupload.js"></script>
<script>
$(document).ready(function(){poodll_audiosdk.audiohelper.init();});
</script>
</head>
<body>
<div class="poodll_audiosdk_recording_cont">

  <?php 
$params = $_GET;
$locations = array();
$locations['poodll_audio_url']=$params['poodll_audio_url'] . '/lib';
unset($_GET['poodll_audio_url']);
$locations['poodll_audio_path']=dirname(dirname(__FILE__));
$locations['poodll_audio_savepath'] = $locations['poodll_audio_path'] . '/out/'; ///var/www/mysite/grec/out
$locations['poodll_audio_ffmpegpath']= $locations['poodll_audio_path'] . '/ffmpeg'; //ffmpeg
$locations['poodll_audio_convertpath']=$locations['poodll_audio_path'] . '/out/';///var/www/mysite/grec/out


$ah = new audiohelper($params,$locations);
$browser = new Browser(); //get our browser object for detecting Os and browser types
	//ismobile accepts 'always' (always HTML5) never (never HTML5) mobile (if mobile HTML5) or webkit (if mobile or webkit browser HTML5)
	if(!$ah->isMobile('mobile',$browser)){
?>
	<div class="poodll_audiosdk_controlpanel btn-group btn-group-lg" role="group">
	<button name="record" id="poodll_audiosdk_record_button" class ="poodll_audiosdk_record_button btn btn-danger">
		<span class="poodll_audiosdk_record_button_text">REC</span>
	</button>
	<button name="stop" id="poodll_audiosdk_stop_button" class ="poodll_audiosdk_stop_button btn btn-default" > 
		<span class="glyphicon glyphicon-unchecked"></span>
	</button>
	<button name="play" id="poodll_audiosdk_play_button" class ="poodll_audiosdk_play_button btn btn-default" >
		<span class="glyphicon glyphicon-chevron-right"></span>
	</button>
	</div>
	<div class="poodll_audiosdk_recorder_status_panel"></div>
<?php } ?>
 <div class="poodll_audiosdk_recorder_cont" id="poodll_audiosdk_recorder_cont">
 <?php
echo $ah->fetchRecorder($params['updatecontrol'],"poodll_audiosdk.audiohelper.poodllcallback" ,$params['p1'],$params['p2'],$params['p3'],$params['p4'],$params['p5'],"therecorderid","true", "volume");
//this is just a dummy to stop a js error. need to fix it up
echo "<input type='hidden' id='" . $params['updatecontrol'] . "'/>"
?>
</div>
</div>
</body>
</html>