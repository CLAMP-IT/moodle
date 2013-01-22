<?php

global $CFG;
require_once($CFG->dirroot . '/mod/turningtech/lib/types/TurningModel.php');
/**

/**
* represents a user/course/deviceId mapping
* @author jacob
*
*/
class TurningTechDeviceMap extends TurningModel {
    // user
    protected $userid;
    // device ID
    protected $deviceid;
    // course
    protected $courseid;
    // is this just for a single course, or all courses?
    protected $all_courses;
    // has this been deleted?
    protected $deleted;
    // device type ID
    protected $typeid;
    // type
    protected $devicetype;

    public $tablename = 'turningtech_device_mapping';
    public $classname = 'TurningTechDeviceMap';

    /**
     * constructor
     * @return unknown_type
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * override parent's save so that we can check the grade escrow to see
     * if we need to update the gradebook
     * (non-PHPdoc)
     * @see docroot/mod/turningtech/lib/types/TurningModel#save()
     */
    public function save() {
        $result = parent::save();
        if ($result) {
            TurningTechIntegrationServiceProvider::migrateEscowGrades($this);
        }
        return $result;
    }

    /**
     * fetch an instance
     * @param $params
     * @return unknown_type
     */
    public static function fetch($params, $orders = null, $limits = null) {
        $params = (array) $params;
        /*
        return parent::fetchHelper('turningtech_device_mapping', 'TurningTechDeviceMap', $params);
        */
        return parent::fetchHelper('turningtech_device_mapping', 'TurningTechDeviceMap', $params, $orders, $limits);
    }

    /**
     * generator function
     * @param $params
     * @return unknown_type
     */
    public static function generate($params, $vetted = TRUE) {
        global $COURSE;
        if (!$vetted) {
            /*if($map = self::fetch(
            array(
            'all_courses' => 1,
            'userid' => $params->userid,
            'deleted' => 0
            )
            )
            )
            {
            $map->all_courses = 0;
            $map->save();
            }
            else*/
            if ($map = self::fetch(array(
                'courseid' => $COURSE->id,
                'deviceid' => $params->deviceid,
                'userid' => $params->userid,
                'deleted' => 0
            ))) {
                $map->delete();
            }
        }
        return parent::generateHelper('TurningTechDeviceMap', $params);
    }

    /**
     * helper function for building a new DeviceMap by turningtech_device_form
     * @param $data
     * @return unknown_type
     */
    public static function generateFromForm($data) {
        global $COURSE;

        $params = array();

        $params['deviceid']     = strtoupper($data->deviceid);
        $params['typeid']       = $data->typeid;
        $params['userid']       = $data->userid;
        $params['all_courses']  = $data->all_courses;

        //if(!$params['all_courses']) {
        if ($params['typeid'] != 2) {
            $params['courseid'] = $data->courseid;
        }

        // check if we're updating an existing device map
        if ($data->devicemapid) {
            $params['id'] = $data->devicemapid;

            if ($data->all_courses) {
                // if user already has an all-courses device ID of the particular device type,
                // edit that record instead of creating a new one
                if ($map = self::fetch(array(
                    'userid' => $params['userid'],
                    'all_courses' => 1,
                    'typeid' => $params['typeid'],
                    'deleted' => 0
                ))) {
                    //$map->delete();
                    $params['id'] = $map->getId();
                }
                /*
                if($map = self::fetch(
                array(
                'userid' => $params['userid'],
                'courseid' => $COURSE->id,
                'deleted' => 0
                )
                )
                ) {
                $map->delete();
                }
                */
            } else if ($map = self::fetch(array(
                'courseid' => $COURSE->id,
                'userid' => $data->userid,
                'all_courses' => 0,
                'deleted' => 0
            ))) {
                $map->delete();
            } else if ($map = self::fetch(array(
                'courseid' => $COURSE->id,
                'userid' => $data->userid,
                'all_courses' => 1,
                'deleted' => 0
            ))) {
                $map->delete();
            } else if ($map = self::fetch(array(
                'courseid' => $COURSE->id,
                'deviceid' => $data->deviceid,
                'userid' => $data->userid,
                'deleted' => 0
            ))) {
                //$map->all_courses = 0;
                $map->delete();
            }
        } else {
            // if the user enters one of their own device IDs, edit that existing devicemap
            // make sure the user only ever has 1 all-courses device ID and
            // 1 course-specific ID for this course
            if ($data->all_courses) {
                // if user already has an all-courses device ID of the particular device type,
                // edit that record instead of creating a new one
                if ($map = self::fetch(array(
                    'userid' => $params['userid'],
                    'all_courses' => 1,
                    'typeid' => $data->typeid,
                    'deleted' => 0
                ))) {
                    if (!$map->courseid) {
                        //$params['courseid'] = null;
                    }
                    $params['id'] = $map->getId();
                }
            } else {
                // if user already has course-specific map for this course, edit that record instead of creating
                // a new one
                if ($map = self::fetch(array(
                    'userid' => $params['userid'],
                    'courseid' => $params['courseid'],
                    'deleted' => 0
                ))) {
                    $params['id'] = $map->getId();
                }
            }
        }
        return self::generate($params);
    }

