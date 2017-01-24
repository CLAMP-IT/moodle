<?php 
 
require_once('annotateApi.php');

function makeLoginLink($user, $loc, $errloc, $sig) {
  global $CFG;
  
  // the api function we will call first
  $phpfn = "loginAs.php";
  
  // the email address of the user (a new user account will be created if needed)
  $annotateuser = $user; 
  
  // Make this request valid for 30 mins.
  $validfor = 60 * 30; 
	
  // add code for signing the request so the a.nnotate server trusts us
  $request = signRequest($phpfn, $CFG->block_annotate_apiuser, $CFG->block_annotate_apikey, $user, $validfor);
  
  // the url we will call is the api php with the signing code in the get arguments
  $url = $CFG->block_annotate_server_url."/php/$phpfn?$request";

  // loc is the url displayed once the user is logged in - the front page of the transferred document
  $url .= "&loc=".rawurlencode($loc);
  
  // errloc is the destination if there is a problem logging them in
  $url .= "&errloc=".rawurlencode($errloc);

  // Store the password + username in a cookie. Can set remember=0 for no cookies
  $url .= "&remember=1"; 
   
  // create the user's account of necessary
  $url .= "&create=1";
  
  // give them an annotate signature the same as their moodle username
  $url .= "&sig=".rawurlencode($sig);
  
  // used the licensed account to authorize the above
  $url .= "&licensed=1";
  return $url;
}



function simpleLoginURL() {
	global $CFG;
	global $USER;
	$ret = "";
	$dest = $CFG->block_annotate_server_url."/php/documents.php";
	if (strlen(trim($CFG->block_annotate_apiuser)) > 10) {
        $errurl = $CFG->wwwroot.'/blocks/annotate/error.php';
          
        $phpfn = "loginAs.php";
  
  		// the email address of the user (a new user account will be created if needed)
 	    $user = $USER->email;
  
  		// Make this request valid for 30 mins.
  		$validfor = 60 * 30; 
	
        // add code for signing the request so the a.nnotate server trusts us
        $request = signRequest($phpfn, $CFG->block_annotate_apiuser, $CFG->block_annotate_apikey, $user, $validfor);
  
        // the url we will call is the api php with the signing code in the get arguments
        $ret = $CFG->block_annotate_server_url."/php/$phpfn?$request";
           
     } else {
     	// no master user configured. Just ask the user to create an account or log in.
     	 $ret = $CFG->block_annotate_server_url."/php/documents.php";
     }
	return $ret;
}


function exposeFileAndRedirect($docpath, $code, $pnhash="", $owner="" , $nameOfCourse="",$courseID="") {
	global $CFG;
	global $USER;
	
     // write a temporary file containing the docpath to tell file.php what to send when 
     // called from the remote site. 
     $temp_dir = "$CFG->dataroot/temp/annotate";
     if (!file_exists("$CFG->dataroot/temp")) {
          mkdir( "$CFG->dataroot/temp", $CFG->directorypermissions );
     }
     if (!file_exists( $temp_dir )) {
          mkdir( $temp_dir, $CFG->directorypermissions );
     }

   
     $fh = fopen( "$temp_dir/$code", 'w' );
     fputs( $fh, $docpath);
     if ($pnhash) {
     	fputs($fh, "\n");
     	fputs($fh, $pnhash);
     } 
     fclose( $fh );
     
     // $docpath is in the url sent to annotate so each file gets a unique url and 
     // annotate can tell if it already has the file or whether it must fetch it.
     // When it requests the file from file.php, the rest of the url is ignored (could be unsafe)
     // and only the x argument is used to look up what to actually send.
     
     $shortname = basename($docpath);
     //foksot keep it short the url in order to have a small get
     $postParams = array(
     "fmt" => "redir"  ,
     "hash" => $pnhash,
     "xCode" => $code,
     "type" => "moodle",
     "courseName" => $nameOfCourse,
     "moodleId" => $CFG->block_moodle_id,
     "courseId" =>$courseID,
     "fname" => $shortname,
     "fuser" => $USER->email,
     "uname" => $USER->firstname,
     "ulname" => $USER->lastname);
    if($owner){
      $postParams["sluser"] = $owner;
      $postParams["slshared"] = 1; 
    }
    $uploadurl = $CFG->block_annotate_server_url.'/php/lmsConnector.php?install='.rawurlencode($CFG->wwwroot);
    foreach ($postParams as $key => $value) {
       $uploadurl .="&$key=".rawurlencode($value);
    }  
/*    
     $uploadurl = $CFG->block_annotate_server_url . '/php/lmsConnector.php' .
               "?url=".rawurlencode($realurl)."&fmt=redir&furi=".rawurlencode($niceurl).
    	       "&fname=".$shortname;*/
 /*    
	if ($owner) {	
     	$uploadurl = $uploadurl . "&fuser=" . $USER->email;
        $uploadurl = $uploadurl . "&sluser=" . $owner;
   		$uploadurl = $uploadurl . "&slshared=1";
	}  */
      


      
 //   print "upload url is : " . $uploadurl;
     
     // the arguments sent to the server are:
     // url:  the real url that the server can get the file from if necessary
     // fmt:  action to take on arrival: it should redirect to the page displaying the resource
     // furi: the "nice" url of the file. This is the name used in Moodle and will form part of the metadata
     //       of the resource in A.nnotate.
     // fname: the name of the file
     // fuser: the user account on A.nnotate that should get access to the file 
     // sluser: ("Soft Link user", though not necessarily a real user). This is a code for the storage area to be used 
     // for master copies of documents. If a document is already in this area, then it is not uploaded again. 
     
     
     
     // redirecting to this url should transfer the file if necessary and go to the annotate page.
	 if (strlen(trim($CFG->block_annotate_apiuser)) > 10) {
	 	  // we have a master user and API key, so we can auto-create accounts and auto-login
          $errurl = $CFG->wwwroot.'/blocks/annotate/error.php';
          $loginurl = makeLoginLink($USER->email, $uploadurl, $errurl, $USER->username);
         	
          header("Location:" . $loginurl);
     
  } else {
     	// no master user configured. Just ask the user to create an account or log in.
      	header("Location:" . $uploadurl);
  }
  
}
