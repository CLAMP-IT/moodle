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
 * The admin settings page for the turningtech module
 * @package    mod_turningtech
 * @copyright  2012 Turning Technologies
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
global $CFG;
require_once($CFG->dirroot . '/mod/turningtech/lib.php');
if ($messages = turningtech_show_messages()) {
    $settings->add(new admin_setting_heading('turningtechmeessages', '', $messages));
}
defined('MOODLE_INTERNAL') || die;
if ($ADMIN->fulltree) {
    require_once("$CFG->libdir/resourcelib.php");
    // Select device ID format.
    $settings->add(new admin_setting_configselect('turningtech_deviceid_format',
                     get_string('deviceidformat', 'turningtech'),
                     get_string('deviceidformatdescription', 'turningtech'),
                     TURNINGTECH_DEVICE_ID_FORMAT_HEX, turningtech_get_device_id_format_options()));
    $settings->add(new admin_setting_configselect('turningtech_encryption_format',
                     get_string('encryptionformat', 'turningtech'),
                     get_string('encryptionformatdescription', 'turningtech'),
                     TURNINGTECH_ENCRYPTION_FORMAT_ECB, turningtech_get_encryption_format_options()));
    // Subject of reminder emails.
    $settings->add(new admin_setting_configtext('turningtech_reminder_email_subject',
                     get_string('reminderemailsubject', 'turningtech'),
                     get_string('reminderemailsubjectdescription', 'turningtech'),
                     get_string('remidneremailsubjectdefault', 'turningtech'), PARAM_TEXT));
    $settings->add(new admin_setting_configtextarea('turningtech_reminder_email_body',
                     get_string('reminderemailbody', 'turningtech'),
                     get_string('reminderemailbodydescription', 'turningtech'),
                     get_string('reminderemailbodydefault', 'turningtech'), PARAM_RAW));
    $settings->add(new admin_setting_configtext('turningtech_responseware_provider',
                     get_string('responsewareprovider', 'turningtech'),
                     get_string('responsewareproviderdescription', 'turningtech'),
                     TURNINGTECH_DEFAULT_RESPONSEWARE_PROVIDER, PARAM_TEXT));
    $settings->add(new admin_setting_configselect('turningtech_device_selection',
                     get_string('displayresponseware', 'turningtech'),
                     get_string('displayresponsewaredesc', 'turningtech'),
                     TURNINGTECH_ENABLE_RESPONSEWARE, turningtech_get_device_display_options()));
    $links = array(
                    'usersearch' => array(
                                    'text' => get_string('usersearch', 'turningtech'),
                                    'href' => "{$CFG->wwwroot}/mod/turningtech/admin.php"
                    ),
                    'purge' => array(
                                    'text' => get_string('purgedeviceids', 'turningtech'),
                                    'href' => "{$CFG->wwwroot}/mod/turningtech/admin_purge.php"
                    )
    );
    $searchlink = "<a href='{$CFG->wwwroot}/mod/turningtech/admin.php'>" . get_string('usersearch', 'turningtech') . "</a>\n";
    $settings->add(new admin_setting_heading('turningtech_search_users', '', turningtech_ul($links)));
}