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
 * @package   block_annotate
 * @copyright Textensor Ltd.
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined ( 'MOODLE_INTERNAL' ) || die ();
global $CFG;

if ($ADMIN->fulltree) {
    $settings->add(new admin_setting_heading("block_annotate_heading_server",
       get_string("annotate_header_config", "block_annotate"),
       get_string("annotate_header_description", "block_annotate"))
    );

    $settings->add(new admin_setting_configtext("block_annotate_server_url",
       get_string("annotate_server_uri_lbl", "block_annotate"),
       get_string("annotate_server_uri_msg", "block_annotate"),
       get_string("annotate_server_uri_default", "block_annotate")),
       PARAM_URL
    );

    $settings->add(new admin_setting_configtext('block_annotate_api_user',
       get_string("annotate_api_user_lbl", "block_annotate"),
       get_string("annotate_api_user_msg", "block_annotate"),
       get_string("annotate_api_user_default", "block_annotate")),
       PARAM_EMAIL
    );

    $settings->add(new admin_setting_configtext('block_annotate_api_key',
       get_string("annotate_api_key_lbl", "block_annotate"),
       get_string("annotate_api_key_msg", "block_annotate"),
       get_string("annotate_api_key_default", "block_annotate")),
       PARAM_ALPHANUMEXT
    );

    $settings->add(new admin_setting_configtext('block_annotate_wsuser_token',
       get_string("annotate_wsuser_token_lbl", "block_annotate"),
       get_string("annotate_wsuser_token_msg", "block_annotate"),
       get_string("annotate_wsuser_token_default", "block_annotate")),
       PARAM_ALPHANUMEXT
    );

    $settings->add(new admin_setting_configtext('block_annotate_moodleId',
       get_string("annotate_moodleId_lbl", "block_annotate"),
       get_string("annotate_moodleId_msg", "block_annotate"),
       sha1($CFG->wwwroot)),
       PARAM_ALPHANUMEXT
    );
}
