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
 * This file is used  for manipulating Extended session data
 *
 * @copyright  2012 Turning Technologies
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
/**
 * Class for manipulating Extended session data
 * 
 * @package mod_turningtech
 * @copyright  2012 Turning Technologies
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class TurningExtendedSession {
    /**
     * @var unknown_type
     */
    private $intcourseid;
    /**
     * @var unknown_type
     */
    private $arrparticipants = array ();
    /**
     * @var unknown_type
     */
    private $arrquestions = array ();
    /**
     * @var unknown_type
     */
    private $objemail;
    /**
     * @var unknown_type
     */
    private $objxml;
    /**
     * @var unknown_type
     */
    private $strexportobjecttype;
    /**
     * @var unknown_type
     */
    private $strexportobjectname;
    /**
     * @var unknown_type
     */
    private $strexportobjectmaxscore;
    // The following code has been commented till all questions types info is ready and email is ready to be sent to users.
    /*
     * {private $arrExportObjectTypeOptions = array("session", "non-session");}
     */
    /**
     * constructor
     * 
     * @return unknown_type
     */
    public function __construct() {
    }
    /**
     * sets the active course
     * 
     * @param object $courseid
     */
    public function setcourseid($courseid) {
        $this->intcourseid = $courseid;
    }
    /**
     * gets the active course
     * 
     * @param
     *            $course
     * @return unknown_type
     */
    public function getcourseid() {
        return $this->intcourseid;
    }
    /**
     * gets the participant list
     * 
     * @return unknown_type
     */
    public function getparticipantslist() {
        return $this->arrparticipants;
    }
    /**
     * gets the question list
     * 
     * @return unknown_type
     */
    public function getquestionslist() {
        return $this->arrquestions;
    }
    /**
     * get email info
     * @return unknown_type
     */
    public function getemailinfo() {
        return $this->objemail;
    }
    /**
     * get object name
     * @return unknown_type
     */
    public function getexportobjectname() {
        return $this->strexportobjectname;
    }
    /**
     * get object type
     * @return unknown_type
     */
    public function getexportobjecttype() {
        return $this->strexportobjecttype;
    }
    /**
     * get max score
     * @return unknown_type
     */
    public function getexportobjectmaxscore() {
        $intmaxscore = $this->strexportobjectmaxscore;
        settype($intmaxscore, "int");
        if (is_null($intmaxscore) || $intmaxscore < 0) {
            $intmaxscore = TurningTechTurningHelper::getdefaultmaxgrade();
        }
        return $intmaxscore;
    }
    /**
     * load xml
     * @param unknown_type $exportdata
     * @throws CustomException
     */
    public function loadxml($exportdata) {
        try {
            // Support for PHP older versions.
            if (!function_exists("simplexml_load_string")) {
                include_once($CFG->dirroot . '/mod/turningtech/lib/api/SimpleXML.class.php');
            }
            $this->objxml = simplexml_load_string($exportdata);
        } catch ( Exception $ex ) {
            throw new CustomException($ex->getMessage(), $ex->getCode(), 1, "XML could not be loaded.");
        }
    }
    /**
     * validate xml
     * @throws CustomException
     */
    public function validatexml() {
        try {
            // Have the XML object as local variable to minimize multiple accesses to class variable.
            $objxml = $this->objxml;
            // Validating -> "XML Structure: should be valid".
            {
            try {
                $this->validatexmlstructure();
            } catch ( CustomException $ex ) {
                throw $ex;
            } catch ( Exception $ex ) {
                throw new CustomException($ex->getMessage(), $ex->getCode(), 5, "XML schema not correct");
            }
            }
            // Validating -> "Object's Type: should not be blank and unknown"
            // Validating -> "Object's Name: should not be blank".
            {
            try {
                $this->validateexportobject();
            } catch ( CustomException $ex ) {
                throw $ex;
            } catch ( Exception $ex ) {
                throw new CustomException($ex->getMessage(), $ex->getCode(), 5, "Export object is not valid");
            }
            }
            // Validating -> "XML Object Type dependent Structure: should be valid".
            {
            try {
                $this->validateexportobjecttypexmlstructure();
            } catch ( CustomException $ex ) {
                throw $ex;
            } catch ( Exception $ex ) {
                throw new CustomException($ex->getMessage(), $ex->getCode(), 5, "XML schema not correct");
            }
            }
            // Validating -> "Course: should not be blank".
            {
            try {
                $this->intcourseid = trim($objxml->courseId);
                $this->validatecourse($this->intcourseid);
            } catch ( CustomException $ex ) {
                throw $ex;
            } catch ( Exception $ex ) {
                throw new CustomException($ex->getMessage(), $ex->getCode(), 5, "Course id is not valid");
            }
            }
            // Validating -> "Email -> From: should not be blank".
            {
            try {
                if (isset($objxml->email)) {
                    $this->objemail = $objxml->email;
                    $this->validateemailinfo($objxml->email);
                } else {
                    $this->objemail = null;
                }
            } catch ( CustomException $ex ) {
                throw $ex;
            } catch ( Exception $ex ) {
                throw new CustomException($ex->getMessage(), $ex->getCode(), 5, "From email is not valid");
            }
            }
        } catch ( CustomException $ex ) {
            throw $ex;
        } catch ( Exception $ex ) {
            throw new CustomException($ex->getMessage(), $ex->getCode(), 3, "XML could not be validated");
        }
    }
    /**
     * validate xml structure
     * @throws CustomException
     */
    private function validatexmlstructure() {
        $objxml = $this->objxml;
        try {
            if (! isset($objxml->exportobject) || ! isset($objxml->participants)) {
                throw new CustomException("", 0, 5, "XML schema not correct");
            }
            $arrexportvars = get_object_vars($objxml->exportobject);
            if (! is_array($arrexportvars) || empty($arrexportvars) ||
                                             ! is_array($arrexportvars['@attributes']) ||
                                             ! isset($arrexportvars['@attributes']['name']) ||
                                            // The following code has been commented till all questions types info is ready and
                                            // email is ready to be sent to users.
                                            /*
                                             * !isset($arrexportvars['@attributes']['type']) ||
                                             */
                                            ! isset($objxml->participants->participant)) {
                throw new CustomException("", 0, 5, "XML schema not correct");
            }
            // The following code has been commented till all questions types info is ready
            //  and email is ready to be sent to users.
            /*
             * {$this->strexportobjecttype = trim($arrexportvars['@attributes']['type']);}
             */
            $this->strexportobjectname = trim($arrexportvars['@attributes']['name']);
            $this->strexportobjectmaxscore = trim($arrexportvars['@attributes']['maxscore']);
        } catch ( CustomException $ex ) {
            throw $ex;
        } catch ( Exception $ex ) {
            throw new CustomException($ex->getMessage(), $ex->getCode(), 5, "XML schema not correct");
        }
    }
    /**
     * validate export object
     * @throws CustomException
     */
    private function validateexportobject() {
        // The following code has been commented till all questions types info is ready and email is ready to be sent to users.
        /*
         * try { if (is_null($this->strexportobjecttype) || $this->strexportobjecttype == "" ||
         * !in_array($this->strexportobjecttype, $this->arrExportObjectTypeOptions)) { throw new CustomException("", 0, 5, "Export
         * object type is not valid"); } } catch (CustomException $ex) { throw $ex; } catch (Exception $ex) { throw new
         * CustomException($ex->getMessage(), $ex->getCode(), 5, "Export object type is not valid"); }
         */
        try {
            if (is_null($this->strexportobjectname) || $this->strexportobjectname == "") {
                throw new CustomException("", 0, 5, "Export object name is blank");
            }
        } catch ( CustomException $ex ) {
            throw $ex;
        } catch ( Exception $ex ) {
            throw new CustomException($ex->getMessage(), $ex->getCode(), 5, "Export object is not valid");
        }
    }
    /**
     * validate xml structure
     */
    private function validateexportobjecttypexmlstructure() {
        // The following code has been commented till all questions types info is ready and email is ready to be sent to users.
        /*
         * try { switch ($this->strexportobjecttype) { case "session": $objxml = $this->objxml; try { $arrexportvars =
         * get_object_vars($objxml->exportobject); if (!isset($arrexportvars['questions']) ||
         * !isset($arrexportvars['questions']->question)) { throw new CustomException("", 0, 5, "XML schema not correct"); } } catch
         * (CustomException $ex) { throw $ex; } catch (Exception $ex) { throw new CustomException($ex->getMessage(), $ex->getCode(),
         * 5, "XML schema not correct"); } break; case "non-session": // Nothing required right now .... break; } } catch
         * (CustomException $ex) { throw $ex; } catch (Exception $ex) { throw new CustomException($ex->getMessage(), $ex->getCode(),
         * 5, "XML schema not correct"); }
         */
    }
    /**
     * validate course
     * @param unknown_type $intcourseid
     * @throws CustomException
     */
    private function validatecourse($intcourseid) {
        try {
            if (is_null($intcourseid) || $intcourseid == "") {
                throw new CustomException("", 0, 5, "Course id is blank");
            }
        } catch ( CustomException $ex ) {
            throw $ex;
        } catch ( Exception $ex ) {
            throw new CustomException($ex->getMessage(), $ex->getCode(), 5, "Course id is not valid");
        }
    }
    /**
     * validate email info
     * @param unknown_type $objemail
     * @throws CustomException
     */
    private function validateemailinfo($objemail) {
        try {
            if (! is_null($objemail) && $objemail != "") {
                $fromemail = $objemail->from;
                if ($fromemail == "" || is_null($fromemail)) {
                    throw new CustomException("", 0, 5, "From email not specified");
                }
            }
        } catch ( CustomException $ex ) {
            throw $ex;
        } catch ( Exception $ex ) {
            throw new CustomException($ex->getMessage(), $ex->getCode(), 5, "From email is not valid");
        }
    }
    /**
     * prepare data from xml
     */
    public function preparedatafromxml() {
        $this->prepareparticipantslist();
        // The following code has been commented till all questions types info is ready and email is ready to be sent to users.
        /*
         * {$this->preparequestionslist();}
         */
    }
    /**
     * prepare participant list
     * @throws CustomException
     */
    private function prepareparticipantslist() {
        try {
            $arrparticipant = $this->objxml->participants->participant;
            if (@count($arrparticipant)) {
                foreach ($arrparticipant as $participant) {
                    $objparticipant = new stdClass();
                    $arrparticipantvar = get_object_vars($participant);
                    if (@count($arrparticipantvar)) {
                        foreach ($arrparticipantvar as $key => $participantvar) {
                            if ($key == "@attributes") {
                                if (@count($participantvar)) {
                                    foreach ($participantvar as $k => $attributevar) {
                                        $objparticipant->$k = $attributevar;
                                    }
                                }
                            } else if ($key == "questions") {
                                $arrques = array ();
                                $arrquestions = $participantvar->question;
                                if (@count($arrquestions)) {
                                    foreach ($arrquestions as $question) {
                                        $arrquestionvar = get_object_vars($question);
                                        $arrquesattributes = $arrquestionvar['@attributes'];
                                        if (@count($arrquesattributes)) {
                                            foreach ($arrquesattributes as $k => $quesattribute) {
                                                $arrques[]->$k = $quesattribute;
                                            }
                                        }
                                    }
                                }
                                $objparticipant->$key = $arrques;
                            } else {
                                $objparticipant->$key = $participantvar;
                            }
                        }
                    }
                    $this->arrparticipants[] = $objparticipant;
                }
            }
        } catch ( CustomException $ex ) {
            throw $ex;
        } catch ( Exception $ex ) {
            throw new CustomException($ex->getMessage(), $ex->getCode(), 3, "Participant list could not be read");
        }
    }
    /**
     * prepare question list
     * @throws CustomException
     */
    private function preparequestionslist() {
        try {
            $arrquestion = $this->objxml->exportobject->questions->question;
            if (is_array($arrquestion)) {
                foreach ($arrquestion as $question) {
                    $objquestion = new stdClass();
                    $arrquestionvar = get_object_vars($question);
                    if (is_array($arrquestionvar)) {
                        foreach ($arrquestionvar as $key => $questionvar) {
                            if ($key == "@attributes") {
                                if (is_array($questionvar)) {
                                    foreach ($questionvar as $k => $attributevar) {
                                        $objquestion->$k = $attributevar;
                                    }
                                }
                            } else if ($key == "answerchoices") {
                                $arranschc = array ();
                                $arranswerchoices = $questionvar->answerchoice;
                                if (is_array($arranswerchoices)) {
                                    foreach ($arranswerchoices as $answerchoice) {
                                        $objanschc = new stdClass();
                                        $arranswerchoicevar = get_object_vars($answerchoice);
                                        $arranschcattributes = $arranswerchoicevar['@attributes'];
                                        if (is_array($arranschcattributes)) {
                                            foreach ($arranschcattributes as $k => $anschcattribute) {
                                                $objanschc->$k = $anschcattribute;
                                            }
                                        }
                                        $arranschc[] = $objanschc;
                                    }
                                }
                                $objquestion->$key = $arranschc;
                            }
                        }
                    }
                    $this->arrquestions[] = $objquestion;
                }
            }
        } catch ( CustomException $ex ) {
            throw $ex;
        } catch ( Exception $ex ) {
            throw new CustomException($ex->getMessage(), $ex->getCode(), 3, "Question list could not be read");
        }
    }
    /**
     * save and update
     * @throws CustomException
     */
    public function saveupdatescoredata() {
        $objcourse = new stdClass();
        $objcourse->id = $this->getcourseid();
        try {
            // Check if we need to create the gradebook item.
            if (! ($objgradeitem = TurningTechMoodleHelper::getgradebookitembycourseandtitle(
                                            $objcourse, $this->getexportobjectname()))) {
                // Create gradebook item.
                TurningTechMoodleHelper::creategradebookitem($objcourse, $this->getexportobjectname(),
                 $this->getexportobjectmaxscore());
            } else {
                if ($objgradeitem->grademax != $this->getexportobjectmaxscore()) {
                    $arrgradeitem = array ();
                    $arrgradeitem['grademax'] = $this->getexportobjectmaxscore();
                    TurningTechMoodleHelper::updategradebookitem($objgradeitem->id, $arrgradeitem);
                }
            }
        } catch ( CustomException $ex ) {
            throw $ex;
        } catch ( Exception $ex ) {
            throw new CustomException($ex->getMessage(), $ex->getCode(), 3,
                                             "Session existence could not be checked or Session could not be created");
        }
        try {
            // Counts number of correctly saved items.
            $saved = 0;
            // Records errors.
            $errors = array ();
            // Keep track of which line of the session file we're on.
            $linecounter = 1;
            // Container for participant to be removed out.
            $arrremoveparticipant = array ();
            $arrparticipants = $this->getparticipantslist();
            if (is_array($arrparticipants)) {
                // Traverse through all of the participants.
                foreach ($arrparticipants as $key => $participant) {
                    try {
                        // Check for the Participant Existence.
                        if (is_object($objuser = TurningTechMoodleHelper::getuserbyusername($participant->userid))) {
                            $objcuruser = new stdClass();
                            $objcuruser->id = $objuser->id;
                            // Log and skip the students which are not enrolled with course now.
                            if (! TurningTechMoodleHelper::isstudentincourse($objcuruser, $objcourse)) {
                                $arrremoveparticipant[] = $key;
                                continue;
                            }
                            $this->arrparticipants[$key]->email = $objuser->email;
                            $this->arrparticipants[$key]->id = $objuser->id;
                            if ($error = $this->savegradebookgrade($objcourse, $participant)) {
                                $a = new stdClass();
                                $a->line = $linecounter;
                                $a->message = $error->errorMessage;
                                $errors[] = get_string('erroronimport', 'turningtech', $a);
                            } else {
                                // Grade saved correctly.
                                $saved ++;
                            }
                            $linecounter ++;
                        } else {
                            $arrremoveparticipant[] = $key;
                        }
                    } catch ( Exception $ex ) {
                        // If an exception occurs while checking for a user, skip it.
                        $arrremoveparticipant[] = $key;
                    }
                }
                if (count($arrremoveparticipant)) {
                    foreach ($arrremoveparticipant as $removeparticipant) {
                        unset($this->arrparticipants[$removeparticipant]);
                    }
                    $this->arrparticipants = array_values($this->arrparticipants);
                }
            }
        } catch ( CustomException $ex ) {
            throw $ex;
        } catch ( Exception $ex ) {
            throw new CustomException($ex->getMessage(), $ex->getCode(), 3, "Session score could not be saved/updated");
        }
    }
    /**
     * Save Grades
     * @param unknown_type $objcourse
     * @param unknown_type $objparticipant
     * @return stdClass|Ambigous <boolean, stdClass>
     */
    private function savegradebookgrade($objcourse, $objparticipant) {
        // Prepare the error just in case.
        $error = new stdClass();
        $error->itemTitle = $this->getexportobjectname();
        // Get the gradebook item for this transaction.
        $grade_item = TurningTechMoodleHelper::getgradebookitembycourseandtitle($objcourse, $this->getexportobjectname());
        if (! $grade_item) {
            $error->errorMessage = get_string('couldnotfindgradeitem', 'turningtech', $objparticipant);
            return $error;
        }
        // Providing default value for the max grade if not existing.
        if ($grade_item->grademax <= 0) {
            $grade_item->grademax = TurningTechTurningHelper::getdefaultmaxgrade();
        }
        // Save the grade.
        if ($grade_item->update_final_grade($objparticipant->id, $objparticipant->score, 'gradebook')) {
            // Everything is fine, no error to return.
			$grade_item->itemtype = TURNINGTECH_GRADE_ITEM_TYPE;
			$grade_item->itemmodule = TURNINGTECH_GRADE_ITEM_MODULE;
			$grade_item->update();
            $error = false;
        } else {
            // Could not save in gradebook.
            $error->errorMessage = get_string('errorsavinggradeitem', 'turningtech');
        }
        return $error;
    }
}