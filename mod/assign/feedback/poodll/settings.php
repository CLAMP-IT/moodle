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
 * This file defines the admin settings for this plugin
 *
 * @package   assignfeedback_poodll
 * @copyright 2013 Justin Hunt {@link http://www.poodll.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

//some constants for poodll feedback
if(!defined('FPS_REPLYVOICE')){
	define('FPS_REPLYMP3VOICE',0);
	define('FPS_REPLYVOICE',1);
	define('FPS_REPLYVIDEO',2);
	define('FPS_REPLYWHITEBOARD',3);
	define('FPS_REPLYSNAPSHOT',4);
}

	//enable by default
	$settings->add(new admin_setting_configcheckbox('assignfeedback_poodll/default',
                   new lang_string('default', 'assignfeedback_poodll'),
                   new lang_string('default_help', 'assignfeedback_poodll'), 1));
                   

	//Recorders
    $rec_options = array( FPS_REPLYMP3VOICE => get_string("replymp3voice", "assignfeedback_poodll"), 
				FPS_REPLYVOICE => get_string("replyvoice", "assignfeedback_poodll"), 
				FPS_REPLYVIDEO => get_string("replyvideo", "assignfeedback_poodll"),
				FPS_REPLYWHITEBOARD => get_string("replywhiteboard", "assignfeedback_poodll"),
				FPS_REPLYSNAPSHOT => get_string("replysnapshot", "assignfeedback_poodll"));
	$rec_defaults = array(FPS_REPLYMP3VOICE  => 1);
	$settings->add(new admin_setting_configmulticheckbox('assignfeedback_poodll/allowedrecorders',
						   get_string('allowedrecorders', 'assignfeedback_poodll'),
						   get_string('allowedrecordersdetails', 'assignfeedback_poodll'), $rec_defaults,$rec_options));
						   
	//show current feedback on feedback form
	$yesno_options = array( 0 => get_string("no", "assignfeedback_poodll"), 
				1 => get_string("yes", "assignfeedback_poodll"));
	$settings->add(new admin_setting_configselect('assignfeedback_poodll/showcurrentfeedback', 
					new lang_string('showcurrentfeedback', 'assignfeedback_poodll'), 
					new lang_string('showcurrentfeedbackdetails', 'assignfeedback_poodll'), 1, $yesno_options));

