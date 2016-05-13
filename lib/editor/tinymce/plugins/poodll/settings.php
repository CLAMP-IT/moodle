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
 * PoodLL Anywhere settings.
 *
 * @package   tinymce_poodll
 * @copyright 2013 Justin Hunt {@link http://www.poodll.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {
	  

	// Recorder settings
	$recoptions = array('show_audiomp3' => new lang_string('show_audiomp3', 'tinymce_poodll'),
					'show_audiored5' => new lang_string('show_audiored5', 'tinymce_poodll'),
					'show_video' => new lang_string('show_video', 'tinymce_poodll'),
					'show_whiteboard' => new lang_string('show_whiteboard', 'tinymce_poodll'),
					'show_snapshot' => new lang_string('show_snapshot', 'tinymce_poodll'));
	$recoptiondefaults = array('show_audiomp3' => 1,'show_audiored5' => 0,'show_video' => 1,'show_whiteboard' => 1,'show_snapshot' => 1);
	$settings->add(new admin_setting_configmulticheckbox('tinymce_poodll/recorderstoshow',
						   get_string('recorderstoshow', 'tinymce_poodll'),
						   get_string('recorderstoshowdetails', 'tinymce_poodll'), $recoptiondefaults,$recoptions));	
	


}
