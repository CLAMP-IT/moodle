<?php
function ipal_print_question_list($quiz, $pageurl, $allowdelete, $reordertool,
        $quiz_qbanktool, $hasattempts, $defaultcategoryobj) {
    global $USER, $CFG, $DB, $OUTPUT;
    $strorder = get_string('order');
    $strquestionname = get_string('questionname', 'quiz');
    $strgrade = get_string('grade');
    $strremove = get_string('remove', 'quiz');
    $stredit = get_string('edit');
    $strview = get_string('view');
    $straction = get_string('action');
    $strmove = get_string('move');
    $strmoveup = get_string('moveup');
    $strmovedown = get_string('movedown');
    $strsave = get_string('save', 'quiz');
    $strreorderquestions = get_string('reorderquestions', 'quiz');

    $strselectall = get_string('selectall', 'quiz');
    $strselectnone = get_string('selectnone', 'quiz');
    $strtype = get_string('type', 'quiz');
    $strpreview = get_string('preview', 'quiz');
	$hasattempts = 0;//modified for ipal this is added because attempts don't mean anything with ipal
    if ($quiz->questions) {//echo "\n<br />debug368 in /ipal/quiz/ipal_editlib.php and quiz has these questions ".$quiz->questions;
        list($usql, $params) = $DB->get_in_or_equal(explode(',', $quiz->questions));
        //$params[] = $quiz->id;$question_id_array = explode(",",$quiz->questions);
		$qcount = 0;
		foreach($params as $key => $value){
		if($value > 0){//echo "\n<br />debug28 in ipal_editlib.php and qcount=$qcount and value=$value";
		$questions = $DB->get_records_sql("SELECT q.* FROM {question} q WHERE q.id = $value",$params);
		$ipal_questions[$value] = $questions[$value];
		$ipal_name[$qcount] = trim($questions[$value]->name);
		$ipal_questiontext[$qcount] = trim($questions[$value]->questiontext);
		$ipal_id[$qcount] = $value;
		$ipal_qtype[$qcount] = $questions[$value]->qtype;
		$qcount++;
		}
		else{
		$ipal_id[$qcount] = 0;
		$qcount++;
		}
		}
			
		//echo "\n<br />debug29 in ipal_editlib.php and questions are ".print_r($questions);
		foreach($ipal_name as $key => $value){
		//echo "\n<br />debug38 text=".$value." and qustiontext=".$ipal_questiontext[$key];
		}
//		$questions = $DB->get_records_sql("SELECT q.*, qc.contextid, qqi.grade as maxmark
//                              FROM {question} q
//                              JOIN {question_categories} qc ON qc.id = q.category
//                              JOIN {quiz_question_instances} qqi ON qqi.question = q.id
//                             WHERE q.id $usql AND qqi.quiz = ?", $params);echo "\n<br />debug375 and questions are ".print_r($questions);
    } else {
        $questions = array();
    }

//    $layout = quiz_clean_layout($quiz->questions);
//    $order = explode(',', $layout);
	if(isset($ipal_id)){
		$order = $ipal_id;
	}
	else
	{
		$order = NULL;
	}
	$lastindex = count($order) - 1;
    $disabled = '';
    $pagingdisabled = '';
    //if ($hasattempts) {
    //    $disabled = 'disabled="disabled"';
    //}
    //if ($hasattempts || $quiz->shufflequestions) {
    //    $pagingdisabled = 'disabled="disabled"';
    //}
    $reordercontrolssetdefaultsubmit = '<div style="display:none;">' .
        '<input type="submit" name="savechanges" value="' .
        $strreorderquestions . '" ' . $pagingdisabled . ' /></div>';
    $reordercontrols1 = '<div class="addnewpagesafterselected">' .
        '<input type="submit" name="addnewpagesafterselected" value="' .
        get_string('addnewpagesafterselected', 'quiz') . '"  ' .
        $pagingdisabled . ' /></div>';
    $reordercontrols1 .= '<div class="quizdeleteselected">' .
        '<input type="submit" name="quizdeleteselected" ' .
        'onclick="return confirm(\'' .
        get_string('areyousureremoveselected', 'quiz') . '\');" value="' .
        get_string('removeselected', 'quiz') . '"  ' . $disabled . ' /></div>';

    $a = '<input name="moveselectedonpagetop" type="text" size="2" ' .
        $pagingdisabled . ' />';
    $b = '<input name="moveselectedonpagebottom" type="text" size="2" ' .
        $pagingdisabled . ' />';

    $reordercontrols2top = '<div class="moveselectedonpage">' .
        get_string('moveselectedonpage', 'quiz', $a) .
        '<input type="submit" name="savechanges" value="' .
        $strmove . '"  ' . $pagingdisabled . ' />' . '
        <br /><input type="submit" name="savechanges" value="' .
        $strreorderquestions . '" /></div>';
    $reordercontrols2bottom = '<div class="moveselectedonpage">' .
        '<input type="submit" name="savechanges" value="' .
        $strreorderquestions . '" /><br />' .
        get_string('moveselectedonpage', 'quiz', $b) .
        '<input type="submit" name="savechanges" value="' .
        $strmove . '"  ' . $pagingdisabled . ' /> ' . '</div>';

    $reordercontrols3 = '<a href="javascript:select_all_in(\'FORM\', null, ' .
            '\'quizquestions\');">' .
            $strselectall . '</a> /';
    $reordercontrols3.=    ' <a href="javascript:deselect_all_in(\'FORM\', ' .
            'null, \'quizquestions\');">' .
            $strselectnone . '</a>';

    $reordercontrolstop = '<div class="reordercontrols">' .
            $reordercontrolssetdefaultsubmit .
            $reordercontrols1 . $reordercontrols2top . $reordercontrols3 . "</div>";
    $reordercontrolsbottom = '<div class="reordercontrols">' .
            $reordercontrolssetdefaultsubmit .
            $reordercontrols2bottom . $reordercontrols1 . $reordercontrols3 . "</div>";

//    if ($reordertool) {
//        echo '<form method="post" action="edit.php" id="quizquestions"><div>';
//
//        echo html_writer::input_hidden_params($pageurl);
//        echo '<input type="hidden" name="sesskey" value="' . sesskey() . '" />';
//
//        echo $reordercontrolstop;
//    }

    //the current question ordinal (no descriptions)
    $qno = 1;
    //the current question (includes questions and descriptions)
    $questioncount = 0;
    //the current page number in iteration
    $pagecount = 0;

    $pageopen = false;

    $returnurl = str_replace($CFG->wwwroot, '', $pageurl->out(false));
    $questiontotalcount = count($order);
	
    if(isset($order)){
	foreach ($order as $count => $qnum) {

        $reordercheckbox = '';
        $reordercheckboxlabel = '';
        $reordercheckboxlabelclose = '';

        if ($qnum && strlen($ipal_name[$count])==0 && strlen($ipal_text[$count])==0){//if ($qnum && empty($questions[$qnum])) {
            continue;
        }

//        // If the questiontype is missing change the question type
//        if ($qnum && !array_key_exists($qnum, $questions)) {
//            $fakequestion = new stdClass();
//            $fakequestion->id = 0;
//            $fakequestion->qtype = 'missingtype';
//            $fakequestion->name = get_string('deletedquestion', 'qtype_missingtype');
//            $fakequestion->questiontext = '<p>' .
//                    get_string('deletedquestion', 'qtype_missing') . '</p>';
//            $fakequestion->length = 0;
//            $questions[$qnum] = $fakequestion;
//            $quiz->grades[$qnum] = 0;
//
//        } else if ($qnum && !question_bank::qtype_exists($questions[$qnum]->qtype)) {
//            $questions[$qnum]->qtype = 'missingtype';
//        }

        if ($qnum != 0 || ($qnum == 0 && !$pageopen)) {
            //this is either a question or a page break after another
            //        (no page is currently open)
            if (!$pageopen) {
                //if no page is open, start display of a page
                $pagecount++;
                echo  '<div class="quizpage"><span class="pagetitle">';// .
                //echo        get_string('page') . '&nbsp;' . $pagecount .'</span>';
                echo        '<div class="pagecontent">';
                $pageopen = true;
            }
//            if ($qnum == 0  && $count < $questiontotalcount) {
//                // This is the second successive page break. Tell the user the page is empty.
//                echo '<div class="pagestatus">';
//                print_string('noquestionsonpage', 'quiz');
//                echo '</div>';
//                if ($allowdelete) {
//                    echo '<div class="quizpagedelete">';
//                    echo $OUTPUT->action_icon($pageurl->out(true,
//                            array('deleteemptypage' => $count - 1, 'sesskey'=>sesskey())),
//                            new pix_icon('t/delete', $strremove),
//                            new component_action('click',
//                                    'M.core_scroll_manager.save_scroll_action'),
//                            array('title' => $strremove));
//                    echo '</div>';
//                }
//            }

            if ($qnum != 0) {
                $question = $ipal_questions[$qnum];//echo "\n<br />debug180 and qnum is $qnum";
                $questionparams = array(
                        'returnurl' => $returnurl,
                        'cmid' => $quiz->cmid,
                        'id' => $ipal_id[$count]);
                $questionurl = new moodle_url('/question/question.php',
                        $questionparams);
                $questioncount++;
                //this is an actual question

                /* Display question start */
                ?>
<div class="question">
    <div class="questioncontainer <?php echo $ipal_qtype[$count]; ?>">
        <div class="qnum">
                <?php
                $reordercheckbox = '';
                $reordercheckboxlabel = '';
                $reordercheckboxlabelclose = '';
//                if ($reordertool) {
//                    $reordercheckbox = '<input type="checkbox" name="s' . $question->id .
//                        '" id="s' . $question->id . '" />';
//                    $reordercheckboxlabel = '<label for="s' . $question->id . '">';
//                    $reordercheckboxlabelclose = '</label>';
//                }
                if ((strlen($ipal_name[$count]) + strlen($ipal_questiontext[$count])) == 0) {
                    $qnodisplay = get_string('infoshort', 'quiz');
//                } else if ($quiz->shufflequestions) {
                    $qnodisplay = '?';
                } else {
//                    if ($qno > 999 || ($reordertool && $qno > 99)) {
                    if ($qno > 999){// || ($reordertool && $qno > 99)) {
                        $qnodisplay = html_writer::tag('small', $qno);
                    } else {
                        $qnodisplay = $qno;
                    }
                    $qno ++;// (strlen($ipal_name[$count]) + strlen($ipal_questiontext[$count]));//$question->length;
                }
                echo $reordercheckboxlabel . $qnodisplay . $reordercheckboxlabelclose .
                        $reordercheckbox;//echo "\n<br />debug229 and qnodisplay is $qnodisplay";

                ?>
        </div>
        <div class="content">
            <div class="questioncontrols">
                <?php
                if ($count != 0) {
                    if (!$hasattempts) {
                        $upbuttonclass = '';
                        if ($count >= $lastindex - 1) {
                            $upbuttonclass = 'upwithoutdown';
                        }
                        echo $OUTPUT->action_icon($pageurl->out(true,
                                array('up' => $ipal_id[$count], 'sesskey'=>sesskey())),
                                new pix_icon('t/up', $strmoveup),
                                new component_action('click',
                                        'M.core_scroll_manager.save_scroll_action'),
                                array('title' => $strmoveup));
                    }

                }
                if ($count < $lastindex - 1) {
                    if (!$hasattempts) {
                        echo $OUTPUT->action_icon($pageurl->out(true,
                                array('down' => $ipal_id[$count], 'sesskey'=>sesskey())),
                                new pix_icon('t/down', $strmovedown),
                                new component_action('click',
                                        'M.core_scroll_manager.save_scroll_action'),
                                array('title' => $strmovedown));
                    }
                }
                if ($allowdelete){// && (empty($ipal_id[$count]) ||
                        //question_has_capability_on($question, 'use', $question->category))) {
                    // remove from quiz, not question delete.
                    if (!$hasattempts) {
                        echo $OUTPUT->action_icon($pageurl->out(true,
                                array('remove' => $ipal_id[$count], 'sesskey'=>sesskey())),
                                new pix_icon('t/delete', $strremove),
                                new component_action('click',
                                        'M.core_scroll_manager.save_scroll_action'),
                                array('title' => $strremove));
                    }
                }
                ?>
            </div><?php
                if ($ipal_qtype[$count]!= 'description'){// && !$reordertool) { //Lines 271-    have been removed becuase I think they deal with regrade and reordering
 
 
 
 
 
 
 
 
 
 
 






























//                    }
                }
                ?>
            <div class="questioncontentcontainer">
                <?php
//                if ($question->qtype == 'random') { // it is a random question
//                    if (!$reordertool) {
//                        quiz_print_randomquestion($question, $pageurl, $quiz, $quiz_qbanktool);
//                    } else {
//                        quiz_print_randomquestion_reordertool($question, $pageurl, $quiz);
//                    }
//                } else { // it is a single question
//                    if (!$reordertool) {
                        ipal_print_singlequestion($question, $returnurl, $quiz);
//                    } else {
//                        quiz_print_singlequestion_reordertool($question, $returnurl, $quiz);
//                    }
//                }
                ?>
            </div>
        </div>
    </div>
</div>

                <?php
            }
        }
        //a page break: end the existing page.
        if ($qnum == 0) {//This should never happen
            if ($pageopen) {
                if (!$reordertool && !($quiz->shufflequestions &&
                        $count < $questiontotalcount - 1)) {
                    ipal_print_pagecontrols($quiz, $pageurl, $pagecount,
                            $hasattempts, $defaultcategoryobj);
                } else if ($count < $questiontotalcount - 1) {
                    //do not include the last page break for reordering
                    //to avoid creating a new extra page in the end
                    echo '<input type="hidden" name="opg' . $pagecount . '" size="2" value="' .
                            (10*$count + 10) . '" />';
                }
                echo "</div></div>";

//                if (!$reordertool && !$quiz->shufflequestions) {
//                    echo $OUTPUT->container_start('addpage');
//                    $url = new moodle_url($pageurl->out_omit_querystring(),
//                            array('cmid' => $quiz->cmid, 'courseid' => $quiz->course,
//                                    'addpage' => $count, 'sesskey' => sesskey()));
//                    echo $OUTPUT->single_button($url, get_string('addpagehere', 'quiz'), 'post',
//                            array('disabled' => $hasattempts,
//                            'actions' => array(new component_action('click',
//                                    'M.core_scroll_manager.save_scroll_action'))));
//                    echo $OUTPUT->container_end();
//                }
                $pageopen = false;
                $count++;
            }
        }

    }
	}//End of if(isset($order)
//    if ($reordertool) {
//        echo $reordercontrolsbottom;
//        echo '</div></form>';
//    }
}

