<?php
global $CFG;
require_once($CFG->dirroot . '/lib/formslib.php');

/**
 *
 * @author jacob
 *
 */
class turningtech_purge_course_form extends moodleform {
    /**
     *
     * @return unknown_type
     */
    function definition() {
        $mform =& $this->_form;
        $mform->addElement('header', 'turningtechpurgecourseheader', get_string('purgecourseheader', 'turningtech'));
        $mform->setType('turningtechpurgecourseheader', PARAM_RAW);
        $mform->addElement('static', 'description', get_string('instructions', 'turningtech'), get_string('purgecourseinstructions', 'turningtech'));
        $mform->setType('description', PARAM_RAW);
        $mform->addElement('checkbox', 'confirm', get_string('awareofdangers', 'turningtech'));
        $mform->addRule('confirm', get_string('youmustconfirm', 'turningtech'), 'required');
        $mform->addElement('submit', 'submitbutton', get_string('purge', 'turningtech'));
    }

    /**
     *
     * @param $data
     * @param $files
     * @return unknown_type
     */
    function validation($data, $files) {
        $errors = parent::validation($data, $files);
    }
}
?>