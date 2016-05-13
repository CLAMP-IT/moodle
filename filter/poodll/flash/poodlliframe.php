<?php

/**
* internal library of functions and constants for Poodll modules
* accessed directly by poodll flash wdgets on web pages.
* @package mod-poodllpairwork
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

	header("Content-type: text/html");
	echo "<html><head>";
	echo "<style type=\"text/css\">html, body { padding:0; margin:0; } </style>";
	echo "</head><body>";
	echo fetchSWFWidgetCode($widget,$paramstring,$width,$height, $bgcolor);
	echo "</body></html>";
	return;

function fetchSWFWidgetCode($widget,$params,$width,$height, $bgcolor="#FFFFFF"){
	global $CFG;

	$widgetid = html_writer::random_id('laszlobase');
	$widgetjson = \filter_poodll\poodlltools::fetchSWFWidgetJSON($widget,$params,$width,$height, $bgcolor="#FFFFFF", $widgetid);

	$retcode = html_writer::div('','',array('id'=>$widgetid . 'Container'));
	$retcode .=   '<script type="text/javascript" src="'. $CFG->httpswwwroot . '/filter/poodll/flash/embed-compressed.js"></script>
        <script type="text/javascript"> lz.embed.swf(' . $widgetjson . ')</script>';

	return $retcode;

}
	
?>
