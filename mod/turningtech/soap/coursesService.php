<?php
/****
 * handles SOAP requests for CoursesService
 ****/
require_once('paths.php');
require_once('../../../config.php');
require_once($turningtech_soap_config_path);
require_once($turningtech_soap_lib_path);
require_once($turningtech_soap_soapClasses_abstract_path);
require_once($turningtech_soap_soapClasses_directory_path . '/CoursesServiceClass.php');

$server = new SoapServer(TURNINGTECH_WSDL_URL . '?service=course');
$server->setClass('TurningTechCoursesService');
$server->handle();
?>