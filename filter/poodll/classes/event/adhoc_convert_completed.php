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
 * The mod_assign extension granted event.
 *
 * @package    filter_poodll
 * @copyright  2017 Justin Hunt
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace filter_poodll\event;

defined('MOODLE_INTERNAL') || die();

/**
 * The filter_poodll adhoc_completed class.
 *
 * @package    filter_poodll
 * @since      Moodle 3.1
 * @copyright  2017 Justin Hunt
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class adhoc_convert_completed extends  adhoc_completed  {

    /**
     * Returns description of what happened.
     *
     * @return string
     */
    public function get_description() {
        return "An ad_hoc task to convert file to: '" . $this->data['other']->outfilename .
            "', owned by userid: " . $this->data['userid'] . ", has been completed.";
    }
    /**
     * Return localised event name.
     *
     * @return string
     */
    public static function get_name() {
        return get_string('event_adhoc_convert_completed', 'filter_poodll');
    }

}
