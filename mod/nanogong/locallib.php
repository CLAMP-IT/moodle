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
 * Internal library of functions for module nanogong
 *
 * All the nanogong specific functions, needed to implement the module
 * logic, should go here. Never include this file from your lib.php!
 *
 * @author     Ning
 * @author     Gibson
 * @package    mod
 * @subpackage nanogong
 * @copyright  2012 The Gong Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @version    4.2.2
 */

defined('MOODLE_INTERNAL') || die();

require_once(dirname(__FILE__).'/lib.php');
require_once($CFG->libdir.'/formslib.php');

function get_student_link($student, $courseid) {
    global $CFG;

    $fullname = fullname($student);
    if ($fullname == '') $fullname = '<Unnamed student>';

    return '<a href="'.$CFG->wwwroot.'/user/view.php?id='.$student->id.'&amp;course='.$courseid.'">'.$fullname.'</a>';
}

function nanogong_get_applet_code($type, $content, $contextid, $nanogongid) {
    global $DB, $CFG, $PAGE;
    
    $nanogongcount = substr_count($content,'NanoGongItem');
    preg_match('/<img.*?title="NanoGongItem".*?alt="/', $content, $m);
    
    for ($i = 0; $i < $nanogongcount; $i++) {  
        $startpos = strpos($content, $m[0]);
        $curpos = $startpos + strlen($m[0]);            
        $nanogongname = substr($content, $curpos, 18);
        
        $curpos += 18;
        $totallength = $curpos - $startpos + strlen('" />');
        
        $relativepath = '/'.implode('/', array($contextid, 'mod_nanogong', 'audio', $nanogongid, $nanogongname));
        $url = $CFG->wwwroot . '/pluginfile.php?file=' . $relativepath;

        // Construct the NanoGong icon for sound file location
        $icon  = "<span style='position:relative'>";
        $icon .=   "<img class='mod_nanogong_img' src='pix/speaker.gif' ";
        $icon .=        "title='" . get_string('imgtitle', 'mod_nanogong') . "'";
        $icon .=        "style='vertical-align: middle' path='$url' />";
        $icon .= "</span>";

        $content = substr_replace($content, $icon, $startpos, $totallength);
    }
    
    return $content;
}

function nanogong_get_student_audios($contextid, $nanogongid, $userid) {
    global $CFG, $DB, $OUTPUT, $PAGE;
    
    $nanogong = $DB->get_record('nanogong', array('id'=>$nanogongid));
    $audios = $DB->get_records('nanogong_audios', array('nanogongid'=>$nanogongid, 'userid'=>$userid, 'type'=>1));
    $submission = $DB->get_record('nanogong_messages', array('nanogongid'=>$nanogongid, 'userid'=>$userid));

    echo '<tr><td align="center">' . get_string('timecreated', 'nanogong') . '</td><td>' . get_string('title', 'nanogong') . '</td><td align="center">' . get_string('voicerecording', 'nanogong') . '</td><td>' . get_string('notes', 'nanogong') . '</td></tr>';

    $nanogongcount = substr_count($submission->message, "NanoGongItem");
    foreach ($audios as $audio) {
        $relativepath = '/'.implode('/', array($contextid, 'mod_nanogong', 'audio', $nanogongid, $audio->name));
        $url = $CFG->wwwroot . '/pluginfile.php?file=' . $relativepath;

        // Construct the NanoGong icon for sound file location
        $icon  = "<span style='position:relative'>";
        $icon .=   "<img class='mod_nanogong_img' src='pix/icon.gif' ";
        $icon .=        "title='" . get_string('imgtitle', 'mod_nanogong') . "'";
        $icon .=        "style='vertical-align: middle' path='$url' />";
        $icon .= "</span>";

        echo '<tr>';
        echo '<td>'.userdate($audio->timecreated).'</td>';
        echo '<td>'.$audio->title.'</td>';
        echo '<td align="center">'.$icon.'</td>';
        echo '<td>';
        if (strpos($submission->message, $audio->name)) {
            echo get_string('inuse', 'nanogong');
        }
        else if ($nanogong->maxnumber == 0 || $nanogongcount < $nanogong->maxnumber) {
            echo $OUTPUT->single_button(new moodle_url($PAGE->url, array('title'=>$audio->title, 'name'=>$audio->name, 'action'=>'insertmessage')), get_string('insert', 'nanogong'));
        }
        echo '</td>';
        echo '</tr>';
    }
}