    /**
     * get all devices associated with the user.  If $course is specified, only find
     * those that apply to that course.
     * @param $user
     * @param $course
     * @return unknown_type
     */
    public static function getAllDevices($user, $course = FALSE) {
        global $DB, $CFG;
        $devices = array();

        $sql = "SELECT mtdp.*, mtdt.type as devicetype FROM " . $CFG->prefix . "turningtech_device_mapping as mtdp ";
        $sql .= "LEFT JOIN " . $CFG->prefix . "turningtech_device_types as mtdt ON mtdt.id = mtdp.typeid ";

        $conditions   = array();
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
                parent::setProperties($device, $record);
                $devices[] = $device;
            }
        }
        return $devices;
    }

    /**
     *
     * @return unknown_type
     */
    public function isAllCourses() {
        return $this->all_courses;
    }
    /**
     *
     * @return boolean
     */
    public function isResponseWare() {
        if ($this->all_courses && !$this->courseid) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    public function getDeviceType() {
        return $this->devicetype;
    }

    /**
     * build a DTO for this item
     * @return unknown_type
     */
    public function getData() {
        $data               = new stdClass();
        $data->all_courses  = $this->all_courses;
        $data->courseid     = $this->courseid;
        $data->deleted      = ($this->deleted ? 1 : 0);
        $data->deviceid     = $this->deviceid;
        $data->typeid       = $this->typeid;
        $data->userid       = $this->userid;
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
     * @return unknown_type
     */
    public function displayLink($admin = FALSE) {
        global $CFG, $COURSE;

        $url = $CFG->wwwroot . '/mod/turningtech/';
        if ($admin) {
            $url .= "admin_device.php?id={$this->id}";
        } else {
            $url .= "edit_device.php?id={$this->id}";
            $course = $COURSE->id;
            if (!$this->all_courses) {
                $course = $this->courseid;
            }
            if (isset($course)) {
                $url .= '&course=' . $course;
            }
        }
        return "<a href='{$url}'>{$this->deviceid}</a>";
    }

    /**
     * verify that the given device ID is not already in use
     * @param $data array with the following keys
     *  - userid
     *  - courseid
     *  - all_courses
     *  - deviceid
     * @return unknown_type
     */
    public static function isAlreadyInUse($data) {
        global $DB;
        global $COURSE;
        // device cannot be in use in ANY course or already be listed as
        // an all-courses device by someone else.  So, we just query to see
        // if the device id is listed AT ALL
        $deviceId   = $data['deviceid'];
        $userId     = $data['userid'];
        $courseId   = $data['courseid'];
        $allCourses = $data['all_courses'];
        $in_use     = FALSE;

        $conditions   = array();
        $conditions[] = 'deleted = 0';
        $conditions[] = 'deviceid ="' . $data['deviceid'] . '"';
        $sql          = implode(' AND ', $conditions);

        if ($records = $DB->get_records_select('turningtech_device_mapping', $sql)) {
            foreach ($records as $record) {
                // If the device is in use by some other user.
                if ($record->userid != $data['userid']) {
                    return TRUE;
                }

                if ($data['all_courses'] == 1 || $record->all_courses == 1) {
                    if ($record->userid != $data['userid']) {
                        $user_other   = TurningTechMoodleHelper::getUserById($record->userid);
                        $current_user = TurningTechMoodleHelper::getUserById($data['userid']);

                        $user_other_courses   = array_keys(enrol_get_users_courses($record->userid));
                        $current_user_courses = array_keys(enrol_get_users_courses($data['userid']));

                        if (TurningTechMoodleHelper::isUserStudentInCourse($user_other, $COURSE) && $record->all_courses == 1) {
                            return TRUE;
                        } else if (count(array_intersect($user_other_courses, $current_user_courses)) > 0 && $data['all_courses'] == 1) {
                            return TRUE;
                        }
                    }

                } else if ($data['courseid'] == $record->courseid && $data['userid'] != $record->userid) {
                    return TRUE;
                }
            }
        }
        return $in_use;
    }

    /**
     * verify that the given device ID is not already in use
     * @param $map array with the following keys
     *  - userid
     *  - all_courses
     *  - deviceid
     * @return unknown_type
     */
    public static function isRWAlreadyInUse($map) {
        global $DB;
        global $COURSE;
        $in_use       = FALSE;
        $conditions   = array();
        $conditions[] = 'deleted = 0';
        $conditions[] = 'deviceid ="' . $map->deviceid . '"';
        $sql          = implode(' AND ', $conditions);
        if ($records = $DB->get_records_select('turningtech_device_mapping', $sql)) {
            foreach ($records as $record) {
                // If the device is in use by some other user.
                if ($record->userid != $map->userid) {
                    return TRUE;
                }

                if ($map->all_courses == 1 || $record->all_courses == 1) {
                    if ($record->userid != $map->userid) {
                        $user_other           = TurningTechMoodleHelper::getUserById($record->userid);
                        $current_user         = TurningTechMoodleHelper::getUserById($map->userid);
                        $user_other_courses   = array_keys(enrol_get_users_courses($record->userid));
                        $current_user_courses = array_keys(enrol_get_users_courses($map->userid));

                        if (TurningTechMoodleHelper::isUserStudentInCourse($user_other, $COURSE) && $record->all_courses == 1) {
                            return TRUE;
                        } else if (count(array_intersect($user_other_courses, $current_user_courses)) > 0) {
                            return TRUE;
                        }
                    }
                } else if ($map->courseid == $record->courseid && $map->userid != $record->userid) {
                    return TRUE;
                }
            }
        }
        return $in_use;
    }

    /**
     * mark the device map as deleted
     * @return unknown_type
     */
    public function delete() {
        global $DB;
        $DB->set_field('turningtech_device_mapping', 'deleted', 1, array(
            'id' => $this->id
        ));
        $this->deleted = 1;
    }

    /**
     * purge all course-based device IDs for this course/user.
     * @param $course
     * @param $user
     * @return count of updated fields
     */
    public static function purgeMappings($course, $user) {
        global $DB;

        if ((!$course || !isset($course->id))) {
            turningtech_set_message(get_string('couldnotpurge', 'turningtech'), 'error');
            return FALSE;
        } else if ((!$user || !isset($user->id))) {
            turningtech_set_message(get_string('couldnotpurge', 'turningtech'), 'error');
            return FALSE;
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

        $rs = $DB->set_field($table, array(
            $field => $value,
            $field1 => $value1,
            $field2 => $value2,
            $field3 => $value3
        ));
        return $DB->Affected_Rows();
    }

    /**
     * purge all device IDs in this course
     * @param $course
     * @return unknown_type
     */
    public static function purgeCourse($course) {
        return self::purge($course);
    }

    /**
     * purge all all-courses device IDs
     * @return unknown_type
     */
    public static function purgeGlobal() {
        return self::purge();
    }

    /**
     * helper function for purging device ids
     * @param $course
     * @return unknown_type
     */
    private static function purge($course = FALSE) {
        global $DB;

        $table = 'turningtech_device_mapping';
        if ($course && isset($course->id)) {
            $field1 = 'all_courses';
            $value1 = 1;
            $field2 = 'courseid';
            $value2 = $course->id;
            $count  = $DB->count_records($table, array(
                'deleted' => 0,
                'all_courses' => $value1,
                'courseid' => $course->id
            ));
            $rs     = $DB->set_field($table, 'deleted', 1, array(
                'all_courses' => $value1
                //'courseid' => $course->id
            ));
        } else if ($course === FALSE) {
            $count = $DB->count_records($table, array(
                'deleted' => 0
            ));
            $rs    = $DB->set_field($table, 'deleted', 1, array(
                'deleted' => 0
            ));
        }
        return $count;
    }
}
?>