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

defined('MOODLE_INTERNAL') || die();

function xmldb_local_lae_install() {
    global $CFG, $DB;
    $dbman = $DB->get_manager();

    // Migrate the old config setting, if present.
    if (!empty($CFG->forum_anonymous)) {
        set_config('forum_enableanonymousposts', $CFG->forum_anonymous);
        set_config('forum_anonymous', null);
    }

    // Extend forum tables.
    $table = new xmldb_table('forum');
    $field = new xmldb_field('anonymous');
    $field->set_attributes(XMLDB_TYPE_INTEGER, '2', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0', 'completionposts');
    if (!$dbman->field_exists($table, $field)) {
        $dbman->add_field($table, $field);
    }
    $table = new xmldb_table('forum_posts');
    $field = new xmldb_field('hiddenuserid');
    $field->set_attributes(XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, null, null, null, null, 'mailnow');
    if (!$dbman->field_exists($table, $field)) {
        $dbman->add_field($table, $field);
    }

    // Add anonymous user.
    if (empty($CFG->anonymous_userid)) {
        $anon_user = new stdClass;
        $anon_user->username = 'anonymous_user';

        // The password needs strings.
        $anon_user->password = hash_internal_user_password(
           str_shuffle($anon_user->username). (string)mt_rand()
        );
        $anon_user->auth = 'nologin';
        $anon_user->firstname = get_string('auser_firstname', 'local_lae');
        $anon_user->lastname = get_string('auser_lastname', 'local_lae');
        $anon_user->mnethostid = $CFG->mnet_localhost_id;
        $anon_user->email = get_string('auser_email', 'local_lae');
        if ($result = $DB->insert_record('user', $anon_user)) {
            set_config('anonymous_userid', $result);
        } else {
            print_error("Failed to create anonymous user");
            return false;
        }
    }

    // Update course table to support display defaults
    $table = new xmldb_table('course');
    $field = new xmldb_field('filedisplaydefault', XMLDB_TYPE_INTEGER, '2', null, null, null, null, null);
    if (!$dbman->field_exists($table, $field)) {
        $dbman->add_field($table, $field);
    }

    return true;
}
