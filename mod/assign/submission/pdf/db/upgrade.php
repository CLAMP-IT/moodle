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
 * Upgrade code for install
 *
 * @package   assignsubmission_pdf
 * @copyright 2012 Davo Smith
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Stub for upgrade code
 * @param int $oldversion
 * @return bool
 */
defined('MOODLE_INTERNAL') || die();

function xmldb_assignsubmission_pdf_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2012111200) {

        // Add field numpages to table assignsubmission_pdf.
        $table = new xmldb_table('assignsubmission_pdf');
        $field = new xmldb_field('numpages', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'submission');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Remove the old numfiles field.
        $field = new xmldb_field('numfiles', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'submission');
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }

        // Pdf savepoint reached.
        upgrade_plugin_savepoint(true, 2012111200, 'assignsubmission', 'pdf');
    }

    if ($oldversion < 2012111700) {

        // Define field status to be added to assignsubmission_pdf.
        $table = new xmldb_table('assignsubmission_pdf');
        $field = new xmldb_field('status', XMLDB_TYPE_INTEGER, '4', null, XMLDB_NOTNULL, null, '0', 'numpages');

        // Conditionally launch add field status.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Pdf savepoint reached.
        upgrade_plugin_savepoint(true, 2012111700, 'assignsubmission', 'pdf');
    }

    if ($oldversion < 2012122600) {

        // Define field templatedata to be added to assignsubmission_pdf.
        $table = new xmldb_table('assignsubmission_pdf');
        $field = new xmldb_field('templatedata', XMLDB_TYPE_TEXT, null, null, null, null, null, 'status');

        // Conditionally launch add field templatedata.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Pdf savepoint reached.
        upgrade_plugin_savepoint(true, 2012122600, 'assignsubmission', 'pdf');
    }

    return true;
}


