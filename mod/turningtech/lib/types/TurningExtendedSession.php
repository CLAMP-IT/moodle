<?php

/**
 * Class for manipulating Extended session data
 * @author magic
 */
class TurningExtendedSession {
    // current course (if any)
    private $intCourseId;

    // list of students in session
    private $arrParticipants = array();

    // list of questions in session
    private $arrQuestions = array();

    // Email Info (Object) for this session
    private $objEmail;

    private $objXML;

    private $strExportObjectType;
    private $strExportObjectName;
    private $strExportObjectMaxScore;

    // ~~ The following code has been commented till all questions types info is ready and email is ready to be sent to users.
    /*
    private $arrExportObjectTypeOptions = array("session", "non-session");
    */

    /**
     * constructor
     * @return unknown_type
     */
    public function __construct() {
    }

    /**
     * sets the active course
     * @param $course
     * @return unknown_type
     */
    public function setCourseId($courseId) {
        $this->intCourseId = $courseId;
    }

    /**
     * gets the active course
     * @param $course
     * @return unknown_type
     */
    public function getCourseId() {
        return $this->intCourseId;
    }

    /**
     * gets the participant list
     * @return unknown_type
     */
    public function getParticipantsList() {
        return $this->arrParticipants;
    }

    /**
     * gets the question list
     * @return unknown_type
     */
    public function getQuestionsList() {
        return $this->arrQuestions;
    }

    public function getEmailInfo() {
        return $this->objEmail;
    }

    public function getExportObjectName() {
        return $this->strExportObjectName;
    }

    public function getExportObjectType() {
        return $this->strExportObjectType;
    }

    public function getExportObjectMaxScore() {
        $intMaxScore = $this->strExportObjectMaxScore;
        settype( $intMaxScore, "int" );

        if (is_null( $intMaxScore ) ||
            $intMaxScore < 0) {
            $intMaxScore = TurningTechTurningHelper::getDefaultMaxGrade();
        }

        return $intMaxScore;
    }

    public function loadXML($exportData) {
        try {
            // Support for PHP older versions.
            if (!function_exists("simplexml_load_string")) {
                include_once $CFG->dirroot . '/mod/turningtech/lib/api/SimpleXML.class.php';
            }

            $this->objXML = simplexml_load_string($exportData);
        }
        catch (Exception $ex) {
            throw new CustomException($ex->getMessage(), $ex->getCode(), 1, "XML could not be loaded.");
        }
    }

    public function validateXML() {
        try {
            // Have the XML object as local variable to minimize multiple accesses to class variable.
            $objXML = $this->objXML;

            // Validating -> "XML Structure: should be valid"
            {
                try {
                    $this->validateXMLStructure();
                }
                catch (CustomException $ex) {
                    throw $ex;
                }
                catch (Exception $ex) {
                    throw new CustomException($ex->getMessage(), $ex->getCode(), 5, "XML schema not correct");
                }
            }

            // Validating -> "Object's Type: should not be blank and unknown"
            // Validating -> "Object's Name: should not be blank"
            {
                try {
                    $this->validateExportObject();
                }
                catch (CustomException $ex) {
                    throw $ex;
                }
                catch (Exception $ex) {
                    throw new CustomException($ex->getMessage(), $ex->getCode(), 5, "Export object is not valid");
                }
            }

            // Validating -> "XML Object Type dependent Structure: should be valid"
            {
                try {
                    $this->validateExportObjectTypeXMLStructure();
                }
                catch (CustomException $ex) {
                    throw $ex;
                }
                catch (Exception $ex) {
                    throw new CustomException($ex->getMessage(), $ex->getCode(), 5, "XML schema not correct");
                }
            }

            // Validating -> "Course: should not be blank"
            {
                try {
                    $this->intCourseId = trim($objXML->courseId);

                    $this->validateCourse($this->intCourseId);
                }
                catch (CustomException $ex) {
                    throw $ex;
                }
                catch (Exception $ex) {
                    throw new CustomException($ex->getMessage(), $ex->getCode(), 5, "Course id is not valid");
                }
            }

            // Validating -> "Email -> From: should not be blank"
            {
                try {
                    if (isset($objXML->email)) {
                        $this->objEmail = $objXML->email;
                        $this->validateEmailInfo($objXML->email);
                    } else {
                        $this->objEmail = null;
                    }
                }
                catch (CustomException $ex) {
                    throw $ex;
                }
                catch (Exception $ex) {
                    throw new CustomException($ex->getMessage(), $ex->getCode(), 5, "From email is not valid");
                }
            }
        }
        catch (CustomException $ex) {
            throw $ex;
        }
        catch (Exception $ex) {
            throw new CustomException($ex->getMessage(), $ex->getCode(), 3, "XML could not be validated");
        }
    }

