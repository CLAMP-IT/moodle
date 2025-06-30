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
 * Language file for local_accessibility_filescan
 *
 * @package   local_accessibility_filescan
 * @copyright 2023 Swarthmore College
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

$string['pluginname'] = 'Accessibility Filescan';

$string['clean_task'] = 'Clean local_accessibility_filescan tables';

$string['pdf:find_files_task'] = 'Find unscanned PDF files';
$string['pdf:scan_files_task'] = 'Scan PDF files';

$string['settings:files_per_cron'] = 'Batch size';
$string['settings:files_per_cron_desc'] = 'Maximum number of files to scan per cron job';

$string['settings:max_file_size_mb'] = 'Maximum file size';
$string['settings:max_file_size_mb_desc'] = 'The max file size to scan, in megabytes; larger files will be ignored';

$string['settings:max_retries'] = 'Maximum retries';
$string['settings:max_retries_desc'] = 'Maximum number of times to try scanning a file before giving up';
