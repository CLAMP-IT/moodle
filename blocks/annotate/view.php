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
 * Annotate block view.php - redirects to Annotate
 *
 * @package   block_annotate
 * @copyright Textensor Ltd.
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname( __FILE__ ) . '/../../config.php');
require_once($CFG->dirroot . '/mod/resource/locallib.php');
require_once($CFG->dirroot . '/course/lib.php');
require_once($CFG->dirroot . '/course/format/lib.php');
require_once('lib.php');

$id    = optional_param('id', 0, PARAM_INT); // Resource ID.
$owner = optional_param('owner', "", PARAM_TEXT); // If set, the doc should be owned by this person and just shared by the actual user.

if (!$cm = get_coursemodule_from_id('resource', $id)) {
    print_error ('invalidcoursemodule');
}

$resource = $DB->get_record('resource', array('id' => $cm->instance), '*', MUST_EXIST);
$course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);

require_course_login($course, true, $cm);
$context = context_module::instance($cm->id);
require_capability('block/annotate:accessannotate', $context);

if ($resource->tobemigrated) {
    resource_print_tobemigrated($resource, $cm, $course);
    die();
}

$fs = get_file_storage();
$files = $fs->get_area_files($context->id, 'mod_resource', 'content', 0, 'sortorder');
if (count($files) < 1) {
    resource_print_filenotfound($resource, $cm, $course);
    die ();
} else {
    $file = array_pop($files);
}

$pathnamehash = $file->get_pathnamehash (); // Used for caching in annotate.
if (! $file = $fs->get_file_by_hash ( $pathnamehash )) {
    resource_print_filenotfound ( $resource, $cm, $course );
    die ();
}
$docpath = '/' . $context->id . '/mod_resource/content/' . $resource->revision . $file->get_filepath () . $file->get_filename ();

$coursename = $course->fullname;
$courseid = $course->id;
block_annotate_redirect_to_annotate($docpath, $pathnamehash, $owner, $coursename, $courseid);
