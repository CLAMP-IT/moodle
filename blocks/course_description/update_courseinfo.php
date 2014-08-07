<?php
/**
 *
 * update_courseinfo.php - Used by course description block for updating field values.
 *
 * @package    block_course_description
 * @author     Sarah Ryder <sryder@hampshire.edu>
 * @copyright  2010 onwards Hampshire College
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

    require_once('../../config.php');
    require_once($CFG->libdir.'/blocklib.php');
    require_once('update_form.php');

    $id         = required_param('id', PARAM_INT);  // course ID
    $instanceid = optional_param('instanceid', 0, PARAM_INT);
    $cdid 	= optional_param('cdid', 0, PARAM_INT);

    $instance = new stdClass;

    if (!$course = $DB->get_record('course', array('id' => $id))) {
        error('Course ID was incorrect');
    }

    require_login($course);
    $context = get_context_instance(CONTEXT_BLOCK, $instanceid);

    if (isset($instanceid)) {
        $instance = $DB->get_record('block_instances', array('id' => $instanceid));
    } else {
        if ($cdblock = $DB->get_record('block', array('name' => 'course_description'))) {
            $instance = $DB->get_record('block_instances', array('blockid' => $cdblock->id, 'pageid' => $course->id));
        }
    }

    if (empty($instance)) {
	$haspermission = false;
    } else {
    	if (has_capability('block/course_description:canedit', get_context_instance(CONTEXT_BLOCK, $instanceid))) {
	    $haspermission = true;
	}
    }

    if (!$haspermission) {
        error('Sorry, you do not have the correct permissions to edit this course description.');
    }

    if ($form = data_submitted()) {   // data was submitted

        if (isset($form->cancel)) {
            // cancel button was hit...
	    redirect(new moodle_url('/course/view.php?id='.$course->id));
        }

       // no errors, then update
        if(!isset($form->error)) {
            // prepare an object for the update_record function

	    $form->id = $form->cdid;
	    $form->courseid = $course->id;

	    $form->course_descriptionformat = $form->course_description['format'];
	    $form->course_description = $form->course_description['text'];

	    $form->course_objformat = $form->course_obj['format'];
	    $form->course_obj = $form->course_obj['text'];

	    $form->eval_criteriaformat = $form->eval_criteria['format'];
	    $form->eval_criteria = $form->eval_criteria['text'];

	    $form->add_infoformat = $form->add_info['format'];
	    $form->add_info = $form->add_info['text'];

	    if($DB->update_record('block_course_description', $form)) {
	        redirect("$CFG->wwwroot/course/view.php?id=$course->id");
	    }
	    else {
	        error('Course information update unsuccessful.');
	    }
	}
    } else {
	$original = $DB->get_record('block_course_description', array('courseid' => $course->id));
    }

    $PAGE->set_context($context);
    $PAGE->set_course($course);
    $context = $PAGE->context;
    $PAGE->navbar->add(get_string('blockname', 'block_course_description'));
    $PAGE->navbar->add(get_string('update_courseinfo', 'block_course_description'));
    $PAGE->set_title(get_string('blockname', 'block_course_description'));
    $PAGE->set_heading($course->fullname);
    $PAGE->set_url('/course/view.php', array('courseid' => $course->id));

    $nform = new update_form();
 
    echo $OUTPUT->header();

    $nform->display();

    echo $OUTPUT->footer($course);