    private function validateXMLStructure() {
        $objXML = $this->objXML;

        try {
            if (!isset($objXML->exportobject) || !isset($objXML->participants)) {
                throw new CustomException("", 0, 5, "XML schema not correct");
            }

            $arrExportVars = get_object_vars($objXML->exportobject);

            if (!is_array($arrExportVars) ||
                empty($arrExportVars) ||
                !is_array($arrExportVars['@attributes']) ||
                !isset($arrExportVars['@attributes']['name']) ||
                // ~~ The following code has been commented till all questions types info is ready and email is ready to be sent to users.
                /*
                !isset($arrExportVars['@attributes']['type']) ||
                */
                !isset($objXML->participants->participant)) {
                    throw new CustomException("", 0, 5, "XML schema not correct");
            }

            // ~~ The following code has been commented till all questions types info is ready and email is ready to be sent to users.
            /*
            $this->strExportObjectType = trim($arrExportVars['@attributes']['type']);
            */
            $this->strExportObjectName     = trim($arrExportVars['@attributes']['name']);
            $this->strExportObjectMaxScore = trim($arrExportVars['@attributes']['maxscore']);
        }
        catch (CustomException $ex) {
            throw $ex;
        }
        catch (Exception $ex) {
            throw new CustomException($ex->getMessage(), $ex->getCode(), 5, "XML schema not correct");
        }
    }

    private function validateExportObject() {
        // ~~ The following code has been commented till all questions types info is ready and email is ready to be sent to users.
        /*
        try {
            if (is_null($this->strExportObjectType) ||
                $this->strExportObjectType == "" ||
                !in_array($this->strExportObjectType, $this->arrExportObjectTypeOptions)) {
                    throw new CustomException("", 0, 5, "Export object type is not valid");
            }
        }
        catch (CustomException $ex) {
            throw $ex;
        }
        catch (Exception $ex) {
            throw new CustomException($ex->getMessage(), $ex->getCode(), 5, "Export object type is not valid");
        }
        */
        try {
            if (is_null($this->strExportObjectName) || $this->strExportObjectName == "") {
                throw new CustomException("", 0, 5, "Export object name is blank");
            }
        }
        catch (CustomException $ex) {
            throw $ex;
        }
        catch (Exception $ex) {
            throw new CustomException($ex->getMessage(), $ex->getCode(), 5, "Export object is not valid");
        }
    }

    private function validateExportObjectTypeXMLStructure() {
        // ~~ The following code has been commented till all questions types info is ready and email is ready to be sent to users.
        /*
        try {
            switch ($this->strExportObjectType) {
                case "session":

                    $objXML = $this->objXML;

                    try {
                        $arrExportVars = get_object_vars($objXML->exportobject);

                        if (!isset($arrExportVars['questions']) ||
                            !isset($arrExportVars['questions']->question)) {
                                throw new CustomException("", 0, 5, "XML schema not correct");
                        }
                    }
                    catch (CustomException $ex) {
                        throw $ex;
                    }
                    catch (Exception $ex) {
                        throw new CustomException($ex->getMessage(), $ex->getCode(), 5, "XML schema not correct");
                    }

                    break;

                case "non-session":

                    // Nothing required right now ....

                    break;
            }
        }
        catch (CustomException $ex) {
            throw $ex;
        }
        catch (Exception $ex) {
            throw new CustomException($ex->getMessage(), $ex->getCode(), 5, "XML schema not correct");
        }
        */
    }

    private function validateCourse($intCourseId) {
        try {
            if (is_null($intCourseId) || $intCourseId == "") {
                throw new CustomException("", 0, 5, "Course id is blank");
            }
        }
        catch (CustomException $ex) {
            throw $ex;
        }
        catch (Exception $ex) {
            throw new CustomException($ex->getMessage(), $ex->getCode(), 5, "Course id is not valid");
        }
    }

