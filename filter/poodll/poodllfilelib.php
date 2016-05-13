<?php

/**
* internal library of functions and constants for Poodll modules
* accessed directly by poodll flash wdgets on web pages.
* @package mod-poodllpairwork
* @category mod
* @author Justin Hunt
*
*/


/**
* Includes and requires
*/
//ob_start();
global $CFG;


define('POODLL_VIDEO_PLACEHOLDER_HASH','c2a342a0a664f2f1c4ea5387554a67caf3dd158e');
define('POODLL_AUDIO_PLACEHOLDER_HASH','e118549e4fc88836f418b6da6028f1fec571cd43');

//we need to do this, because when called from a widet, cfg is not set
//but the relative path fails from a quiz but it has already been set in that case
//, so we check before we call it, to cover both bases

if(!isset($CFG)){
require_once("../../config.php");
}


//commented just while getting other mods working

//added for moodle 2
require_once($CFG->libdir . '/filelib.php');

	$datatype = optional_param('datatype', "", PARAM_TEXT);    // Type of action/data we are requesting
	$contextid  = optional_param('contextid', 0, PARAM_INT);  // the id of the course 
	$courseid  = optional_param('courseid', 0, PARAM_INT);  // the id of the course 
	$moduleid  = optional_param('moduleid', 0, PARAM_INT);  // the id of the module 
	//added justin 20120803 careful here, I think $component is a php keyword or something
	//it screwed the whole world
	$comp = optional_param('component', "", PARAM_TEXT);  // the component
	$farea = optional_param('filearea', "", PARAM_TEXT);  // the filearea
	
	$itemid  = optional_param('itemid', 0, PARAM_INT);  // the id of the module
	$hash  = optional_param('hash', "", PARAM_TEXT);  // file or dir hash
	$requestid  = optional_param('requestid', "", PARAM_TEXT);  // file or dir hash
	$paramone  = optional_param('paramone', "", PARAM_TEXT);  // nature of value depends on datatype, maybe path
	$paramtwo  = optional_param('paramtwo', "", PARAM_TEXT);  // nature of value depends on datatype, maybe protocol
	$paramthree  = optional_param('paramthree', "", PARAM_TEXT);  // nature of value depends on datatype, maybe filearea
	
	//from the general recorder (mp3)
	$p1  =  optional_param('p1', "", PARAM_TEXT);
	$p2 =  optional_param('p2', "", PARAM_TEXT);
	$p3 =  optional_param('p3', "", PARAM_TEXT);
	$p4  = optional_param('p4', "", PARAM_TEXT);
	$p5  = optional_param('p5', "", PARAM_TEXT);
	$filedata  = optional_param('filedata', "", PARAM_TEXT);
	$fileext  = optional_param('fileext', "", PARAM_TEXT);
	//map general recorder upload data to what we expect otherwise
	if($p1!=''){
		$contextid = $p2;
		$comp = $p3;
		$farea = $p4;
		$itemid=$p5;
		$paramone = $filedata;
		$paramtwo = $fileext;
		$paramthree = 'audio';
	}
	
	switch($datatype){
		
		case "uploadfile":
			header("Content-type: text/xml");
			echo "<?xml version=\"1.0\"?>\n";
			//uploadfile filedata(base64), fileextension (needs to be cleaned), blah blah 
			//paramone is the file data, paramtwo is the file extension, paramthree is the mediatype (audio,video, image)
			//requestid is the actionid
			$returnxml = uploadfile($paramone,$paramtwo, $paramthree, $requestid,$contextid, $comp, $farea,$itemid);
			break;
		
		case "poodllpluginfile":
			//poodllpluginfile($contextid,$component,$filearea,$itemid,$filepath,$filename);
			//lets hard code this for now, very very mild security
			poodllpluginfile($contextid,"mod_assignment","submission",$itemid,"/",$paramone);
			return;


		case "instancedownload":
			//paramone=mimetype paramtwo=path paramthree=hash
			instance_download($paramone,$paramtwo,$hash,$requestid);

		case "instanceremotedownload":
			//($contextid,$filename,$component, $filearea,$itemid, $requestid)
			//e.g (15, '123456789.flv','user','draft','746337947',777777)
			$returnxml=instance_remotedownload($contextid, $paramone,$paramtwo,$paramthree,$itemid,$requestid);

			//move the output to here so that there is no trace of stray characters entering output before file downloaded
			header("Content-type: text/xml");
			echo "<?xml version=\"1.0\"?>";

			break;

		default:
			return;
/*
		case "getlast20files":
			header("Content-type: text/html");
			$returnxml="";
			echo "hi";
			getLast20Files();
			break;
	
		case "getrepodata": 
			header("Content-type: text/xml");
			echo "<?xml version=\"1.0\"?>\n";
			$returnxml=fetch_repos();
			break;

		case "repodirlist": 
			header("Content-type: text/xml");
			echo "<?xml version=\"1.0\"?>\n";
			$returnxml=fetch_repodirlist($paramone);
			break;	
			
		case "instancedirlist": 
			header("Content-type: text/xml");
			echo "<?xml version=\"1.0\"?>\n";
			//paramone=path, paramtwo=filearea
			$returnxml=fetch_instancedirlist($moduleid, $courseid, $itemid, $paramone, $paramtwo);
			break;
				
		case "legacydirlist": 
			header("Content-type: text/xml");
			echo "<?xml version=\"1.0\"?>\n";
			//paramone=path, paramtwo=filearea
			$returnxml=fetch_legacydirlist($courseid);
			break;
				
		case "instancedeleteall": 
			header("Content-type: text/xml");
			echo "<?xml version=\"1.0\"?>\n";
			$returnxml=instance_deleteall($moduleid, $courseid, $itemid, $paramone, $requestid);
			break;
			
		case "instancecopyfile": 
			header("Content-type: text/xml");
			echo "<?xml version=\"1.0\"?>\n";
			//$moduleid, $courseid, $itemid, $filearea, $filepath,$newpath, $requestid)
			$returnxml=instance_copyfilein($moduleid, $courseid,$itemid, $paramone, $paramtwo, $paramthree, $requestid);
			break;
			
		case "instanceduplicatefile": 
			header("Content-type: text/xml");
			echo "<?xml version=\"1.0\"?>\n";
			//module, course, itemid, filearea, filepath, (origfile)hash,  reqid
			$returnxml=instance_duplicatefile($moduleid, $courseid, $itemid, $paramone,  $paramtwo, $hash, $requestid);
			break;
		
		case "instancedeletefile": 
			header("Content-type: text/xml");
			echo "<?xml version=\"1.0\"?>\n";
			$returnxml=instance_deletefile($hash, $requestid);
			break;
			
		case "instancefetchfileinfo": 
			header("Content-type: text/xml");
			echo "<?xml version=\"1.0\"?>\n";
			$returnxml=instance_fetchfileinfo($hash, $requestid);
			break;
			
		case "instancecreatedir": 
			header("Content-type: text/xml");
			echo "<?xml version=\"1.0\"?>\n";
			$returnxml=instance_createdir($moduleid, $courseid, $itemid, $paramone, $paramtwo, $requestid);
			break;

		case "instancecopydir": 
			header("Content-type: text/xml");
			echo "<?xml version=\"1.0\"?>\n";
			$returnxml=instance_copydirin($moduleid, $courseid, $itemid, $paramone, $paramtwo, $paramthree, $requestid);
			break;
		
		
		case "instancerenamefile": 
			header("Content-type: text/xml");
			echo "<?xml version=\"1.0\"?>\n";
			//module, course, originalhash, newfilename, copyas, reqid
			$returnxml=instance_renamefile($moduleid, $courseid, $hash, $paramone, false, $requestid);
			break;
			
		case "instancecopyasfile": 
			header("Content-type: text/xml");
			echo "<?xml version=\"1.0\"?>\n";
			//module, course, originalhash, newfilename, copyas, reqid
			$returnxml=instance_renamefile($moduleid, $courseid, $hash, $paramone, true, $requestid);
			break;
			
		case "getmoddata": 
			header("Content-type: text/xml");
			echo "<?xml version=\"1.0\"?>\n";
			$returnxml=getmoddata($courseid, $requestid);
			break;

		
		case "fetchrealurl": 
			header("Content-type: text/xml");
			echo "<?xml version=\"1.0\"?>\n";
			$returnxml=fetchrealurl($moduleid,$courseid, $itemid, $paramone, $paramtwo, $requestid);
			break;

*/


	}//enf od switch


	echo $returnxml;
	return;



