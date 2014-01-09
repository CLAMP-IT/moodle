<?php
require_once $CFG->dirroot.'/lib/grade/grade_item.php';
require_once $CFG->dirroot.'/lib/grade/grade_category.php';
require_once $CFG->dirroot.'/lib/grade/grade_object.php';
require_once $CFG->dirroot.'/grade/edit/tree/lib.php';
require_once $CFG->dirroot.'/grade/lib.php';
require_once($CFG->dirroot.'/grade/export/lib.php');

function grade_tree_local_helper($courseid, $fillers=false, $category_grade_last=true, $collapsed=null, $nooutcomes=false, $currentgroup) {
    global $CFG;
    $CFG->currentgroup = $currentgroup;
    return new grade_tree_local($courseid, $fillers, $category_grade_last, $collapsed, $nooutcomes);
}


class grade_tree_local extends grade_tree {

    /**
     * The basic representation of the tree as a hierarchical, 3-tiered array.
     * @var object $top_element
     */
    public $top_element;

    /**
     * 2D array of grade items and categories
     * @var array $levels
     */
    public $levels;

    /**
     * Grade items
     * @var array $items
     */
    public $items;

    /**
     * LAE Grade items used for cycling through the get_right_rows
     * @var array $items
     */
    public $levelitems;

    /**
     * LAE structure used to get the damn category names into the category-item object
     * @var array $items
     */
    public $catitems;

    /**
     * Constructor, retrieves and stores a hierarchical array of all grade_category and grade_item
     * objects for the given courseid. Full objects are instantiated. Ordering sequence is fixed if needed.
     *
     * @param int   $courseid The Course ID
     * @param bool  $fillers include fillers and colspans, make the levels var "rectangular"
     * @param bool  $category_grade_last category grade item is the last child
     * @param array $collapsed array of collapsed categories
     * @param bool  $nooutcomes Whether or not outcomes should be included
     */
    public function grade_tree_local($courseid, $fillers=false, $category_grade_last=true,
                               $collapsed=null, $nooutcomes=false) {
        global $USER, $CFG, $COURSE, $DB;

        $this->courseid   = $courseid;
        $this->levels     = array();
        $this->context    = context_course::instance($courseid);
        
        if (!empty($COURSE->id) && $COURSE->id == $this->courseid) {
            $course = $COURSE;
        } else {
            $course = $DB->get_record('course', array('id' => $this->courseid));
        }
        $this->modinfo = get_fast_modinfo($course);

        // get course grade tree
        $this->top_element = grade_category::fetch_course_tree($courseid, true);

        // no otucomes if requested
        if (!empty($nooutcomes)) {
            grade_tree_local::no_outcomes($this->top_element);
        }

        // move category item to last position in category
        if ($category_grade_last) {
            grade_tree_local::category_grade_last($this->top_element);
        }

        // provide for crossindexing of modinfo and grades in the case of display by group so items not assigned to a group can be omitted
        // first determine if enablegroupmembersonly is on, then determine if groupmode is set (separate or visible)
/*
        $this->modx = array();
        if ($CFG->enablegroupmembersonly && $CFG->currentgroup > 0 ) {
            $groupingsforthisgroup = $DB->get_fieldset_select('groupings_groups', 'groupingid', " groupid = $CFG->currentgroup ");
            $groupingsforthisgroup = implode(',', $groupingsforthisgroup);

            // get all the records for items that SHOULDN'T be included
            $sql = "SELECT gi.id FROM " .  $CFG->prefix . "grade_items gi, " . $CFG->prefix . "modules m, " . $CFG->prefix . "course_modules cm
                    WHERE m.name = gi.itemmodule
                    AND cm.instance = gi.iteminstance
                    AND cm.module = m.id
                    AND gi.courseid = $courseid
                    AND cm.groupingid <> 0
                    AND cm.groupingid NOT IN($groupingsforthisgroup)";
            $this->modx = $DB->get_records_sql($sql);
        }
*/
        // key to LAE grader, no levels
        grade_tree_local::fill_levels($this->levels, $this->top_element, 0);
        grade_tree_local::fill_levels_local($this->levelitems, $this->top_element, 0);
    }
    /**
     * Static recursive helper - fills the levels array, useful when accessing tree elements of one level
     *
     * @param array &$levels The levels of the grade tree through which to recurse
     * @param array &$element The seed of the recursion
     * @param int   $depth How deep are we?
     * @return void
     */

