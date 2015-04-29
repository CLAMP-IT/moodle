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
 * General-purpose library for use by TurningTech module/blocks.
 * @package    mod_turningtech
 * @copyright  2012 Turning Technologies
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author jacob
 *
 * NOTE: callers which include/require this file ***MUST*** also include/require the following:
 * - [moodle root]/config.php
 */
// To fix maximum grades allowed.
set_config('gradeoverhundredprocentmax', 1000);
// The path to the WSDL definitions.
global $DB;
define('TURNINGTECH_WSDL_URL', $CFG->wwwroot . '/mod/turningtech/wsdl/wsdl.php');
// The 2 types of device ID formats.
define('TURNINGTECH_DEVICE_ID_FORMAT_HEX', 1);
// TODO: is this the correct length?
define('TURNINGTECH_DEVICE_ID_FORMAT_HEX_MIN_LENGTH', 6);
define('TURNINGTECH_DEVICE_ID_FORMAT_HEX_MAX_LENGTH', 8);
define('TURNINGTECH_DEVICE_ID_FORMAT_ALPHA', 2);
// TODO: is this the correct length?
define('TURNINGTECH_DEVICE_ID_FORMAT_ALPHA_MIN_LENGTH', 6);
define('TURNINGTECH_DEVICE_ID_FORMAT_ALPHA_MAX_LENGTH', 8);
define('TURNINGTECH_ENABLE_RESPONSEWARE', 1);
define('TURNINGTECH_DISABLE_RESPONSEWARE', 2);
define('TURNINGTECH_CUSTOM_RESPONSEWARE', 3);
define('TURNINGTECH_ENCRYPTION_FORMAT_ECB', 1);
define('TURNINGTECH_ENCRYPTION_FORMAT_CBC', 2);
// The type of gradebook items to use.
define('TURNINGTECH_GRADE_ITEM_TYPE', 'mod');
define('TURNINGTECH_GRADE_ITEM_MODULE', 'turningtech');
// Different modes of saving scores.
define('TURNINGTECH_SAVE_NO_OVERRIDE', 1);
define('TURNINGTECH_SAVE_ONLY_OVERRIDE', 2);
define('TURNINGTECH_SAVE_ALLOW_OVERRIDE', 3);
// Default user roles.
$roles              = get_all_roles();
$manage_turningtech = get_roles_with_capability('mod/turningtech:manage', 1);
$manage_grades      = get_roles_with_capability('moodle/grade:manage', 1);
// Ensuring that "Manage TurningTech" is an array before it is searched as a collection.
if (!is_array($manage_turningtech)) {
    $manage_turningtech = array();
}
// Ensuring that "Manage Grades" is an array before it is searched as a collection.
if (!is_array($manage_grades)) {
    $manage_grades = array();
}
$sroles = '';
$troles = '';
foreach ($roles as $role) {
    $index = $role->id;
    if (array_key_exists($index, $manage_turningtech) || array_key_exists($index, $manage_grades)) {
        if ($troles) {
            $troles .= ',' . $index;
        } else {
            $troles .= $index;
        }
    } else {
        if ($sroles) {
            $sroles .= ',' . $index;
        } else {
            $sroles .= $index;
        }
    }
}
define('TURNINGTECH_DEFAULT_TEACHER_ROLE', $troles);
define('TURNINGTECH_DEFAULT_STUDENT_ROLE', $sroles);
// Switch for enabling/disabling WS encryption.
define('TURNINGTECH_ENABLE_ENCRYPTION', true);
// Switch for enabling/disabling WS decryption.
define('TURNINGTECH_ENABLE_DECRYPTION', true);
// Switch for enabling/disabling WS encryption EncryptionHelperException messages also being output via error_log().
define('TURNINGTECH_ENABLE_ENCRYPTION_EXCEPTIONS_IN_ERROR_LOG', true);
// Switch for enabling/disabling HttpPostHelperException and HttpPostHelperIOException messages also being output via error_log().
define('TURNINGTECH_ENABLE_POSTRW_EXCEPTIONS_IN_ERROR_LOG', true);
// Default responseware provider.
define('TURNINGTECH_DEFAULT_RESPONSEWARE_PROVIDER', 'http://www.rwpoll.com/');
// Require all necessary libraries.
require_once($CFG->dirroot . '/mod/turningtech/lib/IntegrationServiceProvider.php');
require_once($CFG->dirroot . '/mod/turningtech/lib/types/Escrow.php');
require_once($CFG->dirroot . '/mod/turningtech/lib/types/DeviceMap.php');
require_once($CFG->dirroot . '/mod/turningtech/lib/types/TurningSession.php');
require_once($CFG->dirroot . '/mod/turningtech/lib/types/SessionMap.php');
require_once($CFG->dirroot.'/lib/filelib.php');
/**
 * Library of functions and constants for module turningtech
 * This file should have two well differenced parts:
 *   - All the core Moodle functions, neeeded to allow
 *     the module to work integrated in Moodle.
 *   - All the turningtech specific functions, needed
 *     to implement all the module logic. Please, note
 *     that, if the module become complex and this lib
 *     grows a lot, it's HIGHLY recommended to move all
 *     these module specific functions to a new php file,
 *     called "locallib.php" (see forum, quiz...). This will
 *     help to save some memory when Moodle is performing
 *     actions across all modules.
 */

/**
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will create a new instance and return the id number
 * of the new instance.
 *
 * @param object $turningtech An object from the form in mod_form.php
 * @return int The id of the newly inserted turningtech record
 */
function turningtech_add_instance($turningtech) {
    global $DB;
    $turningtech->timecreated = time();
    // You may have to add extra stuff in here.
    return $DB->insert_record('turningtech', $turningtech);
}
/**
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will update an existing instance with new data.
 *
 * @param object $turningtech An object from the form in mod_form.php
 * @return boolean Success/Fail
 */
