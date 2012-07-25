<?php
//Modified from /lib/questionlib.php my W. F. Junkin 2012.02.14 for IPAL to avoid version updaes from impacting IPAL
class ipal_question_edit_contexts {

    public static $caps = array(
        'editq' => array('moodle/question:add',
            'moodle/question:editmine',
            'moodle/question:editall',
            'moodle/question:viewmine',
            'moodle/question:viewall',
            'moodle/question:usemine',
            'moodle/question:useall',
            'moodle/question:movemine',
            'moodle/question:moveall'),
        'questions'=>array('moodle/question:add',
            'moodle/question:editmine',
            'moodle/question:editall',
            'moodle/question:viewmine',
            'moodle/question:viewall',
            'moodle/question:movemine',
            'moodle/question:moveall'),
        'categories'=>array('moodle/question:managecategory'),
        'import'=>array('moodle/question:add'),
        'export'=>array('moodle/question:viewall', 'moodle/question:viewmine'));

    protected $allcontexts;

    /**
     * @param current context
     */
    public function __construct($thiscontext) {
        $pcontextids = get_parent_contexts($thiscontext);
        $contexts = array($thiscontext);
        foreach ($pcontextids as $pcontextid) {
            $contexts[] = get_context_instance_by_id($pcontextid);
        }
        $this->allcontexts = $contexts;
    }
    /**
     * @return array all parent contexts
     */
    public function all() {
        return $this->allcontexts;
    }
    /**
     * @return object lowest context which must be either the module or course context
     */
    public function lowest() {
        return $this->allcontexts[0];
    }
    /**
     * @param string $cap capability
     * @return array parent contexts having capability, zero based index
     */
    public function having_cap($cap) {
        $contextswithcap = array();
        foreach ($this->allcontexts as $context) {
            if (has_capability($cap, $context)) {
                $contextswithcap[] = $context;
            }
        }
        return $contextswithcap;
    }
    /**
     * @param array $caps capabilities
     * @return array parent contexts having at least one of $caps, zero based index
     */
    public function having_one_cap($caps) {
        $contextswithacap = array();
        foreach ($this->allcontexts as $context) {
            foreach ($caps as $cap) {
                if (has_capability($cap, $context)) {
                    $contextswithacap[] = $context;
                    break; //done with caps loop
                }
            }
        }
        return $contextswithacap;
    }
    /**
     * @param string $tabname edit tab name
     * @return array parent contexts having at least one of $caps, zero based index
     */
    public function having_one_edit_tab_cap($tabname) {
        return $this->having_one_cap(self::$caps[$tabname]);
    }
    /**
     * Has at least one parent context got the cap $cap?
     *
     * @param string $cap capability
     * @return boolean
     */
    public function have_cap($cap) {
        return (count($this->having_cap($cap)));
    }

