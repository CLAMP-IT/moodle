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
 * Poodll Notification
 * This responds to notifications from Poodll Cloud that a transcoding has completed and calls the relevant adhoc task
 * to fetch back the file
 * @package    filter
 * @subpackage poodll
 * @copyright  2017 onwards Justin Hunt  https://poodll.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


if (defined('STDIN')) {
    fwrite(STDERR, "ERROR: This script doesn't support CLI, please use /filter/poodll/cli/poodllcron.php instead\n");
    exit(1);
}


require('../../config.php');
$filename=required_param('filename',PARAM_TEXT);
//require_once($CFG->dirroot . '/filter/poodll/classes/task/adhoc_s3_move.php');

// extra safety
\core\session\manager::write_close();


// send mime type and encoding
@header('Content-Type: text/plain; charset=utf-8');

// execute the cron
$taskclassname= '\filter_poodll\task\adhoc_s3_move';
$starttime=false;
$tr = new \filter_poodll\taskrunner($taskclassname,$starttime);
$tr->run_task_by_filename($filename);