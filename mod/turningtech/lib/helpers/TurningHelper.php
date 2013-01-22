<?php

require_once($CFG->dirroot . '/mod/turningtech/lib/types/TurningExtendedSession.php');
require_once($CFG->dirroot . '/mod/turningtech/lib/helpers/MoodleHelper.php');

/**
 * handles communication with TurningPoint systems
 * @author jacob
 *
 * NOTE: callers which include/require this class MUST also include/require the following:
 * - [moodle root]/config.php
 * - mod/turningtech/lib.php
 * - mod/turningtech/lib/types/Escrow.php
 */
class TurningTechTurningHelper {
    /**
     * get an escrow instance.  This may be a new instance or one fetched
     * from the database, depending on the values handed in.
     * @param $course
     * @param $dto
     * @param $migrated
     * @return unknown_type
     */
    public static function getEscrowInstance($course, $dto, $grade_item, $migrated) {
        $instance = FALSE;
        $params   = array(
            'deviceid' => $dto->deviceId,
            'courseid' => $course->id,
            'itemid' => $grade_item->id,
            'points_possible' => $dto->pointsPossible,
            'migrated' => ($migrated ? 'TRUE' : 'FALSE')
        );
        // check if this represents an item in the DB
        if ($instance = TurningTechEscrow::fetch($params)) {
            return $instance;
        }
        // otherwise, generate a new one
        $params['points_possible'] = $dto->pointsEarned;
        return TurningTechEscrow::generate($params);
    }

    /**
     * looks up the device ID for the user in the given course.  If none can
     * be found, return FALSE
     * @param $user
     * @return unknown_type
     */
    public static function getDeviceIdByCourseAndStudent($course, $student) {
        /*
        $params = array(
            'userid' => $student->id,
            'courseid' => $course->id,
            'all_courses' => 0,
            'deleted' => 0
        );
        $device = TurningTechDeviceMap::fetch($params);
        // if no course-specific association exists, look for global
        if (!$device) {
            // do not search for a specific course
            unset($params['courseid']);
            $params['all_courses'] = 1;
            $device                = TurningTechDeviceMap::fetch($params);
        }
        */
        $params = array(
            'userid'  => $student->id,
            'deleted' => 0
        );

        $orders = array(
            'created' => "DESC"
        );

        $limits = array(
            'start' => 0,
            'end'   => 1
        );

        $device = TurningTechDeviceMap::fetch($params, $orders, $limits);
        return $device;
    }


    /**
     * Checks if there is a (user,course,device) association.  If so, returns
     * the user.  If not, checks if there is a global (user,device) association.
     * If no user is found, returns false.
     * @param $course
     * @param $deviceid
     * @return unknown_type
     */
    public static function getStudentByCourseAndDeviceId($course, $deviceid) {
        $params = array(
            'courseid' => $course->id,
            'deviceid' => $deviceid,
            'deleted' => 0
        );
        $map    = TurningTechDeviceMap::fetch($params);
        // if no course-specific map found, look for global
        if (!$map) {
            // do not search for specific course
            unset($params['courseid']);
            $params['all_courses'] = 1;
            $map                   = TurningTechDeviceMap::fetch($params);
        }
        if ($map) {
            return TurningTechMoodleHelper::getUserById($map->getField('userid'));
        }
        return FALSE;
    }

    /**
     * checks whether the given device ID is in the correct format
     * @param $deviceid
     * @return unknown_type
     */
    public static function isDeviceIdValid($deviceid) {
        global $CFG;
        switch ($CFG->turningtech_deviceid_format) {
            case TURNINGTECH_DEVICE_ID_FORMAT_HEX:
                return self::isDeviceIdValidHex($deviceid);
                break;
            case TURNINGTECH_DEVICE_ID_FORMAT_ALPHA:
                return self::isDeviceIdValidAlpha($deviceid);
                break;
            default:
                return FALSE;
        }
    }