function nanogong_print_recordings_by_time($contextid, $nanogongid, $time, $deletebox, $deletenames) {
    global $DB;
    
    $nanogong = $DB->get_record('nanogong', array('id'=>$nanogongid));
    $audios = $DB->get_records('nanogong_audios', array('timecreated'=>$time));
    foreach ($audios as $audio) {
        $student = $DB->get_record('user', array('id'=>$audio->userid));
        $submission = $DB->get_record('nanogong_messages', array('nanogongid'=>$nanogongid, 'userid'=>$audio->userid));
        
        if ($submission->message && strpos($submission->message, $audio->name) >= 0) {
            echo '<tr>';
            if ($deletebox) {
                $checked = in_array($audio->name, $deletenames)? ' checked="checked"' : '';
                echo '<td align="center">';
                echo '<input type="checkbox" name="deletenames[]" class="nanogong_deletebox" value="' . $audio->name . '"' . $checked . ' />';
                echo '</td>';
            }
            echo '<td>';
            if ($audio->timecreated <= $nanogong->timedue)
                echo userdate($audio->timecreated);
            else
                echo '<font color="red">' . userdate($audio->timecreated) . '</font>';
            echo '</td>';
            echo '<td align="center">' . get_student_link($student, $nanogong->course) . '</td>';
            echo '<td>';
            echo $audio->title . ' ';
            $img = '<img title="NanoGongItem" src="pix/icon.gif" style="vertical-align: middle" alt="' . $audio->name . '" />';
            echo nanogong_get_applet_code(1, $img, $contextid, $nanogongid);
            echo '</td>';
            echo '</tr>';
        }
    }
}

function nanogong_print_student_messages($message, $duedate, $contextid, $nanogongid, $deletebox, $deletenames) {
    global $DB, $CFG;
    
    $nanogongcount = substr_count($message,'NanoGongItem');
    $printeditems = 0;
    
    echo '<table cellspacing="0" cellpadding="0">';
    for ($i = 0; $i < $nanogongcount; $i++) {
        preg_match('/<img.*?title="NanoGongItem".*?alt="/', $message, $m);
        preg_match('/<img.*?title="NanoGongItem".*?>/', $message, $n);
        $startpos = strpos($message, $m[0]);
        $curpos = $startpos + strlen($m[0]);
        $nanogongname = substr($message, $curpos, 18);
        
        preg_match('/<p title="NanoGong Title">.*?<img.*?title="NanoGongItem".*?>/', $message, $o);
        $titlelength = strlen($o[0]) - strlen($n[0]) - strlen('<p title="NanoGong Title">');
        $startpos = strpos($message, $o[0]);
        $curpos = $startpos + strlen('<p title="NanoGong Title">');
        $nanogongtitle = substr($message, $curpos, $titlelength);
        
        $audios = $DB->get_records('nanogong_audios', array('nanogongid'=>$nanogongid));
        foreach ($audios as $audio) {
            if (strcmp($audio->name, $nanogongname) == 0) {
                $nanogongtime = $audio->timecreated;
                break;
            }
        }
        if ($nanogongtime > $duedate) {
            $time = '<font color="red">' . userdate($nanogongtime) . '</font>';
        }
        else {
            $time = userdate($nanogongtime);
        }

        echo '<tr>';
        if ($deletebox) {
            $checked = in_array($audio->name, $deletenames)? ' checked="checked"' : '';
            echo '<td><input type="checkbox" name="deletenames[]" class="nanogong_deletebox" value="' . $audio->name . '"' . $checked . ' /></td>';
        }
        echo '<td>' . $time . '</td>';
        echo '<td>' . nanogong_get_applet_code(2, substr($o[0], strlen('<p title="NanoGong Title">')), $contextid, $nanogongid) . '</td>';
        echo '</tr>';

        $printeditems++;

        $message = substr_replace($message, '', $startpos, strlen($o[0]) + strlen('</p>'));
    }
    if ($printeditems == 0) {
        echo '<tr><td colspan="2">-</td></tr>';
    }
    echo '</table>';
}

