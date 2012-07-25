<?php
// This file is part of Moodle - http://moodle.org/
//
// 
/**
  *
 * @package    mod
 * @subpackage ipal
 * @copyright  1999 onwards Martin Dougiamas and others {@link http://moodle.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


require_once('../../config.php');
require_once($CFG->dirroot . '/mod/quiz/editlib.php');
require_once($CFG->dirroot . '/question/engine/lib.php');//needed for Class 'question_display_options' 
require_once($CFG->dirroot . '/question/editlib.php');//needed for Class 'question_bank_view'
require_once($CFG->dirroot . '/mod/ipal/quiz/ipal_editlib_new.php');
require_once($CFG->dirroot . '/mod/ipal/quiz/ipal_xmlparser.php');

list($thispageurl, $contexts, $cmid, $cm, $ipal, $pagevars) =
        question_edit_setup('editq', '/mod/ipal/compadre_redirect.php', true);//Modified for ipal

$scrollpos = optional_param('scrollpos', '', PARAM_INT);

$defaultcategoryobj = question_make_default_categories($contexts->all());
$defaultcategory = $defaultcategoryobj->id . ',' . $defaultcategoryobj->contextid;

$PAGE->set_url($thispageurl);

$pagetitle = get_string('editingquiz', 'quiz');
// Get the course object and related bits.
$course = $DB->get_record('course', array('id' => $ipal->course));
if (!$course) {
    print_error('invalidcourseid', 'error');
}

// You need mod/quiz:manage in addition to question capabilities to access this page.
require_capability('mod/quiz:manage', $contexts->lowest());

$params = (array) data_submitted();
foreach ($params as $key => $value) {
    if (preg_match('!^s([0-9]+)$!', $key, $matches)) {
        $selectedquestionids[] = $matches[1];
    }
}

$afteractionurl = new moodle_url($thispageurl);
if ($scrollpos) {
    $afteractionurl->param('scrollpos', $scrollpos);
}
if (($addquestion = optional_param('addquestion', 0, PARAM_INT)) && confirm_sesskey()) {
    // Add a single question to the current quiz
    $addonpage = optional_param('addonpage', 0, PARAM_INT);
    ipal_add_quiz_question($addquestion, $ipal, $addonpage);
    //quiz_delete_previews($quiz);
    //quiz_update_sumgrades($quiz);
    $thispageurl->param('lastchanged', $addquestion);
    redirect($afteractionurl);
}

/**
 * Add a question to a quiz  Modified from Add a question to quiz in mod_quiz_editlib.php
 *
 * Adds a question to a quiz by updating $quiz as well as the
 * quiz and quiz_question_instances tables. It also adds a page break
 * if required.
 * @param int $id The id of the question to be added
 * @param object $quiz The extended quiz object as used by edit.php
 *      This is updated by this function
 * @param int $page Which page in quiz to add the question on. If 0 (default),
 *      add at the end
 * @return bool false if the question was already in the quiz
 */
function ipal_add_quiz_question($id, $quiz, $page = 0) {
    global $DB;
    $questions = explode(',', quiz_clean_layout($quiz->questions));
    if (in_array($id, $questions)) {
        return false;
    }

    // remove ending page break if it is not needed
    if ($breaks = array_keys($questions, 0)) {
        // determine location of the last two page breaks
        $end = end($breaks);
        $last = prev($breaks);
        $last = $last ? $last : -1;
        if (!$quiz->questionsperpage || (($end - $last - 1) < $quiz->questionsperpage)) {
            array_pop($questions);
        }
    }
    if (is_int($page) && $page >= 1) {
        $numofpages = quiz_number_of_pages($quiz->questions);
        if ($numofpages<$page) {
            //the page specified does not exist in quiz
            $page = 0;
        } else {
            // add ending page break - the following logic requires doing
            //this at this point
            $questions[] = 0;
            $currentpage = 1;
            $addnow = false;
            foreach ($questions as $question) {
                if ($question == 0) {
                    $currentpage++;
                    //The current page is the one after the one we want to add on,
                    //so we add the question before adding the current page.
                    if ($currentpage == $page + 1) {
                        $questions_new[] = $id;
                    }
                }
                $questions_new[] = $question;
            }
            $questions = $questions_new;
        }
    }
    if ($page == 0) {
        // add question
        $questions[] = $id;
        // add ending page break
        $questions[] = 0;
    }

    // Save new questionslist in database
    $quiz->questions = implode(',', $questions);
    $DB->set_field('ipal', 'questions', $quiz->questions, array('id' => $quiz->id));

}

