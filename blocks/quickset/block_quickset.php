<?php

  /* Quickset to set most commonly changed course settings
  *  as well as rename, rearrange, insert and delete course sections
  * @package quickset
  * @author: Bob Puffer Luther College <puffro01@luther.edu>
  * @date: 2010 ->
  */

  class block_quickset extends block_base {

    function init() {
      $this->title = get_string('pluginname', 'block_quickset');
      $this->cron = 1;
    } //init

    // only one instance of this block is required
    function instance_allow_multiple() {
      return false;
    } //instance_allow_multiple

    // label and button values can be set in admin
    function has_config() {
      return false;
    } //has_config

    function get_content() {
		global $CFG, $COURSE, $USER, $PAGE, $DB;
//		echo '<style>';
//		include_once 'styles.css';
//		echo '</style>';
        $AVAILABLE = 1;
        $UNAVAILABLE = 0;
	  $this->content = new stdClass;
	  $returnurl = "$CFG->wwwroot/course/view.php?id=$COURSE->id";
	  $numsections = $DB->get_field('course_format_options', 'value', array('courseid' => $COURSE->id, 'name' => 'numsections'));

      $context = get_context_instance(CONTEXT_COURSE, $COURSE->id);
    if (has_capability('moodle/course:update', $context)) {
        if ($COURSE->visible == 1) {
            $students = 'green';
            $studentschecked = ' checked="checked"';
            $studentsunchecked = '';
        } else {
            $students = 'red';
            $studentsunchecked = ' checked="checked"';
            $studentschecked = '';
        }
        if ($COURSE->showgrades == 1) {
            $grades = 'green';
            $gradeschecked = ' checked="checked"';
            $gradesunchecked = '';
        } else {
            $grades = 'red';
            $gradesunchecked = ' checked="checked"';
            $gradeschecked = '';
        }
        $this->content->text = '<form id="quickset" action="' . $CFG->wwwroot . '/blocks/quickset/edit.php" method="post">'
                . '<input type="hidden" value="'.$PAGE->course->id.'" name="courseid" />'
                . '<input type="hidden" value="'.sesskey().'" name="sesskey" />'
                . '<input type="hidden" value="' . $returnurl . '" name="pageurl"/>'
                . '<input type="hidden" value="grader" name="report"/>';
        $this->content->text .= '<div id="context">'
                . '<div class="ynlabel" style="margin-right:0em">Yes | No</div>'

                . '<div class="setleft ' . $students . '">Students see course?</div>'
                . '<div class="setright">'
                	. '<span class="leftradio">'
                	. '<input type="radio" name="course" value=' . $AVAILABLE . $studentschecked . ' />'
                    . '</span>'
                	. '<span class="rightradio">'
                    . '<input type="radio" name="course" value=' . $UNAVAILABLE . $studentsunchecked . ' />'
                    . '</span>'
                . '</div>'

                . '<div class="setleft ' . $grades . '">Grades visible?</div>'
                . '<div>'
                	. '<span class="leftradio">'
                    . '<input type="radio" name="grades" value=' . $AVAILABLE . $gradeschecked . ' />'
                    . '</span>'
                	. '<span class="rightradio">'
                    . '<input type="radio" name="grades" value=' . $UNAVAILABLE . $gradesunchecked . ' />'
                    . '</span>'
                . '</div>'

                . '<div class="setleft blue toplevel" >Visible sections </div>'
                . '<div class="setright">'
                	. '<input type="text" name="number" size="2" value="'.$numsections.'"/>'
                . '</div>'

                . '<br /><br />'

                . '<div>'
                    . '<span class="nodisplay defaultaction">'
                        . '<input type="submit" name="updatesettings"  value="Update settings">'
	                . '</span>'
                    . '<span class="noaction">'
	                	. '<input type="submit" name="noaction" value="Edit Sections" >'
	                . '</span>'
                	. '<span class="updatesettings">'
	                	. '<input type="submit" name="updatesettings"  value="Update settings">'
	                . '</span>'
	            . '</div>'

	            . '<br /><br />'

                . '<div class="textcenter"><a href="' . $CFG->wwwroot . '/course/edit.php?id=' . $COURSE->id . '"> More Settings </a></div>'
                . '</div></form>';
        $this->content->text .= '<div class="smallred">Note: This block invisible to students</div>';

    }
		  //no footer, thanks
		  $this->content->footer = '';
		  return $this->content;
    } //get_content

    function specialisation() {
      //empty!
    } //specialisation


  } //block_course_settings
?>