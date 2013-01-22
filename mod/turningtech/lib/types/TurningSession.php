<?php
/**
 * class for manipulating TurningPoint session data
 * @author jacob
 *
 */
class TurningSession {
    // current course (if any)
    private $activeCourse;
    // list of students in session
    private $participants = array();
    // XML DOM for this session
    private $dom;

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
    public function setActiveCourse($course) {
        $this->activeCourse = $course;
    }

    /**
     * reads grade data from a session file
     * @param $assignmentTitle
     * @param $filename
     * @param $override
     * @return unknown_type
     */
    public function importSession($assignmentTitle, $filedata, $override = FALSE) {
        $actions = self::parseSessionFile($filedata);
        if ($actions === FALSE) {
            throw new Exception(get_string('couldnotparsesessionfile', 'turningtech'));
        }

        if (count($actions)) {
            // check if we need to create the gradebook item
            $grade_item = TurningTechMoodleHelper::getGradebookItemByCourseAndTitle($this->activeCourse, $assignmentTitle);
            if (!$grade_item) {
                // must create gradebook item.  Get points possible from one of the actions
                $points_possible = $actions[0]->pointsPossible ? $actions[0]->pointsPossible : 0;
                TurningTechMoodleHelper::createGradebookItem($this->activeCourse, $assignmentTitle, $points_possible);
            }

            $mode           = ($override ? TURNINGTECH_SAVE_ALLOW_OVERRIDE : TURNINGTECH_SAVE_NO_OVERRIDE);
            $service        = new TurningTechIntegrationServiceProvider();
            // counts number of correctly saved items
            $saved          = 0;
            // records errors
            $errors         = array();
            // pre-load some strings for comparison
            $escrow         = get_string('gradesavedinescrow', 'turningtech');
            $cannotoverride = get_string('cannotoverridegrade', 'turningtech');
            // keep track of which line of the session file we're on
            $linecounter    = 1;
            // iterate through actions and save them
            foreach ($actions as $action) {
                $action->itemTitle = $assignmentTitle;
                if ($error = $service->saveGradebookItem($this->activeCourse, $action, $mode)) {
                    switch ($error->errorMessage) {
                        case $escrow:
                            // grade saved in escrow; no error
                            $saved++;
                            break;
                        /*
                        case $cannotoverride:
                        // failed attempt to override grade
                        $overrides++;
                        break;
                        */
                        default:
                            $a          = new stdClass();
                            $a->line    = $linecounter;
                            $a->message = $error->errorMessage;
                            $errors[]   = get_string('erroronimport', 'turningtech', $a);
                            break;
                    }
                } else {
                    // grade saved correctly
                    $saved++;
                }
                $linecounter++;
            }
            // set up messages telling user about import
            self::displayImportStatus($saved, $errors);
        } else {
            // no actions to parse
            turningtech_set_message(get_string('importfilecontainednogrades', 'turningtech'));
        }
    }

