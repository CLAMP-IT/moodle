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
 * @package moodlecore
 * @subpackage backup-moodle2
 * @copyright 2010 onwards Eloy Lafuente (stronk7) {@link http://stronk7.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Define all the backup steps that will be used by the backup_assignment_activity_task
 */

/**
 * Define the complete assignment structure for backup, with file and id annotations
 */

require_once($CFG->dirroot."/mod/turnitintool/lib.php");

class backup_turnitintool_activity_structure_step extends backup_activity_structure_step {

    protected function define_structure() {
        global $CFG, $DB;

        // To know if we are including userinfo
        $userinfo = $this->get_setting_value('userinfo');

        // Define each element separated
        $turnitintool = new backup_nested_element('turnitintool', array('id'), array(
            'type', 'name', 'grade', 'numparts', 'tiiaccount', 'defaultdtstart',
            'defaultdtdue', 'defaultdtpost', 'anon', 'portfolio', 'allowlate',
            'reportgenspeed', 'submitpapersto', 'spapercheck', 'internetcheck',
            'journalcheck', 'maxfilesize', 'intro', 'introformat', 'timecreated',
            'timemodified', 'studentreports', 'dateformat', 'usegrademark',
            'gradedisplay', 'autoupdates', 'commentedittime', 'commentmaxsize',
            'autosubmission', 'shownonsubmission', 'excludebiblio', 'excludequoted',
            'excludevalue', 'excludetype', 'erater', 'erater_handbook', 'erater_dictionary',
            'erater_spelling', 'erater_grammar', 'erater_usage', 'erater_mechanics', 'erater_style', 'transmatch'
        ));

        $parts = new backup_nested_element('parts');

        $part = new backup_nested_element('part', array('id'), array(
            'turnitintoolid', 'partname', 'tiiassignid', 'dtstart', 'dtdue',
            'dtpost', 'maxmarks', 'deleted'));

        $courses = new backup_nested_element('courses');

        $course = new backup_nested_element('course', array('id'), array(
            'courseid', 'ownerid', 'ownertiiuid', 'owneremail', 'ownerfn',
            'ownerln', 'ownerun', 'turnitin_ctl', 'turnitin_cid'));

        $submissions = new backup_nested_element('submissions');

        $submission = new backup_nested_element('submission', array('id'), array(
            'userid', 'submission_part', 'submission_title',
            'submission_type', 'submission_filename', 'submission_objectid',
            'submission_score', 'submission_grade', 'submission_gmimaged',
            'submission_status', 'submission_queued', 'submission_attempts',
            'submission_modified', 'submission_parent', 'submission_nmuserid',
            'submission_nmfirstname', 'submission_nmlastname', 'submission_unanon',
            'submission_anonreason', 'submission_transmatch', 'tiiuserid'));

        $comments = new backup_nested_element('comments');

        $comment = new backup_nested_element('comment', array('id'), array(
            'submissionid', 'userid', 'commenttext', 'dateupdated', 'deleted'));

        // Build the tree
        $comments->add_child($comment);
        $submission->add_child($comments);
        $submissions->add_child($submission);
        $parts->add_child($part);
        $turnitintool->add_child($parts);
        $turnitintool->add_child($course);
        $turnitintool->add_child($submissions);

        // Define sources
        $turnitintool->set_source_table('turnitintool', array('id' => backup::VAR_ACTIVITYID));
        $values['tiiaccount']=$CFG->turnitin_account_id;
        $turnitintool->fill_values($values);

        $part->set_source_table('turnitintool_parts', array('turnitintoolid' => backup::VAR_ACTIVITYID));

        $course->set_source_sql('
            SELECT  t.id, t.courseid, t.ownerid, tu.turnitin_uid AS ownertiiuid,
                    u.email AS owneremail, u.firstname AS ownerfn, u.lastname AS ownerln,
                    u.username AS ownerun, t.turnitin_ctl, t.turnitin_cid
              FROM {turnitintool_courses} t, {user} u, {turnitintool_users} tu
             WHERE t.ownerid=u.id AND tu.userid=t.ownerid AND t.courseid = ?',
            array(backup::VAR_COURSEID));

        // All the rest of elements only happen if we are including user info
        if ($userinfo) {
            $comment->set_source_table('turnitintool_comments', array('submissionid' => '../../id'));
            //$submission->set_source_table('turnitintool_submissions', array('turnitintoolid' => '../../id'));
            $submission->set_source_sql('
            SELECT  s.*, tu.turnitin_uid AS tiiuserid
              FROM {turnitintool_submissions} s, {turnitintool_users} tu
             WHERE s.userid=tu.userid AND s.turnitintoolid = ?',
            array(backup::VAR_ACTIVITYID));
        }

        // Define id annotations
        $submission->annotate_ids('user', 'userid');

        // Define file annotations
        $turnitintool->annotate_files('mod_turnitintool', 'intro', null); // This file area hasn't itemid
        $submission->annotate_files('mod_turnitintool', 'submission', 'id');

        // Return the root element (turnitintool), wrapped into standard activity structure
        return $this->prepare_activity_structure($turnitintool);
    }
}
