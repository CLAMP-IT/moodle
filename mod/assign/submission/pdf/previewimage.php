<?php
// This file is part of the submit PDF plugin for Moodle - http://moodle.org/
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

require_once(dirname(__FILE__).'/../../../../config.php');
global $CFG, $DB, $PAGE;
require_once($CFG->libdir.'/filelib.php');

$contextid = required_param('context', PARAM_INT);

$context = context::instance_by_id($contextid);
if ($context->contextlevel != CONTEXT_COURSE) {
    send_file_not_found();
}

$PAGE->set_url('/');
$PAGE->set_context($context);
require_login();
require_capability('moodle/course:manageactivities', $context);

$fs = get_file_storage();
$file = $fs->get_file($context->id, 'assignsubmission_pdf', 'previewimage', 0, '/', 'preview.png');

if ($file) {
    send_stored_file($file);
}

send_file_not_found();