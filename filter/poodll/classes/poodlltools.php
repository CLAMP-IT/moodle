<?php
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

namespace filter_poodll;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/filelib.php');

/**
 *
 * This is a class containing static functions for general PoodLL filter things
 * like embedding recorders and managing them
 *
 * @package   filter_poodll
 * @since      Moodle 2.7
 * @copyright  2014 Justin Hunt
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class poodlltools
{


	public static function fetch_mediaserver_url()
	{
		global $CFG;
		// Setting up the PoodLL Media Server String
		if ($CFG->filter_poodll_serverport == '443' || $CFG->filter_poodll_serverport == '80') {
			$protocol = 'rtmpt';
		} else {
			$protocol = 'rtmp';
		}

		return $protocol . '://' . $CFG->filter_poodll_servername . ':' . $CFG->filter_poodll_serverport . '/' . $CFG->filter_poodll_serverid;


	}

//Trying to get the Flowplayer JS loaded nicely has been very tricky
//and we are not intercepting mp3 or flv or mp4 links, we don't load it. If we do
//this function checks if we should load it or not. If it is not the default player
//it messes up the multimedia plugin. Justin 20120924
	public static function shouldLoadFlowPlayerJS()
	{
		global $CFG;

		//If the PoodLL filter is using the flowplayer by default and handling media file extensions,
		//return true
		if ($CFG->filter_poodll_defaultplayer == 'fp'
			&& ($CFG->filter_poodll_handleflv
				|| $CFG->filter_poodll_handlemp4
				|| $CFG->filter_poodll_handlemp3)
		) {
			return true;
		} else {
			return false;
		}
	}

//This fetches the admin console for pairwork and screencasting
	public static function fetch_poodllconsole($runtime)
	{
		global $CFG, $USER, $COURSE;

		$broadcastkey = "1234567";
		$mename = "";

		//Set the camera prefs
		$capturewidth = $CFG->filter_poodll_capturewidth;
		$captureheight = (string)(0.75 * intval($CFG->filter_poodll_capturewidth));
		$capturefps = $CFG->filter_poodll_capturefps;
		$prefcam = $CFG->filter_poodll_screencapturedevice;
		$prefmic = $CFG->filter_poodll_studentmic;
		$bandwidth = $CFG->filter_poodll_bandwidth;
		$picqual = $CFG->filter_poodll_picqual;
		$cameraprefs = '&capturefps=' . $capturefps . '&captureheight=' . $captureheight . '&picqual=' . $picqual . '&bandwidth=' . $bandwidth . '&capturewidth=' . $capturewidth . '&prefmic=' . $prefmic . '&prefcam=' . $prefcam;
		$flvserver = self::fetch_mediaserver_url();
		$teacherpairstreamname = "voiceofauthority";

		//auto try ports
		$autotryports = $CFG->filter_poodll_autotryports == 1 ? "true" : "false";


		if ($mename == "" && !empty($USER->username)) {
			$mename = $USER->username;
			$mefullname = fullname($USER);
			$mepictureurl = self::fetch_user_picture($USER, 35);
		}

		//if courseid not passed in, try to get it from global
		$courseid = $COURSE->id;


		//We need a moodle serverid
		$moodleid = self::fetch_moodleid();

		//put in a coursedataurl
		$coursedataurl = $CFG->wwwroot . '/filter/poodll/poodlllogiclib.php%3F';


		//Show the buttons window if we are admin
		//Also won't receive messages intended for students if we are admin. Be aware.
		if (has_capability('mod/quiz:preview', \context_course::instance($courseid))) {
			$am = "admin";
		} else {
			$am = "0";
		}


		//here we setup the url and params for the admin console
		$baseUrl = $CFG->wwwroot . '/filter/poodll/flash/poodllconsole.lzx.swf9.swf';
		$params = '?red5url=' . urlencode($flvserver) .
			'&mename=' . $mename . '&courseid=' . $courseid .
			'&moodleid=' . $moodleid .
			'&autotryports=' . $autotryports .
			'&teacherpairstreamname=' . $teacherpairstreamname .
			$cameraprefs .
			'&coursedataurl=' . $coursedataurl . '&broadcastkey=' . $broadcastkey .
			'&lzr=swf9&runtime=swf9';

		//create our embed tags
		$partone = '<script type="text/javascript">lzOptions = { ServerRoot: \'\'};</script>';
		$parttwo = '<script type="text/javascript" src="' . $CFG->wwwroot . '/filter/poodll/flash/embed-compressed.js"></script>';
		$partthree = '<script type="text/javascript">lz.embed.swf({url: \'' . $baseUrl . $params .
			'\' , width: \'1000\', height: \'750\', id: \'lzapp_admin_console\', accessible: \'false\'});
					</script>
				<noscript>
					Please enable JavaScript in order to use this application.
				</noscript>';
		return $partone . $parttwo . $partthree;


	}


//Because the moodleid is appended to URLs in some PoodLL requests we need to urlencode.
//But some encoded characters mess up shared objects if decode is not called properly.
//Since we are just creating an id, it does not need to be reconstructed, so we just
//play it safe and call this function instead of simply urlencode($CFG->wwwroot)
	public static function fetch_moodleid()
	{
		global $CFG;
		$moodleid = $CFG->wwwroot;
		$splitindex = strpos($moodleid, ":");
		$moodleid = substr($moodleid, $splitindex + 1);
		$moodleid = str_replace("/", "_", $moodleid);
		return $moodleid;
	}

//this is the code to get the embed code for the poodllpairwork client
//We separate the embed and non embed into two functions
//unlike with clientconsole and adminconsole, because of the need for width and height params.
	public static function fetch_embeddablepairclient($runtime, $width, $height, $chat, $whiteboard, $showvideo, $whiteboardback, $useroles = false)
	{
		global $CFG;
//laszlo client expects "true" or "false"  so this line is defunct. Thoug we need to standardise how we do this.
//$showvideo = ($showvideo=="true");
		return ('
        <script type="text/javascript">
            lzOptions = { ServerRoot: \'\'};
        </script>
        <script type="text/javascript" src="' . $CFG->wwwroot . '/filter/poodll/flash/embed-compressed.js"></script>
        <script type="text/javascript">
              lz.embed.swf({url: \'' . self::fetch_pairclient($runtime, $chat, $whiteboard, $showvideo, $whiteboardback, $useroles) . '\', bgcolor: \'#cccccc\', width: \'' . $width . '\', height: \'' . $height . '\', id: \'lzapp_' . rand(100000, 999999) . '\', accessible: \'false\'});
        </script>
        <noscript>
            Please enable JavaScript in order to use this application.
        </noscript>
        ');

	}

//this is the code to get a poodllpairwork client for display without embedding
//in the poodll header section of a moodle page as an inline page, or in a popup
	public static function fetch_pairclient($runtime, $chat = true, $whiteboard = true, $showvideo = false, $whiteboardback = "", $useroles = false)
	{
		global $CFG, $USER, $COURSE;

		if (!empty($USER->username)) {
			$mename = $USER->username;
			$mefullname = fullname($USER);
			$mepictureurl = self::fetch_user_picture($USER, 120);
		} else {
			//this is meaningless currently, there is no current way to do pairs
			//with guest. Lets call it "casual poodllpairwork." Butin future it is possible
			$mename = "guest_" + rand(100000, 999999);
			$mefullname = "guest";
			$mepictureurl = "";
		}

		//Set the servername
		$flvserver = self::fetch_mediaserver_url();

		//auto try ports
		$autotryports = $CFG->filter_poodll_autotryports == 1 ? "true" : "false";

		//in order that this works effectively on tokyo.poodll.com which services multiple Moodles
		//we should change courseid (which creates a kind of virtual "room") to use the domainname of Moodle server
		$courseid = $COURSE->id;
		$moodleid = self::fetch_moodleid();

		$baseUrl = $CFG->wwwroot . '/filter/poodll/flash/newpairclient.lzx.swf9.swf';
		$params = '?red5url=' . urlencode($flvserver) . '&mename=' . $mename . '&mefullname=' . $mefullname . '&mepictureurl=' . urlencode($mepictureurl)
			. '&chat=' . $chat . '&autotryports=' . $autotryports . '&courseid=' . $courseid . '&moodleid=' . $moodleid . '&useroles=' . $useroles . '&whiteboard=' . $whiteboard . '&whiteboardback=' . $whiteboardback . '&showvideo=' . $showvideo . '&teacherallstreamname=voiceofauthority&lzproxied=false';
		return $baseUrl . $params;
	}


	public static function fetch_poodllpalette($runtime, $width = 800, $height = 300)
	{
		global $CFG, $USER, $COURSE;
//Set the servername
		$flvserver = self::fetch_mediaserver_url();
		$width = 800;

//$coursefilesurl = $CFG->wwwroot . '/lib/editor/htmlarea/poodll-coursefiles.php?id=' . $COURSE->id;
// The ID of the current module (eg moodleurl/view.php?id=X ) or in edit mode update=X
		$moduleid = optional_param('update', "-1", PARAM_INT);
		if ($moduleid == -1) {
			$moduleid = optional_param('id', "-1", PARAM_INT);
		}
		$coursefilesurl = $CFG->wwwroot . '/filter/poodll/poodlllogiclib.php?courseid=' . $COURSE->id . '&datatype=instancedirlist&paramone=ignore&paramtwo=content&moduleid=' . $moduleid;

		$componentlist = $CFG->wwwroot . '/filter/poodll/flash/componentlist.xml';
		$poodlllogicurl = $CFG->wwwroot . '/filter/poodll/poodlllogiclib.php';

//Set the camera prefs
		$capturewidth = $CFG->filter_poodll_capturewidth;
		$captureheight = (string)(0.75 * intval($CFG->filter_poodll_capturewidth));
		$capturefps = $CFG->filter_poodll_capturefps;
		$prefcam = $CFG->filter_poodll_studentcam;
		$prefmic = $CFG->filter_poodll_studentmic;
		$bandwidth = $CFG->filter_poodll_bandwidth;
		$picqual = $CFG->filter_poodll_picqual;
		$cameraprefs = '&capturefps=' . $capturefps . '&captureheight=' . $captureheight . '&picqual=' . $picqual . '&bandwidth=' . $bandwidth . '&capturewidth=' . $capturewidth . '&prefmic=' . $prefmic . '&prefcam=' . $prefcam;


		//merge config data with javascript embed code
		$params = array();
		$params['red5url'] = urlencode($flvserver);
		$params['poodlllogicurl'] = $poodlllogicurl . $cameraprefs;
		$params['courseid'] = $COURSE->id;
		$params['filename'] = 'amediafile';
		$params['coursefiles'] = urlencode($coursefilesurl);
		$params['componentlist'] = urlencode($componentlist);


		$returnString = self::fetchSWFWidgetCode('poodllpalette.lzx.swf10.swf',
			$params, $width, $height, '#FFFFFF');


		return $returnString;


	}


	public static function fetch_whiteboard($runtime, $boardname, $imageurl = "", $slave = false, $rooms = "", $width = 0, $height = 0, $mode = 'normal', $standalone = 'false')
	{
		global $CFG, $USER, $COURSE;

		$lm = new \filter_poodll\licensemanager();
		if(!$lm->validate_registrationkey($CFG->filter_poodll_registrationkey)) {
			return $lm->fetch_unregistered_content();
		}


//head off to the correct whiteboard as defined in config
		if (!self::isOldIE()) {
			switch ($CFG->filter_poodll_defaultwhiteboard) {
				case 'literallycanvas':
					$forsubmission = false;
					return self::fetchLiterallyCanvas($forsubmission, $width, $height, $imageurl);
					break;
				case 'drawingboard':
					$forsubmission = false;
					return self::fetchDrawingBoard($forsubmission, $width, $height, $imageurl);
					break;
				default:
			}
		}

//set default size if necessary
		if ($width == 0) {
			$width = $CFG->filter_poodll_whiteboardwidth;
		}
		if ($height == 0) {
			$height = $CFG->filter_poodll_whiteboardheight;
		}

//Set the servername
		$flvserver = self::fetch_mediaserver_url();


//If standalone, then lets standalonify it
		if ($standalone == 'true') {
			$boardname = "solo";
		}


//Determine if we are admin, if necessary , for slave/master mode
		if ($slave && has_capability('mod/quiz:preview', \context_course::instance($COURSE->id))) {
			$slave = false;
		}

//whats my name...? my name goddamit, I can't remember  N A mm eeeE
		$mename = $USER->username;

		//merge config data with javascript embed code
		$params = array();
		$params['red5url'] = urlencode($flvserver);
		$params['mename'] = $mename;
		$params['boardname'] = $boardname;
		$params['imageurl'] = $imageurl;
		$params['courseid'] = $COURSE->id;
		$params['rooms'] = $rooms;

		//Are  we merely a slave to the admin whiteboard ?
		if ($slave) {
			//adjust size for borders and control panel
			//the board size is the size of the drawing canvas, not the widget
			$width = $width + 20;
			$height = $height + 20;
			$widgetstring = self::fetchSWFWidgetCode('scribbleslave.lzx.swf9.swf',
				$params, $width, $height, '#FFFFFF');
		} else {
			//normal mode is a standard scribble with a cpanel
			//simple mode has a simple double click popup menu
			if ($mode == 'normal') {
				//adjust size for borders and control panel
				//the board size is the size of the drawing canvas, not the widget
				$width = $width + 205;
				$height = $height + 20;
				if ($runtime == 'js') {
					$widgetstring = self::fetchJSWidgetiFrame('scribbler.lzx.js',
						$params, $width, $height, '#FFFFFF');
				} elseif ($runtime == 'auto') {
					$widgetstring = self::fetchAutoWidgetCode('scribbler.lzx.swf9.swf',
						$params, $width, $height, '#FFFFFF');
				} else {
					$widgetstring = self::fetchSWFWidgetCode('scribbler.lzx.swf9.swf',
						$params, $width, $height, '#FFFFFF');
				}
			} else {
				//adjust size for borders and control panel
				//the board size is the size of the drawing canvas, not the widget
				$width = $width + 20;
				$height = $height + 20;
				if ($runtime == 'js') {
					$widgetstring = self::fetchJSWidgetiFrame('simplescribble.lzx.js',
						$params, $width, $height, '#FFFFFF');
				} elseif ($runtime == 'auto') {
					$widgetstring = self::fetchAutoWidgetCode('simplescribble.lzx.swf9.swf',
						$params, $width, $height, '#FFFFFF');
				} else {
					$widgetstring = self::fetchSWFWidgetCode('simplescribble.lzx.swf9.swf',
						$params, $width, $height, '#FFFFFF');
				}

			}
		}

		return $widgetstring;


	}

	public static function fetchSimpleAudioRecorder($runtime, $assigname, $userid = "", $updatecontrol = "saveflvvoice", $filename = "", $width = "350", $height = "200", $timelimit = "0")
	{
		global $CFG, $USER, $COURSE, $PAGE;

//Set the servername
		$flvserver = self::fetch_mediaserver_url();

//Set the microphone config params
		$micrate = $CFG->filter_poodll_micrate;
		$micgain = $CFG->filter_poodll_micgain;
		$micsilence = $CFG->filter_poodll_micsilencelevel;
		$micecho = $CFG->filter_poodll_micecho;
		$micloopback = $CFG->filter_poodll_micloopback;
		$micdevice = $CFG->filter_poodll_studentmic;


//If we are using course ids then lets do that
//else send -1 to widget (ignore flag)
		if ($CFG->filter_poodll_usecourseid) {
			$courseid = $COURSE->id;
		} else {
			$courseid = -1;
		}

//set up auto transcoding (mp3) or not
		if ($CFG->filter_poodll_audiotranscode) {
			$saveformat = "mp3";
		} else {
			$saveformat = "flv";
		}

//If no user id is passed in, try to get it automatically
//Not sure if  this can be trusted, but this is only likely to be the case
//when this is called from the filter. ie not from an assignment.
		if ($userid == "") $userid = $USER->username;

//Stopped using this
//$filename = $CFG->filter_poodll_filename;
		$overwritemediafile = $CFG->filter_poodll_overwrite == 1 ? "true" : "false";
		if ($updatecontrol == "saveflvvoice") {
			$savecontrol = "<input name='saveflvvoice' type='hidden' value='' id='saveflvvoice' />";
		} else {
			$savecontrol = "";
		}

//auto try ports, try 2 x on standard port, then 80, then 1935,then 80,1935 ad nauseum
		$autotryports = $CFG->filter_poodll_autotryports == 1 ? "yes" : "no";

		$params = array();
		$params['red5url'] = urlencode($flvserver);
		$params['overwritefile'] = $overwritemediafile;
		$params['rate'] = $micrate;
		$params['gain'] = $micgain;
		$params['prefdevice'] = $micdevice;
		$params['loopback'] = $micloopback;
		$params['echosupression'] = $micecho;
		$params['silencelevel'] = $micsilence;
		$params['filename'] = "123456.flv";
		$params['assigName'] = $assigname;
		$params['course'] = $courseid;
		$params['updatecontrol'] = $updatecontrol;
		$params['saveformat'] = $saveformat;
		$params['uid'] = $userid;
		$params['timelimit'] = $timelimit;
		$params['autotryports'] = $autotryports;

		//fetch and merge lang params
		$langparams = self::filter_poodll_fetch_recorder_strings();
		$params = array_merge($params, $langparams);


		$returnString = self::fetchSWFWidgetCode('PoodLLAudioRecorder.lzx.swf9.swf',
			$params, $width, $height, '#CFCFCF');

		$returnString .= $savecontrol;

		return $returnString;

	}


	public static function fetchMP3SkinnedRecorderForSubmission($params, $skin)
	{
		global $CFG;
		$poodll_audio_url = $CFG->wwwroot . "/filter/poodll/mp3recorderskins";
		$params['poodll_audio_url'] = $poodll_audio_url;
		$width = "240";
		$height = "170";
		//$params['callbackjs']= 'poodll_audiosdk.audiohelper.poodllcallback';
		$iframe_src_url = new \Moodle_URL("/filter/poodll/mp3recorderskins/$skin/index.php", $params);
		$ret = \html_writer::tag('iframe', '', array('src' => $iframe_src_url->out(false), 'frameBorder' => 0, 'scrolling' => 'none', 'allowTransparency' => 'true', 'class' => 'filter_poodll_mp3skinned_recorder'));
		return $ret;


	}

	public static function fetchMP3RecorderForSubmission($updatecontrol, $contextid, $component, $filearea, $itemid, $timelimit = "0", $callbackjs = false)
	{
		global $CFG, $USER, $COURSE;

//get our HTML5 Uploader if we have a mobile device
		if (self::isMobile($CFG->filter_poodll_html5rec)) {
			if (!self::canDoUpload()) {
				$ret = "<div class='mobile_os_version_warning'>" . get_string('mobile_os_version_warning', 'filter_poodll') . "</div>";
			} else {
				$ret = self::fetch_HTML5RecorderForSubmission($updatecontrol, $contextid, $component, $filearea, $itemid, "audio", false, $callbackjs);
			}
			return $ret;

		}

//Set the microphone config params
		$micrate = $CFG->filter_poodll_micrate;
		$micgain = $CFG->filter_poodll_micgain;
		$micsilence = $CFG->filter_poodll_micsilencelevel;
		$micecho = $CFG->filter_poodll_micecho;
		$micloopback = $CFG->filter_poodll_micloopback;
		$micdevice = $CFG->filter_poodll_studentmic;

//get the recorder skin
		$skin = $CFG->filter_poodll_mp3skin;

		$size = $CFG->filter_poodll_mp3recorder_size;
//$size='tiny';
//$size='normal';

//removed from params to make way for moodle 2 filesystem params Justin 20120213
		if ($size == 'normal') {
			$width = "350";
			$height = "200";
		} else {
			$width = "240";
			$height = "170";
		}
		$poodllfilelib = $CFG->wwwroot . '/filter/poodll/poodllfilelib.php';

//we can add or remove this, but right now, testing how good it works
		$autosubmit = "true";


//If we are using course ids then lets do that
//else send -1 to widget (ignore flag)
		if ($CFG->filter_poodll_usecourseid) {
			$courseid = $COURSE->id;
		} else {
			$courseid = -1;
		}

//can we pause or not
		if ($CFG->filter_poodll_miccanpause == 1) {
			$canpause = 'true';
		} else {
			$canpause = 'false';
		}

		if ($updatecontrol == "saveflvvoice") {
			$savecontrol = "<input name='saveflvvoice' type='hidden' value='' id='saveflvvoice' />";
		} else {
			$savecontrol = "";
		}
		//setup config for recirder
		$params = array();
		$params['rate'] = $micrate;
		$params['gain'] = $micgain;
		$params['prefdevice'] = $micdevice;
		$params['loopback'] = $micloopback;
		$params['echosupression'] = $micecho;
		$params['silencelevel'] = $micsilence;
		$params['course'] = $courseid;
		$params['updatecontrol'] = $updatecontrol;
		$params['uid'] = $USER->id;
		//for file system in moodle 2
		/*
    $params['poodllfilelib'] = $poodllfilelib;
    $params['contextid'] = $contextid;
    $params['component'] = $component;
    $params['filearea'] = $filearea;
    $params['itemid'] = $itemid;
    */
		//using generic mp3 recorder these dats
		$params['posturl'] = $poodllfilelib;
		$params['p1'] = $updatecontrol;
		$params['p2'] = $contextid;
		$params['p3'] = $component;
		$params['p4'] = $filearea;
		$params['p5'] = $itemid;
		//$params['chipmunk'] = 'yes';


		$params['autosubmit'] = $autosubmit;
		$params['timelimit'] = $timelimit;
		$params['canpause'] = $canpause;
		$params['size'] = $size;

		//fetch and merge lang params
		$langparams = self::filter_poodll_fetch_recorder_strings();
		$params = array_merge($params, $langparams);

		//callbackjs
		if ($callbackjs) {
			$params['callbackjs'] = $callbackjs;
		}

		//this is the old recorder. (if it has MP3Recorder ie er, its old)
		/*
    	$returnString=  self::fetchSWFWidgetCode('PoodLLMP3Recorder.lzx.swf10.swf',
    						$params,$width,$height,'#CFCFCF');
    */

		if ($skin && $skin != 'none') {
			$returnString = self::fetchMP3SkinnedRecorderForSubmission($params, $skin);
		} else {

			$returnString = self::fetchSWFWidgetCode('PoodllMP3Record.lzx.swf10.swf',
				$params, $width, $height, '#CFCFCF');
		}

		$returnString .= $savecontrol;
		return $returnString;

	}

	/*
* The literally canvas whiteboard
*
*/
	public static function fetchLiterallyCanvas($forsubmission = true, $width = 0, $height = 0, $backimage = "",
												$updatecontrol = "", $contextid = 0, $component = "", $filearea = "", $itemid = 0,
												$callbackjs = false, $vectorcontrol = "", $vectordata = "")
	{

		global $CFG, $USER, $COURSE, $PAGE;

		//javascript upload handler
		$opts = Array();
		if ($backimage != '') {
			$opts['backgroundimage'] = $backimage;
			//$opts['bgimage'] = $backimage;
			$opts['backgroundcolor'] = 'transparent';
		} else {
			$opts['backgroundimage'] = false;
			$opts['backgroundcolor'] = 'whiteSmoke';
		}

		if ($CFG->filter_poodll_autosavewhiteboard && $forsubmission) {
			$opts['autosave'] = $CFG->filter_poodll_autosavewhiteboard;
		}
		//imageurlprefix, that LC requires
		$opts['imageurlprefix'] = $CFG->httpswwwroot . '/filter/poodll/js/literallycanvas.js/img';
		$opts['recorderid'] = 'literallycanvas_' . time() . rand(10000, 999999);
		$opts['callbackjs'] = $callbackjs;
		$opts['updatecontrol'] = $updatecontrol;
		$opts['vectorcontrol'] = $vectorcontrol;
		$opts['base64control'] = '';//do this later
		$opts['vectordata'] = $vectordata;
		//amd requires opts be passed via html if they are too much.
		$opts_html = "";

		//do what we have to do for moodle 2.9 and lower
		if ($CFG->version < 2013051400) {
			//We need this so that we can require the JSON , for json stringify
			$jsmodule = array(
				'name' => 'filter_poodll',
				'fullpath' => '/filter/poodll/module.js',
				'requires' => array('json')
			);
			//setup our JS call
			$PAGE->requires->js_init_call('M.filter_poodll.loadliterallycanvas', array($opts), false, $jsmodule);

			//load dependencies
			$PAGE->requires->js("/filter/poodll/js/literallycanvas.js/js/jquery-1.8.2.js");
			$PAGE->requires->js("/filter/poodll/js/literallycanvas.js/js/react-0.10.0.js");
			$PAGE->requires->js("/filter/poodll/js/literallycanvas.js/js/fastclick.js");
			$PAGE->requires->js("/filter/poodll/js/literallycanvas.js/js/ie_customevent.js");
			$PAGE->requires->js("/filter/poodll/js/literallycanvas.js/js/literallycanvas.min.js");
		} else {

			//we encode the options and send them to html. Moodle doesn't like them cluttering the JS up
			//when using AMD
			$jsonstring = json_encode($opts);
			$opts_html = \html_writer::tag('input', '', array('id' => 'amdopts_' . $opts['recorderid'], 'type' => 'hidden', 'value' => $jsonstring));
			//$PAGE->requires->js_call_amd("filter_poodll/literallycanvas_amd", 'loadliterallycanvas', array($opts));
			$PAGE->requires->js_call_amd("filter_poodll/literallycanvas_amd", 'loadliterallycanvas', array(array('recorderid' => $opts['recorderid'])));
		}


		//removed from params to make way for moodle 2 filesystem params Justin 20120213
		if ($width == 0) {
			$width = $CFG->filter_poodll_whiteboardwidth;
		}
		if ($height == 0) {
			$height = $CFG->filter_poodll_whiteboardheight;
		}
		$poodllfilelib = $CFG->wwwroot . '/filter/poodll/poodllfilelib.php';

		//add the height of the control area, so that the user spec dimensions are the canvas size
		$canvasheight = $height;
		$canvaswidth = $width;
		$height = $height + 65;
		$width = $width + 60;


		//the control to put the filename of our picture
		if ($updatecontrol == "saveflvvoice") {
			$savecontrol = "<input name='saveflvvoice' type='hidden' value='' id='saveflvvoice' />";
		} else {
			$savecontrol = "";
		}

		//set media type
		$mediatype = "image";


		//this won't work in a quiz, and throws an error about trying to add to page head,
		//when page head has already been output. So copy contents of this file to styles.css in poodllfilter
		//$PAGE->requires->css(new \moodle_url($CFG->wwwroot . '/filter/poodll/js/literallycanvas.js/css/literallycanvas.css'));


		//save button
		$savebutton = "<input type=\"hidden\" id=\"" . $opts['recorderid'] . "_updatecontrol\" value=\"$updatecontrol\" />";
		$savebutton .= "<input type=\"hidden\" id=\"" . $opts['recorderid'] . "_contextid\" value=\"$contextid\" />";
		$savebutton .= "<input type=\"hidden\" id=\"" . $opts['recorderid'] . "_component\" value=\"$component\" />";
		$savebutton .= "<input type=\"hidden\" id=\"" . $opts['recorderid'] . "_mediatype\" value=\"$mediatype\" />";
		$savebutton .= "<input type=\"hidden\" id=\"" . $opts['recorderid'] . "_filearea\" value=\"$filearea\" />";
		$savebutton .= "<input type=\"hidden\" id=\"" . $opts['recorderid'] . "_itemid\" value=\"$itemid\" />";

		//justin 20140521 vectordata
		$savebutton .= "<input type=\"hidden\" id=\"" . $opts['recorderid'] . "_vectorcontrol\" value=\"$vectorcontrol\" />";

		$savebutton .= "<input type=\"hidden\" id=\"" . $opts['recorderid'] . "_fileliburl\" value=\"$poodllfilelib\" />";
		//amd opts
		$savebutton .= $opts_html;

		if (array_key_exists('autosave', $opts)) {
			$buttonclass = "w_btn";
		} else {
			$buttonclass = "p_btn";
		}
		$savebutton .= "<button type=\"button\" id=\"" . $opts['recorderid'] . "_btn_upload_whiteboard\" class=\"$buttonclass\">"
			. get_string('whiteboardsave', 'filter_poodll') .
			"</button>";


		//message container
		$progresscontrols = "<div id=\"" . $opts['recorderid'] . "_messages\"></div>";


		//container of whiteboard, bgimage and other bits and pieces.
		//add a buffer background image if necessary
		$lcOpen = "<div class='whiteboard-wrapper' style='width:" . $width . "px; height:" . $height . "px;'>
			<div class='fs-container' style='width:" . $width . "px; height:" . $height . "px;'>
			<div id='" . $opts['recorderid'] . "_literally' class='literally'><canvas></canvas></div></div>";
		if ($opts['backgroundimage']) {
			$lcOpen .= " <img id='" . $opts['recorderid'] . "_separate-background-image' style='display: none;' src='" . $opts['backgroundimage'] . "'/>";
		}
		$lcClose = "</div>";

		//add save control and return string
		$returnString = $lcOpen;
		if ($forsubmission) {
			$returnString .= $savebutton;
			$returnString .= $savecontrol;
			$returnString .= $progresscontrols;
		}
		$returnString .= $lcClose;

		$renderer = $PAGE->get_renderer('filter_poodll');
		return $renderer->fetchLiterallyCanvas($returnString);

	}

	/*
* The Drawingboard whiteboard
*
*/
	public static function fetchDrawingBoard($forsubmission = true, $width = 0, $height = 0, $backimage = "", $updatecontrol = "", $contextid = 0, $component = "", $filearea = "", $itemid = 0, $callbackjs = false, $vectorcontrol = '', $vectordata = '')
	{
		global $CFG, $USER, $COURSE, $PAGE;

		//javascript upload handler
		$opts = Array();
		$opts['recorderid'] = 'drawingboard_' . time() . rand(10000, 999999);
		$opts['callbackjs'] = $callbackjs;
		$opts['updatecontrol'] = $updatecontrol;
		$opts['vectorcontrol'] = $vectorcontrol;
		$opts['vectordata'] = $vectordata;

		//be careful here, only set the background IF
		//(a) we have an image and (b) we have no vectordata
		//if we have vector data, it will contain the image
		if ($backimage != '' && $vectordata == '') {
			$opts['bgimage'] = $backimage;
		}
		if ($CFG->filter_poodll_autosavewhiteboard && $forsubmission) {
			$opts['autosave'] = $CFG->filter_poodll_autosavewhiteboard;
		}


		//do what we have to do for moodle 2.9 and lower
		if (true || $CFG->version < 2013051400) {

			//We need this so that we can require the JSON , for json stringify
			$jsmodule = array(
				'name' => 'filter_poodll',
				'fullpath' => '/filter/poodll/module.js',
				'requires' => array('json')
			);

			//setup our JS call
			$PAGE->requires->js_init_call('M.filter_poodll.loaddrawingboard', array($opts), false, $jsmodule);
		} else {
			$PAGE->requires->js_call_amd("filter_poodll/drawingboard_amd", 'loaddrawingboard', array($opts));

		}
		//removed from params to make way for moodle 2 filesystem params Justin 20120213
		if ($width == 0) {
			$width = $CFG->filter_poodll_whiteboardwidth;
		}
		if ($height == 0) {
			$height = $CFG->filter_poodll_whiteboardheight;
		}
		$poodllfilelib = $CFG->wwwroot . '/filter/poodll/poodllfilelib.php';


		//the control to put the filename of our picture
		if ($updatecontrol == "saveflvvoice") {
			$savecontrol = "<input name='saveflvvoice' type='hidden' value='' id='saveflvvoice' />";
		} else {
			$savecontrol = "";
		}

		//set media type
		$mediatype = "image";


		//include other needed libraries
		$PAGE->requires->js("/filter/poodll/js/drawingboard.js/dist/drawingboard.min.js");


		//save button
		$savebutton = "<input type=\"hidden\" id=\"" . $opts['recorderid'] . "_updatecontrol\" value=\"$updatecontrol\" />";
		$savebutton .= "<input type=\"hidden\" id=\"" . $opts['recorderid'] . "_contextid\" value=\"$contextid\" />";
		$savebutton .= "<input type=\"hidden\" id=\"" . $opts['recorderid'] . "_component\" value=\"$component\" />";
		$savebutton .= "<input type=\"hidden\" id=\"" . $opts['recorderid'] . "_mediatype\" value=\"$mediatype\" />";
		$savebutton .= "<input type=\"hidden\" id=\"" . $opts['recorderid'] . "_filearea\" value=\"$filearea\" />";
		$savebutton .= "<input type=\"hidden\" id=\"" . $opts['recorderid'] . "_itemid\" value=\"$itemid\" />";
		$savebutton .= "<input type=\"hidden\" id=\"" . $opts['recorderid'] . "_fileliburl\" value=\"$poodllfilelib\" />";

		//justin 20151210 vectordata
		$savebutton .= "<input type=\"hidden\" id=\"" . $opts['recorderid'] . "_vectorcontrol\" value=\"$vectorcontrol\" />";

		if (array_key_exists('autosave', $opts)) {
			$buttonclass = "w_btn";
		} else {
			$buttonclass = "p_btn";
		}
		$savebutton .= "<button type=\"button\" id=\"" . $opts['recorderid'] . "_btn_upload_whiteboard\" class=\"$buttonclass\">"
			. get_string('whiteboardsave', 'filter_poodll') .
			"</button>";

		//message container
		$progresscontrols = "<div id=\"" . $opts['recorderid'] . "_messages\"></div>";

		//init return string with container of whiteboard
		$dbOpen = "<div class='whiteboard-wrapper' style='width:" . $width . "px; height:" . $height . "px;'>
		<div class='board drawing-board' id='" . $opts['recorderid'] . "_drawing-board-id' style='width:" . $width . "px; height:" . $height . "px;'></div>";
		$dbClose = "</div>";

		//add save control and return string
		$returnString = $dbOpen;
		if ($forsubmission) {
			$returnString .= $savecontrol;
			$returnString .= $savebutton;
			$returnString .= $progresscontrols;
		}
		$returnString .= $dbClose;

		$renderer = $PAGE->get_renderer('filter_poodll');
		return $renderer->fetchDrawingBoard($returnString);
	}


	public static function fetchWhiteboardForSubmission($updatecontrol, $contextid, $component, $filearea, $itemid, $width = 0, $height = 0, $backimage = "", $prefboard = "", $callbackjs = false, $vectorcontrol = "", $vectordata = "")
	{
		global $CFG, $USER, $COURSE;

		$lm = new \filter_poodll\licensemanager();
		if(!$lm->validate_registrationkey($CFG->filter_poodll_registrationkey)) {
			return $lm->fetch_unregistered_content();
		}


//head off to the correct whiteboard as defined in config
//we override prefboard if they couldn't use it anyway(ie old IE)
		if (self::isOldIE()) {
			$prefboard = 'poodll';
		}
		if ($prefboard == "") {
			$useboard = $CFG->filter_poodll_defaultwhiteboard;
		} else {
			$useboard = $prefboard;
		}

		switch ($useboard) {
			case 'literallycanvas':
				$forsubmission = true;
				return self::fetchLiterallyCanvas($forsubmission, $width, $height, $backimage, $updatecontrol, $contextid, $component, $filearea, $itemid, $callbackjs, $vectorcontrol, $vectordata);
				break;
			case 'drawingboard':
				$forsubmission = true;
				return self::fetchDrawingBoard($forsubmission, $width, $height, $backimage, $updatecontrol, $contextid, $component, $filearea, $itemid, $callbackjs, $vectorcontrol, $vectordata);
				break;
			default:
		}


//head off to HTML5 logic if mobile
		if (self::isMobile($CFG->filter_poodll_html5widgets)) {

			$forsubmission = true;
			return self::fetchDrawingBoard($forsubmission, $width, $height, $backimage, $updatecontrol, $contextid, $component, $filearea, $itemid, $callbackjs, $vectorcontrol, $vectordata);


		}


//If standalone submission will always be standalone ... or will it ...
//pair submissions could be interesting ..
		$boardname = "solo";
		$mode = "normal";


		//removed from params to make way for moodle 2 filesystem params Justin 20120213
		if ($width == 0) {
			$width = $CFG->filter_poodll_whiteboardwidth;
		}
		if ($height == 0) {
			$height = $CFG->filter_poodll_whiteboardheight;
		}
		$poodllfilelib = $CFG->wwwroot . '/filter/poodll/poodllfilelib.php';

//adjust size for borders and control panel
//the board size is the size of the drawing canvas, not the widget
		$width = $width + 205;
		$height = $height + 20;


		//the control to put the filename of our picture
		if ($updatecontrol == "saveflvvoice") {
			$savecontrol = "<input name='saveflvvoice' type='hidden' value='' id='saveflvvoice' />";
		} else {
			$savecontrol = "";
		}

		$params = array();


		$params['updatecontrol'] = $updatecontrol;
		$params['boardname'] = $boardname;
		$params['imageurl'] = $backimage;
		$params['courseid'] = $COURSE->id;
		//for file system in moodle 2
		$params['poodllfilelib'] = $poodllfilelib;
		$params['contextid'] = $contextid;
		$params['component'] = $component;
		$params['filearea'] = $filearea;
		$params['itemid'] = $itemid;
		$params['vectordata'] = $vectordata;
		$params['vectorcontrol'] = $vectorcontrol;
		$params['recorderid'] = 'pwboard_' . time() . rand(10000, 999999);

		if ($callbackjs) {
			$params['callbackjs'] = $callbackjs;
		}
		if ($CFG->filter_poodll_autosavewhiteboard) {
			$params['autosave'] = $CFG->filter_poodll_autosavewhiteboard;
		}

		//normal mode is a standard scribble with a cpanel
		//simple mode has a simple double click popup menu, but not submit feature
		//all submit is via normal mode, for now.
		if ($mode == 'normal') {
			$returnString = self::fetchSWFWidgetCode('scribblesubmit.lzx.swf9.swf',
				$params, $width, $height, '#FFFFFF');
		} else {

			$returnString = self::fetchSWFWidgetCode('scribblesubmit.lzx.swf9.swf',
				$params, $width, $height, '#FFFFFF');
		}


		$returnString .= $savecontrol;

		return $returnString;

	}

	public static function fetchAudioRecorderForSubmission($runtime, $assigname, $updatecontrol = "saveflvvoice", $contextid, $component, $filearea, $itemid, $timelimit = "0", $callbackjs = false)
	{
		global $CFG, $USER, $COURSE;

//get our HTML5 Uploader if we have a mobile device
		if (self::isMobile($CFG->filter_poodll_html5rec)) {
			if (!self::canDoUpload()) {
				$ret = "<div class='mobile_os_version_warning'>" . get_string('mobile_os_version_warning', 'filter_poodll') . "</div>";
			} else {
				$ret = fetch_HTML5RecorderForSubmission($updatecontrol, $contextid, $component, $filearea, $itemid, "audio", false, $callbackjs);
			}
			return $ret;

		}


//Set the servername
		$flvserver = self::fetch_mediaserver_url();
//Set the microphone config params
		$micrate = $CFG->filter_poodll_micrate;
		$micgain = $CFG->filter_poodll_micgain;
		$micsilence = $CFG->filter_poodll_micsilencelevel;
		$micecho = $CFG->filter_poodll_micecho;
		$micloopback = $CFG->filter_poodll_micloopback;
		$micdevice = $CFG->filter_poodll_studentmic;

//removed from params to make way for moodle 2 filesystem params Justin 20120213
		$userid = "dummy";
		$width = "350";
		$height = "200";
		$filename = "12345";
		$poodllfilelib = $CFG->wwwroot . '/filter/poodll/poodllfilelib.php';

//If we are using course ids then lets do that
//else send -1 to widget (ignore flag)
		if ($CFG->filter_poodll_usecourseid) {
			$courseid = $COURSE->id;
		} else {
			$courseid = -1;
		}

//set up auto transcoding (mp3) or not
		if ($CFG->filter_poodll_audiotranscode) {
			$saveformat = "mp3";
		} else {
			$saveformat = "flv";
		}

//If no user id is passed in, try to get it automatically
//Not sure if  this can be trusted, but this is only likely to be the case
//when this is called from the filter. ie not from an assignment.
		if ($userid == "") $userid = $USER->username;

//Stopped using this
//$filename = $CFG->filter_poodll_filename;
		$overwritemediafile = $CFG->filter_poodll_overwrite == 1 ? "true" : "false";
		if ($updatecontrol == "saveflvvoice") {
			$savecontrol = "<input name='saveflvvoice' type='hidden' value='' id='saveflvvoice' />";
		} else {
			$savecontrol = "";
		}

//auto try ports, try 2 x on standard port, then 80, then 1935,then 80,1935 ad nauseum
		$autotryports = $CFG->filter_poodll_autotryports == 1 ? "yes" : "no";

		//set up our params for recorder
		$params = array();
		$params['red5url'] = urlencode($flvserver);
		$params['overwritefile'] = $overwritemediafile;
		$params['rate'] = $micrate;
		$params['gain'] = $micgain;
		$params['prefdevice'] = $micdevice;
		$params['loopback'] = $micloopback;
		$params['echosupression'] = $micecho;
		$params['silencelevel'] = $micsilence;
		$params['filename'] = "123456.flv";
		$params['assigName'] = $assigname;
		$params['course'] = $courseid;
		$params['updatecontrol'] = $updatecontrol;
		$params['saveformat'] = $saveformat;
		$params['uid'] = $userid;
		//for file system in moodle 2
		$params['poodllfilelib'] = $poodllfilelib;
		$params['contextid'] = $contextid;
		$params['component'] = $component;
		$params['filearea'] = $filearea;
		$params['itemid'] = $itemid;
		$params['timelimit'] = $timelimit;
		$params['autotryports'] = $autotryports;

		//fetch and merge lang params
		$langparams = self::filter_poodll_fetch_recorder_strings();
		$params = array_merge($params, $langparams);


		if ($callbackjs) {
			$params['callbackjs'] = $callbackjs;
		}

		$returnString = self::fetchSWFWidgetCode('PoodLLAudioRecorder.lzx.swf9.swf',
			$params, $width, $height, '#CFCFCF');

		$returnString .= $savecontrol;

		return $returnString;
	}


	public static function fetch_stopwatch($runtime, $width, $height, $fontheight, $mode = 'normal', $permitfullscreen = false, $uniquename = 'uniquename')
	{
		global $CFG, $USER, $COURSE;

//Set the servername
		$flvserver = self::fetch_mediaserver_url();

//If we are using course ids then lets do that
//else send -1 to widget (ignore flag)
		if ($CFG->filter_poodll_usecourseid) {
			$courseid = $COURSE->id;
		} else {
			$courseid = -1;
		}

//get username automatically
		$userid = $USER->username;


		//Determine if we are admin, if necessary , for slave/master mode
		if (has_capability('mod/quiz:preview', \context_course::instance($COURSE->id))) {
			$isadmin = true;
		} else {
			$isadmin = false;
		}
		//merge config data with javascript embed code
		$params = array();
		$params['permitfullscreen'] = $permitfullscreen;
		$params['fontheight'] = $fontheight;
		$params['uniquename'] = $uniquename;
		$params['courseid'] = $courseid;
		$params['red5url'] = urlencode($flvserver);
		$params['mode'] = $mode;

		//LZ string if master/save  mode and not admin => show slave mode
		//otherwise show stopwatch
		if ($mode == 'master' && !$isadmin) {
			$returnString = self::fetchSWFWidgetCode('slaveview.lzx.swf9.swf',
				$params, $width, $height, '#FFFFFF');
		} elseif ($runtime == 'swf') {
			$returnString = self::fetchSWFWidgetCode('stopwatch.lzx.swf9.swf',
				$params, $width, $height, '#FFFFFF');

		} elseif ($runtime == 'js') {
			$returnString = self::fetchJSWidgetiFrame('stopwatch.lzx.js',
				$params, $width, $height, '#FFFFFF');

		} elseif ($runtime == 'auto') {
			$returnString = self::fetchAutoWidgetCode('stopwatch.lzx.swf9.swf',
				$params, $width, $height, '#FFFFFF');
		} else {
			$returnString = self::fetchAutoWidgetCode('stopwatch.lzx.swf9.swf',
				$params, $width, $height, '#FFFFFF');
		}

		return $returnString;


	}

	public static function fetch_poodllcalc($runtime, $width, $height, $size = 'normal')
	{
		global $CFG;

		//merge config data with javascript embed code
		$params = array();
		$params['size'] = $size;

		//fix up width and height which should not really be accepted as params
		switch ($size) {
			case 'normal' :
				$width = 242;
				$height = 362;
				break;
			case 'small' :
				$width = 202;
				$height = 302;
				break;
			case 'tiny' :
				$width = 172;
				$height = 262;
				break;

		}

		if ($runtime == 'js') {
			$returnString = self::fetchJSWidgetiFrame('poodllcalc.lzx.js',
				$params, $width, $height, '#FFFFFF');
		} elseif ($runtime == 'auto') {
			$returnString = self::fetchAutoWidgetCode('poodllcalc.lzx.swf9.swf',
				$params, $width, $height, '#FFFFFF');
		} else {
			$returnString = self::fetchSWFWidgetCode('poodllcalc.lzx.swf9.swf',
				$params, $width, $height, '#FFFFFF');
		}

		return $returnString;

	}

	public static function fetch_poodllscroller($start = true, $width = "300", $height = "150", $speed = 10, $repeat = 'yes', $axis = "y", $pixelshift = "2")
	{
		global $CFG, $PAGE;

//start up the scroller
		if ($start) {

			$uniqueid = rand(10000, 999999);
			//configure our options array
			//scrollspeed(1(slow) - 50(fast)) and pixelshift(1 - 5 probably) are the determinants of speed
			//every (50 - scrollspeed)ms the scroller moves (pixelshift)pixels

			$opts = array(
				"scrollerid" => $uniqueid,
				"pixelshift" => $pixelshift,
				"scrollspeed" => 51 - $speed,
				"repeat" => $repeat,
				"topspace" => "2px",
				"leftspace" => "2px",
				"framesize" => "2px",
				"axis" => $axis
			);

			//The JS array for passing in options
			$jsmodule = array(
				'name' => 'filter_poodll',
				'fullpath' => '/filter/poodll/module.js',
				'requires' => array('json')
			);


			//setup our JS call
			$PAGE->requires->js_init_call('M.filter_poodll.loadscroller', array($opts), false, $jsmodule);

			//Set the width/height of the scrollcontainer
			$dimensions = "width:" . $width . "px; height:" . $height . "px";

			//set the display class of scroll box per axis
			//x scroll shouldn't wrap words
			if ($axis == "y") {
				$axisclass = "yaxis";
			} else {
				$axisclass = "xaxis";
			}

			//The scrollbox container
			$returnString = "<div id='p_scrollboxcontainer" . $uniqueid . "' class='p_scrollboxcontainer' style='$dimensions'>";

			//the clickable "start" button
			$returnString .= "<div class='p_scroll_btn_wrapper'>";
			$returnString .= "<button type='button' onclick='M.filter_poodll.ScrollBoxStart($uniqueid)' id='p_scrollstartbutton" . $uniqueid . "' class='p_btn'>Start</button>";
			$returnString .= "</div>";


			//The scrollbox that gets scrolled
			$returnString .= "<div id='p_scrollbox" . $uniqueid . "' class='p_scrollbox $axisclass'>";

			return $returnString;
		} else {
			//close off the scroller
			$returnString = "</div>";

			$returnString .= "</div>";
			return $returnString;
		}

	}

	public static function fetch_countdowntimer($runtime, $initseconds, $usepresets, $width, $height, $fontheight, $mode = 'normal', $permitfullscreen = false, $uniquename = 'uniquename')
	{
		global $CFG, $USER, $COURSE;

//Set the servername
		$flvserver = self::fetch_mediaserver_url();

//If we are using course ids then lets do that
//else send -1 to widget (ignore flag)
		if ($CFG->filter_poodll_usecourseid) {
			$courseid = $COURSE->id;
		} else {
			$courseid = -1;
		}

//get username automatically
		$userid = $USER->username;


		//Determine if we are admin, if necessary , for slave/master mode
		if (has_capability('mod/quiz:preview', \context_course::instance($COURSE->id))) {
			$isadmin = true;
		} else {
			$isadmin = false;
		}


		//merge config data with javascript embed code
		$params = array();
		$params['initseconds'] = $initseconds;
		$params['permitfullscreen'] = $permitfullscreen;
		$params['usepresets'] = $usepresets;
		$params['fontheight'] = $fontheight;
		$params['mename'] = $userid; //this might be wrong, but do we need this?
		$params['uniquename'] = $uniquename;
		$params['courseid'] = $courseid;
		$params['red5url'] = urlencode($flvserver);
		$params['mode'] = $mode;

		//LZ string if master/save  mode and not admin => show slave mode
		//otherwise show countdown timer
		if ($mode == 'master' && !$isadmin) {
			$returnString = self::fetchSWFWidgetCode('slaveview.lzx.swf9.swf',
				$params, $width, $height, '#FFFFFF');
		} elseif ($runtime == 'swf') {
			$returnString = self::fetchSWFWidgetCode('countdowntimer.lzx.swf9.swf',
				$params, $width, $height, '#FFFFFF');
		} elseif ($runtime == 'js') {
			$returnString = self::fetchJSWidgetiFrame('countdowntimer.lzx.js',
				$params, $width, $height, '#FFFFFF');

		} elseif ($runtime == 'auto') {
			$returnString = self::fetchAutoWidgetCode('countdowntimer.lzx.swf9.swf',
				$params, $width, $height, '#FFFFFF');
		} else {
			$returnString = self::fetchAutoWidgetCode('countdowntimer.lzx.swf9.swf',
				$params, $width, $height, '#FFFFFF');


		}

		return $returnString;

	}

	public static function fetch_counter($runtime, $initcount, $usepresets, $width, $height, $fontheight, $permitfullscreen = false)
	{
		global $CFG;

		//merge config data with javascript embed code
		$params = array();
		$params['initcount'] = $initcount;
		$params['permitfullscreen'] = $permitfullscreen;
		$params['usepresets'] = $usepresets;
		$params['fontheight'] = $fontheight;


		if ($runtime == "swf") {
			$returnString = self::fetchSWFWidgetCode('counter.lzx.swf9.swf',
				$params, $width, $height, '#FFFFFF');

		} elseif ($runtime == "js") {
			$returnString = self::fetchJSWidgetiFrame('counter.lzx.js',
				$params, $width, $height, '#FFFFFF');

		} elseif ($runtime == "auto") {
			$returnString = self::fetchAutoWidgetCode('counter.lzx.swf9.swf',
				$params, $width, $height, '#FFFFFF');

		} else {
			$returnString = self::fetchAutoWidgetCode('counter.lzx.swf9.swf',
				$params, $width, $height, '#FFFFFF');
		}

		return $returnString;


	}

	public static function fetch_dice($runtime, $dicecount, $dicesize, $width, $height)
	{
		global $CFG;

		//merge config data with javascript embed code
		$params = array();
		$params['dicecount'] = $dicecount;
		$params['dicesize'] = $dicesize;

		if ($runtime == "swf") {
			$returnString = self::fetchSWFWidgetCode('dice.lzx.swf9.swf',
				$params, $width, $height, '#FFFFFF');

		} elseif ($runtime == "js") {
			$returnString = self::fetchJSWidgetiFrame('dice.lzx.js',
				$params, $width, $height, '#FFFFFF');

		} elseif ($runtime == "auto") {
			$returnString = self::fetchAutoWidgetCode('dice.lzx.swf9.swf',
				$params, $width, $height, '#FFFFFF');
		} else {
			$returnString = self::fetchAutoWidgetCode('dice.lzx.swf9.swf',
				$params, $width, $height, '#FFFFFF');
		}


		return $returnString;

	}

	public static function fetch_flashcards_revealjs($cardset, $cardsetname)
	{
		global $CFG, $COURSE, $PAGE;
		//this won't work in a quiz, and throws an error about trying to add to page head,
		//when page head has already been output. So copy contents of this file to styles.css in poodllfilter
		//$PAGE->requires->css(new \moodle_url($CFG->wwwroot . '/filter/poodll/reveal.js/css/reveal.min.css'));

		//JS
		$PAGE->requires->js(new \moodle_url($CFG->wwwroot . '/filter/poodll/reveal.js/lib/js/head.min.js'));
		//$PAGE->requires->js(new \moodle_url($CFG->wwwroot . '/filter/poodll/reveal.js/js/reveal.js'));
		//$PAGE->requires->js_init_call('M.filter_poodll.init_revealjs');

		//for AMD
		$proparray = array();
		$proparray['CSS_INJECT'] = true;
		$proparray['CSS_REVEAL'] = $CFG->wwwroot . '/filter/poodll/reveal.js/css/reveal.css';
		$proparray['CSS_THEME'] = '';//$CFG->wwwroot . '/filter/poodll/reveal.js/css/theme/sky.css';

		$PAGE->requires->js_call_amd('filter_poodll/reveal_amd', 'loadrevealjs', array($proparray));

		$dm = new \filter_poodll\dataset_manager();
		$renderer = $PAGE->get_renderer('filter_poodll');
		$carddata = $dm->fetch_revealjs_flashcards($cardset, $cardsetname);
		echo $renderer->fetch_revealjs_flashcards($carddata);
	}


	public static function fetch_flashcards($runtime, $cardset, $cardsetname, $frontcolor, $backcolor, $cardwidth, $cardheight, $randomize, $width, $height)
	{

//fetch_flashcards_revealjs($cardset,$cardsetname);
//return;


		global $CFG, $COURSE;


		//determine which of, automated or manual cardsets to use
		if (strlen($cardset) > 4 && substr($cardset, 0, 4) == 'http') {
			$fetchdataurl = $cardset;
		} elseif (strlen($cardset) > 4 && substr($cardset, -4) == ".xml") {
			//get a manually made playlist
			//$fetchdataurl= $CFG->wwwroot . "/file.php/" .  $COURSE->id . "/" . $cardset;
			$fetchdataurl = $CFG->wwwroot . "/" . $CFG->filter_poodll_datadir . "/" . $cardset;
		} else {
			//get the url to the automated medialist maker
			$fetchdataurl = $CFG->wwwroot . '/filter/poodll/poodlllogiclib.php?datatype=poodllflashcards&courseid=' . $COURSE->id
				. '&paramone=' . $cardset
				. '&paramtwo=' . $cardsetname
				. '&paramthree=' . $frontcolor
				. '&paramfour=' . $backcolor
				. '&cachekiller=' . rand(10000, 999999);
		}


		//merge config data with javascript embed code
		$params = array();
		$params['cardset'] = urlencode($fetchdataurl);
		$params['randomize'] = $randomize;
		$params['cardwidth'] = $cardwidth;
		$params['cardheight'] = $cardheight;

		if ($runtime == "js") {
			$returnString = self::fetchJSWidgetiFrame('flashcards.lzx.js',
				$params, $width, $height, '#FFFFFF');
		} elseif ($runtime == "auto") {
			$returnString = self::fetchAutoWidgetCode('flashcards.lzx.swf9.swf',
				$params, $width, $height, '#FFFFFF');

		} else {
			$returnString = self::fetchSWFWidgetCode('flashcards.lzx.swf9.swf',
				$params, $width, $height, '#FFFFFF');
		}

		return $returnString;

	}


	public static function fetchSnapshotCamera($updatecontrol = "filename", $filename = "apic.jpg", $width = "350", $height = "400")
	{
		global $CFG, $USER, $COURSE;

//Set the servername and a capture settings from config file

		$capturewidth = $CFG->filter_poodll_capturewidth;
		$captureheight = (string)(0.75 * intval($CFG->filter_poodll_capturewidth));
		$capturefps = $CFG->filter_poodll_capturefps;
		$prefcam = $CFG->filter_poodll_studentcam;
		$prefmic = $CFG->filter_poodll_studentmic;
		$bandwidth = $CFG->filter_poodll_bandwidth;
		$picqual = $CFG->filter_poodll_picqual;


		$params = array();
		$params['capturefps'] = $capturefps;
		$params['filename'] = $filename;
		$params['captureheight'] = $captureheight;
		$params['picqual'] = $picqual;
		$params['bandwidth'] = $bandwidth;
		$params['capturewidth'] = $capturewidth;
		$params['prefcam'] = $prefcam;
		$params['updatecontrol'] = $updatecontrol;
		$params['moodlewww'] = $CFG->wwwroot;

		//fetch and merge lang params
		$langparams = self::filter_poodll_fetch_recorder_strings();
		$params = array_merge($params, $langparams);

		$returnString = self::fetchSWFWidgetCode('PoodLLSnapshot.lzx.swf9.swf',
			$params, $width, $height, '#FFFFFF');


		return $returnString;


	}

	public static function fetchSnapshotCameraForSubmission($updatecontrol = "filename", $filename = "apic.jpg", $width = "350", $height = "400", $contextid, $component, $filearea, $itemid, $callbackjs = false)
	{
		global $CFG, $USER, $COURSE;

//get our HTML5 Uploader if we have a mobile device
		if (self::isMobile($CFG->filter_poodll_html5widgets)) {
			if (!self::canDoUpload()) {
				$ret = "<div class='mobile_os_version_warning'>" . get_string('mobile_os_version_warning', 'filter_poodll') . "</div>";
			} else {
				$ret = self::fetch_HTML5RecorderForSubmission($updatecontrol, $contextid, $component, $filearea, $itemid, "image", false, $callbackjs);
			}
			return $ret;
		}

//Set the servername and a capture settings from config file

		$capturewidth = $CFG->filter_poodll_capturewidth;
		$captureheight = (string)(0.75 * intval($CFG->filter_poodll_capturewidth));
		$capturefps = $CFG->filter_poodll_capturefps;
		$prefcam = $CFG->filter_poodll_studentcam;
		$prefmic = $CFG->filter_poodll_studentmic;
		$bandwidth = $CFG->filter_poodll_bandwidth;
		$picqual = $CFG->filter_poodll_picqual;

//poodllfilelib for file handling
		$poodllfilelib = $CFG->wwwroot . '/filter/poodll/poodllfilelib.php';

		$params = array();
		$params['capturefps'] = $capturefps;
		$params['filename'] = $filename;
		$params['captureheight'] = $captureheight;
		$params['picqual'] = $picqual;
		$params['bandwidth'] = $bandwidth;
		$params['capturewidth'] = $capturewidth;
		$params['prefcam'] = $prefcam;
		$params['updatecontrol'] = $updatecontrol;
		$params['moodlewww'] = $CFG->wwwroot;

		//for file system in moodle 2
		$params['poodllfilelib'] = $poodllfilelib;
		$params['contextid'] = $contextid;
		$params['component'] = $component;
		$params['filearea'] = $filearea;
		$params['itemid'] = $itemid;

		//recorder id
		$params['recorderid'] = "sshot_" . time() . rand(10000, 999999);

		//set to auto submit
		$params['autosubmit'] = 'true';

		//fetch and merge lang params
		$langparams = self::filter_poodll_fetch_recorder_strings();
		$params = array_merge($params, $langparams);

		//callbackjs
		if ($callbackjs) {
			$params['callbackjs'] = $callbackjs;
		}


		$returnString = self::fetchSWFWidgetCode('PoodLLSnapshot.lzx.swf9.swf',
			$params, $width, $height, '#FFFFFF');


		return $returnString;


	}


	public static function fetchSimpleVideoRecorder($runtime, $assigname, $userid = "", $updatecontrol = "saveflvvoice", $filename = "", $width = "350", $height = "400", $timelimit = "0")
	{
		global $CFG, $USER, $COURSE;

//Set the servername and a capture settings from config file
		$flvserver = self::fetch_mediaserver_url();
		$capturewidth = $CFG->filter_poodll_capturewidth;
		$captureheight = (string)(0.75 * intval($CFG->filter_poodll_capturewidth));
		$capturefps = $CFG->filter_poodll_capturefps;
		$prefcam = $CFG->filter_poodll_studentcam;
		$prefmic = $CFG->filter_poodll_studentmic;
		$bandwidth = $CFG->filter_poodll_bandwidth;
		$picqual = $CFG->filter_poodll_picqual;

//Set the microphone config params
		$micrate = $CFG->filter_poodll_micrate;
		$micgain = $CFG->filter_poodll_micgain;
		$micsilence = $CFG->filter_poodll_micsilencelevel;
		$micecho = $CFG->filter_poodll_micecho;
		$micloopback = $CFG->filter_poodll_micloopback;

//If we are using course ids then lets do that
//else send -1 to widget (ignore flag)
		if ($CFG->filter_poodll_usecourseid) {
			$courseid = $COURSE->id;
		} else {
			$courseid = -1;
		}

//set up auto transcoding (mp4) or not
		if ($CFG->filter_poodll_videotranscode) {
			$saveformat = "mp4";
		} else {
			$saveformat = "flv";
		}

//If no user id is passed in, try to get it automatically
//Not sure if  this can be trusted, but this is only likely to be the case
//when this is called from the filter. ie not from an assignment.
		if ($userid == "") $userid = $USER->username;

//Stopped using this
//$filename = $CFG->filter_poodll_filename;
		$overwritemediafile = $CFG->filter_poodll_overwrite == 1 ? "true" : "false";
		if ($updatecontrol == "saveflvvoice") {
			$savecontrol = "<input name='saveflvvoice' type='hidden' value='' id='saveflvvoice' />";
		} else {
			$savecontrol = "";
		}

//auto try ports, try 2 x on standard port, then 80, then 1935,then 80,1935 ad nauseum
		$autotryports = $CFG->filter_poodll_autotryports == 1 ? "yes" : "no";

		//set up config for recorders
		$params = array();
		$params['red5url'] = urlencode($flvserver);
		$params['overwritefile'] = $overwritemediafile;
		$params['rate'] = $micrate;
		$params['gain'] = $micgain;
		$params['loopback'] = $micloopback;
		$params['echosupression'] = $micecho;
		$params['silencelevel'] = $micsilence;
		$params['capturefps'] = $capturefps;
		$params['filename'] = $filename;
		$params['assigName'] = $assigname;
		$params['captureheight'] = $captureheight;
		$params['picqual'] = $picqual;
		$params['bandwidth'] = $bandwidth;
		$params['capturewidth'] = $capturewidth;
		$params['prefmic'] = $prefmic;
		$params['prefcam'] = $prefcam;
		$params['course'] = $courseid;
		$params['updatecontrol'] = $updatecontrol;
		$params['saveformat'] = $saveformat;
		$params['uid'] = $userid;
		$params['timelimit'] = $timelimit;
		$params['autotryports'] = $autotryports;

		//fetch and merge lang params
		$langparams = self::filter_poodll_fetch_recorder_strings();
		$params = array_merge($params, $langparams);


		$returnString = self::fetchSWFWidgetCode('PoodLLVideoRecorder.lzx.swf9.swf',
			$params, $width, $height, '#FFFFFF');

		$returnString .= $savecontrol;

		return $returnString;


	}

	public static function fetchVideoRecorderForSubmission($runtime, $assigname, $updatecontrol = "saveflvvoice", $contextid, $component, $filearea, $itemid, $timelimit = "0", $callbackjs = false)
	{
		global $CFG, $USER, $COURSE;

//head off to HTML5 logic if mobile
		if (self::isMobile($CFG->filter_poodll_html5rec)) {
			if (!self::canDoUpload()) {
				$ret = "<div class='mobile_os_version_warning'>" . get_string('mobile_os_version_warning', 'filter_poodll') . "</div>";
			} else {
				$ret = self::fetch_HTML5RecorderForSubmission($updatecontrol, $contextid, $component, $filearea, $itemid, "video", false, $callbackjs);
			}
			return $ret;
		}

//Set the servername and a capture settings from config file
		$flvserver = self::fetch_mediaserver_url();
		$capturewidth = $CFG->filter_poodll_capturewidth;
		$captureheight = (string)(0.75 * intval($CFG->filter_poodll_capturewidth));
		$capturefps = $CFG->filter_poodll_capturefps;
		$prefcam = $CFG->filter_poodll_studentcam;
		$prefmic = $CFG->filter_poodll_studentmic;
		$bandwidth = $CFG->filter_poodll_bandwidth;
		$picqual = $CFG->filter_poodll_picqual;

//set up auto transcoding (mp4) or not
		if ($CFG->filter_poodll_videotranscode) {
			$saveformat = "mp4";
		} else {
			$saveformat = "flv";
		}

//Set the microphone config params
		$micrate = $CFG->filter_poodll_micrate;
		$micgain = $CFG->filter_poodll_micgain;
		$micsilence = $CFG->filter_poodll_micsilencelevel;
		$micecho = $CFG->filter_poodll_micecho;
		$micloopback = $CFG->filter_poodll_micloopback;

//removed from params to make way for moodle 2 filesystem params Justin 20120213
		$userid = "dummy";
		$filename = "12345";
		$poodllfilelib = $CFG->wwwroot . '/filter/poodll/poodllfilelib.php';
		switch ($assigname) {
			case 'poodllrepository':
				$width = "298";
				$height = "340";
				break;
			default:
				$width = "350";
				$height = "400";
		}

//If we are using course ids then lets do that
//else send -1 to widget (ignore flag)
		if ($CFG->filter_poodll_usecourseid) {
			$courseid = $COURSE->id;
		} else {
			$courseid = -1;
		}

//If no user id is passed in, try to get it automatically
//Not sure if  this can be trusted, but this is only likely to be the case
//when this is called from the filter. ie not from an assignment.
		if ($userid == "") $userid = $USER->username;

//Stopped using this
//$filename = $CFG->filter_poodll_filename;
		$overwritemediafile = $CFG->filter_poodll_overwrite == 1 ? "true" : "false";
		if ($updatecontrol == "saveflvvoice") {
			$savecontrol = "<input name='saveflvvoice' type='hidden' value='' id='saveflvvoice' />";
		} else {
			$savecontrol = "";
		}

//auto try ports, try 2 x on standard port, then 80, then 1935,then 80,1935 ad nauseum
		$autotryports = $CFG->filter_poodll_autotryports == 1 ? "yes" : "no";

		//set up config for recorders
		$params = array();
		$params['red5url'] = urlencode($flvserver);
		$params['overwritefile'] = $overwritemediafile;
		$params['rate'] = $micrate;
		$params['gain'] = $micgain;
		$params['loopback'] = $micloopback;
		$params['echosupression'] = $micecho;
		$params['silencelevel'] = $micsilence;
		$params['capturefps'] = $capturefps;
		$params['filename'] = $filename;
		$params['assigName'] = $assigname;
		$params['captureheight'] = $captureheight;
		$params['picqual'] = $picqual;
		$params['bandwidth'] = $bandwidth;
		$params['capturewidth'] = $capturewidth;
		$params['prefmic'] = $prefmic;
		$params['prefcam'] = $prefcam;
		$params['course'] = $courseid;
		$params['updatecontrol'] = $updatecontrol;
		$params['saveformat'] = $saveformat;
		$params['uid'] = $userid;
		//for file system in moodle 2
		$params['poodllfilelib'] = $poodllfilelib;
		$params['contextid'] = $contextid;
		$params['component'] = $component;
		$params['filearea'] = $filearea;
		$params['itemid'] = $itemid;
		$params['timelimit'] = $timelimit;
		$params['autotryports'] = $autotryports;

		//fetch and merge lang params
		$langparams = self::filter_poodll_fetch_recorder_strings();
		$params = array_merge($params, $langparams);

		//callbackjs
		if ($callbackjs) {
			$params['callbackjs'] = $callbackjs;
		}

		$returnString = self::fetchSWFWidgetCode('PoodLLVideoRecorder.lzx.swf9.swf',
			$params, $width, $height, '#FFFFFF');

		$returnString .= $savecontrol;

		return $returnString;


	}

	public static function fetch_HTML5RecorderForSubmission($updatecontrol = "saveflvvoice", $contextid, $component, $filearea, $itemid, $mediatype = "image", $fromrepo = false, $callbackjs = false)
	{
		global $CFG, $PAGE;

		//Get our browser object for determining HTML5 options
		$browser = new Browser();

		//configure our options array for the JS Call
		$fileliburl = $CFG->wwwroot . '/filter/poodll/poodllfilelib.php';
		$opts = array();
		$opts['recorderid'] = $mediatype . 'recorder_' . time() . rand(10000, 999999);
		$opts['callbackjs'] = $callbackjs;
		$opts['updatecontrol'] = $updatecontrol;

		//setup our JS call
		if (!$fromrepo) {
			$PAGE->requires->js_init_call('M.filter_poodll.loadmobileupload', array($opts), false);
		}

		//the control to put the filename of our data. The saveflvvoice is a legacy, needs to be changed
		//check at least poodllrecordingquestion and poodll online assignment and poodll database field for it
		if ($updatecontrol == "saveflvvoice") {
			$savecontrol = "<input name='saveflvvoice' type='hidden' value='' id='saveflvvoice' />";
		} else {
			$savecontrol = "";
		}

		//depending on our media type, tell the mobile device what kind of file we want
		//we need to check for audio, because iOS still needs video (can't direct rec audio)
		switch ($mediatype) {
			case "image":
				$acceptmedia = "accept=\"image/*\"";
				break;
			case "audio":
				if (self::canSpecAudio($browser)) {
					$acceptmedia = "accept=\"audio/*\"";
				} else {
					$acceptmedia = "accept=\"video/*\"";
				}
				break;
			case "video":
				$acceptmedia = "accept=\"video/*\"";
				break;
			default:
				$acceptmedia = "";
		}

		//Output our HTML
		$fancybutton = self::showFancyButton($browser);
		$returnString = "";

		if ($fancybutton) {
			$returnString .= "<div class=\"p_btn_wrapper\">";
		}
		$returnString .= "
			$savecontrol
			<input type=\"hidden\" id=\"" . $opts['recorderid'] . "_updatecontrol\" value=\"$updatecontrol\" />
			<input type=\"hidden\" id=\"" . $opts['recorderid'] . "_contextid\" value=\"$contextid\" />
			<input type=\"hidden\" id=\"" . $opts['recorderid'] . "_component\" value=\"$component\" />
			<input type=\"hidden\" id=\"" . $opts['recorderid'] . "_filearea\" value=\"$filearea\" />
			<input type=\"hidden\" id=\"" . $opts['recorderid'] . "_itemid\" value=\"$itemid\" />
			<input type=\"hidden\" id=\"" . $opts['recorderid'] . "_mediatype\" value=\"$mediatype\" />
			<input type=\"hidden\" id=\"" . $opts['recorderid'] . "_fileliburl\" value=\"$fileliburl\" />
			<input type=\"file\" id=\"" . $opts['recorderid'] . "_poodllfileselect\" name=\"poodllfileselect[]\" $acceptmedia />
			";
		if ($fancybutton) {
			$returnString .=
				"<button type=\"button\" class=\"p_btn\">".get_string('recui_btnupload', 'filter_poodll')."</button>
		</div>";
		}
		$returnString .=
			"<div id=\"" . $opts['recorderid'] . "_progress\" class=\"p_progress\"><p></p></div>
		<div id=\"" . $opts['recorderid'] . "_messages\" class=\"p_messages\"></div>";

		return $returnString;
	}

