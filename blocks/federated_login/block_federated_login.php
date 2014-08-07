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
 * @package block_federated_login
 * @copyright 2014 Smith College ITS
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Require the file that defines the federated login object
 */
require_once(dirname(__FILE__) . '/federated_login.class.php');

/**
 * Define the class for the federated login block
 *
 * Extend the block_base class. Moodle provides lots of magic.
 * @copyright 2014 Smith College ITS
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_federated_login extends block_base {

    /**
     * Title for the block on the course page
     */
    public function init() {
        $this->title = get_string('federated_login', 'block_federated_login');
    }

    /**
     * Custom titles for each block instance
     */
    public function specialization() {
        $this->title = isset($this->config->title) ? $this->config->title : get_string('federated_login', 'block_federated_login');
    }

    /**
     * Content for the block on the course page
     */
    public function get_content() {

        if ( $this->content !== null ) {
            return $this->content;
        }

        $login = new block_federated_login_handler();

        $this->content = new stdClass;
        $this->content->text = $login->get_content();

        return $this->content;
    }

    /**
     * tell Moodle to look for global settings
     */
    public function has_config() {
        return true;
    }
}
