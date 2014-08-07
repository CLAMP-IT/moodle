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
 * Manage federated logins
 *
 * @package block_federated_login
 * @copyright 2014 Smith College ITS
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(__FILE__) . '/federated_login.class.php');

defined('MOODLE_INTERNAL') || die;

$settings->add(new admin_setting_configtext('block_federated_login_home_cookie_name' ,
    get_string('homecookiename', 'block_federated_login') ,
    get_string('confighomecookiename', 'block_federated_login') , ''));

$settings->add(new admin_setting_configtext('block_federated_login_home_cookie_manager' ,
    get_string('homecookiemanager', 'block_federated_login') ,
    get_string('confighomecookiemanager', 'block_federated_login') , ''));

$settings->add(new admin_setting_configtext('block_federated_login_help_text',
    get_string('helptext', 'block_federated_login'),
    get_string('confighelptext', 'block_federated_login'), ''));

$settings->add(new admin_setting_configtext('block_federated_login_help_url',
    get_string('helpurl', 'block_federated_login'),
    get_string('confighelpurl', 'block_federated_login'), ''));

$numbers2select = array();
for ($i = 0; $i <= 10; $i++) {
    $numbers2select[] = $i;
}

$settings->add(new admin_setting_configselect('block_federated_login_school_count',
    get_string('schoolcount', 'block_federated_login'),
    get_string('configschoolcount', 'block_federated_login'),
    BLOCK_FEDERATED_LOGIN_DEFAULT_SCHOOL_COUNT, $numbers2select));

$numberofschools = BLOCK_FEDERATED_LOGIN_DEFAULT_SCHOOL_COUNT;
if (isset($CFG->block_federated_login_school_count)) {
    $numberofschools = $CFG->block_federated_login_school_count;
}

for ($i = 1; $i <= $numberofschools; $i++) {

    $settings->add(new admin_setting_heading("block_federated_login_school_heading_${i}",
        get_string('schoolsettings', 'block_federated_login') . " $i", ''));

    $settings->add(new admin_setting_configtext("block_federated_login_school_id_${i}",
        get_string('schoolid' , 'block_federated_login') . " $i",
        get_string('configschoolid', 'block_federated_login'), ''));

    $settings->add(new admin_setting_configtext("block_federated_login_school_name_${i}",
        get_string('schoolname' , 'block_federated_login') . " $i",
        get_string('configschoolname', 'block_federated_login'), ''));

    $settings->add(new admin_setting_configtext("block_federated_login_school_idp_${i}",
        get_string('schoolidp' , 'block_federated_login') . " $i",
        get_string('configschoolidp', 'block_federated_login'), ''));
}