    public function fill_levels_local(&$levelitems, &$element, $depth) {

/*        if (array_key_exists($element['object']->id, $this->modx)) { // don't include something made only for a different group
            return;
        } else */
    	if ($element['type'] == 'category') { // prepare unique identifier
            $element['eid'] = 'c'.$element['object']->id;
            $this->catitems[$element['object']->id] = $element['object']->fullname;
        } else if (in_array($element['type'], array('item', 'courseitem', 'categoryitem'))) {
            $element['eid'] = 'i'.$element['object']->id;
            $this->items[$element['object']->id] =& $element['object'];
            $this->levelitems[$element['object']->id] =& $element;
            if ($element['type'] == 'categoryitem' && array_key_exists($element['object']->iteminstance,$this->catitems)) {
	            $this->items[$element['object']->id]->itemname = $this->catitems[$element['object']->iteminstance];
            }
        }

        if (empty($element['children'])) {
            return;
        }
        $prev = 0;
        foreach ($element['children'] as $sortorder=>$child) {
            grade_tree_local::fill_levels_local($this->levelitems, $element['children'][$sortorder], $depth);
        }
    }

     /**
     * Returns name of element optionally with icon and link
     * USED BY LAEGRADER IN ORDER TO WRAP GRADE TITLES IN THE HEADER
     *
     * @param array &$element An array representing an element in the grade_tree
     * @param bool  $withlink Whether or not this header has a link
     * @param bool  $icon Whether or not to display an icon with this header
     * @param bool  $spacerifnone return spacer if no icon found
     *
     * @return string header
     */
    function get_element_header_local(&$element, $withlink=false, $icon=true, $spacerifnone=false, $titlelength = null, $catname) {
        $header = '';

		switch ($element['type']) {
			case 'courseitem':
				$header .= 'COURSE TOTAL';
				break;
			case 'categoryitem':
				$header .= 'CATEGORY TOTAL<br />';
			default:
 		       	$header .= $catname;
		}
		if ($element['object']->aggregationcoef > 1) {
		    $header .= ' W=' . format_float($element['object']->aggregationcoef,1, true, true) . '%<br />';
		}
		if ($titlelength) {
	        $header = wordwrap($header, $titlelength, '<br />');
        }

        if ($icon) {
            $header = $this->get_element_icon($element, $spacerifnone) . $header;
        }

        if ($withlink) {
            $url = $this->get_activity_link($element);
            if ($url) {
                $a = new stdClass();
                $a->name = get_string('modulename', $element['object']->itemmodule);
                $title = get_string('linktoactivity', 'grades', $a);

                $header = html_writer::link($url, $header, array('title' => $title));
            }
        }

        return $header;
    }

    /*
     * LAE NEED TO INCLUDE THIS BECAUSE ITS THE ONLY WAY TO GET IT CALLED BY get_element_header ABOVE
     */
    private function get_activity_link($element) {
        global $CFG;
        /** @var array static cache of the grade.php file existence flags */
        static $hasgradephp = array();

        $itemtype = $element['object']->itemtype;
        $itemmodule = $element['object']->itemmodule;
        $iteminstance = $element['object']->iteminstance;
        $itemnumber = $element['object']->itemnumber;

        // Links only for module items that have valid instance, module and are
        // called from grade_tree with valid modinfo
        if ($itemtype != 'mod' || !$iteminstance || !$itemmodule || !$this->modinfo) {
            return null;
        }

        // Get $cm efficiently and with visibility information using modinfo
        $instances = $this->modinfo->get_instances();
        if (empty($instances[$itemmodule][$iteminstance])) {
            return null;
        }
        $cm = $instances[$itemmodule][$iteminstance];

        // Do not add link if activity is not visible to the current user
        if (!$cm->uservisible) {
            return null;
        }

        if (!array_key_exists($itemmodule, $hasgradephp)) {
            if (file_exists($CFG->dirroot . '/mod/' . $itemmodule . '/grade.php')) {
                $hasgradephp[$itemmodule] = true;
            } else {
                $hasgradephp[$itemmodule] = false;
            }
        }

        // If module has grade.php, link to that, otherwise view.php
        if ($hasgradephp[$itemmodule]) {
            $args = array('id' => $cm->id, 'itemnumber' => $itemnumber);
            if (isset($element['object']->userid)) {
                $args['userid'] = $element['object']->userid;
            }
            return new moodle_url('/mod/' . $itemmodule . '/grade.php', $args);
        } else {
            return new moodle_url('/mod/' . $itemmodule . '/view.php', array('id' => $cm->id));
        }
    }
    /**
     * Returns a specific Grade Item
     *
     * @param int $itemid The ID of the grade_item object
     *
     * @return grade_item
     * TODO: check if we really need this function, I think we do
     */
    public function get_item($itemid) {
        if (array_key_exists($itemid, $this->items)) {
            return $this->items[$itemid];
        } else {
            return false;
        }
    }

