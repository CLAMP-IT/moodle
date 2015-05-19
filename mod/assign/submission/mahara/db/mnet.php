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
 * MNet publishers/subscribers definition.
 *
 * @package    assignsubmission_mahara
 * @copyright  2013 Lancaster University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$publishes = array(
    'assign_submission_mahara' => array(
        'apiversion' => 1,
        'classname'  => 'mnetservice_assign_submission_mahara',
        'filename'   => 'mnetlib.php',
        'methods'    => array(
            'donothing',
        ),
    ),
);

$subscribes = array(
    'assign_submission_mahara' => array(
        'get_views_for_user' => 'mod/mahara/rpclib.php/get_views_for_user',
        'submit_view_for_assignment' => 'mod/mahara/rpclib.php/submit_view_for_assessment',
        'release_submitted_view' => 'mod/mahara/rpclib.php/release_submitted_view',
    ),
);
