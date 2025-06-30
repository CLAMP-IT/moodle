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
 * @package   block_clampmail
 * @copyright 2018 Collaborative Liberal Arts Moodle Project
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

use \block_clampmail\privacy\provider;

class block_clampmail_privacy_testcase extends \core_privacy\tests\provider_testcase {

    /**
     * Test setUp.
     */
    public function setUp(): void {
        global $DB;

        $this->resetAfterTest(true);
        $this->data = array();

        $this->data['provider'] = new provider();

        // Test teacher user.
        $this->data['teacher'] = $this->getDataGenerator()->create_user(array(
            'username'  => 'hfogg',
            'email'     => 'hfogg@brakebills.edu',
            'firstname' => 'Henry',
            'lastname'  => 'Fogg',
        ));

        // Test student user.
        $this->data['student1'] = $this->getDataGenerator()->create_user(array(
            'username'  => 'jwicker',
            'email'     => 'jwicker@brakebills.edu',
            'firstname' => 'Julia',
            'lastname'  => 'Wicker',
        ));

        // Test student user.
        $this->data['student2'] = $this->getDataGenerator()->create_user(array(
            'username'  => 'qcoldwater',
            'email'     => 'qcoldwater@brakebills.edu',
            'firstname' => 'Quentin',
            'lastname'  => 'Coldwater',
        ));

        // Test course.
        $this->data['course'] = $this->getDataGenerator()->create_course(array(
            'shortname' => 'testcourse'
        ));

        // Second test course.
        $this->data['course2'] = $this->getDataGenerator()->create_course(array(
            'shortname' => 'testcourse2'
        ));

        // Manual enrolment entry and course context.
        $this->data['manualenrol'] = $DB->get_record('enrol', array('enrol' => 'manual', 'courseid' => $this->data['course']->id));
        $this->data['coursecontext'] = \context_course::instance($this->data['course']->id);

        // Manual enrolment entry and course context for test course 2.
        $this->data['manualenrol2'] = $DB->get_record('enrol', array('enrol' => 'manual', 'courseid' => $this->data['course2']->id));
        $this->data['coursecontext2'] = \context_course::instance($this->data['course2']->id);

        // Add the block to the page.
        $page = new \moodle_page();
        $page->set_context($this->data['coursecontext']);
        $page->set_pagelayout('standard');
        $page->set_pagetype('course-view');
        $page->set_course($this->data['course']);
        $page->blocks->load_blocks();
        $page->blocks->add_block_at_end_of_default_region('clampmail');

        $page2 = new \moodle_page();
        $page2->set_context($page->context);
        $page2->set_pagelayout($page->pagelayout);
        $page2->set_pagetype($page->pagetype);
        $page2->set_course($this->data['course']);
        $page->blocks->load_blocks();
        $page2->blocks->load_blocks();
        $blocks = $page2->blocks->get_blocks_for_region($page2->blocks->get_default_region());
        $block = end($blocks);
        $block = block_instance('clampmail', $block->instance);

        $this->data['block'] = $block;

        $this->data['me_plugin'] = new enrol_manual_plugin();

        $this->data['studentrole'] = $DB->get_record('role', array('shortname' => 'student'))->id;
        $this->data['teacherrole'] = $DB->get_record('role', array('shortname' => 'editingteacher'))->id;

        $this->setUser($this->data['teacher']);
    }

    public function insert_message($type, $fromid, $toid, $courseid) {
        global $DB;

        $hash = md5(rand());

        $data = new stdClass();
        $data->courseid = $courseid;
        $data->userid = $fromid;
        $data->mailto = $toid;
        $data->subject = "Subject $hash";
        $data->message = "Message $hash $hash $hash";
        $data->attachment = '';
        $data->time = time();

        $data->id = $DB->insert_record('block_clampmail_' . $type, $data, true);

        return $data;
    }

    public function insert_signature($userid) {
        global $DB;

        $hash = md5(rand());

        $data = new stdClass();
        $data->userid = $userid;
        $data->title = "Title $hash";
        $data->signature = "My name is $hash $hash";

        $data->id = $DB->insert_record('block_clampmail_signatures', $data, true);

        return $data;
    }

    public function delete_data_for_context($context) {
        $classname = $this->get_provider_classname('block_clampmail');
        $classname::_delete_data_for_all_users_in_context($context);
    }

