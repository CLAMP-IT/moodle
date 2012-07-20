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
 * Uploading NanoGong files to the server
 *
 * @author     Ning
 * @author     Gibson
 * @package    mod
 * @subpackage nanogong
 * @copyright  2012 The Gong Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @version    4.2.2
 */

require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once(dirname(__FILE__).'/lib.php');

require_login();

$id = required_param('id', PARAM_INT);

if ($id) {
    $cm = get_coursemodule_from_id('nanogong', $id, 0, false, MUST_EXIST);
    $context = get_context_instance(CONTEXT_MODULE, $cm->id);
    $nanogong = $DB->get_record('nanogong', array('id' => $cm->instance), '*', MUST_EXIST);
}
else {
    error('Invalid Parameters!');
}

$cansubmit = has_capability('mod/nanogong:submit', $context);
$cangrade = has_capability('mod/nanogong:grade', $context);

// Check general permissions
if (!$cansubmit && !$cangrade) {
    print '[You are not authorized to access this page.]';
    die;
}

// Check student permission
if ($cansubmit) {
    $time = time();
    $isavailable = true;
    if ($nanogong->timeavailable > $time) $isavailable = false;
    if ($nanogong->timedue && $time > $nanogong->timedue && $nanogong->preventlate != 0) $isavailable = false;
    if (!$isavailable) {
        print '[notavailable]';
        die;
    }

    $submission = $DB->get_record('nanogong_messages', array('nanogongid'=>$nanogong->id, 'userid'=>$USER->id));
    if ($submission && $submission->locked) {
        print '[submissionlocked]';
        die;
    }
}

$elname = "nanogong_upload_file";

// Use data/time as the file name
if (isset($_FILES[$elname]) && isset($_FILES[$elname]['name'])) {
    $oldname = $_FILES[$elname]['name'];
    $ext = preg_replace("/.*(\.[^\.]*)$/", "$1", $oldname);
    $newname = date("Ymd") . date("His") . $ext;
    $_FILES[$elname]['name'] = $newname;
}
else {
    print '[servererror]';
    die;
}

// Store the audio file
$fs = get_file_storage();
$file = array('contextid'=>$context->id, 'component'=>'mod_nanogong', 'filearea'=>'audio',
              'itemid'=>$nanogong->id, 'filepath'=>'/', 'filename'=>$_FILES[$elname]['name'],
              'timecreated'=>time(), 'timemodified'=>time(),
              'mimetype'=>'audio/ogg', 'userid'=>$USER->id, 'author'=>fullname($USER),
              'license'=>$CFG->sitedefaultlicense);
$fs->create_file_from_pathname($file, $_FILES[$elname]['tmp_name']);

$url = $_FILES[$elname]['name'];

print "$url";
?>
