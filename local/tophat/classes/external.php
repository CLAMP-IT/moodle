<?php
defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir . '/externallib.php');
require_once($CFG->libdir . '/enrollib.php');
require_once($CFG->libdir . '/gradelib.php');
require_once($CFG->dirroot . '/enrol/externallib.php');
require_once(dirname(__DIR__) . '/locallib.php');

class local_tophat_external extends external_api {

    private static function is_admin($uid) {
        $admins = get_admins();
        foreach ($admins as $admin) {
            if ($uid == $admin->id) {
                return true;
            }
        }
        return false;
    }

    private static function make_web_service_endpoint($function, $params) {
        $endpoint = new moodle_url('/webservice/rest/server.php', [
            'wsfunction' => $function,
            'moodlewsrestformat' => 'json',
        ]);
        $endpoint->params($params);
        return $endpoint;
    }

    public static function echo_message_parameters() {
        return new external_function_parameters(
            array(
                'message' => new external_value(PARAM_TEXT, 'any text')
            )
        );
    }

    public static function echo_message($message) {
        return ['message' => 'ECHO: ' . $message];
    }

    public static function echo_message_returns() {
        return new external_single_structure(
            array(
                'message' => new external_value(PARAM_TEXT, 'message echo'),
            )
        );
    }

    public static function get_enrolled_users_parameters() {
        return new external_function_parameters(
            array(
                'courseid' => new external_value(PARAM_INT, 'course id'),
                'page' => new external_value(PARAM_INT, 'page'),
                'per_page' => new external_value(PARAM_INT, 'pageisze'),
            )
        );
    }

    public static function get_enrolled_users($courseid, $page = 0, $perpage = 10) {
        global $USER, $DB, $CFG;

        $selfurl = self::make_web_service_endpoint('local_tophat_get_enrolled_users', array(
            'courseid' => $courseid,
            'page' => $page,
            'per_page' => $perpage,
        ));

        $nexturl = self::make_web_service_endpoint('local_tophat_get_enrolled_users', array(
            'courseid' => $courseid,
            'page' => $page + 1,
            'per_page' => $perpage,
        ));
        $students = local_tophat_cache::get_students($courseid);
        $data = array_slice($students, $page * $perpage, $perpage);

        return array(
            'data' => $data,
            'links' => [
                'next' => array(
                    'href' => $nexturl->out(false),
                ),
                'self' => array(
                    'href' => $selfurl->out(false),
                )
            ],
        );
    }

    public static function get_enrolled_users_returns() {
        return new external_single_structure(array(
            'data' => new external_multiple_structure(
                new external_single_structure(
                    array(
                        'id' => new external_value(PARAM_INT, 'user id'),
                        'username' => new external_value(PARAM_USERNAME, 'username'),
                        'firstname' => new external_value(PARAM_TEXT, 'firstname'),
                        'lastname' => new external_value(PARAM_TEXT, 'lastname'),
                        'email' => new external_value(PARAM_EMAIL, 'email'),
                    )
                )
            ),
            'links' => new external_single_structure(array(
                'next' => new external_single_structure(array(
                    'href' => new external_value(PARAM_URL, 'next url'),
                ), 'next page url', VALUE_OPTIONAL),
                'self' => new external_single_structure(array(
                    'href' => new external_value(PARAM_URL, 'self url'),
                ), 'self url'),
            ), 'links', VALUE_OPTIONAL)
        ));
    }

    public static function get_courses_parameters() {
        return new external_function_parameters(
            array(
                'page' => new external_value(PARAM_INT, 'page'),
                'per_page' => new external_value(PARAM_INT, 'pageisze'),
                'roles' => new external_value(PARAM_TEXT, 'custom teacher roles')
            )
        );
    }

    public static function get_courses($page = 0, $perpage = 10, $customroles = '') {
        global $USER, $DB;

        $courses = local_tophat_cache::get_courses($customroles);

        $data = array_slice($courses, $page * $perpage, $perpage);
        $selfurl = self::make_web_service_endpoint('local_tophat_get_courses', array(
            'page' => $page,
            'per_page' => $perpage,
        ));

        $nexturl = self::make_web_service_endpoint('local_tophat_get_courses', array(
            'page' => $page + 1,
            'per_page' => $perpage,
        ));

        return [
            'data' => $data,
            'links' => [
                'next' => array(
                    'href' => $nexturl->out(false),
                ),
                'self' => array(
                    'href' => $selfurl->out(false),
                )
            ],
        ];
    }

