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
 * local library functions local_accessibility_filescan
 *
 * @package   local_accessibility_filescan
 * @copyright 2023 Swarthmore College
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Types of checks.
define('LOCAL_ACCESSIBILITY_FILESCAN_TYPE_UNDEFINED', 0);
define('LOCAL_ACCESSIBILITY_FILESCAN_TYPE_PDF', 1);

// Status types for scan checks.
define('LOCAL_ACCESSIBILITY_FILESCAN_STATUS_UNCHECKED', 0);
define('LOCAL_ACCESSIBILITY_FILESCAN_STATUS_PASS', 1);
define('LOCAL_ACCESSIBILITY_FILESCAN_STATUS_CHECK', 2);
define('LOCAL_ACCESSIBILITY_FILESCAN_STATUS_FAIL', 3);
define('LOCAL_ACCESSIBILITY_FILESCAN_STATUS_ERROR', 4);

// File is intentionally skipped, either from multiple errors, oversize, or some other issue.
define('LOCAL_ACCESSIBILITY_FILESCAN_STATUS_IGNORE', 5);
