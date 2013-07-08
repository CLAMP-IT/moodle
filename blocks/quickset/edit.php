<?php
// These params are only passed from page request to request while we stay on
// this page otherwise they would go in section_edit_setup.
require_once('../../config.php');
// require_once('lib.php');
global $COURSE,$PAGE, $OUTPUT;

//$courseid = $PAGE->course->id;
$courseid = required_param('courseid', PARAM_NUMBER);
$thispageurl = required_param('pageurl', PARAM_URL); //always sent as the course page
$returnurl = optional_param('returnurl', false, PARAM_URL);
if ($returnurl) {
	$thispageurl = $returnurl;
}
$PAGE->set_url($thispageurl);

// Get the course object and related bits.
$course = $DB->get_record('course', array('id' => $courseid));
$PAGE->set_course($course);
if (!$course) {
    print_error('invalidcourseid', 'error');
}
// Log this visit.
add_to_log($courseid, 'block_quickset', 'editsections',
            "edit.php");

// You need mod/section:manage in addition to section capabilities to access this page.
$context = get_context_instance(CONTEXT_COURSE, $COURSE->id);
require_capability('moodle/course:update', $context);

// Process commands ============================================================

// Get the list of section ids had their check-boxes ticked.
$selectedsectionids = array();
$params = (array) data_submitted();
foreach ($params as $key => $value) {
    if (preg_match('!^s([0-9]+)$!', $key, $matches)) {
        $selectedsectionids[] = $matches[1];
    }
}

if (optional_param('returntocourse', null, PARAM_TEXT)) {
	redirect("$CFG->wwwroot/course/view.php?id=$courseid");
}

if (optional_param('updatesettings', null, PARAM_TEXT)) {
	process_form($courseid, $params);
	redirect("$CFG->wwwroot/course/view.php?id=$courseid");
}

if (optional_param('addnewsectionafterselected', null, PARAM_CLEAN) &&
        !empty($selectedsectionids) && confirm_sesskey()) {
    $sections = array(); // For sections in the new order.
    foreach ($selectedsectionids as $sectionid) {
    	// clone the previous sectionid
    	$newsection = $DB->get_record('course_sections', array('id'=>$sectionid));
    	$newsection->name = null;
    	$newsection->summary = '';
    	$newsection->sequence = '';
    	$newsection->section = $params['o'.$sectionid] * 100;
    	unset($newsection->id);
    	$newsection->id = $DB->insert_record('course_sections', $newsection, true);

    	// get the present order of the selected sectionid and insert newsection into the param array
    	$params['o'.$newsection->id] = $params['o'.$sectionid] + 1;
    }
    foreach ($params as $key => $value) {
		if (preg_match('!^o(pg)?([0-9]+)$!', $key, $matches)) {
            // Parse input for ordering info.
            $sectionid = $matches[2];
            // Make sure two sections don't overwrite each other. If we get a second
            // section with the same position, shift the second one along to the next gap.
            $value = clean_param($value, PARAM_INTEGER);
            $sections[$value] = $sectionid;
        }
    }

    // If ordering info was given, reorder the sections.
    if ($sections) {
	    ksort($sections);
		$counter = 0;
	    foreach ($sections as $rank=>$sectionid) {
	       	$counter++;
	       	$DB->set_field('course_sections', 'section', $counter * 100, array('course' => $courseid, 'id' => $sectionid));
	    }
	    $sql = "UPDATE mdl_course_sections set section = section / 100
	       			WHERE course = '$courseid'
	       			AND section <> 0";
	    $DB->execute($sql);

	    // update the course_format_options table
    	$conditions = array('courseid' => $courseid, 'name' => 'numsections');
    	if (!$courseformat = $DB->get_record('course_format_options', $conditions)) {
    		error('Course format record doesn\'t exist');
    	}
    	$courseformat->value = min($counter,52);
    	if (!$DB->update_record('course_format_options',$courseformat)) {
    		print_error('coursenotupdated');
    	}
    }
}

