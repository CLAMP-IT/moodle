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
 * @package   block_clampmail
 * @copyright 2017 Collaborative Liberal Arts Moodle Project
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_clampmail;

defined('MOODLE_INTERNAL') || die();

class groups {
    /**
     * Return the available groups for the course depending on the mode and user.
     *
     * @param int $groupmode the group mode.
     * @param int $courseid the course id.
     *
     * @return array
     */
    public static function get_groups($groupmode, $courseid) {
        global $DB;

        $allgroups = groups_get_all_groups($courseid);
        $context   = \context_course::instance($courseid);

        switch ($groupmode) {
            case NOGROUPS:
                $groups = array();
                break;
            case VISIBLEGROUPS:
                $groups = $allgroups;
                break;
            case SEPARATEGROUPS:
                if (has_capability('block/clampmail:cansendtoall', $context)) {
                    $groups = $allgroups;
                } else {
                    $mygroups = groups_get_user_groups($courseid);
                    $gids = implode(',', array_values($mygroups['0']));
                    $groups = empty($gids) ? array() : $DB->get_records_select('groups', 'id IN ('.$gids.')');
                }
                break;
        }
        return $groups;
    }
}
