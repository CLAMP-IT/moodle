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
 * PoodLLrecording question renderer class.
 *
 * @package    qtype
 * @subpackage poodllrecording
 * @copyright  2012 Justin Hunt
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();
//require_once($CFG->dirroot . '/filter/poodll/poodllfilelib.php');

/**
 * Generates the output for poodllrecording questions.
 *
 * @copyright  Justin Hunt
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_poodllrecording_renderer extends qtype_renderer {
    public function formulation_and_controls(question_attempt $qa,
            question_display_options $options) {

        $question = $qa->get_question();
        $responseoutput = $question->get_format_renderer($this->page);

        // Answer field.
        $step = $qa->get_last_step_with_qt_var('answer');
        if (empty($options->readonly)) {
            $answer = $responseoutput->response_area_input('answer', $qa,
                    $step, 1, $options->context);

        } else {
            $answer = $responseoutput->response_area_read_only('answer', $qa,
                    $step, 1, $options->context);
        }

		
        $result = '';
        $result .= html_writer::tag('div', $question->format_questiontext($qa),
                array('class' => 'qtext'));

        $result .= html_writer::start_tag('div', array('class' => 'ablock'));
        $result .= html_writer::tag('div', $answer, array('class' => 'answer'));
        $result .= html_writer::end_tag('div');

        return $result;
    }


  
    public function manual_comment(question_attempt $qa, question_display_options $options) {
        if ($options->manualcomment != question_display_options::EDITABLE) {
            return '';
        }

        $question = $qa->get_question();
        return html_writer::nonempty_tag('div', $question->format_text(
                $question->graderinfo, $question->graderinfo, $qa, 'qtype_poodllrecording',
                'graderinfo', $question->id), array('class' => 'graderinfo'));
    }
}

/**
 * An poodllrecording format renderer for poodllrecordings for audio
 *
 * @copyright  2012 Justin Hunt
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_poodllrecording_format_audio_renderer extends plugin_renderer_base {
   

    protected function class_name() {
        return 'qtype_poodllrecording_audio';
    }
    
    	protected function get_submitted_file($name, $qa, $step, $context) {
			//if we don't have an attempt, we don't have a submitted file
			//if(!is_object($context)) return false;
			
			//fetch file from storage and figure out URL
    		$storedfiles=$qa->get_last_qt_files($name,$context->id);
    		foreach ($storedfiles as $sf){
    			//when we find the file that matches the filename in $step, use that
				$usefilename = strip_tags($step->get_qt_var($name));
				$storedfilename = strip_tags($sf->get_filename());
				if($usefilename === $storedfilename){
					return $sf;
				}
    		}
    		return false;	
    }

	//This is not necessary, but when testing it can be handy to display this
	protected function textarea($response, $lines, $attributes) {
        $attributes['class'] = $this->class_name() . ' qtype_essay_response';
        $attributes['rows'] = $lines;
        $attributes['cols'] = 60;
        return html_writer::tag('textarea', s($response), $attributes);
	}
  
    protected function prepare_response_for_editing($name,
            question_attempt_step $step, $context) {
        return $step->prepare_response_files_draft_itemid_with_text(
                $name, $context->id, $step->get_qt_var($name));
                
    }
    
    public function response_area_read_only($name, $qa, $step, $lines, $context) {
			//see if we have a file
			$submittedfile = $this->get_submitted_file($name, $qa, $step, $context);
			
			//if we do, we return the img link. If not, we return an empty string 
			if($submittedfile){
				$pathtofile= $qa->get_response_file_url($submittedfile);
				return  \filter_poodll\poodlltools::fetchSimpleAudioPlayer('auto',$pathtofile,"http",400,25);
			}else{
				return "";
			}
    }

    public function response_area_input($name, $qa, $step, $lines, $context) {
    	global $USER;
    	$usercontextid=context_user::instance($USER->id)->id;
    	
		//prepare a draft file id for use
		list($draftitemid, $response) = $this->prepare_response_for_editing( $name, $step, $context);
		
		//prepare the tags for our hidden( or shown ) input
		$inputname = $qa->get_qt_field_name($name);
		//$inputname="answer";
		$inputid =  $inputname . '_id';
		
		//check of we already have a submitted answer. If so we need to set the filename
		//in our input field.
		$submittedfile = $this->get_submitted_file($name, $qa, $step, $context);
		if($submittedfile){
			$submittedfilename=strip_tags($submittedfile->get_filename());
		}else{
			$submittedfilename="";
		}	
		//our answerfield
		$ret =	html_writer::empty_tag('input', array('type' => 'hidden','id'=>$inputid, 
				'name' => $inputname, 'value' => $submittedfilename));
		//this is just for testing purposes so we can see the value the recorder is writing
		//$ret = $this->textarea($step->get_qt_var($name), $lines, array('name' => $inputname,'id'=>$inputid));
		
		//our answerfield draft id key
		$ret .=	html_writer::empty_tag('input', array('type' => 'hidden', 'name' => $inputname . ':itemid', 'value'=> $draftitemid));
		
		//our answerformat
		$ret .= html_writer::empty_tag('input', array('type' => 'hidden','name' => $inputname . 'format', 'value' => 1));
	
		//if we already have a response, lets display so the student  can check/decide to rerecord
		if($submittedfile){
			$ret .= get_string('currentresponse', 'qtype_poodllrecording');
			$ret .=  \filter_poodll\poodlltools::fetchSimpleAudioPlayer('auto',$qa->get_response_file_url($submittedfile),"http",400,25);
		}
	
		
		//get a handle on the question
		$q = $qa->get_question();
	
		//the context id is the user context for a student submission
		return $ret . \filter_poodll\poodlltools::fetchAudioRecorderForSubmission('swf','question',$inputid, $usercontextid ,'user','draft',$draftitemid,$q->timelimit);

    }
}


/**
 * An poodllrecording format renderer for poodllrecordings for MP3 
 *
 * @copyright  2012 Justin Hunt
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_poodllrecording_format_mp3_renderer extends qtype_poodllrecording_format_audio_renderer {
   

    protected function class_name() {
        return 'qtype_poodllrecording_mp3';
    }

    public function response_area_input($name, $qa, $step, $lines, $context) {
    	global $USER;
    	$usercontextid=context_user::instance($USER->id)->id;
    	
		//prepare a draft file id for use
		list($draftitemid, $response) = $this->prepare_response_for_editing( $name, $step, $context);
		
		//prepare the tags for our hidden( or shown ) input
		$inputname = $qa->get_qt_field_name($name);
		//$inputname="answer";
		$inputid =  $inputname . '_id';
		
		//check of we already have a submitted answer. If so we need to set the filename
		//in our input field.
		$submittedfile = $this->get_submitted_file($name, $qa, $step, $context);
		if($submittedfile){
			$submittedfilename=strip_tags($submittedfile->get_filename());
		}else{
			$submittedfilename="";
		}	
		//our answerfield
		$ret =	html_writer::empty_tag('input', array('type' => 'hidden','id'=>$inputid, 
				'name' => $inputname, 'value' => $submittedfilename));
		//this is just for testing purposes so we can see the value the recorder is writing
		//$ret = $this->textarea($step->get_qt_var($name), $lines, array('name' => $inputname,'id'=>$inputid));
		
		//our answerfield draft id key
		$ret .=	html_writer::empty_tag('input', array('type' => 'hidden', 'name' => $inputname . ':itemid', 'value'=> $draftitemid));
		
		//our answerformat
		$ret .= html_writer::empty_tag('input', array('type' => 'hidden','name' => $inputname . 'format', 'value' => 1));
	
	
		//if we already have a response, lets display it so the student  can check/decide to rerecord
		if($submittedfile){
			$ret .= get_string('currentresponse', 'qtype_poodllrecording');
			$ret .=  \filter_poodll\poodlltools::fetchSimpleAudioPlayer('auto',$qa->get_response_file_url($submittedfile),"http",400,25);
		}
	
		//get a handle on the question
		$q = $qa->get_question();
		
		//the context id is the user context for a student submission
		return $ret . \filter_poodll\poodlltools::fetchMP3RecorderForSubmission($inputid, $usercontextid ,'user','draft',$draftitemid,$q->timelimit);

    }
}


/**
 * An poodllrecording format renderer for poodllrecordings for video
 *
 * @copyright  2012 Justin Hunt
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_poodllrecording_format_video_renderer extends qtype_poodllrecording_format_audio_renderer {
    

    protected function class_name() {
        return 'qtype_poodllrecording_video';
    }

	public function response_area_read_only($name, $qa, $step, $lines, $context) {
			//see if we have a file
			$submittedfile = $this->get_submitted_file($name, $qa, $step, $context);
			
			//if we do, we return the img link. If not, we return an empty string 
			if($submittedfile){
				$pathtofile= $qa->get_response_file_url($submittedfile);
				return  \filter_poodll\poodlltools::fetchSimpleVideoPlayer('auto',$pathtofile,400,380,"http");
			}else{
				return "";
			}
    }


    public function response_area_input($name, $qa, $step, $lines, $context) {
    	global $USER;
    	$usercontextid=context_user::instance($USER->id)->id;
    	
		//prepare a draft file id for use
		list($draftitemid, $response) = $this->prepare_response_for_editing( $name, $step, $context);


		$inputname = $qa->get_qt_field_name($name);
		$inputid =  $inputname . '_id';
		
		//check of we already have a submitted answer. If so we need to set the filename
		//in our input field.
		$submittedfile = $this->get_submitted_file($name, $qa, $step, $context);
		if($submittedfile){
			$submittedfilename=strip_tags($submittedfile->get_filename());
		}else{
			$submittedfilename="";
		}	
		//our answerfield
		$ret =	html_writer::empty_tag('input', array('type' => 'hidden','id'=>$inputid, 
				'name' => $inputname, 'value' => $submittedfilename));
		
		//our answerfield draft id key
		$ret .=	html_writer::empty_tag('input', array('type' => 'hidden', 'name' => $inputname . ':itemid', 'value'=> $draftitemid));
		
		$ret .= html_writer::empty_tag('input', array('type' => 'hidden','name' => $inputname . 'format', 'value' => FORMAT_PLAIN));

		//if we already have a response, lets display it so the student  can check/decide to rerecord
		if($submittedfile){
			$ret .= get_string('currentresponse', 'qtype_poodllrecording');
			$ret .=  \filter_poodll\poodlltools::fetchSimpleVideoPlayer('auto',$qa->get_response_file_url($submittedfile),400,380,"http");
		}
		
		//get a handle on the question
		$q = $qa->get_question();
       
		//the context id is the user context id
		return $ret . \filter_poodll\poodlltools::fetchVideoRecorderForSubmission('swf','question',$inputid, $usercontextid ,'user','draft',$draftitemid,$q->timelimit);
		
    }
}

/**
 * An poodllrecording format renderer for poodllrecordings for whiteboard pictures
 *
 * @copyright  2012 Justin Hunt
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_poodllrecording_format_picture_renderer extends qtype_poodllrecording_format_audio_renderer {


    protected function class_name() {
        return 'qtype_poodllrecording_picture';
    }

    public function response_area_read_only($name, $qa, $step, $lines, $context) {
			//see if we have a file
			$submittedfile = $this->get_submitted_file($name, $qa, $step, $context);
			$returndata="";

                
			//get vectordata only show for debugging
			//$returndata.= 'vd:' . $step->get_qt_var($name . 'vectordata') . '<br/>';
			//get b64data
			//$returndata.= 'b64d:' . $step->get_qt_var($name . 'base64data') . '<br/>';
			
			//if we do, we return the img link. If not, we return an empty string 
			if($submittedfile){
				$pathtofile= $qa->get_response_file_url($submittedfile);
				$returndata.= "<img src=\"" . $pathtofile . "\" src=\"qtype_poodllrecording_whiteboardwidth\" />";
			}else{
				$returndata.= "";
			}
			
			return $returndata;
    }

    public function response_area_input($name, $qa, $step, $lines, $context) {
    	global $USER;
    	$usercontextid=context_user::instance($USER->id)->id;
    	
		//prepare a draft file id for use
		list($draftitemid, $response) = $this->prepare_response_for_editing( $name, $step, $context);
		
		//prepare the tags for our hidden( or shown ) input
		$inputname = $qa->get_qt_field_name($name);
		//$inputname="answer";
		$inputid =  $inputname . '_id';
		
		//check of we already have a submitted answer. If so we need to set the filename
		//in our input field.
		$submittedfile = $this->get_submitted_file($name, $qa, $step, $context);
		if($submittedfile){
			$submittedfilename=strip_tags($submittedfile->get_filename());
		}else{
			$submittedfilename="";
		}	
		//our answerfield
		$ret =	html_writer::empty_tag('input', array('type' => 'hidden','id'=>$inputid, 
				'name' => $inputname, 'value' => $submittedfilename));
				
		//this is just for testing purposes so we can see the value the recorder is writing
		//$ret = $this->textarea($step->get_qt_var($name), $lines, array('name' => $inputname,'id'=>$inputid));
		
		//our answerfield draft id key
		$ret .=	html_writer::empty_tag('input', array('type' => 'hidden', 'name' => $inputname . ':itemid', 'value'=> $draftitemid));
		
		//our answerformat
		$ret .= html_writer::empty_tag('input', array('type' => 'hidden','name' => $inputname . 'format', 'value' => 1));
	
		//the names of controls that will hold base64 and vector data
		$vectorcontrol = $inputname . 'vectordata';
		$base64control = $inputname . 'base64data';
		
		//if we have a previous drawing this is the vectordata we need to restore
		//we don't need to restore base64 data. That is saved as a file.
		//NB $inputname is what is used on htmlpage, $name is name of field for php data access
		$vectordata = $step->get_qt_var($name . 'vectordata');
		if(!$vectordata){
			$vectordata='';
		}
		//output vectordata control and load it with data
		//whiteboard doesn't read data from here, but we stash it to be sure it is
		//resubmitted when user transitions page without touching whiteboard.
		$ret .= html_writer::empty_tag('input', array('type' => 'hidden','id'=>$vectorcontrol,'name' => $vectorcontrol, 'value' => $vectordata));	
		
		//output base64data control
		$ret .= html_writer::empty_tag('input', array('type' => 'hidden','id'=>$base64control,'name' => $base64control, 'value' => ''));
		
		//get a handle on the question
		$q = $qa->get_question();
	
		//Get Backimage, if we have one
		// get file system handle for fetching url to submitted media prompt (if there is one) 
		$fs = get_file_storage();
		$files = $fs->get_area_files($q->contextid, 'qtype_poodllrecording', 'backimage', $q->id);
		$imageurl="";
		//$ret .= '<br />' . $q->id;
		//$ret .= '<br />' . $context->id;
		if($files && count($files)>0){
			//this if for debugging purposes only
			/*
			foreach($files as $file){
				$mediaurl = $qa->rewrite_pluginfile_urls('@@PLUGINFILE@@/' . $file->get_filename(), $file->get_component(),$file->get_filearea() , $file->get_itemid());
				$ret .= '<br />' . $mediaurl;
			}
			*/
		
			$file = array_pop($files);
			if($file->get_filename() !="."){
				$imageurl = $qa->rewrite_pluginfile_urls('@@PLUGINFILE@@/' . $file->get_filename(), $file->get_component(),$file->get_filearea() , $file->get_itemid());
			}
		}//end of if
		
		//get board size
		//NB the board size is the size of the drawing canvas, not the widget
		$boardsize=$q->boardsize;
		switch($boardsize){
			case "320x320": $width=320;$height=320;break;
			case "400x600": $width=400;$height=600;break;
			case "500x500": $width=500;$height=500;break;
			case "600x400": $width=600;$height=400;break;
			case "600x800": $width=600;$height=800;break;
			case "800x600": $width=800;$height=600;break;
		}
		
		
		//for debugging purposes we just print this out here
		//$ret .= $imageurl . " " . $boardsize . " ";
		
		//if we already have a response, lets display it so the student  can check/decide to rerecord
		//we no longer need this if we have vector data in the whiteboard. DB and LC but not PDL Justin 20140522
		/* */
		if($submittedfile && $vectordata==''){
			$ret .= get_string('currentresponse', 'qtype_poodllrecording');
			$ret .= "<img src=\"" . $qa->get_response_file_url($submittedfile) . "\" />";
		}
		
		
		//the context id is the user context for a student submission
		return $ret . $this->prepareWhiteboard($inputid, $usercontextid ,'user','draft',$draftitemid,$width,$height,$imageurl,$vectorcontrol, $vectordata);

    }//end of function
    
    private function prepareWhiteboard($updatecontrol, $contextid,$component,$filearea,$itemid,$width=0,$height=0,$backimage="",$vectorcontrol,$vectordata){
    	$whiteboardString = \filter_poodll\poodlltools::fetchWhiteboardForSubmission($updatecontrol, $contextid,$component,$filearea,$itemid,$width,$height,$backimage,'','',$vectorcontrol,$vectordata);
    	return $whiteboardString;
    }

}//end of class
/**
 * An poodllrecording format renderer for poodllrecordings for simple snapshots
 *
 * @copyright  2012 Justin Hunt
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_poodllrecording_format_simplepicture_renderer extends qtype_poodllrecording_format_picture_renderer {


    protected function class_name() {
        return 'qtype_poodllrecording_simplepicture';
    }
    
    
	private function prepareWhiteboard($updatecontrol, $contextid,$component,$filearea,$itemid,$width=0,$height=0,$backimage=""){
    	$whiteboardString = \filter_poodll\poodlltools::fetchWhiteboardForSubmission($updatecontrol, $contextid,$component,$filearea,$itemid,$width,$height,$backimage);
    	return $whiteboardString;
    }
}//end of class



