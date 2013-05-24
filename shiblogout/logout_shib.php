<?php
// Shibboleth SP logout page

// This page will log a user out of the local SP and the remote IdP. Additionaly
// you'll want to log out of the application as well. Application logout will be
// specific to each application but here is a very simplistic application logout
// which you'll likely want to customize.


/***************************
 * Begin Application logout *
 ***************************/
    session_start();
    $_SESSION = array();
    session_destroy();

/***************************
 *  End Application logout  *
 ***************************/

include "index2.php";
?>

