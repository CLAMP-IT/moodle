<?php
/**
 * File for responseware forms
 * @author jacob
 * @package    mod_turningtech
 * @copyright  2012 Turning Technologies
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */
global $CFG;
require_once($CFG->dirroot . '/lib/formslib.php');

/**
 * provides a form that allows students to enter their responseware username and password
 * to get their device ID
 * @author jacob
 *
 */
/**
 * form class that allows students to enter their responseware username and password
 * @author jacob
 * @copyright  2012 Turning Technologies
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */
class turningtech_responseware_form extends moodleform {
    /**
     * form Definition
     * @return unknown_type
     */
    function definition() {
        $mform =& $this->_form;

        $mform->addElement('header', 'responsewareheader', get_string('responsewareheadertext', 'turningtech'));
        $mform->setType('responsewareheader', PARAM_RAW);
        $link = "<a href='" . TurningTechTurningHelper::getresponsewareurl('forgotpassword') . "' target='_blank'>" . get_string('forgotpassword', 'turningtech') . "</a>";
        $linkcreateaccount = "<a href='" . TurningTechTurningHelper::getresponsewareurl('createaccount') . "' target='_blank'>" . get_string('createaccount', 'turningtech') . "</a>";
        //$mform->addElement('static','createaccountlink', '', $link);
        $mform->addElement('hidden', 'typeid');
        $mform->setType('typeid', PARAM_INT);
        $mform->addElement('html', '<div class="tt_rw_form_item">');
        $mform->addElement('text', 'username', get_string('responsewareuserid', 'turningtech'));
        $mform->setType('username', PARAM_TEXT);
        $mform->addElement('html', '</div>');
        $mform->addElement('html', '<div class="tt_rw_form_item">');
        $mform->addElement('password', 'password', get_string('responsewarepassword', 'turningtech'));
        $mform->addElement('html', '</div>');
        $mform->addElement('html', '<div class="tt_rw_form_item">');
        $mform->addElement('static', 'forgotpasswordlink', '', $link ."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;". $linkcreateaccount);
        $mform->setType('forgotpasswordlink', PARAM_RAW);
        $mform->addElement('html', '</div>');
        $mform->addElement('submit', 'submitbutton', get_string('register', 'turningtech'), array('style'=>'margin-top:10px;text-align:center;margin-left:13%;'));

    }
    /**
     * Validate
     * @param unknown_type $data
     * @param unknown_type $files
     * @return unknown_type
     */
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