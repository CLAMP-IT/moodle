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
 * Access information for the blocks_turningtech plugin.
 *
 * @package    block_turningtech
 * @copyright  Turning Technologies
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
$capabilities = array(
    'block/turningtech:myaddinstance' => array(
                'riskbitmask' => RISK_XSS,
                'captype' => 'write',
                'contextlevel' => CONTEXT_BLOCK,
                'archetypes' => array(
                        'user'          => CAP_PROHIBIT
                ),
        'clonepermissionsfrom' => 'moodle/my:manageblocks'
        ),
        'block/turningtech:addinstance' => array(
                'riskbitmask' => RISK_XSS,
                'captype' => 'write',
                'contextlevel' => CONTEXT_BLOCK,
                'archetypes' => array(
                        'guest'          => CAP_PROHIBIT,
                        'student'        => CAP_PROHIBIT,
                        'teacher'        => CAP_PROHIBIT,
                        'editingteacher' => CAP_PROHIBIT,
                        'coursecreator'  => CAP_PROHIBIT,
                        'manager'        => CAP_PROHIBIT
                ),
            'clonepermissionsfrom' => 'moodle/site:manageblocks'
        )
);