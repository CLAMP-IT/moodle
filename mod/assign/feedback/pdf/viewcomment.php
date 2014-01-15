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
 * The entry point for viewing an annotated PDF
 *
 * @package   mod_assign
 * @subpackage assignsubmission_pdf
 * @copyright 2012 Davo Smith
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(__FILE__).'/../../../../config.php');
global $CFG, $DB, $PAGE, $USER;
require_once($CFG->dirroot.'/mod/assign/locallib.php');
require_once($CFG->dirroot.'/mod/assign/submission/pdf/lib.php');

$id   = required_param('id', PARAM_INT);
$submissionid = optional_param('submissionid', 0, PARAM_INT);
$pageno = optional_param('pageno', 1, PARAM_INT);

$url = new moodle_url('/mod/assign/feedback/pdf/viewcomment.php', array('submissionid'=>$submissionid,
                                                                       'pageno'=>$pageno,
                                                                       'id' => $id));
$cm = get_coursemodule_from_id('assign', $id, 0, false, MUST_EXIST);
$course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);

$PAGE->set_url($url);
require_login($course, false, $cm);

$context = context_module::instance($cm->id);

$assignment = new assign($context, $cm, $course);
$feedbackpdf = new assign_feedback_pdf($assignment, 'feedback_pdf');

// Check the user has the relevant capability to access this assignment submission.
if ($submissionid) {
    $submission = $DB->get_record('assign_submission', array('id' => $submissionid), '*', MUST_EXIST);
} else {
    if ($assignment->get_instance()->teamsubmission) {
        $submission = $assignment->get_group_submission($USER->id, 0, false);
    } else {
        $submission = $assignment->get_user_submission($USER->id, false);
    }
}
if (!$submission) {
    print_error('nosubmission', 'assignfeedback_pdf');
}

$feedbackpdf->edit_comment_page($submission->id, $pageno, false);