<?php

/**
* internal library of functions and constants for Poodll modules
* accessed directly by poodll flash widgets on web pages.
* @package mod-poodllpairwork
* @category mod
* @author Justin Hunt
*
*/


/**
* Includes and requires
*/
//relative path is dangerous, so only use it if we have no $CFG already Justin 20120424
if(!isset($CFG)){
require_once("../../config.php");
}

//added for moodle 2
require_once($CFG->libdir . '/filelib.php');

	$datatype = optional_param('datatype', "", PARAM_TEXT);    // Type of action/data we are requesting
	$courseid  = optional_param('courseid', 0, PARAM_INT);  // the id of the course 
	$moduleid  = optional_param('moduleid', 0, PARAM_INT);  // the id of the module 
	$hash  = optional_param('hash', "", PARAM_TEXT);  // file or dir hash
	$requestid  = optional_param('requestid', "", PARAM_TEXT);  // file or dir hash
	$paramone  = optional_param('paramone', "", PARAM_TEXT);  // nature of value depends on datatype, maybe path
	$paramtwo  = optional_param('paramtwo', "", PARAM_TEXT);  // nature of value depends on datatype, maybe protocol
	$paramthree  = optional_param('paramthree', "", PARAM_TEXT);  // nature of value depends on datatype, maybe filearea
	$paramfour  = optional_param('paramfour', "", PARAM_TEXT);  // nature of value depends on datatype, maybe filearea
	
	$dm = new \filter_poodll\dataset_manager();

	switch($datatype){

		case "xmlpairs": 
			header("Content-type: text/xml");
			echo "<?xml version=\"1.0\"?>\n";
			$returnxml = $dm->fetch_xmlpairs($courseid);
			break;
		case "unassignedusers": 
			header("Content-type: text/xml");
			echo "<?xml version=\"1.0\"?>\n";
			$returnxml = $dm->fetch_unassigned_users(5,$courseid,null);
			break;
		case "offlineusers": 
			header("Content-type: text/xml");
			echo "<?xml version=\"1.0\"?>\n";
			$returnxml="";
			break;

		case "courseusers": 
			header("Content-type: text/xml");
			echo "<?xml version=\"1.0\"?>\n";
			$returnxml=$dm->fetch_course_users($courseid);
			break;

		case "coursemenu": 
			header("Content-type: text/xml");
			echo "<?xml version=\"1.0\"?>\n";
			$returnxml=$dm->fetch_course_menu($courseid);
			break;
			
		case "poodllcastjnlp": 
			header("Content-type: application/x-java-jnlp-file");
			echo "<?xml version=\"1.0\"?>\n";
			$returnxml=$dm->fetch_poodllcastdata_jnlp($courseid);
			break;	
			
		case "poodllcastapplet": 
			header("Content-type: text/html");
			$returnxml=$dm->fetch_poodllcastdata_applet($courseid);
			break;		
		
		case "poodllcastjnlpapplet": 
			header("Content-type: text/html");
			$returnxml=$dm->fetch_poodllcastdata_jnlpapplet($courseid);
			break;
			
		case "oldpoodllaudiolist": 
			header("Content-type: text/xml");
			echo "<?xml version=\"1.0\"?>\n";
			$returnxml=$dm->fetch_oldpoodllaudiolist($courseid, $paramone, $paramtwo);
			break;

		case "poodllmedialist": 
			header("Content-type: text/xml");
			echo "<?xml version=\"1.0\"?>\n";
			if($paramthree=="poodlldata"){
				$returnxml=$dm->fetch_poodllmedialist_poodlldata($courseid, $paramone, $paramtwo);
			}else{
				$returnxml=$dm->fetch_poodllmedialist($moduleid, $courseid, $paramone, $paramtwo, $paramthree);
			}
			break;
		
		case "poodllrsslist": 
			header("Content-type: application/rss+xml");
			echo "<rss version=\"2.0\" 
				xmlns:media=\"http://search.yahoo.com/mrss/\"
				xmlns:fp=\"http://flowplayer.org/fprss/\">";
			//moduleid/courseid/path/playerype/filearea
			$returnxml=$dm->fetch_poodllaudiolist($moduleid, $courseid, $paramone, $paramtwo, $paramthree,"rss",$paramfour=="true");
			$returnxml .="</rss>";
			break;
			
		case "poodllaudiolist": 
			header("Content-type: text/xml");
			echo "<?xml version=\"1.0\"?>\n";
			//moduleid/courseid/path/playerype/filearea
			$returnxml=$dm->fetch_poodllaudiolist($moduleid, $courseid, $paramone, $paramtwo, $paramthree,"xml");
			break;
			
		case "poodllflashcards": 
			header("Content-type: text/xml");
			echo "<?xml version=\"1.0\"?>\n";
			//courseid, cardset id,cardset name, fgcolor, bgcolor 
			$returnxml=$dm->fetch_poodllflashcards($courseid, $paramone,$paramtwo,$paramthree,$paramfour);
			break;	
			
		case "poodllflashcardsconvert": 
			header("Content-type: text/xml");
			echo "<?xml version=\"1.0\"?>\n";
			$returnxml=$dm->fetch_poodllflashcardsconvert($courseid, $paramone);
			break;		
			
		case "dirlist": 
			header("Content-type: text/xml");
			echo "<?xml version=\"1.0\"?>\n";
			$returnxml=$dm->fetch_dirlist($courseid, $paramone);
			break;

		case "repodirlist": 
			header("Content-type: text/xml");
			echo "<?xml version=\"1.0\"?>\n";
			$returnxml=fetch_repodirlist($paramone);
			break;	
			
		case "instancedirlist": 
			header("Content-type: text/xml");
			echo "<?xml version=\"1.0\"?>\n";
			$returnxml=fetch_instancedirlist($moduleid, $courseid, $paramone, $paramtwo);
			break;
				
		case "instancedeleteall": 
			header("Content-type: text/xml");
			echo "<?xml version=\"1.0\"?>\n";
			$returnxml=instance_deleteall($moduleid, $courseid, $paramone, $requestid);
			break;
			
		case "instancecopyfile": 
			header("Content-type: text/xml");
			echo "<?xml version=\"1.0\"?>\n";
			$returnxml=instance_copyfilein($moduleid, $courseid, $paramone, $paramtwo,$paramthree, $requestid);
			break;
		
		case "instancedeletefile": 
			header("Content-type: text/xml");
			echo "<?xml version=\"1.0\"?>\n";
			$returnxml=instance_deletefile($hash, $requestid);
			break;
			
		case "instancecreatedir": 
			header("Content-type: text/xml");
			echo "<?xml version=\"1.0\"?>\n";
			$returnxml=instance_createdir($moduleid, $courseid, $paramone, $paramtwo, $requestid);
			break;

		case "instancecopydir": 
			header("Content-type: text/xml");
			echo "<?xml version=\"1.0\"?>\n";
			$returnxml=instance_copydirin($moduleid, $courseid, $paramone, $paramtwo, $paramthree, $requestid);
			break;
			
		default:
			header("Content-type: text/xml");
			echo "<?xml version=\"1.0\"?>\n";
			$returnxml="";
			break;	
	}
	echo $returnxml;
	return;
?>