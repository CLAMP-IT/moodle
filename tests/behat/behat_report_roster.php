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
 * This file creates a behat step definition
 *
 * @package    report_roster
 * @copyright  2019 Lafayette College
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../../../lib/behat/behat_base.php');

use Behat\Behat\Context\Step\Given as Given;
use Behat\Gherkin\Node\PyStringNode as PyStringNode;

/**
 * Custom step definitions
 *
 * @package    report_roster
 * @copyright  2019 Lafayette College
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class behat_report_roster extends behat_base {
    /**
     * Sets the 'fields' config value as admin
     *
     * @Given /^I set report_roster\/fields to:$/
     * @param PyStringNode $value A triple-quote delimited text block to be set as the value of the setting
     */
    public function i_set_report_roster_fields_to(PyStringNode $value) {
        set_config('fields', $value->getRaw(), 'report_roster');
    }

    /**
     * Creates a dummy custom profile field called 'test_custom_field'
     *
     * @Given /^I create a dummy custom profile field$/
     */
    public function i_create_a_dummy_custom_profile_field() {
        global $DB;

        if ($DB->record_exists('user_info_field', array('shortname' => 'test_custom_field'))) {
            return;
        }

        $field = new stdClass();
        $field->shortname = 'test_custom_field';
        $field->name = 'Test Custom Field';
        $field->datatype = 'text';
        $field->categoryid = 1;
        $DB->insert_record('user_info_field', $field);
    }

    /**
     * Set a custom profile field for a given user
     *
     * @Given /^I set the custom profile field "([^"]*)" to "([^"]*)" for user "([^"]*)"$/
     * @param string $field The shortname of the targetfield
     * @param string $value The value to set
     * @param string $user The username of the target user
     */
    public function i_set_the_custom_profile_field_to_for_user($field, $value, $user) {
        global $DB;

        $fielddata = $DB->get_record('user_info_field', array('shortname' => $field));
        $user = $DB->get_record('user', array('username' => $user));

        if (!empty($fielddata) && !empty($user)) {
            $data = new stdClass();
            $data->userid = $user->id;
            $data->fieldid = $fielddata->id;
            $data->data = $value;

            $existing = $DB->get_record('user_info_data', array(
                'userid' => $user->id,
                'fieldid' => $fielddata->id,
            ));

            if (!empty($existing)) {
                $data->id = $existing->id;
                $DB->update_record('user_info_data', $data);
            } else {
                $DB->insert_record('user_info_data', $data);
            }
        }
    }
}