//For uploading a file diorect from an HTML5 or SWF widget
function uploadfile($filedata,  $fileextension, $mediatype, $actionid,$contextid, $comp, $farea,$itemid){
	global $CFG,$USER;


	//setup our return object
	$return=fetchReturnArray(true);

	//make sure nobodyapassed in a bogey file extension
	switch($fileextension){
		case "mp3":
		case "flv":
		case "jpg":
		case "png":
		case "xml":
		case "mov":
		case "wav":
		case "mp4":
		case "3gpp":
		case "3gp":
		case "3g2":
		case "aac":
		case "wma":
		case "wmv":
		case "smf":
		case "amr":
		case "ogg":
			break;

		case "":
		default:
			//if we are set to FFMPEG convert,lets  not muddle with the file extension
			if($CFG->filter_poodll_ffmpeg && $mediatype=='audio' && $CFG->filter_poodll_audiotranscode){
				//do nothing
			}elseif($CFG->filter_poodll_ffmpeg && $mediatype=='video' && $CFG->filter_poodll_videotranscode){
				//do nothing
			}else{
				if($mediatype=='video'){
					$fileextension="mp4";
				}elseif($mediatype=='image'){
					$fileextension="jpg";
				}else{
					$fileextension="mp3";
				}
			}
	}

	//init our fs object
	$fs = get_file_storage();
	//assume a root level filepath
	$filepath="/";




	//make our filerecord
	$record = new stdClass();
	$record->filearea = $farea;
	$record->component = $comp;
	$record->filepath = $filepath;
	$record->itemid   = $itemid;
	$record->license  = $CFG->sitedefaultlicense;
	$record->author   = 'Moodle User';
	$record->contextid = $contextid;
	$record->userid    = $USER->id;
	$record->source    = '';


	//make filename and set it
	//we are trying to remove useless junk in the draft area here
	//when we know its stable, we will do the same for non images too
	if($mediatype=='image'){
		$filenamebase = "upfile_" . $actionid ;
	}else{
		$filenamebase = "upfile_" . rand(100,32767) . rand(100,32767)  ;
	}
	$fileextension =  "." . $fileextension;
	$filename = $filenamebase . $fileextension;
	$record->filename = $filename;


	//in most cases we will be storing files in a draft area and lettign Moodle do the rest
	//previously we only allowed one file in draft, but we removed that limit
	/*
	if($farea=='draft'){
		$fs->delete_area_files($contextid,$comp,$farea,$itemid);
	}
	*/

	//if file already exists, raise an error
	if($fs->file_exists($contextid,$comp,$farea,$itemid,$filepath,$filename)){
		if($mediatype=='image'){
			//delete any existing draft files.
			$file = $fs->get_file($contextid,$comp,$farea,$itemid,$filepath,$filename);
			$file->delete();

			//check there is no metadata prefixed to the base 64. From OL widgets, none, from JS yes
			$metapos = strPos($filedata,",");
			if($metapos >10 && $metapos <30){
				$filedata = substr($filedata,$metapos+1);
			}

			//decode the data and store it
			$xfiledata = base64_decode($filedata);
			//create the file
			$stored_file = $fs->create_file_from_string($record, $xfiledata);

		}else{
			$stored_file = false;
			$return['success']=false;
			array_push($return['messages'],"Already exists, file with filename:" . $filename );
		}
	}else{

		//check there is no metadata prefixed to the base 64. From OL widgets, none, from JS yes
		//if so it will look like this: data:image/png;base64,iVBORw0K
		//we remove it, there must be a better way of course ...
		//$metapos = strPos($filedata,";base64,");
		$metapos = strPos($filedata,",");
		if($metapos >10 && $metapos <30){
			//$trunced = substr($filedata,0,$metapos+8);
			$filedata = substr($filedata,$metapos+1);

		}

		//decode the data and store it in memory
		$xfiledata = base64_decode($filedata);

		//Determine if we need to convert and what format the conversions should take
		if($CFG->filter_poodll_ffmpeg && $CFG->filter_poodll_audiotranscode && $fileextension!=".mp3" && $mediatype=="audio"){
			$convext = ".mp3";
		}else if($CFG->filter_poodll_ffmpeg && $CFG->filter_poodll_videotranscode && $fileextension!=".mp4" && $mediatype=="video"){
			$convext = ".mp4";
		}else{
			$convext=false;
		}

		//if we need to convert with ffmpeg, get on with it
		if($convext){
			//determine the temp directory
			if (isset($CFG->tempdir)){
				$tempdir =  $CFG->tempdir . "/";
			}else{
				//moodle 2.1 users have no $CFG->tempdir
				$tempdir =  $CFG->dataroot . "/temp/";
			}
			//actually make the file on disk so FFMPEG can get it
			$ret = file_put_contents($tempdir . $filename, $xfiledata);

			//if successfully saved to disk, convert
			if($ret){
				$do_bg_encoding = ($CFG->filter_poodll_bgtranscode_audio && $convext==".mp3") ||
					($CFG->filter_poodll_bgtranscode_video && $convext==".mp4");
				if($do_bg_encoding && $CFG->version>=2014051200){
					$stored_file = convert_with_ffmpeg_bg($record,$tempdir,$filename,$filenamebase, $convext );
				}else{
					$stored_file = convert_with_ffmpeg($record,$tempdir,$filename,$filenamebase, $convext );
				}
				if($stored_file){
					$filename=$stored_file->get_filename();

					//if failed, default to using the original uploaded data
					//and delete the temp file we made
				}else{
					$stored_file = $fs->create_file_from_string($record, $xfiledata);
					if(is_readable(realpath($tempdir . $filename))){
						unlink(realpath($tempdir . $filename));
					}
				}

				//if couldn't create on disk fall back to the original data
			}else{
				$stored_file = $fs->create_file_from_string($record, $xfiledata);
			}

			//if we are not converting, then just create our moodle file entry with original file data
		}else{
			$stored_file = $fs->create_file_from_string($record, $xfiledata);
		}

	}

	//if successful return filename
	if($stored_file){
		array_push($return['messages'],$filename );

		//if unsuccessful, return error
	}else{
		$return['success']=false;
		array_push($return['messages'],"unable to save file with filename:" . $filename );
	}

	//we process the result for return to browser
	$xml_output=prepareXMLReturn($return, $actionid);

	//we return to widget/client the result of our file operation
	return $xml_output;
}

/*
* Extract an image from the video for use as splash
* image stored in same location with same name (diff ext)
* as original video file
*
*/
function get_splash_ffmpeg($videofile, $newfilename){

global $CFG, $USER;

		//determine the temp directory
		if (isset($CFG->tempdir)){
			$tempdir =  $CFG->tempdir . "/";	
		}else{
			//moodle 2.1 users have no $CFG->tempdir
			$tempdir =  $CFG->dataroot . "/temp/";
		}

		//init our fs object
		$fs = get_file_storage();
		//it would be best if we could use $videofile->get_content_filehandle somehow ..
		//but this works for now.
		$tempvideofilepath = $tempdir . $videofile->get_filename();
		$tempsplashfilepath = $tempdir . $newfilename;
		$ok = $videofile->copy_content_to($tempvideofilepath);
		
		//call on ffmpeg to create the snapshot
		//$ffmpegopts = "-vframes 1 -an ";
		//this takes the frame after 1 s
		$ffmpegopts = "-ss 00:00:01 -vframes 1 -an ";
		
		//if there is a version in poodll filter dir, use that
		//else use ffmpeg version on path
		if(file_exists($CFG->dirroot . '/filter/poodll/ffmpeg')){
			$ffmpegpath = $CFG->dirroot . '/filter/poodll/ffmpeg';
		}else{
			$ffmpegpath = 'ffmpeg';
		}
		
		//branch logic if windows
		$iswindows =(strtoupper(substr(PHP_OS, 0, 3)) === 'WIN');
		$command = $ffmpegpath . " -i " . $tempvideofilepath . " " . $ffmpegopts . " " . $tempsplashfilepath;

		if($iswindows){
			$output = system($command, $fv);
		}else{
			shell_exec($command . " >/dev/null 2>/dev/null ");
		}
		
		
		//add the play button
		//this can be done from ffmpeg, but probably not on all installs, so we do in php
		if(is_readable(realpath($tempsplashfilepath))){	
			//provided this is not a place holder. We don't really want to confuse even more
			if($videofile->get_contenthash()!=POODLL_VIDEO_PLACEHOLDER_HASH){
				$bg = imagecreatefrompng($tempsplashfilepath);
				$btn = imagecreatefrompng($CFG->dirroot . '/filter/poodll/pix/playbutton.png');
				imagealphablending($bg, 1);
				imagealphablending($btn, 1);
				//bail if we failed here
				if(!($bg && $btn)){return false;}
			
				//put the button on the bg picture
				imagecopy($bg, $btn, (imagesx($bg)-imagesx($btn)) / 2, (imagesy($bg)-imagesy($btn)) / 2, 0 , 0,imagesx($btn) , imagesy($btn));			
				$btnok = imagepng($bg, $tempsplashfilepath, 7);
			}//end of if place holder
		}else{
			return false;
		}
		
	
		//initialize return value
		$stored_file = false;
	
		//Check if we could create the image
		if(is_readable(realpath($tempsplashfilepath))){			
			//make our filerecord
			 $record = new stdClass();
			$record->filearea = $videofile->get_filearea();
			$record->component = $videofile->get_component();
			$record->filepath = $videofile->get_filepath();
			$record->itemid   = $videofile->get_itemid();
			$record->license  = $CFG->sitedefaultlicense;
			$record->author   = 'Moodle User';
			$record->contextid = $videofile->get_contextid();
			$record->userid    = $USER->id;
			$record->source    = '';
		
			//set the image filename and call on Moodle to make a stored file from the image
			$record->filename = $newfilename;
			
			//delete the existing file if we had one
			$hash  = $fs->get_pathname_hash($record->contextid, 
				$record->component, 
				$record->filearea, 
				$record->itemid, 
				$record->filepath, 
				$record->filename);
			$stored_file = $fs->get_file_by_hash($hash);
			if($stored_file){
				$record->filename = 'temp_' . $record->filename;
				$temp_file = $fs->create_file_from_pathname($record, $tempsplashfilepath );
				$stored_file->replace_file_with($temp_file);
				$temp_file->delete();
			}else{
				//create the new file
				$stored_file = 	$fs->create_file_from_pathname($record, $tempsplashfilepath );
			}
			//need to kill the two temp files here
			if(is_readable(realpath($tempsplashfilepath ))){
				unlink(realpath($tempsplashfilepath ));
			}
			if(is_readable(realpath($tempvideofilepath))){
				unlink(realpath($tempvideofilepath));
			}
	
		//delete the temp file we made, regardless
		}else{
			if(is_readable(realpath($tempvideofile))){
				unlink(realpath($tempvideofile));
			}
		}		
		//return the stored file
		return $stored_file;

}

