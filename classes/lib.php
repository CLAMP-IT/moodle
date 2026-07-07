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
 * Contains class block_filtered_course_list\lib
 *
 * @package    block_filtered_course_list
 * @copyright  2025 CLAMP
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_filtered_course_list;

use core_course_category;

/**
 * Utility functions
 *
 * @package    block_filtered_course_list
 * @copyright  2017 CLAMP
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class lib {
    /**
     * Return all files implementing filters.
     *
     * @return array files
     */
    public static function get_filter_files() {
        global $CFG;

        $files = [];
        $dir = new \RecursiveDirectoryIterator($CFG->dirroot);
        $flt = new \RecursiveCallbackFilterIterator($dir, function ($current, $key, $iterator) {
            if ($current->getFilename()[0] === '.') {
                return false;
            }
            return true;
        });
        $itr = new \RecursiveIteratorIterator($flt);
        foreach ($itr as $file) {
            if (preg_match('/.*fcl_filter\.php$/', $file)) {
                $files[] = $file;
            }
        }
        return $files;
    }

    /**
     * Return all filter classes.
     *
     * @return array classes
     */
    public static function get_filter_classes() {
        $exfilters = array_filter(get_declared_classes(), function ($class) {
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
        $replacements = [
            'FULLNAME'  => $course->fullname,
            'SHORTNAME' => $course->shortname,
            'IDNUMBER'  => $course->idnumber,
            'CATEGORY'  => $catname,
        ];
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
            $limit = [];
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
