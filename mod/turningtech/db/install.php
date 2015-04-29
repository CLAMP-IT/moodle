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
// This file replaces:
//   * STATEMENTS section in db/install.xml
//   * lib.php/modulename_install() post installation hook
//   * partially defaults.php.
/**
 * Inserts records for responsecard and responseware
 *
 * @package    mod_turningtech
 * @copyright  2012 Turning Technologies
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
/**
 * Code run after the mod_turningtech module database tables have been created.
 * @return void
 */
function xmldb_turningtech_install() {
    global $DB;

    // Insert turningtech data.
    $device_type       = new stdClass();
    $device_type->type = 'Response Card';
    $device_type->id   = $DB->insert_record('turningtech_device_types', $device_type);

    $device_type       = new stdClass();
    $device_type->type = 'Response Ware';
    $device_type->id   = $DB->insert_record('turningtech_device_types', $device_type);
}
