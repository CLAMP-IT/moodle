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
 * Definition of the grade_laeuser_report class is defined
 *
 * @package gradereport_laeuser
 * @copyright 2013 Bob Puffer http://www.clamp-it.org
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once($CFG->dirroot . '/grade/report/lib.php');
require_once $CFG->dirroot.'/grade/report/laegrader/locallib.php';
require_once($CFG->libdir.'/tablelib.php');

//showhiddenitems values
define("GRADE_REPORT_LAEUSER_HIDE_HIDDEN", 0);
define("GRADE_REPORT_LAEUSER_HIDE_UNTIL", 1);
define("GRADE_REPORT_LAEUSER_SHOW_HIDDEN", 2);
define("GRADE_REPORT_LAEUSER_FINAL_GRADE_ONLY", 2);

/**
 * Class providing an API for the user report building and displaying.
 * @uses grade_report
 * @package gradereport_laeuser
 */
class grade_report_laeuser extends grade_report {

    /**
     * The user.
     * @var object $user
     */
    public $user;

    /**
     * A flexitable to hold the data.
     * @var object $table
     */
    public $table;

    /**
     * An array of table headers
     * @var array
     */
    public $tableheaders = array();

    /**
     * An array of table columns
     * @var array
     */
    public $tablecolumns = array();

    /**
     * An array containing rows of data for the table.
     * @var type
     */
    public $tabledata = array();

    /**
     * The grade tree structure
     * @var grade_tree
     */
    public $gtree;

    /**
     * Flat structure similar to grade tree
     */
    public $gseq;

    /**
     * show student ranks
     */
    public $showrank;

    /**
     * show grade percentages
     */
    public $showpercentage;

    /**
     * Show range
     */
    public $showrange = true;

    /**
     * Show grades in the report, default true
     * @var bool
     */
    public $showgrade = true;

    /**
     * Decimal points to use for values in the report, default 2
     * @var int
     */
    public $decimals = 2;

    /**
     * The number of decimal places to round range to, default 0
     * @var int
     */
    public $rangedecimals = 0;

    /**
     * Show grade feedback in the report, default true
     * @var bool
     */
    public $showfeedback = true;

    /**
     * Show grade weighting in the report, default false
     * @var bool
     */
    public $showweight = false;

    /**
     * Show letter grades in the report, default false
     * @var bool
     */
    public $showlettergrade = false;

    /**
     * Show average grades in the report, default false.
     * @var false
     */
    public $showaverage = false;

    public $maxdepth;
    public $evenodd;

    public $canviewhidden;

    public $switch;

    /**
     * Show hidden items even when user does not have required cap
     */
    public $showhiddenitems;
    public $showtotalsifcontainhidden;

    public $baseurl;
    public $pbarurl;

    public $items;
    public $parents;

    /**
     * Constructor. Sets local copies of user preferences and initialises grade_tree.
     * @param int $courseid
     * @param object $gpr grade plugin return tracking object
     * @param string $context
     * @param int $userid The id of the user
     */
    public function __construct($courseid, $gpr, $context, $userid) {
        global $DB, $CFG;
        parent::__construct($courseid, $gpr, $context);

        $this->showrank        = grade_get_setting($this->courseid, 'report_laeuser_showrank', $CFG->grade_report_laeuser_showrank);
        $this->showpercentage  = grade_get_setting($this->courseid, 'report_laeuser_showpercentage', $CFG->grade_report_laeuser_showpercentage);
        $this->showhiddenitems = grade_get_setting($this->courseid, 'report_laeuser_showhiddenitems', $CFG->grade_report_laeuser_showhiddenitems);
        $this->showtotalsifcontainhidden = array($this->courseid => grade_get_setting($this->courseid, 'report_laeuser_showtotalsifcontainhidden', $CFG->grade_report_laeuser_showtotalsifcontainhidden));

        $this->showgrade       = grade_get_setting($this->courseid, 'report_laeuser_showgrade',       !empty($CFG->grade_report_laeuser_showgrade));
        $this->showrange       = grade_get_setting($this->courseid, 'report_laeuser_showrange',       !empty($CFG->grade_report_laeuser_showrange));
        $this->showfeedback    = grade_get_setting($this->courseid, 'report_laeuser_showfeedback',    !empty($CFG->grade_report_laeuser_showfeedback));
        $this->showweight      = grade_get_setting($this->courseid, 'report_laeuser_showweight',      !empty($CFG->grade_report_laeuser_showweight));
        $this->showlettergrade = grade_get_setting($this->courseid, 'report_laeuser_showlettergrade', !empty($CFG->grade_report_laeuser_showlettergrade));
        $this->showaverage     = grade_get_setting($this->courseid, 'report_laeuser_showaverage',     !empty($CFG->grade_report_laeuser_showaverage));
        $this->accuratetotals		= ($temp = grade_get_setting($this->courseid, 'report_laegrader_accuratetotals', $CFG->grade_report_laegrader_accuratetotals)) ? $temp : 0;
        $this->showcontrib     = grade_get_setting($this->courseid, 'report_laeuser_showcontrib',     !empty($CFG->grade_report_laeuser_showcontrib));
        $this->emptygrades = array();
	   	$this->grades = array();


        // The default grade decimals is 2
        $defaultdecimals = 2;
        if (property_exists($CFG, 'grade_decimalpoints')) {
            $defaultdecimals = $CFG->grade_decimalpoints;
        }
        $this->decimals = grade_get_setting($this->courseid, 'decimalpoints', $defaultdecimals);

        // The default range decimals is 0
        $defaultrangedecimals = 0;
        if (property_exists($CFG, 'grade_report_laeuser_rangedecimals')) {
            $defaultrangedecimals = $CFG->grade_report_laeuser_rangedecimals;
        }
        $this->rangedecimals = grade_get_setting($this->courseid, 'report_laeuser_rangedecimals', $defaultrangedecimals);

        $this->switch = grade_get_setting($this->courseid, 'aggregationposition', $CFG->grade_aggregationposition);

        // Grab the grade_tree for this course
        $this->gtree = grade_tree_local_helper($this->courseid, false, $this->switch, null, !$CFG->enableoutcomes,0);
        $this->gtree->showtotalsifcontainhidden = $this->showtotalsifcontainhidden[$this->courseid];

        // Fill items with parent information needed later
        $this->gtree->parents = array();
        $this->gtree->cats = array();
        if ($this->accuratetotals) { // don't even go to fill_parents unless accuratetotals is set
            $this->gtree->fill_cats($this->gtree);
    		$this->gtree->parents[$this->gtree->top_element['object']->grade_item->id] = new stdClass(); // initiate the course item
            $this->gtree->fill_parents($this->gtree->top_element, $this->gtree->top_element['object']->grade_item->id, $this->showtotalsifcontainhidden);
        }
//		$this->gtree->calc_weights();

		// Determine the number of rows and indentation
        $this->maxdepth = 1;
        $this->inject_rowspans($this->gtree->top_element);
        $this->maxdepth++; // Need to account for the lead column that spans all children
        for ($i = 1; $i <= $this->maxdepth; $i++) {
            $this->evenodd[$i] = 0;
        }

        $this->tabledata = array();

        $this->canviewhidden = has_capability('moodle/grade:viewhidden', context_course::instance($this->courseid));

        // get the user (for full name)
        $this->user = $DB->get_record('user', array('id' => $userid));

        // base url for sorting by first/last name
        $this->baseurl = $CFG->wwwroot.'/grade/report?id='.$courseid.'&amp;userid='.$userid;
        $this->pbarurl = $this->baseurl;

        // no groups on this report - rank is from all course users
        $this->setup_table();

        //optionally calculate grade item averages
        $this->calculate_averages();
    }