    private function validateEmailInfo($objEmail) {
        try {
            if (!is_null($objEmail) && $objEmail != "") {
                $fromEmail = $objEmail->from;

                if ($fromEmail == "" || is_null($fromEmail)) {
                    throw new CustomException("", 0, 5, "From email not specified");
                }
            }
        }
        catch (CustomException $ex) {
            throw $ex;
        }
        catch (Exception $ex) {
            throw new CustomException($ex->getMessage(), $ex->getCode(), 5, "From email is not valid");
        }
    }

    public function prepareDataFromXML() {
        $this->prepareParticipantsList();

        // ~~ The following code has been commented till all questions types info is ready and email is ready to be sent to users.
        /*
        $this->prepareQuestionsList();
        */
    }

    private function prepareParticipantsList() {
        try {
            $arrParticipant = $this->objXML->participants->participant;

            if (@count($arrParticipant)) {
                foreach ($arrParticipant as $participant) {
                    $objParticipant     = new stdClass();
                    $arrParticipantVar  = get_object_vars($participant);

                    if (@count($arrParticipantVar)) {
                        foreach ($arrParticipantVar as $key => $participantVar) {
                            if ($key == "@attributes") {
                                if (@count($participantVar)) {
                                    foreach ($participantVar as $k => $attributeVar) {
                                        $objParticipant->$k = $attributeVar;
                                    }
                                }
                            } else if ($key == "questions") {
                                $arrQues = array();

                                $arrQuestions = $participantVar->question;

                                if (@count($arrQuestions)) {
                                    foreach ($arrQuestions as $question) {
                                        $arrQuestionVar = get_object_vars($question);

                                        $arrQuesAttributes = $arrQuestionVar['@attributes'];

                                        if (@count($arrQuesAttributes)) {
                                            foreach ($arrQuesAttributes as $k => $quesAttribute) {
                                                $arrQues[]->$k = $quesAttribute;
                                            }
                                        }
                                    }
                                }

                                $objParticipant->$key = $arrQues;
                            } else {
                                $objParticipant->$key = $participantVar;
                            }
                        }
                    }

                    $this->arrParticipants[] = $objParticipant;
                }
            }
        }
        catch (CustomException $ex) {
            throw $ex;
        }
        catch (Exception $ex) {
            throw new CustomException($ex->getMessage(), $ex->getCode(), 3, "Participant list could not be read");
        }
    }

    private function prepareQuestionsList() {
        try {
            $arrQuestion = $this->objXML->exportobject->questions->question;

            if (is_array($arrQuestion)) {
                foreach ($arrQuestion as $question) {
                    $objQuestion = new stdClass();

                    $arrQuestionVar = get_object_vars($question);

                    if (is_array($arrQuestionVar)) {
                        foreach ($arrQuestionVar as $key => $questionVar) {
                            if ($key == "@attributes") {
                                if (is_array($questionVar)) {
                                    foreach ($questionVar as $k => $attributeVar) {
                                        $objQuestion->$k = $attributeVar;
                                    }
                                }
                            } else if ($key == "answerchoices") {
                                $arrAnsChc = array();

                                $arrAnswerChoices = $questionVar->answerchoice;

                                if (is_array($arrAnswerChoices)) {
                                    foreach ($arrAnswerChoices as $answerChoice) {
                                        $objAnsChc = new stdClass();

                                        $arrAnswerChoiceVar = get_object_vars($answerChoice);

                                        $arrAnsChcAttributes = $arrAnswerChoiceVar['@attributes'];

                                        if (is_array($arrAnsChcAttributes)) {
                                            foreach ($arrAnsChcAttributes as $k => $ansChcAttribute) {
                                                $objAnsChc->$k = $ansChcAttribute;
                                            }
                                        }

                                        $arrAnsChc[] = $objAnsChc;
                                    }
                                }

                                $objQuestion->$key = $arrAnsChc;
                            }
                        }
                    }

                    $this->arrQuestions[] = $objQuestion;
                }
            }
        }
        catch (CustomException $ex) {
            throw $ex;
        }
        catch (Exception $ex) {
            throw new CustomException($ex->getMessage(), $ex->getCode(), 3, "Question list could not be read");
        }
    }

