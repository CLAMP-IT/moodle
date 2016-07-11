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
 * Annotate block definition
 *
 * @package   block_annotate
 * @copyright Textensor Ltd.
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once($CFG->dirroot . '/blocks/annotate/lib.php');

/**
 * Annotate block class
 *
 * @copyright Textensor Ltd.
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_annotate extends block_base {

    /**
     * Sets the block title
     *
     * @return void
     */
    public function init() {
        $this->title = get_string ( "pluginname", "block_annotate" );
    }

    /**
     * Block has a settings.php file for global data
     *
     * @return bool
     */
    public function has_config() {
        return true;
    }

    /**
     * Only 1 block instance per page
     *
     * @return bool
     */
    public function instance_allow_multiple() {
        return false;
    }

    /**
     * Block to be added only within a course
     *
     * @return array
     */
    public function applicable_formats() {
        return array (
                'course-view-*' => true
        );
    }

    /**
     * Creates the blocks main content
     *
     * @return string
     */
    public function get_content() {
        global $CFG;
        if ($this->content !== null) {
            return $this->content;
        }
        $this->content = new stdClass ();
        $this->content->text = get_string ( "access_set_to_msg", "block_annotate" );
        $this->content->footer = '';
        if (! empty ( $this->config )) {
            if ($this->config->access == 'group') {
                $this->content->text .= get_string ( "config_group_access", "block_annotate" );
                $this->content->text .= "<p>" . get_string ( "access_shareuser_msg", "block_annotate" );
                if ($this->config->shareuser != '') {
                    $shareuser = block_annotate_process_usr_input ( $this->config->shareuser );
                    $this->content->text .= '<input type="hidden" name="annotate_shareuser" id="annotate_shareuser" value="' . $shareuser . '"/>';
                    $this->content->text .= htmlspecialchars ( $this->config->shareuser, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8' );
                } else {
                    $this->content->text .= get_string ( "access_shareuser_msg_dft", "block_annotate" );
                }
            } else {
                $this->content->text .= get_string ( "config_individual_access", "block_annotate" );
            }
        } else {
            $this->content->text .= get_string ( "access_default_msg", "block_annotate" );
        }

        $jsmodule = array (
                'name' => 'block_annotate',
                'fullpath' => '/blocks/annotate/module.js',
                'requires' => array (),
                'strings' => array ()
        );
        $arguments = array (
                'pdf-doc-xls-ppt-jpg',
                $CFG->wwwroot
        );

        if ( has_capability( 'block/annotate:accessannotate', $this->context ) ) {
            $this->page->requires->js_init_call ( 'M.block_annotate.init', $arguments, false, $jsmodule );
        }
        return $this->content;
    }
}