    /**
     * Parses the array in search of a given eid and returns a element object with
     * information about the element it has found.
     * @param int $id Gradetree item ID
     * @return object element
     * LAE we don't use the standard tree (somebody say, "INEFFICIENT!!") so need local function
     */
/*
    public function locate_element($id) {
        // it is a category or item
        foreach ($this->levelitems as $key=>$element) {
            if ($key == $id) {
                return $element;
            }
        }

        return null;
    }
*/
    
	function accuratepointsprelimcalculation ($itemid, $type, $grade) {
	    $gradeval = $grade->finalgrade;
		if ($type !== 'course' && $type !== 'courseitem') {
		    $parent_id = $this->parents[$itemid]->parent_id; // the parent record contains an id field pointing to its parent, the key on the parent record is the item itself to allow lookup
	    }
	    if ($type == 'categoryitem' || $type == 'courseitem' || $type == 'category' || $type == 'course') { // categoryitems or courseitems
			if (isset($this->parents[$itemid]->cat_item)) { // if category or course has marked grades in it
		        // set up variables that are used in this inserted limit_rules scrap
		        $current_cat = $this->items[$itemid]->get_item_category(); // need category settings like drop-low or keep-high
		
		        // copy cat_max to a variable we can send along with this limit_item
		        $grade_maxes = $this->parents[$itemid]->cat_max; // range of earnable points for marked items
		        $grade_values = $this->parents[$itemid]->cat_item; // earned points
		        $this->limit_item($current_cat,$this->items,$this->parents[$itemid]->pctg,$this->parents[$itemid]->cat_max, $this->parents[$itemid]->cat_item); // TODO: test this with some drop-low conditions to see if we can still ascertain the weighted grade
		    }
	    } else { // items
	        if ($grade->grade_item->aggregationcoef > 0 && $this->parents[$itemid]->parent_agg != GRADE_AGGREGATE_WEIGHTED_MEAN) {
	            $this->parents[$parent_id]->excredit += $gradeval;
	        } else {
	            $this->parents[$parent_id]->cat_item[$itemid] = $gradeval;
	            $this->parents[$parent_id]->cat_max[$itemid] = $grade->grade_item->grademax;
	            if ($this->parents[$itemid]->parent_agg == GRADE_AGGREGATE_WEIGHTED_MEAN) {
	                $this->parents[$parent_id]->agg_coef[$itemid] = $grade->grade_item->aggregationcoef;
	            }
	            $this->parents[$parent_id]->pctg[$itemid] = $gradeval / $grade->grade_item->grademax;
	        }
	    }
	    if (!isset($grade_values)) {
	        // do nothing
	    } else if ($type == 'category' || $type == 'categoryitem') {
	        // if we have a point value or if viewing an empty report
	        // if (isset($gradeval) || $this->user->id == $USER->id) {
	                            
	        // preparing to deal with extra credit which would have an agg_coef of 1 if not WM
	        if ($grade->grade_item->aggregationcoef > 0 && $this->parents[$itemid]->parent_agg != GRADE_AGGREGATE_WEIGHTED_MEAN) {
	            $this->parents[$parent_id]->excredit += array_sum($grade_values);
	        } else {
	            // continue adding to the array under the parent object
	            $this->parents[$parent_id]->cat_item[$itemid] = array_sum($grade_values) + $this->parents[$itemid]->excredit; // earned points
	            $this->parents[$parent_id]->cat_max[$itemid] = array_sum($grade_maxes); // range of earnable points
	            if ($this->parents[$itemid]->parent_agg == GRADE_AGGREGATE_WEIGHTED_MEAN) {
	                $this->parents[$parent_id]->agg_coef[$itemid] = $grade->grade_item->aggregationcoef; // store this regardless of parent aggtype
	            }
	            if ($current_cat->aggregation == GRADE_AGGREGATE_WEIGHTED_MEAN) {
	                // determine the weighted grade culminating in a percentage value
	   	            $weight_normalizer = 1 / max(1,array_sum($this->parents[$itemid]->agg_coef)); // adjust all weights in a container so their sum equals 100
	                $weighted_percentage = 0;
	                foreach ($this->parents[$itemid]->pctg as $key=>$pctg) {
		    			// the previously calculated percentage (which might already be weighted) times the normalizer * the weight
		    			$weighted_percentage += $pctg*$weight_normalizer*$this->parents[$itemid]->agg_coef[$key];
	                }
	                $this->parents[$parent_id]->pctg[$itemid]= $weighted_percentage;
	            } else {
	                $this->parents[$parent_id]->pctg[$itemid] = (array_sum($grade_values) + $this->parents[$itemid]->excredit) / array_sum($grade_maxes);
	            }
	        }
//	    } else if (!isset($this->parents[$itemid]->agg_coef)) { // TODO: when does this happen?
//	        $this->parents[$itemid]->coursepctg = 1;
	    } else { // calculate up the weighted percentage for the course item
//		    $current_cat = $this->items[$itemid]->get_item_category(); // need category settings like drop-low or keep-high
	        if ($current_cat->aggregation == GRADE_AGGREGATE_WEIGHTED_MEAN) {
//		    if ($this->parents[$itemid]->parent_agg == GRADE_AGGREGATE_WEIGHTED_MEAN) {
	             $weight_normalizer = 0;
	             $weighted_percentage = 0;
	             foreach ($this->parents[$itemid]->agg_coef as $key=>$value) {
	//	               	        foreach ($this->parents[$itemid]->pctg as $key=>$pctg) {
	                 // the previously calculated percentage (which might already be weighted) times the normalizer * the weight
	                 $weight_normalizer += $value;
	                 if (isset($this->parents[$itemid]->pctg[$key])) {
	                      $weighted_percentage += $this->parents[$itemid]->pctg[$key]*$value;
	                 }
	             }
	             $weight_normalizer = 1 / $weight_normalizer;
	             $weighted_percentage *= $weight_normalizer;
	             $this->parents[$itemid]->coursepctg = $weighted_percentage;
	         } else {
	             $this->parents[$itemid]->coursepctg = (array_sum($grade_values) + $this->parents[$itemid]->excredit) / array_sum($grade_maxes);
	         } 
	    }
	}
    
