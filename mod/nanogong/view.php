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
 * Prints a particular instance of nanogong
 *
 * @author     Ning 
 * @author     Gibson
 * @package    mod
 * @subpackage nanogong
 * @copyright  2012 The Gong Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @version    4.2.2
 */

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');
require_once(dirname(__FILE__).'/locallib.php');

$id = optional_param('id', 0, PARAM_INT); // course_module ID, or
$n  = optional_param('n', 0, PARAM_INT);  // nanogong instance ID
$action = optional_param('action', '', PARAM_TEXT);

if ($id) {
    if (! $cm = get_coursemodule_from_id('nanogong', $id)) {
        print_error('invalidcoursemodule');
    }
    if (! $course = $DB->get_record('course', array('id'=>$cm->course))) {
        print_error('coursemisconf');
    }
    if (! $nanogong = $DB->get_record('nanogong', array('id'=>$cm->instance))) {
        print_error('invalidid', 'nanogong');
    }
} else {
    if (! $nanogong = $DB->get_record('nanogong', array('id'=>$n))) {
        print_error('invalidid', 'nanogong');
    }
    if (! $course = $DB->get_record('course', array('id'=>$nanogong->course))) {
        print_error('coursemisconf');
    }
    if (! $cm = get_coursemodule_from_instance('nanogong', $nanogong->id, $course->id)) {
        print_error('invalidcoursemodule');
    }
}

require_login($course, true, $cm);
$context = get_context_instance(CONTEXT_MODULE, $cm->id);
require_capability('mod/nanogong:view', $context);

add_to_log($course->id, 'nanogong', 'view', "view.php?id={$cm->id}", $nanogong->name, $cm->id);

$GLOBALS['HTML_QUICKFORM_ELEMENT_TYPES']['applet'] = array('quickform/applet.php','HTML_QuickForm_applet');

/// Print the page header

