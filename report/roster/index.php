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
 * Display a roster of students
 *
 * @package   report_roster
 * @copyright 2013 Lafayette College ITS
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(__FILE__) . '/../../config.php');
require_once(dirname(__FILE__) . '/locallib.php');

$id     = required_param('id', PARAM_INT);
$mode   = optional_param('mode', ROSTER_MODE_DISPLAY, PARAM_TEXT);
$group  = optional_param('group', 0, PARAM_INT);
$course = $DB->get_record('course', array('id' => $id), '*', MUST_EXIST);

require_login($course);

// Setup page.
$PAGE->set_url('/report/roster/index.php', array('id' => $id));
if ($mode === ROSTER_MODE_PRINT) {
    $PAGE->set_pagelayout('print');
} else {
    $PAGE->set_pagelayout('report');
}
$returnurl = new moodle_url('/course/view.php', array('id' => $id));

// Check permissions.
$coursecontext = context_course::instance($course->id);
require_capability('report/roster:view', $coursecontext);

// Get all the users.
$userlist = get_enrolled_users($coursecontext, '', $group, user_picture::fields('u', null, 0, 0, true));

// Get suspended users.
$suspended = get_suspended_userids($coursecontext);

$data = array();
foreach ($userlist as $user) {
    if (!in_array($user->id, $suspended)) {
        $item = $OUTPUT->user_picture($user, array('size' => 100, 'courseid' => $course->id));
        $item .= html_writer::tag('span', fullname($user));
        $data[] = $item;
    }
}

// Finish setting up page.
$PAGE->set_title($course->shortname .': '. get_string('roster' , 'report_roster'));
$PAGE->set_heading($course->fullname);
$PAGE->requires->yui_module('moodle-report_roster-roster', 'M.report_roster.init');
$PAGE->requires->strings_for_js(array(
        'learningmodeon',
        'learningmodeoff',
    ), 'report_roster');

// Display the roster to the user.
echo $OUTPUT->header();
echo html_writer::tag('button', get_string('learningmodeoff', 'report_roster'), array('id' => 'report-roster-toggle'));
echo report_roster_output_action_buttons($id, $group, $mode, $PAGE->url);
echo html_writer::alist($data, array('class' => 'report-roster'));
echo $OUTPUT->footer();
