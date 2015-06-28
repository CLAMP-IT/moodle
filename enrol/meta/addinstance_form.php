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
 * Adds instance form
 *
 * @package    enrol_meta
 * @copyright  2010 Petr Skoda {@link http://skodak.org}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/formslib.php");

class enrol_meta_addinstance_form extends moodleform {
    protected $course;

    /** @var array Included javascript for course selector. */
    private static $jsmodule = array(
        'name' => 'course_selector',
        'fullpath' => '/enrol/meta/module.js',
        'requires' => array('node', 'event-custom', 'datasource', 'json'));

    function definition() {
        global $CFG, $DB, $PAGE;

        $mform  = $this->_form;
        $course = $this->_customdata['course'];
        $instance = $this->_customdata['instance'];
        $this->course = $course;

        $mform->disable_form_change_checker();

        $searchtext = optional_param('link_searchtext', '', PARAM_TEXT);
        $result  = enrol_meta_course_search($course->id, $searchtext, true);
        $display = array();
        foreach ($result->results->display as $item) {
            $display[$item->courseid] = $item->name;
        }
        $listdata = array($result->results->label => $display);

        $groups = array(0 => get_string('none'));
        if (has_capability('moodle/course:managegroups', context_course::instance($course->id))) {
            $groups[ENROL_META_CREATE_GROUP] = get_string('creategroup', 'enrol_meta');
        }
        foreach (groups_get_all_groups($course->id) as $group) {
            $groups[$group->id] = format_string($group->name, true, array('context' => context_course::instance($course->id)));
        }

        $mform->addElement('header','general', get_string('pluginname', 'enrol_meta'));

        if ($instance) {
            $courses = $DB->get_records_menu('course', array('id' => $instance->customint1), 'fullname', 'id,fullname');
            $mform->addElement('select', 'link', get_string('linkedcourse', 'enrol_meta'), $courses);
        } else {
            $select = $mform->addElement('selectgroups', 'link', '', $listdata, array('size' => 10));
            $select->setMultiple(false);
            $searchgroup = array();
            $searchgroup[] = &$mform->createElement('text', 'link_searchtext');
            $mform->setType('link_searchtext', PARAM_TEXT);
            $searchgroup[] = &$mform->createElement('submit', 'link_searchbutton', get_string('search'));
            $mform->registerNoSubmitButton('link_searchbutton');
            $searchgroup[] = &$mform->createElement('submit', 'link_clearbutton', get_string('clear'));
            $mform->registerNoSubmitButton('link_clearbutton');
            $mform->addGroup($searchgroup, 'searchgroup', get_string('search') , array(''), false);
        }
        $mform->addRule('link', get_string('required'), 'required', null, 'client');

        $mform->addElement('select', 'customint2', get_string('addgroup', 'enrol_meta'), $groups);

        $mform->addElement('hidden', 'id', null);
        $mform->setType('id', PARAM_INT);

        $mform->addElement('hidden', 'enrolid');
        $mform->setType('enrolid', PARAM_INT);

        $data = array('id' => $course->id);
        if ($instance) {
            $data['link'] = $instance->customint1;
            $data['enrolid'] = $instance->id;
            $data['customint2'] = $instance->customint2;
            $mform->freeze('link');
            $this->add_action_buttons();
        } else {
            $this->add_add_buttons();
        }
        $this->set_data($data);

        $PAGE->requires->js_init_call('M.core_enrol.init_course_selector', array('link', $course->id), true, self::$jsmodule);
    }

    /**
     * Adds buttons on create new method form
     */
    protected function add_add_buttons() {
        $mform = $this->_form;
        $buttonarray = array();
        $buttonarray[0] = $mform->createElement('submit', 'submitbutton', get_string('addinstance', 'enrol'));
        $buttonarray[1] = $mform->createElement('submit', 'submitbuttonnext', get_string('addinstanceanother', 'enrol'));
        $buttonarray[2] = $mform->createElement('cancel');
        $mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
        $mform->closeHeaderBefore('buttonar');
    }

    function validation($data, $files) {
        global $DB, $CFG;

        $errors = parent::validation($data, $files);

        if ($this->_customdata['instance']) {
            // Nothing to validate in case of editing.
            return $errors;
        }

        return $errors;
    }
}

