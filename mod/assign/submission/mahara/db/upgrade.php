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
 * @package    assignsubmission_mahara
 * @copyright  2012 Lancaster University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Stub for upgrade code
 * @param int $oldversion
 * @return bool
 */
function xmldb_assignsubmission_mahara_upgrade($oldversion) {
    global $CFG, $DB, $OUTPUT;

    $dbman = $DB->get_manager();

    if ($oldversion < 2013062401) {

        // If you're migrating from the Portland U version of the plugin, we can skip this part because
        // the table won't exist at all.
        if ($dbman->table_exists('assignsubmission_mahara')) {
            // Define field iscollection to be added to assignsubmission_mahara.
            $table = new xmldb_table('assignsubmission_mahara');
            $field = new xmldb_field('iscollection', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0', 'viewaccesskey');

            // Conditionally launch add field iscollection.
            if (!$dbman->field_exists($table, $field)) {
                $dbman->add_field($table, $field);
            }

        }

        // Mahara savepoint reached.
        upgrade_plugin_savepoint(true, 2013062401, 'assignsubmission', 'mahara');
    }

    if ($oldversion < 2014071000) {

        // If you're upgrading from the Portland U version of the plugin, this table won't exist yet, so you don't need to add the
        // viewstatus column.
        if ($dbman->table_exists('assignsubmission_mahara')) {
            require_once($CFG->dirroot.'/mod/assign/submissionplugin.php');
            require_once($CFG->dirroot.'/mod/assign/submission/mahara/locallib.php');

            // Define field viewstatus to be added to assignsubmission_mahara.
            $table = new xmldb_table('assignsubmission_mahara');
            $field = new xmldb_field('viewstatus', XMLDB_TYPE_CHAR, '20', null, null, null, null, 'iscollection');

            // Conditionally launch add field viewstatus.
            if (!$dbman->field_exists($table, $field)) {
                $dbman->add_field($table, $field);
            }

            $DB->execute("update {assignsubmission_mahara} set viewstatus='".assign_submission_mahara::STATUS_SELECTED."' where viewaccesskey is null");
            $DB->execute("update {assignsubmission_mahara} set viewstatus='".assign_submission_mahara::STATUS_SUBMITTED."' where viewaccesskey is not null");

            // Define field viewaccesskey to be dropped from assignsubmission_mahara.
            $table = new xmldb_table('assignsubmission_mahara');
            $field = new xmldb_field('viewaccesskey');

            // Conditionally launch drop field viewaccesskey.
            if ($dbman->field_exists($table, $field)) {
                $dbman->drop_field($table, $field);
            }
        }

        // Mahara savepoint reached.
        upgrade_plugin_savepoint(true, 2014071000, 'assignsubmission', 'mahara');
    }

    if ($oldversion < 2014082000) {

        // Migrate from the Portland U version of the plugin
        if (
                !$dbman->table_exists('assignsubmission_mahara')
                && $dbman->table_exists('assign_mahara_submit_views')
                && $dbman->table_exists('mahara_portfolio')
        ) {
            require_once($CFG->dirroot.'/mod/assign/submissionplugin.php');
            require_once($CFG->dirroot.'/mod/assign/submission/mahara/locallib.php');

            // Change config name
            $DB->set_field(
                    'assign_plugin_config',
                    'name',
                    'mnethostid',
                    array(
                            'plugin' => 'mahara',
                            'subtype' => 'assignsubmission',
                            'name' => 'mahara_host'
                    )
            );

            // Define table assignsubmission_mahara to be created.
            $table = new xmldb_table('assignsubmission_mahara');

            // Adding fields to table assignsubmission_mahara.
            $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
            $table->add_field('assignment', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
            $table->add_field('submission', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
            $table->add_field('viewid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
            $table->add_field('viewurl', XMLDB_TYPE_TEXT, null, null, null, null, null);
            $table->add_field('viewtitle', XMLDB_TYPE_TEXT, null, null, null, null, null);
            $table->add_field('iscollection', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0');
            $table->add_field('viewstatus', XMLDB_TYPE_CHAR, '20', null, null, null, null);

            // Adding keys to table assignsubmission_mahara.
            $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
            $table->add_key('assignment', XMLDB_KEY_FOREIGN, array('assignment'), 'assign', array('id'));
            $table->add_key('submission', XMLDB_KEY_FOREIGN, array('submission'), 'assign_submission', array('id'));
            $dbman->create_table($table);

            // Migrate data from assign_mahara_submit_views && mahara_portfolio tables
            $rs = $DB->get_recordset('assign_mahara_submit_views', null, 'id');
            foreach ($rs as $submissiondata) {
                $page = $DB->get_record('mahara_portfolio', array('id'=>$submissiondata->portfolio));
                $todb = new stdClass();
                $todb->assignment = $submissiondata->assignment;
                $todb->submission = $submissiondata->submission;
                $todb->viewid = $page->page;
                $todb->viewurl = $page->url;
                $todb->viewtitle = $page->title;
                $todb->iscollection = 0;
                $status = $submissiondata->status;
                if ($status == assign_submission_mahara::STATUS_RELEASED || $status == assign_submission_mahara::STATUS_SELECTED || $status == assign_submission_mahara::STATUS_SUBMITTED) {
                    $todb->status = $status;
                }
                else {
                }
                $todb->status = $submissiondata->status;
                $DB->insert_record('assignsubmission_mahara', $todb);
            }
            $dbman->drop_table(new xmldb_table('assign_mahara_submit_views'));
            $dbman->drop_table(new xmldb_table('mahara_portfolio'));

        }
        // Mahara savepoint reached.
        upgrade_plugin_savepoint(true, 2014082000, 'assignsubmission', 'mahara');
    }

    return true;
}