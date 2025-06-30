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
 * local_accessibility_filescan unit tests
 *
 * @package   local_accessibility_filescan
 * @copyright 2020 Swarthmore College
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * File discovery unit test.
 *
 * @package   local_accessibility_filescan
 * @copyright 2020 Swarthmore College
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class file_discovery_test extends advanced_testcase {
    /** @var \local_accessibility_filescan\pdf The pdf helper object */
    public $pdfhelper;

    /** @var \local\a11y_check\task\find_pdf_files The task object */
    public $task;

    /** @var \stdClass the test course object */
    public $course;

    /** @var array the generated pdfs */
    public $filesadded;

    public function test_file_discovery() {

        /*
         * This test will fail if $this->add_pdfs() is called with a value greater
         * than the plugins max cron limit size.
         */

        $this->resetAfterTest(true);
        $this->pdfhelper = new \local_accessibility_filescan\pdf();
        $this->task = new \local_accessibility_filescan\task\find_pdf_files();
        $this->course = $this->getDataGenerator()->create_course(['shortname' => 'testcourse']);
        $this->page = $this->getDataGenerator()->create_module('page', ['course' => $this->course->id]);

        // Add files that should be excluded from the scan.
        $this->add_garbage_files();

        // Add 5 pdfs.
        $this->add_pdfs(5);

        // There should be 5 unscanned files.
        $this->assert_unscanned_files_count(5);

        // There should be 0 records created.
        $this->assert_custom_record_count(0);

        // Execute the task.
        $this->task->execute();

        // After the task is run, there should be 0 unscanned files.
        $this->assert_unscanned_files_count(0);

        // After the task has run, there should be 5 records created.
        $this->assert_custom_record_count(5);

        // Add 4 pdfs.
        $this->add_pdfs(4);

        // There should now be 7 unscanned files.
        $this->assert_unscanned_files_count(4);

        // Sanity check - There should still be 5 records created.
        $this->assert_custom_record_count(5);

        // Execute the task again.
        $this->task->execute();

        // After the task executes, there should be 0 unscanned files.
        $this->assert_unscanned_files_count(0);

        // There should be 12 records total.
        $this->assert_custom_record_count(9);
    }

    /**
     * Create PDFs which should be excluded from the scan.
     */
    protected function add_garbage_files() {
        $fs = get_file_storage();

        $garbage = [
            (object) [
                'contextid' => context_system::instance()->id,
            ],
            (object) [
                'contextid' => context_user::instance(1)->id,
            ],
            (object) [
                'contextid' => context_coursecat::instance(1)->id,
            ],
            (object) [
                'contextid' => context_course::instance($this->course->id)->id,
            ],
            (object) [
                'mimetype'  => 'application/json',
            ],
            (object) [
                'component'  => 'assignfeedback_editpdf',
            ],
            (object) [
                'filearea'  => 'stamps',
            ],
        ];

        foreach ($garbage as $i => $record) {
            $uniquetext = (string) time() . (string) $this->filesadded;
            $record->contextid = $record->contextid ?? context_module::instance($this->page->cmid)->id;
            $record->component = $record->component ?? 'local_accessibility_filescan';
            $record->filearea = $record->filearea ?? 'local_accessibility_filescan_test_files';
            $record->itemid = $record->itemid ?? $i;
            $record->filepath = $record->filepath ?? '/';
            $record->filename = $record->filename ?? 'local_accessibility_filescan_test_file_' . $uniquetext . '.pdf';
            $record->mimetype = $record->mimetype ?? 'application/pdf';
            $fs->create_file_from_string($record, $uniquetext);
            $this->filesadded++;
        }

        $record = (object) [
            'contextid' => context_module::instance($this->page->cmid)->id,
            'component' => 'local_accessibility_filescan',
            'filearea' => 'local_accessibility_filescan_test_files',
            'itemid' => $i,
            'filepath' => '/',
            'filename' => 'local_accessibility_filescan_test_file_empty.pdf',
            'mimetype' => 'application/pdf',
        ];
        $fs->create_file_from_string($record, '');
        $this->filesadded++;
    }

    /**
     * Create the requested number of pdfs.
     * @param int $count the number of pdfs to create.
     */
    protected function add_pdfs($count) {
        $fs = get_file_storage();

        for ($i = 0; $i < $count; $i++) {
            $uniquetext = (string) time() . (string) $this->filesadded . (string) $i;
            $record = (object) [
                'contextid' => context_module::instance($this->page->cmid)->id,
                'component' => 'local_accessibility_filescan',
                'filearea' => 'local_accessibility_filescan_test_files',
                'itemid' => $i,
                'filepath' => '/',
                'filename' => 'local_accessibility_filescan_test_file_' . $uniquetext . '.pdf',
                'mimetype' => 'application/pdf',
            ];
            $fs->create_file_from_string($record, $uniquetext);
            $this->filesadded++;
        }
    }

    /**
     * Wrapper for assertEquals against the local_accessibility_filescan table.
     * @param int $count the expected number of records.
     *
     * @return boolean
     */
    protected function assert_custom_record_count($count) {
        global $DB;
        $records = $DB->count_records('local_accessibility_filescan');
        return $this->assertEquals($count, $records);
    }

    /**
     * Wrapper for assertEquals against get_unscanned_pdf_files.
     * @param int $count the expected number of records.
     *
     * @return boolean
     */
    protected function assert_unscanned_files_count($count) {
        $queryresults = $this->pdfhelper->get_unscanned_files();
        return $this->assertEquals($count, count($queryresults));
    }
}
