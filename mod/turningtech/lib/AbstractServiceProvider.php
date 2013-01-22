<?php
/**
 * Abstract service provider class.  This serves as a central listing
 * of all high-level functions.
 *
 * @author jacob
 *
 */
abstract class TurningTechServiceProvider {
    /**
     * Gets the account whose username and password is submitted in AES encrypted format.
     * @param $AESusername
     * @param $AESpassword
     * @return Moodle User
     */
    abstract function getUserByAESAuth($AESusername, $AESpassword);

    /**
     * Gets the roster for a class
     * @param $course
     * @return array of CourseParticipantDTO objects
     */
    abstract function getClassRoster($course);

    /**
     * Gets the extended roster for a class
     * @param $course
     * @return array of CourseParticipantDTO objects
     */
    abstract function getExtClassRoster($course);

    /**
     * fetch the course
     * @param $siteId
     * @return Moodle Course
     */
    abstract function getCourseById($siteId);

    /**
     * determine whether user can read the class roster
     * @param $user
     * @param $course
     * @return unknown_type
     */
    abstract function userHasRosterPermission($user, $course);

    /**
     * get a list of courses for an instructor
     * @param $instructor
     * @return array of CourseSiteView
     */
    abstract function getCoursesByInstructor($instructor);

    /**
     * get a extended list of courses for an instructor
     * @param $instructor
     * @return array of CourseSiteView
     */
    abstract function getExtCoursesByInstructor($instructor);

    /**
     * get capabilities of user
     * @param $user
     * @return array of functionalCapabilityDto
     */
    abstract function getUserCapabilities($user);

    /**
     * create a new activity
     * @param $course
     * @param $title
     * @param $points
     * @return array of gradingErrorDto
     */
    abstract function createGradebookItem($course, $title, $points);

    /**
     * get list of gradebook items for a course
     * @param $course
     * @return unknown_type
     */
    abstract function getGradebookItemsByCourse($course);


    /**
     * finds the student associated with the given device ID in the given course.
     * @param $course
     * @param $deviceId
     * @return student
     */
    abstract function getStudentByCourseAndDeviceId($course, $deviceId);

    /**
     * finds the device ID for this student
     * @param $course
     * @param $student
     * @return unknown_type
     */
    abstract function getDeviceIdByCourseAndStudent($course, $student);

    /**
     * attempt to save a grade item in the gradebook.  If an unknown
     * device ID is used, save in grade escrow instead.
     * @param $course
     * @param $dto
     * @param $override
     * @return unknown_type
     */
    abstract function saveGradebookItem($course, $dto, $override = FALSE);

    /**
     * attempt to
     * @param $course
     * @param $dto
     * @return unknown_type
     */
    abstract function addToExistingScore($course, $dto);

    /**
     * check if user is enrolled as student in course
     * @param $user
     * @param $course
     * @return unknown_type
     */
    abstract function isUserStudentInCourse($user, $course);

    // ~~ abstract function importSessionData($course, $exportData);
    abstract function importSessionData($exportData);

}
?>