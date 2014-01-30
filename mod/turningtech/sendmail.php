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
 * export the course participant list
 * @package    mod_turningtech
 * @copyright  2012 Turning Technologies
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */
require_once('../../config.php');
require_once($CFG->dirroot . '/mod/turningtech/lib.php');
global $DB;
$id = required_param('id', PARAM_INT); // Get course.
if (!$course = $DB->get_record('course', array(
    'id' => $id
))) {
    print_error('courseidincorrect', 'turningtech');
}
// An optional URL parameter to help debugging.
$action = optional_param('action', false, PARAM_ALPHA);
// Do some authentication.
require_course_login($course);
global $USER, $CFG;
if ($CFG->version >= '2013111800.00') {
    $context = context_course::instance($course->id);
} else {
    $context = get_context_instance(CONTEXT_COURSE, $course->id);
}

require_capability('mod/turningtech:manage', $context);
if (TurningTechMoodleHelper::isuserinstructorincourse($USER, $course)) {
    $emailresult = turningtech_mail_reminder($course);
    if ($emailresult) {
        $_SESSION['email_message'] = 'true';
        redirect($CFG->wwwroot . "/mod/turningtech/device_lookup.php?id=" . $course->id . "&status=1");
    } else {
        $_SESSION['email_message'] = 'false';
        redirect($CFG->wwwroot . "/mod/turningtech/device_lookup.php?id=" . $course->id . "&status=0");
    }
}