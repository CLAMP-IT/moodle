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
 * Contains class block_filtered_course_list\output\content
 *
 * @package    block_filtered_course_list
 * @copyright  2025 CLAMP
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_filtered_course_list\output;

/**
 * Helper class for the main block content
 *
 * @package    block_filtered_course_list
 * @copyright  2016 CLAMP
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class content implements \renderable, \templatable {
    /** @var array Rubrics to display */
    public $rubrics = [];
    /** @var string Block instance id */
    public $instid;

    /**
     * Constructor
     *
     * @param string $instid The instance id of the calling block
     * @param array $rubrics The list of rubrics to display
     */
    public function __construct($instid, $rubrics = []) {
        $this->rubrics = $rubrics;
        $this->instid = $instid;
    }

    /**
     * Export the object data for use by a template
     *
     * @param renderer_base $output A renderer_base object
     * @return array $data Template-ready data
     */
    public function export_for_template(\renderer_base $output) {
        $rubricdata = array_map(function ($rubric, $key) use ($output) {
            $rubrichelper = new renderable_rubric($rubric, $this->instid, $key);
            $export = $rubrichelper->export_for_template($output);
            return $export;
        }, $this->rubrics, array_keys($this->rubrics));
        $data = [
            'instid'  => $this->instid,
            'rubrics' => $rubricdata,
            'persist' => $this->rubrics[0]->config->persistentexpansion,
        ];
        return $data;
    }
}