function turningtech_update_instance($turningtech) {
    global $DB;
    $turningtech->timemodified = time();
    $turningtech->id           = $turningtech->instance;
    // You may have to add extra stuff in here.
    return $DB->update_record('turningtech', $turningtech);
}
/**
 * Given an ID of an instance of this module,
 * this function will permanently delete the instance
 * and any data that depends on it.
 *
 * @param int $id Id of the module instance
 * @return boolean Success/Failure
 */
function turningtech_delete_instance($id) {
    global $DB;
    if (!$turningtech = $DB->get_record('turningtech', 'id', $id)) {
        return false;
    }
    $result = true;
    // Delete any dependent records here.
    if (!$DB->delete_records('turningtech', 'id', $turningtech->id)) {
        $result = false;
    }
    return $result;
}
/**
 * Return a small object with summary information about what a
 * user has done with a given particular instance of this module
 * Used for user activity reports.
 * $return->time = the time they did it
 * $return->info = a short text description
 * @param object $course The Course for user
 * @param object $user
 * @param object $mod
 * @param object $turningtech
 * @return bool
 * @todo Finish and implement this function
 */
function turningtech_user_outline($course, $user, $mod, $turningtech) {
    return false;
}

/**
 * Print a detailed representation of what a user has done with
 * a given particular instance of this module, for user activity reports.
 * @param object $course The Course for user
 * @param object $user
 * @param object $mod
 * @param object $turningtech
 * @return bool
 * @todo Finish documenting this function
 */
function turningtech_user_complete($course, $user, $mod, $turningtech) {
    return true;
}
/**
 * Given a course and a time, this module should find recent activity
 * that has occurred in turningtech activities and print it out.
 * Return true if there was output, or false is there was none.
 * @param object $course
 * @param bool $isteacher
 * @param time $timestart
 * @return boolean
 * @todo Finish documenting this function
 */
function turningtech_print_recent_activity($course, $isteacher, $timestart) {
    return false; //  True if anything was printed, otherwise false.
}
/**
 * Must return an array of user records (all data) who are participants
 * for a given instance of turningtech. Must include every user involved
 * in the instance, independient of his role (student, teacher, admin...)
 * See other modules as example.
 *
 * @param int $turningtechid ID of an instance of this module
 * @return mixed boolean/array of students
 */
function turningtech_get_participants($turningtechid) {
    return false;
}
/**
 * This function returns if a scale is being used by one turningtech
 * if it has support for grading and scales. Commented code should be
 * modified if necessary. See forum, glossary or journal modules
 * as reference.
 *
 * @param int $turningtechid ID of an instance of this module
 * @param int $scaleid
 * @return bool
 * @todo Finish documenting this function
 */
function turningtech_scale_used($turningtechid, $scaleid) {
    return false;
}
/**
 * Checks if scale is being used by any instance of turningtech.
 * This function was added in 1.9
 *
 * This is used to find out if scale used anywhere
 * @param int $scaleid
 * @return boolean true if the scale is used by any turningtech
 */
function turningtech_scale_used_anywhere($scaleid) {
    return false;
}
/**
 * Execute post-install custom actions for the module
 * This function was added in 1.9
 *
 * @return boolean true if success, false on error
 */
function turningtech_install() {
    return true;
}
/**
 * Execute post-uninstall custom actions for the module
 * This function was added in 1.9
 *
 * @return boolean true if success, false on error
 */
function turningtech_uninstall() {
    return true;
}
// Any other turningtech functions go here.  Each of them must have a name that
// starts with turningtech_
// Remember (see note in first lines) that, if this section grows, it's HIGHLY
// recommended to move all funcions below to a new "localib.php" file.

/**
 * generates device ID format options
 * @return unknown_type
 */
function turningtech_get_device_id_format_options() {
    return array(
        TURNINGTECH_DEVICE_ID_FORMAT_HEX => get_string('deviceidformathex', 'turningtech'),
        TURNINGTECH_DEVICE_ID_FORMAT_ALPHA => get_string('deviceidformatalpha', 'turningtech')
    );
}
/**
 * generates Responseware Display options
 * @return unknown_type
 */
function turningtech_get_device_display_options() {
    return array(
            TURNINGTECH_ENABLE_RESPONSEWARE => get_string('enableresponseware', 'turningtech'),
            TURNINGTECH_DISABLE_RESPONSEWARE => get_string('disableresponseware', 'turningtech')
    );
}
/**
 * generates cipher mode format options
 * @return unknown_type
 */
function turningtech_get_encryption_format_options() {
    return array(
        TURNINGTECH_ENCRYPTION_FORMAT_ECB => get_string('encryptionformatecb', 'turningtech'),
        TURNINGTECH_ENCRYPTION_FORMAT_CBC => get_string('encryptionformatcbc', 'turningtech')
    );
}
/**
 * outputs a table of students and their device IDs
 * @param object $course
 * @return unknown_type
 */
