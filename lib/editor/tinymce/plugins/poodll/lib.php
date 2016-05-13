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

defined('MOODLE_INTERNAL') || die();

/**
 * Plugin for PoodLL Anywhere button.
 *
 * @package   tinymce_poodll
 * @copyright 2013 Justin Hunt
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class tinymce_poodll extends editor_tinymce_plugin {
    /** @var array list of buttons defined by this plugin */
    protected $buttons = array('poodllaudiomp3','poodllaudiored5','poodllvideo','poodllwhiteboard','poodllsnapshot');

    protected function update_init_params(array &$params, context $context,
            array $options = null) {

		global $USER;

		//use tinymce/poodll:visible capability
		//If they don't have permission don't show it
		if(!has_capability('tinymce/poodll:visible', $context) ){
			return;
		 }
		 
		 //if this textarea allows no files, we also bail
		 if (!isset($options['maxfiles']) || $options['maxfiles'] == 0) {
                return;
        }
	
		//add icons to editor if the permissions are all ok
		$recorders = array('audiomp3','audiored5','video','whiteboard','snapshot');
		$allowedrecorders =  $this->get_config('recorderstoshow');
		if(!empty($allowedrecorders)){
			$allowedrecorders = explode(',',$allowedrecorders);
			foreach($recorders as $recorder){
				if((array_search('show_' . $recorder,$allowedrecorders)!==false) && has_capability('tinymce/poodll:allow' . $recorder, $context)){
					$this->add_button_after($params, 3, 'poodll' . $recorder, 'image');
				}
			}
		}


        // Add JS file, which uses default name.
        $this->add_js_plugin($params);
    }
}
