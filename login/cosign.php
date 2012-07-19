<?php

/*
 * CoSign authentication broker for Moodle
 *
 * @version 0.4
 * @author Jason Meinzer
 */

require_once('../config.php');

global $DB;

// set to true to email debugging output
define('COSIGN_DEBUG', false);
define('COSIGN_DEBUG_EMAIL', 'dlandau@reed.edu');

// we handle logouts here too
if($_GET['logout']) {
        do_logout();
}


// If HTTP_X_FORWARDED_USER is set we're behind a proxy.
if (isset($_SERVER['HTTP_X_FORWARDED_USER'])) {
  $ruser = $_SERVER['HTTP_X_FORWARDED_USER'];
 } else {
  $ruser = $_SERVER['REMOTE_USER'];
 }

if(COSIGN_DEBUG) $debug_1 = print_r($_REQUEST, 1) . "\n\n\n\n\n" . print_r($_SERVER, 1) . "\n\n\n\n\n" . print_r($_ENV, 1);

// check for obviously invalid crap; numbers and whitespace in a username
if(!preg_match('/[0-9\s]+/', $ruser)) {
        // ok, user is CoSign'd
        if($user = $DB-> get_record('user', array('username' => $ruser, 'mnethostid' => $CFG->mnet_localhost_id))) {
                if(COSIGN_DEBUG) {
                        $debug_2 = @print_r($user, 1);
                        mail(COSIGN_DEBUG_EMAIL, 'CoSign No Import', $debug_1 . "\n\n\n\n\n" . $debug_2);
                }


                // sync LDAP attributes
                $ldap = get_auth_plugin('ldap');

		// Pull field names that should be updated on login from the LDAP config.
		$updatekeys = array();
		foreach (array_keys(get_object_vars($ldap->config)) as $key) {
		  if (preg_match('/^field_updatelocal_(.+)$/',$key, $match)) {
                    if ( !empty($ldap->config->{'field_map_'.$match[1]})
                         and $ldap->config->{$match[0]} === 'onlogin') {
		      array_push($updatekeys, $match[1]); // the actual key name
                    }
		  }
		}

                #$ldap->update_user_record($ruser, $updatekeys);

                // user be all up in this Moozy, so log them in
                do_login($user);
        } else {
                // user is not in Moodizzle, import them from the L-dazzle
                if($user = create_user_record($ruser, '', 'ldap')) {
                        if(COSIGN_DEBUG) {
                                $debug_2 = @print_r($user, 1);
                                mail(COSIGN_DEBUG_EMAIL, 'CoSign Import', $debug_1 . "\n\n\n\n\n" . $debug_2);
                        }

                        // ok, now log them in
                        do_login($user);
                } else {
                        // error creating user
                        if(COSIGN_DEBUG) mail(COSIGN_DEBUG_EMAIL, 'CoSign Error', print_r($_REQUEST, 1) . "\n\n\n\n\n" . print_r($_SERVER, 1));

                        // try again?
                        setcookie('cosign-moodle', '', time() - 86400, '/', '', true);
                        session_destroy();
                        header('Location: /login/cosign.php');
                }
        }
} else {
        if(COSIGN_DEBUG) mail(COSIGN_DEBUG_EMAIL, 'CoSign Weird', $ruser . "\n\n\n\n" . $debug_1);

        // headers be tripping.  clear the cookies and try again
        setcookie('cosign', '', time() - 86400, '/', '', true);
        setcookie('cosign-moodle', '', time() - 86400, '/', '', true);
        header('Location: /login/cosign.php');
}



/* helper functions go here */
function do_login(&$user) {
        global $CFG, $USER, $SESSION;

        $USER = complete_user_login($user);
        redirect(empty($SESSION->wantsurl) ? $CFG->wwwroot : $SESSION->wantsurl);
}

function do_logout() {
        global $CFG;

        setcookie('cosign-moodle', '', time() - 86400, '/', '', true);
        header('Location: https://weblogin.reed.edu/cgi-bin/logout-now.cgi?url=' . $CFG->wwwroot);
        die();
}

function rc_user_exists($uid) {
        $c = curl_init();

        curl_setopt($c, CURLOPT_URL, 'https://porthole.reed.edu/api/accounts/' . $uid . '.xml');
        curl_setopt($c, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($c, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($c, CURLOPT_USERAGENT, 'Moodle+CoSign/0.4');
        curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);

        $xml = curl_exec($c);

        return (curl_getinfo($c, CURLINFO_HTTP_CODE) == 200);
}

?>
