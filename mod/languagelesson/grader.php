<?php // Id: grader.php
/******
	This page prints a holistic grading interface for the current lesson.
	
	TODO: rewrite this page! Needs logical ordering of things, and proper use of HTML
		  w/ integrated PHP, not all PHP echoing HTML
 ******/
 
	require_once('../../config.php');
	require_once($CFG->dirroot.'/mod/languagelesson/locallib.php');
    require_once($CFG->dirroot.'/mod/languagelesson/lib.php');
	 
	$id      = required_param('id', PARAM_INT);             // Course Module ID
	list($cm, $course, $lesson) = languagelesson_get_basics($id);

	require_login($course->id, false, $cm);
    
    $context = get_context_instance(CONTEXT_MODULE, $cm->id);
    
//// If a student somehow pulls this page, bail
    if (!has_capability('mod/languagelesson:grade', $context)) {
    	error('You do not have permission to access this page.');
    }
    
 
/// print the basic head stuff of the page
    languagelesson_print_header($cm, $course, $lesson, 'grader');

/// print out the javascript functions to handle mouseover tooltips for question names
	?>
	<script type="text/javascript">
		//<!--[CDATA[


	////////////////////////////////////////
	// Tooltip functions
		function showtooltip(e,text) {
			//var tooltip = document.getElementById('question_name_' + qnum);
			var tooltip = document.getElementById('question_name_field');
			//alert(text);
			tooltip.innerHTML = text;
			tooltip.style.display = 'block';
			
			var st = Math.max(document.body.scrollTop,document.documentElement.scrollTop);
			if(navigator.userAgent.toLowerCase().indexOf('safari')>=0)st=0; 
			var leftPos = e.clientX+10;
			if(leftPos<0)leftPos = 0;
			tooltip.style.left = leftPos + 'px';
			tooltip.style.top = e.clientY-tooltip.offsetHeight-5+st+ 'px';
		}
		
		function showtooltip_itemcell(e,text) {
			check = document.getElementById('alwaysshowqnamebox');
			if (check.checked) {
				showtooltip(e,text);
			}
		}

		function hidetooltip() {
			var tooltip = document.getElementById('question_name_field');
			tooltip.style.display = 'none';
		}
	// End tooltip functions
	////////////////////////////////////////
	
	

	////////////////////////////////////////
	// Functions to maintain list of students to email
		var stulist = Array();
		
		function select_all_students(sender) {
			var objs = document.getElementsByClassName('email_student_checkbox');
			
			if (objs[0].checked) {
				var value = 0;
			} else {
				var value = 1;
			}
			
			sender.checked = value;
			
		  /// clear any stored values in the stulist array
			stulist = Array();
			
			for (var i in objs) {
				objs[i].checked = value;
				if (value) { stulist.push(objs[i].value); }
			}
		}
		
		function update_stulist(e, sender) {
			var obj = e.sender;
			if (sender.checked) {
				stulist.push(sender.value);
			} else {
				stulist = remove_from_array(sender.value, stulist);
			}
		}
		
		function remove_from_array(item, array) {
			var index = array.indexOf(item);
			if (index != -1) {
				array.splice(index, 1);
			}
			return array;
		}
		
		function update_stulist_input() {
			var input_list = document.getElementById('stuidlist');
			input_list.value = stulist;
		}
	// End email list functions
	////////////////////////////////////////
	
	
	
	////////////////////////////////////////
	// Custom holistic grade data functions
		var stuGrades = {};
		
		function update_holistic_grade_array(sender, id) {
			var grade = Number(sender.value);
			
			/// if the box was made (or left) empty, get rid of the grade	
			if (sender.value == '') {
				stuGrades.delete(id);
				return;
			}
			
			/// if it's not a number, less than 0 or more than 100, tell them it won't work
			if (isNaN(grade) || grade < 0 || grade > 100) {
				sender.value = '';
				alert('<?php echo get_string('improperholisticgrade', 'languagelesson'); ?>');
				return;
			}
			
			/// otherwise, save it
			stuGrades[id] = grade;
		}
		
		var thestring = "";
		function update_stugrade_input() {
			
			thestring = '';
			for (var ID in stuGrades) {
				thestring += ID;
				thestring += ',';
				thestring += stuGrades[ID];
				thestring += '|';
			}
			
			var input_list = document.getElementById('stugradelist');
			input_list.value = thestring;
			var input_flag = document.getElementById('savegradesflag');
			input_flag.value = 1;
		}
	// End holistic grade data functions
	////////////////////////////////////////


		//]]-->
	</script>
	
	<!-- print the div for using as question name tooltip -->
	<div id="question_name_field" style="display:none; position:absolute; background-color:#ffffff"></div>
	
	<form action="grader_actions.php" method="post">
	
	
	<script type="text/javascript">
	
		function toggle(ID) {
			var box = document.getElementById(ID);
			box.checked = !box.checked;
		}
	
	</script>
	
	<input type="checkbox" id="alwaysshowqnamebox" checked="checked" />
	<label class="noselect" unselectable="on" for='alwaysshowqnamebox' onclick="toggle('alwaysshowqnamebox');"><?php echo
		get_string('legendshowquestionnamebox', 'languagelesson'); ?></label>
	
	<input type="checkbox" id="toggleallstudentsbox" onclick="select_all_students(this);" />
	<label class="noselect" unselectable="on" for='toggleallstudentsbox' onclick="toggle('toggleallstudentsbox');"><?php echo
		get_string('legendselectallstudentsbox', 'languagelesson'); ?></label>
	
	<input type="checkbox" id="useHTMLbox" name="useHTML" checked="checked" />
	<label class="noselect" unselectable="on" for='useHTMLbox' onclick="toggle('useHTMLbox');"><?php echo
		get_string('legenduseHTMLbox', 'languagelesson'); ?></label>
	
	
	<?php
	

