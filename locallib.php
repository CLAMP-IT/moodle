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
 * Local library functions for the roster report.
 *
 * @package   report_roster
 * @copyright 2013 Lafayette College ITS
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('ROSTER_MODE_DISPLAY', 'display');
define('ROSTER_MODE_PRINT', 'print');

/**
 * Retrieves the groups for the course and formats them for use in a drop-down
 * selector.
 *
 * @param int $id The course id
 * @return array The course groups indexed by group id
 */
function report_roster_get_options_group($id) {
    $groupsfromdb = groups_get_all_groups($id);
    $groups = array(0 => get_string('allusers', 'report_roster'));
    foreach ($groupsfromdb as $key => $value) {
        $groups[$key] = $value->name;
    }
    return $groups;
}

/**
 * Retrieves the roles for the course and formats them for use in a drop-down
 * selector.
 *
 * @param int $id The course id
 * @return array The course roles indexed by role id
 */
function report_roster_get_options_role($id) {
    global $USER;

    $context       = context_course::instance($id);
    $rolesfromdb   = get_roles_used_in_context($context);
    $viewableroles = get_viewable_roles($context, $USER->id);

    $roles = array(0 => get_string('allusers', 'report_roster'));
    foreach ($rolesfromdb as $role) {
        $rolename = $viewableroles[$role->id];
        if ($rolename) {
            $roles[$role->id] = $rolename;
        }
    }
    return $roles;
}

/**
 * Retrieves the size options and formats them for use in a drop-down
 * selector.
 *
 * @return array The user image size options
 */
function report_roster_get_options_size() {
    $sizes = array();

    foreach (array('small', 'medium', 'large') as $size) {
        $pixels = (int) get_config('report_roster', "size_$size");
        $label  = get_string("size:$size", 'report_roster');

        if ($pixels > 0) {
            $sizes[$pixels] = $label;
        }
    }

    return $sizes;
}

/**
 * Creates the action buttons (learning mode and groups) used on the report page.
 *
 * @param int $id The course id
 * @param moodle_url $url The current page URL
 * @param array $params Current parameters values as an associative array (group, role, size, mode)
 * @return string The generated HTML
 */
function report_roster_output_action_buttons($id, $url, $params) {
    global $OUTPUT;

    $options = array();
    $options['mode']   = array(
        ROSTER_MODE_DISPLAY => get_string('webmode', 'report_roster'),
        ROSTER_MODE_PRINT => get_string('printmode', 'report_roster'));
    $options['group'] = report_roster_get_options_group($id);
    $options['role']  = report_roster_get_options_role($id);
    $options['size']  = report_roster_get_options_size($id);

    // If there's only one size, don't bother displaying the select.
    if (count($options['size']) <= 1) {
        $options['size'] = array();
    }

    $selects = array();
    foreach ($params as $key => $val) {
        if (array_key_exists($key, $options) && !empty($options[$key])) {
            $myurl      = clone $url;
            $myparams   = $params;

            unset($myparams[$key]);
            $myurl->params($myparams);

            $myselect        = new single_select($myurl, $key, $options[$key], $val, null);
            $myselect->label = get_string_manager()->string_exists("param:$key", 'report_roster')
                             ? get_string("param:$key", 'report_roster')
                             : get_string($key);

            $selects[$key] = $myselect;
        }
    }

    $html = html_writer::start_tag('div');
    foreach ($selects as $select) {
        $html .= $OUTPUT->render($select);
    }
    $html .= html_writer::end_tag('div');
    return $html;
}

/**
 * Returns the value of the given field for the given user. Returns false
 * if the field does not exist on the user object.
 *
 * @param string $field the user field name
 * @param stdClass $user the user object
 *
 * @return string the value of the field
 */
function report_roster_process_field($field, $user) {
    $field = trim($field);
    if ($field == 'fullname') {
        return fullname($user);
    } else if (strpos($field, 'currenttime') === 0) {
        $format = trim(str_replace('currenttime', '', $field));
        return userdate(time(), $format, $user->timezone);
    } else if (property_exists($user, $field) && !empty($user->{$field}) && is_string($user->{$field})) {
        return $user->{$field};
    }
    return false;
}

/**
 * Resolves which size display when no query param has been passed.
 *
 * @return string The generated HTML
 */
function report_roster_resolve_auto_size() {
    $defaultselector = get_config('report_roster', 'size_default');
    $defaultsize = get_config('report_roster', 'size_' . $defaultselector);

    if ($defaultsize != 0) {
        // If the default size config is valid, return that.
        return (int) $defaultsize;
    } else {
        // Otherwise, check the other size options and return the first non-zero one.
        foreach (array('small', 'medium', 'large') as $selector) {
            $size = get_config('report_roster', 'size_' . $selector);
            if ($size != 0) {
                return $size;
            }
        }
        // And finally, if none of that worked, hard default to 100.
        return 100;
    }
}

/**
 * Build the fields to retrieve from the user profile.
 *
 * @return string SQL-query-like string of fields to fetch, for use in get_enrolled_users / get_role_users
 */
function report_roster_profile_fields_query() {
    global $DB, $USER;

    // Set extra fields to retrieve.
    $extrafields = ['username', 'timezone'];
    $fieldsconfig = explode("\n", get_config('report_roster', 'fields'));
    foreach ($fieldsconfig as $field) {
        $field = trim($field);

        // Explicitly exclude fullname from the database query.
        if ($field === 'fullname') {
            continue;
        }

        if ( property_exists($USER, $field)) {
            $extrafields[] = $field;
        }
    }

    $fields = \core_user\fields::for_userpic();
    $fields->including(...$extrafields);

    $selects = $fields->get_sql('u', false, '', 'id', false)->selects;
    $selects = str_replace(', ', ',', $selects);
    return $selects;
}
