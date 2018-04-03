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

$courseid    = required_param('courseid', PARAM_INT);
$type        = optional_param('type', 'log', PARAM_ALPHA);
$typeid      = optional_param('typeid', 0, PARAM_INT);
$action      = optional_param('action', null, PARAM_ALPHA);
$page        = optional_param('page', 0, PARAM_INT);
$perpage     = optional_param('perpage', 10, PARAM_INT);
$userid      = optional_param('userid', $USER->id, PARAM_INT);
$course      = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);

require_login($course);

// Check permissions.
$coursecontext = context_course::instance($course->id);
require_capability('block/clampmail:cansend', $coursecontext);

// Has to be in on of these.
if (!in_array($type, array('log', 'drafts'))) {
    print_error('not_valid', 'block_clampmail', '', $type);
}

$canimpersonate = has_capability('block/clampmail:canimpersonate', $coursecontext);
if (!$canimpersonate and $userid != $USER->id) {
    print_error('not_valid_user', 'block_clampmail');
}

$config = block_clampmail\config::load_configuration($course);

$validactions = array('delete', 'confirm');

$candelete = ($type == 'drafts');

if (isset($action) and !in_array($action, $validactions)) {
    print_error('not_valid_action', 'block_clampmail', '', $action);
}

if (isset($action) and empty($typeid)) {
    print_error('not_valid_typeid', 'block_clampmail', '', $action);
}

$blockname = get_string('pluginname', 'block_clampmail');
$header = get_string($type, 'block_clampmail');

$PAGE->set_context($coursecontext);
$PAGE->set_course($course);
$PAGE->navbar->add($blockname);
$PAGE->navbar->add($header);
$PAGE->set_title($blockname . ': ' . $header);
$PAGE->set_heading($blockname . ': ' . $header);
$PAGE->set_url('/blocks/clampmail/emaillog.php', array('courseid' => $courseid, 'type' => $type));
$PAGE->set_pagetype($blockname);
$PAGE->set_pagelayout('standard');

$dbtable = 'block_clampmail_' . $type;

$params = array('userid' => $userid, 'courseid' => $courseid);
$count = $DB->count_records($dbtable, $params);

switch ($action) {
    case "confirm":
        if (clampmail::cleanup($dbtable, $coursecontext->id, $typeid)) {
            $url = new moodle_url('/blocks/clampmail/emaillog.php', array(
                'courseid' => $courseid,
                'type' => $type
            ));
            redirect($url);
        } else {
            print_error('delete_failed', 'block_clampmail', '', $typeid);
        }
    case "delete":
        $html = clampmail::delete_dialog($courseid, $type, $typeid);
        break;
    default:
        $html = clampmail::list_entries($courseid, $type, $page, $perpage, $userid, $count, $candelete);
}

if ($canimpersonate and $USER->id != $userid) {
    $user = $DB->get_record('user', array('id' => $userid));
    $header .= ' for '. fullname($user);
}

echo $OUTPUT->header();
echo $OUTPUT->heading($header);

if ($canimpersonate) {
    $sql = "SELECT DISTINCT(l.userid), u.firstname, u.lastname, u.firstnamephonetic, u.lastnamephonetic, u.middlename, u.alternatename
                FROM {block_clampmail_$type} l,
                     {user} u
                WHERE u.id = l.userid AND courseid = ? ORDER BY u.lastname";
    $users = $DB->get_records_sql($sql, array($courseid));

    $useroptions = array_map(function($user) { return fullname($user);
    }, $users);

    $url = new moodle_url('emaillog.php', array(
        'courseid' => $courseid,
        'type' => $type
    ));

    $defaultoption = array('' => get_string('select_users', 'block_clampmail'));

    echo $OUTPUT->single_select($url, 'userid', $useroptions, $userid, $defaultoption);
}

if (empty($count)) {
    echo $OUTPUT->notification(get_string('no_'.$type, 'block_clampmail'));

    echo $OUTPUT->continue_button('/blocks/clampmail/email.php?courseid='.$courseid);

    echo $OUTPUT->footer();
    exit;
}

echo $html;

echo $OUTPUT->footer();
