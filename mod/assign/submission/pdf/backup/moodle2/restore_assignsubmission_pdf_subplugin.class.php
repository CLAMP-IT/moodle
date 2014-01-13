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
 * This file contains the class for restore of this submission plugin
 *
 * @package assignsubmission_pdf
 * @copyright 2012 Davo Smith
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * restore subplugin class that provides the necessary information needed to restore one assign_submission subplugin.
 *
 * @package assignsubmission_pdf
 * @copyright 2012 Davo Smith
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class restore_assignsubmission_pdf_subplugin extends restore_subplugin {

    /**
     * Returns the paths to be handled by the subplugin at workshop level
     * @return array
     */
    protected function define_submission_subplugin_structure() {
        $paths = array();

        $elename = $this->get_namefor('submission');
        $elepath = $this->get_pathfor('/submission_pdf'); // We used get_recommended_name() so this works.
        $paths[] = new restore_path_element($elename, $elepath);

        $elename = 'assignsubmission_pdf_comment';
        $elepath = $this->get_pathfor('/submission_pdf/comments/comment');
        $paths[] = new restore_path_element($elename, $elepath);

        $elename = 'assignsubmission_pdf_annotation';
        $elepath = $this->get_pathfor('/submission_pdf/annotations/annotation');
        $paths[] = new restore_path_element($elename, $elepath);

        return $paths; // And we return the interesting paths.
    }

    /**
     * Processes one submission_pdf element
     * @param mixed $data
     * @return void
     */
    public function process_assignsubmission_pdf_submission($data) {
        global $DB;

        $data = (object)$data;
        $data->assignment = $this->get_new_parentid('assign');
        $oldsubmissionid = $data->submission;
        // The mapping is set in the restore for the core assign activity. When a submission node is processed.
        $data->submission = $this->get_mappingid('submission', $data->submission);

        $DB->insert_record('assignsubmission_pdf', $data);

        $this->add_related_files('assignsubmission_pdf', 'submission_pdf_draft', 'submission', null, $oldsubmissionid);
        $this->add_related_files('assignsubmission_pdf', 'submission_pdf_final', 'submission', null, $oldsubmissionid);
        $this->add_related_files('assignsubmission_pdf', 'submission_pdf_coversheet', null);
        $this->add_related_files('assignfeedback_pdf', 'feedback_pdf_response', 'submission', null, $oldsubmissionid);
    }

    public function process_assignsubmission_pdf_comment($data) {
        global $DB;

        $data = (object)$data;
        // The mapping is set in the restore for the core assign activity. When a submission node is processed.
        $data->submissionid = $this->get_mappingid('submission', $data->submissionid);

        $DB->insert_record('assignfeedback_pdf_cmnt', $data);
    }

    public function process_assignsubmission_pdf_annotation($data) {
        global $DB;

        $data = (object)$data;
        // The mapping is set in the restore for the core assign activity. When a submission node is processed.
        $data->submissionid = $this->get_mappingid('submission', $data->submissionid);

        $DB->insert_record('assignfeedback_pdf_annot', $data);
    }
}
