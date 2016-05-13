<?php
require_once('../../config.php');
require_once($CFG->dirroot.'/repository/lib.php');
global $PAGE, $USER,$CFG;

require_login();  // CONTEXT_SYSTEM level.

// we get the request parameters:
// the repository ID controls where the file will be added
$repo_id = required_param('repo_id', PARAM_INT); // repository ID
$filename = optional_param('filename', '', PARAM_TEXT); // filename

//setup page
$PAGE->set_context(context_system::instance());
$PAGE->set_url($CFG->wwwroot.'/repository/poodll/record.php', array('repo_id' => $repo_id));
//$PAGE->set_url('/repository/poodll/recorder.php');
$PAGE->set_pagelayout('embedded');
$PAGE->set_title('');
//$PAGE->set_heading('');
$PAGE->requires->js(new moodle_url($CFG->wwwroot . '/filter/poodll/flash/embed-compressed.js'),true);
$PAGE->requires->js(new moodle_url($CFG->wwwroot. '/filter/poodll/module.js'),true);
$PAGE->requires->css(new moodle_url($CFG->wwwroot . '/filter/poodll/styles.css'));
//$PAGE->set_context(get_context_instance(CONTEXT_USER, $USER->id));

echo $OUTPUT->header();

// load the repository 
$repo = repository::get_instance($repo_id);
if(empty($repo)) {
    die;
}

// we output a simple HTML page with the poodll recorder code in it
//$PAGE->set_generaltype('popup');
//we meed to do something like this to get a progress bar in the repo for html5
//print_header(null, get_string('recordnew', 'repository_poodll'),null, null, null, false);
?>

<div style="text-align: center;">
<?php if($filename==''){
			$repo->fetch_recorder();
		}else{
			echo 'filename:' . $filename ;
		} 
echo "</div>";
echo $OUTPUT->footer();