/**
 * Print all the controls for adding questions directly into the
 * specific page in the edit tab of edit.php
 *
 * @param unknown_type $quiz
 * @param unknown_type $pageurl
 * @param unknown_type $page
 * @param unknown_type $hasattempts
 */
function ipal_print_pagecontrols($quiz, $pageurl, $page, $hasattempts, $defaultcategoryobj) {
    global $CFG, $OUTPUT;
    static $randombuttoncount = 0;
    $randombuttoncount++;
    echo '<div class="pagecontrols">';
	$hasattempts = 0;//modified for ipal this is added because attempts don't mean anything with ipal
    // Get the current context
    $thiscontext = get_context_instance(CONTEXT_COURSE, $quiz->course);
    $contexts = new question_edit_contexts($thiscontext);

    // Get the default category.
    list($defaultcategoryid) = explode(',', $pageurl->param('cat'));
    if (empty($defaultcategoryid)) {
        $defaultcategoryid = $defaultcategoryobj->id;
    }

    // Create the url the question page will return to
    $returnurladdtoquiz = new moodle_url($pageurl, array('addonpage' => $page));

    // Print a button linking to the choose question type page.
    $returnurladdtoquiz = str_replace($CFG->wwwroot, '', $returnurladdtoquiz->out(false));
    $newquestionparams = array('returnurl' => $returnurladdtoquiz,
            'cmid' => $quiz->cmid, 'appendqnumstring' => 'addquestion');
    create_new_question_button($defaultcategoryid, $newquestionparams,
            get_string('addaquestion', 'quiz'),
            get_string('createquestionandadd', 'quiz'), $hasattempts);

    if ($hasattempts) {
        $disabled = 'disabled="disabled"';
    } else {
        $disabled = '';
    }//I removed lines 424-442  because they added a random button
    ?>



















    <?php //echo $OUTPUT->help_icon('addarandomquestion', 'quiz'); ?>
    <?php
    echo "\n</div>";
}