function nanogong_print_messages_in_listbox($message, $filename, $contextid, $nanogongid, $id) {
    global $DB, $CFG, $PAGE;
    
    $nanogongcount = substr_count($message,'NanoGongItem');
    $listrecordings = array();
    $maxlength= 0;
    $maxcount = 0;
    
    for ($i = 0; $i < $nanogongcount; $i++) {
        preg_match('/<img.*?title="NanoGongItem".*?alt="/', $message, $m);
        preg_match('/<img.*?title="NanoGongItem".*?>/', $message, $n);
        $startpos = strpos($message, $m[0]);
        $curpos = $startpos + strlen($m[0]);
            
        $nanogongname = substr($message, $curpos, 18);
        $modulename = 'mod_nanogong';
        $filearea = 'audio';
        
        $timecreated = 0;
        $audios = $DB->get_records('nanogong_audios', array('nanogongid'=>$nanogongid));
        foreach ($audios as $audio) {
            if (strcmp($audio->name, $nanogongname) == 0) {
                $timecreated = $audio->timecreated;
                if (str_word_count($audio->title) > $maxcount) {
                    $maxcount = str_word_count($audio->title);
                }
                if (strlen($audio->title) > $maxlength) {
                    $maxlength = strlen($audio->title);
                }
            }
        }
        
        $relativepath = '/'.implode('/', array($contextid, $modulename, $filearea, $nanogongid, $nanogongname));
        $url = $CFG->wwwroot . '/pluginfile.php?file=' . $relativepath;
        
        preg_match('/<p title="NanoGong Title">.*?<img.*?title="NanoGongItem".*?>/', $message, $o);
        $titlelength = strlen($o[0]) - strlen($n[0]) - strlen('<p title="NanoGong Title">');
        $startpos = strpos($message, $o[0]);
        $curpos = $startpos + strlen('<p title="NanoGong Title">');
        $nanogongtitle = substr($message, $curpos, $titlelength);
        
        $option = '<option style="height:20px;" value="' . $url . '" filename="' . $nanogongname . '"';
        if ($filename && strpos($url, $filename)) {
            $option .= ' selected="selected"';
        }
        $option .= ' timecreated="' . userdate($timecreated) . '">' . $nanogongtitle . '</option>';

        $listrecordings[] = $option;
        
        $message = substr_replace($message, '', $startpos, strlen($o[0]) + strlen('</p>'));
    }
    
    if ($maxlength < 20 || $maxcount < 5) {
        $maxwidth = 'width:200px;';
    }
    else {
        $maxwidth = '';
    }
    if ($nanogongcount < 4) {
        $nanogongsize = 4;
    }
    else if ($nanogongcount > 20) {
        $nanogongsize = 20;
    }
    else {
        $nanogongsize = $nanogongcount;
    }

    $PAGE->requires->string_for_js('submitted', 'nanogong');

    echo '<div style="position:relative;">';
    echo '<select id="' . $id . '"class="nanogong_message_list" style="' . $maxwidth . 'text-align:center;overflow-y:auto;" size="' . $nanogongsize . '">';
    for ($i = count($listrecordings) - 1; $i > -1; $i--) {
        echo $listrecordings[$i];
    }
    echo '</select></div>';
}

