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
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/2.1.4/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
<script src="basicskin.js"></script>
<script type="text/javascript" src="../lib/embed-compressed.js"></script>
<script type="text/javascript" src="../lib/mobileupload.js"></script>
<link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css">
<link rel="stylesheet" href="basicskin.css">
<link rel="stylesheet" type="text/css" href="../lib/styles.css">
</head>
<body>
<div class="poodll_audiosdk_recording_cont">
 <div class="poodll_audiosdk_recorder_spacer_left" id="poodll_audiosdk_recorder_spacer_left"></div>
 <div class="poodll_audiosdk_recorder_cont" id="poodll_audiosdk_recorder_cont">
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
echo $ah->fetchRecorder($params['updatecontrol'],"poodll_audiosdk.audiohelper.poodllcallback" ,$params['p1'],$params['p2'],$params['p3'],$params['p4'],$params['p5'],"therecorderid","true", "volume");
?>
 </div>
 <div class="poodll_audiosdk_dummy_recorder poodll_audiosdk_dummy_recorder_hidden" id="poodll_audiosdk_dummy_recorder"><i class="fa fa-microphone fa-4x"></i></div>
 <div class="poodll_audiosdk_recorder_spacer_right" id="poodll_audiosdk_recorder_spacer_right"></div>
</div>

<?php $browser = new Browser(); //get our browser object for detecting Os and browser types
	//ismobile accepts 'always' (always HTML5) never (never HTML5) mobile (if mobile HTML5) or webkit (if mobile or webkit browser HTML5)
	if(!$ah->isMobile('mobile',$browser)){
?>
	<div class="poodll_audiosdk_controlpanel">
	<h1><div id="displaytime">0:00:00</div></h1>
	<button name="record" id="poodll_audiosdk_recordbutton" class ="poodll_audiosdk_recordbutton"  onclick="poodll_audiosdk.audiohelper.recordbuttonclick();">record</button>
	<button name="pause" id="poodll_audiosdk_pausebutton" class ="poodll_audiosdk_pausebutton" >pause</button>
	<button name="stop" id="poodll_audiosdk_stopbutton" class ="poodll_audiosdk_stopbutton" >stop</button>
	<button name="play" id="poodll_audiosdk_playbutton" class ="poodll_audiosdk_playbutton" >play</button>
	<button name="export" id="poodll_audiosdk_exportbutton" class ="poodll_audiosdk_exportbutton" >submit</button>
	</div>
<?php }else{ ?>
	<button style="width:180px;height:40px" name="export" id="poodll_audiosdk_exportbutton">submit</button>
<?php } ?>
</body>
</html>