    public function delete_data_for_user($userid) {
        $contextlist = $this->get_contexts_for_userid($userid, 'block_clampmail');

        $approvedcontextlist = new \core_privacy\tests\request\approved_contextlist(
            \core_user::get_user($userid),
            'block_clampmail',
            $contextlist->get_contextids()
        );

        $classname = $this->get_provider_classname('block_clampmail');
        $classname::_delete_data_for_user($approvedcontextlist);
    }

    /**
     * Test the 'get_contexts_for_userid' function.
     */
    public function test_get_contexts_for_userid() {
        $d = $this->data;

        $d['me_plugin']->enrol_user($d['manualenrol'], $d['teacher']->id, $d['teacherrole']);
        $d['me_plugin']->enrol_user($d['manualenrol2'], $d['teacher']->id, $d['teacherrole']);

        // No contexts.
        $contextlist = $this->get_contexts_for_userid($d['teacher']->id, 'block_clampmail');
        $contexts = $contextlist->get_contextids();
        $this->assertCount(0, $contexts);

        // A course context.
        $this->insert_message('drafts', $d['teacher']->id, $d['teacher']->id, $d['course']->id);
        $contextlist = $this->get_contexts_for_userid($d['teacher']->id, 'block_clampmail');
        $contexts = $contextlist->get_contextids();
        $this->assertCount(1, $contexts);
        $this->assertEquals($d['coursecontext']->id, $contexts[0]);

        // Plus a signature (user context).
        $this->insert_signature($d['teacher']->id);
        $contextlist = $this->get_contexts_for_userid($d['teacher']->id, 'block_clampmail');
        $contexts = $contextlist->get_contextids();
        $this->assertCount(2, $contexts);
        $this->assertContainsEquals($d['coursecontext']->id, $contexts);
        $this->assertContainsEquals(\context_user::instance($d['teacher']->id)->id, $contexts);
    }

    /**
     * Test the 'get_users_in_context' function.
     */
    public function test_get_users_in_context() {
        if (!interface_exists('\core_privacy\local\request\core_userlist_provider')) {
            return;
        }

        $d = $this->data;

        $d['me_plugin']->enrol_user($d['manualenrol'], $d['teacher']->id, $d['teacherrole']);
        $d['me_plugin']->enrol_user($d['manualenrol'], $d['student1']->id, $d['studentrole']);

        $this->insert_message('drafts', $d['teacher']->id, $d['student1']->id, $d['course']->id);
        $this->insert_message('drafts', $d['student1']->id, $d['teacher']->id, $d['course']->id);

        $this->insert_signature($d['teacher']->id);

        // Test course context.
        $context = $d['coursecontext'];
        $component = 'block_clampmail';
        $userlist = new \core_privacy\local\request\userlist($context, $component);
        $d['provider']::get_users_in_context($userlist);
        $users = $userlist->get_userids();
        $this->assertCount(2, $users);
        $this->assertContainsEquals($d['teacher']->id, $users);
        $this->assertContainsEquals($d['student1']->id, $users);

        // Test user context.
        $context = \context_user::instance($d['teacher']->id);
        $component = 'block_clampmail';
        $userlist = new \core_privacy\local\request\userlist($context, $component);
        $d['provider']::get_users_in_context($userlist);
        $users = $userlist->get_userids();
        $this->assertCount(1, $users);
        $this->assertContainsEquals($d['teacher']->id, $users);
    }

    /**
     * Test that a user who is enrolled in a course, but who has never
     * used CLAMPMail will not have any link to that context.
     */
    public function test_user_with_no_data() {
        $d = $this->data;

        $d['me_plugin']->enrol_user($d['manualenrol'], $d['teacher']->id, $d['teacherrole']);

        // Test that no contexts were retrieved.
        $contextlist = $this->get_contexts_for_userid($d['teacher']->id, 'block_clampmail');
        $contexts = $contextlist->get_contextids();
        $this->assertCount(0, $contexts);

        // Attempting to export data for this context should return nothing either.
        $this->export_all_data_for_user($d['teacher']->id, 'block_clampmail');
        $writer = \core_privacy\local\request\writer::with_context($d['coursecontext']);
        // The provider should always export data for any context explicitly asked of it.
        // But there should be no metadata, files, or discussions.
        $this->assertEmpty($writer->get_data([get_string('pluginname', 'block_clampmail')]));
        $this->assertEmpty($writer->get_all_metadata([]));
        $this->assertEmpty($writer->get_files([]));

        // And there should be nothing in the user context either.
        $usercontext = \context_user::instance($d['teacher']->id);
        $writer = \core_privacy\local\request\writer::with_context($usercontext);
        $this->assertEmpty($writer->get_data([get_string('pluginname', 'block_clampmail')]));
        $this->assertEmpty($writer->get_all_metadata([get_string('pluginname', 'block_clampmail')]));
        $this->assertEmpty($writer->get_files([get_string('pluginname', 'block_clampmail')]));
    }

