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
 * Image editing page
 *
 * @package   mod_lightboxgallery
 * @copyright 2011 NetSpot Pty Ltd
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/locallib.php');
require_once(dirname(__FILE__).'/edit/base.class.php');
require_once(dirname(__FILE__).'/imageclass.php');

global $DB;

$id = required_param('id', PARAM_INT);
$image = required_param('image', PARAM_PATH);
$tab = optional_param('tab', '', PARAM_TEXT);
$page = optional_param('page', 0, PARAM_INT);

$cm      = get_coursemodule_from_id('lightboxgallery', $id, 0, false, MUST_EXIST);
$course  = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
$gallery = $DB->get_record('lightboxgallery', array('id' => $cm->instance), '*', MUST_EXIST);

require_login($course->id);

$context = get_context_instance(CONTEXT_MODULE, $cm->id);
require_capability('mod/lightboxgallery:edit', $context);

$PAGE->set_cm($cm);
$PAGE->set_pagelayout('incourse');
$PAGE->set_url('/mod/lightboxgallery/imageedit.php', array('id' => $cm->id, 'image' => $image, 'tab' => $tab, 'page' => $page));
$PAGE->set_title($gallery->name);
$PAGE->set_heading($course->shortname);
$PAGE->set_button($OUTPUT->single_button($CFG->wwwroot.'/mod/lightboxgallery/view.php?id='.$id.'&editing=1&page='.$page, get_string('backtogallery','lightboxgallery')));

$edittypes = lightboxgallery_edit_types();

$tabs = array();
foreach ($edittypes as $type => $name) {
    $tabs[] = new tabObject($type, $CFG->wwwroot.'/mod/lightboxgallery/imageedit.php?id='.$cm->id.'&amp;image='.$image.'&amp;page='.$page.'&amp;tab='.$type, $name);
}

if (!in_array($tab, array_keys($edittypes))) {
    $types = array_keys($edittypes);
    if (isset($types[0])) {
        $tab = $types[0];
    } else {
        notice(get_string('allpluginsdisabled', 'lightboxgallery'), "view.php?id=$id&page=$page");
    }
}

require($CFG->dirroot.'/mod/lightboxgallery/edit/'.$tab.'/'.$tab.'.class.php');
$editclass = 'edit_'.$tab;
$editinstance = new $editclass($gallery, $cm, $image, $tab);

$fs = get_file_storage();
if (!$stored_file = $fs->get_file($context->id, 'mod_lightboxgallery', 'gallery_images', '0', '/', $image)) {
    print_error(get_string('errornofile', 'lightboxgallery', $image));
}

if ($editinstance->processing() && confirm_sesskey()) {
    add_to_log($course->id, 'lightboxgallery', 'editimage', 'view.php?id='.$cm->id, $tab.' '.$image, $cm->id, $USER->id);
    $editinstance->process_form();
    redirect($CFG->wwwroot.'/mod/lightboxgallery/imageedit.php?id='.$cm->id.'&image='.$editinstance->image.'&tab='.$tab);
}

$image = new lightboxgallery_image($stored_file, $gallery, $cm);

$table = new html_table();
$table->width = '*';

if ($editinstance->showthumb) {
    $table->attributes = array('style' => 'margin-left:auto;margin-right:auto;');
    $table->align = array('center', 'center');
    $table->size = array('*', '*');
    $table->data[] = array('<img src="'.$image->get_thumbnail_url().'" alt="" /><br /><span title="'.$image->get_image_caption().'">'.$image->get_image_caption().'</span>', $editinstance->output($image->get_image_caption()));
} else {
    $table->align = array('center');
    $table->size = array('*');
    $table->data[] = array($editinstance->output($image->get_image_caption()));
}

echo $OUTPUT->header();

print_tabs(array($tabs), $tab);

echo html_writer::table($table);

/* to be re-implemented at a later stage
$dataroot = $CFG->dataroot.'/'.$course->id.'/'.$gallery->folder;
if ($dirimages = lightboxgallery_directory_images($dataroot)) {
    sort($dirimages);
    $options = array();
    foreach ($dirimages as $dirimage) {
        $options[$dirimage] = $dirimage;
    }
    $index = array_search($image, $dirimages);

    echo('<table class="boxaligncenter menubar">
            <tr>');
    if ($index > 0) {
        echo('<td>');
        print_single_button($CFG->wwwroot.'/mod/lightboxgallery/imageedit.php', array('id' => $gallery->id, 'tab' => $tab, 'page' => $page, 'image' => $dirimages[$index - 1]), '←');
        echo('</td>');
    }
    echo('<td>
            <form method="get" action="'.$CFG->wwwroot.'/mod/lightboxgallery/imageedit.php">
              <fieldset class="invisiblefieldset">
              <input type="hidden" name="id" value="'.$gallery->id.'" />
              <input type="hidden" name="tab" value="'.$tab.'" />
              <input type="hidden" name="page" value="'.$page.'" />');
    choose_from_menu($options, 'image', $image, null, 'submit()');
    echo('  </fieldset>
            </form>
          </td>');
    if ($index < count($dirimages) - 1) {
        echo('<td>');
        print_single_button($CFG->wwwroot.'/mod/lightboxgallery/imageedit.php', array('id' => $gallery->id, 'tab' => $tab, 'page' => $page, 'image' => $dirimages[$index + 1]), '→');
        echo('</td>');
    }
    echo('  </tr>
          </table>');
}
*/

echo $OUTPUT->footer();

?>
