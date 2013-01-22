<?php
/**
 * Class that delegates requests for Moodle and TurningPoint operations
 */
global $CFG, $DB;
require_once($CFG->dirroot . '/mod/turningtech/lib/AbstractServiceProvider.php');
require_once($CFG->dirroot . '/mod/turningtech/lib/helpers/MoodleHelper.php');
require_once($CFG->dirroot . '/mod/turningtech/lib/helpers/TurningHelper.php');
require_once($CFG->dirroot . '/mod/turningtech/lib/helpers/EncryptionHelper.php');

class TurningTechIntegrationServiceProvider extends TurningTechServiceProvider {
    /**
     * constructor
     * @return unknown_type
     */
    public function TurningTechIntegrationServiceProvider() {
    }

    /**
     * (non-PHPdoc)
     * @see docroot/mod/turningtech/lib/ServiceProvider#getUserByAESAuth()
     */
    public function getUserByAESAuth($AESusername, $AESpassword) {
        global $USER;
        list($username, $password) = decryptWebServicesStrings(array(
            $AESusername,
            $AESpassword
        ));
        $USER = TurningTechMoodleHelper::authenticateUser($username, $password);
        return $USER;
    }

    /**
     * (non-PHPdoc)
     * @see docroot/mod/turningtech/lib/ServiceProvider#getClassRoster()
     */
    public function getClassRoster($course) {
        $roster = array();
        if ($participants = TurningTechMoodleHelper::getClassRoster($course, FALSE, FALSE, "d.created")) {
            foreach ($participants as $participant) {
                $roster[] = $this->generateCourseParticipantDTO($participant, $course);
            }
        }

        return $roster;
    }

    /**
     * (non-PHPdoc)
     * @see docroot/mod/turningtech/lib/ServiceProvider#getExtClassRoster()
     */
    public function getExtClassRoster($course) {
        $roster = array();
        if ($participants = TurningTechMoodleHelper::getExtClassRoster($course)) {
            foreach ($participants as $participant) {
                $roster[] = $this->generateExtCourseParticipantDTO($participant, $course);
            }
        }

        return $roster;
    }

    /**
     * check if a user is enrolled as a student in the course
     * @param $user
     * @param $course
     * @return unknown_type
     */
    public function isUserStudentInCourse($user, $course) {
        return TurningTechMoodleHelper::isUserStudentInCourse($user, $course);
    }

    /**
     * (non-PHPdoc)
     * @see docroot/mod/turningtech/lib/ServiceProvider#getCourseById()
     */
    public function getCourseById($siteId) {
        global $DB;
        // technically, this should live in moodleHelper... but it's already
        // abstracted away into oblivion
        return $DB->get_record("course", array(
            "id" => $siteId
        ));
    }

    /**
     * (non-PHPdoc)
     * @see docroot/mod/turningtech/lib/ServiceProvider#userHasRosterPermission()
     */
    public function userHasRosterPermission($user, $course) {
        // delegate to moodle helper
        return TurningTechMoodleHelper::userHasRosterPermission($user, $course);
    }

    /**
     * (non-PHPdoc)
     * @see docroot/mod/turningtech/lib/ServiceProvider#getCoursesByInstructor()
     */
    public function getCoursesByInstructor($instructor) {
        $moodle_courses = TurningTechMoodleHelper::getInstructorCourses($instructor);
        $courses        = array();
        foreach ($moodle_courses as $c) {
            $courses[] = $this->generateCourseSiteView($c);
        }
        return $courses;
    }

    /**
     * (non-PHPdoc)
     * @see docroot/mod/turningtech/lib/ServiceProvider#getExtCoursesByInstructor()
     */
    public function getExtCoursesByInstructor($instructor) {
        $moodle_courses = TurningTechMoodleHelper::getExtInstructorCourses($instructor);
        $courses        = array();
        foreach ($moodle_courses as $c) {
            $courses[] = $this->generateCourseSiteView($c);
        }
        return $courses;
    }

    /**
     * (non-PHPdoc)
     * @see docroot/mod/turningtech/lib/ServiceProvider#getUserCapabilities()
     */
    public function getUserCapabilities($user) {
        $cap              = array();
        $dto              = new stdClass();
        $dto->description = get_string('getcoursesforteacherdesc', 'turningtech');
        $dto->name        = 'getCoursesForTeacher';
        $dto->permissions = array();
        $cap[]            = $dto;
        return $cap;
    }

    /**
     * (non-PHPdoc)
     * @see docroot/mod/turningtech/lib/ServiceProvider#createGradebookItem()
     */
    public function createGradebookItem($course, $title, $points) {
        global $USER;

        // holds any error messages
        $dto = new stdClass();

        if (!TurningTechMoodleHelper::userHasGradeItemPermission($USER, $course)) {
            $dto->itemTitle    = $title;
            $dto->errorMessage = get_string('nogradeitempermission', 'turningtech');
            return $dto;
        }

        return TurningTechMoodleHelper::createGradebookItem($course, $title, $points);
    }