/*
* Convert a video file to a different format using ffmpeg
*
*/
function convert_with_ffmpeg_bg($filerecord, $tempdir, $tempfilename, $convfilenamebase, $convext){
	global $CFG;
	
   //init our fs object
	$fs = get_file_storage();
   $convfilename = $convfilenamebase . $convext;
   $placeholderfilename= "convertingmessage" . $convext;
   $filerecord->filename = $convfilename;
   $stored_file = 	$fs->create_file_from_pathname($filerecord, $CFG->dirroot . '/filter/poodll/' .  $placeholderfilename);
   
   //we need this id later, to find the old draft file and remove it, in ad hoc task
   $filerecord->id = $stored_file->get_id();
   
    // set up task and add custom data
   $conv_task = new \filter_poodll\task\adhoc_convert_media();
   $qdata = array(
       'filerecord' => $filerecord,
       'filename' => $convfilename,
       'tempdir' => $tempdir,
       'tempfilename' => $tempfilename,
       'convfilenamebase' => $convfilenamebase,
       'convext' => $convext
   );
   $conv_task->set_custom_data($qdata);
   // queue it
   \core\task\manager::queue_adhoc_task($conv_task);
   //error_log('queeued:' . $convfilename);
	//error_log(print_r($qdata,true));   
   return $stored_file;
}

/*
* Convert a video file to a different format using ffmpeg
*
*/
function convert_with_ffmpeg($filerecord, $tempdir, $tempfilename, $convfilenamebase, $convext, $throwawayname = false){

global $CFG;

		//init our fs object
		$fs = get_file_storage();

		
		//if use ffmpeg, then attempt to convert mp3 or mp4
		$convfilename = $convfilenamebase . $convext;
		//work out the options we pass to ffmpeg. diff versions supp. dioff switches
		//has to be this way really.

		switch ($convext){
			case '.mp4':
				//$ffmpegopts = "-c:v libx264 -profile:v baseline";
				$ffmpegopts = $CFG->filter_poodll_ffmpeg_mp4opts;
				break;
			case '.mp3':
				$ffmpegopts = $CFG->filter_poodll_ffmpeg_mp3opts;
				break;
			default:
				$ffmpegopts = "";
		}
		
		//if there is a version in poodll filter dir, use that
		//else use ffmpeg version on path
		if(file_exists($CFG->dirroot . '/filter/poodll/ffmpeg')){
			$ffmpegpath = $CFG->dirroot . '/filter/poodll/ffmpeg';
		}else{
			$ffmpegpath = 'ffmpeg';
		}
		
		//branch logic depending on if windows or nopt
		$iswindows =(strtoupper(substr(PHP_OS, 0, 3)) === 'WIN');
		$command = $ffmpegpath . " -i " . $tempdir . $tempfilename . " " . $ffmpegopts . " " . $tempdir . $convfilename;

		if($iswindows){
			$output = system($command, $fv);
		}else{
			shell_exec($command . " >/dev/null 2>/dev/null ");
		}
		
		/* About FFMPEG conv
		it would be better to do the conversion in the background not here.
		in that case you would place an ampersand at the end .. like this ...
		" >/dev/null 2>/dev/null &");
		But you have to get the information back to Moodle, and copy the file over, so the plumbing gets tough.
		Right now there is no "converting message" displayed to user, but we need to do this.
		*/
		
		//Check if conversion worked
		if(is_readable(realpath($tempdir . $convfilename))){
			if($throwawayname){
				$filerecord->filename = $throwawayname;
			}else{
				$filerecord->filename = $convfilename;
			}
			//error_log('we converted successfully');
			$stored_file = 	$fs->create_file_from_pathname($filerecord, $tempdir . $convfilename);
			//error_log('we stashed successfully');
			//need to kill the two temp files here
			if(is_readable(realpath($tempdir . $convfilename))){
				unlink(realpath($tempdir . $convfilename));
			}
			if(is_readable(realpath($tempdir . $tempfilename))){
				unlink(realpath($tempdir . $tempfilename));
			}
			$filename = $convfilename;
		//if failed, set return value to FALSE
		//and delete the temp file we made
		}else{
			$stored_file = false;
			if(is_readable(realpath($tempdir . $tempfilename))){
				unlink(realpath($tempdir . $tempfilename));
			}
		}		
		return $stored_file;

}//end of convert with FFMPEG



//this turns our results array into an xml string for returning to browser
function prepareXMLReturn($resultArray, $requestid){
	//set up xml to return
	$xml_output = "<result requestid='" . $requestid . "'>";

	if($resultArray['success']){
		$xml_output .= 'success';
		//not sure how this will impact attachment explorer .. (expects no messages here, but recorder expects..)
		foreach ($resultArray['messages'] as $message) {
			$xml_output .= '<error>' . $message . '</error>';
		}
	}else{
		$xml_output .= 'failure';
		foreach ($resultArray['messages'] as $message) {
			$xml_output .= '<error>' . $message . '</error>';
		}
	}


	//close off xml to return
	$xml_output .= "</result>";
	return $xml_output;
}


//this merges two result arrays, mostly for use with actions across recursive directories.
function mergeReturnArrays($return1,$return2){
	$return1['success'] = $return1['success'] && $return2['success'];
	//process return values
	if(!$return1['success'] && !$return2['success']){
		foreach ($return2['messages'] as $message) {
			array_push($return1['messages'],$message);
		}
	}
	return $return1;
}

//this initialises and returns a results array
function fetchReturnArray($initsuccess=false){
	//new filearray
	$return = array();
	$return['messages'] = array();
	$return['success'] = $initsuccess;
	return $return;
}

//The basename function is unreliable with multibyte strings
//This is a cobbled together, dodgey alternative
function poodllBasename($filepath){
	//return basename($filepath,'/');
	//if it is a directory then we should remove the trailing slash because it will
	//get exploded into an empty string
	if(substr($filepath,-1)==DIRECTORY_SEPARATOR){
		$filepath = substr($filepath,0,-1);
	}
	return end(explode(DIRECTORY_SEPARATOR,$filepath));

}

//This is a convenience function for checking that a storedfile is writeable
//
function fileIsWritable($f){
	//get the file brower object

	$browser = get_file_browser();
	$thecontext = context::instance_by_id($f->get_contextid());//get_context_instance_by_id($f->get_contextid());
	$fileinfo = $browser->get_file_info($thecontext, $f->get_component(),$f->get_filearea(), $f->get_itemid(), $f->get_filepath(), $f->get_filename());
	//if we have insuff permissions to delete. Exit.
	if(!$fileinfo || !$fileinfo->is_writable()){
		return false;
	}else{
		return true;
	}
}

//This is a convenience function for checking that a storedfile is readable
//
function fileIsReadable($f){
	//get the file brower object
	$browser = get_file_browser();
	$thecontext = context::instance_by_id($f->get_contextid());//get_context_instance_by_id($f->get_contextid());
	$fileinfo = $browser->get_file_info($thecontext, $f->get_component(),$f->get_filearea(), $f->get_itemid(), $f->get_filepath(), $f->get_filename());
	//if we have insuff permissions to delete. Exit.
	if(!$fileinfo || !$fileinfo->is_readable()){
		return false;
	}else{
		return true;
	}
}


//This tells us if the path can be written to
//dirs should have a trailing slash and root is / . if dir, filename should be blank
function pathIsWritable($moduleid, $courseid, $itemid, $filearea,$filepath=DIRECTORY_SEPARATOR,$filename=""){
	global $DB;


	//get a handle on the module context
	$thiscontext = context_module::instance($moduleid);//get_context_instance(CONTEXT_MODULE,$moduleid);

	//fetch info and ids about the module calling this data
	$course = $DB->get_record('course', array('id'=>$courseid));
	$modinfo = get_fast_modinfo($course);
	$cm = $modinfo->get_cm($moduleid);
	$component = "mod_" . $cm->modname;

	//FIlter could submit submission/draft/content/intro as options here
	if($filearea == "") {$filearea ="content";}


	//get our file object
	$filepath=DIRECTORY_SEPARATOR;
	$filename="";
	$browser = get_file_browser();
	$fileinfo = $browser->get_file_info($thiscontext, $component,$filearea, $itemid, $filepath, $filename);

	//return writeable or not
	if($fileinfo && $fileinfo->is_writable()){
		return true;
	}else{
		return false;
	}
}


//This is used to sort an array of filenames alphabetically
function compareFilenames($a, $b)
{
	return strcasecmp($a->get_filename(), $b->get_filename());
}

//This is used to sort an array of directory names alphabetically
function compareDirnames($a, $b)
{
	return strcasecmp(poodllBasename($a['dirfile']->get_filepath()), poodllBasename($b['dirfile']->get_filepath()));
}




/*
* This function is a simple replacement for pluginfile.php when called from assignemnets
* There is whitespace, newline chars, added at present(20120306) so need to bypass
*
*/
function poodllpluginfile($contextid,$component,$filearea,$itemid,$filepath,$filename){

	$fs = get_file_storage();
	$br = get_file_browser();
	$f = $fs->get_file($contextid, $component, $filearea, $itemid, $filepath, $filename);

	//if no file we just quit.
	if(!$f){return;}

	//get permission info for this file: but it doesn't work oh no.....another moodle bug?
	/*
	$thecontext = get_context_instance_by_id($contextid);
	$fileinfo = $br->get_file_info($thecontext, $component,$filearea, $itemid, $filepath, $filename);

	//if we don't have permission to read, exit
	if(!$fileinfo || !$fileinfo->is_readable()){echo "crap"; return;}
		*/

	//send_stored_file also works: but we are using send file, for no reason really
	//send_stored_file($f, 0, 0, true); // download MUST be forced - security!

	$fcontent = $f->get_content();
	send_file($fcontent, $filename, 0, 0, true, true, "video/x-flv");
	return;
}

