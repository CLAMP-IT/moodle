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
 * File for SOAP server courses service
 * @author jacob
 * @package    mod_turningtech
 * @copyright  2012 Turning Technologies
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * NOTE: callers which include/require this class MUST also include/require the following:
 * - [moodle root]/config.php
 * - mod/turningtech/lib.php
 * - mod/turningtech/lib/soapClasses/AbstractSoapServiceClass.php
 */
/**
 * SOAP server class for courses service
 * @author jacob
 * @copyright  2012 Turning Technologies
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * NOTE: callers which include/require this class MUST also include/require the following:
 * - [moodle root]/config.php
 * - mod/turningtech/lib.php
 * - mod/turningtech/lib/soapClasses/AbstractSoapServiceClass.php
 */
class TurningTechCoursesService extends TurningTechSoapService {
    /**
     * Get taught courses
     * @param unknown_type $request
     * @return array of courseSiteView
     */
    public function gettaughtcourses($request) {
        $instructor = null;
        $courses    = null;

        $instructor = $this->authenticaterequest($request);
        $courses    = $this->service->getCoursesByInstructor($instructor);
        if ($courses === false) {
            $this->throwfault('CourseException', get_string('couldnotgetlistofcourses', 'turningtech'));
        }

        return $courses;
    }

    /**
     * Get extended taught courses
     * @param unknown_type $request
     * @return array of courseSiteView
     */
    public function gettaughtcoursesext($request) {
        $instructor = null;
        $courses    = null;

        $instructor = $this->authenticaterequest($request);
        $courses    = $this->service->getExtCoursesByInstructor($instructor);
        if ($courses === false) {
            $this->throwfault('CourseException', get_string('couldnotgetlistofcourses', 'turningtech'));
        }

        return $courses;
    }

    /**
     * Get class roster
     * @param unknown_type $request
     * @return array of courseParticipantDTO
     */
    public function getclassroster($request) {
        $instructor = null;
        $course     = null;
        $roster     = null;

        $instructor = $this->authenticaterequest($request);
        $course     = $this->getcoursefromrequest($request);

        if ($this->service->userHasRosterPermission($instructor, $course)) {
            $roster = $this->service->getClassRoster($course);
            if ($roster === false) {
                $this->throwfault("CourseException", get_string('couldnotgetroster', 'turningtech', $request->siteId));
            }
            return $roster;
        } else {
            $this->throwfault("SiteConnectException", get_string('norosterpermission', 'turningtech'));
        }
    }

    /**
     * Get extended class roster
     * @param unknown_type $request
     * @return array of courseParticipantDTO
     */
    public function getclassrosterext($request) {
        $instructor = null;
        $course     = null;
        $roster     = null;

        $instructor = $this->authenticaterequest($request);
        $course     = $this->getcoursefromrequest($request);

        if ($this->service->userHasRosterPermission($instructor, $course)) {
            $roster = $this->service->getExtClassRoster($course);
            if ($roster === false) {
                $this->throwfault("CourseException", get_string('couldnotgetroster', 'turningtech', $request->siteId));
            }
            return $roster;
        } else {
            $this->throwfault("SiteConnectException", get_string('norosterpermission', 'turningtech'));
        }
    }
}