    /**
     * Recurses through a tree of elements setting the rowspan property on each element
     *
     * @param array $element Either the top element or, during recursion, the current element
     * @return int The number of elements processed
     */
    function inject_rowspans(&$element) {

        if ($element['depth'] > $this->maxdepth) {
            $this->maxdepth = $element['depth'];
        }
        if (empty($element['children'])) {
            return 1;
        }
        $count = 1;

        foreach ($element['children'] as $key=>$child) {
            $count += $this->inject_rowspans($element['children'][$key]);
        }

        $element['rowspan'] = $count;
        return $count;
    }


    /**
     * Prepares the headers and attributes of the flexitable.
     */
    public function setup_table() {
        /*
         * Table has 1-8 columns
         *| All columns except for itemname/description are optional
         */

        // setting up table headers

        $this->tablecolumns = array('itemname');
        $this->tableheaders = array($this->get_lang_string('gradeitem', 'grades'));

        if ($this->showweight) {
            $this->tablecolumns[] = 'weight';
            $this->tableheaders[] = $this->get_lang_string('weightuc', 'gradereport_laeuser');
        }

        if ($this->showgrade) {
            $this->tablecolumns[] = 'grade';
            $this->tableheaders[] = $this->get_lang_string('grade', 'grades');
        }

        if ($this->showrange) {
            $this->tablecolumns[] = 'range';
            $this->tableheaders[] = $this->get_lang_string('range', 'grades');
        }

        if ($this->showpercentage) {
            $this->tablecolumns[] = 'percentage';
            $this->tableheaders[] = $this->get_lang_string('percentage', 'gradereport_laeuser');
        }

        if ($this->showlettergrade) {
            $this->tablecolumns[] = 'lettergrade';
            $this->tableheaders[] = $this->get_lang_string('lettergrade', 'grades');
        }

        if ($this->showrank) {
            $this->tablecolumns[] = 'rank';
            $this->tableheaders[] = $this->get_lang_string('rank', 'grades');
        }

        if ($this->showaverage) {
            $this->tablecolumns[] = 'average';
            $this->tableheaders[] = $this->get_lang_string('average', 'grades');
        }

        if ($this->showcontrib) {
            $this->tablecolumns[] = 'contrib';
            $this->tableheaders[] = $this->get_lang_string('contrib', 'gradereport_laeuser');
        }

        if ($this->showfeedback) {
            $this->tablecolumns[] = 'feedback';
            $this->tableheaders[] = $this->get_lang_string('feedback', 'grades');
        }
    }

    function fill_table() {
        global $DB, $COURSE;
        $userid = $this->user->id;
		$this->load_final_grades($userid);
		$this->gtree->emptycats = array();
        $this->gtree->accuratepointsprelimcalculation($this->grades); // gives us our pctg in case we have drop or keep situations
//        $this->gtree->grades = $this->grades;
        if (isset($this->target_letter)) {
            $this->gtree->calc_weights_recursive($this->gtree->top_element, $this->grades, $this->target_letter);
		} else {
            $this->gtree->calc_weights_recursive($this->gtree->top_element, $this->grades);
		}

    	$this->fill_table_recursive($this->gtree->top_element);
        //print_r($this->tabledata);
        //print "</pre>";
        return true;
    }