/// establish the form and initialize basic values
	echo "<input type=\"hidden\" name=\"cmid\" value=\"$cm->id\" />";
	echo "<input type=\"hidden\" name=\"students_toemail\" id=\"stuidlist\" />";
	echo '<input type="hidden" name="savegradesflag" id="savegradesflag" value="0" />';
	echo '<input type="hidden" name="students_grades" id="stugradelist" />';


/// start the lesson map table
	print_simple_box_start('center');
	
/// print out completed action messages
	$savedgrades = optional_param('savedgrades', 0, PARAM_INT);
	$sentemails = optional_param('sentemails', 0, PARAM_INT);
	
	if ($savedgrades) {
		$message = get_string('gradessaved', 'languagelesson');
	} else if ($sentemails) {
		$message = get_string('emailssent', 'languagelesson');
	}
	
	if (isset($message)) { ?>
		<p class="mod-languagelesson" id="sentemailsnotifier"><?php echo $message; ?></p>
	<?php }
	

/// retrieve necessary data for printing out the grader table
	$numqs = get_numqs($lesson->id); //pull number of questions in complete lesson
	$students = languagelesson_get_students($course->id); //pull list of students enrolled in the course
	$pages = get_records('languagelesson_pages', 'lessonid', $lesson->id, 'ordering'); //pull list of sorted page ids for lesson
	$grades = get_grades_by_userid($lesson->id); //pull array of grades, mapped to userid, for the lesson

	// sort the students by last name, first name
	if (!$students) { error("Could not retrieve students for $course->fullname"); }
	$names = array();
	$students_by_name = array();
	foreach ($students as $student) {
		// make absolutely sure these are unique by including email at the end
		$stuname = strtolower($student->lastname . $student->firstname . $student->email);
		$names[] = $stuname;
		$students_by_name[$stuname] = $student;
	}
	sort($names);

	// print to a string the numbered column headers for the main grid, and populate the branchTracker
	list($colheaders, $btracker) = printColHeaders($pages);
	// print out the structural headers (for branch tables)
	list($btheaders, $numrows) = printBranchTableHeaders($btracker);
	// and print to strings the rows of each of the three tables
	list($namesContents, $gridContents, $rightContents) = populateRows($lesson, $pages, $names, $students_by_name, $grades);

	?>
	<table id="grader_content" style="table-layout: fixed;">
		<tr>
			<td id="stunames_column_cell" class="grader_content_column_cell">

			<?php /// print out the frozen left-hand column of student names as its own table ?>
				<div id="stunames_column_container">
					<table id="stunames_column">
						<?php for ($i=0; $i<$numrows; $i++) { echo '<tr class="offset_row"></tr>'; } ?>
						<tr class="header_row">
							<td id="student_column_header_cell" class="grader header_cell"><span class="rotate-text">
								<?php echo get_string('graderstudentcolumnname','languagelesson'); ?></span></td>
						</tr>
						<?php echo $namesContents; ?>
					</table>
				</div>
		
			</td>
			<td id="lesson_map_cell" class="grader_content_column_cell">

				<div id="lesson_map_container">
					<table id="lesson_map_table" class="grader">
				<?php
				// use style="overflow:auto" to get the scrollbar
				echo $btheaders;
				echo $colheaders;
				echo $gridContents;
				?>
					</table>
				</div>

			</td>
			<td id="right_column_cell" class="grader_content_column_cell">

				<div id="dynamic_content_container">
					<table id="dynamic_content">
						<?php for ($i=0; $i<$numrows; $i++) { echo '<tr class="offset_row"></tr>'; } ?>
						<tr class="header_row">
							<td class=\"grader\" id=\"assign_grade_column_header_cell\">
								<?php echo get_string("assigngradecolumnheader", 'languagelesson', get_field('languagelesson', 'grade',
											'id', $lesson->id)); ?></td>
							<td class=\"grader\" id=\"saved_grade_column_header_cell\">
								<?php echo get_string("savedgradecolumnheader", 'languagelesson'); ?></td>
						</tr>
						<?php
						echo $rightContents;
						print_submission_buttons_row();
						?>
					</table>
				</div>
			
			</td>
		</tr>

	</table>
	<?php

	print_simple_box_end();

    echo "</form>";
    
