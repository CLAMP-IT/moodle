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
 * @version    Ning
 * @package    mod
 * @subpackage nanogong
 * @copyright  2012 The Gong Project 
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @version    4.2.1
 */

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');

$id = required_param('id', PARAM_INT);   // course

$course = $DB->get_record('course', array('id' => $id), '*', MUST_EXIST);

require_course_login($course);

add_to_log($course->id, 'nanogong', 'view all', 'index.php?id=' . $course->id, '');

$coursecontext = get_context_instance(CONTEXT_COURSE, $course->id);

$PAGE->set_url('/mod/nanogong/index.php', array('id' => $id));
$PAGE->set_title(format_string($course->fullname));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($coursecontext);

echo $OUTPUT->header();

if (! $nanogongs = get_all_instances_in_course('nanogong', $course)) {
    notice(get_string('nonanogongs', 'nanogong'), new moodle_url('/course/view.php', array('id' => $course->id)));
}

if ($course->format == 'weeks') {
    $table->head  = array(get_string('week'), get_string('name'));
    $table->align = array('center', 'left');
} else if ($course->format == 'topics') {
    $table->head  = array(get_string('topic'), get_string('name'));
    $table->align = array('center', 'left', 'left', 'left');
} else {
    $table->head  = array(get_string('name'));
    $table->align = array('left', 'left', 'left');
}

$table = new html_table();

foreach ($nanogongs as $nanogong) {
    if (!$nanogong->visible) {
        $link = html_writer::link(
            new moodle_url('/mod/nanogong/view.php', array('n' => $nanogong->id)),
            format_string($nanogong->name, true),
            array('class' => 'dimmed'));
    } else {
        $link = html_writer::link(
            new moodle_url('/mod/nanogong/view.php', array('n' => $nanogong->id)),
            format_string($nanogong->name, true));
    }

    if ($course->format == 'weeks' or $course->format == 'topics') {
        $table->data[] = array($nanogong->section, $link);
    } else {
        $table->data[] = array($link);
    }
}

echo $OUTPUT->heading(get_string('modulenameplural', 'nanogong'), 2);
echo html_writer::table($table);
echo $OUTPUT->footer();
