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
 * This file contains the class used to handle category filters.
 *
 * @package    block_filtered_course_list
 * @copyright  2018 CLAMP
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_filtered_course_list;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/blocks/filtered_course_list/locallib.php');

/**
 * A class to construct rubrics based on category structure
 *
 * @package    block_filtered_course_list
 * @copyright  2016 CLAMP
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class category_filter extends \block_filtered_course_list\filter {
    /**
     * Retrieve filter short name.
     *
     * @return string This filter's shortname.
     */
    public static function getshortname() {
        return 'category';
    }

    /**
     * Retrieve filter full name.
     *
     * @return string This filter's shortname.
     */
    public static function getfullname() {
        return 'Course Category';
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
        $keys = array('expanded', 'catid', 'depth');
        $values = array_map(function($item) {
            return trim($item);
        }, explode('|', $line[1]));
        $this->validate_expanded(0, $values);
        foreach (array(1, 2) as $key) {
            if (!array_key_exists($key, $values)) {
                $values[$key] = '0';
            }
            $values[$key] = current(explode(" ", $values[$key]));
            if (!is_numeric($values[$key])) {
                $values[$key] = '0';
            }
        }
        return array_combine($keys, array_slice($values, 0, 3));
    }

    /**
     * Populate the array of rubrics for this filter type
     *
     * @return array The list of rubric objects corresponding to the filter
     */
    public function get_rubrics() {

        // We only need this for Moodle < 3.4.
        global $CFG;
        $moodleversion = $CFG->version;

        $categories = $this->_get_cat_and_descendants($this->line['catid'], $this->line['depth']);
        foreach ($categories as $category) {
            $rubricname = $category->name;
            if (isset($this->config->catrubrictpl) && $this->config->catrubrictpl != '') {
                $parent = '';
                if ($parentobj = \core_course_category::get($category->parent, IGNORE_MISSING)) {
                    $parent = $parentobj->get_formatted_name();
                }
                $separator = ' / ';
                if (isset($this->config->catseparator) && $this->config->catseparator != '') {
                    $separator = strip_tags($this->config->catseparator);
                }
                $ancestors = \core_course_category::make_categories_list('', 0, $separator);
                $ancestry = isset($ancestors[$category->id]) ? $ancestors[$category->id] : '';
                $replacements = array(
                    'NAME'     => $category->name,
                    'IDNUMBER' => $category->idnumber,
                    'PARENT'   => $parent,
                    'ANCESTRY' => $ancestry,
                );
                $tpl = $this->config->catrubrictpl;
                \block_filtered_course_list_lib::apply_template_limits($replacements, $tpl);
                $rubricname = str_replace(array_keys($replacements), $replacements, $tpl);
                $rubricname = strip_tags($rubricname);
            }
            $courselist = array_filter($this->courselist, function($course) use($category) {
                return ($course->category == $category->id);
            });
            if (empty($courselist)) {
                continue;
            }
            $this->rubrics[] = new \block_filtered_course_list_rubric($rubricname, $courselist,
                                                                $this->config, $this->line['expanded']);
        }

        return $this->rubrics;
    }

    /**
     * Fetch a category and all descendants visible to current usertype
     *
     * @param int $catid The id number of the category to fetch
     * @param int $depth How many generations of categories to show
     * @param array $accumulator An accumulator passed by reference to store the recursive results
     * @return array of \core_course_category objects
     */
    protected function _get_cat_and_descendants($catid=0, $depth=0, &$accumulator=array()) {

        $cats = Array();

        if ($category = \core_course_category::get($catid, IGNORE_MISSING, true)) {

            $allchildren = \core_course_category::get_many($category->get_all_children_ids());
            array_unshift($allchildren, $category);

            $visiblecats = array_filter($allchildren, function($cat) {
                return $cat->is_uservisible();
            });

            $cats = array_filter($visiblecats, function($cat) use($depth, $category) {
                if ($depth == 0) {
                    return true;
                }
                if ($category->id == 0) {
                    $depth++;
                }
                return $cat->depth - $category->depth < $depth;
            });
        }

        return $cats;
    }
}