/// print the lesson map legend
    print_legend();

/// end the page
    print_footer($course);



/// populate the rows of the three columns
function populateRows($lesson, $pages, $names, $students_by_name, $grades) {
	$namesContents = '';
	$gridContents = '';
	$rightContents = '';

	foreach ($names as $name) {
		$student = $students_by_name[$name];

		$thisRow = '';

		// names table
		$thisRow .= '<tr class="student_row">';
		$thisRow .= '<td class="stuname_cell">';
		$thisRow .= "$student->firstname $student->lastname";
		$thisRow .= '</td></tr>';
		
		$namesContents .= $thisRow;
		$thisRow = '';

		// grid table
		$attempts = languagelesson_get_most_recent_attempts($lesson->id, $student->id);
		$thisRow .= '<tr class="student_row">';
		$thisRow .= print_row($attempts, $pages);
		$thisRow .= '</tr>';
		$gridContents .= $thisRow;
		$thisRow = '';

		// right table
		$thisRow .= '<tr class="student_row">';
		$thisRow .= "<td class=\"grader assign_grade_cell\"><input type=\"text\" size=\"2\" class=\"holistic_grade_box\"
			onblur=\"update_holistic_grade_array(this, $student->id);\" name=\"holistic_grade_box_$student->id\" /></td>";
		$thisRow .= "<td class=\"grader saved_grade_cell\">".(in_array($student->id, array_keys($grades)) ?
				$grades[$student->id] : '')."</td>";
		$thisRow .= "<td class=\"grader email_cell\"><input type=\"checkbox\" class=\"email_student_checkbox\"
			onclick=\"update_stulist(event, this);\" value=\"$student->id\" /></td>";
		$thisRow .= '</tr>';

		$rightContents .= $thisRow;
	}

	return array($namesContents, $gridContents, $rightContents);
}

