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
 * This page displays the confirmation form for deleting a device ID map
 * @package    mod_turningtech
 * @copyright  2012 Turning Technologies
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once('../../config.php');
require_once($CFG->dirroot . '/mod/turningtech/lib.php');
global $DB;
$devicemapid = required_param('id', PARAM_INT);
$courseid    = optional_param('course', null, PARAM_INT);
$course      = null;
$devicemap   = null;
if (!$devicemap = TurningTechDeviceMap::fetch(array(
                'id' => $devicemapid
))) {
    print_error('couldnotfinddeviceid', 'turningtech', '', $devicemapid));
}
// Has the form been confirmed.
$confirm = optional_param('confirm', 0, PARAM_BOOL);
// Figure out which course we're dealing with.
if (empty($courseid)) {
    if (!$devicemap->isAllCourses()) {
        $courseid = $devicemap->getField('courseid');
    } else {
        print_error('courseidincorrect', 'turningtech');
    }
}
if (!$course = $DB->get_record('course', array(
                'id' => $courseid
))) {
    print_error('courseidincorrect', 'turningtech');
}
// Make sure user is enrolled.
require_course_login($course);
// Verify user has permission to delete this devicemap.
if ($USER->id != $devicemap->getField('userid')) {
    // Current user is not the owner of the devicemap.  So
    // Verify current user is a teacher.
    if ($CFG->version >= '2013111800.00') {
        $context = context_course::instance($course->id);
    } else {
        $context = get_context_instance(CONTEXT_COURSE, $course->id);
    }
    if (!has_capability('mod/turningtech:manage', $context)) {
        print_error('notpermittedtoeditdevicemap', 'turningtech');
    }
}
if ($confirm && confirm_sesskey()) {
    $devicemap->delete();
    turningtech_set_message(get_string('deviceiddeleted', 'turningtech'));
    redirect($CFG->wwwroot . "/mod/turningtech/index.php?id=" . $course->id);
} else {
    // Build breadcrumbs.
    $PAGE->set_url($CFG->wwwroot . '/mod/turningtech/delete_device.php', array(
                    'id' => $devicemapid,
                    'course' => $courseid
    ));
    $PAGE->set_course($course);
    $PAGE->requires->css('/mod/turningtech/css/style.css');
    $title      = get_string('modulename', 'turningtech');
    $heading    = get_string('editdevicemap', 'turningtech');
    $navlinks   = array();
    $navlinks[] = array(
                    'name' => $title,
                    'link' => "{$CFG->wwwroot}/mod/turningtech/index.php?id={$course->id}",
                    'type' => 'activity'
                                    );
    $navlinks[] = array(
                    'name' => $heading,
                    'link' => '',
                    'type' => 'activity'
    );
    $navigation = build_navigation($navlinks);
    echo $OUTPUT->header();
    $optionyes = array(
                    'id' => $devicemapid,
                    'course' => $course->id,
                    'confirm' => 1,
                    'sesskey' => sesskey()
    );
    $optionno  = array(
                    'id' => $course->id
    );
    $message   = "<p class='tt_confirm_device_delete'>";
    $message  .= get_string('deletedevicemap', 'turningtech', $devicemap->getField('deviceid')) . "</p>";
    echo $OUTPUT->confirm($message, new moodle_url($CFG->wwwroot . '/mod/turningtech/delete_device.php', $optionyes),
                     new moodle_url('/mod/turningtech/index.php', $optionno));
    echo $OUTPUT->footer();
}