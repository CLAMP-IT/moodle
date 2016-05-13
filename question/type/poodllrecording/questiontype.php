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
 * Question type class for the poodllrecording question type.
 *
 * @package    qtype
 * @subpackage poodllrecording
 * @copyright  2012 Justin Hunt
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();


/**
 * The poodllrecording question type.
 *
 * @copyright  2012 Justin Hunt
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_poodllrecording extends question_type {
    public function is_manual_graded() {
        return true;
    }

    public function response_file_areas() {
        return array('answer');
    }

    public function get_question_options($question) {
        global $DB;
        $question->options = $DB->get_record('qtype_poodllrecording_opts',
                array('questionid' => $question->id), '*', MUST_EXIST);
        parent::get_question_options($question);
    }

    public function save_question_options($formdata) {
        global $DB;
        $context = $formdata->context;

        $options = $DB->get_record('qtype_poodllrecording_opts', array('questionid' => $formdata->id));
        if (!$options) {
            $options = new stdClass();
            $options->questionid = $formdata->id;
            $options->id = $DB->insert_record('qtype_poodllrecording_opts', $options);
        }

		//"import_or_save_files" won't work, because it expects output from an editor which is an array with member itemid
		//the filemanager doesn't produce this, so need to use file save draft area directly
		//$options->backimage = $this->import_or_save_files($formdata->backimage,
		// $context, 'qtype_poodllrecording', 'backimage', $formdata->id);
		if (isset($formdata->backimage)){
			file_save_draft_area_files($formdata->backimage, $context->id, 'qtype_poodllrecording',
			'backimage', $formdata->id, array('subdirs' => 0, 'maxbytes' => 0, 'maxfiles' => 1));
			
			//save the itemid of the backimage filearea
			$options->backimage = $formdata->backimage;
		}else{
			$options->backimage = null;
		}
		

		//save the selected board size
		if (isset($formdata->boardsize)){
			$options->boardsize=$formdata->boardsize;
		}else{
			$options->boardsize="320x320";
		}
		
		//if we have a recording time limit
		if (isset($formdata->timelimit)){
			$options->timelimit=$formdata->timelimit;
		}else{
			$options->timelimit=0;
		}
	
        $options->responseformat = $formdata->responseformat;
		$options->graderinfo = $this->import_or_save_files($formdata->graderinfo,
                $context, 'qtype_poodllrecording', 'graderinfo', $formdata->id);
        $options->graderinfoformat = $formdata->graderinfo['format'];
        $DB->update_record('qtype_poodllrecording_opts', $options);
    }

    protected function initialise_question_instance(question_definition $question, $questiondata) {
        parent::initialise_question_instance($question, $questiondata);
        $question->responseformat = $questiondata->options->responseformat;
		$question->graderinfo = $questiondata->options->graderinfo;
        $question->graderinfoformat = $questiondata->options->graderinfoformat;
		$question->backimage=$questiondata->options->backimage;
		$question->boardsize=$questiondata->options->boardsize;
		$question->timelimit=$questiondata->options->timelimit;
    }

    /**
     * @return array the different response formats that the question type supports.
     * internal name => human-readable name.
     */
    public function response_formats() {
        return array(
			'mp3' => get_string('formatmp3', 'qtype_poodllrecording'),
            'audio' => get_string('formataudio', 'qtype_poodllrecording'),
			'video' => get_string('formatvideo', 'qtype_poodllrecording'),
			'picture' => get_string('formatpicture', 'qtype_poodllrecording'),
        );
       // 'simplepicture' => get_string('formatsimplepicture', 'qtype_poodllrecording')  
    }




	/**
	* @return array the different board sizes  that the whiteboard supports.
	* internal name => human-readable name.
	*/
	public function board_sizes() {
	return array(
	'320x320' => get_string('x320x320', 'qtype_poodllrecording'),
	'400x600' => get_string('x400x600', 'qtype_poodllrecording'),
	'500x500' => get_string('x500x500', 'qtype_poodllrecording'),
	'600x400' => get_string('x600x400', 'qtype_poodllrecording'),
	'600x800' => get_string('x600x800', 'qtype_poodllrecording'),
	'800x600' => get_string('x800x600', 'qtype_poodllrecording')
	);
	}

    /**
     * @return array the choices that should be offered for the input box size.
     */
    public function response_sizes() {
        $choices = array();
        for ($lines = 5; $lines <= 40; $lines += 5) {
            $choices[$lines] = get_string('nlines', 'qtype_poodllrecording', $lines);
        }
        return $choices;
    }

    /**
     * @return array the choices that should be offered for the number of attachments.
     */
    public function attachment_options() {
        return array(
            0 => get_string('no'),
            1 => '1',
            2 => '2',
            3 => '3',
            -1 => get_string('unlimited'),
        );
    }

    public function move_files($questionid, $oldcontextid, $newcontextid) {
        parent::move_files($questionid, $oldcontextid, $newcontextid);
        $fs = get_file_storage();
        $fs->move_area_files_to_new_context($oldcontextid,
                $newcontextid, 'qtype_poodllrecording', 'graderinfo', $questionid);
        $fs->move_area_files_to_new_context($oldcontextid,
                $newcontextid, 'qtype_poodllrecording', 'backimage', $questionid);
    }

    protected function delete_files($questionid, $contextid) {
        parent::delete_files($questionid, $contextid);
        $fs = get_file_storage();
        $fs->delete_area_files($contextid, 'qtype_poodllrecording', 'graderinfo', $questionid);
        $fs->delete_area_files($contextid, 'qtype_poodllrecording', 'backimage', $questionid);
    }
    
     /**
     * If your question type has a table that extends the question table, and
     * you want the base class to automatically save, backup and restore the extra fields,
     * override this method to return an array wherer the first element is the table name,
     * and the subsequent entries are the column names (apart from id and questionid).
     *
     * @return mixed array as above, or null to tell the base class to do nothing.
     */
    public function extra_question_fields() {
    	$tableinfo = array("qtype_poodllrecording_opts",
    		"responseformat",
    		"responsefieldlines","attachments",
    		"graderinfo","graderinfoformat",
    		"backimage","boardsize", "timelimit");
    		
        return $tableinfo;
    }
    
    /*
     * Export question to the Moodle XML format
     *
     * Export question using information from extra_question_fields function
     * We override this because we need to export file fields as base 64 strings, not ids
     */
    public function export_to_xml($question, qformat_xml $format, $extra=null) {

		//get file storage
		$fs = get_file_storage();
		$expout ="";
        
	   $expout .= "    <responseformat>" . $question->options->responseformat .
				"</responseformat>\n";
		$expout .= "    <responsefieldlines>" . $question->options->responsefieldlines .
				"</responsefieldlines>\n";
		$expout .= "    <attachments>" . $question->options->attachments .
				"</attachments>\n";
		$expout .= "    <graderinfo " .
				$format->format($question->options->graderinfoformat) . ">\n";
		$expout .= $format->writetext($question->options->graderinfo, 3);
		$expout .= $format->write_files($fs->get_area_files($question->contextid,  'qtype_poodllrecording',
				'graderinfo', $question->id));
		$expout .= "    </graderinfo>\n";
		$expout .= "    <backimage>" . $format->write_files($fs->get_area_files($question->contextid, 'qtype_poodllrecording',
				'backimage', $question->id)).
				"</backimage>\n";
		$expout .= "    <boardsize>" . $question->options->boardsize .
				"</boardsize>\n";
		$expout .= "    <timelimit>" . $question->options->timelimit .
				"</timelimit>\n";
        
        return $expout;
   
    }
    
        /*
     * Imports question from the Moodle XML format
     *
     * Imports question using information from extra_question_fields function
     * If some of you fields contains id's you'll need to reimplement this
     */
    public function import_from_xml($data, $question, qformat_xml $format, $extra=null) {
    global $CFG;
    
        $question_type = "poodllrecording";
        
        //omit table name
        $qo = $format->import_headers($data);
        $qo->qtype = $question_type;
        $q = $data;
        
        $qo->responseformat = $format->getpath($q,
                array('#', 'responseformat', 0, '#'), 'picture');
        $qo->responsefieldlines = $format->getpath($q,
                array('#', 'responsefieldlines', 0, '#'), 15);
        $qo->attachments = $format->getpath($q,
                array('#', 'attachments', 0, '#'), 0);
        //older versions handled files diff. SeeM DL-39-57        
    	if($CFG->version < 2013051400){
    		$qo->graderinfo['text'] = $format->getpath($q,
                array('#', 'graderinfo', 0, '#', 'text', 0, '#'), '', true);
        	$qo->graderinfo['format'] = $format->trans_format($format->getpath($q,
                array('#', 'graderinfo', 0, '@', 'format'), $format->get_format($qo->questiontextformat)));
        	$qo->graderinfo['files'] = $format->import_files($format->getpath($q,
                array('#', 'graderinfo', '0', '#', 'file'), array()));
                
            $qo->backimage = $format->import_files($format->getpath($q,
                array('#', 'backimage', '0', '#', 'file'), array()));
    	
    	}else{
    		$qo->graderinfo =  $format->import_text_with_files($q, array('#', 'graderinfo', 0), '', $qo->questiontextformat);
    		$qo->backimage  = $format->import_files_as_draft($format->getpath($q,
                array('#', 'backimage', '0', '#', 'file'), array()));
    		
    	}                     
        
        $qo->boardsize = $format->getpath($q,
                array('#', 'boardsize', 0, '#'), '320x320');
		$qo->timelimit = $format->getpath($q,
                array('#', 'timelimit', 0, '#'), 0);
        

        return $qo;

    }//end of import from xml
	

	

}
