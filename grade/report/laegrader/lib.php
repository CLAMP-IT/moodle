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
 * File in which the grader_report class is defined.
 * @package gradebook
 */

require_once($CFG->dirroot . '/grade/report/lib.php');
require_once($CFG->libdir.'/tablelib.php');
require_once $CFG->dirroot.'/grade/report/laegrader/locallib.php';
require_once $CFG->dirroot.'/grade/report/grader/lib.php';

/**
 * Class providing an API for the grader report building and displaying.
 * @uses grade_report
 * @package gradebook
 * DONE
 */
class grade_report_laegrader extends grade_report {
    /**
     * The final grades.
     * @var array $grades
     */
    public $grades;

    /**
     * Array of errors for bulk grades updating.
     * @var array $gradeserror
     */
    public $gradeserror = array();

//// SQL-RELATED

    /**
     * The id of the grade_item by which this report will be sorted.
     * @var int $sortitemid
     */
    public $sortitemid;

    /**
     * Sortorder used in the SQL selections.
     * @var int $sortorder
     */
    public $sortorder;

    /**
     * An SQL fragment affecting the search for users.
     * @var string $userselect
     */
    public $userselect;

    /**
     * The bound params for $userselect
     * @var array $userselectparams
     */
    public $userselectparams = array();

    /**
     * List of collapsed categories from user preference
     * @var array $collapsed
     */
    public $collapsed;

    /**
     * A count of the rows, used for css classes.
     * @var int $rowcount
     */
    public $rowcount = 0;

    /**
     * Capability check caching
     * */
    public $canviewhidden;

    var $preferences_page=false;

    /**
     * Length at which feedback will be truncated (to the nearest word) and an ellipsis be added.
     * TODO replace this by a report preference
     * @var int $feedback_trunc_length
     */
    protected $feedback_trunc_length = 50;

    /**
     * Constructor. Sets local copies of user preferences and initialises grade_tree.
     * @param int $courseid
     * @param object $gpr grade plugin return tracking object
     * @param string $context
     * @param int $page The current page being viewed (when report is paged)
     * @param int $sortitemid The id of the grade_item by which to sort the table
     * DONE
     */
    function __construct($courseid, $gpr, $context, $page=null, $sortitemid=null) {
        global $CFG;
        parent::__construct($courseid, $gpr, $context, $page);

        $this->canviewhidden = has_capability('moodle/grade:viewhidden', get_context_instance(CONTEXT_COURSE, $this->course->id));
        $this->accuratetotals		= ($temp = grade_get_setting($this->courseid, 'report_laegrader_accuratetotals', $CFG->grade_report_laegrader_accuratetotals)) ? $temp : 0;

        // need this array, even tho its useless in the laegrader report or we'll generate warnings
        $this->collapsed = array('aggregatesonly' => array(), 'gradesonly' => array());

        if (empty($CFG->enableoutcomes)) {
            $nooutcomes = false;
        } else {
            $nooutcomes = get_user_preferences('grade_report_shownooutcomes');
        }

        // force category_last to true for laegrader report
        $switch = true;

        // Grab the grade_tree for this course
        $this->gtree = new grade_tree_local($this->courseid, false, true, null, $nooutcomes);

        // Fill items with parent information needed later for laegrader report
        $this->gtree->parents = array();
		$this->gtree->cats = array();
        fill_cats($this->gtree);
        fill_parents($this->gtree->parents, $this->gtree->items, $this->gtree->cats, $this->gtree->top_element, $this->gtree->top_element['object']->grade_item->id, $this->accuratetotals);

        $this->sortitemid = $sortitemid;

        // base url for sorting by first/last name
        $studentsperpage = 0; //forced for laegrader report
        $perpage = '';
        $curpage = '';

        $this->baseurl = new moodle_url('index.php', array('id' => $this->courseid));

        $this->pbarurl = new moodle_url('/grade/report/grader/index.php', array('id' => $this->courseid, 'perpage' => $studentsperpage));

        $this->setup_groups();

        $this->setup_sortitemid();
    }

    /**
     * Processes the data sent by the form (grades and feedbacks).
     * Caller is reposible for all access control checks
     * @param array $data form submission (with magic quotes)
     * @return array empty array if success, array of warnings if something fails.
     * DONE
     */
    public function process_data($data) {
        global $DB;
    	$warnings = array();

        $separategroups = false;
        $mygroups       = array();
        if ($this->groupmode == SEPARATEGROUPS and !has_capability('moodle/site:accessallgroups', $this->context)) {
            $separategroups = true;
            $mygroups = groups_get_user_groups($this->course->id);
            $mygroups = $mygroups[0]; // ignore groupings
            // reorder the groups fro better perf bellow
            $current = array_search($this->currentgroup, $mygroups);
            if ($current !== false) {
                unset($mygroups[$current]);
                array_unshift($mygroups, $this->currentgroup);
            }
        }

        // always initialize all arrays
        $queue = array();
        $this->load_users();
        $this->load_final_grades();
        
        foreach ($data as $varname => $postedvalue) {

            // skip, not a grade nor feedback
            if (strpos($varname, 'grade') === 0) {
                $data_type = 'grade';
            } else if (strpos($varname, 'feedback') === 0) {
                $data_type = 'feedback';
            } else {
                continue;
            }
        	
            $gradeinfo = explode("_", $varname);
            $userid = clean_param($gradeinfo[1], PARAM_INT);
            $itemid = clean_param($gradeinfo[2], PARAM_INT);

            // Was change requested?
            $oldvalue = $data->{'old'.$varname};
                        
            /// BP: moved this up to speed up the process of eliminating unchanged values
            if ($oldvalue == $postedvalue) { // string comparison
                continue;
            }
            $needsupdate = false;


            $gradeinfo = explode("_", $varname);
            $userid = clean_param($gradeinfo[1], PARAM_INT);
            $itemid = clean_param($gradeinfo[2], PARAM_INT);

			// HACK: bob puffer call local object
            if (!$gradeitem = grade_item::fetch(array('id'=>$itemid, 'courseid'=>$this->courseid))) { // we must verify course id here!
//            if (!$grade_item = grade_item_local::fetch(array('id'=>$itemid, 'courseid'=>$this->courseid))) { // we must verify course id here!
            	// END OF HACK
                print_error('invalidgradeitmeid');
            }

            // Pre-process grade
            if ($data_type == 'grade') {
                $feedback = false;
                $feedbackformat = false;
                if ($gradeitem->gradetype == GRADE_TYPE_SCALE) {
                    if ($postedvalue == -1) { // -1 means no grade
                        $finalgrade = null;
                    } else {
                        $finalgrade = $postedvalue;
                    }
                } else {
		    		// HACK: bob puffer to allow calculating grades from input letters
                    $context = get_context_instance(CONTEXT_COURSE, $gradeitem->courseid);

                    // percentage input
                    if (strpos($postedvalue, '%')) {
                        $percent = trim(substr($postedvalue, 0, strpos($postedvalue, '%')));
                        $postedvalue = $percent * .01 * $gradeitem->grademax;
                    // letter input?
                    } elseif ($letters = grade_get_letters($this->context)) {
						unset($lastitem);
                        foreach ($letters as $used=>$letter) {
                            if (strtoupper($postedvalue) == strtoupper($letter)) {
                                if (isset($lastitem)) {
                                    $postedvalue = $lastitem;
                                } else {
                                    $postedvalue = $gradeitem->grademax;
                                }
                                break;
                            } else {
                                    $lastitem = ($used - 1) * .01 * $gradeitem->grademax;
                            }
                        }
                    } // END OF HACK
                    $finalgrade = unformat_float($postedvalue);
                }

                $errorstr = '';
                // Warn if the grade is out of bounds.
                if (is_null($finalgrade)) {
                    // ok
                } else {
                    $bounded = $gradeitem->bounded_grade($finalgrade);
                    if ($bounded > $finalgrade) {
                        $errorstr = 'lessthanmin';
                    } else if ($bounded < $finalgrade) {
                        $errorstr = 'morethanmax';
                    }
                }
                if ($errorstr) {
                    $user = $DB->get_record('user', array('id' => $userid), 'id, firstname, lastname');
//                    $user = get_record('user', 'id', $userid, '', '', '', '', 'id, firstname, lastname');
                    $gradestr = new stdClass();
                    $gradestr->username = fullname($user);
                    $gradestr->itemname = $gradeitem->get_name();
                    $warnings[] = get_string($errorstr, 'grades', $gradestr);
                }

            } else if ($data_type == 'feedback') {
                $finalgrade = false;
                $trimmed = trim($postedvalue);
                if (empty($trimmed)) {
                     $feedback = NULL;
                } else {
                     $feedback = $postedvalue;
                }
            }

            // group access control
            if ($separategroups) {
                // note: we can not use $this->currentgroup because it would fail badly
                //       when having two browser windows each with different group
                $sharinggroup = false;
                foreach($mygroups as $groupid) {
                    if (groups_is_member($groupid, $userid)) {
                        $sharinggroup = true;
                        break;
                    }
                }
                if (!$sharinggroup) {
                    // either group membership changed or somebedy is hacking grades of other group
                    $warnings[] = get_string('errorsavegrade', 'grades');
                    continue;
                }
            }

            $gradeitem->update_final_grade($userid, $finalgrade, 'gradebook', $feedback, FORMAT_MOODLE);
        }

        return $warnings;
    }


