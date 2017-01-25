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
 * The auth soap client for panopto
 *
 * @package block_panopto
 * @copyright Panopto 2009 - 2016 with contributions from Spenser Jones (sjones@ambrose.edu),
 * Skylar Kelty <S.Kelty@kent.ac.uk>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// This can't be defined moodle internal because it is called from panopto to authorize login.

/**
 * The auth soap client for panopto
 *
 * @copyright Panopto 2009 - 2016 with contributions from Spenser Jones (sjones@ambrose.edu),
 * Skylar Kelty <S.Kelty@kent.ac.uk>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class panopto_auth_soap_client extends SoapClient {

    /**
     * @var string $getversionaction
     */
    private $getversionaction = 'http://tempuri.org/IAuth/GetServerVersion';

    /**
     * main constructor
     *
     * @param string $servername the name of the server we are on
     */
    public function __construct($servername) {
        // Instantiate SoapClient in WSDL mode.
        // Set call timeout to 5 minutes.
        parent::__construct
        (
            'https://'. $servername . '/Panopto/PublicAPI/4.0/Auth.svc?wsdl'
        );
    }

    /**
     * Override SOAP action to work around bug in older PHP SOAP versions.
     *
     * @param string $request the request being processed
     * @param string $location
     * @param string $action
     * @param string $version
     * @param string $oneway
     */
    public function __doRequest($request, $location, $action, $version, $oneway = null) {
        return parent::__doRequest($request, $location, $this->getversionaction, $version);
    }

    /**
     * gets the version of the server.
     */
    private function get_server_version() {
        return parent::__soapCall('GetServerVersion', array());
    }

    /**
     * Returns the version number of the current Panopto server.
     */
    public function get_panopto_server_version() {
        $panoptoversion;

        $serverversionresult = $this->get_server_version();

        if (!empty($serverversionresult)) {
            if (!empty($serverversionresult->{'GetServerVersionResult'})) {
                $panoptoversion = $serverversionresult->{'GetServerVersionResult'};
            }
        }
        return $panoptoversion;
    }
}

/* End of file panopto_auth_soap_client.php */
