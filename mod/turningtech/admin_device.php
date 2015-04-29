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
 * This page lists all the instances of turningtech in a particular course for admin
 * @package    mod_turningtech
 * @copyright  2012 Turning Technologies
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once('../../config.php');
require_once($CFG->dirroot . '/lib/adminlib.php');
require_once($CFG->dirroot . '/mod/turningtech/lib.php');
require_once($CFG->dirroot . '/mod/turningtech/lib/forms/turningtech_admin_device_form.php');
global $DB;
admin_externalpage_setup('editusers');
$devicemapid = optional_param('id', null, PARAM_INT);
$studentid   = optional_param('student', null, PARAM_INT);
$devicemap = null;
$student   = null;
// We need either an existing devicemap or a student.
if ($devicemapid) {
    if (!$devicemap = TurningTechDeviceMap::fetch(array(
                    'id' => $devicemapid
    ))) {
        print_error('couldnotfinddeviceid', 'turningtech', '', $devicemapid);
    }
    $studentid = $devicemap->getField('userid');
}
if ($studentid) {
    $student = $DB->get_record('user', array(
                    'id' => $studentid
    ));
}
if (empty($student)) {
    print_error('studentidincorrect', 'turningtech');
}
// Build action URL.
$url    = "admin_device.php";
$params = array();
if (!empty($devicemapid)) {
    $params[] = "id={$devicemapid}";
}
if (!empty($studentid)) {
    $params[] = "student={$studentid}";
}
if (count($params)) {
    $url .= '?' . implode('&', $params);
}
// Url of return page.
$redirect_url = "admin.php";
// Set up form.
$deviceform = new turningtech_admin_device_form($url, array(
                'studentid' => $studentid
));
if ($deviceform->is_cancelled()) {
    redirect($redirect_url);
} else if ($data = $deviceform->get_data()) {
    $map = TurningTechDeviceMap::generatefromform($data);
    if ($map->save()) {
        turningtech_set_message(get_string('deviceidsaved', 'turningtech'));
        redirect($redirect_url);
    } else {
        print_error('errorsavingdeviceid', 'turningtech', $redirect_url);
    }
}
// If showing the form, set up data.
$dto = new stdClass();
if (!empty($devicemap)) {
    $dto              = $devicemap->getData();
    $dto->devicemapid = $dto->id;
    unset($dto->id);
} else {
    $dto->userid       = $student->id;
    $dto->all_courses  = 1; // Device registered for All Courses.
    $dto->courseid     = 0; // Device registered for All Courses.
    $dto->typeid = 1; // Device registered as Response Card.
}
$deviceform->set_data($dto);
// Page output.
echo $OUTPUT->header();
$deviceform->display();
echo $OUTPUT->footer();