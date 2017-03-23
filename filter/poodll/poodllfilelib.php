<?php

/**
* internal library of functions and constants for Poodll modules
* accessed directly by poodll flash wdgets on web pages.
* @package filter_poodll
* @category mod
* @author Justin Hunt
*
*/


/**
* Includes and requires
*/

global $CFG;


define('POODLL_VIDEO_PLACEHOLDER_HASH','c2a342a0a664f2f1c4ea5387554a67caf3dd158e');
define('POODLL_AUDIO_PLACEHOLDER_HASH','e118549e4fc88836f418b6da6028f1fec571cd43');

//we need to do this, because when called from a widet, cfg is not set
//but the relative path fails from a quiz but it has already been set in that case
//, so we check before we call it, to cover both bases

if(!isset($CFG)){
require_once("../../config.php");
}

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
        
     //from the universal recorder
    //from the general recorder (mp3)
	$mediatype  = optional_param('mediatype', "", PARAM_TEXT);
	$filename  = optional_param('filename', "", PARAM_TEXT);


	//error log flags
    CONST LOG_PFL_TEMPDIR_FAIL=1;
    CONST LOG_PFL_FILE_SAVE_FAIL=2;
    CONST LOG_PFL_FILE_EXISTS=3;
    CONST LOG_PFL_MAKE_SF_FAIL=4;
    CONST LOG_PFL_LOCAL_CONVERT_FAIL=5;
    CONST LOG_PFL_DOWNLOAD_FAIL=6;
    CONST LOG_PFL_CREATE_FROM_URL_FAIL=7;
    CONST LOG_PFL_FILE_CREATED=8;




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
		case "confirmarrival":
			header("Content-type: text/xml");
			echo "<?xml version=\"1.0\"?>\n";
			//uploadfile filedata(base64), fileextension (needs to be cleaned), blah blah 
			//paramone is the file data, paramtwo is the file extension, paramthree is the mediatype (audio,video, image)
			//requestid is the actionid
			$returnxml = filter_poodll_confirmarrival($mediatype,$filename);
			break;
                    
		case "uploadfile":
			header("Content-type: text/xml");
			echo "<?xml version=\"1.0\"?>\n";
			//uploadfile filedata(base64), fileextension (needs to be cleaned), blah blah 
			//paramone is the file data, paramtwo is the file extension, paramthree is the mediatype (audio,video, image)
			//requestid is the actionid
			$returnxml = filter_poodll_uploadfile($paramone,$paramtwo, $paramthree, $requestid,$contextid, $comp, $farea,$itemid);
			break;
		
		case "poodllpluginfile":
			//poodllpluginfile($contextid,$component,$filearea,$itemid,$filepath,$filename);
			//lets hard code this for now, very very mild security
			filter_poodll_poodllpluginfile($contextid,"mod_assignment","submission",$itemid,"/",$paramone);
			return;
                        
        case "handles3upload":
               $returnxml = filter_poodll_handle_s3_upload($mediatype, $contextid, $comp, $farea,$itemid,$filename);
              	//probably not necessary to return anything, but just in case
              	if(!$returnxml){return;}
               header("Content-type: text/xml");
			   echo "<?xml version=\"1.0\"?>";
			   break;

		case "instancedownload":
			//paramone=mimetype paramtwo=path paramthree=hash
			filter_poodll_instance_download($paramone,$paramtwo,$hash,$requestid);

		case "instanceremotedownload":
			//($contextid,$filename,$component, $filearea,$itemid, $requestid)
			//e.g (15, '123456789.flv','user','draft','746337947',777777)
			$returnxml=filter_poodll_instance_remotedownload($contextid, $paramone,$paramtwo,$paramthree,$itemid,$requestid);

			//move the output to here so that there is no trace of stray characters entering output before file downloaded
			header("Content-type: text/xml");
			echo "<?xml version=\"1.0\"?>";

			break;

		default:
			return;


	}//end of switch


	echo $returnxml;
	return;

//**************************************************************
//**************************************************************
//**************************************************************
//**************************************************************

//this initialises and returns a results array
function filter_poodll_fetchReturnArray($initsuccess=false){
	//new filearray
	$return = array();
	$return['messages'] = array();
	$return['success'] = $initsuccess;
	return $return;
}

