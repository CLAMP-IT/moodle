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
            /**
             * When adding a metalink, there's no event for that. All you see is each metaenrolment. 
             * When we see one, check to see if we have a group already made for that linked course.
             * If we don't have one, loop through and make them.
             */
            $children = local_metasync_child_courses($event->courseid);
            foreach ($children as $childid) {
                $child = get_course($childid);
                if (! $metagroup = $DB->get_record('groups', array('courseid' => $event->courseid, 'idnumber' => $child->id))) {
                    $metagroup = new \stdClass();
                    $metagroup->courseid = $event->courseid;
                    $metagroup->idnumber = $child->id;
                    $metagroup->name = $child->shortname;

                    $metagroup->id = \groups_create_group($metagroup, false, false);
                }
                // If event user is enrolled in this child course, add to this group.
                $coursecontext = \context_course::instance($child->id);
                $user = \core_user::get_user($event->relateduserid);
                if (\is_enrolled($coursecontext, $user)) {
                   $newgroup = $DB->get_record('groups', array('courseid' => $event->courseid, 'idnumber' => $child->id));
                   \groups_add_member($newgroup, $event->relateduserid, 'local_metasync', $child->id);
                }
            }
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
