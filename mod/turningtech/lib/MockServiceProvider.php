<?php
/**
 * mock implementation of ServiceProvider, used for dev testing
 */
global $CFG;
require_once($CFG->dirroot . '/mod/turningtech/lib/AbstractServiceProvider.php');

class TurningTechMockServiceProvider extends TurningTechServiceProvider {
    /**
     * (non-PHPdoc)
     * @see docroot/mod/turningtech/lib/ServiceProvider#getUserByAESAuth()
     */
    public function getUserByAESAuth($AESusername, $AESpassword) {
        global $USER;
        $USER = authenticate_user_login($AESusername, $AESpassword);
        return $USER;
    }

    /**
     * (non-PHPdoc)
     * @see docroot/mod/turningtech/lib/ServiceProvider#getClassRoster()
     */
    public function getClassRoster($course) {
        $roster = array();
        $num    = rand(0, 15);
        for ($i = 0; $i < $num; $i++) {
            $roster[] = $this->_generateFakeCourseParticipantDTO();
        }

        return $roster;
    }

    /**
     * (non-PHPdoc)
     * @see docroot/mod/turningtech/lib/ServiceProvider#getCourseById()
     */
    public function getCourseById($siteId) {
        $course = new stdClass();
        // do something
        return $course;
    }

    /**
     * (non-PHPdoc)
     * @see docroot/mod/turningtech/lib/ServiceProvider#userHasRosterPermission()
     */
    public function userHasRosterPermission($user, $course) {
        return TRUE;
    }

    /**
     * (non-PHPdoc)
     * @see docroot/mod/turningtech/lib/ServiceProvider#getCoursesByInstructor()
     */
    public function getCoursesByInstructor($instructor) {
        $courses = array();
        $num     = rand(0, 5);
        for ($i = 0; $i < $num; $i++) {
            $courses[] = $this->_generateFakeCourseSiteView();
        }

        return $courses;
    }

    /**
     * (non-PHPdoc)
     * @see docroot/mod/turningtech/lib/ServiceProvider#getUserCapabilities()
     */
    public function getUserCapabilities($user) {
        $cap = array();
        $num = rand(0, 10);
        for ($i = 0; $i < $num; $i++) {
            $cap[] = $this->_generateFakeCapabilityDto();
        }

        return $cap;
    }

    /**
     * (non-PHPdoc)
     * @see docroot/mod/turningtech/lib/ServiceProvider#createGradebookItem()
     */
    public function createGradebookItem($course, $title, $points) {
        return array();
    }

    /**
     * (non-PHPdoc)
     * @see docroot/mod/turningtech/lib/ServiceProvider#getGradebookItemsByCourse()
     */
    public function getGradebookItemsByCourse($course) {
        $items = array();
        $num   = rand(0, 10);
        for ($i = 0; $i < $num; $i++) {
            $items[] = $this->_generateFakeGradebookItemView();
        }
        return $items;
    }

    /**
     * (non-PHPdoc)
     * @see docroot/mod/turningtech/lib/ServiceProvider#createGradebookItemInstance()
     */
    public function createGradebookItemInstance($course, $title) {
        return new GradebookItem($title);
    }

    /**
     * (non-PHPdoc)
     * @see docroot/mod/turningtech/lib/ServiceProvider#getExistingGradebookItem()
     */
    public function getExistingGradebookItem($course, $student, $title) {
        $item = new GradebookItem($title);
        $item->setStudent($student);
        return $item;
    }

    /**
     * (non-PHPdoc)
     * @see docroot/mod/turningtech/lib/ServiceProvider#getExistingGradebookItemFromEscrow()
     */
    public function getExistingGradebookItemFromEscrow($course, $deviceId, $title) {
        return new GradebookItem($title);
    }

    /**
     * (non-PHPdoc)
     * @see docroot/mod/turningtech/lib/ServiceProvider#getStudentByCourseAndDeviceId()
     */
    public function getStudentByCourseAndDeviceId($course, $deviceId) {
        $student     = new stdClass();
        $student->id = $this->_generateRandomString();
        return $student;
    }

    /**
     * (non-PHPdoc)
     * @see docroot/mod/turningtech/lib/ServiceProvider#saveGradebookItemInEscrow()
     */
    public function saveGradebookItemInEscrow($gradebookItem) {
        // do nothing
        return TRUE;
    }

    /**
     * (non-PHPdoc)
     * @see docroot/mod/turningtech/lib/ServiceProvider#overrideGradebookItemInEscrow()
     */
    public function overrideGradebookItemInEscrow($gradebookItem) {
        // do nothing
        return TRUE;
    }

    /**
     * create fake gradebook item
     * @return unknown_type
     */
    private function _generateFakeGradebookItemView() {
        $item            = new stdClass();
        $item->creator   = $this->_generateRandomString();
        $item->itemTitle = $this->_generateRandomString();
        $item->points    = rand(5, 100);
        return $item;
    }

    /**
     * create fake grading error DTO
     * @return gradingErrorDto
     */
    private function _generateFakeGradingErrorDto() {
        $dto               = new stdClass();
        $dto->deviceId     = $this->_generateRandomString();
        $dto->errorMessage = 'Fake: ' . $this->_generateRandomString();
        $dto->itemTitle    = $this->_generateRandomString();
        return $dto;
    }

    /**
     * generate fake DTO
     * @return functionalCapabilityDto
     */
    private function _generateFakeCapabilityDto() {
        $dto              = new stdClass();
        $dto->description = $this->_generateRandomString();
        $dto->name        = $this->_generateRandomString();
        $dto->permissions = $this->_generateRandomString();
        return $dto;
    }

    /**
     * generates a fake course
     * @return CourseSiteView
     */
    private function _generateFakeCourseSiteView() {
        $course                  = new stdClass();
        $course->id              = $this->_generateRandomString();
        $course->providerGroupId = $this->_generateRandomString();
        $course->reference       = $this->_generateRandomString();
        $course->title           = $this->_generateRandomString();
        $course->type            = $this->_generateRandomString();
        return $course;
    }

    /**
     * generates a fake student
     * @return CourseParticipantDTO
     */
    private function _generateFakeCourseParticipantDTO() {
        $dto            = new stdClass();
        $dto->deviceId  = $this->_generateRandomString();
        $dto->email     = $this->_generateRandomString();
        $dto->firstName = $this->_generateRandomString();
        $dto->lastName  = $this->_generateRandomString();

        return $dto;
    }

    /**
     * spits out a random string
     * @param $length
     * @return string
     */
    private function _generateRandomString($length = 0) {
        $str = md5(uniqid(rand(), TRUE));
        return ($length ? substr($str, 0, $length) : $str);
    }

}
?>