/**
 * Print a given single question in quiz for the edit tab of edit.php.
 * Meant to be used from quiz_print_question_list()
 *
 * @param object $question A question object from the database questions table
 * @param object $returnurl The url to get back to this page, for example after editing.
 * @param object $quiz The quiz in the context of which the question is being displayed
 */
function ipal_print_singlequestion($question, $returnurl, $quiz) {
    echo '<div class="singlequestion">';
    echo ipal_question_edit_button($quiz->cmid, $question, $returnurl,
            ipal_question_tostring($question) . ' ');
    echo '<span class="questiontype">';
    print_question_icon($question);
    echo ' ' . question_bank::get_qtype_name($question->qtype) . '</span>';
    echo '<span class="questionpreview">' .
            ipal_question_preview_button($quiz, $question, true) . '</span>';
    echo "</div>\n";
}
/**
 * @param int $cmid the course_module.id for this quiz.
 * @param object $question the question.
 * @param string $returnurl url to return to after action is done.
 * @param string $contentbeforeicon some HTML content to be added inside the link, before the icon.
 * @return the HTML for an edit icon, view icon, or nothing for a question
 *      (depending on permissions).
 */
function ipal_question_edit_button($cmid, $question, $returnurl, $contentaftericon = '') {
    global $CFG, $OUTPUT;

    // Minor efficiency saving. Only get strings once, even if there are a lot of icons on one page.
    static $stredit = null;
    static $strview = null;
    if ($stredit === null) {
        $stredit = get_string('edit');
        $strview = get_string('view');
    }

    // What sort of icon should we show?
    $action = '';
    if (!empty($question->id) &&
            (question_has_capability_on($question, 'edit', $question->category) ||
                    question_has_capability_on($question, 'move', $question->category))) {
        $action = $stredit;
        $icon = '/t/edit';
    } else if (!empty($question->id) &&
            question_has_capability_on($question, 'view', $question->category)) {
        $action = $strview;
        $icon = '/i/info';
    }

    // Build the icon.
    if ($action) {
        if ($returnurl instanceof moodle_url) {
            $returnurl = str_replace($CFG->wwwroot, '', $returnurl->out(false));
        }
        $questionparams = array('returnurl' => $returnurl, 'cmid' => $cmid, 'id' => $question->id);
        $questionurl = new moodle_url("$CFG->wwwroot/question/question.php", $questionparams);
        return '<a title="' . $action . '" href="' . $questionurl->out() . '"><img src="' .
                $OUTPUT->pix_url($icon) . '" alt="' . $action . '" />' . $contentaftericon .
                '</a>';
    } else {
        return $contentaftericon;
    }
}