function turningtech_list_course_devices($course) {
    global $CFG;
    $table                      = new html_table();
    $table->attributes['class'] = 'general_table boxaligncenter device_table';
    $table->width               = '80%';
    $table->cellpadding         = 5;
    $table->cellspacing         = 1;
    $sort                       = optional_param('sort', 'name', PARAM_ALPHA);
    $asc                        = optional_param('asc', true, PARAM_BOOL);
    $order                      = false;
    switch ($sort) {
        case 'uid':
            $order = 'u.username';
            break;
        case 'device':
            $order = 'd.deviceid';
            break;
        default:
            $order = 'u.lastname';
            break;
    }
    $href  = "index.php?id={$course->id}&sort=name";
    $class = '';
    if ($sort == 'name' && $asc) {
        $href .= "&asc=0";
        $class = 'asc';
    } else if ($sort == 'name') {
        $class = 'desc';
    }
    $student_col = "<a href='{$href}' class='{$class}'>" . get_string('student', 'grades') . "</a>\n";
    $href        = "index.php?id={$course->id}&sort=device";
    $class       = '';
    if ($sort == 'device' && $asc) {
        $href .= "&asc=0";
        $class = 'asc';
    } else if ($sort == 'device') {
        $class = 'desc';
    }
    $device_col = "<a href='{$href}' class='{$class}'>" . get_string('deviceid', 'turningtech') . "</a>\n";
    $href       = "index.php?id={$course->id}&sort=uid";
    $class      = '';
    if ($sort == 'uid' && $asc) {
        $href .= "&asc=0";
        $class = 'asc';
    } else if ($sort == 'uid') {
        $class = 'desc';
    }
    $id_col       = "<a href='{$href}' class='{$class}'>User ID</a>\n";
    $table->head  = array(
        $student_col,
        $id_col,
        $device_col
    );
    $table->align = array(
        'center',
        'center',
        'center'
    );
    $roster       = TurningTechMoodleHelper::getclassroster($course, false, false, $order, $asc);
    if (!empty($roster)) {
        foreach ($roster as $student) {
            $studentcell = '';
            $idcell      = '';
            $devicecell  = '';
            $studentcell = "<a href='{$CFG->wwwroot}/user/view.php?id={$student->id}&course={$course->id}'>
            {$student->firstname} {$student->lastname}</a>\n";
            $idcell      = "<a href='{$CFG->wwwroot}/user/view.php?id={$student->id}&course={$course->id}'>
            {$student->username}</a>\n";
            if (empty($student->deviceid)) {
                $devicecell = "<a href='edit_device.php?course={$course->id}&student={$student->id}'>";
                $devicecell .= get_string('nodevicesregistered', 'turningtech') . "</a>\n";
            } else {
                $device     = TurningTechDeviceMap::fetch(array(
                    'id' => $student->devicemapid
                ));
                $devicecell = $device->displayLink();
            }
            $table->data[] = array(
                $studentcell,
                $idcell,
                $devicecell
            );
        }
        echo html_writer::table($table);
    } else {
        echo "<p class='empty-roster'>" . get_string('nostudentsfound', 'turningtech') . "</p>\n";
    }
}
/**
 * outputs a table of students and their device IDs based upon search
 * @param string $devicesearch
 * @param object $course
 * @return unknown_type
 * Device Search Display Start
 */
function turningtech_list_search_devices($devicesearch, $course) {
    global $CFG;
    $admid = optional_param('admid', '2', PARAM_ALPHA);
    $table                      = new html_table();
    $table->attributes['class'] = 'general_table boxaligncenter device_table';
    $table->id                  = 'searchdev_id';
    $table->width               = '80%';
    $table->cellpadding         = 5;
    $table->cellspacing         = 1;
    $sort                       = optional_param('sort', 'fname', PARAM_ALPHA);
    $asc                        = optional_param('asc', true, PARAM_BOOL);
    $order                      = false;
    switch ($sort) {
        case 'uid':
            $order = 'u.username';
            break;
        case 'email':
            $order = 'u.email';
            break;
        default:
            $order = 'u.lastname';
            break;
    }
    $href  = "search_device.php?id={$course->id}&sort=name";
    $class = '';
    if ($sort == 'fname' && $asc) {
        $href .= "&asc=0";
        $class = 'asc';
    } else if ($sort == 'fname') {
        $class = 'desc';
    }
    $select_col  = "<input name ='checkall' type='checkbox' onclick=SetAllCheckBoxes('searchdev_id',this.checked); > Select";
    $student_col = get_string('student', 'turningtech') . "\n";
    $href        = "search_device.php?id={$course->id}&sort=email";
    $class       = '';
    if ($sort == 'email' && $asc) {
        $href .= "&asc=0";
        $class = 'asc';
    } else if ($sort == 'email') {
        $class = 'desc';
    }
    $email_col = get_string('email', 'turningtech') . "\n";
    $href      = "search_device.php?id={$course->id}&sort=uid";
    $class     = '';
    if ($sort == 'uid' && $asc) {
        $href .= "&asc=0";
        $class = 'asc';
    } else if ($sort == 'uid') {
        $class = 'desc';
    }
    $id_col       = "User ID";
    $table->head  = array(
        $select_col,
        $student_col,
        $id_col,
        $email_col
    );
    $table->align = array(
        'left',
        'left',
        'left',
        'left'
    );
    $roster       = TurningTechMoodleHelper::getsearchresult($devicesearch, $course);
    if (!empty($roster)) {
        foreach ($roster as $student) {
            $select_cell   = '';
            $studentcell   = '';
            $idcell        = '';
            $emailcell     = '';
            $select_cell   = "<input type='checkbox' name='selected[]' value={$student->id}>
            <input type='hidden' name='selecteditems' value=' '>";
            $studentcell   = "{$student->firstname} {$student->lastname}\n";
            $idcell        = "{$student->username}\n";
            $emailcell     = "{$student->email}\n";
            $table->data[] = array(
                $select_cell,
                $studentcell,
                $idcell,
                $emailcell
            );
        }
        return $table;
    }
}
// Device Search Display End.

/**
 * display a list of a user's device IDs for the given course
 * @param object $user
 * @param object $course
 * @return unknown_type
 */
