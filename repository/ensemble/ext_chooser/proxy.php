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
 * Ensemble Video repository plugin.
 *
 * @package    repository_ensemble
 * @copyright  2013 Symphony Video, Inc.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('AJAX_SCRIPT', true);

require_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/config.php');
require_once(dirname(dirname(dirname(__FILE__))) . '/lib.php');
require_once($CFG->libdir . '/moodlelib.php');
require_once('Zend/Http/Client.php');

$api_url        = urldecode(required_param('request', PARAM_RAW));

$ensembleUrl    = get_config('ensemble', 'ensembleURL');
$serviceUser    = get_config('ensemble', 'serviceUser');
$servicePass    = get_config('ensemble', 'servicePass');
$authDomain     = get_config('ensemble', 'authDomain');

$username       = '';
$password       = '';
$filter         = true;
$cookie_prefix  = str_replace('.', '_', $ensembleUrl);

if (!empty($serviceUser)) {
  $username = $serviceUser;
  $password = $servicePass;
  $filter = true;
} else if (!empty($_COOKIE[$cookie_prefix . '-user'])) {
  $username = $_COOKIE[$cookie_prefix . '-user'];
  $password = $_COOKIE[$cookie_prefix . '-pass'];
  $filter = false;
}

// Only service requests for our configured ensemble url
if (preg_match('#^' . preg_quote($ensembleUrl) . '#i', $api_url) !== 1) {
  header('Bad Request', true, 400);
  print('URL mismatch');
  exit;
}

$client = new Zend_Http_Client($api_url);
$client->setHeaders('Authorization', 'Basic ' . base64_encode($username . ':' . $password));

// Append user filter for currently logged in Moodle user (if we're using a service account)
if ($filter) {
  $userFilter = $USER->username . (!empty($authDomain) ? '@' . $authDomain : '');
  $client->setParameterGet('User', $userFilter);
}

// Send request
$response = $client->request();

// Forward along headers w/ the exception of basic auth as script uses cookies
foreach ($response->getHeaders() as $header => $value) {
  if (strtolower($header) !== 'www-authenticate') {
    header($header . ': ' . $value);
  }
}

// Set response status.
header($response->getMessage(), true, $response->getStatus());

// Print actual data.
print $response->getBody();

exit;

?>
