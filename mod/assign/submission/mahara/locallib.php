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
 * This file contains the definition for the library class for Mahara submission plugin
 *
 * This class provides all the functionality for the new assign module.
 *
 * @package    assignsubmission_mahara
 * @copyright  2012 Lancaster University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * library class for Mahara submission plugin extending submission plugin base class
 *
 * @package    assignsubmission_mahara
 * @copyright  2012 Lancaster University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class assign_submission_mahara extends assign_submission_plugin {

    // We've selected the page/collection, but we haven't locked it or issued a special access token
    const STATUS_SELECTED = 'selected';

    // We've locked the page in Mahara and issued an access token
    const STATUS_SUBMITTED = 'submitted';

    // We locked and then unlocked the page in Mahara, which means we probably still have a valid
    // access token for it
    const STATUS_RELEASED = 'released';

    /**
     * Get the name of the Mahara submission plugin
     *
     * @return string
     */
    public function get_name() {
        return get_string('mahara', 'assignsubmission_mahara');
    }

   /**
    * Get Mahara submission information from the database
    *
    * @global stdClass $DB
    * @param  int $submissionid
    * @return mixed
    */
    private function get_mahara_submission($submissionid) {
        global $DB;

        return $DB->get_record('assignsubmission_mahara', array('submission'=>$submissionid));
    }

    /**
     * Get the settings form for Mahara submission plugin
     *
     * @global stdClass $CFG
     * @global stdClass $DB
     * @param MoodleQuickForm $mform The form to add elements to
     * @return void
     */
    public function get_settings(MoodleQuickForm $mform) {
        global $CFG;
        require_once($CFG->dirroot . '/mod/assign/submission/mahara/lib.php');

        if ($hosts = assignsubmission_mahara_sitelist()) {

            $hostid = $this->get_config('mnethostid');
            if ($hostid === false) {
                // No setting for this instance, so use the sitewide default
                $hostid = get_config('assignsubmission_mahara', 'host');
            }

            $locked = $this->get_config('lock');
            if ($locked === false) {
                // No setting for this instance, so use the sitewide default
                $locked = get_config('assignsubmission_mahara', 'lock');
            }

            // Menu to select which MNet host
            $mform->addElement('select', 'assignsubmission_mahara_mnethostid', get_string('site', 'assignsubmission_mahara'), $hosts);
            $mform->setDefault('assignsubmission_mahara_mnethostid', $hostid);
            $mform->disabledIf('assignsubmission_mahara_mnethostid', 'assignsubmission_mahara_enabled', 'notchecked');

            // Menu to select whether to lock Mahara pages or not
            $mform->addElement('selectyesno', 'assignsubmission_mahara_lockpages', new lang_string('lockpages', 'assignsubmission_mahara'));
            $mform->setDefault('assignsubmission_mahara_lockpages', $locked);
            $mform->addHelpButton('assignsubmission_mahara_lockpages', 'lockpages', 'assignsubmission_mahara');
            $mform->disabledIf('assignsubmission_mahara_lockpages', 'assignsubmission_mahara_enabled', 'notchecked');
        } else {
            // No hosts found.
            $mform->addElement('static', 'assignsubmission_mahara_mnethostid', get_string('site', 'assignsubmission_mahara'), get_string('nomaharahostsfound', 'assignsubmission_mahara'));
            $mform->updateElementAttr('assignsubmission_mahara_enabled', array('disabled' => true));
        }
        $mform->addHelpButton('assignsubmission_mahara_mnethostid', 'site', 'assignsubmission_mahara');
    }

    /**
     * Save the settings for Mahara plugin
     *
     * @param stdClass $data
     * @return bool
     */
    public function save_settings(stdClass $data) {
        global $CFG;
        require_once($CFG->dirroot . '/mod/assign/submission/mahara/lib.php');

        $hostid = $data->assignsubmission_mahara_mnethostid;
        if ($hostid && !array_key_exists($hostid, assignsubmission_mahara_sitelist())) {
            $this->set_error(get_string('errorinvalidhost', 'assignsubmission_mahara'));
            return false;
        }

        $this->set_config('mnethostid', $hostid);
        $this->set_config('lock', $data->assignsubmission_mahara_lockpages);

        return true;
    }

    /**
     * Add elements to user submission form
     *
     * @param mixed $submission stdClass|null
     * @param MoodleQuickForm $mform
     * @param stdClass $data
     * @param int $userid
     * @return bool
     */
    public function get_form_elements_for_user($submission, MoodleQuickForm $mform, stdClass $data, $userid) {
        global $DB, $PAGE;

        $PAGE->requires->js('/mod/assign/submission/mahara/js/popup.js');
        $PAGE->requires->js('/mod/assign/submission/mahara/js/filter.js');
        // Getting submission.
        if ($submission) {
            $maharasubmission = $this->get_mahara_submission($submission->id);
        }
        if (!empty($maharasubmission)) {
            $selectedid = $maharasubmission->viewid;
            $selectediscollection = $maharasubmission->iscollection;
        }
        else {
            $selectedid = 0;
            $selectediscollection = null;
        }

        // Getting views (pages) user have in linked site.
        $views = $this->mnet_get_views();
        if (!$views) {
            $views = array(
                    'data' => array(),
                    'collections' => array('data' => array()),
                    'ids' => array(),
            );
        }

	    // Filter out collection views, special views, and already-submitted views (except the current one)
	    foreach ($views['data'] as $i => $view) {
	        if (
	                $view['collid']
	                || $view['type'] != 'portfolio'
	                || (
	                        $view['submittedtime']
	                        && !($view['id'] == $selectedid && $selectediscollection == false)
	                )
	        ) {
	            unset($views['ids'][$i]);
	            unset($views['data'][$i]);
	            $views['count']--;
	        }
	    }
	    // Filter out empty or submitted collections
	    foreach ($views['collections']['data'] as $i => $coll) {
	        if (
	                (
	                        array_key_exists('numviews', $coll)
	                        && $coll['numviews'] == 0
	                ) || (
	                        $coll['submittedtime']
	                        && !($coll['id'] == $selectedid && $selectediscollection == true)
	                )
	        ) {
	            unset($views['collections']['data'][$i]);
	            $views['collections']['count']--;
	        }
	    }
	    $viewids = $views['ids'];

        // Prepare the header.
        $remotehost = $DB->get_record('mnet_host', array('id'=>$this->get_config('mnethostid')));
        $url = new moodle_url('/auth/mnet/jump.php', array('hostid' => $remotehost->id));
        $remotehost->jumpurl = $url->out();

        // See if any of views are already in use, we will remove them from select.
        if (count($viewids) || count($views['collections']['data'])) {

            $mform->addElement('static', '', $remotehost->name, get_string('selectmaharaview', 'assignsubmission_mahara', $remotehost));

            // Add "none selected" option
            $mform->addElement('radio', 'viewid', '', get_string('noneselected', 'assignsubmission_mahara'), 'none');
            $mform->setType('viewid', PARAM_ALPHANUM);
            $mform->setDefault('viewid', 'none');

            $mform->addElement('text', 'search', get_string('search'));
            $mform->setType('search', PARAM_RAW);
            $mform->addElement('html', '<hr/><br/>');

            if (count($views['data'])) {
                $mform->addElement('static', 'view_by',
                    get_string('viewsby', 'assignsubmission_mahara', $views['displayname'])
                );
                foreach ($views['data'] as $view) {
                    $viewurl = $this->get_view_url($view['url']);
                    $anchor = $this->get_preview_url($view['title'], $viewurl, strip_tags($view['description']));
                    $mform->addElement('radio', 'viewid', '', $anchor, 'v' . $view['id']);
                }
            }
            if (count($views['collections']['data'])) {
                $mform->addElement('static', 'collection_by',
                    get_string('collectionsby', 'assignsubmission_mahara', $views['displayname'])
                );
                foreach ($views['collections']['data'] as $coll) {
                    $collurl = $this->get_view_url($coll['url']);
                    $anchor = $this->get_preview_url($coll['name'], $collurl, strip_tags($coll['description']));
                    $mform->addElement('radio', 'viewid', '', $anchor, 'c' . $coll['id']);
                }
            }
            if (!empty($maharasubmission)) {
                if ($maharasubmission->iscollection) {
                    $prefix = 'c';
                } else {
                    $prefix = 'v';
                }
                $mform->setDefault('viewid', $prefix . $maharasubmission->viewid);
            }

            return true;
        }
        else {
            $mform->addElement(
                    'static',
                    '',
                    $remotehost->name,
                    get_string('noviewscreated', 'assignsubmission_mahara', $remotehost)
            );
            $mform->addElement('hidden', 'viewid', 'none');
            $mform->setType('viewid', PARAM_ALPHANUM);
            return true;
        }

    }

    /**
     * Retrieve user views from Mahara portfolio.
     *
     * @global stdClass $USER
     * @param string $query Search query
     * @return mixed
     */
    public function mnet_get_views($query = '') {
        global $USER;
        return $this->mnet_send_request('get_views_for_user', array($USER->username, $query));
    }

    /**
     * Submit view or collection for assessment in Mahara. This marks the view/collection
     * as "submitted", creates an access token, and locks the view/collection from editing
     * or further submissions in Mahara.
     *
     * @global stdClass $USER
     * @param stdClass $submission The submission record (used for verification)
     * @param int $viewid Id of the view or collection to submit
     * @param boolean $iscollection True if it's a collection, False if not
     * @param $viewownermoodleid ID of the view ower's Moodle user record
     * @return mixed
     */
    public function mnet_submit_view($submission, $viewid, $iscollection, $viewownermoodleid = null) {
        global $USER, $DB;

        // Verify that it's not already submitted to another Mahara assignment in this Moodle site.
        // We can't do this on the Mahara side, because Mahara only knows the remote site's wwwroot.
        if (
                $DB->record_exists_select(
                        'assignsubmission_mahara',
                        'viewid = ? AND iscollection = ? AND viewstatus = ? AND submission != ?',
                        array(
                                $viewid,
                                ($iscollection ? 1 : 0),
                                self::STATUS_SUBMITTED,
                                $submission->id
                        )
                )
        ) {
            throw new moodle_exception('errorvieworcollectionalreadysubmitted', 'assignsubmission_mahara');
        }

        if (!$viewownermoodleid) {
            $username = $USER->username;
        }
        else {
            $username = $DB->get_field('user', 'username', array('id'=>$viewownermoodleid));
        }
        return $this->mnet_send_request('submit_view_for_assessment', array($username, $viewid, $iscollection));
    }

    /**
     * Release submitted view for assessment.
     *
     * @global stdClass $USER
     * @param int $viewid View or Collection ID
     * @param array $viewoutcomes Outcomes data
     * @param boolean $iscollection Whether the $viewid is a view or a collection
     * @return mixed
     */
    public function mnet_release_submitted_view($viewid, $viewoutcomes, $iscollection = false) {
        global $USER;
        return $this->mnet_send_request('release_submitted_view', array($viewid, $viewoutcomes, $USER->username, $iscollection));
    }

    /**
     * Send Mnet request to Mahara portfolio.
     *
     * @global stdClass $CFG
     * @param string $methodname name of remote method to call
     * @param array $parameters list of method parameters
     * @return mixed $responsedata Mnet response
     */
    private function mnet_send_request($methodname, $parameters) {
        global $CFG;

        $error = false;
        $responsedata = false;
        if (!is_enabled_auth('mnet')) {
            $error = get_string('authmnetdisabled', 'mnet');
        } else if (!has_capability('moodle/site:mnetlogintoremote', context_system::instance())) {
            $error = get_string('notpermittedtojump', 'mnet');
        } else {
            // Set up the RPC request.
            require_once $CFG->dirroot . '/mnet/xmlrpc/client.php';
            require_once $CFG->dirroot . '/mnet/peer.php';
            $mnetpeer = new mnet_peer();
            $mnetpeer->set_id($this->get_config('mnethostid'));
            $mnetrequest = new mnet_xmlrpc_client();
            $mnetrequest->set_method('mod/mahara/rpclib.php/' . $methodname);
            foreach ($parameters as $parameter) {
                $mnetrequest->add_param($parameter);
            }

            if ($mnetrequest->send($mnetpeer) === true) {
                $responsedata = $mnetrequest->response;
            } else {
                $error = "RPC mod/mahara/rpclib.php/" . $methodname . ":<br/>";
                foreach ($mnetrequest->error as $errormessage) {
                    list($code, $errormessage) = array_map('trim',explode(':', $errormessage, 2));
                    $error .= "ERROR $code:<br/>$errormessage<br/>";
                }
            }
        }
        if ($error) {
            $this->set_error($error);
        }
        return $responsedata;
    }

     /**
      * Save submission data to the database
      *
      * @global stdClass $DB
      * @param stdClass $submission
      * @param stdClass $data
      * @return bool
      */
     public function save(stdClass $submission, stdClass $data) {
        global $USER, $DB;

        // Because the drop-down menu contains collections & views, we make the id
        // start with "v" or "c" to indicate the type, e.g. v30, c100
        if ($data->viewid == 'none') {
            $data->viewid = null;
        } else {
            $iscollection = ($data->viewid[0] == 'c');
            $data->viewid = substr($data->viewid, 1);
        }

        $maharasubmission = $this->get_mahara_submission($submission->id);
        if ($submission->status === ASSIGN_SUBMISSION_STATUS_DRAFT) {
            // Draft. All we need to do is just save or update submitted view data.
            if ($data->viewid === null) {
                // They selected "(nothing selected)", so remove their Mahara selection
                return $DB->delete_records(
                        'assignsubmission_mahara',
                        array('submission' => $submission->id)
                );
            }

            if (!$views = $this->mnet_get_views()) {
                // Wrap recorded error in language string and return false.
                $this->set_error(get_string('errormnetrequest', 'assignsubmission_mahara', $this->get_error()));
                return false;
            }

            if ($iscollection) {
                $foundcoll = false;
                if (!is_array($views['collections']['data'])) {
                    return false;
                }
                foreach ($views['collections']['data'] as $coll) {
                    if ($coll['id'] == $data->viewid) {
                        $foundcoll = true;
                        $url = $coll['url'];
                        $title = clean_text($coll['name']);
                        break;
                    }
                }
                // The submitted collection id isn't one of the allowed options for this user
                if (!$foundcoll) {
                    return false;
                }
            } else {
                $keys = array_flip($views['ids']);
                // The submitted view id isn't one of the allowed options for this user
                if (!array_key_exists($data->viewid, $keys)) {
                    return false;
                }
                $viewdata = $views['data'][$keys[$data->viewid]];
                $url = $viewdata['url'];
                $title = clean_text($viewdata['title']);
            }

            if ($maharasubmission) {
                $maharasubmission->viewid = $data->viewid;
                $maharasubmission->viewurl = $url;
                $maharasubmission->viewtitle = $title;
                $maharasubmission->iscollection = (int) $iscollection;
                $maharasubmission->viewstatus = self::STATUS_SELECTED;
                return $DB->update_record('assignsubmission_mahara', $maharasubmission);
            } else {
                $maharasubmission = new stdClass();
                $maharasubmission->viewid = $data->viewid;
                $maharasubmission->viewurl = $url;
                $maharasubmission->viewtitle = $title;
                $maharasubmission->iscollection = (int) $iscollection;
                $maharasubmission->viewstatus = self::STATUS_SELECTED;

                $maharasubmission->submission = $submission->id;
                $maharasubmission->assignment = $this->assignment->get_instance()->id;
                return $DB->insert_record('assignsubmission_mahara', $maharasubmission) > 0;
            }
        } else {
            // This is not the draft, but the actual submission. Process it properly.

            // If viewid is null, it means they selected no page.
            if ($data->viewid === null) {
                if ($maharasubmission) {
                    // Unlock the previously selected page
                    if ($maharasubmission->viewstatus == self::STATUS_SUBMITTED) {
                        $response = $this->mnet_release_submitted_view($maharasubmission->viewid, array(), $maharasubmission->iscollection);
                        if ($response === false) {
                            throw new moodle_exception('errormnetrequest', 'assignsubmission_mahara', '', $this->get_error());
                        }
                    }
                    // Delete the record of the previously selected page from our submission, and exit
                    return $DB->delete_records('assignsubmission_mahara', array('submission' => $submission->id));
                } else {
                    // No previously selected page to clear
                    return true;
                }
            }

            // Lock submission on mahara side.
            if (!$response = $this->mnet_submit_view($submission, $data->viewid, $iscollection)) {
                throw new moodle_exception('errormnetrequest', 'assignsubmission_mahara', '', $this->get_error());
            }

            // If we're not locking user pages, then immediately release the page. This will leave it unlocked,
            // but leave the access code in place.
            // TODO: Replace this hack with something more robust. It's an oversight and a security hole, that the
            // access code remains in place in Mahara when you release the page via XML-RPC.
            if (!$this->get_config('lock')) {
                $this->mnet_release_submitted_view($data->viewid, array(), $iscollection);
                $status = self::STATUS_RELEASED;
            } else {
                $status = self::STATUS_SUBMITTED;
            }


            $params = array(
                'context' => context_module::instance($this->assignment->get_course_module()->id),
                'courseid' => $this->assignment->get_course()->id,
                'objectid' => $submission->id,
                'other' => array(
                    'pathnamehashes' => array(),
                    'content' => ''
                )
            );
            if (!empty($submission->userid) && ($submission->userid != $USER->id)) {
                $params['relateduserid'] = $submission->userid;
            }
            $event = \assignsubmission_mahara\event\assessable_uploaded::create($params);
            $event->trigger();

            $groupname = null;
            $groupid = 0;
            // Get the group name as other fields are not transcribed in the logs and this information is important.
            if (empty($submission->userid) && !empty($submission->groupid)) {
                $groupname = $DB->get_field('groups', 'name', array('id' => $submission->groupid), '*', MUST_EXIST);
                $groupid = $submission->groupid;
            } else {
                $params['relateduserid'] = $submission->userid;
            }

            // Unset the objectid and other field from params for use in submission events.
            unset($params['objectid']);
            unset($params['other']);
            $params['other'] = array(
                'submissionid' => $submission->id,
                'submissionattempt' => $submission->attemptnumber,
                'submissionstatus' => $submission->status,
                'groupid' => $groupid,
                'groupname' => $groupname
            );

            if ($maharasubmission) {
                // If we are updating previous submission, release previous submission first (if it's locked).
                if ($maharasubmission->viewid != $data->viewid && $maharasubmission->viewstatus == self::STATUS_SUBMITTED) {
                    if ($this->mnet_release_submitted_view($maharasubmission->viewid, array(), $maharasubmission->iscollection) === false) {
                        throw new moodle_exception('errormnetrequest', 'assignsubmission_mahara', '', $this->get_error());
                    }
                }

                // Update submission data.
                $maharasubmission->viewid = $data->viewid;
                $maharasubmission->viewurl = $response['url'];
                $maharasubmission->viewtitle = clean_text($response['title']);
                $maharasubmission->viewstatus = $status;
                $maharasubmission->iscollection = (int) $iscollection;
                $params['objectid'] = $maharasubmission->id;
                $updatestatus = $DB->update_record('assignsubmission_mahara', $maharasubmission);
                $event = \assignsubmission_mahara\event\submission_updated::create($params);
                $event->set_assign($this->assignment);
                $event->trigger();
                return $updatestatus;
            } else {
                if ($data->viewid === null) {
                    return $DB->delete_records('assignsubmission_mahara', array('submission' => $submission->id));
                } else {
                    // We are dealing with the new submission.
                    $maharasubmission = new stdClass();
                    $maharasubmission->viewid = $data->viewid;
                    $maharasubmission->viewurl = $response['url'];
                    $maharasubmission->viewtitle = clean_text($response['title']);
                    $maharasubmission->viewstatus = $status;
                    $maharasubmission->iscollection = (int) $iscollection;

		            $maharasubmission->submission = $submission->id;
		            $maharasubmission->assignment = $this->assignment->get_instance()->id;
		            $maharasubmission->id = $DB->insert_record('assignsubmission_mahara', $maharasubmission);
		            $params['objectid'] = $maharasubmission->id;
		            $event = \assignsubmission_mahara\event\submission_created::create($params);
		            $event->set_assign($this->assignment);
		            $event->trigger();
		            return $maharasubmission->id > 0;
                }
            }
        }
    }

    /**
     * Check if the submission plugin has all the required data to allow the work
     * to be submitted for grading
     * @param stdClass $submission the assign_submission record being submitted.
     * @return bool|string 'true' if OK to proceed with submission, otherwise a
     *                        a message to display to the user
     */
    public function precheck_submission($submission) {
        $maharasubmission = $this->get_mahara_submission($submission->id);
        if (!$maharasubmission) {
            return get_string('emptysubmission', 'assignsubmission_mahara');
        }
        return true;
    }

     /**
      * Process submission for grading
      *
      * @global stdClass $DB
      * @param stdClass $submission
      * @return void
      */
    public function submit_for_grading($submission) {
        global $DB;

        // If the submission has been locked in the gradebook, then it has already been submitted on the Mahara side
        $flags = $this->assignment->get_user_flags($submission->userid, false);
        if ($flags && $flags->locked == 1) {
            return;
        }

        $maharasubmission = $this->get_mahara_submission($submission->id);
        // Lock view on Mahara side as it has been submitted for assessment.
        if (!$response = $this->mnet_submit_view($submission, $maharasubmission->viewid, $maharasubmission->iscollection)) {
            throw new moodle_exception('errormnetrequest', 'assignsubmission_mahara', '', $this->get_error());
        }
        $maharasubmission->viewurl = $response['url'];
        $maharasubmission->viewstatus = self::STATUS_SUBMITTED;

        if (!$this->get_config('lock')) {
            if ($this->mnet_release_submitted_view($maharasubmission->viewid, array(), $maharasubmission->iscollection) === false) {
                throw new moodle_exception('errormnetrequest', 'assignsubmission_mahara', '', $this->get_error());
            }
            $maharasubmission->viewstatus = self::STATUS_RELEASED;
        }

        $DB->update_record('assignsubmission_mahara', $maharasubmission);
    }

    /**
      * Process locking
      *
      * @global stdClass $DB
      * @param stdClass $submission
      * @return void
      */
    public function lock($submission, stdClass $flags = null) {
        global $DB;

        $maharasubmission = $this->get_mahara_submission($submission->id);

        // If we're not using page locking, then don't need to do anything special here.
        // If no page is selected, then we don't need to do anything special here.
        // If it's in submitted status, then it has already been locked and we don't need to do anything
        if (
                !$this->get_config('lock')
                || !$maharasubmission
                || !$maharasubmission->viewid
                || $maharasubmission->viewstatus == self::STATUS_SUBMITTED
        ) {
            return;
        }

        // Lock view on Mahara side
        if (!$response = $this->mnet_submit_view($submission, $maharasubmission->viewid, $maharasubmission->iscollection, $submission->userid)) {
            throw new moodle_exception('errormnetrequest', 'assignsubmission_mahara', '', $this->get_error());
        }
        $maharasubmission->viewurl = $response['url'];
        $maharasubmission->viewstatus = self::STATUS_SUBMITTED;
        $DB->update_record('assignsubmission_mahara', $maharasubmission);
    }

    /**
      * Process unlocking
      *
      * @global stdClass $DB
      * @param stdClass $submission
      * @return void
      */
    public function unlock($submission, stdClass $flags = null) {
        global $DB;

        // If it has been submitted, and we're using page locking, it needs to remain locked
        if ($submission->status === ASSIGN_SUBMISSION_STATUS_SUBMITTED && $this->get_config('lock')) {
            return;
        }

        $maharasubmission = $this->get_mahara_submission($submission->id);

        // If no page is selected, then we don't need to do anything special here.
        // If the page isn't locked, we don't need to do anything special here.
        if (!$maharasubmission || !$maharasubmission->viewid || $maharasubmission->viewstatus !== self::STATUS_SUBMITTED) {
            return;
        }

        // Unlock view on Mahara side as it has been unlocked.
        if ($this->mnet_release_submitted_view($maharasubmission->viewid, array(), $maharasubmission->iscollection) === false) {
            throw new moodle_exception('errormnetrequest', 'assignsubmission_mahara', '', $this->get_error());
        }
        $this->set_mahara_submission_status($maharasubmission->submission, self::STATUS_RELEASED);
    }

    /**
      * Process reverting to draft
      *
      * @global stdClass $DB
      * @param stdClass $submission
      * @return void
      */
    public function revert_to_draft(stdClass $submission) {
        global $DB;

        // If the submission has been locked in the gradebook, then we don't want to release it on the Mahara side
        // ... unless we've disabled page locking, in which case we might as well unlock it
        $flags = $this->assignment->get_user_flags($submission->userid, false);
        if ($this->get_config('lock') && $flags && $flags->locked == 1) {
            return;
        }

        $maharasubmission = $this->get_mahara_submission($submission->id);
        if ($maharasubmission->viewstatus === self::STATUS_SUBMITTED) {
            // Unlock view on Mahara side as it has been reverted to draft.
            if ($this->mnet_release_submitted_view($maharasubmission->viewid, array(), $maharasubmission->iscollection) === false) {
                throw new moodle_exception('errormnetrequest', 'assignsubmission_mahara', '', $this->get_error());
            }
            $this->set_mahara_submission_status($submission->id, self::STATUS_RELEASED);
        }
    }

    /**
     * Check if submission has been made
     *
     * @param stdClass $submission
     * @return bool
     */
    public function is_empty(stdClass $submission) {
        $maharasubmission = $this->get_mahara_submission($submission->id);
        return empty($maharasubmission);
    }

    /**
     * Get view URL
     *
     * @param stdClass $maharasubmission assignsubmission_mahara record
     * @return stdClass $url Moodle URL object
     */
    public function get_view_url($viewurl) {
        global $DB;
        $remotehost = $DB->get_record('mnet_host', array('id'=>$this->get_config('mnethostid')));
        $url = new moodle_url('/auth/mnet/jump.php', array(
            'hostid' => $remotehost->id,
            'wantsurl' => $viewurl,
        ));
        return $url;
    }

    /**
     * Gets the preview (popup) and link out for the portfolio
     *
     * @param string $name
     * @param string|moodle_url $url
     * @param string $title (Optional)
     * @return string
     */
    public function get_preview_url($name, $url, $title = null) {
        global $OUTPUT;

        $icon = $OUTPUT->pix_icon('t/preview', $name);
        $params = array('target' => '_blank', 'title' => $title ?: $name);

        $popup_icon = html_writer::link($url, $icon, $params + array(
          'class' => 'portfolio popup',
        ));

        $link = html_writer::link($url, $name, $params);

        return "$popup_icon $link";
    }

    /**
     * Display the view of submission.
     *
     * @global stdClass $DB
     * @global stdClass $USER
     * @param stdClass $submission
     * @return string
     */
    public function view(stdClass $submission) {
        global $PAGE, $OUTPUT, $DB, $USER;

        $PAGE->requires->js('/mod/assign/submission/mahara/js/popup.js');

        $result = '';
        $maharasubmission = $this->get_mahara_submission($submission->id);
        if ($maharasubmission) {
            $fields = array( 'assignment' => $submission->assignment );
            if (!empty($submission->groupid)) {
                $fields['groupid'] = $submission->groupid;
            }
            if (!empty($submission->userid)) {
                $fields['userid'] = $submission->userid;
            }
            $lastattempt = $DB->get_field('assign_submission', 'max(attemptnumber)', $fields);
            if ($submission->attemptnumber < $lastattempt) {
                $result .= get_string('previousattemptsnotvisible', 'assignsubmission_mahara');
            } else {
                // Either the page is viewed by the author or access code has been issued
                $remotehost = $DB->get_record('mnet_host', array('id'=>$this->get_config('mnethostid')));
                $url = $this->get_view_url($maharasubmission->viewurl);
                return $this->get_preview_url($maharasubmission->viewtitle, $url);
            }
        }
        return $result;
    }

    /**
     * @see parent
     *
     * @param stdClass $submission
     * @param bool $showviewlink (Mutable)
     * @return string
     */
    public function view_summary(stdClass $submission, &$showviewlink) {
        return $this->view($submission);
    }

     /**
     * Return true if this plugin can upgrade an old Moodle 2.2 assignment of this type and version.
     *
     * @param string $type old assignment subtype
     * @param int $version old assignment version
     * @return bool True if upgrade is possible
     */
    public function can_upgrade($type, $version) {
        if ($type == 'mahara' && $version >= 2011070110) {
            return true;
        }
        return false;
    }

    /**
     * Upgrade the settings from the old assignment to the new plugin based one
     *
     * @param context $oldcontext - the database for the old assignment context
     * @param stdClass $oldassignment - the database for the old assignment instance
     * @param string $log record log events here
     * @return bool Was it a success?
     */
    public function upgrade_settings(context $oldcontext, stdClass $oldassignment, & $log) {
        $this->set_config('mnethostid', $oldassignment->var2);
        return true;
    }

    /**
     * Upgrade the submission from the old assignment to the new one
     *
     * @param context $oldcontext - the database for the old assignment context
     * @param stdClass $oldassignment The data record for the old assignment
     * @param stdClass $oldsubmission The data record for the old submission
     * @param stdClass $submission The data record for the new submission
     * @param string $log Record upgrade messages in the log
     * @return bool true or false - false will trigger a rollback
     */
    public function upgrade(context $oldcontext, stdClass $oldassignment, stdClass $oldsubmission, stdClass $submission, & $log) {
        global $DB;

        $maharadata = unserialize($oldsubmission->data2);

        $maharasubmission = new stdClass();
        $maharasubmission->viewid = $maharadata['id'];
        $maharasubmission->viewurl = $maharadata['url'];
        $maharasubmission->viewtitle = $maharadata['title'];

        $url = new moodle_url($maharadata['url']);
        if ($url->get_param('mt')) {
            $maharasubmission->viewstatus = self::STATUS_SUBMITTED;
        }

        $maharasubmission->submission = $submission->id;
        $maharasubmission->assignment = $this->assignment->get_instance()->id;

        if (!$DB->insert_record('assignsubmission_mahara', $maharasubmission) > 0) {
            $log .= get_string('couldnotconvertsubmission', 'mod_assign', $submission->userid);
            return false;
        }
        return true;
    }

    /**
     * Formatting for log info
     *
     * @global stdClass $DB
     * @param stdClass $submission The new submission
     * @return string
     */
    public function format_for_log(stdClass $submission) {
        global $DB;
        $remotehost = $DB->get_record('mnet_host', array('id'=>$this->get_config('mnethostid')));
        if ($maharasubmission = $this->get_mahara_submission($submission->id)) {
            $maharasubmission->remotehostname = $remotehost->name;
            $output = get_string('outputforlog', 'assignsubmission_mahara', $maharasubmission);
        } else {
            $output = get_string('outputforlognew', 'assignsubmission_mahara', $remotehost->name);
        }
        return $output;
    }

    /**
     * The assignment has been deleted - cleanup
     *
     * @global stdClass $DB
     * @return bool
     */
    public function delete_instance() {
        global $DB;
        // First of all release all pages on remote site.
        $records = $DB->get_records(
                'assignsubmission_mahara',
                array(
                        'assignment' => $this->assignment->get_instance()->id,
                        'viewstatus' => self::STATUS_SUBMITTED
                )
        );
        foreach ($records as $record) {
            if ($this->mnet_release_submitted_view($record->viewid, array(), $record->iscollection) === false) {
                // A problem on the Mahara side should not prevent the assignment from being deleted.
                // But it's worth printing a message to the error logs.
                debugging(get_string('errormnetrequest', 'assignsubmission_mahara', $this->get_error()));
            }
        }
        // Now delete records.
        $DB->delete_records('assignsubmission_mahara', array('assignment'=>$this->assignment->get_instance()->id));

        return true;
    }

    /**
     * Carry out any extra processing required when a student is given a new attempt
     * (i.e. when the submission is "reopened"
     * @param stdClass $oldsubmission The previous attempt
     * @param stdClass $newsubmission The new attempt
     */
    public function add_attempt(stdClass $oldsubmission, stdClass $newsubmission) {
        global $DB;
        // Unlock the previous submission's page if the assignment is reopened. That way
        // the student can make improvements and then resubmit.
        $maharasubmission = $this->get_mahara_submission($oldsubmission->id);
        if ($maharasubmission && $maharasubmission->viewstatus == self::STATUS_SUBMITTED) {
            if ($this->mnet_release_submitted_view($maharasubmission->viewid, array(), $maharasubmission->iscollection) === false) {
                throw new moodle_exception('errormnetrequest', 'assignsubmission_mahara', '', $this->get_error());
            }
            $this->set_mahara_submission_status($maharasubmission->submission, self::STATUS_RELEASED);
        }
    }

    /**
     * Helper method to set the status of the an assignsubmission_mahara record
     *
     * @param int $submissionid
     * @param string $status
     * @throws moodle_exception
     * @return boolean
     */
    public function set_mahara_submission_status($submissionid, $status) {
        global $DB;
        if (!($status === self::STATUS_SELECTED || $status === self::STATUS_SUBMITTED || $status === self::STATUS_RELEASED)) {
            throw new moodle_exception('errorinvalidstatus', 'assignsubmission_mahara');
        }
        return $DB->set_field('assignsubmission_mahara', 'viewstatus', $status, array('submission' => $submissionid));
    }
}
