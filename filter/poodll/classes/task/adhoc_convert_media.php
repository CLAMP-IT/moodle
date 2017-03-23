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

namespace filter_poodll\task;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/filter/poodll/poodllfilelib.php');


/**
 *
 * This is an adhoc task for converting media with FFMPEG
 *
 * @package   filter_poodll
 * @since      Moodle 2.7
 * @copyright  2014 Justin Hunt
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class adhoc_convert_media extends \core\task\adhoc_task {

    const LOG_DID_NOT_CONVERT = 1;
    const LOG_MISSING_FILENAME = 2;
    const LOG_NO_FILE_FOUND_IN_DB = 3;
    const LOG_STORED_FILE_PROBLEM = 4;
    const LOG_SPLASHFILE_MAKE_FAIL = 5;
    const LOG_UNABLE_TO_CONVERT = 6;

    public function execute() {   
    	//NB: seems any exceptions not thrown HERE, kill subsequent tasks
    	//so wrap some function calls in try catch to prevent that happening
    	
    	global $DB,$CFG;
    	//get passed in data we need to perform conversion
    	$cd =  $this->get_custom_data();
    	
    	//find the file in the files database
    	$fs = get_file_storage();
    	switch($cd->convext){
    		case '.mp3': $contenthash = POODLL_AUDIO_PLACEHOLDER_HASH;break;
    		case '.mp4': $contenthash = POODLL_VIDEO_PLACEHOLDER_HASH;break;
    		default:$contenthash = '';
    	
    	}
		if(!property_exists($cd,'filename')){
			$this->handle_error(self::LOG_MISSING_FILENAME,'missing filename in custom data:' , $cd);
			return;
		}
    	$select = "filename='" . $cd->filename. "' AND filearea <> 'draft' AND contenthash='" . $contenthash. "'";
    	$params = null;
    	$sort = "id DESC";
    	$dbfiles = $DB->get_records_select('files',$select,$params,$sort);
    	if(!$dbfiles){
			$nofilefoundmessage='could not find ' . $cd->filename . ' in the DB. Possibly user has not saved yet';
    		$this->handle_error(self::LOG_NO_FILE_FOUND_IN_DB,$nofilefoundmessage,$cd);
			throw new \file_exception('storedfileproblem', $nofilefoundmessage);
			return;
    	}
    	
    	//get the file we will replace
    	$origfilerecord = array_shift($dbfiles);	
    	$origfile = $fs->get_file_by_id($origfilerecord->id);
    	if(!$origfile){
			$this->handle_error(self::LOG_STORED_FILE_PROBLEM, 'something wrong with sf:' . $cd->filename,$cd);
			return;
		}

		//get the original draft record that we will delete and reuse
		$draftfilerecord = $cd->filerecord;
		$draftfile =  $fs->get_file_by_id($draftfilerecord->id);

		//we used to delete the draft file and reuse it. It is just our placeholder.
		//but it didn't seem to always delete, so we use another temporary filename (throwawayfilename) 
		//we still delete it, because some draft areas have file limits right?
		if($draftfile){
			$draftfile->delete();
		}

		//do the conversion
		$throwawayfilename = 'temp_' . $cd->filename;
		try{
			$convertedfile = \filter_poodll\poodlltools::convert_with_ffmpeg($draftfilerecord, 
				 $cd->originalfilename, 
				 $cd->convfilenamebase, 
				$cd->convext,
				$throwawayfilename);
		} catch (Exception $e) {
			$this->handle_error(self::LOG_DID_NOT_CONVERT,'could not get convert:' . $cd->filename . ':' . $e->getMessage(),$cd);
			return;
		}
		
		//replace the placeholder(original) file with the converted one
		if($convertedfile){
			$origfile->replace_file_with($convertedfile);

			//now we make a splash if it needs one
            if($cd->convext=='.mp3') {
                $imagefilename = substr($cd->filename, 0, strlen($cd->filename) - 3) . 'png';
                try {
                    $imagefile = \filter_poodll\poodlltools::get_splash_ffmpeg($origfile, $imagefilename);
                } catch (Exception $e) {
                    $this->handle_error(self::LOG_SPLASHFILE_MAKE_FAIL, 'could not get create splash file from:' . $cd->filename . ':' . $e->getMessage(), $cd);
                    //we don't "return" here, because the lack of a splash is not critical, the file is converted
                }
            }
		}else{
		    $this->handle_error(self::LOG_UNABLE_TO_CONVERT,'unable to convert ' . $cd->originalfilename,$cd);
		    return;
		}
		//if we got here then the task was completed successfully
        $cd->outfilename=$cd->filename;
		$cd->infilename=$cd->originalfilename;
		$cd->filerecord=$origfilerecord;
        \filter_poodll\event\adhoc_convert_completed::create_from_task($cd)->trigger();
		
    }

	private function handle_error($errorcode,$errorstring,$cd){
		//throwing errors will see the process retrying. 
		//however there is little point in retrying.
		$throwerrors = false;

        //data for logging
        $contextid=$cd->filerecord->contextid;
        $userid=$cd->filerecord->userid;
		
		if($throwerrors){
		    //log error
            $this->send_debug_data($errorcode,
                $errorstring,$userid,$contextid);

            throw new \file_exception('storedfileproblem', $errorstring);
		}else{
			error_log('storedfileproblem:' . $errorstring);
			error_log(print_r($cd,true));

            $this->send_debug_data($errorcode,
                $errorstring,$userid,$contextid);
		}
	}

    private function send_debug_data($type,$message, $userid=false,$contextid=false){
        global $CFG;
        //only log if is on in Poodll settings
        if(!$CFG->filter_poodll_debug){return;}

        $debugdata = new \stdClass();
        $debugdata->userid=$userid;
        $debugdata->contextid=$contextid;
        $debugdata->type=$type;
        $debugdata->source='adhoc_convert_media.php';
        $debugdata->message=$message;
        \filter_poodll\event\debug_log::create_from_data($debugdata)->trigger();
    }
} 