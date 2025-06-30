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
//
/**
 * Version information for block_accessibility_filescan
 *
 * @package   block_accessibility_filescan
 * @copyright 2023 Swarthmore College
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$capabilities = [

  'block/accessibility_filescan:addinstance' => [
    'riskbitmask' => RISK_SPAM | RISK_XSS,
    'captype' => 'write',
    'contextlevel' => CONTEXT_BLOCK,
    'archetypes' => [
      'editingteacher' => CAP_ALLOW,
      'manager' => CAP_ALLOW,
      'coursecreator' => CAP_ALLOW,
    ],
    'clonepermissionsfrom' => 'moodle/site:manageblocks',
  ],

  'block/accessibility_filescan:myaddinstance' => [
    'captype' => 'write',
    'contextlevel' => CONTEXT_SYSTEM,
    'archetypes' => [
      'user' => CAP_PREVENT,
    ],
  ],

  'block/accessibility_filescan:view' => [
    'captype' => 'read',
    'contextlevel' => CONTEXT_COURSE,
    'legacy' => [
      'guest' => CAP_PREVENT,
      'student' => CAP_PREVENT,
      'teacher' => CAP_ALLOW,
      'editingteacher' => CAP_ALLOW,
      'coursecreator' => CAP_ALLOW,
      'manager' => CAP_ALLOW,
    ],
  ],

];