    /**
     * checks if the given device ID is in valid hex form
     * @param $deviceid
     * @return unknown_type
     */
    public static function isDeviceIdValidHex($deviceid) {
        if (strlen($deviceid) >= TURNINGTECH_DEVICE_ID_FORMAT_HEX_MIN_LENGTH && strlen($deviceid) <= TURNINGTECH_DEVICE_ID_FORMAT_HEX_MAX_LENGTH && ctype_xdigit($deviceid)) {
            return TRUE;
        }
        return FALSE;
    }

    /**
     * checks if the given device ID is in valid alphanumeric form
     * @param $deviceid
     * @return unknown_type
     */
    public static function isDeviceIdValidAlpha($deviceid) {
        if (strlen($deviceid) >= TURNINGTECH_DEVICE_ID_FORMAT_ALPHA_MIN_LENGTH && ctype_alnum($deviceid)) {
            return TRUE;
        }
        return FALSE;
    }

    /**
     * determines if the user needs to see a reminder.  If so, returns the reminder message.
     * @param $user
     * @param $course
     * @return unknown_type
     */
    public static function getReminderMessage($user, $course) {
        // ensure we only show 1 reminder per session
        if (isset($_SESSION['USER']->turningtech_reminder)) {
            return NULL;
        }
        // set flag so reminder is not shown
        $_SESSION['USER']->turningtech_reminder = 1;
        return get_string('remindermessage', 'turningtech');
    }

    /**
     * compiles a list of all students who do not have devices registered
     * @return unknown_type
     */
    public static function getStudentsWithoutDevices($course) {
        $students = array();
        $roster   = TurningTechMoodleHelper::getClassRoster($course);
        if (!empty($roster)) {
            foreach ($roster as $r) {
                if (empty($r->devicemapid) && !isset($students[$r->id])) {
                    $students[$r->id] = $r;
                }
            }
        }
        return $students;
    }

    /**
     * provides the URL of the responseware provider
     * @return unknown_type
     */
    public static function getResponseWareUrl($action = FALSE) {
        global $CFG;
        $url = $CFG->turningtech_responseware_provider;
        if ($url[strlen($url) - 1] != '/') {
            $url .= '/';
        }
        if ($action) {
            switch ($action) {
                case 'login':
                    $url .= 'Login.aspx';
                    break;
                case 'forgotpassword':
                    $url .= 'ForgotPassword.aspx';
                    break;
            }
        }
        return $url;
    }

    public static function importSessionData($exportData) {
        $objStatus                      = new stdClass();
        $objStatus->ExportedData        = new stdClass();
        $objStatus->ExportedData->error = new stdClass();
        $objTurnExtSession              = new TurningExtendedSession();

        try {
            self::processSessionXML($exportData, &$objTurnExtSession);

            self::processSessionData($objTurnExtSession);

            $objStatus->ExportedData->error->code = 0;
            $objStatus->ExportedData->error->desc = "";
        }
        catch (CustomException $ex) {
            $objStatus->ExportedData->error->code = $ex->getCustomCode();
            $objStatus->ExportedData->error->desc = $ex->getCustomDesc();
        }
        catch (SoapFault $ex) {
            throw $ex;
        }
        catch (Exception $ex) {
            $objStatus->ExportedData->error->code = -1;
            $objStatus->ExportedData->error->desc = "An unknown exception occurred";
        }

        $objStatus->ExportedData->exportObject = $objTurnExtSession->getExportObjectName();
        $objStatus->ExportedData->courseId     = $objTurnExtSession->getCourseId();

        return $objStatus;
    }

