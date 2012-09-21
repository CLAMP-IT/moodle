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
 * Internal library of functions for module lightboxgallery
 *
 * All the newmodule specific functions, needed to implement the module
 * logic, should go here. Never include this file from your lib.php!
 *
 * @package   mod_lightboxgallery
 * @copyright 2011 NetSpot Pty Ltd
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/filelib.php");

define('THUMB_WIDTH', 150);
define('THUMB_HEIGHT', 150);
define('MAX_IMAGE_LABEL', 13);
define('MAX_COMMENT_PREVIEW', 20);

define('AUTO_RESIZE_SCREEN', 1);
define('AUTO_RESIZE_UPLOAD', 2);
define('AUTO_RESIZE_BOTH', 3);

// --- Damon, 9/13/12, yes I know I'm insane for changing this function's declaration ---
// --- from grep, this function is only called from one place, imageadd.php lines around 70 ---
// original declaration:
//function lightboxgallery_add_images($stored_file, $context, $cm, $gallery) {
function lightboxgallery_add_images($stored_file, $context, $cm, $gallery, $width, $height) {

    require_once(dirname(__FILE__).'/imageclass.php');

    $fs = get_file_storage();

    $images = array();
    if ($stored_file->get_mimetype() == 'application/zip') {
        //unpack
        $packer = get_file_packer('application/zip');
        $fs->delete_area_files($context->id, 'mod_lightboxgallery', 'unpacktemp', 0);
        $stored_file->extract_to_storage($packer, $context->id, 'mod_lightboxgallery', 'unpacktemp', 0, '/');
        $images = $fs->get_area_files($context->id, 'mod_lightboxgallery', 'unpacktemp', 0);
        $stored_file->delete();
    } else {
        $images[] = $stored_file;
    }

    foreach ($images as $stored_file) {
        if ($stored_file->is_valid_image()) {
            $filename = $stored_file->get_filename();
            $fileinfo = array(
                'contextid'     => $context->id,
                'component'     => 'mod_lightboxgallery',
                'filearea'      => 'gallery_images',
                'itemid'        => 0,
                'filepath'      => '/',
                'filename'      => $filename
            );
            if (!$fs->get_file($context->id, 'mod_lightboxgallery', 'gallery_images', 0, '/', $filename)) {
                $stored_file = $fs->create_file_from_storedfile($fileinfo, $stored_file);
                $image = new lightboxgallery_image($stored_file, $gallery, $cm);
                $image->set_caption($filename);
                
                // ---- the following is Damon, 9-13-12 ----
                // autoresize of 1 is just screen, 2 and 3 are upload ones
                if (($width != 0 and $height != 0) or ($gallery->autoresize == 2 or $gallery->autoresize == 3)) {
	                if ($gallery->autoresize) {
		            	// get the default autoresize amount
		            	$resizeamount = $gallery->resize;
		            	$resizeoptions = lightboxgallery_resize_options();
		            	list($width, $height) = explode('x', $resizeoptions[$resizeamount]);

		                $image = $image->resize_image($width, $height);
		            
	                } else {
		                $image = $image->resize_image($width, $height);
		            }
				
		                $image->resize_image($width, $height);
		            
	                } else {
		                $image->resize_image($width, $height);
		        }
	// fixed resize bug with lightbox gallery
                }
                // ---- end of Damon's shenanigans -----
                
                
            }
        }
    }
    $fs->delete_area_files($context->id, 'mod_lightboxgallery', 'unpacktemp', 0);
}

function lightboxgallery_config_defaults() {
    $defaults = array(
        'disabledplugins' => '',
        'enablerssfeeds' => 0,
    );

    $localcfg = get_config('lightboxgallery');

    foreach ($defaults as $name => $value) {
        if (! isset($localcfg->$name)) {
            set_config($name, $value, 'lightboxgallery');
        }
    }
}

function lightboxgallery_edit_types($showall = false) {
    global $CFG;

    $result = array();

    $disabledplugins = explode(',', get_config('lightboxgallery', 'disabledplugins'));

    $edittypes = get_list_of_plugins('mod/lightboxgallery/edit');

    foreach ($edittypes as $edittype) {
        if ($showall || !in_array($edittype, $disabledplugins)) {
            $result[$edittype] = get_string('edit_' . $edittype, 'lightboxgallery');
        }
    }

    return $result;
}

