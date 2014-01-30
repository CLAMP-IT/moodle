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
 * This File contains Abstract service provider class.  
 *
 * @package   mod_turningtech
 * @copyright 2012 Turning Technologies
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */
/**
 * This  Class serves as a central listing of all high-level functions.
 *
 * @package   mod_turningtech
 * @copyright 2012 Turning Technologies
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */
abstract class TurningTechServiceProvider {
    /**
     * Gets the account whose username and password is submitted in AES encrypted format.
     * @param string $aesusername
     * @param string $aespassword
     * @return Moodle User
     */
    public abstract function getuserbyaesauth($aesusername, $aespassword);

    /**
     * Gets the roster for a class
     * @param object $course
     * @return array of CourseParticipantDTO objects
     */
    public abstract function getclassroster($course);

    /**
     * Gets the extended roster for a class
     * @param object $course
     * @return array of CourseParticipantDTO objects
     */
    public abstract function getextclassroster($course);

    /**
     * fetch the course
     * @param int $siteid
     * @return Moodle Course
     */
    public abstract function getcoursebyid($siteid);

    /**
     * determine whether user can read the class roster
     * @param object $user
     * @param object $course
     * @return unknown_type
     */
    public abstract function userhasrosterpermission($user, $course);

    /**
     * get a list of courses for an instructor
     * @param mixed $instructor
     * @return array of CourseSiteView
     */
    public abstract function getcoursesbyinstructor($instructor);

    /**
     * get a extended list of courses for an instructor
     * @param mixed $instructor
     * @return array of CourseSiteView
     */
    public abstract function getextcoursesbyinstructor($instructor);

    /**
     * get capabilities of user
     * @param object $user
     * @return array of functionalCapabilityDto
     */
    public abstract function getusercapabilities($user);

    /**
     * create a new activity
     * @param object $course
     * @param mixed $title
     * @param mixed $points
     * @return array of gradingErrorDto
     */
    public abstract function creategradebookitem($course, $title, $points);

    /**
     * get list of gradebook items for a course
     * @param object $course
     * @return unknown_type
     */
    public abstract function getgradebookitemsbycourse($course);


    /**
     * finds the student associated with the given device ID in the given course.
     * @param object $course
     * @param mixed $deviceid
     * @return object student
     */
    public abstract function getstudentbycourseanddeviceid($course, $deviceid);

    /**
     * finds the device ID for this student
     * @param object $course
     * @param object $student
     * @return unknown_type
     */
    public abstract function getdeviceidbycourseandstudent($course, $student);

    /**
     * attempt to save a grade item in the gradebook.  If an unknown
     * device ID is used, save in grade escrow instead.
     * @param object $course
     * @param mixed $dto
     * @param mixed $override
     * @return unknown_type
     */
    public abstract function savegradebookitem($course, $dto, $override = false);

    /**
     * attempt to
     * @param object $course
     * @param mixed $dto
     * @return unknown_type
     */
    public abstract function addtoexistingscore($course, $dto);

    /**
     * check if user is enrolled as student in course
     * @param object $user
     * @param object $course
     * @return unknown_type
     */
    public abstract function isuserstudentincourse($user, $course);


    /**
     * import session data
     * @param unknown_type $exportdata
     * @return unknown_type
     */
    public abstract function importsessiondata($exportdata);

}