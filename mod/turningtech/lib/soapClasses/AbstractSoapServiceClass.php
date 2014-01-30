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
 * File for Abstract parent SOAP services
 * @author jacob
 * @package    mod_turningtech
 * @copyright  2012 Turning Technologies
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * NOTE: callers which include/require this class MUST also include/require the following:
 * - [moodle root]/config.php
 * - mod/turningtech/lib.php
 * ALSO NOTE: this class, being abstract, should not be required/included without the require/include
 * for the specific child class.
 **/
/**
 * Abstract parent SOAP service class
 * @author jacob
 * @copyright  2012 Turning Technologies
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 **/
abstract class TurningTechSoapService {
    // The class that we talk to when handling request.
    /**
     * @var unknown_type
     */
    protected $service = null;
    /**
     * @var unknown_type
     */
    private $user;
    /**
     * @var unknown_type
     */
    private $course;

    /**
     * Constructor
     */
    public function __construct() {
        $this->service = new TurningTechIntegrationServiceProvider();
    }

    /**
     * Authenticates a user
     * @param string $encrypteduserid
     * @param string $encryptedpassword
     * @return MoodleUser
     */
    protected function authenticateuser($encrypteduserid, $encryptedpassword) {
        if ($user = $this->service->getUserByAESAuth($encrypteduserid, $encryptedpassword)) {
            $this->user = $user;
            return $user;
        }

        throw new SoapFault("AuthenticationException", "Could not get user from encrypted username and password");
    }

    /**
     * shortcut function for authenticateuser()
     * @param unknown_type $request
     * @param string $usernamefield
     * @param string $passwordfield
     * @return MoodleUser
     */
    protected function authenticaterequest($request, $usernamefield = 'encryptedUserId', $passwordfield = 'encryptedPassword') {
        return $this->authenticateuser($request->$usernamefield, $request->$passwordfield);
    }

    /**
     * fetch the course used by the request
     * @param unknown_type $request
     * @param unknown_type $field
     * @return unknown_type
     */
    protected function getcoursefromrequest($request, $field = 'siteId') {
        if ($course = $this->service->getCourseById($request->$field)) {
            //  Check Version.
            if ($CFG->version >= '2013111800.00') {
                $context = context_course::instance($course->id);
            } else {
                $context = get_context_instance(CONTEXT_COURSE, $course->id);
            }
            $role_users = array();
            $inst_roles = explode(',', TURNINGTECH_DEFAULT_TEACHER_ROLE);
            foreach ($inst_roles as $index => $roleid) {
                $role_users = array_merge($role_users, get_role_users($roleid, $context));
            }
            foreach ($role_users as $ru) {
                if ($ru->id == $this->user->id) {
                    $this->course = $course;
                    return $course;
                }
            }
            $this->throwfault('AuthenticationException', get_string('userisnotinstructor', 'turningtech'));
        }

        throw new SoapFault('SiteConnectException', get_string('siteconnecterror', 'turningtech', $request->$field));
    }

    /**
     * throw a SoapFault
     * @param unknown_type $type
     * @param unknown_type $message
     */
    protected function throwfault($type, $message) {
        throw new SoapFault($type, $message, '', '', $type);
    }
}