function turningtech_list_user_devices($user, $course) {
    $output                     = '';
    $pluginconfig = get_config('moodle', 'turningtech_device_selection');
    // Show list of registered device IDs.
    $table                      = new html_table();
    $table->head                = array(
        get_string('deviceid', 'turningtech'),
        '',
        '',
        get_string('devicetype', 'turningtech')
    );
    $table->align               = array(
        'center',
        'center',
        'center',
        'center'
    );
    $table->attributes['class'] = 'general_table boxaligncenter user_device_table';
    $table->width               = '100%';
    $table->cellpadding         = 5;
    $table->cellspacing         = 1;
    $str_all_courses            = get_string('allcourses', 'turningtech');
    $str_this_course            = get_string('justthiscourse', 'turningtech');
    // Get all the appropriate data.
    $devices                    = TurningTechDeviceMap::getalldevices($user, $course);
    $currentdevice              = false;
    $devicetypespan             = null;
    $rcpresent                  = false;
    $rwpresent                  = false;
    if (count($devices)) {
        foreach ($devices as $device) {
            if ($device->getDeviceType() == 'Response Card') {
                $rcpresent  = true;
                $devicename = get_string('rctype', 'turningtech');
            }
            if ($device->getDeviceType() == 'Response Ware') {
                $rwpresent  = true;
                $devicename = get_string('rwtype', 'turningtech');
            }
            $currentdevice = $device;
            if ($device->getDeviceType() == 'Response Ware') {
                $devicetypespan = "<span id='ttresponseware'></span>";
            } else if ($device->getDeviceType() == 'Response Card') {
                $devicetypespan = "<span id='ttdevice'></span>";
            }
            if ($device->getDeviceType() == 'Response Card') {
                $displayeditstring   = "<div class='cancelDeleteDiv' id='divEditRC_1' >
                    <a id='lnkEditRC_1' href='#' onclick='EditResponseCard(this)' style='color:blue;'>Edit</a></div>
                    <div id='divUpdateCancelRC_1' class='displayNone' style='margin-left: 10px;'>
                    <a id='lnkUpdateRC_1' href='update_device.php?id=" . $device->getDevId() . "&course=" . $course->id . "'
                 onclick='return UpdateResponseCard(this);' style='color:blue;'>Update</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                 <a id='lnkCancelRC_1' href='#' onclick='CancelResponseCard(this)' style='color:blue;'>
                 Cancel</a></div>";
                $displaydeletestring = "<div id='divDeleteRC_1' class='cancelDeleteDiv'>
                    <a id='lnkDeleteRC_1' href= 'update_device.php?id=" . $device->getDevId() . "&course=" . $course->id . "
                    &action=delete'
                 onclick='DeleteResponseCard(this); return false;' style='color:blue;'>Delete</a></div>";
            } else {
                $displayeditstring   = "";
                $displaydeletestring = "<div id='divDeleteRW_1' class='cancelDeleteDiv'>
                    <a id='lnkDeleteRW_1' href='update_device.php?id=".$device->getDevId()."&course=".$course->id."&action=delete'
                    onclick='DeleteResponseWare(this); return false;' style='color:blue;'>Delete</a></div>";
            }
            if (!($device->getDeviceType() == 'Response Ware' && $pluginconfig ==TURNINGTECH_DISABLE_RESPONSEWARE)) {
                $table->data[] = array(
                $device->displayLink(),
                $displayeditstring,
                $displaydeletestring,
                $devicetypespan . $devicename
                );
            }
        }
    }
    if (!$rcpresent) {
        $table->data[] = array(
            "<div class='displayInline' id='divResponseCard_1'>
<a onclick=javascript:unhide('divResponseCard'); href='#divResponseCard'
style='color:#0000FF;'>" . get_string('regrctype', 'turningtech') . "</a></div>",
            "<div class='cancelDeleteDiv' id='divEditRC_1'></div>",
            "<div id='divDeleteRC_1' class='cancelDeleteDiv'></div>",
            get_string('rctype', 'turningtech')
        );
    }
    if (!$rwpresent && $pluginconfig !=TURNINGTECH_DISABLE_RESPONSEWARE) {
        $table->data[] = array(
            "<div class='displayInline' id='divResponseWare_1'>
<a onclick=javascript:unhide('divResponseWare'); href='#divResponseWare'
style='color:#0000FF;'>" . get_string('regrwtype', 'turningtech') . "</a></div>",
            "",
            "<div id='divDeleteRC_1' class='cancelDeleteDiv'></div>",
            get_string('rwtype', 'turningtech')
        );
    }
    $output .= html_writer::table($table);
    return $output;
}

/**
 * return an array of link data
 * @param object $user
 * @param object $course
 * @param string $action
 * @return unknown_type
 */
function turningtech_get_instructor_lookup($user, $course, $action = 'deviceid') {
    global $CFG;
    $links = array(
        'email' => array(
            'text' => get_string('sendemail', 'turningtech'),
            'href' => "{$CFG->wwwroot}/mod/turningtech/sendmail.php?id={$course->id}"
        ),
        /*'sessionfile' => array(
            'text' => get_string('importsessionfile', 'turningtech'),
            'href' => "{$CFG->wwwroot}/mod/turningtech/device_lookup.php?id={$course->id}&action=sessionfile"
        ),*/
        'export' => array(
            'text' => get_string('downloaddata', 'turningtech'),
            'href' => "{$CFG->wwwroot}/mod/turningtech/export_roster.php?id={$course->id}"
        )
    );
    return $links;
}
/**
 * create an unordered list of links
 * @param string $links
 * @param object $course
 * @param int $id
 * @return unknown_type
 */
