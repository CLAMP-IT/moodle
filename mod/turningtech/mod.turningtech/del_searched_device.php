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
 * This page deletes Device ID
 * @package    mod_turningtech
 * @copyright  2012 Turning Technologies
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once('../../config.php');
require_once($CFG->dirroot . '/mod/turningtech/lib.php');
global $DB;
$courseid    = optional_param('id', null, PARAM_INT);
$deviceid    = optional_param('deviceid', null, PARAM_ALPHANUM);
if (function_exists('optional_param_array')) {
    $selected    = optional_param_array('selected', array(), PARAM_INT);
} else {
    $a = optional_param('selected', null, PARAM_INT);
    $selected = clean_param($a, PARAM_INT);

}
$deleted     = optional_param('Delete', null, PARAM_ALPHA);
require_login();
if ($CFG->version >= '2013111800.00') {
    if (!has_capability('moodle/site:config', context_system::instance())) {
        print_error('notpermittedtoeditdevicemap', 'turningtech');
    }
} else {
    if (!has_capability('moodle/site:config', get_context_instance(CONTEXT_SYSTEM))) {
        print_error('notpermittedtoeditdevicemap', 'turningtech');
    }
}
if (isset($deleted)) { // Delete the device ID.
    foreach ($selected as $userid) {
        $devicemap = false;
        if (!$devicemap = TurningTechDeviceMap::fetch(array(
                        'userid' => $userid,
                        'deviceid' =>strtoupper($deviceid),
                        'deleted' => 0
        )
        )
        ) {
            print_error('couldnotfinddeviceid', 'turningtech', '', $deviceid);
        }
        $devicemap->purgeselectedMappings($userid, $deviceid);
    }
    // Commented code {turningtech_set_message(get_string('deviceiddeleted', 'turningtech'));}.
    redirect($CFG->wwwroot . "/mod/turningtech/search_device.php?id=".$courseid);
}
echo $OUTPUT->footer();