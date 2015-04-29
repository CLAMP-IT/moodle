<?php
/**
 * File for device form
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
 * form class for device
 * @author jacob
 * @copyright  2012 Turning Technologies
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */
class turningtech_device_form extends moodleform {
    /**
     * form Definition
     * @return unknown_type
     */
    function definition() {
        $mform = & $this->_form;
        $mnet_peer = $this->_customdata['turningtech'];
        $mform->addElement('header', 'turningtechdevicemapheaderstudent', get_string('deviceid', 'turningtech'));
        $mform->setType('turningtechdevicemapheader', PARAM_RAW);
        $mform->addElement('hidden', 'devicemapid');
        $mform->setType('devicemapid', PARAM_RAW);
        $mform->addElement('hidden', 'userid');
        $mform->setType('userid', PARAM_RAW);
        $mform->addElement('hidden', 'courseid');
        $mform->setType('courseid', PARAM_RAW);
        $mform->addElement('text', 'deviceid', get_string('deviceidform', 'turningtech'), array ('onkeypress' => 'return validateDeviceInput(event);', 
                        'style' => 'white-space:nowrap;'));
        $mform->setType('deviceid', PARAM_RAW);
        $mform->addElement('hidden', 'all_courses');
        $mform->setType('all_courses', PARAM_RAW);
        $mform->addElement('hidden', 'typeid');
        $mform->setType('typeid', PARAM_INT);
        $mform->addElement('submit', 'submitbutton', get_string('register', 'turningtech'), array ('onclick' => 'return validateRC();'));
    }
    /**
     * Validate
     * @param unknown_type $data
     * @param unknown_type $files
     * @return unknown_type
     */
    function validation($data, $files) {
        $errors = parent::validation($data, $files);
        $pluginconfig = get_config('moodle','turningtech_device_selection');
        if ($pluginconfig ==TURNINGTECH_DISABLE_RESPONSEWARE && strlen($data['deviceid'])== '8') {
            $errors['deviceid'] = get_string('deviceidinwrongformatrw', 'turningtech');
        }
        if (! empty($data['deviceid'])) {
            if (! TurningTechTurningHelper::isdeviceidvalid($data['deviceid'])) {
                $errors['deviceid'] = get_string('deviceidinwrongformat', 'turningtech');
            }
        }
        return $errors;
    }
}