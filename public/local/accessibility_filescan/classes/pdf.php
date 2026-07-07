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
 * PDF helper functions local_accessibility_filescan
 *
 * @package   local_accessibility_filescan
 * @copyright 2023 Swarthmore College
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_accessibility_filescan;

use Exception;
use Throwable;

defined('MOODLE_INTERNAL') || die();

require_once(dirname(__FILE__) . '/../locallib.php');

/**
 * PDF helper functions
 */
class pdf {
    /**
     * Remove all rows that don't have a record in mdl_files (draft context is ignored).
     * @return void
     * @throws \dml_exception
     */
    public static function cleanup_orphaned_records(): void {
        global $DB;

        $sql = 'select lacp.scanid as "scanid", lacp.fileid as "fileid", lacp.courseid as "courseid" ' .
            'from {local_a11y_filescan_queue} lacq ' .
            'inner join {local_a11y_filescan_pivot} lacp on lacq.id = lacp.scanid ' .
            'left outer join {files} f on f.id = lacp.fileid ' .
            'where f.id is null';

        $records = $DB->get_records_sql($sql);

        mtrace('Found ' . count($records) . ' orphaned records');

        foreach ($records as $record) {
            // Remove record from the PDF results table.
            $DB->delete_records('local_a11y_filescan_type_pdf', ['scanid' => $record->scanid]);
            // Remove record from the queue table.
            $DB->delete_records('local_a11y_filescan_queue', ['id' => $record->scanid]);
            // Remove record from the pivot table.
            $DB->delete_records(
                'local_a11y_filescan_pivot',
                ['scanid' => $record->scanid, 'fileid' => $record->fileid, 'courseid' => $record->courseid]
            );
        }
    }

    /**
     * Find all PDF files across Moodle instance that do not have a record in the queue.
     * @param int $limit - Limit on how many records to return.
     * @return array
     * @throws \dml_exception
     */
    public static function get_unqueued_files($limit = 1): array {
        global $DB;

        // These are the components the scanner will look for files in.
        $components = [
            "course",
            "block_html",
            "mod_assign",
            "mod_book",
            "mod_data",
            "mod_folder",
            "mod_forum",
            "mod_glossary",
            "mod_label",
            "mod_lesson",
            "mod_page",
            "mod_publication",
            "mod_questionnaire",
            "mod_quiz",
            "mod_resource",
            "mod_scorm",
            "mod_url",
            "mod_workshop",
            "qtype_essay",
            "question",
        ];

        // Create the IN part of the statement, along with its params.
        [$insql, $inparams] = $DB->get_in_or_equal($components);

        $sql = 'select f.id as "fileid", f.filesize as "filesize", f.filename as "filename", ' .
        'c.id as "courseid", c.shortname as "courseshortname", c.fullname as "coursefullname" ' .
        'from {files} f ' .
        'inner join {context} ctx on ctx.id = f.contextid ' .
        'inner join {course_modules} cm on cm.id = ctx.instanceid ' .
        'inner join {course} c on c.id = cm.course ' .
        'left outer join {local_a11y_filescan_pivot} lacp on lacp.fileid = f.id and lacp.courseid = c.id ' .
        'left outer join {local_a11y_filescan_queue} lacq on lacq.id = lacp.scanid ' .
        'where ctx.contextlevel = 70 ' .
        'and lacq.id is null ' .
        "and f.mimetype = 'application/pdf' " .
        "and f.component $insql " .
        'order by f.timemodified desc ' .
        'limit ' . $limit;

        $files = [];
        $recordset = $DB->get_recordset_sql($sql, $inparams);

        if ($recordset->valid()) {
            foreach ($recordset as $record) {
                $files[] = $record;
            }
        }

        mtrace('Found ' . count($files) . ' PDFs');

        $recordset->close();
        return $files;
    }

    /**
     * Return the ids of PDF files (within $limit) in the queue, but have not been scanned for accessibility.
     * @param int $limit - Limit on how many records to return.
     * @return array
     * @throws \dml_exception
     */
    public static function get_unscanned_files($limit = 1): array {
        global $DB;

        $sql = 'select lacp.fileid as "fileid", lacq.id as "scanid", f.filesize as "filesize", f.filename as "filename" ' .
            'from {local_a11y_filescan_queue} lacq ' .
            'inner join {local_a11y_filescan_pivot} lacp on lacp.scanid = lacq.id ' .
            'inner join {files} f on f.id = lacp.fileid ' .
            'where lacq.status = 0  ' .
            "and f.mimetype = 'application/pdf' " .
            'limit ' . $limit;

        $files = [];
        $recordset = $DB->get_recordset_sql($sql);
        foreach ($recordset as $record) {
            $files[] = $record;
        }
        $recordset->close();
        return $files;
    }

