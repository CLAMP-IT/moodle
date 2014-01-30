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
 * This file is mock implementation of ServiceProvider, used for dev testing
 * @package   mod_turningtech
 * @copyright 2012 Turning Technologies
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * 
 */
global $CFG;
require_once($CFG->dirroot . '/mod/turningtech/lib/AbstractServiceProvider.php');
/**
 * mock implementation class of ServiceProvider, used for dev testing
 * @copyright 2012 Turning Technologies
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */
class TurningTechMockServiceProvider extends TurningTechServiceProvider {
    /**
     * Gets the account whose username and password is submitted in AES encrypted format.
     * 
     * @see docroot/mod/turningtech/lib/ServiceProvider#getuserbyaesauth()
     * @param string $aesusername
     * @param string $aespassword
     * @return Moodle User
     */
    public function getuserbyaesauth($aesusername, $aespassword) {
        global $USER;
        $USER = authenticate_user_login($aesusername, $aespassword);
        return $USER;
    }
    /**
     * Gets the roster for a class
     * @param object $course
     * @return array of CourseParticipantDTO objects
     * 
     * @see docroot/mod/turningtech/lib/ServiceProvider#getclassroster()
     */
    public function getclassroster($course) {
        $roster = array ();
        $num = rand(0, 15);
        for ($i = 0; $i < $num; $i ++) {
            $roster[] = $this->_generatefakecourseparticipantdto();
        }
        return $roster;
    }
    /**
     * fetch the course
     * @param int $siteid
     * @return Moodle Course
     * 
     * @see docroot/mod/turningtech/lib/ServiceProvider#getCourseById()
     */
    public function getcoursebyid($siteid) {
        $course = new stdClass();
        // Do something.
        return $course;
    }
    /**
     * determine whether user can read the class roster
     * @param object $user
     * @param object $course
     * @return unknown_type
     * 
     * @see docroot/mod/turningtech/lib/ServiceProvider#userHasRosterPermission()
     */
    public function userhasrosterpermission($user, $course) {
        return true;
    }
    /**
     * get a list of courses for an instructor
     * @param mixed $instructor
     * @return array of CourseSiteView
     * 
     * @see docroot/mod/turningtech/lib/ServiceProvider#getCoursesByInstructor()
     */
    public function getcoursesbyinstructor($instructor) {
        $courses = array ();
        $num = rand(0, 5);
        for ($i = 0; $i < $num; $i ++) {
            $courses[] = $this->_generatefakecoursesiteview();
        }
        return $courses;
    }
    /**
     * get capabilities of user
     * @param object $user
     * @return array of functionalCapabilityDto
     * 
     * @see docroot/mod/turningtech/lib/ServiceProvider#getUserCapabilities()
     */
    public function getusercapabilities($user) {
        $cap = array ();
        $num = rand(0, 10);
        for ($i = 0; $i < $num; $i ++) {
            $cap[] = $this->_generatefakecapabilitydto();
        }
        return $cap;
    }
    /**
     * create a new activity
     * @param object $course
     * @param mixed $title
     * @param mixed $points
     * @return array of gradingErrorDto
     * 
     * @see docroot/mod/turningtech/lib/ServiceProvider#creategradebookitem()
     */
    public function creategradebookitem($course, $title, $points) {
        return array ();
    }
    /**
     * get list of gradebook items for a course
     * @param object $course
     * @return unknown_type
     * 
     * @see docroot/mod/turningtech/lib/ServiceProvider#getGradebookItemsByCourse()
     */
    public function getgradebookitemsbycourse($course) {
        $items = array ();
        $num = rand(0, 10);
        for ($i = 0; $i < $num; $i ++) {
            $items[] = $this->_generatefakegradebookitemview();
        }
        return $items;
    }
    /**
     * create a new activity
     * @param object $course
     * @param mixed $title
     * @return mixed
     * 
     * @see docroot/mod/turningtech/lib/ServiceProvider#createGradebookItemInstance()
     */
    public function creategradebookiteminstance($course, $title) {
        return new GradebookItem($title);
    }
    /**
     * Get existing gradebook item
     * @param object $course
     * @param mixed $student
     * @param mixed $title
     * @return unknown_type
     * 
     * @see docroot/mod/turningtech/lib/ServiceProvider#getExistingGradebookItem()
     */
    public function getexistinggradebookitem($course, $student, $title) {
        $item = new GradebookItem($title);
        $item->setStudent($student);
        return $item;
    }
    /**
     * Get existing gradebook item from escrow
     * @param object $course
     * @param mixed $deviceid
     * @param mixed $title
     * @return unknown_type
     * 
     * @see docroot/mod/turningtech/lib/ServiceProvider#getExistingGradebookItemFromEscrow()
     */
    public function getexistinggradebookitemfromescrow($course, $deviceid, $title) {
        return new GradebookItem($title);
    }
    /**
     * finds the student associated with the given device ID in the given course.
     * @param object $course
     * @param mixed $deviceid
     * @return object student
     * 
     * @see docroot/mod/turningtech/lib/ServiceProvider#getStudentByCourseAndDeviceId()
     */
    public function getstudentbycourseanddeviceid($course, $deviceid) {
        $student = new stdClass();
        $student->id = $this->_generaterandomstring();
        return $student;
    }
    /**
     * Save gradebook item in escrow
     * @param mixed $gradebookitem
     * @return unknown_type
     * 
     * @see docroot/mod/turningtech/lib/ServiceProvider#saveGradebookItemInEscrow()
     */
    public function savegradebookiteminescrow($gradebookitem) {
        // Do nothing.
        return true;
    }
    /**
     * Override gradebook item in escrow
     * @param mixed $gradebookitem
     * @return unknown_type
     * 
     * @see docroot/mod/turningtech/lib/ServiceProvider#overrideGradebookItemInEscrow()
     */
    public function overridegradebookiteminescrow($gradebookitem) {
        // Do nothing.
        return true;
    }
    /**
     * create fake gradebook item
     * 
     * @return unknown_type
     */
    private function _generatefakegradebookitemview() {
        $item = new stdClass();
        $item->creator = $this->_generaterandomstring();
        $item->itemTitle = $this->_generaterandomstring();
        $item->points = rand(5, 100);
        return $item;
    }
    /**
     * create fake grading error DTO
     * 
     * @return gradingErrorDto
     */
    private function _generatefakegradingerrordto() {
        $dto = new stdClass();
        $dto->deviceId = $this->_generaterandomstring();
        $dto->errorMessage = 'Fake: ' . $this->_generaterandomstring();
        $dto->itemTitle = $this->_generaterandomstring();
        return $dto;
    }
    /**
     * generate fake DTO
     * 
     * @return functionalCapabilityDto
     */
    private function _generatefakecapabilitydto() {
        $dto = new stdClass();
        $dto->description = $this->_generaterandomstring();
        $dto->name = $this->_generaterandomstring();
        $dto->permissions = $this->_generaterandomstring();
        return $dto;
    }
    /**
     * generates a fake course
     * 
     * @return CourseSiteView
     */
    private function _generatefakecoursesiteview() {
        $course = new stdClass();
        $course->id = $this->_generaterandomstring();
        $course->providerGroupId = $this->_generaterandomstring();
        $course->reference = $this->_generaterandomstring();
        $course->title = $this->_generaterandomstring();
        $course->type = $this->_generaterandomstring();
        return $course;
    }
    /**
     * generates a fake student
     * 
     * @return CourseParticipantDTO
     */
    private function _generatefakecourseparticipantdto() {
        $dto = new stdClass();
        $dto->deviceId = $this->_generaterandomstring();
        $dto->email = $this->_generaterandomstring();
        $dto->firstName = $this->_generaterandomstring();
        $dto->lastName = $this->_generaterandomstring();
        return $dto;
    }
    /**
     * spits out a random string
     * 
     * @param mixed $length
     * @return string
     */
    private function _generaterandomstring($length = 0) {
        $str = md5(uniqid(rand(), true));
        return ($length ? substr($str, 0, $length) : $str);
    }
    /**
     * Gets the extended roster for a class
     * @param object $course
     * @return array of CourseParticipantDTO objects
     * @see TurningTechServiceProvider::getextclassroster()
     */
    public function getextclassroster($course) {
        // TODO Auto-generated method stub.
    }
    /**
     * get a extended list of courses for an instructor
     * @param mixed $instructor
     * @return array of CourseSiteView
     * @see TurningTechServiceProvider::getextcoursesbyinstructor()
     */
    public function getextcoursesbyinstructor($instructor) {
        // TODO Auto-generated method stub.
    }
    /**
     * finds the device ID for this student
     * @param object $course
     * @param object $student
     * @return unknown_type
     * @see TurningTechServiceProvider::getdeviceidbycourseandstudent()
     */
    public function getdeviceidbycourseandstudent($course, $student) {
        // TODO Auto-generated method stub.
    }
    /**
     * attempt to save a grade item in the gradebook.  If an unknown
     * device ID is used, save in grade escrow instead.
     * @param object $course
     * @param mixed $dto
     * @param mixed $override
     * @return unknown_type
     * @see TurningTechServiceProvider::savegradebookitem()
     */
    public function savegradebookitem($course, $dto, $override = false) {
        // TODO Auto-generated method stub.
    }
    /**
     * attempt to
     * @param object $course
     * @param mixed $dto
     * @return unknown_type
     * @see TurningTechServiceProvider::addtoexistingscore()
     */
    public function addtoexistingscore($course, $dto) {
        // TODO Auto-generated method stub.
    }
    /**
     * check if user is enrolled as student in course
     * @param object $user
     * @param object $course
     * @return unknown_type
     * @see TurningTechServiceProvider::isuserstudentincourse()
     */
    public function isuserstudentincourse($user, $course) {
        // TODO Auto-generated method stub.
    }
    /**
     * import session data
     * @param unknown_type $exportdata
     * @return unknown_type
     * @see TurningTechServiceProvider::importsessiondata()
     */
    public function importsessiondata($exportdata) {
        // TODO Auto-generated method stub.
    }
}