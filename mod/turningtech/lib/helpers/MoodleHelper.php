<?php
/*******
 * Helper class for support of Moodle API-related functionality.
 * @author jacob
 *
 * NOTE: callers which include/require this class MUST also include/require the following:
 * - [moodle root]/config.php
 * - mod/turningtech/lib.php
 **/
require_once($CFG->dirroot . '/grade/lib.php');
global $DB;
/**
 * Class that abstracts communication with Moodle systems
 * @author jacob
 *
 */
class TurningTechMoodleHelper {
    /**
     * authenticate a username/password pair
     * @param $username
     * @param $password
     * @return user object
     */
    public static function authenticateUser($username, $password) {
        return authenticate_user_login($username, $password);
    }

    /**
     * returns all courses for which the given user is
     * in the "teacher" role
     * @param $user
     * @return unknown_type
     */
    public static function getInstructorCourses($user) {
        $courses   = array();
        $mycourses = enrol_get_users_courses($user->id, false);

        // iterate through courses and verify that this user is
        // the instructor, not a student, for each course
        foreach ($mycourses as $course) {
            $context    = get_context_instance(CONTEXT_COURSE, $course->id);
            $role_users = array();
            $inst_roles = explode(',', TURNINGTECH_DEFAULT_TEACHER_ROLE);
            foreach ($inst_roles as $index => $roleid) {
                $role_users = array_merge($role_users, get_role_users($roleid, $context));
            }
            foreach ($role_users as $ru) {
                if ($ru->id == $user->id) {
                    $courses[] = $course;
                    break;
                }
            }
        }
        return $courses;
    }

    /**
     * returns extended list of all courses for which the given user is
     * in the "teacher" role
     * @param $user
     * @return unknown_type
     */
    public static function getExtInstructorCourses($user) {
        $courses   = array();
        $mycourses = enrol_get_users_courses($user->id, false);

        // iterate through courses and verify that this user is
        // the instructor, not a student, for each course
        foreach ($mycourses as $course) {
            $context    = get_context_instance(CONTEXT_COURSE, $course->id);
            $role_users = array();
            $inst_roles = explode(',', TURNINGTECH_DEFAULT_TEACHER_ROLE);
            foreach ($inst_roles as $index => $roleid) {
                $role_users = array_merge($role_users, get_role_users($roleid, $context));
            }
            foreach ($role_users as $ru) {
                if ($ru->id == $user->id) {
                    $courses[] = $course;
                    break;
                }
            }
        }
        return $courses;
    }

    /**
     * check if user is enrolled as student in course
     * @param $user
     * @param $course
     * @return unknown_type
     */
    public static function isUserStudentInCourse($user, $course) {
        $found = self::getClassRoster($course, FALSE, $user->id);
        return ($found ? TRUE : FALSE);
    }

    /**
     * check if user is instructor for course
     * @param $user
     * @param $course
     * @return unknown_type
     */
    public static function isUserInstructorInCourse($user, $course) {
        $context = get_context_instance(CONTEXT_COURSE, $course->id);
        return has_capability('mod/turningtech:manage', $context, $user->id);
    }

    /**
     * determines whether the user has permission to view the course roster
     * @param $user
     * @param $course
     * @return unknown_type
     */
    public static function userHasRosterPermission($user, $course) {
        $allowed = FALSE;
        if ($context = get_context_instance(CONTEXT_COURSE, $course->id)) {
            $allowed = has_capability('moodle/course:viewparticipants', $context, $user->id);
        }
        return $allowed;
    }

