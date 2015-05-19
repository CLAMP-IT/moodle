<?php

class assign_feedback_mahara extends assign_feedback_plugin {

    /**
     * @see parent
     * @return string
     */
    public function get_name() {
        return get_string('pluginname', 'assignfeedback_mahara');
    }

    /**
     * @see parent
     */
    public function is_empty(stdClass $grade) {
        return false;
    }

    /**
     * @see parent
     */
    public function has_user_summary() {
        return false;
    }

    /**
     * Callback function that is called when the standard grading form is used.
     * @see assign_plugin::save()
     */
    public function save(stdClass $grade, stdClass $formdata = null) {
        $outcomes = $this->process_outcomes_from_form($grade, $formdata);
        return $this->handle_grade_save($grade, $outcomes);
    }

    /**
     * Callback method called when the quickgrading form is used
     * @see assign_feedback_plugin::save_quickgrading_changes()
     */
    public function save_quickgrading_changes($userid, $grade) {
        $outcomes = $this->process_outcomes_from_quickgrading($grade);
        return $this->handle_grade_save($grade, $outcomes);
    }

    /**
     * Method that responds to a grade save event. It checks whether there is a Mahara page
     * that needs to be released, and if so, releases it.
     *
     * @param stdClass $grade
     * @param unknown_type $outcomefunction
     * @param unknown_type $data
     * @return boolean If there's an error, sets $this->set_error() and returns false. If there's no error, returns true.
     */
    private function handle_grade_save(stdClass $grade, $outcomes) {
        global $DB;

        $submission = $this->assignment->get_user_submission($grade->userid, false);

        if (!$submission) {
            return true;
        }

        $maharasubmission = $DB->get_record(
            'assignsubmission_mahara',
            array(
                'assignment' => $this->assignment->get_instance()->id,
                'submission' => $submission->id,
            )
        );

        // If there is a locked Mahara component in this submission, Mahara-release it.
        if ($maharasubmission && $maharasubmission->viewstatus == assign_submission_mahara::STATUS_SUBMITTED) {

            /* @var $maharasubmissionplugin assign_submission_mahara */
            $maharasubmissionplugin = $this->assignment->get_submission_plugin_by_type('mahara');

            $maharasubmissionplugin->mnet_release_submitted_view(
                $maharasubmission->viewid,
                $outcomes,
                $maharasubmission->iscollection
            );

            if ($maharasubmissionplugin->get_error()) {
                $this->set_error($maharasubmissionplugin->get_error());
                return false;
            } else {
                $maharasubmissionplugin->set_mahara_submission_status($maharasubmission->submission, assign_submission_mahara::STATUS_RELEASED);
            }
        }

        return true;
    }

    /**
     * @see parent
     */
    public function supports_quickgrading() {
        return true;
    }

    /**
     * Get user grading info
     *
     * @param $grade
     * @return grading_info
     */
    private function get_user_grade_info($grade) {
        return $grading_info = grade_get_grades(
                $this->assignment->get_course()->id,
                'mod',
                'assign',
                $this->assignment->get_instance()->id,
                $grade->userid
        );
    }

    /**
     * Process outcome data from quick grading
     *
     * @param $grade
     * @return array
     */
    private function process_outcomes_from_quickgrading($grade) {
        $grading_info = $this->get_user_grade_info($grade);

        $viewoutcomes = array();
        if (!empty($grading_info->outcomes)) {
            foreach ($grading_info->outcomes as $outcomeid => $outcome) {
                $newoutcome_name = "outcome_{$outcomeid}_{$grade->userid}";
                $oldoutcome = $outcome->grades[$grade->userid]->grade;
                $newoutcome = optional_param($newoutcome_name, -1, PARAM_INT);

                $scale = make_grades_menu(-$outcome->scaleid);
                if ($oldoutcome == $newoutcome || !isset($scale[$newoutcome])) {
                    continue;
                }

                foreach ($scale as $k => $v) {
                    $scale[$k] = array('name' => $v, 'value' => $k);
                }

                $viewoutcomes[] = array(
                        'name' => $outcome->name,
                        'scale' => $scale,
                        'grade' => $newoutcome,
                );
            }
        }

        return $viewoutcomes;
    }

    /**
     * Process outcome data from a form
     *
     * @param $grade
     * @param stdClass $formdata
     * @return array
     */
    private function process_outcomes_from_form($grade, $formdata) {
        $grading_info = $this->get_user_grade_info($grade);
        $viewoutcomes = array();

        if (!empty($grading_info->outcomes)) {
            foreach ($grading_info->outcomes as $index => $outcome) {
                $name = "outcome_$index";
                $oldoutcome = $outcome->grades[$grade->userid]->grade;
                $scale = make_grades_menu(-$outcome->scaleid);

                if (
                        !isset($formdata->{$name}[$grade->userid])
                        || $oldoutcome == $formdata->{$name}[$grade->userid]
                        || !isset($scale[$formdata->{$name}[$grade->userid]])
                ) {
                    continue;
                }

                foreach ($scale as $k => $v) {
                    $scale[$k] = array('name' => $v, 'value' => $k);
                }

                $viewoutcomes[] = array(
                    'name' => $outcome->name,
                    'scale' => $scale,
                    'grade' => $formdata->{$name}[$grade->userid],
                );
            }
        }

        return $viewoutcomes;
    }
}
