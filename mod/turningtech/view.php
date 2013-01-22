<?php // $Id: view.php,v 1.9 2009/04/09 15:19:29 arborrow Exp $

/**
 * This page prints a particular instance of turningtech
 *
 * @author  Your Name <your@email.address>
 * @version $Id: view.php,v 1.9 2009/04/09 15:19:29 arborrow Exp $
 * @package mod/turningtech
 */

/// (Replace turningtech with the name of your module and remove this line)

require('../../config.php');
require_once($CFG->dirroot . '/mod/turningtech/lib.php');
global $DB;
$id = optional_param('id', 0, PARAM_INT); // course_module ID, or
$a  = optional_param('a', 0, PARAM_INT); // turningtech instance ID

if ($id) {
    if (!$cm = get_coursemodule_from_id('turningtech', $id)) {
        error('Course Module ID was incorrect');
    }

    if (!$course = $DB->get_record('course', array(
        'id' => $cm->course
    ))) {
        error('Course is misconfigured');
    }

    if (!$turningtech = $DB->get_record('turningtech', array(
        'id' => $cm->instance
    ))) {
        error('Course module is incorrect');
    }

} else if ($a) {
    if (!$turningtech = $DB->get_record('turningtech', array(
        'id' => $a
    ))) {
        error('Course module is incorrect');
    }
    if (!$course = $DB->get_record('course', array(
        'id' => $turningtech->course
    ))) {
        error('Course is misconfigured');
    }
    if (!$cm = get_coursemodule_from_instance('turningtech', $turningtech->id, $course->id)) {
        error('Course Module ID was incorrect');
    }

} else {
    error('You must specify a course_module ID or an instance ID');
}

require_login($course, true, $cm);

add_to_log($course->id, "turningtech", "view", "view.php?id=$cm->id", "$turningtech->id");

/// Print the page header
$strturningtechs = get_string('modulenameplural', 'turningtech');
$strturningtech  = get_string('modulename', 'turningtech');

//$navlinks = array();
//$navlinks[] = array('name' => $strturningtechs, 'link' => "index.php?id=$course->id", 'type' => 'activity');
//$navlinks[] = array('name' => format_string($turningtech->name), 'link' => '', 'type' => 'activityinstance');

//$navigation = build_navigation($navlinks);

//print_header_simple(format_string($turningtech->name), '', $navigation, '', '', true,
//              update_module_button($cm->id, $course->id, $strturningtech), navmenu($course, $cm));

/// Print the main part of the page

echo 'YOUR CODE GOES HERE';


/// Finish the page
//print_footer($course);

?>