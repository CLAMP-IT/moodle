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
 * Essay question type upgrade code.
 *
 * @package    qtype
 * @subpackage poodllrecording
 * @copyright  2011 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();


/**
 * Upgrade code for the poodllrecording question type.
 * @param int $oldversion the version we are upgrading from.
 */
function xmldb_qtype_poodllrecording_upgrade($oldversion) {
    global $CFG, $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2011031000) {
        // Define table qtype_poodllrecording_options to be created
        $table = new xmldb_table('qtype_poodllrecording_options');

        // Adding fields to table qtype_poodllrecording_options
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED,
                XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('questionid', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED,
                XMLDB_NOTNULL, null, null);
        $table->add_field('responseformat', XMLDB_TYPE_CHAR, '16', null,
                XMLDB_NOTNULL, null, 'editor');
        $table->add_field('responsefieldlines', XMLDB_TYPE_INTEGER, '4', null,
                XMLDB_NOTNULL, null, '15');
        $table->add_field('attachments', XMLDB_TYPE_INTEGER, '4', null,
                XMLDB_NOTNULL, null, '0');
        $table->add_field('graderinfo', XMLDB_TYPE_TEXT, 'small', null,
                null, null, null);
        $table->add_field('graderinfoformat', XMLDB_TYPE_INTEGER, '4', XMLDB_UNSIGNED,
                XMLDB_NOTNULL, null, '0');

        // Adding keys to table qtype_poodllrecording_options
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->add_key('questionid', XMLDB_KEY_FOREIGN_UNIQUE,
                array('questionid'), 'question', array('id'));

        // Conditionally launch create table for qtype_poodllrecording_options
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // poodllrecording savepoint reached
        upgrade_plugin_savepoint(true, 2011031000, 'qtype', 'poodllrecording');
    }

    if ($oldversion < 2011060300) {
        // Insert a row into the qtype_poodllrecording_options table for each existing poodllrecording question.
        $DB->execute("
                INSERT INTO {qtype_poodllrecording_options} (questionid, responseformat,
                        responsefieldlines, attachments, graderinfo, graderinfoformat)
                SELECT q.id, 'editor', 15, 0, '', " . FORMAT_MOODLE . "
                FROM {question} q
                WHERE q.qtype = 'poodllrecording'
                AND NOT EXISTS (
                    SELECT 'x'
                    FROM {qtype_poodllrecording_options} qeo
                    WHERE qeo.questionid = q.id)");

        // poodllrecording savepoint reached
        upgrade_plugin_savepoint(true, 2011060300, 'qtype', 'poodllrecording');
    }


    // Moodle v2.3.0 release upgrade line
    // Put any upgrade step following this
    
    if ($oldversion < 2012062700) {
    	$table = new xmldb_table('qtype_poodllrecording_options');	
		if ($dbman->table_exists($table)){
			$dbman->rename_table( $table, 'qtype_poodllrecording_opts', $continue=true, $feedback=true);   
        }
		 // poodllrecording savepoint reached
        upgrade_plugin_savepoint(true, 2012062700, 'qtype', 'poodllrecording');
    
    }
    
    // Added whiteboard and related fields 20120814
    // Put any upgrade step following this
    
    if ($oldversion < 2012081400) {
    	// Define field questiontextformat to be added to question_order_sub
        $table = new xmldb_table('qtype_poodllrecording_opts');
        $field1 = new xmldb_field('backimage', XMLDB_TYPE_TEXT, 'small', null, null, null, null, 'graderinfoformat');
		$field2 = new xmldb_field('boardsize', XMLDB_TYPE_TEXT, 'small', null, null, null, null, 'backimage');

        // Conditionally  add backimage field
        if (!$dbman->field_exists($table, $field1)) {
            $dbman->add_field($table, $field1);
        }
        
        // Conditionally add boardsize field
        if (!$dbman->field_exists($table, $field2)) {
            $dbman->add_field($table, $field2);
        }
        
		 // poodllrecording savepoint reached
        upgrade_plugin_savepoint(true, 2012081400, 'qtype', 'poodllrecording');
    
    }
	
	if ($oldversion < 2013022000) {
    	// Define field questiontextformat to be added to question_order_sub
        $table = new xmldb_table('qtype_poodllrecording_opts');
		$field = new xmldb_field('boardsize', XMLDB_TYPE_TEXT, 'small', null, null, null, null, 'backimage');
		
		$dbman->change_field_type($table,$field);
		$dbman->change_field_notnull($table,$field);
		$dbman->change_field_default($table,$field);
		
		 // poodllrecording savepoint reached
        upgrade_plugin_savepoint(true, 2013022000, 'qtype', 'poodllrecording');
	}
	
	if($oldversion < 2013102900){
		 $table = new xmldb_table('qtype_poodllrecording_opts');
		 $field = new xmldb_field('timelimit', XMLDB_TYPE_INTEGER, '4', null,XMLDB_NOTNULL, null, 0);
		 
		  // Conditionally  add backimage field
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
		
		 // poodllrecording savepoint reached
        upgrade_plugin_savepoint(true, 2013102900, 'qtype', 'poodllrecording');
		
	}

    return true;
}
