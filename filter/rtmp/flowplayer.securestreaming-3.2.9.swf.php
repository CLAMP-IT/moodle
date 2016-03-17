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
 *  RTMP streaming media filter plugin
 *
 *  This filter will replace any rtmp links to a media file with
 *  a media plugin that plays that media inline
 *
 * @package    filter_rtmp
 * @author     Fred Woolard <woolardfa@appstate.edu>
 * @copyright  2015 Appalachian State University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define('NO_DEBUG_DISPLAY', true);
define('NO_MOODLE_COOKIES', true);
define('NO_UPGRADE_CHECK', true);


// // No query str or post vars accepted as those could
// be initialized by the plugin as Flash vars in the
// .swf code 
if (!empty($_GET) or !empty($_POST) or !empty($_REQUEST)) {
    header("HTTP/1.1 404 Not Found");
    die;
}

// Will need following for referrer restrictions
require('../../config.php');
require('./lib.php');
send_flash_content('flowplayer.secure-3.2.9.swf');
