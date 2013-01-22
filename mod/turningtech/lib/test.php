<?php
include_once('../../../config.php');

global $CFG;

require_once($CFG->dirroot . '/mod/turningtech/lib.php');
require_once($CFG->dirroot . '/mod/turningtech/lib/soapClasses/AbstractSoapServiceClass.php');

/**
 *
 * @param $testname
 * @param $request
 * @param $response
 * @return unknown_type
 */
function showTestResult($testname, $request, $response) {
?>
<h2>
<?php
print $testname;
?>
</h2>
<dl>
  <dt>Request</dt>
  <dd><pre>
<?php
print_r($request);
?>
  </pre></dd>
  <dt>Response</dt>
  <dd><pre>
<?php
print_r($response);
?>
  </pre></dd>
</dl>
<?
}

/**
 *
 * @param $client
 * @param $name
 * @param $request
 * @return unknown_type
 */
function runTest($client, $name, $request) {
    $response = NULL;

    try {
        $response = $client->$name($request);
    }
    catch (SoapFault $e) {
        $response = $e;
    }

    showTestResult($name, $request, $response);
}

$service = $_GET['service'];
$action  = $_GET['action'];

switch ($service) {
    case "course":

        require_once($CFG->dirroot . '/mod/turningtech/lib/soapClasses/CoursesServiceClass.php');
        $objClient = new TurningTechCoursesService();
        break;

    case "func":

        require_once($CFG->dirroot . '/mod/turningtech/lib/soapClasses/FunctionalCapabilityServiceClass.php');
        $objClient = new TurningTechFunctionalCapabilityService();
        break;

    case "grade":

        require_once($CFG->dirroot . '/mod/turningtech/lib/soapClasses/GradesServiceClass.php');
        $objClient = new TurningTechGradesService();
        break;
}

require_once($CFG->dirroot . '/mod/turningtech/lib/tests/tests.php');
?>