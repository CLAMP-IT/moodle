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
 * Display a list of resources in a block
 *
 * @package block_learningresources
 * @copyright 2013 Smith College ITS
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * The learning resources list
 *
 * Includes the raw list and an articulated array
 * with methods to modify particular settings,
 * build relevant hyperlinks, and more.
 *
 * @copyright 2013 Smith College ITS
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_learningresources_list {

    /**
     * The raw, flat list of resources
     *
     * One resource per line
     * Resource attributes are pipe-separated
     *
     * @var string
     */
    public $raw_list;

    /**
     * The "target" preference for all hyperlinks: "_blank" or "_self"
     *
     * @var string
     */
    public $link_target = '';

    /**
     * An array of resources parsed from the raw list
     *
     * @var array
     */
    public $lr_array = array();

    /**
     * An array of HTML-formatted links to resources
     *
     * By default the list includes only the links flagged as visible
     * after the defaults and instance config have applied
     *
     * @var array
     */
    public $html_array = array();

    /**
     * An HTML-formatted (unordered) list of the visible resource links
     *
     * @var string
     */
    public $html_list;

    /**
     * The array of keys to apply to the attributes of a resource from the raw list
     *
     * @var array
     */
    public $keys = array('text', 'url', 'show', 'id', 'position'); /* keys to apply to rows */

    /**
     * Build the learning resources object
     *
     * get the raw list from the global settings
     * get the target preference from the global settings
     * set the link_target property according to the global setting
     * break the raw list into an array of rows
     * break each row into an array of attributes for the resource
     * make sure the array is in the desired order
     */
    public function __construct() {
        $this->raw_list = get_config('learningresources', 'link_list');
        $this->link_target = get_config('learningresources', 'new_window');
        if ($this->link_target == 1) {
            $this->link_target = "_blank";
        } else {
            $this->link_target = "_self";
        }
        $rows = explode("\n", $this->raw_list);
        $this->parse_rows($rows);
        $this->sort_lr_array();
    }

    /**
     * Parse each row of the raw list
     *
     * ignore blank or ill-formed rows
     * delete newlines and whitespace at the end of each row
     * split the row using the pipe as a separator
     * use the list of $keys as keys for the elements of each row
     * add each row to an array of rows, using the id of the row as the key
     *
     * @param array $rows The array of rows from the raw list
     */
    public function parse_rows($rows) {
        foreach ($rows as $key => $row) {
            $row = rtrim($row);
            $row_items = explode('|', $row);
            array_push($row_items, $key);
            if (count($row_items) != count($this->keys)) {
                continue;
            }
            $keys_and_values = array_combine($this->keys, $row_items);
            $this->lr_array[$keys_and_values['id']] = $keys_and_values;
        }
    }

    /**
     * return the articulated array of learning resources
     */
    public function get_lr_array() {
        return $this->lr_array;
    }

    /**
     * set the visibility of an individual item in the array
     *
     * @param string $id A unique id for each resource
     * @param string $show Should be 'show' or 'hide' to indicate default preference
     */
    public function set_visibility($id, $show='show') {
        if ($show != 'show') {
            $show = 'hide';
        }
        $this->lr_array[$id]['show']=$show;
    }

    /**
     * return the array of HTML formatted links to resources
     *
     * @param string $visible Defaults to 'visible'
     */
    public function get_html_array($visible='visible') {
        foreach ($this->lr_array as $key => $row) {
            if (($visible=='visible') && ($row['show'] != 'show')) {
                continue;
            }
            $this->html_array[$key] = html_writer::link($row['url'], $row['text'], array('target'=>$this->link_target));
        }
        return $this->html_array;
    }

    /**
     * return the unordered list of links to resources
     */
    public function get_html_list() {
        $this->get_html_array();
        $this->html_list = html_writer::alist($this->html_array);
        return $this->html_list;
    }

    /**
     * sort the articulated array of resources according to the custom function
     */
    public function sort_lr_array() {
        uasort($this->lr_array, array($this, 'my_cmp'));
    }

    /**
     * compare the value of two resources 'position' attributes
     *
     * @param array $a first array to compare
     * @param array $b second array to compare
     */
    public function my_cmp($a, $b) {
        if ($a['position'] == $b['position']) {
            return 0;
        }
        return ($a['position'] < $b['position']) ? -1 : 1;
    }
}
