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


/* Quickset to set most commonly changed course settings
* as well as rename, rearrange, insert and delete course sections
* @package quickset
* @author: Bob Puffer Luther College <puffro01@luther.edu>
* @date: 2010 ->
*/

class block_quickset extends block_base {

    function init() {
        $this->title = get_string('pluginname', 'block_quickset');
        $this->cron = 1;
    }

    // Only one instance of this block is required
    function instance_allow_multiple() {
        return false;
    }

    // Label and button values can be set in admin
    function has_config() {
        return false;
    }

    function get_content() {
        global $CFG, $COURSE, $USER, $PAGE, $DB;
        $available = 1;
        $unavailable = 0;
        $this->content = new stdClass;
        $returnurl = "$CFG->wwwroot/course/view.php?id=$COURSE->id";
        $numsections = $DB->get_field('course_format_options', 'value', array('courseid' => $COURSE->id, 'name' => 'numsections', 'format' => $COURSE->format));

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
                        . '<input type="radio" name="course" value=' . $available . $studentschecked . ' />'
                        . '</span>'
                        . '<span class="rightradio">'
                        . '<input type="radio" name="course" value=' . $unavailable . $studentsunchecked . ' />'
                        . '</span>'
                    . '</div>'

                    . '<div class="setleft ' . $grades . '">Grades visible?</div>'
                    . '<div>'
                        . '<span class="leftradio">'
                        . '<input type="radio" name="grades" value=' . $available . $gradeschecked . ' />'
                        . '</span>'
                        . '<span class="rightradio">'
                        . '<input type="radio" name="grades" value=' . $unavailable . $gradesunchecked . ' />'
                        . '</span>'
                    . '</div>'

                    . '<div class="setleft blue toplevel" >Visible sections </div>'
                    . '<div class="setright">'
                        . '<input type="text" class="numsections" name="number" value="'.$numsections.'"/>'
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

                    . '<div class="textcenter">
                            <a href="' . $CFG->wwwroot . '/course/edit.php?id=' . $COURSE->id . '"> More Settings </a>
                            </div>'
                    . '</div></form>';
            $this->content->text .= '<div class="smallred">Note: This block invisible to students</div>';
        }
        $this->content->footer = '';
        return $this->content;
    }

    function specialisation() {
        // empty!
    }
}