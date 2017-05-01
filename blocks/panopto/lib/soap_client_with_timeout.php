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
 * the soap client for panopto with a custome timeout
 *
 * @package block_panopto
 * @copyright  Panopto 2009 - 2016 /With contributions from Spenser Jones (sjones@ambrose.edu)
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

// Provisioning operations with many users can take longer than the default 30 seconds to finish executing.
// We disable maximum execution time for running this script so that it will continue to execute for as long as the
// timeout duration specified.
set_time_limit(0);

// This class extends php's built in SoapClient class with logic for handling different timeout durations
// ... for making calls. The default timeout is 60 seconds, but can be changed by including a 'timeout' value
// ... in the options array passed in as an argument.
// Timeouts are measured in seconds.

/**
 * the soap client for panopto with a custome timeout
 *
 * @copyright  Panopto 2009 - 2016 /With contributions from Spenser Jones (sjones@ambrose.edu)
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class soap_client_with_timeout extends SoapClient
{
    /**
     * @var int $timeout Timeout value in seconds. Default is 60 seconds.
     */
    public $timeout = 60;

    /**
     * Overrides parent constructor to set timeout if included in options.
     * @param string $wsdl
     * @param array $options
     */
    public function _construct($wsdl, $options) {
        if (isset($options['timeout'])) {
            // Only set timeout if it is a positive value.
            if ($options['timeout'] > 0) {
                $this->timeout = $options['timeout'];
            } else {
                // Otherwise, keep default and log that timeout was not set.
                error_log($errorstring);
            }
        }
        // After setting timeout, call the parent constructor.
        parent::__construct($wsdl, $options);
    }

    /**
     * Overrides parent __doRequest function to make SOAP calls with custom timeout.
     *
     * @param string $request the request being processed
     * @param string $location
     * @param string $action
     * @param string $version
     * @param string $oneway
     */
    public function __doRequest($request, $location, $action, $version, $oneway = false) {
        // Attempt to intitialize cURL session to make SOAP calls.
        $curl = curl_init($location);

        // Check cURL was initialized.
        if ($curl !== false) {
            // Set standard cURL options.
            $options = array(
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $request,
                CURLOPT_NOSIGNAL => true,
                CURLOPT_HTTPHEADER => array(
                    sprintf('Content-Type: %s', $version == 2 ? 'application/soap+xml' : 'text/xml'),
                    sprintf('SOAPAction: %s', $action)
                ),
                CURLOPT_SSL_VERIFYPEER => true, // All of our SOAP calls must be made via ssl.
                CURLOPT_TIMEOUT => $this->timeout // Set call timeout in seconds.
            );

            // Attempt to set the options for the cURL call.
            if (curl_setopt_array($curl, $options) !== false) {
                // Make call using cURL (including timeout settings).
                $response = curl_exec($curl);

                // If cURL throws an error, log it.
                if (curl_errno($curl) !== 0) {
                    error_log(curl_error($curl));
                }
            } else {
                // A cURL option could not be set.
                error_log('Failed setting cURL options.');
            }
        } else {
            // ... cURL was not initialized properly.
            error_log("Couldn't initialize cURL to make SOAP calls");
        }

        // Close cURL session.
        curl_close($curl);

        // Return the SOAP response.
        return $response;
    }
}