function turningtech_button($links, $course, $id = '') {
    global $CFG;
    $count  = count($links);
    $i      = 1;
    $output = "<table width='80%' align='center'><tr>
                    <td style='float:left;'><ul " . (!empty($id) ? "id={$id}" : '') . " style = 'margin-left:0px;'>\n";
    foreach ($links as $id => $link) {
        if (!isset($link['classes'])) {
            $link['classes'] = array();
        }
        if ($i == 1) {
            $link['classes'][] = "first";
        }
        if ($i == $count) {
            $link['classes'][] = "last";
        }
        $class = '';
        if (count($link['classes'])) {
            $class = "class='" . implode(' ', $link['classes']) . "'";
        }
        $output .= "<li {$class}>\n";
        $output .= "<input onclick= 'window.location = \"" . $link['href'] . "\"' value = '{$link['text']}' type='button' />\n";
        $output .= "</li>\n";
        $i++;
    }
    $output .= "</ul></td>\n";
    $optional =  optional_param('action', null, PARAM_ALPHA);
    if ($optional == 'sessionfile') {
        $output .= "<td style='vertical-align: middle; text-align:right;'>
    <a href='{$CFG->wwwroot}/mod/turningtech/device_lookup.php?id={$course->id}' style='margin-right: 1em;'>Back to List</a>
    </td><td style='vertical-align: middle; text-align:right;'>
    <a href='{$CFG->wwwroot}/course/view.php?id={$course->id}' style='margin-right: 1em;'>Back to Course</a>
    </td></tr></table>";
    } else {
        $output .= "<td style='vertical-align: middle; text-align:right;'>
    <a href='{$CFG->wwwroot}/course/view.php?id={$course->id}' style='margin-right: 1em;'>Back to Course</a>
    </td></tr></table>";
    }
    return $output;
}
/**
 * list instructor actions
 * @param object $user
 * @param object $course
 * @param string $action
 * @return unknown_type
 */
function turningtech_list_instructor_lookup($user, $course, $action = 'deviceid') {
    global $CFG;
    $actions = turningtech_get_instructor_lookup($user, $course, $action);
    $output  = turningtech_button($actions, $course, 'turningtech-actions');
    echo $output;
}
// Instructor Device listing.

/**
 * outputs a table of students and their device IDs
 * @param object $course
 * @return unknown_type
 */