    public function saveUpdateScoreData($objTurnExtSession) {

        $objCourse     = new stdClass();
        $objCourse->id = $this->getCourseId();

        try {
            // Check if we need to create the gradebook item
            if (!($objGradeItem = TurningTechMoodleHelper::getGradebookItemByCourseAndTitle($objCourse, $this->getExportObjectName()))) {
                // Create gradebook item.
                TurningTechMoodleHelper::createGradebookItem($objCourse, $this->getExportObjectName(), $this->getExportObjectMaxScore());
            } else {
                if ($objGradeItem->grademax != $this->getExportObjectMaxScore()) {
                    $arrGradeItem = array();
                    $arrGradeItem['grademax'] = $this->getExportObjectMaxScore();

                    TurningTechMoodleHelper::updateGradebookItem($objGradeItem->id, $arrGradeItem);
                }
            }
        }
        catch (CustomException $ex) {
            throw $ex;
        }
        catch (Exception $ex) {
            throw new CustomException($ex->getMessage(), $ex->getCode(), 3, "Session existence could not be checked or Session could not be created");
        }

        try {
            // counts number of correctly saved items
            $saved = 0;

            // records errors
            $errors = array();

            // keep track of which line of the session file we're on
            $linecounter = 1;

            // Container for participant to be removed out.
            $arrRemoveParticipant = array();

            $arrParticipants = $this->getParticipantsList();

            if (is_array($arrParticipants)) {
                // Traverse through all of the participants
                foreach ($arrParticipants as $key => $participant) {
                    try {
                        // Check for the Participant Existence
                        if (is_object($objUser = TurningTechMoodleHelper::getUserByUsername($participant->userid))) {
                            $objCurUser     = new stdClass();
                            $objCurUser->id = $objUser->id;

                            // Log and skip the students which are not enrolled with course now
                            if (!TurningTechMoodleHelper::isStudentInCourse($objCurUser, $objCourse)) {
                                $arrRemoveParticipant[] = $key;

                                continue;
                            }

                            $this->arrParticipants[$key]->email = $objUser->email;
                            $this->arrParticipants[$key]->id    = $objUser->id;

                            if ($error = $this->saveGradebookGrade($objCourse, $participant)) {
                                $a          = new stdClass();
                                $a->line    = $linecounter;
                                $a->message = $error->errorMessage;
                                $errors[]   = get_string('erroronimport', 'turningtech', $a);
                            } else {
                                // grade saved correctly
                                $saved++;
                            }

                            $linecounter++;
                        } else {
                            $arrRemoveParticipant[] = $key;
                        }
                    }
                    catch (Exception $ex) {
                        // If an exception occurs while checking for a user, skip it.
                        $arrRemoveParticipant[] = $key;
                    }
                }

                if (count($arrRemoveParticipant)) {
                    foreach ($arrRemoveParticipant as $removeParticipant) {
                        unset($this->arrParticipants[$removeParticipant]);
                    }

                    $this->arrParticipants = array_values($this->arrParticipants);
                }
            }
        }
        catch (CustomException $ex) {
            throw $ex;
        }
        catch (Exception $ex) {
            throw new CustomException($ex->getMessage(), $ex->getCode(), 3, "Session score could not be saved/updated");
        }
    }

    private function saveGradebookGrade($objCourse, $objParticipant) {
        // prepare the error just in case
        $error            = new stdClass();
        $error->itemTitle = $this->getExportObjectName();

        // get the gradebook item for this transaction
        $grade_item = TurningTechMoodleHelper::getGradebookItemByCourseAndTitle($objCourse, $this->getExportObjectName());

        if (!$grade_item) {
            $error->errorMessage = get_string('couldnotfindgradeitem', 'turningtech', $objParticipant);
            return $error;
        }

        // Providing default value for the max grade if not existing.
        if ($grade_item->grademax <= 0) {
            $grade_item->grademax = TurningTechTurningHelper::getDefaultMaxGrade();
        }

        // save the grade
        if ($grade_item->update_final_grade($objParticipant->id, $objParticipant->score, 'gradebook')) {
            // everything is fine, no error to return.
            $error = FALSE;
        } else {
            // could not save in gradebook.
            $error->errorMessage = get_string('errorsavinggradeitem', 'turningtech');
        }

        return $error;
    }
}
?>