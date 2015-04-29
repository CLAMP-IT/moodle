<?php

// This file is part of the Echo360 Moodle Plugin - http://moodle.org/
//
// The Echo360 Moodle Plugin is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// The Echo360 Moodle Plugin is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with the Echo360 Moodle Plugin.  If not, see <http://www.gnu.org/licenses/>.
 
/**
 * This is the base class for the Echo360 EchoCenter Block
 *
 * This block provides the block view interface as well as the moodle course page with the EchoCenter embedded and the
 * direct link to the EchoCenter page (for open in new window).
 * It is uses the configuration information from the echo360_configuration block.
 *
 * @package    block                                                    
 * @subpackage echo360_echocenter
 * @copyright  2011 Echo360 Inc
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later 
 */

defined('MOODLE_INTERNAL') || die;

/**
 * This is the base class for the Echo360 EchoCenter Block
 *
 * This block provides a block view and uses the settings from the echo360_configuration block.
 *
 * @copyright 2011 Echo360 Inc
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later 
 */
class block_echo360_echocenter extends block_base {

    /**
     * Return the version and title as properties in this class.
     */
    function init() {
        $this->version = 2011062001;
        $this->title = get_string('echocenter', 'block_echo360_echocenter');
    }

    /**
     * It does not make sense to have multiples of this block because
     * they will all link to the same EchoCenter page.
     *
     * @return boolean
     */
    function instance_allow_multiple() {
        return FALSE;
    }

    /**
     * This block has a configuration page
     * 
     * @return boolean
     */
    function has_config() {
        return FALSE;
    }
    
    /**
     * There is no content in the header and the block looks cleaner without it.
     * 
     * @return boolean
     */
    function hide_header() {
        // what does this look like?
        return TRUE;
    }

    /**
     * Only allow adding this block to a course page. 
     * 
     * @return array
     */
    function applicable_formats() {
        return array('course-view' => TRUE, 
                        'all' => FALSE);
    }

    /**
     * No configuration for this block
     * 
     * @return boolean
     */
    function instance_allow_config() {
        return FALSE;
    }

    /**
     * Load the default title or the user set one
     */
    function specialization() {

        // load userdefined title and make sure it's never empty
        if (empty($this->config->title)) {
            $this->title = get_string('echocenter', 'block_echo360_echocenter');
        } else {
            $this->title = $this->config->title;
        }
    }

    /**
     * Load the content of this block
     *
     * @return stdClass
     */
    function get_content() {
        global $CFG, $COURSE;

        // set the html to use for the block and the block footer
        $this->content->text = '<div class="block_echo360_echocenter"><a href="' . $CFG->wwwroot . '/blocks/echo360_echocenter/echocenter_frame.php?id=' . $COURSE->id . '"><img src="'. $CFG->wwwroot .'/blocks/echo360_echocenter/pix/echo360_logo_160x60.png" border="0" alt="Echo360"/><br/>' . get_string('echocenter_link', 'block_echo360_echocenter') . '</a></div>';
        $this->content->footer = '';
        return $this->content;
    }

    /**
     * 180px is the preferred size of this block.
     *
     * @return int
     */
    function preferred_width() {
        return 180;
    }

    
}

?>