    /**
     * determines whether user has permission to create a new gradebook item in the given course
     * @param $user
     * @param $course
     * @return unknown_type
     */
    public static function userHasGradeItemPermission($user, $course) {
        $allowed = FALSE;
        if ($context = get_context_instance(CONTEXT_COURSE, $course->id)) {
            $allowed = has_capability('moodle/grade:manage', $context, $user->id);
        }
        return $allowed;
    }
    /**
     * fetches the class roster
     * @param $course
     * @param $roles array of role ids
     * @param $userid optional id of user to quickly check if they are enrolled
     * @return unknown_type
     */
    public static function getClassRoster($course, $roles = FALSE, $userid = FALSE, $order = 'u.lastname', $asc = TRUE) {
        global $CFG, $DB;
        $params = array();

        if (!$roles) {
            $roles = array(
                TURNINGTECH_DEFAULT_STUDENT_ROLE
            );
        }

        $sql = "SELECT u.id, u.firstname, u.lastname, u.username, u.email, d.id AS devicemapid, d.deviceid, d.deleted, d.all_courses, d.courseid, r.roleid AS Role ";
        $sql .= "FROM {user} u ";
        $sql .= "LEFT JOIN {turningtech_device_mapping} dall ON ( dall.userid = u.id AND dall.deleted = :dalldeleted1 AND dall.all_courses = :dallcourses1 ) ";
        $sql .= "LEFT JOIN {turningtech_device_mapping} dcrs ON ( dcrs.userid = u.id AND dcrs.deleted = :dalldeleted2 AND dcrs.all_courses = :dallcourses2 AND dcrs.courseid = :courseid1 ) ";
        $sql .= "LEFT JOIN {turningtech_device_mapping} d ON ( ( dcrs.id IS NOT NULL AND dcrs.id = d.id ) OR ( dcrs.id IS NULL AND dall.id = d.id ) ) ";
        $sql .= "LEFT JOIN {role_assignments} r ON r.userid = u.id ";
        $sql .= "LEFT JOIN {context} c ON r.contextid = c.id ";

        $where  = "r.roleid IN (" . $roles[0] . ")";
        $where .= " AND u.deleted = :udeleted AND c.contextlevel = :contextcourse AND c.instanceid = :courseid2";

        if ($userid) {
            $where .= "AND u.id= :uid ";
            $params['uid'] = $userid;
        }

        $orderby = "ORDER BY :order ";
        $order   .= ($asc ? ' ASC' : ' DESC');

        $params['dalldeleted1']  = 0;
        $params['dallcourses1']  = 1;
        $params['dalldeleted2']  = 0;
        $params['dallcourses2']  = 0;
        $params['courseid1']     = $course->id;
        $params['courseid2']     = $course->id;
        $params['contextcourse'] = CONTEXT_COURSE;
        $params['udeleted']      = 0;
        //$params['roles']       = "'". $roles[0] ."'";
        $params['order']         = $order;

        $sql = "{$sql} WHERE {$where} {$orderby}";

        return $DB->get_records_sql($sql, $params);
    }

