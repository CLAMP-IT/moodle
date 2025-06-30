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
 * This file contains the class used to handle generic filters.
 *
 * @package    block_filtered_course_list
 * @copyright  2018 CLAMP
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_filtered_course_list;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/blocks/filtered_course_list/locallib.php');

/**
 * A class to construct a rubric for generic course and category lists
 *
 * @package    block_filtered_course_list
 * @copyright  2016 CLAMP
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class generic_filter extends \block_filtered_course_list\filter {
    /**
     * Retrieve filter short name.
     *
     * @return string This filter's shortname.
     */
    public static function getshortname() {
        return 'generic';
    }

    /**
     * Retrieve filter full name.
     *
     * @return string This filter's shortname.
     */
    public static function getfullname() {
        return 'Generic';
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
        $keys = array('expanded', 'courselistheading', 'catlistheading');
        $values = array_map(function($item) {
            return trim($item);
        }, explode('|', $line[1]));
        $this->validate_expanded(0, $values);
        if (!array_key_exists(1, $values)) {
            $values[1] = get_string('courses');
            $values[1] = strip_tags($values[1]);
        }
        if (!array_key_exists(2, $values)) {
            $values[2] = get_string('categories');
            $values[2] = strip_tags($values[2]);
        }
        return array_combine($keys, $values);
    }

    /**
     * Populate the array of rubrics for this filter type
     *
     * @return array The list of rubric objects corresponding to the filter
     */
    public function get_rubrics() {

        // Parent = 0   ie top-level categories only.
        $categories = \core_course_category::get(0)->get_children();

        $expanded = $this->line['expanded'];

        if ($categories) {
            // Just print top level category links.
            if (count($categories) > 1 ||
                    (count($categories) == 1 &&
                    current($categories)->coursecount > $this->config->maxallcourse)) {
                $label = $this->line['catlistheading'];
                $list = $categories;
                $this->rubrics[] = new \block_filtered_course_list_rubric($label, $list, $this->config, $expanded);
            } else {
                // Just print course names of single category.
                $category = array_shift($categories);
                $courses = get_courses($category->id);
                if ($courses) {
                    $label = $this->line['courselistheading'];
                    $list = $courses;
                    $this->rubrics[] = new \block_filtered_course_list_rubric($label, $list, $this->config, $expanded);
                }
            }
        }

        return $this->rubrics;
    }
}
