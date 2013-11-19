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
// CLAMP # 194 2010-06-23 bobpuffer

require_once '../../../config.php';
require_once $CFG->libdir.'/gradelib.php';
require_once $CFG->dirroot.'/grade/lib.php';
require_once $CFG->dirroot.'/grade/report/laegrader/lib.php';
require_once $CFG->dirroot.'/grade/report/laegrader/locallib.php'; // END OF HACK
require_once $CFG->dirroot.'/grade/report/laegrader/lae_grade_export_xls.php';

//require_js(array('yui_yahoo', 'yui_dom', 'yui_event', 'yui_container', 'yui_connection', 'yui_dragdrop', 'yui_element'));

global $DB;
$courseid      = required_param('id', PARAM_INT);        // course id
$page          = optional_param('page', 0, PARAM_INT);   // active page
$perpageurl    = optional_param('perpage', 0, PARAM_INT);
$edit          = optional_param('edit', -1, PARAM_BOOL); // sticky editting mode

$itemid        = optional_param('itemid', 0, PARAM_ALPHANUM); // item to zerofill or clear overrides -- laegrader
$sortitemid    = optional_param('sortitemid', 0, PARAM_ALPHANUM); // sort by which grade item
$action        = optional_param('action', 0, PARAM_ALPHAEXT);
$move          = optional_param('move', 0, PARAM_INT);
$type          = optional_param('type', 0, PARAM_ALPHA);
$target        = optional_param('target', 0, PARAM_ALPHANUM);
$toggle        = optional_param('toggle', NULL, PARAM_INT);
$toggle_type   = optional_param('toggle_type', 0, PARAM_ALPHANUM);
$CFG->grade_report_aggregationposition = 1;

$PAGE->set_url(new moodle_url('/grade/report/laegrader/index.php', array('id'=>$courseid)));

/// basic access checks
$sql = " id = $courseid ";
if (!$course = $DB->get_record_select('course', $sql)) {
    print_error('nocourseid');
}
require_login($course);
$context = get_context_instance(CONTEXT_COURSE, $course->id);

require_capability('gradereport/laegrader:view', $context);
require_capability('moodle/grade:viewall', $context);

/// return tracking object
$gpr = new grade_plugin_return(array('type'=>'report', 'plugin'=>'laegrader', 'courseid'=>$courseid, 'page'=>$page));

// clear all overrides in a column when the clearoverrides icon is clicked
if ($action == 'clearoverrides' && $itemid !== 0) {
	$records = $DB->get_records('grade_grades', array('itemid'=>$itemid));
	foreach($records as $record) {
		$record->overridden = 0;
		$DB->update_record('grade_grades', $record);
	}
} elseif ($action == 'changedisplay' && $itemid !==0) {
	$record = $DB->get_record('grade_items', array('id'=>$itemid));
	if ($record->display == GRADE_DISPLAY_TYPE_DEFAULT) {
		$record->display = max(1,($CFG->grade_displaytype + 1) % 4);
	} else {
		$record->display = max(1, ($record->display + 1) % 4);
	}
	$success = $DB->update_record('grade_items', $record);
}

/// last selected report session tracking
if (!isset($USER->grade_last_report)) {
    $USER->grade_last_report = array();
}
$USER->grade_last_report[$course->id] = 'laegrader';

/// Build editing on/off buttons

if (!isset($USER->gradeediting)) {
    $USER->gradeediting = array();
}

if (has_capability('moodle/grade:edit', $context)) {
    if (!isset($USER->gradeediting[$course->id])) {
        $USER->gradeediting[$course->id] = 0;
    }

    if (($edit == 1) and confirm_sesskey()) {
        $USER->gradeediting[$course->id] = 1;
    } else if (($edit == 0) and confirm_sesskey()) {
        $USER->gradeediting[$course->id] = 0;
    }

    // page params for the turn editting on
    $options = $gpr->get_options();
    $options['sesskey'] = sesskey();

    if ($USER->gradeediting[$course->id]) {
        $options['edit'] = 0;
        $string = get_string('turneditingoff');
    } else {
        $options['edit'] = 1;
        $string = get_string('turneditingon');
    }
    $buttons = new single_button(new moodle_url('index.php', $options), $string, 'get');
} else {
    $USER->gradeediting[$course->id] = 0;
    $buttons = '';
}

$gradeserror = array();

//first make sure we have proper final grades - this must be done before constructing of the grade tree
grade_regrade_final_grades($courseid);