function turningtech_list_course_devices_instructor($course) {
    global $CFG;
    $pluginconfig = get_config('moodle', 'turningtech_device_selection');
    $admid = optional_param('admid', '2', PARAM_ALPHA);
    $table                      = new html_table();
    $table->attributes['class'] = 'general_table boxaligncenter c3style';
    $table->width               = '80%';
    $table->cellpadding         = 5;
    $table->cellspacing         = 1;
    $sort                       = optional_param('sort', 'fname', PARAM_ALPHA);
    $asc                        = optional_param('asc', true, PARAM_BOOL);
    $order                      = false;
    switch ($sort) {
        case 'uid':
            $order = 'u.username';
            break;
        case 'rcdevice':
        case 'rwdevice':
            $order = 'd.deviceid';
            break;
        case 'fname':
            $order = 'u.firstname';
            break;
        case 'lname':
            $order = 'u.lastname';
            break;
        default:
            $order = 'u.firstname';
            break;
    }
    $href  = "device_lookup.php?id={$course->id}&sort=fname";
    $class = '';
    if ($sort == 'fname' && $asc) {
        $href .= "&asc=0";
        $class = 'asc';
    } else if ($sort == 'fname') {
        $class = 'desc';
    }
    $studentf_col = "<a href='{$href}'>First Name</a>";
    $href  = "device_lookup.php?id={$course->id}&sort=lname";
    $class = '';
    if ($sort == 'lname' && $asc) {
        $href .= "&asc=0";
        $class = 'asc';
    } else if ($sort == 'lname') {
        $class = 'desc';
    }
    $studentl_col = "<a href='{$href}'>Last Name</a>";
    $href  = "device_lookup.php?id={$course->id}&sort=rcdevice";
    $class = '';
    if ($sort == 'rcdevice' && $asc) {
        $href .= "&asc=0";
        $class = 'asc';
    } else if ($sort == 'rcdevice') {
        $class = 'desc';
    }
    $device_col  = "<a href='{$href}'>ResponseCard DeviceID</a>";
    $href  = "device_lookup.php?id={$course->id}&sort=rwdevice";
    $class = '';
    if ($sort == 'rwdevice' && $asc) {
        $href .= "&asc=0";
        $class = 'asc';
    } else if ($sort == 'rwdevice') {
           $class = 'desc';
    }
    $devicer_col = "<a href='{$href}'>ResponseWare DeviceID</a>";
    $href        = "device_lookup.php?id={$course->id}&sort=uid";
    $class       = '';
    if ($sort == 'uid' && $asc) {
        $href .= "&asc=0";
        $class = 'asc';
    } else if ($sort == 'uid') {
        $class = 'desc';
    }
    $id_col       = "<a href='{$href}'>User ID</a>";
    if ($pluginconfig !=TURNINGTECH_DISABLE_RESPONSEWARE) {
        $table->head  = array(
        $studentf_col,
        $studentl_col,
        $id_col,
        $device_col,
        $devicer_col
        );
    } else {
        $table->head  = array(
            $studentf_col,
            $studentl_col,
            $id_col,
            $device_col
        );
    }
    $table->align = array(
        'left',
        'left',
        'left',
        'center',
        'center'
    );
    $roster       = TurningTechMoodleHelper::getclassrosterxml($course, false, false, $order, $asc);
    if (!empty($roster)) {
        foreach ($roster as $student) {
            $studentfcell = '';
            $studentlcell = '';
            $idcell       = '';
            $devicecell   = '';
            $devicecell2  = '';
            $studentfcell = "{$student->firstname}\n";
            $studentlcell = "{$student->lastname}\n";
            $idcell       = "{$student->username}\n";
            if (empty($student->rcard) && empty($student->rware)) {
                $devicecell = "<div class='cancelDeleteDiv' id='divEditRC_{$student->id}' >
    <a id='lnkEditRC_{$student->id}' href='#' onclick='EditResponseCardr(this)'>
    <div class='displayInline' id='divResponseCard_{$student->id}' >Register ResponseCard ID</div>
    </a>
    </div>
    <div class='displayNone' id='divTextResponseCard_{$student->id}' >
    <input type='text' id='txtResponseCard_{$student->id}'  value='{$student->rcard}'
    onkeypress ='return validateDeviceInputupdate(event, this);'/>
    </div>
    <div id='divUpdateCancelRC_{$student->id}' class='displayNone' style='margin-left: 10px;'>
    <input id='lnkUpdateRC_{$student->id}' type='button'
    name = 'update_device_instructor.php?id={$student->id}&course=" . $course->id . "&action=register' value='Register'
    onclick='return UpdateRCinput(this);'/>
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    <input id='lnkCancelRC_{$student->id}' type='button'  class='button' onclick='CancelResponseCard(this)'  value='Cancel'/>
    </div>";
            } else if (empty($student->rcard) && !empty($student->rware)) {
                $devicecell  = "<div class='cancelDeleteDiv' id='divEditRC_{$student->id}' >
    <a id='lnkEditRC_{$student->id}' href='#' onclick='EditResponseCardr(this)'>
    <div class='displayInline' id='divResponseCard_{$student->id}' >Register ResponseCard ID</div>
    </a>
    </div>
    <div class='displayNone' id='divTextResponseCard_{$student->id}' >
    <input type='text' id='txtResponseCard_{$student->id}'  value='{$student->rcard}'
    onkeypress ='return validateDeviceInputupdate(event, this);'/>
    </div>
    <div id='divUpdateCancelRC_{$student->id}' class='displayNone' style='margin-left: 10px;'>
    <input id='lnkUpdateRC_{$student->id}' type='button'
    name ='update_device_instructor.php?id={$student->id}&course=" . $course->id . "&action=register'
    onclick='return UpdateRCinput(this);' value='Register'/>
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    <input type='button' id='lnkCancelRC_{$student->id}' onclick='CancelResponseCard(this)' value='Cancel'/>
    </div>";
                $devicecell2 = "<div class='displayInline' id='divResponseWare_0' >{$student->rware}</div>
    <div class='displayNone' id='divTextResponseWare_0' ><input type='text' id='txtResponseWare_0' value='{$student->rware}'/>
                </div>";
            } else {
                $device1 = TurningTechDeviceMap::fetch(array(
                    'typeid' => 1,
                    'userid' => $student->id,
                    'deleted' => 0
                ));
                $device2 = TurningTechDeviceMap::fetch(array(
                    'typeid' => 2,
                    'userid' => $student->id,
                    'deleted' => 0
                ));
                if ($student->rcard) {
                    $devicecell = $device1->displayLinkInstructor($student->id);
                }
                if ($student->rware) {
                    $devicecell2 = $device2->displayLinkInstructor($student->id);
                }
            }
            if ($pluginconfig !=TURNINGTECH_DISABLE_RESPONSEWARE) {
                    $table->data[] = array(
                    $studentfcell,
                    $studentlcell,
                    $idcell,
                    $devicecell,
                    $devicecell2 );
            } else {
                $table->data[] = array(
                $studentfcell,
                $studentlcell,
                $idcell,
                $devicecell
                );
            }
        }
        echo html_writer::table($table);
    } else {
        echo "<p class='empty-roster'>" . get_string('nostudentsfound', 'turningtech') . "</p>\n";
    }
}
/**
 * return an array of link data
 * @param object $user
 * @param object $course
 * @param string $action
 * @return unknown_type
 */
function turningtech_get_instructor_actions($user, $course, $action = 'deviceid') {
    global $CFG;
    $links                     = array(
        'deviceid' => array(
            'text' => get_string('deviceids', 'turningtech'),
            'href' => "{$CFG->wwwroot}/mod/turningtech/index.php?id={$course->id}&action=deviceid"
        ),
        'sessionfile' => array(
            'text' => get_string('importsessionfile', 'turningtech'),
            'href' => "{$CFG->wwwroot}/mod/turningtech/index.php?id={$course->id}&action=sessionfile"
        ),
        'export' => array(
            'text' => get_string('exportparticipantlist', 'turningtech'),
            'href' => "{$CFG->wwwroot}/mod/turningtech/export_roster.php?id={$course->id}"
        ),
        'purge' => array(
            'text' => get_string('purgedeviceids', 'turningtech'),
            'href' => "{$CFG->wwwroot}/mod/turningtech/index.php?id={$course->id}&action=purge"
        )
    );
    $links[$action]['classes'] = array(
        'active'
    );
    return $links;
}
/**
 * create an unordered list of links
 * @param string $links
 * @param int $id
 * @return unknown_type
 */
function turningtech_ul($links, $id = '') {
    $count  = count($links);
    $i      = 1;
    $output = "<ul " . (!empty($id) ? "id={$id}" : '') . ">\n";
    foreach ($links as $id => $link) {
        if (!isset($link['classes'])) {
            $link['classes'] = array();
        }
        if ($i == 1) {
            $link['classes'][] = "first";
        }
        if ($i == $count) {
            $link['classes'][] = "last";
        }
        $class = '';
        if (count($link['classes'])) {
            $class = "class='" . implode(' ', $link['classes']) . "'";
        }
        $output .= "<li {$class}>\n";
        $output .= "<a href='{$link['href']}'>{$link['text']}</a>\n";
        $output .= "</li>\n";
        $i++;
    }
    $output .= "</ul>\n";
    return $output;
}
/**
 * list instructor actions
 * @param object $user
 * @param object $course
 * @param string $action
 * @return unknown_type
 */
