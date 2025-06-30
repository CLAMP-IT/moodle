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
 * This file contains an example filter for testing.
 *
 * @package    block_filtered_course_list
 * @copyright  2018 CLAMP
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once(get_config('core', 'dirroot') . '/blocks/filtered_course_list/locallib.php');

/**
 * A class to construct rubrics
 *
 * @package    block_filtered_course_list
 * @copyright  2018 CLAMP
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class test_fcl_filter extends \block_filtered_course_list\filter {

    /** Retrieve filter short name.
     *
     * @return string The shortname of this filter (e.g. shortname, category)
     */
    public static function getshortname() {
        return 'test';
    }

    /**
     * Retrieve filter full name.
     *
     * @return string The fullname of this filter (e.g. Shortname, Course Category )
     */
    public static function getfullname() {
        return 'Test';
    }

    /**
     * Retrieve filter component.
     *
     * @return string The component of this filter (e.g. block_filtered_course_list)
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
        $keys = array('expanded', 'label');
        $values = array_map(function($item) {
            return trim($item);
        }, explode('|', $line[1], 2));
        $this->validate_expanded(0, $values);
        if (!array_key_exists(1, $values)) {
            $values[1] = 'Test Filter Default Label';
        }
        return array_combine($keys, $values);
    }

    /**
     * Populate the array of rubrics for this filter type
     *
     * @return array The list of rubric objects corresponding to the filter
     */
    public function get_rubrics() {
        $courselist = $this->courselist;

        $this->rubrics[] = new \block_filtered_course_list_rubric($this->line['label'],
                            $courselist, $this->config, $this->line['expanded']);
        return $this->rubrics;
    }
}
