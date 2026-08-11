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
 * Contains class block_filtered_course_list\output\renderable_rubric
 *
 * @package    block_filtered_course_list
 * @copyright  2025 CLAMP
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_filtered_course_list\output;

/**
 * Helper class for rendering rubrics.
 *
 * @package    block_filtered_course_list
 * @copyright  2016 CLAMP
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class renderable_rubric implements \renderable, \templatable {
    /** @var object Rubric */
    public $rubric;
    /** @var string Block instance id */
    public $instid;
    /** @var string Rubric key */
    public $key;

    /**
     * Constructor
     *
     * @param array $rubric An original rubric object
     * @param string $instid The instance id of the calling block
     * @param string $key The array index of the rubric
     */
    public function __construct($rubric, $instid, $key) {
        $this->rubric = $rubric;
        $this->instid = $instid;
        $this->key = $key;
    }

    /**
     * Export the object data for use by a template
     *
     * @param renderer_base $output A renderer_base object
     * @return array $data Template-ready data
     */
    public function export_for_template(\renderer_base $output) {
        $itemdata = array_map(function ($item) use ($output) {
            $renderable = new list_item($item, $this->rubric->config);
            $export = $renderable->export_for_template($output);
            return $export;
        }, $this->rubric->courses);
        $key = $this->key + 1;
        $hash = 'block-fcl_' . md5("{$this->instid}{$key}{$this->rubric->title}");
        if (
            array_key_exists($hash, $_COOKIE)
                && property_exists($this->rubric->config, 'persistentexpansion')
                && $this->rubric->config->persistentexpansion
        ) {
            $this->rubric->expanded = ($_COOKIE[$hash] == 'expanded') ? 'expanded' : 'collapsed';
        }
        $exp = ($this->rubric->expanded == 'expanded') ? 'true' : 'false';
        $hidden = ($this->rubric->expanded != 'expanded') ? 'true' : 'false';
        $data = [
            'state'  => $this->rubric->expanded,
            'exp'    => $exp,
            'label'  => $this->rubric->title,
            'hidden' => $hidden,
            'instid' => $this->instid,
            'key'    => $key,
            'items'  => array_values($itemdata),
            'hash'   => $hash,
        ];
        return $data;
    }
}