    private function fill_table_recursive(&$element) {
        global $DB, $CFG, $USER;

        $type = $element['type'];
        $depth = $element['depth'];
        $grade_object = $element['object'];
        $eid = '';
        $itemid = $grade_object->id;
        $element['userid'] = $this->user->id;
        $itemname = $element['object']->get_name();
        $fullname = $this->gtree->get_element_header($element, true, true, true, null, $itemname);
        $data = array();
        $hidden = '';
        $excluded = '';
        $class = '';
        $classfeedback = '';
        $accuratetotals = $this->accuratetotals;
        $showtotalsifcontainhidden = $this->showtotalsifcontainhidden[$this->courseid];
        $targetclass = ' target ';

        // substituting shorthand for long object variables
        $items = $this->gtree->items;
        $item =& $items[$itemid];

        // need to reset extra credit for the item
        if (!isset($this->gtree->parents[$itemid]->excredit)) {
			if (!isset($this->gtree->parents[$itemid])) {
				$this->gtree->parents[$itemid] = new stdClass;
			}
//			$this->gtree->parents[$itemid]->excredit = 0;
		}

        // If this is a hidden grade category, hide it completely from the user
        if ($type == 'category' && $grade_object->is_hidden() && !$this->canviewhidden && (
                $this->showhiddenitems == GRADE_REPORT_LAEUSER_HIDE_HIDDEN ||
                ($this->showhiddenitems == GRADE_REPORT_LAEUSER_HIDE_UNTIL && !$grade_object->is_hiddenuntil()))) {
            return false;
        }

        if ($type == 'category') {
            $this->evenodd[$depth] = (($this->evenodd[$depth] + 1) % 2);
        }
        $alter = ($this->evenodd[$depth] == 0) ? 'even' : 'odd';

        /// Process those items that have scores associated
        if ($type == 'item' or $type == 'categoryitem' or $type == 'courseitem') {
            $header_row = "row_{$eid}_{$this->user->id}";
            $header_cat = "cat_{$grade_object->categoryid}_{$this->user->id}";

			$grade = $this->grades[$itemid];

            /// Hidden Items
            if ($grade->grade_item->is_hidden()) {
                $hidden = ' hidden';
            }

            if ($accuratetotals) {
                $parent_id = $type !== 'courseitem' ? $this->gtree->parents[$itemid]->parent_id : $itemid; // the parent record contains an id field pointing to its parent, the key on the parent record is the item itself to allow lookup
            }
//            if ($type !== 'courseitem') {
//    			$parent_id = $this->gtree->parents[$itemid]->parent_id; // the parent record contains an id field pointing to its parent, the key on the parent record is the item itself to allow lookup
//            }

	        $hide = false;
            // If this is a hidden grade item, hide it completely from the user.
            if ($grade->is_hidden() && !$this->canviewhidden && (
                    $this->showhiddenitems == GRADE_REPORT_LAEUSER_HIDE_HIDDEN ||
                    ($this->showhiddenitems == GRADE_REPORT_LAEUSER_HIDE_UNTIL && !$grade->is_hiddenuntil()))) {
                $hide = true;
            } else if (!empty($grade_object->itemmodule) && !empty($grade_object->iteminstance)) {
                // The grade object can be marked visible but still be hidden if...
                //  1) "enablegroupmembersonly" is on and the activity is assigned to a grouping the user is not in.
                //  2) the student cannot see the activity due to conditional access and its set to be hidden entirely.
                $instances = $this->gtree->modinfo->get_instances_of($grade_object->itemmodule);
                if (!empty($instances[$grade_object->iteminstance])) {
                    $cm = $instances[$grade_object->iteminstance];
                    if (!$cm->uservisible) {
                        // Further checks are required to determine whether the activity is entirely hidden or just greyed out.
                        if ($cm->is_user_access_restricted_by_group() || $cm->is_user_access_restricted_by_conditional_access() ||
                                $cm->is_user_access_restricted_by_capability()) {
                            $hide = true;
                        }
                    }
                }
            }

            if (!$hide) {
                /// Excluded Item
                if ($grade->is_excluded()) {
                    $fullname .= ' ['.get_string('excluded', 'grades').']';
                    $excluded = ' excluded';
                }

                /// Other class information
                $class = "$hidden $excluded";
                if ($this->switch) { // alter style based on whether aggregation is first or last
                   $class .= ($type == 'categoryitem' or $type == 'courseitem') ? " ".$alter."d$depth baggt b2b" : " item b1b";
                } else {
                   $class .= ($type == 'categoryitem' or $type == 'courseitem') ? " ".$alter."d$depth baggb" : " item b1b";
                }
                if ($type == 'categoryitem' or $type == 'courseitem') {
                    $header_cat = "cat_{$grade_object->iteminstance}_{$this->user->id}";
                }

                /// Name
                $data['itemname']['content'] = $fullname;
	        	$data['itemname']['class'] = $class;
                $data['itemname']['colspan'] = ($this->maxdepth - $depth);
                $data['itemname']['celltype'] = 'th';
                $data['itemname']['id'] = $header_row;
	        	if ($item->aggregationcoef > 0 && $this->gtree->parents[$item->id]->parent_agg != GRADE_AGGREGATE_WEIGHTED_MEAN2) {
					$data['itemname']['content'] .= ' (Input weight = ' . format_float($item->aggregationcoef,2) . '%)';
	        	}

                /// Actual Grade
                $gradeval = $grade->finalgrade;

                if ($this->showfeedback) {
                    // Copy $class before appending itemcenter as feedback should not be centered
                    $classfeedback = $class;
                }
                $class .= " itemcenter ";
	           	$rangeclass = $class; // if we alter class for targets we want ranges to remain unaltered
                $targetcalcs = false; // flag for target grade mode
                if ($this->showweight) {
                    $data['weight']['content'] = '-';
                    $data['weight']['headers'] = "$header_cat $header_row weight";
					$data['weight']['class'] = $class;

					// has a weight assigned, might be extra credit
                    if ($grade_object->aggregationcoef > 0 && $type !== 'courseitem') {
						$data['weight']['class'] = $class;
						if ($this->gtree->parents[$itemid]->parent_agg != GRADE_AGGREGATE_WEIGHTED_MEAN) { // extra credit
                    		$data['weight']['content'] = 'Extra Credit';
	        			} else {
                        	$data['weight']['content'] = number_format($grade_object->weight,2).'%';
	        			}
                    } else {
						$data['weight']['class'] = $class;
						$data['weight']['content'] = number_format($grade_object->weight,2).'%';
                    }
                }

                // if target grade mode change class and set flag
                if (isset($this->gtree->items[$itemid]->target)) {
					$class .= $targetclass;
                   	$targetcalcs = true; // switch for everything being treated as a calculated target grade
                }

                if ($this->showgrade) { // display only of points grade
                	if ($grade->grade_item->needsupdate) {
                        $data['grade']['class'] = $class.' gradingerror';
                        $data['grade']['content'] = get_string('error');
                	} else if (!empty($CFG->grade_hiddenasdate) and $grade->get_datesubmitted() and !$this->canviewhidden and $grade->is_hidden()
                           and !$grade->grade_item->is_category_item() and !$grade->grade_item->is_course_item()) {
                        // the problem here is that we do not have the time when grade value was modified, 'timemodified' is general modification date for grade_grades records
                        $class .= ' datesubmitted';
                        $data['grade']['class'] = $class;
                        $data['grade']['content'] = get_string('submittedon', 'grades', userdate($grade->get_datesubmitted(), get_string('strftimedatetimeshort')));
                    } elseif ($grade->is_hidden()) {
                        $data['grade']['class'] = $class.' hidden';
                        $data['grade']['content'] = '-';
                    } else if ($targetcalcs && ($item->itemtype === 'category' || $item->itemtype === 'course')) {
                        $data['grade']['class'] = $class;
						$grade_values = $grade->cat_item; // earned points
                        // $grade_values never gets created if $this->accuratetotals isn't on
                        if (sizeof($grade_values) !== 0) { // CATEGORY or COURSE item with values accumulated from its children
							$gradeval = $this->gtree->accuratepointsfinalvalues($this->grades, $itemid, $grade->grade_item, $type, $parent_id, GRADE_DISPLAY_TYPE_REAL);
                        }
                    	$data['grade']['content'] = grade_format_gradevalue($gradeval, $grade->grade_item, true,GRADE_DISPLAY_TYPE_REAL);
                    } else if ($targetcalcs) {
                        $data['grade']['class'] = $class;
                    	$gradeval = $item->target;
                        $data['grade']['content'] = grade_format_gradevalue($gradeval, $grade->grade_item, true,GRADE_DISPLAY_TYPE_REAL);
                    } else if($type === 'item') {
                        $data['grade']['class'] = $class;
                    	$data['grade']['content'] = grade_format_gradevalue($gradeval, $grade->grade_item, true,GRADE_DISPLAY_TYPE_REAL);
                    } else if (!isset($this->grades[$itemid]->cat_item)) { // CATEGORY or COURSE item with values accumulated from its children
                        $data['grade']['class'] = $class;
                        $data['grade']['content'] = '-';
                    } else {
                        $data['grade']['class'] = $class;
				        // this covers the instance where a course item is sent which doesn't have a parent_id
						$parent_id = !isset($parent_id) ? $itemid: $parent_id;
						$grade_values = $this->grades[$itemid]->cat_item; // earned points
                        // $grade_values never gets created if $this->accuratetotals isn't on
                        if (sizeof($grade_values) !== 0) { // CATEGORY or COURSE item with values accumulated from its children
							$gradeval = $this->gtree->accuratepointsfinalvalues($this->grades, $itemid, $grade->grade_item, $type, $parent_id, GRADE_DISPLAY_TYPE_REAL);
                        }
                    	$data['grade']['content'] = grade_format_gradevalue($gradeval, $grade->grade_item, true,GRADE_DISPLAY_TYPE_REAL);
                    }
                    $data['grade']['headers'] = "$header_cat $header_row grade";
                }
                /***** ACCURATE TOTALS END *****/

                // Range
                if ($this->showrange && (!is_null($gradeval) || $type === 'item')) {
                    $data['range']['class'] = $rangeclass;
                    $data['range']['content'] = $grade->grade_item->get_formatted_range(GRADE_DISPLAY_TYPE_REAL, $this->rangedecimals);
                    $data['range']['headers'] = "$header_cat $header_row range";
                } else {
                    $data['range']['class'] = $rangeclass;
                    $data['range']['content'] = '-';
                    $data['range']['headers'] = "$header_cat $header_row range";
                }

                // adjust gradeval in case of percentage or letter display
                if ($this->accuratetotals && ($type == 'categoryitem' || $type == 'courseitem') && ! $grade->is_hidden()) {
                    if ($type == 'categoryitem') {
                    	$gradeval =  isset($this->grades[$parent_id]->pctg[$itemid]) ? $this->grades[$parent_id]->pctg[$itemid] : null;
                    } else if (!isset($this->grades[$itemid]->coursepctg)) {
                    	$gradeval = 0;
                    } else {
						$gradeval = $this->grades[$itemid]->coursepctg;
					}
					$grade->grade_item->grademax = 1;
				}

                // Percentage
                if ($this->showpercentage) {
                    if ($grade->grade_item->needsupdate) {
                        $data['percentage']['class'] = $class.' gradingerror';
                        $data['percentage']['content'] = get_string('error');
                    } else if ($grade->is_hidden()) {
                        $data['percentage']['class'] = $class.' hidden';
                        $data['percentage']['content'] = '-';
                    } elseif (isset($gradeval) && isset($item->weight) && $item->weight !== 0) {
                        $data['percentage']['class'] = $class;
                    	$data['percentage']['content'] = grade_format_gradevalue($gradeval, $grade->grade_item, true, GRADE_DISPLAY_TYPE_PERCENTAGE, 3);
                    } else {
                        $data['percentage']['class'] = $class;
                        $data['percentage']['content'] = '-';
                    }
                    $data['percentage']['headers'] = "$header_cat $header_row percentage";
                }

                // Lettergrade
                if ($this->showlettergrade) {
                    if ($grade->grade_item->needsupdate) {
                        $data['lettergrade']['class'] = $class.' gradingerror';
                        $data['lettergrade']['content'] = get_string('error');
                    } else if ($grade->is_hidden()) {
                        $data['lettergrade']['class'] = $class.' hidden';
                        if (!$this->canviewhidden) {
                            $data['lettergrade']['content'] = '-';
                        } else {
                            $data['lettergrade']['content'] = grade_format_gradevalue($gradeval, $grade->grade_item, true, GRADE_DISPLAY_TYPE_LETTER);
                        }
                    } else if ($this->showlettergrade == GRADE_REPORT_LAEUSER_FINAL_GRADE_ONLY && $type !== 'courseitem') {
                        $data['lettergrade']['class'] = $class;
                        $data['lettergrade']['content'] = '-';
                    } elseif (isset($gradeval) && isset($item->weight) && $item->weight <> 0) {
                        $data['lettergrade']['class'] = $class;
                        $data['lettergrade']['content'] = grade_format_gradevalue($gradeval, $grade->grade_item, true, GRADE_DISPLAY_TYPE_LETTER);
                    } else {
                        $data['lettergrade']['class'] = $class;
                        $data['lettergrade']['content'] = '-';
                    }
                    $data['lettergrade']['headers'] = "$header_cat $header_row lettergrade";
                }

                // Rank
                if ($this->showrank) {
                    if ($grade->grade_item->needsupdate) {
                        $data['rank']['class'] = $class.' gradingerror';
                        $data['rank']['content'] = get_string('error');
                        } elseif ($grade->is_hidden()) {
                            $data['rank']['class'] = $class.' hidden';
                            $data['rank']['content'] = '-';
                    } else if (is_null($gradeval)) {
                        // no grade, no rank
                        $data['rank']['class'] = $class;
                        $data['rank']['content'] = '-';

                    } else {
                        /// find the number of users with a higher grade
                        $sql = "SELECT COUNT(DISTINCT(userid))
                                  FROM {grade_grades}
                                 WHERE finalgrade > ?
                                       AND itemid = ?
                                       AND hidden = 0";
                        $rank = $DB->count_records_sql($sql, array($grade->finalgrade, $grade->grade_item->id)) + 1;

                        $data['rank']['class'] = $class;
                        $data['rank']['content'] = "$rank/".$this->get_numusers(false); // total course users
                    }
                    $data['rank']['headers'] = "$header_cat $header_row rank";
                }

                // Average
                if ($this->showaverage) {
                    $data['average']['class'] = $class;
                    if (!empty($this->gtree->items[$itemid]->avg)) {
                        $data['average']['content'] = $this->gtree->items[$itemid]->avg;
                    } else {
                        $data['average']['content'] = '-';
                    }
                    $data['average']['headers'] = "$header_cat $header_row average";
                }

                // contrib
                if ($this->showcontrib) {
                    $data['contrib']['class'] = $class;
                    if ($item->itemtype === 'course') {
	                    $data['contrib']['content'] = format_float($gradeval * 100, 3);
                    } else if ($item->itemtype === 'category') {
                        $data['contrib']['content'] = '-';
//                      $data['contrib']['content'] = format_float(array_sum($this->gtree->parents[$item->id]->cat_item) / $item->max_earnable * $this->gtree->items[$item->id]->weight,4);
                    } else if ($this->gtree->items[$item->id]->weight == 0 || in_array($item->id, $this->emptygrades)) {
	                    $data['contrib']['content'] = '-';
                    } else if ($targetcalcs) {
                        $data['grade']['class'] = $class;
                    	$gradeval = $item->target;
	                    $data['contrib']['content'] = format_float($gradeval / $item->grademax * $this->gtree->items[$item->id]->weight,3);
                    } else {
	                    $data['contrib']['content'] = format_float($grade->finalgrade / $item->grademax * $this->gtree->items[$item->id]->weight,3);
                   	}
                    $data['contrib']['headers'] = "$header_cat $header_row contrib";
                }

                // Feedback
                if ($this->showfeedback) {
                    if ($grade->overridden > 0 AND ($type == 'categoryitem' OR $type == 'courseitem')) {
                    	$data['feedback']['class'] = $classfeedback.' feedbacktext';
                        $data['feedback']['content'] = get_string('overridden', 'grades').': ' . format_text($grade->feedback, $grade->feedbackformat);
                    } else if (empty($grade->feedback) or (!$this->canviewhidden and $grade->is_hidden())) {
                        $data['feedback']['class'] = $classfeedback.' feedbacktext';
                        $data['feedback']['content'] = '&nbsp;';
                    } else {
                        $data['feedback']['class'] = $classfeedback.' feedbacktext';
                        $data['feedback']['content'] = format_text($grade->feedback, $grade->feedbackformat);
                    }
                    $data['feedback']['headers'] = "$header_cat $header_row feedback";
                }
	        }
        } else if ($type == 'category') {
            $data['leader']['class'] = $class.' '.$alter."d$depth b1t b2b b1l";
            $data['leader']['rowspan'] = $element['rowspan'];

            if ($this->switch) { // alter style based on whether aggregation is first or last
               $data['itemname']['class'] = $class.' '.$alter."d$depth b1b b1t";
            } else {
               $data['itemname']['class'] = $class.' '.$alter."d$depth b2t";
            }
            $data['itemname']['colspan'] = ($this->maxdepth - $depth + count($this->tablecolumns) - 1);
            $data['itemname']['celltype'] = 'th';
            $data['itemname']['id'] = "cat_{$grade_object->id}_{$this->user->id}";
            // HACK to display keephigh and droplow category elements, if present
            if ($grade_object->keephigh > 0) {
	            $data['itemname']['content'] = '<span style="color: red"> (keep highest ' . $grade_object->keephigh . ' scores)</span>';
            }
            if ($grade_object->droplow > 0) {
	            $data['itemname']['content'] = '<span style="color: red"> (drop lowest ' . $grade_object->droplow . ' scores)</span>';
            } // END OF HACK
            $data['itemname']['id'] = "cat_{$grade_object->id}_{$this->user->id}";
        }
        /// Add this row to the overall system
        $this->tabledata[] = $data;

        /// Recursively iterate through all child elements
        if (isset($element['children'])) {
            foreach ($element['children'] as $key=>$child) {
                $this->fill_table_recursive($element['children'][$key]);
            }
        }
    }

