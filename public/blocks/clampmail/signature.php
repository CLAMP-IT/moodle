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
 * Page for managing signatures.
 *
 * @package   block_clampmail
 * @copyright 2012 Louisiana State University
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');

$courseid = required_param('courseid', PARAM_INT);
$signatureid = optional_param('id', 0, PARAM_INT);
$updated = optional_param('updated', 0, PARAM_INT);
$confirm = optional_param('confirm', 0, PARAM_INT);
$course = $DB->get_record('course', ['id' => $courseid], '*', MUST_EXIST);

require_login($course);

if ($courseid && !$course = $DB->get_record('course', ['id' => $courseid])) {
    print_error('no_course', 'block_clampmail', '', $courseid);
}

// Setup page.
$PAGE->set_url('/blocks/clampmail/signature.php', ['courseid' => $courseid]);
$PAGE->set_pagelayout('report');

// Check permissions.
$coursecontext = context_course::instance($course->id);
require_capability('block/clampmail:cansend', $coursecontext);

// Delete signature if requested.
if ($confirm) {
    require_sesskey();
    $DB->delete_records('block_clampmail_signatures', ['id' => $signatureid, 'userid' => $USER->id]);
    redirect(new moodle_url('/blocks/clampmail/signature.php', ['courseid' => $courseid, 'updated' => 1]));
}

// Get all the signatures.
$signatures = block_clampmail\signature::get_signatures($USER->id);
$signatureoptions = [0 => get_string('newsignature', 'block_clampmail')];
foreach ($signatures as $sigid => $sig) {
    $signatureoptions[$sigid] = $sig->title;
    if (1 == $sig->default_flag) {
        $signatureoptions[$sigid] = get_string('default_signature', 'block_clampmail', $sig->title);
    } else {
        $signatureoptions[$sigid] = $sig->title;
    }
}

// Prepare signature for editor.
if (empty($signatureid)) {
    $signature = new stdClass();
    $signature->signature = '';
} else {
    $signature = $signatures[$signatureid];
}
$signature->signatureformat = $USER->mailformat;
$signature = file_prepare_standard_editor(
    $signature,
    'signature',
    [
         'context' => $coursecontext,
     ],
    $coursecontext,
    'block_clampmail',
    'signature',
    $signatureid
);

// Finish setting up the page.
$blockname = get_string('pluginname', 'block_clampmail');
$header = get_string('manage_signatures', 'block_clampmail');
$PAGE->set_title($course->shortname . ': ' .
    get_string('pluginname', 'block_clampmail') . ': ' .
    get_string('signature', 'block_clampmail'));
$PAGE->set_heading($course->fullname);
$PAGE->set_context($coursecontext);
$PAGE->set_course($course);
$PAGE->navbar->add($blockname, new moodle_url('/blocks/clampmail/email.php', ['courseid' => $courseid]));
$PAGE->navbar->add($header);
$PAGE->set_title($blockname . ': ' . $header);
$PAGE->set_heading($blockname . ': ' . $header);
$PAGE->set_pagetype('CLAMPMail');

// Create form.
$mform = new block_clampmail\signature_form('signature.php', [
    'courseid' => $courseid,
]);
$mform->set_data($signature);

// Process form.
if ($mform->is_cancelled()) {
    redirect(new moodle_url('/blocks/clampmail/email.php', ['courseid' => $courseid]));
} else if ($fromform = $mform->get_data()) {
    if (!empty($fromform->delete)) {
        $delete = true;
    } else {
        $fromform->signature = $fromform->signature_editor['text'];
        $fromform->userid = $USER->id;
        if (empty($fromform->default_flag)) {
            $fromform->default_flag = 0;
        }

        // If the new default clear all defaults.
        if (1 == $fromform->default_flag) {
            $default = $DB->get_record(
                'block_clampmail_signatures',
                ['userid' => $fromform->userid, 'default_flag' => 1]
            );
            if (!empty($default)) {
                $default->default_flag = 0;
                $DB->update_record('block_clampmail_signatures', $default);
            }
        }

        // Update database.
        if (empty($fromform->id)) {
            $fromform->id = $DB->insert_record('block_clampmail_signatures', $fromform);
        } else {
            $DB->update_record('block_clampmail_signatures', $fromform);
        }

        // Return to view signature; this also reloads the signatures.
        redirect(new moodle_url('signature.php', [
            'id' => $fromform->id, 'courseid' => $courseid, 'updated' => 1]));
    }
}

// Display header.
echo $OUTPUT->header();
echo $OUTPUT->heading($blockname);
echo block_clampmail\navigation::print_navigation(
    block_clampmail\navigation::get_links($course->id, $coursecontext),
    $header
);

// Display notifications.
if ($updated) {
    echo $OUTPUT->notification(get_string('changessaved'), 'notifysuccess');
}

// Display deletion confirmation.
if (!empty($delete) && !empty($fromform->id)) {
    echo $OUTPUT->confirm(
        get_string('delete_signature_confirm', 'block_clampmail', $signature->title),
        new moodle_url('signature.php', [
            'id' => $signature->id,
            'courseid' => $courseid,
            'confirm' => 1,
        ]),
        new moodle_url('signature.php', [
            'id' => $signature->id,
            'courseid' => $courseid,
        ])
    );
} else {
    // Display signature selector.
    echo $OUTPUT->single_select(
        new moodle_url(
            'signature.php',
            ['courseid' => $courseid]
        ),
        'id',
        $signatureoptions,
        $signatureid
    );

    // Display form.
    $mform->display();
}

// Display footer.
echo $OUTPUT->footer();
