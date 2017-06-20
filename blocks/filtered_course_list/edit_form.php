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
 * This file defines the form for editing block instances.
 *
 * @package    block_filtered_course_list
 * @copyright  2016 CLAMP
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

/**
 * Form for editing Filtered course list block instances
 *
 * @package    block_filtered_course_list
 * @copyright  2016 CLAMP
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_filtered_course_list_edit_form extends block_edit_form {

    /**
     * Builds the form to edit instance settings
     *
     * @param MoodleQuickForm $mform
     */
    protected function specific_definition($mform) {

        // Section header title according to language file.
        $mform->addElement('header', 'configheader', get_string('blocksettings', 'block'));

        // Set the title for the block.
        $mform->addElement('text', 'config_title', get_string('configtitle', 'block_filtered_course_list'));
        $mform->setDefault('config_title', get_string('blockname', 'block_filtered_course_list'));
        $mform->setType('config_title', PARAM_TEXT);

        // Instance filters.
        $mform->addElement('textarea',
                        'config_filters',
                        get_string('filters', 'block_filtered_course_list'),
                        array('cols' => 80, 'rows' => 8, 'style' => 'resize: both; font-family: monospace;'));
        $mform->setDefault('config_filters', get_config('block_filtered_course_list', 'filters'));
        $mform->addHelpButton('config_filters', 'filters', 'block_filtered_course_list');
        $mform->setType('config_filters', PARAM_RAW);
    }
}
