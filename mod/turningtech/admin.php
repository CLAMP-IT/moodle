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
 * This page lists all the instances of turningtech in a particular course for admin
 * @package    mod_turningtech
 * @copyright  2012 Turning Technologies
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once('../../config.php');
require_once($CFG->dirroot . '/lib/adminlib.php');
require_once($CFG->dirroot . '/mod/turningtech/lib.php');
require_once($CFG->dirroot . '/mod/turningtech/lib/forms/turningtech_admin_search_form.php');
// Verify admin user.
if (0) {
    require_login(0, false);
    $context = get_context_instance(CONTEXT_SYSTEM);
    require_capability('moodle/site:config', $context);
}
admin_externalpage_setup('editusers');

$searchform = new turningtech_admin_search_form();
if ($data = $searchform->get_data()) {
    // TODO: get search results.
    $table = turningtech_admin_search_results($data);
}

// Page output.
echo $OUTPUT->header();
echo turningtech_show_messages();
$searchform->display();
if (isset($data)) {
    if (!empty($table)) {
        echo html_writer::table($table);
    } else {
        echo "<p class='empty-search'>" . get_string('nostudentsfound', 'turningtech') . "</p>\n";
    }
}
echo $OUTPUT->footer();