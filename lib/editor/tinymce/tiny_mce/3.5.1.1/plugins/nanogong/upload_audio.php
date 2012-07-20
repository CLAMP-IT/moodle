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
 * Upload NanoGong files to the server
 *
 * @author     Ning
 * @author     Gibson
 * @copyright  2012 The Gong Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @version    4.2.2
 */

require_once(dirname(dirname(dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))))) . '/config.php');

require_login();

$itemid = required_param('itemid', PARAM_INT);
$context = get_context_instance(CONTEXT_USER, $USER->id);
$elname = "nanogong_upload_file";

// use date/time as the file name
if (isset($_FILES[$elname]['name'])) {
    $oldname = $_FILES[$elname]['name'];
    $ext = preg_replace("/.*(\.[^\.]*)$/", "$1", $oldname);
    $newname = date("Ymd") . date("His") . $ext;
    $_FILES[$elname]['name'] = $newname;
}

$fs = get_file_storage();
$file = array('contextid'=>$context->id, 'component'=>'user', 'filearea'=>'draft',
              'itemid'=>$itemid, 'filepath'=>'/', 'filename'=>$_FILES[$elname]['name'],
              'timecreated'=>time(), 'timemodified'=>time(),
              'mimetype'=>'audio/ogg', 'userid'=>$USER->id, 'author'=>fullname($USER),
              'license'=>$CFG->sitedefaultlicense);
$fs->create_file_from_pathname($file, $_FILES[$elname]['tmp_name']);

print moodle_url::make_draftfile_url($itemid, $file['filepath'], $_FILES[$elname]['name'])->out();
?>
