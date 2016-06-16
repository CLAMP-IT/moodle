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
 * Displays the edit form for device associations
 * @package    mod_turningtech
 * @copyright  2012 Turning Technologies
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once('../../config.php');
require_once($CFG->dirroot . '/mod/turningtech/lib.php');
require_once($CFG->dirroot . '/mod/turningtech/lib/forms/turningtech_device_form.php');
global $DB, $PAGE, $USER;
$PAGE->requires->css('/mod/turningtech/css/style.css');
$devicemapid = optional_param('id', null, PARAM_INT);
$courseid    = optional_param('course', null, PARAM_INT);
$studentid   = optional_param('student', null, PARAM_INT);
$course    = null;
$student   = null;
$devicemap = null;
// Populate course and student data from devicemap.
if (!empty($devicemapid)) {
    if (!$devicemap = TurningTechDeviceMap::fetch(array(
                    'id' => $devicemapid
    ))) {
        print_error('couldnotfinddeviceid', 'turningtech', '', $devicemapid);
    }
    if (!$studentid) {
        $studentid = $devicemap->getField('userid');
    }
}
// Figure out which course we're dealing with.
if (empty($courseid)) {
    if (!empty($devicemap) && !$devicemap->isAllCourses()) {
        $courseid = $devicemap->getField('courseid');
    } else {
        print_error('courseidincorrect', 'turningtech');
    }
}
// Verify course is valid.
if (!$course = $DB->get_record('course', array(
                'id' => $courseid
))) {
    print_error('courseidincorrect', 'turningtech');
}
// If we are creating a new devicemap and did not receive a student ID, throw an error.
if (empty($studentid) && empty($devicemap)) {
    print_error('nostudentdatareceived', 'turningtech');
}
// Verify student ID is valid.
if (!$student = $DB->get_record('user', array(
                'id' => $studentid
))) {
    print_error('studentidincorrect', 'turningtech');
}
// Default URL for redirection.
$default_url = "{$CFG->wwwroot}/mod/turningtech/index.php?id={$course->id}";
// Make sure user is enrolled.
require_course_login($course);
// Verify user has permission to delete this devicemap.
if (!empty($devicemap) && ($USER->id != $devicemap->getField('userid'))) {
    // Current user is not the owner of the devicemap.  So
    // Verify current user is a teacher.
    if ($CFG->version >= '2013111800.00') {
        $context = context_course::instance($course->id);
    } else {
        $context = get_context_instance(CONTEXT_COURSE, $course->id);
    }
    if (!has_capability('mod/turningtech:manage', $context)) {
        error(get_string('notpermittedtoeditdevicemap', 'turningtech'), $default_url);
    }
}
$url  = "edit_device.php?";
$args = array();
if (!empty($devicemapid)) {
    $args[] = "id={$devicemapid}";
}
if (!empty($course)) {
    $args[] = "course={$course->id}";
}
if (!empty($studentid)) {
    $args[] = "student={$studentid}";
}
$editform = new turningtech_device_form($url . implode('&', $args));
if ($editform->is_cancelled()) {
    // User clicked cancel button.
    redirect($default_url);
} else if ($data = $editform->get_data()) {
    // Data is validated.
    $map = TurningTechDeviceMap::generatefromform($data);
    if ($map->save()) {
        turningtech_set_message(get_string('deviceidsaved', 'turningtech'));
        redirect($default_url);
    } else {
        error(get_string('errorsavingdeviceid', 'turningtech'), $default_url);
    }
} else {
    // Display form page.
    $PAGE->set_url('/mod/turningtech/edit_device.php', array(
                    'course' => $courseid,
                    'student' => $studentid
    ));
    $title = get_string('modulename', 'turningtech');
    // Print the header.
    $PAGE->set_heading($title);
    echo $OUTPUT->header();
    $dto = new stdClass();
    if (!empty($devicemap)) {
        // Get data for the form.
        $dto              = $devicemap->getData();
        // Rename id field.
        $dto->devicemapid = $dto->id;
        unset($dto->id);
        // Set the current course in case we need it.
    } else {
        // Set data if we are creating a new device map.
        $dto->userid       = $student->id;
        $dto->all_courses  = 1; // Device registered for All Courses.
        $dto->typeid = 1;       // Device registered for Response Card.
    }
    $dto->courseid = $course->id;
    // Save data to the form.
    $editform->set_data($dto);
    // Display the form.
    $editform->display();
    if (!empty($devicemap)) {
        echo "<p class='tt_delete_device'>
		<a href='{$CFG->wwwroot}/mod/turningtech/delete_device.php?id={$devicemap->getField('id')}&course={$course->id}'>";
        echo get_string('deletethisdeviceid', 'turningtech');
        echo "</a></p>\n";
    }
    echo $OUTPUT->footer();
}