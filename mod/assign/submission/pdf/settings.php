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
 * This file defines the admin settings for this plugin
 *
 * @package   assignsubmission_pdf
 * @copyright 2012 Davo Smith
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot.'/mod/assign/submission/pdf/lib.php');

if (isset($CFG->maxbytes)) {
    $settings->add(new admin_setting_configselect('assignsubmission_pdf/maxbytes',
                        get_string('maximumsubmissionsize', 'assignsubmission_file'),
                        get_string('configmaxbytes', 'assignsubmission_file'), 1048576, get_max_upload_sizes($CFG->maxbytes)));
}

$maxfiles = array();
for ($i=1; $i <= ASSIGNSUBMISSION_PDF_MAXFILES; $i++) {
    $maxfiles[$i] = $i;
}
$settings->add(new admin_setting_configselect('assignsubmission_pdf/maxfilesubmissions',
                                               get_string('defaultmaxfilessubmission', 'assignsubmission_pdf'),
                                               get_string('configmaxfiles', 'assignsubmission_pdf'), 8, $maxfiles));

