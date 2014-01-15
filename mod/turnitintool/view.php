<?php
/**
 * @package   turnitintool
 * @copyright 2012 Turnitin
 */

require_once("../../config.php");
require_once("lib.php");
require_once("../../lib/formslib.php");
require_once("../../lib/form/text.php");
require_once("../../lib/form/datetimeselector.php");
require_once("../../lib/form/hidden.php");
require_once("../../lib/form/button.php");
require_once("../../lib/form/submit.php");
if (!turnitintool_check_config()) {
    turnitintool_print_error('configureerror','turnitintool',NULL,NULL,__FILE__,__LINE__);
    exit();
}
$viewpage='view.php';
$viewpage .= (isset($_REQUEST['id'])) ? '?id='.$_REQUEST['id'] : '';
$viewpage .= (isset($_REQUEST['do'])) ? '&do='.$_REQUEST['do'] : '';
turnitintool_activitylog($viewpage,"REQUEST");
turnitintool_activitylog("lib.php Loaded","REQUIRE_ONCE");
require_once($CFG->dirroot."/lib/uploadlib.php");
turnitintool_activitylog("uploadlib.php Loaded","REQUIRE_ONCE");

turnitintool_process_api_error();

$id = required_param('id', PARAM_INT); // Course Module ID, or
$a  = optional_param('a', 0, PARAM_INT);  // turnitintool ID

if ($id) {
    if (! $cm = get_coursemodule_from_id('turnitintool', $id)) {
        turnitintool_print_error("Course Module ID was incorrect");
    }

    if (! $course = turnitintool_get_record("course", "id", $cm->course)) {
        turnitintool_print_error("Course is misconfigured");
    }

    if (! $turnitintool = turnitintool_get_record("turnitintool", "id", $cm->instance)) {
        turnitintool_print_error("Course module is incorrect");
    }

} else {
    if (! $turnitintool = turnitintool_get_record("turnitintool", "id", $a)) {
        turnitintool_print_error("Course module is incorrect");
    }
    if (! $course = turnitintool_get_record("course", "id", $turnitintool->course)) {
        turnitintool_print_error("Course is misconfigured");
    }
    if (! $cm = get_coursemodule_from_instance("turnitintool", $turnitintool->id, $course->id)) {
        turnitintool_print_error("Course Module ID was incorrect");
    }
}

require_login($course->id);

if (isset($PAGE) AND is_callable(array($PAGE->requires, 'js'))) { // Are we using new moodle or old?
    $jsurl = new moodle_url($CFG->wwwroot.'/mod/turnitintool/scripts/jquery-1.7.2.min.js');
    $PAGE->requires->js($jsurl,true);
    $jsurl = new moodle_url($CFG->wwwroot.'/mod/turnitintool/scripts/datatables.min.js');
    $PAGE->requires->js($jsurl);
    $jsurl = new moodle_url($CFG->wwwroot.'/mod/turnitintool/scripts/datatables.plugins.js');
    $PAGE->requires->js($jsurl);
    $jsurl = new moodle_url($CFG->wwwroot.'/mod/turnitintool/scripts/inboxtable.js');
    $PAGE->requires->js($jsurl);
    $jsurl = new moodle_url($CFG->wwwroot.'/mod/turnitintool/scripts/turnitintool.js');
    $PAGE->requires->js($jsurl,true);
    $cssurl = new moodle_url($CFG->wwwroot.'/mod/turnitintool/styles.css');
    $PAGE->requires->css($cssurl);
} else {
    require_js($CFG->wwwroot.'/mod/turnitintool/scripts/jquery-1.7.2.min.js');
    require_js($CFG->wwwroot.'/mod/turnitintool/scripts/turnitintool.js');
}
turnitintool_activitylog("turnitintool.js Loaded","REQUIRE_JS");

