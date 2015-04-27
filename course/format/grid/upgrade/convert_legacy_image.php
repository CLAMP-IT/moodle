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
 * @copyright  &copy; 2013 G J Barnard.
 * @author     G J Barnard - gjbarnard at gmail dot com and {@link http://moodle.org/user/profile.php?id=442195}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
/* Imports */
require_once('../../../../config.php');
require_once($CFG->dirroot . '/repository/lib.php');
require_once($CFG->libdir . '/gdlib.php');

$logverbose = optional_param('logverbose', 0, PARAM_INT);  // Set to 1 to have verbose logging.
$crop = optional_param('crop', 0, PARAM_INT);  // Set to 1 to have cropped images.

/* Script settings */
define('GRID_ITEM_IMAGE_WIDTH', 210);
define('GRID_ITEM_IMAGE_HEIGHT', 140);

function grid_get_courseids() {
    global $DB;

    if (!$courseids = $DB->get_records('format_grid_icon', null, '', 'courseid')) {
        $courseids = false;
    }
    return $courseids;
}

function course_get_courseids() {
    global $DB;

    if (!$courseids = $DB->get_records('course', null, '', 'id')) {
        $courseids = false;
    }
    return $courseids;
}

function grid_get_icons($courseid) {
    global $DB;

    if (!$courseid) {
        return false;
    }

    if (!$sectionicons = $DB->get_records('format_grid_icon', array('courseid' => $courseid), '', 'sectionid, image')) {
        $sectionicons = false;
    }
    return $sectionicons;
}

function grid_files() {
    global $DB;

    if (!$sectionicons = $DB->get_records('files', null, '', 'pathnamehash, contextid, component, filearea, filepath, filename')) {
        $sectionicons = false;
    }
    return $sectionicons;
}

$courseids = course_get_courseids();
if ($logverbose) {
    echo('<p>Course ids: ' . print_r($courseids, true) . '.</p>');
}

if ($courseids) {

    $fs = get_file_storage();

    if ($logverbose) {
        $sectionfiles = grid_files();
        if ($sectionfiles) {
            echo('<p>Files table before: ' . print_r($sectionfiles, true) . '.</p>');
            error_log('Files table before: ' . print_r($sectionfiles, true) . '.');
        }
    }

    foreach ($courseids as $course) {
        $courseid = $course->id;
        if ($courseid == 1) {
            // Site course.
            continue; // Normally I dislike goto's.
        }
        $sectionicons = grid_get_icons($courseid);

        if ($sectionicons) {
            $context = context_course::instance($courseid);
            $contextid = $context->id;

            if ($contextid) {
                if ($logverbose) {
                    echo('<p>Section icons: ' . print_r($sectionicons, true) . '.</p>');
                }

                if ($sectionicons) {
                    if ($logverbose) {
                        echo('<p>Converting legacy images ' . print_r($sectionicons, true) . ".</p>");
                        error_log('Converting legacy images ' . print_r($sectionicons, true) . '.');
                    }
                    foreach ($sectionicons as $sectionicon) {

                        if (isset($sectionicon->image)) {
                            echo('<p>Converting legacy image ' . $sectionicon->image . ".</p>");
                            error_log('Converting legacy image ' . $sectionicon->image . '.');

                            if ($temp_file = $fs->get_file($contextid, 'course', 'legacy', 0, '/icons/', $sectionicon->image)) {

                                echo('<p> Stored file:' . print_r($temp_file, true) . '</p>');
                                error_log(print_r($temp_file, true));
                                // Resize the image and save it...
                                $created = time();
                                $storedfilerecord = array(
                                    'contextid' => $contextid,
                                    'component' => 'course',
                                    'filearea' => 'section',
                                    'itemid' => $sectionicon->sectionid,
                                    'filepath' => '/',
                                    'filename' => $sectionicon->image,
                                    'timecreated' => $created,
                                    'timemodified' => $created);

                                try {
                                    $convert_success = true;
                                    $mime = $temp_file->get_mimetype();

                                    $storedfilerecord['mimetype'] = $mime;

                                    $tmproot = make_temp_directory('gridformaticon');
                                    $tmpfilepath = $tmproot . '/' . $temp_file->get_contenthash();
                                    $temp_file->copy_content_to($tmpfilepath);

                                    $data = generate_image($tmpfilepath, GRID_ITEM_IMAGE_WIDTH, GRID_ITEM_IMAGE_HEIGHT, $crop);
                                    if (!empty($data)) {
                                        $fs->create_file_from_string($storedfilerecord, $data);
                                    } else {
                                        $convert_success = false;
                                    }
                                    unlink($tmpfilepath);

                                    if ($convert_success == false) {
                                        print('<p>Image ' . $sectionicon->image . ' failed to convert.</p>');
                                        error_log('Image ' . $sectionicon->image . ' failed to convert.');
                                    } else {
                                        print('<p>Image ' . $sectionicon->image . ' converted.</p>');
                                        error_log('Image ' . $sectionicon->image . ' converted.');

                                        // Clean up and remove the old thumbnail too.
                                        $temp_file->delete();
                                        unset($temp_file);
                                        if ($temp_file = $fs->get_file($contextid, 'course', 'legacy', 0, '/icons/', 'tn_' . $sectionicon->image)) {
                                            // Remove thumbnail.
                                            $temp_file->delete();
                                            unset($temp_file);
                                        }
                                    }
                                } catch (Exception $e) {
                                    if (isset($temp_file)) {
                                        $temp_file->delete();
                                        unset($temp_file);
                                    }
                                    print('Grid Format Convert Image Exception:...');
                                    debugging($e->getMessage());
                                }
                            } else {
                                echo('<p>Image ' . $sectionicon->image . ' could not be found in the legacy files.</p>');
                                error_log('Image ' . $sectionicon->image . ' could not be found in the legacy files.');
                            }
                        } else {
                            echo('<p>Section icon not set for course id: ' . $courseid . ', section id: ' . $sectionicon->sectionid . '.</p>');
                            error_log('Section icon not set for course id: ' . $courseid . ', section id: ' . $sectionicon->sectionid . '.');
                        }
                    }
                } else {
                    echo('<p>No section icons found for course id: ' . $courseid . '.</p>');
                    error_log('No section icons found for course id: ' . $courseid . '.');
                }
            } else {
                echo('<p>Cannot get context id for course id: ' . $courseid . '.</p>');
                error_log('Cannot get context id for course id: ' . $courseid . '.');
            }
        } else {
            echo('<p>Course id: ' . $courseid . ', is not a Grid format course or cannot get the sections for it.</p>');
            error_log('Course id: ' . $courseid . ', is not a Grid format course or cannot get the sections for it.');
        }
    }
    if ($logverbose) {
        $sectionfiles = grid_files();
        if ($sectionfiles) {
            echo('<p>Files table after: ' . print_r($sectionfiles, true) . '.</p>');
            error_log('Files table after: ' . print_r($sectionfiles, true) . '.');
        }
    }
} else {
    echo('<p>Cannot get list of course ids from format_grid_icon table.</p>');
    error_log('Cannot get list of course ids from format_grid_icon table.');
}