function turningtech_list_instructor_actions($user, $course, $action = 'deviceid') {
    global $CFG;
    $actions = turningtech_get_instructor_actions($user, $course, $action);
    $output  = turningtech_ul($actions, 'turningtech-actions');
    echo $output;
}
/**
 * figure out the correct name for the exported filename
 * @param object $course
 * @return unknown_type
 */
function turningtech_generate_export_filename($course) {
    $course    = $course->shortname;
    $date      = date('m-d-Y');
    $time      = date('H-i-A');
    $extension = 'tplx';
    return "{$course}_{$date}_{$time}.{$extension}";
}
/**
 * Create a unique temporary directory with a given prefix name,
 * inside a given directory, with given permissions. Return the
 * full path to the newly created temp directory.
 *
 * @param string $dir where to create the temp directory.
 * @param string $prefix prefix for the temp directory name (default '')
 *
 * @return string The full path to the temp directory.
 */
function my_mktempdir($dir, $prefix='') {
    global $CFG;

    if (substr($dir, -1) != '/') {
        $dir .= '/';
    }

    do {
        $path = $dir.$prefix.mt_rand(0, 9999999);
    } while (file_exists($path));

    check_dir_exists($path);

    return $path;
}
/**
 * displays form for importing session file
 * @param object $course
 * @return unknown_type
 */
function turningtech_import_session_file($course) {
    global $CFG;
    require_once($CFG->dirroot . '/mod/turningtech/lib/forms/turningtech_import_session_form.php');
    $filedata = array();
    $default_url = "device_lookup.php?id=" . $course->id . "&action=sessionfile";
    $importform = new turningtech_import_session_form($default_url);
    $newformdata = array(
        'id' => $course->id,
        'action' => 'sessionfile'
    );
    $importform->set_data($newformdata);
    if ($importform->is_cancelled()) {
        // Cancel form.
        redirect($default_url);
    }
    if (empty($entry->id)) {
        $entry = new stdClass;
        $entry->id = null;
    }
    $draftitemid = file_get_submitted_draft_itemid('sessionfile');
    $context = get_context_instance(CONTEXT_COURSE, $course->id);
    $entry->id = $draftitemid;
    if ($data = $importform->get_data()) {
        // Store or update $entry.
        file_save_draft_area_files($data->sessionfile, $context->id, 'mod_turningtech', 'sessionfile', $entry->id, array(
            'maxfiles' => 10
        ));
    }
    $fs = get_file_storage();
    if ($sessionfiles = $fs->get_area_files($context->id, 'mod_turningtech', 'sessionfile')) {
        foreach ($sessionfiles as $file) {
            if ($file->get_filename() != '.') {
                $file->get_filepath();
                if ($sessionfile = $file->copy_content_to_temp()) {
                    $zipdir = my_mktempdir($CFG->tempdir . '/', 'session');
                    $fp = get_file_packer('application/zip');
                    $unzipresult = $fp->extract_to_pathname($sessionfile, $zipdir);
                    if (!$unzipresult) {
                        echo "Could Not Unzip files";
                        @remove_dir($zipdir);
                    } else {
                        // We don't need the zip file any longer, so delete it to make
                        // it easier to process the rest of the files inside the directory.
                        if (file_exists($zipdir.'/TTSession.xml')) {
                            $filedata[] = file_get_contents($zipdir . "/TTSession.xml");
                            $filename[] = $file->get_filename();
                            @unlink($sessionfile);
                        } else {
                            echo "File not found";
                        }
                        @remove_dir($zipdir);
                    }
                }
            }
        }
    }
    $fs->delete_area_files($context->id, 'mod_turningtech', 'sessionfile');
    @unlink($sessionfile);
    @remove_dir($zipdir);
    if ($data) {
        // Process data.
        if ($filedata) {
            $session = new TurningSession();
            $session->setactivecourse($course);
            for ($i=0; $i<count($filedata); $i++) {
                try {
                    $session->importsession($filedata[$i], $filename[$i], isset($data->override));
                } catch (Exception $e) {
                    echo 'Caught exception: ',  $e->getMessage(), $e->getFile(), "\n";
                    print_error('couldnotparsesessionfile', 'turningtech', $default_url);
                }
            }
        } else {
            print_error('errorsavingsessionfile', 'turningtech', $default_url);
        }
    }
    echo turningtech_show_messages();
    // Display form.
    $importform->display();
}
/**
 * purges all device maps for the given course
 * @param object $course
 * @return unknown_type
 */
function turningtech_import_purge_course_devices($course) {
    global $CFG;
    require_once($CFG->dirroot . '/mod/turningtech/lib/forms/turningtech_purge_course_form.php');
    $default_url = "index.php?id={$course->id}&action=purge";
    $mform       = new turningtech_purge_course_form($default_url);
    $data = $mform->get_data();
    if ($data) {
        $purged = TurningTechDeviceMap::purgecourse($course);
        if ($purged === false || $purged === 0) {
            // Set error message.
            turningtech_set_message(get_string('couldnotpurge', 'turningtech'), 'error');
        } else {
            turningtech_set_message(get_string('alldevicesincoursepurged', 'turningtech'));
        }
    } else {
        // Show warning messages.
        turningtech_set_message(get_string('purgecoursewarning', 'turningtech'));
        turningtech_set_message(get_string('purgecoursedescription', 'turningtech'));
    }
    echo turningtech_show_messages();
    $mform->display();
}
/**
 * fetches all messages and optionally clears them
 * @param bool $clear
 * @return unknown_type
 */
