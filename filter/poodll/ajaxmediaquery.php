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

/**
 * Provides the JSON return
 *
 * @package mod_moodlecst
 * @copyright  2014 Justin Hunt  {@link http://poodll.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 **/

define('AJAX_SCRIPT', true);
require_once('../../config.php');

//set up default return object
$result = new stdClass;

// If session has expired and its an ajax request so we cant do a page redirect.
if (!isloggedin()) {
    $result->code = 'notloggedin';
    $result->message = get_string('sessionerroruser', 'error');
    echo json_encode($result);
    die();
}

$filename = required_param('filename', PARAM_TEXT);

if($CFG->filter_poodll_cloudrecording){
    $dbresults = $DB->get_records('task_adhoc',array('component'=>'filter_poodll','classname'=>'\filter_poodll\task\adhoc_s3_move'));
}elseif($CFG->filter_poodll_ffmpeg){
    $dbresults = $DB->get_records('task_adhoc',array('component'=>'filter_poodll','classname'=>'\filter_poodll\task\adhoc_convert_media'));
}else{
    //if we got here we have nothing to do, no conversions are set up
    $result->code = 'notask';
    $result->message = get_string('no_event_or_task', 'filter_poodll', '');
    echo json_encode($result);
    die();
}

if($dbresults){}
    foreach($dbresults as $rec){
     $cd = $rec->customdata;
     $cd_object=json_decode($cd);
     if($cd_object && $cd_object->filename){
         if($cd_object->filename == $filename){
             $result->code = 'stillwaiting';
             $result->message = get_string('have_task', 'filter_poodll', $filename);
             echo json_encode($result);
             die();
         }
    }
}

//if we get here then we could not find a task
//lets see if we have a recent event (TO DO)
$have_recent_event=false;
if($have_recent_event){
    $result->code = 'mediaready';
    $result->message = get_string('have_recent_event', 'filter_poodll', $filename);
    echo json_encode($result);
    die();
}

//if we got here we have nothing
$result->code = 'notask';
$result->message = get_string('no_event_or_task', 'filter_poodll', $filename);
echo json_encode($result);
die();



