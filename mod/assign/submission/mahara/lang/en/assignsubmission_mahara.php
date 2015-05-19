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
 * Strings for component 'assignsubmission_mahara', language 'en'
 *
 * @package   assignsubmission_mahara
 * @copyright 2014 Lancaster University (@link http://www.lancaster.ac.uk/)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['assign_submission_mahara_name'] = 'Assign Submission Mahara services';
$string['assign_submission_mahara_description'] = 'Mahara functions used in Mahara portfolio Assign Submission plugin.<br />Publishing this service on a Moodle site has no effect. Subscribe to this service if you want to be able to use assignments with {$a}.<br />';
$string['collectionsby'] = 'Collections by {$a}';
$string['defaultlockpages'] = 'Default "{$a}"';
$string['defaultlockpages_help'] = 'Default setting to use for the "{$a}" setting in new Mahara assignments.';
$string['defaulton'] = 'Enabled by default';
$string['defaulton_help'] = 'If set, this submission method will be enabled by default for all new assignments.';
$string['defaultsite'] = 'Default "{$a}"';
$string['defaultsite_help'] = 'Default setting to use for the "{$a}" setting in new Mahara assignments.';
$string['enabled'] = 'Mahara portfolio';
$string['enabled_help'] = "If enabled, students are able to submit Mahara pages for assessment in Moodle. The Mahara site must already be configured for mnet networking with this Moodle site.";
$string['emptysubmission'] = 'You have not chosen the page to submit.';
$string['errormnetrequest'] = 'Attempt to send mnet request resulted in error: {$a}';
$string['errorinvalidhost'] = 'Invalid host id selected';
$string['errorinvalidstatus'] = 'Developer Error: Invalid submission status sent to assign_submission_mahara::set_mahara_submission_status()';
$string['errorvieworcollectionalreadysubmitted'] = 'The selected Mahara view or collection could not be submitted. Please choose another.';
$string['eventassessableuploaded'] = 'A Mahara page or collection has been submitted.';
$string['lockpages'] = 'Lock submitted pages';
$string['lockpages_help'] = 'If set, submitted Mahara pages and collections will be locked from editing in Mahara. (If you are also using the Mahara feedback plugin, they will be unlocked once the submission is graded.)';
$string['mahara'] = 'Mahara portfolio';
$string['nomaharahostsfound'] = 'No mahara hosts found.';
$string['noneselected'] = 'None selected';
$string['noviewscreated'] = 'You have no available Mahara pages or collections. Please <a target="_blank" href="{$a->jumpurl}">click here</a> to visit "{$a->name}" and create a new one.';
$string['option_collections'] = 'Collections';
$string['option_views'] = 'Views';
$string['pluginname'] = 'Mahara portfolio';
$string['previousattemptsnotvisible'] = 'Previous attempts with the Mahara submission plugin are not visible.';
$string['selectmaharaview'] = 'Select one of your available portfolio pages or collections from the list below, or <a target="_blank" href="{$a->jumpurl}">click here</a> to visit "{$a->name}" and create a new one.';
$string['site'] = 'Site';
$string['site_help'] = 'This setting lets you select which Mahara site your students should submit their pages from.';
$string['outputforlog'] = '{$a->remotehostname}: {$a->viewtitle} (view id: {$a->viewid})';
$string['outputforlognew'] = 'New {$a} submission.';
$string['viewsby'] = 'Pages by {$a}';