/* download file from remote server and stash it in our file area */
//15,'123456789.flv','user','draft','746337947','99999'
function instance_remotedownload($contextid,$filename,$component, $filearea,$itemid, $requestid, $filepath='/'){
	global $CFG,$USER;
//set up return object
//set up return object

	$return=fetchReturnArray(true);

	//set up auto transcoding (mp3 or mp4) or not
	//The jsp to call is different.
	$jsp="download.jsp";
	$convertlocally=false;
	$downloadfilename = $filename;
	$ext = substr($filename,-4);
	$filenamebase = substr($filename,0,-4);
	switch($ext){

		case ".mp4":
			if ($CFG->filter_poodll_ffmpeg){
				$convertlocally=true;
				$downloadfilename = $filenamebase . ".flv";
			}else{
				$jsp="convert.jsp";
			}
			break;

		case ".mp3":
			if ($CFG->filter_poodll_ffmpeg){
				$convertlocally=true;
				$downloadfilename = $filenamebase . ".flv";
			}else{
				$jsp="convert.jsp";
			}
			break;

		case ".png":
			$jsp="snapshot.jsp";
			break;

		default:
			$jsp="download.jsp";
			break;



	}

	//setup our file manipulators
	$fs = get_file_storage();
	$browser = get_file_browser();

	//create the file record for our new file
	$file_record = new stdClass();
	$file_record->userid    = $USER->id;
	$file_record->contextid = $contextid;
	$file_record->component = $component;
	$file_record->filearea = $filearea;
	$file_record->itemid   = $itemid;
	$file_record->filepath = $filepath;
	$file_record->filename = $filename;
	$file_record->license  = $CFG->sitedefaultlicense;
	$file_record->author   = 'Moodle User';
	$file_record->source    = '';
	$file_record->timecreated = time();
	$file_record->timemodified= time();


	//one condition of using this function is that only one file can be here,
	//attachment limits
	/*
    if($filearea=='draft'){
        $fs->delete_area_files($contextid,$component,$filearea,$itemid);
    }
    */

	//if file already exists, delete it
	//we could use fileinfo, but it don&'t work
	if($fs->file_exists($contextid,$component,$filearea,$itemid,$filepath,$filename)){
		//delete here ---
	}


	//setup download information
	$red5_fileurl= "http://" . $CFG->filter_poodll_servername .
		":"  .  $CFG->filter_poodll_serverhttpport . "/poodll/" . $jsp . "?poodllserverid=" .
		$CFG->filter_poodll_serverid . "&filename=" . $downloadfilename . "&caller=" . urlencode($CFG->wwwroot);
	//download options
	$options = array();
	$options['headers']=null;
	$options['postdata']=null;
	$options['fullresponse']=false;
	$options['timeout']=300;
	$options['connecttimeout']=20;
	$options['skipcertverify']=false;
	$options['calctimeout']=false;

	//clear the output buffer, otherwise strange characters can get in to our file
	//seems to have no effect though ...
	while (ob_get_level()) {
		ob_end_clean();
	}


	//branch logic depending on whether (converting locally) or (not conv||convert on server)
	if($convertlocally){
		//determine the temp directory
		if (isset($CFG->tempdir)){
			$tempdir =  $CFG->tempdir . "/";
		}else{
			//moodle 2.1 users have no $CFG->tempdir
			$tempdir =  $CFG->dataroot . "/temp/";
		}
		//actually make the file on disk so FFMPEG can get it
		$mediastring = file_get_contents($red5_fileurl);
		$ret = file_put_contents($tempdir . $downloadfilename, $mediastring);
		//if successfully saved to disk, convert
		if($ret){
			$do_bg_encoding = ($CFG->filter_poodll_bgtranscode_audio && $ext==".mp3") ||
				($CFG->filter_poodll_bgtranscode_video && $ext==".mp4");
			if($do_bg_encoding && $CFG->version>=2014051200){
				$stored_file = convert_with_ffmpeg_bg($file_record,$tempdir,$downloadfilename,$filenamebase, $ext );
			}else{
				$stored_file = convert_with_ffmpeg($file_record,$tempdir,$downloadfilename,$filenamebase, $ext );
			}



			if($stored_file){
				$filename=$stored_file->get_filename();

				//setup our return object
				$returnfilepath = $filename;
				array_push($return['messages'],$returnfilepath );

				//if failed, default to using the original uploaded data
				//and delete the temp file we made
			}else{
				$return['success']=false;
				array_push($return['messages'],"Unable to convert file locally." );

				if(is_readable(realpath($tempdir . $filename))){
					unlink(realpath($tempdir . $filename));
				}
			}
		}else{
			$return['success']=false;
			array_push($return['messages'],"Unable to create local temp file." );
		}

		//we process the result for return to browser
		$xml_output=prepareXMLReturn($return, $requestid);

		//we return to browser the result of our file operation
		return $xml_output;
	}


	//If get here we are downloading from JSP only, ie not converting locally
	//actually copy over the file from remote server
	if(!$fs->create_file_from_url($file_record, $red5_fileurl,$options, false)){
		//	echo "boo:" . $red5_fileurl;
		$return['success']=false;
		array_push($return['messages'],"Unable to create file from url." );
	}else{
		// echo "yay:" . $red5_fileurl;
		//get a file object if successful
		$thecontext = context::instance_by_id($contextid);//get_context_instance_by_id($contextid);
		$fileinfo = $browser->get_file_info($thecontext, $component,$filearea, $itemid, $filepath, $filename);

		//if we could get a fileinfo object, return the url of the object
		if($fileinfo){
			//$returnfilepath  = $fileinfo->get_url();
			//echo "boo:" . $red5_fileurl;
			$returnfilepath = $filename;
			array_push($return['messages'],$returnfilepath );
		}else{
			//if we couldn't get an url and it is a draft file, guess the URL
			//<p><a href="http://m2.poodll.com/draftfile.php/5/user/draft/875191859/IMG_0594.MOV">IMG_0594.MOV</a></p>
			if($filearea == 'draft'){

				$returnfilepath = $filename;
				array_push($return['messages'],$returnfilepath );
			}else{
				$return['success']=false;
				array_push($return['messages'],"Unable to get URL for file." );
			}
		}//end of if fileinfo


	}//end of if could create_file_from_url


	//we process the result for return to browser
	$xml_output=prepareXMLReturn($return, $requestid);

	//we return to browser the result of our file operation
	return $xml_output;


}

function instance_download($mimetype,$filename,$filehash,$requestid){
//paramone=mimetype paramtwo=filename paramthree=filehash requestid,
	header("Cache-Control: public");
	header("Content-Description: File Transfer");
	header("Content-Disposition: attachment;filename='" . $filename . "'");
	header("Content-Type: " . $mimetype);
	header("Content-Transfer-Encoding: binary");
//header('Accept-Ranges: bytes');

	$fs = get_file_storage();
	$f = $fs->get_file_by_hash($filehash);
	if($f){
		//$content = $f->get_content();
		//echo $content;
		$f->readfile();
	}else{
		//set up return object
		$return=fetchReturnArray(false);
		array_push($return['messages'],"file not found." );
		$xml_output=prepareXMLReturn($return, $requestid);
		header("Content-type: text/xml");
		echo "<?xml version=\"1.0\"?>\n";
		echo $xml_output;
		return;
	}
}




/*
//Fetch a sub directory list for file explorer  
//calls itself recursively, dangerous
function fetch_repodircontents($dir,  $recursive=false){
	$xml_output="";
	$files = scandir($dir);
	if (!empty($files)) {
        foreach ($files as $afile) {
			if ($afile == "." || $afile == "..") {
				continue;
			}
			//here we encode the filename 
			//because the xml breaks otherwise when there arequotes etc.
			$escapedafile =  htmlspecialchars( $afile,ENT_QUOTES);
			if(is_dir($dir."/".$afile)){
				if(!$recursive){
					$xml_output .=  "\t<directory name='" . $escapedafile . "' />\n";
				}else{				
					//recursive
					$xml_output .=  "\t<directory name='" . $escapedafile . "' >\n";
					$xml_output .= fetch_repodircontents($dir."/".$afile,true);	
					$xml_output .=  "\t</directory>";
				}				
			}else{
				$xml_output .=  "\t<file name='" . $escapedafile ."' isleaf='true' "  
				. " filesize='" . filesize($dir . "/" . $afile)  
				. "' created='" . date('d M Y H:i:s', filectime($dir . "/" . $afile)) 
				. "' modified='" . date('d M Y H:i:s', filemtime($dir . "/" . $afile)) 
				. "' type='" .  htmlspecialchars(mime_content_type ($dir . "/" . $afile),ENT_QUOTES)  
				. "'/>\n";
				
				
				
				
				
			}
		}
	}
	return $xml_output;
}
*/

/*
//Fetch a directory list from the repo
function fetch_repodirlist($startpath=''){
	global $CFG;	
	
	
	global $basedir;
    global $usecheckboxes;
    global $id;
    global $USER, $CFG;
	
	//Handle directories
	$fullpath = $CFG->{'dataroot'}  . "/repository/" . $startpath;
	
	//open xml to return
	$xml_output = "<directorylist>";
	
	
	
	// New way which works with php5, but not is_dir : Justin
	$files = scandir($fullpath);
	if (!empty($files)) {
		$xml_output .= fetch_repodircontents($fullpath,true);
	}
	
	
	
	//close xml to return
	$xml_output .= "</directorylist>";
	
	//Return the data
	return $xml_output;
	
	
}
*/

/*
//Fetch a directory list from the repo
function fetch_repos(){
	global $CFG, $DB;	
	
	
	global $basedir;
    global $usecheckboxes;
    global $id;
    global $USER, $CFG;
	
	//Handle directories
	$fullpath = $CFG->{'dataroot'}  . DIRECTORY_SEPARATOR . "repository";
	
	//open xml to return
	$xml_output = "<repositories>";
	
	
	
	
	/// USe this to extract only unique file paths that can be tacked on  / add ri.contextid to hone down the permissions level
	$sql = "SELECT DISTINCT(ric.value) as filepath FROM {repository} r , {repository_instances} ri , {repository_instance_config} ric  WHERE r.type='filesystem' AND ric.name='fs_path' AND r.id=ri.typeid  AND ri.id=ric.instanceid" ;

	 //or try this if above dont work
   	//$sql = "SELECT DISTINCT(ric.value) as filepath FROM {repository} r JOIN {repository_instances} ri ON r.id=ri.typeid JOIN {repository_instance_config} ric ON ri.id=ric.instanceid WHERE r.type='filesystem' AND ric.name='fs_path'";
  
   //possibly could be shortened to 
   //$sql = "SELECT UNIQUE(ric.value) as filepath FROM {repository_instance_config} ric WHERE  ric.name='fs_path'";

   $records=$DB->get_records_sql($sql);
   if (!empty($records)) {
		
		foreach ($records as $record){
			$adir=  htmlspecialchars( $record->filepath,ENT_QUOTES);
			if(is_dir($fullpath . DIRECTORY_SEPARATOR .  $adir)){
				if($adir != "." && $adir != ".."){
					$xml_output .="<repo name='" . $adir . "' path='" . $fullpath . DIRECTORY_SEPARATOR .  $adir . "'/>";
				}
			}
		}
		
	}
	
	

	
	//close xml to return
	$xml_output .= "</repositories>";
	
	//Return the data
	return $xml_output;
	
	
}
*/