	public function accuratepointsfinalvalues($itemid, &$item, $type, $parent_id, $gradeval, $gradedisplaytype = GRADE_DISPLAY_TYPE_REAL) {
		$current_cat = $this->items[$itemid]->get_item_category(); // need category settings like drop-low or keep-high
        switch ($gradedisplaytype) {
       	    case GRADE_DISPLAY_TYPE_REAL:
//       	        $this->limit_item($current_cat,$this->items,$this->parents[$itemid]->pctg,$this->parents[$itemid]->cat_max,$this->parents[$itemid]->cat_item);
       	        $grade_values = $this->parents[$itemid]->cat_item;
       	        $grade_maxes = $this->parents[$itemid]->cat_max;
       	        $grade_pctg = $this->parents[$itemid]->pctg;
       	        $gradeval = array_sum($grade_values) + $this->parents[$itemid]->excredit;
           		$item->grademax = array_sum($grade_maxes);
                break;
       	    case GRADE_DISPLAY_TYPE_PERCENTAGE:
       	        $gradeval = $type == 'category' ? $this->parents[$parent_id]->pctg[$itemid] : $this->parents[$itemid]->coursepctg;
       	        $item->grademax = 1;
       	    case GRADE_DISPLAY_TYPE_LETTER:
       	        break;
       	}
       	return $gradeval;
	}    
	
