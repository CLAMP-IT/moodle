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
 * Custom step definitions for PDF annotation type
 *
 * @package   assignfeedback_pdf
 * @copyright 2013 Davo Smith, Synergy Learning
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// NOTE: no MOODLE_INTERNAL test here, this file may be required by behat before including /config.php.

require_once(__DIR__ . '/../../../../../../lib/behat/behat_base.php');

use Behat\Behat\Context\Step\Then as Then;

class behat_assignfeedback_pdf extends behat_base {

    /**
     * Add a comment to the page at the given coordinates.
     *
     * @Then /^I add a comment at "(?P<x_number>\d+)" "(?P<y_number>\d+)" containing "(?P<content_string>(?:[^"]|\\")*)"$/
     * @param int $x
     * @param int $y
     * @param string $content
     * @return array
     */
    public function i_add_a_comment_at_containing($x, $y, $content) {
        return array(
            new Then('I fill in "behat_comment_at_x" with "'.$x.'"'),
            new Then('I fill in "behat_comment_at_y" with "'.$y.'"'),
            new Then('I fill in "behat_comment_content" with "'.$content.'"'),
            new Then('I press "Add comment"'),
            new Then('I fill in "behat_comment_at_x" with ""'),
            new Then('I fill in "behat_comment_at_y" with ""'),
            new Then('I fill in "behat_comment_content" with ""')
        );
    }
}
