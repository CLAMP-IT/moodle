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
class debug_log extends  \core\event\base  {

    const DEBUG_ZERO_LENGTH_FILE=10;

    /**
     * Create instance of event.
     *
     * @since Moodle 3.1
     *
     * @param \stdClass $filerecord
     * @return adhoc_registered
     */
    public static function create_from_data($debugobject) {
        //store debug object
        $data = array('other'=>$debugobject);
        //set context if we have one
        if($debugobject->contextid !==false){
            $context = \context::instance_by_id($debugobject->contextid);
            $data['context']=$context;
        }
        //set user if we have one
        if($debugobject->userid !==false){
            $data['userid']=$debugobject->userid;
            $data['relateduserid']=$debugobject->userid;
        }

        /** @var debug_log $event */
        $event = self::create($data);
        return $event;
    }

    /**
     * Returns description of what happened.
     *
     * @return string
     */
    public function get_description() {
        return "Debug message: " .  $this->data['other']->message ;
    }

    /**
     * Return localised event name.
     *
     * @return string
     */
    public static function get_name() {
        return get_string('event_debug_log', 'filter_poodll');
    }

    /**
     * Init method.
     *
     * @return void
     */
    protected function init() {
        $this->data['crud'] = 'c';
        $this->data['edulevel'] = self::LEVEL_OTHER;
    }
}
