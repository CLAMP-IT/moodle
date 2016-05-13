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
 * @package    assignsubmission_onlinepoodll
 * @copyright 2012 Justin Hunt {@link http://www.poodll.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Stub for upgrade code
 * @param int $oldversion
 * @return bool
 */
function xmldb_assignsubmission_onlinepoodll_upgrade($oldversion) {
	 global $CFG, $DB;

    $dbman = $DB->get_manager();

    // Moodle v2.3.0 release upgrade line
    // Put any upgrade step following this
	    // Moodle v2.3.0 release upgrade line
    // Put any upgrade step following this
    
	//Change table name to satisfy Moodle.org plugin submissions component name v table name check
	//ie it checks(or will) check 1st 28 chars of component name matches table name.
    if ($oldversion < 2012112000) {
    	$table = new xmldb_table('assignsubmission_onlinepood');	
		if ($dbman->table_exists($table)){
			$dbman->rename_table( $table, 'assignsubmission_onlinepoodl', $continue=true, $feedback=true);   
        }
		 // online PoodLL savepoint reached
        upgrade_plugin_savepoint(true, 2012112000, 'assignsubmission', 'onlinepoodll');
    
    }
	
	//add filename field.
    if ($oldversion < 2013120500) {
    	$table = new xmldb_table('assignsubmission_onlinepoodl');	
    	 $field = new xmldb_field('filename', XMLDB_TYPE_TEXT, null, null, null, null, null, 'recorder');

		// Conditionally launch add field filename.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
		
		 // online PoodLL savepoint reached
        upgrade_plugin_savepoint(true, 2013120500, 'assignsubmission', 'onlinepoodll');
    
    }
    
    //add vectordata field.
   if ($oldversion < 2014052200) {
   

        
        $table = new xmldb_table('assignsubmission_onlinepoodl');
        
        //bug in previous version means that filename field was not added properly.
        //if that is the case, add it now.
         $field = new xmldb_field('filename', XMLDB_TYPE_TEXT, null, null, null, null, null, 'recorder');

		// Conditionally launch add field filename.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        
        // Define field vectordata to be added to assignsubmission_onlinepoodl.
        $field = new xmldb_field('vectordata', XMLDB_TYPE_TEXT, null, null, null, null, null, 'filename');

        // Conditionally launch add field vectordata.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        // online PoodLL savepoint reached
        upgrade_plugin_savepoint(true, 2014052200, 'assignsubmission', 'onlinepoodll');
    }

    return true;
}


