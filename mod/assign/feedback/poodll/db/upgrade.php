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
 * Upgrade code for the feedback_poodll module.
 *
 * @package   assignfeedback_poodll
 * @copyright 2012 NetSpot {@link http://www.netspot.com.au}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Stub for upgrade code
 * @param int $oldversion
 * @return bool
 */
function xmldb_assignfeedback_poodll_upgrade($oldversion) {
    // do the upgrades
	//add filename field
    if ($oldversion < 2013120500) {
    	$table = new xmldb_table('assignfeedback_poodll');	
		$table->add_field('filename', XMLDB_TYPE_TEXT, 'small', null,
                null, null, null);

		
		 // online PoodLL savepoint reached
        upgrade_plugin_savepoint(true, 2013120500, 'assignfeedback', 'poodll');
    
    }


    return true;
}


