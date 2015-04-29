<?php //$Id$

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
 * This file returns a redirect directly to the echocenter for the course. It makes sure the user is a member of the course and
 * has the required capabilities to view this block. It is used by the "Open in new window" link.
 *
 * @package    block
 * @subpackage echo360_echocenter
 * @copyright  2011 Echo360 Inc
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later 
 */

    require_once('../../config.php');
    require_once('../../course/lib.php');
    require_once('locallib.php');

    $id = required_param('id', PARAM_INT);

    global $DB, $CFG, $COURSE, $PAGE;

    require_login($id);

    // load the course context for permission checks
    $context = get_context_instance(CONTEXT_COURSE, $COURSE->id);

    $essapi = new echosystem_remote_api($CFG->block_echo360_configuration_echosystem_url, $CFG->block_echo360_configuration_trusted_system_consumer_key, $CFG->block_echo360_configuration_trusted_system_consumer_secret, 'default');

    $mapping = $CFG->block_echo360_configuration_moodle_external_id_field;

    // the lang string and the property of the course object have different names
    if ($mapping === 'idnumbercourse') {
        $mapping = 'idnumber';
    }

    $error_message = "";
    $error_detail = "";

    // Check they can view this block
    if (!has_capability("block/echo360_configuration:viewasinstructor", $context) && 
        !has_capability("block/echo360_configuration:viewasstudent", $context)) {
        $error_message = 'no_view_capability';
    } else {

        // Generate a signed url and use it to check for 404 (mapping not configured)
        $signedresponse = $essapi->generate_sso_url($USER, has_capability("block/echo360_configuration:viewasinstructor", $context), $COURSE->$mapping, false);

        if ($signedresponse['success']) {
            // we want to test for a 404
            $curl = $essapi->get_curl_with_defaults();
            $headers = $essapi->get_headers($curl, $signedresponse['url'], 1);
            
            if (!strstr($headers[0]['http'], "302")) {
                $error_message = 'unexpected_response';
                $e = explode(" ", $headers[0]['http'], 3);
                $error_detail = $e[2];
            } else if (strstr($headers[1]['http'], "404")) {
                $error_message = 'not_found_response';
                $e = explode(" ", $headers[1]['http'], 3);
                $error_detail = $e[2];
            } else if (strstr($headers[1]['http'], "403")) {
                $error_message = 'forbidden_response';
                $e = explode(" ", $headers[1]['http'], 3);
                $error_detail = $e[2];
            } else if (!strstr($headers[1]['http'], "200")) {
                $error_message = 'unexpected_response';
                $e = explode(" ", $headers[1]['http'], 3);
                $error_detail = $e[2];
            }
            curl_close($curl);
        } else {
            $error_message = 'error_generating_url';
        }
    }

    if ($error_message == "") {
        // all good - but we already used the request - need to sign again (generate a new nonce)
        $signedresponse = $essapi->generate_sso_url($USER, has_capability("block/echo360_configuration:viewasinstructor", $context), $COURSE->$mapping, true);
        header("Location:" . $signedresponse['url']);
    } else {
        print_error($error_message, 'block_echo360_echocenter', '', $error_detail);
    }


?>