/*

//This will fetch the contents of a module instance directory, can be recursively called
function fetch_instancedir_contents($thedir, &$thecontext, $recursive=false){
	
	$browser = get_file_browser();
	$xml_output="";
	
	//first process subdirectories (if recursive)
	if(!empty($thedir['subdirs']) && $recursive){
	
		usort($thedir['subdirs'], "compareDirnames");
		
		 foreach ($thedir['subdirs'] as $subdir) {
			 //this is only necessary of you deleted the dirfile without deleting the subfiles
			 //ie only a dev, not real world situation
			 if(!array_key_exists('dirfile',$subdir)){return;}
			$f = $subdir['dirfile'];
			//$filename =$f->get_filename();
			$filename=poodllBasename($f->get_filepath());
			//$filename=basename($f->get_filepath(),"/");

			
			
					
			//fetch our info object
			$fileinfo = $browser->get_file_info($thecontext, $f->get_component(),$f->get_filearea(), $f->get_itemid(), $f->get_filepath(), $f->get_filename());
			
				//If we could get an info object, process. But if we couldn't, although we have info via $f, we don't have permissions
				//so we don't reveal it
				if($fileinfo){
					$urltofile = $fileinfo->get_url();

					
					//filehash for any delete/edit manipulations we wish to do
					$hash= $f->get_pathnamehash();	
				
					//output xml for dir (escape for odd quotes that kill xml parser)
					$xml_output .=  "\t<directory name='" . htmlspecialchars($filename,ENT_QUOTES) ."'  url='" . htmlspecialchars($urltofile,ENT_QUOTES) . "' hash='" . $hash . "'>\n";
					$xml_output .= fetch_instancedir_contents($subdir,$thecontext,true);	
					$xml_output .=  "\t</directory>";
			
				//}else{
				//	$xml_output .= "<directory url='booboo' name='" . $thecontext->id .'@'. $f->get_component() .'@'. $f->get_filearea() .'@'. $f->get_itemid() .'@'. $f->get_filepath() .'@'. $f->get_filename() . "' hash='buubuu' />";
				}
		
	
		}
		
	}
	
	//then process files
	$files = $thedir['files'];
	if (!empty($files)) {
		usort($files, "compareFilenames");
			foreach ($files as $f) {
				$filename =$f->get_filename();
				if ($filename == "." || $filename == "..") {
					continue;
				}
		
				//fetch our info object
				$fileinfo = $browser->get_file_info($thecontext, $f->get_component(),$f->get_filearea(), $f->get_itemid(), $f->get_filepath(), $f->get_filename());

				
				//get the url to the file
				if($fileinfo){
					$urltofile = $fileinfo->get_url();

						
					
					//filehash for any delete/edit manipulations we wish to do
					$hash= $f->get_pathnamehash();
					
					//create the output xml for this file/dir, we escape special characters so as not to break XML parsing
					$xml_output .=  "\t<file name='" . htmlspecialchars($filename,ENT_QUOTES) ."' isleaf='true' url='" . 
						htmlspecialchars($urltofile,ENT_QUOTES)  . "' filesize='" . $f->get_filesize()  
						. "' created='" . date('d M Y H:i:s', $f->get_timecreated())  
						. "' modified='" . date('d M Y H:i:s', $f->get_timemodified()) 
						. "' type='" . $f->get_mimetype() 
						. "' hash='" . $hash . "'/>\n";

				}//end of if($fileinfo)
				
		}//end of for each
	
	}//end of if empty
	
	return $xml_output;

}
*/

/*
//This is just so we can get a list of stuff in the old course legacy files area
//we can try if it works but it might not.
function fetch_legacydirlist($courseid){

$thiscontext = context_course::instance($courseid); //get_context_instance(CONTEXT_COURSE, $courseid);
$contextid = $thiscontext->id;
    
    $fs = get_file_storage();
  
  
  //set up xml to return	
	$xml_output = "<directorylist>\n";
  
   $fullpath = "/$contextid/course/legacy/0/";
   $file = $fs->get_file_by_hash(sha1($fullpath));
   
   if(!$file){
   	$fullpath = "/$contextid/course/legacy/0";
   	$file = $fs->get_file_by_hash(sha1($fullpath));
   }
   
   if ($file) {
   		//set up xml to return	
		//$xml_output .= "we have a file";
		$topdir = $fs->get_area_tree($file->get_contextid(), $file->get_component(), $file->get_filearea(),$file->get_itemid());
		$xml_output .= fetch_instancedir_contents($topdir,$thiscontext,true);
	}else{
		$xml_output .= "no files " . $courseid . " " . $contextid . " " . $fullpath;
	}
	

	//close xml to return
	$xml_output .= "</directorylist>";

	//Return the data
	return $xml_output;
	
}
*/



/*

//This will fetch the directory list of all the files
//available in a module instance (ie added from repository)
function fetch_instancedirlist($moduleid, $courseid, $itemid, $path, $filearea){
global $CFG, $DB;

	//FIlter could submit submission/draft/content/intro as options here
	if($filearea == "") {$filearea ="content";}
	
	//fetch info and ids about the module calling this data
	$course = $DB->get_record('course', array('id'=>$courseid));
	$modinfo = get_fast_modinfo($course);
	$cm = $modinfo->get_cm($moduleid);

	//get filehandling objects
	$browser = get_file_browser();
	$fs = get_file_storage();
	
	//set up xml to return	
	$xml_output = "<directorylist>\n";
	

	//get a handle on the module context
	$thiscontext = context_module::instance($moduleid); //get_context_instance(CONTEXT_MODULE,$moduleid);
	$contextid = $thiscontext->id;
	
	//fetch a list of files in this area, and sort them alphabetically
	
	$topdir = $fs->get_area_tree($contextid, "mod_" . $cm->modname, $filearea,$itemid);
	$xml_output .= $cm->modname . " " . $itemid;
	//when dev/testing set the recursive flag to false if you prefer not to wait for infinite loops.
	$xml_output .= fetch_instancedir_contents($topdir,$thiscontext,true);
	
	//close xml to return
	$xml_output .= "</directorylist>";

	//Return the data
	return $xml_output;

}
*/


/*

//This will delete a single file/dir from a module instance
function instance_deletefile($filehash, $requestid){
	$fs = get_file_storage();
	$f = $fs->get_file_by_hash($filehash);
	
	//set up return object	
	$return=fetchReturnArray(true);
	
	
	//if we don't get a file we can out
	if(!$f){
		$return['success']=false;
		array_push($return['messages'],"no such file/dir to delete." );
		//we process the result for return to browser
		$xml_output=prepareXMLReturn($return, $requestid);		   
		return $xml_output;
	}
	
	//call our delete file handling method
	$return = instance_deletefile_internal($f);	
	
	
	//we process the result for return to browser
	$xml_output=prepareXMLReturn($return, $requestid);		   
	return $xml_output;
}
*/

/*
//This will delete a single file/dir from a module instance
function instance_fetchfileinfo($filehash, $requestid){
	$fs = get_file_storage();
	$f = $fs->get_file_by_hash($filehash);
	
	//set up return object	
	$return=fetchReturnArray(true);
	
	
	//if we don't get a file we can out
	if(!$f){
		$return['success']=false;
		array_push($return['messages'],"no such file/dir to delete." );
		//we process the result for return to browser
		$xml_output=prepareXMLReturn($return, $requestid);		   
		return $xml_output;
	}else{
		$return['success']=false;
		array_push($return['messages'], "component:" . $f->get_component() . " filearea:" . $f->get_filearea() . " itemid:" . $f->get_itemid() . " filepath:" . $f->get_filepath() . " filename:" . $f->get_filename());
	
	}
	
	
	//we process the result for return to browser
	$xml_output=prepareXMLReturn($return, $requestid);		   
	return $xml_output;
}
*/

/*
//it is called by instance_delete, and instance_rename/copyas file
//BOGUS BOGUS BOGUS
function bogus_instance_deletefile_internal($f){
	//set up return object	
			$return=fetchReturnArray(false);

			//fetch our info object
			$browser = get_file_browser();
			$thecontext = context::instance_by_id($f->get_contextid());//get_context_instance_by_id($f->get_contextid());
			$fileinfo = $browser->get_file_info($thecontext, $f->get_component(),$f->get_filearea(), $f->get_itemid(), $f->get_filepath(), $f->get_filename());
	
			//if we don't have permission to delete, exit
			if(!$fileinfo || !$fileinfo->is_writable()){
				$return['success']=false;
				//array_push($return['messages'],"You do not have  permissions to delete this file." );
				array_push($return['messages'],'TNO:' . $thecontext->id . " " . $f->get_component() . " " .  $f->get_filearea(). " " .  $f->get_itemid(). " " .  $f->get_filepath(). " " .  $f->get_filename());		
			
			//if it is a directory, head in and do recursive processing	
			}else if($f->is_directory()){
				array_push($return['messages'],'TYES:' . $thecontext->id . " " . $f->get_component() . " " .  $f->get_filearea(). " " .  $f->get_itemid(). " " .  $f->get_filepath(). " " .  $f->get_filename());		
			
			   $sreturn= bogus_instance_deletedircontents($f);
			   $return = mergeReturnArrays($return,$sreturn);
			
			//if it is a single file, just delete it
			}else{
				array_push($return['messages'],'TTYES:' . $thecontext->id . " " . $f->get_component() . " " .  $f->get_filearea(). " " .  $f->get_itemid(). " " .  $f->get_filepath(). " " .  $f->get_filename());		
			
				
			}
			return $return;
}

*/