function lightboxgallery_print_comment($comment, $context) {
    global $DB, $CFG, $COURSE, $OUTPUT;

    $user = $DB->get_record('user', array('id' => $comment->userid));

    echo '<table cellspacing="0" width="50%" class="boxaligncenter datacomment forumpost">'.
         '<tr class="header"><td class="picture left">'.$OUTPUT->user_picture($user, array('courseid' => $COURSE->id)).'</td>'.
         '<td class="topic starter" align="left"><a name="c'.$comment->id.'"></a><div class="author">'.
         '<a href="'.$CFG->wwwroot.'/user/view.php?id='.$user->id.'&amp;course='.$COURSE->id.'">'.fullname($user, has_capability('moodle/site:viewfullnames', $context)).'</a> - '.userdate($comment->timemodified).
         '</div></td></tr>'.
         '<tr><td class="left side">'.
//         ($groups = user_group($COURSE->id, $user->id) ? print_group_picture($groups, $COURSE->id, false, false, true) : '&nbsp;').
         '</td><td class="content" align="left">'.
         format_text($comment->comment, FORMAT_MOODLE).
         '<div class="commands">'.
         (has_capability('mod/lightboxgallery:edit', $context) ? '<a href="'.$CFG->wwwroot.'/mod/lightboxgallery/comment.php?id='.$comment->gallery.'&amp;delete='.$comment->id.'">'.get_string('delete').'</a>' : '').
         '</div>'.
         '</td></tr></table>';
}

function lightboxgallery_print_tags($heading, $tags, $courseid, $galleryid) {
    global $CFG, $OUTPUT;

    echo $OUTPUT->box_start();

    echo '<form action="search.php" style="float: right; margin-left: 4px;">'.
         ' <fieldset class="invisiblefieldset">'.
         '  <input type="hidden" name="id" value="'.$courseid.'" />'.
         '  <input type="hidden" name="l" value="'.$galleryid.'" />'.
         '  <input type="text" name="search" size="8" />'.
         '  <input type="submit" value="'.get_string('search').'" />'.
         ' </fieldset>'.
         '</form>'.
         $heading.': ';

    $tagarray = array();
    foreach ($tags as $tag) {
        $tagarray[] = '<a class="taglink" href="'.$CFG->wwwroot.'/mod/lightboxgallery/search.php?id='.$courseid.'&amp;gallery='.$galleryid.'&amp;search='.urlencode(stripslashes($tag->description)).'">'.s($tag->description).'</a>';
    }

    echo implode(', ', $tagarray);

    echo $OUTPUT->box_end();
}

function lightboxgallery_resize_label($label) {
    return lightboxgallery_resize_text($label, MAX_IMAGE_LABEL);
}

function lightboxgallery_resize_options() {
    return array(1 => '1280x1024', 2 => '1024x768', 3 => '800x600', 4 => '640x480');
}

function lightboxgallery_resize_text($text, $length) {
    $textlib = new textlib();
    return ($textlib->strlen($text) > $length ? $textlib->substr($text, 0, $length) . '...' : $text);
}

function lightboxgallery_rss_enabled() {
    global $CFG;

    return ($CFG->enablerssfeeds && get_config('lightboxgallery', 'enablerssfeeds'));
}

function lightboxgallery_index_thumbnail($courseid, $gallery, $newimage = null) {
    global $CFG;

    require_once(dirname(__FILE__).'/imageclass.php');
    $cm = get_coursemodule_from_instance("lightboxgallery", $gallery->id, $courseid);
    $context = get_context_instance(CONTEXT_MODULE, $cm->id);

    $imageid = 'Gallery Index Image';

    $fs = get_file_storage();
    $stored_file = $fs->get_file($context->id, 'mod_lightboxgallery', 'gallery_index', '0', '/', 'index.png');

    if (!is_null($newimage) && is_object($stored_file)) { //delete any existing index
        $stored_file->delete();
    }
    if (is_object($stored_file) && is_null($newimage)) {
        //grab the index
        $index = $stored_file;
    } else {
        //get first image and create an index for that
        if (is_null($newimage)) {
            $files = $fs->get_area_files($context->id,'mod_lightboxgallery','gallery_images');
            $file = array_shift($files);
            while (substr($file->get_mimetype(),0,6) != 'image/') {
                $file = array_shift($files);
            }
            $image = new lightboxgallery_image($file, $gallery, $cm);
        } else {
            $image = $newimage;
        }
        $index = $image->create_index();
    }
    $path = $CFG->wwwroot.'/pluginfile.php/'.$context->id.'/mod_lightboxgallery/gallery_index/'.
                $index->get_itemid().$index->get_filepath().$index->get_filename();

    return '<img src="' . $path . '" alt="" ' . (! empty($imageid) ? 'id="' . $imageid . '"' : '' )  . ' />';
}


/**
 * File browsing support class
 */
class lightboxgallery_content_file_info extends file_info_stored {
    public function get_parent() {
        if ($this->lf->get_filepath() === '/' and $this->lf->get_filename() === '.') {
            return $this->browser->get_file_info($this->context);
        }
        return parent::get_parent();
    }
    public function get_visible_name() {
        if ($this->lf->get_filepath() === '/' and $this->lf->get_filename() === '.') {
            return $this->topvisiblename;
        }
        return parent::get_visible_name();
    }
}
