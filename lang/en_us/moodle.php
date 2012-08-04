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
 * Strings for component 'moodle', language 'en_us', branch 'MOODLE_20_STABLE'
 *
 * @package   moodle
 * @copyright 1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['addedtogroupnotenrolled'] = 'Not added to group "{$a}", because not enrolled in course';
$string['adminhelpthemes'] = 'Choose how the site looks (colors, fonts etc)';
$string['advancedfilter'] = 'Advanced filter';
$string['databaseupgradelocal'] = 'Local database customizations version is now {$a}';
$string['existingstudents'] = 'Enrolled students';
$string['expirynotify'] = 'Enrollment Expiration Notification';
$string['expirynotifyemail'] = 'The following students in this course are expiring after exactly {$a->threshold} days:

{$a->current}

The following students in this course are expiring in less than {$a->threshold} days:

{$a->past}


You may go to the following page to extend their enrollment period:
{$a->extendurl}';
$string['expirynotifystudentsemail'] = 'Dear {$a->studentstr}:

This is a notification that your enrollment in the course {$a->course} will expire in {$a->threshold} days.

Please contact your {$a->teacherstr} for any further enquiries.';
$string['expirynotifystudents_help'] = 'If an enrollment duration has been specified, then this setting determines whether students receive email notification when they are about to be unenrolled from the course.';
$string['expirythreshold_help'] = 'If an enrollment duration has been specified, then this setting determines the number of days notice given before students are unenrolled from the course.';
$string['extendenrol'] = 'Extend enrollment';
$string['frontpagedescription'] = 'Front page description';
$string['gpl'] = 'Copyright (C) 1999 onwards Martin Dougiamas (http://moodle.com)

This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation; either version 2 of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.

See the Moodle License information page for full details: http://docs.moodle.org/dev/License';
$string['gpl3'] = 'Copyright (C) 1999 onwards Martin Dougiamas (http://moodle.com)

This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation; either version 2 of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.

See the Moodle License information page for full details: http://docs.moodle.org/dev/License';
$string['gpllicense'] = 'GPL license';
$string['groupextendenrol'] = 'Extend enrollment for all';
$string['lastname'] = 'Last name';
$string['license'] = 'License';
$string['licenses'] = 'Licenses ';
$string['loginsteps'] = 'Hi! For full access to courses you\'ll need to take
a minute to create a new account for yourself on this web site.
Each of the individual courses may also have a one-time
"enrollment key", which you won\'t need until later. Here are
the steps:
<ol>
<li>Fill out the <a href="{$a}">New Account</a> form with your details.</li>
<li>An email will be immediately sent to your email address.</li>
<li>Read your email, and click on the web link it contains.</li>
<li>Your account will be confirmed and you will be logged in.</li>
<li>Now, select the course you want to participate in.</li>
<li>If you are prompted for an "enrollment key" - use the one
that your teacher has given you. This will "enroll" you in the
course.</li>
<li>You can now access the full course. From now on you will only need
to enter your personal username and password (in the form on this page)
to log in and access any course you have enrolled in.</li>
</ol>';
$string['normalfilter'] = 'Normal filter';
$string['nostudentsyet'] = 'No students enrolled in this course yet';
$string['notenrolled'] = '{$a} is not enrolled in this course.';
$string['notenrolledprofile'] = 'This profile is not available because this user is not enrolled in this course.';
$string['paymentinstant'] = 'Use the button below to pay and be enrolled within minutes!';
$string['paymentthanks'] = 'Thank you for your payment! You are now enrolled in your course:<br />"{$a}"';
$string['profilenotshown'] = 'This profile description will not be shown until this person is enrolled in at least one course.';
$string['statsreport13'] = 'Most participatory courses (enrollments)';
$string['toomanytoshow'] = 'There are too many users to show';
$string['unusedaccounts'] = 'Accounts unused for more than {$a} days are automatically unenrolled';
$string['updatinga'] = 'Updating {$a}';
$string['withselectedusers_help'] = '* Send message - For sending a message to one or more participants * Add a new note - For adding a note to a selected participant * Add a common note - For adding the same note to more than one participant * Extend enrollment (individual) - For extending a selected student\'s access to the course, even when an enrollment period is set * Extend enrollment (common) - For extending more than one student\'s access to the course by the same amount';
$string['youneedtoenrol'] = 'To perform that action you need to enroll in this course.';
