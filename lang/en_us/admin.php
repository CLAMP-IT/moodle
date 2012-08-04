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
 * Strings for component 'admin', language 'en_us', branch 'MOODLE_20_STABLE'
 *
 * @package   admin
 * @copyright 1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['availablelicenses'] = 'Available licenses';
$string['bloglevelupgradedescription'] = '<p>This site has recently been upgraded to Moodle 2.0.</p> <p>Blog visibility was simplified in 2.0, but your site still uses one of the old visibility types. </p> <p>To preserve the course-based or group-based visibility of the blog entries on your site, you need to run the following upgrade script, which will create a special "blog" type forum in each course whose enrolled users have posted blog entries, and will copy these blog entries in this special forum. </p> <p>Blogs will then be entirely switched off at the site level. No blog entries will be deleted in the process.</p> <p>You can run the script by visiting <a href="{$a->fixurl}">the blog level upgrade page</a>.</p>';
$string['bloglevelupgradeinfo'] = 'Blog visibility was simplified in 2.0, but your site still uses one of the old visibility types. To preserve the course-based or group-based visibility of the blog entries on your site, the following upgrade script will create a special "blog" type forum in each course whose enrolled users have posted blog entries, and will copy these blog entries in this special forum. Blogs will then be entirely switched off at the site level. No blog entries will be deleted in the process.';
$string['confignavcourselimit'] = 'Limits the number of courses shown to the user when they are either not logged in or are not enrolled in any courses.';
$string['confignavshowcategories'] = 'Show course categories in the navigation bar and navigation blocks. This does not occur with courses the user is currently enrolled in, they will still be listed under my courses without categories.';
$string['configprofilesforenrolledusersonly'] = 'To prevent misuse by spammers, profile descriptions of users who are not yet enrolled in any course are hidden. New users must enroll in at least one course before they can add a profile description.';
$string['configsessioncookiedomain'] = 'This allows you to change the domain that the Moodle cookies are available from. This is useful for Moodle customizations (e.g. authentication or enrollment plugins) that need to share Moodle session information with a web application on another subdomain. <strong>WARNING: it is strongly recommended to leave this setting at the default (empty) - an incorrect value will prevent all logins to the site.</strong>';
$string['configsitedefaultlicense'] = 'Default site license';
$string['configsitedefaultlicensehelp'] = 'The default license for publishing content on this site';
$string['configstatsuserthreshold'] = 'This setting specifies the minimum number of enrolled users for a course to be included in statistics calculations.';
$string['creatornewroleid_help'] = 'If the user does not already have the permission to manage the new course, the user is automatically enrolled using this role.';
$string['enrolinstancedefaults'] = 'Enrollment instance defaults';
$string['enrolinstancedefaults_desc'] = 'Default enrollment settings in new courses.';
$string['enrolmultipleusers'] = 'Enroll the users';
$string['groupenrolmentkeypolicy'] = 'Group enrollment key policy';
$string['groupenrolmentkeypolicy_desc'] = 'Turning this on will make Moodle check group enrollment keys against a valid password policy.';
$string['guestroleid_help'] = 'This role is automatically assigned to the guest user. It is also temporarily assigned to not enrolled users that enter the course via guest enrollment plugin.';
$string['licensesettings'] = 'License settings';
$string['managelicenses'] = 'Manage licenses';
$string['profilesforenrolledusersonly'] = 'Profiles for enrolled users only';
$string['requiredentrieschanged'] = '<strong>IMPORTANT - PLEASE READ<br/>(This warning message will only be displayed during this upgrade)</strong><br/>Due to a bug fix, the behavior of database activities using the \'Required entries\' and \'Required entries before viewing settings\' settings will change. A more detailed explanation of the changes can be read on <a href="http://moodle.org/mod/forum/discuss.php?d=110928" target="_blank">the database module forum</a>. The expected behavior of these settings can also be read on <a href="http://docs.moodle.org/en/Adding/editing_a_database#Required_entries" target="_blank">Moodle Docs</a>.';
$string['riskconfig'] = 'Users could change site configuration and behavior';