    /**
     * Prints or returns the HTML from the flexitable.
     * @param bool $return Whether or not to return the data instead of printing it directly.
     * @return string
     */
    public function print_table($return=false) {
        global $CFG;
    	$maxspan = $this->maxdepth;

        /// Build table structure
        $html = "
            <table cellspacing='0'
                   cellpadding='0'
                   summary='" . s($this->get_lang_string('tablesummary', 'gradereport_laeuser')) . "'
                   class='boxaligncenter generaltable laeuser-grade'>
            <thead>
                <tr>
                    <th id='".$this->tablecolumns[0]."' class=\"header\" colspan='$maxspan'>".$this->tableheaders[0]."</th>\n";

        for ($i = 1; $i < count($this->tableheaders); $i++) {
            $html .= "<th id='".$this->tablecolumns[$i]."' class=\"header\">".$this->tableheaders[$i]."</th>\n";
        }

        $html .= "
                </tr>
            </thead>
            <tbody>\n";

        /// Print out the table data
        for ($i = 0; $i < count($this->tabledata); $i++) {
            $html .= "<tr>\n";
            if (isset($this->tabledata[$i]['leader'])) {
                $rowspan = $this->tabledata[$i]['leader']['rowspan'];
                $class = $this->tabledata[$i]['leader']['class'];
                $html .= "<td class='$class' rowspan='$rowspan'></td>\n";
            }
            for ($j = 0; $j < count($this->tablecolumns); $j++) {
                $name = $this->tablecolumns[$j];
                $class = (isset($this->tabledata[$i][$name]['class'])) ? $this->tabledata[$i][$name]['class'] : '';
                $colspan = (isset($this->tabledata[$i][$name]['colspan'])) ? "colspan='".$this->tabledata[$i][$name]['colspan']."'" : '';
                $content = (isset($this->tabledata[$i][$name]['content'])) ? $this->tabledata[$i][$name]['content'] : null;
                $celltype = (isset($this->tabledata[$i][$name]['celltype'])) ? $this->tabledata[$i][$name]['celltype'] : 'td';
                $id = (isset($this->tabledata[$i][$name]['id'])) ? "id='{$this->tabledata[$i][$name]['id']}'" : '';
                $headers = (isset($this->tabledata[$i][$name]['headers'])) ? "headers='{$this->tabledata[$i][$name]['headers']}'" : '';
                if (isset($content)) {
                    $html .= "<$celltype $id $headers class='$class' $colspan>$content</$celltype>\n";
                }
            }
            $html .= "</tr>\n";
        }

        $html .= "</tbody></table>";

        if ($return) {
            return $html;
        } else {
            echo $html;
        }
    }

