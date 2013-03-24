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
 * This file keeps track of upgrades to
 * the forum module
 *
 * Sometimes, changes between versions involve
 * alterations to database structures and other
 * major things that may break installations.
 *
 * The upgrade function in this file will attempt
 * to perform all the necessary actions to upgrade
 * your older installation to the current version.
 *
 * If there's something it cannot do itself, it
 * will tell you what you need to do.
 *
 * The commands in here will all be database-neutral,
 * using the methods of database_manager class
 *
 * Please do not forget to use upgrade_set_timeout()
 * before any action that may take longer time to finish.
 *
 * @package mod-forum
 * @copyright 2003 onwards Eloy Lafuente (stronk7) {@link http://stronk7.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

function xmldb_forum_upgrade($oldversion) {
    global $CFG, $DB, $OUTPUT;

    $dbman = $DB->get_manager(); // loads ddl manager and xmldb classes


    // Moodle v2.2.0 release upgrade line
    // Put any upgrade step following this

    /// Add anonymous forums support
    if ($oldversion < 2012061702) {
        require_once($CFG->dirroot . '/mod/forum/lib.php');
        
        // Migrate the old config setting, if present
        if(!empty($CFG->forum_anonymous)) {
            set_config('forum_enableanonymousposts', $CFG->forum_anonymous);
            set_config('forum_anonymous', null);
        }
        
        forum_add_anonymous_user();	// Add the user
        
        // add hooks to _forum and _forum_posts
        $table = new xmldb_table('forum');
        $field = new xmldb_field('anonymous');
        $field->set_attributes(XMLDB_TYPE_INTEGER, '2', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0', 'completionposts');
        if (!$dbman->field_exists($table, $field)) $dbman->add_field($table, $field);
        $table = new xmldb_table('forum_posts');
        $field = new xmldb_field('hiddenuserid');
        $field->set_attributes(XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, null, null, null, null, 'mailnow');
        if (!$dbman->field_exists($table, $field)) $dbman->add_field($table, $field);        
        upgrade_mod_savepoint(true, 2012061702, 'forum');
    }
    
    // Moodle v2.3.0 release upgrade line
    // Put any upgrade step following this

    // Forcefully assign mod/forum:allowforcesubscribe to frontpage role, as we missed that when
    // capability was introduced.
    if ($oldversion < 2012061703) {
        // If capability mod/forum:allowforcesubscribe is defined then set it for frontpage role.
        if (get_capability_info('mod/forum:allowforcesubscribe')) {
            assign_legacy_capabilities('mod/forum:allowforcesubscribe', array('frontpage' => CAP_ALLOW));
        }
        // Forum savepoint reached.
        upgrade_mod_savepoint(true, 2012061703, 'forum');
    }
    return true;
}