function printColHeaders($pages) {
	// print out the column headers for the grid
	$colheaders = "<tr class=\"header_row\">";

	$pageNumber=1;
	$btracker = new BTracker();
	foreach ($pages as $pageid => $page) {
		// tell the branch tracker that we saw another page
		$btracker->incrementActiveBTs();

		switch ($page->qtype) {
			case LL_CLUSTER:
			case LL_ENDOFCLUSTER:
				break;
			case LL_BRANCHTABLE:
				// push the new branch table into the branch tracker
				$btracker->push($pageid, $page->title);

				$colheaders .= "<td class=\"grader bt_border_cell\" />\n";
				break;
			case LL_ENDOFBRANCH:
				// tell the branch tracker that we saw an end of branch page
				$btracker->sawEOB();

				$parentBT = get_field('languagelesson_branches', 'parentid', 'id', $page->branchid);
				if ($parentBT == $page->nextpageid) {
					$colheaders .= "<td class=\"grader eob_cell\" />\n";
				} else {
					$colheaders .= "<td class=\"grader bt_border_cell\" />\n";
				}
				break;
			default:
				$colheaders .= "<td class=\"grader header_cell question_title\" onmouseover=\"showtooltip(event,'" .
					get_field('languagelesson_pages', 'title', 'id', $pageid) . "')\" onmouseout=\"hidetooltip();\">
					<!--<span class=\"question_name\" id=\"question_name_$pageNumber\">" . get_field('languagelesson_pages',
							'title', 'id', $pageid) . "</span>-->
					<span class=\"rotate-text\">$pageNumber</span>
				</td>\n";
				$pageNumber++;
				break;
		}
	}
	$colheaders .= "</tr>";

	return array($colheaders, $btracker);
}


function printBranchTableHeaders($btracker) {
	// now create the rows for showing branch table divisions
	$btheaders = '';
	// init $borderPositions to empty, as it will get set halfway through and needs to roll over
	// between iterations
	$borderPositions = array();

	foreach ($btracker->nestLevels as $level => $bts) {
		// print the titles of any branch tables at this level
		$btheaders = printBTTitles($borderPositions, $bts, $btheaders, $level);
		// get the start/end positions of ALL branch tables at and above this nesting level
		$borderPositions = $btracker->getBorderPositions($level);
		// print the titles of the branches belonging to BTs at this level
		$btheaders = printBranchTitles($borderPositions, $btheaders, $level);
	}

	// return the headers HTML and the number of rows used in printing the headers
	return array($btheaders, ($level+1)*2);
}

function printBTTitles($borderPositions, $bts, $btheaders, $level) {
	// now loop over the border positions and the branches in this level together to print the
	// contents of the row in order
	$bpIndex = 0; // border position
	$btIndex = 0; // branch table
	$nPagesSpanned = 0;

	$btheaders .= '<tr class="grader offset_row">';

	while ($bpIndex < count($borderPositions)
			|| $btIndex < count($bts)) {
		$bt = ($btIndex < count($bts)) ? $bts[$btIndex] : null;

		// $btBorderPositions is not guaranteed to have content, so check that here
		$nextBorder = (count($borderPositions)) ? $borderPositions[$bpIndex] : null;
		$nextTable = (! is_null($bt)) ? $bt->startPosition : null;

		// if there is a border to worry about and it either comes before the next table or there
		// is no next table, print the next border
		if ( !is_null($nextBorder)
				&& ( is_null($nextTable) || ($nextBorder->position < $nextTable) ) ) {
			// handle any blank space
			$blankSpan = $nextBorder->position - $nPagesSpanned;
			if ($blankSpan) { $btheaders .= "<td class=\"grader\" colspan=\"$blankSpan\" />"; }
			// and print the border cell
			$class = (($nextBorder->eob) ? 'eob_cell' : 'bt_border_cell');
			$btheaders .= "<td class=\"grader $class\" />";
			// update $nPagesSpanned
			$nPagesSpanned = $nextBorder->position + 1;
			// and increment the border index
			++$bpIndex;
		// otherwise print the next table
		} else {
			// handle any blank space
			$blankSpan = $nextTable - $nPagesSpanned;
			if ($blankSpan) { $btheaders .= "<td class=\"grader\" colspan=\"$blankSpan\"></td>"; }
			// print the BT title
			$btheaders .= "<td class=\"grader bt_title_cell\" colspan=\"$bt->countPages\">
							$bt->title</td>";
			// update $nPagesSpanned
			$nPagesSpanned = $nextTable + $bt->countPages;
			// and increment the table index
			++$btIndex;
		}
	}

	$btheaders .= '</tr>';

	return $btheaders;
}

