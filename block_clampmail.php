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
 * Block definition.
 *
 * @package   block_clampmail
 * @copyright 2012 Louisiana State University
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Block definition.
 *
 * @package   block_clampmail
 * @copyright 2012 Louisiana State University
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_clampmail extends block_list {
    /**
     * Set the block title.
     */
    public function init() {
        $this->title = get_string('pluginname', 'block_clampmail');
    }

    /**
     * Set the applicable block formats.
     *
     * @return array
     */
    public function applicable_formats() {
        return array('site' => false, 'my' => false, 'course' => true, 'mod' => true);
    }

    /**
     * Whether the block is configurable.
     *
     * @return boolean
     */
    public function has_config() {
        return true;
    }

    /**
     * Generate and return the block content.
     *
     * @return string
     */
    public function get_content() {
        global $COURSE, $OUTPUT;

        if ($this->content !== null) {
            return $this->content;
        }

        $context = context_course::instance($COURSE->id);

        $this->content = new stdClass;
        $this->content->items = block_clampmail\navigation::get_links($COURSE->id, $context);
        $this->content->icons = block_clampmail\navigation::get_icons($context);
        $this->content->footer = '';

        return $this->content;
    }
}
