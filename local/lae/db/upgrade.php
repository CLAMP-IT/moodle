<?php

defined('MOODLE_INTERNAL') || die();

function xmldb_local_lae_upgrade($oldversion) {
    global $CFG, $DB;
    $dbman = $DB->get_manager();

    if ($oldversion < 2013061200) {
        // Update course table to support display defaults
        $table = new xmldb_table('course');
        $field = new xmldb_field('filedisplaydefault', XMLDB_TYPE_INTEGER, '2', null, null, null, null, null);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        upgrade_plugin_savepoint(true, 2013061200, 'local', 'lae');
    }

    if ($oldversion < 2014010900) {
        // Add mnethostid and email address to Anonymous User.
        $user = $DB->get_record('user', array('id' => $CFG->anonymous_user));
        if (empty($user->email)) {
            $user->email = get_string('auser_email', 'local_lae');
        }
        $user->mnethostid = $CFG->mnet_localhost_id;
        $DB->update_record('user', $user);
        upgrade_plugin_savepoint(true, 2014010900, 'local', 'lae');
    }
}
