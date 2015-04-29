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
 * This file handles communication with TurningPoint systems
 *
 * @author jacob
 * @package    mod_turningtech
 * @copyright  2012 Turning Technologies
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *         NOTE: callers which include/require this class MUST also include/require the following:
 *         - [moodle root]/config.php
 *         - mod/turningtech/lib.php
 *         - mod/turningtech/lib/types/Escrow.php
 */
require_once($CFG->dirroot . '/mod/turningtech/lib/types/TurningExtendedSession.php');
require_once($CFG->dirroot . '/mod/turningtech/lib/helpers/MoodleHelper.php');
/**
 * Class to handle communication with TurningPoint systems
 * 
 * @author jacob
 * @package    mod_turningtech
 * @copyright  2012 Turning Technologies
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *         NOTE: callers which include/require this class MUST also include/require the following:
 *         - [moodle root]/config.php
 *         - mod/turningtech/lib.php
 *         - mod/turningtech/lib/types/Escrow.php
 */
class TurningTechTurningHelper {
    /**
     * get an escrow instance.
     * This may be a new instance or one fetched
     * from the database, depending on the values handed in.
     * @copyright  2012 Turning Technologies
     * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
     * 
     * @param object $course
     * @param unknown_type $dto
     * @param unknown_type $grade_item
     * @param unknown_type $migrated
     * @return unknown_type
     */
    public static function getescrowinstance($course, $dto, $grade_item, $migrated) {
        $instance = false;
        $params = array ('deviceid' => $dto->deviceId, 'courseid' => $course->id, 'itemid' => $grade_item->id,
                         'points_possible' => $dto->pointsPossible,
                         'migrated' => ($migrated ? 'true' : 'false') );
        // Check if this represents an item in the DB.
        $instance = TurningTechEscrow::fetch($params);
        if ($instance) {
            return $instance;
        }
        // Otherwise, generate a new one.
        $params['points_possible'] = $dto->pointsEarned;
        return TurningTechEscrow::generate($params);
    }
    /**
     * looks up the device ID for the user in the given course.
     * If none can
     * be found, return false
     * 
     * @param object $course
     * @param object $student
     * @return unknown_type
     */
    public static function getdeviceidbycourseandstudent($course, $student) {
        /*
         * $params = array( 'userid' => $student->id, 'courseid' => $course->id, 'all_courses' => 0, 'deleted' => 0 ); $device =
         * TurningTechDeviceMap::fetch($params); // if no course-specific association exists, look for global if (!$device) { // do
         * not search for a specific course unset($params['courseid']); $params['all_courses'] = 1; $device =
         * TurningTechDeviceMap::fetch($params); }
         */
        $params = array ('userid' => $student->id, 'deleted' => 0 );
        $orders = array ('created' => "DESC" );
        $limits = array ('start' => 0, 'end' => 1 );
        $device = TurningTechDeviceMap::fetch($params, $orders, $limits);
        return $device;
    }
    /**
     * Checks if there is a (user,course,device) association.
     * If so, returns
     * the user. If not, checks if there is a global (user,device) association.
     * If no user is found, returns false.
     * 
     * @param object $course
     * @param string $deviceid
     * @return unknown_type
     */
    public static function getstudentbycourseanddeviceid($course, $deviceid) {
        $params = array ('deviceid' => $deviceid, 'deleted' => 0 );
        $count = TurningTechDeviceMap::countstudents($course->id, $deviceid);
        if ($count==1) {
            $map = TurningTechDeviceMap::fetch($params);
            // If no course-specific map found, look for global.
            if ($map) {
                return TurningTechMoodleHelper::getuserbyid($map->getField('userid'));
            }
        }
        return false;
    }
    /**
     * Checks if there is a (user,course) association.
     * If so, returns
     * the user. If not, checks if there is a global (user,device) association.
     * If no user is found, returns false.
     * 
     * @param object $course
     * @param int $userid
     * @return unknown_type
     */
    public static function getstudentbycourseanduserid($course, $userid) {
        $params = array ('courseid' => $course->id, 'userid' => $userid, 'deleted' => 0 );
        $map = TurningTechDeviceMap::fetch($params);
        // If no course-specific map found, look for global.
        if (! $map) {
            // Do not search for specific course.
            unset($params['courseid']);
            $params['all_courses'] = 1;
            $map = TurningTechDeviceMap::fetch($params);
        }
        if ($map) {
            return TurningTechMoodleHelper::getuserbyid($map->getField('userid'));
        }
        return false;
    }
    /**
     * Checks if there is a user.
     * If so, returns the user.
     * If no user is found, returns false.
     * 
     * @param string $username
     * @return mixed
     */
    public static function getstudentbyusername($username) {
        if (TurningTechMoodleHelper::getuserbyusername($username)) {
            return TurningTechMoodleHelper::getuserbyusername($username);
        }
        return false;
    }
    /**
     * checks whether the given device ID is in the correct format
     * 
     * @param string $deviceid
     * @return bool
     */
    public static function isdeviceidvalid($deviceid) {
        global $CFG;
        switch ($CFG->turningtech_deviceid_format) {
            case TURNINGTECH_DEVICE_ID_FORMAT_HEX :
                return self::isdeviceidvalidhex($deviceid);
                break;
            case TURNINGTECH_DEVICE_ID_FORMAT_ALPHA :
                return self::isdeviceidvalidalpha($deviceid);
                break;
            default :
                return false;
        }
    }
    /**
     * checks if the given device ID is in valid hex form
     * 
     * @param string $deviceid
     * @return bool
     */
    public static function isdeviceidvalidhex($deviceid) {
        if ((strlen($deviceid) == TURNINGTECH_DEVICE_ID_FORMAT_HEX_MIN_LENGTH ||
                                         strlen($deviceid) == TURNINGTECH_DEVICE_ID_FORMAT_HEX_MAX_LENGTH) &&
                                         ctype_xdigit($deviceid)) {
            return true;
        }
        return false;
    }
    /**
     * checks if the given device ID is in valid alphanumeric form
     * 
     * @param string $deviceid
     * @return bool
     */
    public static function isdeviceidvalidalpha($deviceid) {
        if ((strlen($deviceid) == TURNINGTECH_DEVICE_ID_FORMAT_ALPHA_MIN_LENGTH ||
                                         strlen($deviceid) == TURNINGTECH_DEVICE_ID_FORMAT_ALPHA_MAX_LENGTH) &&
                                         ctype_alnum($deviceid)) {
            return true;
        }
        return false;
    }
    /**
     * determines if the user needs to see a reminder.
     * If so, returns the reminder message.
     * 
     * @param object $user
     * @param object $course
     * @return unknown_type
     */
    public static function getremindermessage($user, $course) {
        // Ensure we only show 1 reminder per session.
        if (isset($_SESSION['USER']->turningtech_reminder)) {
            return null;
        }
        // Set flag so reminder is not shown.
        $_SESSION['USER']->turningtech_reminder = 1;
        return get_string('remindermessage', 'turningtech');
    }
    /**
     * compiles a list of all students who do not have devices registered
     * @param object $course
     * @return unknown_type
     */
    public static function getstudentswithoutdevices($course) {
        $students = array ();
        $roster = TurningTechMoodleHelper::getclassrosterxml($course);
        if (! empty($roster)) {
            foreach ($roster as $r) {
                if (empty($r->deviceid) && ! isset($students[$r->id])) {
                    $students[$r->id] = $r;
                }
            }
        }
        return $students;
    }
    /**
     * provides the URL of the responseware provider
     * @param mixed $action
     * @return unknown_type
     */
    public static function getresponsewareurl($action = false) {
        global $CFG;
        $url = $CFG->turningtech_responseware_provider;
        if ($url[strlen($url) - 1] != '/') {
            $url .= '/';
        }
        if ($action) {
            switch ($action) {
                case 'login' :
                    $url .= 'Login.aspx';
                    break;
                case 'forgotpassword' :
                    $url .= 'ForgotPassword.aspx';
                    break;
                case 'createaccount' :
                    $url .= 'CreateAccount.aspx';
                    break;
            }
        }
        return $url;
    }
    /**
     * Import Session Data
     * @param unknown_type $exportdata
     * @throws SoapFault
     * @return stdClass
     */
    public static function importsessiondata($exportdata) {
        $objstatus = new stdClass();
        $objstatus->ExportedData = new stdClass();
        $objstatus->ExportedData->error = new stdClass();
        $objturnextsession = new TurningExtendedSession();
        try {
            self::processsessionxml($exportdata, $objturnextsession);
            self::processsessiondata($objturnextsession);
            $objstatus->ExportedData->error->code = 0;
            $objstatus->ExportedData->error->desc = "";
        } catch ( CustomException $ex ) {
            $objstatus->ExportedData->error->code = $ex->getcustomcode();
            $objstatus->ExportedData->error->desc = $ex->getcustomdesc();
        } catch ( SoapFault $ex ) {
            throw $ex;
        } catch ( Exception $ex ) {
            $objstatus->ExportedData->error->code = - 1;
            $objstatus->ExportedData->error->desc = "An unknown exception occurred";
        }
        $objstatus->ExportedData->exportObject = $objturnextsession->getexportobjectname();
        $objstatus->ExportedData->courseId = $objturnextsession->getcourseid();
        return $objstatus;
    }
    /**
     * Process Session XML
     * @param unknown_type $exportdata
     * @param unknown_type $objturnextsession
     * @throws CustomException
     * @throws SoapFault
     */
    private function processsessionxml($exportdata, &$objturnextsession) {
        global $usercourses, $instructor;
        try {
            $objturnextsession->loadXML($exportdata);
            $objturnextsession->validateXML();
            $objcourse = TurningTechMoodleHelper::getcoursebyid($objturnextsession->getCourseId());
            if (is_null($objcourse) || $objcourse == "") {
                throw new CustomException("", 0, 5, "Course id is unknown");
            }
            $intcrclen = count($usercourses);
            $blncrcfound = false;
            // Checking whether the current user has access over the current course.
            for ($i = 0; $i < $intcrclen; $i ++) {
                if ($objcourse->id == $usercourses[$i]->id) {
                    $blncrcfound = true;
                }
            }
            if (! $blncrcfound) {
                throw new SoapFault('AuthenticationException', get_string('userisnotinstructor', 'turningtech'));
            }
            $objturnextsession->preparedatafromxml();
        } catch ( CustomException $ex ) {
            throw $ex;
        } catch ( SoapFault $ex ) {
            throw $ex;
        } catch ( Exception $ex ) {
            throw new CustomException($ex->getMessage(), $ex->getCode(), - 1, "An unknown exception occurred");
        }
    }
    /**
     * process session data
     * @param unknown_type $objturnextsession
     * @throws CustomException
     * @return unknown
     */
    private function processsessiondata($objturnextsession) {
        try {
            // The following code has been commented till all questions types info is ready and email is ready to be sent to users.
            /*
             * switch ($objturnextsession->getExportObjectType()) { case "non-session": $objturnextsession->saveupdatescoredata();
             * break; case "session"; $objturnextsession->saveupdatescoredata(); self::sendperformanceemail($objturnextsession);
             * break; default: throw new CustomException("", 0, 4, "Unknown export type specified"); }
             */
            // The following code will be used till all questions types info is ready and email is ready to be sent to users.
            $objturnextsession->saveupdatescoredata();
            return $objturnextsession;
        } catch ( CustomException $ex ) {
            throw $ex;
        } catch ( Exception $ex ) {
            throw new CustomException($ex->getMessage(), $ex->getCode(), - 1, "An unknown exception occurred");
        }
    }
    /**
     * Send performance email
     * @param unknown_type $objturnextsession
     * @throws CustomException
     */
    private function sendperformanceemail($objturnextsession) {
        // If email is not to be sent to student.
        if (! is_object($objturnextsession->getEmailInfo())) {
            return;
        }
        try {
            // Get the Email Template Content.
            $arremailtemplates = self::getemailtemplatecontent();
        } catch ( CustomException $ex ) {
            throw $ex;
        } catch ( Exception $ex ) {
            throw new CustomException($ex->getMessage(), $ex->getCode(), 3, "Email Template could not be read");
        }
        try {
            // Set the Email Configurations.
            $objmailer = self::configemailsettings($objturnextsession->getEmailInfo());
        } catch ( CustomException $ex ) {
            throw $ex;
        } catch ( Exception $ex ) {
            throw new CustomException($ex->getMessage(), $ex->getCode(), 3, "Email Configuration could not be set");
        }
        $arrparticipants = $objturnextsession->getParticipantsList();
        $arrquestions = $objturnextsession->getQuestionsList();
        $strsubject = "Phoenix Session Results";
        $strinstrmessage = $objturnextsession->getEmailInfo()->content;
        $arrsearch = array ("{SUBJECT}", "{INSTRUCTOR_MESSAGE}",
                         "{USER_NAME}", "{USER_ID}", "{RESPONDING_DEVICE}",
                         "{PERF_POINTS_EARNED}", "{PERC_CORRECT}" );
        // Traverse through all of the participants.
        foreach ($arrparticipants as $participant) {
            $strquestionrows = '';
            $cnt = 0;
            $cntcrctans = 0;
            $blncrctansres = false;
            // Traverse through all of the questions.
            foreach ($arrquestions as $key => $question) {
                $arranswerchoices = $question->answerchoices;
                // Traversing through the Answer Choices to get the correct one.
                foreach ($arranswerchoices as $k => $answerchoice) {
                    if ($answerchoice->correct == 1) {
                        $intcrctanschc = $k + 1;
                        break;
                    }
                }
                if ($cnt ++ % 2 != 0) {
                    $strrowbgcolor = "#D5D5D5";
                } else {
                    $strrowbgcolor = "#FFFFFF";
                }
                $intresanschc = $participant->questions[$key]->responsedanswerchoice;
                $strresanschoice = $arranswerchoices[$intresanschc - 1]->text;
                if ($intresanschc != $intcrctanschc) {
                    $strresanschoice .= " <i>(i)</i>";
                    $strcolbgcolor = "RED";
                } else {
                    $strresanschoice .= " <i>(c)</i>";
                    $strcolbgcolor = "GREEN";
                    $cntcrctans ++;
                }
                $stranswerchoices = (($intcrctanschc == 1) ? '<font style="color:GREEN">' : '') . "A. " .
                 $arranswerchoices[0]->text . (($intcrctanschc == 1) ? ' <i>(c)</i> </font>' : '') .
                  (($intcrctanschc == 2) ? '<font style="color:GREEN">' : '') . " B. " .
                   $arranswerchoices[1]->text . (($intcrctanschc == 2) ? ' <i>(c)</i> </font>' : '') .
                    (($intcrctanschc == 3) ? '<font style="color:GREEN">' : '') . " C. " .
                     $arranswerchoices[2]->text . (($intcrctanschc == 3) ? ' <i>(c)</i> </font>' : '') .
                      (($intcrctanschc == 4) ? '<font style="color:GREEN">' : '') . " D. " .
                 $arranswerchoices[3]->text . (($intcrctanschc == 4) ? ' <i>(c)</i> </font>' : '');
                $arrdynsearch = array ("{ROW_BG_COLOR}", "{COL_BG_COLOR}", "{QUESTION_TEXT}",
                                 "{RESPOND_ANSWER_CHOICE_TEXT}", "{ANSWER_CHOICES_LIST}" );
                $arrdynreplace = array ($strrowbgcolor, $strcolbgcolor,
                                 ($key + 1) . ". " . $question->text, chr(65 + $intresanschc - 1) . ". " . $strresanschoice,
                                 $stranswerchoices );
                $strquestionrows .= str_replace($arrdynsearch, $arrdynreplace, $arremailtemplates[1]);
            }
            $userconname = $participant->lastname . " " . $participant->firstname;
            $arrreplace = array ($strsubject, $strinstrmessage, $userconname, $participant->userid,
                             $participant->respondingdevice, $participant->performancescore,
                             number_format((($cntcrctans * 100) / $cnt), 2, '.', '') . "%" );
            $stremailbody = $arremailtemplates[0];
            $stremailbody = str_replace($arrsearch, $arrreplace, $stremailbody);
            $objmailer->Body = str_replace("{DYNAMIC_CONTENT_AREA}", $strquestionrows, $stremailbody);
            try {
                // To clean up the previously added 'To' Addresses.
                $objmailer->ClearAddresses();
                $objmailer->AddAddress($participant->email, "");
                $objmailer->Send();
            } catch ( Exception $ex ) {
                $returnval = false;
            } // Do nothing if mail could not be sent to a particular user.
        }
    }
    /**
     * Get the Email template
     * @throws CustomException
     * @return multitype:
     */
    private function getemailtemplatecontent() {
        global $CFG;
        try {
            return explode("<!-- ~| PLEASE DO NOT REMOVE THIS COMMENT |~ -->", file_get_contents($CFG->wwwroot .
                                             '/mod/turningtech/lib/templates/ImportSessionEmailTemplate.html'));
        } catch ( CustomException $ex ) {
            throw $ex;
        } catch ( Exception $ex ) {
            throw new CustomException($ex->getMessage(), $ex->getCode(), 3, "Email Template could not be read.");
        }
    }
    /**
     * Configure email settings
     * @param unknown_type $objemail
     * @throws CustomException
     * @return Ambigous <moodle_phpmailer, NULL, unknown>
     */
    private function configemailsettings($objemail) {
        try {
            $objmailer = get_mailer();
            $objmailer->Subject = "Phoenix Session Results";
            $objmailer->Host = $_SERVER['HTTP_HOST'];
            $objmailer->IsHTML(true);
            $objmailer->SetFrom($objemail->from, "Turning Technologies");
            $objmailer->IsMail();
            $objmailer->Priority = 3;
            $objmailer->CharSet = 'UTF-16';
            $objmailer->AltBody = "\n\n";
            return $objmailer;
        } catch ( CustomException $ex ) {
            throw $ex;
        } catch ( Exception $ex ) {
            throw new CustomException($ex->getMessage(), $ex->getCode(), 3, "Email Settings could not be configured.");
        }
    }
    /**
     * Get default max Grade
     * @return number
     */
    public static function getdefaultmaxgrade() {
        return 9999;
    }
    /**
     * Checks and upgrade Moodle w.r.t Turning Tech Device Types.
     */
    public static function updateuserdevicemappings() {
        global $CFG, $DB, $USER;
        $tblmapping = "turningtech_device_mapping";
        $arrresponsecarddevice = array ();
        $arrresponsewaredevice = array ();
        // Update all of the existing records of device type mapping for the current user.
        $sql = "SELECT id, courseid, typeid  FROM {" . $tblmapping . "} tdm WHERE userid = :userid ORDER BY id DESC";
        $params = array ();
        $params['userid'] = $USER->id;
        $arrmapping = $DB->get_records_sql($sql, $params);
        if (is_array($arrmapping) && count($arrmapping)) {
            foreach ($arrmapping as $key => $map) {
                if ($map->typeid == 0) {
                    $objdata = new stdClass();
                    $objdata->id = $map->id;
                    $objdata->all_courses = 1;
                    $objdata->deleted = 1; // Initially let all of the Response Card devices as dead.
                    if (is_null($map->courseid)) { // If device registered as Response Ware.
                        $objdata->typeid = 2;
                        $arrresponsewaredevice[] = $objdata;
                    } else { // If device registered as Response Card.
                        $objdata->typeid = 1;
                        $arrresponsecarddevice[] = $objdata;
                    }
                }
            }
            // If at-least one Response Card Device need to be upgraded.
            if (count($arrresponsecarddevice)) {
                // The very first i.e. recently added device only should be alive.
                $arrresponsecarddevice[0]->deleted = 0;
                foreach ($arrresponsecarddevice as $key => $responsecarddevice) {
                    $DB->update_record($tblmapping, $responsecarddevice);
                }
            }
            // If at-least one Response Ware Device need to be upgraded.
            if (count($arrresponsewaredevice)) {
                // The very first i.e. recently added device only should be alive.
                $arrresponsewaredevice[0]->deleted = 0;
                foreach ($arrresponsewaredevice as $key => $responsewaredevice) {
                    $DB->update_record($tblmapping, $responsewaredevice);
                }
            }
        }
    }
    /**
     * Check if imported session file is valid.
     * @param unknown_type $arrfileinfo
     * @return number
     */
    public static function isimportsessionfilevalid($arrfileinfo) {
        $arrvalidfiletype = array ("application/zip" );
        $strfileextension = strtoupper(array_pop(explode(".", $arrfileinfo["name"])));
        // Validate that the media type of the file is lying under Valid File Types and the file extension of the file is "TXT"
        // only.
        if (! in_array($arrfileinfo["type"], $arrvalidfiletype) || $strfileextension != "tpzx") {
            return - 1;
        }
        return 1;
    }

}
/**
 * Exception Handler
 * @copyright  2012 Turning Technologies
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class CustomException extends Exception {
    /**
     * @var unknown_type
     */
    private $customcode;
    /**
     * @var unknown_type
     */
    private $customdesc;
    /**
     * Constructor
     * @param unknown_type $message
     * @param unknown_type $code
     * @param unknown_type $customcode
     * @param unknown_type $customdesc
     */
    public function __construct($message, $code = 0, $customcode = 0, $customdesc = '') {
        parent::__construct($message, $code, null);
        $this->customcode = $customcode;
        $this->customdesc = $customdesc;
    }
    /** (non-PHPdoc)
     * @see Exception::__toString()
     */
    public function __toString() {
        return __CLASS__ . ": [{$this->customcode}]: {$this->customdesc}\n";
    }
    /**
     * get custom code
     * @return unknown_type
     */
    public function getcustomcode() {
        return $this->customcode;
    }
    /**
     * get custom description
     * @return unknown_type
     */
    public function getcustomdesc() {
        return $this->customdesc;
    }
}