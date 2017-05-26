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
 * The filter_poodll adhoc_registered class.
 *
 * @package    filter_poodll
 * @since      Moodle 3.1
 * @copyright  2017 Justin Hunt
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class adhoc_convert_registered extends adhoc_registered  {


    /**
     * Returns description of what happened.
     *
     * @return string
     */
    public function get_description() {
        $other=$this->data['other'];
        if(gettype($other)=='object'){
            $other=get_object_vars($other);
        }
        return "The user with id '" . $this->data['userid'] .
            "User has registered an ad_hoc task to FFMPEG convert file of name '" . $other['infilename'] .
            "' to a file of name '" . $other['outfilename'];
    }

    /**
     * Return localised event name.
     *
     * @return string
     */
    public static function get_name() {
        return get_string('event_adhoc_convert_registered', 'filter_poodll');
    }

}
