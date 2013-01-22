<?php
include_once('../../../config.php');

function showTestResult($testname, $request, $response) {
?>
<h2>
<?php
print $testname;
?>
 Method
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

ini_set('soap.wsdl_cache_enabled', '0');

$url = "http://" . $_SERVER['HTTP_HOST'];

$path = split('/', $_SERVER['REQUEST_URI']);

// move up 2 directories
array_pop($path);
array_pop($path);

// add on path to WSDL provider
$path[] = 'wsdl';
$path[] = 'wsdl.php?service=';

$wsdl_url = $url . implode('/', $path);

$soap_params = array(
    'trace' => 1
);

$service = (isset($_GET['service'])) ? $_GET['service'] : "";

switch ($service) {
    case "course":

        $objClient = new SoapClient($wsdl_url . 'course', $soap_params);
        break;

    case "func":

        $objClient = new SoapClient($wsdl_url . 'func', $soap_params);
        break;

    case "grade":

        $objClient = new SoapClient($wsdl_url . 'grades', $soap_params);
        break;
}

global $CFG;

require($CFG->dirroot . '/mod/turningtech/lib/tests/tests.php');
?>