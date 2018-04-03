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

namespace block_clampmail;

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir . '/formslib.php');

class alternate_form extends \moodleform {
    public function definition() {
        $m =& $this->_form;

        $course = $this->_customdata['course'];

        $m->addElement('header', 'alt_header', $course->fullname);
        $m->addElement('text', 'address', get_string('email'));
        $m->setType('address', PARAM_NOTAGS);
        $m->addRule('address', get_string('missingemail'), 'required', null, 'server');
        $m->addRule('address', get_string('maximumchars', '', 100), 'maxlength', 100, 'client');

        $m->addElement('hidden', 'valid', 0);
        $m->setType('valid', PARAM_INT);
        $m->addElement('hidden', 'courseid', $course->id);
        $m->setType('courseid', PARAM_INT);
        $m->addElement('hidden', 'id', '');
        $m->setType('id', PARAM_INT);
        $m->addElement('hidden', 'action', $this->_customdata['action']);
        $m->setType('action', PARAM_RAW);

        $buttons = array(
            $m->createElement('submit', 'submit', get_string('savechanges')),
            $m->createElement('cancel')
        );

        $m->addGroup($buttons, 'buttons', '', array(' '), false);

        $m->closeHeaderBefore('buttons');
    }
}
