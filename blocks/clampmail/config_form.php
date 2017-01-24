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
 * @copyright 2013 Collaborative Liberal Arts Moodle Project
 * @copyright 2012 Louisiana State University (original Quickmail block)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir . '/formslib.php');

class config_form extends moodleform {
    public function definition() {
        $mform =& $this->_form;

        $resetlink = html_writer::link(
            new moodle_url('/blocks/clampmail/config.php', array(
                'courseid' => $this->_customdata['courseid'],
                'reset' => 1
            )), get_string('reset', 'block_clampmail')
        );
        $mform->addElement('static', 'reset', '', $resetlink);

        $studentselect = array(0 => get_string('no'), 1 => get_string('yes'));

        $roles =& $mform->addElement('select', 'roleselection',
            get_string('select_roles', 'block_clampmail'), $this->_customdata['roles']);

        $roles->setMultiple(true);

        $options = array(
            0 => get_string('none'),
            'idnumber' => get_string('idnumber'),
            'shortname' => get_string('shortname')
        );

        $mform->addElement('select', 'prepend_class',
            get_string('prepend_class', 'block_clampmail'), $options);

        $mform->addElement('select', 'receipt',
            get_string('receipt', 'block_clampmail'), $studentselect);

        $mform->addElement('submit', 'save', get_string('savechanges'));
        $mform->addElement('hidden', 'courseid', $this->_customdata['courseid']);
        $mform->setType('courseid', PARAM_INT);

        $mform->addRule('roleselection', null, 'required');
    }
}