    /**
     * Processes the data sent by the form (grades and feedbacks).
     * @var array $data
     * @return bool Success or Failure (array of errors).
     */
    function process_data($data) {
    }
    function process_action($target, $action) {
    }

    /**
     * Builds the grade item averages.
     *
     */
    function calculate_averages() {
        global $USER, $DB;

        if ($this->showaverage) {
            // this settings are actually grader report settings (not user report)
            // however we're using them as having two separate but identical settings the
            // user would have to keep in sync would be annoying
            $averagesdisplaytype   = $this->get_pref('averagesdisplaytype');
            $averagesdecimalpoints = $this->get_pref('averagesdecimalpoints');
            $meanselection         = $this->get_pref('meanselection');
            $shownumberofgrades    = $this->get_pref('shownumberofgrades');

            $avghtml = '';
            $avgcssclass = 'avg';

            $straverage = get_string('overallaverage', 'grades');

            $groupsql = $this->groupsql;
            $groupwheresql = $this->groupwheresql;
            //$groupwheresqlparams = ;

            if ($shownumberofgrades) {
                $straverage .= ' (' . get_string('submissions', 'grades') . ') ';
            }

            $totalcount = $this->get_numusers(false);

            //limit to users with a gradeable role ie students
            list($gradebookrolessql, $gradebookrolesparams) = $DB->get_in_or_equal(explode(',', $this->gradebookroles), SQL_PARAMS_NAMED, 'grbr0');

            //limit to users with an active enrolment
            list($enrolledsql, $enrolledparams) = get_enrolled_sql($this->context);

            $params = array_merge($this->groupwheresql_params, $gradebookrolesparams, $enrolledparams);
            $params['courseid'] = $this->courseid;

            // find sums of all grade items in course
            $sql = "SELECT gg.itemid, SUM(gg.finalgrade) AS sum
                      FROM {grade_items} gi
                      JOIN {grade_grades} gg ON gg.itemid = gi.id
                      JOIN {user} u ON u.id = gg.userid
                      JOIN ($enrolledsql) je ON je.id = gg.userid
                      JOIN (
                                   SELECT DISTINCT ra.userid
                                     FROM {role_assignments} ra
                                    WHERE ra.roleid $gradebookrolessql
                                      AND ra.contextid " . get_related_contexts_string($this->context) . "
                           ) rainner ON rainner.userid = u.id
                      $groupsql
                     WHERE gi.courseid = :courseid
                       AND u.deleted = 0
                       AND gg.finalgrade IS NOT NULL
                       AND gg.hidden = 0
                       $groupwheresql
                  GROUP BY gg.itemid";

            $sum_array = array();
            $sums = $DB->get_recordset_sql($sql, $params);
            foreach ($sums as $itemid => $csum) {
                $sum_array[$itemid] = $csum->sum;
            }
            $sums->close();

            $columncount=0;

            // Empty grades must be evaluated as grademin, NOT always 0
            // This query returns a count of ungraded grades (NULL finalgrade OR no matching record in grade_grades table)
            // No join condition when joining grade_items and user to get a grade item row for every user
            // Then left join with grade_grades and look for rows with null final grade (which includes grade items with no grade_grade)
            $sql = "SELECT gi.id, COUNT(u.id) AS count
                      FROM {grade_items} gi
                      JOIN {user} u ON u.deleted = 0
                      JOIN ($enrolledsql) je ON je.id = u.id
                      JOIN (
                               SELECT DISTINCT ra.userid
                                 FROM {role_assignments} ra
                                WHERE ra.roleid $gradebookrolessql
                                  AND ra.contextid " . get_related_contexts_string($this->context) . "
                           ) rainner ON rainner.userid = u.id
                      LEFT JOIN {grade_grades} gg
                             ON (gg.itemid = gi.id AND gg.userid = u.id AND gg.finalgrade IS NOT NULL AND gg.hidden = 0)
                      $groupsql
                     WHERE gi.courseid = :courseid
                           AND gg.finalgrade IS NULL
                           $groupwheresql
                  GROUP BY gi.id";

            $ungraded_counts = $DB->get_records_sql($sql, $params);

            foreach ($this->gtree->items as $itemid=>$unused) {
                if (!empty($this->gtree->items[$itemid]->avg)) {
                    continue;
                }
                $item = $this->gtree->items[$itemid];

                if ($item->needsupdate) {
                    $avghtml .= '<td class="cell c' . $columncount++.'"><span class="gradingerror">'.get_string('error').'</span></td>';
                    continue;
                }

                if (empty($sum_array[$item->id])) {
                    $sum_array[$item->id] = 0;
                }

                if (empty($ungraded_counts[$itemid])) {
                    $ungraded_count = 0;
                } else {
                    $ungraded_count = $ungraded_counts[$itemid]->count;
                }

                //do they want the averages to include all grade items
                if ($meanselection == GRADE_REPORT_MEAN_GRADED) {
                    $mean_count = $totalcount - $ungraded_count;
                } else { // Bump up the sum by the number of ungraded items * grademin
                    $sum_array[$item->id] += ($ungraded_count * $item->grademin);
                    $mean_count = $totalcount;
                }

                $decimalpoints = $item->get_decimals();

                // Determine which display type to use for this average
                if (!empty($USER->gradeediting) && isset($USER->gradeediting[$this->courseid]) && $USER->gradeediting[$this->courseid]) {
                    $displaytype = GRADE_DISPLAY_TYPE_REAL;
                } else if ($averagesdisplaytype == GRADE_REPORT_PREFERENCE_INHERIT) { // no ==0 here, please resave the report and user preferences
                    $displaytype = $item->get_displaytype();
                } else {
                    $displaytype = $averagesdisplaytype;
                }

                // Override grade_item setting if a display preference (not inherit) was set for the averages
                if ($averagesdecimalpoints == GRADE_REPORT_PREFERENCE_INHERIT) {
                    $decimalpoints = $item->get_decimals();

                } else {
                    $decimalpoints = $averagesdecimalpoints;
                }

                if (empty($sum_array[$item->id]) || $mean_count == 0) {
                    $this->gtree->items[$itemid]->avg = '-';
                } else {
                    $sum = $sum_array[$item->id];
                    $avgradeval = $sum/$mean_count;
                    $gradehtml = grade_format_gradevalue($avgradeval, $item, true, $displaytype, $decimalpoints);

                    $numberofgrades = '';
                    if ($shownumberofgrades) {
                        $numberofgrades = " ($mean_count)";
                    }

                    $this->gtree->items[$itemid]->avg = $gradehtml.$numberofgrades;
                }
            }
        }
    }

