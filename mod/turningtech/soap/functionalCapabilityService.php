<?php
/****
 * handles SOAP requests for FunctionalCapabilityService
 ****/
require_once('paths.php');
require_once($turningtech_soap_config_path);
require_once($turningtech_soap_lib_path);
require_once($turningtech_soap_soapClasses_abstract_path);
require_once($turningtech_soap_soapClasses_directory_path . '/FunctionalCapabilityServiceClass.php');

$server = new SoapServer(TURNINGTECH_WSDL_URL . '?service=func');
$server->setClass('TurningTechFunctionalCapabilityService');
$server->handle();
?>