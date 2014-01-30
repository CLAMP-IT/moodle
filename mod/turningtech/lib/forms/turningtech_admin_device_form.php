<?php
/**
 * File for creating/editing DeviceMaps
 * @author jacob
 * @package    mod_turningtech
 * @copyright  2012 Turning Technologies
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */
global $CFG;
require_once($CFG->dirroot . '/lib/formslib.php');

/**
 * form class for creating/editing DeviceMaps
 * @author jacob
 * @copyright  2012 Turning Technologies
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */
class turningtech_admin_device_form extends moodleform {
    /**
     * (non-PHPdoc)
     * @see docroot/lib/moodleform#definition()
     */
    function definition() {
        $mform =& $this->_form;
        $mform->addElement('header', 'turningtechdevicemapheader', get_string('deviceid', 'turningtech'));
        $mform->setType('turningtechdevicemapheader', PARAM_RAW);
        $mform->addElement('hidden', 'devicemapid');
        $mform->setType('devicemapid', PARAM_RAW);
        $mform->addElement('hidden', 'adminform', 1);
        $mform->setType('adminform', PARAM_RAW);
        $mform->addElement('hidden', 'userid');
        $mform->setType('userid', PARAM_RAW);
        $mform->addElement('text', 'deviceid', get_string('deviceid', 'turningtech'));
        $mform->addRule('deviceid', NULL, 'required');
        /*
        // radio buttons for "just this course" and "all courses"
        $radioarray = array();
        $radioarray[] = &MoodleQuickForm::createElement('radio', 'all_courses', '', get_string('justthiscourse','turningtech'), 0);
        $radioarray[] = &MoodleQuickForm::createElement('radio', 'all_courses', '', get_string('allcourses', 'turningtech'), 1);
        $mform->addGroup($radioarray, 'all_courses_options', get_string('appliesto', 'turningtech'), array(' '), false);

        $coursearray = array();
        $studentcourses = enrol_get_users_courses($this->_customdata['studentid'], false);
        foreach($studentcourses as $studentcourse) {
        $coursearray[] = &MoodleQuickForm::createElement('radio', 'courseid', '', $studentcourse->fullname, $studentcourse->id);
        }
        $mform->addGroup($coursearray, 'select_course', get_string('selectcourse','turningtech'), array(' '), false);
        */
        $mform->addElement('hidden', 'all_courses');
        $mform->addElement('hidden', 'courseid');
        $mform->addElement('hidden', 'typeid');
        // submit/delete buttons
        $this->add_action_buttons();
    }

    /**
     * (non-PHPdoc)
     * @param mixed $data
     * @param mixed $files
     * @return unknown_type
     * @see docroot/lib/moodleform#validation($data, $files)
     */
    function validation($data, $files) {
        $errors = parent::validation($data, $files);
        if (!$data['all_courses'] && empty($data['courseid'])) {
            $errors['select_course'] = get_string('mustselectcourse', 'turningtech');
            return $errors; // return here because continuing validation is pointless and causes errors
        }
        if (!empty($data['deviceid'])) {
            if (!TurningTechTurningHelper::isdeviceidvalid($data['deviceid'])) {
                $errors['deviceid'] = get_string('deviceidinwrongformat', 'turningtech');
            } else if (TurningTechDeviceMap::isalreadyinuse($data)) {
                $errors['deviceid'] = get_string('deviceidalreadyinuse', 'turningtech');
            }
        }

        return $errors;
    }
}
?>