    public static function get_courses_returns() {
        return new external_single_structure(array(
            'data' => new external_multiple_structure(
                new external_single_structure(
                    array(
                        'id' => new external_value(PARAM_INT, 'course id'),
                        'fullname' => new external_value(PARAM_TEXT, 'course fullname'),
                        'idnumber' => new external_value(PARAM_TEXT, 'course id number'),
                        'summary' => new external_value(PARAM_RAW, 'course summary'),
                        'summaryformat' => new external_value(PARAM_FORMAT, 'course summary text format'),
                    )
                )
            ),
            'links' => new external_single_structure(array(
                'next' => new external_single_structure(array(
                    'href' => new external_value(PARAM_URL, 'next url'),
                ), 'next page url', VALUE_OPTIONAL),
                'self' => new external_single_structure(array(
                    'href' => new external_value(PARAM_URL, 'self url'),
                ), 'self url'),
            ), 'links', VALUE_OPTIONAL)
        ));
    }

    public static function update_grade_parameters() {
        return new external_function_parameters([
            'courseid' => new external_value(PARAM_INT, 'course id'),
            'userid' => new external_value(PARAM_INT, 'user id'),
            'iteminstance' => new external_value(PARAM_INT, 'item instance'),
            'grade' => new external_value(PARAM_FLOAT, 'grade value'),
        ]);
    }

    public static function update_grade($courseid, $userid, $iteminstance, $gradeval) {
        global $DB, $CFG;
        local_tophat_capability::can_manage_grade($courseid);
        require_once($CFG->libdir.'/gradelib.php');

        $grade = new stdClass();
        $grade->userid = $userid;
        $grade->rawgrade = $gradeval;
        $result = 'fail';
        $source = "local/tophat";
        $status = grade_update($source, $courseid, 'mod', 'local_tophat', $iteminstance, 0, $grade);
        if (GRADE_UPDATE_OK == $status) {
            $result = 'success';
        }
        return [
            'meta' => [
                'result' => $result
            ]
        ];
    }

    public static function update_grade_returns() {
        return new external_single_structure([
            'meta' => new external_single_structure([
                'result' => new external_value(PARAM_ALPHANUMEXT, 'update status'),
            ])
        ]);
    }
    public static function create_grade_category_parameters() {
        return new external_function_parameters([
            'courseid' => new external_value(PARAM_INT, 'course id'),
            'parent' => new external_value(PARAM_INT, 'parent category id'),
            'fullname' => new external_value(PARAM_TEXT, 'category name'),
            'aggregateonlygraded' => new external_value(PARAM_BOOL, 'aggregate only graded'),
        ]);
    }

    public static function create_grade_category($courseid, $parentid, $fullname, $aggregateonlygraded) {
        local_tophat_capability::can_manage_grade($courseid);
        $gradecategory = new grade_category(['courseid' => $courseid], false);
        $gradeitem = new grade_item(['courseid' => $courseid, 'itemtype' => 'manual'], false);
        $gradecategory->apply_default_settings();
        $gradecategory->apply_forced_settings();
        if (empty($fullname)) {
            $fullname = 'Top Hat';
        }

        $data = new stdclass;
        $data->fullname = $fullname;
        $data->aggregateonlygraded = 1;
        if (empty($aggregateonlygraded)) {
            $data->aggregateonlygraded = 0;
        }
        grade_category::set_properties($gradecategory, $data);

        $category = $gradecategory->get_record_data();
        foreach ($gradeitem->get_record_data() as $key => $value) {
            $category->{"grade_item_$key"} = $value;
        }
        $gradecategory->insert();
        $gradecategory->set_parent($parentid, 'gradebook');
        $category = $gradecategory->get_record_data();
        return ['data' => $category];
    }

