<?php

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Library of interface functions and constants for module nanogong
 *
 * All the core Moodle functions, needed to allow the module to work
 * integrated in Moodle should be placed here.
 * All the nanogong specific functions, needed to implement all the module
 * logic, should go to locallib.php. This will help to save some memory when
 * Moodle is performing actions across all modules.
 *
 * @author     Ning
 * @author     Gibson
 * @package    mod
 * @subpackage nanogong
 * @copyright  2012 The Gong Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @version    4.2.2
 */

defined('MOODLE_INTERNAL') || die();

/** Include eventslib.php */
require_once($CFG->libdir.'/eventslib.php');
/** Include formslib.php */
require_once($CFG->libdir.'/formslib.php');
/** Include calendar/lib.php */
require_once($CFG->dirroot.'/calendar/lib.php');
/** Include grouplib.php */
require_once($CFG->libdir.'/grouplib.php');

/** example constant */
//define('NANOGONG_ULTIMATE_ANSWER', 42);

////////////////////////////////////////////////////////////////////////////////
// Moodle core API                                                            //
////////////////////////////////////////////////////////////////////////////////

/**
 * Returns the information on whether the module supports a feature
 *
 * @see plugin_supports() in lib/moodlelib.php
 * @param string $feature FEATURE_xx constant for requested feature
 * @return mixed true if the feature is supported, null if unknown
 */
function nanogong_supports($feature) {
    if (defined('FEATURE_MOD_INTRO') && $feature == FEATURE_MOD_INTRO) return true;
    if (defined('FEATURE_GRADE_HAS_GRADE') && $feature == FEATURE_GRADE_HAS_GRADE) return true;
    if (defined('FEATURE_SHOW_DESCRIPTION') && $feature == FEATURE_SHOW_DESCRIPTION) return true;
    if (defined('FEATURE_BACKUP_MOODLE2') && $feature == FEATURE_BACKUP_MOODLE2) return true;
}

/**
 * Saves a new instance of the nanogong into the database
 *
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will create a new instance and return the id number
 * of the new instance.
 *
 * @param object $nanogong An object from the form in mod_form.php
 * @param mod_nanogong_mod_form $mform
 * @return int The id of the newly inserted nanogong record
 */
function nanogong_add_instance(stdClass $nanogong, mod_nanogong_mod_form $mform = null) {
    global $DB;

    $nanogong->timecreated = time();

    $nanogong->id = $DB->insert_record("nanogong", $nanogong);

    $event = new stdClass();
    $event->name        = $nanogong->name;
    $event->description = format_module_intro('nanogong', $nanogong, $nanogong->coursemodule);
    $event->courseid    = $nanogong->course;
    $event->groupid     = 0;
    $event->userid      = 0;
    $event->modulename  = 'nanogong';
    $event->instance    = $nanogong->id;
    $event->eventtype   = 'due';
    $event->timestart   = $nanogong->timedue;
    $event->timeduration = 0;

    calendar_event::create($event);

    nanogong_grade_item_update($nanogong);

    return $nanogong->id;
}

/**
 * Updates an instance of the nanogong in the database
 *
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will update an existing instance with new data.
 *
 * @param object $nanogong An object from the form in mod_form.php
 * @param mod_nanogong_mod_form $mform
 * @return boolean Success/Fail
 */
function nanogong_update_instance(stdClass $nanogong, mod_nanogong_mod_form $mform = null) {
    global $DB;

    $nanogong->timemodified = time();
    $nanogong->id = $nanogong->instance;

    $DB->update_record('nanogong', $nanogong);

    if ($nanogong->timedue) {
        $event = new stdClass();

        if ($event->id = $DB->get_field('event', 'id', array('modulename'=>'nanogong', 'instance'=>$nanogong->id))) {
            $event->name        = $nanogong->name;
            $event->description = format_module_intro('nanogong', $nanogong, $nanogong->coursemodule);
            $event->timestart   = $nanogong->timedue;

            $calendarevent = calendar_event::load($event->id);
            $calendarevent->update($event);
        }
        else {
            $event = new stdClass();
            $event->name        = $nanogong->name;
            $event->description = format_module_intro('nanogong', $nanogong, $nanogong->coursemodule);
            $event->courseid    = $nanogong->course;
            $event->groupid     = 0;
            $event->userid      = 0;
            $event->modulename  = 'nanogong';
            $event->instance    = $nanogong->id;
            $event->eventtype   = 'due';
            $event->timestart   = $nanogong->timedue;
            $event->timeduration = 0;

            calendar_event::create($event);
        }
    }
    else {
        $DB->delete_records('event', array('modulename'=>'nanogong', 'instance'=>$nanogong->id));
    }

    // get existing grade item
    nanogong_grade_item_update($nanogong);

    return true;
}


