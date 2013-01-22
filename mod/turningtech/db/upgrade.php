<?php //$Id: upgrade.php,v 1.2 2007/08/08 22:36:54 stronk7 Exp $

// This file keeps track of upgrades to
// the turningtech module
//
// Sometimes, changes between versions involve
// alterations to database structures and other
// major things that may break installations.
//
// The upgrade function in this file will attempt
// to perform all the necessary actions to upgrade
// your older installtion to the current version.
//
// If there's something it cannot do itself, it
// will tell you what you need to do.
//
// The commands in here will all be database-neutral,
// using the functions defined in lib/ddllib.php

function xmldb_turningtech_upgrade($oldversion = 0) {
    global $CFG, $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2009042201) {
        $table = new xmldb_table('turningtech');

        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        /// label savepoint reached
        upgrade_mod_savepoint(true, 2009042201, 'turningtech');
    }

    if ($oldversion < 2012050200) { // ** May 2, 2012 - Revision 00
        $tableTDT = new xmldb_table('turningtech_device_types');
        $tableTDM = new xmldb_table('turningtech_device_mapping');

        if ( !$dbman->table_exists($tableTDT) ) {
            $tableTDT->add_field('id', XMLDB_TYPE_INTEGER, '2', XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
            $tableTDT->add_field('type', XMLDB_TYPE_CHAR, '32', null, XMLDB_NOTNULL, null, null);

            $tableTDT->add_key('primary', XMLDB_KEY_PRIMARY, array(
                'id'
            ));

            $dbman->create_table($tableTDT);

            // insert turningtech data
            $device_type       = new stdClass();
            $device_type->type = 'Response Card';
            $device_type->id   = $DB->insert_record('turningtech_device_types', $device_type);

            $device_type       = new stdClass();
            $device_type->type = 'Response Ware';
            $device_type->id   = $DB->insert_record('turningtech_device_types', $device_type);
        }

        if ( $dbman->table_exists($tableTDM) ) {
            $field = new xmldb_field('typeid', XMLDB_TYPE_INTEGER, '2', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, 0, 'deviceid');

            if (!$dbman->field_exists($tableTDM, $field)) {
                $dbman->add_field($tableTDM, $field);
            }

            $index = new xmldb_index('typeid', XMLDB_INDEX_NOTUNIQUE, array(
                'typeid'
            ));

            if (!$dbman->index_exists($tableTDM, $index)) {
                $dbman->add_index($tableTDM, $index);
            }
        }

        //upgrade_mod_savepoint(true, 2012050220, 'turningtech');
    }

    return true;

    /// And upgrade begins here. For each one, you'll need one
    /// block of code similar to the next one. Please, delete
    /// this comment lines once this file start handling proper
    /// upgrade code.

    /// if ($result && $oldversion < YYYYMMDD00) { //New version in version.php
    ///     $result = result of "/lib/ddllib.php" function calls
    /// }

    /// Lines below (this included)  MUST BE DELETED once you get the first version
    /// of your module ready to be installed. They are here only
    /// for demonstrative purposes and to show how the turningtech
    /// iself has been upgraded.

    /// For each upgrade block, the file turningtech/version.php
    /// needs to be updated . Such change allows Moodle to know
    /// that this file has to be processed.

    /// To know more about how to write correct DB upgrade scripts it's
    /// highly recommended to read information available at:
    ///   http://docs.moodle.org/en/Development:XMLDB_Documentation
    /// and to play with the XMLDB Editor (in the admin menu) and its
    /// PHP generation posibilities.

    /// First example, some fields were added to the module on 20070400

}

?>