/*
//This will delete the contents of a directory in a module instance
//it may be called recursively if the dir contains sub dirs
function bogus_instance_deletedircontents($sfdir){
	
	//set up return object	
	$return=fetchReturnArray(false);
	
	//get file handling objects
	//it is unlikely that sub dirs or files have different permissions to their parents
	//so perhaps the permissions checks(filebrowser) are unnecessary. but 
	 $fs = get_file_storage();
	 $browser = get_file_browser();
	 
	 
	if($sfdir->is_directory()){
		$files = $fs->get_directory_files( $sfdir->get_contextid(), 
										 $sfdir->get_component(),
										 $sfdir->get_filearea(),
										 $sfdir->get_itemid(), 
										 $sfdir->get_filepath(), 
										 false,true);
		
		foreach($files as $f){
		
			$thecontext = context::instance_by_id($f->get_contextid());
			$fileinfo = $browser->get_file_info($thecontext, $f->get_component(),$f->get_filearea(), $f->get_itemid(), $f->get_filepath(), $f->get_filename());
			//if we have insuff permissions to delete. Exit.
			//if(!$fileinfo){
			if(!$fileinfo || !$fileinfo->is_writable()){
				array_push($return['messages'],' SNO:' . $thecontext->id . " " . $f->get_component() . " " .  $f->get_filearea(). " " .  $f->get_itemid(). " " .  $f->get_filepath(). " " .  $f->get_filename());		
			
			}else if(!$fileinfo->is_writable()){
				array_push($return['messages'],'SSNO:' . $thecontext->id . " " . $f->get_component() . " " .  $f->get_filearea(). " " .  $f->get_itemid(). " " .  $f->get_filepath(). " " .  $f->get_filename());		
			
			}else{	
				if(!$f->is_directory()){
					array_push($return['messages'],'SYES:' . $thecontext->id . " " . $f->get_component() . " " .  $f->get_filearea(). " " .  $f->get_itemid(). " " .  $f->get_filepath(). " " .  $f->get_filename());		
					if(!$fileinfo->delete()){				
						$return['success']=false;
						array_push($return['messages'],"unable to delete" . $f->get_filepath() . " "  . $f->get_filename());
					}
				}else{
					array_push($return['messages'],'SSYES:' . $thecontext->id . " " . $f->get_component() . " " .  $f->get_filearea(). " " .  $f->get_itemid(). " " .  $f->get_filepath(). " " .  $f->get_filename());		
					 $sreturn= bogus_instance_deletedircontents($f);
					$return = mergeReturnArrays($return,$sreturn);
				}
			}//end of is deletable
			
		}//end of for each
		
		
	//if it is not a directory complain
	}else{
			$return['success']=false;
			array_push($return['messages'],"unable to delete non dir: " . $singlefile->get_filepath() . $singlefile->get_filename());
	}
	
	return $return;
}
*/

/*
//This will delete a file or directory(by calling a recursive function), 
//it is called by instance_delete, and instance_rename/copyas file
function instance_deletefile_internal($f){

			//set up return object	
			$return=fetchReturnArray(true);

			//fetch our info object
			$browser = get_file_browser();
			$thecontext = context::instance_by_id($f->get_contextid());//get_context_instance_by_id($f->get_contextid());
			$fileinfo = $browser->get_file_info($thecontext, $f->get_component(),$f->get_filearea(), $f->get_itemid(), $f->get_filepath(), $f->get_filename());
	
			//if we don't have permission to delete, or the file cant be info'd exit
			if(!$fileinfo || !$fileinfo->is_writable()){
				$return['success']=false;
				array_push($return['messages'],"You do not have  permissions to delete this file." );
				//array_push($return['messages'],$thecontext->id . " " . $f->get_component() . " " .  $f->get_filearea(). " " .  $f->get_itemid(). " " .  $f->get_filepath(). " " .  $f->get_filename());		
			
			//if it is a directory, head in and do recursive processing	
			}else if($f->is_directory()){
			   $sreturn= instance_deletedircontents($f);
			   $return = mergeReturnArrays($return,$sreturn);
			
			//if it is a single file, just delete it
			}else{
				//array_push( $return['messages'], "DELOK" . $thecontext->id . ':' . $f->get_component() . ':' . $f->get_filearea() . ':' . $f->get_itemid()  . ':' . $f->get_filepath() . ':' . $f->get_filename());

				if($fileinfo->delete()){
					$return['success']=true;	
				}
			}
			return $return;
}
*/

/*

//This will delete the contents of a directory in a module instance
//it may be called recursively if the dir contains sub dirs
function instance_deletedircontents($sfdir){
	
	//set up return object	
	$return=fetchReturnArray(true);
	
	//get file handling objects
	//it is unlikely that sub dirs or files have different permissions to their parents
	//so perhaps the permissions checks(filebrowser) are unnecessary. but 
	 $fs = get_file_storage();
	 $browser = get_file_browser();
	 
	 
	if($sfdir->is_directory()){
		$files = $fs->get_directory_files( $sfdir->get_contextid(), 
										 $sfdir->get_component(),
										 $sfdir->get_filearea(),
										 $sfdir->get_itemid(), 
										 $sfdir->get_filepath(), 
										 false,true);
		
		foreach($files as $singlefile){
		
			$thecontext = context::instance_by_id($singlefile->get_contextid());//get_context_instance_by_id($singlefile->get_contextid());
			$fileinfo = $browser->get_file_info($thecontext, $singlefile->get_component(),$singlefile->get_filearea(), $singlefile->get_itemid(), $singlefile->get_filepath(), $singlefile->get_filename());
			
			//if the file cant be info'd, exit.
			if(!$fileinfo){
				$return['success']=false;
				array_push($return['messages'],"couldnt get fileinfo " . $singlefile->get_filepath() . ": :"  . $singlefile->get_filename());
			
			//if we have insuff. permissions, fail
			}else if(!$fileinfo->is_writable()){
		
				$return['success']=false;
				array_push($return['messages'],"You do not have adequate permissions to delete " . $singlefile->get_filepath() . " "  . $singlefile->get_filename());
		
			//attempt the delete or recursive delete if dir
			}else{	
				if(!$singlefile->is_directory()){
					if(!$fileinfo->delete()){				
						$return['success']=false;
						array_push($return['messages'],"unable to delete" . $singlefile->get_filepath() . " "  . $singlefile->get_filename());
					}
				}else{
					$sreturn = instance_deletedircontents($singlefile);
					$return = mergeReturnArrays($return,$sreturn);
				}
			}//end of is deletable
			
		}//end of for each
		
		//if we could delete all subfiles and dirs, then we can delete this dir itself.
		$files = $fs->get_directory_files( $sfdir->get_contextid(), 
									 $sfdir->get_component(),
									 $sfdir->get_filearea(),
									 $sfdir->get_itemid(), 
									 $sfdir->get_filepath(), 
									 true,true);
		if(!($files && $files.length >0)){
			$sfdir->delete();
		}	
		
	//if it is not a directory complain, single files shouldn't be passed in to this method
	}else{
			$return['success']=false;
			array_push($return['messages'],"unable to delete non dir: " . $singlefile->get_filepath() . $singlefile->get_filename());
	}
	
	return $return;
}
*/

/*

//This creates an empty dir in 
//available in a module instance (ie added from repository)
function instance_createdir($moduleid, $courseid, $itemid, $filearea, $newdir, $requestid){

	if(pathIsWritable($moduleid, $courseid, $itemid, $filearea,"/","")){
		$return = do_createdir($moduleid, $courseid, $itemid, $filearea, $newdir);
	}else{
		//set up return object	
		$return=fetchReturnArray(false);
		 $return['success']=false;
	  	 array_push($return['messages'],"insuffficient permissions to create dir: " . $newdir );
	}
	$xml_return = prepareXMLReturn($return,$requestid);
	return $xml_return;
}
*/

/*
function do_createdir($moduleid, $courseid, $itemid, $filearea, $newdir){
		global $CFG, $DB;

	//FIlter could submit submission/draft/content/intro as options here
	if($filearea == "") {$filearea ="content";}
	
	//fetch info and ids about the module calling this data
	$course = $DB->get_record('course', array('id'=>$courseid));
	$modinfo = get_fast_modinfo($course);
	$cm = $modinfo->get_cm($moduleid);
	$component = "mod_" . $cm->modname;
	
	//get a handle on the module context
	$thiscontext = context_module::instance($moduleid);//get_context_instance(CONTEXT_MODULE,$moduleid);
	$contextid = $thiscontext->id;

	//get filehandling objects
	$browser = get_file_browser();
	$fs = get_file_storage();

	
	//set up return object	
	$return=fetchReturnArray(true);
	
	//Must begin and end with slash
	if($newdir != ''){
		if (strpos($newdir, '/') !== 0){
			$newdir= '/' . $newdir;
		}
		if (strrpos($newdir , '/') !== strlen($newdir)-1){
			$newdir= $newdir . '/' ;
		}
	}else{
		$newdir= '/' ;
	}
	
	//check if file already exists, if so can out
	if($fs->file_exists($contextid,$component,$filearea,$itemid,$newdir,".")){
		//set up return object	
		$return['success']=false;
		array_push($return['messages'],$newdir . " :already exists here.");
		
		
		//for some reason this always returns false.	
	}else if($fs->create_directory($contextid, $component, $filearea, $itemid, $newdir)){
		$return['success']=true;
	
	}else{
	   $return['success']=false;
	   array_push($return['messages'],"unable to create dir: " . $newdir );
	
	}
		   
	return $return;	

}
*/

/*

//Returns boolean true if file at passed in path exists.
function instance_exists($pathname){
	return file_exists_by_hash($pathname);
}
*/


