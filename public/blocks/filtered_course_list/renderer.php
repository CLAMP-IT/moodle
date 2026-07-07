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
 * This file defines the renderer for the Filtered course list block.
 *
 * @package    block_filtered_course_list
 * @copyright  2016 CLAMP
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_filtered_course_list\output;

defined('MOODLE_INTERNAL') || die;

require_once(dirname(__FILE__) . '/locallib.php');

/**
 * Renderer for the Filtered course list block.
 *
 * @package    block_filtered_course_list
 * @copyright  2016 CLAMP
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class renderer extends \plugin_renderer_base {
    /**
     * Render HTML for the "All courses" link
     *
     * @param footer $footer A footer object
     * @return string The rendered html
     */
    protected function render_footer(footer $footer) {
        $data = $footer->export_for_template($this);
        return $this->render_from_template('block_filtered_course_list/footer', $data);
    }

    /**
     * Render HTML for list item
     *
     * @param list_item $listitem
     * @return string The rendered html
     */
    protected function render_list_item(list_item $listitem) {
        $data = $listitem->export_for_template($this);
        return $this->render_from_template('block_filtered_course_list/list_item', $data);
    }

    /**
     * Render HTML for icon
     *
     * @param icon $icon
     * @return string The rendered html
     */
    protected function render_icon(icon $icon) {
        $data = $icon->export_for_template($this);
        return $this->render_from_template('block_filtered_course_list/icon', $data);
    }

    /**
     * Render HTML for rubric
     *
     * @param rubric $rubric
     * @return string The rendered html
     */
    protected function render_rubric(rubric $rubric) {
        $data = $rubric->export_for_template($this);
        return $this->render_from_template('block_filtered_course_list/rubric', $data);
    }

    /**
     * Render HTML for content
     *
     * @param content $content
     * @return string The rendered html
     */
    protected function render_content(content $content) {
        $data = $content->export_for_template($this);
        return $this->render_from_template('block_filtered_course_list/content', $data);
    }
}