    /**
     * fetches the extended class roster with multiple device ids.
     * @param $course
     * @param $roles array of role ids
     * @param $userid optional id of user to quickly check if they are enrolled
     * @return unknown_type
     */
    public static function getExtClassRoster($course, $roles = FALSE, $userid = FALSE, $order = 'u.lastname', $asc = TRUE) {
        global $CFG, $DB;


        if (!$roles) {
            $roles = array(
                TURNINGTECH_DEFAULT_STUDENT_ROLE
            );
        }
        $sql = "SELECT u.id, u.firstname, u.lastname, u.username, u.email, GROUP_CONCAT(d.id) AS devicemapid, GROUP_CONCAT(d.deviceid) AS deviceid, GROUP_CONCAT(d.deleted) AS deleted, GROUP_CONCAT(d.all_courses) AS all_courses, GROUP_CONCAT(d.courseid) AS courseid, GROUP_CONCAT(dt.type) AS devicetype, r.roleid AS Role ";
        $sql .= "FROM {user} u ";
        $sql .= "LEFT JOIN {turningtech_device_mapping} dall ON (dall.userid = u.id AND dall.deleted = :dalldeleted1 AND dall.all_courses = :dallcourses1) ";
        $sql .= "LEFT JOIN {turningtech_device_mapping} dcrs ON (dcrs.userid = u.id AND dcrs.deleted = :dalldeleted2 AND dcrs.all_courses = :dallcourses2 AND dcrs.courseid= :courseid1) ";
        $sql .= "LEFT JOIN {turningtech_device_mapping} d ON ((dcrs.id IS NOT NULL AND dcrs.id = d.id) OR (dcrs.id IS NULL AND dall.id = d.id)) ";
        $sql .= "LEFT JOIN {turningtech_device_types} dt ON dt.id = dall.typeid ";
        $sql .= "LEFT JOIN {role_assignments} r ON r.userid = u.id ";
        $sql .= "LEFT JOIN {context} c ON r.contextid = c.id ";
        $params = array();
        $where  = "r.roleid IN (" . $roles[0] . ")";
        //$params['roles'] = "'". $roles[0] ."'";

        $params['dalldeleted1']  = 0;
        $params['dallcourses1']  = 1;
        $params['dalldeleted2']  = 0;
        $params['dallcourses2']  = 0;
        $params['courseid1']     = $course->id;
        $params['courseid2']     = $course->id;
        $params['contextcourse'] = CONTEXT_COURSE;
        $params['udeleted']      = 0;

        $where .= " AND u.deleted= :udeleted AND c.contextlevel= :contextcourse AND c.instanceid= :courseid2 ";
        $groupby = "GROUP BY u.id";
        if ($userid) {
            $where .= "AND u.id= :uid ";
            $params['uid'] = $userid;
        }

        $orderby         = "ORDER BY :order ";
        $order           = ($asc ? 'ASC' : 'DESC');
        $params['order'] = $order;
        $sql             = "{$sql} WHERE {$where} {$groupby} {$orderby}";

        $classRoster = $DB->get_records_sql($sql, $params);
        return $classRoster;
    }

    /**
     * searches for users by username
     * @param $str
     * @return unknown_type
     */
    public static function adminStudentSearch($str) {
        global $CFG, $DB;
        $roles  = array(
            TURNINGTECH_DEFAULT_STUDENT_ROLE
        );
        $str    = strtolower($str);
        $select = "SELECT u.id, u.firstname, u.lastname, u.username, u.email, d.id AS devicemapid, d.deviceid, d.deleted, d.all_courses, d.courseid ";
        $select .= "FROM {user} u ";
        $select .= "LEFT JOIN {turningtech_device_mapping} d ON (d.userid=u.id AND d.deleted= :dalldeleted) ";
        $select .= "LEFT JOIN {role_assignments} r ON r.userid=u.id ";
        $select .= "LEFT JOIN {context} c ON r.contextid=c.id ";

        $params                  = array();
        $params['dalldeleted']   = 0;
        $params['username']      = $str;
        $params['contextcourse'] = CONTEXT_COURSE;

        $where    = array();
        $where[]  = "r.roleid IN(" . $roles[0] . ")"; //'r.roleid IN(' . TURNINGTECH_DEFAULT_STUDENT_ROLE . ')';
        $where[]  = 'c.contextlevel= :contextcourse';
        $where[]  = 'lower(u.username) LIKE :username';
        $wheresql = implode(' AND ', $where);

        $sql = "{$select} WHERE {$wheresql} ORDER BY u.username";
        return $DB->get_records_sql($sql, $params);
    }

    /**
     * fetches a user object by id
     * @param $id
     * @return unknown_type
     */
    public static function getUserById($id) {
        return get_complete_user_data('id', $id);
    }

    /**
     * fetches a user object by username
     * @param $username
     * @return unknown_type
     */
    public static function getUserByUsername($username) {
        global $DB;

        return $DB->get_record("user", array(
            "username" => $username
        ));
    }

