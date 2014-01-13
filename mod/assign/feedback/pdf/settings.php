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
 * Settings for assignfeedback PDF plugin
 *
 * @package   assignfeedback_pdf
 * @copyright 2013 Davo Smith
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$settings->add(new admin_setting_configtext('assignfeedback_pdf/gspath',
                                            get_string('gspath', 'assignfeedback_pdf'),
                                            get_string('gspath2', 'assignfeedback_pdf'), '/usr/bin/gs'));

$url = new moodle_url('/mod/assign/feedback/pdf/testgs.php');
$link = html_writer::link($url, get_string('testgs', 'assignfeedback_pdf'));
$settings->add(new admin_setting_heading('testgs', '', $link));