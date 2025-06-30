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

defined('MOODLE_INTERNAL') || die();

/**
 * Defines web service functions for the local_accessibility_filescan plugin.
 *
 * This file specifies the external web service functions available in the
 * local_accessibility_filescan Moodle plugin. The functions are designed
 * to be consumed by web services, including Moodle's official mobile service.
 * The primary function detailed here allows for the retrieval of accessibility
 * file scan results by course ID, supporting both server processing and AJAX
 * calls from the web.
 *
 * @package local_accessibility_filescan
 */
$functions = [
    // The name of your web service function, as discussed above.
    'local_accessibility_filescan_get_results_by_course' => [
        // The name of the namespaced class that the function is located in.
        'classname'   => 'local_accessibility_filescan\external\get_results_by_course',

        // A brief, human-readable, description of the web service function.
        'description' => 'Get results of the accessibility fielscan plugin by course id.',

        // Options include read, and write.
        'type'        => 'read',

        // Whether the service is available for use in AJAX calls from the web.
        'ajax'        => true,

        // An optional list of services where the function will be included.
        'services' => [
            // A standard Moodle install includes one default service:
            // - MOODLE_OFFICIAL_MOBILE_SERVICE.
            // Specifying this service means that your function will be available for
            // use in the Moodle Mobile App.
            MOODLE_OFFICIAL_MOBILE_SERVICE,
        ],
    ],
];
