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
 * The main nanogong configuration form
 *
 * It uses the standard core Moodle formslib. For more info about them, please
 * visit: http://docs.moodle.org/en/Development:lib/formslib.php
 *
 * @author     Ning
 * @package    mod
 * @subpackage nanogong
 * @copyright  2011
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @version    4.2
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/course/moodleform_mod.php');

/**
 * Module instance settings form
 */
class mod_nanogong_mod_form extends moodleform_mod {

    /**
     * Defines forms elements
     */
    public function definition() {
        global $USER, $PAGE, $CFG;
        $mform = $this->_form;

        //-------------------------------------------------------------------------------
        // Adding the "general" fieldset, where all the common settings are showed
        $mform->addElement('header', 'general', get_string('general', 'form'));

        // Adding the standard "name" field
        $mform->addElement('text', 'name', get_string('nanogongname', 'nanogong'), array('size'=>'64'));
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEAN);
        }
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');
        //$mform->addHelpButton('name', 'nanogongname', 'nanogong');

        // Adding the standard "intro" and "introformat" fields
        $this->add_intro_editor(false, get_string('description', 'nanogong'));

        //-------------------------------------------------------------------------------
        // Available time and due time
        $mform->addElement('date_time_selector', 'timeavailable', get_string('availabledate', 'nanogong'), array('optional'=>true));
        $mform->setDefault('timeavailable', time());
        $mform->addElement('date_time_selector', 'timedue', get_string('duedate', 'nanogong'), array('optional'=>true));
        $mform->setDefault('timedue', time()+7*24*3600);
        //$mform->addElement('text', 'grade', get_string('maxgrade', 'nanogong'), array('size'=>'16'));
        //$mform->setDefault('grade', 100);
        $grades = array( 0, 1, 2, 3, 4, 5, 6, 7, 8, 9,
                        10,11,12,13,14,15,16,17,18,19,
                        20,21,22,23,24,25,26,27,28,29,
                        30,31,32,33,34,35,36,37,38,39,
                        40,41,42,43,44,45,46,47,48,49,
                        50,51,52,53,54,55,56,57,58,59,
                        60,61,62,63,64,65,66,67,68,69,
                        70,71,72,73,74,75,76,77,78,79,
                        80,81,82,83,84,85,86,87,88,89,
                        90,91,92,93,94,95,96,97,98,99,
                        100);
        $mform->addElement('select', 'grade', get_string('maxgrade', 'nanogong'), $grades);
        $mform->setDefault('grade', 100);
        $mform->addElement('text', 'maxduration', get_string('maxduration', 'nanogong'), array('size'=>'16'));
        $mform->addHelpButton('maxduration', 'maxduration', 'nanogong');
        $mform->setDefault('maxduration', 300);
        $mform->addElement('text', 'maxnumber', get_string('maxnumber', 'nanogong'), array('size'=>'16'));
        $mform->addHelpButton('maxnumber', 'maxnumber', 'nanogong');
        $mform->setDefault('maxnumber', 0);
        $mform->addElement('selectyesno', 'preventlate', get_string('preventlate', 'nanogong'));
        $mform->addElement('selectyesno', 'permission', get_string('permission', 'nanogong'));

        //-------------------------------------------------------------------------------
        // add standard elements
        //$this->standard_grading_coursemodule_elements();
        $this->standard_coursemodule_elements();
        //-------------------------------------------------------------------------------
        // add standard buttons, common to all modules
        $this->add_action_buttons();
    }
}