    /**
     * Setting the sort order, this depends on last state
     * all this should be in the new table class that we might need to use
     * for displaying grades.
     */
    private function setup_sortitemid() {

        global $SESSION;

        if ($this->sortitemid) {
            if (!isset($SESSION->gradeuserreport->sort)) {
                if ($this->sortitemid == 'firstname' || $this->sortitemid == 'lastname') {
                    $this->sortorder = $SESSION->gradeuserreport->sort = 'ASC';
                } else {
                    $this->sortorder = $SESSION->gradeuserreport->sort = 'DESC';
                }
            } else {
                // this is the first sort, i.e. by last name
                if (!isset($SESSION->gradeuserreport->sortitemid)) {
                    if ($this->sortitemid == 'firstname' || $this->sortitemid == 'lastname') {
                        $this->sortorder = $SESSION->gradeuserreport->sort = 'ASC';
                    } else {
                        $this->sortorder = $SESSION->gradeuserreport->sort = 'DESC';
                    }
                } else if ($SESSION->gradeuserreport->sortitemid == $this->sortitemid) {
                    // same as last sort
                    if ($SESSION->gradeuserreport->sort == 'ASC') {
                        $this->sortorder = $SESSION->gradeuserreport->sort = 'DESC';
                    } else {
                        $this->sortorder = $SESSION->gradeuserreport->sort = 'ASC';
                    }
                } else {
                    if ($this->sortitemid == 'firstname' || $this->sortitemid == 'lastname') {
                        $this->sortorder = $SESSION->gradeuserreport->sort = 'ASC';
                    } else {
                        $this->sortorder = $SESSION->gradeuserreport->sort = 'DESC';
                    }
                }
            }
            $SESSION->gradeuserreport->sortitemid = $this->sortitemid;
        } else {
            // not requesting sort, use last setting (for paging)

            if (isset($SESSION->gradeuserreport->sortitemid)) {
                $this->sortitemid = $SESSION->gradeuserreport->sortitemid;
            }else{
                $this->sortitemid = 'lastname';
            }

            if (isset($SESSION->gradeuserreport->sort)) {
                $this->sortorder = $SESSION->gradeuserreport->sort;
            } else {
                $this->sortorder = 'ASC';
            }
        }
    }

    /**
     * pulls out the userids of the users to be display, and sorts them
     * DONE
     */
    public function load_users() {
        global $CFG, $DB;

        //limit to users with a gradeable role
        list($gradebookrolessql, $gradebookrolesparams) = $DB->get_in_or_equal(explode(',', $this->gradebookroles), SQL_PARAMS_NAMED, 'grbr0');

        //limit to users with an active enrollment
        list($enrolledsql, $enrolledparams) = get_enrolled_sql($this->context);

        //fields we need from the user table
        $userfields = user_picture::fields('u');
        $userfields .= get_extra_user_fields_sql($this->context);

        $sortjoin = $sort = $params = null;

        //if the user has clicked one of the sort asc/desc arrows
        if (is_numeric($this->sortitemid)) {
            $params = array_merge(array('gitemid'=>$this->sortitemid), $gradebookrolesparams, $this->groupwheresql_params, $enrolledparams);

            $sortjoin = "LEFT JOIN {grade_grades} g ON g.userid = u.id AND g.itemid = $this->sortitemid";
            $sort = "g.finalgrade $this->sortorder";

        } else {
            $sortjoin = '';
            switch($this->sortitemid) {
                case 'lastname':
                    $sort = "u.lastname $this->sortorder, u.firstname $this->sortorder";
                    break;
                case 'firstname':
                    $sort = "u.firstname $this->sortorder, u.lastname $this->sortorder";
                    break;
                case 'idnumber':
                default:
                    $sort = "u.idnumber $this->sortorder";
                    break;
            }

            $params = array_merge($gradebookrolesparams, $this->groupwheresql_params, $enrolledparams);
        }

        $sql = "SELECT $userfields
                  FROM {user} u
                  JOIN ($enrolledsql) je ON je.id = u.id
                       $this->groupsql
                       $sortjoin
                  JOIN (
                           SELECT DISTINCT ra.userid
                             FROM {role_assignments} ra
                            WHERE ra.roleid IN ($this->gradebookroles)
                              AND ra.contextid " . get_related_contexts_string($this->context) . "
                       ) rainner ON rainner.userid = u.id
                   AND u.deleted = 0
                   $this->groupwheresql
              ORDER BY $sort";

        $this->users = $DB->get_records_sql($sql, $params, $this->get_pref('studentsperpage') * $this->page, $this->get_pref('studentsperpage'));

        if (empty($this->users)) {
            $this->userselect = '';
            $this->users = array();
            $this->userselect_params = array();
        } else {
            list($usql, $uparams) = $DB->get_in_or_equal(array_keys($this->users), SQL_PARAMS_NAMED, 'usid0');
            $this->userselect = "AND g.userid $usql";
            $this->userselect_params = $uparams;

            //add a flag to each user indicating whether their enrolment is active
            $sql = "SELECT ue.userid
                      FROM {user_enrolments} ue
                      JOIN {enrol} e ON e.id = ue.enrolid
                     WHERE ue.userid $usql
                           AND ue.status = :uestatus
                           AND e.status = :estatus
                           AND e.courseid = :courseid
                  GROUP BY ue.userid";
            $coursecontext = get_course_context($this->context);
            $params = array_merge($uparams, array('estatus'=>ENROL_INSTANCE_ENABLED, 'uestatus'=>ENROL_USER_ACTIVE, 'courseid'=>$coursecontext->instanceid));
            $useractiveenrolments = $DB->get_records_sql($sql, $params);

            foreach ($this->users as $user) {
                $this->users[$user->id]->suspendedenrolment = !array_key_exists($user->id, $useractiveenrolments);
            }
        }

        return $this->users;
    }

    /**
     * we supply the userids in this query, and get all the grades
     * pulls out all the grades, this does not need to worry about paging
     */
    public function load_final_grades() {
        global $CFG, $DB;

        // please note that we must fetch all grade_grades fields if we want to construct grade_grade object from it!
        $params = array_merge(array('courseid'=>$this->courseid), $this->userselect_params);
        $sql = "SELECT g.*
                  FROM {grade_items} gi,
                       {grade_grades} g
                 WHERE g.itemid = gi.id AND gi.courseid = :courseid {$this->userselect}";

        $userids = array_keys($this->users);


        if ($grades = $DB->get_records_sql($sql, $params)) {
            foreach ($grades as $graderec) {
                if (in_array($graderec->userid, $userids) and array_key_exists($graderec->itemid, $this->gtree->get_items())) { // some items may not be present!!
                    $this->grades[$graderec->userid][$graderec->itemid] = new grade_grade($graderec, false);
                    $this->grades[$graderec->userid][$graderec->itemid]->grade_item =& $this->gtree->get_item($graderec->itemid); // db caching
                }
            }
        }

        // prefil grades that do not exist yet
        foreach ($userids as $userid) {
            foreach ($this->gtree->get_items() as $itemid=>$unused) {
                if (!isset($this->grades[$userid][$itemid])) {
                    $this->grades[$userid][$itemid] = new grade_grade();
                    $this->grades[$userid][$itemid]->itemid = $itemid;
                    $this->grades[$userid][$itemid]->userid = $userid;
                    $this->grades[$userid][$itemid]->grade_item =& $this->gtree->get_item($itemid); // db caching
                }
            }
        }
    }

    /**
     * Builds and returns a div with on/off toggles.
     * @return string HTML code
     */
    public function get_toggles_html() {
        global $CFG, $USER, $COURSE, $OUTPUT;

        $html = '';
        if ($USER->gradeediting[$this->courseid]) {
            if (has_capability('moodle/grade:manage', $this->context) or has_capability('moodle/grade:hide', $this->context)) {
                $html .= $this->print_toggle('eyecons');
            }
            if (has_capability('moodle/grade:manage', $this->context)
             or has_capability('moodle/grade:lock', $this->context)
             or has_capability('moodle/grade:unlock', $this->context)) {
                $html .= $this->print_toggle('locks');
            }
            if (has_capability('moodle/grade:manage', $this->context)) {
                $html .= $this->print_toggle('quickfeedback');
            }

            if (has_capability('moodle/grade:manage', $this->context)) {
                $html .= $this->print_toggle('calculations');
            }
        }

        if ($this->canviewhidden) {
            $html .= $this->print_toggle('averages');
        }

        $html .= $this->print_toggle('ranges');
        if (!empty($CFG->enableoutcomes)) {
            $html .= $this->print_toggle('nooutcomes');
        }

        return $OUTPUT->container($html, 'grade-report-toggles');
    }

    /**
    * Shortcut function for printing the grader report toggles.
    * @param string $type The type of toggle
    * @param bool $return Whether to return the HTML string rather than printing it
    * @return void
    */
    public function print_toggle($type) {
        global $CFG, $OUTPUT;

        $icons = array('eyecons' => 't/hide',
                       'calculations' => 't/calc',
                       'locks' => 't/lock',
                       'averages' => 't/mean',
                       'quickfeedback' => 't/feedback',
                       'nooutcomes' => 't/outcomes');

        $prefname = 'grade_report_show' . $type;

        if (array_key_exists($prefname, $CFG)) {
            $showpref = get_user_preferences($prefname, $CFG->$prefname);
        } else {
            $showpref = get_user_preferences($prefname);
        }

        $strshow = $this->get_lang_string('show' . $type, 'grades');
        $strhide = $this->get_lang_string('hide' . $type, 'grades');

        $showhide = 'show';
        $toggleaction = 1;

        if ($showpref) {
            $showhide = 'hide';
            $toggleaction = 0;
        }

        if (array_key_exists($type, $icons)) {
            $imagename = $icons[$type];
        } else {
            $imagename = "t/$type";
        }

        $string = ${'str' . $showhide};

        $url = new moodle_url($this->baseurl, array('toggle' => $toggleaction, 'toggle_type' => $type));

        $retval = $OUTPUT->container($OUTPUT->action_icon($url, new pix_icon($imagename, $string))); // TODO: this container looks wrong here

        return $retval;
    }