    /**
     * Return hiding icon for give element
     *
     * @param array  $element An array representing an element in the grade_tree
     * @param object $gpr A grade_plugin_return object
     *
     * @return string
     */
    public function get_zerofill_icon($element, $gpr) {
        global $CFG, $OUTPUT;

        if (!has_capability('moodle/grade:manage', $this->context) and
            !has_capability('moodle/grade:hide', $this->context)) {
            return '';
        }

        $strparams = $this->get_params_for_iconstr($element);
        $strzerofill = get_string('zerofill', 'gradereport_laegrader', $strparams);

        $url = new moodle_url('/grade/report/laegrader/index.php', array('id' => $this->courseid, 'sesskey' => sesskey(), 'action' => 'quickdump'));
        $url = $gpr->add_url_params($url);

        $type = 'zerofill';
        $tooltip = $strzerofill;
        $actiontext = '<img alt="' . $type . '" class="smallicon" title="' . $strzerofill . '" src="' . $CFG->wwwroot . '/grade/report/laegrader/images/zerofill.png" />';
        $url->param('action', 'zerofill');
        $zerofillicon = $OUTPUT->action_link($url, 'text', null, array('class' => 'action-icon', 'onclick'=>'zerofill(' . $element['object']->id . ')'));
		preg_match('/(.*href=")/',$zerofillicon, $matches);
		// sending back an empty href with onclick
		$zerofillicontemp = $matches[0] . '#">' . $actiontext . '</a>';
        return $zerofillicontemp;
    }

    public function get_clearoverrides_icon($element, $gpr) {
        global $CFG, $OUTPUT;

        if (!has_capability('moodle/grade:manage', $this->context) and
            !has_capability('moodle/grade:hide', $this->context)) {
            return '';
        }

        $strparams = $this->get_params_for_iconstr($element);
        $strclearoverrides = get_string('clearoverrides', 'gradereport_laegrader', $strparams);

        $url = new moodle_url('/grade/report/laegrader/index.php', array('id' => $this->courseid, 'sesskey' => sesskey(), 'action' => 'clearoverrides', 'itemid'=>$element['object']->id));

        $type = 'clearoverrides';
        $tooltip = $strclearoverrides;
        $actiontext = '<img alt="' . $type . '" class="smallicon" title="' . $strclearoverrides . '" src="' . $CFG->wwwroot . '/grade/report/laegrader/images/clearoverrides.gif" />';
        $clearoverrides = $OUTPUT->action_link($url, $actiontext, null, array('class' => 'action-icon'));

        return $clearoverrides;
    }

    public function get_changedisplay_icon($element) {
        global $CFG, $OUTPUT;

        if (!has_capability('moodle/grade:manage', $this->context) and
            !has_capability('moodle/grade:hide', $this->context)) {
            return '';
        }

        $strparams = $this->get_params_for_iconstr($element);
        $strchangedisplay = get_string('changedisplay', 'gradereport_laegrader', $strparams);

        $url = new moodle_url('/grade/report/laegrader/index.php', array('id' => $this->courseid, 'sesskey' => sesskey(), 'action' => 'changedisplay', 'itemid'=>$element['object']->id));

        $type = 'changedisplay';
        $tooltip = $strchangedisplay;
        $actiontext = '<img alt="' . $type . ' title="' . $strchangedisplay . '" src="' . $CFG->wwwroot . '/grade/report/laegrader/images/changedisplay.png" />';
        $changedisplay = $OUTPUT->action_link($url, $actiontext, null, array('class' => 'action-icon'));

        return $changedisplay;
    }

    function limit_item($this_cat,$items,&$grade_pctg,&$grade_maxes,&$grade_values) {
    	$extraused = $this_cat->is_extracredit_used();
    	if (!empty($this_cat->droplow)) {
    		asort($grade_pctg, SORT_NUMERIC);
    		$dropped = 0;
    		foreach ($grade_pctg as $itemid=>$pctg) {
    			if ($dropped < $this_cat->droplow) {
    				if ($extraused and $items[$itemid]->aggregationcoef > 0) {
    					// no drop low for extra credits
    				} else {
    					unset($grade_pctg[$itemid]);
    					unset($grade_values[$itemid]);
    					unset($grade_maxes[$itemid]);
    					$dropped++;
    				}
    			} else {
    				// we have dropped enough
    				break;
    			}
    		}
    	} else if (!empty($this_cat->keephigh)) {
    		arsort($grade_pctg, SORT_NUMERIC);
    		$kept = 0;
    		foreach ($grade_pctg as $itemid=>$pctg) {
      			if ($extraused and $items[$itemid]->aggregationcoef > 0) {
    				// we keep all extra credits
    			} else if ($kept < $this_cat->keephigh) {
    				$kept++;
    			} else {
    				unset($grade_pctg[$itemid]);
    				unset($grade_values[$itemid]);
    				unset($grade_maxes[$itemid]);
    			}
    		}
    	}
    }

