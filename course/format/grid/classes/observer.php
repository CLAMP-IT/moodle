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
 * Grid Format - A topics based format that uses a grid of user selectable images to popup a light box of the section.
 *
 * @package    course/format
 * @subpackage grid
 * @category   event
 * @copyright  &copy; 2017-onwards G J Barnard based upon work done by Marina Glancy.
 * @author     G J Barnard - gjbarnard at gmail dot com, about.me/gjbarnard and {@link http://moodle.org/user/profile.php?id=442195}
 * @author     Based on code originally written by Paul Krix and Julian Ridden.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Event observers supported by this format.
 */
class format_grid_observer {

    /**
     * Observer for the event course_content_deleted.
     *
     * Deletes the settings entry for the given course upon course deletion.
     *
     * @param \core\event\course_content_deleted $event
     */
    public static function course_content_deleted(\core\event\course_content_deleted $event) {
        if (class_exists('format_grid', false)) {
            // If class format_grid was never loaded, this is definitely not a course in 'Grid' format.
            global $DB;
            /* Delete any images associated with the course.
               Done this way so will work if the course has
               been a grid format course in the past even if
               it is not now. */
            $courseformat = format_grid::get_instance($event->objectid);
            $courseformat->delete_images();
            unset($courseformat);  // Destruct.

            $DB->delete_records("format_grid_summary", array("courseid" => $event->objectid));
        }
    }
}
