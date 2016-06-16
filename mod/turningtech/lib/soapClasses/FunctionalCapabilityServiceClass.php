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
 * File for SOAP FunctionalCapability services
 * @author jacob
 * @package    mod_turningtech
 * @copyright  2012 Turning Technologies
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * NOTE: callers which include/require this class MUST also include/require the following:
 * - [moodle root]/config.php
 * - mod/turningtech/lib.php
 * - mod/turningtech/lib/soapClasses/AbstractSoapServiceClass.php
 **/
/**
 * SOAP service class for FunctionalCapability services
 * @author jacob
 * @copyright  2012 Turning Technologies
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * NOTE: callers which include/require this class MUST also include/require the following:
 * - [moodle root]/config.php
 * - mod/turningtech/lib.php
 * - mod/turningtech/lib/soapClasses/AbstractSoapServiceClass.php
 **/
class TurningTechFunctionalCapabilityService extends TurningTechSoapService {
    /**
     * get list of capabilities for user
     * @param unknown_type $request
     * @return array of functionalCapabilityDto
     */
    public function getfunctionalcapabilities($request) {
        $user         = null;
        $capabilities = null;

        $user = $this->authenticaterequest($request);
        return $this->service->getUserCapabilities($user);
    }
}