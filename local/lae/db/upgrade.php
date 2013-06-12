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
}