$param_jumppage=optional_param('jumppage',null,PARAM_CLEAN);
$param_userid=optional_param('userid',null,PARAM_CLEAN);
$param_post=optional_param('post',null,PARAM_CLEAN);
$param_delete=optional_param('delete',null,PARAM_CLEAN);
$param_update=optional_param('update',null,PARAM_CLEAN);
$param_do=optional_param('do',null,PARAM_CLEAN);
$param_enroll=optional_param('enroll',null,PARAM_CLEAN);
$param_owner=optional_param('owner',null,PARAM_CLEAN);
$param_anonid=optional_param('anonid',null,PARAM_CLEAN);
$param_updategrade=optional_param('updategrade',null,PARAM_CLEAN);
$param_up=optional_param('up',null,PARAM_CLEAN);
$param_submissiontype=optional_param('submissiontype',null,PARAM_CLEAN);
$param_submitted=optional_param('submitted',null,PARAM_CLEAN);
$param_delpart=optional_param('delpart',null,PARAM_CLEAN);
$param_unenrol=optional_param('unenrol',null,PARAM_CLEAN);
$param_enroltutor=optional_param('enroltutor',null,PARAM_CLEAN);
$param_s=optional_param('s',null,PARAM_CLEAN);
$param_ob=optional_param('ob',null,PARAM_CLEAN);
$param_sh=optional_param('sh',null,PARAM_CLEAN);
$param_fr=optional_param('fr',null,PARAM_CLEAN);
$param_export_data=optional_param('export_data',null,PARAM_CLEAN);
$param_objectid=optional_param('objectid',null,PARAM_CLEAN);
$param_partid=optional_param('partid',null,PARAM_CLEAN);
$param_utp=optional_param('utp',null,PARAM_CLEAN);
$param_enrollstudent=optional_param('enrollstudent',null,PARAM_CLEAN);
$param_reloadrow=optional_param('reloadrow',null,PARAM_CLEAN);

// Clean the post array so we can pass it into the functions below
$post = array();
foreach ( $_POST as $key => $value ) {
    // If 1.9 use clean_param to clean an array  / if 2.0 use clean_param_array
    if ( is_array( $value ) AND is_callable( 'clean_param_array' ) ) {
        $post[$key] = clean_param_array( $value, PARAM_CLEAN );
    } else {
        $post[$key] = clean_param( $value, PARAM_CLEAN );
    }
}

if (!is_null($param_jumppage)) {
    turnitintool_url_jumpto($param_userid,$param_jumppage,$turnitintool,$param_utp,$param_objectid,$param_partid,$param_export_data);
    exit();
}

if (!is_null($param_delete)) {
    if (!$submission = turnitintool_get_record('turnitintool_submissions','id',$param_delete)) {
        print_error('submissiongeterror','turnitintool');
        exit();
    }
    turnitintool_delete_submission($cm,$turnitintool,$USER->id,$submission);
    exit();
}

$redirectlink=$CFG->wwwroot.'/mod/turnitintool/view.php?id='.$cm->id;
$redirectlink.=(!is_null($param_do)) ? '&do='.$param_do : '&do=intro';
$redirectlink.=(!is_null($param_fr)) ? '&fr='.$param_fr : '';
$redirectlink.=(!is_null($param_sh)) ? '&sh='.$param_sh : '';
$redirectlink.=(!is_null($param_ob)) ? '&ob='.$param_ob : '';

if (!is_null($param_reloadrow)) {
    turnitintool_reloadinbox_row($cm,$turnitintool,$param_reloadrow);
    exit();
}

if (!is_null($param_update)) {
    $loaderbar = new turnitintool_loaderbarclass(2);
    turnitintool_update_all_report_scores($cm,$turnitintool,$param_update,$loaderbar);
    turnitintool_redirect($redirectlink);
    exit();
}

if (!is_null($param_enrollstudent)) {
    turnitintool_enroll_student($cm,$turnitintool,$param_enrollstudent);
}

if (!is_null($param_enroll)) {
    turnitintool_enroll_all_students($cm,$turnitintool);
    turnitintool_redirect($redirectlink);
    exit();
}

if (!is_null($param_do) AND $param_do=="tutors" AND !is_null($param_unenrol)) {
    $tutors=turnitintool_remove_tiitutor($cm,$turnitintool,$param_unenrol);
    if (!is_null($tutors->error)) {
        $notice=$tutors->error;
    } else {
        turnitintool_redirect($CFG->wwwroot.'/mod/turnitintool/view.php?id='.$cm->id.'&do=tutors');
        exit();
    }
}

if (!is_null($param_do) AND $param_do=="tutors" AND !is_null($param_enroltutor)) {
    $tutors=turnitintool_add_tiitutor($cm,$turnitintool,$param_enroltutor);
    if (!is_null($tutors->error)) {
        $notice=$tutors->error;
    } else {
        turnitintool_redirect($CFG->wwwroot.'/mod/turnitintool/view.php?id='.$cm->id.'&do=tutors');
        exit();
    }
}

if (!is_null($param_do) AND $param_do=="tutors" AND is_null($param_unenrol) AND is_null($param_enroltutor)) {
    $tutors=turnitintool_get_tiitutors($cm,$turnitintool);
    if (!is_null($tutors->error)) {
        $notice=$tutors->error;
    } else {
        $notice=null;
    }
}

if (!is_null($param_do) AND $param_do=="changeowner") {
    turnitintool_ownerprocess($cm,$turnitintool,$param_owner);
    turnitintool_redirect($CFG->wwwroot.'/mod/turnitintool/view.php?id='.$cm->id.'&do=tutors');
    exit();
}

