<?php

// This file is part of the Echo360 Moodle Plugin - http://moodle.org/
//
// The Echo360 Moodle Plugin is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// The Echo360 Moodle Plugin is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with the Echo360 Moodle Plugin.  If not, see <http://www.gnu.org/licenses/>.
 
/**
 * This file provides the language strings for the echo360_echocenter block
 *
 * @package    block
 * @subpackage echo360_echocenter
 * @copyright  2011 Echo360 Inc
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later 
 */

    $string['echocenter'] = "Echo360 EchoCenter";
    $string['echocenter_link'] = "Launch EchoCenter...";
    $string['loading'] = "Loading. Please wait...";

    $string['pluginname'] = "Echo360 EchoCenter";
    $string['openinnewwindow'] = "Open in new window...";
    $string['not_configured'] = "This block requires system level configuration before it can be used.";
    $string['configure_link_text'] = "Click here to set the system level configuration for this block.";
    $string['error_generating_url'] = "An error occurred while generating the URL to the resource. Please contact your system administrator. <br/> {$a}";
    $string['no_view_capability'] = "You do not have permission to view this content.";
    $string['not_found_response'] = "The EchoSystem could not find a matching course. Contact your system administrator to verify that lecture capture has been enabled for this course. <br/> {$a}";
    $string['forbidden_response'] = "The EchoSystem is denying access to this system. Contact your system administrator to verify that this Moodle plugin has been configured correctly. <br/> {$a->classname}";
    $string['unexpected_response'] = "The EchoSystem returned an unexpected response. Contact your system administrator to verify that this Moodle plugin has been configured correctly. <br/> {$a}";
?>