    public static function create_grade_category_returns() {
        return new external_function_parameters([
            'data' => new external_single_structure([
                'id' => new external_value(PARAM_INT, 'grade category id'),
                'courseid' => new external_value(PARAM_INT, 'course id'),
                'parent' => new external_value(PARAM_INT, 'parent category id'),
                'fullname' => new external_value(PARAM_TEXT, 'category name'),
                'aggregateonlygraded' => new external_value(PARAM_BOOL, 'aggregate only graded'),
            ])
        ]);
    }

    public static function delete_grade_category_parameters() {
        return new external_function_parameters([
            'courseid' => new external_value(PARAM_INT, 'course id'),
            'gradecategoryid' => new external_value(PARAM_INT, 'grade category id'),
        ]);
    }
    public static function delete_grade_category($courseid, $gradecategoryid) {
        local_tophat_capability::can_manage_grade($courseid);
        if (!$gradecategory = grade_category::fetch(array('id' => $gradecategoryid, 'courseid' => $courseid))) {
            throw new moodle_exception('invalidcategory');
        }
        $deleted = $gradecategory->delete('local/tophat');
        $result = 'fail';
        if (!empty($deleted)) {
            $result = 'success';
        }
        return [
            'meta' => [
                'result' => $result
            ]
        ];
    }
    public static function delete_grade_category_returns() {
        return new external_single_structure([
            'meta' => new external_single_structure([
                'result' => new external_value(PARAM_ALPHANUMEXT, 'update status'),
            ])
        ]);
    }

    public static function get_grade_item_parameters() {
        return new external_function_parameters([
            'courseid' => new external_value(PARAM_INT, 'course id'),
            'gradeitemid' => new external_value(PARAM_INT, 'grade item id'),
        ]);
    }

    public static function get_grade_item($courseid, $gradeitemid) {
        global $DB;
        local_tophat_capability::can_manage_grade($courseid);
        if ($gradeitem = grade_item::fetch(array('id' => $gradeitemid, 'courseid' => $courseid))) {
            $gradedata = $gradeitem->get_record_data();
            if (!empty($gradedata->scaleid)) {
                $scale = $DB->get_field('scale', 'scale', array('id' => $gradedata->scaleid));
                $gradedata->scale = $scale;
            }
            return ['data' => $gradedata];
        }
        return null;
    }

    public static function get_grade_item_returns() {
        return new external_function_parameters([
            'data' => new external_single_structure([
                'id' => new external_value(PARAM_INT, 'grade category id'),
                'courseid' => new external_value(PARAM_INT, 'course id'),
                'categoryid' => new external_value(PARAM_INT, 'category id'),
                'itemname' => new external_value(PARAM_TEXT, 'item name'),
                'iteminstance' => new external_value(PARAM_INT, 'item instance'),
                'itemnumber' => new external_value(PARAM_INT, 'item number'),
                'gradetype' => new external_value(PARAM_INT, 'grade type'),
                'grademax' => new external_value(PARAM_FLOAT, 'grade max'),
                'grademin' => new external_value(PARAM_FLOAT, 'grade min'),
                'scale' => new external_value(PARAM_TEXT, 'Scale', VALUE_OPTIONAL),
            ])
        ]);
    }


    public static function create_grade_item_parameters() {
        return new external_function_parameters([
            'courseid' => new external_value(PARAM_INT, 'course id'),
            'gradecategoryid' => new external_value(PARAM_INT, 'grade category id'),
            'itemname' => new external_value(PARAM_TEXT, 'grade item name'),
            'grademax' => new external_value(PARAM_FLOAT, 'grade max'),
            'grademin' => new external_value(PARAM_FLOAT, 'grade min'),
        ]);
    }