if (!is_null($param_do) AND $turnitintool->autoupdates==1 AND $param_do=="allsubmissions" AND !is_null($param_anonid)) {
    $loaderbar = new turnitintool_loaderbarclass(3);
    turnitintool_revealuser($cm,$turnitintool,$post,$loaderbar);
    turnitintool_redirect($CFG->wwwroot.'/mod/turnitintool/view.php?id='.$cm->id.'&do=allsubmissions&update=1');
    exit();
}

if (!is_null($param_updategrade) OR isset($post['updategrade']) OR isset($post["updategrade_x"])) {
    turnitintool_update_form_grades($cm,$turnitintool,$post);
}

if (!is_null($param_up)) { // Manual Submission to Turnitin
    if (!$submission = turnitintool_get_record('turnitintool_submissions','id',$param_up)) {
        print_error('submissiongeterror','turnitintool');
        exit();
    }
    turnitintool_upload_submission($cm,$turnitintool,$submission);
    exit();
}

if (!is_null($param_submissiontype) AND $param_do=='submissions') {
    if (isset($param_userid)) {
        $thisuserid=$param_userid;
    } else {
        $thisuserid=$USER->id;
    }

    if ($param_submissiontype==1) {
        $notice=turnitintool_dofileupload($cm,$turnitintool,$thisuserid,$post);
    } else if ($param_submissiontype==2) {
        $notice=turnitintool_dotextsubmission($cm,$turnitintool,$thisuserid,$post);
    }
    if ($turnitintool->autosubmission AND !empty($notice["subid"])) {
        if (!$submission = turnitintool_get_record('turnitintool_submissions','id',$notice["subid"])) {
            print_error('submissiongeterror','turnitintool');
            exit();
        }
        turnitintool_upload_submission($cm,$turnitintool,$submission);
        exit();
    }
}

if (!is_null($param_submitted) AND $param_do=='intro') {
    $notice=turnitintool_update_partnames($cm,$turnitintool,$post);
}

if (!is_null($param_delpart) AND $param_do=='intro') {
    $notice=turnitintool_delete_part($cm,$turnitintool,$param_delpart);
}

if (!is_null($param_submitted) AND $param_do=='notes') {
    $notice=turnitintool_process_notes($cm,$turnitintool,$param_s,$post);
}

if (!is_null($param_submitted) AND $param_do=='options') {
    $notice=turnitintool_process_options($cm,$turnitintool,$post);
}

add_to_log($course->id, "turnitintool", "view", "view.php?id=$cm->id", "User viewed assignment '$turnitintool->name'", "$cm->id");

/// Print the page header
$strturnitintools = get_string("modulenameplural", "turnitintool");
$strturnitintool  = get_string("modulename", "turnitintool");

if (!is_callable('build_navigation')) {
    $navigation = array(
            array('title' => $course->shortname, 'url' => $CFG->wwwroot."/course/view.php?id=$course->id", 'type' => 'course'),
            array('title' => $strturnitintools, 'url' => $CFG->wwwroot."/mod/turnitintool/index.php?id=$course->id", 'type' => 'activity'),
            array('title' => format_string($turnitintool->name), 'url' => '', 'type' => 'activityinstance')
    );
} else {
    $navigation = build_navigation('',$cm);
}

turnitintool_header($cm,
        $course,
        $_SERVER["REQUEST_URI"],
        $turnitintool->name,
        $SITE->fullname,
        $navigation,
        "",
        "",
        true,
        update_module_button($cm->id, $course->id, $strturnitintool),
        navmenu($course)
);

/// Check to see if groups are being used and abstract for 1.8 if neccessary
if (!is_callable('groups_get_activity_group')) {
    $changegroup = optional_param('group', -1, PARAM_INT);
    $cm->currentgroup=get_and_set_current_group($course, $cm->groupmode, $changegroup);
    setup_and_print_groups($course, $cm->groupmode, $redirectlink);
} else {
    $groupmode = groups_get_activity_groupmode($cm);
    if ($groupmode) {
        groups_get_activity_group($cm, true);
        groups_print_activity_menu($cm, $redirectlink);
    }
}

// Print the main part of the page
echo '<div id="turnitintool_style">';

if (!is_null($param_do)) {
    $do=$param_do;
} else {
    $do='intro';
}

// $do=ACTION
// $do=submissions >>> Student Submission Page
// $do=intro >>> Turnitin Assignment Intro Page
// $do=allsubmissions >>> Tutor View All Submissions
// $do=bulkupload >>> Tutor Bulk Upload Student Submissions
// $do=viewtext >>> View Student Text Submission
// $do=submissiondetails >>> View Submission Details

