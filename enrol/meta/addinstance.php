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
 *
 * Adds new instance of enrol_meta to specified courses
 *
 * @package    enrol_meta
 * @copyright  2010 Petr Skoda {@link http://skodak.org}
 * @copyright  2013 Robert Puffer
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');
require_once("$CFG->dirroot/course/lib.php");
require_once("$CFG->dirroot/enrol/meta/locallib.php");


define("MAX_COURSES_PER_PAGE", 1000);
global $DB, $PAGE, $OUTPUT, $COURSE;

$id             = required_param('id',PARAM_INT); // course id
$add            = optional_param('add', 0, PARAM_BOOL);
$remove         = optional_param('remove', 0, PARAM_BOOL);
$showall        = optional_param('showall', 0, PARAM_BOOL);
$searchtext     = optional_param('searchtext', '', PARAM_RAW); // search string
$previoussearch = optional_param('previoussearch', 0, PARAM_BOOL);
$previoussearch = ($searchtext != '') or ($previoussearch) ? 1:0;

if (! $site = get_site()) {
    redirect("$CFG->wwwroot/$CFG->admin/index.php");
}

$course = $DB->get_record('course', array('id'=>$id), '*', MUST_EXIST);
$context = get_context_instance(CONTEXT_COURSE, $course->id, MUST_EXIST);

$PAGE->set_url('/enrol/meta/addinstance.php', array('id'=>$course->id));
$PAGE->set_pagelayout('admin');

navigation_node::override_active_url(new moodle_url('/enrol/instances.php', array('id'=>$course->id)));


require_login($course->id);
require_capability('moodle/course:enrolconfig', $context);
$enrol = enrol_get_plugin('meta');

$strassigncourses = get_string('assigncourses', 'enrol_meta');
$strsearch        = get_string("search", 'enrol_meta');
$strsearchresults  = get_string("searchresults", 'enrol_meta');
$strcourses   = get_string("courses", 'enrol_meta');
$stralreadycourses = get_string('alreadycourses', 'enrol_meta');
$strpotentialcourses = get_string('potentialcourses', 'enrol_meta');
$straddcourses = get_string('addcourses', 'enrol_meta');
$strremovecourse = get_string('removecourse', 'enrol_meta');
$strtomanytoshow = get_string('toomanytoshow', 'enrol_meta');


if (!$frm = data_submitted()) {
    $note = 'Use this form to add courses to your meta course (this will import the enrolments)';
    $OUTPUT->box($note);

/// A form was submitted so process the input

} else {
    if ($add and !empty($frm->addselect) and confirm_sesskey()) {
        $timestart = $timeend = 0;
        foreach ($frm->addselect as $addcourse) {
            $eid = $enrol->add_instance($COURSE, array('customint1'=>$addcourse));
            enrol_meta_sync($COURSE->id);
        }
    } else if ($remove and !empty($frm->removeselect) and confirm_sesskey()) {
        foreach ($frm->removeselect as $removecourse) {
            $select = "courseid = $COURSE->id AND customint1 = $removecourse";
            $enroltodelete = $DB->get_record_select('enrol', $select);
            $eid = $enrol->delete_instance($enroltodelete);
        }

    } else if ($showall and confirm_sesskey()) {
        $searchtext = '';
        $previoussearch = 0;
    }
}


/// Get all existing students and teachers for this course.
if(! $alreadycourses = $DB->get_records('enrol', array('courseid'=>$course->id, 'enrol'=>'meta'),
        'sortorder,courseid','customint1, courseid, enrol, id')) {
    $alreadycourses = array();
} else {
    foreach ($alreadycourses as $key=>$acourse) {
        $alreadycourses[$key] = $DB->get_field('course', 'fullname', array('id'=>$key));
    }
}

$numcourses = 0;
$searchcourses = array();
// Get search results excluding any users already in this course
if (($searchtext != '') and $previoussearch and confirm_sesskey()) {
    if ($searchcourses = get_courses_search(explode(" ",$searchtext),'fullname ASC',0,99999,$numcourses)) {
        foreach ($searchcourses as $tmp) {
            if (array_key_exists($tmp->id,$alreadycourses)) {
                unset($searchcourses[$tmp->id]);
//            } elseif (!empty($tmp->metacourse)) {
            } elseif ($ismeta = $DB->get_records('enrol',array('courseid' => $tmp->id, 'enrol' => 'meta'))) { // don't allow courses that already have meta enrollments
                unset($searchcourses[$tmp->id]);
            } else {
                $searchcourses[$tmp->id] = $tmp->fullname;
            }
        }
        if (array_key_exists($course->id,$searchcourses)) {
            unset($searchcourses[$course->id]);
        }
        $numcourses = count($searchcourses);
    }
}

// If no search results then get potential students for this course excluding users already in course
if (empty($searchcourses)) {
    $courses = get_courses('all', null, 'c.id, c.fullname, c.visible, c.shortname');
    foreach ($alreadycourses as $key=>$acourse) {
        unset($courses[$key]);
    }
    foreach ($courses as $c) {
        if ($c->id == SITEID or $c->id == $course->id or isset($existing[$c->id])) {
            unset($courses[$c->id]);
            continue;
        }
        $coursecontext = get_context_instance(CONTEXT_COURSE, $c->id);
        if (!$c->visible and !has_capability('moodle/course:viewhiddencourses', $coursecontext)) {
            unset($courses[$c->id]);
            continue;
        }
        if (!has_capability('enrol/meta:selectaslinked', $coursecontext)) {
            unset($courses[$c->id]);
            continue;
        }
    }
    $numcourses = sizeof($courses);
}



$PAGE->set_heading($course->fullname);
$PAGE->set_title(get_string('pluginname', 'enrol_meta'));


echo $OUTPUT->header();

echo $OUTPUT->box_start();

echo html_writer::start_tag('table', array('width' => '80%'));
echo html_writer::start_tag('tr');

// list of installed languages
$url = new moodle_url('/enrol/meta/addinstance.php', array('remove' => 1));
echo html_writer::start_tag('td', array('valign' => 'top', 'width' => '50%'));
echo html_writer::start_tag('form', array('id' => 'removestudentform', 'action' => $url->out(), 'method' => 'post'));
echo html_writer::start_tag('fieldset');
echo html_writer::label($stralreadycourses, 'menuuninstallcourse');
echo html_writer::empty_tag('br');
echo html_writer::select($alreadycourses, 'removeselect[]', '', false, array('size' => 20, 'multiple' => 'multiple'));
echo html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'sesskey', 'value' => sesskey()));
echo html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'id', 'value' => $id));
echo html_writer::empty_tag('br');
echo html_writer::empty_tag('br');
echo html_writer::empty_tag('input', array('type' => 'submit', 'value' => $strremovecourse));
echo html_writer::end_tag('fieldset');
echo html_writer::end_tag('form');
echo html_writer::end_tag('td');