if (optional_param('sectiondeleteselected', false, PARAM_BOOL) &&
        !empty($selectedsectionids) && confirm_sesskey()) {
    $zerosection = $DB->get_record('course_sections', array('section'=>0, 'course'=>$courseid));
	foreach ($selectedsectionids as $sectionid) {
        $section = $DB->get_record('course_sections', array('id'=>$sectionid));
        if ($section->sequence != '') {
	        $zerosection->sequence .= ',' . $section->sequence;
			$DB->update_record('course_sections', $zerosection);
        }
        $DB->delete_records('course_sections', array('id'=>$sectionid));
    }
    $sql = "SELECT * FROM mdl_course_sections
    		WHERE course = $courseid
    		ORDER BY section";
	$sections = $DB->get_records_sql($sql);
	$counter = 0;
	foreach( $sections as $section) {
		$section->section = $counter;
		$DB->update_record('course_sections', $section);
		$counter++;
	}
	// update the course_format_options table
	$conditions = array('courseid' => $courseid, 'name' => 'numsections');
	if (!$courseformat = $DB->get_record('course_format_options', $conditions)) {
		error('Course format record doesn\'t exist');
	}
	$courseformat->value = min($counter - 1,52);
	if (!$DB->update_record('course_format_options',$courseformat)) {
		print_error('coursenotupdated');
	}
}

if (optional_param('savechanges', false, PARAM_BOOL) && confirm_sesskey()) {

    $sections = array(); // For sections in the new order.
    $sectionnames = array(); // For sections in the new order.
    $rawdata = (array) data_submitted();

    foreach ($rawdata as $key => $value) {
		if (preg_match('!^o(pg)?([0-9]+)$!', $key, $matches)) {
            // Parse input for ordering info.
            $sectionid = $matches[2];
            // Make sure two sections don't overwrite each other. If we get a second
            // section with the same position, shift the second one along to the next gap.
            $value = clean_param($value, PARAM_INTEGER);
            $sections[$value] = $sectionid;
        } elseif (preg_match('!^n(pg)?([0-9]+)$!', $key, $namematches)) {
            // Parse input for ordering info.
            $sectionname = $matches[2];
            // Make sure two sections don't overwrite each other. If we get a second
            // section with the same position, shift the second one along to the next gap.
            $value = clean_param($value, PARAM_TEXT);
            $sectionnames[$value] = $sectionid;
        }
    }

    // If ordering info was given, reorder the sections.
    if ($sections) {
        ksort($sections);
		$counter = 0;
        foreach ($sections as $rank=>$sectionid) {
        	$counter++;
        	$DB->set_field('course_sections', 'section', $counter * 100, array('course' => $courseid, 'id' => $sectionid));
        }
       	$sql = "UPDATE mdl_course_sections set section = section / 100
       			WHERE course = '$courseid'
       			AND section <> 0";
       	$DB->execute($sql);
    }
    // If ordering info was given, reorder the sections.
    if ($sectionnames) {
    	foreach ($sectionnames as $sectionname=>$sectionid) {
			if ($sectionname !== "Untitled") {
	    		$DB->set_field('course_sections', 'name', $sectionname, array('course' => $courseid, 'id' => $sectionid));
			}
    	}
    }

}

// End of process commands =====================================================

$PAGE->set_pagelayout('coursecategory');
$PAGE->set_title(get_string('editingcoursesections', 'block_quickset', format_string($course->shortname)));
$PAGE->set_heading($course->fullname);
$node = $PAGE->settingsnav->find('mod_quiz_edit', navigation_node::TYPE_SETTING);
 echo $OUTPUT->header();

$sections = $DB->get_records('course_sections', array('course'=>$courseid));
section_print_section_list($sections, $thispageurl, $courseid);

echo $OUTPUT->footer();


/**
 * Prints a list of sections for the edit.php main view for edit
 *
 * @param moodle_url $pageurl The url of the current page with the parameters required
 *     for links returning to the current page, as a moodle_url object
 */