    /*
     * LAE keeps track of the parents of items in case we need to actually compute accurate point totals instead of everything = 100 points (same as percent, duh)
     * Recursive function for filling gtree-parents array keyed on item-id with elements id=itemid of parent, agg=aggtype of item
     * accumulates $items->max_earnable with either the child's max_earnable or (in the case of a non-category) grademx
     * @param array &$parents - what is being built in order to allow accurate accumulation of child elements' grademaxes (and earned grades) into the container element (category or course)
     * @param array &$items - the array of grade item objects
     * @param array $cats - array of category information used to get the actualy itemid for the child category cuz its not otherwise in item object
     * @param object $element - level element which allows a top down approach to a bottom up procedure (i.e., find the children and store their accumulated values to the parents)
     * @param boolean $accuratetotals - if user wants to see accurate point totals for their gradebook
     * @param boolean $alltotals -- this is passed by the user report because max_earnable can only be figured on graded items
     */
    function fill_parents($element, $idnumber, $showtotalsifcontainhidden = 0) {
        foreach($element['children'] as $sortorder=>$child) {
            // skip items that are only for another group than the one being considered
/*            if (array_key_exists($child['object']->id, $this->modx)) {
                continue;
            }
*/
            switch ($child['type']) {
                case 'courseitem':
                case 'categoryitem':
                    continue 2;
                case 'category':
                    $childid = $this->cats[$child['object']->id]->id;
                    break;
                default:
                    $childid = substr($child['eid'],1,8);
            }
            if (!isset($this->parents[$childid])) {
                $this->parents[$childid] = new stdClass();
                $this->parents[$childid]->cat_item = array();
                $this->parents[$childid]->cat_max = array();
                $this->parents[$childid]->pctg = array();
                $this->parents[$childid]->agg_coef = array();
                $this->parents[$childid]->parent_id = $idnumber;
                $this->parents[$childid]->parent_agg = $element['object']->aggregation;
            }
            if (! empty($child['children'])) {
                $this->fill_parents($child, $childid, $showtotalsifcontainhidden);
            }
            // accumulate max scores for parent
    //        if ($accuratetotals && $alltotals) {
            // this line needs to determine whether to include hidden items
           	if ((!$child['object']->is_hidden() || $showtotalsifcontainhidden == GRADE_REPORT_SHOW_REAL_TOTAL_IF_CONTAINS_HIDDEN) // either its not hidden or the hiding setting allows it to be calculated into the total
           	        && isset($this->parents[$childid]->parent_id) // the parent of this item needs to be set
                    && ((isset($this->items[$childid]->aggregationcoef) && $this->items[$childid]->aggregationcoef != 1) // isn't an extra credit item -- has a weight and the weight isn't 1
                    || (isset($this->parents[$childid]->parent_agg) && $this->parents[$childid]->parent_agg == GRADE_AGGREGATE_WEIGHTED_MEAN))) { // or has a weight but in a category using WM
                $this->items[$idnumber]->max_earnable += (isset($this->items[$childid]->max_earnable)) ? $this->items[$childid]->max_earnable : $this->items[$childid]->grademax;
            }
        }
        return;
    }

    /*
     * LAE need in order to get hold of the category name for the categoryitem structure without using the upper level category which we don't use
    */
    function fill_cats() {
        foreach($this->items as $key=>$item) {
            if (!$item->categoryid) {
                $this->cats[$item->iteminstance] = $item;
            }
        }
    }
}

function is_percentage($gradestr = null) {
    return (substr(trim($gradestr),-1,1) == '%') ? true : false;
}

?>