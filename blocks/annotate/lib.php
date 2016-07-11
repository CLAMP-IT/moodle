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
 * Annotate block helper functions
 *
 * @package   block_annotate
 * @copyright Textensor Ltd.
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once("lib/HMAC2.php");

/**
 * Process user input (JSON-encode + HTML-escape).
 */
function block_annotate_process_usr_input($inputdata) {
    $dataencoded = json_encode ( $inputdata );
    return htmlspecialchars ( $dataencoded, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8' );
}

/**
 * Convert a string to base64.
 */
function block_annotate_hex2b64($str) {
    $raw = '';
    for ($i = 0; $i < strlen ( $str ); $i += 2) {
        $raw .= chr ( hexdec ( substr ( $str, $i, 2 ) ) );
    }
    return base64_encode ( $raw );
}

/**
 * Signs the Annotate request.
 */
function block_annotate_sign_request($phpfn, $apiuser, $apikey, $annotateuser, $validfor = 0) {
    // Include the timestamp.
    $requesttime = time () + $validfor;
    $stringtosign = "$phpfn\n$apiuser\n$requesttime\n$annotateuser";
    $t = new Crypt_HMAC2 ( $apikey, "sha1" );
    $hasher = & $t;
    $signature = block_annotate_hex2b64 ( $hasher->hash ( $stringtosign ) );
    $request = "api-user=" . rawurlencode ( $apiuser ) . "&api-requesttime=" . $requesttime;
    $request .= "&api-annotateuser=" . rawurlencode ( $annotateuser ) . "&api-auth=" . rawurlencode ( $signature );
    return $request;
}

/**
 * Annotate login request, creates account if necessary.
 */
function block_annotate_make_login_link($user, $loc, $errloc, $sig) {
    global $CFG;

    // The api function we will call first.
    $phpfn = "loginAs.php";

    // The email address of the user (a new user account will be created if needed).
    $annotateuser = $user;

    // Make this request valid for 30 mins.
    $validfor = 60 * 30;

    // Add code for signing the request so the a.nnotate server trusts us.
    $request = block_annotate_sign_request ( $phpfn, $CFG->block_annotate_api_user, $CFG->block_annotate_api_key, $user, $validfor );

    // The url we will call is the api php with the signing code in the get arguments.
    $url = $CFG->block_annotate_server_url . "/php/$phpfn?$request";

    // Loc is the url displayed once the user is logged in - the front page of the transferred document.
    $url .= "&loc=" . rawurlencode ( $loc );

    // Errloc is the destination if there is a problem logging them in.
    $url .= "&errloc=" . rawurlencode ( $errloc );

    // Store the password + username in a cookie. Can set remember=0 for no cookies.
    $url .= "&remember=1";

    // Create the user's account of necessary.
    $url .= "&create=1";

    // Give them an annotate signature the same as their moodle username.
    $url .= "&sig=" . rawurlencode ( $sig );

    // Used the licensed account to authorize the above.
    $url .= "&licensed=1";
    return $url;
}

/**
 * Redirects to Annotate.
 */
function block_annotate_redirect_to_annotate($docpath, $pnhash = "", $owner = "", $coursename = "", $courseid = "") {
    global $CFG, $USER;

    $fname = basename ( $docpath );
    $docpath = dirname ( $docpath ) . "/" . rawurlencode ( $fname );
    $params = array (
            "fmt" => "redir",
            "hash" => $pnhash,
            "docPath" => $docpath,
            "type" => "moodle",
            "courseName" => $coursename,
            "moodleId" => $CFG->block_moodle_id,
            "courseId" => $courseid,
            "fname" => $fname,
            "fuser" => $USER->email,
            "uname" => $USER->firstname,
            "ulname" => $USER->lastname,
            "token" => $CFG->block_annotate_wsuser_token
    );
    if ($owner) {
        $params ["sluser"] = $owner;
        $params ["slshared"] = 1;
    }
    $uploadurl = $CFG->block_annotate_server_url . '/php/lmsConnector.php?';
    $uploadurl .= 'install=' . rawurlencode ( $CFG->wwwroot );
    foreach ($params as $key => $value) {
        $uploadurl .= "&$key=" . rawurlencode ( $value );
    }

    // Redirecting to this url should transfer the file if necessary and go to the annotate page.
    if (strlen ( trim ( $CFG->block_annotate_api_user ) ) > 10) {
        // We have a doc owner and API key, so we can auto-create accounts and auto-login.
        $errurl = $CFG->wwwroot . '/blocks/annotate/error.php';
        $loginurl = block_annotate_make_login_link ( $USER->email, $uploadurl, $errurl, $USER->username );
        header ( "Location:" . $loginurl );
    } else {
        // No master user configured. Just ask the user to create an account or log in.
        header ( "Location:" . $uploadurl );
    }
}