    /**
     * display status messages about import
     * @param $saved
     * @param $overrides
     * @param $errors
     * @return unknown_type
     */
    public static function displayImportStatus($saved = 0, $errors = array()) {
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
     * @param $filename
     * @return unknown_type
     */
    public static function parseSessionFile($filedata) {
        global $CFG;
        $actions = array();
        //read file
        if ($rows = preg_split("/[\n\r]+/", $filedata)) {
            $count = count($rows);
            $i     = 1;
            foreach ($rows as $row) {
                //discard spaces and blank lines
                $row = trim($row);
                if (empty($row)) {
                    continue;
                }
                // split
                $data = explode(',', $row);
                if (isset($data[0]) && isset($data[1]) && isset($data[2])) {
                    // Collect only:
                    // 1) If the columns/items count is correct
                    // 2) If the deviceid is valid
                    // 3) If the points earned or points possible are not valid
                    if (count($data) == 3 && TurningTechTurningHelper::isDeviceIdValid($strDeviceId = $data[0]) && is_numeric($pointsEarned = $data[1]) && is_numeric($pointsPossible = $data[2])) {
                        // line seems okay, process it.
                        $a                 = new stdClass();
                        $a->deviceId       = $data[0];
                        $a->pointsEarned   = $data[1];
                        $a->pointsPossible = $data[2];
                        $actions[]         = $a;
                    }
                } else {
                    //make sure this is not just a dangling end (false positive)...
                    if ($i != $count) {
                        //line seems invalid, throw an error.
                        return FALSE;
                    }
                }
                $i++;
            }
        } else {
            return FALSE;
        }
        return $actions;
    }


    /**
     * populate the participant list
     * @return unknown_type
     */
    public function loadParticipantList() {
        if (empty($this->activeCourse)) {
            throw new Exception(get_string('nocourseselectedloadingparticipants', 'turningtech'));
        }

        if ($roster = TurningTechMoodleHelper::getClassRoster($this->activeCourse, FALSE, FALSE, "dall.created")) {
            foreach ($roster as $student) {
                if ($student->deviceid) {
                    $dto                                    = array();
                    $dto['moodleid']                        = $student->id;
                    $dto['deviceid']                        = $student->deviceid;
                    $dto['login']                           = $student->username;
                    $dto['firstname']                       = $student->firstname;
                    $dto['lastname']                        = $student->lastname;
                    $dto['email']                           = $student->email;
                    $this->participants[$student->deviceid] = $dto;
                }
            }
        }
        /*
        else {
        throw new Exception(get_string('couldnotgetroster','turningtech', $this->activeCourse->fullname));
        }
        */
    }

    /**
     * translate this session into an XML file
     * @return unknown_type
     */
    public function exportToXml() {
        if (empty($this->dom)) {
            $this->dom = new DOMDocument("1.0");

            //if(!empty($this->participants)) {
            $participantList =& $this->participantsToXml();
            $this->dom->appendChild($participantList);
            //}
        }

        return $this->dom;
    }

    /**
     * translate the participant list to XML and append it to the DOM
     * @return unknown_type
     */
    private function participantsToXml() {
        // create main participantlist element
        $root         = $this->dom->createElement('participantlist');
        $plistversion = $this->dom->createAttribute('plistversion');
        $plistversion->appendChild($this->dom->createTextNode('2008'));
        $root->appendChild($plistversion);

        // create participantlist header element
        // as this is static text, let's do it the easy way
        $headeritems = $this->dom->createDocumentFragment();
        $headerXML   = <<<EOF
<headeritems count="6">
  <item type="field">Device ID</item>
  <item type="field">Moodle User Id</item>
  <item type="field">Moodle Login Id</item>
  <item type="field">First Name</item>
  <item type="field">Last Name</item>
  <item type="field">Email</item>
</headeritems>
EOF;

        $headeritems->appendXML($headerXML);
        $root->appendChild($headeritems);

        $plist      = $this->dom->createElement('participants');
        $plistcount = $this->dom->createAttribute('count');
        $plistcount->appendChild($this->dom->createTextNode(count($this->participants)));
        $plist->appendChild($plistcount);
        foreach ($this->participants as $id => $participant) {
            $plist->appendChild($this->generateParticipantDOM($participant));
        }
        $root->appendChild($plist);
        return $root;
    }

    /**
     * generates XML for an individual course participant
     * @param $participant
     * @return unknown_type
     */
    private function generateParticipantDOM($participant) {
        $participant = (array) $participant;
        $pdom        = $this->dom->createElement('participant');
        $pdomid      = $this->dom->createAttribute('id');
        $pdomid->appendChild($this->dom->createTextNode($participant['deviceid']));
        $pdom->appendChild($pdomid);

        // iterate through all fields creating necessary DOM elements
        foreach ($participant as $key => $value) {
            $tag = FALSE;
            // tag name doesn't necessarily match field name
            switch ($key) {
                case 'moodleid':
                    $tag = 'custom';
                    break;
                case 'login':
                    $tag = 'custom';
                    break;
                case 'firstname':
                case 'lastname':
                case 'email':
                    $tag = $key;
                    break;
                default:
                    // ignore any other fields (deviceid has already been used)
                    continue;
            }
            if ($tag) {
                $tag_node = $this->dom->createElement($tag);
                if ($key == 'moodleid') {
                    $tag_name = $this->dom->createAttribute('name');
                    $tag_name->appendChild($this->dom->createTextNode('Moodle User Id'));
                    $tag_node->appendChild($tag_name);
                }
                if ($key == 'login') {
                    $tag_name = $this->dom->createAttribute('name');
                    $tag_name->appendChild($this->dom->createTextNode('Moodle Login Id'));
                    $tag_node->appendChild($tag_name);
                }
                $tag_node->appendChild($this->dom->createTextNode($value));
                $pdom->appendChild($tag_node);
            }

        }

        return $pdom;
    }
}
?>