//Audio playltest player with defaults, for use with directories of audio files
	public static function fetch_miniplayer($runtime, $src, $protocol = "http", $imageurl = "", $width = 0, $height = 0, $iframe = false)
	{
		global $CFG, $COURSE;

		//support legacy files, just in case we have an old timer ...
		if ($protocol == 'rtmp' || $protocol == 'legacy') {
			$src = $CFG->wwwroot . "/file.php/" . $COURSE->id . "/" . $src;
			$type = 'http';
		}

		if ($width == 0) {
			$width = $CFG->filter_poodll_miniplayerwidth;
		}
		if ($height == 0) {
			$height = $CFG->filter_poodll_miniplayerwidth;
		}

		$params = array();

		$params['src'] = $src;//urlencode($src);


		//for html5 players we can make a picture link to play the audio
		//the default is in the poodll filter directory
		if ($imageurl == "") {
			$imageurl = $CFG->wwwroot . "/filter/poodll/pix/MiniPlayIcon32.png";
		}


		//depending on runtime, we show a SWF or html5 player
		if ($runtime == "js" || ($runtime == "auto" && self::isMobile($CFG->filter_poodll_html5play))) {

			//the $src url as it comes from assignment and questions, is urlencoded,
			//unlikely to arrive here encoded, but lets just be safe
			//or html 5 playback will fail Justin 20121016
			$src = urldecode($src);

			$returnString = "<a onclick=\"this.firstChild.play()\"><audio src=\"$src\"></audio><img height=\"$height\" width=\"$width\" src=\"" .
				$imageurl .
				"\"/></a>";

		} else {
			//in the autolinked glossary popup, JS is not run and embed fails. In that case we use an iframe justin 20120814
			if ($iframe) {
				$returnString = self::fetchIFrameSWFWidgetCode('poodllminiplayer.lzx.swf9.swf',
					$params, $width, $height, '#FFFFFF');
			} else {
				$returnString = self::fetchSWFWidgetCode('poodllminiplayer.lzx.swf9.swf',
					$params, $width, $height, '#FFFFFF');
			}
		}


		return $returnString;


	}

