<?php
/****
 * Displays the edit form for device associations
 */
require_once('../../config.php');
require_once($CFG->dirroot . '/mod/turningtech/lib.php');
require_once($CFG->dirroot . '/mod/turningtech/lib/forms/turningtech_device_form.php');
global $DB, $PAGE, $USER;
$devicemapid = optional_param('id', NULL, PARAM_INT);
$courseid    = optional_param('course', NULL, PARAM_INT);
$studentid   = optional_param('student', NULL, PARAM_INT);

$course    = NULL;
$student   = NULL;
$devicemap = NULL;

// populate course and student data from devicemap
if (!empty($devicemapid)) {
    if (!$devicemap = TurningTechDeviceMap::fetch(array(
        'id' => $devicemapid
    ))) {
        error(get_string('couldnotfinddeviceid', 'turningtech', $devicemapid));
    }
    if (!$studentid) {
        $studentid = $devicemap->getField('userid');
    }
}

// figure out which course we're dealing with
if (empty($courseid)) {
    if (!empty($devicemap) && !$devicemap->isAllCourses()) {
        $courseid = $devicemap->getField('courseid');
    } else {
        error(get_string('courseidincorrect', 'turningtech'));
    }
}
// verify course is valid
if (!$course = $DB->get_record('course', array(
    'id' => $courseid
))) {
    error(get_string('courseidincorrect', 'turningtech'));
}

// if we are creating a new devicemap and did not receive a student ID, throw an error
if (empty($studentid) && empty($devicemap)) {
    print_error('nostudentdatareceived', 'turningtech');
}
// verify student ID is valid
if (!$student = $DB->get_record('user', array(
    'id' => $studentid
))) {
    print_error('studentidincorrect', 'turningtech');
}

// default URL for redirection
$default_url = "{$CFG->wwwroot}/mod/turningtech/index.php?id={$course->id}";

// make sure user is enrolled
require_course_login($course);

// verify user has permission to delete this devicemap
if (!empty($devicemap) && ($USER->id != $devicemap->getField('userid'))) {
    // current user is not the owner of the devicemap.  So
    // verify current user is a teacher
    $context = get_context_instance(CONTEXT_COURSE, $course->id);
    if (!has_capability('mod/turningtech:manage', $context)) {
        error(get_string('notpermittedtoeditdevicemap', 'turningtech'), $default_url);
    }
}

$url  = "edit_device.php?";
$args = array();
if (!empty($devicemapid)) {
    $args[] = "id={$devicemapid}";
}
if (!empty($course)) {
    $args[] = "course={$course->id}";
}
if (!empty($studentid)) {
    $args[] = "student={$studentid}";
}

$editform = new turningtech_device_form($url . implode('&', $args));

if ($editform->is_cancelled()) {
    // user clicked cancel button
    redirect($default_url);
} else if ($data = $editform->get_data()) {
    // data is validated
    $map = TurningTechDeviceMap::generateFromForm($data);
    if ($map->save()) {
        turningtech_set_message(get_string('deviceidsaved', 'turningtech'));
        redirect($default_url);
    } else {
        error(get_string('errorsavingdeviceid', 'turningtech'), $default_url);
    }
} else {
    // display form page
    //require_course_login($course);
    $PAGE->set_url('/mod/turningtech/edit_device.php', array(
        'course' => $courseid,
        'student' => $studentid
    ));
    //$PAGE->set_course($course);
    //$context = get_context_instance(CONTEXT_COURSE, $course->id);
    $title = get_string('modulename', 'turningtech');

    // what is the "right" way to add CSS?
    $PAGE->requires->css('/mod/turningtech/css/style.css');

    /// Print the header
    $PAGE->set_heading($title);
    echo $OUTPUT->header();

    $dto = new stdClass();
    if (!empty($devicemap)) {
        // get data for the form
        $dto              = $devicemap->getData();
        // rename id field
        $dto->devicemapid = $dto->id;
        unset($dto->id);
        //set the current course in case we need it
    } else {
        // set data if we are creating a new device map
        $dto->userid       = $student->id;
        $dto->all_courses  = 1; // Device registered for All Courses.
        $dto->typeid = 1;       // Device registered for Response Card.
    }

    $dto->courseid = $course->id;
    // save data to the form
    $editform->set_data($dto);
    // display the form

    $editform->display();

    if (!empty($devicemap)) {
        echo "<p class='tt_delete_device'><a href='{$CFG->wwwroot}/mod/turningtech/delete_device.php?id={$devicemap->getField('id')}&course={$course->id}'>";
        echo get_string('deletethisdeviceid', 'turningtech');
        echo "</a></p>\n";
    }
    echo $OUTPUT->footer();
}
?>