function nanogong_student_submit_form($cmid, $default, $maxduration, $cancel) {
    global $CFG, $PAGE, $OUTPUT;
    
    $PAGE->requires->strings_for_js(array('emptymessage', 'notavailable', 'submissionlocked', 'servererror', 'voicetitle'), 'nanogong');
 
    $defaulttitle = get_string('recordingtempname', 'nanogong') . $default;
    
    echo '<form id="nanogong_submit_form" action="' . $PAGE->url . '" method="post">';
    echo '<input id="action" name="action" type="hidden" value="" />';
    echo '<input id="cmid" name="cmid" type="hidden" value="' . $cmid . '" />';
    echo '<input id="submiturl" name="url" type="hidden" value="" />';
    echo '<table align="center" cellspacing="0" cellpadding="0"><tr><td align="right">' . get_string('voicetitle', 'nanogong') . '<img class="req" title="Required field" alt="Required field" src="' . $CFG->wwwroot . '/mod/nanogong/pix/req.gif" />' . '<br >' . get_string('titlemax', 'nanogong') . '</td><td><input type="text" id="nanogongtitle" name="title" maxlength="30" size="25" value="' . $defaulttitle . '" /></td></tr><tr><td align="right">' . get_string('voicerecording', 'nanogong') . '<img class="req" title="Required field" alt="Required field" src="' . $CFG->wwwroot . '/mod/nanogong/pix/req.gif" /></td><td><applet id="nanogonginstance" archive="nanogong.jar" code="gong.NanoGong" width="180" height="40"><param name="MaxDuration" value="' . $maxduration .'" /></applet></td></tr><tr>';
    if ($cancel) {
        echo '<td align="right"><input type="button" id="nanogong_saveaudio_button" name="save" value="' . get_string('submit', 'nanogong') . '" /></td><td>';
        echo $OUTPUT->single_button(new moodle_url($PAGE->url), get_string('cancel', 'nanogong'));
        echo '</td>';
    }
    else {
        echo '<td align="center" colspan="2"><input type="button" id="nanogong_saveaudio_button" name="save" value="' . get_string('submit', 'nanogong') . '" /></td>';
    }
    echo '</tr></table></form>';
    echo '<div style="text-align:right">' . get_string('requiredfield', 'nanogong') . '<img class="req" title="Required field" alt="Required field" src="' . $CFG->wwwroot . '/mod/nanogong/pix/req.gif" />' . '</div>';
}

function nanogong_check_content($filename, $nanogongid, $userid, $type) {
    global $DB;

    $isright = 0;
    $audios = $DB->get_records('nanogong_audios', array('nanogongid'=>$nanogongid, 'userid'=>$userid));
    foreach ($audios as $audio) {
        if (strcmp($audio->name, $filename) == 0) {
            $isright = 1;
        }
    }
    $submission = $DB->get_record('nanogong_messages', array('nanogongid'=>$nanogongid, 'userid'=>$userid));
    if (strpos($submission->message, $filename)) {
        $isright = $type;
    }
    if ($isright == 0) {
        error('Invalid Parameters');
    }
}

function nanogong_print_page_settings($perpage, $url, $options, $string) {
    global $PAGE;
     
    echo '<td>' . get_string('with', 'nanogong') . ' </td>';
    echo '<td>';
    echo '<select id="nanogong_page_setting" action="' . $url . '">';
    foreach ($options as $option) {
        $display = ($option == 0)? get_string('all', 'nanogong') : $option;
        $select = ($perpage == $option)? ' selected="selected"' : '';
        echo '<option value="' . $option . '"' . $select . '>' . $display . '</option>';
    }
    echo '</select>';
    echo '</td>';
    echo '<td> ' . get_string($string, 'nanogong') . ' </td>';
}

