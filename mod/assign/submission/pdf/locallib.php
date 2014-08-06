<?php
// This file is part of the submit PDF plugin for Moodle - http://moodle.org/
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
 * This file contains the definition for the library class for pdf
 *  submission plugin
 *
 * This class provides all the functionality for the new assign module.
 *
 * @package   assignsubmission_pdf
 * @copyright 2012 Davo Smith
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot.'/mod/assign/submission/pdf/lib.php');
require_once($CFG->dirroot.'/mod/assign/feedback/pdf/mypdflib.php');

/*
 * library class for file submission plugin extending submission plugin
 * base class
 *
 * @package   mod_assign
 * @subpackage submission_pdf
 * @copyright 2012 Davo Smith
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class assign_submission_pdf extends assign_submission_plugin {

    /**
     * Get the name of the file submission plugin
     * @return string
     */
    public function get_name() {
        return get_string('pdf', 'assignsubmission_pdf');
    }

    /**
     * Check the ghostscript path is valid to see if the plugin should be enabled.
     * @return bool
     */
    public function is_enabled() {
        global $CFG;

        static $gspathok = null;
        if (is_null($gspathok)) {
            require_once($CFG->dirroot.'/mod/assign/feedback/pdf/mypdflib.php');
            $result = AssignPDFLib::test_gs_path(false);
            $gspathok = ($result->status == AssignPDFLib::GSPATH_OK);
            if (!$gspathok && $this->is_visible()) {
                // Setting 'gspath' is invalid, so the plugin should be globally disabled.
                set_config('disabled', true, $this->get_subtype() . '_' . $this->get_type());
            }
        }

        if (!parent::is_enabled()) {
            return false;
        }

        return $gspathok;
    }

    /**
     * Get file submission information from the database
     *
     * @global moodle_database $DB
     * @param int $submissionid
     * @return mixed
     */
    private function get_pdf_submission($submissionid) {
        global $DB;
        return $DB->get_record('assignsubmission_pdf', array('submission' => $submissionid));
    }

    /**
     * Get the default setting for file submission plugin
     * @global stdClass $CFG
     * @global stdClass $COURSE
     * @param MoodleQuickForm $mform The form to add elements to
     * @return void
     */
    public function get_settings(MoodleQuickForm $mform) {
        global $CFG, $COURSE, $DB;

        $defaultmaxfilesubmissions = $this->get_config('maxfilesubmissions');
        if ($defaultmaxfilesubmissions === false) {
            $defaultmaxfilesubmissions = get_config('assignsubmission_pdf', 'maxfilesubmissions');
        }
        $defaultmaxsubmissionsizebytes = $this->get_config('maxsubmissionsizebytes');
        if ($defaultmaxsubmissionsizebytes === false) {
            $defaultmaxsubmissionsizebytes = get_config('assignsubmission_pdf', 'maxbytes');
        }
        $defaulttemplateid = $this->get_config('templateid');
        if ($defaulttemplateid === false) {
            $defaulttemplateid = 0;
        }

        $settings = array();
        $options = array();
        for ($i = 1; $i<=ASSIGNSUBMISSION_PDF_MAXFILES; $i++) {
            $options[$i] = $i;
        }

        $mform->addElement('select', 'assignsubmission_pdf_maxfiles',
                           get_string('maxfilessubmission', 'assignsubmission_pdf'), $options);
        $mform->setDefault('assignsubmission_pdf_maxfiles', $defaultmaxfilesubmissions);
        $mform->disabledIf('assignsubmission_pdf_maxfiles', 'assignsubmission_pdf_enabled', 'eq', 0);

        $choices = get_max_upload_sizes($CFG->maxbytes, $COURSE->maxbytes);
        $choices[0] = get_string('courseuploadlimit', 'assignsubmission_pdf').' ('.display_size($COURSE->maxbytes).')';
        $settings[] = array(
            'type' => 'select',
            'name' => 'maxsubmissionsizebytes',
            'description' => get_string('maximumsubmissionsize', 'assignsubmission_file'),
            'options' => $choices,
            'default' => $defaultmaxsubmissionsizebytes
        );

        $mform->addElement('select', 'assignsubmission_pdf_maxsizebytes',
                           get_string('maximumsubmissionsize', 'assignsubmission_file'), $choices);
        $mform->setDefault('assignsubmission_pdf_maxsizebytes', $defaultmaxsubmissionsizebytes);
        $mform->disabledIf('assignsubmission_pdf_maxsizebytes', 'assignsubmission_pdf_enabled', 'eq', 0);

        // Coversheet.
        $mform->addElement('filemanager', 'assignsubmission_pdf_coversheet', get_string('coversheet', 'assignsubmission_pdf'), null,
                           array(
                                'subdirs' => 0, 'maxbytes' => $COURSE->maxbytes,
                                'maxfiles' => 1, 'accepted_types' => array('.pdf')
                           ));

        // Templates.
        $templates = array();
        $templates[0] = get_string('notemplate', 'assignsubmission_pdf');
        $templates_data = $DB->get_records_select_menu('assignsubmission_pdf_tmpl', 'courseid = 0 OR courseid = ?',
                                                       array($COURSE->id), 'name', 'id, name');
        foreach ($templates_data as $templateid => $templatename) {
            $templates[$templateid] = $templatename;
        }
        $mform->addElement('select', 'assignsubmission_pdf_templateid',
                           get_string('coversheettemplate', 'assignsubmission_pdf'), $templates);
        $mform->setDefault('assignsubmission_pdf_templateid', $defaulttemplateid);

        $edittemplateurl = new moodle_url('/mod/assign/submission/pdf/edittemplates.php', array('courseid' => $COURSE->id));
        $edittemplatelink = html_writer::link($edittemplateurl, get_string('edittemplates', 'assignsubmission_pdf'),
                                              array('target' => '_blank'));
        $mform->addElement('static', 'assignsubmission_pdf_template_edit', '', $edittemplatelink);
    }

    /**
     * Set up the draft file areas before displaying the settings form
     * @param array $default_values the values to be passed in to the form
     */
    public function data_preprocessing(&$default_values) {
        $context = $this->assignment->get_context();
        $course = $this->assignment->get_course();
        $draftitemid = file_get_submitted_draft_itemid('coversheet');
        if ($context) {
            // Not needed if the activty has not yet been created.
            file_prepare_draft_area($draftitemid, $context->id, 'assignsubmission_pdf', ASSIGNSUBMISSION_PDF_FA_COVERSHEET, 0,
                                    array(
                                         'subdirs' => 0, 'maxbytes' => $course->maxbytes,
                                         'maxfiles' => 1, 'accepted_types' => array('*.pdf')
                                    ));
        }

        $default_values['assignsubmission_pdf_coversheet'] = $draftitemid;
    }

    /**
     * save the settings for file submission plugin
     * @param stdClass $data
     * @return bool
     */
    public function save_settings(stdClass $data) {
        $this->set_config('maxfilesubmissions', $data->assignsubmission_pdf_maxfiles);
        $this->set_config('maxsubmissionsizebytes', $data->assignsubmission_pdf_maxsizebytes);
        $this->set_config('templateid', $data->assignsubmission_pdf_templateid);

        $context = $this->assignment->get_context();
        $course = $this->assignment->get_course();
        file_save_draft_area_files($data->assignsubmission_pdf_coversheet, $context->id, 'assignsubmission_pdf',
                                   ASSIGNSUBMISSION_PDF_FA_COVERSHEET, 0,
                                   array(
                                        'subdirs' => 0, 'maxbytes' => $course->maxbytes,
                                        'maxfiles' => 1, 'accepted_types' => array('*.pdf')
                                   ));

        return true;
    }

    /**
     * File format options
     *
     * @return array
     */
    private function get_file_options() {
        $fileoptions = array(
            'subdirs' => 0,
            'maxbytes' => $this->get_config('maxsubmissionsizebytes'),
            'maxfiles' => $this->get_config('maxfilesubmissions'),
            'accepted_types' => array('*.pdf'),
            'return_types' => FILE_INTERNAL
        );
        return $fileoptions;
    }

    /**
     * Add elements to submission form
     *
     * @param mixed $submission stdClass|null
     * @param MoodleQuickForm $mform
     * @param stdClass $data
     * @return bool
     */
    public function get_form_elements($submission, MoodleQuickForm $mform, stdClass $data) {
        global $DB;

        if ($this->get_config('maxfilesubmissions') <= 0) {
            return false;
        }

        $fileoptions = $this->get_file_options();
        $submissionid = $submission ? $submission->id : 0;

        $context = $this->assignment->get_context();
        $fs = get_file_storage();
        $coversheetfiles = $fs->get_area_files($context->id, 'assignsubmission_pdf', ASSIGNSUBMISSION_PDF_FA_COVERSHEET,
                                               false, '', false);
        if ($coversheetfiles) {
            $file = reset($coversheetfiles);
            $fileurl = moodle_url::make_pluginfile_url($file->get_contextid(), $file->get_component(), $file->get_filearea(),
                                                       null, $file->get_filepath(), $file->get_filename());
            $filelink = html_writer::link($fileurl, $file->get_filename(), array('target' => '_blank'));
            $mform->addElement('static', 'pdf_coversheet', '',
                               get_string('coversheetnotice', 'assignsubmission_pdf').': '.$filelink);
        }

        file_prepare_standard_filemanager($data, 'pdfs', $fileoptions, $this->assignment->get_context(),
                                          'assignsubmission_pdf', ASSIGNSUBMISSION_PDF_FA_DRAFT, $submissionid);
        $label = html_writer::tag('span', get_string('pdfsubmissions', 'assignsubmission_pdf'), array('class' => 'accesshide'));
        $mform->addElement('filemanager', 'pdfs_filemanager', $label, null, $fileoptions);

        if ($coversheetfiles) {
            if ($templateid = $this->get_config('templateid')) {
                $templateitems = $DB->get_records('assignsubmission_pdf_tmplit', array('templateid' => $templateid));
                $templatedata = array();
                if ($submissionid) {
                    if ($templatedata = $DB->get_field('assignsubmission_pdf', 'templatedata',
                                                       array('submission' => $submissionid))) {
                        $templatedata = unserialize($templatedata);
                    }
                }
                foreach ($templateitems as $item) {
                    $elname = "pdf_template[{$item->id}]";
                    if ($item->type == 'shorttext') {
                        $mform->addElement('text', $elname, s($item->setting));
                    } else if ($item->type == 'text') {
                        $mform->addElement('textarea', $elname, s($item->setting));
                    } else {
                        continue;
                    }
                    $mform->setType($elname, PARAM_TEXT);
                    $mform->addRule($elname, null, 'required', null, 'client');
                    if (isset($templatedata[$item->id])) {
                        $mform->setDefault($elname, $templatedata[$item->id]);
                    }
                }
            }
        }

        return true;
    }

    /**
     * Count the number of files
     *
     * @param int $submissionid
     * @param string $area
     * @return int
     */
    private function count_files($submissionid, $area) {
        $fs = get_file_storage();
        $files = $fs->get_area_files($this->assignment->get_context()->id, 'assignsubmission_pdf',
                                     $area, $submissionid, "id", false);

        return count($files);
    }

    /**
     * Save & preprocess the files and trigger plagiarism plugin, if enabled, to scan the uploaded files via events trigger
     *
     * @global stdClass $USER
     * @global moodle_database $DB
     * @param stdClass $submission
     * @param stdClass $data
     * @return bool
     */
    public function save(stdClass $submission, stdClass $data) {
        global $USER, $DB, $SESSION;

        // Pre-process all files to convert to useful PDF format.
        $fileoptions = $this->get_file_options();

        file_postupdate_standard_filemanager($data, 'pdfs', $fileoptions, $this->assignment->get_context(),
                                             'assignsubmission_pdf', ASSIGNSUBMISSION_PDF_FA_DRAFT, $submission->id);

        $pdfsubmission = $this->get_pdf_submission($submission->id);

        // Plagiarism code event trigger when files are uploaded.

        $fs = get_file_storage();
        $files = $fs->get_area_files($this->assignment->get_context()->id, 'assignsubmission_pdf',
                                     ASSIGNSUBMISSION_PDF_FA_DRAFT, $submission->id, "id", false);
        // Check all files are PDF v1.4 or less.
        $submissionok = true;
        foreach ($files as $key => $file) {
            if (!AssignPDFLib::ensure_pdf_compatible($file)) {
                $filename = $file->get_filename();
                $file->delete();
                unset($files[$key]);
                if (!isset($SESSION->assignsubmission_pdf_invalid)) {
                    $SESSION->assignsubmission_pdf_invalid = array();
                }
                $SESSION->assignsubmission_pdf_invalid[] = $filename;
                $submissionok = false;
            }
        }

        if (!$submissionok) {
            return false;
        }

        $templatedata = null;
        if (isset($data->pdf_template)) {
            $templatedata = serialize($data->pdf_template);
        }

        $count = $this->count_files($submission->id, ASSIGNSUBMISSION_PDF_FA_DRAFT);
        // Send files to event system.
        // Let Moodle know that an assessable file was uploaded (eg for plagiarism detection).
        $eventdata = new stdClass();
        $eventdata->modulename = 'assign';
        $eventdata->cmid = $this->assignment->get_course_module()->id;
        $eventdata->itemid = $submission->id;
        $eventdata->courseid = $this->assignment->get_course()->id;
        $eventdata->userid = $USER->id;
        if ($count>1) {
            $eventdata->files = $files; // This is depreceated - please use pathnamehashes instead!
        }
        $eventdata->file = $files; // This is depreceated - please use pathnamehashes instead!
        $eventdata->pathnamehashes = array_keys($files);
        events_trigger('assessable_file_uploaded', $eventdata);

        if ($pdfsubmission) {
            $pdfsubmission->numpages = 0;
            $pdfsubmission->templatedata = $templatedata;
            $DB->update_record('assignsubmission_pdf', $pdfsubmission);
        } else {
            $pdfsubmission = new stdClass();
            $pdfsubmission->submission = $submission->id;
            $pdfsubmission->assignment = $this->assignment->get_instance()->id;
            $pdfsubmission->numpages = 0;
            $pdfsubmission->templatedata = $templatedata;
            $DB->insert_record('assignsubmission_pdf', $pdfsubmission);
        }

        if (!$this->assignment->get_instance()->submissiondrafts) {
            // No 'submit assignment' button - need to submit immediately.
            $this->submit_for_grading($submission);
        }

        return true;
    }

    /**
     * If a coversheet template is in use, make sure the student has supplied all
     * the data required.
     * @param object $submission
     * @return bool|string 'true' if all data supplied, message if some data still needed
     */
    public function precheck_submission($submission = null) {
        global $DB, $USER;

        if (is_null($submission)) {
            if (!empty($this->assignment->get_instance()->teamsubmission)) {
                $submission = $this->assignment->get_group_submission($USER->id, 0, true);
            } else {
                $submission = $this->assignment->get_user_submission($USER->id, true);
            }
        }
        $fs = get_file_storage();
        $context = $this->assignment->get_context();
        $coversheetfiles = $fs->get_area_files($context->id, 'assignsubmission_pdf', ASSIGNSUBMISSION_PDF_FA_COVERSHEET,
                                               false, '', false);

        $msg = array();
        if ($coversheetfiles) {
            if ($templateid = $this->get_config('templateid')) {
                $templateitems = $DB->get_records('assignsubmission_pdf_tmplit', array('templateid' => $templateid));
                $templatedata = $DB->get_field('assignsubmission_pdf', 'templatedata', array('submission' => $submission->id));
                if (!empty($templatedata)) {
                    $templatedata = unserialize($templatedata);
                }
                foreach ($templateitems as $item) {
                    if ($item->type != 'date' && !isset($templatedata[$item->id])) {
                        $msg[] = get_string('missingfield', 'assignsubmission_pdf', $item->setting);
                    }
                }
            }
        }

        if (!empty($msg)) {
            return implode('<br />', $msg);
        }

        return true;
    }

    /**
     * Combine the PDFs together ready for marking
     * @param stdClass $submission optional details of the submission to process
     * @return void
     */
    public function submit_for_grading($submission = null) {
        global $DB, $USER;
        if (is_null($submission)) {
            if (!empty($this->assignment->get_instance()->teamsubmission)) {
                $submission = $this->assignment->get_group_submission($USER->id, 0, true);
            } else {
                $submission = $this->assignment->get_user_submission($USER->id, true);
            }
        }
        $pagecount = $this->create_submission_pdf($submission);

        // Save the pagecount.
        $submissionpdf = $DB->get_record('assignsubmission_pdf', array('assignment' => $submission->assignment,
                                                                      'submission' => $submission->id), 'id');
        $upd = new stdClass();
        $upd->numpages = $pagecount;
        if ($pagecount) {
            $upd->status = ASSIGNSUBMISSION_PDF_STATUS_SUBMITTED;
        } else {
            $upd->status = ASSIGNSUBMISSION_PDF_STATUS_EMPTY;
        }
        if ($submissionpdf) {
            $upd->id = $submissionpdf->id;
            $DB->update_record('assignsubmission_pdf', $upd);
        } else {
            // This should never really happen, but cope with it anyway.
            $upd->assignment = $submission->assignment;
            $upd->submission = $submission->id;
            $upd->id = $DB->insert_record('assignsubmission_pdf', $upd);
        }
    }

    protected function get_temp_folder($submissionid) {
        global $CFG, $USER;

        $tempfolder = $CFG->dataroot.'/temp/assignsubmission_pdf/';
        $tempfolder .= sha1("{$submissionid}_{$USER->id}_".time());
        return $tempfolder;
    }

    protected function create_submission_pdf(stdClass $submission) {
        global $DB;

        $fs = get_file_storage();

        $context = $this->assignment->get_context();
        $coversheet = null;
        $templateitems = null;

        // Create a the required temporary folders.
        $temparea = $this->get_temp_folder($submission->id);
        $tempdestarea = $temparea.'sub';
        $destfile = $tempdestarea.'/'.ASSIGNSUBMISSION_PDF_FILENAME;
        if (!file_exists($temparea) || !file_exists($tempdestarea)) {
            if (!mkdir($temparea, 0777, true) || !mkdir($tempdestarea, 0777, true)) {
                $errdata = (object)array('temparea' => $temparea, 'tempdestarea' => $tempdestarea);
                throw new moodle_exception('errortempfolder', 'assignsubmission_pdf', '', null, $errdata);
            }
        }

        // Get the coversheet details and copy the file to the temporary folder.
        $coversheetfiles = $fs->get_area_files($context->id, 'assignsubmission_pdf', ASSIGNSUBMISSION_PDF_FA_COVERSHEET,
                                               false, '', false);
        if ($coversheetfiles) {
            $coversheetfile = reset($coversheetfiles); // Only ever one coversheet file.
            $coversheet = $tempdestarea.'/coversheet.pdf';
            if (!$coversheetfile->copy_content_to($coversheet)) {
                $errdata = (object)array('coversheet' => $coversheet);
                throw new moodle_exception('errorcoversheet', 'assignsubmission_pdf', '', null, $errdata);
            }
            if ($templateid = $this->get_config('templateid')) {
                $templateitems = $DB->get_records('assignsubmission_pdf_tmplit', array('templateid' => $templateid));
                $templatedata = $DB->get_field('assignsubmission_pdf', 'templatedata', array('submission' => $submission->id));
                if (!empty($templatedata)) {
                    $templatedata = unserialize($templatedata);
                }
                foreach ($templateitems as $item) {
                    if (isset($templatedata[$item->id])) {
                        $item->data = $templatedata[$item->id];
                    } else {
                        $item->data = '';
                    }
                }
            }
        }

        // Copy all the PDFs to the temporary folder.
        $files = $fs->get_area_files($context->id, 'assignsubmission_pdf', ASSIGNSUBMISSION_PDF_FA_DRAFT,
                                     $submission->id, "sortorder, id", false);
        $combinefiles = array();
        foreach ($files as $file) {
            $destpath = $temparea.'/'.$file->get_contenthash();
            if (!$file->copy_content_to($destpath)) {
                throw new moodle_exception('errorcopyfile', 'assignsubmission_pdf', '', $file->get_filename());
            }
            $combinefiles[] = $destpath;
        }

        // Combine all the submitted files and the coversheet.
        $mypdf = new AssignPDFLib();
        $pagecount  = $mypdf->combine_pdfs($combinefiles, $destfile, $coversheet, $templateitems);
        if (!$pagecount) {
            return 0; // No pages found in the submitted files - this shouldn't happen.
        }

        // Copy the combined file into the submission area.
        $fs->delete_area_files($context->id, 'assignsubmission_pdf', ASSIGNSUBMISSION_PDF_FA_FINAL, $submission->id);
        $fileinfo = array(
            'contextid' => $context->id,
            'component' => 'assignsubmission_pdf',
            'filearea' => ASSIGNSUBMISSION_PDF_FA_FINAL,
            'itemid' => $submission->id,
            'filename' => ASSIGNSUBMISSION_PDF_FILENAME,
            'filepath' => $this->get_subfolder()
        );
        $fs->create_file_from_pathname($fileinfo, $destfile);

        // Clean up all the temporary files.
        unlink($destfile);
        if ($coversheet) {
            unlink($coversheet);
        }
        foreach ($combinefiles as $combinefile) {
            unlink($combinefile);
        }
        @rmdir($tempdestarea);
        @rmdir($temparea);

        return $pagecount;
    }

    /**
     * Produce a list of files suitable for export that represent this feedback or submission
     *
     * @param stdClass $submission The submission
     * @param stdClass $user The user
     * @return array - return an array of files indexed by filename
     */
    public function get_files(stdClass $submission, stdClass $user) {
        $result = array();
        $fs = get_file_storage();

        if ($submission->status == ASSIGNSUBMISSION_PDF_STATUS_SUBMITTED) {
            $files = $fs->get_area_files($this->assignment->get_context()->id, 'assignsubmission_pdf',
                                         ASSIGNSUBMISSION_PDF_FA_FINAL, $submission->id, "timemodified", false);
        } else {
            $files = $fs->get_area_files($this->assignment->get_context()->id, 'assignsubmission_pdf',
                                         ASSIGNSUBMISSION_PDF_FA_DRAFT, $submission->id, "timemodified", false);
        }

        foreach ($files as $file) {
            $result[$file->get_filename()] = $file;
        }
        return $result;
    }

    /**
     * Display the list of files  in the submission status table
     * @param stdClass $submission
     * @param bool $showviewlink
     * @return string
     */
    public function view_summary(stdClass $submission, & $showviewlink) {
        global $SESSION, $DB, $OUTPUT;

        $output = '';
        if (isset($SESSION->assignsubmission_pdf_invalid)) {
            $invalidfiles = '';
            foreach ($SESSION->assignsubmission_pdf_invalid as $filename) {
                $invalidfiles .= html_writer::tag('p', get_string('invalidpdf', 'assignsubmission_pdf', $filename));
            }
            $output .= html_writer::tag('div', $invalidfiles, array('class' => 'assignsubmission_pdf_invalid'));
            unset($SESSION->assignsubmission_pdf_invalid);
        }

        if (!isset($submission->status)) {
            // For some silly reason, the status is not included in the details when drawing the grading table - so
            // I need to do an extra DB query just to retrieve that information.
            static $submissionstatus = null;
            if (is_null($submissionstatus)) {
                $submissionstatus = $DB->get_records_menu('assign_submission', array('assignment' => $submission->assignment),
                                                          '', 'id, status');
            }
            if (isset($submissionstatus[$submission->id])) {
                $submission->status = $submissionstatus[$submission->id];
            } else {
                $submission->status = ASSIGN_SUBMISSION_STATUS_DRAFT;
            }
        }

        if ($submission->status == ASSIGN_SUBMISSION_STATUS_DRAFT) {
            $count = $this->count_files($submission->id, ASSIGNSUBMISSION_PDF_FA_DRAFT);
            $showviewlink = $count>ASSIGNSUBMISSION_PDF_MAXSUMMARYFILES;
            if ($showviewlink) {
                $output .= get_string('countfiles', 'assignsubmission_pdf', $count);
            } else {
                $output .= $this->assignment->render_area_files('assignsubmission_pdf', ASSIGNSUBMISSION_PDF_FA_DRAFT,
                                                                $submission->id);
            }
        } else {
            if (!$this->is_empty($submission)) {
                $context = $this->assignment->get_context();
                $url = moodle_url::make_pluginfile_url($context->id, 'assignsubmission_pdf', ASSIGNSUBMISSION_PDF_FA_FINAL,
                                                       $submission->id, $this->get_subfolder(), ASSIGNSUBMISSION_PDF_FILENAME,
                                                       true);
                $output .= $OUTPUT->pix_icon('t/download', '');
                $output .= html_writer::link($url, get_string('finalsubmission', 'assignsubmission_pdf'));
            }
        }

        return $output;
    }

    /**
     * No full submission view - the summary contains the list of files and that is the whole submission
     *
     * @param stdClass $submission
     * @return string
     */
    public function view(stdClass $submission) {
        return $this->assignment->render_area_files('assignsubmission_pdf', ASSIGNSUBMISSION_PDF_FA_DRAFT, $submission->id);
    }

    /**
     * Return true if this plugin can upgrade an old Moodle 2.2 assignment of this type
     * and version.
     *
     * @param string $type
     * @param int $version
     * @return bool True if upgrade is possible
     */
    public function can_upgrade($type, $version) {
        // TODO davo - upgrade from uploadpdf?
        return false;
    }

    /**
     * Upgrade the settings from the old assignment
     * to the new plugin based one
     *
     * @param context $oldcontext - the old assignment context
     * @param stdClass $oldassignment - the old assignment data record
     * @param string $log record log events here
     * @return bool Was it a success? (false will trigger rollback)
     */
    public function upgrade_settings(context $oldcontext, stdClass $oldassignment, & $log) {
        // TODO davo - upgrade from uploadpdf?
        return true;
    }

    /**
     * Upgrade the submission from the old assignment to the new one
     *
     * @global moodle_database $DB
     * @param context $oldcontext The context of the old assignment
     * @param stdClass $oldassignment The data record for the old oldassignment
     * @param stdClass $oldsubmission The data record for the old submission
     * @param stdClass $submission The data record for the new submission
     * @param string $log Record upgrade messages in the log
     * @return bool true or false - false will trigger a rollback
     */
    public function upgrade(context $oldcontext, stdClass $oldassignment, stdClass $oldsubmission, stdClass $submission, & $log) {
        // TODO davo - upgrade from uploadpdf?
        return true;
    }

    /**
     * The assignment has been deleted - cleanup
     *
     * @global moodle_database $DB
     * @return bool
     */
    public function delete_instance() {
        global $DB;

        $params = array('assignment' => $this->assignment->get_instance()->id);
        $submissions = $DB->get_records('assignsubmission_pdf', $params);
        foreach ($submissions as $submission) {
            // Delete any comments / annotations on the submissions.
            $DB->delete_records('assignfeedback_pdf_annot', array('submissionid' => $submission->submission));
            $DB->delete_records('assignfeedback_pdf_cmnt', array('submissionid' => $submission->submission));
        }

        // Delete all PDF submission records for this assignment.
        $DB->delete_records('assignsubmission_pdf', $params);

        // All files in the module context are automatically deleted - no need to delete each area individually.

        return true;
    }

    /**
     * Formatting for log info
     * @param stdClass $submission The submission
     *
     * @return string
     */
    public function format_for_log(stdClass $submission) {
        // Format the info for each submission plugin add_to_log.
        $filecount = $this->count_files($submission->id, ASSIGNSUBMISSION_PDF_FA_DRAFT);
        $fileloginfo = '';
        $fileloginfo .= ' the number of file(s) : '.$filecount." file(s).<br>";

        return $fileloginfo;
    }

    /**
     * Return true if there are no submission files
     */
    public function is_empty(stdClass $submission) {
        return $this->count_files($submission->id, ASSIGNSUBMISSION_PDF_FA_DRAFT) == 0;
    }

    /**
     * Get file areas returns a list of areas this plugin stores files
     * @return array - An array of fileareas (keys) and descriptions (values)
     */
    public function get_file_areas() {
        $name = $this->get_name();
        return array(
            ASSIGNSUBMISSION_PDF_FA_COVERSHEET => get_string('coversheetfor', 'assignsubmission_pdf', $name),
            ASSIGNSUBMISSION_PDF_FA_DRAFT => get_string('draftfor', 'assignsubmission_pdf', $name),
            ASSIGNSUBMISSION_PDF_FA_FINAL => get_string('finalfor', 'assignsubmission_pdf', $name)
        );
    }

    protected function get_subfolder() {
        return '/1/';
    }

    /**
     * Copy the student's submission from a previous submission. Used when a student opts to base their resubmission
     * on the last submission.
     * @param stdClass $sourcesubmission
     * @param stdClass $destsubmission
     * @return bool
     */
    public function copy_submission(stdClass $sourcesubmission, stdClass $destsubmission) {
        global $DB;

        // Copy the files across.
        $contextid = $this->assignment->get_context()->id;
        $fs = get_file_storage();
        $files = $fs->get_area_files($contextid,
                                     'assignsubmission_pdf',
                                     ASSIGNSUBMISSION_PDF_FA_DRAFT,
                                     $sourcesubmission->id,
                                     'id',
                                     false);
        foreach ($files as $file) {
            $fieldupdates = array('itemid' => $destsubmission->id);
            $fs->create_file_from_storedfile($fieldupdates, $file);
        }

        // Copy the assignsubmission_file record.
        if ($filesubmission = $this->get_pdf_submission($sourcesubmission->id)) {
            unset($filesubmission->id);
            $filesubmission->submission = $destsubmission->id;
            $DB->insert_record('assignsubmission_file', $filesubmission);
        }
        return true;
    }
}
