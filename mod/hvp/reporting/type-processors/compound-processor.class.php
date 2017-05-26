<?php

/**
 * Class FillInProcessor
 * Processes and generates HTML report for 'fill-in' interaction type.
 */
class CompoundProcessor extends TypeProcessor {

  /**
   * Determines options for interaction and generates a human readable HTML
   * report.
   *
   * @param string $description Description of interaction task
   * @param array $crp Correct responses pattern
   * @param string $response User response
   *
   * @return string HTML for report
   */
  public function generateHTML($description, $crp, $response, $extras) {
    // We need some style for our report
    $this->setStyle('styles/compound.css');

    $H5PReport = H5PReport::getInstance();
    $reports = '';

    if (isset($extras->children)) {
      foreach ($extras->children as $childData) {
        $reports .= '<div class="h5p-result">' . $H5PReport->generateReport($childData) . '</div>';
      }
    }

    // Do not display description when children is empty
    if (!empty($reports) && !empty($description)) {
      $reports =
          '<p class="h5p-compound-task-description">' . $description . '</p>' .
          $reports;
    }

    return '<div class="h5p-compound-container">' . $reports . '</div>';
  }
}
