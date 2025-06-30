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
 * Result class for local_a11y_check
 *
 * @package   local_accessibility_filescan
 * @copyright 2023 Swarthmore College
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_accessibility_filescan;

/**
 * A class to standardize a11y results for pdfs
 */
class pdf_a11y_results {
    /**
     * Constructor function
     * @param int $text
     * @param int $title
     * @param int $language
     * @param int $istagged
     * @param int $pagecount
     */
    public function __construct($text = 0, $title = 0, $language = 0, $istagged = 0, $pagecount = 0) {
        $this->hastext = $text;
        $this->hastitle = $title;
        $this->haslanguage = $language;
        $this->istagged = $istagged;
        $this->pagecount = $pagecount;
    }
}