    /**
     * get all the grades
     * pulls out all the grades, this does not need to worry about paging
     */
    public function load_final_grades($userid) {
        global $CFG, $DB;
		$courseid = $this->course->id;

        if (!empty($this->grades)) {
            return;
        }

        // please note that we must fetch all grade_grades fields if we want to construct grade_grade object from it!
        $sql = "SELECT g.*
                  FROM {grade_items} gi,
                       {grade_grades} g
                 WHERE g.itemid = gi.id
                 AND g.userid = $userid
                 AND gi.courseid = $courseid";

        if ($grades = $DB->get_records_sql($sql)) {
            foreach ($grades as $graderec) {
                if (array_key_exists($graderec->itemid, $this->gtree->get_items())) { // some items may not be present!!
                    $this->grades[$graderec->itemid] = new grade_grade($graderec, false);
                    $this->grades[$graderec->itemid]->grade_item = $this->gtree->get_item($graderec->itemid); // db caching
                }
            }
        }

        // prefil grades that do not exist yet
        foreach ($this->gtree->get_items() as $itemid=>$unused) {
            if (!isset($this->grades[$itemid])) {
                $this->grades[$itemid] = new grade_grade();
                $this->grades[$itemid]->itemid = $itemid;
                $this->grades[$itemid]->userid = $userid;
                $this->grades[$itemid]->grade_item = $this->gtree->get_item($itemid); // db caching
            }
        }
    }
}


