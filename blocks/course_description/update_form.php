<?php
/**
 *
 * update_form.php - Prints the course info form for block Course Description.
 *
 * @package    block_course_description
 * @author     Sarah Ryder <sryder@hampshire.edu>
 * @copyright  2010 onwards Hampshire College
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once($CFG->libdir . '/formslib.php');

class update_form extends moodleform {
    public function definition() {
        global $CFG, $USER, $COURSE, $OUTPUT, $DB, $instanceid, $cdid, $original;

	$original->id = $COURSE->id;
	$original->instanceid = $instanceid;
	$original->cdid = $cdid;

        $mform =& $this->_form;

	$mform->addElement('header','', get_string('update_courseinfo', 'block_course_description'));

	$mform->addElement('hidden', 'id', $COURSE->id);
	$mform->addElement('hidden', 'instanceid', $instanceid);
	$mform->addElement('hidden', 'cdid', $cdid);

	$mform->addElement('editor', 'course_description', get_string('course_description', 'block_course_description'))->setValue(array('text' => $original->course_description));
	$mform->setType('course_description', PARAM_RAW);
	$mform->addElement('editor', 'course_obj', get_string('course_obj', 'block_course_description'))->setValue(array('text' => $original->course_obj));
	$mform->setType('course_obj', PARAM_RAW);
	$mform->addElement('editor', 'eval_criteria', get_string('eval_criteria', 'block_course_description'))->setValue(array('text' => $original->eval_criteria));
	$mform->setType('eval_criteria', PARAM_RAW);
	$mform->addElement('editor', 'add_info', get_string('add_info', 'block_course_description'))->setValue(array('text' => $original->add_info));
	$mform->setType('add_info', PARAM_RAW);

	$buttonarray=array();
	$buttonarray[] =& $mform->createElement('submit', 'update_courseinfo', get_string('savechanges'));
	$buttonarray[] =& $mform->createElement('submit', 'cancel', get_string('cancel'));
	$mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);

    }
}
