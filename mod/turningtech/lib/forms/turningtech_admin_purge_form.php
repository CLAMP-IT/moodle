<?php
/**
 * File for deleting DeviceMaps
 * @author jacob
 * @package    mod_turningtech
 * @copyright  2012 Turning Technologies
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */
global $CFG;
require_once($CFG->dirroot . '/lib/formslib.php');

/**
 * form class for deleting DeviceMaps
 * @author jacob
 * @copyright  2012 Turning Technologies
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */
class turningtech_admin_purge_form extends moodleform {
    /**
     * form Definition
     * @return unknown_type
     */
    function definition() {
        $mform =& $this->_form;
        $mform->addElement('header', 'turningtechadminpurgeheader', get_string('adminpurgeheader', 'turningtech'));
        $mform->setType('turningtechadminpurgeheader', PARAM_RAW);
        $mform->addElement('static', 'description', get_string('instructions', 'turningtech'), get_string('purgecourseinstructions', 'turningtech'));
        $mform->setType('description', PARAM_RAW);
        $mform->addElement('checkbox', 'confirm', get_string('awareofdangers', 'turningtech'));
        $mform->addRule('confirm', get_string('youmustconfirm', 'turningtech'), 'required');

        $this->add_action_buttons($cancel = true, $submitlabel = "Purge");
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