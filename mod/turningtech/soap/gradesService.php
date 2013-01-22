<?php
/****
 * handles SOAP requests for GradesService
 ****/
require_once('paths.php');
require_once('../../../config.php');
require_once($turningtech_soap_config_path);
require_once($turningtech_soap_lib_path);
require_once($turningtech_soap_soapClasses_abstract_path);
require_once($turningtech_soap_soapClasses_directory_path . '/GradesServiceClass.php');

$server = new SoapServer(TURNINGTECH_WSDL_URL . '?service=grades');
$server->setClass('TurningTechGradesService');
$server->handle();
?>