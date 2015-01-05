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
 * Ensemble Video filter plugin.
 *
 * @package    filter_ensemble
 * @copyright  2012 Liam Moran, Nathan Baxley, University of Illinois
 *             2013 Symphony Video, Inc.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once(dirname(dirname(dirname(__FILE__))) . '/repository/lib.php');

class filter_ensemble extends moodle_text_filter {

  private $ensembleUrl;

  public function filter($text, array $options = array()) {
    global $CFG;

    $newtext = $text;

    if (!isset($CFG->filter_ensemble_urls)) {
      set_config('filter_ensemble_urls', '');
    }

    $urls = explode(',', $CFG->filter_ensemble_urls);
    // Add the currently configured repository url
    $urls[] = get_config('ensemble', 'ensembleURL');

    foreach ($urls as $url) {
      $this->ensembleUrl = trim($url);
      $search = '#<a [^>]*href="' . $this->ensembleUrl . '\?([^"]*)".*</a>#isU';
      $newtext = preg_replace_callback($search, array('filter_ensemble', 'callback'), $newtext);
    }

    if (is_null($newtext) or $newtext === $text) {
      return $text;
    }

    return $newtext;
  }

  private function callback($matches) {
    $settings = array();
    parse_str(html_entity_decode(urldecode($matches[1])), $settings);
    if (isset($settings['type'])) {
      if ($settings['type'] === 'video') {
        $width = isset($settings['width']) ? $settings['width'] : 640;
        $height = isset($settings['height']) ? $settings['height'] : 360;
        $source = $this->ensembleUrl . '/app/plugin/embed.aspx?ID=' . $settings['id'] . '&autoPlay=' . $settings['autoplay'] . '&displayTitle=' . $settings['showtitle'] . '&hideControls=' . $settings['hidecontrols'] . '&showCaptions=' . $settings['showcaptions'] . '&width=' . $width . '&height=' . $height;
        return '<iframe src="' . $source . '" frameborder="0" style="width: ' . $width . 'px;height:' . ($height + 56) . 'px;" allowfullscreen></iframe>';
      } else if ($settings['type'] === 'playlist') {
        $source = $this->ensembleUrl . '/app/plugin/embed.aspx?DestinationID=' . $settings['id'];
        return '<iframe src="' . $source . '" frameborder="0" style="width:800px;height:1000px;" allowfullscreen></iframe>';
      }
    }
  }

}