//this turns our results array into an xml string for returning to browser
function filter_poodll_prepareXMLReturn($resultArray, $requestid){
	//set up xml to return
	$xml_output = "<result requestid='" . $requestid . "'>";

	if($resultArray['success']){
		$xml_output .= 'success';
		foreach ($resultArray['messages'] as $message) {
			$xml_output .= '<filename>' . $message . '</filename>';
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

//this turns our results array into an xml string for returning to browser
function filter_poodll_prepareLegacyXMLReturn($resultArray, $requestid){
	//set up xml to return
	$xml_output = "<result requestid='" . $requestid . "'>";

	if($resultArray['success']){
		$xml_output .= 'success';
		foreach ($resultArray['messages'] as $message) {
			//wpould like to change this errponeous use of word error, but need to 
			//recompile recorders ..hassle J
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


//For uploading a file direct from an HTML5 or SWF widget
function filter_poodll_uploadfile($filedata,  $fileextension, $mediatype, $actionid,$contextid, $comp, $farea,$itemid){
	global $CFG,$USER;

	//setup our return object
	$return=filter_poodll_fetchReturnArray(true);
//error_log('FE:' . $fileextension);
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
		case "webm":
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


	//if file already exists, raise an error
	if($fs->file_exists($contextid,$comp,$farea,$itemid,$filepath,$filename)){
		if($mediatype=='image'){
            \filter_poodll\poodlltools::send_debug_data(LOG_PFL_FILE_EXISTS,'image file already exists. will delete and try:' . $filename,$USER->id,$contextid,'poodllfilelib.php');
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
            \filter_poodll\poodlltools::send_debug_data(LOG_PFL_FILE_EXISTS,'Quitting. file already exists:' . $filename,$USER->id,$contextid,'poodllfilelib.php');
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
			$tempdir =  $CFG->tempdir . "/";
			
			//actually make the file on disk so FFMPEG can get it
			$ret = file_put_contents($tempdir . $filename, $xfiledata);

			//if successfully saved to disk, convert
			if($ret){
				$do_bg_encoding = ($CFG->filter_poodll_bgtranscode_audio && $convext==".mp3") ||
					($CFG->filter_poodll_bgtranscode_video && $convext==".mp4");
				if($do_bg_encoding && $CFG->version>=2014051200){
					$stored_file = \filter_poodll\poodlltools::convert_with_ffmpeg_bg($record,$filename,$filenamebase, $convext );
				}else{
					$stored_file = \filter_poodll\poodlltools::convert_with_ffmpeg($record, $filename,$filenamebase, $convext );
				}
				if($stored_file){
					$filename=$stored_file->get_filename();

					//if failed, default to using the original uploaded data
					//and delete the temp file we made
				}else{
                    \filter_poodll\poodlltools::send_debug_data(LOG_PFL_MAKE_SF_FAIL,'Unable to create stored file:' . $filename,$USER->id,$contextid,'poodllfilelib.php');
					$stored_file = $fs->create_file_from_string($record, $xfiledata);
					if(is_readable(realpath($tempdir . $filename))){
						unlink(realpath($tempdir . $filename));
					}
				}

				//if couldn't create on disk fall back to the original data
			}else{
                \filter_poodll\poodlltools::send_debug_data(LOG_PFL_TEMPDIR_FAIL,'Unable to save file to temp dir:' . $filename,$USER->id,$contextid,'poodllfilelib.php');
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
        \filter_poodll\poodlltools::send_debug_data(LOG_PFL_FILE_CREATED,'Successfully created:' . $filename,$USER->id,$contextid,'poodllfilelib.php');

		//if unsuccessful, return error
	}else{
        \filter_poodll\poodlltools::send_debug_data(LOG_PFL_SAVEFILE_FAIL,'Unable to save file with:' . $filename,$USER->id,$contextid,'poodllfilelib.php');
		$return['success']=false;
		array_push($return['messages'],"unable to save file with filename:" . $filename );
	}

	//we process the result for return to browser
	$xml_output=filter_poodll_prepareXMLReturn($return, $actionid);

	//we return to widget/client the result of our file operation
	return $xml_output;
}


/*
* This function is a simple replacement for pluginfile.php when called from assignemnets
* There is whitespace, newline chars, added at present(20120306) so need to bypass
*
*/
function filter_poodll_poodllpluginfile($contextid,$component,$filearea,$itemid,$filepath,$filename){

	$fs = get_file_storage();
	$br = get_file_browser();
	$f = $fs->get_file($contextid, $component, $filearea, $itemid, $filepath, $filename);

	//if no file we just quit.
	if(!$f){return;}


	$fcontent = $f->get_content();
	send_file($fcontent, $filename, 0, 0, true, true, "video/x-flv");
	return;
}

/* Here we check if the file has been received over on S3 */
function filter_poodll_confirmarrival($mediatype, $filename){
    global $CFG,$USER;

    $return=filter_poodll_fetchReturnArray(true);

      $return['success']=false;       
      $ret =  \filter_poodll\poodlltools::confirm_s3_arrival($mediatype, $filename);
        
      if($ret){
          array_push($return['messages'],'file arrived:' . $filename );
      }else{
	  array_push($return['messages'],"no file arrival" );
      }
      
        //we process the result for return to browser
	$xml_output=filter_poodll_prepareXMLReturn($return, '99999');

	//we return to browser the result of our file operation
	return $xml_output;
        
//set up return object

}

/* The alerts us to the fact that the file has been uploaded to S3. We commence handling */
function filter_poodll_handle_s3_upload($mediatype, $contextid, $comp, $farea,$itemid,$filename){
    
        $return=filter_poodll_fetchReturnArray(true);
    
		global $CFG,$USER;
        $draftfilerecord =new stdClass();
        $draftfilerecord->userid    = $USER->id;
        $draftfilerecord->contextid=$contextid;
        $draftfilerecord->component=$comp;
        $draftfilerecord->filearea=$farea;
        $draftfilerecord->itemid=$itemid;
        $draftfilerecord->filepath='/';
        $draftfilerecord->filename=$filename;
        $draftfilerecord->license  = $CFG->sitedefaultlicense;
		$draftfilerecord->author   = 'Moodle User';
		$draftfilerecord->source    = '';
		$draftfilerecord->timecreated = time();
		$draftfilerecord->timemodified= time();
        
             
     	 $ret =  \filter_poodll\poodlltools::postprocess_s3_upload($mediatype, $draftfilerecord);
        
      	if(!$ret){
          $return['success']=false;
	  		array_push($return['messages'],"Unable to postprocess s3 upload." );
      	}
      
        //we process the result for return to browser
		$xml_output=filter_poodll_prepareXMLReturn($return, '99999');

		//we return to browser the result of our file operation
		return $xml_output;
        
//set up return object

}

/* download file from remote server and stash it in our file area */
//15,'123456789.flv','user','draft','746337947','99999'
function filter_poodll_instance_remotedownload($contextid,$filename,$component, $filearea,$itemid, $requestid, $filepath='/'){
	global $CFG,$USER;

//set up return object
	$return=filter_poodll_fetchReturnArray(true);

	//set up auto transcoding (mp3 or mp4) or not
	//The jsp to call is different.
	$jsp="download.jsp";
	$convertlocally=false;
	$convertremotely=false;
	$downloadfilename = $filename;
	$ext = substr($filename,-4);
	$filenamebase = substr($filename,0,-4);

	
	switch($ext){

		case ".mp4":
		case ".mp3":
			if ($CFG->filter_poodll_cloudrecording){
				$convertremotely=true;
				$downloadfilename = $filenamebase . ".flv";
			}elseif($CFG->filter_poodll_ffmpeg){
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
	

	//if file already exists, delete it
	//we could use fileinfo, but it don&'t work
	if($fs->file_exists($contextid,$component,$filearea,$itemid,$filepath,$filename)){
		//delete here ---
	}


	//setup download information
	$red5_fileurl= "http://" . $CFG->filter_poodll_servername .
		":"  .  $CFG->filter_poodll_serverhttpport . "/poodll/" . $jsp . "?poodllserverid=" .
		$CFG->filter_poodll_serverid . "&filename=" . $downloadfilename . "&caller=" . urlencode($CFG->wwwroot);
		
//echo $red5_fileurl . '<br />';		
		
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


	//convert remotely on AWS
	if($convertremotely){
		 $awstools = new \filter_poodll\awstools();
		 $mediatype='video';
		 	 
		 $filedata = download_file_content($red5_fileurl);
		
		 switch($ext){
		 	case '.mp4': $mediatype='video';
		 		break;
			case '.mp3': $mediatype='audio';
		 		break;		 
		 }
		 $key =  \filter_poodll\awstools::fetch_s3_filename($mediatype, $filename);
		 $success = $awstools->s3_put_filedata($mediatype,$key,$filedata);
     	 $ret =  \filter_poodll\poodlltools::postprocess_s3_upload($mediatype, $file_record);
     	 if(!$ret){
     	 	$return['success']=false;
			array_push($return['messages'],"Unable to setup s3 post processing." );
		}else{
			//setup our return object
			$return['success']=true;
			array_push($return['messages'],$filename );
		}

		//we process the result for return to browser
		$xml_output=filter_poodll_prepareLegacyXMLReturn($return, $requestid);

		//we return to browser the result of our file operation
		return $xml_output;
     	 
	}

	//Convert locally using FFMPEG
	if($convertlocally){
		//determine the temp directory
		$tempdir =  $CFG->tempdir . "/";

	
		//actually make the file on disk so FFMPEG can get it
		$mediastring = download_file_content($red5_fileurl);

		$ret = file_put_contents($tempdir . $downloadfilename, $mediastring);
		

		//if successfully saved to disk, convert
		if($ret){
			$do_bg_encoding = ($CFG->filter_poodll_bgtranscode_audio && $ext==".mp3") ||
				($CFG->filter_poodll_bgtranscode_video && $ext==".mp4");

			if($do_bg_encoding){
				$stored_file = \filter_poodll\poodlltools::convert_with_ffmpeg_bg($file_record,$downloadfilename,$filenamebase, $ext );
			}else{
				$stored_file = \filter_poodll\poodlltools::convert_with_ffmpeg($file_record,$downloadfilename,$filenamebase, $ext );
			}

			if($stored_file){
				$filename=$stored_file->get_filename();
				//setup our return object
				$returnfilepath = $filename;
				array_push($return['messages'],$returnfilepath );

				//if failed, default to using the original uploaded data
				//and delete the temp file we made
			}else{
                \filter_poodll\poodlltools::send_debug_data(LOG_PFL_LOCAL_CONVERT_FAIL,
                        'Unable to convert locally:' . $downloadfilename,
                        $USER->id,$contextid,'poodllfilelib.php');
				$return['success']=false;
				array_push($return['messages'],"Unable to convert file locally." );

				if(is_readable(realpath($tempdir . $filename))){
					unlink(realpath($tempdir . $filename));
				}
			}
		}else{
            \filter_poodll\poodlltools::send_debug_data(LOG_PFL_DOWNLOAD_FAIL,
                    'Unable to download ' . $red5_fileurl . 'and save to:' . $tempdir . $downloadfilename,
                    $USER->id,$contextid,'poodllfilelib.php');
			$return['success']=false;
			array_push($return['messages'],"Unable to create local temp file." );
		}

		//we process the result for return to browser
		$xml_output=filter_poodll_prepareLegacyXMLReturn($return, $requestid);

		//we return to browser the result of our file operation
		return $xml_output;
	}//end of if converting locally


	//If get here we are downloading from JSP only, ie not converting locally or remotely
	//We actually copy over the file from remote server
	if(!$fs->create_file_from_url($file_record, $red5_fileurl,$options, false)){
        \filter_poodll\poodlltools::send_debug_data(LOG_PFL_CREATE_FROM_URL_FAIL,
                'Unable to create from URL:' . $red5_fileurl,
                $USER->id,$contextid,'poodllfilelib.php');
		$return['success']=false;
		array_push($return['messages'],"Unable to create file from url." );
	}else{
		//get a file object if successful
		$thecontext = context::instance_by_id($contextid);//get_context_instance_by_id($contextid);
		$fileinfo = $browser->get_file_info($thecontext, $component,$filearea, $itemid, $filepath, $filename);

		//if we could get a fileinfo object, return the url of the object
		if($fileinfo){
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
	$xml_output=filter_poodll_prepareLegacyXMLReturn($return, $requestid);

	//we return to browser the result of our file operation
	return $xml_output;


}

function filter_poodll_instance_download($mimetype,$filename,$filehash,$requestid){
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
		$return=filter_poodll_fetchReturnArray(false);
		array_push($return['messages'],"file not found." );
		$xml_output=filter_poodll_prepareLegacyXMLReturn($return, $requestid);
		header("Content-type: text/xml");
		echo "<?xml version=\"1.0\"?>\n";
		echo $xml_output;
		return;
	}
}