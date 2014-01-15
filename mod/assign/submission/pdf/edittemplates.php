<?php
// This file is part of the submit PDF plugin for Moodle - http://moodle.org/
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

require_once(dirname(__FILE__).'/../../../../config.php');
global $CFG, $DB, $PAGE;
require_once($CFG->dirroot.'/mod/assign/submission/pdf/edittemplates_class.php');

$courseid   = required_param('courseid', PARAM_INT);          // Course ID.
$templateid = optional_param('templateid', 0, PARAM_INT);
$itemid = optional_param('itemid', 0, PARAM_INT);
$imagetime = optional_param('imagetime', false, PARAM_INT); // Time when the preview image was uploaded.

$course = $DB->get_record('course', array('id'=>$courseid), '*', MUST_EXIST);

$url = new moodle_url('/mod/assign/submission/pdf/edittemplates.php', array('courseid' => $courseid));
if ($templateid) {
    $url->param('templateid', $templateid);
}
if ($itemid) {
    $url->param('itemid', $itemid);
}
if ($imagetime) {
    $url->param('imagetime', $imagetime);
}
$PAGE->set_url($url);

require_login($course->id, false);

$context = context_course::instance($course->id);
require_capability('moodle/course:manageactivities', $context);

$edittmpl = new edit_templates($course->id, $templateid, $imagetime, $itemid);
$edittmpl->view();
