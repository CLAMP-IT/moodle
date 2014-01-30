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
 * This file handles transactions with the gradebook escrow
 *
 * @copyright  2012 Turning Technologies
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
global $CFG;
require_once($CFG->dirroot . '/mod/turningtech/lib/types/TurningModel.php');
/**
 * Class to handle transactions with the gradebook escrow
 * 
 * @author jacob
 * @package mod_turningtech
 * @copyright  2012 Turning Technologies
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class TurningTechEscrow extends TurningModel {
    // DB fields used only by this model.
    /**
     * @var unknown_type
     */
    protected $deviceid;
    /**
     * @var unknown_type
     */
    protected $courseid;
    /**
     * @var unknown_type
     */
    protected $itemid;
    /**
     * @var unknown_type
     */
    protected $points_possible;
    /**
     * @var unknown_type
     */
    protected $points_earned;
    /**
     * @var unknown_type
     */
    protected $migrated;
    /**
     * @var unknown_type
     */
    public $tablename = 'turningtech_escrow';
    /**
     * @var unknown_type
     */
    public $classname = 'TurningTechEscrow';
    /**
     * fetch an instance
     * 
     * @param mixed $params
     * @return unknown_type
     */
    public static function fetch($params) {
        return parent::fetchhelper('turningtech_escrow', 'TurningTechEscrow', $params);
    }
    /**
     * generator function
     * 
     * @param mixed $params
     * @return unknown_type
     */
    public static function generate($params) {
        return parent::generatehelper('TurningTechEscrow', $params);
    }
    /**
     * build a DTO for this escrow item
     * 
     * @return unknown_type
     */
    public function getdata() {
        $data = new stdClass();
        $data->courseid = $this->courseid;
        $data->deviceid = $this->deviceid;
        $data->itemid = $this->itemid;
        $data->points_earned = $this->points_earned ? $this->points_earned : 0;
        $data->points_possible = $this->points_possible ? $this->points_possible : 0;
        $data->migrated = ($this->migrated ? 1 : 0);
        if (isset($this->id)) {
            $data->id = $this->id;
        }
        if (isset($this->created)) {
            $data->created = $this->created;
        }
        return $data;
    }
}