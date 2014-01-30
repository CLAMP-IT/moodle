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
 * File to  delegates requests for Moodle and TurningPoint operations
 * @package   mod_turningtech
 * @copyright 2012 Turning Technologies
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */
global $CFG, $DB;
require_once($CFG->dirroot . '/mod/turningtech/lib/AbstractServiceProvider.php');
require_once($CFG->dirroot . '/mod/turningtech/lib/helpers/MoodleHelper.php');
require_once($CFG->dirroot . '/mod/turningtech/lib/helpers/TurningHelper.php');
require_once($CFG->dirroot . '/mod/turningtech/lib/helpers/EncryptionHelper.php');
/**
 * Class that delegates requests for Moodle and TurningPoint operations
 * @copyright 2012 Turning Technologies
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */
class TurningTechIntegrationServiceProvider extends TurningTechServiceProvider {
    /**
     * constructor
     * 
     * @return unknown_type
     */
    public function __construct() {
    }
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
        list($username, $password) = turningtech_decryptwsstrings(array ($aesusername, $aespassword ));
        $USER = TurningTechMoodleHelper::authenticateuser($username, $password);
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
        if ($participants = TurningTechMoodleHelper::getclassroster($course, false, false, "d.created")) {
            foreach ($participants as $participant) {
                $roster[] = $this->generatecourseparticipantdto($participant, $course);
            }
        }
        return $roster;
    }
    /**
     * Gets the extended roster for a class
     * @param object $course
     * @return array of CourseParticipantDTO objects
     * 
     * @see docroot/mod/turningtech/lib/ServiceProvider#getextclassroster()
     */
    public function getextclassroster($course) {
        $roster = array ();
        if ($participants = TurningTechMoodleHelper::getextclassroster($course)) {
            foreach ($participants as $participant) {
                $roster[] = $this->generateextcourseparticipantdto($participant, $course);
            }
        }
        return $roster;
    }
    /**
     * check if a user is enrolled as a student in the course
     * 
     * @param object $user
     * @param object $course
     * @return unknown_type
     */
    public function isuserstudentincourse($user, $course) {
        return TurningTechMoodleHelper::isuserstudentincourse($user, $course);
    }
    /**
     * fetch the course
     * @param int $siteid
     * @return Moodle Course
     * 
     * @see docroot/mod/turningtech/lib/ServiceProvider#getcoursebyid()
     */
    public function getcoursebyid($siteid) {
        global $DB;
        // Technically, this should live in moodleHelper... but it's already
        // Abstracted away into oblivion.
        return $DB->get_record("course", array ("id" => $siteid ));
    }
    /**
     * determine whether user can read the class roster
     * @param object $user
     * @param object $course
     * @return unknown_type
     * 
     * @see docroot/mod/turningtech/lib/ServiceProvider#userhasrosterpermission()
     */
    public function userhasrosterpermission($user, $course) {
        // Delegate to moodle helper.
        return TurningTechMoodleHelper::userhasrosterpermission($user, $course);
    }
    /**
     * get a list of courses for an instructor
     * @param mixed $instructor
     * @return array of CourseSiteView
     * 
     * @see docroot/mod/turningtech/lib/ServiceProvider#getcoursesbyinstructor()
     */
    public function getcoursesbyinstructor($instructor) {
        $moodle_courses = TurningTechMoodleHelper::getinstructorcourses($instructor);
        $courses = array ();
        foreach ($moodle_courses as $c) {
            $courses[] = $this->generatecoursesiteview($c);
        }
        return $courses;
    }
    /**
     * get a extended list of courses for an instructor
     * @param mixed $instructor
     * @return array of CourseSiteView
     * 
     * @see docroot/mod/turningtech/lib/ServiceProvider#getextcoursesbyinstructor()
     */
    public function getextcoursesbyinstructor($instructor) {
        $moodle_courses = TurningTechMoodleHelper::getextinstructorcourses($instructor);
        $courses = array ();
        foreach ($moodle_courses as $c) {
            $courses[] = $this->generatecoursesiteview($c);
        }
        return $courses;
    }
    /**
     * get capabilities of user
     * @param object $user
     * @return array of functionalCapabilityDto
     * 
     * @see docroot/mod/turningtech/lib/ServiceProvider#getusercapabilities()
     */
    public function getusercapabilities($user) {
        $cap = array ();
        $dto = new stdClass();
        $dto->description = get_string('getcoursesforteacherdesc', 'turningtech');
        $dto->name = 'getCoursesForTeacher';
        $dto->permissions = array ();
        $cap[] = $dto;
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
        global $USER;
        // Holds any error messages.
        $dto = new stdClass();
        if (! TurningTechMoodleHelper::userhasgradeitempermission($USER, $course)) {
            $dto->itemTitle = $title;
            $dto->errorMessage = get_string('nogradeitempermission', 'turningtech');
            return $dto;
        }
        return TurningTechMoodleHelper::creategradebookitem($course, $title, $points);
    }
    /**
     * get list of gradebook items for a course
     * @param object $course
     * @return unknown_type
     * 
     * @see docroot/mod/turningtech/lib/ServiceProvider#getgradebookitemsbycourse()
     */
    public function getgradebookitemsbycourse($course) {
        $items = TurningTechMoodleHelper::getgradebookitemsbycourse($course);
        for ($i = 0; $i < count($items); $i ++) {
            $items[$i] = $this->generategradebookitemview($items[$i]);
        }
        return $items;
    }
    /**
     * Create Gradebook item instance
     * @param unknown_type $course
     * @param unknown_type $title
     * @return Ambigous <unknown_type, grade_item, boolean, mixed>
     */
    public function creategradebookiteminstance($course, $title) {
        return TurningTechMoodleHelper::getgradebookitembycourseandtitle($course, $title);
    }
    /**
     * finds the student associated with the given device ID in the given course.
     * @param object $course
     * @param mixed $deviceid
     * @return object student
     * 
     * @see docroot/mod/turningtech/lib/ServiceProvider#getstudentbycourseanddeviceid()
     */
    public function getstudentbycourseanddeviceid($course, $deviceid) {
        return TurningTechTurningHelper::getstudentbycourseanddeviceid($course, $deviceid);
    }
    /**
     * Get Student from course and userid
     * @param unknown_type $course
     * @param unknown_type $userid
     * @return Ambigous <unknown_type, boolean, mixed, stdClass, false>
     */
    public function getstudentbycourseanduserid($course, $userid) {
        return TurningTechTurningHelper::getstudentbycourseanduserid($course, $userid);
    }
    /**
     * Get Student from username
     * @param unknown_type $username
     * @return Ambigous <mixed, boolean, unknown_type, stdClass, false>
     * @see docroot/mod/turningtech/lib/ServiceProvider#getstudentbycourseanddeviceid()
     */
    public function getstudentbyusername($username) {
        return TurningTechTurningHelper::getstudentbyusername($username);
    }
    /**
     * finds the device ID for this student
     * @param object $course
     * @param object $student
     * @return unknown_type
     * 
     * @see docroot/mod/turningtech/lib/ServiceProvider#getdeviceidbycourseandstudent()
     */
    public function getdeviceidbycourseandstudent($course, $student) {
        return TurningTechTurningHelper::getdeviceidbycourseandstudent($course, $student);
    }
    /**
     * attempt to save a grade item in the gradebook.  If an unknown
     * device ID is used, save in grade escrow instead.
     * @param object $course
     * @param mixed $dto
     * @param mixed $mode
     * @return unknown_type
     * 
     * @see docroot/mod/turningtech/lib/ServiceProvider#savegradebookitem()
     */
    public function savegradebookitem($course, $dto, $mode = TURNINGTECH_SAVE_NO_OVERRIDE) {
        // Prepare the error just in case.
        $error = new stdClass();
        if (strlen($dto->deviceId)<6) {
            $deviceid = str_pad($dto->deviceId, 6, "0", STR_PAD_LEFT);
            $dto->deviceId = $deviceid;
        }
        $error->deviceId = $dto->deviceId;
        $error->itemTitle = $dto->itemTitle;
        // Get the gradebook item for this transaction.
        $grade_item = TurningTechMoodleHelper::getgradebookitembycourseandtitle($course, $dto->itemTitle);
        if (! $grade_item) {
            $error->errorMessage = get_string('couldnotfindgradeitem', 'turningtech', $dto);
            return $error;
        }
        // See if there is a student associated with this device id.
        $student = $this->getstudentbycourseanddeviceid($course, $dto->deviceId);
        if (! $student) {
            // No device association for this device, so save in escrow.
            $escrow = TurningTechTurningHelper::getescrowinstance($course, $dto, $grade_item, false);
            // Check if we can't override an existing entry.
            if (($mode == TURNINGTECH_SAVE_NO_OVERRIDE) && $escrow->getId()) {
                $error->errorMessage = get_string('cannotoverridegrade', 'turningtech');
                // Inversely, check if we're trying to override a grade but none was found.
            } else if (($mode == TURNINGTECH_SAVE_ONLY_OVERRIDE) && ! $escrow->getId()) {
                $error->errorMessage = get_string('existingitemnotfound', 'turningtech');
                // Otherwise we don't care and the escrow item can be saved.
            } else {
                $escrow->setField('points_earned', $dto->pointsEarned);
                $escrow->setField('points_possible', $dto->pointsPossible);
                $escrow->setField('migrated', 0);
                if ($escrow->save()) {
                    $error->errorMessage = get_string('gradesavedinescrow', 'turningtech');
                } else {
                    $error->errorMessage = get_string('errorsavingescrow', 'turningtech');
                }
            }
        } else {
            // We have a student, so we can write directly to the gradebook. First.
            // We need to check if we can't/must override existing grade.
            $exists = TurningTechMoodleHelper::gradealreadyexists($student, $grade_item);
            if (($mode == TURNINGTECH_SAVE_NO_OVERRIDE) && $exists) {
                $error->errorMessage = get_string('cannotoverridegrade', 'turningtech');
            } else if (($mode == TURNINGTECH_SAVE_ONLY_OVERRIDE) && ! $exists) {
                $error->errorMessage = get_string('existingitemnotfound', 'turningtech');
            } else {
                // Save the grade.
                if ($grade_item->update_final_grade($student->id, $dto->pointsEarned, 'gradebook')) {
                    // Everything is fine, no error to return. Save an escrow entry just to record.
                    // the transaction.
                    $escrow = TurningTechEscrow::generate(array ('deviceid' => $dto->deviceId, 'courseid' => $course->id,
                                     'itemid' => $grade_item->id, 'points_possible' => $dto->pointsPossible,
                                     'points_earned' => $dto->pointsEarned, 'migrated' => true ));
                    $escrow->save();
                    $error = false;
                } else {
                    echo "<p>grade not saved successfully, creating escrow entry</p>\n";
                    // Could not save in gradebook. Create escrow item and save it.
                    $escrow = TurningTechTurningHelper::getescrowinstance($course, $dto, $grade_item, false);
                    $escrow->setField('points_earned', $dto->pointsEarned);
                    $escrow->save();
                    $error->errorMessage = get_string('errorsavinggradeitemsavedinescrow', 'turningtech');
                }
            }
        }
        return $error;
    }
    /**
     * Extended attempt to save a grade item in the gradebook.  If an unknown
     * device ID is used, save in grade escrow instead.
     * @param object $course
     * @param mixed $dto
     * @param mixed $mode
     * @return unknown_type
     * 
     * @see docroot/mod/turningtech/lib/ServiceProvider#savegradebookitem()
     */
    public function savegradebookitemext($course, $dto, $mode = TURNINGTECH_SAVE_NO_OVERRIDE) {
        // Prepare the error just in case.
        $error = new stdClass();
        $error->userId = $dto->userId;
        $error->itemTitle = $dto->itemTitle;
        // Set default device id.
        if (! isset($dto->deviceId)) {
            $dto->deviceId = '';
        }
        // Get the gradebook item for this transaction.
        $grade_item = TurningTechMoodleHelper::getgradebookitembycourseandtitle($course, $dto->itemTitle);
        if (! $grade_item) {
            $error->errorMessage = get_string('couldnotfindgradeitem', 'turningtech', $dto);
            return $error;
        }
        $studentbyusername = $this->getstudentbyusername($dto->userId);
        if ($studentbyusername) {
            $student = $this->getstudentbycourseanduserid($course, $studentbyusername->id);
        } else {
            $student = false;
        }
        if (! $student) {
            // No device association for this device, so save in escrow.
            $escrow = TurningTechTurningHelper::getescrowinstance($course, $dto, $grade_item, false);
            // Check if we can't override an existing entry.
            if (($mode == TURNINGTECH_SAVE_NO_OVERRIDE) && $escrow->getId()) {
                $error->errorMessage = get_string('cannotoverridegrade', 'turningtech');
                // Inversely, check if we're trying to override a grade but none was found.
            } else if (($mode == TURNINGTECH_SAVE_ONLY_OVERRIDE) && ! $escrow->getId()) {
                $error->errorMessage = get_string('existingitemnotfound', 'turningtech');
            } else { // Otherwise we don't care and the escrow item can be saved.
                $escrow->setField('points_earned', $dto->pointsEarned);
                $escrow->setField('points_possible', $dto->pointsPossible);
                $escrow->setField('migrated', 0);
                if ($escrow->save()) {
                    $error->errorMessage = get_string('gradesavedinescrow', 'turningtech');
                } else {
                    $error->errorMessage = get_string('errorsavingescrow', 'turningtech');
                }
            }
        } else {
            // We have a student, so we can write directly to the gradebook. First
            // We need to check if we can't/must override existing grade.
            $exists = TurningTechMoodleHelper::gradealreadyexists($student, $grade_item);
            if (($mode == TURNINGTECH_SAVE_NO_OVERRIDE) && $exists) {
                $error->errorMessage = get_string('cannotoverridegrade', 'turningtech');
            } else if (($mode == TURNINGTECH_SAVE_ONLY_OVERRIDE) && ! $exists) {
                $error->errorMessage = get_string('existingitemnotfound', 'turningtech');
            } else {
                // Save the grade.
                if ($grade_item->update_final_grade($student->id, $dto->pointsEarned, 'gradebook')) {
                    // Everything is fine, no error to return. Save an escrow entry just to record
                    // The transaction.
                    $escrow = TurningTechEscrow::generate(array ('deviceid' => $dto->deviceId, 'courseid' => $course->id,
                                     'itemid' => $grade_item->id, 'points_possible' => $dto->pointsPossible,
                                     'points_earned' => $dto->pointsEarned, 'migrated' => true ));
                    $escrow->save();
                    $error = false;
                } else {
                    echo "<p>grade not saved successfully, creating escrow entry</p>\n";
                    // Could not save in gradebook. Create escrow item and save it.
                    $escrow = TurningTechTurningHelper::getescrowinstance($course, $dto, $grade_item, false);
                    $escrow->setField('points_earned', $dto->pointsEarned);
                    $escrow->save();
                    $error->errorMessage = get_string('errorsavinggradeitemsavedinescrow', 'turningtech');
                }
            }
        }
        return $error;
    }
    /**
     * attempt to
     * @param object $course
     * @param mixed $dto
     * @return unknown_type
     * 
     * @see docroot/mod/turningtech/lib/ServiceProvider#addtoexistingscore()
     */
    public function addtoexistingscore($course, $dto) {
        // Prepare the error just in case.
        $error = new stdClass();
        $error->deviceId = $dto->deviceId;
        $error->itemTitle = $dto->itemTitle;
        // Get the gradebook item for this transaction.
        $grade_item = TurningTechMoodleHelper::getgradebookitembycourseandtitle($course, $dto->itemTitle);
        if (! $grade_item) {
            $error->errorMessage = get_string('couldnotfindgradeitem', 'turningtech', $dto);
            return $error;
        }
        // See if there is a student associated with this device id.
        $student = $this->getstudentbycourseanddeviceid($course, $dto->deviceId);
        if (! $student) {
            // No device association for this device, so save in escrow.
            $escrow = TurningTechTurningHelper::getescrowinstance($course, $dto, $grade_item, false);
            // Verify this is an existing item.
            if (! $escrow->getId()) {
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
            $grade = TurningTechMoodleHelper::getgraderecord($student, $grade_item);
            if (! $grade) {
                $error->errorMessage = get_string('existingitemnotfound', 'turningtech');
            } else {
                $grade_item->update_final_grade($student->id, ($grade->finalgrade + $dto->pointsEarned), 'gradebook');
                $error = false;
            }
        }
        return $error;
    }
    /**
     * check the escrow table to see if there are any entries that correspond to
     * the given device map.
     * If so, move them into the database
     * @param mixed $devicemap
     * @param mixed $course
     * @return unknown_type
     */
    public static function migrateescowgrades($devicemap, $course) {
        global $DB;
        $conditions = array ();
        $conditions['deviceid'] = "'{$devicemap->getfield('deviceid')}'";
        $conditions['migrated'] = '0';
        $conditions['courseid'] = $course;
        $sql = TurningModel::buildwhereclause($conditions);
        $items = $DB->get_records_select('turningtech_escrow', $sql);
        if ($items) {
            foreach ($items as $item) {
                $escrow = TurningTechEscrow::generate($item);
                self::dogrademigration($devicemap, $escrow);
            }
        }
    }
    /**
     * add a new entry to the gradebook for escrow item using information provided
     * by the device map.
     * 
     * @param mixed $devicemap
     * @param mixed $escrow
     * @return unknown_type
     */
    public static function dogrademigration($devicemap, $escrow) {
        if ($grade_item = TurningTechMoodleHelper::getgradebookitembyid($escrow->getfield('itemid'))) {
            $grade_item->update_final_grade($devicemap->getfield('userid'), $escrow->getfield('points_earned'), 'gradebook');
            $escrow->setField('migrated', 1);
            $escrow->save();
        }
    }
    /**
     * import session data
     * @param unknown_type $exportdata
     * @return unknown_type
     * @see TurningTechServiceProvider::importsessiondata()
     */
    public function importsessiondata($exportdata) {
        $arr = array ();
        $arr[0] = TurningTechTurningHelper::importsessiondata($exportdata);
        return $arr;
    }
    /**
     * create fake gradebook item
     * @param mixed $gradeitem
     * @return unknown_type
     */
    private function generategradebookitemview($gradeitem) {
        $item = new stdClass();
        $item->itemTitle = $gradeitem->itemname;
        $item->points = $gradeitem->grademax;
        return $item;
    }
    /**
     * generates a fake course
     * @param object $course
     * @return CourseSiteView
     */
    private function generatecoursesiteview($course) {
        $view = new stdClass();
        $view->id = $course->id;
        $view->title = $course->fullname;
        $view->type = $course->category;
        return $view;
    }
    /**
     * translates a Moodle user into a course participant DTO
     * @param mixed $participant
     * @param object $course
     * @return CourseParticipantDTO
     */
    private function generatecourseparticipantdto($participant, $course) {
        $dto = new stdClass();
        $dto->deviceId = null;
        if (! empty($participant->deviceid)) {
            $dto->deviceId = $participant->deviceid;
        } else {
            $dto->deviceId = '';
        }
        $dto->email = $participant->email;
        $dto->firstName = $participant->firstname;
        $dto->lastName = $participant->lastname;
        $dto->loginId = $participant->username;
        $dto->userId = $participant->id;
        return $dto;
    }
    /**
     * translates a Moodle user into a course participant DTO for Phoenix.
     * @param mixed $participant
     * @param object $course
     * @return CourseParticipantDTO
     */
    private function generateextcourseparticipantdto($participant, $course) {
        $dto = new stdClass();
        $dto->deviceId = null;
        if (! empty($participant->deviceid)) {
            $dto->deviceId = $participant->deviceid;
        } else {
            $dto->deviceId = '';
        }
        $dto->email = $participant->email;
        $dto->firstName = $participant->firstname;
        $dto->lastName = $participant->lastname;
        $dto->userId = $participant->username;
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
     * spits out a random string
     * 
     * @param mixed $length
     * @return string
     */
    private function _generaterandomstring($length = 0) {
        $str = md5(uniqid(rand(), true));
        return ($length ? substr($str, 0, $length) : $str);
    }
}