function nanogong_print_student_by_id($contextid, $nanogongid, $userid, $catalog, $topage, $perpage, $deletebox, $deletenames) {
    global $DB, $OUTPUT, $PAGE;
    
    $nanogong = $DB->get_record('nanogong', array('id' => $nanogongid), '*', MUST_EXIST);
    $submission = $DB->get_record('nanogong_messages', array('nanogongid'=>$nanogongid, 'userid'=>$userid));
    $student = $DB->get_record('user', array('id'=>$userid));
    
    echo '<tr><th width="20%"><b>';
    echo get_student_link($student, $nanogong->course);
    if ($submission && $submission->locked) echo ' <img src="pix/lock.gif" style="vertical-align: middle" alt="" title="" />';
    echo '</b></th>';
    if ($submission) {
        echo '<td colspan="2"><b>' . get_string('tableyourmessage', 'nanogong') . '</b></td></tr>';
        echo '<tr><td></td>';
        echo '<td colspan="2">';
        nanogong_print_student_messages($submission->message, $nanogong->timedue, $contextid, $nanogongid, $deletebox, $deletenames);
        echo '</td>';
        echo '</tr>';
        echo '<tr><td></td><td colspan="2"><b>' . get_string('messagefrom', 'nanogong') . '</b></td></tr><tr><td></td>';
        echo '<td colspan="2" style="border-bottom:1px solid #d3d3d3;">';
        if ($submission->supplement) {
            $text = file_rewrite_pluginfile_urls($submission->supplement, 'pluginfile.php', $contextid, 'mod_nanogong', 'message', $submission->id);
            echo format_text($text, $submission->supplementformat);
        }
        else {
            echo '<p>-</p>';
        }
        echo '</td></tr>';
        echo '<tr><td></td><td width="20%"><b>' . get_string('grade', 'nanogong') . '</b></td><td><b>' . get_string('tablevoice', 'nanogong') . '</b></td></tr><tr><td></td>';
        if ($submission->grade >= 0) {
            echo '<td><p>' . $submission->grade . '</p></td>';
        }
        else {
            echo '<td><p>-</p></td>';
        }
        if ($submission->audio) {
            echo '<td><p>';
            echo nanogong_get_applet_code(2, $submission->audio, $contextid, $nanogongid);
            echo '</p></td>';
        }
        else {
            echo '<td><p>-</p></td>';
        }
        echo '</tr><tr><td></td>';
        echo '<td colspan="2"><b>' . get_string('comment', 'nanogong') . '</b></td></tr><tr><td></td>';
        if ($submission->comments) {
            echo '<td colspan="2">';
            $text = file_rewrite_pluginfile_urls($submission->comments, 'pluginfile.php', $contextid, 'mod_nanogong', 'message', $submission->id);
            echo format_text($text, $submission->commentsformat);
            echo '</td>';
        }
        else {
            echo '<td colspan="2" ><p>-</p></td>';
        }
        echo '</tr><tr><td></td><td style="border-bottom:5px double #d3d3d3;">';
        $url = new moodle_url($PAGE->url, array('catalog'=>$catalog, 'topage'=>$topage, 'perpage'=>$perpage, 'student'=>$submission->userid, 'action'=>'showgradeform'));
        echo '<input type="button" value="' . get_string('edit', 'nanogong') . '" class="nanogong_editgrade_button" action="' . $url . '" />';
        echo '</td>';
        echo '<td align="right" style="border-bottom:5px double #d3d3d3;">';
        if ($submission->audio || $submission->comments || $submission->grade >= 0) {
            echo get_string('tablemodified', 'nanogong') . ' ' . userdate($submission->timestamp);
        }
        echo '</td></tr>';
    }
    else {
        echo '<td colspan="2"><b>' . get_string('nosubmission', 'nanogong') . '</b></td></tr>';
    }
}

