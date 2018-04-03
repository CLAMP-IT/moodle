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
 * @package   block_clampmail
 * @copyright 2013 Collaborative Liberal Arts Moodle Project
 * @copyright 2012 Louisiana State University (original Quickmail block)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {
    require_once($CFG->dirroot . '/blocks/clampmail/lib.php');

    $select = array(0 => get_string('no'), 1 => get_string('yes'));

    $roles = $DB->get_records('role', null, 'sortorder ASC');

    $defaultroles = array('editingteacher', 'teacher', 'student');
    $defaults = array_filter($roles, function ($role) use ($defaultroles) {
        return in_array($role->shortname, $defaultroles);
    });

    $onlynames = function ($role) { return $role->shortname;
    };

    $settings->add(
        new admin_setting_configmultiselect('block_clampmail/roleselection',
            get_string('select_roles', 'block_clampmail'), get_string('select_roles', 'block_clampmail'),
            array_keys($defaults),
            array_map($onlynames, $roles)
        )
    );

    $settings->add(
        new admin_setting_configselect('block_clampmail/receipt',
        get_string('receipt', 'block_clampmail'), get_string('receipt_help', 'block_clampmail'),
        0, $select
        )
    );

    $options = array(
        0 => get_string('none'),
        'idnumber' => get_string('idnumber'),
        'shortname' => get_string('shortname')
    );

    $settings->add(
        new admin_setting_configselect('block_clampmail/prepend_class',
            get_string('prepend_class', 'block_clampmail'), get_string('prepend_class_desc', 'block_clampmail'),
            0, $options
        )
    );

    $choices = array();
    $choices[NOGROUPS] = get_string('groupsnone', 'group');
    $choices[SEPARATEGROUPS] = get_string('groupsseparate', 'group');
    $choices[VISIBLEGROUPS] = get_string('groupsvisible', 'group');
    $settings->add(
        new admin_setting_configselect('block_clampmail/groupmode',
            get_string('defaultgroupmode', 'block_clampmail'),
            get_string('defaultgroupmode_desc', 'block_clampmail'),
            SEPARATEGROUPS,
            $choices
        )
    );
}
