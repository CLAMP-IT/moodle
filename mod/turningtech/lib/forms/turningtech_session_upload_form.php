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
class turningtech_session_upload_form extends moodleform {
    /**
     * form Definition
     * @return unknown_type
     */
    function definition() {
        $mform =& $this->_form;
        
        $mform->addElement('filemanager', 'attachments', get_string('attachment', 'moodle'), null,
                    array('subdirs' => 0, 'maxbytes' => $maxbytes, 'maxfiles' => 50,
                          'accepted_types' => array('document') ));
        
        
        // $mform->addElement('header', 'turningtechadminsearchheader', get_string('usersearch', 'turningtech'));
//         $mform->setType('turningtechadminsearchheader', PARAM_RAW);
//         $mform->addElement('text', 'searchstring', get_string('studentusername', 'turningtech'));
//         $mform->addElement('submit', 'submitbutton', get_string('search'));
//         $mform->addRule('searchstring', NULL, 'required');
    }

}
?>