function grade_report_laeuser_settings_definition(&$mform) {
    global $CFG;

    $options = array(-1 => get_string('default', 'grades'),
                      0 => get_string('hide'),
                      1 => get_string('show'));

    if (empty($CFG->grade_report_laeuser_showrank)) {
        $options[-1] = get_string('defaultprev', 'grades', $options[0]);
    } else {
        $options[-1] = get_string('defaultprev', 'grades', $options[1]);
    }

    $mform->addElement('select', 'report_laeuser_showrank', get_string('showrank', 'grades'), $options);
    $mform->addHelpButton('report_laeuser_showrank', 'showrank', 'grades');

    if (empty($CFG->grade_report_laeuser_showpercentage)) {
        $options[-1] = get_string('defaultprev', 'grades', $options[0]);
    } else {
        $options[-1] = get_string('defaultprev', 'grades', $options[1]);
    }

    $mform->addElement('select', 'report_laeuser_showpercentage', get_string('showpercentage', 'grades'), $options);
    $mform->addHelpButton('report_laeuser_showpercentage', 'showpercentage', 'grades');

    if (empty($CFG->grade_report_laeuser_showgrade)) {
        $options[-1] = get_string('defaultprev', 'grades', $options[0]);
    } else {
        $options[-1] = get_string('defaultprev', 'grades', $options[1]);
    }

    $mform->addElement('select', 'report_laeuser_showgrade', get_string('showgrade', 'grades'), $options);

    if (empty($CFG->grade_report_laeuser_showfeedback)) {
        $options[-1] = get_string('defaultprev', 'grades', $options[0]);
    } else {
        $options[-1] = get_string('defaultprev', 'grades', $options[1]);
    }

    $mform->addElement('select', 'report_laeuser_showfeedback', get_string('showfeedback', 'grades'), $options);

    if (empty($CFG->grade_report_laeuser_showweight)) {
        $options[-1] = get_string('defaultprev', 'grades', $options[0]);
    } else {
        $options[-1] = get_string('defaultprev', 'grades', $options[1]);
    }

    $mform->addElement('select', 'report_laeuser_showweight', get_string('showweight', 'grades'), $options);

    if (empty($CFG->grade_report_laeuser_showaverage)) {
        $options[-1] = get_string('defaultprev', 'grades', $options[0]);
    } else {
        $options[-1] = get_string('defaultprev', 'grades', $options[1]);
    }

    $mform->addElement('select', 'report_laeuser_showaverage', get_string('showaverage', 'grades'), $options);
    $mform->addHelpButton('report_laeuser_showaverage', 'showaverage', 'grades');

    if (empty($CFG->grade_report_laeuser_showrange)) {
        $options[-1] = get_string('defaultprev', 'grades', $options[0]);
    } else {
        $options[-1] = get_string('defaultprev', 'grades', $options[1]);
    }

    $mform->addElement('select', 'report_laeuser_showrange', get_string('showrange', 'grades'), $options);

    if (empty($CFG->grade_report_laeuser_showcontrib)) {
        $options[-1] = get_string('defaultprev', 'grades', $options[0]);
    } else {
        $options[-1] = get_string('defaultprev', 'grades', $options[1]);
    }

    $options = array(-1 => get_string('default', 'grades'),
                      0 => get_string('hide'),
                      1 => get_string('show'),
                      2 => get_string('finalgradeonly', 'gradereport_laeuser'));

    if (empty($CFG->grade_report_laeuser_showlettergrade)) {
        $options[-1] = get_string('defaultprev', 'grades', $options[0]);
    } else {
        $options[-1] = get_string('defaultprev', 'grades', $options[1]);
    }

    $mform->addElement('select', 'report_laeuser_showlettergrade', get_string('showlettergrade', 'grades'), $options);

    $mform->addElement('select', 'report_laeuser_showcontrib', get_string('showcontrib', 'gradereport_laeuser'), $options);

    $options = array(0=>0, 1=>1, 2=>2, 3=>3, 4=>4, 5=>5);
    if (! empty($CFG->grade_report_laeuser_rangedecimals)) {
        $options[-1] = $options[$CFG->grade_report_laeuser_rangedecimals];
    }
    $mform->addElement('select', 'report_laeuser_rangedecimals', get_string('rangedecimals', 'grades'), $options);

    $options = array(-1 => get_string('default', 'grades'),
                      0 => get_string('shownohidden', 'grades'),
                      1 => get_string('showhiddenuntilonly', 'grades'),
                      2 => get_string('showallhidden', 'grades'));

    if (empty($CFG->grade_report_laeuser_showhiddenitems)) {
        $options[-1] = get_string('defaultprev', 'grades', $options[0]);
    } else {
        $options[-1] = get_string('defaultprev', 'grades', $options[$CFG->grade_report_laeuser_showhiddenitems]);
    }

    $mform->addElement('select', 'report_laeuser_showhiddenitems', get_string('showhiddenitems', 'grades'), $options);
    $mform->addHelpButton('report_laeuser_showhiddenitems', 'showhiddenitems', 'grades');

    //showtotalsifcontainhidden
    $options = array(-1 => get_string('default', 'grades'),
                      GRADE_REPORT_HIDE_TOTAL_IF_CONTAINS_HIDDEN => get_string('hide'),
                      GRADE_REPORT_SHOW_TOTAL_IF_CONTAINS_HIDDEN => get_string('hidetotalshowexhiddenitems', 'grades'),
                      GRADE_REPORT_SHOW_REAL_TOTAL_IF_CONTAINS_HIDDEN => get_string('hidetotalshowinchiddenitems', 'grades') );

    if (empty($CFG->grade_report_laeuser_showtotalsifcontainhidden)) {
        $options[-1] = get_string('defaultprev', 'grades', $options[0]);
    } else {
        $options[-1] = get_string('defaultprev', 'grades', $options[$CFG->grade_report_laeuser_showtotalsifcontainhidden]);
    }

    $mform->addElement('select', 'report_laeuser_showtotalsifcontainhidden', get_string('hidetotalifhiddenitems', 'grades'), $options);
    $mform->addHelpButton('report_laeuser_showtotalsifcontainhidden', 'hidetotalifhiddenitems', 'grades');
}

function grade_report_laeuser_profilereport($course, $user) {
    global $OUTPUT;
    if (!empty($course->showgrades)) {

        $context = context_course::instance($course->id);

        //first make sure we have proper final grades - this must be done before constructing of the grade tree
        grade_regrade_final_grades($course->id);

        /// return tracking object
        $gpr = new grade_plugin_return(array('type'=>'report', 'plugin'=>'laeuser', 'courseid'=>$course->id, 'userid'=>$user->id));
        // Create a report instance
        $report = new grade_report_laeuser($course->id, $gpr, $context, $user->id);

        // print the page
        echo '<div class="grade-report-laeuser">'; // css fix to share styles with real report page
        echo $OUTPUT->heading(get_string('pluginname', 'gradereport_laeuser'). ' - '.fullname($report->user));

        if ($report->fill_table()) {
            echo $report->print_table(true);
        }
        echo '</div>';
    }
}