    private function processSessionXML($exportData, $objTurnExtSession) {
        global $userCourses, $instructor;

        try {
            $objTurnExtSession->loadXML($exportData);

            $objTurnExtSession->validateXML();

            $objCourse = TurningTechMoodleHelper::getCourseById($objTurnExtSession->getCourseId());

            if (is_null($objCourse) || $objCourse == "") {
                throw new CustomException("", 0, 5, "Course id is unknown");
            }

            $intCrcLen = count($userCourses);
            $blnCrcFound = false;

            // Checking whether the current user has access over the current course.
            for ($i = 0; $i < $intCrcLen; $i++) {
                if ($objCourse->id == $userCourses[$i]->id) {
                    $blnCrcFound = true;
                }
            }

            if (!$blnCrcFound) {
                throw new SoapFault('AuthenticationException', get_string('userisnotinstructor', 'turningtech'));
            }

            $objTurnExtSession->prepareDataFromXML();
        }
        catch (CustomException $ex) {
            throw $ex;
        }
        catch (SoapFault $ex) {
            throw $ex;
        }
        catch (Exception $ex) {
            throw new CustomException($ex->getMessage(), $ex->getCode(), -1, "An unknown exception occurred");
        }
    }

    private function processSessionData($objTurnExtSession) {
        try {
            // ~~ The following code has been commented till all questions types info is ready and email is ready to be sent to users.
            /*
            switch ($objTurnExtSession->getExportObjectType()) {
                case "non-session":

                    $objTurnExtSession->saveUpdateScoreData();

                    break;

                case "session";

                    $objTurnExtSession->saveUpdateScoreData();

                    self::sendPerformanceEmail($objTurnExtSession);

                    break;

                default:

                    throw new CustomException("", 0, 4, "Unknown export type specified");
            }
            */
            // ~~ The following code will be used till all questions types info is ready and email is ready to be sent to users.
            $objTurnExtSession->saveUpdateScoreData();

            return $objTurnExtSession;
        }
        catch (CustomException $ex) {
            throw $ex;
        }
        catch (Exception $ex) {
            throw new CustomException($ex->getMessage(), $ex->getCode(), -1, "An unknown exception occurred");
        }
    }