/**
 * Removes an instance of the nanogong from the database
 *
 * Given an ID of an instance of this module,
 * this function will permanently delete the instance
 * and any data that depends on it.
 *
 * @param int $id Id of the module instance
 * @return boolean Success/Failure
 */
function nanogong_delete_instance($id) {
    global $CFG, $DB;

    if (! $nanogong = $DB->get_record('nanogong', array('id'=>$id))) {
        return false;
    }

    $result = true;

    // now get rid of all files
    $fs = get_file_storage();
    if ($cm = get_coursemodule_from_instance('nanogong', $nanogong->id)) {
        $context = get_context_instance(CONTEXT_MODULE, $cm->id);
        $fs->delete_area_files($context->id);
    }

    if (! $DB->delete_records('nanogong_messages', array('nanogongid'=>$nanogong->id))) {
        $result = false;
    }
    
    if (! $DB->delete_records('nanogong_audios', array('nanogongid'=>$nanogong->id))) {
        $result = false;
    }

    if (! $DB->delete_records('event', array('modulename'=>'nanogong', 'instance'=>$nanogong->id))) {
        $result = false;
    }

    if (! $DB->delete_records('nanogong', array('id'=>$nanogong->id))) {
        $result = false;
    }
    $mod = $DB->get_field('modules','id',array('name'=>'nanogong'));

    nanogong_grade_item_delete($nanogong);

    return $result;
}

/**
 * Returns a small object with summary information about what a
 * user has done with a given particular instance of this module
 * Used for user activity reports.
 * $return->time = the time they did it
 * $return->info = a short text description
 *
 * @return stdClass|null
 */
function nanogong_user_outline($course, $user, $mod, $nanogong) {
    global $CFG;

    require_once("$CFG->libdir/gradelib.php");
    
    $grade = nanogong_get_user_grades($nanogong, $user->id);
    if ($grade > -1) {
        $result = new stdClass();
        $result->info = get_string('grade').': '.$grade;
        $result->time = '';
        return $result;
    }
    else {
        return null;
    }
}

/**
 * Prints a detailed representation of what a user has done with
 * a given particular instance of this module, for user activity reports.
 *
 * @param stdClass $course the current course record
 * @param stdClass $user the record of the user we are generating report for
 * @param cm_info $mod course module info
 * @param stdClass $nanogong the module instance record
 * @return void, is supposed to echp directly
 */
function nanogong_user_complete($course, $user, $mod, $nanogong) {
}

/**
 * Given a course and a time, this module should find recent activity
 * that has occurred in nanogong activities and print it out.
 * Return true if there was output, or false is there was none.
 *
 * @return boolean
 */
function nanogong_print_recent_activity($course, $viewfullnames, $timestart) {
    return false;  //  True if anything was printed, otherwise false
}

/**
 * Prepares the recent activity data
 *
 * This callback function is supposed to populate the passed array with
 * custom activity records. These records are then rendered into HTML via
 * {@link nanogong_print_recent_mod_activity()}.
 *
 * @param array $activities sequentially indexed array of objects with the 'cmid' property
 * @param int $index the index in the $activities to use for the next record
 * @param int $timestart append activity since this time
 * @param int $courseid the id of the course we produce the report for
 * @param int $cmid course module id
 * @param int $userid check for a particular user's activity only, defaults to 0 (all users)
 * @param int $groupid check for a particular group's activity only, defaults to 0 (all groups)
 * @return void adds items into $activities and increases $index
 */
