<?php
global $CFG;
require_once($CFG->dirroot . '/lib/formslib.php');

/**
 * provides a form that allows students to enter their responseware username and password
 * to get their device ID
 * @author jacob
 *
 */
class turningtech_responseware_form extends moodleform {
    function definition() {
        $mform =& $this->_form;

        $mform->addElement('header', 'responsewareheader', get_string('responsewareheadertext', 'turningtech'));
        $mform->setType('responsewareheader', PARAM_RAW);
        $link = "<a href='" . TurningTechTurningHelper::getResponseWareUrl('forgotpassword') . "'>" . get_string('forgotpassword', 'turningtech') . "</a>";
        //$mform->addElement('static','createaccountlink', '', $link);
        $mform->addElement('hidden', 'typeid');
        $mform->addElement('html', '<div class="tt_rw_form_item">');
        $mform->addElement('text', 'username', get_string('responsewareuserid', 'turningtech'));
        $mform->addElement('html', '</div>');
        $mform->addElement('html', '<div class="tt_rw_form_item">');
        $mform->addElement('password', 'password', get_string('responsewarepassword', 'turningtech'));
        $mform->addElement('html', '</div>');
        $mform->addElement('static', 'forgotpasswordlink', '', $link);
        $mform->setType('forgotpasswordlink', PARAM_RAW);
        $mform->addElement('submit', 'submitbutton', get_string('register', 'turningtech'));

    }

    function validation($data, $files) {
        $errors = parent::validation($data, $files);
        if (empty($data['username'])) {
            $errors['username'] = get_string('mustprovideid', 'turningtech');
        }
        if (empty($data['password'])) {
            $errors['password'] = get_string('mustprovidepassword', 'turningtech');
        }
        return $errors;
    }
}
?>