    /**
     * Test exporting data for a user who has sent messages, drafts, and a
     * signature.
     */
    public function test_export_user_data() {
        $d = $this->data;

        $d['me_plugin']->enrol_user($d['manualenrol'], $d['teacher']->id, $d['teacherrole']);

        $this->insert_message('log', $d['teacher']->id, $d['student1']->id, $d['course']->id);
        $this->insert_message('drafts', $d['teacher']->id, $d['student1']->id, $d['course']->id);
        $this->insert_signature($d['teacher']->id);

        // Test that correct contexts were retrieved (one course, one user).
        $contextlist = $this->get_contexts_for_userid($d['teacher']->id, 'block_clampmail');
        $contexts = $contextlist->get_contextids();

        $usercontext = \context_user::instance($d['teacher']->id);

        $this->assertCount(2, $contexts);
        $this->assertContainsEquals($usercontext->id, $contexts);
        $this->assertContainsEquals($d['coursecontext']->id, $contexts);

        // Testing data export.
        $this->export_all_data_for_user($d['teacher']->id, 'block_clampmail');

        // In course context...
        $writer = \core_privacy\local\request\writer::with_context($d['coursecontext']);
        $this->assertNotEmpty($writer->get_data([get_string('pluginname', 'block_clampmail')]));
        $this->assertNotEmpty($writer->get_all_metadata([get_string('pluginname', 'block_clampmail')]));

        // In user context...
        $writer = \core_privacy\local\request\writer::with_context($usercontext);
        $this->assertNotEmpty($writer->get_data([get_string('pluginname', 'block_clampmail')]));
        $this->assertNotEmpty($writer->get_all_metadata([get_string('pluginname', 'block_clampmail')]));
    }

    /**
     * Test deleting all data for a given user.
     */
    public function test_delete_user_data() {
        $d = $this->data;

        $d['me_plugin']->enrol_user($d['manualenrol'], $d['teacher']->id, $d['teacherrole']);

        $usercontext = \context_user::instance($d['teacher']->id);

        $this->insert_message('log', $d['teacher']->id, $d['student1']->id, $d['course']->id);
        $this->insert_message('drafts', $d['teacher']->id, $d['student1']->id, $d['course']->id);
        $this->insert_signature($d['teacher']->id);

        $this->delete_data_for_user($d['teacher']->id);

        $this->export_all_data_for_user($d['teacher']->id, 'block_clampmail');

        // In course context...
        $writer = \core_privacy\local\request\writer::with_context($d['coursecontext']);
        $this->assertEmpty($writer->get_data([get_string('pluginname', 'block_clampmail')]));
        $this->assertEmpty($writer->get_all_metadata([get_string('pluginname', 'block_clampmail')]));

        // In user context...
        $writer = \core_privacy\local\request\writer::with_context($usercontext);
        $this->assertEmpty($writer->get_data([get_string('pluginname', 'block_clampmail')]));
        $this->assertEmpty($writer->get_all_metadata([get_string('pluginname', 'block_clampmail')]));
    }

