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
 * Annotate block English language
 *
 * @package block_annotate
 * @copyright  Textensor Ltd.
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string ['pluginname'] = 'Annotate';
$string ['annotate:addinstance'] = 'Add the Annotate block';
$string ['annotate:myaddinstance'] = 'Add the Annotate block to My home page';
$string ['annotate:accessannotate'] = 'Access Annotate';

$string ['annotate_header_config'] = 'Annotate server settings';
$string ['annotate_header_description'] = 'Here you can enter the necessary parameters to connect to your Annotate server.';
$string ['annotate_server_uri_lbl'] = 'Annotate Server URI';
$string ['annotate_server_uri_msg'] = 'Set this to your Annotate server. For testing, if you have a default Annotate installation on the same server as Moodle try "http://localhost/annotate". If both servers are on the same local subnetwork you can use their local ip addresses here too, as long as each is configured to know its own address.';
$string ['annotate_server_uri_default'] = 'http://localhost/annotate';

$string ['annotate_api_user_lbl'] = 'API user email';
$string ['annotate_api_user_msg'] = 'Email of Annotate user with API access. This is often the user to whom the license has been issued.';
$string ['annotate_api_user_default'] = '';

$string ['annotate_api_key_lbl'] = 'API key for the user';
$string ['annotate_api_key_msg'] = 'The API key can be found at the bottom of the settings page when logged in to Annotate as an user with API access.';
$string ['annotate_api_key_default'] = '';

$string ['annotate_wsuser_token_lbl'] = 'Web Services user token';
$string ['annotate_wsuser_token_msg'] = 'A specific Moodle Web Services user must be created to allow Annotate to fetch files from the Moodle server. Instructions on this can be found in https://moodle.org/plugins/block_annotate';
$string ['annotate_wsuser_token_default'] = '';

$string ['annotate_moodleId_lbl'] = 'Moodle Installation Identifier';
$string ['annotate_moodleId_msg'] = 'In order to separate the workspaces based on the Moodle install location.';

$string ['config_header_label'] = 'Document sharing options';
$string ['config_individual_access'] = 'Individual';
$string ['config_group_access'] = 'Group';
$string ['config_access_label'] = 'Access';
$string ['config_access_label_help'] = 'With "Individual" access each user will get an independent instance of each resource they access and notes will be private unless they explicitly share the document in Annotate. With "Group" access there is one copy for the group which is owned by the specified user below. Students can still make private notes but they can also make Shared notes visible to other users.';
$string ['config_shareuser_label'] = 'Document owner (Group only)';
$string ['config_shareuser_label_help'] = 'For "Group" access, the email of the user who will own the document in Annotate must be provided. This is often set to the course instructor.';

$string ['access_set_to_msg'] = 'Document access in Annotate set to: ';
$string ['access_default_msg'] = 'Individual (Default). This can be changed in config.';
$string ['access_shareuser_msg'] = 'Document owner: ';
$string ['access_shareuser_msg_dft'] = 'Not set yet. Use config.';

$string ['invalid_email_msg'] = 'Invalid email';
$string ['enter_email_msg'] = 'Enter email';

$string ['transfer_error'] = 'Error';
$string ['transfer_error_msg'] = 'There was a problem transferring to Annotate to view the document. Please contact your server administrator.';
