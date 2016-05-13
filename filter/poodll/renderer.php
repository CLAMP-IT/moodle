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


defined('MOODLE_INTERNAL') || die();

/**
 * A custom renderer class that extends the plugin_renderer_base.
 *
 * @package filter_poodll
 * @copyright 2015 Justin Hunt (poodllsupport@gmail.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class filter_poodll_renderer extends plugin_renderer_base {


	public function fetch_revealjs_flashcards($dataset){
		$card_div_array = array();
		foreach($dataset as $data){
			$qsection = html_writer::tag('section',html_writer::tag('h2', $data->questiontext),
				array('data-background'=>'','class'=>'filter_poodll_revealjs_section filter_poodll_revealjs_section_front'));
			$asection = html_writer::tag('section',html_writer::tag('h2', $data->answertext),
				array('data-transition'=>'flip','data-background'=>'','class'=>'filter_poodll_revealjs_section filter_poodll_revealjs_section_back'));
			$cardsection = html_writer::tag('section',$qsection  .  $asection,
				array());
			$card_div_array[] = $cardsection;
		}
		$carddivs = implode(' ', $card_div_array);
      	$slides = html_writer::div($carddivs,'slides');
      	$previousbutton = html_writer::tag('a','previous',array('class'=>'filter_poodll_revealjs_previous btn btn-primary'));
      	$nextbutton = html_writer::tag('a','next',array('class'=>'filter_poodll_revealjs_next btn btn-primary'));
      	$buttons = html_writer::div($previousbutton . $nextbutton,'filter_poodll_revealjs_buttons');
		$reveal = html_writer::div($slides,'reveal filter_poodll_revealjs_container');
		
      	return $reveal . $buttons;
	
	}

	public function fetchLiterallyCanvas($html)
	{
		return $html;

	}

	public function fetchDrawingBoard($html)
	{
		return $html;

	}

	public function fetchAudioPlayer($html)
	{
		return $html;

	}

	public function fetchVideoPlayer($html)
	{
		return $html;

	}

	public function fetchIFrameSWFWidgetCode($widget,$paramsArray,$width,$height, $bgcolor="#FFFFFF"){
		global $CFG;

		//There seems to be an internal margin on the iframe
		//which I could not cancel entirely. So we compensate here to show all the widget
		$marginadjust = 5;
		$fwidth = $marginadjust + $width;
		$fheight = $marginadjust + $height;

		//build the parameter string out of the passed in array
		$params="?";
		foreach ($paramsArray as $key => $value) {
			$params .= '&' . $key . '=' . $value;
		}

		//add in any common params
		$params .= '&debug=false&lzproxied=false';

		//path to our js idgets folder
		$pathtoSWF= $CFG->wwwroot . '/filter/poodll/flash/';


		$retframe="<iframe scrolling=\"no\" class=\"fitvidsignore\" frameBorder=\"0\" src=\"{$pathtoSWF}poodlliframe.php?widget={$widget}&paramstring=" . urlencode($params) . "&width={$width}&height={$height}&bgcolor={$bgcolor}\" width=\"{$fwidth}\" height=\"{$fheight}\"></iframe>";
		return $retframe;


	}

	public function fetchJSWidgetiFrame($widget,$rawparams,$width,$height, $bgcolor="#FFFFFF", $usemastersprite="false")
	{
		global $CFG;

		//build the parameter string out of the passed in array
		$params = "?";
		foreach ($rawparams as $key => $value) {
			$params .= '&' . $key . '=' . $value;
		}

		//add in any common params
		$params .= '&debug=false&lzproxied=false';

		//path to our js idgets folder
		$pathtoJS = $CFG->wwwroot . '/filter/poodll/js/';
		$pathtowidgetfolder = $CFG->wwwroot . '/filter/poodll/js/' . $widget . '/';


		$retframe = "<iframe scrolling=\"no\" frameBorder=\"0\" src=\"{$pathtoJS}poodlliframe.php?widget={$widget}&paramstring=" . urlencode($params) . "&width={$width}&height={$height}&bgcolor={$bgcolor}&usemastersprite={$usemastersprite}\" width=\"{$width}\" height=\"{$height}\"></iframe>";
		return $retframe;
	}


//This is used for all the flash widgets
	public function fetchLazloEmbedCode($widgetopts,$widgetid,$jsmodule)
	{
		global $CFG, $PAGE;

		//this init the M.mod_readaloud thingy, after the page has loaded.
		$PAGE->requires->js(new \moodle_url($CFG->httpswwwroot . '/filter/poodll/flash/embed-compressed.js'));
		$PAGE->requires->js_init_call('M.filter_poodll.laszlohelper.init', array($widgetopts), false, $jsmodule);
		$returnhtml = html_writer::div('', '', array('id' => $widgetid . 'Container'));
		return $returnhtml;
	}


}