    private function sendPerformanceEmail($objTurnExtSession) {
        // If email is not to be sent to student.
        if (!is_object($objTurnExtSession->getEmailInfo())) {
            return;
        }

        try {
            // Get the Email Template Content.
            $arrEmailTemplates = self::getEmailTemplateContent();
        }
        catch (CustomException $ex) {
            throw $ex;
        }
        catch (Exception $ex) {
            throw new CustomException($ex->getMessage(), $ex->getCode(), 3, "Email Template could not be read");
        }

        try {
            // Set the Email Configurations.
            $objMailer = self::configEmailSettings($objTurnExtSession->getEmailInfo());
        }
        catch (CustomException $ex) {
            throw $ex;
        }
        catch (Exception $ex) {
            throw new CustomException($ex->getMessage(), $ex->getCode(), 3, "Email Configuration could not be set");
        }

        $arrParticipants = $objTurnExtSession->getParticipantsList();
        $arrQuestions    = $objTurnExtSession->getQuestionsList();

        $strSubject      = "Phoenix Session Results";
        $strInstrMessage = $objTurnExtSession->getEmailInfo()->content;

        $arrSearch = array(
            "{SUBJECT}",
            "{INSTRUCTOR_MESSAGE}",
            "{USER_NAME}",
            "{USER_ID}",
            "{RESPONDING_DEVICE}",
            "{PERF_POINTS_EARNED}",
            "{PERC_CORRECT}"
        );

        // Traverse through all of the participants
        foreach ($arrParticipants as $participant) {
            $strQuestionRows = '';
            $cnt             = 0;
            $cntCrctAns      = 0;
            $blnCrctAnsRes   = false;

            // Traverse through all of the questions
            foreach ($arrQuestions as $key => $question) {
                $arrAnswerChoices = $question->answerchoices;

                // Traversing through the Answer Choices to get the correct one
                foreach ($arrAnswerChoices as $k => $answerChoice) {
                    if ($answerChoice->correct == 1) {
                        $intCrctAnsChc = $k + 1;
                        break;
                    }
                }

                if ($cnt++ % 2 != 0) {
                    $strRowBgColor = "#D5D5D5";
                } else {
                    $strRowBgColor = "#FFFFFF";
                }

                $intResAnsChc    = $participant->questions[$key]->responsedanswerchoice;
                $strResAnsChoice = $arrAnswerChoices[$intResAnsChc - 1]->text;

                if ($intResAnsChc != $intCrctAnsChc) {
                    $strResAnsChoice .= " <i>(i)</i>";
                    $strColBgColor = "RED";
                } else {
                    $strResAnsChoice .= " <i>(c)</i>";
                    $strColBgColor = "GREEN";

                    $cntCrctAns++;
                }

                $strAnswerChoices = (($intCrctAnsChc == 1) ? '<font style="color:GREEN">' : '') . "A. " . $arrAnswerChoices[0]->text . (($intCrctAnsChc == 1) ? ' <i>(c)</i> </font>' : '') . (($intCrctAnsChc == 2) ? '<font style="color:GREEN">' : '') . " B. " . $arrAnswerChoices[1]->text . (($intCrctAnsChc == 2) ? ' <i>(c)</i> </font>' : '') . (($intCrctAnsChc == 3) ? '<font style="color:GREEN">' : '') . " C. " . $arrAnswerChoices[2]->text . (($intCrctAnsChc == 3) ? ' <i>(c)</i> </font>' : '') . (($intCrctAnsChc == 4) ? '<font style="color:GREEN">' : '') . " D. " . $arrAnswerChoices[3]->text . (($intCrctAnsChc == 4) ? ' <i>(c)</i> </font>' : '');

                $arrDynSearch = array(
                    "{ROW_BG_COLOR}",
                    "{COL_BG_COLOR}",
                    "{QUESTION_TEXT}",
                    "{RESPOND_ANSWER_CHOICE_TEXT}",
                    "{ANSWER_CHOICES_LIST}"
                );

                $arrDynReplace = array(
                    $strRowBgColor,
                    $strColBgColor,
                    ($key + 1) . ". " . $question->text,
                    chr(65 + $intResAnsChc - 1) . ". " . $strResAnsChoice,
                    $strAnswerChoices
                );

                $strQuestionRows .= str_replace($arrDynSearch, $arrDynReplace, $arrEmailTemplates[1]);
            }

            $userConName = $participant->lastname . " " . $participant->firstname;

            $arrReplace = array(
                $strSubject,
                $strInstrMessage,
                $userConName,
                $participant->userid,
                $participant->respondingdevice,
                $participant->performancescore,
                number_format((($cntCrctAns * 100) / $cnt), 2, '.', '') . "%"
            );

            $strEmailBody = $arrEmailTemplates[0];
            $strEmailBody = str_replace($arrSearch, $arrReplace, $strEmailBody);

            $objMailer->Body = str_replace("{DYNAMIC_CONTENT_AREA}", $strQuestionRows, $strEmailBody);

            try {
                // To clean up the previously added 'To' Addresses.
                $objMailer->ClearAddresses();
                $objMailer->AddAddress($participant->email, "");

                $objMailer->Send();
            }
            catch (Exception $ex) {
            } // Do nothing if mail could not be sent to a particular user.
        }
    }

    private function getEmailTemplateContent() {
        global $CFG;

        try {
            return explode("<!-- ~| PLEASE DO NOT REMOVE THIS COMMENT |~ -->", file_get_contents($CFG->wwwroot . '/mod/turningtech/lib/templates/ImportSessionEmailTemplate.html'));
        }
        catch (CustomException $ex) {
            throw $ex;
        }
        catch (Exception $ex) {
            throw new CustomException($ex->getMessage(), $ex->getCode(), 3, "Email Template could not be read.");
        }
    }

    private function configEmailSettings($objEmail) {
        try {
            $objMailer = get_mailer();

            $objMailer->Subject = "Phoenix Session Results";
            $objMailer->Host    = $_SERVER['HTTP_HOST'];
            $objMailer->IsHTML(true);
            $objMailer->SetFrom($objEmail->from, "Turning Technologies");
            $objMailer->IsMail();
            $objMailer->Priority = 3;
            $objMailer->CharSet  = 'UTF-16';
            $objMailer->AltBody  = "\n\n";

            return $objMailer;
        }
        catch (CustomException $ex) {
            throw $ex;
        }
        catch (Exception $ex) {
            throw new CustomException($ex->getMessage(), $ex->getCode(), 3, "Email Settings could not be configured.");
        }
    }

