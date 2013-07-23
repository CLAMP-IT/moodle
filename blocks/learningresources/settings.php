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
 * @package block_learningresources
 * @copyright 2013 Smith College ITS
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/* global setting: link target */
$settings->add(new admin_setting_configcheckbox('learningresources/new_window',
                                                get_string('openlinkssetting', 'block_learningresources'),
                                                '',
                                                'learningresources',
                                                1));

/* global setting: raw list of resources */
$settings->add(new admin_setting_configtextarea('learningresources/link_list',
                                                get_string('listsetting', 'block_learningresources'),
                                                get_string('listsettingdesc', 'block_learningresources'),
                                                '',
                                                PARAM_RAW,
                                                120,
                                                20));