function printBranchTitles($borderPositions, $btheaders, $level) {
	$nPagesSpanned = 0;
	if (count($borderPositions)) {
		$btheaders .= '<tr class="grader offset_row">';

		$printing = false;
		foreach ($borderPositions as $bp) {
			$colspan = $bp->position - $nPagesSpanned;
			if ($colspan) {
				$btheaders .= "<td class=\"grader branch_title_cell\" colspan=\"$colspan\">"
							  . (($printing) ? $branches[$curIndex++]->title : '') . '</td>';
			}
			if (! $bp->eob && $bp->nestlevel == $level) {
				$printing = (! $printing);
				if ($printing) {
					$branches = array_values(get_records('languagelesson_branches', 'parentid', $bp->btid,
								'ordering'));
					$curIndex = 0;
				}
			}
			// and print the border cell
			$class = (($bp->eob) ? 'eob_cell' : 'bt_border_cell');
			$btheaders .= "<td class=\"grader $class\" />";
			// update $nPagesSpanned
			$nPagesSpanned = $bp->position + 1;
		}
		$btheaders .= '</tr>';
	}

	return $btheaders;
}

class BTracker {

	var $nestLevels = array();
	var $n = -1;
	var $curNestLevel = -1;
	var $curBT = null;

	/**
	 * Create a tracking object for a new branch table, initializing it to nothing seen yet;
	 * Push it in at the next nested level
	 **/
	function push($btid, $title) {
		$bt = new stdClass;
		$bt->id = $btid;
		$bt->title = $title;
		$bt->countPages = 1; // counting the BT page itself
		$bt->startPosition = $this->n;
		$bt->complete = false;
		$bt->expectedBranches = count_records('languagelesson_branches', 'parentid', $btid);
		$bt->seenEOBs = 0;
		$bt->parent = $this->curBT;

		++$this->curNestLevel;
		if (! array_key_exists($this->curNestLevel, $this->nestLevels)) {
			$this->nestLevels[$this->curNestLevel] = array();
		}
		$this->nestLevels[$this->curNestLevel][] = $bt;

		$this->curBT = &$bt;
	}

	/**
	 * For all branch tables that are not yet complete, increments the number of pages they span
	 **/
	function incrementActiveBTs() {
		foreach ($this->nestLevels as $level => $bts) {
			foreach ($bts as &$bt) {
				if (! $bt->complete) { ++$bt->countPages; }
			}
		}
		$this->n++;
	}

	/**
	 * Notes in the current branch table that another of its branches has been completed;
	 * If the current branch table has then been completed, pops up to its parent
	 **/
	function sawEOB() {
		if (++$this->curBT->seenEOBs == $this->curBT->expectedBranches) {
			$this->curBT->complete = true;
			$this->curBT = &$this->curBT->parent;
			$this->curNestLevel--;
		}
	}
	
	/**
	 * Gets a sorted list of number of pages preceding each BT beginning and end in all nesting
	 * levels above the input level
	 * @param int $level The nesting level in question
	 * @return array $positions The sorted positions
	 **/
	function getBorderPositions($level) {
		$positions = array();

		for ($i=0; $i<=$level; $i++) {
			$bts = $this->nestLevels[$i];
			foreach ($bts as $bt) {
				$curCount = $bt->startPosition;
				// add in the first (BT) border
				$data = new stdClass;
				$data->eob = false;
				$data->btid = $bt->id;
				$data->btstart = true;
				$data->nestlevel = $i;
				$data->position = $curCount;
				$positions[] = $data;
				// pull the branches to populate EOB borders
				$branches = get_records('languagelesson_branches', 'parentid', $bt->id, 'ordering');
				foreach ($branches as $bid => $b) {
					// check the number of pages directly in the branch
					$numBranchPages = count_records('languagelesson_pages', 'branchid', $bid) - 1;
					// if this branch has any nested branch tables, include their page counts as well
					if ($subs = get_records_select('languagelesson_pages', 'qtype='.LL_BRANCHTABLE." and branchid=$bid")) {
						foreach ($subs as $subID => $sub) {
							// take off 1 for the BT itself, as countPages includes that
							$numBranchPages--;
							// find the corresponding $bt record in $bts
							foreach ($this->nestLevels[$i+1] as $btALT) {
								if ($btALT->id == $subID) {
									$numBranchPages += $btALT->countPages;
									break;
								}
							}
						}
					}
					$curCount = $curCount + $numBranchPages + 1;
					// add in the EOB data
					$data = new stdClass;
					$data->eob = true;
					$data->position = $curCount;
					$positions[] = $data;
				}
				// pop off the most recent EOB added, because we're about to populate it as the end
				// of a BT; NOTE that it should still be added in above in order to update $curCount
				// to the correct value
				array_pop($positions);
				// add the last (BT) border
				$data = new stdClass;
				$data->eob = false;
				$data->btid = $bt->id;
				$data->btstart = false;
				$data->nestlevel = $i;
				$data->position = $curCount;
				$positions[] = $data; 
			}
		}

		usort($positions, "BTracker::cmp");

		return $positions;
	}
	static function cmp($a, $b) { return ($a->position < $b->position) ? -1 : 1; }

}


