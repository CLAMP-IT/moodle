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
 * @copyright  2015 CLAMP
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once(dirname(__FILE__) . '/locallib.php');

if ($ADMIN->fulltree) {

    $settings->add(new admin_setting_heading('block_filtered_course_list/general',
        get_string('generalsettings', 'block_filtered_course_list'), BLOCK_FILTERED_COURSE_LIST_EMPTY));

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

    $settings->add(new admin_setting_configcheckbox('block_filtered_course_list/collapsible',
        get_string('collapsible', 'block_filtered_course_list'),
        get_string('configcollapsible', 'block_filtered_course_list'), BLOCK_FILTERED_COURSE_LIST_TRUE));

    $adminviews = array(
        BLOCK_FILTERED_COURSE_LIST_ADMIN_VIEW_ALL => get_string('allcourses', 'block_filtered_course_list'),
        BLOCK_FILTERED_COURSE_LIST_ADMIN_VIEW_OWN => get_string('owncourses', 'block_filtered_course_list')
    );

    $settings->add(new admin_setting_configselect('block_filtered_course_list/adminview',
        get_string('adminview', 'block_filtered_course_list'),
        get_string('configadminview', 'block_filtered_course_list'),
        BLOCK_FILTERED_COURSE_LIST_ADMIN_VIEW_ALL, $adminviews));

    $filters = array(
        'shortname' => get_string('filtershortname', 'block_filtered_course_list'),
        'categories' => get_string('filtercategories', 'block_filtered_course_list')
    );

    $settings->add(new admin_setting_configselect('block_filtered_course_list/filtertype',
        get_string('filtertype', 'block_filtered_course_list'),
        get_string('configfiltertype', 'block_filtered_course_list'), 'shortname', $filters));

    $settings->add(new admin_setting_heading('block_filtered_course_list/shortname',
        get_string('shortnamesettings', 'block_filtered_course_list'),
        get_string('shortnamesettingsinfo', 'block_filtered_course_list')));

    $settings->add(new admin_setting_configcheckbox('block_filtered_course_list/useregex',
        get_string('useregex', 'block_filtered_course_list'),
        get_string('configuseregex', 'block_filtered_course_list'), BLOCK_FILTERED_COURSE_LIST_FALSE));

    $howmanylabels = array();
    for ($i = 0; $i <= 10; $i++) {
        $howmanylabels[] = $i;
    }

    $settings->add(new admin_setting_configselect('block_filtered_course_list/labelscount',
        get_string('labelscount', 'block_filtered_course_list'),
        get_string('configlabelscount', 'block_filtered_course_list'),
        BLOCK_FILTERED_COURSE_LIST_DEFAULT_LABELSCOUNT, $howmanylabels));

    $labelscount = get_config('block_filtered_course_list', 'labelscount');

    if ($labelscount == '') {
        $labelscount = BLOCK_FILTERED_COURSE_LIST_DEFAULT_LABELSCOUNT;
    }

    $settings->add(new admin_setting_heading('block_filtered_course_list/currentcourses',
        get_string('currentshortname', 'block_filtered_course_list'), ''));

    $settings->add(new admin_setting_configtext('block_filtered_course_list/currentshortname',
        get_string('currentshortname', 'block_filtered_course_list'),
        get_string('configcurrentshortname', 'block_filtered_course_list'), BLOCK_FILTERED_COURSE_LIST_EMPTY));

    $settings->add(new admin_setting_configcheckbox('block_filtered_course_list/currentexpanded',
        get_string('expanded', 'block_filtered_course_list'),
        get_string('configexpanded', 'block_filtered_course_list'), BLOCK_FILTERED_COURSE_LIST_FALSE));

    $settings->add(new admin_setting_heading('block_filtered_course_list/futurecourses',
        get_string('futureshortname', 'block_filtered_course_list'), ''));

    $settings->add(new admin_setting_configtext('block_filtered_course_list/futureshortname',
        get_string('futureshortname', 'block_filtered_course_list'),
        get_string('configfutureshortname', 'block_filtered_course_list'), BLOCK_FILTERED_COURSE_LIST_EMPTY));

    $settings->add(new admin_setting_configcheckbox('block_filtered_course_list/futureexpanded',
        get_string('expanded', 'block_filtered_course_list'),
        get_string('configexpanded', 'block_filtered_course_list'), BLOCK_FILTERED_COURSE_LIST_FALSE));

    for ($i = 1; $i <= $labelscount; $i++) {

        $settings->add(new admin_setting_heading("block_filtered_course_list/custom$i",
            get_string('customlabel', 'block_filtered_course_list') . " $i", ''));

        $settings->add(new admin_setting_configtext("block_filtered_course_list/customlabel$i",
            get_string('customlabel', 'block_filtered_course_list') . " $i",
            get_string('configcustomlabel', 'block_filtered_course_list'), '', PARAM_TEXT));

        $settings->add(new admin_setting_configtext("block_filtered_course_list/customshortname$i",
            get_string('customshortname', 'block_filtered_course_list') . " $i",
            get_string('configcustomshortname', 'block_filtered_course_list'), BLOCK_FILTERED_COURSE_LIST_EMPTY));

        $settings->add(new admin_setting_configcheckbox("block_filtered_course_list/labelexpanded$i",
            get_string('expanded', 'block_filtered_course_list'),
            get_string('configexpanded', 'block_filtered_course_list'), BLOCK_FILTERED_COURSE_LIST_FALSE));
    }

    $settings->add(new admin_setting_heading('block_filtered_course_list/catx',
        get_string('categorysettings', 'block_filtered_course_list'),
        get_string('categorysettingsinfo', 'block_filtered_course_list')));

    $categories = coursecat::make_categories_list();
    $top = array(BLOCK_FILTERED_COURSE_LIST_DEFAULT_CATEGORY  => get_string('top', 'block_filtered_course_list'));
    $categories = $top + $categories;
    $settings->add(new admin_setting_configselect('block_filtered_course_list/categories',
        get_string('categories', 'block_filtered_course_list'),
        get_string('configcategories', 'block_filtered_course_list'), BLOCK_FILTERED_COURSE_LIST_DEFAULT_CATEGORY, $categories));
}
