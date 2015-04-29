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
// using the functions defined in lib/ddllib.php.
/**
 * Upgrade code for install
 *
 * @package   mod_turningtech
 * @copyright 2012 Turning Technologies
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * upgrade this turningtech instance - this function could be skipped but it will be needed later
 * @param int $oldversion The old version of the turningtech module
 * @return bool
 */
function xmldb_turningtech_upgrade($oldversion = 0) {
    global $CFG, $DB;
    $dbman = $DB->get_manager();
    if ($oldversion < 2012050200) { // May 2, 2012 - Revision 00.
        $tabletdt = new xmldb_table('turningtech_device_types');
        $tabletdm = new xmldb_table('turningtech_device_mapping');
        if (! $dbman->table_exists($tabletdt)) {
            $tabletdt->add_field('id', XMLDB_TYPE_INTEGER, '2', XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
            $tabletdt->add_field('type', XMLDB_TYPE_CHAR, '32', null, XMLDB_NOTNULL, null, null);
            $tabletdt->add_key('primary', XMLDB_KEY_PRIMARY, array ('id'));
            $dbman->create_table($tabletdt);
            // Insert turningtech data.
            $device_type = new stdClass();
            $device_type->type = 'Response Card';
            $device_type->id = $DB->insert_record('turningtech_device_types', $device_type);
            $device_type = new stdClass();
            $device_type->type = 'Response Ware';
            $device_type->id = $DB->insert_record('turningtech_device_types', $device_type);
        }
        if ($dbman->table_exists($tabletdm)) {
            $field = new xmldb_field('typeid', XMLDB_TYPE_INTEGER, '2', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, 0, 'deviceid');
            if (! $dbman->field_exists($tabletdm, $field)) {
                $dbman->add_field($tabletdm, $field);
            }
            $index = new xmldb_index('typeid', XMLDB_INDEX_NOTUNIQUE, array ('typeid'));
            if (! $dbman->index_exists($tabletdm, $index)) {
                $dbman->add_index($tabletdm, $index);
            }
        }
        // Upgrade_mod_savepoint(true, 2012050220, 'turningtech');.
    }
    if ($oldversion < 2012101223) {
        // Define field name to be added to turningtech.
        $table = new xmldb_table('turningtech');

        // Adding fields to table turningtech.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('course', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');

        // Adding keys to table turningtech.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        // Adding indexes to table turningtech.
        $table->add_index('course', XMLDB_INDEX_NOTUNIQUE, array('course'));

        // Conditionally launch create table for turningtech.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }
        $field = new xmldb_field('name', XMLDB_TYPE_CHAR, '255', null, null, null, null, 'course');
        // Conditionally launch add field name.
        if (! $dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        // Turningtech savepoint reached.
        upgrade_mod_savepoint(true, 2012101223, 'turningtech');
    }
    return true;
    // And upgrade begins here. For each one, you'll need one
    // block of code similar to the next one. Please, delete
    // this comment lines once this file start handling proper
    // upgrade code.
    // if ($result && $oldversion < YYYYMMDD00) { //New version in version.php
    // $result = result of "/lib/ddllib.php" function calls
    // }
    // Lines below (this included) MUST BE DELETED once you get the first version
    // of your module ready to be installed. They are here only
    // for demonstrative purposes and to show how the turningtech
    // iself has been upgraded.
    // For each upgrade block, the file turningtech/version.php
    // needs to be updated . Such change allows Moodle to know
    // that this file has to be processed.
    // To know more about how to write correct DB upgrade scripts it's
    // highly recommended to read information available at:
    // http://docs.moodle.org/en/Development:XMLDB_Documentation
    // and to play with the XMLDB Editor (in the admin menu) and its
    // PHP generation posibilities.
    // First example, some fields were added to the module on 20070400.
}