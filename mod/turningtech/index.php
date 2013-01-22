<?php // $Id: index.php,v 1.9 2009/03/31 13:03:28 mudrd8mz Exp $

/**
 * This page lists all the instances of turningtech in a particular course
 *
 * @author  Your Name <your@email.address>
 * @version $Id: index.php,v 1.9 2009/03/31 13:03:28 mudrd8mz Exp $
 * @package mod/turningtech
 */

include_once('../../config.php');
include_once('../../course/lib.php');
require_once($CFG->dirroot . '/mod/turningtech/lib.php');
require_once($CFG->dirroot . '/mod/turningtech/lib/forms/turningtech_device_form.php');
require_once($CFG->dirroot . '/mod/turningtech/lib/forms/turningtech_responseware_form.php');
require_once($CFG->dirroot . '/mod/turningtech/lib/helpers/EncryptionHelper.php');
require_once($CFG->dirroot . '/mod/turningtech/lib/helpers/HttpPostHelper.php');
global $PAGE;
// set up javascript requirements
$PAGE->requires->js('/lib/yui/2.9.0/build/yahoo-dom-event/yahoo-dom-event.js');
$PAGE->requires->js('/mod/turningtech/js/turningtech.js');
$id = required_param('id', PARAM_INT); // course
global $DB;
if (!$course = $DB->get_record('course', array(
    'id' => $id
))) {
    error(get_string('courseidincorrect', 'turningtech'));
}
require_login($course);
$PAGE->set_url('/mod/turningtech/index.php', array(
    'id' => $id
));
$PAGE->set_course($course);
add_to_log($course->id, 'turningtech', 'view devices', "index.php?id=$course->id", '');

global $USER;
$context = get_context_instance(CONTEXT_COURSE, $course->id);
$title   = get_string('pluginname', 'turningtech');
$PAGE->navbar->add($title);
$PAGE->set_heading($course->fullname);
// what is the "right" way to add CSS?
$PAGE->requires->css('/mod/turningtech/css/style.css');

// Print the header

echo $OUTPUT->header();
echo $OUTPUT->heading($title);
// determine if this is a student or instructor
if (TurningTechMoodleHelper::isUserStudentInCourse($USER, $course)) {
    // Initializing the Response Card Form Opening as false.
    $leaveResWareFrmOpen = false;
    $rwid                = NULL;
    $rwform              = new turningtech_responseware_form("index.php?id={$id}");
    // process responseware form
    if ($rwdata = $rwform->get_data()) {
        try {
            $rwid                    = doPostRW($CFG->turningtech_responseware_provider, $rwdata->username, $rwdata->password);
            $allParams               = new stdClass();
            $allParams->userid       = $USER->id;
            $allParams->all_courses  = 1;
            $allParams->typeid       = $rwdata->typeid;
            $allParams->deviceid     = $rwid;
            $allParams->deleted      = 0;

            $params              = new stdClass();
            $params->userid      = $USER->id;
            $params->deviceid    = $rwid;
            $params->deleted     = 0;
            $params->all_courses = 1;

            $map = TurningTechDeviceMap::generate($allParams, FALSE);
            if ($existing = TurningTechDeviceMap::fetch($params, FALSE) || TurningTechDeviceMap::isRWAlreadyInUse($allParams)) {
                turningtech_set_message(get_string('deviceidalreadyinuse', 'turningtech'));
                $leaveResWareFrmOpen = true;
            } else if ($map->save()) {
                turningtech_set_message(get_string('deviceidsaved', 'turningtech'));
            } else {
                turningtech_set_message(get_string('errorsavingdeviceid', 'turningtech'), 'error');
                $leaveResWareFrmOpen = true;
            }
        }
        catch (Exception $e) {
            turningtech_set_message(get_string('couldnotauthenticate', 'turningtech', $CFG->turningtech_responseware_provider));
            $leaveResWareFrmOpen = true;
        }
    }
    // Post values got but validation fails, hence show the form with message.
    // If user has tried to register Response Ware device.
    else if (array_key_exists('typeid', $_POST) && $_POST['typeid'] == 2) {
        $leaveResWareFrmOpen = true;
    }

    $dto               = new stdClass();
    $dto->all_courses  = 1;
    $dto->typeid       = 2;
    $rwform->set_data($dto);

    // Initializing the Response Card Form Opening as false.
    $leaveResCardFrmOpen = false;

    // process the edit form
    $editform = new turningtech_device_form("index.php?id={$id}");
    if ($editform->is_cancelled()) {
    } else if ($data = $editform->get_data()) {
        $map = TurningTechDeviceMap::generateFromForm($data);
        if ($map->save()) {
            turningtech_set_message(get_string('deviceidsaved', 'turningtech'));
        } else {
            turningtech_set_message(get_string('errorsavingdeviceid', 'turningtech'), 'error');
            $leaveResCardFrmOpen = true;
        }

    }
    // Post values got but validation fails, hence show the form with message.
    // If user has tried to register Response Card device.
    else if (array_key_exists('typeid', $_POST) && $_POST['typeid'] == 1) {
        $leaveResCardFrmOpen = true;
    }

    // show list of existing devices
    $device_list       = turningtech_list_user_devices($USER, $course);
    // set up and display form for new device map
    $dto               = new stdClass();
    $dto->userid       = $USER->id;
    $dto->courseid     = $course->id;
    /* $dto->deviceid = (empty($rwid) ? '' : $rwid); */
    $dto->all_courses  = 1;
    $dto->typeid       = 1;
    $editform->set_data($dto);

    // call the template to render
    require_once($CFG->dirroot . '/mod/turningtech/lib/templates/student_index.php');

} else {
    // so user is a member of course, but not a student.  Let's make sure they have
    // permission to manage devices
    require_capability('mod/turningtech:manage', $context);
    $action = optional_param('action', 'deviceid', PARAM_ALPHA);
    echo turningtech_show_messages();

    // list actions
    turningtech_list_instructor_actions($USER, $course, $action);
    switch ($action) {
        case 'deviceid':
            turningtech_list_course_devices($course);
            break;
        case 'sessionfile':
            turningtech_import_session_file($course);
            break;
        case 'purge':
            turningtech_import_purge_course_devices($course);
            break;
    }
}

echo $OUTPUT->footer();
?>