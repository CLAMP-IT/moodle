<?php

// This file is not a part of Moodle - http://moodle.org/
// This is a none core contributed module.
//
// This is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// This is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// The GNU General Public License
// can be see at <http://www.gnu.org/licenses/>.

/*
 * __________________________________________________________________________
 *
 * PoodLL Atto for Moodle 2.x
 *
 * This plugin need to use together with Poodll filter.
 *
 * @package    poodll
 * @subpackage atto_poodll
 * @copyright  2013 Justin Hunt  {@link http://www.poodll.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * __________________________________________________________________________
 */

define('NO_MOODLE_COOKIES', false);
require(__DIR__ . '/../../../../../../config.php');

$PAGE->set_context(context_system::instance());
$PAGE->set_url('/lib/editor/atto/plugins/poodll/dialog/poodll.php');

if (isset($SESSION->lang)) {
    // Language is set via page url param.
    $lang = $SESSION->lang;
} else {
    $lang = 'en';
}
require_login();  // CONTEXT_SYSTEM level.
$editor = get_texteditor('atto');
//$plugin = $editor->get_plugin('poodll');
$iframeid = optional_param('iframeid', 'none', PARAM_TEXT);
$itemid = optional_param('itemid', '', PARAM_TEXT);
$recorder = optional_param('recorder', '', PARAM_TEXT);
$updatecontrol = optional_param('updatecontrol', '', PARAM_TEXT);
$usewhiteboard = optional_param('usewhiteboard', 'drawingboard', PARAM_TEXT);
$coursecontextid = optional_param('coursecontextid', 0, PARAM_INT);
$modulecontextid = optional_param('modulecontextid', 0, PARAM_INT);

//contextid
$usercontextid=context_user::instance($USER->id)->id;
$callbackjs = '';//'atto_poodll_button.updatefilename';
$hints= Array('size'=>'small');
if($coursecontextid){
	$hints['coursecontextid']=$coursecontextid;
}
if($modulecontextid){
	$hints['modulecontextid']=$modulecontextid;
}

// Load the recorder.
switch($recorder){
 case 'video':
 	$recorderhtml =  \filter_poodll\poodlltools::fetchVideoRecorderForSubmission('auto', 'none', $updatecontrol, $usercontextid,'user','draft',$itemid,0,$callbackjs,$hints);
	$instruction = get_string('recordtheninsert', 'atto_poodll');
 	break;
 case 'snapshot':
 	$recorderhtml =  \filter_poodll\poodlltools::fetchHTML5SnapshotCamera($updatecontrol,350,400,$usercontextid,'user','draft',$itemid,$callbackjs,$hints);
	$instruction = get_string('snaptheninsert', 'atto_poodll');
 	break;
 case 'whiteboard':
 	$recorderhtml =  \filter_poodll\poodlltools::fetchWhiteboardForSubmission($updatecontrol, $usercontextid,'user','draft',$itemid,400,350,"",$usewhiteboard,$callbackjs);
	$recorderhtml = "<div class='jswhiteboard'>" . $recorderhtml . "</div>"; 
	$instruction = get_string('drawtheninsert', 'atto_poodll');
 	break;
 case 'audiored5':
 case 'audiomp3':
 default:
	$recorderhtml =  \filter_poodll\poodlltools::fetchMP3RecorderForSubmission($updatecontrol, $usercontextid ,'user','draft',$itemid,0,$callbackjs,$hints);
	$instruction = get_string('recordtheninsert', 'atto_poodll');
}

$PAGE->set_pagelayout('embedded');
$PAGE->set_title(get_string('dialogtitle', 'atto_poodll'));
$PAGE->requires->css(new moodle_url($CFG->wwwroot . '/lib/editor/atto/plugins/poodll/dialog/poodll.css'));
$PAGE->requires->js(new moodle_url($CFG->wwwroot. '/filter/poodll/module.js'),true);
$PAGE->requires->jquery();

//load our resize script
//lets disable this ...JUSTIN 20170826
//$PAGE->requires->js_call_amd("filter_poodll/responsiveiframe", 'init', array(array('iframeid' => $iframeid)));

echo $OUTPUT->header();
?>
<div id="atto_poodll_container">
<div style="text-align: center;">
<p id="messageAlert"><?php echo $instruction; ?></p>
<?php
echo $recorderhtml;
?>
</div>
</div>
<?php
echo $OUTPUT->footer();
