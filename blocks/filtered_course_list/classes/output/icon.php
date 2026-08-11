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
 * Contains class block_filtered_course_list\output\icon
 *
 * @package    block_filtered_course_list
 * @copyright  2025 CLAMP
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_filtered_course_list\output;

/**
 * Helper class for rendering icons
 *
 * @package    block_filtered_course_list
 * @copyright  2016 CLAMP
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class icon implements \renderable, \templatable {
    /** @var int course id */
    public $id;
    /** @var bool is favourite */
    public $isfavourite;
    /** @var str Font awesome class to use for item icon */
    public $icon;
    /** @var str Title for screen reader */
    public $title;

    /**
     * Constructor
     *
     * @param int $id course id
     * @param bool $isfavourite is favourite
     * @param str $icon Font-awesome class to use for icon
     * @param str $title The title attribute for the icon link
     */
    public function __construct($id, $isfavourite, $icon, $title) {
        $this->id = $id;
        $this->isfavourite = $isfavourite;
        $this->icon = $icon;
        $this->title = $title;
    }

    /**
     * Export the object data for use by a template
     *
     * @param renderer_base $output A renderer_base object
     * @return array $data Template-ready data
     */
    public function export_for_template(\renderer_base $output) {
        $data = [
            'id' => $this->id,
            'isfavourite' => $this->isfavourite,
            'icon' => $this->icon,
            'title' => $this->title,
        ];
        return $data;
    }
}
