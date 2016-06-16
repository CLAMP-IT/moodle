<?php
/**
 * File for importing session
 * @author jacob
 * @package    mod_turningtech
 * @copyright  2012 Turning Technologies
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */
global $CFG;
require_once($CFG->dirroot . '/lib/formslib.php');
/**
 * form that allows user to import session file
 * @author jacob
 * @copyright  2012 Turning Technologies
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */
class turningtech_import_session_form extends moodleform {
    /**
     * form Definition
     * @return unknown_type
     */
    function definition() {
        $mform =& $this->_form;
        $instance = $this->_customdata;

        $html_formatting = '<div class="tt_import_session_form">';

        // visible elements
        $mform->addElement('header', 'turningtechimportheader', get_string('importformtitle', 'turningtech'));
        $mform->setType('turningtechimportheader', PARAM_RAW);
        $mform->addElement('filemanager', 'sessionfile', get_string('filetoimport', 'turningtech'), null, array(
            'accepted_types' => array('.tpzx'), 'maxfiles' => 50
        ));

        // buttons
        $mform->addElement('checkbox', 'override', get_string('overrideallexisting', 'turningtech'));
        $mform->addRule(array(
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
     * Validate
     * @param unknown_type $data
     * @param unknown_type $files
     * @return unknown_type
     */
    function validation($data, $files) {
        $errors = parent::validation($data, $files);

        // If the file actually has been uploaded.
        if (count($_FILES)) {
            $isValid = TurningTechTurningHelper::isimportsessionfilevalid($_FILES["sessionfile"]);

            if ($isValid < 1) {
                $errors['session_file'] = get_string('importedsesionfilenotvalid', 'turningtech');
            }
        }

        return $errors;
    }
}
?>