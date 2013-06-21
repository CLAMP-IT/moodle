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


defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/formslib.php");

class enrol_meta_manage_form extends moodleform {
    protected $course;
    private static $jsmodule = array(
                'name' => 'course_selector',
                'fullpath' => '/enrol/meta/module.js',
                'requires'  => array('node', 'event-custom', 'datasource', 'json'));

    function definition() {
        global $PAGE;

        $mform  = $this->_form;
        $course = $this->_customdata['course'];
        $this->course = $course;
        
        $mform->disable_form_change_checker();
        
        $mform->addElement('header','general', get_string('pluginname', 'enrol_meta'));
      
        $currentlylinked = enrol_meta_linked_courses($course->id);
        $listdata = array($currentlylinked->label=>$currentlylinked->display);
        
        $mform->addElement('selectgroups', 'remove', get_string('linkedcourses', 'enrol_meta'), $listdata, array('size'=>10, 'multiple'=>true));
        $mform->addElement('submit', 'removebutton', get_string('unlinkselected', 'enrol_meta'));
        
        $mform->addElement('html', html_writer::empty_tag('br'));

        $searchselectname = 'link';
        $searchtext = optional_param($searchselectname.'_searchtext', '', PARAM_TEXT);
        
        $result = enrol_meta_course_search($course->id, $searchtext, true);
        $display = array();
        foreach($result->results->display as $item) {
            $display[$item->courseid] = $item->name;
        }
        $listdata = array($result->results->label=>$display);
        //$listdata = array($result->results->label=>$result->results->display);

        //$mform->addElement('selectgroups', $searchselectname, '', null, array('size'=>10, 'multiple'=>true));
        $mform->addElement('selectgroups', $searchselectname, '', $listdata, array('size'=>10, 'multiple'=>true));
        
        $searchgroup = array();
        $searchgroup[] = &$mform->createElement('text', $searchselectname.'_searchtext');
        $mform->setType($searchselectname.'_searchtext', PARAM_TEXT);
        $searchgroup[] = &$mform->createElement('submit', $searchselectname.'_searchbutton', get_string('search'));
        $mform->registerNoSubmitButton($searchselectname.'_searchbutton');
        $searchgroup[] = &$mform->createElement('submit', $searchselectname.'_clearbutton', get_string('clear'));
        $mform->registerNoSubmitButton($searchselectname.'_clearbutton');
        $searchgroup[] = &$mform->createElement('submit', $searchselectname.'_submitbutton', get_string('linkselected', 'enrol_meta'));
        $mform->addGroup($searchgroup, 'searchgroup', get_string('search') , array(''), false);
        
        $mform->addElement('checkbox', 'courseselector_searchanywhere', get_string('searchanywhere', 'enrol_meta'));
        
        $mform->addElement('hidden', 'id', null);
        $mform->setType('id', PARAM_INT);
        
        $cancellink = html_writer::link(new moodle_url('/enrol/instances.php', array('id'=>$course->id)), get_string('cancel'));
        $mform->addElement('static', 'cancel', $cancellink);
        $mform->closeHeaderBefore('cancel');
        
        $this->set_data(array('id'=>$course->id));
        user_preference_allow_ajax_update('courseselector_searchanywhere', 'bool');
        $searchanywhere = get_user_preferences('courseselector_searchanywhere', false);
        $this->set_data(array('courseselector_searchanywhere'=>$searchanywhere));
        
        $PAGE->requires->js_init_call('M.core_enrol.init_course_selector', array($searchselectname, $course->id), true, self::$jsmodule);
        $PAGE->requires->js_init_call('M.core_enrol.init_course_selector_options_tracker', array(), true, self::$jsmodule);
    }

    function validation($data, $files) {
        global $DB, $CFG;
        $errors = array();
        return $errors;
    }
    
}

?>
