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

class config {

    /**
     * Load the block configuration.
     *
     * @param stdClass $course the course object.
     * @return array
     */
    public static function load_configuration($course) {
        global $DB;

        $fields = 'name,value';
        $params = array('coursesid' => $course->id);
        $config = $DB->get_records_menu('block_clampmail_config', $params, '', $fields);

        if (empty($config)) {
            $config = self::load_default_configuration();
        }

        // Respect groupmodeforce.
        if ($course->groupmodeforce == 1) {
            $config['groupmode'] = $course->groupmode;
        }

         return $config;
    }

    /**
     * Load the default system configuration.
     *
     * @return array
     */
    public static function load_default_configuration() {
        $config = array(
            'roleselection' => get_config('block_clampmail', 'roleselection'),
            'prepend_class' => get_config('block_clampmail', 'prepend_class'),
            'receipt' => get_config('block_clampmail', 'receipt'),
            'groupmode' => get_config('block_clampmail', 'groupmode')
        );

        return $config;
    }

    /**
     * Restore default configuration for the block.
     *
     * @param int courseid The course id.
     */
    public static function reset_course_configuration($courseid) {
            global $DB;
            $params = array('coursesid' => $courseid);
            $DB->delete_records('block_clampmail_config', $params);
    }

    /**
     * Save the configuration for a block.
     *
     * @param int $courseid the course.
     * @param array $data configuration settings.
     */
    public static function save_configuration($courseid, $data) {
        global $DB;

        // Clear values.
        self::reset_course_configuration($courseid);

        foreach ($data as $name => $value) {
            $config = new \stdClass;
            $config->coursesid = $courseid;
            $config->name = $name;
            $config->value = $value;

            $DB->insert_record('block_clampmail_config', $config);
        }
    }
}
