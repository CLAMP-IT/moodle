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
 * Strings for component 'enrol_paypal', language 'en_us', branch 'MOODLE_20_STABLE'
 *
 * @package   enrol_paypal
 * @copyright 1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['cost'] = 'Enroll cost';
$string['costerror'] = 'The enrollment cost is not numeric';
$string['costorkey'] = 'Please choose one of the following methods of enrollment.';
$string['defaultrole_desc'] = 'Select role which should be assigned to users during PayPal enrollments';
$string['enrolenddate_help'] = 'If enabled, users can be enrolled until this date only.';
$string['enrolenddaterror'] = 'Enrollment end date cannot be earlier than start date';
$string['enrolperiod'] = 'Enrollment duration';
$string['enrolperiod_desc'] = 'Default length of time that the enrollment is valid (in seconds). If set to zero, the enrollment duration will be unlimited by default.';
$string['enrolperiod_help'] = 'Length of time that the enrollment is valid, starting with the moment the user is enrolled. If disabled, the enrollment duration will be unlimited.';
$string['enrolstartdate_help'] = 'If enabled, users can be enrolled from this date onward only.';
$string['nocost'] = 'There is no cost associated with enrolling in this course!';
$string['paypal:config'] = 'Configure PayPal enroll instances';
$string['paypal:manage'] = 'Manage enrolled users';
$string['paypal:unenrol'] = 'Unenroll users from course';
$string['paypal:unenrolself'] = 'Unenroll self from the course';
$string['status'] = 'Allow PayPal enrollments';
$string['status_desc'] = 'Allow users to use PayPal to enroll into a course by default.';
$string['unenrolselfconfirm'] = 'Do you really want to unenroll yourself from course "{$a}"?';