//Audio playltest player with defaults, for use with directories of audio files
	public static function fetch_wordplayer($runtime, $src, $word, $fontsize, $protocol = "http", $width = "0", $height = "0", $iframe = false)
	{

		global $CFG, $COURSE;

		//support legacy files, just in case we have an old timer ...
		if ($protocol == 'rtmp' || $protocol == 'legacy') {
			$src = $CFG->wwwroot . "/file.php/" . $COURSE->id . "/" . $src;
			$type = 'http';
		}

		//fontsize if not passed in is set to the filtersettings default
		if ($fontsize == 0) {
			$fontsize = $CFG->filter_poodll_wordplayerfontsize;
		}

		if ($width == 0 || $height == 0) {
			$height = $fontsize + (int)($fontsize * 0.5);
			$width = (int)($fontsize * 0.8) * strlen($word);
		}

		$params = array();
		//$params['red5url'] = urlencode($flvserver);
		$params['src'] = urlencode($src);
		$params['word'] = $word;
		$params['fontsize'] = $fontsize;

		//depending on runtime, we show a SWF or html5 player
		if ($runtime == "js" || ($runtime == "auto" && self::isMobile($CFG->filter_poodll_html5play))) {

			//the $src url as it comes from assignment and questions, is urlencoded,
			//unlikely to arrive here encoded, but lets just be safe
			//or html 5 playback will fail Justin 20121016
			$src = urldecode($src);

			$returnString = "<a onclick=\"this.firstChild.play()\"><audio src=\"$src\"></audio>$word</a>";

		} else {
			//in the autolinked glossary popup, JS is not run and embed fails. In that case we use an iframe justin 20120814
			if ($iframe) {
				$returnString = self::fetchIFrameSWFWidgetCode('poodllwordplayer.lzx.swf9.swf',
					$params, $width, $height, '#FFFFFF');
			} else {
				$returnString = self::fetchSWFWidgetCode('poodllwordplayer.lzx.swf9.swf',
					$params, $width, $height, '#FFFFFF');
			}
		}


		return $returnString;


	}