/**
 * Creates a textual representation of a question for display.
 *
 * @param object $question A question object from the database questions table
 * @param bool $showicon If true, show the question's icon with the question. False by default.
 * @param bool $showquestiontext If true (default), show question text after question name.
 *       If false, show only question name.
 * @param bool $return If true (default), return the output. If false, print it.
 */
function ipal_question_tostring($question, $showicon = false,
        $showquestiontext = true, $return = true) {
    global $COURSE;
    $result = '';
    $result .= '<span class="questionname">';
    if ($showicon) {
        $result .= print_question_icon($question, true);
        echo ' ';
    }
    $result .= shorten_text(format_string($question->name), 200) . '</span>';
    if ($showquestiontext) {
        $formatoptions = new stdClass();
        $formatoptions->noclean = true;
        $formatoptions->para = false;
        $questiontext = strip_tags(format_text($question->questiontext,
                $question->questiontextformat,
                $formatoptions, $COURSE->id));
        $questiontext = shorten_text($questiontext, 200);
        $result .= '<span class="questiontext">';
        if (!empty($questiontext)) {
            $result .= $questiontext;
        } else {
            $result .= '<span class="error">';
            $result .= get_string('questiontextisempty', 'quiz');
            $result .= '</span>';
        }
        $result .= '</span>';
    }
    if ($return) {
        return $result;
    } else {
        echo $result;
    }
}

