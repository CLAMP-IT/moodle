<?php
global $CFG;

require_once($CFG->dirroot . '/mod/turningtech/lib/types/TurningModel.php');

/**
 * handles transactions with the gradebook escrow
 * @author jacob
 *
 */
class TurningTechEscrow extends TurningModel {
    // DB fields used only by this model
    protected $deviceid;
    protected $courseid;
    protected $itemid;
    protected $points_possible;
    protected $points_earned;
    protected $migrated;

    public $tablename = 'turningtech_escrow';
    public $classname = 'TurningTechEscrow';
    /**
     * constructor
     * @return unknown_type
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * fetch an instance
     * @param $params
     * @return unknown_type
     */
    public static function fetch($params) {
        return parent::fetchHelper('turningtech_escrow', 'TurningTechEscrow', $params);
    }

    /**
     * generator function
     * @param $params
     * @return unknown_type
     */
    public static function generate($params) {
        return parent::generateHelper('TurningTechEscrow', $params);
    }


    /**
     * build a DTO for this escrow item
     * @return unknown_type
     */
    public function getData() {
        $data                  = new stdClass();
        $data->courseid        = $this->courseid;
        $data->deviceid        = $this->deviceid;
        $data->itemid          = $this->itemid;
        $data->points_earned   = $this->points_earned ? $this->points_earned : 0;
        $data->points_possible = $this->points_possible ? $this->points_possible : 0;
        $data->migrated        = ($this->migrated ? 1 : 0);
        if (isset($this->id)) {
            $data->id = $this->id;
        }
        if (isset($this->created)) {
            $data->created = $this->created;
        }
        return $data;
    }

}
?>