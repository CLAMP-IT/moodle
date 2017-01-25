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
 * the provision course logic for panopto
 *
 * @package block_panopto
 * @copyright  Panopto 2009 - 2016 /With contributions from Spenser Jones (sjones@ambrose.edu)
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->libdir . '/formslib.php');
require_once('lib/panopto_data.php');

global $courses;

// Populate list of servernames to select from.
$aserverarray = array();
$appkeyarray = array();
if (isset($_SESSION['numservers'])) {
    $maxval = $_SESSION['numservers'];
} else {
    $maxval = 1;
}
for ($x = 0; $x < $maxval; $x++) {

    // Generate strings corresponding to potential servernames in the config.
    $thisservername = get_config('block_panopto', 'server_name' . ($x + 1));
    $thisappkey = get_config('block_panopto', 'application_key' . ($x + 1));

    $hasservername = !is_null_or_empty_string($thisservername);
    if ($hasservername && !is_null_or_empty_string($thisappkey)) {
        $aserverarray[$x] = $thisservername;
        $appkeyarray[$x] = $thisappkey;
    }
}

/**
 * Create form for server selection.
 *
 * @copyright  Panopto 2009 - 2015
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class panopto_provision_form extends moodleform {

    /**
     * @var string $title
     */
    protected $title = '';

    /**
     * @var string $description
     */
    protected $description = '';

    /**
     * Defines a panopto provision form
     */
    public function definition() {

        global $DB;
        global $aserverarray;

        $mform = & $this->_form;
        $selectquery = 'id <> 1';
        $coursesraw = $DB->get_records_select('course', $selectquery, null, 'id, shortname, fullname');
        $courses = array();
        if ($coursesraw) {
            foreach ($coursesraw as $course) {
                $courses[$course->id] = $course->shortname . ': ' . $course->fullname;
            }
        }
        asort($courses);

        $serverselect = $mform->addElement('select', 'servers', get_string('select_server', 'block_panopto'), $aserverarray);
        $mform->addHelpButton('servers', 'select_server', 'block_panopto');

        $select = $mform->addElement('select', 'courses', get_string('provisioncourseselect', 'block_panopto'), $courses);
        $select->setMultiple(true);
        $select->setSize(32);
        $mform->addHelpButton('courses', 'provisioncourseselect', 'block_panopto');

        $this->add_action_buttons(true, get_string('provision', 'block_panopto'));
    }

}

require_login();


// Set course context if we are in a course, otherwise use system context.
$courseidparam = optional_param('course_id', 0, PARAM_INT);
if ($courseidparam != 0) {
    $context = context_course::instance($courseidparam, MUST_EXIST);
} else {
    $context = context_system::instance();
}

$PAGE->set_context($context);

$returnurl = optional_param('return_url', $CFG->wwwroot . '/admin/settings.php?section=blocksettingpanopto', PARAM_LOCALURL);

$urlparams['return_url'] = $returnurl;

$PAGE->set_url('/blocks/panopto/provision_course.php', $urlparams);
$PAGE->set_pagelayout('base');

$mform = new panopto_provision_form($PAGE->url);

if ($mform->is_cancelled()) {
    redirect(new moodle_url($returnurl));
} else {
    $provisiontitle = get_string('provision_courses', 'block_panopto');
    $PAGE->set_pagelayout('base');
    $PAGE->set_title($provisiontitle);
    $PAGE->set_heading($provisiontitle);

    if ($courseidparam != 0) {
        // Course context.
        require_capability('block/panopto:provision_course', $context);

        $courses = array($courseidparam);
        $editcourseurl = new moodle_url($returnurl);
        $PAGE->navbar->add(get_string('pluginname', 'block_panopto'), $editcourseurl);
    } else {
        // System context.
        require_capability('block/panopto:provision_multiple', $context);

        $data = $mform->get_data();
        if ($data) {
            $courses = $data->courses;
            $selectedserver = $aserverarray[$data->servers];
            $selectedkey = $appkeyarray[$data->servers];

            // Are these old? Need input on if we shoud store these in another way.
            $CFG->servername = $selectedserver;
            $CFG->appkey = $selectedkey;
        }

        $manageblocks = new moodle_url('/admin/blocks.php');
        $panoptosettings = new moodle_url('/admin/settings.php?section=blocksettingpanopto');
        $PAGE->navbar->add(get_string('blocks'), $manageblocks);
        $PAGE->navbar->add(get_string('pluginname', 'block_panopto'), $panoptosettings);
    }

    $PAGE->navbar->add($provisiontitle, new moodle_url($PAGE->url));

    echo $OUTPUT->header();

    if ($courses) {
        foreach ($courses as $courseid) {
            if (empty($courseid)) {
                continue;
            }
            // Set the current Moodle course to retrieve info for / provision.
            $panoptodata = new panopto_data($courseid);

            // If an application key and server name are pre-set (happens when provisioning from multi-select page) use those,
            // otherwise retrieve values from the db.
            if (isset($selectedserver)) {

                // If we are not using the same server remove the folder ID reference.
                // NOTE: A moodle course can only point to one panopto server at a time.
                // So reprovisioning to a different server erases the folder mapping to the original server.
                if ($panoptodata->servername !== $selectedserver) {
                    $panoptodata->sessiongroupid = null;
                }
                $panoptodata->servername = $selectedserver;
            }

            if (isset($selectedkey)) {
                $panoptodata->applicationkey = $selectedkey;
            }

            $provisioningdata = $panoptodata->get_provisioning_info();
            $provisioneddata = $panoptodata->provision_course($provisioningdata);
            include('views/provisioned_course.html.php');
        }
        echo "<a href='$returnurl'>" . get_string('back_to_config', 'block_panopto') . '</a>';
    } else {
        $mform->display();
    }

    echo $OUTPUT->footer();
}

/**
 * Returns true if a string is null or empty, false otherwise
 *
 * @param string $name the string being checked for null or empty
 */
function is_null_or_empty_string($name) {
    return (!isset($name) || trim($name) === '');
}

/* End of file provision_course.php */
