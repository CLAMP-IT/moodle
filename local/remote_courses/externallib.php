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
 * @package    local_remote_courses
 * @copyright  2015 Lafayette College ITS
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once("$CFG->dirroot/enrol/externallib.php");

class local_remote_courses_external extends external_api {
    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function get_courses_by_username_parameters() {
        return new external_function_parameters(
                array(
                    'username' => new external_value(PARAM_USERNAME, 'username'),
                )
        );
    }

    /**
     * Get a user's enrolled courses
     * This is a wrapper of core_enrol_get_users_courses(). It accepts
     * the username instead of the id and does some optional filtering
     * logic on the idnumber.
     *
     * @param string $username
     * @return array
     */
    public static function get_courses_by_username($username) {
        global $DB, $USER;

        // Validate parameters passed from webservice.
        $params = self::validate_parameters(self::get_courses_by_username_parameters(), array('username' => $username));

        // Extract the userid from the username.
        $userid = $DB->get_field('user', 'id', array('username' => $username));

        // Get the courses.
        $courses = core_enrol_external::get_users_courses($userid);

        // Process results: apply term logic and drop enrollment counts.
        $result = array();
        foreach ($courses as $course) {
            // Apply term logic.
            $term = '';
            if (!empty($course['idnumber'])) {
                $term = substr($course['idnumber'], strrpos($course['idnumber'], '.') + 1);
            }

            $result[] = array(
                'id' => $course['id'],
                'shortname' => $course['shortname'],
                'fullname' => $course['fullname'],
                'term' => $term,
                'visible' => $course['visible']
            );
        }

        // Sort courses by recent access.
        $courselist = array_keys($DB->get_records_sql('SELECT course, MAX(time) as recent FROM {log}
            WHERE userid = ? AND course != 1 GROUP BY course
            ORDER BY recent DESC', array($userid)));
        $unsorted = $result;
        $sorted = array();
        foreach ($result as $cid => $course) {
            $sort = array_search($course['id'], $courselist);
            if ($sort !== false) {
                $sorted[$sort] = $course;
                unset($unsorted[$cid]);
            }
        }

        ksort($sorted);
        $result = array_merge($sorted, $unsorted);

        return $result;
    }

    public static function get_courses_by_username_returns() {
        return new external_multiple_structure(
            new external_single_structure(
                array(
                    'id'        => new external_value(PARAM_INT, 'id of course'),
                    'shortname' => new external_value(PARAM_RAW, 'short name of course'),
                    'fullname'  => new external_value(PARAM_RAW, 'long name of course'),
                    'term'  => new external_value(PARAM_RAW, 'the course term, if applicable'),
                    'visible'   => new external_value(PARAM_INT, '1 means visible, 0 means hidden course')
                )
            )
        );
    }
}
