<?php
global $CFG;
require_once($CFG->dirroot . '/lib/formslib.php');
/**
 * form that allows user to import session file
 * @author jacob
 *
 */
class turningtech_import_session_form extends moodleform {
    /**
     * (non-PHPdoc)
     * @see docroot/lib/moodleform#definition()
     */
    function definition() {
        $mform =& $this->_form;
        $instance = $this->_customdata;

        $html_formatting = '<div class="tt_import_session_form">';

        // visible elements
        $mform->addElement('header', 'turningtechimportheader', get_string('importformtitle', 'turningtech'));
        $mform->setType('turningtechimportheader', PARAM_RAW);
        $mform->addElement('text', 'assignment_title', get_string('assignmenttitle', 'turningtech'));
        $mform->addElement('filepicker', 'sessionfile', get_string('filetoimport', 'turningtech'), null, array(
            'accepted_types' => 'txt'
        ));

        // buttons
        $mform->addElement('checkbox', 'override', get_string('overrideallexisting', 'turningtech'));
        $mform->addRule(array(
            'assignment_title',
            'sessionfile'
        ), null, 'required');
        // add submit/cancel buttons
        $mform->addElement('submit', 'submit', get_string('savechanges'));
        $mform->addElement('cancel', 'cancel', get_string('cancel'));

        //    $this->add_action_buttons(true, get_string('savechanges', 'admin'));

        // hidden params
        $mform->addElement('hidden', 'contextid', $instance['contextid']);
        $mform->setType('contextid', PARAM_INT);
        $mform->addElement('hidden', 'userid', $instance['userid']);
        $mform->setType('userid', PARAM_INT);
        $mform->addElement('hidden', 'action', 'uploadfile');
        $mform->setType('action', PARAM_ALPHA);

        $html_formatting = '</div>';
    }
    /**
     * (non-PHPdoc)
     * @see docroot/lib/moodleform#validation($data, $files)
     */
    function validation($data, $files) {
        $errors = parent::validation($data, $files);

        // If the file actually has been uploaded.
        if (count($_FILES)) {
            $isValid = TurningTechTurningHelper::isImportSessionFileValid($_FILES["session_file"]);

            if ($isValid < 1) {
                $errors['session_file'] = get_string('importedsesionfilenotvalid', 'turningtech');
            }
        }

        return $errors;
    }
}
?>