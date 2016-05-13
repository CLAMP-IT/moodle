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
			$this->handle_error('missing filename in custom data:' , $cd);
			return;
		}
    	$select = "filename='" . $cd->filename. "' AND filearea <> 'draft' AND contenthash='" . $contenthash. "'";
    	$params = null;
    	$sort = "id DESC";
    	$dbfiles = $DB->get_records_select('files',$select,$params,$sort);
    	if(!$dbfiles){
			if(!$cd->filerecord || !$cd->filerecord->id || !$fs->get_file_by_id($cd->filerecord->id)){
				//the draft file is messed up, or gone, and its not in another area, possibly the user never saved it
				//anyway lets just forget about this task. And move on.
				return;
			}
			$nofilefoundmessage='could not find ' . $cd->filename . ' in the DB. Possibly user has not saved yet';
    		$this->handle_error($nofilefoundmessage,$cd);
			throw new \file_exception('storedfileproblem', $nofilefoundmessage);
			return;
    	}
    	
    	//get the file we will replace
    	$origfilerecord = array_shift($dbfiles);	
    	$origfile = $fs->get_file_by_id($origfilerecord->id);
    	if(!$origfile){
			$this->handle_error( 'something wrong with sf:' . $cd->filename,$cd);
			return;
		}

		//get the original draft record that we will delete and reuse
		$draftfilerecord = $cd->filerecord;
		$draftfile =  $fs->get_file_by_id($draftfilerecord->id);

		//we used to delete the draft file and reuse it. It is just our placeholder.
		//but it didn't seem to always delete, so we use another tempfilename (throwawayfilename) 
		//we still delete it, because some draft areas have file limits right?
		if($draftfile){
			$draftfile->delete();
		}

		//do the conversion
		$throwawayfilename = 'temp_' . $cd->filename;
		try{
			$convertedfile = convert_with_ffmpeg($draftfilerecord, 
				 $cd->tempdir, 
				 $cd->tempfilename, 
				 $cd->convfilenamebase, 
				$cd->convext,
				$throwawayfilename);
		} catch (Exception $e) {
			$this->handle_error('could not get convert:' . $cd->filename . ':' . $e->getMessage(),$cd);
			return;
		}
		
		//replace the placeholder(original) file with the converted one
		if($convertedfile){
			$origfile->replace_file_with($convertedfile);
			
			//now we need to replace the splash if it had one
			$imagefilename = substr($cd->filename,0,strlen($cd->filename)-3) . 'png';
			try{
				$imagefile = get_splash_ffmpeg($origfile, $imagefilename);
			} catch (Exception $e) {
				$this->handle_error('could not get create splash file from:' . $cd->filename . ':' . $e->getMessage(),$cd);
				return;
			}
			return;
		}else{
		 $this->handle_error('unable to convert ' . $cd->tempfilename,$cd);
		 return;
		}
		
    }

	private function handle_error($errorstring,$cd){
		//throwing errors will see the process retrying. 
		//however there is little point in retrying.
		$throwerrors = false;
		
		if($throwerrors){
			throw new \file_exception('storedfileproblem', $errorstring);
		}else{
			error_log('storedfileproblem:' . $errorstring);
			error_log(print_r($cd,true));
		}
	}
} 