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
 * This file contains the class for backup of this submission plugin
 *
 * @package assignsubmission_pdf
 * @copyright 2012 Davo Smith
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Provides the information to backup submission files
 *
 * This just adds its filearea to the annotations and records the number of files
 *
 * @package assignsubmission_pdf
 * @copyright 2012 Davo Smith
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class backup_assignsubmission_pdf_subplugin extends backup_subplugin {

    /**
     *
     * Returns the subplugin information to attach to submission element
     * @return backup_subplugin_element
     */
    protected function define_submission_subplugin_structure() {

        // Create XML elements.
        $subplugin = $this->get_subplugin_element(); // Virtual optigroup element.
        $subpluginwrapper = new backup_nested_element($this->get_recommended_name());
        $subpluginelement = new backup_nested_element('submission_pdf', null,
                                                      array('numpages', 'submission', 'status', 'templatedata'));

        // Backup comments / annotations here, rather than in the 'feedback' backup.
        $comments = new backup_nested_element('comments');
        $comment = new backup_nested_element('comment', null,
                                             array('submissionid', 'posx', 'posy', 'width', 'rawtext', 'pageno', 'colour'));

        $annotations = new backup_nested_element('annotations');
        $annotation = new backup_nested_element('annotation', null,
                                                array('submissionid', 'pageno', 'startx', 'starty', 'endx', 'endy',
                                                     'path', 'colour', 'type'));

        // Connect XML elements into the tree.
        $subplugin->add_child($subpluginwrapper);
        $subpluginwrapper->add_child($subpluginelement);
        $subpluginelement->add_child($comments);
        $comments->add_child($comment);
        $subpluginelement->add_child($annotations);
        $annotations->add_child($annotation);

        // Set source to populate the data.
        $subpluginelement->set_source_table('assignsubmission_pdf', array('submission' => backup::VAR_PARENTID));
        $comment->set_source_table('assignfeedback_pdf_cmnt', array('submissionid' => backup::VAR_PARENTID));
        $annotation->set_source_table('assignfeedback_pdf_annot', array('submissionid' => backup::VAR_PARENTID));

        $subpluginelement->annotate_files('assignsubmission_pdf', 'submission_pdf_draft', 'submission');
        $subpluginelement->annotate_files('assignsubmission_pdf', 'submission_pdf_final', 'submission');
        $subpluginelement->annotate_files('assignsubmission_pdf', 'submission_pdf_coversheet', null);
        $subpluginelement->annotate_files('assignfeedback_pdf', 'feedback_pdf_response', 'submission');
        return $subplugin;
    }
}
