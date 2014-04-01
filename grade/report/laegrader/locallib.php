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
        if (!$category_grade_last) {
        	$this->levelitems = array_reverse($this->levelitems, true);
        }
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
				$header .= get_string('coursetotal', 'gradereport_laegrader');
				break;
			case 'categoryitem':
				$header .= get_string('categorytotal', 'gradereport_laegrader');
			default:
		 		$header .= $catname . ' ';
		}
		if ($element['object']->aggregationcoef > 1) {
		    $header .= ' W=' . format_float($element['object']->aggregationcoef,1, true, true) . '% ';
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

	function accuratepointsprelimcalculation ($grades, $usetargets = false) {
	    // plugin target grade for final

	    foreach ($grades as $grade) {
	    	$grade->excredit = 0;
	    }
		foreach ($this->levelitems as $itemid => $item) {

		    $type = $item['type'];
//		    $itemid = $item['object']->id;
		    $grade = $grades[$itemid];
		    $grade_values = array();
		    $grade_maxes = array();

		    // get the id of this grade's parent
			if ($type !== 'course' && $type !== 'courseitem') {
			    $parent_id = $this->parents[$itemid]->parent_id; // the parent record contains an id field pointing to its parent, the key on the parent record is the item itself to allow lookup
		    }

		    // assign array values to grade_values and grade_maxes for later use
		    if ($type == 'categoryitem' || $type == 'courseitem' || $type == 'category' || $type == 'course') { // categoryitems or courseitems
				if (isset($grades[$itemid]->cat_item)) { // if category or course has marked grades in it
			        // set up variables that are used in this inserted limit_rules scrap
			        $this->cat = $this->items[$itemid]->get_item_category(); // need category settings like drop-low or keep-high

			        // copy cat_max to a variable we can send along with this limit_item
			        $this->limit_item($itemid, $grades); // TODO: test this with some drop-low conditions to see if we can still ascertain the weighted grade
			        $grade_maxes = $grades[$itemid]->cat_max; // range of earnable points for marked items
			        $grade_values = $grades[$itemid]->cat_item; // earned points
				}
		    } else { // items
				if ($usetargets && is_null($grade->finalgrade)) {
			    	$gradeval = $this->items[$itemid]->target;
			    } else {
					$gradeval = $grade->finalgrade;
			    }
                if ($this->items[$itemid]->is_hidden() && $this->showtotalsifcontainhidden == GRADE_REPORT_SHOW_TOTAL_IF_CONTAINS_HIDDEN) {
                    continue;
                } else if ($grade->grade_item->aggregationcoef > 0 && $this->parents[$itemid]->parent_agg != GRADE_AGGREGATE_WEIGHTED_MEAN) {
		            $grades[$parent_id]->excredit += $gradeval;
		    	} else if (!isset($gradeval)) {
		    		continue;
		    	} else {
		            // fill parent's array with information from this grade
		        	$grades[$parent_id]->cat_item[$itemid] = $gradeval;
		            $grades[$parent_id]->cat_max[$itemid] = $grade->grade_item->grademax;
		            if ($this->parents[$itemid]->parent_agg == GRADE_AGGREGATE_WEIGHTED_MEAN) {
		                $grades[$parent_id]->agg_coef[$itemid] = $grade->grade_item->aggregationcoef;
		            }
		            $grades[$parent_id]->pctg[$itemid] = $gradeval / $grade->grade_item->grademax;
		        }
		    }

		    if (!isset($grade_values) || sizeof($grade_values) == 0 || $type === 'item') {
		        // do nothing
		    } else if ($type == 'category' || $type == 'categoryitem') {
		        // if we have a point value or if viewing an empty report
		        // if (isset($gradeval) || $this->user->id == $USER->id) {

		        // preparing to deal with extra credit which would have an agg_coef of 1 if not WM
		        if ($grade->grade_item->aggregationcoef > 0 && $this->parents[$itemid]->parent_agg != GRADE_AGGREGATE_WEIGHTED_MEAN) {
		            $grades[$parent_id]->excredit += array_sum($grade_values);
		        } else {
		            // continue adding to the array under the parent object
		            $grades[$parent_id]->cat_item[$itemid] = array_sum($grade_values) + $grades[$itemid]->excredit; // earned points
		            $grades[$parent_id]->cat_max[$itemid] = array_sum($grade_maxes); // range of earnable points
		            if ($this->parents[$itemid]->parent_agg == GRADE_AGGREGATE_WEIGHTED_MEAN) {
		                $grades[$parent_id]->agg_coef[$itemid] = $grade->grade_item->aggregationcoef; // store this regardless of parent aggtype
		            }
		            if ($this->cat->aggregation == GRADE_AGGREGATE_WEIGHTED_MEAN) {
		                // determine the weighted grade culminating in a percentage value
		   	            $weight_normalizer = 1 / max(1,array_sum($grades[$itemid]->agg_coef)); // adjust all weights in a container so their sum equals 100
		                $weighted_percentage = 0;
		                foreach ($grades[$itemid]->pctg as $key=>$pctg) {
			    			// the previously calculated percentage (which might already be weighted) times the normalizer * the weight
			    			if (isset($grades[$itemid]->agg_coef[$key]))  {
    		                	$weighted_percentage += $pctg*$weight_normalizer*$grades[$itemid]->agg_coef[$key];
			    			}
		                }
		                $grades[$parent_id]->pctg[$itemid]= $weighted_percentage;
	//	            } else if (sizeof($grade_maxes)) {
	//	            	// skip
		            } else if ($type == 'course' || $type == 'courseitem') {
		            	// skip
		            } else {
		                $grades[$parent_id]->pctg[$itemid] = (array_sum($grade_values) + $grades[$itemid]->excredit) / array_sum($grade_maxes);
		            }
		        }
		        $this->items[$itemid]->grademax = array_sum($grade_maxes);
		    } else { // calculate up the weighted percentage for the course item
		        if ($this->cat->aggregation == GRADE_AGGREGATE_WEIGHTED_MEAN) {
		             $weight_normalizer = 0;
		             $weighted_percentage = 0;
		             foreach ($grades[$itemid]->agg_coef as $key=>$value) {
		                 if (isset($grades[$itemid]->pctg[$key])) {
		                 	$weight_normalizer += $value;
		                 	$weighted_percentage += $grades[$itemid]->pctg[$key]*$value;
		                 }
		             }
		             if ($weight_normalizer != 0) {
			             $weight_normalizer = 1 / $weight_normalizer;
		             }
		             $weighted_percentage *= $weight_normalizer;
		             $grades[$itemid]->coursepctg = $weighted_percentage;
		         } else {
		             $grades[$itemid]->coursepctg = (array_sum($grade_values) + $grades[$itemid]->excredit) / array_sum($grade_maxes);
		         }
		        $this->items[$itemid]->grademax = array_sum($grade_maxes);
		    }
	    }
	}

	public function accuratepointsfinalvalues(&$grades, $itemid, &$item, $type, $parent_id, $gradedisplaytype = GRADE_DISPLAY_TYPE_REAL) {
		$current_cat = $this->items[$itemid]->get_item_category(); // need category settings like drop-low or keep-high
	    if (!isset($grades[$itemid]->cat_item)) {
	        $gradeval = 0;
	    } else {
			switch ($gradedisplaytype) {
	       	    case GRADE_DISPLAY_TYPE_REAL:
	       	    	$grade_values = $grades[$itemid]->cat_item;
	       	        $grade_maxes = $grades[$itemid]->cat_max;
	       	        $grade_pctg = $grades[$itemid]->pctg;
	       	        $gradeval = array_sum($grade_values) + $grades[$itemid]->excredit;
	           		$item->grademax = array_sum($grade_maxes);
	                break;
	       	    case GRADE_DISPLAY_TYPE_LETTER:
	            case GRADE_DISPLAY_TYPE_PERCENTAGE:
//	       	        $gradeval = $type == 'category' ? array_sum($grades[$itemid]->pctg) / sizeof($grades[$itemid]->pctg) : $grades[$itemid]->coursepctg;
	            	$gradeval = $type == 'category' ? $grades[$parent_id]->pctg[$itemid] : $grades[$itemid]->coursepctg;
	       	        $item->grademax = 1;
	       	        break;
	       	}
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

        if ($element['type'] == 'item') {
            $url = new moodle_url('/grade/report/laegrader/index.php', array('id' => $this->courseid, 'sesskey' => sesskey(), 'action' => 'clearoverrides', 'itemid'=>$element['object']->id));
        } else {
            $element['object']->load_grade_item();
        	$url = new moodle_url('/grade/report/laegrader/index.php', array('id' => $this->courseid, 'sesskey' => sesskey(), 'action' => 'clearoverrides', 'itemid'=>$element['object']->grade_item->id));
        }

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
		$actiontext = '<img alt="' . $type . '" title="' . $strchangedisplay . '" src="' . $CFG->wwwroot . '/grade/report/laegrader/images/changedisplay.png" />';
        $changedisplay = $OUTPUT->action_link($url, $actiontext, null, array('class' => 'action-icon'));

        return $changedisplay;
    }

    function limit_item($itemid, $grades, $unsetgrades = true) {
    	$extraused = $this->cat->is_extracredit_used();
    	if (!empty($this->cat->droplow)) {
    		asort($grades[$itemid]->pctg, SORT_NUMERIC);
    		$dropped = 0;
    		foreach ($grades[$itemid]->pctg as $childid=>$pctg) {
    			if ($dropped < $this->cat->droplow) {
    				if (is_null($pctg)) {
    					continue;
    				} else if ($extraused && $this->cat->aggregation == GRADE_AGGREGATE_WEIGHTED_MEAN2 && $this->items[$childid]->aggregationcoef > 0) {
    					// no drop low for extra credits
    				} else {
    					if ($unsetgrades) {
	    					unset($grades[$itemid]->pctg[$childid]);
	    					unset($grades[$itemid]->cat_item[$childid]);
	    					unset($grades[$itemid]->cat_max[$childid]);
	    					unset($grades[$itemid]->agg_coef[$childid]);
    					}
                        $this->items[$childid]->weight = 0; // need to set the weight here because calc_weights doesn't consider drop or keep conditions
    					$dropped++;
    				}
    			} else {
    				// we have dropped enough
    				break;
    			}
    		}
    	} else if (!empty($this->cat->keephigh)) {
    		arsort($grades[$itemid]->pctg, SORT_NUMERIC);
    		$kept = 0;
    		foreach ($grades[$itemid]->pctg as $childid=>$pctg) {
                if (is_null($pctg)) {
                    continue;
    		    } else if ($extraused && $this->cat->aggregation == GRADE_AGGREGATE_WEIGHTED_MEAN2 && $this->items[$childid]->aggregationcoef > 0) {
       				// we keep all extra credits
    			} else if ($kept < $this->cat->keephigh) {
    				$kept++;
    			} else {
                    if ($unsetgrades) {
	    				unset($grades[$itemid]->pctg[$childid]);
	    				unset($grades[$itemid]->cat_item[$childid]);
	    				unset($grades[$itemid]->cat_max[$childid]);
	    				unset($grades[$itemid]->agg_coef[$childid]);
                    }
	    			$this->items[$childid]->weight = 0; // need to set the weight here because calc_weights doesn't consider drop or keep conditions
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
	            $this->parents[$childid]->excredit = 0;
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

    /*
     * TODO: take into account hidden grades and setting of show total excluding hidden items
     */
    function calc_weights_recursive(&$element, &$grades, $target_letter = null) {
        /// Recursively iterate through all child elements
        switch ($element['type']) {
            case 'grade_item':
            case 'item':
                $elementid = $element['object']->id;
                break;
            case 'categoryitem':
            case 'courseitem':
                return;
            default:
                $elementid = $element['object']->grade_item->id;
                break;
        }

        // determine container (course or category weight) calced in previous recursion
        $container_weight = 0;
        if (isset($element['object']->grade_item) && $element['object']->grade_item->itemtype == 'course') {
            $container_weight = 100; // since we're starting from the top down this is the only weight we can know for sure at the outset
            $this->items[$elementid]->weight = 100;
        } else if (isset($this->items[$elementid]->weight) && $this->items[$elementid]->weight == 0) {
        } else {
            $container_weight = $this->items[$elementid]->weight; // this will have been determined in previous iterations
        }

        // build the weight for category or course, we don't go any further if not a container
        if (isset($element['children'])) {
            $combined_weight = 0;
            $contribution = 0;
            $missing_weight = 0;
            if (!isset($this->items[$elementid]->max_earnable)) {
                $this->items[$elementid]->max_earnable = 0;
            }

            // determine how much non-normalized weight we already have in the category and how much might be missing (in case we have a target grade condition)
            // also determine the relative contibution so far
            foreach ($element['children'] as $key => $child) {
                if ($child['object'] instanceof grade_category) {
                    $child['object']->load_grade_item();
                    $id = $child['object']->grade_item->id;
                } else {
                    $id = $child['object']->id;
                }

                // check to see if this is a category with no visible children
                $exitval = false;
                if ($child['type'] === 'category' && $this->showtotalsifcontainhidden !== GRADE_REPORT_SHOW_REAL_TOTAL_IF_CONTAINS_HIDDEN) {
                	foreach ($child['children'] as $key => $grandchild) {
                		if ($grandchild['type'] !== 'categoryitem' && !$grandchild['object']->is_hidden()) {
                			$exitval = true;
                			break;
                		}
                	}
                	if (!$exitval) {
                    	$this->emptycats[$id] = 'empty';
                		continue; // if all are hidden then don't count this into calculations
                	}
                }

                if ($child['type'] === 'categoryitem' || $child['type'] === 'courseitem') {
                    continue; // do nothing with these types of elements
                } else if ($this->items[$id]->is_hidden() && $target_letter) { // either its not hidden or the hiding setting allows it to be calculated into the total
                    continue;
                } else if ($this->items[$id]->is_hidden() && $this->showtotalsifcontainhidden !== GRADE_REPORT_SHOW_REAL_TOTAL_IF_CONTAINS_HIDDEN) { // either its not hidden or the hiding setting allows it to be calculated into the total
                    continue;
                } else if (!isset($grades[$id]->finalgrade) && $element['object']->aggregation == GRADE_AGGREGATE_WEIGHTED_MEAN) { // if usetargets falls to the remainder even when empty
                    $missing_weight += $this->items[$id]->aggregationcoef;
                    $this->emptygrades[$id] = $this->items[$id];
                } else if (!isset($grades[$id]->finalgrade)) { // implied SWM
                    $missing_weight += $this->items[$id]->grademax;
                    $this->emptygrades[$id] = $this->items[$id];
                } else if ($child['type'] === 'categoryitem' || $child['type'] === 'courseitem') {
                    continue; // do nothing with these types of elements
                } else if (isset($this->items[$id]->weight) && $this->items[$id]->weight == 0) { // has been dropped or not kept
                    continue;
                } else if ($element['object']->aggregation == GRADE_AGGREGATE_WEIGHTED_MEAN) {
                    $combined_weight += $this->items[$id]->aggregationcoef;
                    if (isset($grades[$id]->cat_item)) {
                        $contribution += $this->items[$id]->aggregationcoef * array_sum($grades[$id]->cat_item) / array_sum($grades[$id]->cat_max);
                    } else {
                        $contribution += $this->items[$id]->aggregationcoef * $grades[$id]->finalgrade / $this->items[$id]->grademax;
                    }
                } else if ($element['object']->aggregation == GRADE_AGGREGATE_WEIGHTED_MEAN2 && $this->items[$id]->aggregationcoef == 1) { // extra credit
                    continue;
                } else if ($element['object']->aggregation == GRADE_AGGREGATE_WEIGHTED_MEAN2) {
                    if (isset($grades[$id]->cat_item)) {
                        $combined_weight += array_sum($this->items[$id]->cat_max); // TODO: fix this to use cat_max or something
                    	$contribution += array_sum($grades[$id]->cat_item) / array_sum($grades[$id]->cat_max);
                    } else {
                        $combined_weight += $this->items[$id]->grademax; // TODO: fix this to use cat_max or something
                    	$contribution += $grades[$id]->finalgrade / $this->items[$id]->grademax;
                    }
                } else {
                    $combined_weight = 0;
                }
            }

            // how much potential grades are left to be earned
            // normalizer adjust the weights to be equal to 100
            // weight adjuster is multiplied by the child's weight to achieve the right percentage of the container weight
            if ($combined_weight == 0 && $missing_weight == 0) {
                $normalizer = 1;
                $weight_adjuster = 1;
            } else {
                $normalizer = 100 / ($combined_weight + $missing_weight);
                $weight_adjuster = $container_weight / $normalizer;
            }

            // go back through and apply normalizer to have weights add up to container
            foreach ($element['children'] as $key=>$child) {
                if ($child['object'] instanceof grade_category) {
                    $id = $child['object']->grade_item->id;
                } else {
                    $id = $child['object']->id;
                }
                if ($child['type'] === 'categoryitem' || $child['type'] === 'courseitem') {
                    continue; // do nothing with these types of elements
                } else if (array_key_exists($id, $this->emptycats)) {
                    $this->items[$id]->weight = 0;
                } else if (!isset($grades[$id]->finalgrade) && !$target_letter) { // empty grade no targets
                    $this->items[$id]->weight = 0;
                } else if (isset($this->items[$id]->weight) && $this->items[$id]->weight == 0) {
                    // do nothing, weight has been set by drop or keep function
                } else if ($element['object']->aggregation == GRADE_AGGREGATE_WEIGHTED_MEAN) {
                    $this->items[$id]->weight = $this->items[$id]->aggregationcoef * $normalizer;
                    if ($this->items[$id]->weight == 0) {
                    	unset($grades[$elementid]->cat_max[$id]);
                    }
                } else if ($combined_weight + $missing_weight == 0) {
                    $weight_adjuster = 1;
                    $this->items[$id]->weight = $container_weight * $weight_adjuster; // TODO: fix this to use cat_max or something
                } else if ($element['object']->aggregation == GRADE_AGGREGATE_WEIGHTED_MEAN2) {
                    $weight_adjuster = $this->items[$id]->grademax / ($combined_weight + $missing_weight);
                    $this->items[$id]->weight = $container_weight * $weight_adjuster; // TODO: fix this to use cat_max or something
                }
            }

            foreach ($element['children'] as $key=>$child) {
                if (isset($child['children'])) {
                    $this->calc_weights_recursive($child, $grades, $target_letter);
                }
            }

        }
        // set maxtarget
        if ($element['object']->grade_item->itemtype === 'course') { // if target_letter normalizer should come to 1
            // set the maximum grade that can be achieved
            $normalizer = 100 / ($combined_weight + $missing_weight);
            $combined_weight *= $normalizer;
            $missing_weight *= $normalizer;
            $contribution *= $normalizer;
            $this->maxtarget = $missing_weight + $contribution;
            if (!is_null($target_letter)) {
                $tobegotten = $target_letter - $contribution;
                // calculate up the multiplier for all weighted * grademaxes leftover
                $gradescaler = $tobegotten / $missing_weight;

                // work from bottom up to reaggregate with targets
                $levelsreverse = array_reverse($this->levels);
                foreach ($levelsreverse[0] as $key => $item) {
                    $id = $item['object']->id;
                    if ($item['object']->itemtype !== 'category') {
	                    $cat = $this->cats[$item['object']->categoryid]->id; // grade item id for this items category
	                    if (array_key_exists($id, $this->emptygrades)) {
	                        $this->items[$id]->target = $item['object']->grademax * $gradescaler;
	                        $grades[$cat]->cat_item[$id] = $this->items[$id]->target; // store target value to container's cat_item for later
	                        $grades[$cat]->cat_max[$id] = $this->items[$id]->grademax;
	                        $grades[$cat]->pctg[$id] = $this->items[$id]->target / $this->items[$id]->grademax;
	                        if (!isset($this->items[$cat]->target)) {
	                            $this->items[$cat]->target = 0;
	                        }
	                        $this->items[$cat]->target += $this->items[$id]->target;
	                    }
                    }
                }
                unset($levelsreverse[0]); // no longer want the items level
                unset($levelsreverse[sizeof($levelsreverse)]); // don't want the course item to have to go through the loop either
                foreach ($levelsreverse as $levels) {
                    foreach ($levels as $key => $item) {
                        if ($item['type'] !== 'categoryitem' && $item['type'] !== 'courseitem') {
	                    	$id = $item['object']->grade_item->id;
                            $cat = $this->cats[$item['object']->parent]->id; // grade item id for this catagory's container
	                    	if (isset($this->items[$id]->target)) {
	                            if (!isset($this->items[$cat]->target)) {
	                                $this->items[$cat]->target = 0;
	                            }
	                            $this->items[$cat]->target += $this->items[$id]->target;
                                $grades[$cat]->cat_item[$id] = array_sum($grades[$id]->cat_item);
                                $grades[$cat]->cat_max[$id] = array_sum($grades[$id]->cat_max);
                                $grades[$cat]->pctg[$id] = array_sum($grades[$id]->cat_item) / array_sum($grades[$id]->cat_max);
	                        }
                        }
                    }
                }

                // finally the course target needs setting
                $this->gtree->items[$elementid]->target = array_sum($grades[$elementid]->cat_item) * $gradescaler;
                $grades[$elementid]->coursepctg = 0;
                foreach ($grades[$elementid]->pctg as $id => $child) {
                    $grades[$elementid]->coursepctg += $grades[$elementid]->pctg[$id] * $this->items[$id]->weight / 100;

                }
            }
        }
    }

}




function is_percentage($gradestr = null) {
    return (substr(trim($gradestr),-1,1) == '%') ? true : false;
}

?>