/**
 * @param object $quiz the quiz settings
 * @param object $question the question
 * @param bool $label if true, show the preview question label after the icon
 * @return the HTML for a preview question icon.
 */
function ipal_question_preview_button($quiz, $question, $label = false) {
    global $CFG, $OUTPUT;
    if (!question_has_capability_on($question, 'use', $question->category)) {
        return '';
    }

    $url = ipal_question_preview_url($quiz, $question);

    // Do we want a label?
    $strpreviewlabel = '';
    if ($label) {
        $strpreviewlabel = get_string('preview', 'quiz');
    }

    // Build the icon.
    $strpreviewquestion = get_string('previewquestion', 'quiz');
    $image = $OUTPUT->pix_icon('t/preview', $strpreviewquestion);

    $action = new popup_action('click', $url, 'questionpreview',
            question_preview_popup_params());

    return $OUTPUT->action_link($url, $image, $action, array('title' => $strpreviewquestion));
}

/**
 * @param object $quiz the quiz settings
 * @param object $question the question
 * @return moodle_url to preview this question with the options from this quiz.
 */
function ipal_question_preview_url($quiz, $question) {
    // Get the appropriate display options.
    $displayoptions = mod_ipal_display_options::make_from_quiz($quiz,
            mod_ipal_display_options::DURING);

    $maxmark = null;
    if (isset($question->maxmark)) {
        $maxmark = $question->maxmark;
    }

    // Work out the correcte preview URL.
    return question_preview_url($question->id, NULL,
            $maxmark, $displayoptions);
}

/**
 * An extension of question_display_options that includes the extra options used
 * by the quiz.
 *
 * @copyright  2010 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_ipal_display_options extends question_display_options {
    /**#@+
     * @var integer bits used to indicate various times in relation to a
     * quiz attempt.
     */
    const DURING =            0x10000;
    const IMMEDIATELY_AFTER = 0x01000;
    const LATER_WHILE_OPEN =  0x00100;
    const AFTER_CLOSE =       0x00010;
    /**#@-*/

    /**
     * @var boolean if this is false, then the student is not allowed to review
     * anything about the attempt.
     */
    public $attempt = true;

    /**
     * @var boolean if this is false, then the student is not allowed to review
     * anything about the attempt.
     */
    public $overallfeedback = self::VISIBLE;

    /**
     * Set up the various options from the quiz settings, and a time constant.
     * @param object $quiz the quiz settings.
     * @param int $one of the {@link DURING}, {@link IMMEDIATELY_AFTER},
     * {@link LATER_WHILE_OPEN} or {@link AFTER_CLOSE} constants.
     * @return mod_quiz_display_options set up appropriately.
     */
    public static function make_from_quiz($quiz, $when) {
        $options = new self();

//        $options->attempt = self::extract($quiz->reviewattempt, $when, true, false);
//        $options->correctness = self::extract($quiz->reviewcorrectness, $when);
//        $options->marks = self::extract($quiz->reviewmarks, $when,
//                self::MARK_AND_MAX, self::MAX_ONLY);
//        $options->feedback = self::extract($quiz->reviewspecificfeedback, $when);
//        $options->generalfeedback = self::extract($quiz->reviewgeneralfeedback, $when);
//        $options->rightanswer = self::extract($quiz->reviewrightanswer, $when);
//        $options->overallfeedback = self::extract($quiz->reviewoverallfeedback, $when);

//        $options->numpartscorrect = $options->feedback;

//        if ($quiz->questiondecimalpoints != -1) {
//            $options->markdp = $quiz->questiondecimalpoints;
//        } else {
//            $options->markdp = $quiz->decimalpoints;
//        }
			$options->markdp = 2;
        return $options;
    }

    protected static function extract($bitmask, $bit,
            $whenset = self::VISIBLE, $whennotset = self::HIDDEN) {
        if ($bitmask & $bit) {
            return $whenset;
        } else {
            return $whennotset;
        }
    }
}

