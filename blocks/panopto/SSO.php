<?php
global $CFG, $USER;

if(empty($CFG))
{
	require_once("../../config.php");
}
require_once ($CFG->libdir . '/weblib.php');
require_once("lib/block_panopto_lib.php");

$server_name = required_param("serverName");
$callback_url = required_param("callbackURL");
$expiration = required_param("expiration");
$request_auth_code = required_param("authCode");
$action = optional_param("action");

$relogin = ($action == "relogin"); 

if($relogin || (isset($USER->username) && ($USER->username == "guest")))
{
	require_logout();

	// Return to this page, minus the "action=relogin" parameter.
	redirect($CFG->wwwroot . "/blocks/panopto/SSO.php" . 
				"?authCode=$request_auth_code" .
				"&serverName=$server_name" .
				"&expiration=$expiration" .
				"&callbackURL=" . urlencode($callback_url));
	return;
}

// No course ID (0).  Don't autologin guests (false).
require_login(0, false);

// Reproduce canonically-ordered incoming auth payload.
$request_auth_payload = "serverName=" . $server_name . "&expiration=" . $expiration;

// Verify passed in parameters are properly signed.
if(validate_auth_code($request_auth_payload, $request_auth_code))
{
	$user_key = decorate_username($USER->username);

	// Generate canonically-ordered auth payload string
	$response_params = "serverName=" . $server_name . "&externalUserKey=" . $user_key . "&expiration=" . $expiration;
	// Sign payload with shared key and hash.
	$response_auth_code = generate_auth_code($response_params);
	
	$separator = (strpos($callback_url, "?") ? "&" : "?");
	$redirect_url = $callback_url . $separator . $response_params . "&authCode=" . $response_auth_code;  
	
	// Redirect to Panopto Focus login page.
	redirect($redirect_url);
}
else
{
	print_header();
	
	echo "Invalid auth code.";
	
	print_footer();
}
