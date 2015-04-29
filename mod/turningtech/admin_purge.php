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
require_once($CFG->dirroot . '/mod/turningtech/lib/forms/turningtech_admin_purge_form.php');
admin_externalpage_setup('managemodules');
$form         = new turningtech_admin_purge_form();
$redirect_url = "{$CFG->wwwroot}/admin/settings.php?section=modsettingturningtech";
if ($form->is_cancelled()) {
    redirect($redirect_url);
} else if ($data = $form->get_data()) {
    $purged = TurningTechDeviceMap::purgeglobal();
    if ($purged === false) {
        turningtech_set_message(get_string('admincouldnotpurge', 'turningtech'));
        redirect($redirect_url);
    } else {
        turningtech_set_message(get_string('adminalldevicespurged', 'turningtech'));
        turningtech_set_message(get_string('numberdevicespurged', 'turningtech', $purged));
        redirect($redirect_url);
    }
}
// Page output.
echo $OUTPUT->header();
echo turningtech_show_messages();
$form->display();
echo $OUTPUT->footer();