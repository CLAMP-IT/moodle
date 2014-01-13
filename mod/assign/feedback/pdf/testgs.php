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
 * Test that ghostscript is configured correctly
 *
 * @package   assignfeedback_pdf
 * @copyright 2013 Davo Smith
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(__FILE__).'/../../../../config.php');
global $CFG, $PAGE, $OUTPUT;
require_once($CFG->dirroot.'/mod/assign/feedback/pdf/mypdflib.php');

$PAGE->set_url(new moodle_url('/mod/assign/feedback/pdf/testgs.php'));
$PAGE->set_context(context_system::instance());

require_login();
if (!is_siteadmin()) {
    die('Admin only');
}

if (optional_param('sendimage', false, PARAM_BOOL)) {
    // Serve the generated test image.
    AssignPDFLib::send_test_image();
    die();
}

$result = AssignPDFLib::test_gs_path();

switch ($result->status) {
    case AssignPDFLib::GSPATH_OK:
        $msg = get_string('test_ok', 'assignfeedback_pdf');
        $msg .= html_writer::empty_tag('br');
        $imgurl = new moodle_url($PAGE->url, array('sendimage' => 1));
        $msg .= html_writer::empty_tag('img', array('src' => $imgurl));
        break;

    case AssignPDFLib::GSPATH_ERROR:
        $msg = $result->message;
        break;

    default:
        $msg = get_string("test_{$result->status}", 'assignfeedback_pdf');
        break;
}

$returl = new moodle_url('/admin/settings.php', array('section' => 'assignfeedback_pdf'));
$msg .= $OUTPUT->continue_button($returl);

$strheading = get_string('testgs', 'assignfeedback_pdf');
$PAGE->set_heading($strheading);
$PAGE->set_title($strheading);

echo $OUTPUT->header();
echo $OUTPUT->box($msg, 'generalbox ');
echo $OUTPUT->footer();