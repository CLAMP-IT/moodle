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
 * This page prints a particular instance of turningtech
 *
 * @package    mod_turningtech
 * @copyright  Turning Technologies
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Replace turningtech with the name of your module and remove this line.

require('../../config.php');
require_once($CFG->dirroot . '/mod/turningtech/lib.php');
global $DB;
$id = optional_param('id', 0, PARAM_INT); // Course_module ID, or
$a  = optional_param('a', 0, PARAM_INT); // turningtech instance ID.

if ($id) {
    if (!$cm = get_coursemodule_from_id('turningtech', $id)) {
        print_error('Course Module ID was incorrect');
    }

    if (!$course = $DB->get_record('course', array(
        'id' => $cm->course
    ))) {
        print_error('Course is misconfigured');
    }

    if (!$turningtech = $DB->get_record('turningtech', array(
        'id' => $cm->instance
    ))) {
        print_error('Course module is incorrect');
    }

} else if ($a) {
    if (!$turningtech = $DB->get_record('turningtech', array(
        'id' => $a
    ))) {
        print_error('Course module is incorrect');
    }
    if (!$course = $DB->get_record('course', array(
        'id' => $turningtech->course
    ))) {
        print_error('Course is misconfigured');
    }
    if (!$cm = get_coursemodule_from_instance('turningtech', $turningtech->id, $course->id)) {
        print_error('Course Module ID was incorrect');
    }

} else {
    print_error('You must specify a course_module ID or an instance ID');
}

require_login($course, true, $cm);

add_to_log($course->id, "turningtech", "view", "view.php?id=$cm->id", "$turningtech->id");

// Print the page header.
$strturningtechs = get_string('modulenameplural', 'turningtech');
$strturningtech  = get_string('modulename', 'turningtech');
/*
// Commented Code
// $navlinks = array();
// $navlinks[] = array('name' => $strturningtechs, 'link' => "index.php?id=$course->id", 'type' => 'activity');
// $navlinks[] = array('name' => format_string($turningtech->name), 'link' => '', 'type' => 'activityinstance');

// $navigation = build_navigation($navlinks);

// print_header_simple(format_string($turningtech->name), '', $navigation, '', '', true,
//              update_module_button($cm->id, $course->id, $strturningtech), navmenu($course, $cm));
// End.

// Print the main part of the page.
 */
echo 'YOUR CODE GOES HERE';


// Finish the page
// print_footer($course);
// End.