    /**
     * (non-PHPdoc)
     * @see docroot/mod/turningtech/lib/ServiceProvider#getGradebookItemsByCourse()
     */
    public function getGradebookItemsByCourse($course) {
        $items = TurningTechMoodleHelper::getGradebookItemsByCourse($course);
        //echo "<pre>" . print_r($items, TRUE) . "</pre>";
        for ($i = 0; $i < count($items); $i++) {
            $items[$i] = $this->generateGradebookItemView($items[$i]);
        }

        return $items;
    }

    /**
     * (non-PHPdoc)
     * @see docroot/mod/turningtech/lib/ServiceProvider#createGradebookItemInstance()
     */
    public function createGradebookItemInstance($course, $title) {
        return TurningTechMoodleHelper::getGradebookItemByCourseAndTitle($course, $title);
    }


    /**
     * (non-PHPdoc)
     * @see docroot/mod/turningtech/lib/ServiceProvider#getStudentByCourseAndDeviceId()
     */
    public function getStudentByCourseAndDeviceId($course, $deviceId) {
        return TurningTechTurningHelper::getStudentByCourseAndDeviceId($course, $deviceId);
    }

    /**
     * (non-PHPdoc)
     * @see docroot/mod/turningtech/lib/ServiceProvider#getDeviceIdByCourseAndStudent()
     */
    public function getDeviceIdByCourseAndStudent($course, $student) {
        return TurningTechTurningHelper::getDeviceIdByCourseAndStudent($course, $student);
    }

    /**
     * (non-PHPdoc)
     * @see docroot/mod/turningtech/lib/ServiceProvider#saveGradebookItem()
     */
    public function saveGradebookItem($course, $dto, $mode = TURNINGTECH_SAVE_NO_OVERRIDE) {
        // prepare the error just in case
        $error            = new stdClass();
        $error->deviceId  = $dto->deviceId;
        $error->itemTitle = $dto->itemTitle;

        // get the gradebook item for this transaction
        $grade_item = TurningTechMoodleHelper::getGradebookItemByCourseAndTitle($course, $dto->itemTitle);
        if (!$grade_item) {
            $error->errorMessage = get_string('couldnotfindgradeitem', 'turningtech', $dto);
            return $error;
        }

        // see if there is a student associated with this device id
        $student = $this->getStudentByCourseAndDeviceId($course, $dto->deviceId);
        if (!$student) {
            // no device association for this device, so save in escrow
            $escrow = TurningTechTurningHelper::getEscrowInstance($course, $dto, $grade_item, FALSE);
            // check if we can't override an existing entry
            if (($mode == TURNINGTECH_SAVE_NO_OVERRIDE) && $escrow->getId()) {
                $error->errorMessage = get_string('cannotoverridegrade', 'turningtech');
            }
            // inversely, check if we're trying to override a grade but none was found
            else if (($mode == TURNINGTECH_SAVE_ONLY_OVERRIDE) && !$escrow->getId()) {
                $error->errorMessage = get_string('existingitemnotfound', 'turningtech');
            }
            // otherwise we don't care and the escrow item can be saved
            else {
                $escrow->setField('points_earned', $dto->pointsEarned);
                $escrow->setField('points_possible', $dto->pointsPossible);

                if ($escrow->save()) {
                    $error->errorMessage = get_string('gradesavedinescrow', 'turningtech');
                } else {
                    $error->errorMessage = get_string('errorsavingescrow', 'turningtech');
                }
            }
        } else {
            // we have a student, so we can write directly to the gradebook.  First
            // we need to check if we can't/must override existing grade
            $exists = TurningTechMoodleHelper::gradeAlreadyExists($student, $grade_item);
            if (($mode == TURNINGTECH_SAVE_NO_OVERRIDE) && $exists) {
                $error->errorMessage = get_string('cannotoverridegrade', 'turningtech');
            } else if (($mode == TURNINGTECH_SAVE_ONLY_OVERRIDE) && !$exists) {
                $error->errorMessage = get_string('existingitemnotfound', 'turningtech');
            } else {
                // save the grade
                if ($grade_item->update_final_grade($student->id, $dto->pointsEarned, 'gradebook')) {
                    // everything is fine, no error to return. Save an escrow entry just to record
                    // the transaction
                    $escrow = TurningTechEscrow::generate(array(
                        'deviceid' => $dto->deviceId,
                        'courseid' => $course->id,
                        'itemid' => $grade_item->id,
                        'points_possible' => $dto->pointsPossible,
                        'points_earned' => $dto->pointsEarned,
                        'migrated' => TRUE
                    ));
                    $escrow->save();
                    $error = FALSE;
                } else {
                    echo "<p>grade not saved successfully, creating escrow entry</p>\n";
                    // could not save in gradebook.  Create escrow item and save it
                    $escrow = TurningTechTurningHelper::getEscrowInstance($course, $dto, $grade_item, FALSE);
                    $escrow->setField('points_earned', $dto->pointsEarned);
                    $escrow->save();
                    $error->errorMessage = get_string('errorsavinggradeitemsavedinescrow', 'turningtech');
                }
            }
        }

        return $error;
    }

