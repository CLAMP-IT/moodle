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
 * This file is used for manipulating TurningPoint session data
 * @author jacob
 * @copyright  2012 Turning Technologies
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
/**
 * class for manipulating TurningPoint session data
 * @author jacob
 * @package mod_turningtech
 * @copyright  2012 Turning Technologies
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class TurningSession {
    // Current course (if any).
    /**
     * @var unknown_type
     */
    private $activecourse;
    // List of students in session.
    /**
     * @var unknown_type
     */
    private $participants = array ();
    // XML DOM for this session.
    /**
     * @var unknown_type
     */
    private $dom;
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
     * @param object $course
     * @return unknown_type
     */
    public function setactivecourse($course) {
        $this->activecourse = $course;
    }
    /**
     * get the active course
     * 
     * @return unknown_type
     */
    public function getactivecourse() {
        return $this->activecourse;
    }
    /**
     * get the device score array
     * @param mixed $data
     * @return unknown_type
     */
    public function getassignmenttitle($data) {
        try {
            $data = self::removeBOM($data);
            if (strpos($data, '<?xml') !== 0) {
                throw new moodle_exception('invalidxmlresponse');
            }
            $data = str_replace('xmlns=', 'ns=', $data);
            $xml = new SimpleXMLElement($data);
            return strval($xml->name);
        } catch (Exception $e) {
            // An error occured while trying to parse the XML, let's just return nothing. SimpleXML does not
            // return a more specific Exception, that's why the global Exception class is caught here.
            return false;
        }
    }
    /**
     * get the device score array
     * @param mixed $data
     * @return unknown_type
     */
    public function getadevicescorelist($data) {
        try {
            $data = self::removeBOM($data);
            if (strpos($data, '<?xml') !== 0) {
                throw new moodle_exception('invalidxmlresponse');
            }
            $data = str_replace('xmlns=', 'ns=', $data);
            $xml = new SimpleXMLElement($data);
        } catch (Exception $e) {
            // An error occured while trying to parse the XML, let's just return nothing. SimpleXML does not
            // return a more specific Exception, that's why the global Exception class is caught here.
            return false;
        }
        $devices = array();
        $actions = array();
        $result = $xml->xpath('gradeentries');
        $name = $xml->name;
        $maxpoint = $xml->scoring->calculatedmaxpoints;
        foreach ($result[0] as $res => $val) {
            if (count($val)) {
                $devices[strval($val->assigneddevice)] = strval($val->calculatedscore);
                $a = new stdClass();
                $a->deviceId = strval($val->assigneddevice);
                $a->pointsEarned = strval($val->calculatedscore);
                $a->pointsPossible = strval($maxpoint);
                $actions[] = $a;
            }
        }
        return $actions;
    }
    /**
     * reads grade data from a session file
     * 
     * @param mixed $filedata
     * @param mixed $filename
     * @param mixed $override
     * @return unknown_type
     */
    public function importsession($filedata, $filename, $override) {
        $sessionmap = new TurningSessionMap($filedata, $filename);
        $assignmenttitle = $sessionmap->getsessionname();
        if (!$actions = $sessionmap->getdevicescore()) {
            throw new Exception(get_string('couldnotparsesessionfile', 'turningtech'));
        }
        if (count($actions)) {
            // Check if we need to create the gradebook item.
            $grade_item = TurningTechMoodleHelper::getgradebookitembycourseandtitle($this->activecourse, $assignmenttitle);
            if (! $grade_item) {
                // Must create gradebook item. Get points possible from one of the actions.
                $points_possible = $actions[0]->pointsPossible ? $actions[0]->pointsPossible : 0;
                TurningTechMoodleHelper::creategradebookitem($this->activecourse, $assignmenttitle, $points_possible);
            }
            $mode = ($override ? TURNINGTECH_SAVE_ALLOW_OVERRIDE : TURNINGTECH_SAVE_NO_OVERRIDE);
            $service = new TurningTechIntegrationServiceProvider();
            // Counts number of correctly saved items.
            $saved = 0;
            // Records errors.
            $errors = array ();
            // Pre-load some strings for comparison.
            $escrow = get_string('gradesavedinescrow', 'turningtech');
            $cannotoverride = get_string('cannotoverridegrade', 'turningtech');
            // Keep track of which line of the session file we're on.
            $linecounter = 1;
            // Iterate through actions and save them.
            foreach ($actions as $action) {
                $action->itemTitle = $assignmenttitle;
                if ($error = $service->savegradebookitem($this->activecourse, $action, $mode)) {
                    switch ($error->errorMessage) {
                        case $escrow :
                            // Grade saved in escrow; no error.
                            $saved ++;
                            break;
                        /*
                         * case $cannotoverride: // failed attempt to override grade $overrides++; break;
                         */
                        default :
                            $a = new stdClass();
                            $a->line = $linecounter;
                            $a->message = $error->errorMessage;
                            $errors[] = get_string('erroronimport', 'turningtech', $a);
                            break;
                    }
                } else {
                    // Grade saved correctly.
                    $saved ++;
                }
                $linecounter ++;
            }
            // Set up messages telling user about import.
            self::displayimportstatus($saved, $errors);
        } else {
            // No actions to parse.
            turningtech_set_message(get_string('importfilecontainednogrades', 'turningtech'));
        }
    }
    /**
     * display status messages about import
     * 
     * @param mixed $saved
     * @param mixed $errors
     * @return unknown_type
     */
    public static function displayimportstatus($saved = 0, $errors = array()) {
        turningtech_set_message(get_string('successfulimport', 'turningtech', $saved));
        if (count($errors)) {
            turningtech_set_message(get_string('importcouldnotcomplete', 'turningtech'), 'error');
            foreach ($errors as $error) {
                turningtech_set_message($error, 'error');
            }
        }
    }
    /**
     * reads the session file and compiles a list of operations
     * 
     * @param mixed $filedata
     * @return unknown_type
     */
    public static function parsesessionfile($filedata) {
        global $CFG;
        $actions = array ();
        // Read file.
        if ($rows = preg_split("/[\n\r]+/", $filedata)) {
            $count = count($rows);
            $i = 1;
            foreach ($rows as $row) {
                // Discard spaces and blank lines.
                $row = trim($row);
                if (empty($row)) {
                    continue;
                }
                // Split.
                $data = explode(',', $row);
                if (isset($data[0]) && isset($data[1]) && isset($data[2])) {
                    // Collect only:
                    // 1) If the columns/items count is correct
                    // 2) If the deviceid is valid
                    // 3) If the points earned or points possible are not valid.
                    if (count($data) == 3 && TurningTechTurningHelper::isdeviceidvalid($strdeviceid = $data[0]) &&
                                                     is_numeric($pointsearned = $data[1]) &&
                                                     is_numeric($pointspossible = $data[2])) {
                        // Line seems okay, process it.
                        $a = new stdClass();
                        $a->deviceid = $data[0];
                        $a->pointsearned = $data[1];
                        $a->pointspossible = $data[2];
                        $actions[] = $a;
                    }
                } else {
                    // Make sure this is not just a dangling end (false positive)...
                    if ($i != $count) {
                        // Line seems invalid, throw an error.
                        return false;
                    }
                }
                $i ++;
            }
        } else {
            return false;
        }
        return $actions;
    }
    /**
     * Remove Bom
     * @param String $str
     * @return str
     */
    public function removeBOM($str="") {
        if (substr($str, 0, 3) == pack("CCC", 0xef, 0xbb, 0xbf)) {
            $str=substr($str, 3);
        }
        return $str;
    }
    /**
     * populate the participant list
     * @param mixed $data
     * @return unknown_type
     */
    public function getdevicesxml($data) {
        try {
            $data = self::removeBOM($data);
            if (strpos($data, '<?xml') !== 0) {
                throw new moodle_exception('invalidxmlresponse');
            }
            $data = str_replace('xmlns=', 'ns=', $data);
            $xml = new SimpleXMLElement($data);
        } catch (Exception $e) {
            // An error occured while trying to parse the XML, let's just return nothing. SimpleXML does not
            // return a more specific Exception, that's why the global Exception class is caught here.
            return false;
        }
        $questionsarray = array(
        'multichoice',
        'shortanswer',
        'numeric',
        'matching'
        );
        $totalpoints = 0;
        $devices = array();
        $devicescore = array();
        $result = $xml->xpath('responders');
        foreach ($result[0] as $res) {
            $devices[strval($res->deviceid)] = 0;
        }
        $qsresult = $xml->xpath('questionlist/questions');
        foreach ($qsresult[0] as $qsres => $value) {
            if (in_array($qsres, $questionsarray)) {
                if ($qsres == 'matching') {
                    $multiplier =  count($value->matches[0]);
                    $correctvalue = (int)strval($value->correctvalue);
                    $correctvalue = $correctvalue*$multiplier;
                    $totalpoints+=$correctvalue;
                    foreach ($value->responses->response as $score) {
                        $k = strval($score->responsestring);
                        foreach ($value->answers as $key => $v) {
                            if ($v->valuetype == 1 || $v->valuetype == 0) {
                                $j = $key;
                            }
                        }
                    }
                } else if ($qsres == 'multichoice') {
                    if ($value->demographic == true) {
                        // Do nothing.
                    } else {
                        $correctvalue = (int)strval($value->correctvalue);
                        $totalpoints+=$correctvalue;
                        foreach ($value->responses->response as $score) {
                            $k = strval($score->responsestring)-1;
                            foreach ($value->answers as $key => $v) {
                                if ($v->valuetype == 1 || $v->valuetype == 0) {
                                    $j = $key;
                                }
                            }
                            if ($j==$k) {
                                $devices[strval($score->deviceid)] += $correctvalue;
                            }
                        }
                    }
                } else if ($qsres == 'shortanswer') {
                    $correctvalue = (int)strval($value->correctvalue);
                    $totalpoints+=$correctvalue;
                    foreach ($value->responses->response as $score) {
                        $k = strval($score->responsestring);
                        $j = $value->keywords->keyword;
                        if ($j==$k) {
                            $devices[strval($score->deviceid)] += $correctvalue;
                        }
                    }
                } else {
                    foreach ($value->responses->response as $score) {
                        $k = strval($score->responsestring)-1;
                        foreach ($value->answers as $key => $v) {
                            if ($v->valuetype == 1 || $v->valuetype == 0) {
                                    $j = $key;
                            }
                        }
                        if ($j==$k) {
                                $devices[strval($score->deviceid)] += $correctvalue;
                        }
                    }
                    $correctvalue = (int)strval($value->correctvalue);
                    $totalpoints+=$correctvalue;
                }
            }
        }
        return $xml;
    }

    /**
     * populate the participant list
     * 
     * @return unknown_type
     */
    public function loadparticipantlist() {
        if (empty($this->activecourse)) {
            throw new Exception(get_string('nocourseselectedloadingparticipants', 'turningtech'));
        }
        if ($roster = TurningTechMoodleHelper::getclassrosterxml($this->activecourse, false, false, "dall.created")) {
            foreach ($roster as $student) {
                    $dto = array ();
                    $dto['moodleid'] = $student->id;
                    $dto['devices'] = "{$student->rcard},{$student->rware}";
                    $dto['firstname'] = $student->firstname;
                    $dto['lastname'] = $student->lastname;
                    $dto['userid'] = $student->username;
                    $this->participants[] = $dto;
            }
        }
        /*
         * else { throw new Exception(get_string('couldnotgetroster','turningtech', $this->activecourse->fullname)); }
         */
    }
    /**
     * translate this session into an XML file
     * 
     * @return unknown_type
     */
    public function exporttoxml() {
        if (empty($this->dom)) {
            $this->dom = new DOMDocument("1.0");
            $participantlist = $this->participantstoxml();
            $this->dom->appendChild($participantlist);
        }
        return $this->dom;
    }
    /**
     * translate the participant list to XML and append it to the DOM
     * 
     * @return unknown_type
     */
    private function participantstoxml() {
        // Create main participantlist element.
        global $DB;
        if ($courselms = $DB->get_record('course', array ('id' => 1))) {
            $lmssource = $courselms->fullname;
        }
        $guid = strtoupper(md5(uniqid()));
        $root = $this->dom->createElement('participantlist');
        $dttim = date("m/j/Y  g:i:s a");
        $coursename = $this->getactivecourse();
        $topitems = $this->dom->createDocumentFragment();
        $headerxml = <<<EOF
    <ttxmlversion>2012</ttxmlversion>
    <guid>$guid</guid>
    <lmssource>$lmssource</lmssource>
    <name>$coursename->fullname</name>
    <created>$dttim</created>
    <modified>$dttim</modified>
EOF;
        $topitems->appendXML($headerxml);
        $root->appendChild($topitems);
        // Create participantlist header element
        // as this is static text, let's do it the easy way.
        $headeritems = $this->dom->createDocumentFragment();
        $headerxml = <<<EOF
<headers>
<deviceid />
<firstname />
<lastname />
<userid />
</headers>
EOF;
        $headeritems->appendXML($headerxml);
        $root->appendChild($headeritems);
        $plist = $this->dom->createElement('participants');
        foreach ($this->participants as $id => $participant) {
            $plist->appendChild($this->generateparticipantdom($participant));
        }
        $root->appendChild($plist);
        return $root;
    }
    /**
     * generates XML for an individual course participant
     * 
     * @param mixed $participant
     * @return unknown_type
     */
    private function generateparticipantdom($participant) {
        $participant = (array)$participant;
        $pdom = $this->dom->createElement('participant');
        foreach ($participant as $key => $value) {
            $tag = false;
            $status = true;
            // Tag name doesn't necessarily match field name.
            switch ($key) {
                case 'moodleid' :
                    $tag = 'participantid';
                    break;
                case 'firstname' :
                case 'lastname' :
                case 'userid' :
                case 'devices' :
                    $tag = $key;
                    break;
                default :
                    // Ignore any other fields (deviceid has already been used).
                    continue;
            }
            if ($tag != 'devices') {
                $tag_node = $this->dom->createElement($tag);
                /*
                 * if ($key == 'moodleid') { $tag_name = $this->dom->createAttribute('name');
                 * $tag_name->appendChild($this->dom->createtextnode('Moodle User Id')); $tag_node->appendChild($tag_name); } if
                 * ($key == 'login') { $tag_name = $this->dom->createAttribute('name');
                 * $tag_name->appendChild($this->dom->createtextnode('Moodle Login Id')); $tag_node->appendChild($tag_name); }
                 */
                $tag_node->appendChild($this->dom->createTextNode($value));
                $pdom->appendChild($tag_node);
            } else if ($tag == 'devices') {
                $tag_node1 = $this->dom->createElement($tag);
                $devval = explode(",", $value);
                if ($devval[0] != '') {
                    $tag_node2 = $this->dom->createElement('device');
                    $tag_node2->appendChild($this->dom->createTextNode($devval[0]));
                    $tag_node1->appendChild($tag_node2);
                }
                if ($devval[1] != '') {
                    $tag_node3 = $this->dom->createElement('device');
                    $tag_node3->appendChild($this->dom->createTextNode($devval[1]));
                    $tag_node1->appendChild($tag_node3);
                }
                $pdom->appendChild($tag_node1);
            }
        }
        return $pdom;
    }
}