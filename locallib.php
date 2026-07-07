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
 * This file defines constants and classes used by the Filtered course list block.
 *
 * @package    block_filtered_course_list
 * @copyright  2016 CLAMP
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('BLOCK_FILTERED_COURSE_LIST_ADMIN_VIEW_ALL', 'all');
define('BLOCK_FILTERED_COURSE_LIST_ADMIN_VIEW_OWN', 'own');
define('BLOCK_FILTERED_COURSE_LIST_DEFAULT_LABELSCOUNT', 2);
define('BLOCK_FILTERED_COURSE_LIST_DEFAULT_CATEGORY', 0);
define('BLOCK_FILTERED_COURSE_LIST_EMPTY', '');
define('BLOCK_FILTERED_COURSE_LIST_FALSE', 0);
define('BLOCK_FILTERED_COURSE_LIST_FILTER_VERSION_SYNC_NUMBER', '1.0.0');
define('BLOCK_FILTERED_COURSE_LIST_TRUE', 1);
define('BLOCK_FILTERED_COURSE_LIST_GENERIC_CONFIG', 'generic|e');

/**
 * Get the name of the filter corresponding to a configuration line.
 *
 * @param string $name Putative filter name
 * @param array $exfilters List of external filters designated in config
 * @return string Filtername or null
 */
function get_filter($name, $exfilters) {
    global $CFG;

    if (empty($name)) {
        return null;
    }
    // Assume base filter.
    $classname = "\\block_filtered_course_list\\{$name}_filter";
    // If not base filter, look for external filter.
    if (!class_exists($classname)) {
        // Find the filter we're looking for.
        $exfilters = array_filter(explode(',', $exfilters), function ($info) use ($name) {
            return strpos($info, "$name|") === 0;
        });
        // Abort if filter not found.
        if (empty($exfilters)) {
            return null;
        }
        // Split out filter info.
        $filterinfo = explode('|', $exfilters[0]);
        $path = $CFG->dirroot . $filterinfo[2];

        // Check that path exists.
        if (file_exists($path)) {
            // Set class name.
            $classname = "{$name}_fcl_filter";
            // Require path.
            require_once($path);
        } else {
            $classname = false;
        }
    }
    return $classname;
}

/**
 * A class to structure rubrics regardless of their config type
 *
 * @package    block_filtered_course_list
 * @copyright  2016 CLAMP
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_filtered_course_list_rubric {
    /** @var string The rubric's title */
    public $title;
    /** @var array The subset of enrolled courses that match the filter criteria */
    public $courses = [];
    /** @var string Indicates whether the rubric is expanded or collapsed by default */
    public $expanded;
    /** @var array Config settings */
    public $config;

    /**
     * Constructor
     *
     * @param string $title The display title of the rubric
     * @param array $courses Courses the user is enrolled in that match the Filtered
     * @param array $config Block configuration
     * @param string $expanded Indicates the rubrics initial state: expanded or collapsed
     */
    public function __construct($title, $courses, $config, $expanded = false) {
        $this->title = format_string(htmlspecialchars($title));
        $this->courses = $courses;
        $this->config = $config;
        $this->expanded = $expanded;
    }
}