function print_submission_buttons_row() {
	echo '<td> <input type="submit" onclick="update_stugrade_input();" value="'.get_string('assigngradesbutton','languagelesson').'" /> </td>';
	//echo '<td><button onclick="document.form.submit();" style="width:50px;height:50px;">Save grades</button></td>';
	echo '<td></td>';
	echo '<td> <input type="submit" onclick="update_stulist_input();" value="Notify students" /> </td>';
}



function get_grades_by_userid($lessonid) {
	$dict = array();
	if ($grades = get_records("languagelesson_grades", "lessonid", $lessonid)) {
		foreach ($grades as $grade) {
			$dict[$grade->userid] = $grade->grade;
		}
	}
	return $dict;
}



function get_numqs($lessonid)
{
	/* returns the number of pages for the input lessonid */
	global $CFG;
	$numqs = get_record_sql("select distinct count(id) as ct_id
											from {$CFG->prefix}languagelesson_pages
											where lessonid=$lessonid");
	return $numqs->ct_id;
}









/*
 * Construct and print a row of table cells corresponding to a student's attempts on this languagelesson
 * @param objArr $attempts The student's recorded attempt records for this ll instance
 * @param objArr $pages All pages in the languagelesson, in sorted order
 */
function print_row($attempts, $pages)
{
	global $colors, $CFG, $cm;

	// for convenience; if no attempts have been made, $attempts will be null, so init it here to be an empty array so we can use the
	// foreach construction below
	if (! $attempts) { $attempts = array(); }
	
	// begin by constructing an array of table cells for attempts the student has made
	$attemptCells = array();
	foreach ($attempts as $attempt)
	{
		$page = $pages[$attempt->pageid];

		$onclick = false;
		
		$cellcontents = '';
		
	/// assign block classes and onclick values appropriately
		if ($attempt->qtype == LL_AUDIO
				   || $attempt->qtype == LL_VIDEO
				   || $attempt->qtype == LL_ESSAY) {
			
		/// pull the corresponding manual attempt record
			if (! $manattempt = get_record('languagelesson_manattempts', 'id', $attempt->manattemptid)) {
				error('Could not fetch manual attempt record');
			}
			
		/// set $onclick to call the grading window
			$onclick = "window.open('{$CFG->wwwroot}/mod/languagelesson/"
					   . "respond_window.php?attemptid={$attempt->id}&cmid={$cm->id}"
					   . ((! $manattempt->viewed) ? '&needsflag=1' : '') . "'"
					   . ",'Grading Language Lesson','width=950,height=800,toolbar=no,scrollbars=1');";
			
			if ($manattempt->resubmit) {
				$class = get_class_str('resubmit');
			} else if ($manattempt->graded && $feedbacks = get_records('languagelesson_feedback', 'manattemptid', $manattempt->id)) {
				$class = get_class_str('commented');
			} else if ($manattempt->viewed) {
				$class = get_class_str('viewed');
			} else {
				$class = get_class_str('new');
			}
			
			if ($attempt->qtype == LL_AUDIO) {
				$cellcontents = get_cell_contents('audio');
			} else if ($attempt->qtype == LL_VIDEO) {
				$cellcontents = get_cell_contents('video');
			} else {
				$cellcontents = get_cell_contents('essay');
			}
			
		} else {
		//dealing with automatically graded question, so check its correct value
			if ($attempt->correct) {
				$class = get_class_str('autocorrect');
			} else {
				$class = get_class_str('autowrong');
			}
		}
		
	  /// set the onclick script
		if ($onclick) {
			$onclickprint = "onclick=\"$onclick\"";
		} else {
			$onclickprint = "";
		}
		
	  /// set the tooltip
		$showqnamescript = "onmouseover=\"showtooltip_itemcell(event,'" . $page->title . "')\" onmouseout=\"hidetooltip();\"";
		
	  /// set the cell id
		$id = "attempt_cell_{$attempt->id}";
		
		// finally, construct the cell
		$thiscell = "<td class=\"grader item_cell $class\" id=\"$id\" "
			 . "$onclickprint $showqnamescript>$cellcontents</td>\n";

		// and store it, keyed to the pageid
		$attemptCells[$attempt->pageid] = $thiscell;
	}


	$output = '';


	// now loop over all the pages in order, printing out the cell if there is an attempt or printing an empty one if no attempt
	foreach ($pages as $pageid => $page) {
		// if it's a structural page, handle it appropriately
		switch ($page->qtype) {
			case LL_BRANCHTABLE:
				$output .= "<td class=\"grader bt_border_cell\" />\n";
			case LL_CLUSTER:
			case LL_ENDOFCLUSTER:
				continue 2;
				break;
			case LL_ENDOFBRANCH:
				$parentBT = get_field('languagelesson_branches', 'parentid', 'id', $page->branchid);
				if ($parentBT == $page->nextpageid) {
					$output .= "<td class=\"grader eob_cell\" />\n";
				} else {
					$output .= "<td class=\"grader bt_border_cell\" />\n";
				}
				continue 2;
				break;
			default:
				break;
		}

		// if there was an attempt recorded for this page, just print the attempt cell
		if (array_key_exists($pageid, $attemptCells)) {
			$output .= $attemptCells[$pageid];
		}

		// otherwise, build an empty cell
		else {
			$class = get_class_str('none');
			$showqnamescript = "onmouseover=\"showtooltip_itemcell(event,'" . $page->title . "')\" onmouseout=\"hidetooltip();\"";
			$emptycell = "<td class=\"grader item_cell $class\" $showqnamescript>&nbsp;</td>\n";

			$output .= $emptycell;
		}

	}
	

	// return the content of this grid row 
	return $output;

}




function get_class_str($input) {
	return get_string("grader{$input}",'languagelesson');
}


function get_cell_contents($type) {
	return get_string("cellcontents_$type", 'languagelesson');
}




function print_legend() {
	
	print_simple_box_start('center');
	
	echo "<table id=\"legend_table\" class=\"legend leg_table\">
				<tr>
					<td class=\"legend leg_color_cell " . get_class_str('none') . "\" />
					<td class=\"legend leg_name_cell\">" . get_string('legendnone', 'languagelesson') . "</td>
				
					<td class=\"legend leg_color_cell " . get_class_str('autocorrect') . "\" />
					<td class=\"legend leg_name_cell\">" . get_string('legendautocorrect', 'languagelesson') . "</td>
					
					<td class=\"legend leg_color_cell " . get_class_str('autowrong') . "\" />
					<td class=\"legend leg_name_cell\">" . get_string('legendautowrong', 'languagelesson') . "</td>

					<td class=\"legend leg_color_cell " . get_class_str('new') . "\" />
					<td class=\"legend leg_name_cell\">" . get_string('legendnew', 'languagelesson') . "</td>
					
					<td class=\"legend leg_color_cell " . get_class_str('viewed') . "\" />
					<td class=\"legend leg_name_cell\">" . get_string('legendviewed', 'languagelesson') . "</td>
					
					<td class=\"legend leg_color_cell " . get_class_str('commented') . "\" />
					<td class=\"legend leg_name_cell\">" . get_string('legendcommented', 'languagelesson') . "</td>
					
					<td class=\"legend leg_color_cell " . get_class_str('resubmit') . "\" />
					<td class=\"legend leg_name_cell\">" . get_string('legendresubmit', 'languagelesson') . "</td>
				</tr>
			</table>";	
	
	print_simple_box_end();
	
}

?>