function nanogong_print_list_by_students($context, $nanogongid) {
    global $DB, $OUTPUT, $PAGE;
    
    $catalog = optional_param('catalog', 'submitted', PARAM_TEXT);
    $topage = optional_param('topage', 0, PARAM_INT);
    $perpage = optional_param('perpage', 10, PARAM_INT);
    $deletebox = optional_param('deletebox', 0, PARAM_BOOL);
    $confirmdelete = optional_param('confirmdelete', 0, PARAM_BOOL);
    $deletenames = optional_param('deletenames', array(), PARAM_TEXT);

    $students = get_users_by_capability($context, 'mod/nanogong:submit');
    $filtered_students = array();

    foreach ($students as $student) {
        // All students
        if ($catalog == 'all') {
            $filtered_students[] = $student->id;
        }
        else {
            $submission = $DB->get_record('nanogong_messages', array('nanogongid'=>$nanogongid, 'userid'=>$student->id));
            
            // Students who have been graded
            if ($catalog == 'graded') {
                if ($submission && $submission->grade >= 0) {
                    $filtered_students[] = $student->id;
                }
            }

            // Students who have not been graded
            else if ($catalog == 'ungraded') {
                if ($submission && $submission->grade < 0) {
                    $filtered_students[] = $student->id;
                }
            }

            // Students who have not submitted anything
            else if ($catalog == 'unsubmitted') {
                if (!$submission) {
                    $filtered_students[] = $student->id;
                }
            }

            // Students who have submitted something
            else {
                if ($submission) {
                    $filtered_students[] = $student->id;
                }
            }
        }
    }

    echo '<table align="center" cellspacing="0" cellpadding="0">';
    echo '<tr><td align="center" colspan="4"><p><b>' . get_string('studentlist', 'nanogong') . ' ' . get_string('forentering', 'nanogong') . '</b></p></td></tr>';
    echo '<tr><td align="right">' . get_string('show', 'nanogong') . '</td><td colspan="3">';

    $url = new moodle_url($PAGE->url, array('perpage'=>$perpage));
    echo '<select id="nanogong_catalog" action="' . $url . '">';

    $selected = ($catalog == 'all')? ' selected="selected"' : '';
    echo '<option value="all"' . $selected . '>- ' . get_string('allstudents', 'nanogong') . '</option>';

    $selected = ($catalog == 'submitted')? ' selected="selected"' : '';
    echo '<option value="submitted"' . $selected . '>-- ' . get_string('submiitedrecordings', 'nanogong') . '</option>';

    $selected = ($catalog == 'graded')? ' selected="selected"' : '';
    echo '<option value="graded"' . $selected . '>---- ' . get_string('gradedstudents', 'nanogong') . '</option>';

    $selected = ($catalog == 'ungraded')? ' selected="selected"' : '';
    echo '<option value="ungraded"' . $selected . '>---- ' . get_string('ungradedstudents', 'nanogong') . '</option>';

    $selected = ($catalog == 'unsubmitted')? ' selected="selected"' : '';
    echo '<option value="unsubmitted"' . $selected . '>-- ' . get_string('studentsnosubmissions', 'nanogong') . '</option>';

    echo '</select>';
    echo '</td>';
    $url = new moodle_url($PAGE->url, array('catalog'=>$catalog));
    echo nanogong_print_page_settings($perpage, $url, array(1, 2, 5, 10, 20, 50, 0), 'inonepage');
    echo '</tr>';

    echo '<tr><td colspan="4"><i>';
    if ($catalog == 'all') {
        $subcategorystring = get_string('allcategory', 'nanogong');
        $subcategorystringone = get_string('allcategoryone', 'nanogong');
    }
    else if ($catalog == 'graded') {
        $subcategorystring = get_string('gradedcategory', 'nanogong');
        $subcategorystringone = get_string('gradedcategoryone', 'nanogong');
    }
    else if ($catalog == 'ungraded') {
        $subcategorystring = get_string('ungradedcategory', 'nanogong');
        $subcategorystringone = get_string('ungradedcategoryone', 'nanogong');
    }
    else if ($catalog == 'unsubmitted') {
        $subcategorystring = get_string('unsubmittedcategory', 'nanogong');
        $subcategorystringone = get_string('unsubmittedcategoryone', 'nanogong');
    }
    else {
        $subcategorystring = get_string('submittedcategory', 'nanogong');
        $subcategorystringone = get_string('submittedcategoryone', 'nanogong');
    }
    if (count($filtered_students) == 1) {
        echo get_string('thereis', 'nanogong') . count($filtered_students) . ' ' . $subcategorystringone;
    }
    else if (count($filtered_students) > 1) {
        echo get_string('thereare', 'nanogong') . count($filtered_students) . ' ' . $subcategorystring;
    }
    else {
        echo get_string('thereareno', 'nanogong') . $subcategorystring;
    }
    echo '</i></td></tr></table>';
    
    if (count($filtered_students)) {
        $names = array();
        for ($i = 0; $i < count($filtered_students); $i++) {
            $student = $DB->get_record('user', array('id'=>$filtered_students[$i]));
            $names[$filtered_students[$i]] = $student->lastname . ' ' . $student->firstname;
        }
        asort($names);
        $filtered_students = array_keys($names);

        if ($confirmdelete) {
            echo '<table align="center" cellspacing="0" cellpadding="0"><tr>';
            echo '<td><font color="red">' . get_string('checkdeletemessages', 'nanogong') . '</font></td>';
            echo '<td>';
            echo '<input type="button" value="' . get_string('yes', 'nanogong') . '" id="nanogong_deleterecording_button" /></td>';
            $url = new moodle_url($PAGE->url, array('catalog'=>$catalog, 'topage'=>$topage, 'perpage'=>$perpage));
            echo '<td>' . $OUTPUT->single_button($url, get_string('no', 'nanogong'), 'get') . '</td>';
            echo '</tr></table>';
        }

        if ($deletebox) {
            $url = new moodle_url($PAGE->url, array('catalog'=>$catalog, 'topage'=>$topage, 'perpage'=>$perpage, 'deletebox'=>1));
            echo '<form id="nanogong_delete_form" action="' . $url . '" method="post">';
            echo '<input type="hidden" name="action" value="" />';
            echo '<input type="hidden" name="confirmdelete" value="1" />';
        }

        echo '<table align="center" width="100%" cellspacing="0" cellpadding="0">';

        if ($perpage == 0) $perpage = count($filtered_students);

        for ($i = $topage * $perpage; $i < count($filtered_students) && $i < ($topage + 1) * $perpage; $i++) {
            nanogong_print_student_by_id($context->id, $nanogongid, $filtered_students[$i], $catalog, $topage, $perpage, $deletebox, $deletenames);
        }

        echo '</table>';

        if ($deletebox) {
            echo '</form>';
        }

        echo '<table align="center" cellspacing="0" cellpadding="0"><tr>';
        if ($topage) {
            $url = new moodle_url($PAGE->url, array('catalog'=>$catalog, 'topage'=>$topage - 1, 'perpage'=>$perpage));
            echo '<td>' . $OUTPUT->single_button($url, get_string('previouspage', 'nanogong'), 'get') . '</td>';
        }
        echo '<td>' . get_string('page', 'nanogong') . ($topage + 1) . '/' . ceil(count($filtered_students) / $perpage) . '</td>';
        if (($topage + 1) * $perpage < count($filtered_students)) {
            $url = new moodle_url($PAGE->url, array('catalog'=>$catalog, 'topage'=>$topage + 1, 'perpage'=>$perpage));
            echo '<td>' . $OUTPUT->single_button($url, get_string('nextpage', 'nanogong'), 'get') . '</td>';
        }
        echo '</tr></table>';

        if (!$confirmdelete) {
            echo '<table style="width: 100%" cellspacing="0" cellpadding="0">';
            echo '<tr>';
            if ($deletebox) {
                $PAGE->requires->string_for_js('deletealertmessage', 'nanogong');

                echo '<td align="right">';
                echo '<input type="button" id="nanogong_confirmdeleterec_button" value="' . get_string('deleterecordings', 'nanogong') . '" />';
                echo '</td>';
            }
            else {
                $url = new moodle_url($PAGE->url, array('catalog'=>$catalog, 'topage'=>$topage, 'perpage'=>$perpage, 'deletebox'=>1));
                echo '<td align="right">' . $OUTPUT->single_button($url, get_string('showdeleteboxes', 'nanogong'), 'get') . '</td>';
            }
            echo '</tr>';
            echo '</table>';
        }
    }
}