// Private function used by the following two.
function _ipal_move_question($layout, $questionid, $shift) {
    if (!$questionid || !($shift == 1 || $shift == -1)) {
        return $layout;
    }

    $questionids = explode(',', $layout);
    $key = array_search($questionid, $questionids);
    if ($key === false) {
        return $layout;
    }

    $otherkey = $key + $shift;
    if ($otherkey < 0 || $otherkey >= count($questionids) - 1) {
        return $layout;
    }

    $temp = $questionids[$otherkey];
    $questionids[$otherkey] = $questionids[$key];
    $questionids[$key] = $temp;

    return implode(',', $questionids);
}

/**
 * Move a particular question one space earlier in the $quiz->questions list.
 * If that is not possible, do nothing.
 * @param string $layout the existinng layout, $quiz->questions.
 * @param int $questionid the id of a question.
 * @return the updated layout
 */
function ipal_move_question_up($layout, $questionid) {
    return _ipal_move_question($layout, $questionid, -1);
}

/**
 * Move a particular question one space later in the $quiz->questions list.
 * If that is not possible, do nothing.
 * @param string $layout the existinng layout, $quiz->questions.
 * @param int $questionid the id of a question.
 * @return the updated layout
 */
function ipal_move_question_down($layout, $questionid) {
    return _ipal_move_question($layout, $questionid, +1);
}

/**
 * Subclass to customise the view of the question bank for the quiz editing screen.
 *
 * @copyright  2009 Tim Hunt
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class ipal_question_bank_view extends question_bank_view {
    protected $quizhasattempts = false;
    /** @var object the quiz settings. */
    protected $ipal = false;//Modified for ipal new in ipal_editlib_new.php

    /**
     * Constructor
     * @param question_edit_contexts $contexts
     * @param moodle_url $pageurl
     * @param object $course course settings
     * @param object $cm activity settings.
     * @param object $quiz quiz settings.
     */
    public function __construct($contexts, $pageurl, $course, $cm, $ipal) {//echo "\n<br />debug750 in ipal_editlib_new.php and exit after print contexts = ".print_r($ipal);exit;
        parent::__construct($contexts, $pageurl, $course, $cm);
        $this->quiz = $ipal;//Modified for ipal and new in ipal_editlib_new.php
    }

    protected function known_field_types() {//I may need to edit this to remove unacceptable question types
        $types = parent::known_field_types();
        $types[] = new question_bank_add_to_ipal_action_column($this);//Modified for ipal in editlib_new
        $types[] = new ipal_question_bank_question_name_text_column($this);
        return $types;
    }

    protected function wanted_columns() {
        return array('checkbox', 'qtype', 'questionnametext',
                'editaction', 'previewaction');
//        return array('addtoquizaction', 'checkbox', 'qtype', 'questionnametext',
//                'editaction', 'previewaction');
    }

    /**
     * Let the question bank display know whether the quiz has been attempted,
     * hence whether some bits of UI, like the add this question to the quiz icon,
     * should be displayed.
     * @param bool $quizhasattempts whether the quiz has attempts.
     */
    public function set_quiz_has_attempts($quizhasattempts) {
        $this->quizhasattempts = $quizhasattempts;
        if ($quizhasattempts && isset($this->visiblecolumns['addtoquizaction'])) {
            unset($this->visiblecolumns['addtoquizaction']);
        }
    }

    public function preview_question_url($question) {//Debug this controls the URL of the preview icon.
        return quiz_question_preview_url($this->quiz, $question);
    }

    public function add_to_quiz_url($questionid) {
        global $CFG;
        $params = $this->baseurl->params();
        $params['addquestion'] = $questionid;
        $params['sesskey'] = sesskey();
        return new moodle_url('/mod/quiz/edit.php', $params);
    }

