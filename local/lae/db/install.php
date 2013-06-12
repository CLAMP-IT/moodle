<?php

defined('MOODLE_INTERNAL') || die();

function xmldb_local_lae_install() {
    global $CFG, $DB;
    $dbman = $DB->get_manager();

    // Migrate the old config setting, if present
    if(!empty($CFG->forum_anonymous)) {
        set_config('forum_enableanonymousposts', $CFG->forum_anonymous);
        set_config('forum_anonymous', null);
    } 

    // Extend forum tables
    $table = new xmldb_table('forum');
    $field = new xmldb_field('anonymous');
    $field->set_attributes(XMLDB_TYPE_INTEGER, '2', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0', 'completionposts');
    if (!$dbman->field_exists($table, $field)) $dbman->add_field($table, $field);
    $table = new xmldb_table('forum_posts');
    $field = new xmldb_field('hiddenuserid');
    $field->set_attributes(XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, null, null, null, null, 'mailnow');
    if (!$dbman->field_exists($table, $field)) $dbman->add_field($table, $field);

    // Add anonymous user
    if(empty($CFG->anonymous_userid)) {
        $anon_user = new stdClass;
        $anon_user->username = 'anonymous_user';
        $anon_user->password = hash_internal_user_password(mt_rand());
        $anon_user->auth = 'nologin';
        $anon_user->firstname = get_string('auser_firstname', 'local_lae');
        $anon_user->lastname = get_string('auser_lastname', 'local_lae');
        if($result = $DB->insert_record('user', $anon_user)) {
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