    /**
     * Test deleting all data for a given context.
     */
    public function test_delete_all_data() {
        $d = $this->data;

        $d['me_plugin']->enrol_user($d['manualenrol'], $d['student1']->id, $d['studentrole']);
        $d['me_plugin']->enrol_user($d['manualenrol'], $d['student2']->id, $d['studentrole']);
        $d['me_plugin']->enrol_user($d['manualenrol'], $d['teacher']->id, $d['teacherrole']);

        $this->insert_message('log', $d['teacher']->id, $d['student1']->id, $d['course']->id);
        $this->insert_message('drafts', $d['teacher']->id, $d['student1']->id, $d['course']->id);
        $this->insert_message('log', $d['student1']->id, $d['student2']->id, $d['course']->id);
        $this->insert_message('drafts', $d['student1']->id, $d['student2']->id, $d['course']->id);
        $this->insert_message('log', $d['student2']->id, $d['teacher']->id, $d['course']->id);
        $this->insert_message('drafts', $d['student2']->id, $d['teacher']->id, $d['course']->id);
        $this->insert_signature($d['teacher']->id);
        $this->insert_signature($d['student1']->id);
        $this->insert_signature($d['student2']->id);

        $this->delete_data_for_context($d['coursecontext']);

        $this->export_all_data_for_user($d['teacher']->id, 'block_clampmail');
        $writer = \core_privacy\local\request\writer::with_context($d['coursecontext']);
        $this->assertEmpty($writer->get_data([get_string('pluginname', 'block_clampmail')]));
        $this->assertEmpty($writer->get_all_metadata([get_string('pluginname', 'block_clampmail')]));
        $ucontext = \context_user::instance($d['teacher']->id);
        $uwriter = \core_privacy\local\request\writer::with_context($ucontext);
        $this->assertNotEmpty($uwriter->get_data([get_string('pluginname', 'block_clampmail')]));

        $this->export_all_data_for_user($d['student1']->id, 'block_clampmail');
        $writer = \core_privacy\local\request\writer::with_context($d['coursecontext']);
        $this->assertEmpty($writer->get_data([get_string('pluginname', 'block_clampmail')]));
        $this->assertEmpty($writer->get_all_metadata([get_string('pluginname', 'block_clampmail')]));
        $ucontext = \context_user::instance($d['student1']->id);
        $uwriter = \core_privacy\local\request\writer::with_context($ucontext);
        $this->assertNotEmpty($uwriter->get_data([get_string('pluginname', 'block_clampmail')]));

        $this->export_all_data_for_user($d['student2']->id, 'block_clampmail');
        $writer = \core_privacy\local\request\writer::with_context($d['coursecontext']);
        $this->assertEmpty($writer->get_data([get_string('pluginname', 'block_clampmail')]));
        $this->assertEmpty($writer->get_all_metadata([get_string('pluginname', 'block_clampmail')]));
        $ucontext = \context_user::instance($d['student2']->id);
        $uwriter = \core_privacy\local\request\writer::with_context($ucontext);
        $this->assertNotEmpty($uwriter->get_data([get_string('pluginname', 'block_clampmail')]));
    }

    /**
     * Test deleting data for context, limited to certain users.
     */
    public function test_delete_data_for_users() {
        if (!interface_exists('\core_privacy\local\request\core_userlist_provider')) {
            return;
        }

        $d = $this->data;

        $d['me_plugin']->enrol_user($d['manualenrol'], $d['student1']->id, $d['studentrole']);
        $d['me_plugin']->enrol_user($d['manualenrol'], $d['student2']->id, $d['studentrole']);
        $d['me_plugin']->enrol_user($d['manualenrol'], $d['teacher']->id, $d['teacherrole']);

        $this->insert_message('log', $d['teacher']->id, $d['student1']->id, $d['course']->id);
        $this->insert_message('drafts', $d['teacher']->id, $d['student1']->id, $d['course']->id);
        $this->insert_message('log', $d['student1']->id, $d['student2']->id, $d['course']->id);
        $this->insert_message('drafts', $d['student1']->id, $d['student2']->id, $d['course']->id);
        $this->insert_message('log', $d['student2']->id, $d['teacher']->id, $d['course']->id);
        $this->insert_message('drafts', $d['student2']->id, $d['teacher']->id, $d['course']->id);
        $this->insert_signature($d['teacher']->id);
        $this->insert_signature($d['student1']->id);
        $this->insert_signature($d['student2']->id);

        // Course context.
        $context = $d['coursecontext'];
        $component = 'block_clampmail';
        $userlist = new \core_privacy\local\request\approved_userlist($context, $component, [$d['teacher']->id, $d['student2']->id]);

        $d['provider']->delete_data_for_users($userlist);

        $userlist = new \core_privacy\local\request\userlist($context, $component);
        $d['provider']::get_users_in_context($userlist);
        $users = $userlist->get_userids();
        $this->assertCount(1, $users);
        $this->assertContainsEquals($d['student1']->id, $users);

        // User context.
        $context = \context_user::instance($d['student1']->id);
        $userlist = new \core_privacy\local\request\approved_userlist($context, $component, [$d['student1']->id]);

        $d['provider']->delete_data_for_users($userlist);

        $userlist = new \core_privacy\local\request\userlist($context, $component);
        $d['provider']::get_users_in_context($userlist);
        $users = $userlist->get_userids();
        $this->assertCount(0, $users);
    }
}
