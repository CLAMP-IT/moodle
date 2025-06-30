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

defined('MOODLE_INTERNAL') || die();

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
        $exfilters = array_filter(explode(',', $exfilters), function($info) use($name) {
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
    public $courses = array();
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

/**
 * Utility functions
 *
 * @package    block_filtered_course_list
 * @copyright  2017 CLAMP
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_filtered_course_list_lib {
    public static function get_filter_files() {
        global $CFG;

        $files = [];
        $dir = new RecursiveDirectoryIterator($CFG->dirroot);
        $flt = new RecursiveCallbackFilterIterator($dir, function($current, $key, $iterator) {
            if ($current->getFilename()[0] === '.') {
                return false;
            }
            return true;
        });
        $itr = new RecursiveIteratorIterator($flt);
        foreach ($itr as $file) {
            if (preg_match('/.*fcl_filter\.php$/', $file)) {
                $files[] = $file;
            }
        }
        return $files;
    }

    public static function get_filter_classes() {
        $exfilters = array_filter(get_declared_classes(), function($class) {
            return preg_match('/.*fcl_filter/', $class);
        });
        return $exfilters;
    }

    /**
     * Display a coursename according to the template
     *
     * @param object $course An object with all of the course attributes
     * @param string $tpl The coursename display template
     */
    public static function coursedisplaytext($course, $tpl) {
        if ($tpl == '') {
            $tpl = 'FULLNAME';
        }
        $cat = core_course_category::get($course->category, IGNORE_MISSING);
        $catname = (is_object($cat)) ? $cat->name : '';
        $replacements = array(
            'FULLNAME'  => $course->fullname,
            'SHORTNAME' => $course->shortname,
            'IDNUMBER'  => $course->idnumber,
            'CATEGORY'  => $catname,
        );
        // If we have limits defined, apply them.
        static::apply_template_limits($replacements, $tpl);
        $displaytext = str_replace(array_keys($replacements), $replacements, $tpl);
        return format_string(strip_tags($displaytext));
    }

    /**
     * Apply length limits to a template string. TOKEN{#} in the template string
     * is replaced by TOKEN, and the replacement value for TOKEN is truncated to
     * # characters.
     *
     * @param object $replacements an array of pattern => replacement
     * @param string $tpl the template string (coursename or category)
     */
    public static function apply_template_limits(&$replacements, &$tpl) {
        $limitpattern = "{(\d+)}";
        foreach ($replacements as $pattern => $replace) {
            $limit = array();
            if (preg_match("/$pattern$limitpattern/", $tpl, $limit)) {
                $replacements[$pattern] = static::truncate($replace, (int) $limit[1]);
            }
        }
        $tpl = preg_replace("/$limitpattern/", "", $tpl);
    }

    /**
     * Ellipsis truncate the given string to $length characters.
     *
     * @param string $string the string to be truncated
     * @param int $length the number of characters to truncate to
     * @return $string the truncated string
     */
    public static function truncate($string, $length) {
        if ($length > 0 && \core_text::strlen($string) > $length) {
            $string = \core_text::substr($string, 0, $length);
            $string = trim($string);
            $string .= "…";
        }
        return $string;
    }
}
