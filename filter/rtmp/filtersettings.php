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
 * @author     Lacey Vickery, Fred Woolard (based on mediaplugin filter {@link http://moodle.com})
 * @copyright  2012 Appalachian State University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {

    $settings->add(new admin_setting_configcheckbox('filter_rtmp_enable_audio', get_string('rtmp_audio',       'filter_rtmp'), '', 1));
    $settings->add(new admin_setting_configcheckbox('filter_rtmp_enable_video', get_string('rtmp_video',       'filter_rtmp'), '', 1));
    $settings->add(new admin_setting_configcheckbox('filter_rtmp_default_cc',   get_string('rtmp_defcc',       'filter_rtmp'), '', 0));
    $settings->add(new admin_setting_configcheckbox('filter_rtmp_hls_fallback', get_string('rtmp_hls_fallback','filter_rtmp'), '', 0));
    $settings->add(new admin_setting_configselect(  'filter_rtmp_hls_urlfmt',   get_string('rtmp_hls_urlfmt',  'filter_rtmp'), '', 'wowza',
      array('wse' => 'Wowza', 'fms' => 'Adobe FMS')));

}
