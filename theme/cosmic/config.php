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
 * Configuration for Cosmic, a customization of the Rocket theme.
 *
 * @package    theme
 * @subpackage cosmic
 * @copyright  2012 Lafayette College ITS
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$THEME->name = 'cosmic';

$THEME->parents = array('rocket','base');

$THEME->sheets = array(
    'cosmic'
);

$THEME->layouts = array(
    // Main course page
    'course' => array(
        'file' => 'general.php',
        'regions' => array('side-pre', 'side-post','search'),
        'defaultregion' => 'side-post',
        'options' => array('langmenu'=>true),
    ),
);

$THEME->enable_dock = true;

$THEME->csspostprocess = 'rocket_process_css';

$THEME->rendererfactory = 'theme_overridden_renderer_factory';
