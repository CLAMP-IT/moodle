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
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle. If not, see <http://www.gnu.org/licenses/>.

/**
 * This file defines the admin settings for this plugin
 *
 * @package assignsubmission_file
 * @copyright 2013 Catalyst IT (@link http://www.catalyst.net.nz)
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once ($CFG->dirroot . '/mod/assign/submission/mahara/lib.php');

$settings->add(
        new admin_setting_configcheckbox('assignsubmission_mahara/default',
                new lang_string('defaulton', 'assignsubmission_mahara'),
                new lang_string('defaulton_help', 'assignsubmission_mahara'),
                0
        )
);

if ($hosts = assignsubmission_mahara_sitelist ()) {
    $settings->add(
            new admin_setting_configselect(
                    'assignsubmission_mahara/host',
                    new lang_string(
                            'defaultsite',
                            'assignsubmission_mahara',
                            new lang_string('site', 'assignsubmission_mahara')
                    ),
                    new lang_string(
                            'defaultsite_help',
                            'assignsubmission_mahara',
                            new lang_string('site', 'assignsubmission_mahara')
                    ),
                    key($hosts),
                    $hosts
            )
    );
}

$settings->add(
    new admin_setting_configselect(
        'assignsubmission_mahara/lock',
        new lang_string(
                'defaultlockpages',
                'assignsubmission_mahara',
                new lang_string('lockpages', 'assignsubmission_mahara')
        ),
        new lang_string(
                'defaultlockpages_help',
                'assignsubmission_mahara',
                new lang_string('lockpages', 'assignsubmission_mahara')
        ),
        1,
        array(0 => new lang_string('no'), 1 => new lang_string('yes'))
    )
);
