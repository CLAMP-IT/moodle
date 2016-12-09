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
 * Grid Format - A topics based format that uses a grid of user selectable images to popup a light box of the section.
 *
 * @package    course/format
 * @subpackage grid
 * @copyright  &copy; 2013 G J Barnard in respect to modifications of standard topics format.
 * @author     G J Barnard - gjbarnard at gmail dot com and {@link http://moodle.org/user/profile.php?id=442195}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
// Instructions.
// 1.  Ensure this file and the image '515-797no09sa.jpg' are in the Moodle installation folder '/course/format/grid/test'.
// 2.  Ensure the value of courseid is for a valid course in the URL i.e. test_image.php?courseid=2.
// 3.1 In a browser, log into Moodle so that you have a valid MoodleSession cookie.
// 3.2 In another tab of the same browser navigate to 'your moodle installation'/course/format/grid/test/test_image.php.
//     E.g. http://localhost/moodlegjb/course/format/grid/test/test_image.php.
// Success: Image shows.
// Failure: Image does not show.

require_once('../../../../config.php');
global $CFG, $DB;
require_once($CFG->libdir . '/filelib.php');
require_once($CFG->libdir . '/weblib.php');
require_once($CFG->libdir . '/outputcomponents.php');
require_once($CFG->dirroot . '/repository/lib.php');
require_once($CFG->dirroot . '/course/format/lib.php'); // For format_base.
require_once($CFG->dirroot . '/course/lib.php'); // For course functions.
require_once($CFG->dirroot . '/course/format/grid/lib.php'); // For format_grid.

$courseid = required_param('courseid', PARAM_INT);

/* Script settings */
define('GRID_ITEM_IMAGE_WIDTH', 210);
define('GRID_ITEM_IMAGE_HEIGHT', 140);
$context = context_course::instance($courseid);
$contextid = $context->id;

// Find an section id to use:....
$sections = $DB->get_records('course_sections', array('course' => $courseid));
// Use the first.
$sectionid = reset($sections)->id;

// Adapted code from test_convert_image()' of '/lib/filestorage/tests/file_storage_test.php'.
$imagefilename = '515-797no09sa.jpg';  // Image taken by G J Barnard 2002 - Only use for these tests.
$filepath = $CFG->dirroot . '/course/format/grid/test/' . $imagefilename;

$filerecord = array(
    'contextid' => $contextid,
    'component' => 'course',
    'filearea' => 'section',
    'itemid' => $sectionid,
    'filepath' => '/gridtest/',
    'filename' => $imagefilename
);

$fs = get_file_storage();
$convertedfilename = 'converted_' . $imagefilename;

// Clean area from previous test run...
if ($file = $fs->get_file($contextid, 'course', 'section', $sectionid, $filerecord['filepath'], $imagefilename)) {
    $file->delete();
}
if ($file = $fs->get_file($contextid, 'course', 'section', $sectionid, $filerecord['filepath'], $convertedfilename)) {
    $file->delete();
}

$original = $fs->create_file_from_pathname($filerecord, $filepath);

$filerecord['filename'] = $convertedfilename;
$converted = $fs->convert_image($filerecord, $original, GRID_ITEM_IMAGE_WIDTH, GRID_ITEM_IMAGE_HEIGHT, true, 75);

require_once('test_header.php');
$o = html_writer::tag('h1', 'Upload and convert image....');
$o .= html_writer::start_tag('div');
$o .= html_writer::tag('p', 'Original image:');
$src = $CFG->wwwroot . '/course/format/grid/test/' . $imagefilename;
$o .= html_writer::empty_tag('img', array('src' => $src, 'alt' => 'Grid Format Image Test Original'));
$o .= html_writer::end_tag('div');
$o .= html_writer::start_tag('div');
$o .= html_writer::tag('p', 'Converted image:');
$src = moodle_url::make_pluginfile_url($contextid, 'course', 'section', $sectionid, '/gridtest/', $convertedfilename);
$o .= html_writer::empty_tag('img', array('src' => $src, 'alt' => 'Grid Format Image Test Converted'));
$o .= html_writer::end_tag('div');
$o .= html_writer::tag('p', 'Converted object:');
$o .= print_r($converted, true);
$o .= html_writer::start_tag('div');
$o .= html_writer::empty_tag('br');
$o .= html_writer::tag('p', 'Course Id: ' . $courseid);
$o .= html_writer::tag('p', 'Context Id: ' . $contextid);
$o .= html_writer::tag('p', 'Item / Section Id: ' . $sectionid);
$o .= html_writer::tag('p', 'Plugin URL: ' . $src);
$o .= html_writer::empty_tag('br');
$o .= html_writer::end_tag('div');
echo $o;

$o = html_writer::tag('h1', 'Convert image and set as section 2 image, repeat to check delete method....');
// Use the second.
$sectionid = next($sections)->id;
$courseformat = course_get_format($courseid);
// Clean up from previous test....
$courseformat->delete_image($sectionid, $contextid);
// This test....
$storedfilerecord = $courseformat->create_original_image_record($contextid, $sectionid, $imagefilename);
$sectionimage = $courseformat->get_image($courseid, $sectionid);
$courseformat->create_section_image($original, $storedfilerecord, $sectionimage);
$o .= html_writer::start_tag('div');
$o .= html_writer::tag('p', 'Original image resized to maximum width:');
$src = moodle_url::make_pluginfile_url($contextid, 'course', 'section', $sectionid, '/', $imagefilename);
$o .= html_writer::empty_tag('img', array('src' => $src, 'alt' => 'Grid Format Image Test Original Resized'));
$o .= html_writer::end_tag('div');
$o .= html_writer::start_tag('div');
$o .= html_writer::tag('p', 'Converted image to current course settings:');
$sectionimage = $courseformat->get_image($courseid, $sectionid);
$src = moodle_url::make_pluginfile_url($contextid, 'course', 'section', $sectionid, $courseformat->get_image_path(), $sectionimage->displayedimageindex . '_' . $sectionimage->image);
$o .= html_writer::empty_tag('img', array('src' => $src, 'alt' => 'Grid Format Image Test Converted to current course settings'));
$o .= html_writer::end_tag('div');
$currentsettings = $courseformat->get_settings();
$o .= html_writer::start_tag('div');
$o .= html_writer::tag('p', 'Current settings: ' . print_r($currentsettings, true));
$ratios = format_grid::get_image_container_ratios();
$resizemethods = array(
    1 => new lang_string('scale', 'format_grid'), // Scale.
    2 => new lang_string('crop', 'format_grid')   // Crop.
);
$o .= html_writer::tag('p', 'Width: ' . $currentsettings['imagecontainerwidth']);
$o .= html_writer::tag('p', 'Ratio: ' . $ratios[$currentsettings['imagecontainerratio']]);
$o .= html_writer::tag('p', 'Resize method: ' . $resizemethods[$currentsettings['imageresizemethod']]);
$o .= html_writer::end_tag('div');
echo $o;
require_once('test_footer.php');

// Remove original...
$original->delete();
unset($original);
