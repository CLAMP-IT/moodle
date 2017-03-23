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
 * filter poodll installation tasks
 *
 * @package    filter_poodll
 * @copyright  2016 Justin Hunt {@link http://poodll.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Install the plugin.
 */
function xmldb_filter_poodll_install() {
    $presets = \filter_poodll\poodllpresets::fetch_presets();
	$forinstall = array('fff','flowplayer','mediaelementvideo','jwplayer','videojs','nativevideo','audiojs','mediaelementaudio','nativeaudio','youtubelightbox','stopwatch','tabs','tabitem','accordian','accordianitem');
	$templateindex=0;
	foreach($presets as $preset){			
		if(in_array($preset['key'],$forinstall)){
			$templateindex++;
			//set the config
			\filter_poodll\poodllpresets::set_preset_to_config($preset,$templateindex);
		}
	}//end of for each presets	
	
	//Set the handlers
	set_config('handlemp4', 1, 'filter_poodll');
	set_config('handlemp3', 1, 'filter_poodll');
	set_config('useplayermp4','fff','filter_poodll');
	set_config('useplayermp3','audiojs','filter_poodll');
}