//Plays audio file only once
	public static function fetch_onceplayer($runtime, $src, $protocol = "http", $width = 0, $height = 0, $iframe = false)
	{
		global $CFG, $COURSE;

		//support legacy files, just in case we have an old timer ...
		if ($protocol == 'rtmp' || $protocol == 'legacy') {
			$src = $CFG->wwwroot . "/file.php/" . $COURSE->id . "/" . $src;
			$type = 'http';
		}

		if ($width == 0) {
			$width = 250;
		}
		if ($height == 0) {
			$height = 100;
		}

		$params = array();

		$params['src'] = $src;//urlencode($src);


		//depending on runtime, we would show a SWF or html5 player
		//but not html5 player yet
		//use iframe or not
		if ($iframe) {
			$returnString = self::fetchIFrameSWFWidgetCode('onceplayer.lzx.swf9.swf',
				$params, $width, $height, '#FFFFFF');
		} else {
			$returnString = self::fetchSWFWidgetCode('onceplayer.lzx.swf9.swf',
				$params, $width, $height, '#FFFFFF');
		}


		return $returnString;


	}

//Audio playlisttest player with defaults, for use with directories of audio files
	public static function fetchAudioTestPlayer($runtime, $playlist, $protocol = "", $width = "400", $height = "150", $filearea = "content", $usepoodlldata = true)
	{
		global $CFG, $USER, $COURSE;

		$moduleid = optional_param('id', 0, PARAM_INT);    // The ID of the current module (eg moodleurl/view.php?id=X )

//Set our servername .
		$flvserver = self::fetch_mediaserver_url();

////if usepoodlldata, then set that to filearea
		if ($usepoodlldata) {
			$filearea = "poodlldata";
		}


//determine which of, automated or manual playlists to use
		if (strlen($playlist) > 4 && substr($playlist, -4) == ".xml") {
			//get a manually made playlist
			$fetchdataurl = $CFG->wwwroot . "/" . $CFG->filter_poodll_datadir . "/" . $playlist;
		} else {
			//get the url to the automated medialist maker
			$fetchdataurl = $CFG->wwwroot . '/filter/poodll/poodlllogiclib.php?datatype=poodllaudiolist'
				. '&courseid=' . $COURSE->id
				. '&moduleid=' . $moduleid
				. '&paramone=' . $playlist
				. '&paramtwo=' . $protocol
				. '&paramthree=' . $filearea
				. '&cachekiller=' . rand(10000, 999999);
		}


		$params = array();
		$params['red5url'] = urlencode($flvserver);
		$params['playertype'] = $protocol;
		$params['playlist'] = urlencode($fetchdataurl);

		$returnString = self::fetchSWFWidgetCode('poodllaudiotestplayer.lzx.swf9.swf',
			$params, $width, $height, '#FFFFFF');

		return $returnString;

	}

