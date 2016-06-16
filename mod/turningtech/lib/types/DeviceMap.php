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
 * represents a user/course/deviceId mapping
 *
 * @author jacob
 * @package mod_turningtech
 * @copyright 2012 Turning Technologies
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
global $CFG;
require_once($CFG->dirroot . '/mod/turningtech/lib/types/TurningModel.php');
require_once($CFG->dirroot . '/mod/turningtech/lib/helpers/MoodleHelper.php');
/**
 * Class for user, course and deviceid mapping
 * @author jacob
 * @copyright 2012 Turning Technologies
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class TurningTechDeviceMap extends TurningModel {
    /**
     * @var unknown_type
     */
    protected $userid;
    /**
     * @var unknown_type
     */
    protected $deviceid;
    /**
     * @var unknown_type
     */
    protected $courseid;
    /**
     * @var unknown_type
     */
    protected $all_courses;
    /**
     * @var unknown_type
     */
    protected $deleted;
    /**
     * @var unknown_type
     */
    protected $typeid;
    /**
     * @var unknown_type
     */
    protected $devicetype;
    /**
     * @var unknown_type
     */
    public $tablename = 'turningtech_device_mapping';
    /**
     * @var unknown_type
     */
    public $classname = 'TurningTechDeviceMap';
    /**
     * override parent's save so that we can check the grade escrow to see
     * if we need to update the gradebook
     * (non-PHPdoc)
     * 
     * @see docroot/mod/turningtech/lib/types/TurningModel#save()
     */
    public function save() {
        $result = parent::save();
        $courses = TurningTechMoodleHelper::getstudentcourses($this->userid);
        foreach ($courses as $course) {
            $c = self::countstudents($course->id, $this->deviceid);
            if ($result && $c == 1) {
                TurningTechIntegrationServiceProvider::migrateescowgrades($this, $course->id);
            }
        }
        return $result;
    }
    /**
     * Update Device Id
     * @param mixed $deviceparam
     * @return unknown_type
     */
    public function updatedevice($deviceparam) {
        global $DB;
        $deviceparam = strtoupper($deviceparam);
        $DB->set_field('turningtech_device_mapping', 'deviceid', $deviceparam, array ('id' => $this->id));
        TurningTechIntegrationServiceProvider::migrateescowgrades($this, $this->deviceid);
    }
    /**
     * fetches instances of child classes
     * @param unknown_type $params
     * @param unknown_type $orders
     * @param unknown_type $limits
     * @return Ambigous <unknown_type, boolean, unknown>
     */
    public static function fetch($params, $orders = null, $limits = null) {
        $params = (array)$params;
        return parent::fetchhelper('turningtech_device_mapping', 'TurningTechDeviceMap', $params, $orders, $limits);
    }
    /**
     * generator function
     * @see docroot/mod/turningtech/lib/types/TurningModel#fetchhelper($table, $classname, $params, $orders = null, $limits = null)
     * @param mixed $params
     * @param bool $vetted
     * @return unknown_type
     */
    public static function generate($params, $vetted = true) {
        global $COURSE;
        if (!$vetted) {
            $map = self::fetch(array ('courseid' => $COURSE->id,
                            'deviceid' => $params->deviceid, 'userid' => $params->userid,
                            'deleted' => 0));
            if ($map) {
                $map->delete();
            }
        }
        return parent::generatehelper('TurningTechDeviceMap', $params);
    }
    /**
     * helper function for building a new DeviceMap by turningtech_device_form
     * 
     * @param mixed $data
     * @return unknown_type
     */
    public static function generatefromform($data) {
        global $COURSE;
        $params = array ();
        $params['deviceid'] = strtoupper($data->deviceid);
        $params['typeid'] = $data->typeid;
        $params['userid'] = $data->userid;
        $params['all_courses'] = $data->all_courses;
        if ($params['typeid'] != 2) {
            $params['courseid'] = $data->courseid;
        }
		$params['courseid'] = $data->courseid;
        // Check if we're updating an existing device map.
        if ($data->devicemapid) {
            $params['id'] = $data->devicemapid;
            if ($data->all_courses) {
                // If user already has an all-courses device ID of the particular device type,
                // edit that record instead of creating a new one.
                if ($map = self::fetch(array ('userid' => $params['userid'],
                                 'all_courses' => 1, 'typeid' => $params['typeid'], 'deleted' => 0))) {
                    $params['id'] = $map->getId();
                }
                /*
                 * Comment
                 * if($map = self::fetch( array( 'userid' => $params['userid'], 'courseid' => $COURSE->id, 'deleted' => 0 ) ) ) {
                 * $map->delete(); }
                 */
            } else if ($map = self::fetch(array ('courseid' => $COURSE->id,
                             'userid' => $data->userid, 'all_courses' => 0, 'deleted' => 0))) {
                $map->delete();
            } else if ($map = self::fetch(array ('courseid' => $COURSE->id,
                             'userid' => $data->userid, 'all_courses' => 1, 'deleted' => 0))) {
                $map->delete();
            } else if ($map = self::fetch(array ('courseid' => $COURSE->id,
                             'deviceid' => $data->deviceid, 'userid' => $data->userid, 'deleted' => 0))) {
                /*
                 * Comment
                 * $map->all_courses = 0;
                 */
                $map->delete();
            }
        } else {
            // If the user enters one of their own device IDs, edit that existing devicemap
            // make sure the user only ever has 1 all-courses device ID and
            // 1 course-specific ID for this course.
            if ($data->all_courses) {
                // If user already has an all-courses device ID of the particular device type,
                // edit that record instead of creating a new one.
                if ($map = self::fetch(array ('userid' => $params['userid'], 'all_courses' => 1,
                                 'typeid' => $data->typeid, 'deleted' => 0))) {
                    if (!$map->courseid) {
                        /* Commented
                         * $params['courseid'] = null;
                         * Code.
                         */
                        $donothing = false;
                    }
                    $params['id'] = $map->getId();
                }
            } else {
                // If user already has course-specific map for this course, edit that record instead of creating
                // a new one.
                if ($map = self::fetch(array ('userid' => $params['userid'], 'courseid' => $params['courseid'], 'deleted' => 0))) {
                    $params['id'] = $map->getId();
                }
            }
        }
        return self::generate($params);
    }
    /**
     * get all devices associated with the user.
     * If $course is specified, only find
     * those that apply to that course.
     * 
     * @param object $user
     * @param mixed $course
     * @return unknown_type
     */
    public static function getalldevices($user, $course = false) {
        global $DB, $CFG;
        $devices = array ();
        $sql = "SELECT mtdp.*, mtdt.type as devicetype FROM " . $CFG->prefix . "turningtech_device_mapping as mtdp ";
        $sql .= "LEFT JOIN " . $CFG->prefix . "turningtech_device_types as mtdt ON mtdt.id = mtdp.typeid ";
        $conditions = array ();
        $conditions[] = 'mtdp.deleted = 0';
        $conditions[] = 'mtdp.userid = ' . $user->id;
        if ($course) {
            $conditions[] = '(mtdp.all_courses = 1 OR mtdp.courseid = ' . $course->id . ')';
        }
        if (count($conditions)) {
            $sql .= "WHERE " . implode(' AND ', $conditions);
        }
        $sql .= " ORDER BY created ASC";
        if ($records = $DB->get_records_sql($sql)) {
            foreach ($records as $record) {
                $device = new TurningTechDeviceMap();
                parent::setproperties($device, $record);
                $devices[] = $device;
            }
        }
        return $devices;
    }
    /**
     * count number of users havin devices associated.
     * If $course is specified, only find
     * those that apply to that course.
     * 
     * @param mixed $course
     * @param string $deviceid
     * @return unknown_type
     */
    public static function countstudents($course, $deviceid) {
        $count = count(TurningTechMoodleHelper::getdevicecount($deviceid, $course));
        return $count;
    }
    /**
     * get boolean if enrolled in all courses
     * @return unknown_type
     */
    public function isallcourses() {
        return $this->all_courses;
    }
    /**
     * get boolean if device is responseware
     * @return boolean
     */
    public function isresponseware() {
        if ($this->all_courses && ! $this->courseid) {
            return true;
        } else {
            return false;
        }
    }
    /**
     * Get Device type
     * @return unknown_type
     */
    public function getdevicetype() {
        return $this->devicetype;
    }
    /**
     * Get Device ID
     * @return Ambigous <boolean, number>
     */
    public function getdevid() {
        $devicegetid = $this->id;
        return $devicegetid;
    }
	/**
     * UnSet Device ID
     */
    public function unsetdevid() {
       $this->id = '';
    }
    /**
     * UnSet Device ID deleted param
     */
    public function unsetdeleted() {
       $this->deleted = '0';
    }
    /**
     * Get Device ID
     * @return unknown_type
     */
    public function getdeviceid() {
        return $this->deviceid;
    }
    /**
     * Get Device ID
     * @param string $deviceid
     * @return unknown_type
     */
    public function setdeviceid($deviceid) {
        $this->deviceid = strtoupper($deviceid);
    }
    /**
     * build a DTO for this item
     * 
     * @return unknown_type
     */
    public function getdata() {
        $data = new stdClass();
        $data->all_courses = $this->all_courses;
        $data->courseid = $this->courseid;
        $data->deleted = ($this->deleted ? 1 : 0);
        $data->deviceid = $this->deviceid;
        $data->typeid = $this->typeid;
        $data->userid = $this->userid;
        if (isset($this->id)) {
            $data->id = $this->id;
        }
        if (isset($this->created)) {
            $data->created = $this->created;
        }
        return $data;
    }
    /**
     * display a link to the form for editing this device map
     * @param mixed $admin
     * @return unknown_type
     */
    public function displaylink($admin = false) {
        global $CFG, $COURSE;
        $url = $CFG->wwwroot . '/mod/turningtech/';
        if ($admin) {
            $url .= "admin_device.php?id={$this->id}";
        } else {
            $url .= "edit_device.php?id={$this->id}";
            $course = $COURSE->id;
            if (! $this->all_courses) {
                $course = $this->courseid;
            }
            if (isset($course)) {
                $url .= '&course=' . $course;
            }
        }
        if ($this->devicetype == 'Response Card') {
            return "<div class='displayInline' id='divResponseCard_1' >{$this->deviceid}</div>
            <div class='displayNone' id='divTextResponseCard_1' >
            <input type='text' id='txtResponseCard_1'  value='{$this->deviceid}'
             onkeypress ='return validateDeviceInputupdate(event);'/></div>";
        } else {
            return "<div class='displayInline' id='divResponseWare_1' >{$this->deviceid}</div>
            <div class='displayNone' id='divTextResponseWare_1' >
            <input type='text' id='txtResponseWare_1' value='{$this->deviceid}'/></div>";
        }
    }
    /**
     * verify that the given device ID is not already in use
     * 
     * @param array $data  with the following keys
     *        - userid
     *        - courseid
     *        - all_courses
     *        - deviceid
     * @return unknown_type
     */
    public static function isalreadyinuse($data) {
        global $DB;
        global $COURSE;
        // Device cannot be in use in ANY course or already be listed as
        // an all-courses device by someone else. So, we just query to see
        // if the device id is listed AT ALL.
        $deviceid = $data['deviceid'];
        $userid = $data['userid'];
        $courseid = $data['courseid'];
        $allcourses = $data['all_courses'];
        $in_use = false;
        $conditions = array ();
        $conditions[] = 'deleted = 0';
        // Fix for postgresql compatibility.
        $conditions[] = "deviceid = '" . $data['deviceid'] . "'";
        $sql = implode(' AND ', $conditions);
        if ($records = $DB->get_records_select('turningtech_device_mapping', $sql)) {
            foreach ($records as $record) {
                // If the device is in use by some other user.
                if ($record->userid != $data['userid']) {
                    return true;
                } else if ($record->userid == $data['userid'] && $data['typeid'] != $record->typeid) {
                    return true;
                }
                if ($data['all_courses'] == 1 || $record->all_courses == 1) {
                    if ($record->userid != $data['userid']) {
                        $user_other = TurningTechMoodleHelper::getuserbyid($record->userid);
                        $current_user = TurningTechMoodleHelper::getuserbyid($data['userid']);
                        $user_other_courses = array_keys(enrol_get_users_courses($record->userid));
                        $current_user_courses = array_keys(enrol_get_users_courses($data['userid']));
                        if (TurningTechMoodleHelper::isuserstudentincourse($user_other, $COURSE) && $record->all_courses == 1) {
                            return true;
                        } else if (count(array_intersect($user_other_courses, $current_user_courses)) > 0 &&
                                                         $data['all_courses'] == 1) {
                            return true;
                        }
                    }
                } else if ($data['courseid'] == $record->courseid && $data['userid'] != $record->userid) {
                    return true;
                }
            }
        }
        return $in_use;
    }
    /**
     * verify that the given device ID is not already in use
     * 
     * @param array $map  with the following keys
     *        - userid
     *        - all_courses
     *        - deviceid
     * @return unknown_type
     */
    public static function isrwalreadyinuse($map) {
        global $DB;
        global $COURSE;
        $in_use = false;
        $conditions = array ();
        $conditions[] = 'deleted = 0';
        // Fix for postgresql compatibility.
        $conditions[] = "deviceid = '" . $map->deviceid . "'";
        $sql = implode(' AND ', $conditions);
        if ($records = $DB->get_records_select('turningtech_device_mapping', $sql)) {
            foreach ($records as $record) {
                // If the device is in use by some other user.
                if ($record->userid != $map->userid) {
                    return true;
                }
                if ($map->all_courses == 1 || $record->all_courses == 1) {
                    if ($record->userid != $map->userid) {
                        $user_other = TurningTechMoodleHelper::getuserbyid($record->userid);
                        $current_user = TurningTechMoodleHelper::getuserbyid($map->userid);
                        $user_other_courses = array_keys(enrol_get_users_courses($record->userid));
                        $current_user_courses = array_keys(enrol_get_users_courses($map->userid));
                        if (TurningTechMoodleHelper::isuserstudentincourse($user_other, $COURSE) && $record->all_courses == 1) {
                            return true;
                        } else if (count(array_intersect($user_other_courses, $current_user_courses)) > 0) {
                            return true;
                        }
                    }
                } else if ($map->courseid == $record->courseid && $map->userid != $record->userid) {
                    return true;
                }
            }
        }
        return $in_use;
    }
    /**
     * mark the device map as deleted
     * 
     * @return unknown_type
     */
    public function delete() {
        global $DB;
        $DB->set_field('turningtech_device_mapping', 'deleted', 1, array ('id' => $this->id));
        $this->deleted = 1;
    }
    /**
     * display a link to the form for this device map
     * @param mixed $admin
     * @return unknown_type
     */
    public function displaylinkinstructor($admin = false) {
        global $CFG, $COURSE;
        $url = $CFG->wwwroot . '/mod/turningtech/';
        if ($admin) {
            $url .= "admin_device.php?id={$this->id}";
        } else {
            $url .= "edit_device.php?id={$this->id}";
            $course = $COURSE->id;
            if (! $this->all_courses) {
                $course = $this->courseid;
            }
            if (isset($course)) {
                $url .= '&course=' . $course;
            }
        }
        if ($this->typeid == 1) {
            return "<div class='cancelDeleteDiv' id='divEditRC_{$this->userid}' >
		<a id='lnkEditRC_{$this->userid}' href='#' onclick='EditResponseCard(this)'  style='text-decoration:underline;'>
		<div class='displayInline' id='divResponseCard_{$this->userid}' >{$this->deviceid}</div>
		</a>
		</div>
		<div class='displayNone' id='divTextResponseCard_{$this->userid}' >
		<input type='text' id='txtResponseCard_{$this->userid}'  value='{$this->deviceid}'
		 onkeypress ='return validateDeviceInputupdate(event, this);'/>
		</div>
		<div id='divUpdateCancelRC_{$this->userid}' class='displayNone' style='margin-left: 10px;'>
		<input id='lnkUpdateRC_{$this->userid}' type='button' value='Update' onclick='UpdateRCinput(this);'
		name='update_device_instructor.php?id={$this->userid}&course=". $COURSE->id . "&action=update'/></a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
		<input id='lnkCancelRC_{$this->userid}' onclick='CancelResponseCard(this)' type='button' value='Cancel'/>
		</div>";
        } else {
            return "<div class='displayInline' id='divResponseWare_0' >{$this->deviceid}</div>
            <div class='displayNone' id='divTextResponseWare_0' >
            <input type='text' id='txtResponseWare_0' value='{$this->deviceid}'/></div>";
        }
        /* Comment
         * return "<a href='{$url}'>{$this->deviceid}</a>";
         * Code.
         */
    }
    /**
     * purge all course-based device IDs for this course/user.
     * 
     * @param object $course
     * @param object $user
     * @return count of updated fields
     */
    public static function purgemappings($course, $user) {
        global $DB;
        if ((! $course || ! isset($course->id))) {
            turningtech_set_message(get_string('couldnotpurge', 'turningtech'), 'error');
            return false;
        } else if ((! $user || ! isset($user->id))) {
            turningtech_set_message(get_string('couldnotpurge', 'turningtech'), 'error');
            return false;
        }
        $table = 'turningtech_device_mapping';
        $field = 'deleted';
        $value = 1;
        $field1 = 'all_courses';
        $value1 = 0;
        $field2 = 'courseid';
        $value2 = $course->id;
        $field3 = 'userid';
        $value3 = $user->id;
        $rs = $DB->set_field($table, array ($field => $value, $field1 => $value1, $field2 => $value2, $field3 => $value3));
        return $DB->Affected_Rows();
    }
    /**
     * purge all selected students device IDs for all course.
     * 
     * @param mixed $userid
     * @param mixed $deviceid
     * @return bool
     */
    public static function purgeselectedmappings($userid, $deviceid) {
        global $DB;
        if (! isset($userid)) {
            turningtech_set_message(get_string('couldnotpurge', 'turningtech'), 'error');
            return false;
        }
        $table = 'turningtech_device_mapping';
        $rs = $DB->set_field($table, 'deleted', 1, array ('userid' => $userid, 'deviceid' => $deviceid));
        return true;
    }
    /**
     * purge all device IDs in this course
     * 
     * @param object $course
     * @return unknown_type
     */
    public static function purgecourse($course) {
        return self::purge($course);
    }
    /**
     * purge all all-courses device IDs
     * 
     * @return unknown_type
     */
    public static function purgeglobal() {
        return self::purge();
    }
    /**
     * helper function for purging device ids
     * 
     * @param mixed $course
     * @return unknown_type
     */
    private static function purge($course = false) {
        global $DB;
        $table = 'turningtech_device_mapping';
        if ($course && isset($course->id)) {
            $field1 = 'all_courses';
            $value1 = 1;
            $field2 = 'courseid';
            $value2 = $course->id;
            $count = $DB->count_records($table, array ('deleted' => 0, 'all_courses' => $value1, 'courseid' => $course->id));
            $rs = $DB->set_field($table, 'deleted', 1, array ('all_courses' => $value1, 'courseid' => $course->id));
        } else if ($course === false) {
            $count = $DB->count_records($table, array ('deleted' => 0));
            $rs = $DB->set_field($table, 'deleted', 1, array ('deleted' => 0));
        }
        return $count;
    }
}