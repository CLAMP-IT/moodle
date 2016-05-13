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
 * TinyMCE PoodLL Anywhere version details.
 *
 * @package   tinymce_poodll
 * @copyright 2013 Justin Hunt
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

// The current plugin version (Date: YYYYMMDDXX).
$plugin->version   = 2015121102;
$plugin->requires  = 2015051100;
$plugin->component = 'tinymce_poodll';
//beta
$plugin->maturity  = MATURITY_STABLE;
// Human readable version informatiomn
$plugin->release   = '1.0.8 (Build 2015121102)';
$plugin->dependencies = array('filter_poodll' => 2015121101);