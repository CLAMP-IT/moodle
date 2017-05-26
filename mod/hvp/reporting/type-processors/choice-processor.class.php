<?php

/**
 * Class FillInProcessor
 * Processes and generates HTML report for 'fill-in' interaction type.
 */
class ChoiceProcessor extends TypeProcessor {

  /**
   * Determines options for interaction and generates a human readable HTML
   * report.
   *
   * @param string $description Description of interaction task
   * @param array $crp Correct responses pattern
   * @param string $response User response
   * @param stdClass $extras Optional xAPI properties like choice descriptions
   * @return string HTML for report
   */
  public function generateHTML($description, $crp, $response, $extras = NULL) {
    if ($this->isLongChoice($extras)) {
      return H5PReport::getInstance()->generateReport($this->xapiData, 'long-choice');
    }

    // We need some style for our report
    $this->setStyle('styles/choice.css');

    $correctAnswers = explode('[,]', $crp[0]);
    $responses = explode('[,]', $response);

    $descriptionHTML = $this->generateDescription($description);
    $tableHTML = $this->generateTable($extras, $correctAnswers, $responses);

    return
      '<div class="h5p-choices-container">' .
        $descriptionHTML . $tableHTML .
      '</div>';
  }

  /**
   * Generate description element
   *
   * @param string $description
   *
   * @return string Description element as a string
   */
  private function generateDescription($description) {
    return'<p class="h5p-choices-task-description">' . $description . '</p>';
  }

  /**
   * Generate HTML table of choices
   *
   * @param object $extras
   * @param array $correctAnswers
   * @param array $responses
   *
   * @return string Table element
   */
  private function generateTable($extras, $correctAnswers, $responses) {

    $choices = $extras->choices;
    $tableHeader =
      '<tr class="h5p-choices-table-heading">' .
        '<td class="h5p-choices-choice">Answers</td>' .
        '<td class="h5p-choices-user-answer">Your Answer</td>' .
        '<td class="h5p-choices-crp-answer">Correct</td>' .
      '</tr>';

    $rows = '';
    foreach($choices as $choice) {
      $choiceID = $choice->id;
      $isCRP = in_array($choiceID, $correctAnswers);
      $isAnswered = in_array($choiceID, $responses);

      $userClasses = 'h5p-choices-user';
      $crpClasses = 'h5p-choices-crp';
      if ($isAnswered) {
        $userClasses .= ' h5p-choices-answered';
      }
      if ($isCRP) {
        $userClasses .= ' h5p-choices-user-correct';
        $crpClasses .= ' h5p-choices-crp-correct';
      }

      $row =
        '<td>' . $choice->description->{'en-US'} . '</td>' .
        '<td class="h5p-choices-icon-cell">' .
          '<span class="' . $userClasses . '"></span>' .
        '</td>' .
        '<td class="h5p-choices-icon-cell">' .
          '<span class="' . $crpClasses . '"></span>' .
        '</td>';

      $rows .= '<tr>' . $row . '</tr>';
    }

    $tableContent = '<tbody>' . $tableHeader . $rows . '</tbody>';
    return '<table class="h5p-choices-table">' . $tableContent . '</table>';
  }

  /**
   * Determine if choice is a long choice interaction type
   *
   * @param $extras
   *
   * @return bool
   */
  private function isLongChoice($extras) {
    $extensions = isset($extras->extensions) ? $extras->extensions : (object) array();

    // Determine if line-breaks extension exists
    return isset($extensions->{'https://h5p.org/x-api/line-breaks'});
  }
}
