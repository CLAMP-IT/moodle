<?php
// Shibboleth SP logout page
// 10/6/11 - cakDS@hampshire.edu

// This page will log a user out of the local SP and the remote IdP. Additionaly
// you'll want to log out of the application as well. Applicaiton logout will be
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
 *  End Appliation logout  *
 ***************************/

// If you would like to wrap the logout page with the header and footer of your
// Logs the user out and sends them to the home page

print_header($SITE->fullname, $SITE->fullname, 'home');

// can be overriden by auth plugins
$redirect = $CFG->wwwroot.'/';

//$sesskey = optional_param('sesskey', '__notpresent__', PARAM_RAW); // we want not null default to prevent required sesskey warning

?>

<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.6.2/jquery.min.js"></script>

<style type="text/css">
	.logoutheader {
		font-size: xx-large;
	}

	.notice {
		margin-top: 2em;
		padding: 1em;
		font-size: x-large;
		border-radius: 1em;
	}

	.warning {
		color: white;
		background-color: #c0230f;
	}

	.success {
		background-color: #7ca634;
	}
</style>

<div class="ajaxstuff" id="spframe" style="display: none;">spframe</div>

<img src="/pix/i/shiblogo.png" style="float: right;"> <h1 style="margin-top: 0px;">Moodle Logout</h1>
<table cellpadding="0" cellspacing="2">
	<tr>
		<td>Logging out of the application:</td>
		<td>
			<img src="/pix/i/spinninggear.gif" width="16px" height="16px" id="applogout">
			<img src="/pix/i/no.png" width="16px" height="16px" id="applogoutno">
			<img src="/pix/i/yes.png" width="16px" height="16px" id="applogoutyes">
		</td>
	</tr>
	<tr>
		<td>Logging out of the service provider:</td>
		<td>
			<img src="/pix/i/spinninggear.gif" width="16px" height="16px" id="splogout">
			<img src="/pix/i/no.png" width="16px" height="16px" id="splogoutno">
			<img src="/pix/i/yes.png" width="16px" height="16px" id="splogoutyes">
		</td>
	</tr>
	<tr>
		<td>Logging out of the identity provider:</td>
		<td>
			<img src="/pix/i/spinninggear.gif" width="16px" height="16px" id="idplogout">
			<img src="/pix/i/no.png" width="16px" height="16px" id="idplogoutno">
			<img src="/pix/i/yes.png" width="16px" height="16px" id="idplogoutyes">
		</td>
	</tr>
	<tr>
		<td>Verifying logout:</td>
		<td>
			<img src="/pix/i/spinninggear.gif" width="16px" height="16px" id="verifylogout">
			<img src="/pix/i/no.png" width="16px" height="16px" id="verifylogoutno">
			<img src="/pix/i/yes.png" width="16px" height="16px" id="verifylogoutyes">
		</td>
	</tr>
</table>

<div id="warning" class="notice warning">You were not logged out! You must quit your browser to log out.</div>
<div id="success" class="notice success">You have been logged out. If you were logged into any other sites then you will need to log out of those separately.</div>

<script type="text/javascript">
var idplogouturl = '<?if(isset($_SERVER["Shib-Identity-Provider-LogoutURL"])) { echo($_SERVER["Shib-Identity-Provider-LogoutURL"]); }?>';
var appsuccess = false;
var spsuccess = false;
var idpsuccess = false;
var verifysuccess = false;

$('#warning').hide();
$('#success').hide();
$('#applogoutno').hide();
$('#applogoutyes').hide();
$('#splogoutno').hide();
$('#splogoutyes').hide();
$('#idplogoutno').hide();
$('#idplogoutyes').hide();
$('#verifylogoutno').hide();
$('#verifylogoutyes').hide();

var logouttimeout = window.setTimeout(logoutfailure, 5000);

function logoutfailure() {
	if(!appsuccess) {	$('#applogoutno').show();	$('#applogout').hide(); }
	if(!spsuccess) {	$('#splogoutno').show();	$('#splogout').hide(); }
	if(!idpsuccess) {	$('#idplogoutno').show();	$('#idplogout').hide(); }
	if(!verifysuccess) {	$('#verifylogoutno').show();	$('#verifylogout').hide(); }

	$('#warning').show('slow');
}

//$('#result').hide().text('You are still logged in. You will need to quit your browser to finish logging off.').delay(2000).show();

$(document).ready(function() {
	// Logout of the Application
	// This is handled in the PHP code above and should already be done. Assume that this is the case.
	appsuccess = true;
	$('#applogout').hide();
	$('#applogoutyes').show();

	// Logout of the SP
	$('#spframe').load('/Shibboleth.sso/Logout', function(response, status, xhr) {
		var splogout = false;
		var theText = $('#spframe').text();

		// If the SP is not configured correctly you may get a partial logout which would have the message:
		// 	You remain logged into one or more applications accessed during your session. To complete the logout process, please close/exit your browser completely.
		// If that is the case then you need to comment out the <LogoutInitiator type="SAML2" template="bindingTemplate.html"/> line in /etc/shibboleth/shibboleth2.xml
		if(
			(theText.indexOf('You remain logged into one or more applications accessed during your session.') != -1) &&
			(theText.indexOf('To complete the logout process, please close/exit your browser completely.') != -1)
		) {
			$('#warning').append(' This Service Providor is using the unsupported global logout feature.');
			window.clearTimeout(logouttimeout);
			logoutfailure();
			return false;
		}

		// Test for full logout
		if(theText.indexOf('Logout was successful') != -1)         { splogout = true; } // Version 2.0   (running on Minerva)
		if(theText.indexOf('Logout completed successfully') != -1) { splogout = true; } // Version 2.3.1 (running on Daphne)
	
		if(splogout) {
			spsuccess = true;
			$('#splogout').hide();
			$('#splogoutyes').show();

			if(idplogouturl != '') {
				// Logout of the IdP
				$.ajax({
					url: idplogouturl,
					xhrFields: { withCredentials: true },
					dataType: "jsonp",
					success: function(j) {
						// If logout.php returns true then the cookie was found and an attempt was made to delete it.
						$('#idplogout').hide();
						$('#idplogoutyes').show();
						if(j.result) {
							// Now check if that attempt was successful by setting the attempt variable to true
							$.ajax({
								url: "https://idp.hampshire.edu/idp/logout2.php",
								data: "attempt=1",
								xhrFields: { withCredentials: true },
								dataType: "jsonp",
								success: function(j) {
									if(j.result) {
										window.clearTimeout(logouttimeout);
										$('#verifylogout').hide();
										$('#verifylogoutyes').show();
										$('#success').show('slow');
										window.location = '<?echo($redirect)?>';
										return true;
									} else {
										$('#warning').append(' Unable to remove IdP Session.');
										window.clearTimeout(logouttimeout);
										logoutfailure();
										return false;
									}
								}
							});
						} else {
							$('#warning').append(' ' + j.status);
							window.clearTimeout(logouttimeout);
							logoutfailure();
							return false;
						}
					},
					error: function(j) {
						// This function will never fire due to a limitation of html/javascript/jquery 
					}
				});
			} else {
				//$('#warning').append(' Unable to logout of <?echo($_SERVER["Shib-Identity-Provider"])?>.');
				window.clearTimeout(logouttimeout);
				logoutfailure();
				return false;
			}
		}
	});
});

</script>
<?
print_footer();
?>
