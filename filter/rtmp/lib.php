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

defined('MOODLE_INTERNAL') || die();
define('HEADER_NOTFOUND', 'HTTP/1.1 404 Not Found');


/**
 * Safer flash serving code. Lifted from  Petr Skoda's function
 * flowplayer_send_flash_content in /lib/flowplayer/lib.php
 *
 * @param string $filename
 * @return void
 * @uses $CFG
 */
function send_flash_content($filename)
{
    global $CFG;
    // Our referrers only, nobody else should embed these scripts.
    if (empty($_SERVER['HTTP_REFERER'])) {
        // No referrer, no joy
        header(HEADER_NOTFOUND);
        die;
    }
    
    $refhost = parse_url($_SERVER['HTTP_REFERER'], PHP_URL_HOST);    
    $ourhost = parse_url($CFG->wwwroot . '/', PHP_URL_HOST);
    if (strtolower($refhost) !== strtolower($ourhost)) {
        header(HEADER_NOTFOUND);
        die;
    }

    // The contents of the .swf file are encoded (base64) and put
    // in a file of the same name with a .bin extension
    if (false === ($content = file_get_contents($filename . '.bin'))) {
        header(HEADER_NOTFOUND);

        die;
    }
    
    // No caching allowed. HTTPS sites - watch out
    // for IE! KB812935 and KB316431.
    if (strpos($CFG->wwwroot, 'https://') === 0) {
        header('Cache-Control: private, max-age=10, no-transform');
        header('Pragma: ');
    } else {
        header('Cache-Control: private, must-revalidate, pre-check=0, post-check=0, max-age=0, no-transform');
        header('Pragma: no-cache');
    }

    header('Expires: ' . gmdate('D, d M Y H:i:s', 0) . ' GMT');
    header('Content-Type: application/x-shockwave-flash');
    echo base64_decode($content);
     
    die;

}
