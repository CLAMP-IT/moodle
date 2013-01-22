<?php

$arrAllowedServer = array(
    '10.11.2.114',
    '10.11.9.6',
    'localhost',
    '127.0.0.1'
);

if ( !isset( $_SERVER['SERVER_NAME'] ) ||
    !in_array( $_SERVER['SERVER_NAME'], $arrAllowedServer ) )
{
    die("You are not authorized to access this page.");
}

require_once('../../../../config.php');
require_once($CFG->dirroot . '/mod/turningtech/lib.php');
require_once($CFG->dirroot . '/mod/turningtech/lib/helpers/EncryptionHelper.php');

echo "<br/>Username >> ", $username = encryptWebServicesString($_GET['username']);
echo "<br/>Password >> ", $password = encryptWebServicesString($_GET['password']);

//echo "<br/>Username >> " , $username = decryptWebServicesString($username);
//echo "<br/>Password >> " , $password = decryptWebServicesString($password);

?>