$PAGE->set_url('/mod/nanogong/view.php', array('id'=>$cm->id));
$PAGE->set_title(format_string($nanogong->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);

// Language strings
$PAGE->requires->strings_for_js(array('show', 'hide'), 'nanogong');

// Initialize the NanoGong JavaScript
$PAGE->requires->js_init_call('M.mod_nanogong.init', array($PAGE->url->out()));

// Output starts here
echo $OUTPUT->header();

echo '<table align="center" cellspacing="0" cellpadding="0"><tr><td><b>' . get_string('modulenamefull', 'nanogong') . '</b></td></tr></table>';
if ($nanogong->intro) {
    echo $OUTPUT->box_start('generalbox boxaligncenter', 'intro');
    echo format_module_intro('nanogong', $nanogong, $cm->id);
    echo $OUTPUT->box_end();
    echo '<br >';
}

echo $OUTPUT->box_start();
echo '<table align="center" cellspacing="0" cellpadding="0">';
echo '<tr><td align="right"><b>' . get_string('maxdurationdetail', 'nanogong') . ':</b></td><td>';
echo $nanogong->maxduration;
echo '</td></tr><tr><td align="right"><b>' . get_string('maxnumberdetail', 'nanogong') . ':</b></td><td>';
if ($nanogong->maxnumber) {
    echo $nanogong->maxnumber;
}
else {
    echo get_string('nolimitation', 'nanogong');
}
echo '</td></tr>';
echo '</td></tr><tr><td align="right"><b>' . get_string('maxgrade', 'nanogong') . ':</b></td><td>';
echo $nanogong->grade;
echo '</td></tr>';
if ($nanogong->timeavailable || $nanogong->timedue) {
    if ($nanogong->timeavailable) {
        echo '<tr><td align="right"><b>' . get_string('availabledate','nanogong') . ':</b></td>';
        echo '<td>' . userdate($nanogong->timeavailable) . '</td></tr>';
    }
    if ($nanogong->timedue) {
        echo '<tr><td align="right"><b>' . get_string('duedate','nanogong') . ':</b></td>';
        echo '<td>' . userdate($nanogong->timedue) . '</td></tr>';
    }
}
echo '</table>';
echo $OUTPUT->box_end();

///
/// Capabilities of students
///
//if (has_capability('mod/nanogong:submit', $context) && is_enrolled($coursecontext, $USER)) {
if (has_capability('mod/nanogong:submit', $context)) {
    $time = time();
    $isavailable = true;
    if ($nanogong->timeavailable > $time) $isavailable = false;
    if ($nanogong->timedue && $time > $nanogong->timedue && $nanogong->preventlate != 0) $isavailable = false;

    $submission = $DB->get_record('nanogong_messages', array('nanogongid'=>$nanogong->id, 'userid'=>$USER->id));
    
    ///
    /// Insert a deleted message
    ///
    if ($action === 'insertmessage') {
        if (!$isavailable) {
            print_error('notavailable', 'nanogong', $PAGE->url);
        }

        if ($submission && $submission->locked) {
            print_error('studentlocked', 'nanogong', $PAGE->url);
        }

        $title = optional_param('title', '', PARAM_TEXT);
        $name = optional_param('name', '', PARAM_TEXT);
        if ($title && $name) {
            nanogong_check_content($name, $nanogong->id, $USER->id, 0);    
            $nanogongimg = '<img title="NanoGongItem" src="pix/speaker.gif" style="vertical-align: middle" alt="' . $name . '" />';
            $submission->message .= '<p title="NanoGong Title">' . $title . ' ' . $nanogongimg . '</p>';
            $DB->update_record('nanogong_messages', $submission);
            
            add_to_log($course->id, 'nanogong', 'update', 'view.php?n='.$nanogong->id, $nanogong->id, $cm->id);
        }

        redirect(new moodle_url($PAGE->url));
    }
    
    ///
    /// Save a new message
    ///
    if ($action === 'savemessage') {
        if (!$isavailable) {
            print_error('notavailable', 'nanogong', $PAGE->url);
        }

        if ($submission && $submission->locked) {
            print_error('studentlocked', 'nanogong', $PAGE->url);
        }

        $url = required_param('url', PARAM_TEXT);
        $title = required_param('title', PARAM_TEXT);

        // Check for empty title
        $title = preg_replace('/(^\s+)|(\s+$)/us', '', $title); 
        if ($title == '') {
            print_error('voicetitle', 'nanogong', $PAGE->url);
        }

        $nanogongimg = '<img title="NanoGongItem" src="pix/speaker.gif" style="vertical-align: middle" alt="' . $url . '" />';
        $nanogongform = '<p title="NanoGong Title">' . $title . ' ' . $nanogongimg . '</p>';

        // Create a submission if none exists
        if (!$submission) {
            $submission = new stdClass();
            $submission->nanogongid       = $nanogong->id;
            $submission->userid           = $USER->id;
            $submission->message          = '';
            $submission->supplement       = '';
            $submission->supplementformat = FORMAT_HTML;
            $submission->audio            = '';
            $submission->comments         = '';
            $submission->commentsformat   = FORMAT_HTML;
            $submission->commentedby      = 0;
            $submission->grade            = -1;
            $submission->timestamp        = time();
            $submission->locked           = false;
            $submission->id = $DB->insert_record("nanogong_messages", $submission);
        }

        // Save the new message in DB
        if (!strpos($submission->message, $nanogongform)) {
            $submission->message .= $nanogongform;

            $DB->update_record('nanogong_messages', $submission);
            add_to_log($course->id, 'nanogong', 'update', 'view.php?n='.$nanogong->id, $nanogong->id, $cm->id);
    
            $nanogongaudio = new stdClass();
            $nanogongaudio->nanogongid   = $nanogong->id;
            $nanogongaudio->userid       = $USER->id;
            $nanogongaudio->type         = 1;
            $nanogongaudio->title        = $title;
            $nanogongaudio->name         = $url;
            $nanogongaudio->timecreated  = time();
            $nanogongaudio->id = $DB->insert_record("nanogong_audios", $nanogongaudio);
            $DB->update_record('nanogong_audios', $nanogongaudio);    
        }

        redirect(new moodle_url($PAGE->url));
    }
    
    ///
    /// Delete a message
    ///
    if ($submission && $action === 'deletemessage') {
        if ($submission->locked) {
            print_error('studentlocked', 'nanogong', $PAGE->url);
        }

        $filename = optional_param('filename', '', PARAM_TEXT);

        if ($filename) {
            nanogong_check_content($filename, $nanogong->id, $USER->id, 1);

            $nanogongaudios = $DB->get_records('nanogong_audios', array('nanogongid'=>$nanogong->id));
            foreach ($nanogongaudios as $nanogongaudio) {
                if (strcmp($nanogongaudio->name, $filename) == 0) {
                    break;
                }
            }

            preg_match('/<img.*?title="NanoGongItem".*?alt="/', $submission->message, $m);
            $nanogongvoice = $m[0] . $filename . '" />';
            $nanogongform = '<p title="NanoGong Title">' . $nanogongaudio->title . ' ' . $nanogongvoice . '</p>';
        
            $submission->message = str_replace($nanogongform, '', $submission->message);
            $DB->update_record('nanogong_messages', $submission);
        
            add_to_log($course->id, 'nanogong', 'update', 'view.php?n='.$nanogong->id, $nanogong->id, $cm->id);
        }

        redirect(new moodle_url($PAGE->url));
    }

    ///
    /// Handle the message form
    ///
    if ($submission && $action === 'showmessageform') {
        if (!$isavailable) {
            print_error('notavailable', 'nanogong', $PAGE->url);
        }

        if ($submission->locked) {
            print_error('studentlocked', 'nanogong', $PAGE->url);
        }

        // Prepare the message form
        $editoroptions = array(
            'noclean'   => false,
            'maxfiles'  => EDITOR_UNLIMITED_FILES,
            'maxbytes'  => $course->maxbytes,
            'context'   => $context
        );
    
        $data = new stdClass();
        $data->action           = 'showmessageform';
        $data->id               = $cm->id;
        $data->maxduration      = $nanogong->maxduration;
        $data->sid              = $submission->id;
        $data->supplement       = $submission->supplement;
        $data->supplementformat = $submission->supplementformat;
        $data = file_prepare_standard_editor($data, 'supplement', $editoroptions, $context, 'mod_nanogong', 'message', $data->sid);

        $supplementform = new mod_nanogong_supplement_form(null, array($data, $editoroptions));

        if ($supplementform->is_cancelled()) {
            redirect(new moodle_url($PAGE->url));
        }

        if ($supplementform->is_submitted()) {
            $data = $supplementform->get_data();
            $data = file_postupdate_standard_editor($data, 'supplement', $editoroptions, $context, 'mod_nanogong', 'message', $submission->id);

            $submission->supplement = $data->supplement;
            $submission->supplementformat = $data->supplementformat;

            $DB->update_record('nanogong_messages', $submission);
        
            //TODO fix log actions - needs db upgrade
            add_to_log($course->id, 'nanogong', 'update', 'view.php?n='.$nanogong->id, $nanogong->id, $cm->id);
        
            redirect(new moodle_url($PAGE->url));
        }
    }

    ///
    /// Message submission form
    ///
    if (!$submission || (!$submission->locked && ($submission->message == '' || $action === 'showsubmitform'))) {
        if ($isavailable) {
            if ($action == 'showsubmitform') {
                $default = substr_count($submission->message,'NanoGongItem') + 1;
                $cancel = true;
            }
            else {
                $default = 1;
                $cancel = false;
            }

            echo '<br >';
            echo $OUTPUT->box_start('generalbox', 'submitform');
            nanogong_student_submit_form($cm->id, $default, $nanogong->maxduration, $cancel);
            echo $OUTPUT->box_end();
        }
        else {
            echo '<p><i>' . get_string('notavailable', 'nanogong') . '</i></p>';
        }
    }

    ///
    /// List of recordings
    ///
    if ($submission && $submission->message <> '') {
        $filename = optional_param('filename', '', PARAM_TEXT);

        echo '<br >';
        echo $OUTPUT->box_start('generalbox', 'submit');

        echo '<table align="center" cellspacing="0" cellpadding="0" width="100%"><tr>';
        echo '<td><b>' . get_string ('tablemessage', 'nanogong') . '</b></td>';
        echo '<td align="right">';
        echo '<a id="submissionarea_icon" class="nanogong_showhide_icon" href="javascript:;"><img src="pix/switch_minus.gif" alt="" title="" /></a>';
        echo '</td></tr></table>';
        echo '<div id="submissionarea">';

        echo '<table align="center" cellspacing="0" cellpadding="0">';

        // Display the delete confirmation
        if ($action === 'confirmdelete') {
            echo '<tr><td colspan="2" align="center">';
            echo '<font color="red">' . get_string('checkdeletemessage', 'nanogong') . '</font></td><td>';
            echo $OUTPUT->single_button(new moodle_url($PAGE->url, array('action'=>'deletemessage', 'filename'=>$filename)), get_string('yes', 'nanogong'));
            echo '</td><td>';
            echo $OUTPUT->single_button(new moodle_url($PAGE->url), get_string('no', 'nanogong'), 'get');
            echo '</td></tr></table><table align="center" cellspacing="0" cellpadding="0">';
        }

        echo '<tr><td align="center">' . get_string('instructions', 'nanogong') . '</td></tr>';       
        echo '<tr><td align="center">';
        echo nanogong_print_messages_in_listbox($submission->message, $filename, $context->id, $nanogong->id, 'nanogong_student_list');
        echo '</td></tr></table><table align="center" cellspacing="0" cellpadding="0"><tr><td align="center">';
        $nanogongcount = substr_count($submission->message, "NanoGongItem");
        if ($isavailable && !$submission->locked && $action !== 'showsubmitform' && $action !== 'confirmdelete') {
            $PAGE->requires->string_for_js('deletealertmessage', 'nanogong');

            if ($nanogong->maxnumber == 0 || $nanogongcount < $nanogong->maxnumber) {
                echo $OUTPUT->single_button(new moodle_url($PAGE->url, array('action'=>'showsubmitform')), get_string('add', 'nanogong'));
            }
            echo '</td><td align="left">';
            echo '<input id="nanogong_confirmdelete_button" type="button" value="' . get_string('delete', 'nanogong') . '" />';
        }
        echo '</td></tr></table>';
        echo '</div>';
        echo $OUTPUT->box_end();
    }

    ///
    /// Message area
    ///
    if ($submission) {
        echo '<br >';
        echo $OUTPUT->box_start('generalbox', 'message');

        echo '<table align="center" cellspacing="0" cellpadding="0" width="100%"><tr><td><b>' . get_string ('messagearea', 'nanogong') . '</b></td>';
        echo '<td align="right">';
        echo '<a id="messagearea_icon" class="nanogong_showhide_icon" href="javascript:;"><img src="pix/switch_minus.gif" alt="" title="" /></a>';
        echo '</td></tr></table>';
        echo '<div id="messagearea">';

        if ($action === 'showmessageform') {
            $supplementform->display();
        }
        else {
            echo '<table align="center" cellspacing="0" cellpadding="0">';
            if ($submission->supplement) {
                echo '<tr><td>';    
                $text = file_rewrite_pluginfile_urls($submission->supplement, 'pluginfile.php', $context->id, 'mod_nanogong', 'message', $submission->id);
                echo format_text($text, $submission->supplementformat);
                echo '</td></tr>';
            }
            if ($isavailable && !$submission->locked) {
                echo '<tr><td align="center">';
                echo $OUTPUT->single_button(new moodle_url($PAGE->url, array('action'=>'showmessageform')), get_string('leavemessage', 'nanogong'));
                echo '</td></tr></table>';
            }
            else {
                if (!$submission->supplement) {
                    echo '<tr><td>-</td></tr>';
                }    
                echo '</table>';
            }
        }
        echo '</div>';
        echo $OUTPUT->box_end();
    }

    ///
    /// Comment area
    ///
    if ($submission) {
        echo '<br >';
        echo $OUTPUT->box_start('generalbox', 'comment');

        echo '<table align="center" cellspacing="0" cellpadding="0" width="100%"><tr><td><b>' . get_string ('feedbacktitle', 'nanogong') . '</b></td>';
        echo '<td align="right">';
        echo '<a id="feedbackarea_icon" class="nanogong_showhide_icon" href="javascript:;"><img src="pix/switch_minus.gif" alt="" title="" /></a>';
        echo '</td></tr></table>';
        echo '<div id="feedbackarea">';

        echo '<table align="center" border="0" cellspacing="0" cellpadding="0">';       
        if ($submission->grade >= 0 || $submission->comments || $submission->audio) {
            echo '<tr><td align="center"><b>' . get_string('grade', 'nanogong') . '</b></td><td align="center"><b>' . get_string('voicefeedback', 'nanogong') . '</b></td></tr><tr>';
            
            if ($submission->grade >= 0) {   
                echo '<td align="center">' . $submission->grade . '</td>';
            }
            else {
                 echo '<td align="center">-</td>'; 
            }
            echo '<td align="center">';
            if ($submission->audio) {
                echo nanogong_get_applet_code(1, $submission->audio, $context->id, $nanogong->id, $USER->id);
            }
            else {
                echo '-';
            }
            echo '</td>';
            echo '</tr></table>';
            echo '<table align="center" border="0" cellspacing="0" cellpadding="0"><tr><td align="center"><b>' . get_string('commentmessage', 'nanogong') . '</b></td></tr>';
            echo '<tr>';
            if ($submission->comments) {
                echo '<td>';    
                $text = file_rewrite_pluginfile_urls($submission->comments, 'pluginfile.php', $context->id, 'mod_nanogong', 'message', $submission->id);
                echo format_text($text, $submission->commentsformat);
            }
            else {
                echo '<td align="center">-';
            }
            echo '</td></tr>';
            echo '<tr><td align="center">' . get_string('gradedon', 'nanogong') . ' ' . userdate($submission->timestamp) . '</td></tr>';
            echo '</table>';
        }
        else {
            echo '<tr><td><i>' . get_string('nofeeadback', 'nanogong') . '</i></td></tr></table>';
        }
        echo '</div>';
        echo $OUTPUT->box_end();
    }
    
    ///
    /// Other recordings area
    ///
    if ($nanogong->permission) {
        $order = optional_param('order', 0, PARAM_BOOL);

        echo '<br >';
        echo $OUTPUT->box_start('generalbox', 'choice');

        echo '<table align="center" cellspacing="0" cellpadding="0" width="100%"><tr><td><b>' . get_string ('otherrecording', 'nanogong');
        echo ' ' . get_string(($order)? 'reverse' : 'chronological', 'nanogong');
        echo '</b></td>';

        echo '<td align="right">';
        echo '<a id="allrecordingsarea_icon" class="nanogong_showhide_icon" href="javascript:;"><img src="pix/switch_minus.gif" alt="" title="" /></a>';
        echo '</td></tr></table>';
        echo '<div id="allrecordingsarea">';

        nanogong_print_list_by_recordings($context, $nanogong->id, $action);
        echo '</div>';
        echo $OUTPUT->box_end();
    }

    ///
    /// History area
    ///
    if ($isavailable && $submission && !$submission->locked) {
        echo '<br >';
        echo $OUTPUT->box_start('generalbox', 'history');

        echo '<table align="center" cellspacing="0" cellpadding="0" width="100%"><tr><td><b>' . get_string ('voicerecorded', 'nanogong') . '</b></td>';

        echo '<td align="right">';
        echo '<a id="historyarea_icon" class="nanogong_showhide_icon" href="javascript:;"><img src="pix/switch_minus.gif" alt="" title="" /></a>';
        echo '</td></tr></table>';
        echo '<div id="historyarea">';

        echo '<table align="center" cellspacing="0" cellpadding="0">';
        echo nanogong_get_student_audios($context->id, $nanogong->id, $USER->id);
        echo '</table>';
        echo '</div>';
        echo $OUTPUT->box_end();
    }
    echo '<br >';
}

///
/// Capabilities of teachers
///
if (has_capability('mod/nanogong:grade', $context)) {
    ///
    /// Handle the grade form
    ///
    if ($action === 'showgradeform') {
        $catalog = optional_param('catalog', 'submitted', PARAM_TEXT);
        $topage = optional_param('topage', 0, PARAM_INT);
        $perpage = optional_param('perpage', 10, PARAM_INT);
        $student = optional_param('student', 0, PARAM_INT);

        $submission = $DB->get_record('nanogong_messages', array('nanogongid'=>$nanogong->id, 'userid'=>$student));
        if (!$submission) {
            print_error('The student submission does not exist!', 'nanogong', $PAGE->url);
        }

        // Prepare the grade form
        $editoroptions = array(
            'noclean'   => false,
            'maxfiles'  => EDITOR_UNLIMITED_FILES,
            'maxbytes'  => $course->maxbytes,
            'context'   => $context
        );

        $data = new stdClass();
        $data->id             = $cm->id;
        $data->action         = $action;
        $data->catalog        = $catalog;
        $data->topage         = $topage;
        $data->perpage        = $perpage;
        $data->student        = $student;
        $data->maxduration    = $nanogong->maxduration;
        $data->sid            = $submission->id;
        $data->grade          = ($submission->grade < 0)? '' : $submission->grade;
        $data->url            = '';
        $data->comments       = $submission->comments;
        $data->commentsformat = $submission->commentsformat;
        $data->locked         = $submission->locked;
        $data = file_prepare_standard_editor($data, 'comments', $editoroptions, $context, 'mod_nanogong', 'message', $data->sid);

        $url = '';
        if ($submission->audio) {
            $filename = substr($submission->audio, strpos($submission->audio, 'alt="') + strlen('alt="'), 18);
            $relativepath = '/'.implode('/', array($context->id, 'mod_nanogong', 'audio', $nanogong->id, $filename));
            $url = $CFG->wwwroot . '/pluginfile.php?file=' . $relativepath;
        }

        $gradeform = new mod_nanogong_grade_form(null, array($data, $editoroptions, $url, $nanogong->grade));

        if ($gradeform->is_cancelled()) {
            redirect(new moodle_url($PAGE->url, array('catalog'=>$catalog, 'topage'=>$topage, 'perpage'=>$perpage)));
        }

        if ($gradeform->is_submitted()) {
            $data = $gradeform->get_data();

            $data = file_postupdate_standard_editor($data, 'comments', $editoroptions, $context, 'mod_nanogong', 'message', $submission->id);
            if (!empty($data->url)) {
                $submission->audio = '<img title="NanoGongItem" src="pix/speaker.gif" style="vertical-align: middle" alt="' . $data->url . '" />';
            }
            $submission->comments = $data->comments;
            $submission->commentsformat = $data->commentsformat;
        
            $grade = trim($data->grade);
            if ($grade && (int) $grade <= $nanogong->grade && (int) $grade >= 0) {
                $submission->grade = $data->grade;
            }
            else {
                $submission->grade = -1;
            }
            $submission->commentedby    = $USER->id;
            $submission->locked         = empty($data->locked)? 0 : 1;
            $submission->timestamp      = time();

            $DB->update_record('nanogong_messages', $submission);
        
            //TODO fix log actions - needs db upgrade
            add_to_log($course->id, 'nanogong', 'update', 'view.php?n='.$nanogong->id, $nanogong->id, $cm->id);
        
            nanogong_update_grades($nanogong, $submission->userid);
        
            redirect(new moodle_url($PAGE->url, array('catalog'=>$catalog, 'topage'=>$topage, 'perpage'=>$perpage)));
        }

        ///
        /// The grade form
        ///
        echo '<br >';    
        echo $OUTPUT->box_start('generalbox', 'gradeform');
        echo '<table align="center" cellspacing="0" cellpadding="0"><tr><td><b>' . get_string('listof', 'nanogong');

        $student = $DB->get_record('user', array('id'=>$submission->userid));
        echo get_student_name($student);

        echo '</b></td></tr></table><table align="center" cellspacing="0" cellpadding="0"><tr><td align="center">';
        echo '<p>' . get_string('instructions', 'nanogong') . '</p>';
        echo nanogong_print_messages_in_listbox($submission->message, '', $context->id, $nanogong->id, 'nanogong_teacher_list');
        echo '</td>';
        echo '</tr></table>';

        echo '<table cellspacing="0" cellpadding="0"><tr><td><b>' . get_string('feedbackfor', 'nanogong') . ' ';
        echo get_student_name($student);
        echo '</b></td></tr></table>';

        $PAGE->requires->strings_for_js(array('emptymessage', 'notavailable', 'submissionlocked', 'servererror', 'voicetitle'), 'nanogong');
        $gradeform->display();

        echo $OUTPUT->box_end();
    }

    ///
    /// Delete the recordings
    ///
    elseif ($action === 'deleterecordings') {
        $catalog = optional_param('catalog', '', PARAM_TEXT);
        $order = optional_param('order', '', PARAM_INT);
        $topage = optional_param('topage', 0, PARAM_INT);
        $perpage = optional_param('perpage', 10, PARAM_INT);
        $deletenames = optional_param('deletenames', array(), PARAM_TEXT);

        if (count($deletenames) > 0) {
            foreach ($deletenames as $name) {
                $audios = $DB->get_records('nanogong_audios', array('nanogongid'=>$nanogong->id));
                $found = false;
                foreach ($audios as $audio) {
                    if (strcmp($audio->name, $name) == 0) {
                        $found = true;
                        break;
                    }
                }
                if ($found) {
                    $submission = $DB->get_record('nanogong_messages', array('nanogongid'=>$nanogong->id, 'userid'=>$audio->userid));
                    if ($submission) {
                        preg_match('/<img.*?title="NanoGongItem".*?alt="/', $submission->message, $m);
                        $nanogongvoice = $m[0] . $name . '" />';
                        $nanogongform = '<p title="NanoGong Title">' . $audio->title . ' ' . $nanogongvoice . '</p>';
                    
                        $submission->message = str_replace($nanogongform, '', $submission->message);

                        $DB->update_record('nanogong_messages', $submission);

                        add_to_log($course->id, 'nanogong', 'update', 'view.php?n='.$nanogong->id, $nanogong->id, $cm->id);
                    }
                }
            }
        }

        if (empty($catalog))
            redirect(new moodle_url($PAGE->url, array('order'=>$order, 'topage'=>$topage, 'perpage'=>$perpage, 'action'=>'listbyrecordings')));
        else
            redirect(new moodle_url($PAGE->url, array('catalog'=>$order, 'topage'=>$topage, 'perpage'=>$perpage)));
    }

    ///
    /// Show all students in chronological order
    ///
    elseif ($action === 'listbyrecordings') {
        $order = optional_param('order', 0, PARAM_INT);

        echo $OUTPUT->single_button(new moodle_url($PAGE->url), get_string('changetostudents', 'nanogong'), 'get');

        echo '<br >';
        echo $OUTPUT->box_start('generalbox', 'teacherchoice');
        echo '<table align="center" cellspacing="0" cellpadding="0"><tr><td align="center"><b>' . get_string('otherrecording', 'nanogong');
        echo ' ' . get_string(($order)? 'reverse' : 'chronological', 'nanogong');
        echo '</b></td></tr></table>';

        nanogong_print_list_by_recordings($context, $nanogong->id, $action);

        echo $OUTPUT->box_end();
    }

    ///
    /// Show recordings of each student
    ///
    else {
        echo $OUTPUT->single_button(new moodle_url($PAGE->url, array('action'=>'listbyrecordings')), get_string('changetorecordings', 'nanogong'), 'get');

        echo '<br >';
        echo $OUTPUT->box_start('generalbox', 'studentlist');

        nanogong_print_list_by_students($context, $nanogong->id);

        echo $OUTPUT->box_end();
    }
}

// Finish the page
echo $OUTPUT->footer();
