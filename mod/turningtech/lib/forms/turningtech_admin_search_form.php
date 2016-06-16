<?php
/**
 * File for searching DeviceMaps
 * @author jacob
 * @package    mod_turningtech
 * @copyright  2012 Turning Technologies
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */
global $CFG;
require_once($CFG->dirroot . '/lib/formslib.php');

/**
 * form class for searching DeviceMaps
 * @author jacob
 * @copyright  2012 Turning Technologies
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */
class turningtech_admin_search_form extends moodleform {
    /**
     * form Definition
     * @return unknown_type
     */
    function definition() {
        $mform =& $this->_form;
        $mform->addElement('header', 'turningtechadminsearchheader', get_string('usersearch', 'turningtech'));
        $mform->setType('turningtechadminsearchheader', PARAM_RAW);
        $mform->addElement('text', 'searchstring', get_string('studentusername', 'turningtech'));
        $mform->setType('searchstring', PARAM_TEXT);
        $mform->addElement('submit', 'submitbutton', get_string('search'));
        $mform->addRule('searchstring', NULL, 'required');
    }

    /**
     * Validate
     * @param unknown_type $data
     * @param unknown_type $files
     * @return unknown_type
     */
    function validation($data, $files) {
        if (strlen($data['searchstring']) < 3) {
            $errors['searchstring'] = get_string('mustbe3chars', 'turningtech');
            return $errors;
        }
    }
}
?>