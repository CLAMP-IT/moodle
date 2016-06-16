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
 **/

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
$print = optional_param('print', false, PARAM_BOOL);

// Do some authentication.
require_course_login($course);

global $USER;
if ($CFG->version >= '2013111800.00') {
    $context = context_course::instance($course->id);
} else {
    $context = get_context_instance(CONTEXT_COURSE, $course->id);
}
require_capability('mod/turningtech:manage', $context);

// Now generate XML doc.
$session = new TurningSession();
$session->setactivecourse($course);
$session->loadparticipantlist();
$dom = $session->exporttoxml();

// Download file or display it?
if ($print) {
    $text = $dom->saveXML();
    $text = str_replace(array(
                    '><',
                    '<',
                    '>',
                    "\n"
    ), array(
                    "&gt;\n&lt;",
                    '&lt;',
                    '&gt;',
                    "<br>\n"
    ), $text);
    echo "<pre>" . $text . "</pre>";
} else {
    // Default behavior: download file.
    $filename = turningtech_generate_export_filename($course);
    header("Content-Type: text/xml");
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    echo $dom->saveXML();
}