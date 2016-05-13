<?php
require_once ('about.php');
require_once ('annotateUtil.php');
class block_annotate extends block_base {
function init() {
		
		$this->title = get_string ( 'annotate', 'block_annotate' );
		$this->version=2013112200;
		
}
function instance_allow_multiple(){
		return false;
}
function instance_allow_config() {
		return true;
}
function has_config() {
		return true;
}
	//where this block will be displayed 
function applicable_formats(){
	return array('all'=>true);
}
function get_content() {
	global $PAGE;
	global $CFG;
	$PAGE->requires->js_init_call ( 'M.block_annotate.init', array (
			'pdf-doc-xls-ppt-jpg',
			$CFG->wwwroot
	) );
	if ($this->content !== null) {
		return $this->content;
	}
	$this->content = new stdClass ();
	$this->content->text = 'Use the annotate buttons beside resources, or ' . '<a href="' . simpleLoginURL () . '">Log on directly</a>. ';
	if (!empty($this->config->access)){
		if($this->config->access== '1') {
			$this->content->text .= '<input type="hidden" name="annotate_shareuser" id="annotate_shareuser" value="' . $this->config->shareuser . '"/>';
		}
	}
	$this->content->footer = '';
	return $this->content;
	}
}