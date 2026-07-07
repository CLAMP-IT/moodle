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
 * Library functions for the roster report.
 *
 * @package   report_roster
 * @copyright 2019 Lafayette College ITS
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {

    $settings->add(
        new admin_setting_heading('general', get_string('settings:headings:general', 'report_roster'), '')
    );

    $settings->add(
        new admin_setting_configtextarea('report_roster/fields',
            get_string('settings:fields', 'report_roster'),
            get_string('settings:fields:description', 'report_roster'),
            get_string('settings:fields:default', 'report_roster')
        )
    );

    $settings->add(
        new admin_setting_configtext('report_roster/displayname',
            get_string('settings:displayname', 'report_roster'),
            get_string('settings:displayname:description', 'report_roster'),
            get_string('settings:displayname:default', 'report_roster')
        )
    );

    $options = array(
        'small'  => get_string('size:small', 'report_roster'),
        'medium' => get_string('size:medium', 'report_roster'),
        'large'  => get_string('size:large', 'report_roster'),
    );

    $settings->add(
        new admin_setting_heading('size', get_string('settings:headings:size', 'report_roster'), '')
    );

    $settings->add(
        new admin_setting_configselect('report_roster/size_default',
            get_string('settings:size_default', 'report_roster'),
            get_string('settings:size_default:description', 'report_roster'),
            'small',
            $options
        )
    );

    $settings->add(
        new admin_setting_configtext('report_roster/size_small',
            get_string('settings:size_small', 'report_roster'),
            get_string('settings:size_small:description', 'report_roster'),
            100,
            PARAM_INT
        )
    );

    $settings->add(
        new admin_setting_configtext('report_roster/size_medium',
            get_string('settings:size_medium', 'report_roster'),
            get_string('settings:size_medium:description', 'report_roster'),
            200,
            PARAM_INT
        )
    );

    $settings->add(
        new admin_setting_configtext('report_roster/size_large',
            get_string('settings:size_large', 'report_roster'),
            get_string('settings:size_large:description', 'report_roster'),
            300,
            PARAM_INT
        )
    );
}