function section_print_section_list($sections, $thispageurl, $courseid) {
	require_once('../../config.php');
	global $CFG, $DB, $OUTPUT;

	$strorder = get_string('order');
	$strreturn = get_string('returntocourse', 'block_quickset');
	$strremove = get_string('removeselected', 'block_quickset');
	$stredit = get_string('edit');
	$strview = get_string('view');
	$straction = get_string('action');
	$strmove = get_string('move');
	$strmoveup = get_string('moveup');
	$strmovedown = get_string('movedown');
	$strreordersections = get_string('reordersections', 'block_quickset');
	$straddnewsectionafterselected = get_string('addnewsectionsafterselected', 'block_quickset');
	$strareyousureremoveselected = get_string('areyousureremoveselected', 'block_quickset');

	//	$sections = $DB->get_records('course_sections', array('course'=>$courseid));
	foreach ($sections as $section) {
		$order[] = $section->section;
		$sections[$section->section] = $section;
		unset($sections[$section->id]);
	}

	$lastindex = count($order) - 1;

	$reordercontrolssetdefaultsubmit = '<span class="nodisplay">' .
			'<input type="submit" name="savechanges" value="' .
			$strreordersections . '" /></span>';

	$reordercontrols1 = '<span class="sectiondeleteselected">' .
			'<input type="submit" name="sectiondeleteselected" ' .
			'onclick="return confirm(\'' .
			$strareyousureremoveselected . '\');" style="background-color: #ffb2b2" value="' .
			get_string('removeselected', 'block_quickset') . '" /></span>';
	$reordercontrols1 .= '<span class="addnewsectionafterselected">' .
			'<input type="submit" name="addnewsectionafterselected" value="' .
			$straddnewsectionafterselected . '" /></span>';

	$reordercontrols2top = '<span class="moveselectedonpage">' .
			'<input type="submit" name="savechanges" value="' .
			$strreordersections . '" /></span>';
	$reordercontrols2bottom = '<span class="moveselectedonpage">' .
			'<input type="submit" name="savechanges" value="' .
			$strreordersections . '" /></span>';

	$reordercontrols3 = '<span class="nameheader"></span>';
	$reordercontrols4 = '<span class="returntocourse">' .
			'<input type="submit" name="returntocourse" value="' .
			$strreturn . '" /></span>';

    $reordercontrolstop = '<div class="reordercontrols">' .
            $reordercontrolssetdefaultsubmit .
            $reordercontrols1 . $reordercontrols3 . $reordercontrols2top . "</div><br />";
    $reordercontrolsbottom = '<br /><br /><div class="reordercontrols">' .
            $reordercontrolssetdefaultsubmit .
            $reordercontrols4 . $reordercontrols2bottom . "</div>";

	echo '<div class="editsectionsform">';
    echo '<form method="post" action="edit.php" id="sections"><div>';

	echo '<input type="hidden" name="sesskey" value="' . sesskey() . '" />';
	echo '<input type="hidden" name="courseid" value="' . $courseid . '" />';
	echo '<input type="hidden" name="pageurl" value="' . $thispageurl . '" />';

	echo $reordercontrolstop;
	$sectiontotalcount = count($order);

	// The current section ordinal (no descriptions).
	$sno = -1;

	foreach ($order as $count => $sectnum) {

		$sno++;
		$reordercheckbox = '';
		$reordercheckboxlabel = '';
		$reordercheckboxlabelclose = '';
		if ($sectnum != 0) {
			$section = $sections[$sectnum];
			$sectionparams = array();
			$sectionurl = new moodle_url('/section/section.php',
					$sectionparams);

				// This is an actual section.
				?>
                <div class="section">
                    <span class="sectioncontainer">
                        <span class="sectnum">
                            <?php
                            $reordercheckbox = '';
                            $reordercheckboxlabel = '';
                            $reordercheckboxlabelclose = '';
                            $reordercheckbox = '<input type="checkbox" name="s' . $section->id .
                                '" id="s' . $section->id . '" />';
                            $reordercheckboxlabel = '<label for="s' . $section->id . '">';
                            $reordercheckboxlabelclose = '</label>';
                            echo $reordercheckboxlabel . $sno . $reordercheckboxlabelclose .
                                    $reordercheckbox;

                            ?>
                        </span>
                        <span class="content">
                            <span class="sectioncontentcontainer">
                                <?php
                                    print_section_reordertool($section, $lastindex, $sno);
                                ?>
                            </span>
                			<span class="sorder">
                                <?php
                                echo '<input type="text" name="o' . $section->id .
                                        '" size="2" value="' . (10*$count) .
                                        '" tabindex="' . ($lastindex + $sno) . '" />';
                                ?>
                			</span>
                        </span>
                </span>
            </div>
            <?php
        }
    }
    echo $reordercontrolsbottom;
    echo '</div></form></div>';
}

