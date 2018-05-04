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
 * Plugin capabilities
 *
 * @package    atto_poodll
 * @copyright  2013 Justin Hunt {@link http://www.poodll.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$capabilities = array(

    'atto/poodll:visible' => array(
        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes' => array(
			'coursecreator' => CAP_ALLOW,
			'teacher' => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
			'student' => CAP_ALLOW,
            'manager' => CAP_ALLOW
        )
    ),
    'atto/poodll:allowaudiomp3' => array(
        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes' => array(
			'coursecreator' => CAP_ALLOW,
			'teacher' => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
			'student' => CAP_ALLOW,
            'manager' => CAP_ALLOW
        )
    ),

    'atto/poodll:allowvideo' => array(
        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes' => array(
			'coursecreator' => CAP_ALLOW,
			'teacher' => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
			'student' => CAP_ALLOW,
            'manager' => CAP_ALLOW
        )
    ),
    'atto/poodll:allowwhiteboard' => array(
        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes' => array(
			'coursecreator' => CAP_ALLOW,
			'teacher' => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
			'student' => CAP_ALLOW,
            'manager' => CAP_ALLOW
        )
    ),
    'atto/poodll:allowsnapshot' => array(
        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes' => array(
			'coursecreator' => CAP_ALLOW,
			'teacher' => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
			'student' => CAP_ALLOW,
            'manager' => CAP_ALLOW
        )
    ),
    'atto/poodll:allowwidgets' => array(
        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes' => array(
			'coursecreator' => CAP_ALLOW,
			'teacher' => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
			'student' => CAP_ALLOW,
            'manager' => CAP_ALLOW
        )
    )
);

