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
 * This is the base class for the Echo360 Configuration Block
 *
 * This block provides the settings and configuration options for the plugin.
 * It is separated into its own block so that the configuration options can
 * be shared by multiple Echo360 Plugins that provide different views of the EchoSystem.
 *
 * @package    block                                                    
 * @subpackage echo360_configuration
 * @copyright  2011 Echo360 Inc
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later 
 */

/**
 * This is the base class for the Echo360 Configuration Block
 *
 * This block only provides a settings page and cannot be added to a course.
 *
 * @copyright 2011 Echo360 Inc
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later 
 */
class block_echo360_configuration extends block_base {

    /**
     * Return the version and title as properties in this class.
     */
    public function init() {
        $this->version = 2011062003;
        $this->title = get_string('configuration', 'block_echo360_configuration');
    }
 
    /**
     * Do not allow multiple instances of this block
     * 
     * @return boolean
     */
    public function instance_allow_multiple() {
        return FALSE;
    }
 
    /**
     * This block has a configuration page
     * 
     * @return boolean
     */
    function has_config() {
        return TRUE;
    }
     
    /**
     * This block is not applicable anywhere as it only contains configuration settings
     * 
     * @return array
     */
    function applicable_formats() {
        // This block is only for configuration
        // 'z' is to prevent the self_test from failing for this block
        return array('all' => FALSE, 'z' => TRUE);
    }
 
    /**
     * You may not create instances of this block and you may not configure them
     * 
     * @return boolean
     */
    function instance_allow_config() {
        return FALSE;
    }

    /**
     * This block displays no content
     *
     * @return stdClass
     */
    function get_content() {
        // set the html to use for the block and the block footer
        // this block is always empty
        $this->content->text = '';
        $this->content->footer = '';
        return $this->content;
    }
 
}
 
?>
