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
 * Version information for block_accessibility_filescan
 *
 * @package   block_accessibility_filescan
 * @copyright 2023 Swarthmore College
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;

if ($hassiteconfig) {
    $settings = new admin_settingpage('block_accessibility_filescan', get_string('pluginname', 'block_accessibility_filescan'));

  // $ADMIN->add('blockplugins', $settings);

    $settings->add(new admin_setting_configtext(
        'block_accessibility_filescan/text_check_help',
        get_string('settings:text_check_help', 'block_accessibility_filescan'),
        get_string('settings:text_check_help_desc', 'block_accessibility_filescan'),
        'https://www.adobe.com/accessibility/pdf/pdf-accessibility-overview.html',
        PARAM_URL,
        60
    ));

    $settings->add(new admin_setting_configtext(
        'block_accessibility_filescan/title_check_help',
        get_string('settings:title_check_help', 'block_accessibility_filescan'),
        get_string('settings:title_check_help_desc', 'block_accessibility_filescan'),
        'https://www.adobe.com/accessibility/pdf/pdf-accessibility-overview.html',
        PARAM_URL,
        60
    ));

    $settings->add(new admin_setting_configtext(
        'block_accessibility_filescan/lang_check_help',
        get_string('settings:lang_check_help', 'block_accessibility_filescan'),
        get_string('settings:lang_check_help_desc', 'block_accessibility_filescan'),
        'https://www.adobe.com/accessibility/pdf/pdf-accessibility-overview.html',
        PARAM_URL,
        60
    ));

    $settings->add(new admin_setting_configtext(
        'block_accessibility_filescan/outline_check_help',
        get_string('settings:outline_check_help', 'block_accessibility_filescan'),
        get_string('settings:outline_check_help_desc', 'block_accessibility_filescan'),
        'https://www.adobe.com/accessibility/pdf/pdf-accessibility-overview.html',
        PARAM_URL,
        60
    ));
}
