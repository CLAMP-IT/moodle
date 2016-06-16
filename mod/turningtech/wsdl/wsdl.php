<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * This file is used to Load moodle config.
 * @author jacob
 * @copyright  2012 Turning Technologies
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once('../../../config.php');

// Possible options for service type.
$arrservicetype = array(
    "course",
    "func",
    "grades"
);

// Get the service after sanitization.
$service = required_param('service', PARAM_ALPHANUM);

// If the service type is not valid.
if (!in_array($service, $arrservicetype)) {
    echo "expecting parameter 'service' with value 'course', 'func' or 'grades'\n";
    die;
}

// The WSDL file to read, depending on the request.
$filename = '';

// Set filename depending on request.
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

// The URL of the module.
$url = $CFG->wwwroot . '/mod/turningtech';
$urlfile = $url."/wsdl/".$filename;
function file_get_contents_curl($url, $retries=5)
{
    $ua = 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1)';

    if (extension_loaded('curl') === true)
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_USERAGENT, $ua);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_FAILONERROR, TRUE);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
        curl_setopt($ch, CURLOPT_AUTOREFERER, TRUE);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 5);

        $result = trim(curl_exec($ch));

        curl_close($ch);
    }

    else
    {
        $result = trim(file_get_contents($url));
    }        

    if (empty($result) === true)
    {
        $result = false;

        if ($retries >= 1)
        {
            sleep(1);
            return file_get_contents_curl($url, $retries--);
        }
    }    

    return $result;
}

$contents = file_get_contents_curl($urlfile);

header('Content-type: text/xml');

echo str_replace('@URL', $url, $contents);