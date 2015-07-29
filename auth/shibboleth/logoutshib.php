<?php
// Shibboleth SP logout page

// This page will log a user out of the local SP and the remote IdP. Additionaly
// you'll want to log out of the application as well. Application logout will be
// specific to each application but here is a very simplistic application logout
// which you'll likely want to customize.

require_once("../../config.php");

$PAGE->set_context(context_system::instance());
$PAGE->set_url('/auth/shibboleth/logoutshib.php');

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
 *  End Appliation logout  *
 ***************************/

// If you would like to wrap the logout page with the header and footer of your application
// Logs the user out and tells them to quit the browser

$PAGE->set_title("$SITE->shortname: Logout");
$PAGE->set_heading("$SITE->fullname: Logout");

echo $OUTPUT->header();

// can be overriden by auth plugins
$redirect = $CFG->wwwroot.'/';

?>
<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.8.0/jquery.min.js"></script>
<script type="text/javascript" src="logoutshib.js"></script>
 
<script type="text/javascript">
	doShibbolethLogout('<?php echo($_SERVER["Shib-Identity-Provider-LogoutURL"])?>', logoutCallback);
 
	function logoutCallback(status) {
		console.log('logoutCallback - ' + status);
	}
</script>

<?php
echo('Please quit the browser to finish the log out process.');
echo $OUTPUT->footer();
