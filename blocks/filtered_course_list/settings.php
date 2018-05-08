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
 * This file defines the admin settings available for the Filtered course list block.
 *
 * @package    block_filtered_course_list
 * @copyright  2016 CLAMP
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once(dirname(__FILE__) . '/locallib.php');

if ($ADMIN->fulltree) {

    $settings->add(new admin_setting_configtextarea('block_filtered_course_list/filters',
        get_string('filters', 'block_filtered_course_list'),
        get_string('configfilters', 'block_filtered_course_list'),
        get_string('defaultfilters', 'block_filtered_course_list'), PARAM_RAW));

    $settings->add(new admin_setting_configcheckbox('block_filtered_course_list/hideallcourseslink',
        get_string('hideallcourseslink', 'block_filtered_course_list'),
        get_string('confighideallcourseslink', 'block_filtered_course_list'), BLOCK_FILTERED_COURSE_LIST_FALSE));

    $settings->add(new admin_setting_configcheckbox('block_filtered_course_list/hidefromguests',
        get_string('hidefromguests', 'block_filtered_course_list'),
        get_string('confighidefromguests', 'block_filtered_course_list'), BLOCK_FILTERED_COURSE_LIST_FALSE));

    $settings->add(new admin_setting_configcheckbox('block_filtered_course_list/hideothercourses',
        get_string('hideothercourses', 'block_filtered_course_list'),
        get_string('confighideothercourses', 'block_filtered_course_list'), BLOCK_FILTERED_COURSE_LIST_FALSE));

    $settings->add(new admin_setting_configtext('block_filtered_course_list/maxallcourse',
        get_string('maxallcourse', 'block_filtered_course_list'),
        get_string('configmaxallcourse', 'block_filtered_course_list'), 10, '/^\d{1,3}$/', 3));

    $settings->add(new admin_setting_configtext('block_filtered_course_list/coursenametpl',
        get_string('coursenametpl', 'block_filtered_course_list'),
        get_string('configcoursenametpl', 'block_filtered_course_list'), 'FULLNAME'));

    $settings->add(new admin_setting_configtext('block_filtered_course_list/catrubrictpl',
        get_string('catrubrictpl', 'block_filtered_course_list'),
        get_string('configcatrubrictpl', 'block_filtered_course_list'), 'NAME'));

    $settings->add(new admin_setting_configtext('block_filtered_course_list/catseparator',
        get_string('catseparator', 'block_filtered_course_list'),
        get_string('configcatseparator', 'block_filtered_course_list'), ' / '));

    $managerviews = array(
        BLOCK_FILTERED_COURSE_LIST_ADMIN_VIEW_ALL => get_string('allcourses', 'block_filtered_course_list'),
        BLOCK_FILTERED_COURSE_LIST_ADMIN_VIEW_OWN => get_string('owncourses', 'block_filtered_course_list')
    );

    $settings->add(new admin_setting_configselect('block_filtered_course_list/managerview',
        get_string('managerview', 'block_filtered_course_list'),
        get_string('configmanagerview', 'block_filtered_course_list'),
        BLOCK_FILTERED_COURSE_LIST_ADMIN_VIEW_ALL, $managerviews));

    $sortablefields = array(
        'fullname'  => get_string('fullname'),
        'shortname' => get_string('shortname'),
        'sortorder' => get_string('sort_sortorder', 'core_admin'),
        'idnumber'  => get_string('idnumber'),
        'startdate' => get_string('startdate'),
        'none'      => get_string('none'),
    );

    $sortvectors = array(
        'ASC'  => get_string('asc'),
        'DESC' => get_string('desc'),
    );

    $settings->add(new admin_setting_configselect('block_filtered_course_list/primarysort',
        get_string('primarysort', 'block_filtered_course_list'),
        get_string('configprimarysort', 'block_filtered_course_list'),
        'fullname', $sortablefields));

    $settings->add(new admin_setting_configselect('block_filtered_course_list/primaryvector',
        get_string('primaryvector', 'block_filtered_course_list'), '',
        'ASC', $sortvectors));

    $settings->add(new admin_setting_configselect('block_filtered_course_list/secondarysort',
        get_string('secondarysort', 'block_filtered_course_list'),
        get_string('configsecondarysort', 'block_filtered_course_list'),
        'none', $sortablefields));

    $settings->add(new admin_setting_configselect('block_filtered_course_list/secondaryvector',
        get_string('secondaryvector', 'block_filtered_course_list'), '',
        'ASC', $sortvectors));
}
