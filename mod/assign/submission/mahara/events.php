<?php

abstract class assign_mahara_events {
  /**
   * Clean up portfolio dependencies
   *
   * @param stdClass
   * @return boolean
   */
  public static function mahara_portfolio_deleted($portfolio) {
    global $DB;

    return $DB->delete_records('assign_mahara_submit_views', array(
      'portfolio' => $portfolio->id,
    ));
  }

  /**
   * Sets the portfolio submission to released upon grading
   *
   * @param stdClass $event {
   * - assign $assignment
   * - stdClass $submission
   * - stdClass $grade
   * }
   * @return boolean
   */
  public static function assign_mahara_grade_submitted($event) {
    global $DB;

    $plugin = $event->assignment->get_submission_plugin_by_type('mahara');
    if ($plugin) {
      $submission = $event->submission;
      if ($submission) {
        $submitted = $plugin->get_portfolio_record($submission);
        if ($submitted && $submitted->status == $plugin::STATUS_SUBMITTED) {
          return $plugin
            ->get_service()
            ->get_local_portfolio($submitted->portfolio)
            ->map(
              function($portfolio) use ($plugin, $submission) {
                return $plugin->add_update_portfolio_record($submission, $portfolio, $plugin::STATUS_RELEASED);
              })
            ->getOrElse(true);
        }
      }
    }

    return true;
  }
}
