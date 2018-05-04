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
    const LOG_SAVE_PLACEHOLDER_FAIL = 1;
    const LOG_NOTHING_TO_TRANSCODE = 2;

    const AUDIO_PLACEHOLDER_HASH ='805daf96c0b5e197a728f230d9550e9ba49e5ea7';
    const VIDEO_PLACEHOLDER_HASH ='4eab92af4205d642e774718c85d5ea3a19881ba6';
    const AUDIO_PLACEHOLDER_LENGTH = 4.362449;// as reported by firefox: 4.388496
    const VIDEO_PLACEHOLDER_LENGTH = 4.133333;//as reported by firefox: 4.202811

    const AUDIO_PLACEHOLDER_HASH_OLD ='e118549e4fc88836f418b6da6028f1fec571cd43';
    const VIDEO_PLACEHOLDER_HASH_OLD ='c2a342a0a664f2f1c4ea5387554a67caf3dd158e';

	//this is just a temporary function, until the PoodLL filter client plugins are upgraded to not use simpleaudioplayer
    public static function fetchSimpleAudioPlayer($param1='auto',$url,$param3='http',$param4='width', $param5='height'){ 
        $html_snippet = \html_writer::tag('a','audiofile.mp3',array('href'=>$url));
       return format_text($html_snippet);
    }
    
    //this is just a temporary function, until the PoodLL filter client plugins are upgraded to not use simpleaudioplayer
    public static function fetchSimpleVideoPlayer($param1='auto',$url,$param3='http',$param4='width', $param5='height'){ 
        $html_snippet = \html_writer::tag('a','videofile.mp4',array('href'=>$url));
       return format_text($html_snippet);
    }

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

	public static function fetch_whiteboard($runtime, $boardname, $imageurl = "", $slave = false, $rooms = "", $width = 0, $height = 0, $mode = 'normal', $standalone = 'false')
	{
		global $CFG, $USER, $COURSE;
		$lm = new \filter_poodll\licensemanager();
        $registration_status = $lm->validate_registrationkey($CFG->filter_poodll_registrationkey);
		if($registration_status != \filter_poodll\licensemanager::FILTER_POODLL_IS_REGISTERED){
			return $lm->fetch_unregistered_content($registration_status);
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


    /*
    * The red5 based audio recorder
   *
   */
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

	/*
    * The old fetch MP3 Recorder fetch call now delegates to the AMD based universal recorder
   *
   */
	public static function fetchMP3RecorderForSubmission($updatecontrol, $contextid, $component, $filearea, $itemid, $timelimit = "0", $callbackjs = false,$hints=[])
	{
		return self::fetchAMDRecorderCode('audio', $updatecontrol, $contextid, 
					$component, $filearea, $itemid, $timelimit, $callbackjs,$hints);

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
		}else{
            $opts['autosave'] =false;
        }

		//are we allowing zoom, or not ...
		$opts['whiteboardnozoom'] = $CFG->filter_poodll_whiteboardnozoom;

		
		//set media type
		$mediatype = "image";
		$poodllfilelib = $CFG->wwwroot . '/filter/poodll/poodllfilelib.php';
		
		//imageurlprefix, that LC requires
		$opts['imageurlprefix'] = $CFG->httpswwwroot . '/filter/poodll/js/literallycanvas.js/img';
		$opts['recorderid'] = 'literallycanvas_' . time() . rand(10000, 999999);
		$opts['widgetid'] = $opts['recorderid'];
		$opts['callbackjs'] = $callbackjs;
		$opts['using_s3'] = false;
		$opts['updatecontrol'] = $updatecontrol;
		$opts['vectorcontrol'] = $vectorcontrol;
		$opts['base64control'] = '';//do this later
		$opts['vectordata'] = $vectordata;
		$opts['p1'] = '';
		$opts['p2'] = $contextid;
		$opts['p3'] = $component;
		$opts['p4'] = $filearea;
		$opts['p5'] = $itemid;
		$opts['mediatype'] = $mediatype;
		$opts['posturl'] = $poodllfilelib;
		

		//we encode the options and send them to html. Moodle doesn't like them cluttering the JS up
		//when using AMD
		$jsonstring = json_encode($opts);
		$opts_html = \html_writer::tag('input', '', array('id' => 'amdopts_' . $opts['recorderid'], 'type' => 'hidden', 'value' => $jsonstring));
		$PAGE->requires->js_call_amd("filter_poodll/literallycanvas_amd", 'loadliterallycanvas', array(array('recorderid' => $opts['recorderid'])));



		//removed from params to make way for moodle 2 filesystem params Justin 20120213
		if ($width == 0) {
			$width = $CFG->filter_poodll_whiteboardwidth;
		}
		if ($height == 0) {
			$height = $CFG->filter_poodll_whiteboardheight;
		}
		

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

		if ($opts['autosave']) {
			$buttonclass = "w_btn";
		} else {
			$buttonclass = "p_btn";
		}
		
		$savebutton = "<button type=\"button\" id=\"" . $opts['recorderid'] . "_btn_upload_whiteboard\" class=\"$buttonclass\">"
			. get_string('whiteboardsave', 'filter_poodll') .
			"</button>";


		//message container
		$progresscontrols ="<div id=\"" . $opts['recorderid'] . "_messages\"></div>";


		//container of whiteboard, bgimage and other bits and pieces.
		//add a buffer background image if necessary
		$lcOpen = "<div class='whiteboard-wrapper' style='width:" . $width . "px; height:" . $height . "px;'>
			<div class='fs-container' style='width:" . $width . "px; height:" . $height . "px;'>
			<div id='" . $opts['recorderid'] . "_literally' class='literallycanvas' style='width:" . $width . "px; height:" . $height . "px;'></div></div>";
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
			$returnString .= $opts_html;
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
		
		//set url of poodllfilelib
		$poodllfilelib = $CFG->wwwroot . '/filter/poodll/poodllfilelib.php';
		//set media type
		$mediatype = "image";

		//javascript upload handler
		$opts = Array();
		$opts['recorderid'] = 'drawingboard_' . time() . rand(10000, 999999);
		$opts['callbackjs'] = $callbackjs;
		$opts['updatecontrol'] = $updatecontrol;
		$opts['vectorcontrol'] = $vectorcontrol;
		$opts['vectordata'] = $vectordata;	
		$opts['widgetid'] = $opts['recorderid'];
		$opts['callbackjs'] = $callbackjs;
		$opts['using_s3'] = false;
		$opts['base64control'] = '';//do this later
		$opts['p1'] = '';
		$opts['p2'] = $contextid;
		$opts['p3'] = $component;
		$opts['p4'] = $filearea;
		$opts['p5'] = $itemid;
		$opts['mediatype'] = $mediatype;
		$opts['posturl'] = $poodllfilelib;
		

		//be careful here, only set the background IF
		//(a) we have an image and (b) we have no vectordata
		//if we have vector data, it will contain the image
		if ($backimage != '' && $vectordata == '') {
			$opts['bgimage'] = $backimage;
		}
		if ($CFG->filter_poodll_autosavewhiteboard && $forsubmission) {
			$opts['autosave'] = $CFG->filter_poodll_autosavewhiteboard;
		}
	
		//we encode the options and send them to html. Moodle doesn't like them cluttering the JS up
		//when using AMD
		$jsonstring = json_encode($opts);
		$opts_html = \html_writer::tag('input', '', array('id' => 'amdopts_' . $opts['recorderid'], 'type' => 'hidden', 'value' => $jsonstring));
		$PAGE->requires->js_call_amd("filter_poodll/drawingboard_amd", 'loaddrawingboard', array(array('recorderid' => $opts['recorderid'])));
	
		//removed from params to make way for moodle 2 filesystem params Justin 20120213
		if ($width == 0) {
			$width = $CFG->filter_poodll_whiteboardwidth;
		}
		if ($height == 0) {
			$height = $CFG->filter_poodll_whiteboardheight;
		}

		//the control to put the filename of our picture
		if ($updatecontrol == "saveflvvoice") {
			$savecontrol = "<input name='saveflvvoice' type='hidden' value='' id='saveflvvoice' />";
		} else {
			$savecontrol = "";
		}
		
		//if autosaving
		if (array_key_exists('autosave', $opts)) {
			$buttonclass = "w_btn";
		} else {
			$buttonclass = "p_btn";
		}
		//save button
		$savebutton = "<button type=\"button\" id=\"" . $opts['recorderid'] . "_btn_upload_whiteboard\" class=\"$buttonclass\">"
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
		$returnString .= $opts_html;

		$renderer = $PAGE->get_renderer('filter_poodll');
		return $renderer->fetchDrawingBoard($returnString);
	}


	public static function fetchWhiteboardForSubmission($updatecontrol, $contextid, $component, $filearea, $itemid, $width = 0, $height = 0, $backimage = "", $prefboard = "", $callbackjs = false, $vectorcontrol = "", $vectordata = "")
	{
		global $CFG, $USER, $COURSE;

		$lm = new \filter_poodll\licensemanager();
                $registration_status = $lm->validate_registrationkey($CFG->filter_poodll_registrationkey);
		if($registration_status != \filter_poodll\licensemanager::FILTER_POODLL_IS_REGISTERED){
			return $lm->fetch_unregistered_content($registration_status);
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
			default:
				$forsubmission = true;
				return self::fetchDrawingBoard($forsubmission, $width, $height, $backimage, $updatecontrol, $contextid, $component, $filearea, $itemid, $callbackjs, $vectorcontrol, $vectordata);
				break;

		}

	}

	public static function fetch_flashcards($runtime, $cardset, $cardsetname, $frontcolor, $backcolor, $cardwidth, $cardheight, $randomize, $width, $height,$flashcardstype='poodll')
	{
		global $CFG;
		switch($flashcardstype){
	
			case 'owl':
                //removed OWL flashcards. They were not being used and AMD was ugly
				//return self::fetch_flashcards_owl($cardset,$cardsetname, $cardwidth, $cardheight);
				//break;
			case 'poodll':
			default:
			return self::fetch_flashcards_poodll($runtime, $cardset, $cardsetname, $frontcolor, $backcolor, $cardwidth, $cardheight, $randomize, $width, $height);
			break;

		}

	}

	public static function fetch_flashcards_poodll($runtime, $cardset, $cardsetname, $frontcolor, $backcolor, $cardwidth, $cardheight, $randomize, $width, $height)
	{

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


	public static function filter_poodll_fetch_recorder_strings()
	{
		$params = array();

		//Get localised labels:
		$params['ui_record'] = urlencode(get_string('recui_record', 'filter_poodll'));
        $params['ui_restart'] = urlencode(get_string('recui_restart', 'filter_poodll'));
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
		$paramobj->appenddivid = $widgetid . 'Container';

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



	/*
	* Convert a video file to a different format using ffmpeg
	*
	*/
	public static function convert_with_ffmpeg_bg($filerecord, $originalfilename, $convfilenamebase, $convext){
		global $CFG;
	
		switch ($convext){
			case '.mp4':
				$mediatype="video";
				break;
			case '.mp3':
			default:
				$mediatype="audio";
				break;
		}
		
	
		//store placeholder audio or video to display until conversion is finished
		$filerecord->filename = $convfilenamebase . $convext;
		//$stored_file =self::save_placeholderfile_in_moodle($filerecord,$convfilenamebase,$convext);
		$stored_file =self::save_placeholderfile_in_moodle($mediatype,$filerecord);
		//we need this id later, to find the old draft file and remove it, in ad hoc task
		$filerecord->id = $stored_file->get_id();
	
		// register task
	   $success = self::register_ffmpeg_task($filerecord,$originalfilename, $convfilenamebase,$convext);
	 
	   return $stored_file;
	}


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
		global $CFG,$PAGE;

		$lm = new \filter_poodll\licensemanager();
                $registration_status = $lm->validate_registrationkey($CFG->filter_poodll_registrationkey);
		if($registration_status != \filter_poodll\licensemanager::FILTER_POODLL_IS_REGISTERED){
			return $lm->fetch_unregistered_content($registration_status);
		}

		$renderer = $PAGE->get_renderer('filter_poodll');
		return $renderer->fetchJSWidgetiFrame($widget, $rawparams, $width, $height, $bgcolor, $usemastersprite);

	}

	public static function fetchIFrameSWFWidgetCode($widget, $paramsArray, $width, $height, $bgcolor = "#FFFFFF")
	{
		global $PAGE, $CFG;

		$lm = new \filter_poodll\licensemanager();
                $registration_status = $lm->validate_registrationkey($CFG->filter_poodll_registrationkey);
		if($registration_status != \filter_poodll\licensemanager::FILTER_POODLL_IS_REGISTERED){
			return $lm->fetch_unregistered_content($registration_status);
		}

		$renderer = $PAGE->get_renderer('filter_poodll');
		return $renderer->fetchIFrameSWFWidgetCode($widget, $paramsArray, $width, $height, $bgcolor);

	}

//This is used for all the flash widgets
	public static function fetchSWFWidgetCode($widget, $paramsArray, $width, $height, $bgcolor = "#FFFFFF")
	{
		global $CFG, $PAGE;

		$lm = new \filter_poodll\licensemanager();
                $registration_status = $lm->validate_registrationkey($CFG->filter_poodll_registrationkey);
		if($registration_status != \filter_poodll\licensemanager::FILTER_POODLL_IS_REGISTERED){
			return $lm->fetch_unregistered_content($registration_status);
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

        
        public static function fetch_placeholder_file_record($mediatype, $filename){
            global $DB, $CFG;
            
            switch($mediatype){
                    case 'audio': $contenthash = self::AUDIO_PLACEHOLDER_HASH;break;
                    case 'video': $contenthash = self::VIDEO_PLACEHOLDER_HASH;break;
                    default:$contenthash = '';

            }

            //We replace both permanent and draft files because otherwise some race conditions can cause placeholder
            //to overwrite converted files when user is editing an html area after intially submitting and before conv. compl.
            $perm_draft_select = "filename='" . $filename. "'  AND contenthash='" . $contenthash. "'";
            $params = null;
            $sort = "id DESC";
            $placeholderfiles = $DB->get_records_select('files',$perm_draft_select,$params,$sort);

            //if we did not get anything then just return
            if(!$placeholderfiles){
                return false;
            }

            //get the file we will replace
            return $placeholderfiles;
        }
        
        public static function replace_placeholderfile_in_moodle($draftfilerecord,$permfilerecord,$newfilepath){
               $fs = get_file_storage();
               $dfr=$draftfilerecord;
               //TODO: do we really need the use old draft record?
               $newfilename =$fs->get_unused_filename($dfr->contextid, $dfr->component, $dfr->filearea, $dfr->itemid, $dfr->filepath, $dfr->filename);
               $draftfilerecord->filename =$newfilename;
                $newfile = $fs->create_file_from_pathname($draftfilerecord, 
			$newfilepath);
                $permanentfile = $fs->get_file_by_id($permfilerecord->id);
                $permanentfile->replace_file_with($newfile);
		return true;
        }//end of function
            
        
        public static function save_placeholderfile_in_moodle($mediatype,$draftfilerecord){
           global $CFG;
            
            $fs=get_file_storage();
            $dfr=$draftfilerecord;
            switch($mediatype){
                case 'audio':$placeholderfilename = 'convertingmessage.mp3';break;
                case 'video':$placeholderfilename = 'convertingmessage.mp4';break;
            }
            //if we already have a stored file (second submit) just return that
            $stored_file = $fs->get_file($dfr->contextid, $dfr->component, $dfr->filearea, $dfr->itemid, $dfr->filepath, $dfr->filename);
            if(!$stored_file){
            	$stored_file = $fs->create_file_from_pathname($draftfilerecord, 
				$CFG->dirroot . '/filter/poodll/' .  $placeholderfilename);
			}
			if(!$stored_file) {
                self::send_debug_data(SELF::LOG_SAVE_PLACEHOLDER_FAIL,'Unable to save placeholder:' . $dfr->filename,$dfr->userid,$dfr->contextid);
            }
            return $stored_file ;
            
        }
	
	public static function register_s3_download_task($mediatype,$infilename,$outfilename, $draftfilerecord){
         global $USER;

	 	// set up task and add custom data
	   $s3_task = new \filter_poodll\task\adhoc_s3_move();
	   $s3_task->set_component('filter_poodll');

	   $savedatetime = new \DateTime();
	   $isodate=$savedatetime->format('Y-m-d H:i');
	   $qdata = array(
		   'filerecord' => $draftfilerecord,
		   'filename' => $draftfilerecord->filename,
           'infilename'=>$infilename,
            'outfilename'=>$outfilename,
		   'mediatype'=> $mediatype,
		   'isodate'=>$isodate
	   );
	   $s3_task->set_custom_data($qdata);
	   // queue it
	   \core\task\manager::queue_adhoc_task($s3_task);
        \filter_poodll\event\adhoc_move_registered::create_from_task($qdata)->trigger();

	}

	//this should never be called, the adhoc task is no longer there.
    //but we might need in near future, so we hang on to it.
    public static function register_s3_transcode_task($mediatype,$s3filename){
	 	// set up task and add custom data
	   $s3_task = new \filter_poodll\task\adhoc_s3_transcode();
	   $s3_task->set_component('filter_poodll');

	   $savedatetime = new \DateTime();
           $isodate=$savedatetime->format('Y-m-d H:i');
	   $qdata = array(
                   's3filename'=>$s3filename,
		   'mediatype'=> $mediatype,
		   'isodate'=>$isodate
	   );
	   $s3_task->set_custom_data($qdata);
	   // queue it
	   \core\task\manager::queue_adhoc_task($s3_task);
	}
	
	public static function commence_s3_transcode($mediatype,$infilename,$outfilename){
		global $CFG,$USER;

        $ret = false;
        $awstools = new \filter_poodll\awstools();
        
        //does file exist on s3 in bucket
		if($awstools->does_file_exist($mediatype,$infilename,'in' )){
			$awstools->create_one_transcoding_job($mediatype,$infilename,$outfilename);
			$ret = true;
        }else {
            if ($USER->id) {
                self::send_debug_data(SELF::LOG_NOTHING_TO_TRANSCODE, 'Nothing to transcode:' . $infilename, $USER->id, \context_user::instance($USER->id)->id);
            }
		}
        return $ret;
	}
        
    public static function confirm_s3_arrival($mediatype,$filename){
		global $CFG;
          //does file exist on s3
         $s3filename = \filter_poodll\awstools::fetch_s3_filename($mediatype, $filename);
		$awstools = new \filter_poodll\awstools();
		if($awstools->does_file_exist($mediatype,$s3filename,'in' )){
                    return true;
		}else{
                   return false;
        }
	}


    public static function postprocess_upload_fromiframeembed($mediatype,$filename)
    {
        $s3filename = \filter_poodll\awstools::fetch_s3_filename($mediatype, $filename);
        $infilename = $s3filename;
        $outfilename = $infilename;

        $success = self::commence_s3_transcode($mediatype, $infilename, $outfilename);
        return $success;
    }

    public static function postprocess_s3_upload($mediatype,$draftfilerecord)
    {
        $s3filename = \filter_poodll\awstools::fetch_s3_filename($mediatype, $draftfilerecord->filename);
        $infilename = $s3filename;
        $outfilename = $infilename;
        switch ($mediatype) {
            case 'audio':
                $newsuffix = '_' . rand(100000, 999999) . '.mp3';
                $outfilename = str_replace('.mp3', $newsuffix, $infilename);
                //$draftfilerecord->filename = str_replace('.mp3',$newsuffix ,$draftfilerecord->filename );
                break;
            case 'video':
                $newsuffix = '_' . rand(100000, 999999) . '.mp4';
                $outfilename = str_replace('.mp4', $newsuffix, $infilename);
            //$draftfilerecord->filename = str_replace('.mp4',$newsuffix ,$draftfilerecord->filename );
        }
        $success = self::commence_s3_transcode($mediatype, $infilename, $outfilename);
        if ($success) {
            $success=false;
            $storedfile = self::save_placeholderfile_in_moodle($mediatype, $draftfilerecord);
            if($storedfile) {
                $draftfilerecord->id = $storedfile->get_id();
                self::register_s3_download_task($mediatype, $infilename, $outfilename, $draftfilerecord);
                $success = true;
            }
        }
        return $success;
   }

	public static function register_ffmpeg_task($filerecord,$originalfilename, $convfilenamebase,$convext){
		 // set up task and add custom data
	   $conv_task = new \filter_poodll\task\adhoc_convert_media();
	   $conv_task->set_component('filter_poodll');

	   $qdata = array(
		   'filerecord' => $filerecord,
		   'filename' => $filerecord->filename,
		   'originalfilename' => $originalfilename,
		   'convfilenamebase' => $convfilenamebase,
		   'convext' => $convext,
           'infilename'=>$originalfilename,
           'outfilename'=>$filerecord->filename
	   );
	   //infilename and outfilename, are used only for logging. But we need them

	   $conv_task->set_custom_data($qdata);
	   // queue it
	   \core\task\manager::queue_adhoc_task($conv_task);
	   \filter_poodll\event\adhoc_convert_registered::create_from_task($qdata)->trigger();
	   return true;
	   
	}
	
	/*
	* Extract an image from the video for use as splash
	* image stored in same location with same name (diff ext)
	* as original video file
	* THIS IS NOW UNUSED : left as reference J 20180326
	*/
	public static function get_splash_ffmpeg($videofile, $newfilename){

		global $CFG, $USER;

			$tempdir =  $CFG->tempdir . "/";	
	

			//init our fs object
			$fs = get_file_storage();
			//it would be best if we could use $videofile->get_content_filehandle somehow ..
			//but this works for now.
			$tempvideofilepath = $tempdir . $videofile->get_filename();
			$tempsplashfilepath = $tempdir . $newfilename;
			$ok = $videofile->copy_content_to($tempvideofilepath);
		
			//call on ffmpeg to create the snapshot
			//$ffmpegopts = "-vframes 1 -an ";
			//this takes the frame after 1 s
			$ffmpegopts = "-ss 00:00:01 -vframes 1 -an ";
		
			//if there is a version in poodll filter dir, use that
			//else use ffmpeg version on path
			if(file_exists($CFG->dirroot . '/filter/poodll/ffmpeg')){
				$ffmpegpath = $CFG->dirroot . '/filter/poodll/ffmpeg';
			}else{
				$ffmpegpath = 'ffmpeg';
			}
		
			//branch logic if windows
			$iswindows =(strtoupper(substr(PHP_OS, 0, 3)) === 'WIN');
			$command = $ffmpegpath . " -i " . $tempvideofilepath . " " . $ffmpegopts . " " . $tempsplashfilepath;

			if($iswindows){
				$output = system($command, $fv);
			}else{
				shell_exec($command . " >/dev/null 2>/dev/null ");
			}
		
		
			//add the play button
			//this can be done from ffmpeg, but probably not on all installs, so we do in php
			if(is_readable(realpath($tempsplashfilepath))){	
				//provided this is not a place holder. We don't really want to confuse even more
				if($videofile->get_contenthash()!=self::VIDEO_PLACEHOLDER_HASH){
					$bg = imagecreatefrompng($tempsplashfilepath);
					$btn = imagecreatefrompng($CFG->dirroot . '/filter/poodll/pix/playbutton.png');
					imagealphablending($bg, 1);
					imagealphablending($btn, 1);
					//bail if we failed here
					if(!($bg && $btn)){return false;}
			
					//put the button on the bg picture
					imagecopy($bg, $btn, (imagesx($bg)-imagesx($btn)) / 2, (imagesy($bg)-imagesy($btn)) / 2, 0 , 0,imagesx($btn) , imagesy($btn));			
					$btnok = imagepng($bg, $tempsplashfilepath, 7);
				}//end of if place holder
			}else{
				return false;
			}
		
	
			//initialize return value
			$stored_file = false;
	
			//Check if we could create the image
			if(is_readable(realpath($tempsplashfilepath))){			
				//make our filerecord
				 $record = new \stdClass();
				$record->filearea = $videofile->get_filearea();
				$record->component = $videofile->get_component();
				$record->filepath = $videofile->get_filepath();
				$record->itemid   = $videofile->get_itemid();
				$record->license  = $CFG->sitedefaultlicense;
				$record->author   = 'Moodle User';
				$record->contextid = $videofile->get_contextid();
				$record->userid    = $USER->id;
				$record->source    = '';
		
				//set the image filename and call on Moodle to make a stored file from the image
				$record->filename = $newfilename;
			
				//delete the existing file if we had one
				$hash  = $fs->get_pathname_hash($record->contextid, 
					$record->component, 
					$record->filearea, 
					$record->itemid, 
					$record->filepath, 
					$record->filename);
				$stored_file = $fs->get_file_by_hash($hash);
				if($stored_file){
					$record->filename = 'temp_' . $record->filename;
					$temp_file = $fs->create_file_from_pathname($record, $tempsplashfilepath );
					$stored_file->replace_file_with($temp_file);
					$temp_file->delete();
				}else{
					//create the new file
					$stored_file = 	$fs->create_file_from_pathname($record, $tempsplashfilepath );
				}
				//need to kill the two temp files here
				if(is_readable(realpath($tempsplashfilepath ))){
					unlink(realpath($tempsplashfilepath ));
				}
				if(is_readable(realpath($tempvideofilepath))){
					unlink(realpath($tempvideofilepath));
				}
	
			//delete the temp file we made, regardless
			}else{
				if(is_readable(realpath($tempvideofilepath))){
					unlink(realpath($tempvideofilepath));
				}
			}		
			//return the stored file
			return $stored_file;
	}
	
	/*
	* Convert a video file to a different format using ffmpeg
	*
	*/
	public static function convert_with_ffmpeg($filerecord, $tempfilename, $convfilenamebase, $convext, $throwawayname = false){

		global $CFG;

			//init our fs object
			$fs = get_file_storage();
			$tempdir = $CFG->tempdir . '/';
		
			//if use ffmpeg, then attempt to convert mp3 or mp4
			$convfilename = $convfilenamebase . $convext;
			//work out the options we pass to ffmpeg. diff versions supp. dioff switches
			//has to be this way really.

			switch ($convext){
				case '.mp4':
					$ffmpegopts = $CFG->filter_poodll_ffmpeg_mp4opts;
					break;
				case '.mp3':
					$ffmpegopts = $CFG->filter_poodll_ffmpeg_mp3opts;
					break;
				default:
					$ffmpegopts = "";
			}
		
			//if there is a version in poodll filter dir, use that
			//else use ffmpeg version on path
			if(file_exists($CFG->dirroot . '/filter/poodll/ffmpeg')){
				$ffmpegpath = $CFG->dirroot . '/filter/poodll/ffmpeg';
			}else{
				$ffmpegpath = 'ffmpeg';
			}
		
			//branch logic depending on if windows or nopt
			$iswindows =(strtoupper(substr(PHP_OS, 0, 3)) === 'WIN');
			$command = $ffmpegpath . " -i " . $tempdir . $tempfilename . " " . $ffmpegopts . " " . $tempdir . $convfilename;

			if($iswindows){
				$output = system($command, $fv);
			}else{
				shell_exec($command . " >/dev/null 2>/dev/null ");
			}
		
			/* About FFMPEG conv
			it would be better to do the conversion in the background not here.
			in that case you would place an ampersand at the end .. like this ...
			" >/dev/null 2>/dev/null &");
			But you have to get the information back to Moodle, and copy the file over, so the plumbing gets tough.
			That is why we call the background task convert_with_ffmpeg_bg
			Right now there is no "converting message" displayed to user, but we need to do this.
			*/
		
			//Check if conversion worked
			if(is_readable(realpath($tempdir . $convfilename))){
				if($throwawayname){
					$filerecord->filename = $throwawayname;
				}else{
					$filerecord->filename = $convfilename;
				}

				$stored_file = 	$fs->create_file_from_pathname($filerecord, $tempdir . $convfilename);

				//need to kill the two temp files here
				if(is_readable(realpath($tempdir . $convfilename))){
					unlink(realpath($tempdir . $convfilename));
				}
				if(is_readable(realpath($tempdir . $tempfilename))){
					unlink(realpath($tempdir . $tempfilename));
				}
				$filename = $convfilename;
			//if failed, set return value to FALSE
			//and delete the temp file we made
			}else{
				$stored_file = false;
				if(is_readable(realpath($tempdir . $tempfilename))){
					unlink(realpath($tempdir . $tempfilename));
				}
			}		
			return $stored_file;

	}//end of convert with FFMPEG
	
	
	//This a legacy call from client plugins, that ais mapped to amd recorder code
	public static function fetchAudioRecorderForSubmission($runtime, $assigname, $updatecontrol = "saveflvvoice", $contextid, $component, $filearea, $itemid, $timelimit = "0", $callbackjs = false,$hints=[])
	{
		  return self::fetchAMDRecorderCode('audio', $updatecontrol, $contextid, $component, $filearea, $itemid, $timelimit, $callbackjs,$hints);      
	}

	//This a legacy call from client plugins, that ais mapped to amd recorder code
	public static function fetchVideoRecorderForSubmission($runtime, $assigname, $updatecontrol = "saveflvvoice", $contextid, $component, $filearea, $itemid, $timelimit = "0", $callbackjs = false,$hints=[])
	{
                return self::fetchAMDRecorderCode('video', $updatecontrol, $contextid, $component, $filearea, $itemid, $timelimit, $callbackjs,$hints);
	}
	
    //This a legacy call from client plugins, that ais mapped to amd recorder code
	public static function fetchHTML5SnapshotCamera($updatecontrol = "saveflvvoice", $width,$height,$contextid, $component, $filearea, $itemid, $callbackjs = false,$hints=[])
	{
		$mediatype = "snapshot";
		return self::fetchAMDRecorderCode($mediatype, $updatecontrol, $contextid, $component, $filearea, $itemid, 0, $callbackjs,$hints);
	}
	
	//This a legacy call from client plugins, that ais mapped to amd recorder code
	public static function fetch_HTML5RecorderForSubmission($updatecontrol = "saveflvvoice", $contextid, $component, $filearea, $itemid, $mediatype = "image", $fromrepo = false, $callbackjs = false,$hints=[])
	{
		return self::fetchAMDRecorderCode($mediatype, $updatecontrol, $contextid, $component, $filearea, $itemid, 0, $callbackjs,$hints);
	}
	
	//This is use for assembling the html elements + javascript that will be swapped out and replaced with the recorders
	public static function fetchAMDRecorderCode($mediatype, $updatecontrol, $contextid, $component, $filearea, $itemid, $timelimit = "0", $callbackjs = false, $hints=[])
	{
		global $CFG, $PAGE;

		$lm = new \filter_poodll\licensemanager();
                $registration_status = $lm->validate_registrationkey($CFG->filter_poodll_registrationkey);
		if($registration_status != \filter_poodll\licensemanager::FILTER_POODLL_IS_REGISTERED){
			return $lm->fetch_unregistered_content($registration_status);
		}

		// Lets determine if we are using S3
		$using_s3 = $CFG->filter_poodll_cloudrecording && ($mediatype=='audio' || $mediatype=='video');

		
		// if we are using S3 lets get an upload url
		if($using_s3){
			switch($mediatype){
				case 'audio': $ext='.mp3';break;
				case 'video': $ext='.mp4';break;
				default:$ext='.wav';
			}
			$filename = \html_writer::random_id('poodllfile') . $ext;
            $s3filename = \filter_poodll\awstools::fetch_s3_filename($mediatype, $filename);
			$awstools = new \filter_poodll\awstools();
			$posturl  = $awstools->get_presigned_upload_url($mediatype,60,$s3filename);
			$quicktime_signed_url = $awstools->get_presigned_upload_url($mediatype,60,$s3filename,true);
		}else{
			$filename = false;
            $s3filename = false;
			$posturl = $CFG->wwwroot . '/filter/poodll/poodllfilelib.php';
			$quicktime_signed_url = '';
		}
		
		//cloudbypassurl
		$cloudbypassurl = $CFG->wwwroot . '/filter/poodll/poodllfilelib.php';
		
		//generate a (most likely) unique id for the recorder, if one was not passed in
		$widgetid = \html_writer::random_id('recorderbase');

		$widgetopts = new \stdClass();
		$widgetopts->id = $widgetid;
		$widgetopts->widgetid = $widgetid;
		$widgetopts->posturl = $posturl;
		$widgetopts->cloudbypassurl = $cloudbypassurl;
		$widgetopts->updatecontrol = $updatecontrol;
		$widgetopts->mediatype=$mediatype;
		$widgetopts->p1 = '';
		$widgetopts->p2 = $contextid;
		$widgetopts->p3 = $component;
		$widgetopts->p4 = $filearea;
		$widgetopts->p5 = $itemid;
        $widgetopts->timelimit = $timelimit;
        $widgetopts->callbackjs = $callbackjs;
        $widgetopts->quicktimesignedurl =$quicktime_signed_url;


		//store the filename
		$widgetopts->filename = $filename;
		$widgetopts->s3filename = $s3filename;
        $widgetopts->iframeembed = false;//this is only when embedding from iframe
		$widgetopts->using_s3 = intval($using_s3);
		$widgetopts->allowedURL=$CFG->wwwroot;

        //recorder order of preference and media skin style
        $skinstyle = '';
        switch($mediatype) {

            case 'video':
                $rec_order = explode(',', $CFG->filter_poodll_recorderorder_video);
                break;
            case 'whiteboard':
                $rec_order = explode(',', $CFG->filter_poodll_recorderorder_whiteboard);
                break;
            case 'snapshot':
                $rec_order = explode(',', $CFG->filter_poodll_recorderorder_snapshot);
                break;
            case 'audio':
            default:
                $rec_order = explode(',', $CFG->filter_poodll_recorderorder_audio);
                break;
        }
        $widgetopts->rec_order = $rec_order;// array('mobile','media','flashaudio','red5','upload','flash');

        //size profile
        if(array_key_exists('size',$hints)){
        	$widgetopts->size= $hints['size'];
        }else{
        	$widgetopts->size= 'auto';
        }

        //resource
        if(array_key_exists('resource',$hints)){
            $widgetopts->resource= $hints['resource'];
        }else{
            $widgetopts->resource= '';
        }

        //model url
        if(array_key_exists('resource2',$hints)){
            $widgetopts->resource2= $hints['resource2'];
        }else{
            $widgetopts->resource2= '';
        }

        //hints we pass these as is
        $widgetopts->hints=$hints;

		//do we use flash on android
        $widgetopts->flashonandroid=$CFG->filter_poodll_flash_on_android;

        //do we user html5 audio on desktop safari
        $widgetopts->html5ondsafari=$CFG->filter_poodll_html5ondsafari;
                
		//for mobile amd params
		$rawparams = self::fetchMobileRecorderAMDParams($mediatype);
                foreach ($rawparams as $key => $value) {
                                $widgetopts->{$key} = $value;
		}
                
		//for upload amd params
		$rawparams = self::fetchUploadRecorderAMDParams();
                foreach ($rawparams as $key => $value) {
                                $widgetopts->{$key} = $value;
		}
                
		//for mediarecorder amd params
		$rawparams = self::fetchMediaRecorderAMDParams($mediatype,$hints);
		foreach ($rawparams as $key => $value) {
						$widgetopts->{$key} = $value;
		}

		//for red5 video recorder amd params
		$rawparams = self::fetchRed5VideoRecorderAMDParams($widgetid, $updatecontrol, $contextid, $component, $filearea, $itemid, $timelimit, $callbackjs);
		foreach ($rawparams as $key => $value) {
						$widgetopts->{$key} = $value;
		}

		//for red5 audio recorder amd params
		$rawparams = self::fetchRed5AudioRecorderAMDParams($widgetid, $updatecontrol, $contextid, $component, $filearea, $itemid, $timelimit, $callbackjs);
		foreach ($rawparams as $key => $value) {
						$widgetopts->{$key} = $value;
		}

		//for audio mp3 recorder amd params
		$rawparams = self::fetchFlashMP3RecorderAMDParams($widgetid,$updatecontrol, $contextid, $component, $filearea, $itemid,$timelimit, $callbackjs);
		foreach ($rawparams as $key => $value) {
								$widgetopts->{$key} = $value;
		}
		
		//for html5 snapshot recorder amd params
		$rawparams = self::fetchHTML5SnapshotAMDParams($widgetid,$updatecontrol,$timelimit, $callbackjs);
		foreach ($rawparams as $key => $value) {
								$widgetopts->{$key} = $value;
		}
		
		//for flash snapshot recorder amd params
		$rawparams = self::fetchFlashSnapshotAMDParams($widgetid,$updatecontrol,$contextid, $component, $filearea, $itemid, $timelimit, $callbackjs);
		foreach ($rawparams as $key => $value) {
								$widgetopts->{$key} = $value;
		}

        //send it to renderer for putting on the page
		$renderer = $PAGE->get_renderer('filter_poodll');
		return $renderer->fetchAMDRecorderEmbedCode($widgetopts,$widgetid);
	}
	
        
	public static function fetchRed5AudioRecorderAMDParams($widgetid,$updatecontrol, 
        		$contextid, $component, $filearea, $itemid, $timelimit, $callbackjs){
		  
		global $CFG, $USER, $COURSE;
		
		//formerly this was from the flag assigname=poodllrepository=small
		$bigorsmall='big';
		
		//Set the servername and a capture settings from config file
		$flvserver = self::fetch_mediaserver_url();
		

		//set up auto transcoding (mp4) or not
		if ($CFG->filter_poodll_audiotranscode) {
			$saveformat = "mp3";
		} else {
			$saveformat = "flv";
		}

		//Set the microphone config params
		$prefmic = $CFG->filter_poodll_studentmic;
		$micrate = $CFG->filter_poodll_micrate;
		$micgain = $CFG->filter_poodll_micgain;
		$micsilence = $CFG->filter_poodll_micsilencelevel;
		$micecho = $CFG->filter_poodll_micecho;
		$micloopback = $CFG->filter_poodll_micloopback;


		
		$poodllfilelib = $CFG->wwwroot . '/filter/poodll/poodllfilelib.php';
		$width = "350";
		$height = "190";

		//If no user id is passed in, try to get it automatically
		//Not sure if  this can be trusted, but this is only likely to be the case
		//when this is called from the filter. ie not from an assignment.
		$userid = $USER->username;
		$filename = "12345";

		//Stopped using this
		//$filename = $CFG->filter_poodll_filename;
		$overwritemediafile = "false";
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
		$params['filename'] = $filename;
		$params['assigName'] = 'thismightsometimesbepoodllrepository';//can we delete this already?
		$params['prefmic'] = $prefmic;
		$params['course'] = -1;
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
		$params['debug'] = 'false';
		$params['lzproxied'] = 'false';
				
		//fetch and merge lang params
		$langparams = self::filter_poodll_fetch_recorder_strings();
		$params = array_merge($params, $langparams);

		//callbackjs
		if ($callbackjs) {
			$params['callbackjs'] = $callbackjs;
		}
			
		 
		//make the widget opts which we will return
		$widgetopts= array();			
		$widget="PoodLLAudioRecorder.lzx.swf9.swf";
		$widgetopts['red5audio_widgetjson'] = self::fetchSWFWidgetJSON($widget, $params, $width, $height, '#FFFFFF', $widgetid);

		//return opts
		return $widgetopts;
	}
	
	public static function fetchRed5VideoRecorderAMDParams($widgetid,$updatecontrol, 
			$contextid, $component, $filearea, $itemid, $timelimit, $callbackjs){
		  
		global $CFG, $USER, $COURSE;
		
		//formerly this was from the flag assigname=poodllrepository=small
		$bigorsmall='big';
		
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


		
		$poodllfilelib = $CFG->wwwroot . '/filter/poodll/poodllfilelib.php';
		switch ($bigorsmall) {
			case 'small':
				$width = "298";
				$height = "340";
				break;
			case 'big':
			default:
				$width = "350";
				$height = "400";
		}



		//If no user id is passed in, try to get it automatically
		//Not sure if  this can be trusted, but this is only likely to be the case
		//when this is called from the filter. ie not from an assignment.
		$userid = $USER->username;
		$filename = "12345";

		//Stopped using this
		//$filename = $CFG->filter_poodll_filename;
		$overwritemediafile = "false";
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
		$params['assigName'] = 'thismightsometimesbepoodllrepository';//can we delete this already?
		$params['captureheight'] = $captureheight;
		$params['picqual'] = $picqual;
		$params['bandwidth'] = $bandwidth;
		$params['capturewidth'] = $capturewidth;
		$params['prefmic'] = $prefmic;
		$params['prefcam'] = $prefcam;
		$params['course'] = -1;
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
		$params['debug'] = 'false';
		$params['lzproxied'] = 'false';
				
		//fetch and merge lang params
		$langparams = self::filter_poodll_fetch_recorder_strings();
		$params = array_merge($params, $langparams);

		//callbackjs
		if ($callbackjs) {
			$params['callbackjs'] = $callbackjs;
		}
			
		 
		//make the widget opts which we will return
		$widgetopts= array();			
		$widget="PoodLLVideoRecorder.lzx.swf9.swf";
		$widgetopts['red5video_widgetjson'] = self::fetchSWFWidgetJSON($widget, $params, $width, $height, '#FFFFFF', $widgetid);
		
		//return opts
		return $widgetopts;
	}//end of fetch red5 video recorder amd params
        
        
	/*
	 * Fetch any special parameters required by the Flash recorder
	 *
	 */
	public static function fetchFlashSnapshotAMDParams($widgetid,$updatecontrol,$contextid, $component, $filearea, $itemid, $timelimit = "0", $callbackjs = false){
		global $CFG;
		
		//Set  capture settings from config file
		$capturewidth = $CFG->filter_poodll_capturewidth;
		$captureheight = (string)(0.75 * intval($CFG->filter_poodll_capturewidth));
		$capturefps = $CFG->filter_poodll_capturefps;
		$prefcam = $CFG->filter_poodll_studentcam;
		$prefmic = $CFG->filter_poodll_studentmic;
		$bandwidth = $CFG->filter_poodll_bandwidth;
		$picqual = $CFG->filter_poodll_picqual;
		$filename ="somepicture.jpg"; //we need this?
		
		//just hardcode widtha nd height  ...for now(?)
		$width = "350";
		$height = "400";

		//poodllfilelib for file handling
		$poodllfilelib = $CFG->wwwroot . '/filter/poodll/poodllfilelib.php';
		
		

		$params = array();
		$params['capturefps'] = $capturefps;
		$params['filename'] = $filename; //we need this?
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
		$params['recorderid'] = $widgetid;

		//set to auto submit
		$params['autosubmit'] = 'true';

		//fetch and merge lang params
		$langparams = self::filter_poodll_fetch_recorder_strings();
		$params = array_merge($params, $langparams);

		//callbackjs
		if ($callbackjs) {
			$params['callbackjs'] = $callbackjs;
		}


			
		 //make the widget opts which we will return
		$widgetopts= array();

		
		$widget="PoodLLSnapshot.lzx.swf9.swf";
		$widgetopts['flashsnapshot_widgetjson'] = self::fetchSWFWidgetJSON($widget, $params, $width, $height, '#FFFFFF', $widgetid);

		//return opts
		return $widgetopts;

			  
	  }
	  
	  /*
	 * Fetch any special parameters required by the Flash recorder
	 *
	 */
	public static function fetchHTML5SnapshotAMDParams($widgetid,$updatecontrol,$timelimit = "0", $callbackjs = false){	  
			  return array();
	}
        
        
        

	 /*
	 * Fetch any special parameters required by the MP3 recorder
	 *
	 */
	  public static function fetchFlashMP3RecorderAMDParams($widgetid,$updatecontrol, $contextid, $component, $filearea, $itemid,$timelimit = "0", $callbackjs = false)
		{
			global $CFG, $USER, $COURSE;
                

		//Set the microphone config params
		$micrate = $CFG->filter_poodll_micrate;
		$micgain = $CFG->filter_poodll_micgain;
		$micsilence = $CFG->filter_poodll_micsilencelevel;
		$micecho = $CFG->filter_poodll_micecho;
		$micloopback = $CFG->filter_poodll_micloopback;
		$micdevice = $CFG->filter_poodll_studentmic;

		//this only applies to direct from flash uploads (ala internet explorer)
		$autosubmit = "true";

		//can we pause or not
		if ($CFG->filter_poodll_miccanpause == 1) {
			$canpause = 'true';
		} else {
			$canpause = 'false';
		}

	
		//setup config for recirder
		$params = array();
		$params['rate'] = $micrate;
		$params['gain'] = $micgain;
		$params['prefdevice'] = $micdevice;
		$params['loopback'] = $micloopback;
		$params['echosupression'] = $micecho;
		$params['silencelevel'] = $micsilence;
		$params['uid'] = $USER->id;
		$params['autosubmit'] = $autosubmit;
		$params['timelimit'] = $timelimit;
		$params['canpause'] = $canpause;
        $params['debug'] = 'false';
		$params['lzproxied'] = 'false';
		$params['sendmethod'] = 'post';//'ajax' = direct fron flash uploading;

		$params['showexportbutton'] = 'false';
		$poodllfilelib = $CFG->wwwroot . '/filter/poodll/poodllfilelib.php';
		$params['posturl'] = $poodllfilelib;
		$params['p1'] = 1;//?? what goes here?
		$params['p2'] = $contextid;
		$params['p3'] = $component;
		$params['p4'] = $filearea;
		$params['p5'] = $itemid;
				
		$params['updatecontrol'] = $updatecontrol;
		$params['audiodatacontrol']=$widgetid . '_adc';
		

		//fetch and merge lang params
		$langparams = self::filter_poodll_fetch_recorder_strings();
		$params = array_merge($params, $langparams);

		//callbackjs
		if ($callbackjs) {
			$params['callbackjs'] = $callbackjs;
		} 
                
		//set dimensions
		if ($CFG->filter_poodll_mp3recorder_size =='normal') {
			$width = "350";
			$height = "180";
			$params['size'] = 'normal';
		} else {
			$width = "240";
			$height  = "170";
			$params['size'] = 'small';
		}
		
		 //make the widget opts which we will return
		$widgetopts= array();

		
		$widget="PoodllMP3Record.lzx.swf10.swf";
		$widgetopts['flashmp3audio_widgetjson'] = self::fetchSWFWidgetJSON($widget, $params, $width, $height, '#FFFFFF', $widgetid);
		//if we are bypassing clooud
		$widgetopts['flashmp3_cloudbypass'] =  $CFG->filter_poodll_mp3recorder_nocloud;
		
		//return opts
		return $widgetopts;
                
	}
	
	/*
	 * Fetch any special parameters required by the Upload Recorder
	 *
	 */
	public static function fetchUploadRecorderAMDParams()
	{
		return array();
	}
        
      /*
	 * Fetch any special parameters required by the Media Recorder
	 *
	 */
	public static function fetchMediaRecorderAMDParams($mediatype, $hints)
	{
		global $CFG, $COURSE;

		$params=array();
		$params['media_timeinterval'] = 2000;
		$params['media_audiomimetype'] = 'audio/webm';//or audio/wav
        	$params['media_videorecordertype'] = 'auto';//or mediarec or webp
        	$params['media_videocapturewidth'] = 320;
        	$params['media_videocaptureheight'] = 240;

		if(array_key_exists('coursecontextid',$hints)){
        	$coursecontextid = $hints['coursecontextid'];
        }else{
        	$coursecontextid = \context_course::instance($COURSE->id)->id;
        }
        if(array_key_exists('modulecontextid',$hints)){
            $localconfig = filtertools::fetch_local_filter_props('poodll',$hints['modulecontextid']);
        }else{
            $localconfig = false;
        }
        $courseconfig = filtertools::fetch_local_filter_props('poodll',$coursecontextid);
        $adminconfig = get_config('filter_poodll');


        switch($mediatype) {

            case 'video':

                $prop= "html5recorder_skin_video";
                if($localconfig && isset($localconfig[$prop]) && $localconfig[$prop] != 'sitedefault'){
                    $params['media_skin'] = $localconfig[$prop];
                }elseif( isset($courseconfig[$prop]) && $courseconfig[$prop] != 'sitedefault'){
                    $params['media_skin'] = $courseconfig[$prop];
                }else{
                    $params['media_skin'] = $adminconfig->{$prop};
                }

                $prop= "skinstylevideo";
                if($localconfig && isset($localconfig[$prop]) && $localconfig[$prop] != ''){
                    $params['media_skin_style'] = $localconfig[$prop];
                }elseif(isset($courseconfig[$prop]) && $courseconfig[$prop] != ''){
                    $params['media_skin_style'] = $courseconfig[$prop];
                }else{
                    $params['media_skin_style'] = $adminconfig->{$prop};
                }

                break;
            case 'audio':
            default:
                $prop= "html5recorder_skin_audio";
                if($localconfig && isset($localconfig[$prop]) && $localconfig[$prop] != 'sitedefault'){
                    $params['media_skin'] = $localconfig[$prop];
                }elseif( isset($courseconfig[$prop]) && $courseconfig[$prop] != 'sitedefault'){
                    $params['media_skin'] = $courseconfig[$prop];
                }else{
                    $params['media_skin'] = $adminconfig->{$prop};
                }

                $prop= "skinstyleaudio";
                if($localconfig && isset($localconfig[$prop]) && $localconfig[$prop] != ''){
                    $params['media_skin_style'] = $localconfig[$prop];
                }elseif(isset($courseconfig[$prop]) && $courseconfig[$prop] != ''){
                    $params['media_skin_style'] = $courseconfig[$prop];
                }else{
                    $params['media_skin_style'] = $adminconfig->{$prop};
                }
        }

        //the above mediaskin selection goes out the window if its passed in the hints array
        if(array_key_exists('mediaskin',$hints)){
            $params['media_skin']= $hints['mediaskin'];
        }


		return $params;
	}
        
        /*
 * Fetch any special parameters required by the mobile recorder
 *
 */
	public static function fetchMobileRecorderAMDParams($mediatype)
	{
		global $CFG;
        $params=array();
        switch($mediatype){
        	case 'audio': 
        		//from low/medium/high
        		$params['mobilequality']=$CFG->filter_poodll_mobile_audio_quality; 
        		break;
        	case 'video': 
        		//from low/medium/high
        		$params['mobilequality']=$CFG->filter_poodll_mobile_video_quality;
        		break;
        	case 'image':
        	default:
        		//this is irrelevant because the app won't be handling it.
        		//just for completeness
        		$params['mobilequality']='medium';
        }
        //from front or back
        $params['mobilecamera']=$CFG->filter_poodll_mobile_default_camera;
       //show the mobile app button .. or not
        $params['showmobile']=$CFG->filter_poodll_mobile_show;
		return $params;
	}

    public static function send_debug_data($type,$message, $userid=false,$contextid=false, $source='poodlltools.php'){
        global $CFG;
        //only log if is on in Poodll settings
        if(!$CFG->filter_poodll_debug){return;}

        $debugdata = new \stdClass();
        $debugdata->userid=$userid;
        $debugdata->contextid=$contextid;
        $debugdata->type=$type;
        $debugdata->source='poodlltools.php';
        if(array_key_exists('HTTP_USER_AGENT', $_SERVER)) {
            $debugdata->useragent = $_SERVER['HTTP_USER_AGENT'];
        }else{
            $debugdata->useragent = '';
        }
        $debugdata->message=$message;
        \filter_poodll\event\debug_log::create_from_data($debugdata)->trigger();
    }



}//end of class