/*

//Copies over a single file from rep to module instance
//workhorse function, is called internally
function do_copyfilein($moduleid, $courseid, $itemid, $filearea, $filepath,$newpath, $requestid){
	global $CFG, $DB;


	//new return values array
	$return = fetchReturnArray(false);
	
	//FIlter could submit submission/draft/content/intro as options here
	if($filearea == "") {$filearea ="content";}
	
	//fetch info and ids about the module calling this data
	$course = $DB->get_record('course', array('id'=>$courseid));
	$modinfo = get_fast_modinfo($course);
	$cm = $modinfo->get_cm($moduleid);
	$component = "mod_" . $cm->modname;
	
	//get a handle on the module context
	$thiscontext = context_module::instance($moduleid);//get_context_instance(CONTEXT_MODULE,$moduleid);
	$contextid = $thiscontext->id;
	
	//get filehandling objects
	$browser = get_file_browser();
	$fs = get_file_storage();

	
	//Make full path to source file
	$filepath = $CFG->{'dataroot'} . $filepath;
	
	//Make full"virtual path" as new path
	if($newpath != ''){
		if (strpos($newpath, '/') !== 0){
			$newpath= '/' . $newpath;
		}
		if (strrpos($newpath , '/') !== strlen($newpath)-1){
			$newpath= $newpath . '/' ;
		}
		//$newpath= '/' . $newpath . '/';
	}else{
		$newpath= '/' ;
	}
	
	//Make filename
	//basename dont work well for multibyte unless locale set so try the explode function(maybe unic dependant though)
	//$filename=basename($filepath);
	$filename = poodllBasename($filepath);
	//$filename="hello";

	//check if file already exists, if so can out
	if($fs->file_exists($contextid,$component,$filearea,$itemid,$newpath,$filename)){
		$return['success'] = false;
		array_push($return['messages'],$filename . " already exists at " . $newpath);
		
		//Return the data
		return $return;
	}
	
	//new filearray
	$newfile = array();
	$newfile['contextid'] = $contextid;
	$newfile['component'] = $component;
	$newfile['filearea'] = $filearea;
	$newfile['itemid'] = $itemid;
	$newfile['filepath'] = $newpath; // I guess change here for subdirs, begin slash, trail slash
	$newfile['sortorder'] = "0";
	$newfile['filename'] = $filename;
	
	
	//set up xml to return	
	$xml_output = "<result requestid='" . $requestid . "'>";
	
	if($fs->create_file_from_pathname($newfile, $filepath)){
	//if(false){
		
		$return['success'] = true;
		
	//	$return['success'] = false;
	//	array_push($return['messages'], $newpath . "  " . $filepath);
		
		return $return;
	}else{
		$return['success'] = false;
		array_push($return['messages'],"Unable to create " . $filename . " at " . $newpath);
		//array_push($return['messages'],"newfilename:" . $newfile['filename']);
		//array_push($return['messages'],"filepath:" . $newfile['filepath']);
		//array_push($return['messages'],"itemid:" . $newfile['itemid']);
		return $return;
	}
	
}
*/

/*

//Copy a single file into an instance file area
function instance_copyfilein($moduleid, $courseid, $itemid, $filearea, $filepath,$newpath, $requestid){
	global $CFG, $DB;
	
	//do the copying and fetch back the result
	//$filepath="/repository/audiofiles/adir/jackquizimages/iconfour.png";
	$return = do_copyfilein($moduleid, $courseid, $itemid, $filearea, $filepath,$newpath, $requestid);
							
	$xml_output = prepareXMLReturn($return, $requestid);

	//Return the data
	return $xml_output;
	
}
*/

/*

//Fetch a sub directory list for file explorer  
//calls itself recursively
function instance_copydircontents($moduleid, $courseid, $itemid, $filearea, $dir,$newpath, $requestid,  $recursive=false){
	global $CFG;
	
	//new return values array
	$dirreturn = fetchReturnArray(true);
	
	$fullpath = $CFG->{'dataroot'}  . $dir; 
	$files = scandir($fullpath);
	if (!empty($files)) {
        foreach ($files as $afile) {
			if ($afile == "." || $afile == "..") {
				continue;
			}
			
			//differntiate between copying file and copying subdir
			if(is_dir($fullpath."/".$afile) && $recursive){
				//$subsubreturn = do_createdir($moduleid, $courseid, $filearea, $newdir);
				$subreturn =  instance_copydircontents($moduleid, $courseid, $itemid, $filearea, $dir."/".$afile ,$newpath . "/" . $afile, $requestid,  $recursive);
			}else{
				$subreturn = do_copyfilein($moduleid, $courseid, $itemid, $filearea, $dir."/". $afile,$newpath, $requestid);
			}
			
			//process return values
			if(!$subreturn['success']){
				$dirreturn = mergeReturnArrays($dirreturn,$subreturn);
			}//end of process returns
		}//end of for each file
	}//end of if empty files

	return $dirreturn;
}
*/

/*

//Copy an entire directory from rep over to module instance
function instance_copydirin($moduleid, $courseid, $itemid, $filearea, $filepath,$newpath, $requestid){
	global $USER, $CFG;	
	
	
	global $basedir;
    global $usecheckboxes;
    global $id;
    
	
	//Handle directories
	$fullpath = $CFG->{'dataroot'}  . $filepath;
	
	//prepare return array
	$return=fetchReturnArray(false);
	
	$files = scandir($fullpath);
	
	//if no files to copy throw error
	if (empty($files)) {
		array_push($return['messages'],"no files in directory to copy.");
		
	}else{
		//if area writeable proceed, else throw error
		if(pathIsWritable($moduleid, $courseid, $itemid, $filearea,"/","")){
			$return = instance_copydircontents($moduleid, $courseid, $itemid, $filearea, $filepath,$newpath, $requestid,true);
		
		}else{
			$return['success']=false;
			array_push($return['messages'],"you do not have permission to write in this directory.");
		
		}
	}//end of if empty files 
	
	
	//Return the data
	$xml_output = prepareXMLReturn($return,$requestid);
	//$xml_output = "<result>I love you</result>";
	return $xml_output;
}
*/

/*
function instance_duplicatefile($moduleid, $courseid, $itemid, $filearea, $filepath, $originalhash, $requestid){
	
	//set up return object	
	$return=fetchReturnArray(true);
	
	//get filehandling objects
	$browser = get_file_browser();
	$fs = get_file_storage();
	
	//get file to copy 
	$f = $fs->get_file_by_hash($originalhash);
	
	if(!$f){
		$return['success']=false;
		array_push($return['messages'],"Unable to fetch original file. ");
	}else{
		//kick off the recursive thing
		$return = instance_duplicatefilecontents($f, $moduleid, $courseid, $itemid, $filearea, $filepath , $requestid);
	}
	
	//Return the data
	$xml_output = prepareXMLReturn($return,$requestid);
	//$xml_output = "<result>I love you</result>";
	return $xml_output;

}
*/

/*


function instance_duplicatefilecontents($f, $moduleid, $courseid, $itemid, $filearea, $filepath , $requestid, $filename=''){
	global $CFG, $DB;

	//new return values array
	$return = fetchReturnArray(true);
	
	//get filehandling objects
	$browser = get_file_browser();
	$fs = get_file_storage();
	
	//get file to copy 
	if(!$f){
		$return['success']=false;
		array_push($return['messages'],"Unable to fetch original file. ");
		return $return;
	}
	
	//Filter could submit submission/draft/content/intro as options here
	if($filearea == "") {$filearea ="content";}
	
	//fetch info and ids about the module to which we will duplicate our file.
	$course = $DB->get_record('course', array('id'=>$courseid));
	$modinfo = get_fast_modinfo($course);
	$cm = $modinfo->get_cm($moduleid);
	$component = "mod_" . $cm->modname;
	
	//get a handle on the module context
	$thiscontext = context_module::instance($moduleid);//get_context_instance(CONTEXT_MODULE,$moduleid);
	$contextid = $thiscontext->id;
	
	
	//use the original filename , as the new name if we have not been given a name to use
	
	//$filepath=$f->get_filepath();
	
	if ($f->is_directory()){
	
		
		$files = $fs->get_directory_files( $f->get_contextid(), 
										 $f->get_component(),
										 $f->get_filearea(),
										 $f->get_itemid(), 
										 $f->get_filepath(), 
										 true,true);
		
		//get the dir name	if one has not been passed in
		if($filename==''){
			$dirname=poodllBasename($f->get_filepath());
			
		}else{
			//here we need to get logic
			//e.g /home/villages should go to /home/smalltown but it is going to /home/villages/smalltown
			//so we need to get the parent directory (what if root?)
			$filepath = $f->get_parent_directory()->get_filepath();
			$dirname=$filename;
		}
		
		//add the new dir name to the filepath to copy to								 
		$filepath = $filepath . $dirname  .  DIRECTORY_SEPARATOR;
			
	
		
		
		//call on the copy logic for each file
		foreach($files as $singlefile){
			
			$subreturn = instance_duplicatefilecontents($singlefile,$moduleid, $courseid,$itemid, $filearea, $filepath , $requestid);
				//process return values
			if(!$subreturn['success']){
				$return = mergeReturnArrays($return,$subreturn);
			}//end of process returns
		}//end of for each
	
	
	
		
	
	}else{
	
		//autogenerate a filename if one has not been passed in
		if($filename==''){
			$filename=$f->get_filename();
		}
		
		//fetch the file info object for our original file
		$original_context = context::instance_by_id($f->get_contextid());//get_context_instance_by_id($f->get_contextid());
		$original_fileinfo = $browser->get_file_info($original_context, $f->get_component(),$f->get_filearea(), $f->get_itemid(), $f->get_filepath(), $f->get_filename());
	
		//perform the copy	
		if($original_fileinfo){
			$return['success'] = $original_fileinfo->copy_to_storage($contextid, $component, $filearea, $itemid, $filepath, $filename);
			//$return['success'] =false;
			
		}//end of if $original_fileinfo
	}

	//add a message if we could not do the action
	if(!$return['success']){
		array_push($return['messages'],"unable to copy file to: " . $filepath . $filename);
	}

	//Return the data
	return $return;

}
*/