//Audio playlist player with defaults, for use with directories of audio files
	public static function fetchAudioListPlayer($runtime, $playlist, $filearea = "content", $protocol = "", $width = "400", $height = "350", $sequentialplay = "true", $useplayer, $showplaylist, $usepoodlldata = false)
	{
		global $CFG, $USER, $COURSE;

		$moduleid = optional_param('id', 0, PARAM_INT);    // The ID of the current module (eg moodleurl/view.php?id=X )


//determine if we are on a mobile device or not
		$ismobile = self::isMobile($CFG->filter_poodll_html5play);

		//if its a poodll player we want an xml feed
		//if its jw or fp we want an rss feed
		//if we are ipads or html playlists + fp, we wont use a data feed, we will use a list of links
		//so in that case we pass a "" and just spit out the links.
		switch ($useplayer) {
			case "pd":
				$datatype = "poodllaudiolist";
				break;
			case "jw":
				$datatype = "poodllrsslist";
				break;
			case "fp":
				if ($showplaylist) {
					$datatype = "";
				} else {
					$datatype = "poodllrsslist";
				}
				break;
		}

		//if we are using poodll data, flag that in the filearea param
		if ($usepoodlldata) {
			$filearea = "poodlldata";
		}


		//determine playlist url if necessary, if we are using fp player and a visible list we don't need this
		$fetchdataurl = "";
		if ($datatype != "") {
			//get the url to the automated medialist maker
			//$fetchdataurl= $CFG->wwwroot . '/filter/poodll/poodlllogiclib.php?datatype=poodllaudiolist'
			$fetchdataurl = $CFG->wwwroot . '/filter/poodll/poodlllogiclib.php?datatype=' . $datatype
				. '&courseid=' . $COURSE->id
				. '&moduleid=' . $moduleid
				. '&paramone=' . $playlist
				. '&paramtwo=' . $protocol
				. '&paramthree=' . $filearea
				. '&cachekiller=' . rand(10000, 999999);
		}


		//If poodll player is not default, use flowplayer it will handle mobile and flash
		if ($useplayer != "pd") {
			$returnString = "";
			//if html playlist use links as list
			if ($showplaylist) {
				$returnString = self::fetch_poodllaudiolist($moduleid, $COURSE->id, $playlist, "http", $filearea, "alist");
				$returnString .= "<br clear='all'/>";
				//get a flowplayer without a datafeed
				//size is hardcoded to match images pulled from styles.css in pooodll filter
				$returnString .= self::fetchFlowPlayerCode($width, 40, "/", "audiolist", $ismobile, "", $sequentialplay);

				//if rss playlist use url of datafeed and pass to flowplayer
			} else {
				//get a flowplayer using the data feed
				//size is hardcoded to match images pulled from styles.css in pooodll filter
				$returnString .= self::fetchFlowPlayerCode($width, 40, "/", "audiolist", $ismobile, $fetchdataurl, $sequentialplay);
			}

			return $returnString;

			//If this is a poodll player playlist
		} else {
			//Set our servername .
			$flvserver = self::fetch_mediaserver_url();


			$params = array();
			$params['red5url'] = urlencode($flvserver);
			$params['playertype'] = $protocol;
			$params['sequentialplay'] = $sequentialplay;
			$params['playlist'] = urlencode($fetchdataurl);

			$returnString = self::fetchSWFWidgetCode('poodllaudiolistplayer.lzx.swf9.swf',
				$params, $width, $height, '#FFFFFF');

			return $returnString;
		}
	}