/**
 * Print a given single section in quiz for the reordertool tab of edit.php.
 * Meant to be used from quiz_print_section_list()
 *
 * @param object $section A section object from the database sections table
 * @param object $sectionurl The url of the section editing page as a moodle_url object
 * @param object $quiz The quiz in the context of which the section is being displayed
 */
function print_section_reordertool($section, $lastindex, $sno) {
	echo '<span class="singlesection ">';
	echo '<label for="n' . $section->id . '">';
	echo ' ' . section_tostring($section, $lastindex, $sno);
	echo '</label>';
	echo "</span>\n";
}

/**
 * Creates a textual representation of a section for display.
 *
 * @param object $section A section object from the database sections table
 * @param bool $showicon If true, show the section's icon with the section. False by default.
 * @param bool $showsectiontext If true (default), show section text after section name.
 *       If false, show only section name.
 * @param bool $return If true (default), return the output. If false, print it.
 */
function section_tostring($section, $lastindex, $sno, $showicon = false,
        $showsectiontext = true, $return = true) {
    global $COURSE;
    $result = '';
    $result .= '<span class="">';
    if ($section->name == '') {
    	$result .= '<input type="text" name="n' . $section->id .
                                '" size="75" value="Untitled" tabindex="' . ($lastindex + $sno) . '" /></span>';
    } else {
    	$result .= '<input type="text" name="n' . $section->id .
                                '" size="75" value="' . $section->name .
                                '" tabindex="' . ($lastindex + $sno) . '" /></span>';
    }
    if ($return) {
        return $result;
    } else {
        echo $result;
    }
}

function process_form($courseid, $data) {
	ini_set('error_reporting', E_ALL);
	ini_set('display_errors', 1);
	require_once('../../config.php');
	global $CFG, $DB, $COURSE, $USER;
	require_once($CFG->dirroot.'/lib/accesslib.php');

	$conditions = array('id' => $courseid);
	if (!$course = $DB->get_record('course', $conditions)) {
		error('Course ID was incorrect');
	}
	$shortname = $COURSE->shortname;

	$conditions = array('courseid' => $courseid, 'name' => 'numsections');
	if (!$courseformat = $DB->get_record('course_format_options', $conditions)) {
		error('Course format record doesn\'t exist');
	}

	$context = get_context_instance(CONTEXT_COURSE, $courseid);
	$context = get_context_instance(CONTEXT_COURSE, $courseid);
	if (has_capability('moodle/course:update', $context)) {
		//// process making grades available data
		$course->showgrades = $data['grades'];
		//// Process course availability
		$course->visible = $data['course'];
		//// Process number of sections
		if (!$DB->update_record('course',$course)) {
			print_error('coursenotupdated');
		}
		$courseformat->value = min($data['number'],52);
		if (!$DB->update_record('course_format_options',$courseformat)) {
			print_error('coursenotupdated');
		}
		// check to see if new sections need to be added onto the end
		$sql = " SELECT MAX(section) from " . $CFG->prefix . "course_sections
		            WHERE course = '$courseid'";
		$maxsection = $DB->get_field_sql($sql);
		for ($i = $data['number'] - $maxsection; $i > 0; $i--) {
		    // clone the previous sectionid
		    $newsection = $DB->get_record('course_sections', array('course' => $courseid, 'section' => $maxsection));
		    $newsection->name = null;
		    $newsection->summary = '';
		    $newsection->sequence = '';
		    $newsection->section = $maxsection + $i;
		    unset($newsection->id);
		    $newsection->id = $DB->insert_record('course_sections', $newsection, true);
		}
	}
}
?>