    public static function create_grade_item($courseid, $gradecategoryid, $itemname, $grademax, $grademin) {
        global $DB;

        local_tophat_capability::can_manage_grade($courseid);

        $params = [
            'courseid' => $courseid,
            'itemtype' => 'mod',
            'itemmodule' => 'local_tophat',
            'itemnumber' => 0
        ];
        $gradeitem = new grade_item($params, false);
        $item = $gradeitem->get_record_data();
        $item->itemname = $itemname;
        $item->gradetype = 1;

        if ($gradecategoryid === 0) {
            $parentcategory = grade_category::fetch_course_category($courseid);
        } else {
            $parentcategory = grade_category::fetch(array('id' => $gradecategoryid));
            if (!$parentcategory) {
                throw new moodle_exception('invalidgradecategoryid', 'local_tophat');
            }
        }
        $item->parentcategory = $parentcategory->id;

        if ($parentcategory->aggregation == GRADE_AGGREGATE_SUM or
            $parentcategory->aggregation == GRADE_AGGREGATE_WEIGHTED_MEAN2) {
            $item->aggregationcoef = $item->aggregationcoef == 0 ? 0 : 1;
        } else {
            $item->aggregationcoef = format_float($item->aggregationcoef, 4);
        }

        if ($parentcategory->aggregation == GRADE_AGGREGATE_SUM) {
            $item->aggregationcoef2 = format_float($item->aggregationcoef2 * 100.0);
        }

        $defaults = grade_category::get_default_aggregation_coefficient_values($parentcategory->aggregation);
        if (!isset($data->aggregationcoef) || $data->aggregationcoef == '') {
            $item->aggregationcoef = $defaults['aggregationcoef'];
        }
        if (!isset($data->weightoverride)) {
            $item->weightoverride = $defaults['weightoverride'];
        }

        $item->scaleid = null;

        $decimalpoints = $gradeitem->get_decimals();

        $item->grademax = format_float($grademax, $decimalpoints);
        $item->grademin = format_float($grademin, $decimalpoints);

        grade_item::set_properties($gradeitem, $item);
        $id = $gradeitem->insert();
        if ($gradeitem = grade_item::fetch(array('id' => $id, 'courseid' => $courseid))) {
            if (isset($item->parentcategory)) {
                $gradeitem->set_parent($item->parentcategory, false);
            }
            $gradedata = $gradeitem->get_record_data();
            $DB->set_field('grade_items', 'iteminstance', $gradedata->id, array('id' => $gradedata->id));
            return ['data' => $gradedata];
        }
        return ['data' => []];
    }
    public static function create_grade_item_returns() {
        return new external_function_parameters([
            'data' => new external_single_structure([
                'id' => new external_value(PARAM_INT, 'grade category id'),
                'courseid' => new external_value(PARAM_INT, 'course id'),
                'categoryid' => new external_value(PARAM_INT, 'category id'),
                'itemname' => new external_value(PARAM_TEXT, 'item name'),
                'grademax' => new external_value(PARAM_FLOAT, 'grade max'),
                'grademin' => new external_value(PARAM_FLOAT, 'grade min'),
            ])
        ]);
    }

    public static function delete_grade_item_parameters() {
        return new external_function_parameters([
            'courseid' => new external_value(PARAM_INT, 'course id'),
            'gradeitemid' => new external_value(PARAM_INT, 'grade category id'),
        ]);
    }

    public static function delete_grade_item($courseid, $gradeitemid) {
        local_tophat_capability::can_manage_grade($courseid);
        if (!$gradeitem = grade_item::fetch(array('id' => $gradeitemid, 'courseid' => $courseid))) {
            throw new moodle_exception('invalidcategory');
        }
        $deleted = $gradeitem->delete('local/tophat');
        $result = 'fail';
        if (!empty($deleted)) {
            $result = 'success';
        }
        return [
            'meta' => [
                'result' => $result
            ]
        ];
    }

    public static function delete_grade_item_returns() {
        return new external_single_structure([
            'meta' => new external_single_structure([
                'result' => new external_value(PARAM_ALPHANUMEXT, 'delete grade item status'),
            ])
        ]);
    }

    public static function validate_token_parameters() {
        return new external_function_parameters([]);
    }

    public static function validate_token() {
        global $USER;
        return [
            'data' => $USER,
        ];
    }

    public static function validate_token_returns() {
        return new external_single_structure([
            'data' => new external_single_structure([
                'id' => new external_value(PARAM_INT, 'user id'),
                'username' => new external_value(PARAM_USERNAME, 'username'),
                'firstname' => new external_value(PARAM_TEXT, 'firstname'),
                'lastname' => new external_value(PARAM_TEXT, 'lastname'),
                'email' => new external_value(PARAM_EMAIL, 'email'),
            ])
        ]);
    }
}
