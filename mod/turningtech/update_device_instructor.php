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
 * Updates and deletes Device ID
 * @package    mod_turningtech
 * @copyright  2012 Turning Technologies
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once('../../config.php');
require_once($CFG->dirroot . '/mod/turningtech/lib.php');
global $DB;
$devicemapid = required_param('id', PARAM_INT);
$courseid    = optional_param('course', null, PARAM_INT);
$deviceid    = optional_param('deviceid', null, PARAM_ALPHANUM);
$action      = optional_param('action', null, PARAM_ALPHA);
$course      = null;
$devicemap   = null;
if ($action != 'register') {
    if (!$devicemap = TurningTechDeviceMap::fetch(array(
                    'userid' => $devicemapid,
                    'deleted'=>0,
                    'typeid'=>1
    ))) {
        print_error('couldnotfinddeviceid', 'turningtech', '', $devicemapid);
    }
}
// Has the form been confirmed?
// Figure out which course we're dealing with.
if ($action != 'register') {
    if (empty($courseid)) {
        if (!$devicemap->isAllCourses()) {
            $courseid = $devicemap->getField('courseid');
        } else {
            print_error('courseidincorrect', 'turningtech');
        }
    }
}
if (!$course = $DB->get_record('course', array(
                'id' => $courseid
))) {
    print_error('courseidincorrect', 'turningtech');
}
// Make sure user is enrolled.
require_course_login($course);
// Verify user has permission to delete this devicemap.
if ($action != 'register') {
    if ($USER->id != $devicemap->getField('userid')) {
        // Current user is not the owner of the devicemap.  So
        // Verify current user is a teacher.
        //  Check Version.
        if ($CFG->version >= '2013111800.00') {
            $context = context_course::instance($course->id);
        } else {
            $context = get_context_instance(CONTEXT_COURSE, $course->id);
        }
        if (!has_capability('mod/turningtech:manage', $context)) {
            error(get_string('notpermittedtoeditdevicemap', 'turningtech'));
        }
    }
}
if (TurningTechTurningHelper::isdeviceidvalid($deviceid)) {
    if ($action == 'register') {
        $deviceid = strtoupper($deviceid);
        try {
            $allparams              = new stdClass();
            $allparams->userid      = $devicemapid;
            $allparams->all_courses = 1;
            $allparams->typeid      = 1;
            $allparams->deviceid    = strtoupper($deviceid);
            $allparams->deleted     = 0;
            $allparams->courseid    = $course->id;
            $map                    = TurningTechDeviceMap::generate($allparams, false);
            if ($map->save()) {
                redirect($CFG->wwwroot . "/mod/turningtech/device_lookup.php?id=" . $course->id);
            } else {
                turningtech_set_message(get_string('errorsavingdeviceid', 'turningtech'), 'error');
            }
        } catch (Exception $e) {
            turningtech_set_message(get_string('couldnotauthenticate', 'turningtech', $CFG->turningtech_responseware_provider));
        }
    } else if ($action == 'update') {
        $devicemap->setdeviceid($deviceid);
		$devicemap->delete();
        $devicemap->unsetdevid();
        $devicemap->unsetdeleted();
        if ($devicemap->save()) {
                redirect($CFG->wwwroot . "/mod/turningtech/device_lookup.php?id=" . $course->id);
        } else {
            turningtech_set_message(get_string('errorsavingdeviceid', 'turningtech'), 'error');
        }
    }
} else {
    turningtech_set_message(get_string('deviceidcorrectform', 'turningtech'));
    redirect($CFG->wwwroot . "/mod/turningtech/device_lookup.php?id=" . $course->id);
}