    /**
     * Builds and returns the rows that will make up the left part of the grader report
     * This consists of student names and icons, links to user reports and id numbers, as well
     * as header cells for these columns. It also includes the fillers required for the
     * categories displayed on the right side of the report.
     * @return array Array of html_table_row objects
     */
    public function get_left_rows() {
        global $CFG, $USER, $OUTPUT;

        $rows = array();

        $showuserimage = $this->get_pref('showuserimage');
        $fixedstudents = 0; // always for LAE

        $strfeedback  = $this->get_lang_string("feedback");
        $strgrade     = $this->get_lang_string('grade');

        $extrafields = get_extra_user_fields($this->context);

        $arrows = $this->get_sort_arrows($extrafields);

        $colspan = 1;
        if (has_capability('gradereport/'.$CFG->grade_profilereport.':view', $this->context)) {
            $colspan++;
        }
        $colspan += count($extrafields);

        $levels = count($this->gtree->levels) - 1;

        $headerrow = new html_table_row();
        $headerrow->attributes['class'] = 'heading';

        $studentheader = new html_table_cell();
        $studentheader->attributes['class'] = 'header';
        $studentheader->scope = 'col';
        $studentheader->header = true;
        $studentheader->id = 'studentheader';

        // LAE here's where we insert the "Copy to Excel" button
//        $output = '<div class="inlinebutton" title="Download contents of gradebook as-is to Excel. SAVE CHANGES FIRST!">';
//        $output .= '<a href="' . $CFG->wwwroot . '/grade/report/laegrader/index.php?id=' . $this->courseid
//                . '&action=quick-dump" class="inlinebutton"><img src="' . $CFG->wwwroot . '/grade/report/laegrader/images/copytoexcel.png" /></a></div>';

//        $studentheader->text = $output . $arrows['studentname'];
        $studentheader->text = $arrows['studentname'];
                
        $headerrow->cells[] = $studentheader;

        if (has_capability('gradereport/'.$CFG->grade_profilereport.':view', $this->context)) {
	        $userheader = new html_table_cell();
	        $userheader->attributes['class'] = 'header';
	        $userheader->scope = 'col';
	        $userheader->header = true;
	        $userheader->id = 'userreport';
        	$headerrow->cells[] = $userheader;
        }
        foreach ($extrafields as $field) {
            $fieldheader = new html_table_cell();
            $fieldheader->attributes['class'] = 'header userfield user' . $field;
            $fieldheader->scope = 'col';
            $fieldheader->header = true;
            $fieldheader->text = $arrows[$field];

            $headerrow->cells[] = $fieldheader;
        }

        $rows[] = $headerrow;

        $rows = $this->get_left_icons_row($rows, $colspan);
        $rows = $this->get_left_range_row($rows, $colspan);
        $rows = $this->get_left_avg_row($rows, $colspan, true);
        $rows = $this->get_left_avg_row($rows, $colspan);
        
        $rowclasses = array('even', 'odd');

        $suspendedstring = null;
        foreach ($this->users as $userid => $user) {
            $userrow = new html_table_row();
            $userrow->id = 'fixed_user_'.$userid;
            $userrow->attributes['class'] = 'r'.$this->rowcount++.' '.$rowclasses[$this->rowcount % 2];

            $usercell = new html_table_cell();
            $usercell->attributes['class'] = 'user';

            $usercell->header = true;
            $usercell->scope = 'row';

            if ($showuserimage) {
                $usercell->text = $OUTPUT->user_picture($user);
            }

            $usercell->text .= html_writer::link(new moodle_url('/user/view.php', array('id' => $user->id, 'course' => $this->course->id)), fullname($user));

            if (!empty($user->suspendedenrolment)) {
                $usercell->attributes['class'] .= ' usersuspended';

                //may be lots of suspended users so only get the string once
                if (empty($suspendedstring)) {
                    $suspendedstring = get_string('userenrolmentsuspended', 'grades');
                }
                $usercell->text .= html_writer::empty_tag('img', array('src'=>$OUTPUT->pix_url('i/enrolmentsuspended'), 'title'=>$suspendedstring, 'alt'=>$suspendedstring, 'class'=>'usersuspendedicon'));
            }

            $userrow->cells[] = $usercell;

            if (has_capability('gradereport/'.$CFG->grade_profilereport.':view', $this->context)) {
                $userreportcell = new html_table_cell();
                $userreportcell->attributes['class'] = 'userreport ' . $rowclasses[$this->rowcount % 2];
//                $userreportcell->header = true;
                $a = new stdClass();
                $a->user = fullname($user);
                $strgradesforuser = get_string('gradesforuser', 'grades', $a);
                $url = new moodle_url('/grade/report/'.$CFG->grade_profilereport.'/index.php', array('userid' => $user->id, 'id' => $this->course->id));
                $userreportcell->text = $OUTPUT->action_icon($url, new pix_icon('t/grades', $strgradesforuser, null, array('class'=>' usergradesicon')));
                $userrow->cells[] = $userreportcell;
            }

            foreach ($extrafields as $field) {
                $fieldcell = new html_table_cell();
                $fieldcell->attributes['class'] = 'header userfield user' . $field;
                $fieldcell->header = true;
                $fieldcell->scope = 'row';
                $fieldcell->text = $user->{$field};
                $userrow->cells[] = $fieldcell;
            }

            $rows[] = $userrow;
        }


        return $rows;
    }

