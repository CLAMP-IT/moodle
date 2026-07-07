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
 * Language file for local_accessibility_filescan
 *
 * @package   local_accessibility_filescan
 * @copyright 2023 Swarthmore College
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/** @var admin_root $ADMIN */

defined('MOODLE_INTERNAL') || die;

global $CFG;

$settings = new admin_settingpage('local_accessibility_filescan', get_string('pluginname', 'local_accessibility_filescan'));
$ADMIN->add('localplugins', $settings);

$settings->add(new admin_setting_configtext(
    'local_accessibility_filescan/files_per_cron',
    get_string('settings:files_per_cron', 'local_accessibility_filescan'),
    get_string('settings:files_per_cron_desc', 'local_accessibility_filescan'),
    5,
    PARAM_INT
));

$settings->add(new admin_setting_configtext(
    'local_accessibility_filescan/max_file_size_mb',
    get_string('settings:max_file_size_mb', 'local_accessibility_filescan'),
    get_string('settings:max_file_size_mb_desc', 'local_accessibility_filescan'),
    100,
    PARAM_INT
));

$settings->add(new admin_setting_configtext(
    'local_accessibility_filescan/max_retries',
    get_string('settings:max_retries', 'local_accessibility_filescan'),
    get_string('settings:max_retries_desc', 'local_accessibility_filescan'),
    3,
    PARAM_INT
));