function nanogong_get_recent_mod_activity(&$activities, &$index, $timestart, $courseid, $cmid, $userid=0, $groupid=0) {
}

/**
 * Prints single activity item prepared by {@see nanogong_get_recent_mod_activity()}

 * @return void
 */
function nanogong_print_recent_mod_activity($activity, $courseid, $detail, $modnames, $viewfullnames) {
}

/**
 * Function to be run periodically according to the moodle cron
 * This function searches for things that need to be done, such
 * as sending out mail, toggling flags etc ...
 *
 * @return boolean
 * @todo Finish documenting this function
 **/
function nanogong_cron () {
    return true;
}

/**
 * Returns an array of users who are participanting in this nanogong
 *
 * Must return an array of users who are participants for a given instance
 * of nanogong. Must include every user involved in the instance,
 * independient of his role (student, teacher, admin...). The returned
 * objects must contain at least id property.
 * See other modules as example.
 *
 * @param int $nanogongid ID of an instance of this module
 * @return boolean|array false if no participants, array of objects otherwise
 */
function nanogong_get_participants($nanogongid) {
    global $CFG, $DB;

    //Get students
    $students = $DB->get_records_sql("SELECT DISTINCT u.id, u.id
                                        FROM {user} u,
                                             {nanogong_messages} n
                                       WHERE n.nanogongid = ? and
                                             u.id = n.userid", array($nanogongid));
    return ($students);
}

/**
 * Returns all other caps used in the module
 *
 * @example return array('moodle/site:accessallgroups');
 * @return array
 */
function nanogong_get_extra_capabilities() {
    return array();
}

////////////////////////////////////////////////////////////////////////////////
// Gradebook API                                                              //
////////////////////////////////////////////////////////////////////////////////

/**
 * Is a given scale used by the instance of nanogong?
 *
 * This function returns if a scale is being used by one nanogong
 * if it has support for grading and scales. Commented code should be
 * modified if necessary. See forum, glossary or journal modules
 * as reference.
 *
 * @param int $nanogongid ID of an instance of this module
 * @return bool true if the scale is used by the given nanogong instance
 */
function nanogong_scale_used($nanogongid, $scaleid) {
    global $DB;

    if ($scaleid and $DB->record_exists('nanogong', array('id' => $nanogongid, 'grade' => -$scaleid))) {
        return true;
    } else {
        return false;
    }
}

/**
 * Checks if scale is being used by any instance of nanogong.
 *
 * This is used to find out if scale used anywhere.
 *
 * @param $scaleid int
 * @return boolean true if the scale is used by any nanogong instance
 */
function nanogong_scale_used_anywhere($scaleid) {
    global $DB;

    if ($scaleid and $DB->record_exists('nanogong', array('grade' => -$scaleid))) {
        return true;
    } else {
        return false;
    }
}

/**
 * Creates or updates grade item for the give nanogong instance
 *
 * Needed by grade_update_mod_grades() in lib/gradelib.php
 *
 * @param stdClass $nanogong instance object with extra cmidnumber and modname property
 * @return void
 */
function nanogong_grade_item_update(stdClass $nanogong, $grades=NULL) {
    global $CFG;
    require_once($CFG->libdir.'/gradelib.php');

    $item = array();
    $item['itemname'] = clean_param($nanogong->name, PARAM_NOTAGS);
    $item['gradetype'] = GRADE_TYPE_VALUE;
    $item['grademax']  = $nanogong->grade;
    $item['grademin']  = 0;

    return grade_update('mod/nanogong', $nanogong->course, 'mod', 'nanogong', $nanogong->id, 0, $grades, $item);
}

/**
 * Delete grade item for given nanogong
 *
 * @param object $nanogong object
 * @return object nanogong
 */
function nanogong_grade_item_delete($nanogong) {
    global $CFG;
    require_once($CFG->libdir.'/gradelib.php');

    if (!isset($nanogong->courseid)) {
        $nanogong->courseid = $nanogong->course;
    }

    return grade_update('mod/nanogong', $nanogong->courseid, 'mod', 'nanogong', $nanogong->id, 0, NULL, array('deleted'=>1));
}

/**
 * Return grade for given user or all users.
 *
 * @param int $nanogongid id of nanogong
 * @param int $userid optional user id, 0 means all users
 * @return array array of grades, false if none
 */
function nanogong_get_user_grades($nanogong, $userid=0) {
    global $CFG, $DB;

    if ($userid) {
        $user = "AND u.id = :userid";
        $params = array('userid'=>$userid);
    } else {
        $user = "";
    }
    $params['nid'] = $nanogong->id;

    $sql = "SELECT u.id, u.id AS userid, m.grade AS rawgrade, m.comments AS feedback, m.commentsformat AS feedbackformat, m.commentedby AS usermodified, m.timestamp AS dategraded
              FROM {user} u, {nanogong_messages} m
             WHERE u.id = m.userid AND m.nanogongid = :nid
                   $user";

    return $DB->get_records_sql($sql, $params);
}

/**
 * Update nanogong grades in the gradebook
 *
 * Needed by grade_update_mod_grades() in lib/gradelib.php
 *
 * @param stdClass $nanogong instance object with extra cmidnumber and modname property
 * @param int $userid update grade of specific user only, 0 means all participants
 * @return void
 */
function nanogong_update_grades(stdClass $nanogong, $userid = 0, $nullifnone=true) {
    global $CFG, $DB;
    require_once($CFG->libdir.'/gradelib.php');

    if ($nanogong->grade == 0) {
        nanogong_grade_item_update($nanogong);
    }
    else if ($grades = nanogong_get_user_grades($nanogong, $userid)) {
        foreach($grades as $k=>$v) {
            if ($v->rawgrade == -1) {
                $grades[$k]->rawgrade = null;
            }
        }
        nanogong_grade_item_update($nanogong, $grades);
    }
    else {
        nanogong_grade_item_update($nanogong);
    }
}

////////////////////////////////////////////////////////////////////////////////
// File API                                                                   //
////////////////////////////////////////////////////////////////////////////////

/**
 * Returns the lists of all browsable file areas within the given module context
 *
 * The file area 'intro' for the activity introduction field is added automatically
 * by {@link file_browser::get_file_info_context_module()}
 *
 * @param stdClass $course
 * @param stdClass $cm
 * @param stdClass $context
 * @return array of [(string)filearea] => (string)description
 */
function nanogong_get_file_areas($course, $cm, $context) {
    $areas = array();
    if (has_capability('moodle/course:managefiles', $context)) {
        $areas['audio'] = get_string('nanogongaudios', 'nanogong');
        $areas['message'] = get_string('nanogongmessages', 'nanogong');
    }
    return $areas;
}

/**
 * Serves the files from the nanogong file areas
 *
 * @param stdClass $course
 * @param stdClass $cm
 * @param stdClass $context
 * @param string $filearea
 * @param array $args
 * @param bool $forcedownload
 * @return void this should never return to the caller
 */
function nanogong_pluginfile($course, $cm, $context, $filearea, array $args, $forcedownload=false) {
    global $DB, $CFG, $USER;

    if ($context->contextlevel != CONTEXT_MODULE) {
        send_file_not_found();
    }

    require_login($course, true, $cm);

    if (!$nanogong = $DB->get_record('nanogong', array('id'=>$cm->instance))) {
        send_file_not_found();
    }
    
    require_capability('mod/nanogong:view', $context);

    $fullpath = "/{$context->id}/mod_nanogong/$filearea/".implode('/', $args);

    $fs = get_file_storage();
    if (!$file = $fs->get_file_by_hash(sha1($fullpath)) or $file->is_directory()) {
        send_file_not_found();
    }

    session_get_instance()->write_close(); // unlock session during fileserving
    send_stored_file($file, 60*60, 0, true);
}

////////////////////////////////////////////////////////////////////////////////
// Navigation API                                                             //
////////////////////////////////////////////////////////////////////////////////

/**
 * Extends the global navigation tree by adding nanogong nodes if there is a relevant content
 *
 * This can be called by an AJAX request so do not rely on $PAGE as it might not be set up properly.
 *
 * @param navigation_node $navref An object representing the navigation tree node of the nanogong module instance
 * @param stdClass $course
 * @param stdClass $module
 * @param cm_info $cm
 */
function nanogong_extend_navigation(navigation_node $navref, stdclass $course, stdclass $module, cm_info $cm) {
}

/**
 * Extends the settings navigation with the nanogong settings
 *
 * This function is called when the context for the page is a nanogong module. This is not called by AJAX
 * so it is safe to rely on the $PAGE.
 *
 * @param settings_navigation $settingsnav {@link settings_navigation}
 * @param navigation_node $nanogongnode {@link navigation_node}
 */
function nanogong_extend_settings_navigation(settings_navigation $settingsnav, navigation_node $nanogongnode=null) {
}

class mod_nanogong_grade_form extends moodleform {
    function definition() {
        global $PAGE;

        $mform = $this->_form;

        list($data, $editoroptions, $url, $maxgrade) = $this->_customdata;

        // Main content
        $gradetitle = get_string('grade', 'nanogong') . get_string('outof', 'nanogong') . $maxgrade;
        $mform->addElement('text', 'grade', $gradetitle, array('maxlength'=>30, 'size'=>25));
        $mform->setType('grade', PARAM_INT);
        $grademsg = get_string('wronggrade', 'nanogong') . $maxgrade;
        $mform->addRule('grade', $grademsg, 'numeric', null, 'client');
        $mform->addElement('applet', 'nanogonginstance', get_string('voicerecording', 'nanogong'), array('id'=>'nanogonggradeinstance', 'archive'=>'nanogong.jar', 'code'=>'gong.NanoGong', 'width'=>180, 'height'=>40));
        if ($url) {
            $mform->addElement('button', 'nanogong_loadaudio_button', get_string('loadcurrent', 'nanogong'), array('path'=>$url));
        }
        $mform->addElement('editor', 'comments_editor', get_string('yourmessage', 'nanogong'), null, $editoroptions);
        $mform->setType('comments_editor', PARAM_RAW);
        $mform->addElement('checkbox', 'locked', get_string('lockstudent', 'nanogong'));

        // Hidden params
        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);
        $mform->addElement('hidden', 'action');
        $mform->setType('action', PARAM_TEXT);
        $mform->addElement('hidden', 'catalog');
        $mform->setType('catalog', PARAM_TEXT);
        $mform->addElement('hidden', 'topage');
        $mform->setType('topage', PARAM_INT);
        $mform->addElement('hidden', 'perpage');
        $mform->setType('perpage', PARAM_INT);
        $mform->addElement('hidden', 'student');
        $mform->setType('student', PARAM_INT);
        $mform->addElement('hidden', 'maxduration');
        $mform->setType('maxduration', PARAM_INT);
        $mform->addElement('hidden', 'url');
        $mform->setType('url', PARAM_TEXT);
        
        // Buttons
        $buttonarray = array();
        $buttonarray[] = &$mform->createElement('button', 'nanogong_saveaudio_button', get_string('savechanges'));
        $buttonarray[] = &$mform->createElement('cancel', 'cancelbutton', get_string('cancel'));
        $mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
        $mform->closeHeaderBefore('buttonar');

        $this->set_data($data);
    }
}

class mod_nanogong_supplement_form extends moodleform {
    function definition() {
        $mform = $this->_form;

        list($data, $editoroptions) = $this->_customdata;

        // Main content
        $mform->addElement('editor', 'supplement_editor', get_string('messagefor', 'nanogong'), null, $editoroptions);
        $mform->setType('supplement_editor', PARAM_RAW);
        
        // Hidden params
        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);
        $mform->addElement('hidden', 'action');
        $mform->setType('action', PARAM_TEXT);
        $mform->addElement('hidden', 'maxduration');
        $mform->setType('maxduration', PARAM_INT);
        
        // Buttons
        $this->add_action_buttons();
        
        $this->set_data($data);
    }
}