    /**
     * Create a queue record a single PDF.
     * @param mixed $file The file object. (Must have filesize, courseid, and fileid).
     * @return void
     * @throws \dml_exception
     */
    public static function put_file_in_queue($file): void {
        global $DB;

        // Check if the file exceeds the max filesize set in the config.
        $maxfilesize = (int) get_config('local_accessibility_filescan', 'max_file_size_mb');
        $canprocess = (bool) ($file->filesize / pow(1024, 2)) <= $maxfilesize;

        $now = time();

        // Insert the record into the queue table.
        $scanid = $DB->insert_record('local_a11y_filescan_queue', [
            'checktype' => LOCAL_ACCESSIBILITY_FILESCAN_TYPE_PDF,
            'faildelay' => $now + 120,
            'lastchecked' => $now,
            'status' => $canprocess ? LOCAL_ACCESSIBILITY_FILESCAN_STATUS_UNCHECKED : LOCAL_ACCESSIBILITY_FILESCAN_STATUS_IGNORE,
            'statustext' => $canprocess ? null : 'File exceeds max filesize',
        ]);

        // Insert the record into the pivot table.
        $DB->execute('INSERT INTO {local_a11y_filescan_pivot} (courseid, scanid, fileid) VALUES (?,?,?)', [
            $file->courseid,
            $scanid,
            $file->fileid,
        ]);
    }

    /**
     * Scan queued files (at random) and returns its accessibility results.
     * @param int $limit - Limit the amount of files scanned.
     * @return void
     * @throws \dml_exception
     */
    public static function scan_queued_files($limit = 1): void {

        global $DB;

        $files = self::get_unscanned_files($limit);

        if (count($files) == 0) {
            mtrace('No files found');
            return;
        } else {
            mtrace('Found ' . count($files) . ' files to scan');
        }

        mtrace("Scanning " . count($files) . " PDF files for accessibility issues.");

        // User setting to not scan giant PDFs.
        $maxfilesize = (int) get_config('local_accessibility_filescan', 'max_file_size_mb');

        foreach ($files as $file) {
            try {
                mtrace("Scanning $file->fileid");

                // Check and see if the file exceeds max filesize.
                // Get the filesize.
                $filesizebytes = $file->filesize;
                $filesizemb = $filesizebytes / pow(1024, 2);

                if ($filesizemb > $maxfilesize) {
                    mtrace('File ' . $file->filename . ' is too large to scan');

                    // Make sure the file doesn't get scanned again.
                    $msg = "File is larger than" . round($maxfilesize) . "MB.";

                    // Update the status. 5 = do not scan again.
                    $DB->update_record('local_a11y_filescan_queue', (object) [
                    'id' => $file->scanid,
                    'status' => 5,
                    'statusText' => $msg,
                    'lastchecked' => time(),
                    ]);

                    // No need to proces rest of iteration.
                    continue;
                }

                // Create a tmp file handle to scan.
                $tmpfile = self::create_tmp_file($file->fileid);
                $results = \local_accessibility_filescan\pdf_scanner::scan($tmpfile);
                $record = [
                    'scanid' => $file->scanid,
                    'hastext' => $results->hastext,
                    'hastitle' => $results->hastitle,
                    'haslanguage' => $results->haslanguage,
                    'istagged' => $results->istagged,
                    'pagecount' => $results->pagecount,
                ];
                // Create the results record that will be inserted into the PDF results table.
                // Insert the results into the PDF results table.
                $DB->insert_record('local_a11y_filescan_type_pdf', $record);
                // Update the scan status in the queue table.
                $DB->update_record('local_a11y_filescan_queue', (object) [
                    'id' => $file->scanid,
                    'status' => 1,
                    'lastchecked' => time(),
                ]);
                // Make sure to delete tmpfile!
                unlink($tmpfile);
            } catch (Exception | Throwable $e) {
                // If there's an error, get the message and update the queue table.
                $DB->update_record('local_a11y_filescan_queue', (object) [
                    'id' => $file->scanid,
                    'status' => 4,
                    'statustext' => $e->getMessage(),
                    'lastchecked' => time(),
                ]);
            }
        }
    }

    /**
     * Create a tmp file in the Moodle temp file storage area and return the path.
     * @param int $fileid - The id of the file in Moodle database.
     * @return string
     */
    private static function create_tmp_file(int $fileid): string {
        global $CFG;
        $fs = get_file_storage();
        $file = $fs->get_file_by_id($fileid);
        $content = $file->get_content();
        // Copy the file to a temp directory so that it can be scanned.
        $tmpfile = $CFG->dataroot . '/temp/filestorage/' . $file->get_pathnamehash() . '.pdf';
        file_put_contents($tmpfile, $content);
        return $tmpfile;
    }
}
