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
 * @package   block_clampmail
 * @copyright 2013 Collaborative Liberal Arts Moodle Project
 * @copyright 2012 Louisiana State University (original Quickmail block)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once('lib.php');
require_once($CFG->libdir . '/formslib.php');

require_login();

$courseid = required_param('courseid', PARAM_INT);
$type = optional_param('type', '', PARAM_ALPHA);
$typeid = optional_param('typeid', 0, PARAM_INT);
$sigid = optional_param('sigid', 0, PARAM_INT);

if (!$course = $DB->get_record('course', array('id' => $courseid))) {
    print_error('no_course', 'block_clampmail', '', $courseid);
}

if (!empty($type) and !in_array($type, array('log', 'drafts'))) {
    print_error('no_type', 'block_clampmail', '', $type);
}

if (!empty($type) and empty($typeid)) {
    $string = new stdclass;
    $string->tpe = $type;
    $string->id = $typeid;

    print_error('no_typeid', 'block_clampmail', '', $string);
}

$config = block_clampmail\config::load_configuration($course);

$context = context_course::instance($courseid);
if (!has_capability('block/clampmail:cansend', $context)) {
    print_error('no_permission', 'block_clampmail');
}

$sigs = $DB->get_records('block_clampmail_signatures',
    array('userid' => $USER->id), 'default_flag DESC');

$altparams = array('courseid' => $course->id, 'valid' => 1);
$alternates = $DB->get_records_menu('block_clampmail_alternate',
    $altparams, '', 'id, address');

$blockname = get_string('pluginname', 'block_clampmail');
$header = get_string('email', 'block_clampmail');

$PAGE->set_context($context);
$PAGE->set_course($course);
$PAGE->navbar->add($blockname);
$PAGE->navbar->add($header);
$PAGE->set_title($blockname . ': '. $header);
$PAGE->set_heading($blockname . ': '.$header);
$PAGE->set_url('/blocks/clampmail/email.php', array('courseid' => $courseid));
$PAGE->set_pagetype($blockname);
$PAGE->set_pagelayout('standard');

$PAGE->requires->js('/blocks/clampmail/js/jquery.js');
$PAGE->requires->js('/blocks/clampmail/js/selection.js');

// Build role arrays.
$courseroles = get_roles_used_in_context($context);
$filterroles = $DB->get_records_select('role',
    sprintf('id IN (%s)', $config['roleselection']));
$roles = clampmail::filter_roles($courseroles, $filterroles);

// Add role names.
foreach ($roles as $id => $role) {
    if (empty($role->name)) {
        $roles[$id]->name = role_get_name($role, $context);
    }
}

// Build groups list.
$groupmode = $config['groupmode'];
$groups = block_clampmail\groups::get_groups($groupmode, $courseid);

// Get all the users in the course.
$users = $everyone = block_clampmail\users::get_users($courseid, $groupmode);

// Exclude the current user.
unset($users[$USER->id]);

// In separate groups we filter out users for students.
if ($groupmode == SEPARATEGROUPS && !has_capability('block/clampmail:cansendtoall', $context)) {
    foreach ($everyone as $userid => $user) {
        // Drop users who aren't in a group the user can see.
        if (empty(array_intersect_key($groups, array_flip($user->groups)))) {
            unset($users[$userid]);
        }
    }
}

// Stop execution if there's no valid email target.
$returnurl = new moodle_url('/course/view.php', array('id' => $course->id));
if (empty($users)) {
    notice(get_string('no_users', 'block_clampmail'), $returnurl);
}

if (!empty($type)) {
    $email = $DB->get_record('block_clampmail_'.$type, array('id' => $typeid));
    $email->messageformat = $email->format;
} else {
    $email                = new stdClass;
    $email->id            = null;
    $email->subject       = optional_param('subject', '', PARAM_TEXT);
    $email->message       = optional_param('message_editor[text]', '', PARAM_RAW);
    $email->mailto        = optional_param('mailto', '', PARAM_TEXT);
    $email->messageformat = editors_get_preferred_format();
}
$email->messagetext = $email->message;

$defaultsigid = $DB->get_field('block_clampmail_signatures', 'id', array(
    'userid' => $USER->id, 'default_flag' => 1
));
$email->sigid = $defaultsigid ? $defaultsigid : -1;

// Some setters for the form.
$email->type = $type;
$email->typeid = $typeid;

$editoroptions = array(
    'trusttext' => true,
    'subdirs' => true,
    'maxfiles' => EDITOR_UNLIMITED_FILES,
    'context' => $context,
    'format' => $email->messageformat
);

$email = file_prepare_standard_editor($email, 'message', $editoroptions,
    $context, 'block_clampmail', $type, $email->id);

$selected = array();
if (!empty($email->mailto)) {
    foreach (explode(',', $email->mailto) as $id) {
        $selected[$id] = $users[$id];
        unset($users[$id]);
    }
}

