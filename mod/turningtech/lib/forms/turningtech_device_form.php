<?php

global $CFG;
//require_once($CFG->dirroot . '/lib/formslib.php');
//require_once ($CFG->dirroot.'/course/moodleform_mod.php');
defined('MOODLE_INTERNAL') || die();

require_once $CFG->libdir . '/formslib.php';

/**
 * form class for creating/editing DeviceMaps
 * @author jacob
 *
 */
class turningtech_device_form extends moodleform {
    /**
     * (non-PHPdoc)
     * @see docroot/lib/moodleform#definition()
     */
    function definition() {
        $mform =& $this->_form;
        $mnet_peer = $this->_customdata['turningtech'];
        $mform->addElement('header', 'turningtechdevicemapheaderstudent', get_string('deviceid', 'turningtech'));
        $mform->setType('turningtechdevicemapheader', PARAM_RAW);
        $mform->addElement('hidden', 'devicemapid');
        $mform->setType('devicemapid', PARAM_RAW);
        $mform->addElement('hidden', 'userid');
        $mform->setType('userid', PARAM_RAW);
        $mform->addElement('hidden', 'courseid');
        $mform->setType('courseid', PARAM_RAW);
        $mform->addElement('text', 'deviceid', get_string('deviceid', 'turningtech'));
        $mform->addRule('deviceid', NULL, 'required');
        /*
        // radio buttons for "just this course" and "all courses"
        $radioarray = array();
        $radioarray[] = MoodleQuickForm::createElement('radio', 'all_courses', '', get_string('justthiscourse','turningtech'), 0);
        $radioarray[] = MoodleQuickForm::createElement('radio', 'all_courses', '', get_string('allcourses', 'turningtech'), 1);
        $mform->addGroup($radioarray, 'all_courses_options', get_string('appliesto', 'turningtech'), array(' '), false);
        */
        $mform->addElement('hidden', 'all_courses');
        $mform->addElement('hidden', 'typeid');
        // submit/delete buttons
        //$this->add_action_buttons();
        $mform->addElement('submit', 'submitbutton', get_string('register', 'turningtech'));
    }


    /**
     * (non-PHPdoc)
     * @see docroot/lib/moodleform#validation($data, $files)
     */
    function validation($data, $files) {
        $errors = parent::validation($data, $files);
        if (!empty($data['deviceid'])) {
            if (!TurningTechTurningHelper::isDeviceIdValid($data['deviceid'])) {
                $errors['deviceid'] = get_string('deviceidinwrongformat', 'turningtech');
            } else if (TurningTechDeviceMap::isAlreadyInUse($data)) {
                $errors['deviceid'] = get_string('deviceidalreadyinuse', 'turningtech');
            }
        }
        return $errors;
    }
}
?>