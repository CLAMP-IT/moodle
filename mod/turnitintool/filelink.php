<?php
/**
 * @package   turnitintool
 * @copyright 2012 Turnitin
 */

require_once("../../config.php");
require_once("lib.php");

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
$param_sub=optional_param('sub',null,PARAM_CLEAN);
$param_type=optional_param('type',null,PARAM_CLEAN);
$param_part=optional_param('part',null,PARAM_CLEAN);
$param_papers=optional_param('papers',null,PARAM_CLEAN);

if (!is_null($param_sub)) {

    if (!$submission = turnitintool_get_record('turnitintool_submissions','id',$param_sub)) {
        turnitintool_print_error('submissiongeterror', 'turnitintool', $CFG->wwwroot.'/mod/turnitintool/view.php?id='.$cm->id, NULL, __FILE__, __LINE__);
        exit();
    }

    if (!has_capability('mod/turnitintool:grade', get_context_instance(CONTEXT_MODULE, $cm->id)) AND $USER->id!=$submission->userid) {
        // Check to see if the user logged in is the user that submitted or is a grader (tutor)
        turnitintool_print_error('permissiondeniederror','turnitintool',NULL,NULL,__FILE__,__LINE__);
        exit();
    }

    $fs = get_file_storage();
    $file = $fs->get_file($cm->id,'mod_turnitintool','submission',$submission->id,'/',$submission->submission_filename);
    send_stored_file($file, 0, 0, true);

} if (!is_null($param_part)) {

    if (!has_capability('mod/turnitintool:grade', get_context_instance(CONTEXT_MODULE, $cm->id))) {
        turnitintool_print_error('permissiondeniederror','turnitintool',NULL,NULL,__FILE__,__LINE__);
        exit();
    }

    $owner=turnitintool_get_owner($course->id);
    $loaderbar = null;
    $tii = new turnitintool_commclass(turnitintool_getUID($owner),$owner->firstname,$owner->lastname,$owner->email,2,$loaderbar);

    $post = new stdClass();
    $post->cid=turnitintool_getCID($course->id);
    $post->assignid=turnitintool_getAID($param_part);
    $post->ctl=turnitintool_getCTL($course->id);
    $post->assign=$turnitintool->name.' - '.turnitintool_partnamefromnum($param_part).' (Moodle '.$post->assignid.')';
    $post->fcmd=4;
    $tii->listSubmissions($post, get_string('downloadingfile','turnitintool'));

    if ($tii->getRerror()) {
        if (!$tii->getAPIunavailable()) {
            $reason=($tii->getRcode()==TURNITINTOOL_DB_UNIQUEID_ERROR) ? get_string('assignmentdoesnotexist','turnitintool') : $tii->getRmessage();
        } else {
            $reason=get_string('apiunavailable','turnitintool');
        }
        turnitintool_print_error('downloadingfileerror','turnitintool',NULL,NULL,__FILE__,__LINE__);
        exit();
    } else {
        $output = $tii->getFileData();
        if (function_exists('mb_strlen')) {
            $size = mb_strlen($output, '8bit');
        } else {
            $size = strlen($output);
        }
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-control: must-revalidate, post-check=0, pre-check=0");
        header("Content-type: application/force-download");
        header("Content-type: application/octet-stream");
        header("Content-type: application/download");;
        header("Content-disposition: attachment; filename=".get_string('file','turnitintool')."_".$post->assignid.".xls");
        header("Content-transfer-encoding: binary ");
        header("Content-length: " . $size);

        echo $output;
    }

}

//?>