$form = new block_clampmail\email_form(null,
    array(
        'editor_options' => $editoroptions,
        'selected' => $selected,
        'users' => $users,
        'roles' => $roles,
        'groups' => $groups,
        'groupmode' => $groupmode,
        'sigs' => array_map(function($sig) { return $sig->title;
        }, $sigs),
        'alternates' => $alternates
    )
);

$warnings = array();

if ($form->is_cancelled()) {
    redirect(new moodle_url('/course/view.php?id='.$courseid));
} else if ($data = $form->get_data()) {
    if (empty($data->subject)) {
        $warnings[] = get_string('no_subject', 'block_clampmail');
    }

    if (empty($data->mailto)) {
        $warnings[] = get_string('no_users', 'block_clampmail');
    }

    if (empty($warnings)) {

        // Submitted data.
        $data->time = time();
        $data->format = $data->message_editor['format'];
        $data->message = $data->message_editor['text'];
        $data->attachment = clampmail::attachment_names($data->attachments);

        // Store data; id is needed for file storage.
        if (isset($data->send)) {
            $data->id = $DB->insert_record('block_clampmail_log', $data);
            $table = 'log';
        } else if (isset($data->draft)) {
            $table = 'drafts';

            if (!empty($typeid) and $type == 'drafts') {
                $data->id = $typeid;
                $DB->update_record('block_clampmail_drafts', $data);
            } else {
                $data->id = $DB->insert_record('block_clampmail_drafts', $data);
            }
        }

        $data = file_postupdate_standard_editor($data, 'message', $editoroptions,
            $context, 'block_clampmail', $table, $data->id);

        $DB->update_record('block_clampmail_'.$table, $data);

        $prepender = $config['prepend_class'];
        if (!empty($prepender) and !empty($course->$prepender)) {
            $subject = "[{$course->$prepender}] $data->subject";
        } else {
            $subject = $data->subject;
        }

        // An instance id is needed before storing the file repository.
        file_save_draft_area_files($data->attachments, $context->id,
            'block_clampmail', 'attachment_' . $table, $data->id);

        // Send emails.
        if (isset($data->send)) {
            if ($type == 'drafts') {
                clampmail::draft_cleanup($context->id, $typeid);
            }

            list($filename, $file, $actualfile) = clampmail::process_attachments(
                $context, $data, $table, $data->id
            );

            if (!empty($sigs) and $data->sigid > -1) {
                $sig = $sigs[$data->sigid];

                $signaturetext = file_rewrite_pluginfile_urls($sig->signature,
                    'pluginfile.php', $context->id, 'block_clampmail',
                    'signature', $sig->id, $editoroptions);

                $data->message .= $signaturetext;
            }

            // Prepare html content of message.
            $data->message = file_rewrite_pluginfile_urls($data->message, 'pluginfile.php',
                $context->id, 'block_clampmail', $table, $data->id,
                $editoroptions);

            // Same user, alternate email.
            if (!empty($data->alternateid)) {
                $user = clone($USER);
                $user->email = $alternates[$data->alternateid];
            } else {
                $user = $USER;
            }

            // Prepare both plaintext and HTML messages.
            $data->messagetext = format_text_email($data->message, $data->format);
            $data->messagehtml = format_text($data->message, $data->format);

            // Send emails.
            foreach (explode(',', $data->mailto) as $userid) {
                $success = email_to_user($everyone[$userid], $user, $subject,
                    $data->messagetext, $data->messagehtml, $file, $filename, false, $user->email);

                if (!$success) {
                    $warnings[] = get_string("no_email", 'block_clampmail', $everyone[$userid]);
                }
            }

            if ($data->receipt) {
                email_to_user($USER, $user, $subject,
                    $data->messagetext, $data->messagehtml, $file, $filename, false, $user->email);
            }

            if (!empty($actualfile)) {
                unlink($actualfile);
            }
        }
    }
    $email = $data;
}

if (empty($email->attachments)) {
    if (!empty($type)) {
        $attachid = file_get_submitted_draft_itemid('attachment');
        file_prepare_draft_area(
            $attachid, $context->id, 'block_clampmail',
            'attachment_' . $type, $typeid
        );
        $email->attachments = $attachid;
    }
}

$form->set_data($email);

if (empty($warnings)) {
    if (isset($email->send)) {
        redirect(new moodle_url('/blocks/clampmail/emaillog.php',
            array('courseid' => $course->id)));
    } else if (isset($email->draft)) {
        $warnings['success'] = get_string("changessaved");
    }
}

echo $OUTPUT->header();
echo $OUTPUT->heading($blockname);

foreach ($warnings as $type => $warning) {
    $class = ($type == 'success') ? 'notifysuccess' : 'notifyproblem';
    echo $OUTPUT->notification($warning, $class);
}

echo html_writer::start_tag('div', array('class' => 'no-overflow'));
$form->display();
echo html_writer::end_tag('div');

echo $OUTPUT->footer();
