<?php

// load moodle config
include_once('../../../config.php');

// possible options for service type
$arrServiceType = array(
    "course",
    "func",
    "grades"
);

// get the service after sanitization.
$service = required_param('service', PARAM_ALPHANUM);

// If the service type is not valid.
if (!in_array($service, $arrServiceType)) {
    echo "expecting parameter 'service' with value 'course', 'func' or 'grades'\n";
    die;
}

// the WSDL file to read, depending on the request
$filename = '';

// set filename depending on request
switch ($service) {
    case 'course':
        $filename = 'CoursesService.wsdl';
        break;
    case 'func':
        $filename = 'FunctionalCapabilityService.wsdl';
        break;
    case 'grades':
        $filename = 'GradesService.wsdl';
        break;
    default:
        echo "expecting parameter 'service' with value 'course', 'func' or 'grades'\n";
        die;
}

// the URL of the module
$url = $CFG->wwwroot . '/mod/turningtech';

$contents = file_get_contents($filename);

header('Content-type: text/xml');

echo str_replace('@URL', $url, $contents);

?>