//Audio player with defaults, for use with PoodLL filter
	public static function fetchSimpleAudioPlayer($runtime, $rtmp_file, $protocol = "", $width = "450", $height = "25",
												  $embed = false, $embedstring = "Play", $permitfullscreen = false,
												  $usepoodlldata = false, $splashurl = '')
	{
		global $CFG, $COURSE, $PAGE;

//Set our servername .
		$flvserver = self::fetch_mediaserver_url();
		$courseid = $COURSE->id;
		$useplayer = $CFG->filter_poodll_defaultplayer;

//determine if we are on a mobile device or not
		$ismobile = self::isMobile($CFG->filter_poodll_html5play);

		//Set our use protocol type
		//if one was not passed, then it may have been tagged to the url
		//this was the old way.
		if ($protocol == "") {
			$type = "rtmp";
			if (strlen($rtmp_file) > 5) {
				$protocol = substr($rtmp_file, 0, 5);
				switch ($protocol) {
					case "yutu:":
						$rtmp_file = substr($rtmp_file, 5);
						$rtmp_file = getYoutubeLink($rtmp_file);
						$type = "http";
						break;
					case "http:":
						$rtmp_file = substr($rtmp_file, 5);
						$type = "http";
						break;
					case "rtmp:":
						$rtmp_file = substr($rtmp_file, 5);
					default:
						$type = "rtmp";

				}

			}//end of if strlen(rtmpfile) > 4

			//If we have one passed in, lets set it to our type
		} else {
			switch ($protocol) {
				case "yutu":
					$rtmp_file = getYoutubeLink($rtmp_file);
					$type = "http";
					break;
				case "http":
				case "rtmp":
				case "legacy":
				default:
					$type = $protocol;

			}
		}

		//If we are using the legacy coursefiles, we want to fall into this code
		//this is just a temporary fix to achieve this. Justin 20111213
		if ($protocol == 'rtmp' || $protocol == 'legacy') {
			$rtmp_file = $CFG->wwwroot . "/file.php/" . $courseid . "/" . $rtmp_file;
			$type = 'http';
			//if using poodlldata, take stub from base dir + poodlldatadir then add file name/path
		} else if ($usepoodlldata) {
			$baseURL = $CFG->{'wwwroot'} . "/" . $CFG->{'filter_poodll_datadir'} . "/";
			$rtmp_file = $baseURL . $rtmp_file;
		}


		//if we are using javascript to detect and insert (probably best..?)
		$params = array();
		$params['red5url'] = urlencode($flvserver);
		$params['playertype'] = $type;
		$params['mediapath'] = $rtmp_file;
		$params['permitfullscreen'] = $permitfullscreen;


		//establish the fileextension
		$ext = substr($rtmp_file, -3);

		//if we are on mobile we want to play mp3 using html5 tags
		//if we have a file type that flash wont play, default to runtime = js
		if ($runtime == 'auto') {
			if ($ismobile) {
				$runtime = 'js';
			} else if ($ext == '3gp' || $ext == 'ebm' || $ext == '3g2') {
				$runtime = 'js';
			} else {
				$runtime = 'swf';
			}
		}//end of if runtime=auto


		if ($runtime == 'js' && ($CFG->filter_poodll_html5controls == 'native')) {
			$returnString = "";


			//the $rtmp_file as it comes from assignment and questions, is urlencoded, we need to decode
			//or html 5 playback will fail Justin 20121016
			$rtmp_file = urldecode($rtmp_file);

			//figure out the mime type by the extension
			$mime = "";
			switch ($ext) {
				case "mov":
				case "mp4":
					$mime = "video/mp4";
					break;
				case "3gp":
					$mime = "video/3gpp";
					break;
				case "3g2":
					$mime = "video/3gpp2";
					break;
				case "ebm":
					$mime = "video/webm";
					break;
				default:
					$mime = "video/mp4";
			}

			//The HTML5 Code (can be used on its own OR with the mediaelement code below it
			$returnString .= "<audio controls width='" . $width . "' height='" . $height . "'>
								<source type='" . $mime . "' src='" . $rtmp_file . "'/>
								</audio>";


			//if we are using SWF
		} else {


			//Flowplayer
			if ($useplayer == "fp" || $CFG->filter_poodll_html5controls == "js") {

				$returnString = self::fetchFlowPlayerCode($width, $height, $rtmp_file, "audio", $ismobile, "", false, $splashurl);

				//JW player
			} else if ($useplayer == "jw") {
				$flashvars = array();
				$flashvars['file'] = $rtmp_file;
				$flashvars['autostart'] = 'false';
				$returnString = self::fetchSWFObjectWidgetCode('jwplayer.swf',
					$flashvars, $width, $height, '#FFFFFF');

				//if the file is an mp3, and we are using poodll player, don't handle it
				//either pass it to multi media plugin filter or pass it flowplayer
				// PoodLL player can't mp3 without RTMP
			} else if (substr($rtmp_file, -4) == '.mp3') {
				$returnString = self::fetchFlowPlayerCode($width, $height, $rtmp_file, "audio", $ismobile);
				//$returnString= "<a href=\"$rtmp_file\">$rtmp_file</a>";

				//PoodLL Player
			} else {

				$returnString = self::fetchSWFWidgetCode('poodllaudioplayer.lzx.swf9.swf',
					$params, $width, $height, '#FFFFFF');
			}

			//regardless of swf player, add a download icon if appropriate
			$context = \context_course::instance($COURSE->id);
			$has_permission = has_capability('filter/poodll:candownloadmedia', $context);
			if ($CFG->filter_poodll_download_media_ok && $has_permission) {
				$returnString .= "<a href='" . urldecode($rtmp_file) . "'>"
					. "&nbsp;<img src='" . $CFG->{'wwwroot'} . "/filter/poodll/pix/download.gif' alt='download' />"
					. "</a>";
			}

		}

		$renderer = $PAGE->get_renderer('filter_poodll');
		return $renderer->fetchAudioPlayer($returnString);

	}


//Video player with defaults, for use with PoodLL filter
	public static function fetchSimpleVideoPlayer($runtime, $rtmp_file, $width = "400", $height = "380", $protocol = "", $embed = false, $permitfullscreen = false, $embedstring = "Play", $splashurl = "", $useplayer = "")
	{
		global $CFG, $USER, $COURSE, $PAGE;

//Set our servername .
		$flvserver = self::fetch_mediaserver_url();
		$courseid = $COURSE->id;

//Set the playertype to use
		if ($protocol == "yutu") {
			$useplayer = "pd";
		} else if ($useplayer == "") {
			$useplayer = $CFG->filter_poodll_defaultplayer;
		}

//determine if we are on a mobile device or not
		$ismobile = self::isMobile($CFG->filter_poodll_html5play);
//$ismobile=true;


		//Massage the media file name if we have a username variable passed in.
		//This allows us to show different video to each student
		if (isset($USER->username)) {
			$rtmp_file = str_replace("@@username@@", $USER->username, $rtmp_file);
		}

		//Determine if we are admin, admins can always fullscreen
		if (has_capability('mod/quiz:preview', \context_course::instance($COURSE->id))) {
			$permitfullscreen = 'true';
		}


		//Set our use protocol type
		//if one was not passed, then it may have been tagged to the url
		//this was the old way.
		if ($protocol == "") {
			$type = "rtmp";
			if (strlen($rtmp_file) > 5) {
				$protocol = substr($rtmp_file, 0, 5);
				switch ($protocol) {
					case "yutu:":
						$rtmp_file = substr($rtmp_file, 5);
						$type = "yutu";
						break;
					case "http:":
						$rtmp_file = substr($rtmp_file, 5);
						$type = "http";
						break;
					case "rtmp:":
						$rtmp_file = substr($rtmp_file, 5);
					default:
						$type = "rtmp";

				}

			}//end of if strlen(rtmpfile) > 4

			//If we have one passed in, lets set it to our type
		} else {
			switch ($protocol) {
				case "yutu":
				case "http":
				case "rtmp":
				case "legacy":
				default:
					$type = $protocol;

			}
		}

		//If we are using the legacy coursefiles, we want to fall into this code
		//this is just a temporary fix to achieve this. Justin 20111213
		if ($protocol == 'rtmp' || $protocol == 'legacy') {
			$rtmp_file = $CFG->wwwroot . "/file.php/" . $courseid . "/" . $rtmp_file;
			$type = 'http';
		}

		//If we want to avoid loading multiple players on the screen, we use this script
		//to load players ondemand
		//this does screw up updating the entry on the page,
		//which is seen after marking a single audio/vide assignment and returning to the list
		//poodllonline assignment
		//if ($embed){
		if (false) {
			$lzid = "lzapp_videoplayer_" . rand(100000, 999999);
			$returnString = "
	  <div id='$lzid' class='player'>
        <a href='#' onclick=\"javascript:loadVideoPlayer('$rtmp_file', '$lzid', 'sample_$lzid', '$width', '$height'); return false;\">$embedstring </a>
      </div>
		";


			return $returnString;

		} else {

			$params = array();
			$params['red5url'] = urlencode($flvserver);
			$params['playertype'] = $type;
			$params['mediapath'] = $rtmp_file;
			$params['permitfullscreen'] = $permitfullscreen;

			//establish the fileextension
			$ext = substr($rtmp_file, -3);

			//if we are on mobile we want to play mp3 using html5 tags
			if ($runtime == 'auto') {
				if ($ismobile) {
					$runtime = 'js';
				} else if ($ext == '3gp' || $ext == 'ebm' || $ext == '3g2') {
					$runtime = 'js';
				} else {
					$runtime = 'swf';
				}
			}//end of if runtime=auto


			if ($runtime == 'js' && ($CFG->filter_poodll_html5controls == 'native')) {
				$returnString = "";

				//get a poster image if it is appropriate
				$poster = "";
				if ($splashurl != "") {
					$poster = $splashurl;
				} else if ($CFG->filter_poodll_videosplash) {
					if ($CFG->filter_poodll_thumbnailsplash) {
						$splashurl = self::fetchVideoSplash($rtmp_file);
					} else {
						$splashurl = false;
					}
					if (!$splashurl) {
						$splashurl = $CFG->wwwroot . "/filter/poodll/flowplayer/videosplash.jpg";
					}
					$poster = $splashurl;
				}

				//the $rtmp_file as it comes from assignment and questions, is urlencoded, we need to decode
				//or html 5 playback will fail Justin 20121016
				$rtmp_file = urldecode($rtmp_file);

				//figure out the mime type by the extension
				$mime = "";
				switch ($ext) {
					case "mov":
					case "mp4":
						$mime = "video/mp4";
						break;
					case "3gp":
						$mime = "video/3gpp";
						break;
					case "3g2":
						$mime = "video/3gpp2";
						break;
					case "ebm":
						$mime = "video/webm";
						break;
					default:
						$mime = "video/mp4";
				}

				//return the html5 video code
				$returnString .= "<video controls poster='" . $poster . "' width='" . $width . "' height='" . $height . "'>
								<source type='" . $mime . "' src='" . $rtmp_file . "'/>
							</video>";


				//if we are using SWF
			} else {


				//Flowplayer
				if ($useplayer == "fp" || $CFG->filter_poodll_html5controls == "js") {

					$returnString = self::fetchFlowPlayerCode($width, $height, $rtmp_file, "video", $ismobile, "", false, $splashurl);

					//JW player
				} else if ($useplayer == "jw") {
					$flashvars = array();
					$flashvars['file'] = $rtmp_file;
					$flashvars['autostart'] = 'false';
					$returnString = self::fetchSWFObjectWidgetCode('jwplayer.swf',
						$flashvars, $width, $height, '#FFFFFF');


					//PoodLL Player
				} else {
					$params['playerbackcolor'] = $CFG->filter_poodll_fp_bgcolor;
					$returnString = self::fetchSWFWidgetCode('poodllvideoplayer.lzx.swf9.swf',
						$params, $width, $height, '#FFFFFF');
				}

				$context = \context_course::instance($COURSE->id);
				$has_permission = has_capability('filter/poodll:candownloadmedia', $context);
				if ($CFG->filter_poodll_download_media_ok && $has_permission) {
					$returnString .= "<a href='" . urldecode($rtmp_file) . "'>"
						. "&nbsp;<img src='" . $CFG->{'wwwroot'} . "/filter/poodll/pix/download.gif' alt='download' />"
						. "</a>";
				}

			}

			$renderer = $PAGE->get_renderer('filter_poodll');
			return $renderer->fetchVideoPlayer($returnString);
		}

	}


	public static function fetchSmallVideoGallery($runtime, $playlist, $filearea = "content", $protocol = "", $width, $height, $permitfullscreen = false, $usepoodlldata = false)
	{
		global $CFG, $USER, $COURSE;

//Set the servername
		$courseid = $COURSE->id;
		$flvserver = self::fetch_mediaserver_url();

		$moduleid = optional_param('id', 0, PARAM_INT);    // The ID of the current module (eg moodleurl/view.php?id=X )

//If we are using poodll data we fetch from data dir
//So we just flag that in the filearea parameter
		if ($usepoodlldata) {
			$filearea = "poodlldata";
		}


//set size params
		if ($width == '') {
			$width = $CFG->filter_poodll_smallgallwidth;
		}
		if ($height == '') {
			$height = $CFG->filter_poodll_smallgallheight;
		}

//Determine if we are admin, admins can always fullscreen
		if (has_capability('mod/quiz:preview', \context_course::instance($COURSE->id))) {
			$permitfullscreen = 'true';
		}


//determine which of, automated or manual playlists to use
		if (strlen($playlist) > 4 && substr($playlist, -4) == ".xml") {
			//get a manually made playlist
			$fetchdataurl = $CFG->wwwroot . "/file.php/" . $courseid . "/" . $playlist;
		} else {

			//get the url to the automated medialist maker
			$fetchdataurl = $CFG->wwwroot . '/filter/poodll/poodlllogiclib.php?datatype=poodllmedialist'
				. '&courseid=' . $COURSE->id
				. '&moduleid=' . $moduleid
				. '&paramone=' . $playlist
				. '&paramtwo=' . $protocol
				. '&paramthree=' . $filearea
				. '&cachekiller=' . rand(10000, 999999);
		}

		$params = array();
		$params['red5url'] = urlencode($flvserver);
		$params['playlist'] = urlencode($fetchdataurl);
		$params['protocol'] = urlencode($protocol);
		$params['permitfullscreen'] = urlencode($permitfullscreen);

		$returnString = self::fetchSWFWidgetCode('smallvideogallery.lzx.swf9.swf',
			$params, $width, $height, '#D5FFFA');

		return $returnString;


	}

	public static function fetchBigVideoGallery($runtime, $playlist, $filearea = "content", $protocol, $width, $height, $usepoodlldata = false)
	{
		global $CFG, $USER, $COURSE;

//Set the servername
		$courseid = $COURSE->id;
		$flvserver = self::fetch_mediaserver_url();

		$moduleid = optional_param('id', 0, PARAM_INT);    // The ID of the current module (eg moodleurl/view.php?id=X )

//If we are using poodll data we fetch from data dir
//So we just flag that in the filearea parameter
		if ($usepoodlldata) {
			$filearea = "poodlldata";
		}


//set size params
		if ($width == '') {
			$width = $CFG->filter_poodll_biggallwidth;
		}
		if ($height == '') {
			$height = $CFG->filter_poodll_biggallheight;
		}

//determine which of, automated or manual playlists to use
		if (strlen($playlist) > 4 && substr($playlist, -4) == ".xml") {
			//get a manually made playlist
			$fetchdataurl = $CFG->wwwroot . "/file.php/" . $courseid . "/" . $playlist;
		} else {
			//get the url to the automated medialist maker
			//get the url to the automated medialist maker
			$fetchdataurl = $CFG->wwwroot . '/filter/poodll/poodlllogiclib.php?datatype=poodllmedialist'
				. '&courseid=' . $COURSE->id
				. '&moduleid=' . $moduleid
				. '&paramone=' . $playlist
				. '&paramtwo=' . $protocol
				. '&paramthree=' . $filearea
				. '&cachekiller=' . rand(10000, 999999);
		}

		$params = array();
		$params['red5url'] = urlencode($flvserver);
		$params['playlist'] = urlencode($fetchdataurl);

		//if($runtime=='swf'){
		if (true) {
			//set the flash widget suffix
			$widget = "bigvideogallery.lzx.swf9.swf";
			$returnString = self::fetchSWFWidgetCode($widget, $params, $width, $height, '#D5FFFA');
		} else {
			//set the JS widget suffix
			$widget = "bigvideogallery.lzx.js";
			$returnString = self::fetchJSWidgetiFrame($widget, $params, $width, $height, '#D5FFFA');
		}

		return $returnString;

	}


	public static function filter_poodll_fetch_recorder_strings()
	{
		$params = array();

		//Get localised labels:
		$params['ui_record'] = urlencode(get_string('recui_record', 'filter_poodll'));
		$params['ui_play'] = urlencode(get_string('recui_play', 'filter_poodll'));
		$params['ui_continue'] = urlencode(get_string('recui_continue', 'filter_poodll'));
		$params['ui_pause'] = urlencode(get_string('recui_pause', 'filter_poodll'));
		$params['ui_stop'] = urlencode(get_string('recui_stop', 'filter_poodll'));
		$params['ui_time'] = urlencode(get_string('recui_time', 'filter_poodll'));
		$params['ui_audiogain'] = urlencode(get_string('recui_audiogain', 'filter_poodll'));
		$params['ui_silencelevel'] = urlencode(get_string('recui_silencelevel', 'filter_poodll'));
		$params['ui_echo'] = urlencode(get_string('recui_echo', 'filter_poodll'));
		$params['ui_loopback'] = urlencode(get_string('recui_loopback', 'filter_poodll'));
		$params['ui_audiorate'] = urlencode(get_string('recui_audiorate', 'filter_poodll'));
		$params['ui_on'] = urlencode(get_string('recui_on', 'filter_poodll'));
		$params['ui_off'] = urlencode(get_string('recui_off', 'filter_poodll'));
		$params['ui_ok'] = urlencode(get_string('recui_ok', 'filter_poodll'));
		$params['ui_close'] = urlencode(get_string('recui_close', 'filter_poodll'));
		$params['ui_uploading'] = urlencode(get_string('recui_uploading', 'filter_poodll'));
		$params['ui_converting'] = urlencode(get_string('recui_converting', 'filter_poodll'));
		$params['ui_timeouterror'] = urlencode(get_string('recui_timeouterror', 'filter_poodll'));
		$params['ui_uploaderror'] = urlencode(get_string('recui_uploaderror', 'filter_poodll'));
		$params['ui_inaudibleerror'] = urlencode(get_string('recui_inaudibleerror', 'filter_poodll'));

		return $params;
	}


