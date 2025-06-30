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
 * This file contains the class used to handle completion filters.
 *
 * @package    block_filtered_course_list
 * @copyright  2018 CLAMP
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_filtered_course_list;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/blocks/filtered_course_list/locallib.php');

/**
 * A class to construct a rubric based on course completion
 *
 * @package    block_filtered_course_list
 * @copyright  2016 CLAMP
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class completion_filter extends \block_filtered_course_list\filter {
    /**
     * Retrieve filter short name.
     *
     * @return string This filter's shortname.
     */
    public static function getshortname() {
        return 'completion';
    }

    /**
     * Retrieve filter full name.
     *
     * @return string This filter's shortname.
     */
    public static function getfullname() {
        return 'Course Completion';
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
        $keys = array('expanded', 'label', 'completionstate');
        $values = array_map(function($item) {
            return trim($item);
        }, explode('|', $line[1]));
        $this->validate_expanded(0, $values);
        if (!array_key_exists(1, $values)) {
            $values[1] = get_string('completedcourses', 'block_filtered_course_list');
        }
        if (!array_key_exists(2, $values)) {
            $values[2] = 'complete';
        }
        $values[2] = (\core_text::strpos($values[2], 'c') === 0) ? '1' : '0';
        return array_combine($keys, $values);
    }

    /**
     * Populate the array of rubrics for this filter type
     *
     * @return array The list of rubric objects corresponding to the filter
     */
    public function get_rubrics() {
        global $USER;

        if (!\completion_info::is_enabled_for_site()) {
            return null;
        }

        $courselist = array_filter($this->courselist, function($course) use($USER) {
            $completioninfo = new \completion_info($course);
            if (!$completioninfo->is_enabled()) {
                return false;
            }
            return ($completioninfo->is_course_complete($USER->id) == $this->line['completionstate']);
        });
        if (empty($courselist)) {
            return null;
        }

        $this->rubrics[] = new \block_filtered_course_list_rubric($this->line['label'], $courselist,
                                                                    $this->config, $this->line['expanded']);
        return $this->rubrics;
    }
}