//Added function to send the program back to the correct place when in ipal
    public function add_to_ipal_url($questionid) {
        global $CFG;
        $params = $this->baseurl->params();
        $params['addquestion'] = $questionid;
        $params['sesskey'] = sesskey();
        return new moodle_url('/mod/ipal/ipal_quiz_edit.php', $params);
    }
	
    public function display($tabname, $page, $perpage, $cat,
            $recurse, $showhidden, $showquestiontext) {
        global $OUTPUT;
        if ($this->process_actions_needing_ui()) {
            return;
        }

        // Display the current category.
        if (!$category = $this->get_current_category($cat)) {
            return;
        }
        $this->print_category_info($category);

        echo $OUTPUT->box_start('generalbox questionbank');

        $this->display_category_form($this->contexts->having_one_edit_tab_cap($tabname),
                $this->baseurl, $cat);

        // continues with list of questions
        $this->ipal_display_question_list($this->contexts->having_one_edit_tab_cap($tabname),
                $this->baseurl, $cat, $this->cm, $recurse, $page,
                $perpage, $showhidden, $showquestiontext,
                $this->contexts->having_cap('moodle/question:add'));

        $this->display_options($recurse, $showhidden, $showquestiontext);
        echo $OUTPUT->box_end();
    }

    protected function print_choose_category_message($categoryandcontext) {
        global $OUTPUT;
        echo $OUTPUT->box_start('generalbox questionbank');
        $this->display_category_form($this->contexts->having_one_edit_tab_cap('edit'),
                $this->baseurl, $categoryandcontext);
        echo "<p style=\"text-align:center;\"><b>";
        print_string('selectcategoryabove', 'quiz');
        echo "</b></p>";
        echo $OUTPUT->box_end();
    }

    protected function print_category_info($category) {
        $formatoptions = new stdClass();
        $formatoptions->noclean = true;
        $strcategory = get_string('category', 'quiz');
        echo '<div class="categoryinfo"><div class="categorynamefieldcontainer">' .
                $strcategory;
        echo ': <span class="categorynamefield">';
        echo shorten_text(strip_tags(format_string($category->name)), 60);
        echo '</span></div><div class="categoryinfofieldcontainer">' .
                '<span class="categoryinfofield">';
        echo shorten_text(strip_tags(format_text($category->info, $category->infoformat,
                $formatoptions, $this->course->id)), 200);
        echo '</span></div></div>';
    }

    protected function display_options($recurse, $showhidden, $showquestiontext) {
        echo '<form method="get" action="ipal_quiz_edit.php" id="displayoptions">';//Modified for ipal
        echo "<fieldset class='invisiblefieldset'>";
        echo html_writer::input_hidden_params($this->baseurl,
                array('recurse', 'showhidden', 'qbshowtext'));
        $this->display_category_form_checkbox('recurse', $recurse,
                get_string('includesubcategories', 'question'));
        $this->display_category_form_checkbox('showhidden', $showhidden,
                get_string('showhidden', 'question'));
        echo '<noscript><div class="centerpara"><input type="submit" value="' .
                get_string('go') . '" />';
        echo '</div></noscript></fieldset></form>';
    }

    protected function ipal_print_table_row($question, $rowcount) {
        $rowclasses = implode(' ', $this->get_row_classes($question, $rowcount));
        if ($rowclasses) {
            echo '<tr class="' . $rowclasses . '">' . "\n";
        } else {
            echo "<tr>\n";
        }
        foreach ($this->visiblecolumns as $colkey => $column) {
            //global $CFG;
			if($colkey == 'addtoquizaction'){
			$column = new question_bank_add_to_ipal_action_column($this);
			$column->display($question,$rowclasses);
			//->display($question,$rowclasses);
			//echo '<td></td>';
//			echo '<td class="iconcol addtoquizaction"><a title="Add to quiz" href="http://socrates.eckerd.edu/dev_ipal/mod/quiz/edit.php?cmid=2&amp;cat=1%2C16&amp;qpage=0&amp;addquestion=7&amp;sesskey=9OJWih86dw">
//                <img src="http://socrates.eckerd.edu/dev_ipal/theme/image.php?theme=standard&amp;image=t%2Fmoveleft&amp;rev=173" class="iconsmall" alt="Add to quiz" /></a></td>';
			}
			else{
			$column->display($question, $rowclasses);
			}
        }
        echo "</tr>\n";
        foreach ($this->extrarows as $row) {
            $row->display($question, $rowclasses);
        }
    }

	
	    /**
    * Prints the table of questions in a category with interactions
    *
    * @param object $course   The course object
    * @param int $categoryid  The id of the question category to be displayed
    * @param int $cm      The course module record if we are in the context of a particular module, 0 otherwise
    * @param int $recurse     This is 1 if subcategories should be included, 0 otherwise
    * @param int $page        The number of the page to be displayed
    * @param int $perpage     Number of questions to show per page
    * @param bool $showhidden   True if also hidden questions should be displayed
    * @param bool $showquestiontext whether the text of each question should be shown in the list
    */
    protected function ipal_display_question_list($contexts, $pageurl, $categoryandcontext,
            $cm = null, $recurse=1, $page=0, $perpage=100, $showhidden=false,
            $showquestiontext = false, $addcontexts = array()) {
        global $CFG, $DB, $OUTPUT;

        $category = $this->get_current_category($categoryandcontext);

        $cmoptions = new stdClass();
        $cmoptions->hasattempts = !empty($this->quizhasattempts);

        $strselectall = get_string('selectall');
        $strselectnone = get_string('deselectall');
        $strdelete = get_string('delete');

        list($categoryid, $contextid) = explode(',', $categoryandcontext);
        $catcontext = get_context_instance_by_id($contextid);

        $canadd = has_capability('moodle/question:add', $catcontext);
        $caneditall =has_capability('moodle/question:editall', $catcontext);
        $canuseall =has_capability('moodle/question:useall', $catcontext);
        $canmoveall =has_capability('moodle/question:moveall', $catcontext);

        $this->create_new_question_form($category, $canadd);

        $this->build_query_sql($category, $recurse, $showhidden);
        $totalnumber = $this->get_question_count();
        if ($totalnumber == 0) {
            return;
        }

        $questions = $this->load_page_questions($page, $perpage);

        echo '<div class="categorypagingbarcontainer">';
        $pageing_url = new moodle_url('edit.php');
        $r = $pageing_url->params($pageurl->params());
        $pagingbar = new paging_bar($totalnumber, $page, $perpage, $pageing_url);
        $pagingbar->pagevar = 'qpage';
        echo $OUTPUT->render($pagingbar);
        echo '</div>';

        echo '<form method="post" action="ipal_quiz_edit.php">';//Modified for ipal
        echo '<fieldset class="invisiblefieldset" style="display: block;">';
        echo '<input type="hidden" name="sesskey" value="'.sesskey().'" />';
        echo html_writer::input_hidden_params($pageurl);

        echo '<div class="categoryquestionscontainer">';
        $this->start_table();
        $rowcount = 0;
        foreach ($questions as $question) {
            $this->ipal_print_table_row($question, $rowcount);
            $rowcount += 1;
        }
        $this->end_table();
        echo "</div>\n";

        echo '<div class="categorypagingbarcontainer pagingbottom">';
        echo $OUTPUT->render($pagingbar);
        if ($totalnumber > DEFAULT_QUESTIONS_PER_PAGE) {
            if ($perpage == DEFAULT_QUESTIONS_PER_PAGE) {
                $url = new moodle_url('edit.php', ($pageurl->params()+array('qperpage'=>1000)));
                $showall = '<a href="'.$url.'">'.get_string('showall', 'moodle', $totalnumber).'</a>';
            } else {
                $url = new moodle_url('edit.php', ($pageurl->params()+array('qperpage'=>DEFAULT_QUESTIONS_PER_PAGE)));
                $showall = '<a href="'.$url.'">'.get_string('showperpage', 'moodle', DEFAULT_QUESTIONS_PER_PAGE).'</a>';
            }
            echo "<div class='paging'>$showall</div>";
        }
        echo '</div>';

        echo '<div class="modulespecificbuttonscontainer">';
        if ($caneditall || $canmoveall || $canuseall){
            echo '<strong>&nbsp;'.get_string('withselected', 'question').':</strong><br />';

            if (function_exists('module_specific_buttons')) {
                echo module_specific_buttons($this->cm->id,$cmoptions);
            }

            // print delete and move selected question
//            if ($caneditall) {
//                echo '<input type="submit" name="deleteselected" value="' . $strdelete . "\" />\n";
//            }//Modified for ipal

            if ($canmoveall && count($addcontexts)) {
                echo '<input type="submit" name="move" value="'.get_string('moveto', 'question')."\" />\n";
                question_category_select_menu($addcontexts, false, 0, "$category->id,$category->contextid");
            }

            if (function_exists('module_specific_controls') && $canuseall) {
                $modulespecific = module_specific_controls($totalnumber, $recurse, $category, $this->cm->id,$cmoptions);
                if(!empty($modulespecific)){
                    echo "<hr />$modulespecific";
                }
            }
        }
        echo "</div>\n";

        echo '</fieldset>';
        echo "</form>\n";
    }

}

