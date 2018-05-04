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
 * Essay question type upgrade code.
 *
 * @package    filter
 * @subpackage poodll
 * @copyright  2016 Justin Hunt (@link http://poodll.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();


/**
 * Upgrade code for the poodll filter
 */
function xmldb_filter_poodll_upgrade($oldversion) {
    global $CFG;

    if ($oldversion < 2016071604) {

		$presets = \filter_poodll\poodllpresets::fetch_presets();
		$forinstall = array('fff','stopwatch','audiojs');
		$templateindex=0;
		foreach($presets as $preset){			
			if(in_array($preset['key'],$forinstall)){
				$templateindex++;
				//set the config
				\filter_poodll\poodllpresets::set_preset_to_config($preset,$templateindex);
			}
		}//end of for each presets	
	
        // poodllrecording savepoint reached
        upgrade_plugin_savepoint(true, 2016071604, 'filter', 'poodll');
    }

    if ($oldversion < 2017051301) {
        set_config('filter_poodll_recorderorder_audio',$CFG->filter_poodll_recorderorder);
        set_config('filter_poodll_recorderorder_video',$CFG->filter_poodll_recorderorder);
        set_config('filter_poodll_recorderorder_whiteboard',$CFG->filter_poodll_recorderorder);
        set_config('filter_poodll_recorderorder_snapshot',$CFG->filter_poodll_recorderorder);

        //  savepoint reached
        upgrade_plugin_savepoint(true, 2017051301, 'filter', 'poodll');
    }

    if($oldversion < 2017082601) {
        if (property_exists($CFG, 'filter_poodll_html5recorder_skin')) {
            $currentskin = $CFG->filter_poodll_html5recorder_skin;
            set_config('html5recorder_skin_audio', $currentskin, 'filter_poodll');
            set_config('html5recorder_skin_video', $currentskin, 'filter_poodll');
        }
        if (property_exists($CFG, 'filter_poodll_html5recorder_skinstyle_audio')) {
            $currentskin = $CFG->filter_poodll_html5recorder_skinstyle_audio;
            set_config('skinstyleaudio', $currentskin, 'filter_poodll');
        }
        if (property_exists($CFG, 'filter_poodll_html5recorder_skinstyle_video')) {
            $currentskin = $CFG->filter_poodll_html5recorder_skinstyle_video;
            set_config('skinstylevideo', $currentskin, 'filter_poodll');
        }
    }
    if($oldversion < 2017092402) {
     	if (property_exists($CFG, 'filter_poodll_recorderorder_audio')) {
            $currentaudio = $CFG->filter_poodll_recorderorder_audio;
            set_config('filter_poodll_recorderorder_audio', str_replace('mobile,media,','media,mobile,',$currentaudio));
             $currentvideo = $CFG->filter_poodll_recorderorder_video;
            set_config('filter_poodll_recorderorder_video', str_replace('mobile,media,','media,mobile,',$currentvideo));
    	}
    }
    return true;
}
