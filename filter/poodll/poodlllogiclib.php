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

require_login();

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

			
		case "poodllflashcards": 
			header("Content-type: text/xml");
			echo "<?xml version=\"1.0\"?>\n";
			//courseid, cardset id,cardset name, fgcolor, bgcolor 
			$returnxml=$dm->fetch_poodllflashcards($courseid, $paramone,$paramtwo,$paramthree,$paramfour);
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