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
 * This file contains the definition for the library class for PDF feedback plugin
 *
 *
 * @package   assignfeedback_pdf
 * @copyright 2012 Davo Smith
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot.'/mod/assign/feedback/pdf/lib.php');

define('ASSIGNFEEDBACK_PDF_MAXSUMMARYFILES', 5);


/**
 * library class for pdf feedback plugin extending feedback plugin base class
 *
 * @package   assignfeedback_pdf
 * @copyright 2012 Davo Smith
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class assign_feedback_pdf extends assign_feedback_plugin {

    /**
     * Get the name of the file feedback plugin
     * @return string
     */
    public function get_name() {
        return get_string('pdf', 'assignfeedback_pdf');
    }

    /**
     * Check the ghostscript path is valid to see if the plugin should be enabled.
     * @return bool
     */
    public function is_enabled() {
        global $CFG;

        static $gspathok = null;
        if (is_null($gspathok)) {
            require_once($CFG->dirroot.'/mod/assign/feedback/pdf/mypdflib.php');
            $result = AssignPDFLib::test_gs_path(false);
            $gspathok = ($result->status == AssignPDFLib::GSPATH_OK);
            if (!$gspathok && $this->is_visible()) {
                // Setting 'gspath' is invalid, so the plugin should be globally disabled.
                set_config('disabled', true, $this->get_subtype() . '_' . $this->get_type());
            }
        }

        if (!parent::is_enabled()) {
            return false;
        }

        return $gspathok;
    }

    /**
     * Find the current row from the assignment 'return_params'
     * @return bool
     */
    protected function get_rownum() {
        $returnaction = $this->assignment->get_return_action();
        if (!in_array($returnaction, array('grade', 'nextgrade', 'previousgrade'))) {
            return false;
        }
        $params = $this->assignment->get_return_params();
        if (!isset($params['rownum'])) {
            return false;
        }
        $rownum = $params['rownum'];

        if ($returnaction == 'nextgrade') {
            $rownum++;
        } else if ($returnaction == 'previousgrade') {
            $rownum--;
        }

        return $rownum;
    }

    /**
     * Work out the userid from the return parameters.
     * @return bool
     */
    protected function get_userid_because_assign_really_does_not_want_to_tell_me() {
        $rownum = $this->get_rownum();
        if ($rownum === false) {
            return false;
        }

        // This part is copied out of the 'assign' class (as it is private, so I can't use it directly).
        $filter = get_user_preferences('assign_filter', '');
        $table = new assign_grading_table($this->assignment, 0, $filter, 0, false);

        $useridlist = $table->get_column_data('userid');
        $userid = $useridlist[$rownum];

        return $userid;
    }

    /**
     * Get the user submission and team submission objects for the given user.
     * @param $userid
     * @param $attemptnumber
     * @return array - [$submission, $teamsubmission]
     */
    protected function get_submission($userid, $attemptnumber) {
        $submission = $this->assignment->get_user_submission($userid, false, $attemptnumber);
        $teamsubmission = false;
        if (!empty($this->assignment->get_instance()->teamsubmission)) {
            $teamsubmission = $this->assignment->get_group_submission($userid, 0, false, $attemptnumber);
        }

        return array($submission, $teamsubmission);
    }

    /**
     * Get form elements for grading form - retained so that plugin will be compatible with Moodle 2.3
     *
     * @param stdClass $grade
     * @param MoodleQuickForm $mform
     * @param stdClass $data
     * @return bool true if elements were added to the form
     */
    public function get_form_elements($grade, MoodleQuickForm $mform, stdClass $data) {
        if (isset($grade->userid)) {
            $userid = $grade->userid;
        } else {
            $userid = $this->get_userid_because_assign_really_does_not_want_to_tell_me();
        }
        return $this->get_form_elements_for_user($grade, $mform, $data, $userid);
    }

    /**
     * Get form elements for grading form
     *
     * @param stdClass $grade
     * @param MoodleQuickForm $mform
     * @param stdClass $data
     * @param int $userid
     * @return bool true if elements were added to the form
     */
    public function get_form_elements_for_user($grade, MoodleQuickForm $mform, stdClass $data, $userid) {
        $attemptnumber = -1;
        if (isset($grade->attemptnumber)) {
            $attemptnumber = $grade->attemptnumber;
        }
        list($submission, $teamsubmission) = $this->get_submission($userid, $attemptnumber);
        $annotatelink = $this->annotate_link($submission, $teamsubmission);
        if ($annotatelink) {
            $mform->addElement('static', '', '', $annotatelink);
        }
        $responselink = $this->response_link($submission, $teamsubmission);
        if ($responselink) {
            $mform->addElement('static', '', '', $responselink);
        }
        return ($annotatelink || $responselink);
    }

    /**
     * Display the list of files  in the feedback status table
     *
     * @param stdClass $grade
     * @param bool $showviewlink - Set to true to show a link to see the full list of files
     * @return string
     */
    public function view_summary(stdClass $grade, & $showviewlink) {
        $attemptnumber = -1;
        if (isset($grade->attemptnumber)) {
            $attemptnumber = $grade->attemptnumber;
        }
        list($submission, $teamsubmission) = $this->get_submission($grade->userid, $attemptnumber);
        return $this->response_link($submission, $teamsubmission);
    }

    /**
     * Display the list of files  in the feedback status table
     * @param stdClass $grade
     * @return string
     */
    public function view(stdClass $grade) {
        $attemptnumber = -1;
        if (isset($grade->attemptnumber)) {
            $attemptnumber = $grade->attemptnumber;
        }
        list($submission, $teamsubmission) = $this->get_submission($grade->userid, $attemptnumber);
        return $this->response_link($submission, $teamsubmission);
    }

    /**
     * This plugin does support quick grading.
     * @return bool
     */
    public function supports_quickgrading() {
        return true;
    }

    /**
     * Return the annotation and response links
     * @param int $userid
     * @param mixed $grade
     * @return string
     */
    public function get_quickgrading_html($userid, $grade) {
        $attemptnumber = -1;
        if (isset($grade->attemptnumber)) {
            $attemptnumber = $grade->attemptnumber;
        }
        list($submission, $teamsubmission) = $this->get_submission($userid, $attemptnumber);

        $annotate = $this->annotate_link($submission, $teamsubmission);
        $resp = $this->response_link($submission, $teamsubmission);

        if (!$resp) {
            return $annotate;
        }
        return $annotate.'<br />'.$resp;
    }

    /**
     * Return a link to annotate the given submission (uses the 'team submission' if set, otherwise uses
     * the individual submission)
     * @param $submission
     * @param $teamsubmission
     * @return string
     */
    protected function annotate_link($submission, $teamsubmission) {
        global $DB, $OUTPUT;
        if ($teamsubmission) {
            $realsubmission = $teamsubmission;
        } else {
            if (!empty($this->assignment->get_instance()->teamsubmission)) {
                return ''; // This is a team assignment, but there isn't a team submission yet.
            }
            $realsubmission = $submission;
        }
        if (!$realsubmission || $realsubmission->status == ASSIGN_SUBMISSION_STATUS_DRAFT) {
            return '';
        }
        $context = $this->assignment->get_context();
        if (has_capability('mod/assign:grade', $context)) {
            $status = $DB->get_field('assignsubmission_pdf', 'status', array('submission' => $realsubmission->id));
            if ($status == ASSIGNSUBMISSION_PDF_STATUS_EMPTY) {
                return get_string('emptysubmission', 'assignfeedback_pdf');
            }
            if ($status != ASSIGNSUBMISSION_PDF_STATUS_SUBMITTED && $status != ASSIGNSUBMISSION_PDF_STATUS_RESPONDED) {
                return ''; // Not yet submitted for marking.
            }
            // Add 'annotate submission' link.
            $cm = $this->assignment->get_course_module();
            $url = new moodle_url('/mod/assign/feedback/pdf/editcomment.php',
                                  array('id' => $cm->id, 'submissionid' => $realsubmission->id));
            $url->param('returnparams', $this->encode_return_params());
            $rownum = $this->get_rownum();
            if ($rownum !== false) {
                $url->param('rownum', $rownum);
            }

            $ret = $OUTPUT->pix_icon('annotate', '', 'assignfeedback_pdf').' ';
            $ret .= html_writer::link($url, get_string('annotatesubmission', 'assignfeedback_pdf'));
            return $ret;
        }

        return '';
    }

    /**
     * Encode the 'return parameters', so they can be passed on to the annotation page.
     * @return string
     */
    protected function encode_return_params() {
        $returnparams = $this->assignment->get_return_params();
        if (isset($returnparams['useridlistid']) && $returnparams['useridlistid'] == 0) {
            unset($returnparams['useridlistid']);
        }
        if ($action = $this->assignment->get_return_action()) {
            $returnparams['action'] = $this->assignment->get_return_action();
        }
        return http_build_query($returnparams);
    }

    /**
     * Return the links to view / download the response file
     * @param $submission
     * @param $teamsubmission
     * @return string
     */
    protected function response_link($submission, $teamsubmission) {
        global $DB, $OUTPUT;

        if ($teamsubmission) {
            $realsubmission = $teamsubmission;
        } else {
            if (!empty($this->assignment->get_instance()->teamsubmission)) {
                return ''; // This is a team assignment, but there is no team submission yet.
            }
            $realsubmission = $submission;
        }
        if (!$realsubmission || $realsubmission->status == ASSIGN_SUBMISSION_STATUS_DRAFT) {
            return '';
        }
        $status = $DB->get_field('assignsubmission_pdf', 'status', array('submission' => $realsubmission->id));
        if ($status == ASSIGNSUBMISSION_PDF_STATUS_RESPONDED) {
            // Add 'download response' link.
            $context = $this->assignment->get_context();
            $downloadurl = moodle_url::make_pluginfile_url(
                $context->id,
                'assignfeedback_pdf',
                ASSIGNFEEDBACK_PDF_FA_RESPONSE,
                $realsubmission->id,
                $this->get_subfolder(),
                ASSIGNFEEDBACK_PDF_FILENAME,
                true);
            $cm = $this->assignment->get_course_module();
            $viewurl = new moodle_url('/mod/assign/feedback/pdf/viewcomment.php',
                                      array('id' => $cm->id,
                                           'submissionid' => $realsubmission->id));
            $viewurl->param('returnparams', $this->encode_return_params());
            $ret = $OUTPUT->pix_icon('t/download', '').' ';
            $ret .= html_writer::link($downloadurl, get_string('downloadresponse', 'assignfeedback_pdf'));
            $ret .= html_writer::empty_tag('br');
            $ret .= $OUTPUT->pix_icon('t/preview', '').' ';
            $ret .= html_writer::link($viewurl, get_string('viewresponse', 'assignfeedback_pdf'));
            return $ret;
        }
        return '';
    }

    /**
     * The assignment has been deleted - cleanup
     *
     * @return bool
     */
    public function delete_instance() {

        // Nothing to delete - the submission plugin handles any deletion needed.

        return true;
    }

    /**
     * Return true if there are no feedback files
     * @param stdClass $grade
     * @return bool
     */
    public function is_empty(stdClass $grade) {
        global $DB;
        $attemptnumber = -1;
        if (isset($grade->attemptnumber)) {
            $attemptnumber = $grade->attemptnumber;
        }
        list($submission, $teamsubmission) = $this->get_submission($grade->userid, $attemptnumber);
        if ($teamsubmission) {
            $submission = $teamsubmission;
        }
        if (!$submission) {
            return false;
        }
        if ($submission->status == ASSIGN_SUBMISSION_STATUS_DRAFT) {
            return true;
        }
        $status = $DB->get_field('assignsubmission_pdf', 'status', array('submission' => $submission->id));
        return $status != ASSIGNSUBMISSION_PDF_STATUS_RESPONDED;
    }

    /**
     * Get file areas returns a list of areas this plugin stores files
     * @return array - An array of fileareas (keys) and descriptions (values)
     */
    public function get_file_areas() {
        $name = $this->get_name();
        return array(
            ASSIGNFEEDBACK_PDF_FA_IMAGE => get_string('imagefor', 'assignfeedback_pdf', $name),
            ASSIGNFEEDBACK_PDF_FA_RESPONSE => get_string('responsefor', 'assignfeedback_pdf', $name)
        );
    }

    /**
     * Return true if this plugin can upgrade an old Moodle 2.2 assignment of this type
     * and version.
     *
     * @param string $type old assignment subtype
     * @param int $version old assignment version
     * @return bool True if upgrade is possible
     */
    public function can_upgrade($type, $version) {
        // Submission plugin handles the upgrade.
        return false;
    }

    /**
     * Upgrade the settings from the old assignment to the new plugin based one
     *
     * @param context $oldcontext - the context for the old assignment
     * @param stdClass $oldassignment - the data for the old assignment
     * @param string $log - can be appended to by the upgrade
     * @return bool was it a success? (false will trigger a rollback)
     */
    public function upgrade_settings(context $oldcontext, stdClass $oldassignment, & $log) {
        // First upgrade settings (nothing to do).
        return true;
    }

    /**
     * Upgrade the feedback from the old assignment to the new one
     *
     * @param context $oldcontext - the database for the old assignment context
     * @param stdClass $oldassignment The data record for the old assignment
     * @param stdClass $oldsubmission The data record for the old submission
     * @param stdClass $grade The data record for the new grade
     * @param string $log Record upgrade messages in the log
     * @return bool true or false - false will trigger a rollback
     */
    public function upgrade(context $oldcontext, stdClass $oldassignment, stdClass $oldsubmission, stdClass $grade, & $log) {
        // Submission plugin handles the upgrade.
        return true;
    }

    /**
     * Output the page for annotating the PDF
     * @param int $submissionid - the submission to annotate
     * @param int $pageno - the page to start on
     * @param bool $enableedit - true if the editing interface should be enabled (overridden if the user does not
     *                           have the required capabilities.
     */
    public function edit_comment_page($submissionid, $pageno, $enableedit = true) {
        global $CFG, $DB, $OUTPUT, $PAGE, $USER;

        // Check user and submission both exist.
        $assignment = $this->assignment->get_instance();
        $params = array('id' => $submissionid, 'assignment' => $assignment->id);
        $submission = $DB->get_record('assign_submission', $params, '*', MUST_EXIST);
        $user = null;
        $group = null;
        if ($submission->userid) {
            $user = $DB->get_record('user', array('id' => $submission->userid), '*', MUST_EXIST);
        } else if ($submission->groupid) {
            $group = $DB->get_record('groups', array('id' => $submission->groupid), '*', MUST_EXIST);
        }
        $params = array('assignment' => $assignment->id, 'submission' => $submission->id);
        $submissionpdf = $DB->get_record('assignsubmission_pdf', $params, '*', MUST_EXIST);
        $submission->numpages = $submissionpdf->numpages;
        $cm = $this->assignment->get_course_module();

        // Check capabilities.
        $context = $this->assignment->get_context();
        if ($user) {
            if ($USER->id == $user->id) {
                if (!has_capability('mod/assign:grade', $context)) {
                    require_capability('mod/assign:submit', $context);
                    $enableedit = false;
                }
            } else {
                require_capability('mod/assign:grade', $context);
            }
        } else {
            // Team submission.
            if (!$group || groups_is_member($group->id)) {
                if (!has_capability('mod/assign:grade', $context)) {
                    require_capability('mod/assign:submit', $context);
                    $enableedit = false;
                }
            } else {
                require_capability('mod/assign:grade', $context);
            }
        }

        // Create a frameset if to handle the 'showprevious comments' sidebar.
        $showprevious = optional_param('showprevious', -1, PARAM_INT);
        if ($user && $enableedit && optional_param('topframe', false, PARAM_INT)) {
            if ($showprevious != -1 && !$assignment->blindmarking) {
                echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Frameset//EN" "http://www.w3.org/TR/html4/frameset.dtd">';
                echo '<html><head><title>'.get_string('feedback', 'assign').':'.fullname($user, true).
                    ':'.format_string($assignment->name).'</title></head>';
                echo html_writer::start_tag('frameset', array('cols' => "70%, 30%"));
                $mainframeurl = new moodle_url('/mod/assign/feedback/pdf/editcomment.php', array('id' => $cm->id,
                                                                                  'submissionid' => $submission->id,
                                                                                  'pageno' => $pageno,
                                                                                  'showprevious' => $showprevious,
                                                                                           ));
                $previouscm = get_coursemodule_from_instance('assign', $showprevious, $this->assignment->get_course()->id,
                                                             false, MUST_EXIST);
                $previoussubmission = $DB->get_field('assign_submission', 'id', array('assignment' => $showprevious,
                                                                                'userid' => $user->id), MUST_EXIST);
                $sideframeurl = new moodle_url('/mod/assign/feedback/pdf/editcomment.php', array('id' => $previouscm->id,
                                                                                       'submissionid' => $previoussubmission,
                                                                                       'action' => 'showprevious'));
                echo html_writer::empty_tag('frame', array('src' => $mainframeurl));
                echo html_writer::empty_tag('frame', array('src' => $sideframeurl));
                echo html_writer::end_tag('frameset');
                echo '</html>';
                die();
            }
        }

        $savedraft = optional_param('savedraft', false, PARAM_BOOL);
        $generateresponse = optional_param('generateresponse', false, PARAM_BOOL);

        // Close the window (if the user clicks on 'savedraft').
        if ($enableedit && $savedraft) {
            redirect($this->return_url());
        }

        // Generate the response PDF and cose the window, if requested.
        if ($enableedit && $generateresponse) {
            if ($this->create_response_pdf($submission->id)) {
                // Update the submission status.
                $updated = new stdClass();
                $updated->id = $submissionpdf->id;
                $updated->status = ASSIGNSUBMISSION_PDF_STATUS_RESPONDED;
                $DB->update_record('assignsubmission_pdf', $updated);

                // Make sure there is a grade record for this submission (or it won't appear in the overview page).
                $attemptnumber = !empty($submission->attemptnumber) ? $submission->attemptnumber : 0;
                if ($user) {
                    $this->assignment->get_user_grade($user->id, true, $attemptnumber);
                } else if ($group) {
                    foreach (get_users_by_capability($context, 'mod/assign:submit', 'u.id', '', '', '', $group->id) as $u) {
                        $this->assignment->get_user_grade($u->id, true, $attemptnumber);
                    }
                }

                redirect($this->return_url());

            } else {
                echo $OUTPUT->header(get_string('feedback', 'assignment').':'.
                                     format_string($this->assignment->get_instance()->name));
                print_error('responseproblem', 'assignfeedback_pdf');
                die();
            }
        }

        list($imageurl, $imgwidth, $imgheight, $pagecount) = $this->get_page_image($pageno, $submission);

        if ($user) {
            $titlestr = fullname($user);
        } else if ($group) {
            $titlestr = format_string($group->name);
        } else {
            $titlestr = get_string('nogroup', 'assignfeedback_pdf');
        }
        if (!empty($assignment->blindmarking)) {
            $titlestr = get_string('blindmarking', 'assign');
        }
        $PAGE->set_title(get_string('feedback', 'assignment').':'.$titlestr.':'.format_string($assignment->name));
        $PAGE->set_heading('');
        $returnurl = $this->return_url();
        if (in_array($returnurl->get_param('action'), array('grading', 'grade', 'nextgrade', 'previousgrade'))) {
            $PAGE->navbar->add(get_string('grading', 'mod_assign'), $returnurl);
        }
        if ($enableedit) {
            $PAGE->navbar->add(get_string('annotatesubmission', 'assignfeedback_pdf'));
        } else {
            $PAGE->navbar->add(get_string('viewresponse', 'assignfeedback_pdf'));
        }

        echo $OUTPUT->header();

        echo '<noscript>'.get_string('jsrequired', 'assignfeedback_pdf').'</noscript>';
        echo '<div id="everythingspinner">'.$OUTPUT->pix_icon('i/loading', '').'</div>';
        echo '<div id="everything" class="hidden">';

        echo $this->output_controls($submission, $user, $pageno, $enableedit, $showprevious);

        // Output the page image.
        echo '<div id="pdfsize" style="clear: both; width:'.$imgwidth.'px; height:'.$imgheight.'px; ">';
        echo '<div id="pdfouter" style="position: relative; "> <div id="pdfholder" > ';
        echo '<img id="pdfimg" src="'.$imageurl.'" width="'.$imgwidth.'" height="'.$imgheight.'" />';
        echo '</div></div></div>';

        $pageselector = $this->output_pageselector($submission, $pageno);
        $pageselector = str_replace(array('selectpage', '"nextpage"', '"prevpage"'),
                                    array('selectpage2', '"nextpage2"', '"prevpage2"'), $pageselector);
        echo '<br/>';
        echo $pageselector;
        if ($enableedit) {
            $openurl = new moodle_url('/mod/assign/feedback/pdf/editcomment.php', array('id' => $cm->id,
                                                                                       'submissionid' => $submission->id,
                                                                                       'pageno' => $pageno,
                                                                                       'showprevious' => $showprevious));
            echo '<p>';
            echo html_writer::link($openurl, get_string('opennewwindow', 'assignfeedback_pdf'), array('target' => '_blank',
                                                                                                     'id' => 'opennewwindow'));
            echo '</p>';
        }
        echo '<br style="clear:both;" />';

        if ($enableedit) {
            // Definitions for the right-click menus.
            echo '<ul class="contextmenu" style="display: none;" id="context-quicklist"><li class="separator">'.
                get_string('quicklist', 'assignfeedback_pdf').'</li></ul>';
            echo '<ul class="contextmenu" style="display: none;" id="context-comment"><li><a href="#addtoquicklist">'.
                get_string('addquicklist', 'assignfeedback_pdf').'</a></li>';
            echo '<li class="separator"><a href="#red">'.get_string('colourred', 'assignfeedback_pdf').'</a></li>';
            echo '<li><a href="#yellow">'.get_string('colouryellow', 'assignfeedback_pdf').'</a></li>';
            echo '<li><a href="#green">'.get_string('colourgreen', 'assignfeedback_pdf').'</a></li>';
            echo '<li><a href="#blue">'.get_string('colourblue', 'assignfeedback_pdf').'</a></li>';
            echo '<li><a href="#white">'.get_string('colourwhite', 'assignfeedback_pdf').'</a></li>';
            echo '<li><a href="#clear">'.get_string('colourclear', 'assignfeedback_pdf').'</a></li>';
            echo '<li class="separator"><a href="#deletecomment">'.get_string('deletecomment', 'assignfeedback_pdf').'</a></li>';
            echo '</ul>';
        }

        // Definition for 'resend' box.
        echo '<div id="sendfailed" style="display: none;"><p>'.get_string('servercommfailed', 'assignfeedback_pdf').'</p>';
        echo '<button id="sendagain">'.get_string('resend', 'assignfeedback_pdf').'</button>';
        echo '<button id="cancelsendagain">'.get_string('cancel', 'assignfeedback_pdf').'</button></div>';

        // Prepare all the parameters to pass into the javascript.
        $serverurl = new moodle_url('/mod/assign/feedback/pdf/updatecomment.php');
        $config = array(
            'id' => $cm->id,
            'submissionid' => $submission->id,
            'sesskey' => sesskey(),
            'pageno' => $pageno,
            'pagecount' => $pagecount,
            'serverurl' => $serverurl->out(),
            'blank_image' => $OUTPUT->pix_url('blank', 'assignfeedback_pdf')->out(),
            'image_path' => $CFG->wwwroot.'/mod/assign/feedback/pdf/pix/',
            'editing' => ($enableedit ? 1 : 0),
            // This adds a form to the page to simulate clicking at positions on the PDF.
            'behattest' => defined('BEHAT_TEST') || defined('BEHAT_SITE_RUNNING'),
        );

        $preferences = array(
            'colour' => 'yellow',
            'linecolour' => 'red',
            'stamp' => 'tick',
            'tool' => 'comment'
        );
        $userpreferences = array();
        foreach ($preferences as $preference => $default) {
            $userpreferences[$preference] = get_user_preferences("assignfeedback_pdf_{$preference}", $default);
            user_preference_allow_ajax_update("assignfeedback_pdf_{$preference}", PARAM_ALPHA);
        }

        $strings = array(
            array('servercommfailed', 'assignfeedback_pdf'),
            array('errormessage', 'assignfeedback_pdf'),
            array('okagain', 'assignfeedback_pdf'),
            array('emptyquicklist', 'assignfeedback_pdf'),
            array('emptyquicklist_instructions', 'assignfeedback_pdf'),
            array('findcommentsempty', 'assignfeedback_pdf'),
            array('annotationhelp', 'assignfeedback_pdf')
        );

        $jsmodule = array('name' => 'assignfeedback_pdf',
                          'fullpath' => new moodle_url('/mod/assign/feedback/pdf/scripts/annotate.js'),
                          'requires' => array('node', 'event', 'get', 'button', 'overlay',
                                              'dd-drag', 'dd-constrain', 'resize-plugin', 'io-base', 'json',
                                              'panel', 'button-plugin', 'button-group',
                                              'moodle-assignfeedback_pdf-menubutton'),
                          'strings' => $strings,
        );
        $PAGE->requires->js_init_call('uploadpdf_init', array($config, $userpreferences), true, $jsmodule);

        echo '</div>'; // Div: 'everything'.

        echo $OUTPUT->footer();
    }

    /**
     * Extract the URL to return to when annotation is complete.
     * @return moodle_url
     */
    protected function return_url() {
        $cm = $this->assignment->get_course_module();
        $redir = new moodle_url('/mod/assign/view.php', array('id' => $cm->id));
        $returnparams = optional_param('returnparams', null, PARAM_TEXT);
        if (!is_null($returnparams)) {
            $returnparams = explode('&amp;', $returnparams);
            foreach ($returnparams as $returnparam) {
                list($name, $value) = explode('=', $returnparam, 2);
                $redir->param($name, $value);
            }
        }
        return $redir;
    }

    /**
     * Return the controls toolbar
     * @param $submission
     * @param $user
     * @param $pageno
     * @param $enableedit
     * @param $showprevious
     * @return string
     */
    protected function output_controls($submission, $user, $pageno, $enableedit, $showprevious) {
        global $PAGE, $DB, $OUTPUT;

        $context = $this->assignment->get_context();

        $out = '';
        $saveopts = '';
        if ($enableedit) {
            // Save draft / generate response buttons.
            $saveopts .= html_writer::start_tag('form', array('action' => $PAGE->url->out_omit_querystring(),
                                                             'method' => 'post', 'target' => '_top'));
            $saveopts .= html_writer::input_hidden_params($PAGE->url);
            $saveopts .= html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'savedraft', 'value' => 1));
            $saveopts .= html_writer::empty_tag('input', array('type' => 'image', 'name' => 'savedraft_btn',
                                                              'value' => 'savedraft', 'id' => 'savedraft',
                                                              'src' => $OUTPUT->pix_url('savequit', 'assignfeedback_pdf'),
                                                              'title' => get_string('savedraft', 'assignfeedback_pdf')));
            $saveopts .= html_writer::end_tag('form');

            $saveopts .= "\n";
            $saveopts .= html_writer::start_tag('form', array('action' => $PAGE->url->out_omit_querystring(),
                                                             'method' => 'post', 'target' => '_top'));
            $saveopts .= html_writer::input_hidden_params($PAGE->url);
            $saveopts .= html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'generateresponse', 'value' => 1));
            $saveopts .= html_writer::empty_tag('input', array('type' => 'image', 'name' => 'generateresponse_btn',
                                                              'value' => 'generateresponse', 'id' => 'generateresponse',
                                                              'src' => $OUTPUT->pix_url('tostudent', 'assignfeedback_pdf'),
                                                              'title' => get_string('generateresponse', 'assignfeedback_pdf')));
            $saveopts .= "\n";
            $saveopts .= html_writer::end_tag('form');
        }

        // Output the 'Download original' button.
        $pdfurl = moodle_url::make_pluginfile_url($context->id, 'assignsubmission_pdf', ASSIGNSUBMISSION_PDF_FA_FINAL,
                                                  $submission->id, $this->get_subfolder(), ASSIGNSUBMISSION_PDF_FILENAME, true);
        $downloadorig = get_string('downloadoriginal', 'assignfeedback_pdf');
        if (!$enableedit) {
            $downloadorig = get_string('downloadresponse', 'assignfeedback_pdf');
            $pdfurl = moodle_url::make_pluginfile_url($context->id, 'assignfeedback_pdf', ASSIGNFEEDBACK_PDF_FA_RESPONSE,
                                                      $submission->id, $this->get_subfolder(), ASSIGNFEEDBACK_PDF_FILENAME, true);
        }
        $saveopts .= html_writer::start_tag('form', array('method' => 'get', 'action' => $pdfurl->out()));
        $saveopts .= html_writer::input_hidden_params($pdfurl);
        $saveopts .= html_writer::empty_tag('input', array('type' => 'image', 'name' => 'downloadpdf', 'id' => 'downloadpdf',
                                                          'src' => $OUTPUT->pix_url('download', 'assignfeedback_pdf'),
                                                          'title' => $downloadorig, 'alt' => $downloadorig));
        $saveopts .= html_writer::end_tag('form');

        // Show previous assignment.
        if (!$this->assignment->get_instance()->blindmarking && $enableedit && $user) {
            $ps_sql = "SELECT asn.id, asn.name
                       FROM {assign} asn
                       JOIN {assignsubmission_pdf} subp ON subp.assignment = asn.id
                       JOIN {assign_submission} sub ON sub.id = subp.submission
                       WHERE asn.course = ?
                       AND sub.userid = ?
                       AND asn.id != ?
                       ORDER BY sub.timemodified DESC;";
            $assignment = $this->assignment->get_instance();
            $course = $this->assignment->get_course();
            $previoussubs = $DB->get_records_sql_menu($ps_sql, array($course->id, $user->id, $assignment->id) );
            if ($previoussubs) {
                $showpreviousstr = get_string('showpreviousassignment', 'assignfeedback_pdf');;
                $saveopts .= html_writer::empty_tag('input', array('type' => 'submit', 'id' => 'showpreviousbutton',
                                                             'name' => 'showpreviousbutton', 'value' => $showpreviousstr));
                $list = html_writer::tag('li', get_string('previousnone', 'assignfeedback_pdf'), array('value' => '-1'));
                foreach ($previoussubs as $id => $previoussub) {
                    $list .= html_writer::tag('li', $previoussub, array('value' => $id));
                }
                $saveopts .= html_writer::tag('ul', $list, array('id' => 'showpreviousselect', 'class' => 'dropmenu'));
            }
        }

        $comments = $DB->get_records('assignfeedback_pdf_cmnt', array('submissionid' => $submission->id), 'pageno, posy, posx');
        $saveopts .= html_writer::tag('button', get_string('findcomments', 'assignfeedback_pdf'),
                                      array('id' => 'findcommentsbutton'));
        if (empty($comments)) {
            $outcomments = html_writer::tag('li', get_string('findcommentsempty', 'assignfeedback_pdf'), array('value' => '0:0'));
        } else {
            $outcomments = '';
            foreach ($comments as $comment) {
                $text = $comment->rawtext;
                if (strlen($text) > 40) {
                    $text = substr($text, 0, 39).'&hellip;';
                }
                $outcomments .= html_writer::tag('li', $comment->pageno.': '.format_string($text),
                                                 array('value' => "{$comment->pageno}:{$comment->id}"));
            }
        }
        $saveopts .= html_writer::tag('ul', $outcomments, array('id' => 'findcommentsselect', 'class' => 'dropmenu'));

        if (!$enableedit) {
            // If opening in same window - show 'back to comment list' link.
            if (array_key_exists('uploadpdf_commentnewwindow', $_COOKIE) && !$_COOKIE['uploadpdf_commentnewwindow']) {
                $url = new moodle_url('/mod/assign/feedback/pdf/editcomment.php',
                                      array('id' => $this->assignment->get_course_module()->id,
                                           'submissionid' => $submission->id,
                                           'action' => 'showprevious'));
                echo html_writer::link($url, get_string('backtocommentlist', 'assignfeedback_pdf'));
            }
        }

        if ($enableedit) {
            $helpicon = $OUTPUT->pix_icon('help', '');
            $saveopts .= '&nbsp;'.html_writer::link('#', $helpicon.' '.get_string('annotationhelp', 'assignfeedback_pdf'),
                                                    array('id' => 'annotationhelp'));
            $images = (object)array(
                'save' => $OUTPUT->pix_icon('savequit', '', 'assignfeedback_pdf'),
                'generate' => $OUTPUT->pix_icon('tostudent', '', 'assignfeedback_pdf'),
                'comment' => $OUTPUT->pix_icon('commenticon', '', 'assignfeedback_pdf'),
                'line' => $OUTPUT->pix_icon('lineicon', '', 'assignfeedback_pdf'),
                'rectangle' => $OUTPUT->pix_icon('rectangleicon', '', 'assignfeedback_pdf'),
                'oval' => $OUTPUT->pix_icon('ovalicon', '', 'assignfeedback_pdf'),
                'freehand' => $OUTPUT->pix_icon('freehandicon', '', 'assignfeedback_pdf'),
                'highlight' => $OUTPUT->pix_icon('highlighticon', '', 'assignfeedback_pdf'),
                'stamp' => $OUTPUT->pix_icon('stampicon', '', 'assignfeedback_pdf'),
                'erase' => $OUTPUT->pix_icon('eraseicon', '', 'assignfeedback_pdf'),
            );
            $saveopts .= html_writer::tag('div', get_string('annotationhelp_text', 'assignfeedback_pdf', $images),
                                          array('id' => 'annotationhelp_text', 'style' => 'display:none;'));
        }

        $out .= html_writer::tag('div', $saveopts, array('id' => 'saveoptions'));

        $tools = '';
        $tools .= $this->output_pageselector($submission, $pageno);

        if ($enableedit) {
            $tools .= $this->output_toolbar();
        }

        $out .= html_writer::tag('div', $tools, array('id' => 'toolbar-line2'));

        return $out;
    }

    /**
     * Return the next/previous buttons + drop-down page list
     * @param $submission
     * @param $pageno
     * @return string
     */
    protected function output_pageselector($submission, $pageno) {
        $prevstr = '&lt;-- '.get_string('previous', 'assignfeedback_pdf');
        $prevtipstr = get_string('keyboardprev', 'assignfeedback_pdf');
        $nextstr = get_string('next', 'assignfeedback_pdf').' --&gt;';
        $nexttipstr = get_string('keyboardnext', 'assignfeedback_pdf');
        $pagenos = range(1, $submission->numpages);
        $pagenos = array_combine($pagenos, $pagenos);
        $select = html_writer::select($pagenos, 'selectpage', $pageno, false, array('id' => 'selectpage'));

        $pageselector = '';
        $pageselector .= html_writer::tag('button', $prevstr, array('id' => 'prevpage', 'title' => $prevtipstr));
        $pageselector .= "\n";
        $pageselector .= html_writer::tag('span', $select,
                                          array('style' => 'position:relative;width:50px;display:inline-block;height:34px;'));
        $pageselector .= "\n";
        $pageselector .= html_writer::tag('button', $nextstr, array('id' => 'nextpage', 'title' => $nexttipstr));
        $pageselector .= "\n";

        return $pageselector;
    }

    /**
     * Return the second row of the controls: colour + tool selectors.
     * @return string
     */
    protected function output_toolbar() {
        global $OUTPUT;

        $tools = '';

        // Choose comment colour.
        $titlestr = get_string('commentcolour', 'assignfeedback_pdf');
        $tools .= html_writer::empty_tag('input', array('type' => 'image', 'id' => 'choosecolour',
                                                  'style' => 'line-height:normal;', 'name' => 'choosecolour',
                                                  'value' => '', 'title' => $titlestr));
        $colours = array('red', 'yellow', 'green', 'blue', 'white', 'clear');
        $list = '';
        foreach ($colours as $colour) {
            $colourimg = html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url($colour, 'assignfeedback_pdf')));
            $list .= html_writer::tag('li', $colourimg, array('class' => "choosecolour-{$colour}", 'value' => $colour));
        }
        $list = html_writer::tag('ul', $list, array('id' => 'choosecolourmenu', 'class' => 'dropmenu'));
        $tools .= $list;

        // Choose line colour.
        $titlestr = get_string('linecolour', 'assignfeedback_pdf');
        $tools .= html_writer::empty_tag('input', array('type' => 'image', 'id' => 'chooselinecolour',
                                                        'style' => 'line-height:normal;', 'name' => 'chooselinecolour',
                                                        'value' => '', 'title' => $titlestr));
        $colours = array('red', 'yellow', 'green', 'blue', 'white', 'black');
        $list = '';
        foreach ($colours as $colour) {
            $colourimg = html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url("line{$colour}", 'assignfeedback_pdf')));
            $list .= html_writer::tag('li', $colourimg, array('class' => "choosecolour-{$colour}", 'value' => $colour));
        }
        $list = html_writer::tag('ul', $list, array('id' => 'chooselinecolourmenu', 'class' => 'dropmenu'));
        $tools .= $list;

        // Stamps.
        $titlestr = get_string('stamp', 'assignfeedback_pdf');
        $tools .= html_writer::empty_tag('input', array('type' => 'image', 'id' => 'choosestamp',
                                                        'style' => 'line-height:normal;', 'name' => 'choosestamp',
                                                        'width' => '32px', 'height' => '32px',
                                                        'value' => '', 'title' => $titlestr));
        $stamps = AssignPDFLib::get_stamps();
        $list = '';
        foreach ($stamps as $stamp => $filename) {
            $stampimg = html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url("stamps/{$stamp}", 'assignfeedback_pdf'),
                                                           'width' => '32', 'height' => '32'));
            $list .= html_writer::tag('li', $stampimg, array('class' => "choosestamp-{$stamp}", 'value' => $stamp));
        }
        $list = html_writer::tag('ul', $list, array('id' => 'choosestampmenu', 'class' => 'dropmenu'));
        $tools .= $list;

        // Choose annotation type.
        $drawingtools = array('commenticon', 'lineicon', 'rectangleicon', 'ovalicon', 'freehandicon', 'highlighticon',
                              'stampicon', 'eraseicon');
        $list = '';
        foreach ($drawingtools as $drawingtool) {
            $item = html_writer::tag('button', '', array('name' => 'choosetoolradio', 'value' => $drawingtool,
                                                           'id' => $drawingtool,
                                                           'title' => get_string($drawingtool, 'assignfeedback_pdf')));
            $list .= $item;
        }
        $list = html_writer::tag('div', $list, array('id' => 'choosetoolgroup', 'class' => 'yui-buttongroup'));
        $tools .= $list;

        return $tools;
    }

    /**
     * Return a unique path for writing temporary files to
     * @param $submissionid
     * @return string
     */
    protected function get_temp_folder($submissionid) {
        global $CFG, $USER;

        $tempfolder = $CFG->dataroot.'/temp/assignfeedback_pdf/';
        $tempfolder .= sha1("{$submissionid}_{$USER->id}_".time()).'/';
        return $tempfolder;
    }

    /**
     * Get the image details from a file and return them.
     * @param stored_file $file
     * @param $pagecount
     * @return mixed array|false
     */
    protected static function get_image_details($file, $pagecount) {
        if ($imageinfo = $file->get_imageinfo()) {
            $imgurl = moodle_url::make_pluginfile_url($file->get_contextid(), $file->get_component(),
                                                      $file->get_filearea(), $file->get_itemid(),
                                                      $file->get_filepath(), $file->get_filename());
            // Prevent browser from caching image if it has changed.
            $imgurl->param('ts', $file->get_timemodified());
            return array($imgurl, $imageinfo['width'], $imageinfo['height'], $pagecount);
        }
        // Something went wrong.
        return false;
    }

    /**
     * Generate a page image (if it doesn't exist already) and return the details
     * @param int $pageno
     * @param object $submission
     * @return array|mixed - [ image url, width, height, total page count ]
     */
    protected function get_page_image($pageno, $submission) {
        global $CFG, $DB;

        require_once($CFG->dirroot.'/mod/assign/feedback/pdf/mypdflib.php');
        require_once($CFG->dirroot.'/mod/assign/submission/pdf/lib.php');

        $pagefilename = 'page'.$pageno.'.png';
        $pdf = new AssignPDFLib();

        $pagecount = $submission->numpages;

        $context = $this->assignment->get_context();
        $fs = get_file_storage();
        $subfile = $fs->get_file($context->id, 'assignsubmission_pdf', ASSIGNSUBMISSION_PDF_FA_FINAL, $submission->id,
                                 $this->get_subfolder(), ASSIGNSUBMISSION_PDF_FILENAME);
        if (!$subfile) {
            throw new moodle_exception('errornosubmission', 'assignfeedback_pdf');
        }

        // If pagecount is 0, then we need to skip down to the next stage to find the real page count.
        if ($pagecount && ($file = $fs->get_file($context->id, 'assignfeedback_pdf', ASSIGNFEEDBACK_PDF_FA_IMAGE,
                                                 $submission->id, $this->get_subfolder(), $pagefilename)) ) {
            if ($file->get_timemodified() < $subfile->get_timemodified()) {
                // Check the image file was last generated before the most recent PDF was generated.
                $file->delete();
            } else {
                if ($ret = self::get_image_details($file, $pagecount)) {
                    return $ret;
                }
            }
            // If the image is bad in some way, try to create a new image instead.
        }

        // Generate the image.
        $tempfolder = $this->get_temp_folder($submission->id);
        $imagefolder = $tempfolder.'img';
        if (!file_exists($imagefolder)) {
            if (!mkdir($imagefolder, 0777, true)) {
                $errdata = (object)array('temparea' => $imagefolder);
                throw new moodle_exception('errortempfolder', 'assignfeedback_pdf', '', null, $errdata);
            }
        }
        $pdffolder = $tempfolder.'sub';
        $pdffile = $pdffolder.'/submission.pdf';
        if (!file_exists($pdffolder)) {
            if (!mkdir($pdffolder, 0777, true)) {
                $errdata = (object)array('temparea' => $pdffolder);
                throw new moodle_exception('errortempfolder', 'assignfeedback_pdf', '', null, $errdata);
            }
        }

        $subfile->copy_content_to($pdffile);  // Copy the PDF out of the file storage, into the temp area.

        $pagecount = $pdf->set_pdf($pdffile, $pagecount); // Only loads the PDF if the pagecount is unknown (0).
        if (!$submission->numpages && $pagecount) {
            // Save the pagecount for future reference.
            $submission->numpages = $pagecount;
            $DB->set_field('assignsubmission_pdf', 'numpages', $pagecount, array('submission' => $submission->id));
        }
        if ($pageno > $pagecount) {
            @unlink($pdffile);
            @rmdir($imagefolder);
            @rmdir($pdffolder);
            @rmdir($tempfolder);
            return array(null, 0, 0, $pagecount);
        }

        $pdf->set_image_folder($imagefolder);
        if (!$imgname = $pdf->get_image($pageno)) { // Generate the image in the temp area.
            throw new moodle_exception('errorgenerateimage', 'assignfeedback_pdf');
        }

        $imginfo = array(
            'contextid' => $context->id,
            'component' => 'assignfeedback_pdf',
            'filearea' => ASSIGNFEEDBACK_PDF_FA_IMAGE,
            'itemid' => $submission->id,
            'filepath' => $this->get_subfolder(),
            'filename' => $pagefilename
        );
        $subfile = $fs->create_file_from_pathname($imginfo, $imagefolder.'/'.$imgname); // Copy the image into the file storage.

        // Delete the temporary files.
        @unlink($pdffile);
        @unlink($imagefolder.'/'.$imgname);
        @rmdir($imagefolder);
        @rmdir($pdffolder);
        @rmdir($tempfolder);

        if ($ret = self::get_image_details($subfile, $pagecount)) {
            return $ret;
        }
        return array(null, 0, 0, $pagecount);
    }

    /**
     * Output a list of comments written on a previous submission.
     * @param $submissionid
     */
    public function show_previous_comments($submissionid) {
        global $DB, $PAGE, $OUTPUT;

        $context = $this->assignment->get_context();
        require_capability('mod/assign:grade', $context);

        $assignment = $this->assignment->get_instance();
        $params = array('id' => $submissionid, 'assignment' => $assignment->id);
        $submission = $DB->get_record('assign_submission', $params, '*', MUST_EXIST);
        $user = $DB->get_record('user', array('id' => $submission->userid), '*', MUST_EXIST);
        $params = array('assignment' => $assignment->id, 'submission' => $submission->id);
        $submissionpdf = $DB->get_record('assignsubmission_pdf', $params, '*', MUST_EXIST);
        $submission->numpages = $submissionpdf->numpages;
        $cm = $this->assignment->get_course_module();

        $PAGE->set_pagelayout('popup');
        $PAGE->set_title(get_string('feedback', 'assignment').':'.fullname($user, true).':'.format_string($assignment->name));
        $PAGE->set_heading('');
        echo $OUTPUT->header();

        // Nasty javascript hack to stop the page being a minimum of 900 pixels wide.
        echo '<script type="text/javascript">
              document.getElementById("page-content").setAttribute("style", "min-width:0px;");
              </script>';

        echo $OUTPUT->heading(format_string($assignment->name), 2);

        // Add download link for submission.
        $fs = get_file_storage();
        if ( !($file = $fs->get_file($context->id, 'mod_assign', ASSIGNFEEDBACK_PDF_FA_RESPONSE, $submission->id,
                                     $this->get_subfolder(), ASSIGNFEEDBACK_PDF_FILENAME)) ) {
            $file = $fs->get_file($context->id, 'mod_assign', ASSIGNSUBMISSION_PDF_FA_FINAL, $submission->id,
                                  $this->get_subfolder(), ASSIGNSUBMISSION_PDF_FILENAME);
        }

        if ($file) {
            $pdfurl = moodle_url::make_pluginfile_url($file->get_contextid(), $file->get_component(),
                                                      $file->get_filearea(), $file->get_itemid(), $file->get_filepath(),
                                                      $file->get_filename(), true);
            echo html_writer::link($pdfurl, get_string('downloadoriginal', 'assignsbmission_pdf'));
        }

        // Output the 'Open in new window' check box.
        $checked = "checked='checked'";
        if (array_key_exists('uploadpdf_commentnewwindow', $_COOKIE)) {
            if (!$_COOKIE['uploadpdf_commentnewwindow']) {
                $checked = '';
            }
        }
        $onclick = "var checked = this.checked ? 1 : 0; document.cookie='uploadpdf_commentnewwindow='+checked; return true;";
        echo '<br/><input type="checkbox" name="opennewwindow" id="opennewwindow" '.$checked.' onclick="'.$onclick.'" />';
        echo '<label for="opennewwindow">'.get_string('openlinknewwindow', 'assignfeedback_pdf').'</label><br/>';

        // Put all the comments in a table.
        $comments = $DB->get_records('assignfeedback_pdf_cmnt', array('submissionid' => $submission->id), 'pageno, posy');
        if (!$comments) {
            echo '<p>'.get_string('nocomments', 'assignfeedback_pdf').'</p>';
        } else {
            $style1 = ' style="border: black 1px solid;"';
            $style2 = ' style="border: black 1px solid; text-align: center;" ';
            echo '<table'.$style1.'><tr><th'.$style1.'>'.get_string('pagenumber', 'assignfeedback_pdf').'</th>';
            echo '<th'.$style1.'>'.get_string('comment', 'assignfeedback_pdf').'</th></tr>';
            foreach ($comments as $comment) {
                $linkurl = new moodle_url('/mod/assign/feedback/pdf/editcomment.php', array('id' => $cm->id,
                                                                                           'submissionid' => $submission->id,
                                                                                           'pageno' => $comment->pageno,
                                                                                           'commentid' => $comment->id,
                                                                                           'action' => 'showpreviouspage'));

                $title = fullname($user, true).':'.format_string($assignment->name).':'.$comment->pageno;
                $onclick = "var el = document.getElementById('opennewwindow'); if (el && !el.checked) { return true; } ";
                $onclick .= "this.target='showpage{$submission->id}'; ";
                $onclick .= "return openpopup('".$linkurl->out(false)."', 'showpage{$submission->id}', ";
                $onclick .= "'menubar=0,location=0,scrollbars,resizable,width=700,height=700', 0)";

                $link = '<a title="'.$title.'" href="'.$linkurl->out().'" onclick="'.$onclick.'">'.$comment->pageno.'</a>';

                echo '<tr><td'.$style2.'>'.$link.'</td>';
                echo '<td'.$style1.'>'.s($comment->rawtext).'</td></tr>';
            }
            echo '</table>';
        }
        echo $OUTPUT->footer();
    }

    /**
     * Generate a new PDF with the original submission + annotations
     * @param $submissionid
     * @return bool - true if successful
     */
    public function create_response_pdf($submissionid) {
        global $DB;

        $context = $this->assignment->get_context();
        $fs = get_file_storage();
        $file = $fs->get_file($context->id, 'assignsubmission_pdf', ASSIGNSUBMISSION_PDF_FA_FINAL, $submissionid,
                              $this->get_subfolder(), ASSIGNSUBMISSION_PDF_FILENAME);
        if (!$file) {
            throw new moodle_exception('errornosubmission2', 'assignfeedback_pdf');
        }
        $temparea = $this->get_temp_folder($submissionid).'sub';
        if (!file_exists($temparea)) {
            if (!mkdir($temparea, 0777, true)) {
                throw new moodle_exception('errortempfolder', 'assignfeedback_pdf');
            }
        }
        $sourcefile = $temparea.'/submission.pdf';
        $destfile = $temparea.'/response.pdf';

        $file->copy_content_to($sourcefile);

        $mypdf = new AssignPDFLib();
        $mypdf->load_pdf($sourcefile);

        $comments = $DB->get_records('assignfeedback_pdf_cmnt', array('submissionid' => $submissionid), 'pageno');
        $annotations = $DB->get_records('assignfeedback_pdf_annot', array('submissionid' => $submissionid), 'pageno');

        if ($comments) {
            $comment = current($comments);
        } else {
            $comment = false;
        }
        if ($annotations) {
            $annotation = current($annotations);
        } else {
            $annotation = false;
        }
        while (true) {
            if ($comment) {
                $nextpage = $comment->pageno;
                if ($annotation) {
                    if ($annotation->pageno < $nextpage) {
                        $nextpage = $annotation->pageno;
                    }
                }
            } else {
                if ($annotation) {
                    $nextpage = $annotation->pageno;
                } else {
                    break;
                }
            }

            while ($nextpage > $mypdf->current_page()) {
                if (!$mypdf->copy_page()) {
                    break 2;
                }
            }

            while (($comment) && ($comment->pageno == $mypdf->current_page())) {
                $mypdf->add_comment($comment->rawtext, $comment->posx, $comment->posy, $comment->width, $comment->colour);
                $comment = next($comments);
            }

            while (($annotation) && ($annotation->pageno == $mypdf->current_page())) {
                if ($annotation->type == 'freehand') {
                    $path = explode(',', $annotation->path);
                    $mypdf->add_annotation(0, 0, 0, 0, $annotation->colour, 'freehand', $path);
                } else {
                    $mypdf->add_annotation($annotation->startx, $annotation->starty, $annotation->endx,
                                           $annotation->endy, $annotation->colour, $annotation->type, $annotation->path);
                }
                $annotation = next($annotations);
            }
        }

        $mypdf->copy_remaining_pages();
        $mypdf->save_pdf($destfile);

        // Delete any previous response file.
        if ($file = $fs->get_file($context->id, 'assignfeedback_pdf', ASSIGNFEEDBACK_PDF_FA_RESPONSE, $submissionid,
                                  $this->get_subfolder(), ASSIGNFEEDBACK_PDF_FILENAME) ) {
            $file->delete();
        }

        $fileinfo = array(
            'contextid' => $context->id,
            'component' => 'assignfeedback_pdf',
            'filearea' => ASSIGNFEEDBACK_PDF_FA_RESPONSE,
            'itemid' => $submissionid,
            'filepath' => $this->get_subfolder(),
            'filename' => ASSIGNFEEDBACK_PDF_FILENAME
        );
        $fs->create_file_from_pathname($fileinfo, $destfile);

        @unlink($sourcefile);
        @unlink($destfile);
        @rmdir($temparea);
        @rmdir(dirname($temparea));

        return true;
    }

    /**
     * Process the AJAX requests - retrieve/update comments, annotations, etc.
     * @param int $submissionid
     * @param int $pageno
     */
    public function update_comment_page($submissionid, $pageno) {
        global $USER, $DB;

        $resp = array('error' => 0);

        require_sesskey();

        // Retrieve all database records.
        $assignment = $this->assignment->get_instance();
        $params = array('id' => $submissionid, 'assignment' => $assignment->id);
        $submission = $DB->get_record('assign_submission', $params, '*', MUST_EXIST);
        $user = null;
        $group = null;
        if ($submission->userid) {
            $user = $DB->get_record('user', array('id' => $submission->userid), '*', MUST_EXIST);
        } else if ($submission->groupid) {
            $group = $DB->get_record('groups', array('id' => $submission->groupid), '*', MUST_EXIST);
        }
        $params = array('assignment' => $assignment->id, 'submission' => $submission->id);
        $submissionpdf = $DB->get_record('assignsubmission_pdf', $params, '*', MUST_EXIST);
        $submission->numpages = $submissionpdf->numpages;
        $context = $this->assignment->get_context();

        $action = optional_param('action', '', PARAM_ALPHA);

        if ($action == 'getcomments' || $action == 'getimageurl') {
            if ($user) {
                $ownsubmission = ($user->id == $USER->id);
            } else {
                $ownsubmission = !$group || groups_is_member($group->id);
            }
            if ($ownsubmission) {
                // Students can view comments / images for their own assignment.
                require_capability('mod/assign:submit', $context);
            } else {
                require_capability('mod/assign:grade', $context);
            }
        } else {
            // All annotation requests need to have 'grade' capability.
            require_capability('mod/assign:grade', $context);
        }

        if ($action == 'update') {
            $comment = new stdClass();
            $comment->id = optional_param('comment_id', -1, PARAM_INT);
            $comment->posx = optional_param('comment_position_x', -1, PARAM_INT);
            $comment->posy = optional_param('comment_position_y', -1, PARAM_INT);
            $comment->width = optional_param('comment_width', -1, PARAM_INT);
            $comment->rawtext = optional_param('comment_text', null, PARAM_TEXT);
            $comment->colour = optional_param('comment_colour', 'yellow', PARAM_TEXT);
            $comment->pageno = $pageno;
            $comment->submissionid = $submission->id;

            if (($comment->posx < 0) || ($comment->posy < 0) || ($comment->width < 0) || ($comment->rawtext === null)) {
                throw new moodle_exception('missingcommentdata', 'assignfeedback_pdf');
            }

            if ($comment->id === -1) {
                // Insert new comment.
                unset($comment->id);
                $oldcomments = $DB->get_records('assignfeedback_pdf_cmnt', array('submissionid' => $comment->submissionid,
                                                                                'pageno' => $comment->pageno,
                                                                                'posx' => $comment->posx,
                                                                                'posy' => $comment->posy));
                foreach ($oldcomments as $oldcomment) {
                    if ($oldcomment->rawtext == $comment->rawtext) {
                        // Avoid inserting duplicate comments (likely to be due to network glitch).
                        $comment->id = reset(array_keys($oldcomments));
                        break;
                    }
                }
                if (!isset($comment->id)) {
                    $comment->id = $DB->insert_record('assignfeedback_pdf_cmnt', $comment);
                }
            } else {
                // Update old comment.
                $oldcomment = $DB->get_record('assignfeedback_pdf_cmnt', array('id' => $comment->id));
                if (!$oldcomment) {
                    // Comment not found - create a new one.
                    unset($comment->id);
                    $comment->id = $DB->insert_record('assignfeedback_pdf_cmnt', $comment);
                } else if (($oldcomment->submissionid != $submission->id) || ($oldcomment->pageno != $pageno)) {
                    throw new moodle_exception('badcommentid', 'assignfeedback_pdf');
                } else {
                    $DB->update_record('assignfeedback_pdf_cmnt', $comment);
                }
            }

            $resp['id'] = $comment->id;

        } else if ($action == 'getcomments') {
            $comments = $DB->get_records('assignfeedback_pdf_cmnt', array('submissionid' => $submission->id,
                                                                         'pageno' => $pageno));
            $respcomments = array();
            foreach ($comments as $comment) {
                $respcomment = array();
                $respcomment['id'] = ''.$comment->id;
                $respcomment['text'] = $comment->rawtext;
                $respcomment['width'] = $comment->width;
                $respcomment['position'] = array('x'=> $comment->posx, 'y'=> $comment->posy);
                $respcomment['colour'] = $comment->colour;
                $respcomments[] = $respcomment;
            }
            $resp['comments'] = $respcomments;

            $annotations = $DB->get_records('assignfeedback_pdf_annot', array('submissionid' => $submission->id,
                                                                             'pageno' => $pageno));
            $respannotations = array();
            foreach ($annotations as $annotation) {
                $respannotation = array();
                $respannotation['id'] = ''.$annotation->id;
                $respannotation['type'] = $annotation->type;
                if ($annotation->type == 'freehand') {
                    $respannotation['path'] = $annotation->path;
                    if (is_null($annotation->path)) {
                        $DB->delete_records('assignfeedback_pdf_annot', array('id'=>$annotation->id));
                        continue;
                    }
                } else {
                    $respannotation['coords'] = array('startx'=> $annotation->startx, 'starty'=> $annotation->starty,
                                                      'endx'=> $annotation->endx, 'endy'=> $annotation->endy );
                }
                if ($annotation->type == 'stamp') {
                    $respannotation['path'] = $annotation->path;
                }
                $respannotation['colour'] = $annotation->colour;
                $respannotations[] = $respannotation;
            }
            $resp['annotations'] = $respannotations;

        } else if ($action == 'delete') {
            $commentid = required_param('commentid', PARAM_INT);
            $DB->delete_records('assignfeedback_pdf_cmnt', array('id' => $commentid,
                                                                'submissionid' => $submission->id,
                                                                'pageno' => $pageno));

        } else if ($action == 'getquicklist') {

            $quicklist = $DB->get_records('assignfeedback_pdf_qcklst', array('userid' => $USER->id), 'id');
            $respquicklist = array();
            foreach ($quicklist as $item) {
                $respitem = array();
                $respitem['id'] = ''.$item->id;
                $respitem['text'] = $item->text;
                $respitem['width'] = $item->width;
                $respitem['colour'] = $item->colour;
                $respquicklist[] = $respitem;
            }
            $resp['quicklist'] = $respquicklist;

        } else if ($action == 'addtoquicklist') {

            $item = new stdClass();
            $item->userid = $USER->id;
            $item->width = required_param('width', PARAM_INT);
            $item->text = required_param('text', PARAM_TEXT);
            $item->colour = optional_param('colour', 'yellow', PARAM_TEXT);

            if ($item->width < 0 || empty($item->text)) {
                throw new moodle_exception('missingquicklistdata', 'assignfeedback_pdf');
            }

            $item->id = $DB->insert_record('assignfeedback_pdf_qcklst', $item);
            $resp['item'] = $item;

        } else if ($action == 'removefromquicklist') {

            $itemid = required_param('itemid', PARAM_INT);
            $DB->delete_records('assignfeedback_pdf_qcklst', array('id' => $itemid, 'userid' => $USER->id));
            $resp['itemid'] = $itemid;

        } else if ($action == 'getimageurl') {

            if ($pageno < 1) {
                throw new moodle_exception('pagenumbertoosmall', 'assignfeedback_pdf');
            }

            list($imageurl, $imgwidth, $imgheight, $pagecount) = $this->get_page_image($pageno, $submission);

            if ($pageno > $pagecount) {
                $info = (object)array('pageno' => $pageno, 'pagecount' => $pagecount);
                throw new moodle_exception('pagenumbertoobig', 'assignfeedback_pdf', $info);
            }

            $resp['image'] = new stdClass();
            $resp['image']->url = $imageurl->out();
            $resp['image']->width = $imgwidth;
            $resp['image']->height = $imgheight;

        } else if ($action == 'addannotation') {

            $annotation = new stdClass();
            $annotation->startx = optional_param('annotation_startx', -1, PARAM_INT);
            $annotation->starty = optional_param('annotation_starty', -1, PARAM_INT);
            $annotation->endx = optional_param('annotation_endx', -1, PARAM_INT);
            $annotation->endy = optional_param('annotation_endy', -1, PARAM_INT);
            $annotation->path = optional_param('annotation_path', null, PARAM_TEXT);
            $annotation->colour = optional_param('annotation_colour', 'red', PARAM_TEXT);
            $annotation->type = optional_param('annotation_type', 'line', PARAM_TEXT);
            $annotation->id = optional_param('annotation_id', -1, PARAM_INT);
            $annotation->pageno = $pageno;
            $annotation->submissionid = $submission->id;

            if (!in_array($annotation->type, array('freehand', 'line', 'oval', 'rectangle', 'highlight', 'stamp'))) {
                throw new moodle_exception("badtype", 'assignfeedback_pdf', $annotation->type);
            }

            if ($annotation->type == 'freehand') {
                if (!$annotation->path) {
                    throw new moodle_exception('missingannotationdata', 'assignfeedback_pdf');
                }
                // Double-check path is valid list of points.
                $points = explode(',', $annotation->path);
                if (count($points)%2 != 0) {
                    throw new moodle_exception('badcoordinate', 'assignfeedback_pdf');
                }
                foreach ($points as $point) {
                    if (!preg_match('/^\d+$/', $point)) {
                        throw new moodle_exception('badpath', 'assignfeedback_pdf');
                    }
                }
            } else {
                if ($annotation->type != 'stamp') {
                    $annotation->path = null;
                }
                if (($annotation->startx < 0) || ($annotation->starty < 0) || ($annotation->endx < 0) || ($annotation->endy < 0)) {
                    if ($annotation->id < 0) {
                        throw new moodle_exception('missingannotationdata', 'assignfeedback_pdf');
                    } else {
                        // OK not to send these when updating a line.
                        unset($annotation->startx);
                        unset($annotation->starty);
                        unset($annotation->endx);
                        unset($annotation->endy);
                    }
                }
            }

            if ($annotation->id === -1) {
                unset($annotation->id);
                $annotation->id = $DB->insert_record('assignfeedback_pdf_annot', $annotation);
            } else {
                $oldannotation = $DB->get_record('assignfeedback_pdf_annot', array('id' => $annotation->id) );
                if (!$oldannotation) {
                    unset($annotation->id);
                    $annotation->id = $DB->insert_record('assignfeedback_pdf_annot', $annotation);
                } else if (($oldannotation->submissionid != $submission->id) || ($oldannotation->pageno != $pageno)) {
                    throw new moodle_exception('badannotationid', 'assignfeedback_pdf');
                } else {
                    $DB->update_record('assignfeedback_pdf_annot', $annotation);
                }
            }

            $resp['id'] = $annotation->id;

        } else if ($action == 'removeannotation') {

            $annotationid = required_param('annotationid', PARAM_INT);
            $DB->delete_records('assignfeedback_pdf_annot', array('id' => $annotationid,
                                                                 'submissionid' => $submission->id,
                                                                 'pageno' => $pageno));
        } else {
            throw new moodle_exception('badaction', 'assignfeedback_pdf', $action);
        }

        echo json_encode($resp);
    }

    /**
     * Return the subfolder that contains the details for this submission (was intended to handle
     * resubmissions, but that is already implemented for all assignment types in Moodle 2.5)
     * @return string
     */
    protected function get_subfolder() {
        return '/1/';
    }

    /**
     * Delete old page image files after 3 weeks.
     */
    public static function cron() {
        global $DB;

        if ($lastcron = get_config('assignfeedback_pdf', 'lastcron')) {
            if ($lastcron + 86400 > time()) { /* Only check once a day for images */
                return;
            }
        }

        echo "Clear up images generated for pdf assignments\n";

        $fs = get_file_storage();

        $deletetime = time() - (3 * WEEKSECS);

        // Ideally we would use: $fs->get_area_files
        // However, this does not allow retrieval of files by timemodified.
        $to_clear = $DB->get_records_select('files', "component = 'assignfeedback_pdf' AND filearea = 'image' AND timemodified < ?",
                                            array($deletetime));
        $tmpl_to_clear = $DB->get_records_select('files', "component = 'assignsubmission_pdf'
                                                           AND filearea = 'previewimage'
                                                           AND timemodified < ?",
                                                 array($deletetime));
        $to_clear = array_merge($to_clear, $tmpl_to_clear);

        foreach ($to_clear as $filerecord) {
            $file = $fs->get_file_by_hash($filerecord->pathnamehash);
            if ($file && !$file->is_directory()) {
                $file->delete();
            }
        }

        $lastcron = time(); // Remember when the last cron job ran.
        set_config('lastcron', $lastcron, 'assignfeedback_pdf');
    }

    /**
     * Delete all generated images for a particular submission.
     * (Intended for use if there is a problem with the generated images).
     * @param $submissionid
     * @param $nextaction
     */
    public function clear_image_cache($submissionid, $nextaction) {
        global $PAGE;

        $context = $this->assignment->get_context();
        $fs = get_file_storage();
        $fs->delete_area_files($context->id, 'assignfeedback_pdf', ASSIGNFEEDBACK_PDF_FA_IMAGE, $submissionid);

        $redir = new moodle_url($PAGE->url);
        if ($nextaction) {
            $redir->param('action', $nextaction);
        }
        redirect($redir);
    }

    /**
     * Output an image browser for all the pages in the given submission.
     * (Intended for debugging purposes)
     * @param $submissionid
     * @param $pageno
     */
    public function browse_images($submissionid, $pageno) {
        global $DB, $OUTPUT, $PAGE;

        $assignment = $this->assignment->get_instance();
        $params = array('id' => $submissionid, 'assignment' => $assignment->id);
        $submission = $DB->get_record('assign_submission', $params, '*', MUST_EXIST);
        $params = array('assignment' => $assignment->id, 'submission' => $submission->id);
        $submissionpdf = $DB->get_record('assignsubmission_pdf', $params, '*', MUST_EXIST);
        $submission->numpages = $submissionpdf->numpages;

        list($imageurl, $imgwidth, $imgheight, $pagecount) = $this->get_page_image($pageno, $submission);
        $baseurl = new moodle_url($PAGE->url, array('action' => 'browseimages'));
        $clearurl = new moodle_url($PAGE->url, array('action' => 'clearcache', 'nextaction' => 'browseimages'));

        $PAGE->set_pagelayout('embedded');

        echo $OUTPUT->header();
        $paging = '';
        for ($i=1; $i<=$pagecount; $i++) {
            if ($pageno != $i) {
                $pageurl = new moodle_url($baseurl, array('pageno' => $i));
                $paging .= html_writer::link($pageurl, $i).' ';
            } else {
                $paging .= $i.' ';
            }
        }
        echo html_writer::tag('p', $paging);

        echo html_writer::tag('p', html_writer::link($clearurl, get_string('clearimagecache', 'assignfeedback_pdf')));

        echo html_writer::empty_tag('img', array('src' => $imageurl, 'width' => $imgwidth, 'height' => $imgheight));

        echo html_writer::empty_tag('br');

        echo $OUTPUT->footer();
    }
}
