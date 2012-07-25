<?php
//For iapl from /question/engine/lib.php

/**
 * This class contains all the options that controls how a question is displayed.
 *
 * Normally, what will happen is that the calling code will set up some display
 * options to indicate what sort of question display it wants, and then before the
 * question is rendered, the behaviour will be given a chance to modify the
 * display options, so that, for example, A question that is finished will only
 * be shown read-only, and a question that has not been submitted will not have
 * any sort of feedback displayed.
 *
 * @copyright  2009 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * Modified by W. F. Junkin 2012.02.14 for IPAL so that changing versons would not impact IPAL
 */
class ipal_question_display_options {
    /**#@+ @var integer named constants for the values that most of the options take. */
    const HIDDEN = 0;
    const VISIBLE = 1;
    const EDITABLE = 2;
    /**#@-*/

    /**#@+ @var integer named constants for the {@link $marks} option. */
    const MAX_ONLY = 1;
    const MARK_AND_MAX = 2;
    /**#@-*/

    /**
     * @var integer maximum value for the {@link $markpd} option. This is
     * effectively set by the database structure, which uses NUMBER(12,7) columns
     * for question marks/fractions.
     */
    const MAX_DP = 7;

    /**
     * @var boolean whether the question should be displayed as a read-only review,
     * or in an active state where you can change the answer.
     */
    public $readonly = false;

    /**
     * @var boolean whether the question type should output hidden form fields
     * to reset any incorrect parts of the resonse to blank.
     */
    public $clearwrong = false;

    /**
     * Should the student have what they got right and wrong clearly indicated.
     * This includes the green/red hilighting of the bits of their response,
     * whether the one-line summary of the current state of the question says
     * correct/incorrect or just answered.
     * @var integer {@link question_display_options::HIDDEN} or
     * {@link question_display_options::VISIBLE}
     */
    public $correctness = self::VISIBLE;

    /**
     * The the mark and/or the maximum available mark for this question be visible?
     * @var integer {@link question_display_options::HIDDEN},
     * {@link question_display_options::MAX_ONLY} or {@link question_display_options::MARK_AND_MAX}
     */
    public $marks = self::MARK_AND_MAX;

    /** @var number of decimal places to use when formatting marks for output. */
    public $markdp = 2;

    /**
     * Should the flag this question UI element be visible, and if so, should the
     * flag state be changable?
     * @var integer {@link question_display_options::HIDDEN},
     * {@link question_display_options::VISIBLE} or {@link question_display_options::EDITABLE}
     */
    public $flags = self::VISIBLE;

    /**
     * Should the specific feedback be visible.
     * @var integer {@link question_display_options::HIDDEN} or
     * {@link question_display_options::VISIBLE}
     */
    public $feedback = self::VISIBLE;

    /**
     * For questions with a number of sub-parts (like matching, or
     * multiple-choice, multiple-reponse) display the number of sub-parts that
     * were correct.
     * @var integer {@link question_display_options::HIDDEN} or
     * {@link question_display_options::VISIBLE}
     */
    public $numpartscorrect = self::VISIBLE;

    /**
     * Should the general feedback be visible?
     * @var integer {@link question_display_options::HIDDEN} or
     * {@link question_display_options::VISIBLE}
     */
    public $generalfeedback = self::VISIBLE;

    /**
     * Should the automatically generated display of what the correct answer is
     * be visible?
     * @var integer {@link question_display_options::HIDDEN} or
     * {@link question_display_options::VISIBLE}
     */
    public $rightanswer = self::VISIBLE;

    /**
     * Should the manually added marker's comment be visible. Should the link for
     * adding/editing the comment be there.
     * @var integer {@link question_display_options::HIDDEN},
     * {@link question_display_options::VISIBLE}, or {@link question_display_options::EDITABLE}.
     * Editable means that form fields are displayed inline.
     */
    public $manualcomment = self::VISIBLE;

    /**
     * Should we show a 'Make comment or override grade' link?
     * @var string base URL for the edit comment script, which will be shown if
     * $manualcomment = self::VISIBLE.
     */
    public $manualcommentlink = null;

    /**
     * Used in places like the question history table, to show a link to review
     * this question in a certain state. If blank, a link is not shown.
     * @var string base URL for a review question script.
     */
    public $questionreviewlink = null;

    /**
     * Should the history of previous question states table be visible?
     * @var integer {@link question_display_options::HIDDEN} or
     * {@link question_display_options::VISIBLE}
     */
    public $history = self::HIDDEN;

    /**
     * If not empty, then a link to edit the question will be included in
     * the info box for the question.
     *
     * If used, this array must contain an element courseid or cmid.
     *
     * It shoudl also contain a parameter returnurl => moodle_url giving a
     * sensible URL to go back to when the editing form is submitted or cancelled.
     *
     * @var array url parameter for the edit link. id => questiosnid will be
     * added automatically.
     */
    public $editquestionparams = array();

    /**
     * @var int the context the attempt being output belongs to.
     */
    public $context;

    /**
     * Set all the feedback-related fields {@link $feedback}, {@link generalfeedback},
     * {@link rightanswer} and {@link manualcomment} to
     * {@link question_display_options::HIDDEN}.
     */
    public function hide_all_feedback() {
        $this->feedback = self::HIDDEN;
        $this->numpartscorrect = self::HIDDEN;
        $this->generalfeedback = self::HIDDEN;
        $this->rightanswer = self::HIDDEN;
        $this->manualcomment = self::HIDDEN;
        $this->correctness = self::HIDDEN;
    }

    /**
     * Returns the valid choices for the number of decimal places for showing
     * question marks. For use in the user interface.
     *
     * Calling code should probably use {@link question_engine::get_dp_options()}
     * rather than calling this method directly.
     *
     * @return array suitable for passing to {@link choose_from_menu()} or similar.
     */
    public static function get_dp_options() {
        $options = array();
        for ($i = 0; $i <= self::MAX_DP; $i += 1) {
            $options[$i] = $i;
        }
        return $options;
    }
}


?>