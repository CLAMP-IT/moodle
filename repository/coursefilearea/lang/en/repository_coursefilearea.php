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
 * Strings for component 'repository_coursefilearea', language 'en', branch 'MOODLE_20_STABLE'
 *
 * @package   repository_coursefilearea
 * @copyright 2010 Dongsheng Cai <dongsheng@moodle.com>, Modified by Tim Williams <tmw@autotrain.org>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['allowinternaltitle'] = 'Allow Internal use';
$string['allowinternaldesc'] = 'This option allows the course file area to act as an internal repository type. ';
$string['configplugin'] = 'Configuration for course file area repository';
$string['emptyfilelist'] = 'There are no files to show';
$string['redirectpatchdetected'] = 'Warning: The file store redirection patch has been detected on the system, this will cause files to be read directly from'.
 ' the course files area, instead of being copied into the Moodle file store. This patch should only be used for development/testing systems, '.
 'it has not yet been properly tested, may cause backups to fail and will cause broken files if it is subsequently removed from the system.';
$string['redirectpatchnotdetected'] = 'The original file in the course file area will be copied into the regular file store when it is selected, '.
   'so changes to the file within the course files area will not be reflected in the resource which uses the file, unless it is re-added to the relevant resource.';
$string['notitle'] = 'notitle';
$string['remember'] = 'Remember me';
$string['pluginname_help'] = 'Course file area';
$string['pluginname'] = 'Course file area';
$string['coursefilearea:view'] = 'Access Files';