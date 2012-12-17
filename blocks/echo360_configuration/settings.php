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
 * This is the settings class for the Echo360 Configuration Block
 *
 * It just adds all the required settings fields to the moodle config object.
 * It does not contain a class - it is included automatically by Moodle in the
 * correct place.
 *
 * @package    block                                                    
 * @subpackage echo360_configuration
 * @copyright  2011 Echo360 Inc
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later 
 */

defined('MOODLE_INTERNAL') || die;
global $OUTPUT;

if ($ADMIN->fulltree) {

    // Heading
    $settings->add(new admin_setting_heading('block_echo360_configuration_connection_details_heading', get_string('echosystem_connection_details', 'block_echo360_configuration'), get_string('echosystem_connection_details_description', 'block_echo360_configuration')));

    // EchoSystem URL
    $settings->add(new admin_setting_configtext('block_echo360_configuration_echosystem_url', get_string('echosystem_url', 'block_echo360_configuration'), get_string('echosystem_url_description', 'block_echo360_configuration'), get_string('default_echosystem_url', 'block_echo360_configuration'), PARAM_TEXT));
    
    // Trusted System Consumer Key
    $settings->add(new admin_setting_configtext('block_echo360_configuration_trusted_system_consumer_key', get_string('trusted_system_consumer_key', 'block_echo360_configuration'), get_string('trusted_system_consumer_key_description', 'block_echo360_configuration'), get_string('default_trusted_system_consumer_key', 'block_echo360_configuration'), PARAM_TEXT));
    
    // Trusted System Consumer Secret
    $settings->add(new admin_setting_configtext('block_echo360_configuration_trusted_system_consumer_secret', get_string('trusted_system_consumer_secret', 'block_echo360_configuration'), get_string('trusted_system_consumer_secret_description', 'block_echo360_configuration'), get_string('default_trusted_system_consumer_secret', 'block_echo360_configuration'), PARAM_TEXT));

    // Security Realm
    $settings->add(new admin_setting_configtext('block_echo360_configuration_security_realm', get_string('security_realm', 'block_echo360_configuration') . ' ' . $OUTPUT->help_icon('security_realm', 'block_echo360_configuration'), get_string('security_realm_description', 'block_echo360_configuration'), get_string('default_security_realm', 'block_echo360_configuration'), PARAM_TEXT));
    
    // Heading
    $settings->add(new admin_setting_heading('block_echo360_configuration_moodle_course_mapping_details_heading', get_string('moodle_course_mapping_details', 'block_echo360_configuration'), get_string('moodle_course_mapping_details_description', 'block_echo360_configuration')));

    // Moodle External Id Field
    $options = array('shortname'=>get_string('shortnamecourse'), 'fullname'=>get_string('fullname'), 'idnumbercourse'=>get_string('idnumbercourse'), 'id'=>get_string('databaseid', 'block_echo360_configuration'));
    $settings->add(new admin_setting_configselect('block_echo360_configuration_moodle_external_id_field', get_string('moodle_external_id_field', 'block_echo360_configuration'), get_string('moodle_external_id_field_description', 'block_echo360_configuration'), get_string('default_moodle_external_id_field', 'block_echo360_configuration'), $options, PARAM_TEXT));

}



?>