$reportname = get_string('pluginname', 'gradereport_laegrader');

/// Print header
if ($action !== 'quick-dump') {
    print_grade_page_head($COURSE->id, 'report', 'laegrader', $reportname, false, $buttons);
}

// Initialise the grader report object
$report = new grade_report_laegrader($courseid, $gpr, $context, $page, $sortitemid); // END OF HACK

// make sure separate group does not prevent view
if ($report->currentgroup == -2) {
    echo $OUTPUT->heading(get_string("notingroup"));
    echo $OUTPUT->footer();
    exit;
}

/// processing posted grades & feedback here
if ($data = data_submitted() and confirm_sesskey() and has_capability('moodle/grade:edit', $context)) {
    $warnings = $report->pre_process_grade($data);
} else {
    $warnings = array();
}

// final grades MUST be loaded after the processing
$report->load_users();
$numusers = $report->get_numusers();
$report->load_final_grades();

if ($action === 'quick-dump') {

    require_capability('moodle/grade:export', $context);
    require_capability('gradeexport/xls:view', $context);

    if (groups_get_course_groupmode($COURSE) == SEPARATEGROUPS and !has_capability('moodle/site:accessallgroups', $context)) {
        if (!groups_is_member($groupid, $USER->id)) {
            print_error('cannotaccessgroup', 'grades');
        }
    }

    // print all the exported data here
    $report->quick_dump();
}

// AT THIS POINT WE HAVE ACCURATE GRADES FOR DISPLAY
// no other grader actions are relevant as they expand or compress the column headers
echo $report->group_selector;
echo '<div class="clearer"></div>';
// echo $report->get_toggles_html();

//show warnings if any
foreach($warnings as $warning) {
    echo $OUTPUT->notification($warning);
}


$studentsperpage = $report->get_students_per_page();
// Don't use paging if studentsperpage is empty or 0 at course AND site levels
if (!empty($studentsperpage)) {
	echo $OUTPUT->paging_bar($numusers, $report->page, $studentsperpage, $report->pbarurl);
}

$reporthtml = $report->get_grade_table();
$reporthtml .= '<script src="jquery-1.7.2.min.js" type="text/javascript"></script>';
		/*
       	 * code going into the html entity to enable scrolling columns and rows
       	 */
        // get how tall the scrolling window is by user configuration
		$scrolling = get_user_preferences('grade_report_laegrader_reportheight');
		$scrolling = $scrolling == null ? 380 : 300 + ($scrolling * 40);

		// initialize the javascript that will be used to enable scrolling
		// special thanks to jaimon mathew for jscript
		$headerrows = ($USER->gradeediting[$courseid]) ? 2 : 1;
		$headerrows += ($report->get_pref('showaverages')) ? 1 : 0;
		$headerrows += ($report->get_pref('showranges')) ? 1 : 0;
		$extrafields = $report->extrafields;
		$headercols = 1 + count($extrafields);
		$headercols += has_capability('gradereport/'.$CFG->grade_profilereport.':view', $context) ? 1 : 0;
        $headerinit = "fxheaderInit('lae-user-grades', $scrolling," . $headerrows . ',' . $headercols . ');';
		$reporthtml .=
		        '<script src="' . $CFG->wwwroot . '/grade/report/laegrader/fxHeader_0.6.min.js" type="text/javascript"></script>
		        <script type="text/javascript">' .$headerinit . 'fxheader(); </script>';

		$reporthtml .=
		        '<script src="' . $CFG->wwwroot . '/grade/report/laegrader/my_jslib.js" type="text/javascript"></script>';


// print submit button
if ($USER->gradeediting[$course->id] && ($report->get_pref('showquickfeedback') || $report->get_pref('quickgrading'))) {
    echo '<form action="index.php" method="post">';
    echo '<div>';
    echo '<input type="hidden" value="'.s($courseid).'" name="id" />';
    echo '<input type="hidden" value="'.sesskey().'" name="sesskey" />';
    echo '<input type="hidden" value="laegrader" name="report"/>';
    echo '<input type="hidden" value="'.$page.'" name="page"/>';
    echo $reporthtml;
    echo '<div class="submit"><input type="submit" value="'.s(get_string('update')).'" /></div>';
    echo '</div></form>';
} else {
    echo $reporthtml;
}
echo $OUTPUT->footer();
// CLAMP # 194 2010-06-23 end
?>