/*

//here we rename a file or directory
function instance_renamefile($moduleid, $courseid, $originalhash,$newfilename, $copyas, $requestid){
	//set up return object	
	$return=fetchReturnArray(true);
	
	//get filehandling objects
	$browser = get_file_browser();
	$fs = get_file_storage();
	
	//get file to copy 
	$f = $fs->get_file_by_hash($originalhash);
	
	//get oldfilename
	if($f){
		if($f->is_directory()) {
			$oldfilename=poodllBasename($f->get_filepath());
		}else{
			$oldfilename=$f->get_filename();
		}
	}else{
		$oldfilename="";
	}
	
	if(!$f){
		$return['success']=false;
		array_push($return['messages'],"Unable to fetch original file: " . $originalhash);
	//check if the file we wish to copy to already exists, and that the new filename is diff to old one
	}else if( $newfilename == $oldfilename){
		$return['success']=false;
		array_push($return['messages'],"can't rename a file with its original name.");
		
	}else if($fs->file_exists($f->get_contextid(),$f->get_component(),$f->get_filearea(),$f->get_itemid(),$f->get_filepath(),$newfilename)){
		$return['success']=false;
		array_push($return['messages'],"A file with that name already exists in the current directory.");
	
	}else {
		//kick off the recursive copy thing
		$return = instance_duplicatefilecontents($f, $moduleid, $courseid, $f->get_itemid(), $f->get_filearea(), $f->get_filepath() , $requestid, $newfilename);
	}
	
	//if we were successful, and were renaming, delete the original
	if($return['success']){
		if(!$copyas){
			$return = instance_deletefile_internal($f);
		}
	
	//if we were not successful, and renaming, add an error
	}else{
		if(!$copyas){
			array_push($return['messages'],"Because of errors, original file(s) not deleted ");
		}
	}
	
	//Return the data
	$xml_output = prepareXMLReturn($return,$requestid);
	//$xml_output = "<result>I love you</result>";
	return $xml_output;

}
*/

/*

function xinstance_renamefile($moduleid, $courseid, $itemid, $filearea,  $filepath,$newfilename, $copyas, $requestid){
	global $CFG, $DB;

	//new return values array
	$return = fetchReturnArray(false);
	
	
	//FIlter could submit submission/draft/content/intro as options here
	if($filearea == "") {$filearea ="content";}
	
	//fetch info and ids about the module calling this data
	$course = $DB->get_record('course', array('id'=>$courseid));
	$modinfo = get_fast_modinfo($course);
	$cm = $modinfo->get_cm($moduleid);
	$component = "mod_" . $cm->modname;
	
	//get a handle on the module context
	$thiscontext = context_module::instance($moduleid); //get_context_instance(CONTEXT_MODULE,$moduleid);
	$contextid = $thiscontext->id;
	

	//establish our filename and filepath
	$filename=poodllBasename($filepath);
	//There is probably a better way to do this, that doesnt hang on multipbyte
	//need to remove filename and leave trailing dir sep. 
	//perhaps we should have sent it from widget to here with preceding slash? This could get confusing ..
	$filepath=DIRECTORY_SEPARATOR . strrev(strstr(strrev($filepath),DIRECTORY_SEPARATOR));
	
	
	
	//get filehandling objects
	$browser = get_file_browser();
	$fs = get_file_storage();
	$f = $fs->get_file($contextid,$component,$filearea,$itemid,$filepath,$filename) ;
	
	//get file fails if it is directory (i think its a bug ..check latest moodle version)
	//if($f && $f->is_directory()) {
	if(!$f){
		$return['success']=false;
		array_push($return['messages'],"Directories cannot be copied or renamed(yet). Sorry, next version.");
	
	//check if the file we wish to copy to already exists, and that the new filename is diff to old one
	}else if( $newfilename == $filename){
		$return['success']=false;
		array_push($return['messages'],"can't rename a file with its original name.");
		
	}else if($fs->file_exists($contextid,$component,$filearea,$itemid,$filepath,$newfilename)){
		$return['success']=false;
		array_push($return['messages'],"A file with that name already exists in the current directory.");
	

	
	//commence the copy and delete
	}else{
		//fetch our info object
		$fileinfo = $browser->get_file_info($thiscontext, $component,$filearea, $itemid, $filepath, $filename);
		if($fileinfo && $fileinfo->is_writable()){
			$return['success'] = $fileinfo->copy_to_storage($contextid, $component, $filearea, $itemid, $filepath, $newfilename);
			//if we could copy ok, lets delete the original file
			if($return['success']){
			
					if($copyas || $fileinfo ->delete()){
						$return['success']=true;	
					}else{
						$return['success']=false;
						array_push($return['messages'],"copied but unable to delete original file." );
					}
			
			}
		}else{
			$return['success']=false;
			array_push($return['messages'],"unable to fetch original file. Are you logged in?");
			//array_push($return['messages'],$thiscontext->id . " " . $component . " " .  $filearea . " " .  $itemid . " " .  $filepath . " " .  $filename);
		}
	}


	
	
	if(!$return['success']){
		$return['success']=false;
		array_push($return['messages'],"unable to rename/copy file");
	}

	//Return the data
	$xml_output = prepareXMLReturn($return,$requestid);
	return $xml_output;

}
*/

/*

//this initialises and returns a results array
//But I think it may misguided, because we need more info at use time
//than is available to be passed in
//function fetchRealUrl($moduleid, $courseid, $filearea, $filepath, $requestid){
function fetchRealUrl($moduleid,$courseid, $itemid, $filearea, $filepath, $requestid){
	global $PAGE, $DB;
	
	//new return values array
	$return = fetchReturnArray(false);
	
	
	//fetch info and ids about the module calling this data
	$course = $DB->get_record('course', array('id'=>$courseid));
	$modinfo = get_fast_modinfo($course);
	
	//get component info
	//may be able to avoid useing moduleid, by using PAGE global
	$cm = $modinfo->get_cm($moduleid);
	//$cm=$PAGE->cm;
	$component = "mod_" . $cm->modname;
	
	//get module context
	//may be able to avoid useing moduleid, by using PAGE global
	$thecontext = context_module::instance($moduleid); //get_context_instance(CONTEXT_MODULE,$moduleid);
	//$thecontext=$PAGE->context;

	
	
	
	//FIlter could submit submission/draft/content/intro as options here
	if($filearea == "") {$filearea ="content";}
	
	
	//establish our filename
	$filename=poodllBasename($filepath);
	//There is probably a better way to do this, that doesnt hang on multipbyte
	//need to remove filename and leave trailing dir sep
	$filepath=strrev(strstr(strrev($filepath),DIRECTORY_SEPARATOR));
	
	
	//get the file brower object
	$browser = get_file_browser();
			
					
	//fetch our info object
	$fileinfo = $browser->get_file_info($thecontext, $component,$filearea, $itemid, $filepath, $filename);
			
	//get the url to the file
	if($fileinfo){
			$urltofile = $fileinfo->get_url();
	}else{
			$urltofile = "accessdenied";
	}

	//prepare our return array
	$return['success']=false;
	array_push($return['messages'],"we have a url");
	array_push($return['messages'],$filepath . " " . $filename);
	//array_push($return['messages'],$thecontext->id . " " . $component . " " .  $filearea . " " .  $itemid . " " .  $filepath . " " .  $filename);
	array_push($return['messages'],$urltofile);

	//Return the data
	$xml_output = prepareXMLReturn($return,$requestid);
	return $xml_output;


}
*/

/*
function getmoddata($courseid,$requestid){
	global $DB;
	
	
	
	//fetch info and ids about the modules in this course
	$course = $DB->get_record('course', array('id'=>$courseid));
 	$modinfo =& get_fast_modinfo($course);   
    get_all_mods($courseid, $mods, $modnames, $modnamesplural, $modnamesused);
    $sections = get_all_sections($courseid);

    
    $sectionarray = array();
    foreach($sections as $section){
    	
    	//$sectionarray[$section->id] = get_section_name($course,$section);
    	//here we will store all the mods for the section
    	$sectionarray[$section->section] = array();
    	
    }
    
    //for each mod add its name and id to an array for its section
    foreach($mods as $mod) {
    		//$modname = htmlspecialchars($modinfo->cms[$mod->id]->name, ENT_QUOTES);
    		$modname = htmlspecialchars($mod->name, ENT_QUOTES);
    		$modtype = $mod->modfullname;
    		$sectionid=$modinfo->cms[$mod->id]->sectionnum;
    		array_push($sectionarray[$sectionid], "<module sectionid='" . $sectionid . "' modid='" . $mod->id .  "' modname='" . $modname. "'  modtype='" . $modtype . "'  />");
    }
    
    //init xml output
  	$xml_output = "<course courseid='" . $courseid . "'>";
   
   //go through each section adding a sect header and all the modules in it
   foreach($sections as $section){
    	
    	//$sectionarray[$section->id] = get_section_name($course,$section);
    	//here we will store all the mods for the section
    	$sectionname =  htmlspecialchars(get_section_name($course,$section),ENT_QUOTES);
    	$xml_output .= "<section sectionid='" . $section->section . "' sectionname='" . $sectionname ."'>"; 
    	foreach($sectionarray[$section->section]  as $line){
    		$xml_output .= "\t" . $line;
    	}
    	$xml_output .= "</section>";
    }

   //close off xml output
    $xml_output .= "</course>";
   
    //"section", "section, id, course, name, summary, summaryformat, sequence, visible");

	//Return the data
	//$xml_output = prepareXMLReturn($return,$requestid);
	return $xml_output;
}
*/

/*
function getLast20Files(){
global $DB;
	$sql = "select * from {files} order by id desc limit 20;";
	 
   $records=$DB->get_records_sql($sql);
   if (!empty($records)) {
		
		foreach ($records as $record){
		
			print_object($record);
		}
		
	}
}
*/