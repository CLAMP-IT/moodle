<?php
require_once("../../config.php");
require_once($CFG->dirroot.'/mod/resource/locallib.php');
require_once($CFG->dirroot.'/course/lib.php');
require_once($CFG->dirroot.'/course/format/lib.php');

require_once('annotateUtil.php');


$id       = optional_param('id', 0, PARAM_INT); // resource ID

$owner = optional_param('owner', "", PARAM_TEXT);  // if set, the doc should be owned by this person and
 													 // just shared by the actual user


if (!$cm = get_coursemodule_from_id('resource', $id)) {
        print_error('invalidcoursemodule');
}

$resource = $DB->get_record('resource', array('id'=>$cm->instance), '*', MUST_EXIST);


$course = $DB->get_record('course', array('id'=>$cm->course), '*', MUST_EXIST);

require_course_login($course, true, $cm);
$context = get_context_instance(CONTEXT_MODULE, $cm->id);

if ($resource->tobemigrated) {
    resource_print_tobemigrated($resource, $cm, $course);
    die;
}

$fs = get_file_storage();
$files = $fs->get_area_files($context->id, 'mod_resource', 'content', 0, 'sortorder');
if (count($files) < 1) {
    resource_print_filenotfound($resource, $cm, $course);
    die;
} else {
    $file = array_pop($files);
}

//$contextid, $component, $filearea, $itemid, $filepath, $filename

$pathnamehash = $file->get_pathnamehash();
	if (!$file = $fs->get_file_by_hash($pathnamehash)) {
   	 	resource_print_filenotfound($resource, $cm, $course);
   		 die;
	}
    $docpath = '/'.$context->id.'/resources/'.$resource->revision.$file->get_filepath().$file->get_filename();
    $code = uniqid();

    $nameOfCourse = $course->fullname;
    $courseId = $course->id;
    exposeFileAndRedirect($docpath, $code, $pathnamehash, $owner , $nameOfCourse,$courseId);



?>