    /**
     * Builds and returns the rows that will make up the right part of the grader report
     * @return array Array of html_table_row objects
     */
    public function get_right_rows() {
        global $CFG, $USER, $OUTPUT, $DB, $PAGE;

        $rows = array();
        $this->rowcount = 0;
        $numrows = count($this->gtree->get_levels());
        $numusers = count($this->users);
        $gradetabindex = 1;
        $columnstounset = array();
        $strgrade = $this->get_lang_string('grade');
        $strfeedback  = $this->get_lang_string("feedback");
        $arrows = $this->get_sort_arrows();

        $jsarguments = array(
            'id'        => '#fixed_column',
            'cfg'       => array('ajaxenabled'=>false),
            'items'     => array(),
            'users'     => array(),
            'feedback'  => array()
        );
        $jsscales = array();

        $headingrow = new html_table_row();
        $headingrow->attributes['class'] = 'heading_name_row';
        
        // these vars used to color backgrounds of items belonging to particular categories since our header display is flat
        $catcolors = array(' catblue ', ' catorange ');
        $catcolorindex = 0;
		$currentcat = 0;
        foreach ($this->gtree->levelitems as $key=>$element) {
        	$coursecat = substr($this->gtree->top_element['eid'],1,9);
			if ($element['object']->categoryid === $coursecat || $element['type'] == 'courseitem') {
				$currentcatcolor = ''; // individual items belonging to the course category and course total are white background
			} elseif ($element['type'] !== 'categoryitem' && $element['object']->categoryid !== $currentcat) { // alternate background colors for 
				$currentcat = $element['object']->categoryid;
				$catcolorindex++;
				$currentcatcolor = $catcolors[$catcolorindex % 2];
			}
        	$sortlink = clone($this->baseurl);
            $sortlink->param('sortitemid', $key); // itemid
            $eid    = $element['eid'];
            $object = $element['object'];
            $type   = $element['type'];
            $categorystate = @$element['categorystate'];
            $colspan = 1;
            $catlevel = '';
			if ($element['object']->id == $this->sortitemid) {
				if ($this->sortorder == 'ASC') {
					$arrow = $this->get_sort_arrow('up', $sortlink);
				} else {
					$arrow = $this->get_sort_arrow('down', $sortlink);
				}
			} else {
				$arrow = $this->get_sort_arrow('move', $sortlink);
			}

			// LAE this line calls a local instance of get_element_header with the name of the grade item or category
			$headerlink = $this->gtree->get_element_header($element, true, $this->get_pref('showactivityicons'), false, 25,$this->gtree->items[$key]->itemname);

			$itemcell = new html_table_cell();
			$itemcell->attributes['class'] = $type . ' ' . $catlevel . 'highlightable' . $currentcatcolor;

			if ($element['object']->is_hidden()) {
				$itemcell->attributes['class'] .= ' hidden';
			}

			$itemcell->colspan = 1; // $colspan;
			$itemcell->text = $headerlink . $arrow;
			$itemcell->header = true;
			$itemcell->scope = 'col';

			$headingrow->cells[] = $itemcell;
        }
        $rows[] = $headingrow;
        
        $rows = $this->get_right_icons_row($rows);
        $rows = $this->get_right_range_row($rows);
        $rows = $this->get_right_avg_row($rows, true);
        $rows = $this->get_right_avg_row($rows);
        
        // Preload scale objects for items with a scaleid and initialize tab indices
        $scaleslist = array();
        $tabindices = array();

        foreach ($this->gtree->get_items() as $itemid=>$item) {
            $scale = null;
            if (!empty($item->scaleid)) {
                $scaleslist[] = $item->scaleid;
                $jsarguments['items'][$itemid] = array('id'=>$itemid, 'name'=>$item->get_name(true), 'type'=>'scale', 'scale'=>$item->scaleid, 'decimals'=>$item->get_decimals());
            } else {
                $jsarguments['items'][$itemid] = array('id'=>$itemid, 'name'=>$item->get_name(true), 'type'=>'value', 'scale'=>false, 'decimals'=>$item->get_decimals());
            }
            $tabindices[$item->id]['grade'] = $gradetabindex;
            $tabindices[$item->id]['feedback'] = $gradetabindex + $numusers;
            $gradetabindex += $numusers * 2;
//			$gradetabindex++;
        }
        $scalesarray = array();

        if (!empty($scaleslist)) {
            $scalesarray = $DB->get_records_list('scale', 'id', $scaleslist);
        }
        $jsscales = $scalesarray;

        $rowclasses = array('even', 'odd');

        foreach ($this->users as $userid => $user) {

            if ($this->canviewhidden) {
                $altered = array();
                $unknown = array();
            } else {
                $hidingaffected = grade_grade::get_hiding_affected($this->grades[$userid], $this->gtree->get_items());
                $altered = $hidingaffected['altered'];
                $unknown = $hidingaffected['unknown'];
                unset($hidingaffected);
            }


            $itemrow = new html_table_row();
            $itemrow->id = 'user_'.$userid;
            $itemrow->attributes['class'] = $rowclasses[$this->rowcount % 2];

            $jsarguments['users'][$userid] = fullname($user);

            foreach ($this->gtree->items as $itemid=>$unused) {
                $item =& $this->gtree->items[$itemid];
                $grade = $this->grades[$userid][$item->id];

                $itemcell = new html_table_cell();

                $itemcell->id = 'u'.$userid.'i'.$itemid;

                // Get the decimal points preference for this item
                $decimalpoints = $item->get_decimals();

                if (in_array($itemid, $unknown)) {
                    $gradeval = null;
                } else if (array_key_exists($itemid, $altered)) {
                    $gradeval = $altered[$itemid];
                } else {
                    $gradeval = $grade->finalgrade;
                }

                // MDL-11274
                // Hide grades in the grader report if the current grader doesn't have 'moodle/grade:viewhidden'
                if (!$this->canviewhidden and $grade->is_hidden()) {
                    if (!empty($CFG->grade_hiddenasdate) and $grade->get_datesubmitted() and !$item->is_category_item() and !$item->is_course_item()) {
                        // the problem here is that we do not have the time when grade value was modified, 'timemodified' is general modification date for grade_grades records
                        $itemcell->text = html_writer::tag('span', userdate($grade->get_datesubmitted(),get_string('strftimedatetimeshort')), array('class'=>'datesubmitted'));
                    } else {
                        $itemcell->text = '-';
                    }
                    $itemrow->cells[] = $itemcell;
                    continue;
                }

                // emulate grade element
                $eid = $this->gtree->get_grade_eid($grade);
                $element = array('eid'=>$eid, 'object'=>$grade, 'type'=>'grade');

                $itemcell->attributes['class'] .= ' grade';
                if ($item->is_category_item()) {
                    $itemcell->attributes['class'] .= ' cat';
                }
                if ($item->is_course_item()) {
                    $itemcell->attributes['class'] .= ' course';
                }
                if ($grade->is_overridden()) {
                    $itemcell->attributes['class'] .= ' overridden';
                }

                if ($grade->is_excluded()) {
                    // $itemcell->attributes['class'] .= ' excluded';
                }

                if (!empty($grade->feedback)) {
                    //should we be truncating feedback? ie $short_feedback = shorten_text($feedback, $this->feedback_trunc_length);
                    $jsarguments['feedback'][] = array('user'=>$userid, 'item'=>$itemid, 'content'=>wordwrap(trim(format_string($grade->feedback, $grade->feedbackformat)), 34, '<br/ >'));
                }

                if ($grade->is_excluded()) {
                    $itemcell->text .= html_writer::tag('span', get_string('excluded', 'grades'), array('class'=>'excludedfloater'));
                }

                // Do not show any icons if no grade (no record in DB to match)
                if (!$item->needsupdate and $USER->gradeediting[$this->courseid]) {
                    $itemcell->text .= $this->get_icons($element);
                }

                $hidden = '';
                if ($grade->is_hidden()) {
                    $hidden = ' hidden ';
                }
                $itemcell->attributes['class'] .= $hidden;
                
                $gradepass = ' gradefail ';
                if ($grade->is_passed($item)) {
                    $gradepass = ' gradepass ';
                } elseif (is_null($grade->is_passed($item))) {
                    $gradepass = '';
                }

                // if in editing mode, we need to print either a text box
                // or a drop down (for scales)
                // grades in item of type grade category or course are not directly editable
                if ($item->needsupdate) {
                    $itemcell->text .= html_writer::tag('span', get_string('error'), array('class'=>"gradingerror$hidden"));

                } else if ($USER->gradeediting[$this->courseid]) {

                    if ($item->scaleid && !empty($scalesarray[$item->scaleid])) {
                        $scale = $scalesarray[$item->scaleid];
                        $gradeval = (int)$gradeval; // scales use only integers
                        $scales = explode(",", $scale->scale);
                        // reindex because scale is off 1

                        // MDL-12104 some previous scales might have taken up part of the array
                        // so this needs to be reset
                        $scaleopt = array();
                        $i = 0;
                        foreach ($scales as $scaleoption) {
                            $i++;
                            $scaleopt[$i] = $scaleoption;
                        }

                        if ($this->get_pref('quickgrading') and $grade->is_editable()) {
                            $oldval = empty($gradeval) ? -1 : $gradeval;
                            if (empty($item->outcomeid)) {
                                $nogradestr = $this->get_lang_string('nograde');
                            } else {
                                $nogradestr = $this->get_lang_string('nooutcome', 'grades');
                            }
                            $itemcell->text .= '<input type="hidden" id="oldgrade_'.$userid.'_'.$item->id.'" name="oldgrade_'.$userid.'_'.$item->id.'" value="'.$oldval.'"/>';
                            $attributes = array('tabindex' => $tabindices[$item->id]['grade'], 'id'=>'grade_'.$userid.'_'.$item->id);
                            $itemcell->text .= html_writer::select($scaleopt, 'grade_'.$userid.'_'.$item->id, $gradeval, array(-1=>$nogradestr), $attributes);;
                        } elseif(!empty($scale)) {
                            $scales = explode(",", $scale->scale);

                            // invalid grade if gradeval < 1
                            if ($gradeval < 1) {
                                $itemcell->text .= html_writer::tag('span', '-', array('class'=>"gradevalue$hidden$gradepass"));
                            } else {
                                $gradeval = $grade->grade_item->bounded_grade($gradeval); //just in case somebody changes scale
                                $itemcell->text .= html_writer::tag('span', $scales[$gradeval-1], array('class'=>"gradevalue$hidden$gradepass"));
                            }
                        } else {
                            // no such scale, throw error?
                        }

                    } else if ($item->gradetype != GRADE_TYPE_TEXT) { // Value type
                    	// We always want to display the correct (first) displaytype when editing
                    	// regardless of grade_report_gradeeditalways
                    	$gradedisplaytype = (integer) substr( (string) $item->get_displaytype(),0,1);
                    	 
                    	// if we have an accumulated total points that's not accurately reflected in the db, then we want to display the ACCURATE number
                        // we only need to take the extra calculation into account if points display since percent and letter are accurate by their nature
                        // If the settings don't call for ACCURATE point totals ($this->accuratetotals) then there will be no earned_total value
	                    $tempmax = $item->grademax;
                        if ($gradedisplaytype == GRADE_DISPLAY_TYPE_REAL && isset($this->grades[$userid][$grade->itemid]->cat_item)) {
                        	$items = $this->gtree->items;
		               		$grade_values = $this->grades[$userid][$grade->itemid]->cat_item;
		               		$grade_maxes = $this->grades[$userid][$grade->itemid]->cat_max;
		               		$this_cat = $this->gtree->items[$grade->itemid]->get_item_category();
		               		$this->gtree->limit_item($this_cat,$items,$grade_values,$grade_maxes);
			       			$gradeval = array_sum($grade_values);
			       			$item->grademax = array_sum($grade_maxes);
                        }
                      	$value = grade_format_gradevalue($gradeval, $item, true, $gradedisplaytype, null);
						$item->grademax = $tempmax;
                      	if (! $grade->is_hidden() && $gradeval <> null && $this->accuratetotals) {
                			$this->grades[$userid][$this->gtree->parents[$grade->itemid]->id]->cat_item[$grade->itemid] = $gradeval;
							$this->grades[$userid][$this->gtree->parents[$grade->itemid]->id]->cat_max[$grade->itemid] = $grade->rawgrademax;
				   		}
                    	if ($this->get_pref('quickgrading') and $grade->is_editable()) {
                            if (! $this->accuratetotals || (! $item->is_course_item() and ! $item->is_category_item())) {
	                    		$value = format_float($gradeval, $decimalpoints);
	                            $itemcell->text .= '<input type="hidden" id="oldgrade_'.$userid.'_'.$item->id.'" name="oldgrade_'.$userid.'_'.$item->id.'" value="'.$value.'" />';
	                            $itemcell->text .= '<input size="6" tabindex="' . $tabindices[$item->id]['grade']
	                                          . '" type="text" class="text" title="'. $strgrade .'" name="grade_'
	                                          .$userid.'_' .$item->id.'" id="grade_'.$userid.'_'.$item->id.'" value="'.$value.'" rel="' . $item->id . '" />';
                            } else {
                            	$itemcell->text .= html_writer::tag('span', format_float($gradeval, $decimalpoints), array('class'=>"gradevalue$hidden$gradepass"));
                            }
                        }
                    }


                    // If quickfeedback is on, print an input element
                    if ($this->get_pref('showquickfeedback') and $grade->is_editable()) {

                        $itemcell->text .= '<input type="hidden" id="oldfeedback_'.$userid.'_'.$item->id.'" name="oldfeedback_'.$userid.'_'.$item->id.'" value="' . s($grade->feedback) . '" />';
                        $itemcell->text .= '<input class="quickfeedback" tabindex="' . $tabindices[$item->id]['feedback'].'" id="feedback_'.$userid.'_'.$item->id
                                      . '" size="6" title="' . $strfeedback . '" type="text" name="feedback_'.$userid.'_'.$item->id.'" value="' . s($grade->feedback) . '" />';
                    }

                } else { // Not editing
                    $gradedisplaytype = $item->get_displaytype();

                    if ($item->scaleid && !empty($scalesarray[$item->scaleid])) {
                        $itemcell->attributes['class'] .= ' grade_type_scale';
                    } else if ($item->gradetype != GRADE_TYPE_TEXT) {
                        $itemcell->attributes['class'] .= ' grade_type_text';
                    }

                    if ($this->get_pref('enableajax')) {
                        $itemcell->attributes['class'] .= ' clickable';
                    }

                    if ($item->needsupdate) {
                        $itemcell->text .= html_writer::tag('span', get_string('error'), array('class'=>"gradingerror$hidden$gradepass"));
                    } else {
                        // We always want to display the correct (first) displaytype when editing
                    	// regardless of grade_report_gradeeditalways
                    	$gradedisplaytype = (integer) substr( (string) $item->get_displaytype(),0,1);
                    	 
                    	// if we have an accumulated total points that's not accurately reflected in the db, then we want to display the ACCURATE number
                        // we only need to take the extra calculation into account if points display since percent and letter are accurate by their nature
                        // If the settings don't call for ACCURATE point totals ($this->accuratetotals) then there will be no earned_total value
	                    $tempmax = $item->grademax;
                        if ($gradedisplaytype == GRADE_DISPLAY_TYPE_REAL && isset($this->grades[$userid][$grade->itemid]->cat_item)) {
                        	$items = $this->gtree->items;
		               		$grade_values = $this->grades[$userid][$grade->itemid]->cat_item;
		               		$grade_maxes = $this->grades[$userid][$grade->itemid]->cat_max;
		               		$this_cat = $this->gtree->items[$grade->itemid]->get_item_category();
		               		$this->gtree->limit_item($this_cat,$items,$grade_values,$grade_maxes);
			       			$gradeval = array_sum($grade_values);
			       			$item->grademax = array_sum($grade_maxes);
                        }
//                      	$value = grade_format_gradevalue($gradeval, $item, true, $gradedisplaytype, null);
						$item->grademax = $tempmax;
                      	if (! $grade->is_hidden() && $gradeval <> null && $this->accuratetotals) {
                			$this->grades[$userid][$this->gtree->parents[$grade->itemid]->id]->cat_item[$grade->itemid] = $gradeval;
							$this->grades[$userid][$this->gtree->parents[$grade->itemid]->id]->cat_max[$grade->itemid] = $grade->rawgrademax;
				   		}
                    	$itemcell->text .= html_writer::tag('span', grade_format_gradevalue($gradeval, $item, true, $gradedisplaytype, null), array('class'=>"gradevalue$hidden$gradepass"));
                        if ($this->get_pref('showanalysisicon')) {
                            $itemcell->text .= $this->gtree->get_grade_analysis_icon($grade);
                        }
                    }
                }

                if (!empty($this->gradeserror[$item->id][$userid])) {
                    $itemcell->text .= $this->gradeserror[$item->id][$userid];
                }

                $itemrow->cells[] = $itemcell;
            }
            $rows[] = $itemrow;
        }
/*
        if ($this->get_pref('enableajax')) {
            $jsarguments['cfg']['ajaxenabled'] = true;
            $jsarguments['cfg']['scales'] = array();
            foreach ($jsscales as $scale) {
                $jsarguments['cfg']['scales'][$scale->id] = explode(',',$scale->scale);
            }
            $jsarguments['cfg']['feedbacktrunclength'] =  $this->feedback_trunc_length;

            //feedbacks are now being stored in $jsarguments['feedback'] in get_right_rows()
            //$jsarguments['cfg']['feedback'] =  $this->feedbacks;
        }
        $jsarguments['cfg']['isediting'] = (bool)$USER->gradeediting[$this->courseid];
        $jsarguments['cfg']['courseid'] =  $this->courseid;
        $jsarguments['cfg']['studentsperpage'] =  $this->get_pref('studentsperpage');
        $jsarguments['cfg']['showquickfeedback'] =  (bool)$this->get_pref('showquickfeedback');
*/
        return $rows;
    }

