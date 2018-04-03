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
 * @package   block_clampmail
 * @copyright 2017 Collaborative Liberal Arts Moodle Project
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

function block_clampmail_migrate_settings() {
    // Existing settings.
    $roleselection = get_config('moodle', 'block_clampmail_roleselection');
    $prependclass  = get_config('moodle', 'block_clampmail_prepend_class');
    $receipt       = get_config('moodle', 'block_clampmail_receipt');

    // Copy to new settings.
    set_config('roleselection', $roleselection, 'block_clampmail');
    set_config('prepend_class', $prependclass, 'block_clampmail');
    set_config('receipt', $receipt, 'block_clampmail');

    // Remove existing settings.
    unset_config('block_clampmail_roleselection');
    unset_config('block_clampmail_prepend_class');
    unset_config('block_clampmail_receipt');
}