    public static function getDefaultMaxGrade() {
        return 9999;
    }

    /*
     * Checks and upgrade Moodle w.r.t Turning Tech Device Types.
     */
    public static function updateUserDeviceMappings() {
        global $CFG, $DB, $USER;

        $tblMapping = "turningtech_device_mapping";

        $arrResponseCardDevice = array();
        $arrResponseWareDevice = array();

        // Update all of the existing records of device type mapping for the current user.
        $sql = "SELECT id, courseid, typeid  FROM {" . $tblMapping . "} tdm WHERE userid = :userid ORDER BY id DESC";

        $params            = array();
        $params['userid']  = $USER->id;

        $arrMapping = $DB->get_records_sql($sql, $params);

        if (is_array($arrMapping) && count($arrMapping)) {
            foreach ($arrMapping as $key => $map) {
                if ($map->typeid == 0) {
                    $objData                 = new stdClass();
                    $objData->id             = $map->id;
                    $objData->all_courses    = 1;
                    $objData->deleted        = 1;              // Initially let all of the Response Card devices as dead.

                    if(is_null($map->courseid)) {              // If device registered as Response Ware
                        $objData->typeid     = 2;

                        $arrResponseWareDevice[] = $objData;
                    } else {                                   // If device registered as Response Card
                        $objData->typeid     = 1;

                        $arrResponseCardDevice[] = $objData;
                    }
                }
            }

            // If at-least one Response Card Device need to be upgraded.
            if(count($arrResponseCardDevice))
            {
                // The very first i.e. recently added device only should be alive.
                $arrResponseCardDevice[0]->deleted = 0;

                foreach ($arrResponseCardDevice as $key => $responseCardDevice) {
                    $DB->update_record($tblMapping, $responseCardDevice);
                }
            }

            // If at-least one Response Ware Device need to be upgraded.
            if(count($arrResponseWareDevice))
            {
                // The very first i.e. recently added device only should be alive.
                $arrResponseWareDevice[0]->deleted = 0;

                foreach ($arrResponseWareDevice as $key => $responseWareDevice) {
                    $DB->update_record($tblMapping, $responseWareDevice);
                }
            }
        }
    }

    public static function isImportSessionFileValid($arrFileInfo) {
        $arrValidFileType = array(
            "text/plain"
        );

        $strFileExtension = strtoupper(array_pop(explode(".", $arrFileInfo["name"])));

        // Validate that the media type of the file is lying under Valid File Types and the file extension of the file is "TXT" only.
        if (!in_array($arrFileInfo["type"], $arrValidFileType) || $strFileExtension != "TXT") {
            return -1;
        }

        return 1;
    }

    /**
     *
     * @param $student
     * @return unknown_type
     */
    //  public static function getReminderEmailBody($course) {
    //    global $CFG;
    //    $raw = "\n{$CFG->turningtech_reminder_email_body}\n";
    //    return str_replace(
    //      array('@coursename', '@courselink'),
    //      array($course->fullname, "{$CFG->wwwroot}/course/view.php?id={$course->id}"),
    //      $raw
    //    );
    //  }
}

class CustomException extends Exception {
    private $customCode;
    private $customDesc;

    public function __construct($message, $code = 0, $customCode = 0, $customDesc = '') {
        parent::__construct($message, $code, null);

        $this->customCode = $customCode;
        $this->customDesc = $customDesc;
    }

    public function __toString() {
        return __CLASS__ . ": [{$this->customCode}]: {$this->customDesc}\n";
    }

    public function getCustomCode() {
        return $this->customCode;
    }

    public function getCustomDesc() {
        return $this->customDesc;
    }
}

?>