//helper callback public static function to sort filenames, called from poodllaudiolist
	public static function srtFilenames($a, $b)
	{
		return strcasecmp($a->get_filename(), $b->get_filename());
	}

//this function returns an rss/xml/ or link list of files for a list player
//originally it existed in poodlllogiclib.php bu t was moved here so we did not have
//to include poodlllogiclib here
	function fetch_poodllaudiolist($moduleid, $courseid, $path = "/", $playertype, $filearea, $listtype = "xml")
	{
		global $CFG, $DB, $COURSE;


		//if a single file was passed in, just play that alone.
		//for PoodlL 2 this is all we can do in a question right now
		if (strlen($path) > 4 && substr($path, -4) == ".flv") {
			switch ($listtype) {
				case "xml":
					$ret_output = "<audios>\n";
					$ret_output .= "\t<audio audioname='" . basename($path) . "' playertype='" . $playertype . "' url='" . trim($path) . "'/>\n";
					$ret_output .= "</audios>\n";
					break;

				case "rss":
					$ret_output = "<channel><title></title>";
					break;

				case "alinks":
					$ret_output = "<div class=\"poodllplaylist\">";
					$ret_output .= "<a href=\"" . trim($path) . "\"><span>" . basename($path) . "</span></a>";
					$ret_output .= "</div>";
					break;
			}

			return $ret_output;
		}


		//FIlter could submit submission/draft/content/intro as options here
		if ($filearea == "") {
			$filearea = "content";
		}


		//make sure we have a trailing slash
		if (strlen($path) > 0) {
			if (substr($path, -1) != '/') {
				$path .= "/";
			}
			if (substr($path, 0, 1) != '/') {
				$path = "/" . $path;
			}
		} else {
			$path = "/";
		}


		//set up xml/div to return
		switch ($listtype) {
			case "xml":
				$ret_output = "<audios>\n";
				break;
			case "rss":
				$ret_output = "<channel><title></title>";
				break;
			case "alist":
				$ret_output = "<div class=\"poodllplaylist\">";
				break;
		}

		if ($filearea == "poodlldata") {
			//if(strlen($path)>6 && true){
			//If we are using PoodLL Data Dir file handling, we build a list of files here:
			//=============================================
			//filter file types
			$filterstring = "/*.{flv,mp3,mp4}";
			//set up the search dir
			$baseDir = $CFG->{'dirroot'} . "/" . $CFG->{'filter_poodll_datadir'} . $path;
			$baseURL = $CFG->{'wwwroot'} . "/" . $CFG->{'filter_poodll_datadir'} . $path;
			//for debugging
			//$ret_output .= $baseDir . " " . $baseURL;
			foreach (glob($baseDir . $filterstring, GLOB_BRACE) as $filename) {
				$urltofile = $baseURL . basename($filename);
				switch ($listtype) {
					case "xml":
						$ret_output .= "\t<audio audioname='" . basename($filename) . "' playertype='" . $playertype . "' url='" . $urltofile . "'/>\n";
						break;

					//"type" was necessary for flowplayer ??? but it breaks JWPlayer
					// justin 20141220
					case "xrss":
						$ext = substr($filename, -4);
						switch ($ext) {
							case ".mp3":
								$mimetype = "audio/mpeg3";
								break;
							case ".flv":
								$mimetype = "audio/mp4";
								break;
							case ".mp4":
								$mimetype = "video/x-flv";
								break;
						}
						$ret_output .= "\t<item><title>" .
							basename($filename) . "</title><media:content url=\"" .
							trim($urltofile) . "\" type=\"" . $mimetype .
							"\"/></item>";
						break;

					case "rss":
						$ret_output .= "\t<item><title>" .
							basename($filename) . "</title><media:content url=\"" .
							trim($urltofile) . "\" /></item>";
						break;

					case "alist":
						$ret_output .= "<a href=\"" . trim($urltofile) . "\"><span>" . basename($filename) . "</span></a>";
						break;
				}

				//$xml_output .=  "\t<audio audioname='" . basename($filename) ."' playertype='" . $playertype . "' url='" . $baseURL . basename($filename). "'/>\n";
			}

			//=============================================
			//end of PoodLL Data Dir
		} else {

			//fetch info and ids about the module calling this data
			$course = $DB->get_record('course', array('id' => $courseid));
			$modinfo = get_fast_modinfo($course);
			$cm = $modinfo->get_cm($moduleid);

			//If we are using Moodle 2 file handling, we build a list of files here:
			//=============================================
			//get filehandling objects
			$browser = get_file_browser();
			$fs = get_file_storage();

			//get a handle on the module context
			$thiscontext = \context_module::instance($moduleid);
			$contextid = $thiscontext->id;

			//fetch a list of files in this area, and sort them alphabetically
			$files = $fs->get_area_files($contextid, "mod_" . $cm->modname, $filearea);
			usort($files, '\filter_poodll\poodlltools\srtFilenames');

			//loop through all the media files and load'em up
			foreach ($files as $f) {
				$filename = trim($f->get_filename());
				//if we are not a directory and filename is long enough and extension is mp3 or flv or mp4, we proceed
				if ($filename != ".") {
					if (strlen($filename) > 4) {
						$ext = substr($filename, -4);
						if ($ext == ".mp3" || $ext == ".mp4" || $ext == ".flv") {
							switch ($ext) {
								case ".mp3":
									$mimetype = "audio/mpeg3";
									break;
								case ".flv":
									$mimetype = "audio/mp4";
									break;
								case ".mp4":
									$mimetype = "video/x-flv";
									break;
							}

							//fetch our info object
							$fileinfo = $browser->get_file_info($thiscontext, $f->get_component(), $f->get_filearea(), $f->get_itemid(), $f->get_filepath(), $f->get_filename());

							//if we are at the dir level
							if ($f->get_filepath() == $path) {
								//get the url to the file and add it to the XML
								$urltofile = $fileinfo->get_url();
								switch ($listtype) {
									case "xml":
										$ret_output .= "\t<audio audioname='" . basename($filename) . "' playertype='" . $playertype . "' url='" . trim($urltofile) . "'/>\n";
										break;
									case "rss":
										$ret_output .= "\t<item><title>" .
											basename($filename) . "</title><media:content url=\"" .
											trim($urltofile) . "\" type=\"" . $mimetype .
											"\"/></item>";
										break;
									case "alist":
										$ret_output .= "<a href=\"" . trim($urltofile) . "\"><span>" . basename($filename) . "</span></a>";
										break;
								}

							}
						}
					}
				}
			}

			//=============================================
			//end of Moodle 2 file
		}


		//for debugging
		//$ret_output .=  "\t<audio audioname='" . $cm->modname  . " " . $filearea . " " . $urltofile ."' playertype='" . $playertype . "' url='" . $mediapath . basename($contextid). "'/>\n";

		//close xml/alist tags to return
		switch ($listtype) {
			case "xml":
				$ret_output .= "</audios>";
				break;
			case "rss":
				$ret_output .= "</channel>";
				break;
			case "alist":
				$ret_output .= "</div>";
				break;
		}


		//Return the data
		return $ret_output;


	}


//Given a user object, return the url to a picture for that user.
//Given a user object, return the url to a picture for that user.
	public static function fetch_user_picture($user, $size = 35)
	{
		global $CFG, $PAGE;
		//we ignore size these days Justin 20120705
		$upic = new \user_picture($user);
		if ($upic) {
			return $upic->get_url($PAGE);
		} else {
			return "";
		}

	}


//embed a quizlet iframe
	public static function fetch_quizlet($quizletid, $quizlettitle = "", $mode = "flashcards", $width = "100%", $height = "")
	{

		//massage mode, other options are as is "learn" or "scatter"
		if ($mode == "") $mode = "flashcards";

		//set default heights
		$dh = "410";
		if ($height == '') {
			$height = $dh;
		}

		//only do scrolling for test
		if ($mode == "test") {
			$scroll = "yes";
		} else {
			$scroll = "no";
		}

		//return iframe
		$ret = "<div style=\"background:#fff;padding:3px\">
			<iframe src=\"//quizlet.com/$quizletid/$mode/embedv2/?hideLinks\" height=\"$height\" width=\"$width\" style=\"border:0;\" scrolling=\"$scroll\"></iframe>
			</div>";
		return $ret;

	}


	public static function fetch_filter_properties($filterstring)
	{
		//this just removes the {POODLL: .. } to leave us with the good stuff.
		//there MUST be a better way than this.
		$rawproperties = explode("{POODLL:", $filterstring);
		$rawproperties = $rawproperties[1];
		$rawproperties = explode("}", $rawproperties);
		$rawproperties = $rawproperties[0];

		//Now we just have our properties string
		//Lets run our regular expression over them
		//string should be property=value,property=value
		//got this regexp from http://stackoverflow.com/questions/168171/regular-expression-for-parsing-name-value-pairs
		$regexpression = '/([^=,]*)=("[^"]*"|[^,"]*)/';
		$matches = array();

		//here we match the filter string and split into name array (matches[1]) and value array (matches[2])
		//we then add those to a name value array.
		$itemprops = array();
		if (preg_match_all($regexpression, $rawproperties, $matches, PREG_PATTERN_ORDER)) {
			$propscount = count($matches[1]);
			for ($cnt = 0; $cnt < $propscount; $cnt++) {
				// echo $matches[1][$cnt] . "=" . $matches[2][$cnt] . " ";
				$itemprops[$matches[1][$cnt]] = $matches[2][$cnt];
			}
		}

		return $itemprops;

	}


	//This is use for assembling the html elements + javascript that will be swapped out and replaced with the MP3 recorder
	public static function fetchSWFWidgetJSON($widget, $rawparams, $width, $height, $bgcolor = "#FFFFFF", $widgetid = '')
	{
		global $CFG;

		//build the parameter string out of the passed in array
		$params = "";
		if (is_array($rawparams)) {
			$params = "?";
			foreach ($rawparams as $key => $value) {
				$params .= '&' . $key . '=' . $value;
			}
		} else {
			$params = $rawparams;
		}

		//add in any common params
		$params .= '&debug=false&lzproxied=false';

		//generate a (most likely) unique id for the recorder, if one was not passed in
		if ($widgetid == '') {
			$widgetid = 'lzapp_' . rand(100000, 999999);
		}
		$paramobj = new \stdClass();
		$paramobj->url = $CFG->wwwroot . '/filter/poodll/flash/' . $widget . $params;
		$paramobj->bgcolor = $bgcolor;
		$paramobj->cancelmousewheel = true;
		$paramobj->allowfullscreen = true;
		$paramobj->width = $width;
		$paramobj->height = $height;
		$paramobj->id = $widgetid;
		$paramobj->accessible = true;
		$paramobj->serverroot = '/';
		$paramobj->appenddivid = $widgetid + 'Container';

		$retjson = json_encode($paramobj);
		return $retjson;

	}

	//This is use for assembling the html elements + javascript that will be swapped out and replaced with the MP3 recorder
	public static function fetchJSWidgetJSON($widget, $params, $width, $height, $bgcolor = "#FFFFFF", $widgetid = '')
	{
		global $CFG;

		//build the parameter string out of the passed in array
		/*
        $params="?";
        foreach ($paramsArray as $key => $value) {
            $params .= '&' . $key . '=' . $value;
        }
        */
		//  lzOptions = {ServerRoot: '\\'};
		// lzOptions = {ServerRoot: '\\'};
		//add in any common params
		//  $params="?" . $params;
		$params .= '&debug=false&lzproxied=false';

		//generate a (most likely) unique id for the recorder, if one was not passed in
		if ($widgetid == '') {
			$widgetid = 'lzapp_' . rand(100000, 999999);
		}

		$pathtoJS = $CFG->wwwroot . '/filter/poodll/js/';
		$pathtowidgetfolder = $CFG->wwwroot . '/filter/poodll/js/' . $widget . '/';

		$paramobj = new \stdClass();
		$paramobj->url = $pathtowidgetfolder . $widget . $params;
		$paramobj->bgcolor = $bgcolor;
		$paramobj->cancelmousewheel = false;
		$paramobj->cancelkeyboardcontrol = false;
		$paramobj->usemastersprite = false;
		$paramobj->skipchromeinstall = false;
		$paramobj->allowfullscreen = true;
		$paramobj->approot = $pathtowidgetfolder;
		$paramobj->lfcurl = $pathtoJS . 'lps/includes/lfc/LFCdhtml.js';
		$paramobj->serverroot = $pathtoJS . 'lps/resources/';
		$paramobj->accessible = false;
		$paramobj->width = $width;
		$paramobj->height = $height;
		$paramobj->id = $widgetid;
		$paramobj->accessible = true;
		$paramobj->appenddivid = $widgetid + 'Container';

		$retjson = json_encode($paramobj);
		return $retjson;

	}

//this is only used for JW player, ie not really used
	public static function fetchSWFObjectWidgetCode($widget, $flashvarsArray, $width, $height, $bgcolor)
	{
		global $CFG, $PAGE;
		//this doesn't work here or at top of file!!
		//$PAGE->requires->js(new \moodle_url($CFG->httpswwwroot . '/filter/poodll/flash/swfobject_22.js'));

		$containerid = 'swfobject_' . rand(100000, 999999);
		$widgetid = $containerid . '_widget';

		$flashvars = "";
		foreach ($flashvarsArray as $key => $value) {
			if ($flashvars != "") {
				$flashvars .= ",";
			}
			$flashvars .= $key . ":'" . $value . "'";
		}

		$retcode = "<p id='" . $containerid . "'>Please install the Flash Plugin</p>
		<script type='text/javascript' src='/filter/poodll/flash/swfobject_22.js'></script>
		<script type='text/javascript'>
		  var flashvars = { " . $flashvars . " };
		  var params = { allowfullscreen:'true', allowscriptaccess:'always' };
		  var attributes = { id:'" . $widgetid . "', name:'" . $widgetid . "' };
		  swfobject.embedSWF('" . $CFG->wwwroot . '/filter/poodll/flash/' . $widget . "','" . $containerid . "','" . $width . "','" . $height . "','9.0.115','false',
			flashvars, params, attributes);
		</script>
		";
		return $retcode;


	}

//If we wish to show a styled upload button, here we return true
//on Firefox on Android doesn't support it currently, so we hard code that to false
//also for MS Surface
//(2013/08/19)
	public static function showFancyButton($browser)
	{
		global $CFG;

		if ($browser->getPlatform() == Browser::PLATFORM_ANDROID &&
			$browser->getBrowser() == Browser::BROWSER_FIREFOX
		) {
			return false;
		} else if ($browser->getPlatform() == Browser::PLATFORM_MICROSOFT_SURFACE) {
			return false;
		} else {
			return $CFG->filter_poodll_html5fancybutton;
		}
	}

//Here we try to detect if this supports uploading audio files spec
//iOS doesn't but android can record from mic. Apple and Windows can just filter by audio when browsing
//(2013/03/05)Firefox on android, doesn't use sound recorder currently.
//(2013/03/05)Chrome on android gives wrong useragent(ipad/safari!)
	public static function canSpecAudio($browser)
	{

		switch ($browser->getPlatform()) {

			case Browser::PLATFORM_APPLE:
			case Browser::PLATFORM_WINDOWS:
				return true;
				break;

			case Browser::PLATFORM_IPAD:
				return false;
				break;

			case Browser::PLATFORM_IPOD:
			case Browser::PLATFORM_IPHONE:
				return false;
				break;

			case Browser::PLATFORM_ANDROID:
				if ($browser->getBrowser() == Browser::BROWSER_FIREFOX) {
					return false;
				} else if ($browser->isNexus7()) {
					return false;
				} else {
					return true;
				}
				break;

			default:
				return false;
		}//end of switch
	}

//We check if the OS version is too old here,
//Android 4+ iOS6+
//(2013/09/26)
	public static function canDoUpload()
	{
		$browser = new Browser();

		switch ($browser->getPlatform()) {

			case Browser::PLATFORM_ANDROID:
				$ver = $browser->getAndroidMajorVersion();
				//if parsing failed, just assume they can upload
				if (!$ver) {
					return true;
				} elseif ($ver > 3) {
					return true;
				} else {
					return false;
				}
				break;

			case Browser::PLATFORM_IPHONE:
			case Browser::PLATFORM_IPOD:
			case Browser::PLATFORM_IPAD:
				$ver = $browser->getIOSMajorVersion();
				//if parsing failed, just assume they can upload
				if (!$ver) {
					return true;
				} elseif ($ver > 5) {
					return true;
				} else {
					return false;
				}
				break;
			default:
				return true;
		}//end of switch


	}//end of function

	public static function isOldIE()
	{
		$browser = new Browser();

		if ($browser->getBrowser() == Browser::BROWSER_IE && $browser->getVersion() < 10) {
			return true;
		} else {
			return false;
		}
	}

