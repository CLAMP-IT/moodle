<?php
global $CFG;
require_once($CFG->dirroot . '/lib/formslib.php');

/**
 *
 * @author jacob
 *
 */
class turningtech_admin_search_form extends moodleform {
    /**
     * (non-PHPdoc)
     * @see docroot/lib/moodleform#definition()
     */
    function definition() {
        $mform =& $this->_form;
        $mform->addElement('header', 'turningtechadminsearchheader', get_string('usersearch', 'turningtech'));
        $mform->setType('turningtechadminsearchheader', PARAM_RAW);
        $mform->addElement('text', 'searchstring', get_string('studentusername', 'turningtech'));
        $mform->addElement('submit', 'submitbutton', get_string('search'));
        $mform->addRule('searchstring', NULL, 'required');
    }

    /**
     * (non-PHPdoc)
     * @see docroot/lib/moodleform#validation($data, $files)
     */
    function validation($data, $files) {
        if (strlen($data['searchstring']) < 3) {
            $errors['searchstring'] = get_string('mustbe3chars', 'turningtech');
            return $errors;
        }
    }
}
?>