$studentdos=array('submissions','intro','viewtext','submissiondetails','notes');
$graderdos=array('allsubmissions','options','changeowner','tutors');

// If an unrecognised DO request produce error
if (!in_array($do,$studentdos) AND !in_array($do,$graderdos)) {
    turnitintool_print_error('dorequesterror','turnitintool');
    exit();
} else if (!has_capability('mod/turnitintool:grade', get_context_instance(CONTEXT_MODULE, $cm->id)) AND in_array($do,$graderdos)) {
    turnitintool_print_error('permissiondeniederror','turnitintool');
    exit();
}

echo '<br />';
turnitintool_draw_menu($cm,$do);

if ($do=='intro') {
    if (isset($notice['error'])) {
        turnitintool_box_start('generalbox boxwidthwide boxaligncenter error', 'errorbox');
        echo $notice['message'];
        turnitintool_box_end();
    } else {
        $notice=NULL;
    }
    // Update the GradeBook to make sure the grade stays 'hidden' until and wasn't revealed by modedit
    turnitintool_grade_item_update( $turnitintool );
    echo turnitintool_duplicatewarning($cm,$turnitintool);
    echo turnitintool_introduction($cm,$turnitintool,$notice);
}

if ($do=='submissions') {
    if ( !has_capability('mod/turnitintool:grade', get_context_instance(CONTEXT_MODULE, $cm->id)) 
         AND !has_capability('mod/turnitintool:submit', get_context_instance(CONTEXT_MODULE, $cm->id))) {
        turnitintool_print_error('permissiondeniederror','turnitintool');
        exit();
    } else {
        echo turnitintool_view_student_submissions($cm,$turnitintool);
        if (isset($notice["error"])) {
            turnitintool_box_start('generalbox boxwidthwide boxaligncenter error', 'errorbox');
            echo $notice["error"];
            turnitintool_box_end();
        }
        echo turnitintool_view_submission_form($cm,$turnitintool);
    }
}

if ($do=='allsubmissions') {
    if (!empty($notice)) {
        turnitintool_box_start('generalbox boxwidthwide boxaligncenter error', 'errorbox');
        echo $notice;
        turnitintool_box_end();
    }
    if (isset($param_ob)) {
        $ob=$param_ob;
    } else {
        $ob=1;
    }
    echo turnitintool_view_all_submissions($cm,$turnitintool,$ob);
}

if ($do=='notes') {
    echo turnitintool_view_notes($cm,$turnitintool,$param_s,$post);
    if (isset($notice['error'])) {
        turnitintool_box_start('generalbox boxwidthwide boxaligncenter error', 'errorbox');
        echo $notice['message'];
        turnitintool_box_end();
    } else {
        $notice=NULL;
    }
    echo turnitintool_addedit_notes($cm,$turnitintool,$param_s,$post,$notice);

}

if ($do=='options') {
    if (!empty($notice)) {
        turnitintool_box_start('generalbox boxwidthwide boxaligncenter', 'general');
        echo $notice;
        turnitintool_box_end();
    }
    echo turnitintool_view_options($cm,$turnitintool);
}

if ($do=='tutors') {
    if (!is_null($notice)) {
        turnitintool_box_start('generalbox boxwidthwide boxaligncenter error', 'errorbox');
        echo $notice;
        turnitintool_box_end();
    } else {
        echo turnitintool_view_tiitutors($cm,$turnitintool,$tutors);
    }
}

// Finish the page
echo '</div>';

if (isset($PAGE) AND @is_callable(array($PAGE->requires, 'js'))) { // Are we using new moodle or old?
    // We already added the Moodle 2.0+ stuff
} else {
    // These need to go to the botton here to avoid conflicts
    require_js($CFG->wwwroot.'/mod/turnitintool/scripts/datatables.min.js');
    require_js($CFG->wwwroot.'/mod/turnitintool/scripts/datatables.plugins.js');
    require_js($CFG->wwwroot.'/mod/turnitintool/scripts/inboxtable.js');
}

turnitintool_footer($course);
$module=turnitintool_get_record('modules','name','turnitintool');
$parts=turnitintool_get_records('turnitintool_parts','turnitintoolid',$turnitintool->id);
$parts_string="(";
foreach ($parts as $part) {
    $parts_string.=($parts_string!="(") ? " | " : "";
    $parts_string.= $part->partname.': '.$part->tiiassignid;
}
$parts_string.=")";
echo '<!-- Turnitin Moodle Direct Version: '.$module->version.' - '.$parts_string.' -->';

/* ?> */