if (optional_param('add', false, PARAM_BOOL) && confirm_sesskey()) {
    // Add selected questions to the current quiz
    $rawdata = (array) data_submitted();
    foreach ($rawdata as $key => $value) { // Parse input for question ids
        if (preg_match('!^q([0-9]+)$!', $key, $matches)) {
            $key = $matches[1];
            ipal_add_quiz_question($key, $ipal);//Modified for ipal
        }
    }
    redirect($afteractionurl);
}

// End of process commands =====================================================

$PAGE->requires->yui2_lib('container');
$PAGE->requires->yui2_lib('dragdrop');
$PAGE->requires->skip_link_to('questionbank',
        get_string('skipto', 'access', get_string('questionbank', 'question')));
$PAGE->requires->skip_link_to('quizcontentsblock',
        get_string('skipto', 'access', get_string('questionsinthisquiz', 'quiz')));
$PAGE->set_title($pagetitle);
$PAGE->set_heading($course->fullname);
$node = $PAGE->settingsnav->find('mod_quiz_edit', navigation_node::TYPE_SETTING);
if ($node) {
    $node->make_active();
}
//echo $OUTPUT->header();

// Initialise the JavaScript.
//$quizeditconfig = new stdClass();
//$quizeditconfig->url = $thispageurl->out(true, array('qbanktool' => '0'));
//$quizeditconfig->dialoglisteners = array();
//$numberoflisteners = max(quiz_number_of_pages($quiz->questions), 1);
//for ($pageiter = 1; $pageiter <= $numberoflisteners; $pageiter++) {
//    $quizeditconfig->dialoglisteners[] = 'addrandomdialoglaunch_' . $pageiter;
//}
//$PAGE->requires->data_for_js('quiz_edit_config', $quizeditconfig);
//$PAGE->requires->js('/question/qengine.js');
//$PAGE->requires->js('/mod/quiz/edit.js');
//$PAGE->requires->js_init_call('quiz_edit_init');


//echo '<div class="quizcontents" id="quizcontentsblock">';//Modified for ipal 
$debug = 0;
    $repaginatingdisabledhtml = '';
    $repaginatingdisabled = false;
	$courseid = $course->id;
	//echo "\n<br />debug188 and course id is ".$courseid." and ";
$contextid = $DB->get_record('context', array('instanceid'=>"$courseid",'contextlevel'=>'50'));
	$contextID = $contextid->id;
	//echo "\n<br />The context Id for the course is $contextID";
$categories = $DB->get_records_menu('question_categories',array('contextid'=>"$contextID"));
$cats = $DB->get_records('question_categories',array('contextid'=>"$contextID"));
if($debug){
	foreach($categories as $key =>$value){echo "\n<br />category id =$key\n<br />";
		foreach($cats[$key] as $ky => $vlue){
			echo ", $ky=".$vlue;
		}
	}
}
//if(!($_POST['SESSKEY'] == sha1($sessionKey))){echo "YOur session has expired. Please log in again";exit;)
//    echo $OUTPUT->heading('Adding questions from ComPADRE for ' . $ipal->name, 2);//Modified for ipal
//if(isset($_POST['questionXMLencode']) and (strlen($_POST['questionXMLencode']) > 0)){
//echo "\n<br />debug183 exit on line 193";exit;
	require_once($CFG->dirroot . '/mod/ipal/quiz/adding_compadre_questions.php');
//	echo '</div>';
//	echo $OUTPUT->footer();
//	exit;
$ipalInstance = $CFG->wwwroot . '/mod/ipal/view.php?id='.$ipal->cmid;
header("Location: $ipalInstance"); 
echo "Please click <a href='".$CFG->wwwroot . '/mod/ipal/view.php?id='.$ipal->cmid."'>here.</a>";	

?>