    /**
     * creates a gradebook item
     * @param $course
     * @param $title
     * @param $points
     * @return unknown_type
     */
    public static function createGradebookItem($course, $title, $points) {
        // contains possible error DTO
        $dto = new stdClass();

        if (self::getGradebookItemByCourseAndTitle($course, $title)) {
            $dto->itemTitle    = $title;
            $dto->errorMessage = get_string('gradebookitemalreadyexists', 'turningtech');
            return $dto;
        }

        // create new grade item
        $grade_item           = new grade_item(array(
            'courseid' => $course->id
        ), FALSE);
        // set parent category
        $data                 = $grade_item->get_record_data();
        $parent_category      = grade_category::fetch_course_category($course->id);
        $data->parentcategory = $parent_category->id;
        // set points
        $data->grademax       = unformat_float($points);
        $data->grademin       = unformat_float(0.0);
        // set title
        $data->itemname       = $title;

        grade_item::set_properties($grade_item, $data);
        $grade_item->outcomeid = null;

        $grade_item->itemtype   = TURNINGTECH_GRADE_ITEM_TYPE;
        $grade_item->itemmodule = TURNINGTECH_GRADE_ITEM_MODULE;
        $grade_item->insert();

        return FALSE;
    }

    /**
     * updates a gradebook item
     * @param $id
     * @param $data
     * @return unknown_type
     */
    public static function updateGradebookItem($id, $data) {
        // create new grade item
        $grade_item = grade_item::fetch(array(
            'id' => $id
        ));

        // set values
        if (is_array($data)) {

            foreach ($data as $prop => $value) {
                if ($prop == "grademax" || $prop == "grademin") {
                    $data->$prop = unformat_float($value);
                } else {
                    $data->$prop = $value;
                }
            }

            grade_item::set_properties($grade_item, $data);

            $grade_item->update();
        }

        return FALSE;
    }

    /**
     * fetches a list of all gradebook items in the course
     * @param $course
     * @return unknown_type
     */
    public static function getGradebookItemsByCourse($course) {
        $gtree = new grade_tree($course->id, false, false);
        $items = array();

        foreach ($gtree->top_element['children'] as $item) {
            // do not include courses, categories, etc
            if ($item['object']->itemmodule == TURNINGTECH_GRADE_ITEM_MODULE) {
                $items[] = $item['object'];
            }
        }

        return $items;
    }

    /**
     * fetches a gradebook item
     * @param $course
     * @param $title
     * @return unknown_type
     */
    public static function getGradebookItemByCourseAndTitle($course, $title) {
        return grade_item::fetch(array(
            'itemname' => $title,
            'courseid' => $course->id,
            'itemmodule' => TURNINGTECH_GRADE_ITEM_MODULE
        ));
    }

    /**
     * fetch a gradebook item
     * @param $id
     * @return unknown_type
     */
    public static function getGradebookItemById($id) {
        return grade_item::fetch(array(
            'id' => $id,
            'itemmodule' => TURNINGTECH_GRADE_ITEM_MODULE
        ));
    }

    /**
     * get a record from the gradebook
     * @param $student
     * @param $grade_item
     * @return unknown_type
     */
    public static function getGradeRecord($student, $grade_item) {
        return new grade_grade(array(
            'userid' => $student->id,
            'itemid' => $grade_item->id
        ));
    }

    /**
     * check if the specified user already has a grade for the given item
     * @param $studentid
     * @param $gradeitemid
     * @return unknown_type
     */
    public static function gradeAlreadyExists($user, $grade_item) {
        $grade = self::getGradeRecord($user, $grade_item);
        return !empty($grade->id);
    }

    public static function getCourseById($siteId) {
        global $DB;

        return $DB->get_record("course", array(
            "id" => $siteId
        ));
    }

    public static function isStudentInCourse($user, $course) {
        global $CFG, $DB;
        $roles = array(
            TURNINGTECH_DEFAULT_STUDENT_ROLE
        );

        $sql = "SELECT ue.enrolid ";
        $sql .= "FROM {user_enrolments} ue ";
        $sql .= "INNER JOIN {user} u ON ( u.id = ue.userid AND u.id = :userid ) ";
        $sql .= "INNER JOIN {enrol} e ON ( e.id = ue.enrolid AND e.courseid = :courseid  AND e.roleid IN ( " . $roles[0] . " ) )";

        $params             = array();
        $params['userid']   = $user->id;
        $params['courseid'] = $course->id;

        $found = $DB->get_records_sql($sql, $params);

        return ($found ? TRUE : FALSE);
    }
}
?>