<?php
/**
 * File for device search
 * @author jacob
 * @package    mod_turningtech
 * @copyright  2012 Turning Technologies
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */
global $CFG;
defined('MOODLE_INTERNAL') || die();
require_once $CFG->libdir . '/formslib.php';
/**
 * form class for device search
 * @author jacob
 * @copyright  2012 Turning Technologies
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */
class turningtech_device_search_form extends moodleform {
    /**
     * form Definition
     * @return unknown_type
     */
    function definition() {
        $mform = & $this->_form;
        $mnet_peer = $this->_customdata['turningtech'];
        $mform->addElement('hidden', 'userid');
        $mform->setType('userid', PARAM_RAW);
        $mform->addElement('hidden', 'courseid');
        $mform->setType('courseid', PARAM_RAW);
        $mform->addElement('text', 'deviceid', get_string('deviceidlabel', 'turningtech'), array ('onkeypress' => 'return validateDeviceInput(event);'));
        $mform->setType('deviceid', PARAM_RAW);
        $mform->addElement('hidden', 'all_courses');
        $mform->setType('all_courses', PARAM_RAW);
        $mform->addElement('hidden', 'typeid');
        $mform->setType('typeid', PARAM_INT);
        $buttonarray = array ();
        $buttonarray[] = &$mform->createElement('submit', 'submitbutton', 'Search', array ('onclick' => 'return checkinput();'));
        $buttonarray[] = &$mform->createElement('cancel');
        $mform->addElement('html', '<div class="divDeviceSearchButton">');
        $mform->addGroup($buttonarray, 'buttonar', '', array (' '), false);
        $mform->closeHeaderBefore('buttonar');
        $mform->addElement('html', '</div>');
    }
    /**
     * Validate
     * @param unknown_type $data
     * @param unknown_type $files
     * @return unknown_type
     */
    function validation($data, $files) {
        $errors = array ();
        if (! empty($data['deviceid'])) {
            if (! TurningTechTurningHelper::isdeviceidvalid($data['deviceid'])) {
                $errors['deviceid'] = get_string('deviceidinwrongformatold', 'turningtech');
            }
            if (empty($data['deviceid'])) {
                $errors['deviceid'] = get_string('deviceidempty', 'turningtech');
            }
        }
        return $errors;
    }
}