//Here we try to detect if this is a mobile device or not
//this is used to determine whther to return a JS or SWF widget
	public static function isMobile($profile = 'mobile')
	{
		global $CFG;

		if ($profile == 'never') {
			return false;
		}
		if ($profile == 'always') {
			return true;
		}

		$browser = new Browser();

		//check by browser
		switch ($browser->getBrowser()) {
			case Browser::BROWSER_IPAD:
			case Browser::BROWSER_IPOD:
			case Browser::BROWSER_IPHONE:
			case Browser::BROWSER_ANDROID:
			case Browser::BROWSER_WINDOWS_PHONE:
				return true;
		}

		//check by platform
		switch ($browser->getPlatform()) {

			case Browser::PLATFORM_IPHONE:
			case Browser::PLATFORM_IPOD:
			case Browser::PLATFORM_IPAD:
			case Browser::PLATFORM_BLACKBERRY:
			case Browser::PLATFORM_NOKIA:
			case Browser::PLATFORM_ANDROID:
			case Browser::PLATFORM_WINDOWS_CE:
			case Browser::PLATFORM_WINDOWS_PHONE:
				//case Browser::PLATFORM_MICROSOFT_SURFACE:
				return true;
		}//end of switch


		//if we are still not mobile, but webkit browwsers count, check that too
		if ($profile == 'webkit') {
			switch ($browser->getBrowser()) {
				case Browser::BROWSER_SAFARI:
				case Browser::BROWSER_ICAB:
				case Browser::BROWSER_OMNIWEB:
				case Browser::BROWSER_NOKIA_S60:
				case Browser::BROWSER_CHROME:
					return true;
			}
		}
		return false;
	}


	public static function fetchFlowPlayerCode($width, $height, $path, $playertype = "audio", $ismobile = false, $playlisturlstring = "", $loop = 'false', $splashurl = '')
	{

		global $CFG, $PAGE, $FPLAYERJSLOADED;

		$playerid = "flowplayer_" . $path;
		$playerpath = $CFG->wwwroot . "/filter/poodll/flowplayer/flowplayer-3.2.10.swf";
		$playerclass = "flowplayer_poodll";


		//this is the embed style for flowplayer.
		//it got a bit nasty with js conflicts and possibly fp js bugs.
		//so added options to embed alternatively. should purge cache after changing embed type.
		//justin 20120928
		$embedtype = $CFG->filter_poodll_fp_embedtype;


		$jscontrolsid = "flowplayer_js_" . $playlisturlstring;

		$defaultcontrolsheight = $CFG->filter_poodll_audioheight;

		//usually we displayhtml5 controls depending on config prefs
		//but for lists, so if we are mobile we use js, if not we use flash
		if ($playertype == 'audiolist' || $playertype == 'videolist') {
			if ($ismobile) {
				$jscontrols = true;
			} else {
				$jscontrols = false;
			}
		} else {
			$jscontrols = ($CFG->filter_poodll_html5controls == 'js') && $ismobile;
		}

		//This is used in styles.css in poodll filter folder, so it needs to be hard coded
		$jscontrolsclass = "fpjscontrols";

		//init our return code
		$retcode = "";


		//the params are different depending on the playertype
		//we need to specify provider for audio if the clips are not MP3 or mp3
		//jqueryseems unavoidable even if not using it for playlists
		switch ($playertype) {
			case "audio":
				//If we have a splash screen show it and enable autoplay(user only clicks once)
				//best to have a splash screen to prevent browser hangs on many flashplayers in a forum etc
				if ($splashurl != '') {
					$splash = "<img src='" . $splashurl . "' alt='click to play audio' width='" . $width . "' height='" . $height . "'/>";

				} else if ($CFG->filter_poodll_audiosplash) {
					$splash = "<img src='" . $CFG->wwwroot . "/filter/poodll/flowplayer/audiosplash.jpg' alt='click to play audio' width='" . $width . "' height='" . $height . "'/>";
				} else {
					$splash = "";
				}
				break;

			case "audiolist":
				$splash = "";
				break;

			case "video":
				//If we have a splash screen show it and enable autoplay(user only clicks once)
				//best to have a splash screen to prevent browser hangs on many flowplayers in a forum etc
				if ($splashurl != '') {
					$splash = "<img src='" . $splashurl . "' alt='click to play video' width='" . $width . "' height='" . $height . "'/>";

				} else if ($CFG->filter_poodll_videosplash) {
					if ($CFG->filter_poodll_thumbnailsplash) {
						$splashurl = self::fetchVideoSplash($path);
					} else {
						$splashurl = false;
					}
					if (!$splashurl) {
						$splashurl = $CFG->wwwroot . "/filter/poodll/flowplayer/videosplash.jpg";
					}
					$splash = "<img src='" . $splashurl . "' alt='click to play video' width='" . $width . "' height='" . $height . "'/>";

				} else {
					$splash = "";
				}
				break;

			case "videolist":
				$splash = "";
				break;


		}

		//add a media rss playlist if one was passed in
		if ($playlisturlstring == "") {
			$playlisturlstring = null;
		}

		//put together the a link/div that will be replaced by a player
		//gave up on a link because the mediaplugin kept trying to double replace it
		//justin 20120928

		//A link method
		if ($embedtype == 'flowplayer') {
			$retcode .= "<a href='" . $path . "'
						style='display:block;width:" . $width . "px;height:" . $height . "px;'
						id='" . $playerid . "' class='" . $playerclass . "' >
						" . $splash . "
					</a>";
		} else {

			//DIV method
			$retcode .= "<div style='display:block;width:" . $width . "px;height:" . $height . "px;'
						id='" . $playerid . "' class='" . $playerclass . "' >
						" . $splash . "
					</div>";
		}


		//put together the div that will be replaced by the JS controls if necessary
		if ($jscontrols) {
			$retcode .= "<div id='" . $jscontrolsid . "' class='" . $jscontrolsclass . "'></div>";
		}

		//determine the flowplayer components we need to incorp.
		//the js will figure outhow to assemble it all
		//but only flowplayer js embedding will do more than the basic swf player
		$controls = "0";
		$ipad = false;
		$playlist = false;
		$loop = false;

		if ($ismobile) {
			if (($playertype == "audiolist" || $playertype == "videolist") && $jscontrols) {
				$controls = $jscontrolsid;
				$ipad = true;
				$playlist = true;
				$loop = true;

			} else if ($playertype == "audiolist" || $playertype == "videolist") {
				$ipad = true;
				$playlist = true;
				$loop = true;

			} else if ($jscontrols) {
				$controls = $jscontrolsid;
				$ipad = true;

			} else {
				$ipad = true;

			}
		} else {
			if (($playertype == "audiolist" || $playertype == "videolist") && $jscontrols) {
				$controls = $jscontrolsid;
				$playlist = true;
				$loop = true;

			} else if ($playertype == "audiolist" || $playertype == "videolist") {
				$playlist = true;
				$loop = true;

			} else if ($jscontrols) {
				$controls = $jscontrolsid;
			}
		}

		switch ($embedtype) {
			case 'swfobject':
				//likely to have already been loaded elsewhere
				$PAGE->requires->js(new \moodle_url($CFG->httpswwwroot . '/filter/poodll/flash/swfobject_22.js'));
				break;

			case 'flashembed':
				//Load JS dependancies
				$PAGE->requires->js(new \moodle_url($CFG->httpswwwroot . '/filter/poodll/flowplayer/flowplayer-3.2.9.min.js'));
				break;

			case 'flowplayer':
			default:
				//Load JS dependancies
				$PAGE->requires->js(new \moodle_url($CFG->httpswwwroot . '/filter/poodll/flowplayer/flowplayer-3.2.9.min.js'));

				//these are for the list players, but i wonder if list players from flowplayer are too much hassle ...
				if ($CFG->filter_poodll_fp_playlist) {
					$PAGE->requires->js(new \moodle_url($CFG->httpswwwroot . '/filter/poodll/flowplayer/jquery.tools.min.js'));
					//alternatively this can be used for the jquerystuff js, its better, but its inline and wont work on LAN only nets
					//$retcode .= "<script src=\"http://cdn.jquerytools.org/1.2.7/full/jquery.tools.min.js\"></script>";

					$PAGE->requires->js(new \moodle_url($CFG->httpswwwroot . '/filter/poodll/flowplayer/flowplayer.playlist-3.2.8.min.js'));
					$PAGE->requires->js(new \moodle_url($CFG->httpswwwroot . '/filter/poodll/flowplayer/flowplayer.ipad-3.2.8.min.js'));
				}

		}

		//configure our options array
		$opts = array(
			"path" => $path,
			"playerid" => $playerid,
			"playerpath" => $playerpath,
			"poodll_audiosplash" => ($CFG->filter_poodll_audiosplash == 1),
			"poodll_videosplash" => ($CFG->filter_poodll_videosplash == 1),
			"jscontrols" => $jscontrols,
			"height" => $height,
			"width" => $width,
			"defaultcontrolsheight" => $defaultcontrolsheight,
			"playertype" => $playertype,
			"playlisturl" => $playlisturlstring,
			"controls" => $controls,
			"ipad" => $ipad,
			"playlist" => $playlist,
			"loop" => ($loop ? 'true' : 'false'),
			"embedtype" => $embedtype,
			"bgcolor" => $CFG->filter_poodll_fp_bgcolor,
			"audiocontrolsurl" => $CFG->wwwroot . "/filter/poodll/flowplayer/flowplayer.audio-3.2.9.swf"
		);

		//We need this so that we can require the JSON , for json stringify
		$jsmodule = array(
			'name' => 'filter_poodll',
			'fullpath' => '/filter/poodll/module.js',
			'requires' => array('json')
		);

		//setup our JS call
		$PAGE->requires->js_init_call('M.filter_poodll.loadflowplayer', array($opts), false, $jsmodule);


		//return the html that the Flowplayer JS will swap out
		return $retcode;
	}


	public static function fetchVideoSplash($src)
	{
		global $CFG;

		$src = urldecode($src);

		//if this is not a local file , quit.
		$possy = strpos($src, "pluginfile.php");
		if (!$possy) {
			return false;
		}
		//get relative path
		//e.g http://m23.poodll.com/pluginfile.php/59/mod_page/content/20/360332574229687.flv
		//should become /59/mod_page/content/20/360332574229687.flv
		$relpath = substr($src, $possy + 14);

		//remove any pesky forcedownload params
		$relpath = str_replace("?forcedownload=1", "", $relpath);

		//if something went wrong, and we can't confirm get a handle on the file,
		//muddle with the itemid. Some mods don't bother to use it if it is a certain filearea
		//eg assignment intro, others use it strangely,eg mod_page, and we need to set it to 0
		//quiz questions have extra stuff between filearea and itemid
		$fs = get_file_storage();
		$file = $fs->get_file_by_hash(sha1($relpath));
		if (!$file) {
			$relarray = explode('/', $relpath);
			//index 1 = contextid, 2 =component,3=filearea
			//itemid can change, filename is last

			switch ($relarray[2]) {
				case 'question':
					$qitemid = $relarray[count($relarray) - 2];
					$qfilename = $relarray[count($relarray) - 1];
					$relpath = '/' . $relarray[1] . '/' . $relarray[2] . '/' . $relarray[3];
					$relpath .= '/' . $qitemid . '/' . $qfilename;
					break;

				case 'mod_page':
					//1st we set itemid to 0
					$originalitemid = $relarray[4];
					$relarray[4] = '0';
					$relpath = implode('/', $relarray);
					break;

				case 'mod_assign':
					array_splice($relarray, 4, 0, '0');
					$relpath = implode('/', $relarray);
					break;

				default:
					//if we have no itemid, zero is assumed
					if (count($relarray) == 5) {
						$relpath = '/' . $relarray[1] . '/' . $relarray[2] . '/' . $relarray[3];
						$relpath .= '/0/' . $relarray[4];
					}
			}


			//Then hash the path and try to get the file
			$file = $fs->get_file_by_hash(sha1($relpath));

			//if we still don't have a file, give up
			if (!$file) {
				return false;
			}
		}


		//check if we really can have/make a splash for this file
		//if name is too short, we didn't make it, it wont be on our red5 server
		$filename = $file->get_filename();
		if (strlen($filename) < 5) {
			return false;
		}

		//if we are NOT using FFMPEG, we can only take snaps from Red5, so ...
		//if name is not numeric, it is not a video file we recorded on red5.it wont be there
		if (!$CFG->filter_poodll_ffmpeg && !is_numeric(substr($filename, 0, strlen($filename) - 4))) {
			return false;
		}

		//check if we have an image file here already, if so return that URL
		$relimagepath = substr($relpath, 0, strlen($relpath) - 3) . 'png';
		$trimsrc = str_replace("?forcedownload=1", "", $src);
		$fullimagepath = substr($trimsrc, 0, strlen($trimsrc) - 3) . 'png';
		$imagefilename = substr($filename, 0, strlen($filename) - 3) . 'png';
		if ($imagefile = $fs->get_file_by_hash(sha1($relimagepath))) {
			return $fullimagepath;
		}

		//from this point on we will need our file handling functions
		require_once($CFG->dirroot . '/filter/poodll/poodllfilelib.php');

		//if we are using FFMPEG, try to get the splash image
		if ($CFG->filter_poodll_ffmpeg) {

			$imagefile = get_splash_ffmpeg($file, $imagefilename);
			if ($imagefile) {
				return $fullimagepath;
			} else {
				return false;
			}

			//if not FFMPEG pick it up from Red5 server
		} else {

			$result = instance_remotedownload($file->get_contextid(),
				$imagefilename,
				$file->get_component(),
				$file->get_filearea(),
				$file->get_itemid(),
				"99999",
				$file->get_filepath()
			);

			if (strpos($result, "success")) {
				return $fullimagepath;
			} else {
				return false;
			}
		}//end of if ffmpeg
	}//end of fetchVideoSplash

	public static function fetchAutoWidgetCode($widget, $paramsArray, $width, $height, $bgcolor = "#FFFFFF")
	{
		global $CFG;
		$ret = "";
		//determine if this is mobile or not
		if (self::isMobile($CFG->filter_poodll_html5widgets)) {

			$pos = strPos($widget, ".lzx.");
			if ($pos > 0) {
				$basestring = substr($widget, 0, $pos + 4);
				$widget = $basestring . ".js";
				$ret = self::fetchJSWidgetiFrame($widget, $paramsArray, $width, $height, $bgcolor = "#FFFFFF");
			}
		} else {
			//$ret=$browser->getPlatform();
			$ret = self::fetchSWFWidgetCode($widget, $paramsArray, $width, $height, $bgcolor = "#FFFFFF");
		}
		return $ret;
	}

	public static function fetchJSWidgetiFrame($widget, $rawparams, $width, $height, $bgcolor = "#FFFFFF", $usemastersprite = "false")
	{
		global $PAGE;

		$lm = new \filter_poodll\licensemanager();
		if(!$lm->validate_registrationkey($CFG->filter_poodll_registrationkey)) {
			return $lm->fetch_unregistered_content();
		}

		$renderer = $PAGE->get_renderer('filter_poodll');
		return $renderer->fetchJSWidgetiFrame($widget, $rawparams, $width, $height, $bgcolor, $usemastersprite);

	}

	public static function fetchIFrameSWFWidgetCode($widget, $paramsArray, $width, $height, $bgcolor = "#FFFFFF")
	{
		global $PAGE, $CFG;

		$lm = new \filter_poodll\licensemanager();
		if(!$lm->validate_registrationkey($CFG->filter_poodll_registrationkey)) {
			return $lm->fetch_unregistered_content();
		}

		$renderer = $PAGE->get_renderer('filter_poodll');
		return $renderer->fetchIFrameSWFWidgetCode($widget, $paramsArray, $width, $height, $bgcolor);

	}

//This is used for all the flash widgets
	public static function fetchSWFWidgetCode($widget, $paramsArray, $width, $height, $bgcolor = "#FFFFFF")
	{
		global $CFG, $PAGE;

		$lm = new \filter_poodll\licensemanager();
		if(!$lm->validate_registrationkey($CFG->filter_poodll_registrationkey)) {
			return $lm->fetch_unregistered_content();
		}

//get our module javascript all ready to go
		$jsmodule = array(
			'name' => 'filter_poodll',
			'fullpath' => '/filter/poodll/module.js',
			'requires' => array('json')
		);

		$widgetopts = Array();
		$widgetid = \html_writer::random_id('laszlobase');//'lzapp_' . rand(100000, 999999);
		$widgetopts['widgetjson'] = self::fetchSWFWidgetJSON($widget, $paramsArray, $width, $height, $bgcolor, $widgetid);


		$renderer = $PAGE->get_renderer('filter_poodll');
		return $renderer->fetchLazloEmbedCode($widgetopts,$widgetid,$jsmodule);


	}

}//end of class
