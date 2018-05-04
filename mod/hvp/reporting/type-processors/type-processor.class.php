<?php

require(__DIR__ . '/../html-purifier/HtmlReportPurifier.php');

/**
 * Class TypeProcessor
 */
abstract class TypeProcessor {

  private $style;

  protected $xapiData;

  protected $disableScoring;

  /**
   * Generate HTML for report
   *
   * @param object $xapiData
   * @param bool $disableScoring Disables scoring
   *
   * @return string HTML as string
   */
  public function generateReport($xapiData, $disableScoring = false) {
    $this->xapiData       = $xapiData;
    $this->disableScoring = $disableScoring;

    // Grab description
    $description = $this->getDescription($xapiData);

    // Grab correct response pattern
    $crp = $this->getCRP($xapiData);

    // Grab extras
    $extras        = $this->getExtras($xapiData);
    $scoreSettings = $this->getScoreSettings($xapiData);

    return HtmlReportPurifier::filter_xss($this->generateHTML(
      $description,
      $crp,
      $this->getResponse($xapiData),
      $extras,
      $scoreSettings
    ));
  }

  /**
   * Get score settings
   *
   * @param object $xapiData
   *
   * @return object Score settings
   */
  protected function getScoreSettings($xapiData) {
    $scoreSettings = (object) [];

    if (!isset($xapiData->raw_score) || !isset($xapiData->max_score)) {
      return $scoreSettings;
    }

    // Grab scores and score labels
    $scoreSettings->rawScore = $xapiData->raw_score;
    $scoreSettings->maxScore = $xapiData->max_score;

    $scoreSettings->scoreLabel = 'Score:';
    if (isset($xapiData->score_label)) {
      $scoreSettings->scoreLabel = $xapiData->score_label;
    }

    $scoreSettings->scoreDelimiter = 'out of';
    if (isset($xapiData->score_delimiter)) {
      $scoreSettings->scoreDelimiter = $xapiData->score_delimiter;
    }

    $scoreSettings->scaledScoreDelimiter = ',';
    if (isset($xapiData->scaled_score_delimiter)) {
      $scoreSettings->scaledScoreDelimiter = $xapiData->scaled_score_delimiter;
    }

    // Scaled score
    if (isset($xapiData->score_scale)) {
      $scoreSettings->scoreScale = $xapiData->score_scale;

      $scoreSettings->scaledScoreLabel = 'Scaled score:';
      if (isset($xapiData->score_label)) {
        $scoreSettings->scaledScoreLabel = $xapiData->scaled_score_label;
      }
    }

    return $scoreSettings;
  }

  /**
   * Generate score html
   *
   * @param object $scoreSettings Score settings
   *
   * @return string Score html
   */
  protected function generateScoreHtml($scoreSettings) {
    $showScores = isset($scoreSettings->rawScore)
                  && isset($scoreSettings->maxScore)
                  && !$this->disableScoring;

    if (!$showScores) {
      return '';
    }

    // Generate html for score
    $scoreLabel     = $scoreSettings->scoreLabel;
    $scoreDelimiter = $scoreSettings->scoreDelimiter;
    $scaleDelimiter = '';

    // Generate html for scaled score
    $scaledHtml = "";
    if (isset($scoreSettings->scoreScale)) {
      $scaleDelimiter = $scoreSettings->scaledScoreDelimiter;
      $scaledHtml =
        "<div class='h5p-reporting-scaled-container'>" .
          "<span class='h5p-reporting-scaled-label'>{$scoreSettings->scaledScoreLabel}</span>" .
          "<span class='h5p-reporting-scaled-score'>{$scoreSettings->scoreScale}</span>" .
        "</div>";
    }

    $scoreHtml =
      "<div class='h5p-reporting-score-container'>" .
        "<span class='h5p-reporting-score-label'>{$scoreLabel}</span>" .
        "<span class='h5p-reporting-score'>" .
          $scoreSettings->rawScore . " " . $scoreDelimiter . " " .
          $scoreSettings->maxScore . $scaleDelimiter .
        "</span>" .
      "</div>";

    $html = "<div class='h5p-reporting-score-wrapper'>{$scoreHtml}{$scaledHtml}</div>";

    return $html;
  }

  /**
   * Decode extras from xAPI data.
   *
   * @param stdClass $xapiData
   *
   * @return stdClass
   */
  protected function getExtras($xapiData) {
    $extras = ($xapiData->additionals === '' ? new stdClass() : json_decode($xapiData->additionals));
    if (isset($xapiData->children)) {
      $extras->children = $xapiData->children;
    }

    return $extras;
  }

  /**
   * Decode and retrieve 'en-US' description from xAPI data.
   *
   * @param stdClass $xapiData
   *
   * @return string Description as a string
   */
  protected function getDescription($xapiData) {
    return $xapiData->description;
  }

  /**
   * Decode and retrieve Correct Responses Pattern from xAPI data.
   *
   * @param stdClass $xapiData
   *
   * @return array Correct responses pattern as an array
   */
  protected function getCRP($xapiData) {
    return json_decode($xapiData->correct_responses_pattern, true);
  }

  /**
   * Decode and retrieve user response from xAPI data.
   *
   * @param stdClass $xapiData
   *
   * @return string User response
   */
  protected function getResponse($xapiData) {
    return $xapiData->response;
  }

  /**
   * Processes xAPI data and returns a human readable HTML report
   *
   * @param string $description Description
   * @param array $crp Correct responses pattern
   * @param string $response User given answer
   * @param object $extras Additional data
   * @param object $scoreSettings Score settings
   *
   * @return string HTML for the report
   */
  abstract function generateHTML($description, $crp, $response, $extras, $scoreSettings);

  /**
   * Set style used by the processor.
   *
   * @param string $style Path to style
   */
  protected function setStyle($style) {
    $this->style = $style;
  }

  /**
   * Get style used by processor if used.
   *
   * @return string Library relative path to CSS
   */
  public function getStyle() {
    return $this->style;
  }
}