function turningtech_get_messages($clear = false) {
    $messages = turningtech_set_message();
    if ($clear) {
        unset($_SESSION['turning_messages']);
    }
    return $messages;
}
/**
 * add a message that will be displayed
 * @param string $message
 * @param string $type
 * @return unknown_type
 */
function turningtech_set_message($message = '', $type = 'message') {
    if ($message) {
        if (!isset($_SESSION['turning_messages'])) {
            $_SESSION['turning_messages'] = array();
        }
        if (!isset($_SESSION['turning_messages'][$type])) {
            $_SESSION['turning_messages'][$type] = array();
        }
        $_SESSION['turning_messages'][$type][] = $message;
    }
    return isset($_SESSION['turning_messages']) ? $_SESSION['turning_messages'] : array();
}
/**
 * display all status messages
 * @return unknown_type
 */
function turningtech_show_messages() {
    $output   = '';
    $messages = turningtech_get_messages(true);
    foreach ($messages as $type => $message) {
        $output .= "<div class='messages {$type}'>";
        $output .= "<ul class='{$type}'>\n";
        foreach ($message as $item) {
            $output .= "<li>{$item}</li>\n";
        }
        $output .= "</ul>\n";
        $output .= "</div>\n";
    }
    return $output;
}
/**
 * Creates a directory file name, suitable for make_upload_directory()
 * @param object $course
 * @return unknown_type
 */
function turningtech_file_dir($course) {
    global $CFG;
    return "{$course->id}/{$CFG->moddata}/turningtech";
}
/**
 * conducts search and builds table of results
 * @param object $data
 * @return unknown_type
 */
function turningtech_admin_search_results($data) {
    global $CFG;
    $users = TurningTechMoodleHelper::adminstudentsearch($data->searchstring);
    if (!empty($users) && count($users)) {
        $table                      = new html_table();
        $table->head                = array(
            get_string('student', 'grades'),
            get_string('deviceid', 'turningtech')
        );
        $table->align               = array(
            'center',
            'center'
        );
        $table->attributes['class'] = 'general_table boxaligncenter device_table';
        $table->width               = '80%';
        $table->cellpadding         = 5;
        $table->cellspacing         = 1;
        foreach ($users as $user) {
            if (empty($user->deviceid)) {
                $devicecell = "
                    <a href='admin_device.php?student={$user->id}'>" . get_string('nodevicesregistered', 'turningtech') . "
                        </a>\n";
            } else {
                $device     = TurningTechDeviceMap::fetch(array(
                    'id' => $user->devicemapid
                ));
                $devicecell = $device->displayLink(true);
            }
            $usercell      = "<a href='{$CFG->wwwroot}/user/view.php?id={$user->id}&course=1'>{$user->firstname} {$user->lastname}
                </a>";
            $table->data[] = array(
                $usercell,
                $devicecell
            );
        }
        return $table;
    }
    return false;
}
/** 
 * Send reminder email
 * @param object $course Course
 * @param bool $cron Boolean Value indicating if to use cron or not.
 * @return str|bool
 * 
 */
function turningtech_mail_reminder($course, $cron = true) {
    global $CFG;
    $total = 0;
    $users = TurningTechTurningHelper::getstudentswithoutdevices($course);
    $total += count($users);
    if (empty($users) || !empty($CFG->noemailever)) {
        return 0;
    }
    $mailer = get_mailer();
    if ($CFG->version >= '2013111800.00') {
        $supportuser      = core_user::get_support_user();
    } else {
        $supportuser      = generate_email_supportuser();
    }
    $mailer->Sender   = $supportuser->email;
    $mailer->From     = $CFG->noreplyaddress;
    $mailer->FromName = fullname($supportuser);
    $mailer->Subject  = "Register your Turning Technologies ResponseCard or ResponseWare ID.";
    $mailer->IsHTML(true);
    $mailer->Body     = turningtech_getreminderemailbody($course);
    $mailer->Encoding = 'quoted-printable';
    $count            = 0;
    foreach ($users as $id => $user) {
        // Ensure user actually can be emailed.
        if (empty($user->email) || (isset($user->auth) && $user->auth == 'nologin') || over_bounce_threshold($user)) {
            continue;
        }
        $mailer->AddBCC($user->email, "{$user->firstname} {$user->lastname}");
        $count++;
    }
    if ($count > 0) {
        if (!$mailer->Send()) {
            if ($cron) {
                mtrace('--ERROR-- failed to send reminder email: ' . $mailer->ErrorInfo);
            }
            return false;
        }
    }
    return $total;
}
/** 
 * Get content of message to be sent as reminder.
 * @param object $course  Course
 * @return str
 * 
 */
function turningtech_getreminderemailbody($course) {
    global $CFG;
    $raw = "\nYour instructor for the course @coursename has chosen to use the Turning Technologies student response system.
                You have not yet registered your ResponseCard device ID.
                Select the course that will be using ResponseCards.
                Select 'ResponseCard Registration' in the left window pane. \n";
    return str_replace('@coursename', $course->fullname, $raw);
}
/** 
 * Unassign student from device mappings in course
 * @param INT $userid  user id of student
 * @param mixed $context
 * 
 */
function turningtech_role_unassign($userid, $context) {
    global $DB;
    if ($context->contextlevel == CONTEXT_COURSE) {
        $student = $DB->get_record('user', 'id', $userid);
        $course  = $DB->get_record('course', 'id', $context->instanceid);
        if ($student && $course) {
            TurningTechTurningHelper::getdeviceidbycourseandstudent($course, $student);
        }
        TurningTechDeviceMap::purgemappings($course, $student);
    }
}