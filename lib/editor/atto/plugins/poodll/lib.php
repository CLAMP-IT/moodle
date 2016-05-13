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
 * Atto text editor integration version file.
 *
 * @package    atto_poodll
 * @copyright  2013 Justin Hunt <poodllsupport@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Initialise this plugin
 * @param string $elementid
 */
function atto_poodll_strings_for_js() {
    global $PAGE;

    $PAGE->requires->strings_for_js(array('insert',
                                          'cancel',
                                          'dialogtitle',
                                          'audiored5_desc','audiomp3_desc','video_desc','whiteboard_desc','snapshot_desc'),
                                    'atto_poodll');
}

/**
 * Return the js params required for this module.
 * @return array of additional params to pass to javascript init function for this module.
 */
function atto_poodll_params_for_js($elementid, $options, $fpoptions) {
	global $USER, $COURSE;
	//coursecontext
	$coursecontext=context_course::instance($COURSE->id);	
	
	//usercontextid
	$usercontextid=context_user::instance($USER->id)->id;
	$disabled=false;
	
	//config our array of data
	$params = array();
	$params['usercontextid'] = $usercontextid;

		//If they don't have permission don't show it
		if(!has_capability('atto/poodll:visible', $coursecontext) ){
			$disabled=true;
		 }
		 
		 //if this textarea allows no files, we also bail
		 if (!isset($options['maxfiles']) || $options['maxfiles'] == 0) {
                $disabled=true;
        }
        
        //add our disabled param
        $params['disabled'] = $disabled;
        
        //add our use whiteboard option
        $params['usewhiteboard'] = get_config('atto_poodll','usewhiteboard');
	
		//add icons to editor if the permissions are all ok
		$recorders = array('audiomp3','audiored5','video','whiteboard','snapshot');
		$allowedrecorders =  get_config('atto_poodll','recorderstoshow');
		if(!empty($allowedrecorders)){
			$allowedrecorders = explode(',',$allowedrecorders);
			foreach($recorders as $recorder){
				if((array_search('show_' . $recorder,$allowedrecorders)!==false) && has_capability('atto/poodll:allow' . $recorder, $coursecontext)){
					$params[$recorder]=true;
				}
			}
		}

    return $params;
}

