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
 * @package   atto_poodll
 * @copyright 2014 Justin Hunt {@link http://www.poodll.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

$ADMIN->add('editoratto', new admin_category('atto_poodll', new lang_string('pluginname', 'atto_poodll')));

$settings = new admin_settingpage('atto_poodll_settings', new lang_string('settings', 'atto_poodll'));
if ($ADMIN->fulltree) {


	// Recorder settings
	$recoptions = array('show_audiomp3' => new lang_string('show_audiomp3', 'atto_poodll'),
					'show_video' => new lang_string('show_video', 'atto_poodll'),
					'show_whiteboard' => new lang_string('show_whiteboard', 'atto_poodll'),
					'show_snapshot' => new lang_string('show_snapshot', 'atto_poodll'),
					'show_widgets' => new lang_string('show_widgets', 'atto_poodll'));
					
	$recoptiondefaults = array('show_audiomp3' => 1,'show_video' => 1,'show_whiteboard' => 1,'show_snapshot' => 1,'show_widgets' => 1);
	$settings->add(new admin_setting_configmulticheckbox('atto_poodll/recorderstoshow',
						   get_string('recorderstoshow', 'atto_poodll'),
						   get_string('recorderstoshowdetails', 'atto_poodll'), $recoptiondefaults,$recoptions));	
	
	//PoodLL Whiteboard
	$settings->add(new admin_setting_heading('atto_poodll/whiteboards', get_string('whiteboardheading', 'atto_poodll'), ''));
	$wboptions = array('drawingboard' => 'Drawing Board(js)', 'literallycanvas' => 'Literally Canvas(js)');
	$settings->add(new admin_setting_configselect('atto_poodll/usewhiteboard', get_string('usewhiteboard', 'atto_poodll'), '', 'drawingboard', $wboptions));

	
}
