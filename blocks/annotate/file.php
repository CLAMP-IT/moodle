<?php 
    require_once('../../config.php');
    require_once('../../lib/filelib.php');
    require_once('../../lib/moodlelib.php');
    
    // security: this script is intended to be accessed from the A.nnotate server which is independent of the 
    // user's current session and is not authenticated as the user.  
    // Instead it must be called with an argument x=tmpid, where tmpid is the name of a file that has just been 
    // created in temp/annotate containing the path of the file we should actually serve. 
    // The existence of the temp/annotate/--tmpid-- file indicates that the requesting application has followed
    // a trusted link from inside moodle and that it should be given the file to display to the user.
     
    
    $filecode = required_param('x', PARAM_ALPHANUM);
    
    $temp_dir = "$CFG->dataroot/temp/annotate";
    $filepath = "$temp_dir/$filecode";
    
    // Only serve the target file if the temp file was created in the last 3 minutes. 
    // It was made by the page that redirected to annotate, so this should only fail if 
    // there is a problem with that connection. 

    $xcontent = "";
    
    if (file_exists($filepath)  && (time() - filemtime($filepath)) < 60*3) {
          $bits = file($filepath);
   //      unlink($filepath);
     } else {
     	if (file_exists($filepath)) {
     		unlink($filepath);
     	}
        not_found();
     }
    
       
     if (count($bits) > 1) {
     	$pnhash = $bits[1];
       	$fs = get_file_storage();
    	//$pathnamehash = sha1($context->id.'resource_content0'.$resource->mainfile);
    	$file = $fs->get_file_by_hash($pnhash);
	    if ($file) {
	     	send_stored_file($file, 60*60, 0, 1);
	    } else {
	    	send_file_not_found();
	    } 
     } 
    
    function not_found($fullpath="") {
        header('HTTP/1.0 404 not found');
        print_error('filenotfound', 'error');  
    }
?>
