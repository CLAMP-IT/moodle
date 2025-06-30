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
 * Library functions including primary wrapper class.
 *
 * @package   block_clampmail
 * @copyright 2012 Louisiana State University
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * File serving.
 *
 * @param stdClass $course The course object.
 * @param stdClass $record The block instance record.
 * @param context $context The context object.
 * @param string $filearea The file area.
 * @param array $args List of arguments.
 * @param bool $forcedownload Whether or not to force the download of the file.
 */
function block_clampmail_pluginfile($course, $record, $context, $filearea, $args, $forcedownload) {
    $fs = get_file_storage();
    global $DB;

    list($itemid, $filename) = $args;
    $params = array(
        'component' => 'block_clampmail',
        'filearea' => $filearea,
        'itemid' => $itemid,
        'filename' => $filename
    );

    $instanceid = $DB->get_field('files', 'id', $params);

    if (empty($instanceid)) {
        send_file_not_found();
    } else {
        $file = $fs->get_file_by_id($instanceid);
        send_stored_file($file);
    }
}

/**
 * Extends core navigation to display the CLAMPMail link in the course administration.
 *
 * @param navigation_node $navigation The navigation node to extend
 * @param stdClass        $course The course object
 * @param context         $context The course context
 */
function block_clampmail_extend_navigation_course($navigation, $course, $context) {
    if (has_capability('block/clampmail:cansend', $context)) {
        $url = new moodle_url('/blocks/clampmail/email.php', array('courseid' => $course->id));
        $node = navigation_node::create(get_string('pluginname', 'block_clampmail'), $url,
                navigation_node::TYPE_SETTING, null, null, new pix_icon('i/email', get_string('pluginname', 'block_clampmail')));
        $navigation->add_node($node);
    }
}
