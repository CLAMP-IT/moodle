<?php

/**
* internal library of functions and constants for Poodll modules
* accessed directly by poodll flash wdgets on web pages.
* @package filter_poodll
* @category mod
* @author Justin Hunt
*
*/


/**
* Includes and requires
*/
require_once("../../../config.php");
global $CFG;

	$widget = optional_param('widget', "", PARAM_TEXT);    // The widget
	$paramstring  = optional_param('paramstring', "", PARAM_TEXT);  // the string of paramaters 
	$width  = optional_param('width', 0, PARAM_INT);  // the width of frame 
	$height  = optional_param('height', 0, PARAM_INT);  // the height of widget
	$bgcolor  = optional_param('bgcolor', "#FFFFFF", PARAM_TEXT);  // the bg color
	$usemastersprite  = optional_param('usemastersprite', "false", PARAM_TEXT);  // to use embedded resources aka sprite sheet(probably never)

	header("Content-type: text/html");
	echo "<html><head>";
	echo "<!--[if IE]><script type=\"text/javascript\" src=\"" . $CFG->wwwroot . "/filter/poodll/js/lps/includes/excanvas.js\" ></script><![endif]-->";
	echo "</head><body>";
	echo fetchJSWidgetCode($widget,$paramstring,$width,$height, $bgcolor);
	echo "</body></html>";
	return;


function fetchJSWidgetCode($widget,$params,$width,$height, $bgcolor="#FFFFFF"){
	global $CFG;

	$widgetid = html_writer::random_id('laszlobase');
	$widgetjson = fetchJSWidgetJSON($widget,$params,$width,$height, $bgcolor="#FFFFFF", $widgetid);

	$retcode = html_writer::div('','',array('id'=>$widgetid . 'Container'));
	$pathtoJS = $CFG->wwwroot . '/filter/poodll/js/';
	$retcode .=   '<script type="text/javascript" src="'. $pathtoJS . 'lps/includes/embed-compressed.js"></script>
        <script type="text/javascript"> lz.embed.dhtml(' . $widgetjson . ')</script>';
       // $adjustscript_a="<script type='text/javascript'>var overflow= document.getElementsByClassName('lzappoverflow');overflow[0].style='width: $width px; height: $height px';</script>";
       // $adjustscript_b="<script type='text/javascript'>var lzcanvas= document.getElementsByClassName('lzcanvasdiv');lzcanvas[0].style='width: $width px; height: $height px; overflow: hidden';</script>";
       // $retcode .= $adjustscript_a . $adjustscript_b;

	return $retcode;

}
//This is use for assembling the html elements + javascript that will be swapped out and replaced with the MP3 recorder
 function fetchJSWidgetJSON($widget, $params, $width, $height, $bgcolor = "#FFFFFF", $widgetid = '')
	{
		global $CFG;

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

?>