    /**
     * Has at least one parent context got one of the caps $caps?
     *
     * @param array $caps capability
     * @return boolean
     */
    public function have_one_cap($caps) {
        foreach ($caps as $cap) {
            if ($this->have_cap($cap)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Has at least one parent context got one of the caps for actions on $tabname
     *
     * @param string $tabname edit tab name
     * @return boolean
     */
    public function have_one_edit_tab_cap($tabname) {
        return $this->have_one_cap(self::$caps[$tabname]);
    }

    /**
     * Throw error if at least one parent context hasn't got the cap $cap
     *
     * @param string $cap capability
     */
    public function require_cap($cap) {
        if (!$this->have_cap($cap)) {
            print_error('nopermissions', '', '', $cap);
        }
    }

    /**
     * Throw error if at least one parent context hasn't got one of the caps $caps
     *
     * @param array $cap capabilities
     */
    public function require_one_cap($caps) {
        if (!$this->have_one_cap($caps)) {
            $capsstring = join($caps, ', ');
            print_error('nopermissions', '', '', $capsstring);
        }
    }

    /**
     * Throw error if at least one parent context hasn't got one of the caps $caps
     *
     * @param string $tabname edit tab name
     */
    public function require_one_edit_tab_cap($tabname) {
        if (!$this->have_one_edit_tab_cap($tabname)) {
            print_error('nopermissions', '', '', 'access question edit tab '.$tabname);
        }
    }
}

/**
 * Gets the default category in the most specific context.
 * If no categories exist yet then default ones are created in all contexts.
 *
 * @param array $contexts  The context objects for this context and all parent contexts.
 * @return object The default category - the category in the course context
 * Modified by W. F. Junkin 2012.02.14 for IPAL so that version updates won't impact IPAL
 */
function ipal_question_make_default_categories($contexts) {
    global $DB;
    static $preferredlevels = array(
        CONTEXT_COURSE => 4,
        CONTEXT_MODULE => 3,
        CONTEXT_COURSECAT => 2,
        CONTEXT_SYSTEM => 1,
    );

    $toreturn = null;
    $preferredness = 0;
    // If it already exists, just return it.
    foreach ($contexts as $key => $context) {
        if (!$exists = $DB->record_exists("question_categories",
                array('contextid' => $context->id))) {
            // Otherwise, we need to make one
            $category = new stdClass();
            $contextname = print_context_name($context, false, true);
            $category->name = get_string('defaultfor', 'question', $contextname);
            $category->info = get_string('defaultinfofor', 'question', $contextname);
            $category->contextid = $context->id;
            $category->parent = 0;
            // By default, all categories get this number, and are sorted alphabetically.
            $category->sortorder = 999;
            $category->stamp = make_unique_id_code();
            $category->id = $DB->insert_record('question_categories', $category);
        } else {
            $category = ipal_question_get_default_category($context->id);
        }
        if ($preferredlevels[$context->contextlevel] > $preferredness && has_any_capability(
                array('moodle/question:usemine', 'moodle/question:useall'), $context)) {
            $toreturn = $category;
            $preferredness = $preferredlevels[$context->contextlevel];
        }
    }

    if (!is_null($toreturn)) {
        $toreturn = clone($toreturn);
    }
    return $toreturn;
}


/**
 * @param integer $contextid a context id.
 * @return object the default question category for that context, or false if none.
 * Modified by W. F. Junkin 2012.02.14 for IPAL to avoid version updates from impacting IPAL
 */
function ipal_question_get_default_category($contextid) {
    global $DB;
    $category = $DB->get_records('question_categories',
            array('contextid' => $contextid), 'id', '*', 0, 1);
    if (!empty($category)) {
        return reset($category);
    } else {
        return false;
    }
}

/**
 * Check capability on category
 *
 * @param mixed $question object or id
 * @param string $cap 'add', 'edit', 'view', 'use', 'move'
 * @param integer $cachecat useful to cache all question records in a category
 * @return boolean this user has the capability $cap for this question $question?
 */
function ipal_question_has_capability_on($question, $cap, $cachecat = -1) {
    global $USER, $DB;

    // these are capabilities on existing questions capabilties are
    //set per category. Each of these has a mine and all version. Append 'mine' and 'all'
    $question_questioncaps = array('edit', 'view', 'use', 'move');
    static $questions = array();
    static $categories = array();
    static $cachedcat = array();
    if ($cachecat != -1 && array_search($cachecat, $cachedcat) === false) {
        $questions += $DB->get_records('question', array('category' => $cachecat));
        $cachedcat[] = $cachecat;
    }
    if (!is_object($question)) {
        if (!isset($questions[$question])) {
            if (!$questions[$question] = $DB->get_record('question',
                    array('id' => $question), 'id,category,createdby')) {
                print_error('questiondoesnotexist', 'question');
            }
        }
        $question = $questions[$question];
    }
    if (empty($question->category)) {
        // This can happen when we have created a fake 'missingtype' question to
        // take the place of a deleted question.
        return false;
    }
    if (!isset($categories[$question->category])) {
        if (!$categories[$question->category] = $DB->get_record('question_categories',
                array('id'=>$question->category))) {
            print_error('invalidcategory', 'quiz');
        }
    }
    $category = $categories[$question->category];
    $context = get_context_instance_by_id($category->contextid);

    if (array_search($cap, $question_questioncaps)!== false) {
        if (!has_capability('moodle/question:' . $cap . 'all', $context)) {
            if ($question->createdby == $USER->id) {
                return has_capability('moodle/question:' . $cap . 'mine', $context);
            } else {
                return false;
            }
        } else {
            return true;
        }
    } else {
        return has_capability('moodle/question:' . $cap, $context);
    }

}

/**
 * Require capability on question.
 */
function ipal_question_require_capability_on($question, $cap) {
    if (!ipal_question_has_capability_on($question, $cap)) {
        print_error('nopermissions', '', '', $cap);
    }
    return true;
}

/**
 * This function should be considered private to the question bank, it is called from
 * question/editlib.php question/contextmoveq.php and a few similar places to to the
 * work of acutally moving questions and associated data. However, callers of this
 * function also have to do other work, which is why you should not call this method
 * directly from outside the questionbank.
 *
 * @param array $questionids of question ids.
 * @param integer $newcategoryid the id of the category to move to.
 */
function ipal_question_move_questions_to_category($questionids, $newcategoryid) {
    global $DB;

    $newcontextid = $DB->get_field('question_categories', 'contextid',
            array('id' => $newcategoryid));
    list($questionidcondition, $params) = $DB->get_in_or_equal($questionids);
    $questions = $DB->get_records_sql("
            SELECT q.id, q.qtype, qc.contextid
              FROM {question} q
              JOIN {question_categories} qc ON q.category = qc.id
             WHERE  q.id $questionidcondition", $params);
    foreach ($questions as $question) {
        if ($newcontextid != $question->contextid) {
            //No need to check qtype
			//ipal_question_bank::get_qtype($question->qtype)->move_files(
            //        $question->id, $question->contextid, $newcontextid);
        }
    }

    // Move the questions themselves.
    $DB->set_field_select('question', 'category', $newcategoryid,
            "id $questionidcondition", $params);

    // Move any subquestions belonging to them.
    $DB->set_field_select('question', 'category', $newcategoryid,
            "parent $questionidcondition", $params);

    // TODO Deal with datasets.

    return true;
}