function nanogong_print_list_by_recordings($context, $nanogongid, $action) {
    global $DB, $OUTPUT, $PAGE;

    $order = optional_param('order', 0, PARAM_INT);
    $topage = optional_param('topage', 0, PARAM_INT);
    $perpage = optional_param('perpage', 10, PARAM_INT);
    $deletebox = optional_param('deletebox', 0, PARAM_BOOL);
    $confirmdelete = optional_param('confirmdelete', 0, PARAM_BOOL);
    $deletenames = optional_param('deletenames', array(), PARAM_TEXT);

    if (!has_capability('mod/nanogong:grade', $context)) {
        $deletebox = 0;
        $confirmdelete = 0;
        $deletenames = array();
    }

    $recordings = array();
    $students = nanogong_get_participants($nanogongid);
    foreach ($students as $student) {
        $submission = $DB->get_record('nanogong_messages', array('nanogongid'=>$nanogongid, 'userid'=>$student->id));
        $audios = $DB->get_records('nanogong_audios', array('nanogongid'=>$nanogongid, 'userid'=>$student->id, 'type'=>1));
        foreach ($audios as $audio) {
            if (strpos($submission->message, $audio->name)) {
                $recordings[] = $audio->timecreated;
            }
        }
    }
    sort($recordings);

    if (count($recordings)) {
        echo '<table align="center" cellspacing="0" cellpadding="0"><tr>';
        $url = new moodle_url($PAGE->url, array('order'=>$order, 'action'=>$action));
        echo nanogong_print_page_settings($perpage, $url, array(5, 10, 20, 50, 0), 'recordinginonepage');
        echo '</tr></table>';

        echo '<table align="center" cellspacing="0" cellpadding="0"><tr><td>';
        if ($order == 0)
            $url = new moodle_url($PAGE->url, array('order'=>1, 'perpage'=>$perpage, 'action'=>$action));
        else
            $url = new moodle_url($PAGE->url, array('perpage'=>$perpage, 'action'=>$action));
        echo $OUTPUT->single_button($url, get_string('reversebutton', 'nanogong'), 'get');
        echo '</td></tr></table>';

        if ($confirmdelete) {
            echo '<table align="center" cellspacing="0" cellpadding="0"><tr>';
            echo '<td><font color="red">' . get_string('checkdeletemessages', 'nanogong') . '</font></td>';
            echo '<td>';
            echo '<input type="button" value="' . get_string('yes', 'nanogong') . '" id="nanogong_deleterecording_button" /></td>';
            $url = new moodle_url($PAGE->url, array('order'=>$order, 'topage'=>$topage, 'perpage'=>$perpage, 'action'=>$action));
            echo '<td>' . $OUTPUT->single_button($url, get_string('no', 'nanogong'), 'get') . '</td>';
            echo '</tr></table>';
        }

        if ($deletebox) {
            $url = new moodle_url($PAGE->url, array('order'=>$order, 'topage'=>$topage, 'perpage'=>$perpage, 'deletebox'=>1));
            echo '<form id="nanogong_delete_form" action="' . $url . '" method="post">';
            echo '<input type="hidden" name="action" value="' . $action . '" />';
            echo '<input type="hidden" name="confirmdelete" value="1" />';
        }

        echo '<table align="center" cellspacing="0" cellpadding="0">';
        echo '<tr>';
        if ($deletebox) {
            echo '<td align="center"><b>' . get_string('isdelete', 'nanogong') . '</b> ';
            echo '<input type="checkbox" class="nanogong_deleteall" /></td>';
        }
        echo '<td align="center"><b>' . get_string('submittedtime', 'nanogong') . '</b></td>';
        echo '<td align="center"><b>' . get_string('tablename', 'nanogong') . '</b></td>';
        echo '<td><b>' . get_string('recording', 'nanogong') . '</b></td>';
        echo '</tr>';

        if ($perpage == 0) $perpage = count($recordings);
     
        for ($i = $topage * $perpage; $i < count($recordings) && $i < ($topage + 1) * $perpage; $i++) {
            if ($order)
                $j = count($recordings) - $i - 1;
            else 
                $j = $i;
            nanogong_print_recordings_by_time($context->id, $nanogongid, $recordings[$j], $deletebox, $deletenames);
        }
        echo '</table>';

        if ($deletebox) {
            echo '</form>';
        }

        echo '<table align="center" cellspacing="0" cellpadding="0"><tr>';
        if ($topage) {
            $url = new moodle_url($PAGE->url, array('order'=>$order, 'topage'=>$topage - 1, 'perpage'=>$perpage, 'action'=>$action));
            echo '<td>' . $OUTPUT->single_button($url, get_string('previouspage', 'nanogong'), 'get') . '</td>';
        }
        echo '<td>' . get_string('page', 'nanogong') . ($topage + 1) . '/' . ceil(count($recordings) / $perpage) . '</td>';
        if (($topage + 1) * $perpage < count($recordings)) {
            $url = new moodle_url($PAGE->url, array('order'=>$order, 'topage'=>$topage + 1, 'perpage'=>$perpage, 'action'=>$action));
            echo '<td>' . $OUTPUT->single_button($url, get_string('nextpage', 'nanogong'), 'get') . '</td>';
        }
        echo '</tr></table>';

        if (has_capability('mod/nanogong:grade', $context) && !$confirmdelete) {
            echo '<table style="width: 100%" cellspacing="0" cellpadding="0">';
            echo '<tr>';
            if ($deletebox) {
                $PAGE->requires->string_for_js('deletealertmessage', 'nanogong');

                echo '<td align="right">';
                echo '<input type="button" id="nanogong_confirmdeleterec_button" value="' . get_string('deleterecordings', 'nanogong') . '" />';
                echo '</td>';
            }
            else {
                $url = new moodle_url($PAGE->url, array('order'=>$order, 'topage'=>$topage, 'perpage'=>$perpage, 'deletebox'=>1, 'action'=>$action));
                echo '<td align="right">' . $OUTPUT->single_button($url, get_string('showdeleteboxes', 'nanogong'), 'get') . '</td>';
            }
            echo '</tr>';
            echo '</table>';
        }
    }
}
