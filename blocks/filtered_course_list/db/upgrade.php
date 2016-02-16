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
 * This file keeps track of upgrades to the filtered course list block
 *
 * @since 2.5
 * @package block_filtered_course_list
 * @copyright 2014 Kevin Wiliarty
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Upgrade code for the section links block.
 *
 * @param int $oldversion
 * @return bool
 */
function xmldb_block_filtered_course_list_upgrade($oldversion) {

    // Moodle v2.3.0 release upgrade line
    // Put any upgrade step following this.

    // Moodle v2.4.0 release upgrade line
    // Put any upgrade step following this.

    // Moodle v2.5.0 release upgrade line
    // Put any upgrade step following this.

    if ($oldversion < 2014010601) {

        $oldfiltertype = get_config('moodle', 'block_filtered_course_list/filtertype');
        if ($oldfiltertype == 'term') {
            set_config('block_filtered_course_list/filtertype', 'shortname');
        }

        $oldtermcurrent = get_config('moodle', 'block_filtered_course_list_termcurrent');
        if (!empty($oldtermcurrent)) {
            set_config('block_filtered_course_list_currentshortname', $oldtermcurrent);
            unset_config('block_filtered_course_list_termcurrent');
        }

        $oldtermfuture = get_config('moodle', 'block_filtered_course_list_termfuture');
        if (!empty($oldtermfuture)) {
            set_config('block_filtered_course_list_futureshortname', $oldtermfuture);
            unset_config('block_filtered_course_list_termfuture');
        }

        // Main savepoint reached.
        upgrade_block_savepoint(true, 2014010601, 'filtered_course_list');

    }

    // Moodle v2.6.0 release upgrade line.
    // Put any upgrade step following this.

    if ($oldversion < 2015102002) {

        $fclsettings = array(
            'filtertype',
            'hideallcourseslink',
            'hidefromguests',
            'hideothercourses',
            'useregex',
            'currentshortname',
            'currentexpanded',
            'futureshortname',
            'futureexpanded',
            'labelscount',
            'categories',
            'adminview',
            'maxallcourse',
            'collapsible',
        );

        $customrubrics = array(
            'customlabel',
            'customshortname',
            'labelexpanded',
        );

        foreach ($fclsettings as $name) {
            $value = get_config('moodle', 'block_filtered_course_list_' . $name);
            set_config($name, $value, 'block_filtered_course_list');
            unset_config('block_filtered_course_list_' . $name);
        }

        for ($i = 1; $i <= 10; $i++) {
            foreach ($customrubrics as $setting) {
                $name = $setting . $i;
                $value = get_config('moodle', 'block_filtered_course_list_' . $name);
                if (!empty($value)) {
                    set_config($name, $value, 'block_filtered_course_list');
                    unset_config('block_filtered_course_list_' . $name);
                }
            }
        }

        // Main savepoint reached.
        upgrade_block_savepoint(true, 2015102002, 'filtered_course_list');

    }

    return true;
}