echo html_writer::start_tag('td', array('valign' => 'top', 'width' => '50%'));
$url = new moodle_url('/enrol/meta/addinstance.php', array('add' => 1));
echo html_writer::start_tag('form', array('id' => 'addstudentform', 'action' => $url->out(), 'method' => 'post'));
echo html_writer::start_tag('fieldset');
if ($numcourses > MAX_COURSES_PER_PAGE) {
    echo html_writer::label($strtomanytoshow, 'menucourse');
    echo html_writer::empty_tag('br');
    echo html_writer::select(array(), 'nothing', '', false, array('size' => 20, 'multiple' => 'multiple'));
} elseif (! empty($searchcourses)) {
    echo html_writer::label($strpotentialcourses, 'menucourse');
    echo html_writer::select($searchcourses, 'addselect[]', '', false, array('size' => 20, 'multiple' => 'multiple'));
} elseif (! empty($courses)) {
    echo html_writer::label($strpotentialcourses, 'menucourse');
    echo html_writer::select($courses, 'addselect[]', '', false, array('size' => 20, 'multiple' => 'multiple'));
}
echo html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'sesskey', 'value' => sesskey()));
echo html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'id', 'value' => $id));
echo html_writer::empty_tag('br');
echo html_writer::empty_tag('br');
echo html_writer::empty_tag('input', array('type' => 'submit', 'name' => 'addcourses', 'value' => $straddcourses));
echo html_writer::empty_tag('br');
echo html_writer::empty_tag('br');
echo html_writer::empty_tag('input', array('type' => 'text', 'name' => 'searchtext', 'id' =>'searchtext', 'size' => '30'));
echo html_writer::empty_tag('input', array('type' => 'submit', 'name' => 'search', 'id' => 'search', 'value' => $strsearch));
echo html_writer::end_tag('fieldset');
echo html_writer::end_tag('form');
echo html_writer::end_tag('td');

echo html_writer::end_tag('tr');
echo html_writer::end_tag('table');
echo $OUTPUT->box_end();
echo $OUTPUT->footer();
