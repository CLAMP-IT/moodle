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
class adhoc_completed extends  \core\event\base  {

    /**
     * Create instance of event.
     *
     * @since Moodle 3.1
     *
     * @param array $taskdata
     * @param \stdClass $filerecord
     * @return adhoc_completed
     */
    public static function create_from_task(\stdClass $taskdata) {
        //($taskdata->mediatype,$taskdata->infilename, $taskdata->outfilename, $taskdata->filename,$taskdata->filerecord);
        $filerecord= $taskdata->filerecord;
        $contextid=$filerecord->contextid;
        $userid=$filerecord->userid;
        $context = \context::instance_by_id($contextid);
        
        //weird moodle event proc. complains if other field is an object
        //and showhow it gets altered in json, so we do this to avoid a warning
        $taskjson = json_encode($taskdata);
        $taskvars = json_decode($taskjson,true);

        $data = array(
            'context' => $context,
            'objectid' => $filerecord->id,
            'userid' => $userid,
            'relateduserid' => $userid,
            'other' => $taskvars
        );
        /** @var extension_granted $event */
        $event = self::create($data);
       //the filerecord data might not be sufficient here ...for now we skip it
        // $event->add_record_snapshot('files', $filerecord);

        return $event;
    }

    /**
     * Returns description of what happened.
     *
     * @return string
     */
    public function get_description() {
        return "An ad_hoc task has been completed.";
    }

    /**
     * Return localised event name.
     *
     * @return string
     */
    public static function get_name() {
        return get_string('event_adhoc_completed', 'filter_poodll');
    }

    /**
     * Init method.
     *
     * @return void
     */
    protected function init() {
        $this->data['crud'] = 'c';
        $this->data['edulevel'] = self::LEVEL_OTHER;
        $this->data['objecttable'] = 'files';
    }
}
