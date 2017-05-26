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
 * This file contains the definition for the library class for poodll feedback plugin
 *
 *
 * @package   assignfeedback_poodll
 * @copyright 2013 Justin Hunt {@link http://www.poodll.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();


if(!defined('FP_REPLYMP3VOICE')){
 /**
 * File areas for PoodLL feedback
	 */
	define('ASSIGNFEEDBACK_POODLL_FILEAREA', 'poodll_files');
	define('ASSIGNFEEDBACK_POODLL_COMPONENT', 'assignfeedback_poodll');

	//some constants for poodll feedback
	define('FP_REPLYMP3VOICE',0);
	define('FP_REPLYVOICE',1);
	define('FP_REPLYVIDEO',2);
	define('FP_REPLYWHITEBOARD',3);
	define('FP_REPLYSNAPSHOT',4);
	define('FP_FILENAMECONTROL','poodllfeedback');
}

/**
 * library class for PoodLL feedback plugin extending feedback plugin base class
 *
 * @package   assignfeedback_poodll
 * @copyright 2013 Justin Hunt {@link http://www.poodll.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class assign_feedback_poodll extends assign_feedback_plugin {

   /**
    * Get the name of the online comment feedback plugin
    * @return string
    */
    public function get_name() {
        return get_string('pluginname', 'assignfeedback_poodll');
    }

    /**
     * Get the PoodLL feedback  from the database
     *
     * @param int $gradeid
     * @return stdClass|false The feedback poodll for the given grade if it exists. False if it doesn't.
     */
    public function get_feedback_poodll($gradeid) {
        global $DB;
        return $DB->get_record('assignfeedback_poodll', array('grade'=>$gradeid));
    }
    
    	    /**
     * Get the settings for PoodLL Feedback plugin form
     *
     * @global stdClass $CFG
     * @global stdClass $COURSE
     * @param MoodleQuickForm $mform The form to add elements to
     * @return void
     */
    public function get_settings(MoodleQuickForm $mform) {
        global $CFG, $COURSE;

		//get saved values and return them as defaults
        $recordertype = $this->get_config('recordertype');

        //convert old Red5 refs to audio media type option
        if($recordertype==FP_REPLYVOICE){
            $recordertype=FP_REPLYMP3VOICE;
        }
		$boardsize = $this->get_config('boardsize');
		$downloadsok = $this->get_config('downloadsok');
		
		//get allowed recorders from admin settings
		$allowed_recorders = get_config('assignfeedback_poodll', 'allowedrecorders');
		$allowed_recorders  = explode(',',$allowed_recorders);
		$recorderoptions = array();
		if(array_search(FP_REPLYMP3VOICE,$allowed_recorders)!==false || array_search(FP_REPLYVOICE,$allowed_recorders)!==false){
			$recorderoptions[FP_REPLYMP3VOICE] = get_string("replymp3voice", "assignfeedback_poodll");
		}
		if(array_search(FP_REPLYVIDEO ,$allowed_recorders)!==false){
			$recorderoptions[FP_REPLYVIDEO ] = get_string("replyvideo", "assignfeedback_poodll");
		}
		if(array_search(FP_REPLYWHITEBOARD,$allowed_recorders)!==false){
			$recorderoptions[FP_REPLYWHITEBOARD ] = get_string("replywhiteboard", "assignfeedback_poodll");
		}
		if(array_search(FP_REPLYSNAPSHOT,$allowed_recorders)!==false){
			$recorderoptions[FP_REPLYSNAPSHOT] = get_string("replysnapshot", "assignfeedback_poodll");
		}
		
	
	$mform->addElement('select', 'assignfeedback_poodll_recordertype', get_string("recordertype", "assignfeedback_poodll"), $recorderoptions);
        //$mform->addHelpButton('assignfeedback_poodll_recordertype', get_string('onlinepoodll', ASSIGNSUBMISSION_ONLINEPOODLL_COMPONENT), ASSIGNSUBMISSION_ONLINEPOODLL_COMPONENT);
    $mform->setDefault('assignfeedback_poodll_recordertype', $recordertype);
	$mform->disabledIf('assignfeedback_poodll_recordertype', 'assignfeedback_poodll_enabled', 'eq', 0);
	
	//Are students and teachers shown the download link for the feedback recording
	$yesno_options = array( 1 => get_string("yes", "assignfeedback_poodll"), 
				0 => get_string("no", "assignfeedback_poodll"));
	$mform->addElement('select', 'assignfeedback_poodll_downloadsok', get_string('downloadsok', 'assignfeedback_poodll'), $yesno_options);
	$mform->setDefault('assignfeedback_poodll_downloadsok', $downloadsok);
		
	//If whiteboard not allowed, not much point showing boardsizes
		if(array_search(FP_REPLYWHITEBOARD,$allowed_recorders)!==false){
				//board sizes for the whiteboard feedback
				$boardsizes = array(
						'320x320' => '320x320',
						'400x600' => '400x600',
						'500x500' => '500x500',
						'600x400' => '600x400',
						'600x800' => '600x800',
						'800x600' => '800x600'
						);
				$mform->addElement('select', 'assignfeedback_poodll_boardsize',
						get_string('boardsize', 'assignfeedback_poodll'), $boardsizes);
				$mform->setDefault('assignfeedback_poodll_boardsize', $boardsize);
				$mform->disabledIf('assignfeedback_poodll_boardsize', 'assignfeedback_poodll_enabled', 'eq', 0);
					$mform->disabledIf('assignfeedback_poodll_boardsize', 'assignfeedback_poodll_recordertype', 'ne', FP_REPLYWHITEBOARD );
		}//end of if whiteboard
		
    }//end of function
    
    
       /**
     * Save the settings for poodll feedback plugin
     *
     * @param stdClass $data
     * @return bool 
     */
    public function save_settings(stdClass $data) {

        $this->set_config('recordertype', $data->assignfeedback_poodll_recordertype);
		$this->set_config('downloadsok', $data->assignfeedback_poodll_downloadsok);
		
		//if we have a board size, set it
		if(isset($data->assignfeedback_poodll_boardsize)){
			$this->set_config('boardsize', $data->assignfeedback_poodll_boardsize);
		}else{
			$this->set_config('boardsize', '320x320');
		}
		
	
        return true;
    }
    
    function shift_draft_file($grade, $data) {
        global $CFG, $USER, $DB,$COURSE;	
 
	//When we add the recorder via the poodll filter, it adds a hidden form field of the name FP_FILENAMECONTROL
	//the recorder updates that field with the filename of the audio/video it recorded. We pick up that filename here.
	$filename ='';     
	$draftitemid = 	0;
	if(property_exists($data,FP_FILENAMECONTROL) && !empty($data->{FP_FILENAMECONTROL})){
		$filename = $data->{FP_FILENAMECONTROL};
		$draftitemid = $data->draftitemid;
	}
	
		//Don't do anything in this case
		//possibly the user is just updating something else on the page(eg grade)
		//if we overwrite here, we might trash their existing poodllfeedback file
		if($filename=='' || $filename==null){return false;}
        
        //if this should fail, we get regular user context, is it the same anyway?
        $usercontextid = optional_param('usercontextid', '', PARAM_RAW);
        if ($usercontextid == ''){
        	$usercontextid = context_user::instance($USER->id)->id;
        }
         
         $fs = get_file_storage();
         $browser = get_file_browser();
         $fs->delete_area_files($this->assignment->get_context()->id, ASSIGNFEEDBACK_POODLL_COMPONENT,ASSIGNFEEDBACK_POODLL_FILEAREA , $grade->id);
		
		
		//if filename = -1 we are being told to delete the file
		//so we have done enough
		if($filename==-1){
			return '';
		}
		
		//fetch the file info object for our original file
		$original_context = context::instance_by_id($usercontextid);
		$draft_fileinfo = $browser->get_file_info($original_context, 'user','draft', $draftitemid, '/', $filename);

 		//perform the copy	
		if($draft_fileinfo){
			//create the file record for our new file
			$file_record = array(
			'userid' => $USER->id,
			'contextid'=>$this->assignment->get_context()->id, 
			'component'=>ASSIGNFEEDBACK_POODLL_COMPONENT, 
			'filearea'=>ASSIGNFEEDBACK_POODLL_FILEAREA,
			'itemid'=>$grade->id, 
			'filepath'=>'/', 
			'filename'=>$filename,
			'author'=>'moodle user',
			'license'=>'allrighttsreserved',		
			'timecreated'=>time(), 
			'timemodified'=>time()
			);
			$ret = $draft_fileinfo->copy_to_storage($file_record);
		}//end of if $draft_fileinfo
		
		return $filename;

	}//end of shift_draft_file
    

    /**
     * Override to indicate a plugin supports quickgrading
     *
     * @return boolean - True if the plugin supports quickgrading
     */
    public function supports_quickgrading() {
        return false;
    }

     /**
     * Get file areas returns a list of areas this plugin stores files
     * @return array - An array of fileareas (keys) and descriptions (values)
     */
    public function get_file_areas() {
        return array(ASSIGNFEEDBACK_POODLL_FILEAREA=>$this->get_name());
    }

	/**
     * Get form elements for grading form 
	 * [this is deprecated from 2.3.4 ..but prev moodle versions need it]
     *
     * @param stdClass $grade
     * @param MoodleQuickForm $mform
     * @param stdClass $data
     * @return bool true if elements were added to the form
     */
	public function get_form_elements($grade, MoodleQuickForm $mform, stdClass $data) {
        return $this->get_form_elements_for_user($grade, $mform,$data,0);
    }
	
     /**
     * Get form elements for grading form
     *
     * @param stdClass $grade
     * @param MoodleQuickForm $mform
     * @param stdClass $data
     * @param int $userid The userid we are currently grading
     * @return bool true if elements were added to the form
     */
    public function get_form_elements_for_user($grade, MoodleQuickForm $mform, stdClass $data, $userid) {
        global $USER,$PAGE,$CFG;
        $debug="";
        $PAGE->requires->js(new moodle_url($CFG->httpswwwroot . '/mod/assign/feedback/poodll/module.js'));
        $displayname = $this->get_name(); 
        $gradeid = $grade ? $grade->id : 0;
        
        if ($gradeid > 0 && get_config('assignfeedback_poodll', 'showcurrentfeedback')) {
           $currentfeedback = $this->fetch_responses($gradeid);
            if($currentfeedback != ''){
				$deletefeedback = "<a href='javascript:void(0);' onclick='M.assignfeedback_poodll.deletefeedback();'>".
            						"<img src='" . $CFG->httpswwwroot . '/mod/assign/feedback/poodll/pix/deletebutton.png' . 
									"' alt='" . get_string('deletefeedback','assignfeedback_poodll') . "'/>" . 
            						"</a>";
            	$currentfeedback .= $deletefeedback;
            }
            $currentcontainer = 'currentfeedbackwrapper';
            $currentfeedback = "<div id='" .$currentcontainer. "'>" . $currentfeedback . "</div>";

             $mform->addElement('static', 'currentfeedback', $displayname,$currentfeedback);
             //reset the display name so it doesn't show with the recorder
             $displayname="";
             
             $opts = array(
				"filecontrolid"=> FP_FILENAMECONTROL,
				"reallydeletefeedback"=> get_string('reallydeletefeedback','assignfeedback_poodll'),
				"currentcontainer"=> $currentcontainer
			);
			//$PAGE->requires->js(new moodle_url($CFG->httpswwwroot . '/mod/assign/feedback/poodll/module.js'));
			$PAGE->requires->js_init_call('M.assignfeedback_poodll.init',array($opts),false);
        }

		//We prepare our form here and fetch/save data in SAVE method
		$usercontextid=context_user::instance($USER->id)->id;
		$draftitemid = file_get_submitted_draft_itemid(FP_FILENAMECONTROL);
		$contextid=$this->assignment->get_context()->id;
		file_prepare_draft_area($draftitemid, $contextid, ASSIGNFEEDBACK_POODLL_COMPONENT, ASSIGNFEEDBACK_POODLL_FILEAREA, $gradeid, null,null);
		$mform->addElement('hidden', 'draftitemid', $draftitemid);
		$mform->addElement('hidden', 'usercontextid', $usercontextid);	
		$mform->addElement('hidden', FP_FILENAMECONTROL, '',array('id' => FP_FILENAMECONTROL));
		$mform->setType('draftitemid', PARAM_INT);
		$mform->setType('usercontextid', PARAM_INT); 
		$mform->setType(FP_FILENAMECONTROL, PARAM_TEXT); 
	
        //no timelimit on recordings
        $timelimit=0;

        //get saved values and return them as defaults
        $recordertype = $this->get_config('recordertype');
		
		//fetch the required "recorder
		switch($recordertype){
				
			case FP_REPLYWHITEBOARD:
				//get board sizes
				switch($this->get_config('boardsize')){
					case "320x320": $width=320;$height=320;break;
					case "400x600": $width=400;$height=600;break;
					case "500x500": $width=500;$height=500;break;
					case "600x400": $width=600;$height=400;break;
					case "600x800": $width=600;$height=800;break;
					case "800x600": $width=800;$height=600;break;
				}

				
				$imageurl="";
				$mediadata= \filter_poodll\poodlltools::fetchWhiteboardForSubmission(FP_FILENAMECONTROL,
						$usercontextid ,'user','draft',$draftitemid, $width, $height, $imageurl);
				$mform->addElement('static', 'description',$displayname,$mediadata);
				break;
			
			case FP_REPLYSNAPSHOT:
				$mediadata= \filter_poodll\poodlltools::fetchSnapshotCameraforSubmission(FP_FILENAMECONTROL,
						"snap.jpg" ,350,400,$usercontextid ,'user','draft',$draftitemid);
				$mform->addElement('static', 'description',$displayname,$mediadata);
				break;

			case FP_REPLYVIDEO:
				$mediadata= \filter_poodll\poodlltools::fetchVideoRecorderForSubmission('swf','poodllfeedback',FP_FILENAMECONTROL,
						$usercontextid ,'user','draft',$draftitemid,$timelimit);
				$mform->addElement('static', 'description',$displayname,$mediadata);			
									
				break;

            case FP_REPLYVOICE:
            case FP_REPLYMP3VOICE:
                $mediadata= \filter_poodll\poodlltools::fetchMP3RecorderForSubmission(FP_FILENAMECONTROL, $usercontextid ,'user','draft',$draftitemid,$timelimit);
                $mform->addElement('static', 'description',$displayname,$mediadata);
                break;
					
		}

        // hidden params: Pretty sure we don't need this. Justin 20170523
        //$mform->addElement('hidden', 'id', 0);
        //$mform->setType('id', PARAM_INT);
	return true;

    }


    public function is_feedback_modified(stdClass $grade, stdClass $data){

        $thefilename = '';
        /*
        if ($grade) {
            $poodllfeedback = $this->get_feedback_poodll($grade->id);
            if (isset($poodllfeedback->filename) && !empty($poodllfeedback->filename)) {
                $thefilename = $poodllfeedback->filename;
            }
        }
        */
        if($data->{FP_FILENAMECONTROL}==$thefilename){
            return false;
        }else{
            return true;
        }
    }


    /**
     * Saving the comment content into dtabase
     *
     * @param stdClass $grade
     * @param stdClass $data
     * @return bool
     */
    public function save(stdClass $grade, stdClass $data) {
        global $DB;
   
        //Move recorded files from draft to the correct area
        //if shift_draft_file is false, no change, so do nothing
        //if it is an empty string, user has deleted file, so we clear it too
		$filename = $this->shift_draft_file($grade, $data);
		if($filename === false){return true;}
        
        $feedbackpoodll = $this->get_feedback_poodll($grade->id);
        if ($feedbackpoodll) {
        	$feedbackpoodll->filename = $filename;
            return $DB->update_record('assignfeedback_poodll', $feedbackpoodll);
        } else {
            $feedbackpoodll = new stdClass();
            $feedbackpoodll->grade = $grade->id;
            $feedbackpoodll->filename = $filename;
            $feedbackpoodll->assignment = $this->assignment->get_instance()->id;
            return $DB->insert_record('assignfeedback_poodll', $feedbackpoodll) > 0;
        }
    }

    /**
     * display the player in the feedback table
     *
     * @param stdClass $grade
     * @param bool $showviewlink Set to true to show a link to view the full feedback
     * @return string
     */
    public function view_summary(stdClass $grade, & $showviewlink) {
        $showviewlink = false;

        //our response, this will output a player/image
        return $this->fetch_responses($grade->id) ;
    }
    
/*
* Fetch the player to show the submitted recording(s)
*
*
*
*/
function fetch_responses($gradeid){
        global $CFG;
        $responsestring = "";
		
        //get filename, from the filearea for this submission. 
        //there should be only one.
        $fs = get_file_storage();
        $filename="";
        $files = $fs->get_area_files($this->assignment->get_context()->id, 
				ASSIGNFEEDBACK_POODLL_COMPONENT, ASSIGNFEEDBACK_POODLL_FILEAREA, $gradeid, "id", false);
        if (!empty($files)) {
			//if the filename property exists, and is filled, use that to fetch the file
			$poodllfeedback= $this->get_feedback_poodll($gradeid);
			if(isset($poodllfeedback->filename) && !empty($poodllfeedback->filename)){
				$filename =  $poodllfeedback->filename;
				
			//if no filename property just take the first file. That is how we used to do it	
			}else{
				foreach ($files as $file) {
					$filename = $file->get_filename();
					break;
				}
			}
	}
	
        //if this is a playback area, for teacher, show a string if no file
        if (empty($filename)){ 
                                $responsestring .= "";
        }else{	
                //The path to any media file we should play
                $rawmediapath = $CFG->wwwroot.'/pluginfile.php/'.$this->assignment->get_context()->id 
						. '/assignfeedback_poodll/' . ASSIGNFEEDBACK_POODLL_FILEAREA  . '/'.$gradeid.'/'.$filename;
                $mediapath = urlencode($rawmediapath);

                //prepare our response string, which will parsed and replaced with the necessary player
                switch($this->get_config('recordertype')){


                        case FP_REPLYVOICE:
                        case FP_REPLYMP3VOICE:
	
							$responsestring .= format_text("<a href=\"$rawmediapath\">$filename</a>", FORMAT_HTML);
							if($this->get_config('downloadsok')){
								$responsestring .= "<a href='" . $rawmediapath . "'>" 
										. get_string('downloadfile', 'assignfeedback_poodll') 
										."</a>";
							}
							
							break;						

                        case FP_REPLYVIDEO:
                            $responsestring .= format_text("<a href=\"$rawmediapath\">$filename</a>", FORMAT_HTML);
                            break;

                        case FP_REPLYWHITEBOARD:
                                $responsestring .= "<img alt=\"submittedimage\" class=\"assignfeedback_poodll_whiteboardwidth\" src=\"" . $rawmediapath . "\" />";
                                break;

                        case FP_REPLYSNAPSHOT:
                                $responsestring .= "<img alt=\"submittedimage\" class=\"assignfeedback_poodll_snapshotwidth\" src=\"" . $rawmediapath . "\" />";
                                break;

                        default:
                                $responsestring .= format_text("<a href=\"$rawmediapath\">$filename</a>", FORMAT_HTML);
					break;
                                break;	

                }//end of switch
        }//end of if (checkfordata ...) 

        return $responsestring;
		
}//end of fetch_responses


    /**
     * display the comment in the feedback table
     *
     * @param stdClass $grade
     * @return string
     */
    public function view(stdClass $grade) {
        return $this->fetch_responses($grade->id) ;
    }

    /**
     * Return true if this plugin can upgrade an old Moodle 2.2 assignment of this type
     * and version.
     *
     * @param string $type old assignment subtype
     * @param int $version old assignment version
     * @return bool True if upgrade is possible
     */
    public function can_upgrade($type, $version) {

        if (($type == 'upload' || $type == 'uploadsingle' ||
             $type == 'online' || $type == 'offline') && $version >= 2011112900) {
            return true;
        }
        return false;
    }

    /**
     * Upgrade the settings from the old assignment to the new plugin based one
     *
     * @param context $oldcontext - the context for the old assignment
     * @param stdClass $oldassignment - the data for the old assignment
     * @param string $log - can be appended to by the upgrade
     * @return bool was it a success? (false will trigger a rollback)
     */
    public function upgrade_settings(context $oldcontext, stdClass $oldassignment, & $log) {
        // first upgrade settings (nothing to do)
        return true;
    }

    /**
     * Upgrade the feedback from the old assignment to the new one
     *
     * @param context $oldcontext - the database for the old assignment context
     * @param stdClass $oldassignment The data record for the old assignment
     * @param stdClass $oldsubmission The data record for the old submission
     * @param stdClass $grade The data record for the new grade
     * @param string $log Record upgrade messages in the log
     * @return bool true or false - false will trigger a rollback
     */
    public function upgrade(context $oldcontext, stdClass $oldassignment, stdClass $oldsubmission, stdClass $grade, & $log) {
        global $DB;

        $feedbackpoodll = new stdClass();
        $feedbackpoodll->commenttext = $oldsubmission->submissioncomment;
        $feedbackpoodll->commentformat = FORMAT_HTML;

        $feedbackpoodll->grade = $grade->id;
        $feedbackpoodll->assignment = $this->assignment->get_instance()->id;
        if (!$DB->insert_record('assignfeedback_poodll', $feedbackpoodll) > 0) {
            $log .= get_string('couldnotconvertgrade', 'mod_assign', $grade->userid);
            return false;
        }

        return true;
    }

    /**
     * The assignment has been deleted - cleanup
     *
     * @return bool
     */
    public function delete_instance() {
        global $DB;
        // will throw exception on failure
        $DB->delete_records('assignfeedback_poodll', array('assignment'=>$this->assignment->get_instance()->id));
        return true;
    }

    /**
     * Returns true if there are no feedback poodll for the given grade
     *
     * @param stdClass $grade
     * @return bool
     */
    public function is_empty(stdClass $grade) {
        return $this->view($grade) == '';
    }

}