/**
 * A column type for the add this question to the quiz. from /mod/quiz/editlib.php
 *
 * @copyright  2009 Tim Hunt
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class question_bank_add_to_ipal_action_column extends question_bank_action_column_base {
    protected $stradd;

    public function init() {
        parent::init();
        $this->stradd = get_string('addtoquiz', 'quiz');
    }

    public function get_name() {
        return 'addtoipalaction';
    }

    protected function display_content($question, $rowclasses) {
        // for RTL languages: switch right and left arrows
        if (right_to_left()) {
            $movearrow = 't/removeright';
        } else {
            $movearrow = 't/moveleft';
        }
        //$quizediturl = $this->qbank->add_to_quiz_url($question-$id);
		$this->print_icon($movearrow, $this->stradd, $this->qbank->add_to_ipal_url($question->id));
    }

    public function get_required_fields() {
        return array('q.id');
    }
}

/**
 * A column type for the name followed by the start of the question text.
 *
 * @copyright  2009 Tim Hunt
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class ipal_question_bank_question_name_text_column extends question_bank_question_name_column {//Modified for ipal from /mod/quiz/editlib.php class question_bank_question_name_text_column
    public function get_name() {
        return 'questionnametext';
    }

    protected function display_content($question, $rowclasses) {
        echo '<div>';
        $labelfor = $this->label_for($question);
        if ($labelfor) {
            echo '<label for="' . $labelfor . '">';
        }
        echo quiz_question_tostring($question, false, true, true);
        if ($labelfor) {
            echo '</label>';
        }
        echo '</div>';
    }

    public function get_required_fields() {
        $fields = parent::get_required_fields();
        $fields[] = 'q.questiontext';
        $fields[] = 'q.questiontextformat';
        return $fields;
    }
}


?>