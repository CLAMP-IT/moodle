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
 * PHPUnit tests for the roster report.
 *
 * @package   report_roster
 * @copyright 2024 Lafayette College ITS
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace report_roster;

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once(dirname(__FILE__) . '/../locallib.php');

/**
 * Unit tests user fetch.
 *
 * @package    report_roster
 * @category   test
 * @covers     ::report_roster_profile_fields_query
 * @copyright  2024 Lafayette College ITS
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class report_roster_test extends \advanced_testcase {
    public function test_enrolled_user_fetch() {
        $this->setAdminUser();
        $this->resetAfterTest(true);

        $fieldsconfig = explode("\n", get_config('report_roster', 'fields'));
        $this->assertEquals([
            'fullname',
            'currenttime %l:%M %p',
        ], $fieldsconfig);
        $expectedfields = [
            'u.id',
            'u.picture',
            'u.firstname',
            'u.lastname',
            'u.firstnamephonetic',
            'u.lastnamephonetic',
            'u.middlename',
            'u.alternatename',
            'u.imagealt',
            'u.email',
            'u.username',
            'u.timezone'
        ];
        $fieldstofetch = report_roster_profile_fields_query();
        $this->assertEquals(implode(',', $expectedfields), $fieldstofetch);
    }
}
