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
 * Find PDF files task definition for local_accessibility_filescan
 *
 * @package   local_accessibility_filescan
 * @copyright 2020 Swarthmore College
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_accessibility_filescan\task;

/**
 * Scheduled task to find unscanned PDF files.
 *
 * @package   local_accessibility_filescan
 * @copyright 2020 Swarthmore College
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class find_pdf_files extends \core\task\scheduled_task {
    /**
     * Get the name of the task.
     *
     * @return string the name of the task
     */
    public function get_name() {
        return get_string('pdf:find_files_task', 'local_accessibility_filescan');
    }

    /**
     * Find unscanned PDF files in the Moodle file system.
     * @throws \dml_exception
     */
    public function execute() {

        // Set the timeout in seconds.
        $timeout = 10;
        $limit = 100;

        // Get the unscanned PDF files.
        $files = \local_accessibility_filescan\pdf::get_unqueued_files($limit);

        // Only process if there are files to process.
        if (count($files) > 0) {
            foreach ($files as $file) {
                \local_accessibility_filescan\pdf::put_file_in_queue($file);
            }
        }
    }
}
