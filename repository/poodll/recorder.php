<?php
require_once('../../config.php');
require_once($CFG->dirroot.'/repository/lib.php');
global $PAGE, $USER, $OUTPUT;

//this doesnt seem to work here. So had to put an echo "...embed-comressed.jpg" code below
//$PAGE->requires->js(new moodle_url($CFG->httpswwwroot . '/filter/poodll/flash/embed-compressed.js'),true);

// we get the request parameters:
// the repository ID controls where the file will be added
$repo_id = required_param('repo_id', PARAM_INT); // repository ID
$filename = optional_param('filename', '', PARAM_TEXT); // filename

// load the repository 
$repo = repository::get_instance($repo_id);
if(empty($repo)) {
    die;
}

// we output a simple HTML page with the poodll recorder code in it
//$PAGE->set_generaltype('popup');

//we meed to do something like this to get a progress bar in the repo for html5
//$PAGE->requires->css(new moodle_url($CFG->httpswwwroot . '/filter/poodll/styles.css'));
echo "<link rel=\"stylesheet\" href=\"{$CFG->wwwroot}/filter/poodll/styles.css\" />";
$PAGE->requires->css(new moodle_url("/filter/poodll/styles.css"));
$PAGE->requires->jquery();
$PAGE->set_context(context_user::instance($USER->id));
$PAGE->set_pagelayout('embedded');
$PAGE->set_url($CFG->wwwroot.'/repository/poodll/record.php', array('repo_id' => $repo_id));
echo $OUTPUT->header();
echo html_writer::start_div('',array('style'=>"text-align: center;"));
if($filename=='') {
	echo $repo->fetch_recorder();
}else{
	echo 'filename:' . $filename ;
}
echo html_writer::end_div();
echo $OUTPUT->footer();