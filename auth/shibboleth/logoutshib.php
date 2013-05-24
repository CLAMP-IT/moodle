<?php
// Shibboleth SP logout page

// This page will log a user out of the local SP and the remote IdP. Additionaly
// you'll want to log out of the application as well. Application logout will be
// specific to each application but here is a very simplistic application logout
// which you'll likely want to customize.

require_once("../../config.php");

/***************************
 * Begin Appliation logout *
 ***************************/
$authsequence = get_enabled_auth_plugins(); // auths, in sequence
foreach($authsequence as $authname) {
	$authplugin = get_auth_plugin($authname);
	$authplugin->logoutpage_hook();
}
require_logout();
/***************************
 *  End Application logout  *
 ***************************/

include "../../shiblogout/index.php";
?>
