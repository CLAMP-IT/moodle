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
 * @package    local_metasync
 * @copyright  2014 Paul Holden (pholden@greenhead.ac.uk)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_metasync;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/local/metasync/locallib.php');

class observers {

    /**
     * Enrolment added
     *
     * @param \core\event\user_enrolment_created $event
     * @return void
     */
    public static function user_enrolment_created(\core\event\user_enrolment_created $event) {
        global $DB;

        if (!enrol_is_enabled('meta')) {
            // No more enrolments for disabled plugins.
            return true;
        }

        if ($event->other['enrol'] === 'meta') {
            // Prevent circular dependencies - we can not sync meta enrolments recursively.
            return true;
        }

        $userid = $event->relateduserid;

        $parentcourseids = local_metasync_parent_courses($event->courseid);
        foreach ($parentcourseids as $courseid) {
            $course = get_course($courseid);

            if ($metagroup = $DB->get_record('groups', array('courseid' => $course->id, 'idnumber' => $event->courseid))) {
                groups_add_member($metagroup, $userid, 'local_metasync', $event->courseid);
            }
        }
    }

    /**
     * Enrolment removed
     *
     * @param \core\event\user_enrolment_deleted $event
     * @return void
     */
    public static function user_enrolment_deleted(\core\event\user_enrolment_deleted $event) {
        global $DB;

        if (!enrol_is_enabled('meta')) {
            // No more enrolments for disabled plugins.
            return true;
        }

        if ($event->other['enrol'] === 'meta') {
            // Prevent circular dependencies - we can not sync meta enrolments recursively.
            return true;
        }

        $userid = $event->relateduserid;

        $parentcourseids = local_metasync_parent_courses($event->courseid);
        foreach ($parentcourseids as $courseid) {
            $course = get_course($courseid);

            if ($metagroup = $DB->get_record('groups', array('courseid' => $course->id, 'idnumber' => $event->courseid))) {
                groups_remove_member($metagroup, $userid);
            }
        }
    }
}
