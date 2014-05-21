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
 * Renderer for the grade user report
 *
 * @package   gradereport_laeuser
 * @copyright 2013 Bob Puffer http://www.clamp-it.org
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Custom renderer for the user grade report
 *
 * To get an instance of this use the following code:
 * $renderer = $PAGE->get_renderer('gradereport_user');
 *
 * @copyright 2010 Sam Hemelryk
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class gradereport_laeuser_renderer extends plugin_renderer_base {

    public function graded_users_selector($report, $course, $userid, $groupid, $includeall) {
        global $USER;

        $select = grade_get_graded_users_select($report, $course, $userid, $groupid, $includeall);
        $output = html_writer::tag('div', $this->output->render($select), array('id'=>'graded_users_selector'));
        $output .= html_writer::tag('p', '', array('style'=>'page-break-after: always;'));

        return $output;
    }

    public function target_grades_selector($report, $course, $userid, $context, $maxpercentage, $target_letter) {
        global $USER;

       	if (!$target_letter > 0) {
       		$target_letter = null;
       	}
        $letters = grade_get_letters($context);

       	// cycle through letters and remove any that are greater than maxpercentage
       	foreach ($letters as $key => $value) {
       		if ($key > $maxpercentage) {
       			unset($letters[$key]);
       		}
       	}
    	$select = new single_select(new moodle_url('/grade/report/laeuser/index.php', array('id'=>$course->id)), 'target_letter', $letters, $target_letter, array('' => 'No Target'));
       	$output = html_writer::tag('div', 'Your desired grade... ' . $this->output->render($select) . '  WARNING: The grades listed here may not be the only graded items for this course', array('id'=>'target_grades_selector', 'class' => 'warning'));
       	$output .= html_writer::tag('p', '', array('style'=>'page-break-after: always;'));

        return $output;
    }

}