    /**
     * (non-PHPdoc)
     * @see docroot/mod/turningtech/lib/ServiceProvider#addToExistingScore()
     */
    public function addToExistingScore($course, $dto) {
        // prepare the error just in case
        $error            = new stdClass();
        $error->deviceId  = $dto->deviceId;
        $error->itemTitle = $dto->itemTitle;

        // get the gradebook item for this transaction
        $grade_item = TurningTechMoodleHelper::getGradebookItemByCourseAndTitle($course, $dto->itemTitle);
        if (!$grade_item) {
            $error->errorMessage = get_string('couldnotfindgradeitem', 'turningtech', $dto);
            return $error;
        }

        // see if there is a student associated with this device id
        $student = $this->getStudentByCourseAndDeviceId($course, $dto->deviceId);
        if (!$student) {
            // no device association for this device, so save in escrow
            $escrow = TurningTechTurningHelper::getEscrowInstance($course, $dto, $grade_item, FALSE);
            // verify this is an existing item
            if (!$escrow->getId()) {
                $error->errorMessage = get_string('existingitemnotfound', 'turningtech');
            } else {
                $escrow->setField('points_earned', ($escrow->getField('points_earned') + $dto->pointsEarned));
                if ($escrow->save()) {
                    $error->errorMessage = get_string('gradesavedinescrow', 'turningtech');
                } else {
                    $error->errorMessage = get_string('errorsavingescrow', 'turningtech');
                }
            }
        } else {
            $grade = TurningTechMoodleHelper::getGradeRecord($student, $grade_item);
            if (!$grade) {
                $error->errorMessage = get_string('existingitemnotfound', 'turningtech');
            } else {
                $grade_item->update_final_grade($student->id, ($grade->finalgrade + $dto->pointsEarned), 'gradebook');
                $error = FALSE;
            }
        }

        return $error;
    }

    /**
     * check the escrow table to see if there are any entries that correspond to
     * the given device map.  If so, move them into the database
     * @param $devicemap
     * @return unknown_type
     */
    public static function migrateEscowGrades($devicemap) {
        global $DB;
        $conditions             = array();
        $conditions['deviceid'] = "'{$devicemap->getField('deviceid')}'";
        $conditions['migrated'] = FALSE;
        if (!$devicemap->isAllCourses()) {
            $conditions['courseid'] = $devicemap->getField('courseid');
        }

        $sql   = TurningModel::buildWhereClause($conditions);
        $items = $DB->get_records_select('turningtech_escrow', $sql);
        if ($items) {
            foreach ($items as $item) {
                $escrow = TurningTechEscrow::generate($item);
                self::doGradeMigration($devicemap, $escrow);
            }
        }
    }

    /**
     * add a new entry to the gradebook for escrow item using information provided
     * by the device map.
     * @param $devicemap
     * @param $escrow
     * @return unknown_type
     */
    public static function doGradeMigration($devicemap, $escrow) {
        if ($grade_item = TurningTechMoodleHelper::getGradebookItemById($escrow->getField('itemid'))) {
            $grade_item->update_final_grade($devicemap->getField('userid'), $escrow->getField('points_earned'), 'gradebook');
            $escrow->setField('migrated', 1);
            $escrow->save();
        }
    }

    public function importSessionData($exportData) {
        $arr    = array();
        $arr[0] = TurningTechTurningHelper::importSessionData($exportData);

        return $arr;
    }

    /**
     * create fake gradebook item
     * @return unknown_type
     */
    private function generateGradebookItemView($gradeitem) {
        $item            = new stdClass();
        $item->itemTitle = $gradeitem->itemname;
        $item->points    = $gradeitem->grademax;
        return $item;
    }


    /**
     * generates a fake course
     * @return CourseSiteView
     */
    private function generateCourseSiteView($course) {
        $view        = new stdClass();
        $view->id    = $course->id;
        $view->title = $course->fullname;
        $view->type  = $course->category;
        return $view;
    }

    /**
     * translates a Moodle user into a course participant DTO
     * @return CourseParticipantDTO
     */
    private function generateCourseParticipantDTO($participant, $course) {
        $dto           = new stdClass();
        $dto->deviceId = NULL;
        if (!empty($participant->deviceid)) {
            $dto->deviceId = $participant->deviceid;
        } else {
			$dto->deviceId = '';
		}

        $dto->email     = $participant->email;
        $dto->firstName = $participant->firstname;
        $dto->lastName  = $participant->lastname;
        $dto->loginId   = $participant->username;
        $dto->userId    = $participant->id;

        return $dto;
    }

    /**
     * translates a Moodle user into a course participant DTO for Phoenix.
     * @return CourseParticipantDTO
     */
    private function generateExtCourseParticipantDTO($participant, $course) {
        $dto           = new stdClass();
        $dto->deviceId = NULL;

        if (!empty($participant->deviceid)) {
            $dto->deviceId = $participant->deviceid;
        } else {
			$dto->deviceId = '';
		}

        $dto->email     = $participant->email;
        $dto->firstName = $participant->firstname;
        $dto->lastName  = $participant->lastname;
        $dto->userId    = $participant->username;

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