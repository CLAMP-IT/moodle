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
 * @package   assignsubmission_onlinepoodll
 * @copyright 2012 Justin Hunt {@link http://www.poodll.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
//some constants for poodll feedback
if(!defined('OP_REPLYVOICE')){
	define('OP_REPLYMP3VOICE',0);
	define('OP_REPLYVOICE',1);
	define('OP_REPLYVIDEO',2);
	define('OP_REPLYWHITEBOARD',3);
	define('OP_REPLYSNAPSHOT',4);
	define('OP_REPLYTALKBACK',5);
}

	//enable by default
	$settings->add(new admin_setting_configcheckbox('assignsubmission_onlinepoodll/default',
                   new lang_string('default', 'assignsubmission_onlinepoodll'),
                   new lang_string('default_help', 'assignsubmission_onlinepoodll'), 0));
                   

	//Recorders
   $rec_options = array( OP_REPLYMP3VOICE => get_string("replymp3voice", "assignsubmission_onlinepoodll"), 
				OP_REPLYVOICE => get_string("replyvoice", "assignsubmission_onlinepoodll"), 
				OP_REPLYVIDEO => get_string("replyvideo", "assignsubmission_onlinepoodll"),
				OP_REPLYWHITEBOARD => get_string("replywhiteboard", "assignsubmission_onlinepoodll"),
				OP_REPLYSNAPSHOT => get_string("replysnapshot", "assignsubmission_onlinepoodll"));
	$rec_defaults = array(OP_REPLYMP3VOICE  => 1, OP_REPLYVIDEO => 1 , OP_REPLYVOICE => 1,OP_REPLYWHITEBOARD => 1,OP_REPLYSNAPSHOT => 1);
	$settings->add(new admin_setting_configmulticheckbox('assignsubmission_onlinepoodll/allowedrecorders',
						   get_string('allowedrecorders', 'assignsubmission_onlinepoodll'),
						   get_string('allowedrecordersdetails', 'assignsubmission_onlinepoodll'), $rec_defaults,$rec_options));
						   
	//show current submission on submission form
	$yesno_options = array( 0 => get_string("no", "assignsubmission_onlinepoodll"), 
				1 => get_string("yes", "assignsubmission_onlinepoodll"));
	$settings->add(new admin_setting_configselect('assignsubmission_onlinepoodll/showcurrentsubmission', 
					new lang_string('showcurrentsubmission', 'assignsubmission_onlinepoodll'), 
					new lang_string('showcurrentsubmissiondetails', 'assignsubmission_onlinepoodll'), 1, $yesno_options));

	//Settings for video recordings
    $settings->add(new admin_setting_heading('assignsubmission_onlinepoodll/video_heading',
        get_string('setting_video_heading', 'assignsubmission_onlinepoodll'),
        get_string('setting_video_heading_details', 'assignsubmission_onlinepoodll')));


    //The size of the video player on the various screens
	$size_options = array('0' => new lang_string('shownovideo', 'assignsubmission_onlinepoodll'),
					'160' => '160x120', '320' => '320x240','480' => '480x360',
					'640' => '640x480','800'=>'800x600','1024'=>'1024x768');
				
	$settings->add(new admin_setting_configselect('assignsubmission_onlinepoodll/displaysize_single', 
						new lang_string('displaysizesingle', 'assignsubmission_onlinepoodll'), 
						new lang_string('displaysizesingledetails', 'assignsubmission_onlinepoodll'), '320', $size_options));

	$settings->add(new admin_setting_configselect('assignsubmission_onlinepoodll/displaysize_list', 
						new lang_string('displaysizelist', 'assignsubmission_onlinepoodll'), 
						new lang_string('displaysizelistdetails', 'assignsubmission_onlinepoodll'), '0', $size_options));

    //The width of an image
    $width_options = array('0' => new lang_string('shownoimage', 'assignsubmission_onlinepoodll'),
        '160' => '160', '320' => '320','480' => '480',
        '640' => '640','800'=>'800','1024'=>'1024');

    //Settings for snapshots
    $settings->add(new admin_setting_heading('assignsubmission_onlinepoodll/snapshot_heading',
        get_string('setting_snapshot_heading', 'assignsubmission_onlinepoodll'),
        get_string('setting_snapshot_heading_details', 'assignsubmission_onlinepoodll')));

    //snapshot
    $settings->add(new admin_setting_configselect('assignsubmission_onlinepoodll/snapshot_displaysize_single',
        new lang_string('imagedisplaysizesingle', 'assignsubmission_onlinepoodll'),
        new lang_string('imagedisplaysizesingledetails', 'assignsubmission_onlinepoodll'), '320', $width_options));

    $settings->add(new admin_setting_configselect('assignsubmission_onlinepoodll/snapshot_displaysize_list',
        new lang_string('imagedisplaysizelist', 'assignsubmission_onlinepoodll'),
        new lang_string('imagedisplaysizelistdetails', 'assignsubmission_onlinepoodll'), '160', $width_options));

    //Settings for whiteboard images
    $settings->add(new admin_setting_heading('assignsubmission_onlinepoodll/whiteboard_heading',
        get_string('setting_whiteboard_heading', 'assignsubmission_onlinepoodll'),
        get_string('setting_whiteboard_heading_details', 'assignsubmission_onlinepoodll')));

    //whiteboard
    $settings->add(new admin_setting_configselect('assignsubmission_onlinepoodll/whiteboard_displaysize_single',
        new lang_string('imagedisplaysizesingle', 'assignsubmission_onlinepoodll'),
        new lang_string('imagedisplaysizesingledetails', 'assignsubmission_onlinepoodll'), '320', $width_options));

    $settings->add(new admin_setting_configselect('assignsubmission_onlinepoodll/whiteboard_displaysize_list',
        new lang_string('imagedisplaysizelist', 'assignsubmission_onlinepoodll'),
        new lang_string('imagedisplaysizelistdetails', 'assignsubmission_onlinepoodll'), '160', $width_options));
					



