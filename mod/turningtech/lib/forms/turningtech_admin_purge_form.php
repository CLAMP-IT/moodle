<?php
global $CFG;
require_once($CFG->dirroot . '/lib/formslib.php');

/**
 *
 * @author jacob
 *
 */
class turningtech_admin_purge_form extends moodleform {
    /**
     *
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