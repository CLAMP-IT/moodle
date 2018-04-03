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
 * @package   block_clampmail
 * @copyright 2017 Collaborative Liberal Arts Moodle Project
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

global $CFG;
require_once($CFG->dirroot . '/blocks/clampmail/db/upgradelib.php');

class block_clampmail_upgradelib_testcase extends advanced_testcase {
    public function test_upgradelib() {
        global $DB;
        $this->resetAfterTest(true);

        // Create config in main namespace.
        set_config('block_clampmail_roleselection', 'foo,bar');
        set_config('block_clampmail_prepend_class', 'baz');
        set_config('block_clampmail_receipt', 'bar');

        // Migrate configuration.
        block_clampmail_migrate_settings();

        // Ensure new config was set.
        $this->assertEquals('foo,bar', get_config('block_clampmail', 'roleselection'));
        $this->assertEquals('baz', get_config('block_clampmail', 'prepend_class'));
        $this->assertEquals('bar', get_config('block_clampmail', 'receipt'));
    }
}
