<?php

/**
 * Class FillInProcessor
 * Processes and generates HTML report for 'fill-in' interaction type.
 */
class LongChoiceProcessor extends TypeProcessor {

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
    // We need some style for our report
    $this->setStyle('styles/long-choice.css');

    $correctAnswers = explode('[,]', $crp[0]);
    $responses = !empty($response) ? explode('[,]', $response) : array();

    $descriptionHTML = $this->generateDescription($description);
    $bodyHTML = $this->generateBody($extras, $correctAnswers, $responses);
    $footer = $this->generateFooter();

    return
      '<div class="h5p-long-choice-container">' .
        $descriptionHTML . $bodyHTML .
      '</div>' .
      $footer;
  }

  /**
   * Generates description element
   *
   * @param string $description
   *
   * @return string Description element
   */
  private function generateDescription($description) {
    return'<div class="h5p-long-choice-task-description">' . $description . '</div>';
  }

  /**
   * Generates report body from words
   *
   * @param object $extras Additional information used to render report
   * @param array $correctAnswers
   * @param array $responses
   *
   * @return string Body element as a string
   */
  private function generateBody($extras, $correctAnswers, $responses) {
    $choices = $extras->choices;

    $extensions = isset($extras->extensions) ? $extras->extensions : (object) array();

    // Determine if line-breaks extension exists
    $lineBreaks = isset($extensions->{'https://h5p.org/x-api/line-breaks'}) ?
      $extensions->{'https://h5p.org/x-api/line-breaks'} : array();
    $lineBreakIndex = 0;

    $choicesHTML = array();
    foreach($choices as $index => $choice) {
      $choiceID = $choice->id;
      $isCRP = in_array($choiceID, $correctAnswers);
      $isAnswered = in_array($choiceID, $responses);

      $classes = 'h5p-long-choice-word';
      if ($isAnswered) {
        $classes .= ' h5p-long-choice-answered';
      }
      if ($isCRP) {
        $classes .= ' h5p-long-choice-correct';
      }

      // Add choices html
      $choicesHTML[] =
        '<span class="' . $classes . '">' .
          $choice->description->{'en-US'} .
        '</span>';

      // Add line break if extension found
      if (isset($lineBreaks[$lineBreakIndex]) && $lineBreaks[$lineBreakIndex] === $index) {
        $choicesHTML[] = '</br>';
        $lineBreakIndex++;
      }
    }

    return
      '<div class="h5p-long-choice-words">' .
        join(' ', $choicesHTML) .
      '</div>';
  }

  /**
   * Generate footer
   *
   * @return string
   */
  private function generateFooter() {
    return
      '<div class="h5p-long-choice-footer">' .
        '<span class="h5p-long-choice-word h5p-long-choice-correct">Correct Answer</span>' .
        '<span class="h5p-long-choice-word h5p-long-choice-answered h5p-long-choice-correct">' .
          'Your correct answer' .
        '</span>' .
        '<span class="h5p-long-choice-word h5p-long-choice-answered">' .
          'Your incorrect answer' .
        '</span>' .
      '</div>';
  }
}