    /**
     * Depending on the style of report (fixedstudents vs traditional one-table),
     * arranges the rows of data in one or two tables, and returns the output of
     * these tables in HTML
     * @return string HTML
     */
    public function get_grade_table() {
        global $OUTPUT;
 		$fixedstudents = 0; // always for laegrader report
//        $fixedstudents = $this->is_fixed_students();

        $leftrows = $this->get_left_rows();
        $rightrows = $this->get_right_rows();

        $html = '';
        
        $fulltable = new html_table();
        $fulltable->attributes['class'] = 'laegradestable';
        $fulltable->id = 'lae-user-grades';

        // Extract rows from each side (left and right) and collate them into one row each
        foreach ($leftrows as $key => $row) {
            $row->cells = array_merge($row->cells, $rightrows[$key]->cells);
            $fulltable->data[] = $row;
        }
        $html .= html_writer::table($fulltable);

        return $OUTPUT->container($html, 'gradeparent');
    }

    /**
     * Builds and return the row of icons for the left side of the report.
     * It only has one cell that says "Controls"
     * @param array $rows The Array of rows for the left part of the report
     * @param int $colspan The number of columns this cell has to span
     * @return array Array of rows for the left part of the report
     */
    public function get_left_icons_row($rows=array(), $colspan=1) {
        global $USER;

        if ($USER->gradeediting[$this->courseid]) {
            $controlsrow = new html_table_row();
            $controlsrow->attributes['class'] = 'controls';
            $controlscell = new html_table_cell();
            $controlscell->attributes['class'] = 'header controls';
            $controlscell->colspan = $colspan;
            $controlscell->header = true;
            $controlscell->scope = 'row';
            $controlscell->text = $this->get_lang_string('controls','grades');

            $controlsrow->cells[] = $controlscell;
            $rows[] = $controlsrow;
        }
        return $rows;
    }

    /**
     * Builds and return the header for the row of ranges, for the left part of the grader report.
     * @param array $rows The Array of rows for the left part of the report
     * @param int $colspan The number of columns this cell has to span
     * @return array Array of rows for the left part of the report
     */
    public function get_left_range_row($rows=array(), $colspan=1) {
        global $CFG, $USER;

        if ($this->get_pref('showranges')) {
            $rangerow = new html_table_row();
            $rangerow->attributes['class'] = 'range r'.$this->rowcount++;
            $rangecell = new html_table_cell();
            $rangecell->attributes['class'] = 'header range';
            $rangecell->colspan = $colspan;
            $rangecell->header = true;
            $rangecell->scope = 'row';
            $rangecell->text = $this->get_lang_string('range','grades');
            $rangerow->cells[] = $rangecell;
 /*           for ($i = 1; $i < $colspan; ++$i) {
            	$rangecell = new html_table_cell();
            	$rangecell->attributes['class'] = 'header range';
	            $rangecell->header = true;
	            $rangecell->scope = 'row';
            	$rangecell->text = '';
            	$rangerow->cells[] = $rangecell;
            }
*/            $rows[] = $rangerow;
        }

        return $rows;
    }

    /**
     * Builds and return the headers for the rows of averages, for the left part of the grader report.
     * @param array $rows The Array of rows for the left part of the report
     * @param int $colspan The number of columns this cell has to span
     * @param bool $groupavg If true, returns the row for group averages, otherwise for overall averages
     * @return array Array of rows for the left part of the report
     */
    public function get_left_avg_row($rows=array(), $colspan=1, $groupavg=false) {
        if (!$this->canviewhidden) {
            // totals might be affected by hiding, if user can not see hidden grades the aggregations might be altered
            // better not show them at all if user can not see all hideen grades
            return $rows;
        }

        $showaverages = $this->get_pref('showaverages');
        $showaveragesgroup = $this->currentgroup && $showaverages;
        $straveragegroup = get_string('groupavg', 'grades');

        if ($groupavg) {
            if ($showaveragesgroup) {
                $groupavgrow = new html_table_row();
                $groupavgrow->attributes['class'] = 'groupavg r'.$this->rowcount++;
                $groupavgcell = new html_table_cell();
                $groupavgcell->attributes['class'] = 'header range';
                $groupavgcell->colspan = $colspan;
                $groupavgcell->header = true;
                $groupavgcell->scope = 'row';
                $groupavgcell->text = $straveragegroup;
                $groupavgrow->cells[] = $groupavgcell;
                $rows[] = $groupavgrow;
            }
        } else {
            $straverage = get_string('overallaverage', 'grades');

            if ($showaverages) {
                $avgrow = new html_table_row();
                $avgrow->attributes['class'] = 'avg r'.$this->rowcount++;
                $avgcell = new html_table_cell();
                $avgcell->attributes['class'] = 'header range';
                $avgcell->colspan = $colspan;
                $avgcell->header = true;
                $avgcell->scope = 'row';
                $avgcell->text = $straverage;
                $avgrow->cells[] = $avgcell;
                $rows[] = $avgrow;
            }
        }

        return $rows;
    }

    /**
     * Builds and return the row of icons when editing is on, for the right part of the grader report.
     * @param array $rows The Array of rows for the right part of the report
     * @return array Array of rows for the right part of the report
     */
    public function get_right_icons_row($rows=array()) {
        global $USER;
        if ($USER->gradeediting[$this->courseid]) {
            $iconsrow = new html_table_row();
            $iconsrow->attributes['class'] = 'controls';

            foreach ($this->gtree->items as $itemid=>$unused) {
                // emulate grade element
                $element = $this->gtree->locate_element($itemid);
                $itemcell = new html_table_cell();
                $itemcell->attributes['class'] = 'controls icons';
                $itemcell->text = $this->get_icons($element);
                $iconsrow->cells[] = $itemcell;
            }
            $rows[] = $iconsrow;
        }
        return $rows;
    }

    /**
     * Builds and return the row of ranges for the right part of the grader report.
     * @param array $rows The Array of rows for the right part of the report
     * @return array Array of rows for the right part of the report
     */
    public function get_right_range_row($rows=array()) {
        global $OUTPUT;

        if ($this->get_pref('showranges')) {
            $rangesdisplaytype   = $this->get_pref('rangesdisplaytype');
            $rangesdecimalpoints = $this->get_pref('rangesdecimalpoints');
            $rangerow = new html_table_row();
            $rangerow->attributes['class'] = 'heading range';

            foreach ($this->gtree->items as $itemid=>$unused) {
                $item =& $this->gtree->items[$itemid];
                $itemcell = new html_table_cell();
//                $itemcell->header = true;
                $itemcell->attributes['class'] .= ' range';
                // if we have an accumulated total points that's not accurately reflected in the db, then we want to display the ACCURATE number
                // we only need to take the extra calculation into account if points display since percent and letter are accurate by their nature
                // If the settings don't call for ACCURATE point totals ($this->accuratetotals) then there will be no earned_total value
                $tempmax = $item->grademax;
                if (isset($this->gtree->items[$itemid]->cat_max)) {
//                	$items = $this->gtree->items;
//                	$grade_values = $this->gtree->items[$itemid]->cat_item;
                	$grade_maxes = $this->gtree->items[$itemid]->cat_max;
//                	$this_cat = $this->gtree->items[$itemid]->get_item_category();
//                	$this->gtree->limit_item($this_cat,$items,$grade_values,$grade_maxes);
//                	$gradeval = array_sum($grade_values);
                	$item->grademax = array_sum($grade_maxes);
                }
//                $value = grade_format_gradevalue($gradeval, $item, true, $gradedisplaytype, null);
                if ($this->accuratetotals && isset($this->gtree->parents[$itemid]->id)) {
//                	$this->gtree->items[$this->gtree->parents[$itemid]->id]->cat_item[$itemid] = $gradeval;
                	$this->gtree->items[$this->gtree->parents[$itemid]->id]->cat_max[$itemid] = $item->grademax;
                }
                
                $hidden = '';
                if ($item->is_hidden()) {
                    $hidden = ' hidden ';
                }

                $formattedrange = $item->get_formatted_range(GRADE_DISPLAY_TYPE_REAL, $rangesdecimalpoints);

                $itemcell->text = $OUTPUT->container($formattedrange, 'rangevalues'.$hidden);
                $rangerow->cells[] = $itemcell;
                $item->grademax = $tempmax;
            }
            $rows[] = $rangerow;
        }
        return $rows;
    }

