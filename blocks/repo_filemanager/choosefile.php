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
//

/**
 * This file is used to add files to a resource
 *
 * @since 2.0
 * @package repo_filemanager
 * @subpackage blocks
 * @copyright 2009 Dongsheng Cai <dongsheng@moodle.com>, modified for use in the repo filemanager by Tim Williams (tmw@autotrain.org)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once($CFG->libdir.'/filelib.php');
require_once($CFG->libdir.'/licenselib.php');
require_once($CFG->dirroot.'/repository/lib.php');
/// Wait as long as it takes for this script to finish
set_time_limit(0);

require_sesskey();
require_login();

// disable blocks in this page
$PAGE->set_pagelayout('embedded');

// general parameters
$action      = optional_param('action', '',        PARAM_ALPHA);
$itemid      = optional_param('itemid', '',        PARAM_INT);

// parameters for repository
$contextid   = optional_param('ctx_id',    SYSCONTEXTID, PARAM_INT);    // context ID
$courseid    = optional_param('course',    SITEID, PARAM_INT);    // course ID
$filename    = optional_param('filename', '',      PARAM_FILE);
$fileurl     = optional_param('fileurl', '',       PARAM_RAW);
$repo_id     = required_param('repo_id', PARAM_INT);    // repository ID
$maxbytes    = optional_param('maxbytes',  0,      PARAM_INT);    // maxbytes
$author      = optional_param('author', $USER->firstname.' '.$USER->lastname, PARAM_TEXT);
$licence     = optional_param('licence', $CFG->sitedefaultlicense, PARAM_TEXT);
$client_id   = optional_param('client_id', '', PARAM_ALPHANUM);

// the path to save files
$savepath = optional_param('savepath', '/',    PARAM_PATH);
// path in draft area
$draftpath = optional_param('draftpath', '/',    PARAM_PATH);


// user context
$user_context = get_context_instance(CONTEXT_USER, $USER->id);

$PAGE->set_context($user_context);
if (!$course = $DB->get_record('course', array('id'=>$courseid))) {
    print_error('invalidcourseid');
}
$PAGE->set_course($course);

// init repository plugin
$sql = 'SELECT i.name, i.typeid, r.type FROM {repository} r, {repository_instances} i '.
       'WHERE i.id=? AND i.typeid=r.id';
if ($repository = $DB->get_record_sql($sql, array($repo_id))) {
    $type = $repository->type;
    if (file_exists($CFG->dirroot.'/repository/'.$type.'/lib.php')) {
        require_once($CFG->dirroot.'/repository/'.$type.'/lib.php');
        $classname = 'repository_' . $type;
        try {
            $repo = new $classname($repo_id, $contextid, array('ajax'=>false, 'name'=>$repository->name, 'type'=>$type));
        } catch (repository_exception $e){
            print_error('pluginerror', 'repository');
        }
    } else {
        print_error('invalidplugin', 'repository');
    }
}

$params = array('ctx_id' => $contextid, 'itemid' => $itemid, 'course'=>$courseid, 'maxbytes'=>$maxbytes, 'sesskey'=>sesskey(), 'client_id'=>$client_id,
 'action'=>'browse', 'draftpath'=>$draftpath, 'savepath'=>$savepath, 'repo_id'=>$repo_id);

$PAGE->set_url('/blocks/repo_filemanager/choosefile.php', $params);

switch ($action) {

case 'download':
    $thefile = $repo->get_file($fileurl, $filename, $itemid);
    $filesize = filesize($thefile['path']);
    if (($maxbytes!=-1) && ($filesize>$maxbytes)) {
        print_error('maxbytes');
    }
    if (!empty($thefile)) {
        $record = new stdclass;
        $record->filepath = $savepath;
        $record->filename = $filename;
        $record->component = 'user';
        $record->filearea = 'draft';
        $record->itemid   = $itemid;
        $record->license  = $licence;
        $record->author   = $author;
        $record->source   = $thefile['url'];
        $info = repository::move_to_filepool($thefile['path'], $record);

        echo $OUTPUT->header();
        echo "<h1 style=\"text-align:center;\">".get_string("pluginname", "block_repo_filemanager")."</h1>".
             "<div class=\"generalbox\"><p style=\"text-align:center;\">".get_string("alldone", "block_repo_filemanager")."</p>".
             "<noscript><p style=\"text-align:center;\">(".get_string("refresh", "block_repo_filemanager").")</p></noscript>".
             "<p style=\"text-align:center;\">".
             "<script type=\"text/javascript\">\n".
             "//<!--\n".
             "window.opener.refresh_".$client_id."();\n".
             "document.writeln('<a href=\"javascript:window.close();\">".get_string("closewindow", "block_repo_filemanager")."</a>');\n".
             "//-->\n".
             "</script>".
             "</p></div>";
        echo $OUTPUT->footer();
    } else {
        print_error('cannotdownload', 'repository');
    }

    break;

case 'confirm':

    $licences=license_manager::get_licenses();

    $icon = mimeinfo("icon", $filename);

    echo $OUTPUT->header();
    echo '<h1 style="text-align:center;">'.get_string("pluginname", "block_repo_filemanager").'</h1>';
    echo '<div class="generalbox"><br />';
    echo '<form method="post">';
    echo '<table style="margin-right:auto;margin-left:auto;">';
    echo '  <tr>';
    echo '   <td colspan="2" style="text-align:center;">';
    echo $OUTPUT->pix_icon("f/".$icon."-32", $filename);
    echo '</td>';
    echo '  </tr><tr>';
    echo '    <td><label>'.get_string('filename', 'repository').'</label></td>';
    echo '    <td><input type="text" name="filename" value="'.s($filename).'" /></td>';
    echo '  </tr><tr>';
    echo '    <td><label>'.get_string('author', 'repository').'</label></td>';
    echo '    <td><input type="text" name="author" value="'.s($author).'" /></td>';
    echo '  </tr><tr>';
    echo '    <td><label>'.get_string('license').'</label></td>';
    echo '    <td><select name="licence">';
    foreach ($licences as $l)
    {
        if ($l->enabled)
        {
            echo '<option value="'.$l->shortname.'"';
            if ($l->shortname==$CFG->sitedefaultlicense)
                echo ' selected="selected"';
            echo '>'.$l->fullname.'</option>';
        }
    }
    echo '    </select></td>';
    echo '  </tr><tr>';
    echo '    <td></td>';
    echo '    <td><input type="submit" value="'.s(get_string('addfile', 'block_repo_filemanager')).'" /></td>';
    echo '  </tr>';
    echo '</table>';
    echo '<div>';

    echo ' <input type="hidden" name="sesskey" value="'.$USER->sesskey.'" />';
    echo ' <input type="hidden" name="fileurl" value="'.s($fileurl).'" />';
    echo ' <input type="hidden" name="action" value="download" />';
    echo ' <input type="hidden" name="itemid" value="'.s($itemid).'" />';
    echo ' <input name="draftpath" type="hidden" value="'.s($draftpath).'" />';
    echo ' <input name="savepath" type="hidden" value="'.s($savepath).'" />';
    echo '</div>';
    echo '</form>';
    echo '</div>';
    echo $OUTPUT->footer();
    break;

default:
    echo $OUTPUT->header();
    echo "Should not be here....";
    echo $OUTPUT->footer();
    break;
}
