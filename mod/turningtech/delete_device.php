<?php
/***
 * displays the confirmation form for deleting a device ID map
 */


include_once('../../config.php');

//print "<pre>";
// print_r ($CFG);
// die('dd');

require_once($CFG->dirroot . '/mod/turningtech/lib.php');
global $DB;
$devicemapid = required_param('id', PARAM_INT);
$courseid    = optional_param('course', NULL, PARAM_INT);
$course      = NULL;
$devicemap   = NULL;
if (!$devicemap = TurningTechDeviceMap::fetch(array(
    'id' => $devicemapid
))) {
    error(get_string('couldnotfinddeviceid', 'turningtech', $devicemapid));
}

// has the form been confirmed?
$confirm = optional_param('confirm', 0, PARAM_BOOL);

// figure out which course we're dealing with
if (empty($courseid)) {
    if (!$devicemap->isAllCourses()) {
        $courseid = $devicemap->getField('courseid');
    } else {
        error(get_string('courseidincorrect', 'turningtech'));
    }
}

if (!$course = $DB->get_record('course', array(
    'id' => $courseid
))) {
    error(get_string('courseidincorrect', 'turningtech'));
}

// make sure user is enrolled
require_course_login($course);

// verify user has permission to delete this devicemap
if ($USER->id != $devicemap->getField('userid')) {
    // current user is not the owner of the devicemap.  So
    // verify current user is a teacher
    $context = get_context_instance(CONTEXT_COURSE, $course->id);
    if (!has_capability('mod/turningtech:manage', $context)) {
        error(get_string('notpermittedtoeditdevicemap', 'turningtech'));
    }
}

if ($confirm && confirm_sesskey()) {
    $devicemap->delete();
    turningtech_set_message(get_string('deviceiddeleted', 'turningtech'));
    redirect($CFG->wwwroot . "/mod/turningtech/index.php?id=" . $course->id);
} else {
    // build breadcrumbs
    $PAGE->set_url($CFG->wwwroot . '/mod/turningtech/delete_device.php', array(
        'id' => $devicemapid,
        'course' => $courseid
    ));
    $PAGE->set_course($course);

    $PAGE->requires->css('/mod/turningtech/css/style.css');

    $title      = get_string('modulename', 'turningtech');
    $heading    = get_string('editdevicemap', 'turningtech');
    $navlinks   = array();
    $navlinks[] = array(
        'name' => $title,
        'link' => "{$CFG->wwwroot}/mod/turningtech/index.php?id={$course->id}",
        'type' => 'activity'
    );
    $navlinks[] = array(
        'name' => $heading,
        'link' => '',
        'type' => 'activity'
    );
    $navigation = build_navigation($navlinks);

    echo $OUTPUT->header();
    $optionyes = array(
        'id' => $devicemapid,
        'course' => $course->id,
        'confirm' => 1,
        'sesskey' => sesskey()
    );
    $optionno  = array(
        'id' => $course->id
    );
    $message   = "<p class='tt_confirm_device_delete'>" . get_string('deletedevicemap', 'turningtech', $devicemap->getField('deviceid')) . "</p>";
    echo $OUTPUT->confirm($message, new moodle_url($CFG->wwwroot . '/mod/turningtech/delete_device.php', $optionyes), new moodle_url('/mod/turningtech/index.php', $optionno));
    echo $OUTPUT->footer();
}

?>