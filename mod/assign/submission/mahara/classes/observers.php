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
 * assign_submission_mahara events subscription
 *
 * @package    assignsubmission_mahara
 * @copyright  2015 Lancaster University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot . '/mod/assign/submission/mahara/lib.php');

/**
 * Event handler for assign_submission_mahara plugin.
 */
class assignsubmission_mahara_observers {

   /**
    * Process assignment grading function called by event trigger (see db/events.php).
    * It unlocks pages on Mahara when submission has been graded.
    *
    * @param object $event Event data object passed over by mod_assign
    * @return void
    */
    public static function submission_graded(\mod_assign\event\submission_graded $event) {
        global $DB;
        $eventdata = $event->get_data();
        $grade = $event->get_record_snapshot('assign_grades', $eventdata['objectid']);
        $assign = $event->get_assign();
        $maharasubmissionplugin = $assign->get_submission_plugin_by_type('mahara');

        // See if need to unlock anything at all.
        if ((int)$maharasubmissionplugin->get_config('lock') !== ASSIGNSUBMISSION_MAHARA_SETTING_UNLOCK) {
            return;
        }

        // Get submission if it exists.
        if (!$submission = $assign->get_user_submission($grade->userid, false)) {
            return;
        }

        // Get Mahara submission.
        $maharasubmission = $DB->get_record('assignsubmission_mahara', array('submission'=>$submission->id));

        // Process further only if we are dealing with mahara submission that is locked.
        if ($maharasubmission && $maharasubmission->viewstatus == assign_submission_mahara::STATUS_SUBMITTED) {
            // Check if marking workflow is in place, page unlocking will be handled in
            // assignsubmission_mahara_observers::workflow_state_updated unless .
            if ($assign->get_instance()->markingworkflow) {
                return;
            }

            // If submission has been "graded" but no grade is selected do not unlock page.
            if ($grade->grade == -1 || $grade->grade === null) {
                return;
            }

            self::release_submitted_view($maharasubmissionplugin, $maharasubmission);
        }
    }

   /**
    * Process workflow state update called by event trigger (see db/events.php).
    * It unlocks pages on Mahara when grades are released to students.
    *
    * @param object $event Event data object passed over by mod_assign
    * @return void
    */
    public static function workflow_state_updated(\mod_assign\event\workflow_state_updated $event) {
        global $DB;
        $eventdata = $event->get_data();
        $assign = $event->get_assign();
        $maharasubmissionplugin = $assign->get_submission_plugin_by_type('mahara');

        // See if need to unlock anything at all.
        if ((int)$maharasubmissionplugin->get_config('lock') !== ASSIGNSUBMISSION_MAHARA_SETTING_UNLOCK) {
            return;
        }

        // Get submission if it exists.
        if (!$submission = $assign->get_user_submission($eventdata['relateduserid'], false)) {
            return;
        }

        // Get Mahara submission.
        $maharasubmission = $DB->get_record('assignsubmission_mahara', array('submission'=>$submission->id));

        // Process further only if we are dealing with mahara submission that is locked.
        if ($maharasubmission && $maharasubmission->viewstatus == assign_submission_mahara::STATUS_SUBMITTED) {
            // Check marking workflow state, only unlock page if marks are released.
            if ($eventdata['other']['newstate'] !== ASSIGN_MARKING_WORKFLOW_STATE_RELEASED) {
                return;
            }

            self::release_submitted_view($maharasubmissionplugin, $maharasubmission);
        }
    }

   /**
    * Process unlocking Mahara page.
    *
    * @param \assign_submission_mahara $maharasubmissionplugin
    * @param object $maharasubmission Mahara submission data object.
    * @return void
    */
    protected static function release_submitted_view($maharasubmissionplugin, $maharasubmission) {
        // Relese submitted page, but provide no outcomes.
        $maharasubmissionplugin->mnet_release_submitted_view(
            $maharasubmission->viewid,
            array(),
            $maharasubmission->iscollection
        );

        if ($maharasubmissionplugin->get_error()) {
            $this->set_error($maharasubmissionplugin->get_error());
        } else {
            $maharasubmissionplugin->set_mahara_submission_status($maharasubmission->submission, assign_submission_mahara::STATUS_RELEASED);
        }
    }
}