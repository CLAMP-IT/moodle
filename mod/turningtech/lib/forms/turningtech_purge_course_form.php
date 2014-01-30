<?php
/**
 * File for deleting course specific DeviceMaps
 * @author jacob
 * @package    mod_turningtech
 * @copyright  2012 Turning Technologies
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */
global $CFG;
require_once($CFG->dirroot . '/lib/formslib.php');

/**
 * form class for deleting course specific devicemap
 * @author jacob
 * @copyright  2012 Turning Technologies
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */
class turningtech_purge_course_form extends moodleform {
    /**
     * form Definition
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
     * Validate
     * @param unknown_type $data
     * @param unknown_type $files
     * @return unknown_type
     */
    function validation($data, $files) {
        $errors = parent::validation($data, $files);
    }
}
?>