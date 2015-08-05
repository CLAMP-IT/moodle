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
 * Prints a list of courses from another Moodle instance.
 *
 * @package   block_remote_courses
 * @copyright 2015 Lafayette College ITS
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_remote_courses_edit_form extends block_edit_form {
    protected function specific_definition($mform) {
        // Section header title according to language file.
        $mform->addElement('header', 'configheader', get_string('blocksettings', 'block'));

        // Configure the block title.
        $mform->addElement('text', 'config_title', get_string('blocktitle', 'block_remote_courses'));
        $mform->setDefault('config_title', 'Remote Courses');
        $mform->setType('config_title', PARAM_MULTILANG);

        // Remote site.
        $mform->addElement('text', 'config_remotesite', get_string('blockremotesite', 'block_remote_courses'));
        $mform->setType('config_remotesite', PARAM_URL);

        // Webservice token.
        $mform->addElement('text', 'config_wstoken', get_string('blockwstoken', 'block_remote_courses'));
        $mform->setType('config_wstoken', PARAM_NOTAGS);

        // Intro text.
        $mform->addElement('editor', 'config_introtext', get_string('blockintrotext', 'block_remote_courses'));
        $mform->setType('config_introtext', PARAM_RAW);

        // Webservice function.
        // Since this isn't generalized we're assuming tight integration with existing development.
        // In the future this might be configurable.

        // Courses to show.
        $mform->addElement('text', 'config_numcourses',
            get_string('blocknumcourses', 'block_remote_courses'), array('size' => '2'));
        $mform->setDefault('config_numcourses', 3);
        $mform->setType('config_numcourses', PARAM_INT);
    }
}