    /**
     * Builds and return the row of averages for the right part of the grader report.
     * @param array $rows Whether to return only group averages or all averages.
     * @param bool $grouponly Whether to return only group averages or all averages.
     * @return array Array of rows for the right part of the report
     */
    public function get_right_avg_row($rows=array(), $grouponly=false) {
        global $CFG, $USER, $DB, $OUTPUT;

        if (!$this->canviewhidden) {
            // totals might be affected by hiding, if user can not see hidden grades the aggregations might be altered
            // better not show them at all if user can not see all hidden grades
            return $rows;
        }

        $showaverages = $this->get_pref('showaverages');
        $showaveragesgroup = $this->currentgroup && $showaverages;

        $averagesdisplaytype   = $this->get_pref('averagesdisplaytype');
        $averagesdecimalpoints = $this->get_pref('averagesdecimalpoints');
        $meanselection         = $this->get_pref('meanselection');
        $shownumberofgrades    = $this->get_pref('shownumberofgrades');

        $avghtml = '';
        $avgcssclass = 'avg';

        if ($grouponly) {
            $straverage = get_string('groupavg', 'grades');
            $showaverages = $this->currentgroup && $this->get_pref('showaverages');
            $groupsql = $this->groupsql;
            $groupwheresql = $this->groupwheresql;
            $groupwheresqlparams = $this->groupwheresql_params;
            $avgcssclass = 'groupavg';
        } else {
            $straverage = get_string('overallaverage', 'grades');
            $showaverages = $this->get_pref('showaverages');
            $groupsql = "";
            $groupwheresql = "";
            $groupwheresqlparams = array();
        }

        if ($shownumberofgrades) {
            $straverage .= ' (' . get_string('submissions', 'grades') . ') ';
        }

        $totalcount = $this->get_numusers($grouponly);

        //limit to users with a gradeable role
        list($gradebookrolessql, $gradebookrolesparams) = $DB->get_in_or_equal(explode(',', $this->gradebookroles), SQL_PARAMS_NAMED, 'grbr0');

        //limit to users with an active enrollment
        list($enrolledsql, $enrolledparams) = get_enrolled_sql($this->context);

        if ($showaverages) {
            $params = array_merge(array('courseid'=>$this->courseid), $gradebookrolesparams, $enrolledparams, $groupwheresqlparams);

            // find sums of all grade items in course
            $sql = "SELECT g.itemid, SUM(g.finalgrade) AS sum
                      FROM {grade_items} gi
                      JOIN {grade_grades} g ON g.itemid = gi.id
                      JOIN {user} u ON u.id = g.userid
                      JOIN ($enrolledsql) je ON je.id = u.id
                      JOIN (
                               SELECT DISTINCT ra.userid
                                 FROM {role_assignments} ra
                                WHERE ra.roleid $gradebookrolessql
                                  AND ra.contextid " . get_related_contexts_string($this->context) . "
                           ) rainner ON rainner.userid = u.id
                      $groupsql
                     WHERE gi.courseid = :courseid
                       AND u.deleted = 0
                       AND g.finalgrade IS NOT NULL
                       $groupwheresql
                     GROUP BY g.itemid";
            $sumarray = array();
            if ($sums = $DB->get_records_sql($sql, $params)) {
                foreach ($sums as $itemid => $csum) {
                    $sumarray[$itemid] = $csum->sum;
                }
            }

            // MDL-10875 Empty grades must be evaluated as grademin, NOT always 0
            // This query returns a count of ungraded grades (NULL finalgrade OR no matching record in grade_grades table)
            $sql = "SELECT gi.id, COUNT(DISTINCT u.id) AS count
                      FROM {grade_items} gi
                      CROSS JOIN {user} u
                      JOIN ($enrolledsql) je
                           ON je.id = u.id
                      JOIN {role_assignments} ra
                           ON ra.userid = u.id
                      LEFT OUTER JOIN {grade_grades} g
                           ON (g.itemid = gi.id AND g.userid = u.id AND g.finalgrade IS NOT NULL)
                      $groupsql
                     WHERE gi.courseid = :courseid
                           AND ra.roleid $gradebookrolessql
                           AND ra.contextid ".get_related_contexts_string($this->context)."
                           AND u.deleted = 0
                           AND g.id IS NULL
                           $groupwheresql
                  GROUP BY gi.id";

            $ungradedcounts = $DB->get_records_sql($sql, $params);

            $avgrow = new html_table_row();
            $avgrow->attributes['class'] = 'avg';

            foreach ($this->gtree->items as $itemid=>$unused) {
                $item =& $this->gtree->items[$itemid];

                if ($item->needsupdate) {
                    $avgcell = new html_table_cell();
                    $avgcell->text = $OUTPUT->container(get_string('error'), 'gradingerror');
                    $avgrow->cells[] = $avgcell;
                    continue;
                }

                if (!isset($sumarray[$item->id])) {
                    $sumarray[$item->id] = 0;
                }

                if (empty($ungradedcounts[$itemid])) {
                    $ungradedcount = 0;
                } else {
                    $ungradedcount = $ungradedcounts[$itemid]->count;
                }

                if ($meanselection == GRADE_REPORT_MEAN_GRADED) {
                    $meancount = $totalcount - $ungradedcount;
                } else { // Bump up the sum by the number of ungraded items * grademin
                    $sumarray[$item->id] += $ungradedcount * $item->grademin;
                    $meancount = $totalcount;
                }

                $decimalpoints = $item->get_decimals();

                // Determine which display type to use for this average
                if ($USER->gradeediting[$this->courseid]) {
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

                if (!isset($sumarray[$item->id]) || $meancount == 0) {
                    $avgcell = new html_table_cell();
                    $avgcell->text = '-';
                    $avgrow->cells[] = $avgcell;

                } else {
                    $sum = $sumarray[$item->id];
                    $avgradeval = $sum/$meancount;
                    $gradehtml = grade_format_gradevalue($avgradeval, $item, true, $displaytype, $decimalpoints);

                    $numberofgrades = '';
                    if ($shownumberofgrades) {
                        $numberofgrades = " ($meancount)";
                    }

                    $avgcell = new html_table_cell();
                    $avgcell->text = $gradehtml.$numberofgrades;
                    $avgrow->cells[] = $avgcell;
                }
            }
            $rows[] = $avgrow;
        }
        return $rows;
    }

    /**
     * Given a grade_category, grade_item or grade_grade, this function
     * figures out the state of the object and builds then returns a div
     * with the icons needed for the grader report.
     *
     * @param array $object
     * @return string HTML
     */
    protected function get_icons($element) {
        global $CFG, $USER, $OUTPUT;

        if (!$USER->gradeediting[$this->courseid]) {
            return '<div class="grade_icons" />';
        }

        // Init all icons
        $editicon = '';

        if ($element['type'] != 'categoryitem' && $element['type'] != 'courseitem') {
            $editicon = $this->gtree->get_edit_icon($element, $this->gpr);
        }

        $editcalculationicon = '';
        $showhideicon        = '';
        $lockunlockicon      = '';
        $zerofillicon      = '';
        
        if (has_capability('moodle/grade:manage', $this->context)) {
            if ($this->get_pref('showcalculations')) {
                $editcalculationicon = $this->gtree->get_calculation_icon($element, $this->gpr);
            }

            if ($this->get_pref('showeyecons')) {
               $showhideicon = $this->gtree->get_hiding_icon($element, $this->gpr);
               $showhideicon = str_replace('iconsmall', 'smallicon', $showhideicon); // need to fix the error when the eye is shut sending the wrong class
            }

            if ($this->get_pref('showlocks')) {
                $lockunlockicon = $this->gtree->get_locking_icon($element, $this->gpr);
            }

            if ($this->get_pref('showzerofill') && $element['type'] == 'item') {
            	$zerofillicon = $this->gtree->get_zerofill_icon($element, $this->gpr);
            }

        }

        $gradeanalysisicon   = '';
        if ($this->get_pref('showanalysisicon') && $element['type'] == 'grade') {
            $gradeanalysisicon .= $this->gtree->get_grade_analysis_icon($element['object']);
        }

        return $OUTPUT->container($editicon.$zerofillicon.$editcalculationicon.$showhideicon.$lockunlockicon.$gradeanalysisicon, 'grade_icons');
    }

    /**
     * Given a category element returns collapsing +/- icon if available
     * @param object $object
     * @return string HTML
     */
    protected function get_collapsing_icon($element) {
        global $OUTPUT;

        $icon = '';
        // If object is a category, display expand/contract icon
        if ($element['type'] == 'category') {
            // Load language strings
            $strswitchminus = $this->get_lang_string('aggregatesonly', 'grades');
            $strswitchplus  = $this->get_lang_string('gradesonly', 'grades');
            $strswitchwhole = $this->get_lang_string('fullmode', 'grades');

            $url = new moodle_url($this->gpr->get_return_url(null, array('target'=>$element['eid'], 'sesskey'=>sesskey())));

            if (in_array($element['object']->id, $this->collapsed['aggregatesonly'])) {
                $url->param('action', 'switch_plus');
                $icon = $OUTPUT->action_icon($url, new pix_icon('t/switch_plus', $strswitchplus));

            } else if (in_array($element['object']->id, $this->collapsed['gradesonly'])) {
                $url->param('action', 'switch_whole');
                $icon = $OUTPUT->action_icon($url, new pix_icon('t/switch_whole', $strswitchwhole));

            } else {
                $url->param('action', 'switch_minus');
                $icon = $OUTPUT->action_icon($url, new pix_icon('t/switch_minus', $strswitchminus));
            }
        }
        return $icon;
    }

    
    /**
     * Given the name of a user preference (without grade_report_ prefix), locally saves then returns
     * the value of that preference. If the preference has already been fetched before,
     * the saved value is returned. If the preference is not set at the User level, the $CFG equivalent
     * is given (site default).
     * @static (Can be called statically, but then doesn't benefit from caching)
     * @param string $pref The name of the preference (do not include the grade_report_ prefix)
     * @param int $objectid An optional itemid or categoryid to check for a more fine-grained preference
     * @return mixed The value of the preference
     */
    public function get_pref($pref, $objectid=null) {
    	global $CFG;
    	$fullprefname = 'grade_report_' . $pref;
    	$shortprefname = 'grade_' . $pref;
    
    	$retval = null;
    
    	if (!isset($this) OR get_class($this) != 'grade_report_laegrader') {
    		if (!empty($objectid)) {
    			$retval = get_user_preferences($fullprefname . $objectid, grade_report::get_pref($pref));
    		} elseif (isset($CFG->$fullprefname)) {
    			$retval = get_user_preferences($fullprefname, $CFG->$fullprefname);
    		} elseif (isset($CFG->$shortprefname)) {
    			$retval = get_user_preferences($fullprefname, $CFG->$shortprefname);
    		} else {
    			$retval = null;
    		}
    	} else {
    		if (empty($this->prefs[$pref.$objectid])) {
    
    			if (!empty($objectid)) {
    				$retval = get_user_preferences($fullprefname . $objectid);
    				if (empty($retval)) {
    					// No item pref found, we are returning the global preference
    					$retval = $this->get_pref($pref);
    					$objectid = null;
    				}
    			} else {
    				$retval = get_user_preferences($fullprefname, $CFG->$fullprefname);
    			}
    			$this->prefs[$pref.$objectid] = $retval;
    		} else {
    			$retval = $this->prefs[$pref.$objectid];
    		}
    	}
    
    	return $retval;
    }
    
    /**
     * Processes a single action against a category, grade_item or grade.
     * @param string $target eid ({type}{id}, e.g. c4 for category4)
     * @param string $action Which action to take (edit, delete etc...)
     * @return
     */
    public function process_action($target, $action) {
        // TODO: this code should be in some grade_tree static method
        $targettype = substr($target, 0, 1);
        $targetid = substr($target, 1);
        // TODO: end

        if ($collapsed = get_user_preferences('grade_report_grader_collapsed_categories')) {
            $collapsed = unserialize($collapsed);
        } else {
            $collapsed = array('aggregatesonly' => array(), 'gradesonly' => array());
        }

        switch ($action) {
            case 'switch_minus': // Add category to array of aggregatesonly
                if (!in_array($targetid, $collapsed['aggregatesonly'])) {
                    $collapsed['aggregatesonly'][] = $targetid;
                    set_user_preference('grade_report_grader_collapsed_categories', serialize($collapsed));
                }
                break;

            case 'switch_plus': // Remove category from array of aggregatesonly, and add it to array of gradesonly
                $key = array_search($targetid, $collapsed['aggregatesonly']);
                if ($key !== false) {
                    unset($collapsed['aggregatesonly'][$key]);
                }
                if (!in_array($targetid, $collapsed['gradesonly'])) {
                    $collapsed['gradesonly'][] = $targetid;
                }
                set_user_preference('grade_report_grader_collapsed_categories', serialize($collapsed));
                break;
            case 'switch_whole': // Remove the category from the array of collapsed cats
                $key = array_search($targetid, $collapsed['gradesonly']);
                if ($key !== false) {
                    unset($collapsed['gradesonly'][$key]);
                    set_user_preference('grade_report_grader_collapsed_categories', serialize($collapsed));
                }

                break;
            default:
                break;
        }

        return true;
    }

    /**
     * Returns whether or not to display fixed students column.
     * Includes a browser check, because IE6 doesn't support the scrollbar.
     *
     * @return bool
     */
    public function is_fixed_students() {
    	return 0; // always return no for LAE
    }

    /**
     * Refactored function for generating HTML of sorting links with matching arrows.
     * Returns an array with 'studentname' and 'idnumber' as keys, with HTML ready
     * to inject into a table header cell.
     * @param array $extrafields Array of extra fields being displayed, such as
     *   user idnumber
     * @return array An associative array of HTML sorting links+arrows
     */
    public function get_sort_arrows(array $extrafields = array()) {
        global $OUTPUT;
        $arrows = array();

        $strsortasc   = $this->get_lang_string('sortasc', 'grades');
        $strsortdesc  = $this->get_lang_string('sortdesc', 'grades');
        $strfirstname = $this->get_lang_string('firstname');
        $strlastname  = $this->get_lang_string('lastname');

        $firstlink = html_writer::link(new moodle_url($this->baseurl, array('sortitemid'=>'firstname')), $strfirstname);
        $lastlink = html_writer::link(new moodle_url($this->baseurl, array('sortitemid'=>'lastname')), $strlastname);

        $arrows['studentname'] = $lastlink;

        if ($this->sortitemid === 'lastname') {
            if ($this->sortorder == 'ASC') {
                $arrows['studentname'] .= print_arrow('up', $strsortasc, true);
            } else {
                $arrows['studentname'] .= print_arrow('down', $strsortdesc, true);
            }
        }

        $arrows['studentname'] .= ' ' . $firstlink;

        if ($this->sortitemid === 'firstname') {
            if ($this->sortorder == 'ASC') {
                $arrows['studentname'] .= print_arrow('up', $strsortasc, true);
            } else {
                $arrows['studentname'] .= print_arrow('down', $strsortdesc, true);
            }
        }

        foreach ($extrafields as $field) {
            $fieldlink = html_writer::link(new moodle_url($this->baseurl,
                    array('sortitemid'=>$field)), get_user_field_name($field));
            $arrows[$field] = $fieldlink;

            if ($field == $this->sortitemid) {
                if ($this->sortorder == 'ASC') {
                    $arrows[$field] .= print_arrow('up', $strsortasc, true);
                } else {
                    $arrows[$field] .= print_arrow('down', $strsortdesc, true);
                }
            }
        }

        return $arrows;
    }
/*
    public function quick_dump() {
        global $CFG;
        require_once($CFG->dirroot.'/lib/excellib.class.php');

//        $export_tracking = $this->track_exports();

        $strgrades = get_string('grades');
        $accuratetotals = get_user_preferences('accuratepointtotals') == null ? 1 : 0;
        
    /// Calculate file name
        $shortname = format_string($this->course->shortname, true, array('context' => get_context_instance(CONTEXT_COURSE, $this->course->id)));
        $downloadfilename = clean_filename("$shortname $strgrades.xls");
    /// Creating a workbook
        $workbook = new MoodleExcelWorkbook("-");
    /// Sending HTTP headers
        $workbook->send($downloadfilename);
    /// Adding the worksheet
        $myxls =& $workbook->add_worksheet($strgrades);

    /// Print names of all the fields
        $myxls->write_string(0,0,get_string("firstname"));
        $myxls->write_string(0,1,get_string("lastname"));
        $myxls->write_string(0,2,get_string("idnumber"));
        $myxls->write_string(0,3,get_string("email"));
        $pos=4;
        
        
/*        
        foreach ($this->columns as $grade_item) {
            $myxls->write_string(0, $pos++, $this->format_column_name($grade_item));

            /// add a column_feedback column
            if ($this->export_feedback) {
                $myxls->write_string(0, $pos++, $this->format_column_name($grade_item, true));
            }
        }
*/
        // write out column headers
/*        foreach ($this->gtree->items as $grade_item) {
//            $myxls->write_string(0, $pos++, $this->format_column_name($grade_item));
            switch ($grade_item->itemtype) {
                    case 'category':
                        $grade_item->item_category = grade_category::fetch(array('id'=>$grade_item->iteminstance));
//                        $grade_item->load_category();
                        $myxls->write_string(0, $pos++, $grade_item->item_category->fullname . ' Category total');
                        break;
                    case 'course':
                        $myxls->write_string(0, $pos++, 'Course total');
                        break;
                    default:
                        $myxls->write_string(0, $pos++, $grade_item->itemname);
            }

            /// add a column_feedback column
            if (isset($this->export_feedback) AND $this->export_feedback) {
//                $myxls->write_string(0, $pos++, $this->format_column_name($grade_item, true));
                $myxls->write_string(0, $pos++, $grade_item->itemname);
            }
        }
/*        
        // write out range row
        $myxls->write_string(1, 2, 'Maximum grade->');
        $pos=4;
        foreach ($this->gtree->items as $grade_item) {
//            $myxls->write_string(0, $pos++, $this->format_column_name($grade_item));
            $myxls->write_number(1, $pos++, $grade_item->grademax);
//            $myxls->write_number($i,$j++,$gradestr);
            /// add a column_feedback column
            if (isset($this->export_feedback) AND $this->export_feedback) {
//                $myxls->write_string(0, $pos++, $this->format_column_name($grade_item, true));
                $myxls->write_string(1, $pos++, $grade_item->name);
            }
        }

        // write out weights row
        $myxls->write_string(2, 2, 'Weight->');
        $pos=4;
        foreach ($this->gtree->items as $grade_item) {
            if (isset($this->gtree->parents[$grade_item->id]->id) && $this->gtree->parents[$grade_item->id]->agg == GRADE_AGGREGATE_WEIGHTED_MEAN) {
                $myxls->write_number(2,$pos++,$grade_item->aggregationcoef);
//                $myxls->write_string(1, $pos++, format_float($grade_item->aggregationcoef,0));
            } else {
                $myxls->write_string(2, $pos++, "");
            }
            /// add a column_feedback column
            if (isset($this->export_feedback) AND $this->export_feedback) {
                $myxls->write_string(2, $pos++, $grade_item->name);
            }
        }

*/        
/*        
    /// Print all the lines of data.
        $i = 2;
//        $geub = new grade_export_update_buffer();
        $gui = new graded_users_iterator($this->course, $this->gtree->items);
        $gui->init();
        while ($userdata = $gui->next_user()) {
            $i++;
            $user = $userdata->user;

            $myxls->write_string($i,0,$user->firstname);
            $myxls->write_string($i,1,$user->lastname);
            $myxls->write_string($i,2,$user->idnumber);
//            $myxls->write_string($i,3,$user->institution);
//            $myxls->write_string($i,4,$user->department);
//            $myxls->write_string($i,5,$user->email);
            $j=4;
            foreach ($userdata->grades as $itemid => $grade) {
                if ($export_tracking) {
//                    $status = $geub->track($grade);
                }

                $gradestr = $this->format_grade($grade);
                if (is_numeric($gradestr)) {
                    $myxls->write_number($i,$j++,$gradestr);
                }
                else {
                    $myxls->write_string($i,$j++,$gradestr);
                }

                // writing feedback if requested
                if ($this->export_feedback) {
                    $myxls->write_string($i, $j++, $this->format_feedback($userdata->feedbacks[$itemid]));
                }
            }
        }
/*
    /// Print all the lines of data.
        $i = 2;
//        $geub = new grade_export_update_buffer();
//        $gui = new graded_users_iterator($this->course, $this->columns, $this->groupid);
//        $gui->init();
        foreach ($this->users as $key=>$user) {
            $i++;
//            $user = $userdata->user;

            $myxls->write_string($i,0,$user->firstname);
            $myxls->write_string($i,1,$user->lastname);
            $myxls->write_string($i,2,$user->idnumber);
            $myxls->write_string($i,3,$user->email);
//           $myxls->write_string($i,3,$user->institution);
//            $myxls->write_string($i,4,$user->department);
//            $myxls->write_string($i,3,$user->email);
            $j=4;
            foreach ($this->gtree->items as $itemid => $item) {
//                if ($export_tracking) {
//                    $status = $geub->track($grade);
//                }

                $grade = $this->grades[$key][$itemid];
				$gradeval = $grade->finalgrade;
                // if gradeeditalways then we only want the first displaytype (in case multiple displaytypes are requested)
                $gradedisplaytype = (integer) substr( (string) $item->get_displaytype(),0,1);
                // if we have an accumulated total points that's not accurately reflected in the db, then we want to display the ACCURATE number
                if ($gradedisplaytype == GRADE_DISPLAY_TYPE_REAL && isset($this->grades[$userid][$grade->itemid]->cat_item)) {
                   	$items = $this->gtree->items;
               		$grade_values = $this->grades[$userid][$grade->itemid]->cat_item;
               		$grade_maxes = $this->grades[$userid][$grade->itemid]->cat_max;
               		$this_cat = $this->gtree->items[$grade->itemid]->get_item_category();
               		limit_item($this_cat,$items,$grade_values,$grade_maxes);
	       			$gradeval = array_sum($grade_values);
					$item->grademax = array_sum($grade_maxes);
                    }
           		// is calculating accurate totals store the earned_total for this item to its parent, if there is one
                if (! $grade->is_hidden() && $gradeval <> null && $this->accuratetotals) {
          			$this->grades[$userid][$this->gtree->parents[$grade->itemid]->id]->cat_item[$grade->itemid] = $gradeval;
					$this->grades[$userid][$this->gtree->parents[$grade->itemid]->id]->cat_max[$grade->itemid] = $grade->rawgrademax;
		   		}
                $gradestr = grade_format_gradevalue($gradeval, $item, true, $gradedisplaytype, null);
		   		
/*
		   		if ($gradedisplaytype == GRADE_DISPLAY_TYPE_REAL && $this->accuratetotals) {
                    $gradestr = grade_format_gradevalue($grade->earned_total, $item, true, $gradedisplaytype, null);
                } else {
                    $gradestr = grade_format_gradevalue($grade->finalgrade, $item, true, $gradedisplaytype, null);
                }
*/
/*                if (is_percentage($gradestr)) {
                    $myxls->write_number($i,$j++,$gradestr * .01);
//                    $myxls->write_number($i,$j++,substr(trim($gradestr),0,strlen(trim($gradestr))-1), array(num_format=>'Percent'));
                } else if (is_numeric($gradestr)) {
                    $myxls->write_number($i,$j++,$gradestr);
                } else {
                    $myxls->write_string($i,$j++,$gradestr);
                }
/*
                // writing feedback if requested
                if ($this->export_feedback) {
                    $myxls->write_string($i, $j++, $this->format_feedback($userdata->feedbacks[$itemid]));
                }
 * 
 */
//            }
//        }
        
        
        
/*        
        
        $gui->close();
        $geub->close();

    /// Close the workbook
        $workbook->close();

        exit;
    }
*/    
    function quick_dumpOLD() {
        global $CFG;
        require_once($CFG->dirroot.'/lib/excellib.class.php');

//        $export_tracking = $this->track_exports();

        $strgrades = get_string('grades');
        $accuratetotals = get_user_preferences('accuratepointtotals') == null ? 1 : 0;


    /// Calculate file name
        $downloadfilename = clean_filename("{$this->course->shortname} $strgrades.xls");
    /// Creating a workbook
        $workbook = new MoodleExcelWorkbook("-");
    /// Sending HTTP headers
        $workbook->send($downloadfilename);
    /// Adding the worksheet
        $myxls =& $workbook->add_worksheet($strgrades);

    /// Print names of all the fields
        $myxls->write_string(0,0,get_string("firstname"));
        $myxls->write_string(0,1,get_string("lastname"));
        $myxls->write_string(0,2,get_string("idnumber"));
        $myxls->write_string(0,3,get_string("email"));
        $pos=4;
        
        // write out column headers
        foreach ($this->gtree->items as $grade_item) {
//            $myxls->write_string(0, $pos++, $this->format_column_name($grade_item));
            switch ($grade_item->itemtype) {
                    case 'category':
                        $grade_item->item_category = grade_category::fetch(array('id'=>$grade_item->iteminstance));
//                        $grade_item->load_category();
                        $myxls->write_string(0, $pos++, $grade_item->item_category->fullname . ' Category total');
                        break;
                    case 'course':
                        $myxls->write_string(0, $pos++, 'Course total');
                        break;
                    default:
                        $myxls->write_string(0, $pos++, $grade_item->itemname);
            }

            /// add a column_feedback column
            if (isset($this->export_feedback) AND $this->export_feedback) {
//                $myxls->write_string(0, $pos++, $this->format_column_name($grade_item, true));
                $myxls->write_string(0, $pos++, $grade_item->itemname);
            }
        }

        // write out range row
        $myxls->write_string(1, 2, 'Maximum grade->');
        $pos=4;
        foreach ($this->gtree->items as $grade_item) {
//            $myxls->write_string(0, $pos++, $this->format_column_name($grade_item));
            $myxls->write_number(1, $pos++, $grade_item->grademax);
//            $myxls->write_number($i,$j++,$gradestr);
            /// add a column_feedback column
            if (isset($this->export_feedback) AND $this->export_feedback) {
//                $myxls->write_string(0, $pos++, $this->format_column_name($grade_item, true));
                $myxls->write_string(1, $pos++, $grade_item->name);
            }
        }

        // write out weights row
        $myxls->write_string(2, 2, 'Weight->');
        $pos=4;
        foreach ($this->gtree->items as $grade_item) {
            if (isset($this->gtree->parents[$grade_item->id]->id) && $this->gtree->parents[$grade_item->id]->agg == GRADE_AGGREGATE_WEIGHTED_MEAN) {
                $myxls->write_number(2,$pos++,$grade_item->aggregationcoef);
//                $myxls->write_string(1, $pos++, format_float($grade_item->aggregationcoef,0));
            } else {
                $myxls->write_string(2, $pos++, "");
            }
            /// add a column_feedback column
            if (isset($this->export_feedback) AND $this->export_feedback) {
                $myxls->write_string(2, $pos++, $grade_item->name);
            }
        }


    /// Print all the lines of data.
        $i = 2;
//        $geub = new grade_export_update_buffer();
//        $gui = new graded_users_iterator($this->course, $this->columns, $this->groupid);
//        $gui->init();
        foreach ($this->users as $key=>$user) {
            $i++;
//            $user = $userdata->user;

            $myxls->write_string($i,0,$user->firstname);
            $myxls->write_string($i,1,$user->lastname);
            $myxls->write_string($i,2,$user->idnumber);
            $myxls->write_string($i,3,$user->email);
//           $myxls->write_string($i,3,$user->institution);
//            $myxls->write_string($i,4,$user->department);
//            $myxls->write_string($i,3,$user->email);
            $j=4;
            foreach ($this->gtree->items as $itemid => $item) {
//                if ($export_tracking) {
//                    $status = $geub->track($grade);
//                }

                $grade = $this->grades[$key][$itemid];
				$gradeval = $grade->finalgrade;
                // if gradeeditalways then we only want the first displaytype (in case multiple displaytypes are requested)
                $gradedisplaytype = (integer) substr( (string) $item->get_displaytype(),0,1);
                // if we have an accumulated total points that's not accurately reflected in the db, then we want to display the ACCURATE number
                if ($gradedisplaytype == GRADE_DISPLAY_TYPE_REAL && isset($this->grades[$userid][$grade->itemid]->cat_item)) {
                   	$items = $this->gtree->items;
               		$grade_values = $this->grades[$userid][$grade->itemid]->cat_item;
               		$grade_maxes = $this->grades[$userid][$grade->itemid]->cat_max;
               		$this_cat = $this->gtree->items[$grade->itemid]->get_item_category();
               		limit_item($this_cat,$items,$grade_values,$grade_maxes);
	       			$gradeval = array_sum($grade_values);
					$item->grademax = array_sum($grade_maxes);
                    }
           		// is calculating accurate totals store the earned_total for this item to its parent, if there is one
                if (! $grade->is_hidden() && $gradeval <> null && $this->accuratetotals) {
          			$this->grades[$userid][$this->gtree->parents[$grade->itemid]->id]->cat_item[$grade->itemid] = $gradeval;
					$this->grades[$userid][$this->gtree->parents[$grade->itemid]->id]->cat_max[$grade->itemid] = $grade->rawgrademax;
		   		}
                $gradestr = grade_format_gradevalue($gradeval, $item, true, $gradedisplaytype, null);
		   		
/*
		   		if ($gradedisplaytype == GRADE_DISPLAY_TYPE_REAL && $this->accuratetotals) {
                    $gradestr = grade_format_gradevalue($grade->earned_total, $item, true, $gradedisplaytype, null);
                } else {
                    $gradestr = grade_format_gradevalue($grade->finalgrade, $item, true, $gradedisplaytype, null);
                }
*/
                if (is_percentage($gradestr)) {
                    $myxls->write_number($i,$j++,$gradestr * .01);
//                    $myxls->write_number($i,$j++,substr(trim($gradestr),0,strlen(trim($gradestr))-1), array(num_format=>'Percent'));
                } else if (is_numeric($gradestr)) {
                    $myxls->write_number($i,$j++,$gradestr);
                } else {
                    $myxls->write_string($i,$j++,$gradestr);
                }
/*
                // writing feedback if requested
                if ($this->export_feedback) {
                    $myxls->write_string($i, $j++, $this->format_feedback($userdata->feedbacks[$itemid]));
                }
 * 
 */
            }
        }
//        $gui->close();
//        $geub->close();

    /// Close the workbook
        $workbook->close();

        exit;
    }    
}

function grade_report_laegrader_settings_definition(&$mform) {
	global $CFG;

	$options = array(-1 => get_string('default', 'grades'),
			0 => get_string('hide'),
			1 => get_string('show'));
	if (empty($CFG->grade_report_laegrader_accuratetotals)) {
		$options[-1] = get_string('defaultprev', 'grades', $options[0]);
	} else {
		$options[-1] = get_string('defaultprev', 'grades', $options[1]);
	}

	$mform->addElement('select', 'report_laegrader_accuratetotals', get_string('accuratetotals', 'gradereport_laegrader'), $options);
}


