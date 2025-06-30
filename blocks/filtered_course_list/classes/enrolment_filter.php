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
 * This file contains the class used to filter courses by enrolment methods.
 *
 * @package    block_filtered_course_list
 * @copyright  2018 CLAMP
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_filtered_course_list;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/blocks/filtered_course_list/locallib.php');

/**
 * A class to construct rubric showing courses filtered by enrolment methods.
 *
 * @package    block_filtered_course_list
 * @copyright  2016 CLAMP
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class enrolment_filter extends \block_filtered_course_list\filter {
    /**
     * Retrieve filter short name.
     *
     * @return string This filter's shortname.
     */
    public static function getshortname() {
        return 'enrolment';
    }

    /**
     * Retrieve filter full name.
     *
     * @return string This filter's shortname.
     */
    public static function getfullname() {
        return 'Enrolment filter';
    }

    /**
     * Retrieve filter component.
     *
     * @return string This filter's component.
     */
    public static function getcomponent() {
        return 'block_filtered_course_list';
    }

    /**
     * Retrieve filter version sync number.
     *
     * @return string This filter's version sync number.
     */
    public static function getversionsyncnum() {
        return BLOCK_FILTERED_COURSE_LIST_FILTER_VERSION_SYNC_NUMBER;
    }

    /**
     * Validate the line
     *
     * @param array $line The array of line elements that has been passed to the constructor
     * @return array A fixed-up line array
     */
    public function validate_line($line) {
        $keys = array('expanded', 'enrol', 'label');
        $values = array_map(function($item) {
            return trim($item);
        }, explode('|', $line[1], 3));
        $this->validate_expanded(0, $values);
        if (!array_key_exists(1, $values)) {
            $values[1] = array('guest');
        } else {
            $values[1] = array_map(function($item) {
                return trim($item);
            }, explode(',', $values[1]));
            $values[1] = array_intersect(
                $values[1],
                array_keys(\core_component::get_plugin_list('enrol'))
            );
        }
        if (!array_key_exists(2, $values)) {
            $methods = implode('/', $values[1]);
            $values[2] = get_string('courseswithxenrolment', 'block_filtered_course_list', $methods);
        }
        return array_combine($keys, $values);
    }

    /**
     * Populate the array of rubrics for this filter type
     *
     * @return array The list of rubric objects corresponding to the filter
     */
    public function get_rubrics() {
        global $DB;

        $where = implode(' OR ', array_map(function($enrol) {
            return "enrol = '$enrol'";
        }, $this->line['enrol']));
        $where = "($where) AND status = 0";

        $candidates = $DB->get_fieldset_select('enrol', 'courseid', $where);

        $courselist = array_map(function($courseid) use($DB) {
            return $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
        }, array_unique($candidates));

        if (empty($courselist)) {
            return null;
        }
        $this->rubrics[] = new \block_filtered_course_list_rubric($this->line['label'],
                                        $courselist, $this->config, $this->line['expanded']);
        return $this->rubrics;
    }
}
