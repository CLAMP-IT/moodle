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

require('../../config.php');
require_once("$CFG->dirroot/enrol/meta/manage_form.php");
require_once("$CFG->dirroot/enrol/meta/locallib.php");

$id = required_param('id', PARAM_INT); // course id

$course = $DB->get_record('course', array('id'=>$id), '*', MUST_EXIST);
$context = context_course::instance($course->id, MUST_EXIST);

$pageurl = new moodle_url('/enrol/meta/manage.php');
$pageurl->param('id', $course->id);

$PAGE->set_url($pageurl);
$PAGE->set_pagelayout('admin');

navigation_node::override_active_url(new moodle_url('/enrol/instances.php', array('id'=>$course->id)));

require_login($course);
require_capability('moodle/course:enrolconfig', $context);

if (optional_param('links_clearbutton', 0, PARAM_RAW) && confirm_sesskey()) {
    redirect($pageurl);
}
$enrol = enrol_get_plugin('meta');

if (!$enrol->get_newinstance_link($course->id)) {
    redirect(new moodle_url('/enrol/instances.php', array('id'=>$course->id, '')));
}

$mform = new enrol_meta_manage_form($pageurl->out(false), array('course'=>$course));
if ($mform->is_cancelled()) {
    redirect(new moodle_url('/enrol/instances.php', array('id'=>$course->id)));
} else if ($data = $mform->get_data()) {
    if (isset($data->removebutton) && !empty($data->remove)) {
        $params = array('courseid'=>$data->id);
        list($insql, $inparams) = $DB->get_in_or_equal($data->remove, SQL_PARAMS_NAMED);
        $params = $params + $inparams;
        $select = "enrol = 'meta' AND courseid = :courseid AND customint1 ".$insql;
        $instances = $DB->get_records_select('enrol', $select, $params);
        foreach ($instances as $instance) {
            $enrol->delete_instance($instance);
        }
    }
    if (isset($data->link_submitbutton) && !empty($data->link)) {
        foreach ($data->link as $link) {
            if (!empty($link)) { // because of formlib selectgroups
                $eid = $enrol->add_instance($course, array('customint1'=>$link));
            }
        }
        enrol_meta_sync($course->id);
    }
    redirect(new moodle_url('/enrol/meta/manage.php', array('id'=>$course->id)));
}
$PAGE->set_heading($course->fullname);
$PAGE->set_title(get_string('pluginname', 'enrol_meta'));
echo $OUTPUT->header();
$mform->display();
echo $OUTPUT->footer();
