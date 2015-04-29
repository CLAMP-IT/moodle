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
 * This file is used for DB modelling
 * @author jacob
 * @copyright  2012 Turning Technologies
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
/**
 * This file is used for DB modelling
 * @author jacob
 * @package mod_turningtech
 * @copyright  2012 Turning Technologies
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class TurningModel {
    // DB fields used by all models.
    /**
     * @var unknown_type
     */
    protected $id;
    /**
     * @var unknown_type
     */
    protected $created;
    // Keeps track of whether the model needs saving.
    /**
     * @var unknown_type
     */
    protected $save;
    // Stores child classes' DB table.
    /**
     * @var unknown_type
     */
    protected $tablename;
    /**
     * abstract constructor
     * 
     * @return unknown_type
     */
    public function __construct() {
        $save = false;
    }
    /**
     * fetches instances of child classes
     * 
     * @param mixed $table
     * @param mixed $classname
     * @param mixed $params
     * @param mixed $orders
     * @param mixed $limits
     * @return unknown_type
     */
    protected static function fetchhelper($table, $classname, $params, $orders = null, $limits = null) {
        global $DB, $CFG;
        if (! is_null($params) && is_array($params)) {
            $wheresql = "";
            foreach ($params as $field => $value) {
                if ($value || $value == 0) {
                    if ($field == 'migrated' && $value == "") {
                        $value = 'false';
                    }
                    $wheresql .= "AND $field = '$value' ";
                }
            }
            $wheresql = "WHERE" . substr($wheresql, 3);
        } else {
            $wheresql = "";
        }
        if (is_array($orders) && count($orders)) {
            $ordersql = "";
            foreach ($orders as $field => $dir) {
                $dir = strtoupper($dir);
                if (in_array($dir, array ("ASC", "DESC"))) {
                    $ordersql .= ", $field $dir";
                }
            }
            $ordersql = "ORDER BY" . substr($ordersql, 1);
        } else {
            $ordersql = '';
        }
        if (is_array($limits) && count($limits)) {
            // Fix for postgresql compatibility.
            $limitsql = "LIMIT {$limits['end']} OFFSET {$limits['start']}";
        } else {
            $limitsql = "";
        }
        $sql = "SELECT * FROM {$CFG->prefix}$table $wheresql $ordersql $limitsql";
        if ($data = $DB->get_record_sql($sql)) {
            $instance = new $classname();
            self::setproperties($instance, $data);
            return $instance;
        } else {
            return false;
        }
        return false;
    }
    /**
     * sets object fields
     * 
     * @param mixed $instance
     * @param mixed $properties
     * @return unknown_type
     */
    protected static function setproperties(&$instance, $properties) {
        $properties = (array)$properties;
        foreach ($properties as $field => $value) {
            $instance->$field = $value;
        }
    }
    /**
     * generator function
     * @param mixed $classname
     * @param mixed $params
     * @return unknown_type
     */
    protected static function generatehelper($classname, $params) {
        $instance = new $classname();
        $params = (array)$params;
        $params['saved'] = false;
        self::setproperties($instance, $params);
        return $instance;
    }
    /**
     * all-purpose set function
     * 
     * @param mixed $fieldname
     * @param mixed $value
     * @return unknown_type
     */
    public function setfield($fieldname, $value) {
        $this->$fieldname = $value;
        $this->saved = false;
    }
    /**
     * all-purpose getter function
     * 
     * @param mixed $fieldname
     * @return unknown_type
     */
    public function getfield($fieldname) {
        return $this->$fieldname;
    }
    /**
     * get the id or return false if not yet saved
     * 
     * @return unknown_type
     */
    public function getid() {
        return (isset($this->id) ? $this->id : false);
    }
    /**
     * saves the model to the database (insert and update)
     * 
     * @return unknown_type
     */
    public function save() {
        global $DB;
        $result = false;
        if (!$this->id) {
            $this->created = time();
            $data = $this->getdata();
            $result = $DB->insert_record($this->tablename, $data);
            if ($result) {
                $this->id = $result;
            }
        } else {
            $result = $DB->update_record($this->tablename, $this->getdata());
        }
        $this->saved = ($result ? true : false);
        return $result;
    }
    /**
     * builds a DTO suitable for saving to the DB
     * 
     * @return unknown_type
     */
    public abstract function getdata();
    /**
     * builds the WHERE clause of a query from an array of fields
     * 
     * @param mixed $params
     * @return unknown_type
     */
    public static function buildwhereclause($params) {
        $conditions = array ();
        foreach ($params as $field => $value) {
            if ($value != null) {
                $conditions[] = "{$field} = {$value}";
            }
        }
        return implode(' AND ', $conditions);
    }
}
