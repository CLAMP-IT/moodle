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
 * This file contains general functions for the course format class
 * modified version of the weekly format
 *
 * @since 2.0
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


/**
 * Indicates this format uses sections.
 *
 * @return bool Returns true
 */
function callback_class_uses_sections() {
    return true;
}


/**
 * Used to display the course structure for a course where format=class
 *
 * This is called automatically by {@link load_course()} if the current course
 * format = class.
 *
 * @param navigation_node $navigation The course node
 * @param array $path An array of keys to the course node
 * @param stdClass $course The course we are loading the section for
 */
function callback_class_load_content(&$navigation, $course, $coursenode) {
	global $class_sections;
	$class_sections = format_class_get_meeting_times($course);
    return $navigation->load_generic_course_sections($course, $coursenode, 'class');
}

/**
 * The string that is used to describe a section of the course
 * e.g. Topic, Week...
 *
 * @return string
 */
function callback_class_definition() {
    return get_string('class');
}

/**
 * Gets the name for the provided section.
 *
 * @param stdClass $course
 * @param stdClass $section
 * @return string
 */
function callback_class_get_section_name($course, $section) {
	global $class_sections;
    // We can't add a node without text
    if (!empty($section->name)) {
        // Return the name the user set.
        return format_string($section->name, true, array('context' => context_course::instance($course->id)));
    } else if ($section->section == 0) {
        // Return the general section.
        return get_string('section0name', 'format_class');
    } else {
		
		if(isset($class_sections[$section->section])) {
			return $class_sections[$section->section];
		}
		else {
			return "";
		}

    }
}

/**
 * Declares support for course AJAX features
 *
 * @see course_format_ajax_support()
 * @return stdClass
 */
function callback_class_ajax_support() {
    $ajaxsupport = new stdClass();
    $ajaxsupport->capable = true;
    $ajaxsupport->testedbrowsers = array('MSIE' => 6.0, 'Gecko' => 20061111, 'Safari' => 531, 'Chrome' => 6.0);
    return $ajaxsupport;
}

function format_class_get_meeting_times($course) {
	global $DB;

	$class_sections = array();
	// gets all course desc data from the db
	$desc_info = $DB->get_record('block_course_description', array('courseid' => $course->id));
	if(!empty($desc_info->mtg_days)) {

		$desc_info->mtg_days = preg_replace("/SU/", "0", $desc_info->mtg_days);
		$desc_info->mtg_days = preg_replace("/TH/", "4", $desc_info->mtg_days);
		$desc_info->mtg_days = preg_replace("/M/", "1", $desc_info->mtg_days);
		$desc_info->mtg_days = preg_replace("/T/", "2", $desc_info->mtg_days);
		$desc_info->mtg_days = preg_replace("/W/", "3", $desc_info->mtg_days);
		$desc_info->mtg_days = preg_replace("/F/", "5", $desc_info->mtg_days);
		$desc_info->mtg_days = preg_replace("/S/", "6", $desc_info->mtg_days);

		// multiples are stored w/ ; as delimiter so this breaks em out into arrays
		$mtg_days = explode(";", $desc_info->mtg_days);
		$mtg_start_times = explode(";", $desc_info->mtg_start_times);
		$mtg_end_times = explode(";", $desc_info->mtg_end_times);

		// sets up arrays of weekdays and buildings used for display
		$weekdays = array("Sunday","Monday","Tuesday","Wednesday","Thursday","Friday","Saturday");

		$numofdays = count($mtg_days);

		$timenow = time();
		$classdate = $course->startdate;
		$classdate += 7200;                 // Add two hours to avoid possible DST problems
		$section = 1;
		$sectionmenu = array();
		$weekofseconds = 604800;
		$course->enddate = $course->startdate + ($weekofseconds * 15); // limits it to 15 weeks at most since classes don't run longer than that

		$strftimedateshort = ' '.get_string('strftimedateshort');

		for($stamp=$classdate;$stamp<=$course->enddate;$stamp=strtotime(strftime("%Y-%m-%d",$stamp)." +1 day")) {
        	if($section <= $course->numsections) {
            	for($tmp=0;$tmp<$numofdays;$tmp++) {

                	for ($k=0;$k<strlen($mtg_days[$tmp]);$k++) {
                    	$daynum = substr($mtg_days[$tmp], $k, 1);
                    	$start = $mtg_start_times[$tmp];
                    	$end = $mtg_end_times[$tmp];

                    	if(strftime("%A",$stamp)==$weekdays[$daynum]) {
                        	// only adds 1 hour (instead of 1 day) in case we have multiple class sessions on the same day
                        	$nextclassdate = strtotime(strftime("%Y-%m-%d",$stamp)." +1 hour");
                        	//$classday = userdate($stamp, $strftimedateshort);
                        	$classdatelabel = strftime("%A, %e %B", $stamp).' ('.$start.' - '.$end.')';
                        	$classday = strftime("%A", $stamp);

                            $class_sections[$section] = $classdatelabel;
							$section++;
						}
					}
				}
			}
		}
	}

	return $class_sections;
}


/**
 * Callback function to do some action after section move
 *
 * @param stdClass $course The course entry from DB
 * @return array This will be passed in ajax respose.
 */
function callback_class_ajax_section_move($course) {
    global $COURSE, $PAGE;

    $titles = array();
    rebuild_course_cache($course->id);
    $modinfo = get_fast_modinfo($COURSE);
    $renderer = $PAGE->get_renderer('format_class');
    if ($renderer && ($sections = $modinfo->get_section_info_all())) {
        foreach ($sections as $number => $section) {
            $titles[$number] = $renderer->section_title($section, $course);
        }
    }
    return array('sectiontitles' => $titles, 'action' => 'move');
}
